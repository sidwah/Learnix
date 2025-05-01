<?php
// includes/student/quiz-handler.php

// Assumes session and config are already included in course-content.php
if (!isset($quiz_id) || !isset($user_id) || !isset($course_id)) {
    die("Required parameters are missing.");
}

// Fetch quiz details
$quiz_query = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();

if ($quiz_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Quiz not found</div>';
    return;
}
$quiz = $quiz_result->fetch_assoc();

// Fetch total number of attempts for current attempts
$attempt_count_query = "SELECT COUNT(*) as attempt_count 
                       FROM student_quiz_attempts 
                       WHERE user_id = ? AND quiz_id = ?";
$stmt = $conn->prepare($attempt_count_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$attempt_count_result = $stmt->get_result();
$current_attempts = $attempt_count_result->fetch_assoc()['attempt_count'];

// Fetch previous attempts (last 5 for display)
$attempts_query = "SELECT attempt_id, attempt_number, score, passed, time_spent, start_time 
                   FROM student_quiz_attempts 
                   WHERE user_id = ? AND quiz_id = ? 
                   ORDER BY attempt_number DESC 
                   LIMIT 5";
$stmt = $conn->prepare($attempts_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$attempts_result = $stmt->get_result();
$attempts = $attempts_result->fetch_all(MYSQLI_ASSOC);

// Set max attempts from quiz data
$max_attempts = $quiz['attempts_allowed'];

// Fetch question count
$question_count_query = "SELECT COUNT(*) as question_count 
                        FROM quiz_questions 
                        WHERE quiz_id = ?";
$stmt = $conn->prepare($question_count_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$question_count_result = $stmt->get_result();
$question_count = $question_count_result->fetch_assoc()['question_count'];

// Check for active (incomplete) attempt
$active_attempt_query = "SELECT attempt_id, start_time, time_spent 
                        FROM student_quiz_attempts 
                        WHERE user_id = ? AND quiz_id = ? AND is_completed = 0 
                        ORDER BY start_time DESC LIMIT 1";
$stmt = $conn->prepare($active_attempt_query);
$stmt->bind_param("ii", $user_id, $quiz_id);
$stmt->execute();
$active_attempt_result = $stmt->get_result();
$active_attempt = $active_attempt_result->num_rows > 0 ? $active_attempt_result->fetch_assoc() : null;

// Calculate remaining time for active attempt
$remaining_time = null;
if ($active_attempt && $quiz['time_limit'] > 0) {
    $start_time = strtotime($active_attempt['start_time']);
    $time_limit_seconds = $quiz['time_limit'] * 60;
    $elapsed_time = time() - $start_time;
    $remaining_time = $time_limit_seconds - $elapsed_time;
    
    if ($remaining_time <= 0) {
        // Placeholder: Auto-submit expired attempt and send notification
        /*
        UPDATE student_quiz_attempts 
        SET is_completed = 1, end_time = NOW(), 
            time_spent = ?, score = COALESCE((
                SELECT SUM(points_awarded) 
                FROM student_question_responses 
                WHERE attempt_id = ? AND points_awarded IS NOT NULL
            ), 0)
        WHERE attempt_id = ?;

        INSERT INTO user_notifications (user_id, notification_type, message, is_read, created_at)
        VALUES (?, 'quiz_submission', 
                CONCAT('Your quiz attempt for ', ?, ' has been automatically submitted due to time expiration with a score of ', ?), 
                0, NOW());
        */
        $active_attempt = null; // Clear active attempt
    }
}
?>

<!-- Quiz UI -->
<div class="quiz-cont">
    <!-- Quiz Overview Card -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h3 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
        </div>
        <div class="card-body">
            <?php if (!empty($quiz['instruction'])): ?>
                <div class="mb-4">
                    <h5><i class="bi bi-info-circle me-2"></i>Instructions</h5>
                    <p class="text-muted"><?php echo nl2br(htmlspecialchars($quiz['instruction'])); ?></p>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-clock fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Time Limit</h6>
                        <p class="mb-0"><?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' minutes' : 'No time limit'; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-check-circle fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Pass Mark</h6>
                        <p class="mb-0"><?php echo $quiz['pass_mark']; ?>%</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-question-circle fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Questions</h6>
                        <p class="mb-0"><?php echo $question_count; ?></p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-arrow-repeat fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Attempts</h6>
                        <p class="mb-0"><?php echo $current_attempts . ' of ' . $max_attempts; ?></p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <?php if ($active_attempt && $remaining_time > 0): ?>
                    <div class="mb-3 text-muted">
                        Active Attempt: <span id="remainingTime"><?php echo gmdate('i:s', $remaining_time); ?></span> remaining
                    </div>
                    <button class="btn btn-success btn-lg me-2" id="resumeQuizBtn" data-bs-toggle="modal" data-bs-target="#resumeQuizModal" data-attempt-id="<?php echo $active_attempt['attempt_id']; ?>" data-remaining-time="<?php echo $remaining_time; ?>">
                        <i class="bi bi-play-circle me-2"></i>Resume Quiz
                    </button>
                    <button class="btn btn-danger btn-lg" id="forfeitQuizBtn" data-bs-toggle="modal" data-bs-target="#forfeitQuizModal" data-attempt-id="<?php echo $active_attempt['attempt_id']; ?>">
                        <i class="bi bi-x-circle me-2"></i>Forfeit Quiz
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary btn-lg" id="startQuizBtn" data-bs-toggle="modal" data-bs-target="#startQuizModal" data-max-attempts="<?php echo $max_attempts; ?>" data-current-attempts="<?php echo $current_attempts; ?>">
                        <i class="bi bi-play-circle me-2"></i>Start Quiz
                    </button>
                <?php endif; ?>
                <div id="cooldownTimer" class="mt-2 text-muted" style="display: none;">
                    Cooldown: <span id="cooldownSeconds">10</span>s
                </div>
            </div>

            <!-- Include Previous Attempts -->
            <?php include 'previous-attempts.php'; ?>
        </div>
    </div>

    <!-- Start Quiz Modal -->
    <div class="modal fade" id="startQuizModal" tabindex="-1" aria-labelledby="startQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Start <?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you ready to start the quiz?</p>
                    <ul class="list-unstyled">
                        <?php if ($quiz['time_limit'] > 0): ?>
                            <li><i class="bi bi-clock me-2"></i><?php echo $quiz['time_limit']; ?> min limit</li>
                        <?php endif; ?>
                        <li><i class="bi bi-check-circle me-2"></i><?php echo $quiz['pass_mark']; ?>% to pass</li>
                        <li><i class="bi bi-exclamation-circle me-2"></i>No pausing or rewinding allowed</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="confirmStartQuiz"><i class="bi bi-play-circle me-2"></i>Start Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Resume Quiz Modal -->
    <div class="modal fade" id="resumeQuizModal" tabindex="-1" aria-labelledby="resumeQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="resumeQuizModalLabel">Resume <?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>You have an active quiz attempt with <span id="modalRemainingTime"><?php echo $remaining_time ? gmdate('i:s', $remaining_time) : '0:00'; ?></span> remaining.</p>
                    <p>Would you like to resume where you left off?</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-exclamation-circle me-2"></i>Time will continue counting down immediately.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-success" id="confirmResumeQuiz"><i class="bi bi-play-circle me-2"></i>Resume Now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forfeit Quiz Modal -->
    <div class="modal fade" id="forfeitQuizModal" tabindex="-1" aria-labelledby="forfeitQuizModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forfeitQuizModalLabel">Forfeit <?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to forfeit this quiz attempt?</p>
                    <p>Your attempt will be submitted with the score based on answers provided so far.</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-exclamation-circle me-2"></i>This action cannot be undone.</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-danger" id="confirmForfeitQuiz"><i class="bi bi-x-circle me-2"></i>Submit and Forfeit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Submission Modal -->
    <div class="modal fade" id="confirmSubmitModal" tabindex="-1" aria-labelledby="confirmSubmitLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmSubmitLabel">Submit Quiz</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to submit this quiz?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Yes, Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Quiz Questions Area -->
    <div id="quizQuestions" class="card shadow-sm mt-4" style="display: none;">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h3>
            <div id="quizTimer" class="fs-5">
                <?php if ($quiz['time_limit'] > 0): ?>
                    Time Left: <span id="timeRemaining"><?php echo $active_attempt ? gmdate('i:s', $remaining_time) : ($quiz['time_limit'] . ':00'); ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div id="questionContainer">
                <div class="alert alert-info text-center">Click "Start Quiz" or "Resume Quiz" to load questions.</div>
            </div>
            <div class="text-end mt-4" id="submitButtonWrapper" style="display: block;">
                <button class="btn btn-primary" id="submitQuiz" data-bs-toggle="modal" data-bs-target="#confirmSubmitModal">
                    <i class="bi bi-send me-2"></i>Submit Quiz
                </button>
            </div>
        </div>
    </div>

    <!-- Blinking CSS for Attempt Reset -->
    <style>
        @keyframes blink {
            50% {
                opacity: 0;
            }
        }

        .blink {
            animation: blink 0.5s step-end infinite;
        }
    </style>
</div>