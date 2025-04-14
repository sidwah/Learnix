<?php
/**
 * Get Elapsed Time AJAX Handler
 * 
 * Returns the elapsed time for a quiz attempt in seconds.
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
    // Get the quiz attempt with start time
    $stmt = $conn->prepare("
        SELECT start_time
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
    
    $attempt = $result->fetch_assoc();
    $stmt->close();
    
    // Calculate elapsed time
    $startTime = new DateTime($attempt['start_time']);
    $currentTime = new DateTime();
    $elapsedSeconds = $currentTime->getTimestamp() - $startTime->getTimestamp();
    
    echo json_encode([
        'success' => true,
        'start_time' => $attempt['start_time'],
        'elapsed_seconds' => $elapsedSeconds
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}