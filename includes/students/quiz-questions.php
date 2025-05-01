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
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo '<div class="alert alert-info">No questions found.</div>';
    exit;
}

while ($question = $result->fetch_assoc()) {
    echo '<div class="quiz-question" data-question-id="' . $question['question_id'] . '">';
    echo '<h5>' . htmlspecialchars($question['question_text']) . '</h5>';

    // Fetch answers
    $answers_query = "SELECT answer_id, answer_text FROM quiz_answers WHERE question_id = ?";
    $stmt2 = $conn->prepare($answers_query);
    $stmt2->bind_param("i", $question['question_id']);
    $stmt2->execute();
    $answers = $stmt2->get_result();

    while ($answer = $answers->fetch_assoc()) {
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="radio" name="question_' . $question['question_id'] . '" id="answer_' . $answer['answer_id'] . '" value="' . $answer['answer_id'] . '">';
        echo '<label class="form-check-label" for="answer_' . $answer['answer_id'] . '">' . htmlspecialchars($answer['answer_text']) . '</label>';
        echo '</div>';
    }

    echo '</div>';
}
?>