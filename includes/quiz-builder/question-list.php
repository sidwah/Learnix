<?php
// This file loads the list of questions for a quiz

// Validate user is signed in and is an instructor
require_once '../../backend/session_start.php';
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo '<div class="alert alert-danger">Unauthorized access</div>';
    exit;
}

// Get required parameters
if (!isset($_GET['quiz_id'])) {
    echo '<div class="alert alert-danger">Missing quiz ID</div>';
    exit;
}

$quiz_id = intval($_GET['quiz_id']);

// Connect to database
require_once '../../backend/config.php';

// Verify instructor's access to this quiz - FIXED: Using course_instructors junction table instead
$instructor_id = $_SESSION['instructor_id'];
$access_check_query = "SELECT sq.quiz_id 
                       FROM section_quizzes sq 
                       JOIN course_sections cs ON sq.section_id = cs.section_id 
                       JOIN course_instructors ci ON cs.course_id = ci.course_id 
                       WHERE sq.quiz_id = ? 
                       AND ci.instructor_id = ?
                       AND ci.deleted_at IS NULL";
$stmt = $conn->prepare($access_check_query);
$stmt->bind_param("ii", $quiz_id, $instructor_id);
$stmt->execute();
$access_result = $stmt->get_result();

if ($access_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Unauthorized access to this quiz</div>';
    exit;
}
$stmt->close();

// Load questions for this quiz
$questions_query = "SELECT * FROM quiz_questions 
                    WHERE quiz_id = ? 
                    ORDER BY question_order ASC";
$stmt = $conn->prepare($questions_query);
$stmt->bind_param("i", $quiz_id);
$stmt->execute();
$questions_result = $stmt->get_result();

// If no questions found
if ($questions_result->num_rows === 0) {
    ?>
    <div class="text-center py-5" id="noQuestionsMessage">
        <div class="mb-3">
            <i class="mdi mdi-help-circle-outline" style="font-size: 64px; color: #adb5bd;"></i>
        </div>
        <h5>No Questions Added Yet</h5>
        <p class="text-muted">Start adding questions using the button above.</p>
    </div>
    <?php
    exit;
}

// Questions found, display them
$total_points = 0;

?>
<div class="row mb-3">
    <div class="col-md-12">
        <div class="alert alert-info">
            <h5 class="mb-1"><i class="mdi mdi-information-outline"></i> Quiz Overview</h5>
            <div class="d-flex flex-wrap gap-4 mt-2">
                <div>
                    <strong>Total Questions:</strong> <span id="totalQuestions"><?php echo $questions_result->num_rows; ?></span>
                </div>
                <div>
                    <strong>Total Points:</strong> <span id="totalPoints">0</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="list-group question-list">
    <?php while ($question = $questions_result->fetch_assoc()): 
        $total_points += $question['points'];
        
        // Get answers for this question
        $answers_query = "SELECT * FROM quiz_answers 
                         WHERE question_id = ? 
                         ORDER BY answer_id ASC";
        $answers_stmt = $conn->prepare($answers_query);
        $answers_stmt->bind_param("i", $question['question_id']);
        $answers_stmt->execute();
        $answers_result = $answers_stmt->get_result();
        
        // Count total and correct answers
        $total_answers = $answers_result->num_rows;
        $correct_answers = 0;
        
        while ($answer = $answers_result->fetch_assoc()) {
            if ($answer['is_correct']) {
                $correct_answers++;
            }
        }
        
        // Reset to get answers again
        $answers_result->data_seek(0);
    ?>
        <div class="list-group-item question-item mb-3" data-question-id="<?php echo $question['question_id']; ?>">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <span class="badge bg-primary me-2"><?php echo $question['question_type']; ?></span>
                    <span class="badge bg-secondary"><?php echo $question['points']; ?> Points</span>
                </div>
                <div class="question-actions">
                    <button type="button" class="btn btn-sm btn-outline-secondary preview-question-btn me-1"
                        data-question-id="<?php echo $question['question_id']; ?>">
                        <i class="mdi mdi-eye"></i> Preview
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary edit-question-btn me-1"
                        data-question-id="<?php echo $question['question_id']; ?>"
                        data-question-type="<?php echo $question['question_type'] === 'Multiple Choice' ? 'multiple_choice' : 'true_false'; ?>">
                        <i class="mdi mdi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-question-btn"
                        data-question-id="<?php echo $question['question_id']; ?>">
                        <i class="mdi mdi-delete"></i> Delete
                    </button>
                </div>
            </div>
            
            <h5 class="question-text mb-2"><?php echo htmlspecialchars($question['question_text']); ?></h5>
            
            <?php if ($question['question_type'] === 'Multiple Choice'): ?>
                <div class="answers-list ms-3">
                    <?php while ($answer = $answers_result->fetch_assoc()): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" disabled <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                            <label class="form-check-label <?php echo $answer['is_correct'] ? 'fw-bold text-success' : ''; ?>">
                                <?php echo htmlspecialchars($answer['answer_text']); ?>
                                <?php if ($answer['is_correct']): ?>
                                    <i class="mdi mdi-check-circle text-success"></i>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php elseif ($question['question_type'] === 'True/False'): ?>
                <div class="answers-list ms-3">
                    <?php while ($answer = $answers_result->fetch_assoc()): ?>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="radio" disabled <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                            <label class="form-check-label <?php echo $answer['is_correct'] ? 'fw-bold text-success' : ''; ?>">
                                <?php echo htmlspecialchars($answer['answer_text']); ?>
                                <?php if ($answer['is_correct']): ?>
                                    <i class="mdi mdi-check-circle text-success"></i>
                                <?php endif; ?>
                            </label>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($question['explanation'])): ?>
                <div class="explanation mt-2">
                    <strong>Explanation:</strong> <span class="text-muted"><?php echo htmlspecialchars($question['explanation']); ?></span>
                </div>
            <?php endif; ?>
        </div>
    <?php 
        $answers_stmt->close();
        endwhile; 
    ?>
</div>

<script>
    // Update total points in the overview
    document.getElementById('totalPoints').textContent = <?php echo $total_points; ?>;
</script>

<style>
    .question-item {
        border-left: 4px solid #3e7bfa;
        transition: all 0.2s ease;
    }
    
    .question-item:hover {
        background-color: #f8f9fa;
    }
    
    .answers-list {
        margin-top: 10px;
    }
    
    .explanation {
        font-size: 0.9rem;
        padding: 8px;
        background-color: #f8f9fa;
        border-radius: 4px;
    }
</style>