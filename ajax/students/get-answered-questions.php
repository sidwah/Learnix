<?php
/**
 * Get Answered Questions AJAX Handler
 * 
 * Returns a list of question IDs that have been answered in a specific quiz attempt.
 * 
 * @package Learnix
 * @subpackage AJAX
 */

// Include necessary files
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login as a student.']);
    exit;
}

// Verify request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get attempt ID
$attemptId = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if (!$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing attempt ID.']);
    exit;
}

try {
    // Verify attempt belongs to current user
    $stmt = $conn->prepare("
        SELECT attempt_id
        FROM student_quiz_attempts
        WHERE attempt_id = ? AND user_id = ?
    ");
    $stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid attempt.']);
        exit;
    }
    $stmt->close();
    
    // Get all questions that have been answered in this attempt
    $stmt = $conn->prepare("
        SELECT DISTINCT question_id
        FROM student_question_responses
        WHERE attempt_id = ?
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $answeredQuestions = [];
    while ($row = $result->fetch_assoc()) {
        $answeredQuestions[] = $row['question_id'];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'answered_questions' => $answeredQuestions
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}