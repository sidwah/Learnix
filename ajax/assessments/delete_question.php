<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required parameters
if (!isset($_POST['quiz_id'])) {
    echo json_encode(['success' => false, 'message' => 'Missing quiz ID']);
    exit;
}

$quiz_id = intval($_POST['quiz_id']);

// Verify that the quiz belongs to a course owned by the current instructor
$stmt = $conn->prepare("
    SELECT sq.quiz_id, c.course_id, c.instructor_id
    FROM section_quizzes sq
    JOIN course_sections cs ON sq.section_id = cs.section_id
    JOIN courses c ON cs.course_id = c.course_id
    WHERE sq.quiz_id = ?
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$quiz_data = $result->fetch_assoc();
$stmt->close();

if (!$quiz_data || $quiz_data['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Quiz not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete quiz questions and answers
    $stmt = $conn->prepare("
        SELECT question_id FROM quiz_questions
        WHERE quiz_id = ?
    ");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $questions_result = $stmt->get_result();
    $question_ids = [];
    
    while ($question = $questions_result->fetch_assoc()) {
        $question_ids[] = $question['question_id'];
    }
    $stmt->close();
    
    // If there are questions, delete related answers
    if (!empty($question_ids)) {
        $placeholders = implode(',', array_fill(0, count($question_ids), '?'));
        $types = str_repeat('i', count($question_ids));
        
        $stmt = $conn->prepare("
            DELETE FROM quiz_answers
            WHERE question_id IN ($placeholders)
        ");
        $stmt->bind_param($types, ...$question_ids);
        $stmt->execute();
        $stmt->close();
        
        // Delete quiz questions
        $stmt = $conn->prepare("
            DELETE FROM quiz_questions
            WHERE quiz_id = ?
        ");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Delete student quiz attempts and responses
    $stmt = $conn->prepare("
        SELECT attempt_id FROM student_quiz_attempts
        WHERE quiz_id = ?
    ");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $attempts_result = $stmt->get_result();
    $attempt_ids = [];
    
    while ($attempt = $attempts_result->fetch_assoc()) {
        $attempt_ids[] = $attempt['attempt_id'];
    }
    $stmt->close();
    
    // If there are attempts, delete related responses
    if (!empty($attempt_ids)) {
        $placeholders = implode(',', array_fill(0, count($attempt_ids), '?'));
        $types = str_repeat('i', count($attempt_ids));
        
        // Delete student answers
        $stmt = $conn->prepare("
            SELECT response_id FROM student_question_responses
            WHERE attempt_id IN ($placeholders)
        ");
        $stmt->bind_param($types, ...$attempt_ids);
        $stmt->execute();
        $responses_result = $stmt->get_result();
        $response_ids = [];
        
        while ($response = $responses_result->fetch_assoc()) {
            $response_ids[] = $response['response_id'];
        }
        $stmt->close();
        
        // Delete answer selections if there are responses
        if (!empty($response_ids)) {
            $resp_placeholders = implode(',', array_fill(0, count($response_ids), '?'));
            $resp_types = str_repeat('i', count($response_ids));
            
            $stmt = $conn->prepare("
                DELETE FROM student_answer_selections
                WHERE response_id IN ($resp_placeholders)
            ");
            $stmt->bind_param($resp_types, ...$response_ids);
            $stmt->execute();
            $stmt->close();
            
            // Delete student_question_responses
            $stmt = $conn->prepare("
                DELETE FROM student_question_responses
                WHERE attempt_id IN ($placeholders)
            ");
            $stmt->bind_param($types, ...$attempt_ids);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete quiz attempts
        $stmt = $conn->prepare("
            DELETE FROM student_quiz_attempts
            WHERE quiz_id = ?
        ");
        $stmt->bind_param("i", $quiz_id);
        $stmt->execute();
        $stmt->close();
    }
    
    // Finally delete the quiz
    $stmt = $conn->prepare("
        DELETE FROM section_quizzes
        WHERE quiz_id = ?
    ");
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $stmt->close();
    
    // Update course last modified timestamp
    $stmt = $conn->prepare("UPDATE courses SET updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $quiz_data['course_id']);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Quiz deleted successfully'
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>