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
if (!isset($_GET['question_id']) || empty($_GET['question_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Question ID is required']);
    exit;
}

$question_id = intval($_GET['question_id']);

// Check if question exists and belongs to instructor's course
$stmt = $conn->prepare("
    SELECT q.* 
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

$question = $result->fetch_assoc();

// Get answers for this question
$stmt = $conn->prepare("
    SELECT * 
    FROM quiz_answers 
    WHERE question_id = ? 
    ORDER BY answer_id ASC
");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$answers_result = $stmt->get_result();

$answers = [];
while ($answer = $answers_result->fetch_assoc()) {
    $answers[] = [
        'answer_id' => $answer['answer_id'],
        'answer_text' => $answer['answer_text'],
        'is_correct' => (bool)$answer['is_correct'],
        'explanation' => $answer['explanation']
    ];
}

// Return question data with answers
echo json_encode([
    'status' => 'success',
    'data' => [
        'question_id' => $question['question_id'],
        'quiz_id' => $question['quiz_id'],
        'question_text' => $question['question_text'],
        'question_type' => $question['question_type'],
        'points' => $question['points'],
        'explanation' => $question['explanation'],
        'difficulty' => $question['difficulty'],
        'question_order' => $question['question_order'],
        'answers' => $answers
    ]
]);

$stmt->close();
$conn->close();
?>