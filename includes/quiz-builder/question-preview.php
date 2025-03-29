<?php
// Include session check
require_once '../../backend/session_start.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

// Get question ID from request
$question_id = isset($_GET['question_id']) ? intval($_GET['question_id']) : 0;

if (!$question_id) {
    echo '<div class="alert alert-danger">Question ID not specified</div>';
    exit;
}

// Connect to database
require_once '../../backend/config.php';

// Query to get the question details
$question_query = "SELECT qq.*, sq.section_id, sq.quiz_title, sq.show_correct_answers, cs.course_id, c.instructor_id 
                 FROM quiz_questions qq
                 JOIN section_quizzes sq ON qq.quiz_id = sq.quiz_id
                 JOIN course_sections cs ON sq.section_id = cs.section_id
                 JOIN courses c ON cs.course_id = c.course_id
                 WHERE qq.question_id = ?";
                 
$stmt = $conn->prepare($question_query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$question_result = $stmt->get_result();

if ($question_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Question not found</div>';
    exit;
}

$question = $question_result->fetch_assoc();
$stmt->close();

// Verify instructor's access to this question
if ($question['instructor_id'] != $_SESSION['instructor_id']) {
    echo '<div class="alert alert-danger">You do not have permission to view this question</div>';
    exit;
}

// Get answers for this question
$answers_query = "SELECT * FROM quiz_answers WHERE question_id = ? ORDER BY answer_id";
$stmt = $conn->prepare($answers_query);
$stmt->bind_param("i", $question_id);
$stmt->execute();
$answers_result = $stmt->get_result();
$answers = [];

while ($answer = $answers_result->fetch_assoc()) {
    $answers[] = $answer;
}
$stmt->close();

// Determine if it's a multiple choice or true/false question
$is_multiple_choice = (stripos($question['question_type'], 'Multiple Choice') !== false);
$is_true_false = (stripos($question['question_type'], 'True/False') !== false);

// Functions to randomize answer order for preview purposes
function shuffleAnswers($answers) {
    $keys = array_keys($answers);
    shuffle($keys);
    $shuffled = [];
    foreach($keys as $key) {
        $shuffled[$key] = $answers[$key];
    }
    return $shuffled;
}

// Build the student view version of the question
?>

<div class="student-preview-container">
    <div class="card">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Student View Preview</h5>
                <span class="badge bg-primary">
                    <?php echo $is_multiple_choice ? 'Multiple Choice' : ($is_true_false ? 'True/False' : $question['question_type']); ?>
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="question-container mb-4">
                <div class="question-text mb-3">
                    <h5><?php echo htmlspecialchars($question['question_text']); ?></h5>
                    <div class="text-muted small"><?php echo $question['points']; ?> <?php echo $question['points'] > 1 ? 'points' : 'point'; ?></div>
                </div>
                
                <div class="answers-container">
                    <?php 
                    // Use a randomized copy of answers for preview effect
                    $preview_answers = $answers;
                    // Uncomment the line below to simulate shuffle answers option
                    // $preview_answers = shuffleAnswers($answers);
                    
                    if ($is_multiple_choice): 
                    ?>
                        <div class="form-text mb-2">Select the correct answer:</div>
                        <?php foreach($preview_answers as $index => $answer): ?>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="previewAnswer" id="previewAnswer<?php echo $index; ?>" disabled>
                                <label class="form-check-label" for="previewAnswer<?php echo $index; ?>">
                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    <?php elseif ($is_true_false): ?>
                        <div class="form-text mb-2">Select the correct answer:</div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="previewAnswer" id="previewAnswerTrue" disabled>
                            <label class="form-check-label" for="previewAnswerTrue">True</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="previewAnswer" id="previewAnswerFalse" disabled>
                            <label class="form-check-label" for="previewAnswerFalse">False</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="feedback-section">
                <h6 class="mb-3">After Submission (if "Show Correct Answers" is enabled):</h6>
                
                <?php if ($is_multiple_choice): ?>
                    <div class="correct-answers mt-3">
                        <div class="mb-2 fw-bold">Correct Answer(s):</div>
                        <ul class="list-group">
                            <?php foreach($answers as $answer): ?>
                                <?php if ($answer['is_correct']): ?>
                                    <li class="list-group-item list-group-item-success">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                        <i class="mdi mdi-check-circle float-end"></i>
                                    </li>
                                <?php else: ?>
                                    <li class="list-group-item">
                                        <?php echo htmlspecialchars($answer['answer_text']); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php elseif ($is_true_false): ?>
                    <div class="correct-answers mt-3">
                        <div class="mb-2 fw-bold">Correct Answer:</div>
                        <?php 
                        $correct_answer = 'Unknown';
                        foreach($answers as $answer) {
                            if ($answer['is_correct']) {
                                $correct_answer = $answer['answer_text'];
                                break;
                            }
                        }
                        ?>
                        <div class="alert alert-success">
                            <?php echo $correct_answer; ?>
                            <i class="mdi mdi-check-circle float-end"></i>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($question['explanation'])): ?>
                    <div class="explanation mt-3">
                        <div class="mb-2 fw-bold">Explanation:</div>
                        <div class="alert alert-info">
                            <i class="mdi mdi-lightbulb-outline me-2"></i>
                            <?php echo htmlspecialchars($question['explanation']); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="text-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close Preview</button>
                
                <div class="form-text mt-3">
                    <i class="mdi mdi-information-outline me-1"></i>
                    This is a preview of how students will see this question.
                </div>
            </div>
        </div>
    </div>
</div>