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
if (!isset($_GET['quiz_id']) || empty($_GET['quiz_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Quiz ID is required']);
    exit;
}

$quiz_id = intval($_GET['quiz_id']);

// Check if quiz exists and belongs to instructor's course
$stmt = $conn->prepare("
    SELECT q.* 
    FROM section_quizzes q 
    JOIN course_sections s ON q.section_id = s.section_id 
    JOIN courses c ON s.course_id = c.course_id 
    WHERE q.quiz_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $quiz_id, $instructor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Quiz not found or not authorized']);
    exit;
}

$quiz = $result->fetch_assoc();

// Return quiz data
echo json_encode([
    'status' => 'success',
    'data' => [
        'quiz_id' => $quiz['quiz_id'],
        'section_id' => $quiz['section_id'],
        'topic_id' => $quiz['topic_id'],
        'quiz_title' => $quiz['quiz_title'],
        'description' => $quiz['description'],
        'randomize_questions' => $quiz['randomize_questions'],
        'pass_mark' => $quiz['pass_mark'],
        'time_limit' => $quiz['time_limit'],
        'attempts_allowed' => $quiz['attempts_allowed'],
        'show_correct_answers' => $quiz['show_correct_answers'],
        'shuffle_answers' => $quiz['shuffle_answers'],
        'is_required' => $quiz['is_required'],
        'instruction' => $quiz['instruction']
    ]
]);

$stmt->close();
$conn->close();
?>