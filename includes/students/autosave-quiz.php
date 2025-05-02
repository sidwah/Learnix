<?php
// includes/students/autosave-quiz.php

header('Content-Type: application/json');
require_once '../../backend/config.php';

$response = ['success' => false, 'error' => ''];

// Use the user_id passed in the POST request
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id === 0) {
    $response['error'] = 'User ID not provided or invalid.';
    echo json_encode($response);
    exit();
}

$attempt_id = isset($_POST['attempt_id']) ? (int)$_POST['attempt_id'] : 0;
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$answered_questions = isset($_POST['answered_questions']) ? $_POST['answered_questions'] : '{}';

if (!$attempt_id || !$quiz_id) {
    $response['error'] = 'Missing required parameters.';
    echo json_encode($response);
    exit();
}

// Verify the attempt is active (not completed)
$check_query = "SELECT is_completed FROM student_quiz_attempts WHERE attempt_id = ? AND user_id = ? AND quiz_id = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("iii", $attempt_id, $user_id, $quiz_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $response['error'] = 'Attempt not found.';
    echo json_encode($response);
    $check_stmt->close();
    exit();
}

$row = $check_result->fetch_assoc();
if ($row['is_completed'] == 1) {
    $response['error'] = 'Cannot autosave: Attempt is already completed.';
    echo json_encode($response);
    $check_stmt->close();
    exit();
}
$check_stmt->close();

// Update the answered_questions field
$query = "UPDATE student_quiz_attempts 
          SET answered_questions = ?, answered_count = ? 
          WHERE attempt_id = ? AND user_id = ? AND quiz_id = ? AND is_completed = 0";
$answered_questions_array = json_decode($answered_questions, true);
$answered_count = count($answered_questions_array);

$stmt = $conn->prepare($query);
$stmt->bind_param("siiii", $answered_questions, $answered_count, $attempt_id, $user_id, $quiz_id);

if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['error'] = 'Failed to autosave answers.';
}

$stmt->close();
echo json_encode($response);
?>