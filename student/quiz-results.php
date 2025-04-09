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

// Check if required parameters are provided
if (!isset($_GET['course_id']) || !isset($_GET['quiz_id'])) {
    header("Location: dashboard.php");
    exit;
}

$course_id = intval($_GET['course_id']);
$quiz_id = intval($_GET['quiz_id']);

// Get attempt_id if provided, otherwise get the latest attempt
$attempt_id = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : null;

if (!$attempt_id) {
    $latest_attempt_sql = "SELECT attempt_id FROM student_quiz_attempts 
                          WHERE user_id = ? AND quiz_id = ? AND is_completed = 1 
                          ORDER BY end_time DESC LIMIT 1";
    $latest_attempt_stmt = $conn->prepare($latest_attempt_sql);
    $latest_attempt_stmt->bind_param("ii", $user_id, $quiz_id);
    $latest_attempt_stmt->execute();
    $latest_attempt_result = $latest_attempt_stmt->get_result();
    
    if ($latest_attempt_result->num_rows > 0) {
        $latest_attempt = $latest_attempt_result->fetch_assoc();
        $attempt_id = $latest_attempt['attempt_id'];
    } else {
        header("Location: course-materials.php?course_id=$course_id&error=no_attempts");
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
    header("Location: course-materials.php?course_id=$course_id&error=quiz_not_found");
    exit;
}

$quiz = $quiz_result->fetch_assoc();

// Get attempt information
$attempt_sql = "SELECT * FROM student_quiz_attempts WHERE attempt_id = ? AND user_id = ?";
$attempt_stmt = $conn->prepare($attempt_sql);
$attempt_stmt->bind_param("ii", $attempt_id, $user_id);
$attempt_stmt->execute();
$attempt_result = $attempt_stmt->get_result();

if ($attempt_result->num_rows == 0) {
    header("Location: course-materials.php?course_id=$course_id&error=attempt_not_found");
    exit;
}

$attempt = $attempt_result->fetch_assoc();

// Get all attempts by this user for this quiz
$all_attempts_sql = "SELECT * FROM student_quiz_attempts 
                    WHERE user_id = ? AND quiz_id = ? AND is_completed = 1 
                    ORDER BY end_time DESC";
$all_attempts_stmt = $conn->prepare($all_attempts_sql);
$all_attempts_stmt->bind_param("ii", $user_id, $quiz_id);
$all_attempts_stmt->execute();
$all_attempts_result = $all_attempts_stmt->get_result();

$all_attempts = [];
while ($attempt_row = $all_attempts_result->fetch_assoc()) {
    $all_attempts[] = $attempt_row;
}

// Get quiz questions with responses
$question_sql = "SELECT q.*, qr.is_correct, qr.points_awarded, qr.response_id 
                FROM quiz_questions q 
                LEFT JOIN student_question_responses qr ON q.question_id = qr.question_id AND qr.attempt_id = ? 
                WHERE q.quiz_id = ? 
                ORDER BY q.question_order, q.question_id";
$question_stmt = $conn->prepare($question_sql);
$question_stmt->bind_param("ii", $attempt_id, $quiz_id);
$question_stmt->execute();
$question_result = $question_stmt->get_result();

$questions = [];
$correct_count = 0;
$total_questions = 0;

while ($question = $question_result->fetch_assoc()) {
    $total_questions++;
    if ($question['is_correct']) {
        $correct_count++;
    }
    
    // Get all answers for this question
    $answer_sql = "SELECT a.*, (
                    SELECT COUNT(*) FROM student_answer_selections 
                    WHERE response_id = ? AND answer_id = a.answer_id
                   ) as selected 
                   FROM quiz_answers a 
                   WHERE a.question_id = ? 
                   ORDER BY a.answer_id";
    $answer_stmt = $conn->prepare($answer_sql);
    $answer_stmt->bind_param("ii", $question['response_id'], $question['question_id']);
    $answer_stmt->execute();
    $answer_result = $answer_stmt->get_result();
    
    $answers = [];
    while ($answer = $answer_result->fetch_assoc()) {
        $answers[] = $answer;
    }
    
    $question['answers'] = $answers;
    $questions[] = $question;
}

// Calculate stats
$score = $attempt['score'];
$passed = $attempt['passed'];
$time_spent = formatTime($attempt['time_spent']);

// Format dates
$start_time = new DateTime($attempt['start_time']);
$end_time = new DateTime($attempt['end_time']);
$attempt_date = $start_time->format('F j, Y');
$attempt_start = $start_time->format('g:i A');
$attempt_end = $end_time->format('g:i A');

// Include header
include '../includes/student-header.php';

// Helper function to format time
function formatTime($seconds) {
    $minutes = floor($seconds / 60);
    $secs = $seconds % 60;
    return sprintf('%d min %d sec', $minutes, $secs);
}
?>

<div class="container py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <!-- Quiz Results -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0"><?php echo htmlspecialchars($quiz['quiz_title']); ?> - Results</h5>
                            <p class="text-muted small mb-0"><?php echo htmlspecialchars($quiz['section_title']); ?></p>
                        </div>
                        <div>
                            <span class="badge <?php echo $passed ? 'bg-success' : 'bg-danger'; ?> px-3 py-2">
                                <?php echo $passed ? 'Passed' : 'Failed'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Score Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Score</h6>
                                <h2 class="mb-0 <?php echo $passed ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($score, 1); ?>%
                                </h2>
                                <p class="small text-muted mb-0">Pass mark: <?php echo $quiz['pass_mark']; ?>%</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3 mb-md-0">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Correct Answers</h6>
                                <h2 class="mb-0 text-primary"><?php echo $correct_count; ?>/<?php echo $total_questions; ?></h2>
                                <p class="small text-muted mb-0"><?php echo $total_questions > 0 ? number_format(($correct_count / $total_questions) * 100, 1) : 0; ?>% accuracy</p>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="p-3 bg-light rounded">
                                <h6 class="text-muted mb-1">Time Spent</h6>
                                <h2 class="mb-0 text-primary"><?php echo $time_spent; ?></h2>
                                <p class="small text-muted mb-0"><?php echo $attempt_date; ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Previous Attempts -->
                    <?php if (count($all_attempts) > 1): ?>
                        <div class="mb-4">
                            <h5 class="mb-3">Previous Attempts</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Attempt</th>
                                            <th>Date</th>
                                            <th>Score</th>
                                            <th>Result</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_attempts as $idx => $att): ?>
                                            <tr class="<?php echo $att['attempt_id'] == $attempt_id ? 'table-primary' : ''; ?>">
                                                <td>#<?php echo $att['attempt_number']; ?></td>
                                                <td><?php echo (new DateTime($att['start_time']))->format('M j, Y g:i A'); ?></td>
                                                <td><?php echo number_format($att['score'], 1); ?>%</td>
                                                <td>
                                                    <span class="badge <?php echo $att['passed'] ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo $att['passed'] ? 'Pass' : 'Fail'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($att['attempt_id'] != $attempt_id): ?>
                                                        <a href="quiz-results.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz_id; ?>&attempt_id=<?php echo $att['attempt_id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Current</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Question Review -->
                    <div class="mb-4">
                        <h5 class="mb-3">Question Review</h5>
                        
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="mb-4 p-3 border rounded <?php echo $question['is_correct'] ? 'border-success' : 'border-danger'; ?>">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></h6>
                                    <span class="badge <?php echo $question['is_correct'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $question['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                    </span>
                                </div>
                                
                                <?php if ($question['question_type'] === 'Multiple Choice' || $question['question_type'] === 'True/False'): ?>
                                    <div class="mb-3">
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="form-check mb-2 <?php 
                                                if ($answer['selected'] && $answer['is_correct']) echo 'text-success';
                                                else if ($answer['selected'] && !$answer['is_correct']) echo 'text-danger';
                                                else if (!$answer['selected'] && $answer['is_correct']) echo 'text-success';
                                            ?>">
                                                <input class="form-check-input" type="radio" 
                                                    disabled 
                                                    <?php echo $answer['selected'] ? 'checked' : ''; ?>
                                                    name="question_<?php echo $question['question_id']; ?>" 
                                                    id="answer_<?php echo $answer['answer_id']; ?>">
                                                <label class="form-check-label" for="answer_<?php echo $answer['answer_id']; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                    <?php if ($answer['is_correct']): ?>
                                                        <i class="bi bi-check-circle-fill text-success ms-1"></i>
                                                    <?php endif; ?>
                                                    <?php if ($answer['selected'] && !$answer['is_correct']): ?>
                                                        <i class="bi bi-x-circle-fill text-danger ms-1"></i>
                                                    <?php endif; ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($question['explanation'])): ?>
                                    <div class="alert alert-info">
                                        <strong>Explanation:</strong> <?php echo htmlspecialchars($question['explanation']); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-secondary">Back to Course</a>
                        
                        <?php 
                        // Check if retakes are allowed
                        $max_attempts = isset($quiz['attempts_allowed']) ? $quiz['attempts_allowed'] : null;
                        $can_retake = ($max_attempts === null || $attempt['attempt_number'] < $max_attempts);
                        ?>
                        
                        <?php if ($can_retake): ?>
                            <a href="take-quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz_id; ?>" class="btn btn-primary">
                                Retake Quiz
                            </a>
                        <?php else: ?>
                            <button class="btn btn-secondary" disabled>Maximum attempts reached</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include '../includes/student-footer.php';
?>
