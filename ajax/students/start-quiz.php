<?php
/**
 * Start Quiz AJAX Handler
 * 
 * Initializes a new quiz attempt and creates a quiz session.
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

// Verify AJAX request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Get POST data
$quizId = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
$enrollmentId = isset($_POST['enrollment_id']) ? intval($_POST['enrollment_id']) : 0;

if (!$quizId || !$enrollmentId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // Verify enrollment
    $stmt = $conn->prepare("
        SELECT e.user_id, e.course_id FROM enrollments e
        WHERE e.enrollment_id = ? AND e.user_id = ? AND e.status = 'active'
    ");
    $stmt->bind_param("ii", $enrollmentId, $_SESSION['user_id']);
    $stmt->execute();
    $enrollment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$enrollment) {
        throw new Exception('Invalid enrollment or you do not have access to this course.');
    }

    // Get quiz settings
    $stmt = $conn->prepare("
    SELECT q.*, sq.section_id, sq.topic_id, sq.attempts_allowed, c.course_id
    FROM section_quizzes sq
    JOIN course_sections s ON sq.section_id = s.section_id
    JOIN courses c ON s.course_id = c.course_id
    LEFT JOIN quizzes q ON sq.quiz_id = q.quiz_id
    WHERE sq.quiz_id = ? AND c.course_id = ?
");
    $stmt->bind_param("ii", $quizId, $enrollment['course_id']);
    $stmt->execute();
    $quiz = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$quiz) {
        throw new Exception('Quiz not found or you do not have access to it.');
    }

    // Check if user already has an active attempt
    $stmt = $conn->prepare("
        SELECT a.attempt_id
        FROM student_quiz_attempts a
        JOIN quiz_sessions s ON a.attempt_id = s.attempt_id
        WHERE a.user_id = ? AND a.quiz_id = ? AND a.is_completed = 0 AND s.is_active = 1
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
    $stmt->execute();
    $activeAttempt = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($activeAttempt) {
        throw new Exception('You already have an active attempt for this quiz. Please resume or abandon it.');
    }

    // Check attempt limits
    $stmt = $conn->prepare("
        SELECT COUNT(*) as attempt_count FROM student_quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
    $stmt->execute();
    $attemptCount = $stmt->get_result()->fetch_assoc()['attempt_count'];
    $stmt->close();

    if ($quiz['attempts_allowed'] > 0 && $attemptCount >= $quiz['attempts_allowed']) {
        throw new Exception('You have reached the maximum number of attempts allowed for this quiz.');
    }

    // Create new attempt
    $stmt = $conn->prepare("
        INSERT INTO student_quiz_attempts (user_id, quiz_id, start_time, attempt_number)
        VALUES (?, ?, NOW(), ?)
    ");
    $attemptNumber = $attemptCount + 1;
    $stmt->bind_param("iii", $_SESSION['user_id'], $quizId, $attemptNumber);
    $stmt->execute();
    $attemptId = $conn->insert_id;
    $stmt->close();

    // Set end time if timed quiz
    $endTime = null;
    if ($quiz['time_limit']) {
        $endTime = date('Y-m-d H:i:s', strtotime('+' . $quiz['time_limit'] . ' minutes'));
        $stmt = $conn->prepare("
            UPDATE student_quiz_attempts
            SET end_time = ?
            WHERE attempt_id = ?
        ");
        $stmt->bind_param("si", $endTime, $attemptId);
        $stmt->execute();
        $stmt->close();
    }

    // Generate session token
    $sessionToken = bin2hex(random_bytes(16));

    // Create quiz session
    $stmt = $conn->prepare("
        INSERT INTO quiz_sessions (attempt_id, session_token, client_ip, user_agent, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $clientIp = $_SERVER['REMOTE_ADDR'];
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $stmt->bind_param("isss", $attemptId, $sessionToken, $clientIp, $userAgent);
    $stmt->execute();
    $sessionId = $conn->insert_id;
    $stmt->close();

    // Get questions
    $questions = [];
    if ($quiz['randomize_questions']) {
        $stmt = $conn->prepare("
            SELECT q.question_id, q.question_text, q.question_type, q.points, q.difficulty
            FROM quiz_questions q
            WHERE q.quiz_id = ?
            ORDER BY RAND()
        ");
    } else {
        $stmt = $conn->prepare("
            SELECT q.question_id, q.question_text, q.question_type, q.points, q.difficulty
            FROM quiz_questions q
            WHERE q.quiz_id = ?
            ORDER BY q.question_order
        ");
    }
    $stmt->bind_param("i", $quizId);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $questions[] = [
            'question_id' => $row['question_id'],
            'question_type' => $row['question_type'],
            'points' => $row['points'],
            'answered' => false
        ];
    }
    $stmt->close();

    if (empty($questions)) {
        throw new Exception('This quiz has no questions.');
    }

    // Store question order in session data
    $questionIds = array_column($questions, 'question_id');
    $sessionData = [
        'question_order' => $questionIds,
        'current_question' => 0,
        'answers' => []
    ];

    $sessionDataJson = json_encode($sessionData);
    $stmt = $conn->prepare("
        UPDATE quiz_sessions
        SET session_data = ?
        WHERE session_id = ?
    ");
    $stmt->bind_param("si", $sessionDataJson, $sessionId);
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'attempt_id' => $attemptId,
        'session_id' => $sessionId,
        'session_token' => $sessionToken,
        'questions' => $questions,
        'end_time' => $endTime,
        'message' => 'Quiz started successfully.'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>