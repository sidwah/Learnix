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

try {
    // Get session data
    $stmt = $conn->prepare("
        SELECT s.*, a.quiz_id, a.start_time, a.end_time, a.is_completed, q.time_limit
        FROM quiz_sessions s
        JOIN student_quiz_attempts a ON s.attempt_id = a.attempt_id
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE s.session_id = ? AND a.attempt_id = ? AND a.user_id = ? AND a.is_completed = 0 AND s.is_active = 1
    ");
    $stmt->bind_param("iii", $sessionId, $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
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

    // Parse session data
    $sessionData = json_decode($session['session_data'], true) ?: [
        'question_order' => [],
        'current_question' => 0,
        'answers' => []
    ];

    // Calculate remaining time for timed quizzes
    $remainingTime = null;
    if ($session['end_time']) {
        $endTime = new DateTime($session['end_time']);
        $now = new DateTime();
        
        if ($now > $endTime) {
            // Time is up - submit quiz automatically
            require_once 'submit-quiz.php';
            exit;
        }
        
        $remainingTime = $endTime->getTimestamp() - $now->getTimestamp();
    }

    // Get all questions
    $questions = [];
    $stmt = $conn->prepare("
        SELECT q.question_id, q.question_text, q.question_type, q.points
        FROM quiz_questions q
        WHERE q.quiz_id = ?
        ORDER BY FIELD(q.question_id, " . implode(',', $sessionData['question_order']) . ")
    ");
    $stmt->bind_param("i", $session['quiz_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $answered = isset($sessionData['answers'][$row['question_id']]);
        $questions[] = [
            'question_id' => $row['question_id'],
            'question_type' => $row['question_type'],
            'points' => $row['points'],
            'answered' => $answered
        ];
    }
    $stmt->close();

    // Return success response
    echo json_encode([
        'success' => true,
        'attempt_id' => $attemptId,
        'session_id' => $sessionId,
        'questions' => $questions,
        'current_question' => $sessionData['current_question'],
        'answers' => $sessionData['answers'],
        'remaining_time' => $remainingTime,
        'end_time' => $session['end_time'],
        'message' => 'Quiz session resumed successfully.'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
