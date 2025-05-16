<?php
//ajax/assessments/get_quiz.php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor ID from session
$instructor_id = $_SESSION['instructor_id'];

// Get quiz ID
$quizId = $_GET['quiz_id'] ?? null;
if (!$quizId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Quiz ID is required']);
    exit;
}

// Get quiz details
$stmt = $conn->prepare("SELECT * FROM section_quizzes WHERE quiz_id = ?");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}
$quiz = $result->fetch_assoc();
$stmt->close();

// Check if instructor has permission to access this quiz using course_instructors
$stmt = $conn->prepare("
    SELECT 
        sq.quiz_id
    FROM 
        section_quizzes sq
    JOIN 
        course_sections cs ON sq.section_id = cs.section_id
    JOIN 
        course_instructors ci ON cs.course_id = ci.course_id
    WHERE 
        sq.quiz_id = ? AND
        ci.instructor_id = ? AND
        ci.deleted_at IS NULL
");
$stmt->bind_param("ii", $quizId, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to access this quiz']);
    exit;
}
$stmt->close();

// Return the quiz details
header('Content-Type: application/json');
echo json_encode(['success' => true, 'data' => $quiz]);
?>