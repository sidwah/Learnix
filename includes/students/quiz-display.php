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

// Determine if student can take the quiz
$canTakeQuiz = ($quiz['attempts_allowed'] > $attemptCount || $quiz['attempts_allowed'] == 0) && (!$quiz['is_required'] || !$hasPassed);
$attemptsRemaining = $quiz['attempts_allowed'] == 0 ? 'Unlimited' : ($quiz['attempts_allowed'] - $attemptCount);

// Process quiz state
$quizState = 'intro';
if ($activeSession && !$activeSession['is_completed']) {
    $quizState = 'resume';
} elseif (isset($_GET['attempt_id']) && isset($_GET['view']) && $_GET['view'] == 'results') {
    $quizState = 'results';
} elseif (!$canTakeQuiz && $attemptCount > 0) {
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
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
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
                                
                                <?php if ($attemptCount > 0 && $canTakeQuiz): ?>
                                    <p class="mb-0 small text-muted">
                                        You have <?php echo $attemptsRemaining; ?> attempt(s) remaining
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($canTakeQuiz): ?>
                        <button type="button" class="btn btn-coursera start-quiz-btn w-100">
                            <i class="bi bi-play-circle me-2"></i> Start Quiz
                        </button>
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
                                            <a href="?course_id=<?php echo $_GET['course_id']; ?>&<?php echo isset($_GET['topic']) ? 'topic=' . $_GET['topic'] : 'quiz_id=' . $quizId; ?>&attempt_id=<?php echo $attempt['attempt_id']; ?>&view=results"
                                                class="btn btn-sm btn-outline-primary">
                                                View Results
                                            </a>
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
                        <?php if ($hasPassed): ?>
                            <strong>Congratulations!</strong> You have successfully passed this quiz with a score of <?php echo number_format($highestScore, 1); ?>%.
                        <?php else: ?>
                            You have used all available attempts for this quiz. Your highest score was <?php echo number_format($highestScore, 1); ?>%.
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-3 mt-4">
                        <a href="?course_id=<?php echo $_GET['course_id']; ?>&<?php echo isset($_GET['topic']) ? 'topic=' . $_GET['topic'] : (isset($quiz['topic_id']) ? 'topic=' . $quiz['topic_id'] : ''); ?>&attempt_id=<?php echo $lastAttemptId; ?>&view=results"
                            class="btn btn-coursera">
                            <i class="bi bi-bar-chart me-2"></i> View Latest Results
                        </a>

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
                                            <a href="?course_id=<?php echo $_GET['course_id']; ?>&<?php echo isset($_GET['topic']) ? 'topic=' . $_GET['topic'] : 'quiz_id=' . $quizId; ?>&attempt_id=<?php echo $attempt['attempt_id']; ?>&view=results"
                                                class="btn btn-sm btn-outline-primary">
                                                View Results
                                            </a>
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

<!-- JavaScript for quiz functionality will be implemented separately -->

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
        if (resumeQuizBtn) {
            resumeQuizBtn.addEventListener('click', function() {
                // Show loading overlay
                showOverlay('Resuming quiz...');

                // Get session information
                const sessionId = this.getAttribute('data-session-id');
                const attemptId = this.getAttribute('data-attempt-id');

                // AJAX request to get quiz data
                fetch(`../ajax/students/resume-quiz-session.php?session_id=${sessionId}&attempt_id=${attemptId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Load quiz interface
                            loadQuizInterface(data);

                            // Dispatch event that quiz has resumed
                            document.dispatchEvent(new Event('quizResumed'));
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

        // Abandon Quiz button
        if (abandonQuizBtn) {
            abandonQuizBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to abandon this quiz attempt? This action cannot be undone.')) {
                    // Show loading overlay
                    showOverlay('Abandoning quiz...');

                    // Get session information
                    const sessionId = this.getAttribute('data-session-id');
                    const attemptId = this.getAttribute('data-attempt-id');

                    // AJAX request to abandon the quiz
                    fetch('../ajax/students/abandon-quiz-session.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `session_id=${sessionId}&attempt_id=${attemptId}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Reload the page to show the intro screen
                                window.location.reload();
                            } else {
                                removeOverlay();
                                showAlert('danger', data.message || 'Failed to abandon quiz. Please try again.');
                            }
                        })
                        .catch(error => {
                            console.error('Error abandoning quiz:', error);
                            removeOverlay();
                            showAlert('danger', 'An error occurred while abandoning the quiz. Please try again.');
                        });
                }
            });
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
        function loadQuizInterface(data) {
            // Set attempt ID on the slide container
            quizSlideContainer.setAttribute('data-attempt-id', data.attempt_id);

            // Show the slide container
            document.body.style.overflow = 'hidden'; // Prevent scrolling of background
            quizSlideContainer.classList.add('active');

            // Initialize timer if applicable
            if (data.end_time) {
                initializeTimer(data.end_time);
            }

            // Load all questions
            loadAllQuestions(data.attempt_id);

            // Hide the loading overlay
            removeOverlay();
        }

        // Function to initialize the timer
        function initializeTimer(endTime) {
            // Calculate the time remaining
            const endTimeDate = new Date(endTime);
            let remainingTime = endTimeDate - new Date();

            // Update the timer display initially
            updateTimerDisplay(remainingTime);

            // Update the timer every second
            const timerInterval = setInterval(function() {
                remainingTime -= 1000; // Decrease by one second

                if (remainingTime <= 0) {
                    // Time's up
                    clearInterval(timerInterval);
                    quizTimerDisplay.textContent = '00:00';
                    quizTimerDisplay.classList.add('danger');

                    // Auto-submit the quiz
                    document.getElementById('confirmSubmitQuiz').click();
                    return;
                }

                updateTimerDisplay(remainingTime);
            }, 1000);

            // Function to update timer display
            function updateTimerDisplay(time) {
                const hours = Math.floor(time / (1000 * 60 * 60));
                const minutes = Math.floor((time % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((time % (1000 * 60)) / 1000);

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

                // Warning colors
                quizTimerDisplay.classList.remove('warning', 'danger');
                if (time < 60000) { // Less than 1 minute
                    quizTimerDisplay.classList.add('danger');
                } else if (time < 300000) { // Less than 5 minutes
                    quizTimerDisplay.classList.add('warning');
                }
            }

            // Store interval in window to clear it if needed
            window.timerInterval = timerInterval;
        }

        // Function to initialize question-specific functionality
        function initializeQuestionFunctionality(attemptId) {
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

        // Function to submit quiz to server
        function submitQuizToServer(attemptId) {
            console.log(`Submitting quiz, attempt ID: ${attemptId}`);

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
                            // Redirect to results page
                            const courseId = new URLSearchParams(window.location.search).get('course_id');
                            const topicId = new URLSearchParams(window.location.search).get('topic');

                            console.log(`Quiz submitted successfully. Redirecting to results: course_id=${courseId}, topic=${topicId}, attempt_id=${attemptId}`);

                            // Dispatch event for quiz completion
                            document.dispatchEvent(new Event('quizCompleted'));

                            // Redirect to results page
                            window.location.href = `?course_id=${courseId}&topic=${topicId}&attempt_id=${attemptId}&view=results`;
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

                    // Show a simple loading message
                    const loaderDiv = document.createElement('div');
                    loaderDiv.style.position = 'fixed';
                    loaderDiv.style.top = '0';
                    loaderDiv.style.left = '0';
                    loaderDiv.style.width = '100%';
                    loaderDiv.style.height = '100%';
                    loaderDiv.style.backgroundColor = 'rgba(0,0,0,0.5)';
                    loaderDiv.style.display = 'flex';
                    loaderDiv.style.justifyContent = 'center';
                    loaderDiv.style.alignItems = 'center';
                    loaderDiv.style.zIndex = '10000';
                    loaderDiv.innerHTML = '<div style="background: white; padding: 20px; border-radius: 5px;"><p>Submitting quiz...</p></div>';
                    document.body.appendChild(loaderDiv);

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
                                    // Redirect to results page
                                    const courseId = new URLSearchParams(window.location.search).get('course_id');
                                    const topicId = new URLSearchParams(window.location.search).get('topic');

                                    const redirectUrl = `?course_id=${courseId}&topic=${topicId}&attempt_id=${attemptId}&view=results`;
                                    console.log(`DIRECT HANDLER: Redirecting to: ${redirectUrl}`);

                                    // Force redirect
                                    window.location.href = redirectUrl;
                                } else {
                                    // Show error
                                    loaderDiv.remove();
                                    alert(`Error: ${data.message || 'Failed to submit quiz. Please try again.'}`);
                                }
                            } catch (e) {
                                console.error("DIRECT HANDLER: Error parsing response:", e);
                                loaderDiv.remove();
                                alert("Error: The server response was invalid. Please try again.");
                            }
                        })
                        .catch(error => {
                            console.error("DIRECT HANDLER: Fetch error:", error);
                            loaderDiv.remove();
                            alert("Error: Could not connect to the server. Please check your connection and try again.");
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