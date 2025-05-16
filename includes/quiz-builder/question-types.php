<?php
// This file loads the appropriate question type editor based on the request

// Validate user is signed in and is an instructor
require_once '../../backend/session_start.php';
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

// Get required parameters
if (!isset($_GET['type']) || !isset($_GET['quiz_id'])) {
    echo '<div class="alert alert-danger">Missing required parameters</div>';
    exit;
}

$question_type = $_GET['type'];
$quiz_id = intval($_GET['quiz_id']);
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : null;

// Connect to database
require_once '../../backend/config.php';

// ADDED: Verify instructor has access to this quiz through course_instructors
$instructor_id = $_SESSION['instructor_id'];

// Get the course_id for this quiz
$course_query = "SELECT cs.course_id 
                FROM section_quizzes sq 
                JOIN course_sections cs ON sq.section_id = cs.section_id 
                WHERE sq.quiz_id = ?";
$stmt = $conn->prepare($course_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Quiz not found</div>';
    exit;
}

$course_data = $course_result->fetch_assoc();
$course_id = $course_data['course_id'];
$stmt->close();

// Check if instructor is assigned to this course
$access_check_query = "SELECT ci.course_id 
                      FROM course_instructors ci 
                      WHERE ci.course_id = ? 
                      AND ci.instructor_id = ?
                      AND ci.deleted_at IS NULL";
$stmt = $conn->prepare($access_check_query);
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$access_result = $stmt->get_result();

if ($access_result->num_rows === 0) {
    echo '<div class="alert alert-danger">You do not have access to this quiz</div>';
    exit;
}
$stmt->close();

// Variables for editing
$question_data = null;
$answers_data = [];

// If editing, load question data
if ($question_id) {
    // Load question
    $question_query = "SELECT * FROM quiz_questions WHERE question_id = ? AND quiz_id = ?";
    $stmt = $conn->prepare($question_query);
    $stmt->bind_param("ii", $question_id, $quiz_id);
    $stmt->execute();
    $question_result = $stmt->get_result();
    
    if ($question_result->num_rows === 0) {
        echo '<div class="alert alert-danger">Question not found</div>';
        exit;
    }
    
    $question_data = $question_result->fetch_assoc();
    $stmt->close();
    
    // Load answers
    $answers_query = "SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY answer_id ASC";
    $stmt = $conn->prepare($answers_query);
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $answers_result = $stmt->get_result();
    
    while ($answer = $answers_result->fetch_assoc()) {
        $answers_data[] = $answer;
    }
    $stmt->close();
    
    // Map question type from database to frontend format
    if ($question_data['question_type'] === 'Multiple Choice') {
        $question_type = 'multiple_choice';
    } elseif ($question_data['question_type'] === 'True/False') {
        $question_type = 'true_false';
    }
}

// Load the appropriate question type editor
if ($question_type === 'multiple_choice') {
    // Multiple Choice Question Editor
    ?>
    <form id="multipleChoiceForm" class="question-form">
        <input type="hidden" id="questionId" value="<?php echo $question_id ? $question_id : ''; ?>">
        
        <div class="mb-3">
            <label for="questionText" class="form-label">Question Text <span class="text-danger">*</span></label>
            <textarea class="form-control" id="questionText" rows="3" required
                placeholder="Enter your question"><?php echo $question_data ? htmlspecialchars($question_data['question_text']) : ''; ?></textarea>
            <div class="invalid-feedback">Please enter a question.</div>
        </div>
        
        <div class="mb-3">
            <label for="questionPoints" class="form-label">Points <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="questionPoints" min="1" value="<?php echo $question_data ? intval($question_data['points']) : '1'; ?>" required>
            <div class="form-text">How many points this question is worth.</div>
            <div class="invalid-feedback">Please enter a valid point value (minimum 1).</div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Answer Options <span class="text-danger">*</span></label>
            <div class="form-text mb-2">Select the radio button next to the correct answer. You need at least 2 options.</div>
            
            <div id="answersContainer">
                <?php if (!empty($answers_data)): ?>
                    <?php foreach ($answers_data as $index => $answer): ?>
                        <div class="answer-option mb-3" data-answer-id="<?php echo $answer['answer_id']; ?>">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input correct-answer" type="radio" name="correctAnswer" 
                                        value="<?php echo $index + 1; ?>" <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                                </div>
                                <input type="text" class="form-control answer-text" 
                                    value="<?php echo htmlspecialchars($answer['answer_text']); ?>" 
                                    placeholder="Answer option">
                                <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Default 4 options if new question -->
                    <div class="answer-option mb-3" data-answer-id="">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input correct-answer" type="radio" name="correctAnswer" value="1" checked>
                            </div>
                            <input type="text" class="form-control answer-text" placeholder="Answer option 1">
                            <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                    <div class="answer-option mb-3" data-answer-id="">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input correct-answer" type="radio" name="correctAnswer" value="2">
                            </div>
                            <input type="text" class="form-control answer-text" placeholder="Answer option 2">
                            <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                    <div class="answer-option mb-3" data-answer-id="">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input correct-answer" type="radio" name="correctAnswer" value="3">
                            </div>
                            <input type="text" class="form-control answer-text" placeholder="Answer option 3">
                            <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                    <div class="answer-option mb-3" data-answer-id="">
                        <div class="input-group">
                            <div class="input-group-text">
                                <input class="form-check-input correct-answer" type="radio" name="correctAnswer" value="4">
                            </div>
                            <input type="text" class="form-control answer-text" placeholder="Answer option 4">
                            <button type="button" class="btn btn-outline-danger remove-answer-btn">
                                <i class="mdi mdi-delete"></i>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <button type="button" id="addAnswerBtn" class="btn btn-outline-primary">
                <i class="mdi mdi-plus-circle"></i> Add Answer Option
            </button>
        </div>
        
        <div class="mb-3">
            <label for="questionExplanation" class="form-label">Explanation (Optional)</label>
            <textarea class="form-control" id="questionExplanation" rows="2" 
                placeholder="Explain why the correct answer is right"><?php echo $question_data && $question_data['explanation'] ? htmlspecialchars($question_data['explanation']) : ''; ?></textarea>
            <div class="form-text">Provide an explanation that will be shown after answering.</div>
        </div>
        
        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="saveQuestionBtn" class="btn btn-primary" data-question-type="multiple_choice">
                <?php echo $question_id ? 'Update' : 'Save'; ?> Question
            </button>
        </div>
    </form>
    <?php
} elseif ($question_type === 'true_false') {
    // True/False Question Editor
    // For true/false questions, the first answer is always 'True' and the second is 'False'
    $true_is_correct = false;
    $false_is_correct = false;
    
    if (!empty($answers_data)) {
        foreach ($answers_data as $answer) {
            if ($answer['answer_text'] === 'True' && $answer['is_correct']) {
                $true_is_correct = true;
            } elseif ($answer['answer_text'] === 'False' && $answer['is_correct']) {
                $false_is_correct = true;
            }
        }
    }
    ?>
    <form id="trueFalseForm" class="question-form">
        <input type="hidden" id="questionId" value="<?php echo $question_id ? $question_id : ''; ?>">
        
        <div class="mb-3">
            <label for="questionText" class="form-label">Question Text/Statement <span class="text-danger">*</span></label>
            <textarea class="form-control" id="questionText" rows="3" required
                placeholder="Enter your question or statement"><?php echo $question_data ? htmlspecialchars($question_data['question_text']) : ''; ?></textarea>
            <div class="form-text">Enter a statement that is either true or false.</div>
            <div class="invalid-feedback">Please enter a question or statement.</div>
        </div>
        
        <div class="mb-3">
            <label for="questionPoints" class="form-label">Points <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="questionPoints" min="1" value="<?php echo $question_data ? intval($question_data['points']) : '1'; ?>" required>
            <div class="form-text">How many points this question is worth.</div>
            <div class="invalid-feedback">Please enter a valid point value (minimum 1).</div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Correct Answer <span class="text-danger">*</span></label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="correctAnswer" id="answerTrue" value="true"
                    <?php echo $true_is_correct ? 'checked' : ''; ?>>
                <label class="form-check-label" for="answerTrue">True</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="correctAnswer" id="answerFalse" value="false"
                    <?php echo $false_is_correct || (empty($answers_data) && !$true_is_correct) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="answerFalse">False</label>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="questionExplanation" class="form-label">Explanation (Optional)</label>
            <textarea class="form-control" id="questionExplanation" rows="2" 
                placeholder="Explain why the statement is true or false"><?php echo $question_data && $question_data['explanation'] ? htmlspecialchars($question_data['explanation']) : ''; ?></textarea>
            <div class="form-text">Provide an explanation that will be shown after answering.</div>
        </div>
        
        <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="saveQuestionBtn" class="btn btn-primary" data-question-type="true_false">
                <?php echo $question_id ? 'Update' : 'Save'; ?> Question
            </button>
        </div>
    </form>
    <?php
} else {
    // Unsupported question type
    echo '<div class="alert alert-danger">Unsupported question type</div>';
}
?>