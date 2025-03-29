<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['topic_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$topic_id = intval($_POST['topic_id']);

// Verify that the topic belongs to a section of a course owned by the current instructor
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

// Start transaction
$conn->begin_transaction();

try {
    // Get content IDs for this topic
    $stmt = $conn->prepare("SELECT content_id FROM topic_content WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $content_result = $stmt->get_result();
    $content_ids = [];
    while ($content = $content_result->fetch_assoc()) {
        $content_ids[] = $content['content_id'];
    }
    $stmt->close();
    
    // Delete content versions if any
    if (!empty($content_ids)) {
        $content_placeholders = str_repeat('?,', count($content_ids) - 1) . '?';
        
        $stmt = $conn->prepare("DELETE FROM topic_content_versions WHERE content_id IN ($content_placeholders)");
        $stmt->bind_param(str_repeat('i', count($content_ids)), ...$content_ids);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete topic content
    $stmt = $conn->prepare("DELETE FROM topic_content WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete topic resources
    $stmt = $conn->prepare("DELETE FROM topic_resources WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete quizzes associated with this topic
    $stmt = $conn->prepare("SELECT quiz_id FROM section_quizzes WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $quizzes_result = $stmt->get_result();
    $quiz_ids = [];
    while ($quiz = $quizzes_result->fetch_assoc()) {
        $quiz_ids[] = $quiz['quiz_id'];
    }
    $stmt->close();
    
    if (!empty($quiz_ids)) {
        $quiz_placeholders = str_repeat('?,', count($quiz_ids) - 1) . '?';
        
        // Delete quiz questions
        $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE quiz_id IN ($quiz_placeholders)");
        $stmt->bind_param(str_repeat('i', count($quiz_ids)), ...$quiz_ids);
        $stmt->execute();
        $stmt->close();
        
        // Delete quizzes
        $stmt = $conn->prepare("DELETE FROM section_quizzes WHERE quiz_id IN ($quiz_placeholders)");
        $stmt->bind_param(str_repeat('i', count($quiz_ids)), ...$quiz_ids);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete the topic
    $stmt = $conn->prepare("DELETE FROM section_topics WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id);
    $stmt->execute();
    $stmt->close();
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $topic_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Reorder remaining topics
  // Replace the SQL position reordering with this:
$conn->query("SET @pos = 0");
$stmt = $conn->prepare("
    UPDATE section_topics 
    SET position = (@pos := @pos + 1) 
    WHERE section_id = ? 
    ORDER BY position
");
$stmt->bind_param("i", $topic_data['section_id']);
$stmt->execute();
$stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Topic deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>