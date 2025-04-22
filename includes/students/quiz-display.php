<?php

/**
 * Coursera-Style Quiz Display Component
 * 
 * This file handles the display of quizzes for students with a UI similar to Coursera:
 * - Slides in from the right
 * - Clean, modern interface with proper feedback
 * - Shows all questions at once with progress tracking
 * - Supports multiple attempts with history display
 * 
 * @package Learnix
 * @subpackage Students
 */

// Extract the quiz ID from the topic content
$quizId = $topicContent['quiz_id'] ?? 0;

// Ensure we have an enrollment ID
$enrollmentId = $_SESSION['enrollment_id'] ?? 0;

if (!$quizId) {
    echo '<div class="alert alert-danger">Quiz not found. Please contact support.</div>';
    return;
}

if (!$enrollmentId) {
    echo '<div class="alert alert-danger">You are not properly enrolled in this course. Please go back to the course page.</div>';
    return;
}

// Get quiz details
$stmt = $conn->prepare("
    SELECT q.*, 
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.quiz_id) as question_count
    FROM section_quizzes q
    WHERE q.quiz_id = ?
");
$stmt->bind_param("i", $quizId);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz information could not be loaded. Please try again later.</div>';
    return;
}

// Check how many attempts the student has made
$stmt = $conn->prepare("
    SELECT COUNT(*) as attempt_count, 
           MAX(score) as highest_score,
           MAX(passed) as has_passed,
           MAX(attempt_id) as last_attempt_id
    FROM student_quiz_attempts
    WHERE user_id = ? AND quiz_id = ?
");
$stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
$stmt->execute();
$attemptInfo = $stmt->get_result()->fetch_assoc();
$stmt->close();

$attemptCount = $attemptInfo['attempt_count'];
$highestScore = $attemptInfo['highest_score'];
$hasPassed = $attemptInfo['has_passed'];
$lastAttemptId = $attemptInfo['last_attempt_id'];

// Check if there's an active session for this quiz
$activeSession = null;
$stmt = $conn->prepare("
    SELECT s.*, a.start_time, a.end_time, a.is_completed
    FROM quiz_sessions s
    JOIN student_quiz_attempts a ON s.attempt_id = a.attempt_id
    WHERE a.user_id = ? AND a.quiz_id = ? AND a.is_completed = 0 AND s.is_active = 1
    ORDER BY s.created_at DESC
    LIMIT 1
");
$stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $activeSession = $result->fetch_assoc();
}
$stmt->close();

// Get all attempts with details
$allAttempts = [];
if ($attemptCount > 0) {
    $stmt = $conn->prepare("
        SELECT attempt_id, start_time, end_time, score, passed, time_spent
        FROM student_quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
        ORDER BY attempt_id DESC
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
    $stmt->execute();
    $attempts = $stmt->get_result();
    while ($attempt = $attempts->fetch_assoc()) {
        $allAttempts[] = $attempt;
    }
    $stmt->close();
}

// Check when the next attempt is available (only if max attempts reached)
$nextAttemptAvailable = null;
$cooldownActive = false;
$cooldownTimeRemaining = null;

// Only check for cooldown if max attempts have been reached
if ($attemptCount >= $quiz['attempts_allowed'] && $quiz['attempts_allowed'] > 0) {
    $stmt = $conn->prepare("
        SELECT next_attempt_available
        FROM student_quiz_attempts
        WHERE user_id = ? AND quiz_id = ?
        ORDER BY attempt_id DESC
        LIMIT 1
    ");
    $stmt->bind_param("ii", $_SESSION['user_id'], $quizId);
    $stmt->execute();
    $cooldownResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($cooldownResult && $cooldownResult['next_attempt_available']) {
        $nextAttemptAvailable = new DateTime($cooldownResult['next_attempt_available']);
        $currentTime = new DateTime();

        if ($currentTime < $nextAttemptAvailable) {
            $cooldownActive = true;
            $interval = $currentTime->diff($nextAttemptAvailable);

            // Format the cooldown time remaining
            if ($interval->days > 0) {
                $cooldownTimeRemaining = $interval->format('%a days, %h hours, %i minutes');
            } else if ($interval->h > 0) {
                $cooldownTimeRemaining = $interval->format('%h hours, %i minutes');
            } else {
                $cooldownTimeRemaining = $interval->format('%i minutes, %s seconds');
            }
        } else {
            // Reset attempt count since cooldown has passed
            $attemptCount = 0;
        }
    }
}

// Determine if student can take the quiz - only check cooldown
$canTakeQuiz = !$cooldownActive;
$attemptsRemaining = $quiz['attempts_allowed'] == 0 ? 'Unlimited' : max(1, $quiz['attempts_allowed'] - $attemptCount);

// Process quiz state
$quizState = 'intro';
if ($activeSession && !$activeSession['is_completed']) {
    $quizState = 'resume';
} elseif (isset($_GET['attempt_id']) && isset($_GET['view']) && $_GET['view'] == 'results') {
    $quizState = 'results';
} elseif ($cooldownActive) {
    // Only show completed state when cooldown is active
    $quizState = 'completed';
}
?>

<style>
    /* Coursera-style Quiz Interface */
    .quiz-container {
        max-width: 960px;
        margin: 0 auto;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }

    .quiz-intro {
        padding: 1.5rem;
        border-radius: 8px;
        background-color: #f8f9fa;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Quiz Card Design */
    .quiz-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }

    .quiz-card-header {
        padding: 1rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        font-weight: 500;
    }

    .quiz-card-body {
        padding: 1.5rem;
    }

    /* Quiz Badge */
    .quiz-badge {
        display: inline-flex;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 600;
        align-items: center;
    }

    .quiz-badge-success {
        background-color: rgba(25, 135, 84, 0.1);
        color: #198754;
    }

    .quiz-badge-warning {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }

    .quiz-badge-danger {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .quiz-badge-info {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    /* Quiz Slide Container */
    .quiz-slide-container {
        position: fixed;
        top: 0;
        right: -100%;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.98);
        z-index: 9999;
        overflow-y: auto;
        transition: right 0.3s ease-in-out;
        display: flex;
        flex-direction: column;
    }

    .quiz-slide-container.active {
        right: 0;
    }

    .quiz-timer {
        position: sticky;
        top: 0;
        background-color: #fff;
        padding: 10px 20px;
        border-bottom: 1px solid #e0e0e0;
        z-index: 2;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .quiz-timer-display {
        font-weight: 600;
        color: #333;
    }

    .quiz-timer-display.warning {
        color: #ffc107;
    }

    .quiz-timer-display.danger {
        color: #dc3545;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    .quiz-content {
        padding: 20px;
        flex: 1;
    }

    .quiz-title-header {
        margin-bottom: 24px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e0e0e0;
    }

    .quiz-question {
        margin-bottom: 2rem;
        padding: 1.5rem;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        background-color: #fff;
        transition: all 0.2s ease;
    }

    .quiz-question.answered {
        border-left: 3px solid #0d6efd;
    }

    .quiz-question-header {
        margin-bottom: 1rem;
    }

    .quiz-footer {
        position: sticky;
        bottom: 0;
        background-color: #fff;
        padding: 15px 20px;
        border-top: 1px solid #e0e0e0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }

    /* Quiz Options */
    .quiz-options label {
        display: block;
        padding: 10px 15px;
        margin-bottom: 8px;
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }

    .quiz-options label:hover {
        background-color: #f8f9fa;
    }

    .quiz-options input[type="radio"]:checked+label,
    .quiz-options input[type="checkbox"]:checked+label {
        background-color: #e7f0ff;
        border-color: #0d6efd;
    }

    .quiz-options input[type="radio"],
    .quiz-options input[type="checkbox"] {
        display: none;
    }

    /* Coursera Buttons */
    .btn-coursera {
        background-color: #0056D2;
        color: white;
        border: none;
        border-radius: 4px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        transition: background-color 0.2s;
    }

    .btn-coursera:hover {
        background-color: #0046be;
        color: white;
    }

    .btn-coursera-outline {
        background-color: transparent;
        color: #0056D2;
        border: 1px solid #0056D2;
        border-radius: 4px;
        font-weight: 500;
        padding: 0.5rem 1.25rem;
        transition: all 0.2s;
    }

    .btn-coursera-outline:hover {
        background-color: #e7f0ff;
        color: #0046be;
    }

    /* Result-specific styles */
    .quiz-result-summary {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .quiz-result-chart {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .chart-container {
        width: 150px;
        height: 150px;
    }

    .quiz-attempts-table {
        font-size: 0.875rem;
    }

    .quiz-attempts-table th {
        font-weight: 500;
    }

    /* Progress tracking dots */
    .progress-dots {
        display: flex;
        justify-content: center;
        margin: 1rem 0;
    }

    .progress-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background-color: #e0e0e0;
        margin: 0 5px;
        transition: all 0.2s;
    }

    .progress-dot.active {
        background-color: #0056D2;
        transform: scale(1.2);
    }

    .progress-dot.completed {
        background-color: #198754;
    }
</style>

<div class="quiz-container" data-quiz-id="<?php echo $quizId; ?>" data-enrollment-id="<?php echo $enrollmentId; ?>">
    <?php if ($quizState == 'intro'): ?>
        <!-- Quiz Introduction -->
        <div class="quiz-intro">
            <h3 class="mb-4"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>

            <div class="row mb-4">
                <div class="col-md-7">
                    <?php if (!empty($quiz['description'])): ?>
                        <p class="mb-4"><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
                    <?php endif; ?>

                    <div class="d-flex flex-wrap gap-4 mb-4">
                        <div>
                            <div class="text-muted small mb-1">Questions</div>
                            <div class="fw-bold"><?php echo $quiz['question_count']; ?></div>
                        </div>

                        <?php if ($quiz['time_limit']): ?>
                            <div>
                                <div class="text-muted small mb-1">Time Limit</div>
                                <div class="fw-bold"><?php echo $quiz['time_limit']; ?> minutes</div>
                            </div>
                        <?php endif; ?>

                        <div>
                            <div class="text-muted small mb-1">Pass Mark</div>
                            <div class="fw-bold"><?php echo $quiz['pass_mark']; ?>%</div>
                        </div>

                        <div>
                            <div class="text-muted small mb-1">Attempts</div>
                            <div class="fw-bold"><?php echo $attemptCount; ?> / <?php echo $quiz['attempts_allowed'] == 0 ? '∞' : $quiz['attempts_allowed']; ?></div>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <?php if ($attemptCount > 0): ?>
                        <div class="quiz-card mb-3">
                            <div class="quiz-card-body">
                                <h5 class="card-title mb-3">Your Progress</h5>
                                <p class="mb-2">Highest Score:
                                    <strong class="<?php echo ($hasPassed ? 'text-success' : 'text-danger'); ?>">
                                        <?php echo number_format($highestScore, 1); ?>%
                                    </strong>
                                </p>
                                <p class="mb-2">Status:
                                    <?php if ($hasPassed): ?>
                                        <span class="quiz-badge quiz-badge-success">
                                            <i class="bi bi-check-circle me-1"></i> Passed
                                        </span>
                                    <?php else: ?>
                                        <span class="quiz-badge quiz-badge-warning">
                                            <i class="bi bi-exclamation-circle me-1"></i> Not Passed
                                        </span>
                                    <?php endif; ?>
                                </p>

                                <p class="mb-0 small text-muted">
    <?php if ($cooldownActive): ?>
        Quiz available after cooldown
    <?php else: ?>
        You can retake this quiz
    <?php endif; ?>
</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($canTakeQuiz): ?>
                        <button type="button" class="btn btn-coursera start-quiz-btn w-100">
                            <i class="bi bi-play-circle me-2"></i> Start Quiz
                        </button>
                    <?php elseif ($cooldownActive): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-hourglass-split me-2"></i>
                            <strong>Cooldown Period Active</strong>
                            <div class="mt-2">
                                <p class="mb-1">You can attempt this quiz again in:</p>
                                <div class="d-flex justify-content-center">
                                    <div class="cooldown-timer text-center fs-5 fw-bold" data-available-time="<?php echo $nextAttemptAvailable->format('c'); ?>">
                                        <?php echo $cooldownTimeRemaining; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            <?php if ($hasPassed): ?>
                                You have successfully passed this quiz.
                            <?php else: ?>
                                You have used all available attempts for this quiz.
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($attemptCount > 0): ?>
                <div class="mt-4">
                    <h5 class="mb-3">Previous Attempts</h5>
                    <div class="table-responsive">
                        <table class="table table-sm quiz-attempts-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Time Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $attemptNumber = $attemptCount;
                                foreach ($allAttempts as $attempt):
                                    $duration = '';
                                    if ($attempt['time_spent']) {
                                        $minutes = floor($attempt['time_spent'] / 60);
                                        $seconds = $attempt['time_spent'] % 60;
                                        $duration = "{$minutes}m {$seconds}s";
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $attemptNumber--; ?></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($attempt['start_time'])); ?></td>
                                        <td class="<?php echo ($attempt['passed'] ? 'text-success' : 'text-danger'); ?> fw-bold">
                                            <?php echo number_format($attempt['score'], 1); ?>%
                                        </td>
                                        <td>
                                            <?php if ($attempt['passed']): ?>
                                                <span class="quiz-badge quiz-badge-success">Passed</span>
                                            <?php else: ?>
                                                <span class="quiz-badge quiz-badge-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $duration; ?></td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary view-results-btn"
                                                data-attempt-id="<?php echo $attempt['attempt_id']; ?>">
                                                View Results
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php elseif ($quizState == 'resume'): ?>
        <!-- Resume Quiz Section -->
        <div class="quiz-intro">
            <div class="quiz-card">
                <div class="quiz-card-header">
                    <h3 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
                </div>
                <div class="quiz-card-body">
                    <div class="alert alert-info mb-4">
                        <i class="bi bi-info-circle me-2"></i>
                        You have an active quiz session that was started on
                        <?php echo date('M d, Y g:i A', strtotime($activeSession['start_time'])); ?>.
                    </div>

                    <?php if ($quiz['time_limit']):
                        $startTime = new DateTime($activeSession['start_time']);
                        $currentTime = new DateTime();
                        $elapsedSeconds = $currentTime->getTimestamp() - $startTime->getTimestamp();
                        $remainingSeconds = ($quiz['time_limit'] * 60) - $elapsedSeconds;

                        if ($remainingSeconds <= 0): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-circle me-2"></i>
                                Your time for this attempt has expired. The system will automatically submit your attempt.
                            </div>

                            <!-- Add auto-submit form -->
                            <form id="autoSubmitForm" method="post" action="../ajax/students/submit-quiz.php">
                                <input type="hidden" name="attempt_id" value="<?php echo $activeSession['attempt_id']; ?>">
                                <input type="hidden" name="is_time_expired" value="1">
                            </form>

                            <script>
                                // Auto-submit the quiz after 3 seconds
                                setTimeout(function() {
                                    document.getElementById('autoSubmitForm').submit();
                                }, 3000);
                            </script>

                        <?php else:
                            $remainingMinutes = floor($remainingSeconds / 60);
                            $remainingSecondsDisplay = $remainingSeconds % 60;
                        ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-clock me-2"></i>
                                You have <strong><?php echo $remainingMinutes; ?> minutes, <?php echo $remainingSecondsDisplay; ?> seconds</strong> remaining to complete this quiz.
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="d-flex gap-3 mt-4">
                        <button type="button" class="btn btn-coursera resume-quiz-btn" data-session-id="<?php echo $activeSession['session_id']; ?>" data-attempt-id="<?php echo $activeSession['attempt_id']; ?>">
                            <i class="bi bi-arrow-right-circle me-2"></i> Resume Quiz
                        </button>

                        <button type="button" class="btn btn-outline-danger abandon-quiz-btn" data-session-id="<?php echo $activeSession['session_id']; ?>" data-attempt-id="<?php echo $activeSession['attempt_id']; ?>">
                            <i class="bi bi-x-circle me-2"></i> Abandon Attempt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($quizState == 'results'): ?>
        <!-- Quiz Results Section - To be loaded via AJAX -->
        <div id="quizResults" data-attempt-id="<?php echo $_GET['attempt_id']; ?>">
            <div class="text-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3">Loading quiz results...</p>
            </div>
        </div>
    <?php else: ?>
        <!-- Quiz Completed Section -->
<div class="quiz-intro">
    <div class="quiz-card">
        <div class="quiz-card-header">
            <h3 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
        </div>
        <div class="quiz-card-body">
            <div class="alert alert-<?php echo $hasPassed ? 'success' : 'warning'; ?> mb-4">
                <i class="bi bi-<?php echo $hasPassed ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <?php if ($cooldownActive): ?>
    <strong>Cooldown Period Active</strong>. Your highest score was <?php echo number_format($highestScore, 1); ?>%.
    <div class="mt-2">
        <p class="mb-1">You can attempt this quiz again in: <span class="cooldown-timer fw-bold" data-available-time="<?php echo $nextAttemptAvailable->format('c'); ?>"><?php echo $cooldownTimeRemaining; ?></span></p>
    </div>
<?php else: ?>
    Your highest score was <?php echo number_format($highestScore, 1); ?>%. You can attempt this quiz again.
<?php endif; ?>
            </div>

                    <div class="d-flex gap-3 mt-4">
                        <a href="?course_id=<?php echo $_GET['course_id']; ?>&section=<?php echo $_GET['section'] ?? ''; ?>"
                            class="btn btn-coursera-outline">
                            <i class="bi bi-arrow-left me-2"></i> Back to Course
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($attemptCount > 0): ?>
                <div class="mt-4">
                    <h5 class="mb-3">Attempt History</h5>
                    <div class="table-responsive">
                        <table class="table table-sm quiz-attempts-table">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Time Spent</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $attemptNumber = $attemptCount;
                                foreach ($allAttempts as $attempt):
                                    $duration = '';
                                    if ($attempt['time_spent']) {
                                        $minutes = floor($attempt['time_spent'] / 60);
                                        $seconds = $attempt['time_spent'] % 60;
                                        $duration = "{$minutes}m {$seconds}s";
                                    }
                                ?>
                                    <tr>
                                        <td><?php echo $attemptNumber--; ?></td>
                                        <td><?php echo date('M d, Y g:i A', strtotime($attempt['start_time'])); ?></td>
                                        <td class="<?php echo ($attempt['passed'] ? 'text-success' : 'text-danger'); ?> fw-bold">
                                            <?php echo number_format($attempt['score'], 1); ?>%
                                        </td>
                                        <td>
                                            <?php if ($attempt['passed']): ?>
                                                <span class="quiz-badge quiz-badge-success">Passed</span>
                                            <?php else: ?>
                                                <span class="quiz-badge quiz-badge-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $duration; ?></td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary view-results-btn"
                                                data-attempt-id="<?php echo $attempt['attempt_id']; ?>">
                                                View Results
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Quiz Slide-in Container (Hidden initially) -->
<div id="quizSlideContainer" class="quiz-slide-container">
    <!-- Quiz Timer -->
    <div class="quiz-timer">
        <div>
            <h4 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h4>
        </div>
        <div class="d-flex align-items-center">
            <i class="bi bi-clock me-2"></i>
            <div id="quizTimerDisplay" class="quiz-timer-display">--:--</div>
        </div>
    </div>

    <!-- Quiz Content - All questions will be loaded here -->
    <div class="quiz-content" id="quizContent">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading questions...</span>
            </div>
            <p class="mt-3">Loading quiz questions...</p>
        </div>
    </div>

    <!-- Quiz Footer -->
    <div class="quiz-footer">
        <button id="exitQuizBtn" class="btn btn-coursera-outline">
            <i class="bi bi-x-circle me-2"></i> Exit Quiz
        </button>
        <div>
            <span id="questionsProgress" class="me-3 d-none d-md-inline">0/0 Questions Answered</span>
            <button id="submitQuizBtn" class="btn btn-coursera">
                <i class="bi bi-check-circle me-2"></i> Submit Quiz
            </button>
        </div>
    </div>
</div>

<!-- Start Quiz Confirmation Modal -->
<div class="modal fade" id="startQuizModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Start Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>You are about to start "<?php echo htmlspecialchars($quiz['quiz_title']); ?>".</p>

                <ul class="mb-4">
                    <li>This quiz has <strong><?php echo $quiz['question_count']; ?> questions</strong>.</li>
                    <?php if ($quiz['time_limit']): ?>
                        <li>There is a time limit of <strong><?php echo $quiz['time_limit']; ?> minutes</strong>.</li>
                    <?php endif; ?>
                    <li>You need to score at least <strong><?php echo $quiz['pass_mark']; ?>%</strong> to pass.</li>
                </ul>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Once you start, the timer will begin and cannot be paused.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-coursera" id="confirmStartQuiz">
                    <i class="bi bi-play-circle me-2"></i> Start Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Submit Quiz Confirmation Modal -->
<div class="modal fade" id="submitQuizModal" tabindex="-1" aria-labelledby="submitQuizModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="submitQuizModalLabel">Submit Quiz</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to submit your quiz?</p>

                <div id="unansweredWarning" class="alert alert-warning d-none">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    You have <span id="unansweredCount">0</span> unanswered questions.
                </div>

                <p class="mb-0">Once submitted, you will not be able to change your answers.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Quiz</button>
                <button type="button" class="btn btn-coursera" id="confirmSubmitQuiz">
                    <i class="bi bi-check-circle me-2"></i> Submit Quiz
                </button>
            </div>
        </div>
    </div>
</div>


<!-- JavaScript for the quiz functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elements
        const quizContainer = document.querySelector('.quiz-container');
        const startQuizBtn = document.querySelector('.start-quiz-btn');
        const confirmStartQuizBtn = document.getElementById('confirmStartQuiz');
        const resumeQuizBtn = document.querySelector('.resume-quiz-btn');
        const abandonQuizBtn = document.querySelector('.abandon-quiz-btn');
        const quizSlideContainer = document.getElementById('quizSlideContainer');
        const quizContent = document.getElementById('quizContent');
        const quizTimerDisplay = document.getElementById('quizTimerDisplay');
        const exitQuizBtn = document.getElementById('exitQuizBtn');
        const submitQuizBtn = document.getElementById('submitQuizBtn');
        const confirmSubmitQuizBtn = document.getElementById('confirmSubmitQuiz');

        // Start Quiz button
        if (startQuizBtn) {
            startQuizBtn.addEventListener('click', function() {
                // Show confirmation modal
                const modal = new bootstrap.Modal(document.getElementById('startQuizModal'));
                modal.show();
            });
        }

        // Confirm Start Quiz button
        if (confirmStartQuizBtn) {
            confirmStartQuizBtn.addEventListener('click', function() {
                // Show loading overlay
                showOverlay('Starting quiz...');

                // Get quiz information
                const quizId = parseInt(quizContainer.getAttribute('data-quiz-id'));
                const enrollmentId = parseInt(quizContainer.getAttribute('data-enrollment-id'));

                // Validate parameters
                if (!quizId || isNaN(quizId) || !enrollmentId || isNaN(enrollmentId)) {
                    removeOverlay();
                    showAlert('danger', 'Error: Missing quiz information. Please refresh the page and try again.');
                    return;
                }

                // Use FormData
                const formData = new FormData();
                formData.append('quiz_id', quizId);
                formData.append('enrollment_id', enrollmentId);

                // AJAX request to start quiz
                fetch('../ajax/students/start-quiz.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Server responded with status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log("Server response:", data);

                        if (data.success) {
                            // Hide modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('startQuizModal'));
                            if (modal) modal.hide();

                            // Load quiz interface
                            loadQuizInterface(data);

                            // Dispatch event that quiz has started
                            document.dispatchEvent(new Event('quizStarted'));
                        } else {
                            removeOverlay();
                            showAlert('danger', data.message || 'Failed to start quiz. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error starting quiz:', error);
                        removeOverlay();
                        showAlert('danger', 'An error occurred while starting the quiz. Please try again.');
                    });
            });
        }

        // Resume Quiz button
        // Resume Quiz button
        if (resumeQuizBtn) {
            resumeQuizBtn.addEventListener('click', function() {
                // Show loading overlay
                showOverlay('Resuming quiz...');

                // Get session information
                const sessionId = this.getAttribute('data-session-id');
                const attemptId = this.getAttribute('data-attempt-id');

                // AJAX request to get quiz data
                fetch(`../ajax/students/resume-quiz-session.php?session_id=${sessionId}&attempt_id=${attemptId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Server responded with status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Load quiz interface
                            loadQuizInterface(data);

                            // Dispatch event that quiz has resumed
                            document.dispatchEvent(new Event('quizResumed'));
                        } else if (data.time_expired) {
                            // Time has expired, submit the quiz
                            showOverlay('Time expired. Submitting your quiz...');

                            fetch('../ajax/students/submit-quiz.php', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded',
                                    },
                                    body: `attempt_id=${attemptId}&is_time_expired=1`
                                })
                                .then(response => response.json())
                                .then(submitData => {
                                    if (submitData.success) {
                                        // Redirect to results
                                        window.location.href = `?course_id=${getUrlParameter('course_id')}&section=${getUrlParameter('section')}&attempt_id=${attemptId}&view=results`;
                                    } else {
                                        removeOverlay();
                                        showAlert('danger', submitData.message || 'Failed to submit quiz. Please try again.');
                                    }
                                })
                                .catch(error => {
                                    console.error('Error submitting expired quiz:', error);
                                    removeOverlay();
                                    showAlert('danger', 'An error occurred while submitting the quiz. Please try again.');
                                });
                        } else {
                            removeOverlay();
                            showAlert('danger', data.message || 'Failed to resume quiz. Please try again.');
                        }
                    })
                    .catch(error => {
                        console.error('Error resuming quiz:', error);
                        removeOverlay();
                        showAlert('danger', 'An error occurred while resuming the quiz. Please try again.');
                    });
            });
        }

        // Helper function to get URL parameters (if not already defined)
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name) || '';
        }


        // Abandon Quiz button
        if (abandonQuizBtn) {
            abandonQuizBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to forfeit this quiz attempt? Your current answers will be submitted and this will count as an attempt.')) {
                    // Show loading overlay
                    showOverlay('Submitting your current answers...');

                    // Get session information
                    const sessionId = this.getAttribute('data-session-id');
                    const attemptId = this.getAttribute('data-attempt-id');

                    // AJAX request to submit the quiz with current answers
                    fetch('../ajax/students/submit-quiz.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `attempt_id=${attemptId}&is_forfeit=1`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload the page to show results
                                window.location.href = `?course_id=${getUrlParameter('course_id')}&section=${getUrlParameter('section')}&attempt_id=${attemptId}&view=results`;
                            } else {
                                removeOverlay();
                                showAlert('danger', data.message || 'Failed to submit quiz. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting quiz:', error);
                            removeOverlay();
                            showAlert('danger', 'An error occurred while submitting the quiz. Please try again.');
                        });
                }
            });
        }

        // Helper function to get URL parameters
        function getUrlParameter(name) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(name) || '';
        }

        // Exit Quiz button
        if (exitQuizBtn) {
            exitQuizBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to exit? Your progress will be saved, but the timer will continue.')) {
                    quizSlideContainer.classList.remove('active');
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                }
            });
        }

        // Submit Quiz button
        if (submitQuizBtn) {
            submitQuizBtn.onclick = function(event) {
                console.log("Submit Quiz button clicked!");

                // Count unanswered questions
                const questions = quizContent.querySelectorAll('.quiz-question');
                let unansweredCount = 0;

                console.log(`Found ${questions.length} questions to check`);

                questions.forEach(question => {
                    const form = question.querySelector('form');
                    if (!form) return;

                    const questionType = form.getAttribute('data-question-type');
                    const questionId = form.getAttribute('data-question-id');

                    console.log(`Checking question ${questionId} of type ${questionType}`);

                    // Check if question is answered based on type
                    let isAnswered = false;

                    if (questionType === 'Multiple Choice' || questionType === 'True/False') {
                        const checkedInputs = form.querySelectorAll('input[type="radio"]:checked, input[type="checkbox"]:checked');
                        isAnswered = checkedInputs.length > 0;
                    } else if (questionType === 'Short_Answer' || questionType === 'Essay' || questionType === 'Fill in the Blanks') {
                        const textInput = form.querySelector('textarea, input[type="text"]');
                        isAnswered = textInput && textInput.value.trim() !== '';
                    } else if (questionType === 'Matching') {
                        const selects = form.querySelectorAll('select');
                        isAnswered = Array.from(selects).every(select => select.value !== '');
                    }

                    if (!isAnswered) {
                        unansweredCount++;
                        console.log(`Question ${questionId} is not answered`);
                    }
                });

                // Show warning if there are unanswered questions
                const unansweredWarning = document.getElementById('unansweredWarning');
                const unansweredCountSpan = document.getElementById('unansweredCount');

                if (unansweredCount > 0) {
                    console.log(`${unansweredCount} questions are not answered`);
                    if (unansweredWarning) {
                        unansweredWarning.classList.remove('d-none');
                        if (unansweredCountSpan) {
                            unansweredCountSpan.textContent = unansweredCount;
                        }
                    }
                } else {
                    console.log("All questions are answered");
                    if (unansweredWarning) {
                        unansweredWarning.classList.add('d-none');
                    }
                }

                // Show confirmation modal using Bootstrap API directly
                try {
                    const submitQuizModal = new bootstrap.Modal(document.getElementById('submitQuizModal'));
                    submitQuizModal.show();
                } catch (error) {
                    console.error("Error showing modal:", error);
                    // Fallback - direct confirmation
                    if (confirm("Are you sure you want to submit your quiz? This action cannot be undone.")) {
                        submitQuizDirectly();
                    }
                }
            };
        }

        // Confirm Submit Quiz button
        if (confirmSubmitQuizBtn) {
            confirmSubmitQuizBtn.onclick = function(event) {
                console.log("Confirm Submit Quiz button clicked!");
                submitQuizDirectly();
            };
        }

        // Function to load all questions for the quiz
        function loadAllQuestions(attemptId) {
            // AJAX request to get all questions
            fetch(`../ajax/students/get-all-quiz-questions.php?attempt_id=${attemptId}`)
                .then(response => response.text())
                .then(html => {
                    quizContent.innerHTML = html;

                    // Initialize question-specific functionality
                    initializeQuestionFunctionality(attemptId);

                    // Remove loading overlay
                    removeOverlay();
                })
                .catch(error => {
                    console.error('Error loading questions:', error);
                    quizContent.innerHTML = '<div class="alert alert-danger">Failed to load quiz questions. Please try again.</div>';
                    removeOverlay();
                });
        }

        // Function to load the quiz interface
        // Function to load the quiz interface
        function loadQuizInterface(data) {
            // Set attempt ID on the slide container
            quizSlideContainer.setAttribute('data-attempt-id', data.attempt_id);

            // Show the slide container
            document.body.style.overflow = 'hidden'; // Prevent scrolling of background
            quizSlideContainer.classList.add('active');

            // Initialize timer - pass end_time if it exists, otherwise null for count-up timer
            initializeTimer(data.end_time || null);

            // Load all questions
            loadAllQuestions(data.attempt_id);

            // Hide the loading overlay
            removeOverlay();
        }

        // Function to initialize the timer
        // Function to initialize the timer
        function initializeTimer(endTime) {
            // Clear any existing timer
            if (window.timerInterval) {
                clearInterval(window.timerInterval);
            }

            let isCountDown = !!endTime;
            let startTime = new Date();

            // Get the attempt ID to fetch elapsed time for resumed quizzes
            const attemptId = quizSlideContainer.getAttribute('data-attempt-id');

            // If we have an end time (timed quiz), calculate the remaining time
            if (isCountDown) {
                const endTimeDate = new Date(endTime);
                const remainingTime = endTimeDate - startTime;

                // Update timer display immediately with the correct remaining time
                updateTimerDisplay(remainingTime, isCountDown);

                // Update timer every second
                const timerInterval = setInterval(function() {
                    const currentTime = new Date();
                    const remainingTime = endTimeDate - currentTime;

                    if (remainingTime <= 0) {
                        // Time's up
                        clearInterval(timerInterval);
                        quizTimerDisplay.textContent = '00:00';
                        quizTimerDisplay.classList.add('danger');

                        // Auto-submit the quiz
                        document.getElementById('confirmSubmitQuiz').click();
                        return;
                    }

                    updateTimerDisplay(remainingTime, isCountDown);
                }, 1000);

                // Store interval in window to clear it if needed
                window.timerInterval = timerInterval;
            }
            // For count-up timer (untimed quiz)
            else {
                // Fetch elapsed time for this attempt
                fetch(`../ajax/students/get-elapsed-time.php?attempt_id=${attemptId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Start from the elapsed time (in seconds) rather than zero
                            let timerStartValue = data.elapsed_seconds * 1000;

                            // Update timer display immediately
                            updateTimerDisplay(timerStartValue, false);

                            // Update timer every second
                            const timerInterval = setInterval(function() {
                                timerStartValue += 1000; // Increase by one second
                                updateTimerDisplay(timerStartValue, false);
                            }, 1000);

                            // Store interval in window to clear it if needed
                            window.timerInterval = timerInterval;
                        } else {
                            console.error('Error fetching elapsed time:', data.message);
                            // Fallback to starting from zero
                            let timerStartValue = 0;
                            updateTimerDisplay(timerStartValue, false);

                            const timerInterval = setInterval(function() {
                                timerStartValue += 1000;
                                updateTimerDisplay(timerStartValue, false);
                            }, 1000);

                            window.timerInterval = timerInterval;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching elapsed time:', error);
                        // Fallback to starting from zero
                        let timerStartValue = 0;
                        updateTimerDisplay(timerStartValue, false);

                        const timerInterval = setInterval(function() {
                            timerStartValue += 1000;
                            updateTimerDisplay(timerStartValue, false);
                        }, 1000);

                        window.timerInterval = timerInterval;
                    });
            }

            // Function to update timer display
            function updateTimerDisplay(time, isCountingDown) {
                const hours = Math.floor(Math.abs(time) / (1000 * 60 * 60));
                const minutes = Math.floor((Math.abs(time) % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((Math.abs(time) % (1000 * 60)) / 1000);

                // Format with leading zeros
                const formattedHours = hours.toString().padStart(2, '0');
                const formattedMinutes = minutes.toString().padStart(2, '0');
                const formattedSeconds = seconds.toString().padStart(2, '0');

                // Display hours only if there are any
                if (hours > 0) {
                    quizTimerDisplay.textContent = `${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
                } else {
                    quizTimerDisplay.textContent = `${formattedMinutes}:${formattedSeconds}`;
                }

                // Warning colors (only for countdown)
                quizTimerDisplay.classList.remove('warning', 'danger');
                if (isCountingDown) {
                    // Update timer icon and add classes for warning colors
                    document.querySelector('.quiz-timer i').className = 'bi bi-clock-history me-2';

                    if (time < 60000) { // Less than 1 minute
                        quizTimerDisplay.classList.add('danger');
                    } else if (time < 300000) { // Less than 5 minutes
                        quizTimerDisplay.classList.add('warning');
                    }
                } else {
                    // Change icon for count-up timer
                    document.querySelector('.quiz-timer i').className = 'bi bi-stopwatch me-2';
                }
            }
        }
        // Function to update the questions progress counter
        function updateQuestionsProgress() {
            const questionsProgress = document.getElementById('questionsProgress');
            if (!questionsProgress) return;

            const totalQuestions = quizContent.querySelectorAll('.quiz-question').length;
            const answeredQuestions = quizContent.querySelectorAll('.quiz-question.answered').length;

            questionsProgress.textContent = `${answeredQuestions}/${totalQuestions} Questions Answered`;
            questionsProgress.classList.remove('d-none');
        }

        // Function to initialize question-specific functionality
        function initializeQuestionFunctionality(attemptId) {

            updateQuestionsProgress();

            // Add event listeners for answer selection
            const questionForms = quizContent.querySelectorAll('.quiz-question form');

            questionForms.forEach(form => {
                // For multiple choice questions
                const multipleChoiceInputs = form.querySelectorAll('input[type="radio"], input[type="checkbox"]');
                multipleChoiceInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        // Auto-save after a short delay
                        clearTimeout(window.autoSaveTimeout);
                        window.autoSaveTimeout = setTimeout(function() {
                            saveQuestionResponse(attemptId, form);
                        }, 1000);
                    });
                });

                // For text-based questions
                const textInputs = form.querySelectorAll('textarea, input[type="text"]');
                textInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        // Auto-save after a short delay
                        clearTimeout(window.autoSaveTimeout);
                        window.autoSaveTimeout = setTimeout(function() {
                            saveQuestionResponse(attemptId, form);
                        }, 2000);
                    });
                });

                // For select dropdowns (matching questions)
                const selectInputs = form.querySelectorAll('select');
                selectInputs.forEach(select => {
                    select.addEventListener('change', function() {
                        // Auto-save after a short delay
                        clearTimeout(window.autoSaveTimeout);
                        window.autoSaveTimeout = setTimeout(function() {
                            saveQuestionResponse(attemptId, form);
                        }, 1000);
                    });
                });

                // For ordering questions
                form.addEventListener('orderChanged', function() {
                    saveQuestionResponse(attemptId, form);
                });
            });
        }

        // Function to save a single question response
        function saveQuestionResponse(attemptId, form) {
            const questionId = form.getAttribute('data-question-id');
            const formData = new FormData(form);

            // Add required parameters
            formData.append('attempt_id', attemptId);
            formData.append('question_id', questionId);

            // Debug output
            console.log(`Saving response for question ${questionId}, attempt ${attemptId}`);

            // AJAX request to save the response with proper error handling
            fetch('../ajax/students/save-quiz-response.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    // Check if the response is OK
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }

                    // Try to parse the response as JSON, but handle potential errors
                    return response.text().then(text => {
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error("Failed to parse response as JSON:", text);
                            throw new Error("Invalid server response");
                        }
                    });
                })
                .then(data => {
                    if (data.success) {
                        // Update question status
                        form.closest('.quiz-question').classList.add('answered');

                        // Update progress counter
                        updateQuestionsProgress();
                    } else {
                        console.error('Error saving response:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error saving response:', error);
                });
        }

        // Function to save all responses before submitting
        function saveAllQuizResponses(attemptId, callback) {
            const forms = quizContent.querySelectorAll('.quiz-question form');

            console.log(`Found ${forms.length} forms to save`);

            if (forms.length === 0) {
                console.log("No forms to save, proceeding to callback");
                if (callback) callback();
                return;
            }

            let savedCount = 0;
            const totalForms = forms.length;

            // Save each form
            forms.forEach(form => {
                const questionId = form.getAttribute('data-question-id');

                console.log(`Preparing to save question ${questionId}`);

                if (!questionId) {
                    console.warn("Form without question ID, skipping");
                    savedCount++;
                    if (savedCount === totalForms && callback) {
                        callback();
                    }
                    return;
                }

                // Create form data
                const formData = new FormData(form);
                formData.append('attempt_id', attemptId);
                formData.append('question_id', questionId);

                // Send AJAX request
                console.log(`Saving response for question ${questionId}`);

                fetch('../ajax/students/save-quiz-response.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Server responded with status: ${response.status}`);
                        }
                        return response.text();
                    })
                    .then(text => {
                        try {
                            const data = JSON.parse(text);
                            savedCount++;
                            console.log(`Saved question ${questionId} (${savedCount}/${totalForms})`);

                            if (savedCount === totalForms && callback) {
                                console.log("All responses saved, calling callback");
                                callback();
                            }
                        } catch (e) {
                            console.error(`Failed to parse response for question ${questionId}:`, text);
                            savedCount++;

                            if (savedCount === totalForms && callback) {
                                console.log("All save attempts completed (with errors), calling callback");
                                callback();
                            }
                        }
                    })
                    .catch(error => {
                        console.error(`Error saving question ${questionId}:`, error);
                        savedCount++;

                        if (savedCount === totalForms && callback) {
                            console.log("All save attempts completed (with errors), calling callback");
                            callback();
                        }
                    });
            });
        }

        // Handle View Results buttons in attempt history
        const viewResultsButtons = document.querySelectorAll('.view-results-btn');
        if (viewResultsButtons.length > 0) {
            viewResultsButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get attempt ID
                    const attemptId = this.getAttribute('data-attempt-id');
                    if (!attemptId) {
                        showAlert('danger', 'Error: Could not find attempt information.');
                        return;
                    }

                    // Show the slide container
                    document.body.style.overflow = 'hidden'; // Prevent scrolling of background
                    quizSlideContainer.classList.add('active');
                    quizSlideContainer.setAttribute('data-attempt-id', attemptId);

                    // Update header for results mode
                    document.querySelector('.quiz-timer').innerHTML = `
                <div>
                    <h4 class="mb-0">Quiz Results</h4>
                </div>
                <div>
                    <span class="badge bg-primary">Loading...</span>
                </div>
            `;

                    // Change footer buttons
                    const quizFooter = document.querySelector('.quiz-footer');
                    if (quizFooter) {
                        quizFooter.innerHTML = `
                    <button id="returnToCourseBtn" class="btn btn-coursera-outline">
                        <i class="bi bi-x-circle me-2"></i> Close Results
                    </button>
                    <div>
                        <button id="reviewQuestionsBtn" class="btn btn-coursera">
                            <i class="bi bi-search me-2"></i> Review All Questions
                        </button>
                    </div>
                `;

                        // Add event listeners to new buttons
                        document.getElementById('returnToCourseBtn').addEventListener('click', function() {
                            quizSlideContainer.classList.remove('active');
                            document.body.style.overflow = '';
                        });

                        document.getElementById('reviewQuestionsBtn').addEventListener('click', function() {
                            // Scroll to top to review all questions
                            quizContent.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        });
                    }

                    // Load the results
                    loadQuizResults(attemptId);
                });
            });
        }

        // Function to submit quiz to server
        function submitQuizToServer(attemptId) {
            console.log(`Submitting quiz, attempt ID: ${attemptId}`);

            // Show loading overlay in the quiz content area
            quizContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Calculating results...</span>
            </div>
            <p class="mt-3">Calculating your results...</p>
        </div>
    `;

            // AJAX request to submit the quiz
            fetch('../ajax/students/submit-quiz.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `attempt_id=${attemptId}`
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.text();
                })
                .then(text => {
                    console.log("Raw server response:", text);

                    try {
                        const data = JSON.parse(text);
                        console.log("Parsed response:", data);

                        if (data.success) {
                            // Instead of redirecting, load results directly
                            loadQuizResults(attemptId);

                            // Dispatch event for quiz completion
                            document.dispatchEvent(new Event('quizCompleted'));
                        } else {
                            console.error("Quiz submission failed:", data.message);
                            removeOverlay();
                            showAlert('danger', data.message || 'Failed to submit quiz. Please try again.');
                        }
                    } catch (e) {
                        console.error("Failed to parse submission response:", text, e);
                        removeOverlay();
                        showAlert('danger', 'An error occurred during quiz submission. Please try again.');
                    }
                })
                .catch(error => {
                    console.error("Error submitting quiz:", error);
                    removeOverlay();
                    showAlert('danger', 'An error occurred while submitting the quiz. Please try again.');
                });
        }
        // Function to load and display quiz results
        function loadQuizResults(attemptId) {
            console.log(`Loading quiz results for attempt ID: ${attemptId}`);

            // Update the quiz UI to indicate loading
            quizContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading results...</span>
            </div>
            <p class="mt-3">Loading your quiz results...</p>
        </div>
    `;

            // Update header/footer for results mode
            document.querySelector('.quiz-timer').innerHTML = `
        <div>
            <h4 class="mb-0">Quiz Results</h4>
        </div>
        <div>
            <span class="badge bg-primary">Attempt completed</span>
        </div>
    `;

            // Change footer buttons
            const quizFooter = document.querySelector('.quiz-footer');
            if (quizFooter) {
                quizFooter.innerHTML = `
            <button id="returnToCourseBtn" class="btn btn-coursera-outline">
                <i class="bi bi-arrow-left me-2"></i> Return to Course
            </button>
            <div>
                <button id="reviewQuestionsBtn" class="btn btn-coursera">
                    <i class="bi bi-search me-2"></i> Review All Questions
                </button>
            </div>
        `;

                // Add event listeners to new buttons
                document.getElementById('returnToCourseBtn').addEventListener('click', function() {
                    quizSlideContainer.classList.remove('active');
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                });

                document.getElementById('reviewQuestionsBtn').addEventListener('click', function() {
                    // Scroll to top to review all questions
                    quizContent.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }

            // Fetch results data
            fetch(`../ajax/students/get-quiz-results.php?attempt_id=${attemptId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Server responded with status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        displayQuizResults(data.results);
                    } else {
                        console.error("Error loading quiz results:", data.message);
                        quizContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        Failed to load quiz results: ${data.message}
                    </div>
                `;
                    }
                })
                .catch(error => {
                    console.error("Error fetching quiz results:", error);
                    quizContent.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle me-2"></i>
                    An error occurred while loading your results. Please try refreshing the page.
                </div>
            `;
                });
        }

        function loadAllQuestions(attemptId) {
            // AJAX request to get all questions
            fetch(`../ajax/students/get-all-quiz-questions.php?attempt_id=${attemptId}`)
                .then(response => response.text())
                .then(html => {
                    quizContent.innerHTML = html;

                    // Initialize question-specific functionality
                    initializeQuestionFunctionality(attemptId);

                    // NEW CODE: Check which questions are already answered in this attempt
                    fetch(`../ajax/students/get-answered-questions.php?attempt_id=${attemptId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.answered_questions) {
                                // Mark questions as answered
                                data.answered_questions.forEach(questionId => {
                                    const questionElem = quizContent.querySelector(`.quiz-question[data-question-id="${questionId}"]`);
                                    if (questionElem) {
                                        questionElem.classList.add('answered');
                                    }
                                });

                                // Update progress counter after marking questions
                                updateQuestionsProgress();
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching answered questions:', error);
                        });

                    // Remove loading overlay
                    removeOverlay();
                })
                .catch(error => {
                    console.error('Error loading questions:', error);
                    quizContent.innerHTML = '<div class="alert alert-danger">Failed to load quiz questions. Please try again.</div>';
                    removeOverlay();
                });
        }
        // Function to display quiz results in the quiz UI
        function displayQuizResults(results) {
            // Create results content
            let resultsHTML = `
        <div class="quiz-title-header">
            <h3>${results.quiz_title || 'Quiz Results'}</h3>
        </div>
        
        <div class="quiz-result-summary mb-4">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="quiz-result-chart">
                        <div class="chart-container">
                            <div class="d-flex align-items-center justify-content-center h-100">
                                <div class="text-center">
                                    <h2 class="display-4 fw-bold ${results.passed ? 'text-success' : 'text-danger'}">
                                        ${Math.round(results.score_percentage)}%
                                    </h2>
                                    <p class="mb-0">${results.passed ? 'Passed' : 'Failed'}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="h5 mb-0">${results.correct_count}/${results.total_questions}</div>
                                <div class="small text-muted">Correct Answers</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="h5 mb-0">${formatTime(results.time_taken)}</div>
                                <div class="small text-muted">Time Taken</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center">
                                <div class="h5 mb-0">${results.pass_mark}%</div>
                                <div class="small text-muted">Pass Mark</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;

            // Add questions with feedback
            resultsHTML += `<h4 class="mb-3">Review Questions</h4>`;

            results.questions.forEach((question, index) => {
                const questionNumber = index + 1;
                const isCorrect = question.is_correct;

                resultsHTML += `
            <div class="quiz-question ${isCorrect ? 'border-success' : 'border-danger'}" data-question-id="${question.id}">
                <div class="quiz-question-header">
                    <div class="d-flex align-items-center mb-2">
                        <div class="badge ${isCorrect ? 'bg-success' : 'bg-danger'} me-2">${questionNumber}</div>
                        <h5 class="mb-0">
                            ${question.question_text}
                            <span class="badge ${isCorrect ? 'bg-success' : 'bg-danger'} ms-2">
                                ${isCorrect ? 'Correct' : 'Incorrect'}
                            </span>
                        </h5>
                    </div>
                    <div class="text-muted small">
                        ${question.question_type}
                    </div>
                </div>

                <div class="quiz-question-body">
        `;

                // Display answers based on question type
                if (question.question_type === 'Multiple Choice' || question.question_type === 'True/False') {
                    resultsHTML += `<div class="quiz-options">`;

                    question.answers.forEach(answer => {
                        const isUserAnswer = answer.id == question.user_answer_id;
                        const isCorrectAnswer = answer.is_correct;

                        let optionClass = '';
                        let iconHTML = '';

                        if (isUserAnswer && isCorrectAnswer) {
                            // User selected the correct answer
                            optionClass = 'border-success bg-success bg-opacity-10';
                            iconHTML = '<i class="bi bi-check-circle-fill text-success ms-2"></i>';
                        } else if (isUserAnswer && !isCorrectAnswer) {
                            // User selected the wrong answer
                            optionClass = 'border-danger bg-danger bg-opacity-10';
                            iconHTML = '<i class="bi bi-x-circle-fill text-danger ms-2"></i>';
                        } else if (!isUserAnswer && isCorrectAnswer) {
                            // The correct answer that user didn't select
                            optionClass = 'border-success bg-success bg-opacity-10';
                            iconHTML = '<i class="bi bi-check-circle text-success ms-2"></i>';
                        }

                        resultsHTML += `
                    <div class="form-check">
                        <input class="form-check-input" type="radio" 
                               ${isUserAnswer ? 'checked' : ''} disabled>
                        <label class="form-check-label p-2 ${optionClass} d-block rounded">
                            ${answer.answer_text} ${iconHTML}
                        </label>
                    </div>
                `;
                    });

                    resultsHTML += `</div>`;
                } else if (question.question_type === 'Short_Answer' || question.question_type === 'Essay' || question.question_type === 'Fill in the Blanks') {
                    // Show the user's answer
                    const userAnswer = question.user_answer || 'No answer provided';

                    // For short answer questions, also show the correct answer
                    let correctAnswer = '';
                    if (question.question_type === 'Short_Answer' || question.question_type === 'Fill in the Blanks') {
                        const correctAnswers = question.answers.filter(a => a.is_correct).map(a => a.answer_text);
                        if (correctAnswers.length > 0) {
                            correctAnswer = `
                        <div class="mt-3">
                            <div class="fw-bold text-success">Correct Answer:</div>
                            <div class="p-2 border border-success rounded bg-success bg-opacity-10">
                                ${correctAnswers.join(' or ')}
                            </div>
                        </div>
                    `;
                        }
                    }

                    resultsHTML += `
                <div class="mb-3">
                    <div class="fw-bold">Your Answer:</div>
                    <div class="p-2 border ${isCorrect ? 'border-success' : 'border-danger'} rounded 
                                ${isCorrect ? 'bg-success' : 'bg-danger'} bg-opacity-10">
                        ${userAnswer}
                    </div>
                    ${correctAnswer}
                </div>
            `;
                }

                // Add explanation if available
                if (question.explanation) {
                    resultsHTML += `
                <div class="mt-3 p-3 bg-light border-start border-4 border-info">
                    <div class="fw-bold mb-1">Explanation:</div>
                    <div>${question.explanation}</div>
                </div>
            `;
                }

                resultsHTML += `
                </div>
            </div>
        `;
            });

            // Display the results in the quiz content area
            quizContent.innerHTML = resultsHTML;
        }

        // Helper function to format time in MM:SS format
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const remainingSeconds = seconds % 60;
            return `${minutes}:${remainingSeconds < 10 ? '0' : ''}${remainingSeconds}`;
        }

        // Direct submission function
        function submitQuizDirectly() {
            console.log("Executing direct quiz submission...");

            // Show loading overlay
            showOverlay('Submitting quiz...');

            // Get the quiz slide container and attempt ID
            const quizSlideContainer = document.getElementById('quizSlideContainer');
            const attemptId = quizSlideContainer.getAttribute('data-attempt-id');

            console.log("Attempt ID for submission:", attemptId);

            if (!attemptId) {
                console.error("No attempt ID found for submission");
                removeOverlay();
                showAlert('danger', 'Error: Could not find quiz attempt information. Please try again.');
                return;
            }

            // Close modal if it's open
            try {
                const submitQuizModal = bootstrap.Modal.getInstance(document.getElementById('submitQuizModal'));
                if (submitQuizModal) {
                    submitQuizModal.hide();
                }
            } catch (error) {
                console.log("No modal to close or error closing modal:", error);
            }

            // First save all responses
            console.log("Saving all responses before submission...");
            saveAllQuizResponses(attemptId, function() {
                // Then submit the quiz
                console.log("All responses saved, now submitting quiz...");
                submitQuizToServer(attemptId);
            });
        }

        // Results view functionality
        const quizResults = document.getElementById('quizResults');
        if (quizResults) {
            const attemptId = quizResults.getAttribute('data-attempt-id');

            // AJAX request to load quiz results
            fetch(`../includes/students/quiz-results.php?attempt_id=${attemptId}`)
                .then(response => response.text())
                .then(html => {
                    quizResults.innerHTML = html;
                })
                .catch(error => {
                    console.error('Error loading quiz results:', error);
                    quizResults.innerHTML = '<div class="alert alert-danger">Failed to load quiz results. Please try again.</div>';
                });
        }


        // Cooldown Timer
        const cooldownTimers = document.querySelectorAll('.cooldown-timer');
        if (cooldownTimers.length > 0) {
            cooldownTimers.forEach(timer => {
                const availableTime = new Date(timer.getAttribute('data-available-time'));

                const updateCooldownTimer = () => {
                    const now = new Date();
                    const diff = availableTime - now;

                    if (diff <= 0) {
                        // Cooldown is over, refresh the page
                        window.location.reload();
                        return;
                    }

                    // Calculate remaining time
                    const hours = Math.floor(diff / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

                    // Format time string
                    let timeString = '';
                    if (hours > 0) {
                        timeString = `${hours}h ${minutes}m ${seconds}s`;
                    } else if (minutes > 0) {
                        timeString = `${minutes}m ${seconds}s`;
                    } else {
                        timeString = `${seconds}s`;
                    }

                    // Update the timer display
                    timer.textContent = timeString;
                };

                // Initial update
                updateCooldownTimer();

                // Set interval to update every second
                setInterval(updateCooldownTimer, 1000);
            });
        }

        // Utility functions
        function showOverlay(message = null) {
            // Remove any existing overlay
            const existingOverlay = document.querySelector('.custom-overlay');
            if (existingOverlay) {
                existingOverlay.remove();
            }

            // Create new overlay
            const overlay = document.createElement('div');
            overlay.className = 'custom-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
            overlay.style.display = 'flex';
            overlay.style.justifyContent = 'center';
            overlay.style.alignItems = 'center';
            overlay.style.zIndex = '9999';

            overlay.innerHTML = `
            <div class="d-flex align-items-center bg-white p-3 rounded">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                ${message ? `<div class="ms-3">${message}</div>` : ''}
            </div>
        `;

            document.body.appendChild(overlay);
        }

        function removeOverlay() {
            const overlay = document.querySelector('.custom-overlay');
            if (overlay) {
                overlay.remove();
            }
        }

        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
            alertDiv.setAttribute('role', 'alert');
            alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

            // Position the alert
            alertDiv.style.position = 'fixed';
            alertDiv.style.top = '20px';
            alertDiv.style.left = '50%';
            alertDiv.style.transform = 'translateX(-50%)';
            alertDiv.style.zIndex = '9999';
            alertDiv.style.minWidth = '300px';
            alertDiv.style.maxWidth = '80%';
            alertDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';

            document.body.appendChild(alertDiv);

            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.classList.remove('show');
                    setTimeout(() => {
                        if (alertDiv.parentNode) {
                            alertDiv.parentNode.removeChild(alertDiv);
                        }
                    }, 300);
                }
            }, 5000);
        }
    });
</script>

<!-- Add this script tag at the very end of your file, after all other JavaScript -->
<script>
    // Direct implementation of quiz submission - completely separate from other code
    (function() {
        console.log("Initializing direct submit handler");

        // Get the submit button directly
        const directSubmitBtn = document.getElementById('submitQuizBtn');

        if (directSubmitBtn) {
            console.log("Submit button found, attaching direct handler");

            // Attach a direct click handler (this will override any existing handlers)
            directSubmitBtn.onclick = function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log("DIRECT HANDLER: Submit button clicked");

                // Show a simple confirmation dialog
                if (window.confirm("Are you sure you want to submit this quiz? You won't be able to change your answers after submission.")) {
                    console.log("DIRECT HANDLER: Confirmation approved, proceeding with submission");

                    // Get the quiz slide container and attempt ID
                    const quizSlideContainer = document.getElementById('quizSlideContainer');
                    const attemptId = quizSlideContainer.getAttribute('data-attempt-id');

                    if (!attemptId) {
                        alert("Error: Could not find the quiz attempt information. Please reload the page and try again.");
                        console.error("DIRECT HANDLER: No attempt ID found");
                        return;
                    }

                    console.log(`DIRECT HANDLER: Found attempt ID ${attemptId}, proceeding with submission`);

                    // Show loading overlay in the quiz content area
                    const quizContent = document.getElementById('quizContent');
                    quizContent.innerHTML = `
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Calculating results...</span>
                            </div>
                            <p class="mt-3">Calculating your results...</p>
                        </div>
                    `;

                    // Update header for results mode
                    document.querySelector('.quiz-timer').innerHTML = `
                        <div>
                            <h4 class="mb-0">Quiz Results</h4>
                        </div>
                        <div>
                            <span class="badge bg-primary">Calculating...</span>
                        </div>
                    `;

                    // Submit directly to the server
                    fetch('../ajax/students/submit-quiz.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `attempt_id=${attemptId}`
                        })
                        .then(response => {
                            console.log(`DIRECT HANDLER: Server response status: ${response.status}`);
                            return response.text();
                        })
                        .then(text => {
                            console.log("DIRECT HANDLER: Raw server response:", text);

                            try {
                                const data = JSON.parse(text);
                                console.log("DIRECT HANDLER: Parsed response:", data);

                                if (data.success) {
                                    // Instead of redirecting, load results directly
                                    console.log("DIRECT HANDLER: Loading quiz results in-place");
                                    loadQuizResults(attemptId);
                                } else {
                                    // Show error
                                    console.error("DIRECT HANDLER: Quiz submission failed:", data.message);
                                    quizContent.innerHTML = `
                                        <div class="alert alert-danger">
                                            <i class="bi bi-exclamation-circle me-2"></i>
                                            Failed to submit quiz: ${data.message || 'Unknown error'}
                                        </div>
                                    `;
                                }
                            } catch (e) {
                                console.error("DIRECT HANDLER: Error parsing response:", e);
                                quizContent.innerHTML = `
                                    <div class="alert alert-danger">
                                        <i class="bi bi-exclamation-circle me-2"></i>
                                        An error occurred while processing the server response. Please try again.
                                    </div>
                                `;
                            }
                        })
                        .catch(error => {
                            console.error("DIRECT HANDLER: Fetch error:", error);
                            quizContent.innerHTML = `
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    Could not connect to the server. Please check your connection and try again.
                                </div>
                            `;
                        });
                }
            };

            console.log("DIRECT HANDLER: Setup complete");
        } else {
            console.error("DIRECT HANDLER: Submit button not found in the DOM");
            // Try to find it again after a delay to handle delayed rendering
            setTimeout(() => {
                const retryBtn = document.getElementById('submitQuizBtn');
                if (retryBtn) {
                    console.log("DIRECT HANDLER: Submit button found after delay");
                    retryBtn.addEventListener('click', function() {
                        alert("Submit quiz button clicked!");
                    });
                }
            }, 2000);
        }
    })();
</script>