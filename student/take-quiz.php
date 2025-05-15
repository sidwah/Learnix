<?php
// Start the session
session_start();

// Include configuration file and necessary functions
require_once '../backend/config.php';
require_once '../includes/functions.php';

// Check if user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if course_id and quiz_id are provided
if (!isset($_GET['course_id']) || !isset($_GET['quiz_id'])) {
    header("Location: index.php");
    exit;
}

$course_id = intval($_GET['course_id']);
$quiz_id = intval($_GET['quiz_id']);

// Check if the user is enrolled in the course
$enrollment_sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$enrollment_stmt = $conn->prepare($enrollment_sql);
$enrollment_stmt->bind_param("ii", $user_id, $course_id);
$enrollment_stmt->execute();
$enrollment_result = $enrollment_stmt->get_result();

if ($enrollment_result->num_rows == 0) {
    // User is not enrolled, check if they are the instructor
    $instructor_sql = "SELECT c.course_id FROM courses c 
                      JOIN instructors i ON c.instructor_id = i.instructor_id 
                      WHERE i.user_id = ? AND c.course_id = ?";
    $instructor_stmt = $conn->prepare($instructor_sql);
    $instructor_stmt->bind_param("ii", $user_id, $course_id);
    $instructor_stmt->execute();
    $instructor_result = $instructor_stmt->get_result();

    if ($instructor_result->num_rows == 0) {
        // Not enrolled and not the instructor
        header("Location: course-details.php?id=$course_id&error=not_enrolled");
        exit;
    }
}

// Get quiz information
$quiz_sql = "SELECT q.*, s.title as section_title 
             FROM section_quizzes q 
             JOIN course_sections s ON q.section_id = s.section_id 
             WHERE q.quiz_id = ?";
$quiz_stmt = $conn->prepare($quiz_sql);
$quiz_stmt->bind_param("i", $quiz_id);
$quiz_stmt->execute();
$quiz_result = $quiz_stmt->get_result();

if ($quiz_result->num_rows == 0) {
    header("Location: course-details.php?id=$course_id&error=quiz_not_found");
    exit;
}

$quiz = $quiz_result->fetch_assoc();

// Check if the user has any ongoing attempt
$attempt_sql = "SELECT * FROM student_quiz_attempts 
                WHERE user_id = ? AND quiz_id = ? AND is_completed = 0 
                ORDER BY start_time DESC LIMIT 1";
$attempt_stmt = $conn->prepare($attempt_sql);
$attempt_stmt->bind_param("ii", $user_id, $quiz_id);
$attempt_stmt->execute();
$attempt_result = $attempt_stmt->get_result();

$attempt_id = null;
$time_limit_seconds = isset($quiz['time_limit']) ? $quiz['time_limit'] * 60 : 600; // Default 10 minutes
$time_remaining = $time_limit_seconds;
$end_time = null;

if ($attempt_result->num_rows > 0) {
    // Resume existing attempt
    $attempt = $attempt_result->fetch_assoc();
    $attempt_id = $attempt['attempt_id'];
    $start_time = strtotime($attempt['start_time']);
    $elapsed_time = time() - $start_time;
    $time_remaining = max(0, $time_limit_seconds - $elapsed_time);
    $end_time = $start_time + $time_limit_seconds; // Use the original start time to calculate end time
} else {
    // Create new attempt
    $new_attempt_sql = "INSERT INTO student_quiz_attempts 
                        (user_id, quiz_id, start_time, is_completed, score, passed, time_spent, attempt_number) 
                        VALUES (?, ?, NOW(), 0, 0, 0, 0, 
                        (SELECT COUNT(*) + 1 FROM (SELECT * FROM student_quiz_attempts WHERE user_id = ? AND quiz_id = ?) AS a))";
    $new_attempt_stmt = $conn->prepare($new_attempt_sql);
    $new_attempt_stmt->bind_param("iiii", $user_id, $quiz_id, $user_id, $quiz_id);
    $new_attempt_stmt->execute();
    $attempt_id = $conn->insert_id;

    // Get the newly created attempt to get the start time
    $new_attempt_sql = "SELECT * FROM student_quiz_attempts WHERE attempt_id = ?";
    $new_attempt_stmt = $conn->prepare($new_attempt_sql);
    $new_attempt_stmt->bind_param("i", $attempt_id);
    $new_attempt_stmt->execute();
    $new_attempt_result = $new_attempt_stmt->get_result();
    $attempt = $new_attempt_result->fetch_assoc();
    $start_time = strtotime($attempt['start_time']);
    $end_time = $start_time + $time_limit_seconds;
}

// Get quiz questions
$question_sql = "SELECT * FROM quiz_questions WHERE quiz_id = ?";

// Check if we should randomize questions
if (isset($quiz['randomize_questions']) && $quiz['randomize_questions']) {
    $question_sql .= " ORDER BY RAND()";

    // If there's a limit on number of questions to display
    if (isset($quiz['questions_to_display']) && $quiz['questions_to_display'] > 0) {
        $question_sql .= " LIMIT " . intval($quiz['questions_to_display']);
    }
} else {
    // Default ordering by question order or ID
    $question_sql .= " ORDER BY question_order, question_id";
}

$question_stmt = $conn->prepare($question_sql);
$question_stmt->bind_param("i", $quiz_id);
$question_stmt->execute();
$question_result = $question_stmt->get_result();

$questions = [];
while ($question = $question_result->fetch_assoc()) {
    // Get answers for this question
    $answer_sql = "SELECT * FROM quiz_answers WHERE question_id = ?";

    // Check if we should randomize answers
    if (isset($quiz['shuffle_answers']) && $quiz['shuffle_answers']) {
        $answer_sql .= " ORDER BY RAND()";
    } else {
        $answer_sql .= " ORDER BY answer_id";
    }

    $answer_stmt = $conn->prepare($answer_sql);
    $answer_stmt->bind_param("i", $question['question_id']);
    $answer_stmt->execute();
    $answer_result = $answer_stmt->get_result();

    $answers = [];
    while ($answer = $answer_result->fetch_assoc()) {
        $answers[] = $answer;
    }

    $question['answers'] = $answers;
    $questions[] = $question;
}

// Check if this is a submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_quiz'])) {
    $total_points = 0;
    $earned_points = 0;
    $question_responses = [];

    foreach ($questions as $question) {
        $question_id = $question['question_id'];
        $total_points += $question['points'];

        if ($question['question_type'] === 'Multiple Choice' || $question['question_type'] === 'True/False') {
            if (isset($_POST['question_' . $question_id])) {
                $selected_answer_id = $_POST['question_' . $question_id];

                // Find if this is the correct answer
                $is_correct = 0;
                foreach ($question['answers'] as $answer) {
                    if ($answer['answer_id'] == $selected_answer_id && $answer['is_correct']) {
                        $is_correct = 1;
                        $earned_points += $question['points'];
                        break;
                    }
                }

                // Save response
                $response_sql = "INSERT INTO student_question_responses 
                                (attempt_id, question_id, is_correct, points_awarded) 
                                VALUES (?, ?, ?, ?)";
                $response_stmt = $conn->prepare($response_sql);
                $points_awarded = $question['points'] * $is_correct;
                $response_stmt->bind_param("iiid", $attempt_id, $question_id, $is_correct, $points_awarded);
                $response_stmt->execute();
                $response_id = $conn->insert_id;

                // Save answer selection
                $selection_sql = "INSERT INTO student_answer_selections 
                                 (response_id, answer_id) 
                                 VALUES (?, ?)";
                $selection_stmt = $conn->prepare($selection_sql);
                $selection_stmt->bind_param("ii", $response_id, $selected_answer_id);
                $selection_stmt->execute();

                $question_responses[$question_id] = [
                    'selected_answer' => $selected_answer_id,
                    'is_correct' => $is_correct
                ];
            }
        }
        // Add handlers for other question types if needed
    }

    // Calculate score as percentage
    $score = ($total_points > 0) ? ($earned_points / $total_points) * 100 : 0;
    $passed = ($score >= $quiz['pass_mark']);

    // Update attempt
    $update_attempt_sql = "UPDATE student_quiz_attempts 
                          SET end_time = NOW(), is_completed = 1, score = ?, passed = ?, 
                          time_spent = TIMESTAMPDIFF(SECOND, start_time, NOW()) 
                          WHERE attempt_id = ?";
    $update_attempt_stmt = $conn->prepare($update_attempt_sql);
    $update_attempt_stmt->bind_param("dii", $score, $passed, $attempt_id);
    $update_attempt_stmt->execute();

    // Redirect to results page
    header("Location: quiz-results.php?course_id=$course_id&quiz_id=$quiz_id&attempt_id=$attempt_id");
    exit;
}

// Auto-submit if time has already expired
if ($time_remaining <= 0) {
    // Time's up, auto-submit
    $update_attempt_sql = "UPDATE student_quiz_attempts 
                          SET end_time = DATE_ADD(start_time, INTERVAL ? SECOND), 
                          is_completed = 1, score = 0, passed = 0, 
                          time_spent = ? 
                          WHERE attempt_id = ?";
    $update_attempt_stmt = $conn->prepare($update_attempt_sql);
    $update_attempt_stmt->bind_param("iii", $time_limit_seconds, $time_limit_seconds, $attempt_id);
    $update_attempt_stmt->execute();

    // Redirect to results page
    header("Location: quiz-results.php?course_id=$course_id&quiz_id=$quiz_id&attempt_id=$attempt_id&timeout=1");
    exit;
}

// Include header
include '../includes/student-header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Quiz Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h5>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($quiz['section_title']); ?></p>
                        </div>
                        <div id="timer" class="badge bg-primary px-3 py-2" data-end-time="<?php echo $end_time; ?>">
                            Time remaining: <span id="minutes"></span>:<span id="seconds"></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p><strong>Instructions:</strong> <?php echo !empty($quiz['instruction']) ? htmlspecialchars($quiz['instruction']) : "Answer all questions to the best of your ability."; ?></p>
                        <div class="alert alert-info">
                            <div><strong>Time Limit:</strong> <?php echo isset($quiz['time_limit']) ? $quiz['time_limit'] . ' minutes' : 'No time limit'; ?></div>
                            <div><strong>Pass Mark:</strong> <?php echo $quiz['pass_mark']; ?>%</div>
                            <div><strong>Questions:</strong> <?php echo count($questions); ?></div>
                            <?php if (isset($quiz['attempts_allowed'])): ?>
                                <div><strong>Attempts Allowed:</strong> <?php echo $quiz['attempts_allowed']; ?></div>
                            <?php endif; ?>
                            <?php if (isset($attempt['attempt_number'])): ?>
                                <div><strong>Current Attempt:</strong> #<?php echo $attempt['attempt_number']; ?></div>
                            <?php endif; ?>
                        </div>

                        <?php if (isset($quiz['randomize_questions']) && $quiz['randomize_questions']): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-shuffle"></i> Questions are randomly ordered for each attempt.
                            </div>
                        <?php endif; ?>
                    </div>

                    <form id="quizForm" method="post" action="">
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-4 p-3 border rounded">
                                <h5 class="mb-3">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></h5>

                                <?php if ($question['question_type'] === 'Multiple Choice'): ?>
                                    <div class="mb-3">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="question_<?php echo $question['question_id']; ?>"
                                                    id="answer_<?php echo $answer['answer_id']; ?>"
                                                    value="<?php echo $answer['answer_id']; ?>">
                                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php elseif ($question['question_type'] === 'True/False'): ?>
                                    <div class="mb-3">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="radio"
                                                    name="question_<?php echo $question['question_id']; ?>"
                                                    id="answer_<?php echo $answer['answer_id']; ?>"
                                                    value="<?php echo $answer['answer_id']; ?>">
                                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <!-- Add more question types here if needed -->
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-secondary">Back to Course</a>
                            <button type="submit" name="submit_quiz" class="btn btn-primary">Submit Quiz</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Quiz timer
    function updateTimer() {
        const timerElement = document.getElementById('timer');
        const endTime = parseInt(timerElement.getAttribute('data-end-time')) * 1000; // Convert to milliseconds
        const now = new Date().getTime();
        const timeLeft = endTime - now;

        if (timeLeft <= 0) {
            // Time's up, auto-submit the form
            document.getElementById('quizForm').submit();
            return;
        }

        // Calculate minutes and seconds
        const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

        // Display the timer
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

        // Update color based on time remaining
        if (timeLeft < 60000) { // less than 1 minute
            timerElement.classList.remove('bg-primary', 'bg-warning', 'text-dark');
            timerElement.classList.add('bg-danger');
        } else if (timeLeft < 300000) { // less than 5 minutes
            timerElement.classList.remove('bg-primary', 'bg-danger');
            timerElement.classList.add('bg-warning', 'text-dark');
        }
    }

    // Update timer every second
    updateTimer();
    setInterval(updateTimer, 1000);

    // Save user's progress periodically
    function saveProgress() {
        const form = document.getElementById('quizForm');
        const formData = new FormData(form);

        // Add an identifier to indicate this is just a progress save, not a final submission
        formData.append('save_progress', '1');

        fetch(form.action, {
            method: 'POST',
            body: formData
        }).catch(error => {
            console.error('Error saving progress:', error);
        });
    }

    // Save progress every 30 seconds
    setInterval(saveProgress, 30000);

    // Form validation before submission
    document.getElementById('quizForm').addEventListener('submit', function(event) {
        let allAnswered = true;
        const radioGroups = this.querySelectorAll('input[type="radio"]');
        const questionIds = new Set();

        // Collect all question IDs
        radioGroups.forEach(radio => {
            const name = radio.getAttribute('name');
            questionIds.add(name);
        });

        // Check if all questions are answered
        questionIds.forEach(name => {
            const answered = document.querySelector(`input[name="${name}"]:checked`);
            if (!answered) {
                allAnswered = false;
            }
        });

        if (!allAnswered) {
            if (!confirm('Some questions are not answered. Are you sure you want to submit?')) {
                event.preventDefault();
            }
        }
    });

    // Prevent accidental navigation away
    window.addEventListener('beforeunload', function(e) {
        // Cancel the event
        e.preventDefault();
        // Chrome requires returnValue to be set
        e.returnValue = '';
    });

    // Record selected answers in localStorage to restore on refresh
    function saveAnswersToLocalStorage() {
        const answers = {};
        const radioGroups = document.querySelectorAll('input[type="radio"]:checked');

        radioGroups.forEach(radio => {
            const name = radio.getAttribute('name');
            const value = radio.value;
            answers[name] = value;
        });

        localStorage.setItem('quiz_<?php echo $attempt_id; ?>_answers', JSON.stringify(answers));
    }

    // Save answers when they're selected
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', saveAnswersToLocalStorage);
    });

    // Restore answers from localStorage on page load
    function restoreAnswersFromLocalStorage() {
        const savedAnswers = localStorage.getItem('quiz_<?php echo $attempt_id; ?>_answers');

        if (savedAnswers) {
            const answers = JSON.parse(savedAnswers);

            for (const name in answers) {
                const value = answers[name];
                const radio = document.querySelector(`input[name="${name}"][value="${value}"]`);

                if (radio) {
                    radio.checked = true;
                }
            }
        }
    }

    // Restore saved answers when page loads
    document.addEventListener('DOMContentLoaded', restoreAnswersFromLocalStorage);
</script>

<?php
// Include footer
include '../includes/student-footer.php';
?>