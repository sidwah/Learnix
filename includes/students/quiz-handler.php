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
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-clock fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Time Limit</h6>
                        <p class="mb-0"><?php echo $quiz['time_limit'] > 0 ? $quiz['time_limit'] . ' minutes' : 'No time limit'; ?></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-check-circle fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Pass Mark</h6>
                        <p class="mb-0"><?php echo $quiz['pass_mark']; ?>%</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded text-center">
                        <i class="bi bi-question-circle fs-3 text-primary"></i>
                        <h6 class="mt-2 mb-1">Questions</h6>
                        <p class="mb-0">TBD</p> <!-- Optional: Replace with actual count -->
                    </div>
                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary btn-lg" id="startQuizBtn" data-bs-toggle="modal" data-bs-target="#startQuizModal" data-max-attempts="5" data-current-attempts="4">
                    <i class="bi bi-play-circle me-2"></i>Start Quiz
                </button>
                <div id="cooldownTimer" class="mt-2 text-muted" style="display: none;">
                    Cooldown: <span id="cooldownSeconds">10</span>s
                </div>
            </div>
            <!-- Previous Attempts (Card Stack Style) -->
            <div class="card mt-4">
                <div class="card-header bg-light d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Previous Attempts</h5>
                    <span class="badge bg-secondary">Last 5 Attempts</span>
                </div>
                <div class="card-body">
                    <div id="attemptsList" class="d-flex flex-column gap-3">
                        <!-- Hardcoded initial attempts -->
                        <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">Attempt 4</div>
                                <small class="text-muted">April 29, 2025 - 3:30 PM</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-semibold text-success">92% <span class="badge bg-success ms-2">Passed</span></div>
                                <div class="small text-muted">18/20 · 08:41 mins</div>
                            </div>
                        </div>
                        <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">Attempt 3</div>
                                <small class="text-muted">April 29, 2025 - 12:15 PM</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-semibold text-success">78% <span class="badge bg-success ms-2">Passed</span></div>
                                <div class="small text-muted">16/20 · 10:05 mins</div>
                            </div>
                        </div>
                        <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">Attempt 2</div>
                                <small class="text-muted">April 28, 2025 - 6:45 PM</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-semibold text-danger">54% <span class="badge bg-danger ms-2">Failed</span></div>
                                <div class="small text-muted">11/20 · 09:50 mins</div>
                            </div>
                        </div>
                        <div class="border rounded p-3 bg-white shadow-sm d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">Attempt 1</div>
                                <small class="text-muted">April 28, 2025 - 1:03 PM</small>
                            </div>
                            <div class="text-end">
                                <div class="fs-5 fw-semibold text-danger">69% <span class="badge bg-danger ms-2">Failed</span></div>
                                <div class="small text-muted">13/20 · 12:20 mins</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                    Time Left: <span id="timeRemaining"><?php echo $quiz['time_limit']; ?>:00</span>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body">
            <div id="questionContainer">
                <div class="alert alert-info text-center">Click "Start Quiz" to load questions.</div>
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
            50% { opacity: 0; }
        }
        .blink {
            animation: blink 0.5s step-end infinite;
        }
    </style>
</div>