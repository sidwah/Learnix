<?php
// grades.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/signin-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Check if course_id is provided in the URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    echo "<script>window.location.href ='courses.php'; </script>";
    exit();
}

// Get course ID from URL
$course_id = intval($_GET['course_id']);

// Connect to database
require_once '../backend/config.php';

// First, check if user is enrolled in this course
$enrollment_query = "SELECT enrollment_id, status FROM enrollments 
                     WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$stmt = $conn->prepare($enrollment_query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    header("Location: courses.php");
    exit();
}
$enrollment = $enrollment_result->fetch_assoc();
$enrollment_id = $enrollment['enrollment_id'];

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, 
              cat.name AS category_name, sub.name AS subcategory_name
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
        WHERE c.course_id = ? AND c.status = 'Published'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>window.location.href ='courses.php'; </script>";
    exit();
}

$course = $result->fetch_assoc();

// Get the maximum number of attempts allowed for quizzes
$max_attempts_query = "SELECT COALESCE(MAX(attempts_allowed), 3) as max_attempts 
                       FROM section_quizzes 
                       WHERE section_id IN (SELECT section_id FROM course_sections WHERE course_id = ?)";
$stmt = $conn->prepare($max_attempts_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$max_attempts_result = $stmt->get_result();
$max_attempts = $max_attempts_result->fetch_assoc()['max_attempts'];

// Fetch all quiz attempts for this course with more details
$quiz_attempts_query = "SELECT 
                            sq.quiz_id, 
                            sq.quiz_title, 
                            sq.attempts_allowed,
                            sq.time_limit,
                            sqa.attempt_id,
                            sqa.score,
                            sqa.passed,
                            sqa.start_time,
                            sqa.end_time,
                            sqa.attempt_number,
                            sqa.time_spent,
                            s.section_id,
                            s.title as section_title,
                            s.position as section_position,
                            sq.pass_mark,
                            COUNT(DISTINCT qq.question_id) as total_questions,
                            COALESCE(
                                (SELECT COUNT(*) 
                                 FROM student_question_responses sqr 
                                 JOIN quiz_questions qq2 ON sqr.question_id = qq2.question_id
                                 WHERE sqr.attempt_id = sqa.attempt_id AND sqr.is_correct = 1), 
                                0
                            ) as correct_answers
                        FROM section_quizzes sq
                        JOIN course_sections s ON sq.section_id = s.section_id
                        LEFT JOIN quiz_questions qq ON sq.quiz_id = qq.quiz_id
                        LEFT JOIN student_quiz_attempts sqa ON sq.quiz_id = sqa.quiz_id AND sqa.user_id = ?
                        WHERE s.course_id = ?
                        GROUP BY sq.quiz_id, sqa.attempt_id
                        ORDER BY s.position, sq.quiz_id, sqa.attempt_number DESC";

$stmt = $conn->prepare($quiz_attempts_query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$quiz_attempts_result = $stmt->get_result();

// Organize quiz attempts by section
$quizzes_by_section = [];
$all_attempts = [];

while ($attempt = $quiz_attempts_result->fetch_assoc()) {
    $section_id = $attempt['section_id'];
    $section_title = $attempt['section_title'];
    $section_position = $attempt['section_position'];
    
    if (!isset($quizzes_by_section[$section_id])) {
        $quizzes_by_section[$section_id] = [
            'title' => $section_title,
            'position' => $section_position,
            'quizzes' => []
        ];
    }
    
    // Check if this quiz is already in the array
    $quiz_id = $attempt['quiz_id'];
    if (!isset($quizzes_by_section[$section_id]['quizzes'][$quiz_id])) {
        $quizzes_by_section[$section_id]['quizzes'][$quiz_id] = [
            'quiz_id' => $quiz_id,
            'quiz_title' => $attempt['quiz_title'],
            'pass_mark' => $attempt['pass_mark'],
            'time_limit' => $attempt['time_limit'],
            'attempts_allowed' => $attempt['attempts_allowed'] ?: $max_attempts,
            'total_questions' => $attempt['total_questions'],
            'highest_score' => 0,
            'attempts_used' => 0,
            'attempts' => []
        ];
    }
    
    // Add this attempt if it exists
    if ($attempt['attempt_id']) {
        $attempt_data = [
            'attempt_id' => $attempt['attempt_id'],
            'score' => $attempt['score'],
            'passed' => $attempt['passed'],
            'start_time' => $attempt['start_time'],
            'end_time' => $attempt['end_time'],
            'attempt_number' => $attempt['attempt_number'],
            'time_spent' => $attempt['time_spent'],
            'correct_answers' => $attempt['correct_answers'],
            'total_questions' => $attempt['total_questions'],
            'quiz_title' => $attempt['quiz_title'],
            'section_title' => $section_title
        ];
        
        $quizzes_by_section[$section_id]['quizzes'][$quiz_id]['attempts'][] = $attempt_data;
        $quizzes_by_section[$section_id]['quizzes'][$quiz_id]['attempts_used'] = max(
            $quizzes_by_section[$section_id]['quizzes'][$quiz_id]['attempts_used'],
            $attempt['attempt_number']
        );
        
        // Track highest score
        if ($attempt['score'] > $quizzes_by_section[$section_id]['quizzes'][$quiz_id]['highest_score']) {
            $quizzes_by_section[$section_id]['quizzes'][$quiz_id]['highest_score'] = $attempt['score'];
        }
        
        // Add to all attempts array for the history section
        $all_attempts[] = $attempt_data;
    }
}

// Sort sections by position
uasort($quizzes_by_section, function($a, $b) {
    return $a['position'] <=> $b['position'];
});

// Calculate overall course performance
$total_quizzes = 0;
$completed_quizzes = 0;
$passed_quizzes = 0;
$highest_total_score = 0;
$total_questions_answered = 0;
$total_correct_answers = 0;

foreach ($quizzes_by_section as $section) {
    foreach ($section['quizzes'] as $quiz) {
        $total_quizzes++;
        
        if (!empty($quiz['attempts'])) {
            $completed_quizzes++;
            $highest_total_score += $quiz['highest_score'];
            
            // Check if any attempt passed
            $has_passed = false;
            $best_correct = 0;
            $best_total = 1; // Prevent division by zero
            
            foreach ($quiz['attempts'] as $attempt) {
                if ($attempt['passed']) {
                    $has_passed = true;
                }
                
                if ($attempt['correct_answers'] > $best_correct) {
                    $best_correct = $attempt['correct_answers'];
                    $best_total = max(1, $attempt['total_questions']);
                }
            }
            
            if ($has_passed) {
                $passed_quizzes++;
            }
            
            $total_questions_answered += $best_total;
            $total_correct_answers += $best_correct;
        }
    }
}

$average_highest_score = ($total_quizzes > 0) ? round($highest_total_score / $total_quizzes, 1) : 0;
$completion_rate = ($total_quizzes > 0) ? round(($completed_quizzes / $total_quizzes) * 100) : 0;
$accuracy_rate = ($total_questions_answered > 0) ? round(($total_correct_answers / $total_questions_answered) * 100) : 0;

// Sort attempts by date (newest first)
usort($all_attempts, function($a, $b) {
    return strtotime($b['end_time'] ?? $b['start_time']) - strtotime($a['end_time'] ?? $a['start_time']);
});

// Recent attempts (last 10)
$recent_attempts = array_slice($all_attempts, 0, 10);
?>

<!-- Main Content -->
<main id="content" role="main" class="bg-light">
    <!-- Breadcrumb -->
    <div class="container content-space-t-1 pb-3">
        <div class="row align-items-lg-center">
            <div class="col-lg mb-2 mb-lg-0">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item"><a href="course-materials.php?course_id=<?php echo $course_id; ?>"><?php echo htmlspecialchars($course['title']); ?></a></li>
                        <li class="breadcrumb-item active" aria-current="page">Grades</li>
                    </ol>
                </nav>
                <!-- End Breadcrumb -->
            </div>
        </div>
    </div>
    
    <div class="container py-5">
        <div class="row">
            <!-- Left Sidebar - Course Info Summary -->
            <div class="col-lg-4 mb-4 mb-lg-0">
                <!-- Course Info Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <p class="card-text text-muted small">
                            <i class="bi bi-person-circle me-1"></i> 
                            <?php echo htmlspecialchars($course['first_name'] . ' ' . $course['last_name']); ?>
                        </p>
                        
                        <p class="card-text text-muted small">
                            <i class="bi bi-folder me-1"></i> 
                            <?php echo htmlspecialchars($course['category_name'] . ' > ' . $course['subcategory_name']); ?>
                        </p>
                        
                        <hr>
                        
                        <div class="d-grid gap-2">
                            <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Course
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Grade Summary Card -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light py-3">
                        <h5 class="mb-0 fw-bold">Grade Summary</h5>
                    </div>
                    <div class="card-body">
                        <!-- Overall Grade -->
                        <div class="text-center mb-4">
                            <div class="display-1 fw-bold <?php echo ($average_highest_score >= 70) ? 'text-success' : (($average_highest_score >= 50) ? 'text-warning' : 'text-danger'); ?>">
                                <?php echo $average_highest_score; ?>%
                            </div>
                            <p class="text-muted">Best Performance</p>
                        </div>
                        
                        <!-- Grade Stats -->
                        <div class="row text-center g-3 mb-4 small">
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h4 mb-0"><?php echo $completion_rate; ?>%</div>
                                    <div class="small text-muted">Completion</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h4 mb-0"><?php echo $passed_quizzes; ?>/<?php echo $total_quizzes; ?></div>
                                    <div class="small text-muted">Passed</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-2">
                                    <div class="h4 mb-0"><?php echo $accuracy_rate; ?>%</div>
                                    <div class="small text-muted">Accuracy</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Achievements -->
                        <div class="mt-4">
                            <h6 class="fw-bold">Assessment Achievements</h6>
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <?php if ($passed_quizzes > 0): ?>
                                    <div class="badge bg-success p-2">
                                        <i class="bi bi-award-fill me-1"></i> Passed <?php echo $passed_quizzes; ?> Quiz<?php echo $passed_quizzes > 1 ? 'zes' : ''; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($accuracy_rate >= 80): ?>
                                    <div class="badge bg-primary p-2">
                                        <i class="bi bi-bullseye me-1"></i> High Accuracy (<?php echo $accuracy_rate; ?>%)
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($completion_rate > 0): ?>
                                    <div class="badge bg-info text-dark p-2">
                                        <i class="bi bi-check-circle-fill me-1"></i> <?php echo $completion_rate; ?>% Complete
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (count($all_attempts) >= 5): ?>
                                    <div class="badge bg-secondary p-2">
                                        <i class="bi bi-star-fill me-1"></i> <?php echo count($all_attempts); ?> Attempts
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Main Grade Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Course Assessments</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($quizzes_by_section)): ?>
                            <div class="p-4 text-center">
                                <div class="mb-3">
                                    <i class="bi bi-clipboard-data text-muted" style="font-size: 3rem;"></i>
                                </div>
                                <h5>No Assessments Yet</h5>
                                <p class="text-muted">This course doesn't have any graded assessments yet, or you haven't attempted any quizzes.</p>
                                <a href="course-materials.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary">
                                    Go to Course Materials
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Assessment</th>
                                            <th scope="col" class="text-center">Status</th>
                                            <th scope="col" class="text-center">Best Score</th>
                                            <th scope="col" class="text-center">Attempts</th>
                                            <th scope="col" class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($quizzes_by_section as $section_id => $section): ?>
                                            <!-- Section Header -->
                                            <tr class="table-light">
                                                <td colspan="5" class="fw-bold"><?php echo htmlspecialchars($section['title']); ?></td>
                                            </tr>
                                            
                                            <?php foreach ($section['quizzes'] as $quiz): ?>
                                                <?php 
                                                $has_passed = false;
                                                $is_incomplete = false;
                                                $status_class = "bg-secondary";
                                                $status_text = "Not Started";
                                                
                                                if (!empty($quiz['attempts'])) {
                                                    foreach ($quiz['attempts'] as $attempt) {
                                                        if ($attempt['passed']) {
                                                            $has_passed = true;
                                                            break;
                                                        }
                                                    }
                                                    
                                                    // Check if any attempt is incomplete (started but not ended)
                                                    $is_incomplete = !empty($quiz['attempts'][0]['start_time']) && empty($quiz['attempts'][0]['end_time']);
                                                    
                                                    if ($has_passed) {
                                                        $status_class = "bg-success";
                                                        $status_text = "Passed";
                                                    } else if ($is_incomplete) {
                                                        $status_class = "bg-warning text-dark";
                                                        $status_text = "In Progress";
                                                    } else {
                                                        $status_class = "bg-danger";
                                                        $status_text = "Failed";
                                                    }
                                                }
                                                
                                                $attempts_remaining = $quiz['attempts_allowed'] - $quiz['attempts_used'];
                                                ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-3">
                                                                <i class="bi bi-clipboard-check fs-4 text-primary"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-medium"><?php echo htmlspecialchars($quiz['quiz_title']); ?></div>
                                                                <div class="small text-muted">
                                                                    Pass Mark: <?php echo $quiz['pass_mark']; ?>% • 
                                                                    <?php echo $quiz['total_questions']; ?> questions • 
                                                                    <?php echo $quiz['time_limit']; ?> min
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if (!empty($quiz['attempts'])): ?>
                                                            <span class="<?php echo $has_passed ? 'text-success fw-bold' : 'text-danger'; ?>">
                                                                <?php echo $quiz['highest_score']; ?>%
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">--</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="d-flex flex-column align-items-center">
                                                            <span><?php echo $quiz['attempts_used']; ?>/<?php echo $quiz['attempts_allowed']; ?></span>
                                                            <?php if ($attempts_remaining > 0 && !$has_passed): ?>
                                                                <span class="badge bg-light text-dark mt-1">
                                                                    <?php echo $attempts_remaining; ?> remaining
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                    <td class="text-end">
                                                        <?php if (empty($quiz['attempts'])): ?>
                                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-primary">
                                                                <i class="bi bi-play-fill me-1"></i> Start
                                                            </a>
                                                        <?php elseif ($is_incomplete): ?>
                                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-warning">
                                                                <i class="bi bi-arrow-right me-1"></i> Continue
                                                            </a>
                                                        <?php elseif (!$has_passed && $attempts_remaining > 0): ?>
                                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-arrow-clockwise me-1"></i> Retry
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="bi bi-eye me-1"></i> Review
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Attempt History -->
                <?php if (!empty($recent_attempts)): ?>
                    <div class="card border-0 shadow-sm mt-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 fw-bold">Recent Attempt History</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Quiz</th>
                                            <th scope="col">Section</th>
                                            <th scope="col" class="text-center">Date</th>
                                            <th scope="col" class="text-center">Score</th>
                                            <th scope="col" class="text-center">Time Spent</th>
                                            <th scope="col" class="text-center">Result</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_attempts as $attempt): ?>
                                            <tr>
                                                <td>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($attempt['quiz_title']); ?></div>
                                                    <div class="small text-muted">Attempt #<?php echo $attempt['attempt_number']; ?></div>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo htmlspecialchars($attempt['section_title']); ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    $date = $attempt['end_time'] ?? $attempt['start_time'];
                                                    echo date('M j, Y', strtotime($date)); 
                                                    ?>
                                                    <div class="small text-muted">
                                                        <?php echo date('g:i A', strtotime($date)); ?>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="fw-medium <?php echo $attempt['passed'] ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo $attempt['score']; ?>%
                                                    </div>
                                                    <div class="small text-muted">
                                                        <?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?> correct
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <?php 
                                                    if (!empty($attempt['time_spent'])) {
                                                        $minutes = floor($attempt['time_spent'] / 60);
                                                        $seconds = $attempt['time_spent'] % 60;
                                                        echo "{$minutes}m {$seconds}s";
                                                    } else {
                                                        echo "--";
                                                    }
                                                    ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if (empty($attempt['end_time'])): ?>
                                                        <span class="badge bg-warning text-dark">In Progress</span>
                                                    <?php elseif ($attempt['passed']): ?>
                                                        <span class="badge bg-success">Passed</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Failed</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php if (count($all_attempts) > 10): ?>
                            <div class="card-footer bg-white text-center py-3">
                                <button type="button" class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="modal" data-bs-target="#allAttemptsModal">
                                    View All Attempts (<?php echo count($all_attempts); ?>)
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Grading Guide -->
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold">Grading Guide</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Score Interpretation</h6>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item px-0 d-flex align-items-center border-0 pb-2">
                                        <span class="badge bg-success me-2">90-100%</span>
                                        <span>Excellent</span>
                                    </li>
                                    <li class="list-group-item px-0 d-flex align-items-center border-0 pb-2">
                                        <span class="badge bg-primary me-2">80-89%</span>
                                        <span>Very Good</span>
                                    </li>
                                    <li class="list-group-item px-0 d-flex align-items-center border-0 pb-2">
                                        <span class="badge bg-info text-dark me-2">70-79%</span>
                                        <span>Good</span>
                                    </li>
                                    <li class="list-group-item px-0 d-flex align-items-center border-0 pb-2">
                                        <span class="badge bg-warning text-dark me-2">60-69%</span>
                                        <span>Satisfactory</span>
                                    </li>
                                    <li class="list-group-item px-0 d-flex align-items-center border-0">
                                        <span class="badge bg-danger me-2">0-59%</span>
                                        <span>Needs Improvement</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold mb-3">Assessment Policies</h6>
                                <ul class="list-unstyled">
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Quizzes can be attempted up to <?php echo $max_attempts; ?> times
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Your highest score is recorded
                                        </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Most quizzes require <?php echo $course['pass_mark'] ?? 70; ?>% to pass
                                    </li>
                                    <li class="mb-2">
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        Incomplete attempts may be resumed
                                    </li>
                                    <li>
                                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                                        All quizzes must be completed to earn a certificate
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for All Attempts -->
    <?php if (count($all_attempts) > 10): ?>
    <div class="modal fade" id="allAttemptsModal" tabindex="-1" aria-labelledby="allAttemptsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="allAttemptsModalLabel">All Quiz Attempts</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th scope="col">Quiz</th>
                                    <th scope="col">Section</th>
                                    <th scope="col" class="text-center">Date</th>
                                    <th scope="col" class="text-center">Score</th>
                                    <th scope="col" class="text-center">Time Spent</th>
                                    <th scope="col" class="text-center">Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_attempts as $attempt): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-medium"><?php echo htmlspecialchars($attempt['quiz_title']); ?></div>
                                            <div class="small text-muted">Attempt #<?php echo $attempt['attempt_number']; ?></div>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo htmlspecialchars($attempt['section_title']); ?>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            $date = $attempt['end_time'] ?? $attempt['start_time'];
                                            echo date('M j, Y', strtotime($date)); 
                                            ?>
                                            <div class="small text-muted">
                                                <?php echo date('g:i A', strtotime($date)); ?>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="fw-medium <?php echo $attempt['passed'] ? 'text-success' : 'text-danger'; ?>">
                                                <?php echo $attempt['score']; ?>%
                                            </div>
                                            <div class="small text-muted">
                                                <?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?> correct
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <?php 
                                            if (!empty($attempt['time_spent'])) {
                                                $minutes = floor($attempt['time_spent'] / 60);
                                                $seconds = $attempt['time_spent'] % 60;
                                                echo "{$minutes}m {$seconds}s";
                                            } else {
                                                echo "--";
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if (empty($attempt['end_time'])): ?>
                                                <span class="badge bg-warning text-dark">In Progress</span>
                                            <?php elseif ($attempt['passed']): ?>
                                                <span class="badge bg-success">Passed</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<?php include '../includes/student-footer.php'; ?>