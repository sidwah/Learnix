<?php
/**
 * Resume Quiz Session AJAX Handler
 * 
 * Restores an active quiz session after page refresh or return.
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

// Allow both GET and POST methods
$isGet = ($_SERVER['REQUEST_METHOD'] === 'GET');
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

if (!$isGet && !$isPost) {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get request data (from either GET or POST)
$sessionId = $isGet ? (isset($_GET['session_id']) ? intval($_GET['session_id']) : 0) : 
                     (isset($_POST['session_id']) ? intval($_POST['session_id']) : 0);
                     
$attemptId = $isGet ? (isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0) : 
                     (isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : 0);

if (!$sessionId || !$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

try {
    // Get session data
    $stmt = $conn->prepare("
        SELECT s.*, a.quiz_id, a.start_time, a.end_time, a.is_completed, q.time_limit, q.quiz_title
        FROM quiz_sessions s
        JOIN student_quiz_attempts a ON s.attempt_id = a.attempt_id
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE s.session_id = ? AND a.attempt_id = ? AND a.user_id = ? AND a.is_completed = 0 AND s.is_active = 1
    ");
    $stmt->bind_param("iii", $sessionId, $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $session = $result->fetch_assoc();
    $stmt->close();

    if (!$session) {
        throw new Exception('Session not found or has expired.');
    }

    // Validate client information (basic security check)
    $clientIp = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if ($session['client_ip'] !== $clientIp) {
        // Just log the IP change but continue - could be mobile/WiFi switching
        $stmt = $conn->prepare("
            UPDATE quiz_sessions SET client_ip = ? WHERE session_id = ?
        ");
        $stmt->bind_param("si", $clientIp, $sessionId);
        $stmt->execute();
        $stmt->close();
    }

    // Update session activity
    $stmt = $conn->prepare("
        UPDATE quiz_sessions SET last_activity = NOW() WHERE session_id = ?
    ");
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $stmt->close();

    // Check if time has expired for timed quizzes
    if ($session['time_limit']) {
        $startTime = new DateTime($session['start_time']);
        $currentTime = new DateTime();
        $elapsedSeconds = $currentTime->getTimestamp() - $startTime->getTimestamp();
        $totalSeconds = $session['time_limit'] * 60;
        $remainingSeconds = $totalSeconds - $elapsedSeconds;
        
        if ($remainingSeconds <= 0) {
            // Time has expired
            echo json_encode([
                'success' => false, 
                'time_expired' => true,
                'attempt_id' => $attemptId,
                'message' => 'Quiz time has expired.'
            ]);
            exit;
        }
        
        // Calculate end time for the timer
        $endTimeObj = clone $startTime;
        $endTimeObj->add(new DateInterval('PT' . $totalSeconds . 'S'));
        $endTime = $endTimeObj->format('Y-m-d H:i:s');
    } else {
        $endTime = null;
    }

    // Parse session data if available
    $sessionData = isset($session['session_data']) ? json_decode($session['session_data'], true) : null;
    if (!$sessionData) {
        $sessionData = [
            'question_order' => [],
            'current_question' => 0,
            'answers' => []
        ];
    }

    // Return success response with minimal data for initial resume
    echo json_encode([
        'success' => true,
        'attempt_id' => $attemptId,
        'quiz_id' => $session['quiz_id'],
        'quiz_title' => $session['quiz_title'],
        'session_id' => $sessionId,
        'session_token' => $session['session_token'],
        'time_limit' => $session['time_limit'],
        'end_time' => $endTime,
        'message' => 'Quiz session resumed successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>