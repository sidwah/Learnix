<?php
// Path: ajax/department/load_quiz_preview.php
require '../../backend/session_start.php';

// Check if user is signed in as department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get quiz ID from request
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// If no quiz ID or course ID is provided, return error
if ($quiz_id === 0 || $course_id === 0) {
    echo '<div class="alert alert-danger">Invalid parameters</div>';
    exit;
}

// Include database connection
require_once '../../backend/config.php';

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo '<div class="alert alert-danger">Department access error</div>';
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Check if the course belongs to the department head's department
$course_check_query = "SELECT course_id FROM courses WHERE course_id = ? AND department_id = ? AND deleted_at IS NULL";
$course_check_stmt = $conn->prepare($course_check_query);
$course_check_stmt->bind_param("ii", $course_id, $department_id);
$course_check_stmt->execute();
$course_access = ($course_check_stmt->get_result()->num_rows > 0);
$course_check_stmt->close();

if (!$course_access) {
    echo '<div class="alert alert-danger">You do not have permission to view this course</div>';
    exit;
}

// Fetch quiz details
$quiz_query = "SELECT sq.*, cs.course_id
              FROM section_quizzes sq
              JOIN course_sections cs ON sq.section_id = cs.section_id
              WHERE sq.quiz_id = ? AND cs.course_id = ?";
$stmt = $conn->prepare($quiz_query);
$stmt->bind_param("ii", $quiz_id, $course_id);
$stmt->execute();
$quiz = $stmt->get_result()->fetch_assoc();
$stmt->close();

// If quiz not found, return error
if (!$quiz) {
    echo '<div class="alert alert-danger">Quiz not found</div>';
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

// Quiz header with review indicator
echo '<div class="mb-4 d-flex justify-content-between align-items-center">';
echo '<h3>' . htmlspecialchars($quiz['quiz_title']) . '</h3>';
echo '<span class="badge bg-primary">Under Review</span>';
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

// If no questions
if (empty($questions)) {
    echo '<div class="alert alert-warning">';
    echo '<h5 class="alert-heading">No Questions Added</h5>';
    echo '<p>This quiz does not have any questions. The instructor needs to add questions for students.</p>';
    echo '</div>';
} else {
    // Questions display
    echo '<div class="questions-container">';
    
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
                    $is_correct = $answer['is_correct'] ? ' text-success' : '';
                    $correct_badge = $answer['is_correct'] ? ' <span class="badge bg-success ms-2">Correct Answer</span>' : '';

                    echo '<div class="form-check mb-2' . $is_correct . '">';
                    echo '<input class="form-check-input" type="radio" disabled ';
                    echo 'id="' . $answer_id . '" value="' . $answer['answer_id'] . '">';
                    echo '<label class="form-check-label" for="' . $answer_id . '">';
                    echo htmlspecialchars($answer['answer_text']) . $correct_badge;
                    echo '</label>';
                    echo '</div>';
                }
                echo '</div>';
                break;

            case 'True/False':
                echo '<div class="answers-container">';
                foreach ($answers as $answer) {
                    $answer_id = 'answer_' . $question['question_id'] . '_' . $answer['answer_id'];
                    $is_correct = $answer['is_correct'] ? ' text-success' : '';
                    $correct_badge = $answer['is_correct'] ? ' <span class="badge bg-success ms-2">Correct Answer</span>' : '';

                    echo '<div class="form-check mb-2' . $is_correct . '">';
                    echo '<input class="form-check-input" type="radio" disabled ';
                    echo 'id="' . $answer_id . '" value="' . $answer['answer_id'] . '">';
                    echo '<label class="form-check-label" for="' . $answer_id . '">';
                    echo htmlspecialchars($answer['answer_text']) . $correct_badge;
                    echo '</label>';
                    echo '</div>';
                }
                echo '</div>';
                break;

            default:
                echo '<div class="alert alert-info">Preview for this question type is not available in review mode.</div>';
        }

        echo '</div>'; // End card-body

        // Display explanation if available
        if (!empty($question['explanation'])) {
            echo '<div class="card-footer bg-light">';
            echo '<div class="mb-0">';
            echo '<strong>Explanation:</strong> ' . htmlspecialchars($question['explanation']);
            echo '</div>';
            echo '</div>'; // End card-footer
        }

        echo '</div>'; // End question-card
    }
    
    echo '</div>'; // End questions-container
}

echo '</div>'; // End quiz-preview-container
?>