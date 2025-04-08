<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate that we have a file upload and other required data
if (!isset($_FILES['video_file']) || $_FILES['video_file']['error'] !== UPLOAD_ERR_OK || 
    !isset($_POST['title']) || empty($_POST['title'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required data']);
    exit;
}

$title = trim($_POST['title']);
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;

// Verify that the topic belongs to a section of a course owned by the current instructor
if ($topic_id > 0) {
    $stmt = $conn->prepare("
        SELECT st.section_id, cs.course_id, c.instructor_id 
        FROM section_topics st
        JOIN course_sections cs ON st.section_id = cs.section_id
        JOIN courses c ON cs.course_id = c.course_id
        WHERE st.topic_id = ?
    ");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $topic_data = $result->fetch_assoc();
    $stmt->close();

    if (!$topic_data || $topic_data['instructor_id'] != $_SESSION['instructor_id']) {
        echo json_encode(['success' => false, 'message' => 'Topic not found or not authorized']);
        exit;
    }
}

// Validate file
$file = $_FILES['video_file'];
$allowedTypes = ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-matroska'];
$fileType = $file['type'];

// Check MIME type
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed types: MP4, WebM, MOV, MKV']);
    exit;
}

// Check file size (max 100MB)
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

// Move the uploaded file
if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    if ($content_id > 0) {
        // Update existing content
        $stmt = $conn->prepare("
            UPDATE topic_content
            SET title = ?, video_url = NULL, video_file = ?, description = ?, updated_at = NOW()
            WHERE content_id = ? AND topic_id = ?
        ");
        $stmt->bind_param("sssii", $title, $filename, $description, $content_id, $topic_id);
        $stmt->execute();
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Video uploaded and content updated successfully',
            'content_id' => $content_id,
            'file_path' => $filename,
            'file_name' => $file['name']
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
        
        // Create new content
        $stmt = $conn->prepare("
            INSERT INTO topic_content 
            (topic_id, content_type, title, video_file, description, position, created_at)
            VALUES (?, 'video', ?, ?, ?, 0, NOW())
        ");
        $stmt->bind_param("isss", $topic_id, $title, $filename, $description);
        $stmt->execute();
        $new_content_id = $stmt->insert_id;
        $stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Video uploaded and content created successfully',
            'content_id' => $new_content_id,
            'file_path' => $filename,
            'file_name' => $file['name']
        ]);
    }
    
    // Update course last modified timestamp if we have course data
    if (isset($topic_data) && isset($topic_data['course_id'])) {
        $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
        $stmt->bind_param("i", $topic_data['course_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
} catch (Exception $e) {
    // Rollback in case of error and remove uploaded file
    $conn->rollback();
    if (file_exists($targetPath)) {
        unlink($targetPath);
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>