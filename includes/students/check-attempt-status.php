<?php
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user_id'] ?? 0;
$quiz_id = isset($_POST['quiz_id']) ? (int)$_POST['quiz_id'] : 0;

if (!$user_id || !$quiz_id) {
    echo json_encode(['error' => 'Invalid user or quiz ID']);
    exit;
}

try {
    // Get total attempts allowed from section_quizzes
    $query = "SELECT attempts_allowed FROM section_quizzes WHERE quiz_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $quiz_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $quiz = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$quiz) {
        throw new Exception('Quiz not found');
    }

    $max_attempts = (int)$quiz['attempts_allowed'];

    // Count completed attempts for this user and quiz
    $query = "SELECT COUNT(*) as completed_attempts FROM student_quiz_attempts 
              WHERE user_id = ? AND quiz_id = ? AND is_completed = 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $quiz_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $attempts_data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $completed_attempts = (int)$attempts_data['completed_attempts'];
    $remaining_attempts = $max_attempts - $completed_attempts;

    // Check for active (incomplete) attempt
    $query = "SELECT attempt_id, start_time, next_attempt_available 
              FROM student_quiz_attempts 
              WHERE user_id = ? AND quiz_id = ? AND is_completed = 0 
              ORDER BY start_time DESC LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $quiz_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $active_attempt = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    $can_attempt = $remaining_attempts > 0 && !$active_attempt;
    $cooldown_remaining = 0;

    if ($active_attempt && $active_attempt['next_attempt_available']) {
        $next_attempt = new DateTime($active_attempt['next_attempt_available']);
        $now = new DateTime();
        $cooldown_remaining = max(0, $next_attempt->getTimestamp() - $now->getTimestamp());
    }

    echo json_encode([
        'remaining_attempts' => $remaining_attempts,
        'can_attempt' => $can_attempt,
        'cooldown_remaining' => $cooldown_remaining
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Failed to check attempt status: ' . $e->getMessage()]);
}

mysqli_close($conn);
?>