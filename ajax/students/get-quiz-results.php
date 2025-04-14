<?php
/**
 * Get Quiz Results AJAX Handler
 * 
 * Returns detailed results for a completed quiz attempt.
 * 
 * @package Learnix
 * @subpackage AJAX
 */

// Include necessary files
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login as a student.']);
    exit;
}

// Get request parameters
$attemptId = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if (!$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

try {
    // Verify attempt belongs to current user
    $stmt = $conn->prepare("
        SELECT a.*, q.quiz_title, q.pass_mark
        FROM student_quiz_attempts a
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE a.attempt_id = ? AND a.user_id = ?
    ");
    $stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$attempt) {
        throw new Exception('Quiz attempt not found or does not belong to you.');
    }

    // Get all questions for this quiz
    $stmt = $conn->prepare("
        SELECT qq.*
        FROM quiz_questions qq
        WHERE qq.quiz_id = ?
        ORDER BY qq.question_order
    ");
    $stmt->bind_param("i", $attempt['quiz_id']);
    $stmt->execute();
    $questionsResult = $stmt->get_result();
    $questions = [];
    
    while ($question = $questionsResult->fetch_assoc()) {
        $questions[] = $question;
    }
    $stmt->close();

    // Get student responses
    $responses = [];
    $correctCount = 0;
    $questionResults = [];
    
    foreach ($questions as $question) {
        // Get student response for this question
        $stmt = $conn->prepare("
            SELECT sqr.*
            FROM student_question_responses sqr
            WHERE sqr.attempt_id = ? AND sqr.question_id = ?
        ");
        $stmt->bind_param("ii", $attemptId, $question['question_id']);
        $stmt->execute();
        $responseResult = $stmt->get_result();
        $response = $responseResult->fetch_assoc();
        $stmt->close();

        // Get all possible answers for this question
        $stmt = $conn->prepare("
            SELECT qa.*
            FROM quiz_answers qa
            WHERE qa.question_id = ?
        ");
        $stmt->bind_param("i", $question['question_id']);
        $stmt->execute();
        $answersResult = $stmt->get_result();
        $answers = [];
        
        while ($answer = $answersResult->fetch_assoc()) {
            $answers[] = $answer;
        }
        $stmt->close();

        // Get user's selected answer for multiple choice
        $userAnswerId = null;
        if ($response && in_array($question['question_type'], ['Multiple Choice', 'True/False'])) {
            $stmt = $conn->prepare("
                SELECT sas.answer_id
                FROM student_answer_selections sas
                WHERE sas.response_id = ?
            ");
            $stmt->bind_param("i", $response['response_id']);
            $stmt->execute();
            $selectionResult = $stmt->get_result();
            if ($selectionResult->num_rows > 0) {
                $userAnswerId = $selectionResult->fetch_assoc()['answer_id'];
            }
            $stmt->close();
        }

        // Track correct answers
        if ($response && $response['is_correct']) {
            $correctCount++;
        }

        // Build question result data
        $questionResults[] = [
            'id' => $question['question_id'],
            'question_text' => $question['question_text'],
            'question_type' => $question['question_type'],
            'is_correct' => $response ? (bool)$response['is_correct'] : false,
            'points_awarded' => $response ? $response['points_awarded'] : 0,
            'max_points' => $question['points'],
            'user_answer_id' => $userAnswerId,
            'user_answer' => $response ? $response['answer_text'] : null,
            'explanation' => $question['explanation'],
            'answers' => array_map(function($answer) {
                return [
                    'id' => $answer['answer_id'],
                    'answer_text' => $answer['answer_text'],
                    'is_correct' => (bool)$answer['is_correct']
                ];
            }, $answers)
        ];
    }

    // Build the results data
    $results = [
        'quiz_id' => $attempt['quiz_id'],
        'quiz_title' => $attempt['quiz_title'],
        'score' => $attempt['score'],
        'score_percentage' => $attempt['score'],
        'passed' => (bool)$attempt['passed'],
        'pass_mark' => $attempt['pass_mark'],
        'total_questions' => count($questions),
        'correct_count' => $correctCount,
        'incorrect_count' => count($questions) - $correctCount,
        'time_taken' => $attempt['time_spent'],
        'questions' => $questionResults
    ];

    // Return success with results
    echo json_encode([
        'success' => true,
        'results' => $results
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}