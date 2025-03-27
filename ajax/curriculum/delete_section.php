<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['section_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$section_id = intval($_POST['section_id']);

// Verify that the section belongs to a course owned by the current instructor
$stmt = $conn->prepare("
    SELECT cs.course_id, c.instructor_id 
    FROM course_sections cs
    JOIN courses c ON cs.course_id = c.course_id
    WHERE cs.section_id = ?
");
$stmt->bind_param("i", $section_id);
$stmt->execute();
$result = $stmt->get_result();
$section_data = $result->fetch_assoc();
$stmt->close();

if (!$section_data || $section_data['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Section not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Get all topics in this section
    $stmt = $conn->prepare("SELECT topic_id FROM section_topics WHERE section_id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $topics_result = $stmt->get_result();
    $topic_ids = [];
    while ($topic = $topics_result->fetch_assoc()) {
        $topic_ids[] = $topic['topic_id'];
    }
    $stmt->close();
    
    // Delete topic content for all topics in this section
    if (!empty($topic_ids)) {
        // First get content IDs
        $content_ids = [];
        $placeholders = str_repeat('?,', count($topic_ids) - 1) . '?';
        
        $stmt = $conn->prepare("SELECT content_id FROM topic_content WHERE topic_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($topic_ids)), ...$topic_ids);
        $stmt->execute();
        $content_result = $stmt->get_result();
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
        $stmt = $conn->prepare("DELETE FROM topic_content WHERE topic_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($topic_ids)), ...$topic_ids);
        $stmt->execute();
        $stmt->close();
        
        // Delete topic resources
        $stmt = $conn->prepare("DELETE FROM topic_resources WHERE topic_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($topic_ids)), ...$topic_ids);
        $stmt->execute();
        $stmt->close();
        
        // Delete quizzes associated with topics
        $stmt = $conn->prepare("SELECT quiz_id FROM section_quizzes WHERE topic_id IN ($placeholders)");
        $stmt->bind_param(str_repeat('i', count($topic_ids)), ...$topic_ids);
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
        
        // Delete section topics
        $stmt = $conn->prepare("DELETE FROM section_topics WHERE section_id = ?");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete the section
    $stmt = $conn->prepare("DELETE FROM course_sections WHERE section_id = ?");
    $stmt->bind_param("i", $section_id);
    $stmt->execute();
    $stmt->close();
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $section_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Reorder remaining sections
    $stmt = $conn->prepare("
        SET @pos = 0;
        UPDATE course_sections 
        SET position = (@pos := @pos + 1) 
        WHERE course_id = ? 
        ORDER BY position;
    ");
    $stmt->bind_param("i", $section_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Section deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback in case of error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>