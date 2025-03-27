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
    SELECT q.quiz_id 
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

// Get all questions for this quiz
$stmt = $conn->prepare("
    SELECT * 
    FROM quiz_questions 
    WHERE quiz_id = ? 
    ORDER BY question_order ASC, question_id ASC
");
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

$questions = [];
while ($question = $questions_result->fetch_assoc()) {
    // Get answers for this question
    $stmt = $conn->prepare("
        SELECT * 
        FROM quiz_answers 
        WHERE question_id = ? 
        ORDER BY answer_id ASC
    ");
    $stmt->bind_param("i", $question['question_id']);
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
    
    $questions[] = [
        'question_id' => $question['question_id'],
        'question_text' => $question['question_text'],
        'question_type' => $question['question_type'],
        'points' => $question['points'],
        'explanation' => $question['explanation'],
        'difficulty' => $question['difficulty'],
        'question_order' => $question['question_order'],
        'answers' => $answers
    ];
}

// Return questions data
echo json_encode([
    'status' => 'success',
    'data' => $questions
]);

$stmt->close();
$conn->close();
?>