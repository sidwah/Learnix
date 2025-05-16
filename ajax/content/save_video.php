<?php
// ../ajax/content/save_video.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get input data - handle both POST and JSON input
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
$isJson = strpos($contentType, 'application/json') !== false;

if ($isJson) {
    // Handle JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Extract data fields
    $topic_id = isset($data['topic_id']) ? intval($data['topic_id']) : 0;
    $title = isset($data['title']) ? trim($data['title']) : '';
    $description = isset($data['description']) ? trim($data['description']) : '';
    $content_id = isset($data['content_id']) ? intval($data['content_id']) : 0;
    $source = isset($data['source']) ? trim($data['source']) : 'url';
    
    if ($source === 'url') {
        $video_url = isset($data['video_url']) ? trim($data['video_url']) : '';
        $video_file = null;
    } else {
        $video_url = null;
        $video_file = isset($data['video_file']) ? trim($data['video_file']) : '';
    }
} else {
    // Handle form POST data
    $topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
    $source = isset($_POST['source']) ? trim($_POST['source']) : 'url';
    
    if ($source === 'url') {
        $video_url = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
        $video_file = null;
    } else {
        $video_url = null;
        $video_file = isset($_POST['video_file']) ? trim($_POST['video_file']) : '';
    }
}

// Validate required input
if (!$topic_id || empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate content based on source
if ($source === 'url' && empty($video_url)) {
    echo json_encode(['success' => false, 'message' => 'Video URL cannot be empty']);
    exit;
}

if ($source === 'upload' && empty($video_file) && !isset($_FILES['video_file'])) {
    echo json_encode(['success' => false, 'message' => 'Video file cannot be empty']);
    exit;
}

// Validate video URL if source is url
if ($source === 'url' && !filter_var($video_url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid video URL format']);
    exit;
}

// Get user info for tracking
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Verify that the topic belongs to a section of a course assigned to the current instructor
$stmt = $conn->prepare("
    SELECT 
        st.section_id, 
        cs.course_id
    FROM 
        section_topics st
    JOIN 
        course_sections cs ON st.section_id = cs.section_id
    JOIN 
        course_instructors ci ON cs.course_id = ci.course_id
    WHERE 
        st.topic_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $topic_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$topic_data = $result->fetch_assoc();
$stmt->close();

if (!$topic_data) {
    echo json_encode(['success' => false, 'message' => 'Topic not found or not authorized']);
    exit;
}

// Handle file upload if source is upload and a new file is being uploaded
if ($source === 'upload' && isset($_FILES['video_file']) && $_FILES['video_file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['video_file'];
    
    // Validate file type
    $allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska'];
    $fileType = $file['type'];
    
    if (!in_array($fileType, $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: MP4, WebM, MOV, MKV']);
        exit;
    }
    
    // Validate file size (max 100MB)
    $maxSize = 100 * 1024 * 1024; // 100MB in bytes
    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds the 100MB limit']);
        exit;
    }
    
    // Generate unique filename
    $filename = 'video_topic_' . $topic_id . '_' . time() . '_' . rand(1000, 9999) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $uploadDir = '../../uploads/videos/';
    
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $targetPath = $uploadDir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $video_file = $filename;
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
        exit;
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Check if content already exists
    if ($content_id > 0) {
        // First, save the current version if it exists
        $stmt = $conn->prepare("
            SELECT * FROM topic_content 
            WHERE content_id = ? AND topic_id = ?
        ");
        $stmt->bind_param("ii", $content_id, $topic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $current_content = $result->fetch_assoc();
        $stmt->close();
        
        if ($current_content) {
            // Save the current version if versioning table exists
            if ($conn->query("SHOW TABLES LIKE 'topic_content_versions'")->num_rows > 0) {
                $stmt = $conn->prepare("
                    INSERT INTO topic_content_versions 
                    (content_id, version_number, content_data, created_by, created_at)
                    SELECT 
                        ?, 
                        IFNULL((SELECT MAX(version_number) FROM topic_content_versions WHERE content_id = ?) + 1, 1),
                        JSON_OBJECT(
                            'title', ?,
                            'content_type', ?,
                            'video_url', ?,
                            'video_file', ?,
                            'description', ?,
                            'position', ?
                        ),
                        ?,
                        NOW()
                ");
                $stmt->bind_param(
                    "iisssssis",
                    $content_id,
                    $content_id,
                    $current_content['title'],
                    $current_content['content_type'],
                    $current_content['video_url'],
                    $current_content['video_file'],
                    $current_content['description'],
                    $current_content['position'],
                    $user_id
                );
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Update existing content based on source
        if ($source === 'url') {
            $stmt = $conn->prepare("
                UPDATE topic_content
                SET title = ?, video_url = ?, video_file = NULL, description = ?, 
                    updated_at = NOW(), updated_by = ?
                WHERE content_id = ? AND topic_id = ?
            ");
            $stmt->bind_param("sssiii", $title, $video_url, $description, $user_id, $content_id, $topic_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE topic_content
                SET title = ?, video_url = NULL, video_file = ?, description = ?, 
                    updated_at = NOW(), updated_by = ?
                WHERE content_id = ? AND topic_id = ?
            ");
            $stmt->bind_param("sssiii", $title, $video_file, $description, $user_id, $content_id, $topic_id);
        }
        
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Video content updated successfully',
            'content_id' => $content_id,
            'source' => $source,
            'file_path' => $source === 'upload' ? $video_file : null
        ]);
    } else {
        // Check if any content already exists for this topic
        $stmt = $conn->prepare("SELECT content_id FROM topic_content WHERE topic_id = ?");
        $stmt->bind_param("i", $topic_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_content = $result->fetch_assoc();
        $stmt->close();
        
        if ($existing_content) {
            // Delete existing content
            $stmt = $conn->prepare("DELETE FROM topic_content WHERE topic_id = ?");
            $stmt->bind_param("i", $topic_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Create new content based on source
        if ($source === 'url') {
            $stmt = $conn->prepare("
                INSERT INTO topic_content 
                (topic_id, content_type, title, video_url, description, position, created_at, created_by)
                VALUES (?, 'video', ?, ?, ?, 0, NOW(), ?)
            ");
            $stmt->bind_param("isssi", $topic_id, $title, $video_url, $description, $user_id);
        } else {
            $stmt = $conn->prepare("
                INSERT INTO topic_content 
                (topic_id, content_type, title, video_file, description, position, created_at, created_by)
                VALUES (?, 'video', ?, ?, ?, 0, NOW(), ?)
            ");
            $stmt->bind_param("isssi", $topic_id, $title, $video_file, $description, $user_id);
        }
        
        $stmt->execute();
        $new_content_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Video content created successfully',
            'content_id' => $new_content_id,
            'source' => $source,
            'file_path' => $source === 'upload' ? $video_file : null
        ]);
    }
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>