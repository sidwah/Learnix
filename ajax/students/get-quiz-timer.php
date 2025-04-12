<?php
/**
 * Get Quiz Timer AJAX Handler
 * 
 * Returns the current remaining time for a timed quiz.
 * Helps synchronize client-side timer with server-side time to prevent timer manipulation.
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

// Verify AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get request parameters
$attemptId = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if (!$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

try {
    // Get quiz attempt and end time
    $stmt = $conn->prepare("
        SELECT a.end_time, a.is_completed, a.start_time, q.time_limit
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
        throw new Exception('Attempt not found.');
    }

    // If the quiz is not timed, return null
    if (!$attempt['time_limit']) {
        echo json_encode(['success' => true, 'remaining_time' => null]);
        exit;
    }

    // If the quiz is already completed, return 0
    if ($attempt['is_completed']) {
        echo json_encode(['success' => true, 'remaining_time' => 0]);
        exit;
    }

    // Calculate remaining time
    $endTime = new DateTime($attempt['end_time']);
    $now = new DateTime();
    $remainingTime = $endTime->getTimestamp() - $now->getTimestamp();

    // If time is up, auto-submit the quiz
    if ($remainingTime <= 0) {
        // Include submit quiz script
        require_once 'submit-quiz.php';
        exit;
    }

    // Return remaining time
    echo json_encode([
        'success' => true,
        'remaining_time' => $remainingTime,
        'server_time' => $now->getTimestamp() * 1000, // Milliseconds for JS
        'end_time' => $endTime->getTimestamp() * 1000 // Milliseconds for JS
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>