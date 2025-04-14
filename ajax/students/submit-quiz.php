<?php
/**
 * Submit Quiz AJAX Handler
 * 
 * Finalizes a quiz attempt and calculates the final score.
 * Handles normal submission, forfeited attempts, and time-expired submissions.
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
$attemptId = isset($_POST['attempt_id']) ? intval($_POST['attempt_id']) : 0;
$isForfeit = isset($_POST['is_forfeit']) && $_POST['is_forfeit'] == 1;
$isTimeExpired = isset($_POST['is_time_expired']) && $_POST['is_time_expired'] == 1;

if (!$attemptId) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // First, check if 'notes' column exists
    $notesColumnExists = false;
    $checkColumnResult = $conn->query("SHOW COLUMNS FROM student_quiz_attempts LIKE 'notes'");
    if ($checkColumnResult && $checkColumnResult->num_rows > 0) {
        $notesColumnExists = true;
    }
    
    // If it doesn't exist, try to add it
    if (!$notesColumnExists) {
        try {
            $conn->query("ALTER TABLE student_quiz_attempts ADD COLUMN notes TEXT NULL");
            $notesColumnExists = true;
        } catch (Exception $e) {
            // Log the error but continue without using notes
            error_log("Failed to add notes column: " . $e->getMessage());
        }
    }

    // Verify attempt belongs to current user and is active
    $stmt = $conn->prepare("
        SELECT a.*, q.pass_mark
        FROM student_quiz_attempts a
        JOIN section_quizzes q ON a.quiz_id = q.quiz_id
        WHERE a.attempt_id = ? AND a.user_id = ? AND a.is_completed = 0
    ");
    $stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $attempt = $result->fetch_assoc();
    $stmt->close();

    if (!$attempt) {
        throw new Exception('Invalid attempt or quiz already completed.');
    }

    // Calculate time spent
    $startTime = new DateTime($attempt['start_time']);
    $endTime = new DateTime();
    $timeSpent = $endTime->getTimestamp() - $startTime->getTimestamp();

    // Get all questions for this quiz
    $stmt = $conn->prepare("
        SELECT q.question_id, q.points
        FROM quiz_questions q
        WHERE q.quiz_id = ?
    ");
    $stmt->bind_param("i", $attempt['quiz_id']);
    $stmt->execute();
    $questions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Calculate total possible points
    $totalPoints = 0;
    foreach ($questions as $question) {
        $totalPoints += $question['points'];
    }

    // Get points awarded
    $stmt = $conn->prepare("
        SELECT SUM(points_awarded) as earned_points
        FROM student_question_responses
        WHERE attempt_id = ?
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $pointsResult = $stmt->get_result()->fetch_assoc();
    $earnedPoints = $pointsResult['earned_points'] ?: 0;
    $stmt->close();

    // Calculate score as percentage
    $score = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
    
    // Round score to 1 decimal place
    $score = round($score, 1);

    // Determine if passed based on pass mark
    $passed = $score >= $attempt['pass_mark'];

    // Update the attempt record - with or without notes depending on column existence
    if ($notesColumnExists && ($isForfeit || $isTimeExpired)) {
        $notes = $isForfeit ? "Quiz forfeited by student" : "Quiz auto-submitted due to time expiration";
        $stmt = $conn->prepare("
            UPDATE student_quiz_attempts
            SET is_completed = 1, end_time = NOW(), score = ?, passed = ?, time_spent = ?, notes = ?
            WHERE attempt_id = ?
        ");
        $stmt->bind_param("diiis", $score, $passed, $timeSpent, $notes, $attemptId);
    } else {
        $stmt = $conn->prepare("
            UPDATE student_quiz_attempts
            SET is_completed = 1, end_time = NOW(), score = ?, passed = ?, time_spent = ?
            WHERE attempt_id = ?
        ");
        $stmt->bind_param("diii", $score, $passed, $timeSpent, $attemptId);
    }
    $stmt->execute();
    $stmt->close();

    // Mark the session as inactive
    $stmt = $conn->prepare("
        UPDATE quiz_sessions
        SET is_active = 0
        WHERE attempt_id = ?
    ");
    $stmt->bind_param("i", $attemptId);
    $stmt->execute();
    $stmt->close();

    // Update progress if quiz is required and passed
    $stmt = $conn->prepare("
        SELECT topic_id, is_required
        FROM section_quizzes
        WHERE quiz_id = ?
    ");
    $stmt->bind_param("i", $attempt['quiz_id']);
    $stmt->execute();
    $quizResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($quizResult && $quizResult['topic_id'] && $quizResult['is_required'] && $passed) {
        // Get enrollment ID
        $stmt = $conn->prepare("
            SELECT e.enrollment_id
            FROM enrollments e
            JOIN section_topics t ON e.course_id = t.course_id
            WHERE e.user_id = ? AND t.topic_id = ?
        ");
        $stmt->bind_param("ii", $_SESSION['user_id'], $quizResult['topic_id']);
        $stmt->execute();
        $enrollmentResult = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($enrollmentResult) {
            $enrollmentId = $enrollmentResult['enrollment_id'];
            
            // Check if progress record exists
            $stmt = $conn->prepare("
                SELECT progress_id, completion_status
                FROM progress
                WHERE enrollment_id = ? AND topic_id = ?
            ");
            $stmt->bind_param("ii", $enrollmentId, $quizResult['topic_id']);
            $stmt->execute();
            $progressResult = $stmt->get_result();
            $progressExists = $progressResult->num_rows > 0;
            $progressStatus = $progressExists ? $progressResult->fetch_assoc()['completion_status'] : null;
            $stmt->close();

            if ($progressExists) {
                if ($progressStatus !== 'Completed') {
                    // Update existing progress record
                    $stmt = $conn->prepare("
                        UPDATE progress
                        SET completion_status = 'Completed', completion_date = NOW()
                        WHERE enrollment_id = ? AND topic_id = ?
                    ");
                    $stmt->bind_param("ii", $enrollmentId, $quizResult['topic_id']);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Create new progress record
                $stmt = $conn->prepare("
                    INSERT INTO progress (enrollment_id, topic_id, completion_status, completion_date)
                    VALUES (?, ?, 'Completed', NOW())
                ");
                $stmt->bind_param("ii", $enrollmentId, $quizResult['topic_id']);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // Set the next attempt available time (12 hour cooldown)
$nextAttemptTime = new DateTime();
$nextAttemptTime->add(new DateInterval('PT12H')); // 12 hours
$nextAttemptAvailable = $nextAttemptTime->format('Y-m-d H:i:s');

$stmt = $conn->prepare("
    UPDATE student_quiz_attempts
    SET next_attempt_available = ?
    WHERE attempt_id = ?
");
$stmt->bind_param("si", $nextAttemptAvailable, $attemptId);
$stmt->execute();
$stmt->close();


    // Commit transaction
    $conn->commit();

    // Build response message based on submission type
    $message = 'Quiz submitted successfully.';
    if ($isForfeit) {
        $message = 'Quiz forfeited and submitted successfully.';
    } elseif ($isTimeExpired) {
        $message = 'Quiz time expired. Your answers have been submitted.';
    }

    // Return success
    echo json_encode([
        'success' => true,
        'score' => $score,
        'passed' => $passed,
        'message' => $message,
        'submission_type' => $isForfeit ? 'forfeit' : ($isTimeExpired ? 'time_expired' : 'normal')
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>