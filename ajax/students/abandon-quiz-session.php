<?php
/**
 * Abandon Quiz Session AJAX Handler
 * 
 * Marks an active quiz session as abandoned, allowing the student to start fresh.
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$sessionId = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
$attemptId = isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : 0;

if (!$sessionId || !$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify session belongs to current user
    $stmt = $conn->prepare("
        SELECT a.quiz_id
        FROM quiz_sessions s
        JOIN student_quiz_attempts a ON s.attempt_id = a.attempt_id
        WHERE s.session_id = ? AND a.attempt_id = ? AND a.user_id = ?
    ");
    $stmt->bind_param("iii", $sessionId, $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$attempt) {
        throw new Exception('Session not found or you do not have permission to abandon it.');
    }

    // Mark session as inactive
    $stmt = $conn->prepare("
        UPDATE quiz_sessions
        SET is_active = 0
        WHERE session_id = ?
    ");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $stmt->close();

    // Mark attempt as abandoned (special status)
    $stmt = $conn->prepare("
        UPDATE student_quiz_attempts
        SET is_completed = 1, end_time = NOW(), status = 'abandoned', score = 0
        WHERE attempt_id = ?
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Return success
    echo json_encode([
        'success' => true,
        'message' => 'Quiz attempt abandoned successfully.'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>