<?php
// Include necessary files
require_once '../../backend/config.php';
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id from user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Instructor not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];

// Validate input data
if (!isset($_POST['question_id']) || empty($_POST['question_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
    exit;
}

$question_id = intval($_POST['question_id']);

// Check if question exists and belongs to instructor's course
$stmt = $conn->prepare("
    SELECT q.question_id 
    FROM quiz_questions q 
    JOIN section_quizzes sq ON q.quiz_id = sq.quiz_id 
    JOIN course_sections s ON sq.section_id = s.section_id 
    JOIN courses c ON s.course_id = c.course_id 
    WHERE q.question_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $question_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Question not found or not authorized']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete answers first (due to foreign key constraints)
    $stmt = $conn->prepare("DELETE FROM quiz_answers WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    
    // Delete the question
    $stmt = $conn->prepare("DELETE FROM quiz_questions WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Question deleted successfully']);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete question: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>