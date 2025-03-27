<?php
// Include required files
require_once '../../backend/config.php';

// Check if user is logged in as instructor
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get POST data
$topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : 0;
$content_text = isset($_POST['content_text']) ? $_POST['content_text'] : '';
$title = isset($_POST['title']) ? $_POST['title'] : '';
$description = isset($_POST['description']) ? $_POST['description'] : '';
$position = isset($_POST['position']) ? intval($_POST['position']) : 0;
$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;

// Validate data
if (!$topic_id) {
    echo json_encode(['success' => false, 'message' => 'Topic ID is required']);
    exit;
}

if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Content title is required']);
    exit;
}

// Check if the instructor has rights to this topic
$stmt = $conn->prepare("
    SELECT cs.course_id 
    FROM section_topics st 
    JOIN course_sections cs ON st.section_id = cs.section_id 
    JOIN courses c ON cs.course_id = c.course_id 
    WHERE st.topic_id = ? AND c.instructor_id = (SELECT instructor_id FROM instructors WHERE user_id = ?)
");
$stmt->bind_param("ii", $topic_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to edit this content']);
    exit;
}

// If content_id is provided, update existing content
if ($content_id > 0) {
    $stmt = $conn->prepare("
        UPDATE topic_content 
        SET title = ?, content_text = ?, description = ?, position = ?, updated_at = NOW() 
        WHERE content_id = ? AND topic_id = ?
    ");
    $stmt->bind_param("sssiii", $title, $content_text, $description, $position, $content_id, $topic_id);
    $success = $stmt->execute();
    
    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Text content updated successfully', 'content_id' => $content_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update content: ' . $conn->error]);
    }
} else {
    // Insert new content
    $stmt = $conn->prepare("
        INSERT INTO topic_content (topic_id, content_type, title, content_text, description, position)
        VALUES (?, 'text', ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssi", $topic_id, $title, $content_text, $description, $position);
    $success = $stmt->execute();
    
    if ($success) {
        $content_id = $conn->insert_id;
        echo json_encode(['success' => true, 'message' => 'Text content added successfully', 'content_id' => $content_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add content: ' . $conn->error]);
    }
}
?>