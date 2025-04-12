<?php
session_start();
/**
 * Quiz Results Component
 * 
 * This file displays the results of a completed quiz attempt.
 * 
 * @package Learnix
 * @subpackage Students
 */

// Prevent direct access
if (!defined('LEARNIX_CORE')) {
    define('LEARNIX_CORE', true);
}

// Include database connection
require_once '../../backend/config.php';

// Get attempt ID
$attemptId = isset($_GET['attempt_id']) ? intval($_GET['attempt_id']) : 0;

if (!$attemptId) {
    echo '<div class="alert alert-danger">Invalid attempt ID.</div>';
    return;
}

// Get user ID from session
if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-danger">You must be logged in to view quiz results.</div>';
    return;
}

// Get attempt details
$stmt = $conn->prepare("
    SELECT a.*, q.quiz_title, q.pass_mark, q.show_correct_answers, 
           (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.quiz_id) as total_questions
    FROM student_quiz_attempts a
    JOIN section_quizzes q ON a.quiz_id = q.quiz_id
    WHERE a.attempt_id = ? AND a.user_id = ?
");
$stmt->bind_param("ii", $attemptId, $_SESSION['user_id']);
$stmt->execute();
$attempt = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$attempt) {
    echo '<div class="alert alert-danger">Attempt not found or you do not have permission to view it.</div>';
    return;
}

// Calculate time spent
$timeSpent = $attempt['time_spent'];
$formattedTime = '';
if ($timeSpent >= 3600) {
    $hours = floor($timeSpent / 3600);
    $minutes = floor(($timeSpent % 3600) / 60);
    $seconds = $timeSpent % 60;
    $formattedTime = "{$hours}h {$minutes}m {$seconds}s";
} else if ($timeSpent >= 60) {
    $minutes = floor($timeSpent / 60);
    $seconds = $timeSpent % 60;
    $formattedTime = "{$minutes}m {$seconds}s";
} else {
    $formattedTime = "{$timeSpent}s";
}

// Get question responses
$stmt = $conn->prepare("
    SELECT r.*, q.question_text, q.question_type, q.points, q.explanation
    FROM student_question_responses r
    JOIN quiz_questions q ON r.question_id = q.question_id
    WHERE r.attempt_id = ?
    ORDER BY q.question_order
");
$stmt->bind_param("i", $attemptId);
$stmt->execute();
$responses = $stmt->get_result();
$stmt->close();

// Count correct and incorrect answers
$correctCount = 0;
$incorrectCount = 0;
$partialCount = 0;
$totalPoints = 0;
$earnedPoints = 0;

$responseData = [];
while ($response = $responses->fetch_assoc()) {
    $responseData[] = $response;
    
    if ($response['is_correct']) {
        $correctCount++;
    } else {
        $incorrectCount++;
    }
    
    $totalPoints += $response['points'];
    $earnedPoints += $response['points_awarded'];
}
?>

<div class="quiz-results bg-white p-4 rounded shadow-sm">
    <h3 class="quiz-title"><?php echo htmlspecialchars($attempt['quiz_title']); ?></h3>
    
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="quiz-result-summary bg-light p-3 rounded">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="text-muted">Attempt Date:</span>
                            <strong><?php echo date('M d, Y g:i A', strtotime($attempt['start_time'])); ?></strong>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Time Spent:</span>
                            <strong><?php echo $formattedTime; ?></strong>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Questions:</span>
                            <strong><?php echo $attempt['total_questions']; ?></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <span class="text-muted">Score:</span>
                            <strong class="fs-5 <?php echo ($attempt['passed'] ? 'text-success' : 'text-danger'); ?>">
                                <?php echo number_format($attempt['score'], 1); ?>%
                            </strong>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Status:</span>
                            <strong class="<?php echo ($attempt['passed'] ? 'text-success' : 'text-danger'); ?>">
                                <?php echo ($attempt['passed'] ? 'PASSED' : 'FAILED'); ?>
                            </strong>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted">Required to Pass:</span>
                            <strong><?php echo $attempt['pass_mark']; ?>%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quiz-result-chart p-3 rounded text-center">
                <div class="chart-container">
                    <canvas id="resultPieChart" width="200" height="200"></canvas>
                </div>
                <div class="mt-3">
                    <span class="badge bg-success p-2 me-2">Correct: <?php echo $correctCount; ?></span>
                    <span class="badge bg-danger p-2">Incorrect: <?php echo $incorrectCount; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="quiz-result-questions mt-4">
        <h4 class="mb-3">Question Review</h4>
        
        <?php if (count($responseData) === 0): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No question responses found for this attempt.
            </div>
        <?php else: ?>
            <div class="accordion" id="questionReviewAccordion">
                <?php 
                $questionNumber = 1;
                foreach ($responseData as $response): 
                    $isCorrect = $response['is_correct'];
                    $questionClass = $isCorrect ? 'border-success' : 'border-danger';
                    $questionIcon = $isCorrect ? 'check-circle' : 'times-circle';
                    $questionIconClass = $isCorrect ? 'text-success' : 'text-danger';
                    
                    // Get answer details for multiple choice questions
                    $selectedAnswers = [];
                    if (in_array($response['question_type'], ['Multiple Choice', 'True/False'])) {
                        $answerStmt = $conn->prepare("
                            SELECT s.*, a.answer_text, a.is_correct
                            FROM student_answer_selections s
                            JOIN quiz_answers a ON s.answer_id = a.answer_id
                            WHERE s.response_id = ?
                        ");
                        $answerStmt->bind_param("i", $response['response_id']);
                        $answerStmt->execute();
                        $selectedAnswers = $answerStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $answerStmt->close();
                    }
                    
                    // Get all possible answers for this question
                    $allAnswers = [];
                    if ($attempt['show_correct_answers']) {
                        $allAnswersStmt = $conn->prepare("
                            SELECT answer_id, answer_text, is_correct
                            FROM quiz_answers
                            WHERE question_id = ?
                            ORDER BY answer_id
                        ");
                        $allAnswersStmt->bind_param("i", $response['question_id']);
                        $allAnswersStmt->execute();
                        $allAnswers = $allAnswersStmt->get_result()->fetch_all(MYSQLI_ASSOC);
                        $allAnswersStmt->close();
                    }
                ?>
                    <div class="accordion-item mb-3 border-start border-4 <?php echo $questionClass; ?>">
                        <h2 class="accordion-header" id="heading<?php echo $questionNumber; ?>">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $questionNumber; ?>" aria-expanded="false" aria-controls="collapse<?php echo $questionNumber; ?>">
                                <div class="d-flex w-100 align-items-center">
                                    <div class="me-3">
                                        <i class="fas fa-<?php echo $questionIcon; ?> <?php echo $questionIconClass; ?> fa-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold">Question <?php echo $questionNumber; ?></div>
                                        <div class="text-truncate" style="max-width: 500px;">
                                            <?php echo htmlspecialchars(strip_tags($response['question_text'])); ?>
                                        </div>
                                    </div>
                                    <div class="ms-auto text-end">
                                        <span class="badge bg-light text-dark p-2">
                                            <?php echo number_format($response['points_awarded'], 1); ?> / <?php echo $response['points']; ?> points
                                        </span>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $questionNumber; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?php echo $questionNumber; ?>" data-bs-parent="#questionReviewAccordion">
                            <div class="accordion-body">
                                <div class="question-text mb-3">
                                    <?php echo $response['question_text']; ?>
                                </div>
                                
                                <?php if (in_array($response['question_type'], ['Multiple Choice', 'True/False'])): ?>
                                    <div class="question-answers mb-3">
                                        <strong>Your Answer:</strong>
                                        <ul class="list-group mt-2">
                                            <?php foreach ($selectedAnswers as $answer): ?>
                                                <li class="list-group-item <?php echo $answer['is_correct'] ? 'list-group-item-success' : 'list-group-item-danger'; ?>">
                                                    <i class="fas fa-<?php echo $answer['is_correct'] ? 'check' : 'times'; ?>-circle me-2"></i>
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <?php if ($attempt['show_correct_answers'] && !$isCorrect): ?>
                                        <div class="question-correct-answers mb-3">
                                            <strong>Correct Answer:</strong>
                                            <ul class="list-group mt-2">
                                                <?php foreach ($allAnswers as $answer): ?>
                                                    <?php if ($answer['is_correct']): ?>
                                                        <li class="list-group-item list-group-item-success">
                                                            <i class="fas fa-check-circle me-2"></i>
                                                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($response['question_type'] === 'Short_Answer'): ?>
                                    <div class="question-answers mb-3">
                                        <strong>Your Answer:</strong>
                                        <div class="mt-2 p-3 bg-light rounded">
                                            <?php echo nl2br(htmlspecialchars($response['answer_text'] ?? 'No answer provided')); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($response['explanation'] && $attempt['show_correct_answers']): ?>
                                    <div class="question-explanation mt-3 p-3 bg-light rounded">
                                        <strong>Explanation:</strong>
                                        <div class="mt-2">
                                            <?php echo $response['explanation']; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    $questionNumber++;
                endforeach; 
                ?>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="d-grid gap-2 d-md-flex justify-content-md-center mt-4">
        <a href="?topic_id=<?php echo $_GET['topic_id']; ?>" class="btn btn-primary px-4 py-2">
            <i class="fas fa-redo-alt me-2"></i> 
            <?php echo $attempt['passed'] ? 'Back to Quiz' : 'Try Again'; ?>
        </a>
        
        <a href="?section_id=<?php echo $_GET['section_id']; ?>" class="btn btn-outline-secondary px-4 py-2">
            <i class="fas fa-arrow-left me-2"></i> Back to Section
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Create pie chart for quiz results
    const ctx = document.getElementById('resultPieChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Correct', 'Incorrect'],
            datasets: [{
                data: [<?php echo $correctCount; ?>, <?php echo $incorrectCount; ?>],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.7)',
                    'rgba(220, 53, 69, 0.7)'
                ],
                borderColor: [
                    'rgba(40, 167, 69, 1)',
                    'rgba(220, 53, 69, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>