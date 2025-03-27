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
if (!isset($_POST['section_id']) || !isset($_POST['quiz_title']) || !isset($_POST['pass_mark'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$section_id = intval($_POST['section_id']);
$topic_id = isset($_POST['topic_id']) ? intval($_POST['topic_id']) : null;
$quiz_title = $_POST['quiz_title'];
$pass_mark = intval($_POST['pass_mark']);
$time_limit = isset($_POST['time_limit']) && !empty($_POST['time_limit']) ? intval($_POST['time_limit']) : null;
$attempts_allowed = isset($_POST['attempts_allowed']) ? intval($_POST['attempts_allowed']) : 1;
$randomize_questions = isset($_POST['randomize_questions']) ? 1 : 0;
$show_correct_answers = isset($_POST['show_correct_answers']) ? 1 : 0;
$shuffle_answers = isset($_POST['shuffle_answers']) ? 1 : 0;
$is_required = isset($_POST['is_required']) ? 1 : 0;
$instruction = isset($_POST['instruction']) ? $_POST['instruction'] : null;

// Verify that the section belongs to the instructor's course
$stmt = $conn->prepare("
    SELECT s.section_id 
    FROM course_sections s 
    JOIN courses c ON s.course_id = c.course_id 
    WHERE s.section_id = ? AND c.instructor_id = ?
");
$stmt->bind_param("ii", $section_id, $instructor_id);
$stmt->execute();
$section_result = $stmt->get_result();

if ($section_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Section not found or not authorized']);
    exit;
}

// Check if topic exists if provided
if ($topic_id !== null) {
    $stmt = $conn->prepare("SELECT topic_id FROM section_topics WHERE topic_id = ? AND section_id = ?");
    $stmt->bind_param("ii", $topic_id, $section_id);
    $stmt->execute();
    $topic_result = $stmt->get_result();
    
    if ($topic_result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Topic not found or not associated with this section']);
        exit;
    }
}

if ($quiz_id > 0) {
    // Update existing quiz
    $stmt = $conn->prepare("
        UPDATE section_quizzes 
        SET quiz_title = ?, description = ?, randomize_questions = ?, pass_mark = ?, time_limit = ?,
            attempts_allowed = ?, show_correct_answers = ?, shuffle_answers = ?, is_required = ?, instruction = ?, updated_at = NOW()
        WHERE quiz_id = ? AND section_id = ?
    ");
    $stmt->bind_param("ssiiiiiiisii", $quiz_title, $description, $randomize_questions, $pass_mark, $time_limit, 
                   $attempts_allowed, $show_correct_answers, $shuffle_answers, $is_required, $instruction, $quiz_id, $section_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Quiz updated successfully', 'quiz_id' => $quiz_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update quiz: ' . $conn->error]);
    }
} else {
    // Insert new quiz
    $stmt = $conn->prepare("
        INSERT INTO section_quizzes 
        (section_id, topic_id, quiz_title, description, randomize_questions, pass_mark, time_limit, attempts_allowed, 
         show_correct_answers, shuffle_answers, is_required, instruction) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iissiiiiiiis", $section_id, $topic_id, $quiz_title, $description, $randomize_questions, $pass_mark, 
                   $time_limit, $attempts_allowed, $show_correct_answers, $shuffle_answers, $is_required, $instruction);
    
    if ($stmt->execute()) {
        $quiz_id = $conn->insert_id;
        echo json_encode(['status' => 'success', 'message' => 'Quiz created successfully', 'quiz_id' => $quiz_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to create quiz: ' . $conn->error]);
    }
}

$stmt->close();
$conn->close();
?>