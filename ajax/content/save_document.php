<?php
//ajax/content/save_document.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['topic_id']) || !isset($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$topic_id = intval($_POST['topic_id']);
$title = trim($_POST['title']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title cannot be empty']);
    exit;
}

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

// Handle file upload if a new file is provided
$file_path = null;
$file_name = null;

if (isset($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
    // Get file details
    $file_tmp = $_FILES['document_file']['tmp_name'];
    $file_size = $_FILES['document_file']['size'];
    $file_name = $_FILES['document_file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validate file size (max 10MB)
    if ($file_size > 10 * 1024 * 1024) {
        echo json_encode(['success' => false, 'message' => 'File size exceeds 10MB limit']);
        exit;
    }
    
    // Validate file extension
    $allowed_ext = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'];
    if (!in_array($file_ext, $allowed_ext)) {
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX']);
        exit;
    }
    
    // Create upload directory if not exists
    $upload_dir = '../../uploads/documents/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Generate unique filename
    $file_path = 'doc_' . $topic_id . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $file_ext;
    $upload_path = $upload_dir . $file_path;
    
    // Move uploaded file
    if (!move_uploaded_file($file_tmp, $upload_path)) {
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
            // Save the current version
            $stmt = $conn->prepare("
                INSERT INTO topic_content_versions 
                (content_id, version_number, content_data, created_by, created_at)
                SELECT 
                    ?, 
                    IFNULL((SELECT MAX(version_number) FROM topic_content_versions WHERE content_id = ?) + 1, 1),
                    JSON_OBJECT(
                        'title', ?,
                        'content_type', ?,
                        'description', ?,
                        'file_path', ?,
                        'position', ?
                    ),
                    ?,
                    NOW()
            ");
            $stmt->bind_param(
                "iissssis",
                $content_id,
                $content_id,
                $current_content['title'],
                $current_content['content_type'],
                $current_content['description'],
                $current_content['file_path'],
                $current_content['position'],
                $user_id
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // Update existing content
        if ($file_path) {
            $stmt = $conn->prepare("
                UPDATE topic_content
                SET title = ?, description = ?, file_path = ?, 
                    updated_at = NOW(), updated_by = ?
                WHERE content_id = ? AND topic_id = ?
            ");
            $stmt->bind_param("sssiii", $title, $description, $file_path, $user_id, $content_id, $topic_id);
        } else {
            $stmt = $conn->prepare("
                UPDATE topic_content
                SET title = ?, description = ?, 
                    updated_at = NOW(), updated_by = ?
                WHERE content_id = ? AND topic_id = ?
            ");
            $stmt->bind_param("ssiii", $title, $description, $user_id, $content_id, $topic_id);
        }
        $stmt->execute();
        $stmt->close();
        
        // Get current file path for response
        if (!$file_path) {
            $stmt = $conn->prepare("SELECT file_path FROM topic_content WHERE content_id = ?");
            $stmt->bind_param("i", $content_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $file_data = $result->fetch_assoc();
            $file_path = $file_data['file_path'];
            $file_name = basename($file_path);
            $stmt->close();
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Document content updated successfully',
            'content_id' => $content_id,
            'file_path' => $file_path,
            'file_name' => $file_name
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
        
        // Require file for new document content
        if (!$file_path) {
            echo json_encode(['success' => false, 'message' => 'Document file is required for new content']);
            $conn->rollback();
            exit;
        }
        
        // Create new content
        $stmt = $conn->prepare("
            INSERT INTO topic_content 
            (topic_id, content_type, title, description, file_path, position, created_at, created_by)
            VALUES (?, 'document', ?, ?, ?, 0, NOW(), ?)
        ");
        $stmt->bind_param("isssi", $topic_id, $title, $description, $file_path, $user_id);
        $stmt->execute();
        $new_content_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Document content created successfully',
            'content_id' => $new_content_id,
            'file_path' => $file_path,
            'file_name' => $file_name
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
    
    // Delete uploaded file if any
    if ($file_path && file_exists('../../uploads/documents/' . $file_path)) {
        unlink('../../uploads/documents/' . $file_path);
    }
    
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>