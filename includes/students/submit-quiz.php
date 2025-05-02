<?php
// includes/students/submit-quiz.php

require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Suppress warnings to ensure clean JSON output
error_reporting(E_ERROR | E_PARSE);

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;
$answers = isset($_POST['answers']) ? json_decode(urldecode($_POST['answers']), true) : [];
$is_auto_submit = isset($_POST['is_auto_submit']) && $_POST['is_auto_submit'] === 'true';
$forfeit = isset($_POST['forfeit']) && $_POST['forfeit'] === 'true';
$attempt_id = isset($_POST['attempt_id']) ? (int)$_POST['attempt_id'] : 0;

if (!$user_id || !$quiz_id) {
    echo json_encode(['error' => 'Invalid user or quiz ID']);
    exit;
}

try {
    // Fetch quiz details
    $query = "SELECT pass_mark FROM section_quizzes WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $quiz = $result->fetch_assoc();
    $stmt->close();

    if (!$quiz) {
        echo json_encode(['error' => 'Quiz not found']);
        exit;
    }

    // Get active attempt (use attempt_id if provided, e.g., during forfeit)
    $query = "SELECT attempt_id, start_time FROM student_quiz_attempts 
              WHERE user_id = ? AND quiz_id = ? AND is_completed = 0 
              ORDER BY start_time DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $user_id, $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $active_attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$active_attempt && !$attempt_id) {
        echo json_encode(['error' => 'No active attempt found']);
        exit;
    }

    // Use provided attempt_id if available (e.g., during forfeit)
    $attempt_id = $attempt_id ?: $active_attempt['attempt_id'];

    // Fetch all questions for the quiz
    $query = "SELECT question_id, points FROM quiz_questions WHERE quiz_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $quiz_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $questions = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $total_points = 0;
    $earned_points = 0;

    // Process each question, even if no answer was submitted
    foreach ($questions as $question) {
        $question_id = (int)$question['question_id'];
        $points = (int)$question['points'];
        $total_points += $points;

        $student_answer_id = isset($answers[$question_id]) ? (int)$answers[$question_id] : 0;
        $is_correct = 0;
        $points_awarded = 0;
        $answer_text = 'Not answered';

        if ($student_answer_id > 0) {
            // Get correct answers
            $query = "SELECT answer_id, is_correct FROM quiz_answers WHERE question_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('i', $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $correct_answers = [];
            while ($row = $result->fetch_assoc()) {
                if ($row['is_correct']) {
                    $correct_answers[] = $row['answer_id'];
                }
            }
            $stmt->close();

            // Check if the answer is correct
            $is_correct = in_array($student_answer_id, $correct_answers) ? 1 : 0;
            $points_awarded = $is_correct ? $points : 0;
            $earned_points += $points_awarded;

            // Fetch the answer text for the student's selected answer
            $query = "SELECT answer_text FROM quiz_answers WHERE answer_id = ? AND question_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $student_answer_id, $question_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $answer_row = $result->fetch_assoc();
            $answer_text = $answer_row['answer_text'] ?? 'Not answered';
            $stmt->close();
        }

        // Save student response
        $query = "INSERT INTO student_question_responses (attempt_id, question_id, answer_text, is_correct, points_awarded) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iisid', $attempt_id, $question_id, $answer_text, $is_correct, $points_awarded);
        $success = $stmt->execute();
        if (!$success) {
            error_log("Failed to insert student response for question $question_id: " . $conn->error);
        } else {
            error_log("Successfully inserted response for question $question_id: answer_text=$answer_text, is_correct=$is_correct");
        }
        $response_id = $conn->insert_id;
        $stmt->close();

        // Save answer selection (for multiple-choice)
        if ($student_answer_id > 0) {
            $query = "INSERT INTO student_answer_selections (response_id, answer_id) VALUES (?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('ii', $response_id, $student_answer_id);
            $success = $stmt->execute();
            if (!$success) {
                error_log("Failed to insert answer selection for response $response_id: " . $conn->error);
            }
            $stmt->close();
        }
    }

    // Calculate score and update attempt
    $score = $total_points > 0 ? ($earned_points / $total_points) * 100 : 0;
    $pass_mark = isset($quiz['pass_mark']) && $quiz['pass_mark'] > 0 ? (float)$quiz['pass_mark'] : 0;
    $passed = $score >= $pass_mark;
    $start_time = $active_attempt['start_time'] ?? null;
    $time_spent = $start_time ? (time() - strtotime($start_time)) : 0;

    // Update attempt with end_time and other details
    $query = "UPDATE student_quiz_attempts 
              SET score = ?, passed = ?, is_completed = 1, time_spent = ?, end_time = NOW() 
              WHERE attempt_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('diii', $score, $passed, $time_spent, $attempt_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        echo json_encode([
            'success' => true,
            'score' => number_format($score, 2),
            'passed' => $passed,
            'badges_earned' => [] // Placeholder for future badge logic
        ]);
    } else {
        echo json_encode(['error' => 'Failed to update attempt']);
    }
} catch (Exception $e) {
    error_log("Submit Quiz Error: " . $e->getMessage());
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}

$conn->close();
?>