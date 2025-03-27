<?php
require_once('../../backend/config.php');
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get quiz ID
$quizId = $_GET['quiz_id'] ?? null;

if (!$quizId) {
    echo json_encode(['success' => false, 'message' => 'Quiz ID is required']);
    exit;
}

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM section_quizzes WHERE quiz_id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}

$quiz = $result->fetch_assoc();
$stmt->close();

// Get section's course_id
$stmt = $conn->prepare("SELECT course_id FROM course_sections WHERE section_id = ?");
$stmt->bind_param("i", $quiz['section_id']);
$stmt->execute();
$result = $stmt->get_result();
$courseId = $result->fetch_assoc()['course_id'];
$stmt->close();

// Check if user owns this course
$stmt = $conn->prepare("
    SELECT c.course_id 
    FROM courses c 
    JOIN instructors i ON c.instructor_id = i.instructor_id 
    WHERE c.course_id = ? AND i.user_id = ?
");
$stmt->bind_param("ii", $courseId, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'You do not have permission to access this quiz']);
    exit;
}
$stmt->close();

// Return the quiz details
echo json_encode($quiz);
?>