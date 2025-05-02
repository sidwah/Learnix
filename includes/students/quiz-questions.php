<?php
require_once '../../backend/config.php';
session_start();

if (!isset($_GET['quiz_id']) || !is_numeric($_GET['quiz_id'])) {
    echo '<div class="alert alert-danger">Invalid quiz ID.</div>';
    exit;
}

$quiz_id = intval($_GET['quiz_id']);
$user_id = $_SESSION['user_id'] ?? 0;

// Query questions
$questions_query = "SELECT question_id, question_text FROM quiz_questions WHERE quiz_id = ?";
$questions_stmt = $conn->prepare($questions_query);
if (!$questions_stmt) {
    echo '<div class="alert alert-danger">Database error: Failed to prepare query for questions.</div>';
    exit;
}
$questions_stmt->bind_param("i", $quiz_id);
$questions_stmt->execute();
$result = $questions_stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">No questions found.</div>';
    $questions_stmt->close();
    exit;
}

// Fetch in-progress answers from student_quiz_attempts if resuming
$in_progress_answers = [];
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;
if ($attempt_id && $user_id) {
    $attempt_query = "SELECT answered_questions 
                     FROM student_quiz_attempts 
                     WHERE attempt_id = ? AND user_id = ? AND quiz_id = ? AND is_completed = 0";
    $attempt_stmt = $conn->prepare($attempt_query);
    if (!$attempt_stmt) {
        echo '<div class="alert alert-danger">Database error: Failed to prepare query for attempt.</div>';
        $questions_stmt->close();
        exit;
    }
    $attempt_stmt->bind_param("iii", $attempt_id, $user_id, $quiz_id);
    $attempt_stmt->execute();
    $result_check = $attempt_stmt->get_result();
    
    if ($result_check->num_rows > 0) {
        $row = $result_check->fetch_assoc();
        $in_progress_answers = json_decode($row['answered_questions'] ?? '{}', true) ?? [];
    }
    $attempt_stmt->close();
}

while ($question = $result->fetch_assoc()) {
    echo '<div class="quiz-question" data-question-id="' . $question['question_id'] . '">';
    echo '<h5>' . htmlspecialchars($question['question_text']) . '</h5>';

    // Fetch answers
    $answers_query = "SELECT answer_id, answer_text FROM quiz_answers WHERE question_id = ?";
    $answers_stmt = $conn->prepare($answers_query);
    if (!$answers_stmt) {
        echo '<div class="alert alert-danger">Database error: Failed to prepare query for answers.</div>';
        $questions_stmt->close();
        exit;
    }
    $answers_stmt->bind_param("i", $question['question_id']);
    $answers_stmt->execute();
    $answers = $answers_stmt->get_result();

    while ($answer = $answers->fetch_assoc()) {
        $is_checked = isset($in_progress_answers[$question['question_id']]) && $in_progress_answers[$question['question_id']] == $answer['answer_id'] ? 'checked' : '';
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="radio" name="question_' . $question['question_id'] . '" id="answer_' . $answer['answer_id'] . '" value="' . $answer['answer_id'] . '" ' . $is_checked . '>';
        echo '<label class="form-check-label" for="answer_' . $answer['answer_id'] . '">' . htmlspecialchars($answer['answer_text']) . '</label>';
        echo '</div>';
    }

    echo '</div>';
    $answers_stmt->close();
}
$questions_stmt->close();
?>