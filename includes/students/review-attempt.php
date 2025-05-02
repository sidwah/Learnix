<?php
// includes/students/review-attempt.php

require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

header('Content-Type: application/json');

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$user_id = $_SESSION['user_id'] ?? 0;

if (!$attempt_id || !$user_id) {
    echo json_encode(['error' => 'Invalid attempt ID or user']);
    exit;
}

try {
    // Verify the attempt belongs to the user
    $query = "SELECT quiz_id, is_completed FROM student_quiz_attempts WHERE attempt_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $attempt_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$attempt) {
        echo json_encode(['error' => 'Attempt not found or unauthorized']);
        exit;
    }

    $quiz_id = $attempt['quiz_id'];
    $is_completed = $attempt['is_completed'];

    // Fetch quiz settings to determine if correct answers should be shown
    $query = "SELECT show_correct_answers FROM section_quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    $stmt->close();

    // Interpret TINYINT(1) as boolean (0 = false, non-zero = true)
    $show_correct_answers = $is_completed && ($quiz['show_correct_answers'] ?? 0) != 0;

    // Fetch all questions for the quiz
    $query = "SELECT question_id, question_text FROM quiz_questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = [];
    while ($row = $result->fetch_assoc()) {
        $questions[$row['question_id']] = $row;
    }
    $stmt->close();

    if (empty($questions)) {
        echo json_encode(['error' => 'No questions found for this quiz']);
        exit;
    }

    // Fetch responses
    $query = "SELECT 
        sqr.question_id,
        sqr.answer_text AS student_answer,
        sqr.is_correct
    FROM student_question_responses sqr
    WHERE sqr.attempt_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $attempt_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $responses = [];
    while ($row = $result->fetch_assoc()) {
        $responses[$row['question_id']] = $row;
    }
    $stmt->close();

    // Build response data for all questions
    $response_data = [];
    foreach ($questions as $question_id => $question) {
        $response = $responses[$question_id] ?? [
            'student_answer' => 'Not answered',
            'is_correct' => 0
        ];

        // Fetch correct answers if needed
        $correct_answers = [];
        if ($show_correct_answers) {
            $query = "SELECT answer_text FROM quiz_answers WHERE question_id = ? AND is_correct = 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $correct_answers[] = $row['answer_text'];
            }
            $stmt->close();
        }

        $response_data[] = [
            'question_id' => $question_id,
            'question_text' => $question['question_text'],
            'student_answer' => $response['student_answer'],
            'is_correct' => (int)$response['is_correct'],
            'correct_answers' => $correct_answers
        ];
    }

    error_log("Review Attempt Responses: " . print_r($response_data, true)); // Debug log

    echo json_encode([
        'success' => true,
        'responses' => $response_data,
        'show_correct_answers' => $show_correct_answers
    ]);
} catch (Exception $e) {
    error_log("Review Attempt Error: " . $e->getMessage());
    echo json_encode(['error' => 'Failed to load attempt details: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>