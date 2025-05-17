<?php
// ajax/department/preview_quiz.php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is logged in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get quiz ID from request
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// If no quiz ID is provided, return error
if ($quiz_id === 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid quiz ID']);
    exit;
}

// Include database connection
require_once '../../backend/config.php';

// Fetch quiz details and verify department access
$quiz_query = "
    SELECT sq.*, t.topic_id, t.title as topic_title, s.section_id, s.title as section_title, 
           c.course_id, c.title as course_title, c.department_id 
    FROM section_quizzes sq
    LEFT JOIN section_topics t ON sq.topic_id = t.topic_id
    LEFT JOIN course_sections s ON sq.section_id = s.section_id
    LEFT JOIN courses c ON s.course_id = c.course_id
    WHERE sq.quiz_id = ? AND sq.deleted_at IS NULL";
$stmt = $conn->prepare($quiz_query);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
$quiz = $quiz_result->fetch_assoc();
$stmt->close();

// If quiz not found, return error
if (!$quiz) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Quiz not found']);
    exit;
}

// Verify department head has access to this department
$dept_query = "
    SELECT ds.department_id 
    FROM department_staff ds 
    WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
    LIMIT 1";
$stmt = $conn->prepare($dept_query);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$department = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$department || $department['department_id'] != $quiz['department_id']) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You do not have permission to view this quiz']);
    exit;
}

// Fetch quiz questions and answers
$questions_query = "
    SELECT qq.*, qa.answer_id, qa.answer_text, qa.is_correct
    FROM quiz_questions qq
    LEFT JOIN quiz_answers qa ON qq.question_id = qa.question_id AND qa.deleted_at IS NULL
    WHERE qq.quiz_id = ? AND qq.deleted_at IS NULL
    ORDER BY qq.question_order ASC, qa.answer_id ASC";
$stmt = $conn->prepare($questions_query);
if (!$stmt) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit;
}
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

$questions = [];
$current_question_id = null;
while ($row = $questions_result->fetch_assoc()) {
    if ($row['question_id'] !== $current_question_id) {
        $questions[] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'question_type' => $row['question_type'],
            'points' => $row['points'],
            'explanation' => $row['explanation'],
            'answers' => []
        ];
        $current_question_id = $row['question_id'];
    }
    if ($row['answer_id']) {
        $questions[count($questions) - 1]['answers'][] = [
            'answer_id' => $row['answer_id'],
            'answer_text' => $row['answer_text'],
            'is_correct' => $row['is_correct']
        ];
    }
}
$stmt->close();

// Start quiz preview output
?>
<div class="quiz-preview-container" role="region" aria-label="Quiz Preview">
    <!-- Quiz header -->
    <div class="mb-4 text-center">
        <h4 class="mb-2"><?php echo htmlspecialchars($quiz['quiz_title']); ?></h4>
        <div class="d-flex justify-content-center align-items-center gap-2">
            <span class="badge bg-primary-soft text-primary">Preview Mode</span>
            <span class="badge bg-secondary">From <?php echo htmlspecialchars($quiz['section_title']); ?></span>
        </div>
    </div>

    <!-- Quiz info -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <div class="row">
                <!-- Quiz details -->
                <div class="col-md-6">
                    <h5 class="h6 mb-3">Quiz Information</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><i class="bi-trophy text-success me-2"></i> Passing score: <?php echo $quiz['pass_mark']; ?>%</li>
                        <?php if ($quiz['time_limit']): ?>
                            <li class="mb-2"><i class="bi-clock text-warning me-2"></i> Time limit: <?php echo $quiz['time_limit']; ?> minutes</li>
                        <?php endif; ?>
                        <li class="mb-2"><i class="bi-arrow-repeat text-info me-2"></i> Attempts allowed: <?php echo $quiz['attempts_allowed'] > 1 ? $quiz['attempts_allowed'] : 1; ?></li>
                        <?php if ($quiz['randomize_questions']): ?>
                            <li class="mb-2"><i class="bi-shuffle text-primary me-2"></i> Questions will be randomized</li>
                        <?php endif; ?>
                        <?php if ($quiz['is_required']): ?>
                            <li class="mb-2"><i class="bi-exclamation-circle text-danger me-2"></i> This quiz is required</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- Instructions -->
                <div class="col-md-6">
                    <h5 class="h6 mb-3">Instructions</h5>
                    <div class="bg-light p-3 rounded">
                        <?php echo !empty($quiz['instruction']) ? nl2br(htmlspecialchars($quiz['instruction'])) : '<span class="text-muted">No specific instructions provided for this quiz.</span>'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Start Quiz button -->
    <div class="text-center mb-4">
        <button type="button" class="btn btn-primary" id="startQuizBtn" aria-label="Start the quiz preview">
            <i class="bi-play-circle me-1"></i> Start Quiz
        </button>
    </div>

    <!-- Quiz content (initially hidden) -->
    <div id="quizContent" style="display: none;">
        <?php if ($quiz['time_limit']): ?>
            <div id="quizTimer" class="alert alert-warning text-center mb-3" style="display: none;" role="timer" aria-live="polite">
                <strong>Time Remaining:</strong> <span id="timeDisplay" aria-label="Time remaining">00:00</span>
            </div>
        <?php endif; ?>

        <!-- Progress bar -->
        <div class="progress mb-4" style="height: 8px;" role="progressbar" aria-label="Quiz progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div class="progress-bar" style="width: 0%" aria-valuenow="0"></div>
        </div>

        <?php if (empty($questions)): ?>
            <div class="alert alert-warning" role="alert">
                <h5 class="alert-heading">No Questions Added</h5>
                <p>This quiz does not have any questions.</p>
            </div>
        <?php else: ?>
            <form id="quizForm" role="form" aria-label="Quiz questions">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="card mb-4 question-card border-0 shadow-sm" data-question-index="<?php echo $index; ?>" style="display: <?php echo $index === 0 ? 'block' : 'none'; ?>;" role="group" aria-labelledby="question-<?php echo $question['question_id']; ?>">
                        <div class="card-header bg-light border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="h6 mb-0" id="question-<?php echo $question['question_id']; ?>">Question <?php echo $index + 1; ?> of <?php echo count($questions); ?></h5>
                                <span class="badge bg-info"><?php echo $question['points']; ?> <?php echo $question['points'] > 1 ? 'points' : 'point'; ?></span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="question-text mb-4">
                                <p class="mb-0 fw-medium"><?php echo nl2br(htmlspecialchars($question['question_text'])); ?></p>
                            </div>

                            <?php
                            switch ($question['question_type']) {
                                case 'Multiple Choice':
                                case 'True/False':
                                    echo '<div class="answers-container" data-question-type="' . htmlspecialchars($question['question_type']) . '">';
                                    foreach ($question['answers'] as $answer) {
                                        $answer_id = 'answer_' . $question['question_id'] . '_' . $answer['answer_id'];
                                        echo '<div class="form-check mb-3 answer-option">';
                                        echo '<input class="form-check-input" type="radio" name="question_' . $question['question_id'] . '" id="' . $answer_id . '" value="' . $answer['answer_id'] . '" data-is-correct="' . ($answer['is_correct'] ? '1' : '0') . '" aria-labelledby="label-' . $answer_id . '">';
                                        echo '<label class="form-check-label" id="label-' . $answer_id . '" for="' . $answer_id . '">' . htmlspecialchars($answer['answer_text']) . '</label>';
                                        echo '</div>';
                                    }
                                    echo '</div>';
                                    break;

                                case 'Short Answer':
                                    echo '<div class="answers-container" data-question-type="Short Answer">';
                                    echo '<div class="mb-3">';
                                    echo '<input type="text" class="form-control" name="question_' . $question['question_id'] . '" placeholder="Type your answer here" aria-label="Short answer input">';
                                    echo '</div>';
                                    echo '<div class="correct-answers" style="display: none;">';
                                    echo '<strong>Acceptable Answers:</strong> ';
                                    $correct_answers = array_map('htmlspecialchars', array_column(array_filter($question['answers'], fn($a) => $a['is_correct']), 'answer_text'));
                                    echo implode(', ', $correct_answers);
                                    echo '</div>';
                                    echo '</div>';
                                    break;

                                default:
                                    echo '<div class="alert alert-warning" role="alert">Preview for this question type is not available</div>';
                            }
                            ?>
                        </div>

                        <div class="card-footer bg-white border-0 d-flex justify-content-between">
                            <?php if ($index > 0): ?>
                                <button type="button" class="btn btn-outline-secondary btn-prev" data-prev-question="<?php echo $index - 1; ?>" aria-label="Previous question">
                                    <i class="bi-arrow-left me-1"></i> Previous
                                </button>
                            <?php else: ?>
                                <div></div>
                            <?php endif; ?>

                            <?php if ($index < count($questions) - 1): ?>
                                <button type="button" class="btn btn-primary btn-next" data-next-question="<?php echo $index + 1; ?>" aria-label="Next question">
                                    Next <i class="bi-arrow-right ms-1"></i>
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success" id="completeQuizBtn" aria-label="Complete quiz">
                                    Complete Quiz <i class="bi-check-circle ms-1"></i>
                                </button>
                            <?php endif; ?>
                        </div>

                        <div class="answer-feedback mt-3 p-3 rounded border" style="display: none;" role="alert" aria-live="assertive"></div>
                    </div>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    </div>

    <!-- Quiz results (initially hidden) -->
    <div id="quizResults" style="display: none;">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi-check-circle me-1"></i> Quiz Completed</h5>
            </div>
            <div class="card-body text-center">
                <div class="mb-4">
                    <div class="display-1 mb-2 score-display">0%</div>
                    <div class="h5 score-text">You answered 0 out of 0 questions correctly</div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center">
                                <div class="h1 mb-1"><span id="correctAnswers">0</span></div>
                                <div class="text-muted">Correct</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center">
                                <div class="h1 mb-1"><span id="incorrectAnswers">0</span></div>
                                <div class="text-muted">Incorrect</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light mb-3">
                            <div class="card-body text-center">
                                <div class="h1 mb-1"><span id="unansweredQuestions">0</span></div>
                                <div class="text-muted">Unanswered</div>
                            </div>
                        </div>
                    </div>
                </div>
                <p class="mb-4">Pass mark: <?php echo htmlspecialchars($quiz['pass_mark']); ?>%</p>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-primary" id="reviewQuizBtn" aria-label="Review quiz answers">
                        <i class="bi-eye me-1"></i> Review Answers
                    </button>
                    <button type="button" class="btn btn-secondary" id="resetQuizBtn" aria-label="Reset quiz">
                        <i class="bi-arrow-repeat me-1"></i> Reset Quiz
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const totalQuestions = <?php echo count($questions); ?>;
    const correctAnswers = <?php echo json_encode(array_map(function($q) {
        return [
            'question_id' => $q['question_id'],
            'type' => $q['question_type'],
            'answers' => array_filter($q['answers'], fn($a) => $a['is_correct'])
        ];
    }, $questions)); ?>;
    let timerInterval;

    // Start Quiz
    $('#startQuizBtn').click(function() {
        $(this).parent().hide();
        $('#quizContent').fadeIn();
        updateProgressBar(1, totalQuestions);
        <?php if ($quiz['time_limit']): ?>
            const timeLimit = <?php echo $quiz['time_limit']; ?> * 60;
            $('#quizTimer').show();
            startTimer(timeLimit);
        <?php endif; ?>
    });

    // Navigation
    $('.btn-next').click(function() {
        const currentQuestion = $(this).closest('.question-card');
        const nextQuestionIndex = $(this).data('next-question');
        currentQuestion.hide();
        $(`.question-card[data-question-index="${nextQuestionIndex}"]`).show();
        updateProgressBar(nextQuestionIndex + 1, totalQuestions);
    });

    $('.btn-prev').click(function() {
        const currentQuestion = $(this).closest('.question-card');
        const prevQuestionIndex = $(this).data('prev-question');
        currentQuestion.hide();
        $(`.question-card[data-question-index="${prevQuestionIndex}"]`).show();
        updateProgressBar(prevQuestionIndex + 1, totalQuestions);
    });

    // Complete Quiz
    $('#completeQuizBtn').click(function() {
        const results = processAnswers();
        updateResults(results);
        $('#quizContent').hide();
        $('#quizResults').fadeIn();
        if (timerInterval) clearInterval(timerInterval);
    });

    // Review Quiz
    $('#reviewQuizBtn').click(function() {
        $('#quizResults').hide();
        $('.question-card').show();
        $('.answer-feedback').show();
        $('#quizForm input').prop('disabled', true);
        $('.btn-next, .btn-prev, #completeQuizBtn').hide();
        $('.correct-answers').show();
        $('#quizContent').fadeIn();
    });

    // Reset Quiz
    $('#resetQuizBtn').click(function() {
        $('#quizForm')[0].reset();
        $('#quizForm input').prop('disabled', false);
        $('.question-card').removeClass('border-success border-danger border-warning');
        $('.answer-option').removeClass('selected-correct selected-incorrect correct-answer');
        $('.answer-option i.feedback-icon').remove();
        $('.question-card').hide();
        $('.question-card[data-question-index="0"]').show();
        $('.btn-next, .btn-prev, #completeQuizBtn').show();
        $('.answer-feedback').hide();
        $('.correct-answers').hide();
        updateProgressBar(1, totalQuestions);
        $('#quizResults').hide();
        $('#startQuizBtn').parent().fadeIn();
        if (timerInterval) clearInterval(timerInterval);
        $('#quizTimer').hide();
    });

    function updateProgressBar(current, total) {
        const percentage = (current / total) * 100;
        $('.progress-bar').css('width', `${percentage}%`).attr('aria-valuenow', percentage);
        $('.progress').attr('aria-valuenow', Math.round(percentage));
    }

    function startTimer(duration) {
        let timer = duration;
        timerInterval = setInterval(function() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            $('#timeDisplay').text(display).attr('aria-label', `Time remaining: ${display}`);
            if (timer < 60) {
                $('#quizTimer').removeClass('alert-warning').addClass('alert-danger');
            }
            if (--timer < 0) {
                clearInterval(timerInterval);
                $('#quizTimer').html('<strong>Time\'s Up!</strong> In a real quiz, this would be submitted automatically.');
                setTimeout(() => $('#completeQuizBtn').click(), 3000);
            }
        }, 1000);
    }

    function processAnswers() {
        let correctCount = 0, incorrectCount = 0, unansweredCount = 0;
        $('.question-card').each(function(index) {
            const $question = $(this);
            const questionId = $question.find('input, textarea').attr('name').replace('question_', '');
            const questionType = $question.find('.answers-container').data('question-type');
            let isAnswered = false, isCorrect = false, feedback = '';
            const correctAnswerData = correctAnswers[index];

            if (questionType === 'Short Answer') {
                const userAnswer = $question.find('input[type="text"]').val().trim().toLowerCase();
                isAnswered = userAnswer !== '';
                const correctAnswersList = correctAnswerData.answers.map(a => a.answer_text.toLowerCase());
                isCorrect = isAnswered && correctAnswersList.includes(userAnswer);
                if (isAnswered) {
                    if (isCorrect) {
                        correctCount++;
                        $question.addClass('border-success');
                        feedback = '<div class="alert alert-success mb-0"><i class="bi-check-circle-fill me-2"></i>Correct!</div>';
                    } else {
                        incorrectCount++;
                        $question.addClass('border-danger');
                        feedback = '<div class="alert alert-danger mb-0"><i class="bi-x-circle-fill me-2"></i>Incorrect</div>';
                    }
                } else {
                    unansweredCount++;
                    $question.addClass('border-warning');
                    feedback = '<div class="alert alert-warning mb-0"><i class="bi-exclamation-triangle-fill me-2"></i>Not answered</div>';
                }
            } else {
                const $selectedOption = $question.find('input[type="radio"]:checked');
                if ($selectedOption.length) {
                    isAnswered = true;
                    isCorrect = $selectedOption.data('is-correct') === 1;
                    if (isCorrect) {
                        correctCount++;
                        $question.addClass('border-success');
                        $selectedOption.closest('.answer-option').addClass('selected-correct');
                        $selectedOption.closest('.answer-option').find('label').prepend('<i class="bi-check-circle-fill text-success me-1 feedback-icon"></i>');
                        feedback = '<div class="alert alert-success mb-0"><i class="bi-check-circle-fill me-2"></i>Correct!</div>';
                    } else {
                        incorrectCount++;
                        $question.addClass('border-danger');
                        $selectedOption.closest('.answer-option').addClass('selected-incorrect');
                        $selectedOption.closest('.answer-option').find('label').prepend('<i class="bi-x-circle-fill text-danger me-1 feedback-icon"></i>');
                        $question.find('input[data-is-correct="1"]').closest('.answer-option').addClass('correct-answer').find('label').prepend('<i class="bi-check-circle-fill text-success me-1 feedback-icon"></i>');
                        feedback = '<div class="alert alert-danger mb-0"><i class="bi-x-circle-fill me-2"></i>Incorrect</div>';
                    }
                } else {
                    unansweredCount++;
                    $question.addClass('border-warning');
                    $question.find('input[data-is-correct="1"]').closest('.answer-option').addClass('correct-answer').find('label').prepend('<i class="bi-check-circle-fill text-success me-1 feedback-icon"></i>');
                    feedback = '<div class="alert alert-warning mb-0"><i class="bi-exclamation-triangle-fill me-2"></i>Not answered</div>';
                }
            }

            if (correctAnswerData.explanation) {
                feedback += '<div class="mt-2"><strong>Explanation:</strong> ' + correctAnswerData.explanation + '</div>';
            }

            $question.find('.answer-feedback').html(feedback);
        });

        const percentageScore = totalQuestions > 0 ? Math.round((correctCount / totalQuestions) * 100) : 0;
        return {
            correct: correctCount,
            incorrect: incorrectCount,
            unanswered: unansweredCount,
            total: totalQuestions,
            percentage: percentageScore
        };
    }

    function updateResults(results) {
        $('#correctAnswers').text(results.correct);
        $('#incorrectAnswers').text(results.incorrect);
        $('#unansweredQuestions').text(results.unanswered);
        $('.score-display').text(results.percentage + '%');
        $('.score-text').text(`You answered ${results.correct} out of ${results.total} questions correctly`);
        const passMark = <?php echo $quiz['pass_mark']; ?>;
        const hasPassed = results.percentage >= passMark;
        const $header = $('.card-header', '#quizResults');
        $header.toggleClass('bg-success', hasPassed).toggleClass('bg-danger', !hasPassed);
        $header.find('h5').html(hasPassed ? 
            '<i class="bi-check-circle me-1"></i> Quiz Completed - Passed!' : 
            '<i class="bi-x-circle me-1"></i> Quiz Completed - Failed');
    }
});
</script>

<style>
/* Unique styles not covered in review-course.php */
.form-check-input:checked + .form-check-label {
    font-weight: 500;
}
</style>