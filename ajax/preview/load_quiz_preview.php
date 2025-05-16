<?php
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get quiz ID from request
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;

// If no quiz ID is provided, return error
if ($quiz_id === 0) {
    echo '<div class="alert alert-danger">Invalid quiz ID</div>';
    exit;
}

// Include database connection
require_once '../../backend/config.php';

// Fetch quiz details
$quiz_query = "SELECT * FROM section_quizzes WHERE quiz_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If quiz not found, return error
if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz not found</div>';
    exit;
}

// Fetch section and course info
$section_query = "SELECT cs.section_id, cs.course_id
                 FROM course_sections cs 
                 WHERE cs.section_id = ? AND cs.deleted_at IS NULL";
$stmt = $conn->prepare($section_query);
$stmt->bind_param("i", $quiz['section_id']);
$stmt->execute();
$section_info = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$section_info) {
    echo '<div class="alert alert-danger">Section not found</div>';
    exit;
}

// Verify instructor has access to this course using course_instructors junction table
$instructor_access_query = "SELECT ci.course_id
                           FROM course_instructors ci
                           WHERE ci.course_id = ? 
                           AND ci.instructor_id = ?
                           AND ci.deleted_at IS NULL";
$stmt = $conn->prepare($instructor_access_query);
$stmt->bind_param("ii", $section_info['course_id'], $_SESSION['instructor_id']);
$stmt->execute();
$instructor_has_access = $stmt->get_result()->num_rows > 0;
$stmt->close();

// Verify instructor has access to this course
if (!$instructor_has_access) {
    echo '<div class="alert alert-danger">You do not have permission to view this quiz</div>';
    exit;
}

// Fetch quiz questions
$questions_query = "SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY question_order ASC";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();
$questions = [];
while ($question = $questions_result->fetch_assoc()) {
    $questions[] = $question;
}
$stmt->close();

// Start quiz preview output
echo '<div class="quiz-preview-container">';

// Quiz header
echo '<div class="mb-4 text-center">';
echo '<h3>' . htmlspecialchars($quiz['quiz_title']) . '</h3>';
echo '<div class="badge bg-primary">Preview Mode</div>';
echo '</div>';

// Quiz info
echo '<div class="card mb-4">';
echo '<div class="card-body">';
echo '<div class="row">';

// Quiz details
echo '<div class="col-md-6">';
echo '<h5>Quiz Information</h5>';
echo '<ul class="list-unstyled">';
echo '<li><i class="mdi mdi-checkbox-marked-circle-outline text-success me-2"></i> Passing score: ' . $quiz['pass_mark'] . '%</li>';

if ($quiz['time_limit']) {
    echo '<li><i class="mdi mdi-clock-outline text-warning me-2"></i> Time limit: ' . $quiz['time_limit'] . ' minutes</li>';
}

if ($quiz['attempts_allowed'] > 1) {
    echo '<li><i class="mdi mdi-refresh text-info me-2"></i> Attempts allowed: ' . $quiz['attempts_allowed'] . '</li>';
} else {
    echo '<li><i class="mdi mdi-refresh text-info me-2"></i> Attempts allowed: 1</li>';
}

if ($quiz['randomize_questions']) {
    echo '<li><i class="mdi mdi-shuffle-variant text-primary me-2"></i> Questions will be randomized</li>';
}

echo '</ul>';
echo '</div>';

// Instructions
echo '<div class="col-md-6">';
echo '<h5>Instructions</h5>';
if (!empty($quiz['instruction'])) {
    echo '<p>' . htmlspecialchars($quiz['instruction']) . '</p>';
} else {
    echo '<p class="text-muted">No specific instructions provided for this quiz.</p>';
}
echo '</div>';

echo '</div>'; // End row
echo '</div>'; // End card-body
echo '</div>'; // End card

// Start Quiz button
echo '<div class="text-center mb-4">';
echo '<button type="button" class="btn btn-primary btn-lg" id="startQuizBtn">';
echo '<i class="mdi mdi-play-circle-outline me-1"></i> Start Quiz';
echo '</button>';
echo '</div>';

// Quiz content (initially hidden)
echo '<div id="quizContent" style="display: none;">';

// If no questions
if (empty($questions)) {
    echo '<div class="alert alert-warning">';
    echo '<h5 class="alert-heading">No Questions Added</h5>';
    echo '<p>This quiz does not have any questions. As an instructor, you should add questions for your students.</p>';
    echo '<a href="quiz-builder.php?course_id=' . $section_info['course_id'] . '&section_id=' . $quiz['section_id'] . '&quiz_id=' . $quiz_id . '" class="btn btn-primary btn-sm mt-2">';
    echo '<i class="mdi mdi-plus-circle"></i> Add Questions Now';
    echo '</a>';
    echo '</div>';
} else {
    // Quiz form
    echo '<form id="quizForm">';

    // Questions
    foreach ($questions as $index => $question) {
        $question_num = $index + 1;

        echo '<div class="card mb-4 question-card">';
        echo '<div class="card-header bg-light">';
        echo '<h5 class="mb-0">Question ' . $question_num . '</h5>';
        echo '</div>';
        echo '<div class="card-body">';

        // Question text
        echo '<div class="question-text mb-3">';
        echo $question['question_text']; // Using HTML as is since it might contain rich text
        echo '</div>';

        // Get answers for this question
        $answers_query = "SELECT * FROM quiz_answers WHERE question_id = ?";
        $stmt = $conn->prepare($answers_query);
        $stmt->bind_param("i", $question['question_id']);
        $stmt->execute();
        $answers_result = $stmt->get_result();
        $answers = [];
        while ($answer = $answers_result->fetch_assoc()) {
            $answers[] = $answer;
        }
        $stmt->close();

        // Different question types
        switch ($question['question_type']) {
            case 'Multiple Choice':
                echo '<div class="answers-container">';
                foreach ($answers as $answer) {
                    $answer_id = 'answer_' . $question['question_id'] . '_' . $answer['answer_id'];

                    echo '<div class="form-check mb-2">';
                    echo '<input class="form-check-input" type="radio" name="question_' . $question['question_id'] . '" ';
                    echo 'id="' . $answer_id . '" value="' . $answer['answer_id'] . '">';
                    echo '<label class="form-check-label" for="' . $answer_id . '">';
                    echo htmlspecialchars($answer['answer_text']);
                    echo '</label>';
                    echo '</div>';
                }
                echo '</div>';
                break;

            case 'True/False':
                echo '<div class="answers-container">';
                foreach ($answers as $answer) {
                    $answer_id = 'answer_' . $question['question_id'] . '_' . $answer['answer_id'];

                    echo '<div class="form-check mb-2">';
                    echo '<input class="form-check-input" type="radio" name="question_' . $question['question_id'] . '" ';
                    echo 'id="' . $answer_id . '" value="' . $answer['answer_id'] . '">';
                    echo '<label class="form-check-label" for="' . $answer_id . '">';
                    echo htmlspecialchars($answer['answer_text']);
                    echo '</label>';
                    echo '</div>';
                }
                echo '</div>';
                break;

            default:
                echo '<div class="alert alert-warning">Preview for this question type is not available</div>';
        }

        echo '</div>'; // End card-body

        // In preview mode, show the correct answer
        // First, find the correct answer
        $correct_answer = null;
        foreach ($answers as $answer) {
            if ($answer['is_correct'] == 1) {
                $correct_answer = $answer;
                break;
            }
        }

        echo '<div class="card-footer bg-light answer-feedback" style="display: none;" data-correct-answer-id="' . ($correct_answer ? $correct_answer['answer_id'] : '') . '">';
        if ($correct_answer) {
            echo '<div class="alert alert-success">';
            echo '<i class="mdi mdi-check-circle me-1"></i> ';
            echo '<strong>Correct Answer:</strong> ' . htmlspecialchars($correct_answer['answer_text']);
            echo '</div>';

            if (!empty($question['explanation'])) {
                echo '<div class="mt-2">';
                echo '<strong>Explanation:</strong> ' . htmlspecialchars($question['explanation']);
                echo '</div>';
            }
        } else {
            echo '<div class="alert alert-warning">No correct answer defined for this question.</div>';
        }
        echo '</div>'; // End card-footer

        echo '</div>'; // End question-card
    }

    // Submit button
    echo '<div class="text-center mb-4">';
    echo '<button type="button" class="btn btn-success btn-lg" id="submitQuizBtn">';
    echo '<i class="mdi mdi-check-circle me-1"></i> Submit Quiz';
    echo '</button>';
    echo '</div>';

    echo '</form>';
}

echo '</div>'; // End quizContent

// Quiz results (initially hidden)
echo '<div id="quizResults" style="display: none;">';
echo '<div class="card">';
echo '<div class="card-header bg-success text-white">';
echo '<h4 class="mb-0"><i class="mdi mdi-check-circle me-1"></i> Quiz Completed</h4>';
echo '</div>';
echo '<div class="card-body">';
echo '<div class="text-center">';
echo '<div class="display-4 mb-3">Preview Mode</div>';
echo '<p class="lead">In the actual quiz, students would see their score and feedback here.</p>';
echo '<div class="mt-4">';
echo '<button type="button" class="btn btn-primary" id="reviewQuizBtn">';
echo '<i class="mdi mdi-eye me-1"></i> Review Answers';
echo '</button>';
echo '<button type="button" class="btn btn-secondary ms-2" id="resetQuizBtn">';
echo '<i class="mdi mdi-refresh me-1"></i> Reset Quiz';
echo '</button>';
echo '</div>';
echo '</div>';
echo '</div>'; // End card-body
echo '</div>'; // End card
echo '</div>'; // End quizResults

echo '</div>'; // End quiz-preview-container
?>

<script>
$(document).ready(function() {
    // Start Quiz button
    $('#startQuizBtn').click(function() {
        $(this).parent().hide();
        $('#quizContent').fadeIn();

        // If time limit exists, show timer
        <?php if (!empty($quiz['time_limit'])): ?>
            const timeLimit = <?php echo $quiz['time_limit']; ?> * 60; // Convert to seconds
            startTimer(timeLimit);
        <?php endif; ?>
    });
    
    // Submit Quiz button
    $('#submitQuizBtn').click(function() {
        // Disable all radio buttons to prevent changes
        $('.form-check-input').prop('disabled', true);
        
        // Process the answers and calculate score
        const score = processAnswers();
        
        // Update the results display with the score
        updateQuizResults(score);
        
        // Hide quiz content and show results
        $('#quizContent').hide();
        $('#quizResults').fadeIn();
    });
    
    // Function to process answers and show feedback, returns score
    function processAnswers() {
        let correctAnswers = 0;
        let totalQuestions = $('.question-card').length;
         
        $('.question-card').each(function() {
            const questionId = $(this).find('input[type="radio"]').attr('name').replace('question_', '');
            const selectedAnswer = $(this).find('input[type="radio"]:checked').val();
            const feedbackContainer = $(this).find('.answer-feedback');
            
            // Find the correct answer
            const correctAnswerId = feedbackContainer.data('correct-answer-id');
            
            // Track if user selected an answer for this question
            const hasSelection = $(this).find('input[type="radio"]:checked').length > 0;
            
            // Add status to the entire question card
            if (hasSelection) {
                if (selectedAnswer == correctAnswerId) {
                    // Increment score for correct answers
                    correctAnswers++;
                    
                    $(this).addClass('border-success');
                    $(this).find('.card-header').addClass('bg-success text-white');
                } else {
                    $(this).addClass('border-danger');
                    $(this).find('.card-header').addClass('bg-danger text-white');
                }
            } else {
                $(this).addClass('border-warning');
                $(this).find('.card-header').addClass('bg-warning');
            }
            
            // Style each answer
            $(this).find('.form-check').each(function() {
                const answerInput = $(this).find('input[type="radio"]');
                const answerId = answerInput.val();
                const isSelected = answerInput.is(':checked');
                const isCorrect = (answerId == correctAnswerId);
                
                // Remove any existing styles
                $(this).removeClass('selected-correct selected-incorrect correct-answer');
                
                // Create a new styled container
                const answerClass = isSelected ? 
                    (isCorrect ? 'selected-correct' : 'selected-incorrect') : 
                    (isCorrect ? 'correct-answer' : '');
                
                if (answerClass) {
                    $(this).addClass(answerClass);
                    
                    // Add icon based on answer state
                    if (isSelected && isCorrect) {
                        // User selected correctly
                        $(this).find('label').prepend('<i class="mdi mdi-check-circle me-1 text-success"></i>');
                    } else if (isSelected && !isCorrect) {
                        // User selected incorrectly
                        $(this).find('label').prepend('<i class="mdi mdi-close-circle me-1 text-danger"></i>');
                    } else if (isCorrect) {
                        // Correct answer not selected
                        $(this).find('label').prepend('<i class="mdi mdi-check-circle me-1 text-success"></i>');
                    }
                }
            });
            
            // Show the feedback
            feedbackContainer.show();
        });
        
        // Calculate percentage score
        const percentageScore = totalQuestions > 0 ? Math.round((correctAnswers / totalQuestions) * 100) : 0;
        return {
            correct: correctAnswers,
            total: totalQuestions,
            percentage: percentageScore
        };
    }
    
    // Function to update the quiz results display
    function updateQuizResults(score) {
        // Get pass mark
        const passMark = <?php echo $quiz['pass_mark']; ?>;
        const hasPassed = score.percentage >= passMark;
        
        // Update the header based on pass/fail
        $('.card-header', '#quizResults').removeClass('bg-success bg-danger').addClass(hasPassed ? 'bg-success' : 'bg-danger');
        
        // Update the heading text
        $('.card-header h4', '#quizResults').html(
            hasPassed ? 
            '<i class="mdi mdi-check-circle me-1"></i> Quiz Completed - Passed!' : 
            '<i class="mdi mdi-close-circle me-1"></i> Quiz Completed - Failed'
        );
        
        // Update the score display
        $('.display-4', '#quizResults').html(`${score.percentage}%`);
        
        // Add more detailed score info
        $('.lead', '#quizResults').html(`
            You got ${score.correct} out of ${score.total} questions correct.<br>
            Pass mark: ${passMark}%
        `);
        
        // Optional: Add grade based on score
        let grade = '';
        if (score.percentage >= 90) grade = 'A';
        else if (score.percentage >= 80) grade = 'B';
        else if (score.percentage >= 70) grade = 'C';
        else if (score.percentage >= 60) grade = 'D';
        else grade = 'F';
        
        // Add grade if we want to show it
        if (grade) {
            $('<div class="mt-3 display-2"></div>').text(`Grade: ${grade}`).insertAfter($('.display-4', '#quizResults'));
        }
    }
    
    // Review Quiz button
    $('#reviewQuizBtn').click(function() {
        $('#quizResults').hide();
        $('#quizContent').fadeIn();
        
        // Keep radio buttons disabled during review
        $('.form-check-input').prop('disabled', true);
    });
    
    // Reset Quiz button
    $('#resetQuizBtn').click(function() {
        // Reset form
        $('#quizForm')[0].reset();
        
        // Re-enable radio buttons
        $('.form-check-input').prop('disabled', false);
        
        // Remove all styling and icons
        $('.question-card').removeClass('border-success border-danger border-warning');
        $('.card-header').removeClass('bg-success bg-danger bg-warning text-white');
        $('.form-check').removeClass('selected-correct selected-incorrect correct-answer');
        $('.form-check label i').remove();
        
        // Hide feedback
        $('.answer-feedback').hide();
        
        // Hide results
        $('#quizResults').hide();
        
        // Show start button
        $('#startQuizBtn').parent().fadeIn();
    });
    
    // Function to start timer
    function startTimer(duration) {
        let timer = duration;
        const timerDisplay = $('<div class="alert alert-warning text-center mb-3" id="quizTimer"></div>');
        $('#quizContent').prepend(timerDisplay);
        
        const interval = setInterval(function() {
            const minutes = Math.floor(timer / 60);
            const seconds = timer % 60;
            
            // Format display
            const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            $('#quizTimer').html(`<strong>Time Remaining:</strong> ${display}`);
            
            // Decrement timer
            if (--timer < 0) {
                clearInterval(interval);
                $('#quizTimer').removeClass('alert-warning').addClass('alert-danger');
                $('#quizTimer').html('<strong>Time\'s Up!</strong> In a real quiz, this would be submitted automatically.');
                
                // Auto-submit after 3 seconds in preview mode
                setTimeout(function() {
                    $('#submitQuizBtn').click();
                }, 3000);
            }
        }, 1000);
    }
});
</script>
<style>
    /* Answer styling for quiz feedback */
    .selected-correct {
        background-color: rgba(25, 135, 84, 0.1);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #198754;
        margin-bottom: 10px !important;
    }
    
    .selected-incorrect {
        background-color: rgba(220, 53, 69, 0.1);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #dc3545;
        margin-bottom: 10px !important;
    }
    
    .correct-answer {
        background-color: rgba(25, 135, 84, 0.05);
        padding: 10px 15px;
        border-radius: 4px;
        border-left: 4px solid #198754;
        margin-bottom: 10px !important;
    }
    
    /* Question card styling */
    .question-card {
        transition: all 0.3s ease;
    }
    
    .question-card.border-success {
        border-left: 5px solid #198754 !important;
    }
    
    .question-card.border-danger {
        border-left: 5px solid #dc3545 !important;
    }
    
    .question-card.border-warning {
        border-left: 5px solid #ffc107 !important;
    }
    
    /* Make disabled radio buttons look better */
    input[type="radio"]:disabled + label {
        cursor: default;
        opacity: 0.8;
    }
</style>