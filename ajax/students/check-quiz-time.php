<?php
/**
 * Check Quiz Time AJAX Handler
 * Checks if a quiz's time limit has expired
 */

// Include necessary files
require_once '../../backend/config.php';
require_once '../../backend/auth/session.php';

// Check if user is logged in
if (!isLoggedIn() || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login as a student.']);
    exit;
}

// Allow GET method for this endpoint
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get attempt ID
$attemptId = $_GET['attempt_id'] ?? 0;

if (!$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing attempt ID']);
    exit;
}

try {
    // Get quiz details and time information
    $stmt = $conn->prepare("
        SELECT a.start_time, q.time_limit
        FROM student_quiz_attempts a
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE a.attempt_id = ? AND a.user_id = ?
    ");
    $stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Quiz attempt not found']);
        exit;
    }
    
    $quiz = $result->fetch_assoc();
    $stmt->close();
    
    // If no time limit, never expired
    if (!$quiz['time_limit']) {
        echo json_encode(['success' => true, 'time_expired' => false]);
        exit;
    }
    
    // Calculate if time expired
    $startTime = new DateTime($quiz['start_time']);
    $currentTime = new DateTime();
    $elapsedSeconds = $currentTime->getTimestamp() - $startTime->getTimestamp();
    $timeLimit = $quiz['time_limit'] * 60; // Convert minutes to seconds
    
    $timeExpired = $elapsedSeconds >= $timeLimit;
    
    echo json_encode([
        'success' => true,
        'time_expired' => $timeExpired,
        'elapsed_seconds' => $elapsedSeconds,
        'time_limit' => $timeLimit,
        'remaining_seconds' => max(0, $timeLimit - $elapsedSeconds)
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>