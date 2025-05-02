<?php
// includes/students/quiz-handler.php

// Start session and include config (absolute path from project root)
require_once dirname(__DIR__, 2) . '/backend/session_start.php';
require_once dirname(__DIR__, 2) . '/backend/config.php';

// Rest of the file remains the same...
// Get required parameters
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : (isset($_GET['quiz_id']) ? (int)$_GET['quiz_id'] : 0);
$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$course_id = isset($_POST['course_id']) ? (int)$_POST['course_id'] : (isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0);

if (!$quiz_id || !$user_id || !$course_id) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Required parameters are missing.']);
    exit;
}

// Fetch quiz details
$quiz_query = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    $quiz = ['quiz_title' => 'Unknown Quiz', 'instruction' => '', 'time_limit' => 0, 'pass_mark' => 0, 'attempts_allowed' => 0];
} else {
    $quiz = $quiz_result->fetch_assoc();
}
$stmt->close();

// Fetch total number of attempts for current attempts
$attempt_count_query = "SELECT COUNT(*) as attempt_count 
                       FROM student_quiz_attempts 
                       WHERE user_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($attempt_count_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$attempt_count_result = $stmt->get_result();
$current_attempts = $attempt_count_result->fetch_assoc()['attempt_count'] ?? 0;
$stmt->close();

// Fetch previous attempts (last 5 for display) with answered questions count
$attempts_query = "SELECT 
    sqa.attempt_id, 
    sqa.attempt_number, 
    sqa.score, 
    sqa.passed, 
    sqa.time_spent, 
    sqa.start_time,
    (SELECT COUNT(*) 
     FROM student_question_responses sqr 
     WHERE sqr.attempt_id = sqa.attempt_id 
     AND sqr.answer_text != 'Not answered') as answered_questions,
    (SELECT COUNT(*) 
     FROM quiz_questions qq 
     WHERE qq.quiz_id = sqa.quiz_id) as total_questions
FROM student_quiz_attempts sqa
WHERE sqa.user_id = ? AND sqa.quiz_id = ?
ORDER BY sqa.attempt_number DESC 
LIMIT 5";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$attempts_result = $stmt->get_result();
$attempts = $attempts_result->fetch_all(MYSQLI_ASSOC) ?? [];
$stmt->close();

// Debug: Log the attempts array to see the answered_questions count
error_log("Attempts Data in quiz-handler.php: " . print_r($attempts, true));


// Set max attempts from quiz data
$max_attempts = isset($quiz['attempts_allowed']) ? (int)$quiz['attempts_allowed'] : 0;

// Fetch total question count
$question_count_query = "SELECT COUNT(*) as question_count 
                        FROM quiz_questions 
                        WHERE quiz_id = ?";
$stmt = $conn->prepare($question_count_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$question_count_result = $stmt->get_result();
$question_count = $question_count_result->fetch_assoc()['question_count'] ?? 0;
$stmt->close();

// Check for active (incomplete) attempt
$active_attempt_query = "SELECT attempt_id, start_time, time_spent 
                        FROM student_quiz_attempts 
                        WHERE user_id = ? AND quiz_id = ? AND is_completed = 0 
                        ORDER BY start_time DESC LIMIT 1";
$stmt = $conn->prepare($active_attempt_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$active_attempt_result = $stmt->get_result();
$active_attempt = $active_attempt_result->num_rows > 0 ? $active_attempt_result->fetch_assoc() : null;
$stmt->close();

// Calculate remaining time for active attempt
$remaining_time = null;
if ($active_attempt && $quiz['time_limit'] > 0) {
    $start_time = strtotime($active_attempt['start_time']);
    $time_limit_seconds = $quiz['time_limit'] * 60;
    $elapsed_time = time() - $start_time;
    $remaining_time = $time_limit_seconds - $elapsed_time;
    
    if ($remaining_time <= 0) {
        $active_attempt = null; // Clear active attempt
    }
}

// Handle quiz start action (if POST request)
// Handle quiz start action (if POST request)
if (isset($_POST['action']) && $_POST['action'] === 'start_quiz') {
    header('Content-Type: application/json');
    
    // Debug: Log the incoming request
    error_log("Start Quiz Request: quiz_id=$quiz_id, user_id=$user_id, course_id=$course_id");

    if ($current_attempts >= $max_attempts) {
        echo json_encode(['success' => false, 'error' => 'Maximum attempts reached']);
        exit;
    }

    if (!$active_attempt) {
        // Step 1: Fetch the next attempt number
        $query = "SELECT COALESCE(MAX(attempt_number), 0) + 1 as next_attempt_number 
                  FROM student_quiz_attempts 
                  WHERE user_id = ? AND quiz_id = ?";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed for attempt number query: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Database error: Failed to prepare statement']);
            exit;
        }
        $stmt->bind_param("ii", $user_id, $quiz_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $next_attempt_number = $row['next_attempt_number'] ?? 1;
        $stmt->close();

        // Step 2: Insert the new attempt with the calculated attempt number
        $query = "INSERT INTO student_quiz_attempts (user_id, quiz_id, attempt_number, start_time) 
                  VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            error_log("Prepare failed for insert query: " . $conn->error);
            echo json_encode(['success' => false, 'error' => 'Database error: Failed to prepare statement']);
            exit;
        }
        $stmt->bind_param("iii", $user_id, $quiz_id, $next_attempt_number);
        $success = $stmt->execute();
        $new_attempt_id = $stmt->insert_id;
        $stmt->close();

        if ($success) {
            error_log("New attempt started: attempt_id=$new_attempt_id");
            echo json_encode(['success' => true, 'attempt_id' => $new_attempt_id]);
        } else {
            error_log("Insert failed: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Failed to start quiz']);
        }
    } else {
        echo json_encode(['success' => true, 'attempt_id' => $active_attempt['attempt_id']]);
    }
    exit;
}

?>