<?php
// grades.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

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
uasort($quizzes_by_section, function ($a, $b) {
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
usort($all_attempts, function ($a, $b) {
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

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-gradient py-2" style="background: linear-gradient(90deg, #007bff, #0056b3); color: white;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2"></i>Grade Summary</h5>
                    </div>
                    <div class="card-body p-3">
                        <style>
                            .grade-card {
                                background-color: #ffffff;
                                border-radius: 8px;
                                overflow: hidden;
                                font-size: 0.95rem;
                            }

                            .grade-circle {
                                width: 100px;
                                height: 100px;
                                border-radius: 50%;
                                background-color: #f8f9fa;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                margin: 0 auto 0.5rem;
                                border: 3px solid;
                                transition: transform 0.2s;
                            }

                            .grade-circle:hover {
                                transform: scale(1.05);
                            }

                            .stat-tile {
                                border: 1px solid #dee2e6;
                                border-radius: 6px;
                                padding: 10px;
                                text-align: center;
                                transition: transform 0.2s, box-shadow 0.2s;
                                background-color: #f8f9fa;
                            }

                            .stat-tile:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                            }

                            .stat-tile .h5 {
                                font-size: 1.1rem;
                                margin-bottom: 0.25rem;
                            }

                            .stat-tile .small {
                                font-size: 0.75rem;
                            }

                            .achievement-badge {
                                padding: 8px 12px;
                                font-size: 0.85rem;
                                border-radius: 12px;
                                display: inline-flex;
                                align-items: center;
                                transition: transform 0.2s;
                                cursor: default;
                            }

                            .achievement-badge:hover {
                                transform: scale(1.03);
                            }

                            .achievement-badge i {
                                font-size: 0.9rem;
                            }

                            .bg-teal {
                                background-color: #20c997;
                            }

                            .tooltip-inner {
                                font-size: 0.8rem;
                            }

                            @media (max-width: 768px) {
                                .grade-circle {
                                    width: 80px;
                                    height: 80px;
                                    font-size: 1.5rem;
                                }

                                .stat-tile {
                                    padding: 8px;
                                    font-size: 0.9rem;
                                }

                                .stat-tile .h5 {
                                    font-size: 1rem;
                                }

                                .achievement-badge {
                                    font-size: 0.8rem;
                                    padding: 6px 10px;
                                }
                            }
                        </style>

                        <!-- Overall Grade -->
                        <div class="text-center mb-3">
                            <div class="grade-circle <?php echo ($average_highest_score >= 70) ? 'border-success text-success' : (($average_highest_score >= 50) ? 'border-warning text-warning' : 'border-danger text-danger'); ?>">
                                <span class="fw-bold" style="font-size: 1.75rem;"><?php echo $average_highest_score; ?>%</span>
                            </div>
                            <p class="text-muted small">Best Performance</p>
                        </div>

                        <!-- Grade Stats -->
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <div class="stat-tile">
                                    <div class="h5"><?php echo $completion_rate; ?>%</div>
                                    <div class="small text-muted"><i class="bi bi-check-circle me-1"></i>Completion</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-tile">
                                    <div class="h5"><?php echo $passed_quizzes; ?>/<?php echo $total_quizzes; ?></div>
                                    <div class="small text-muted"><i class="bi bi-trophy me-1"></i>Passed</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-tile">
                                    <div class="h5"><?php echo $accuracy_rate; ?>%</div>
                                    <div class="small text-muted"><i class="bi bi-bullseye me-1"></i>Accuracy</div>
                                </div>
                            </div>
                        </div>

                        <!-- Achievements -->
                        <div class="mt-3">
                            <h6 class="fw-bold mb-2" style="font-size: 1rem;">Assessment Achievements</h6>
                            <div class="d-flex flex-wrap gap-2">
                                <?php if ($passed_quizzes > 0): ?>
                                    <div class="badge bg-success achievement-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="Passed <?php echo $passed_quizzes; ?> quiz<?php echo $passed_quizzes > 1 ? 'zes' : ''; ?>">
                                        <i class="bi bi-award-fill me-1"></i> <?php echo $passed_quizzes; ?> Passed
                                    </div>
                                <?php endif; ?>
                                <?php if ($accuracy_rate >= 80): ?>
                                    <div class="badge bg-primary achievement-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="Achieved <?php echo $accuracy_rate; ?>% accuracy">
                                        <i class="bi bi-bullseye me-1"></i> High Accuracy
                                    </div>
                                <?php endif; ?>
                                <?php if ($completion_rate > 0): ?>
                                    <div class="badge bg-teal achievement-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="<?php echo $completion_rate; ?>% of the course completed">
                                        <i class="bi bi-check-circle-fill me-1"></i> <?php echo $completion_rate; ?>% Complete
                                    </div>
                                <?php endif; ?>
                                <?php if (count($all_attempts) >= 5): ?>
                                    <div class="badge bg-secondary achievement-badge" data-bs-toggle="tooltip" data-bs-placement="top" title="Made <?php echo count($all_attempts); ?> quiz attempts">
                                        <i class="bi bi-star-fill me-1"></i> <?php echo count($all_attempts); ?> Attempts
                                    </div>
                                <?php endif; ?>
                                <?php if (empty($passed_quizzes) && empty($all_attempts)): ?>
                                    <!-- <p class="text-muted small">No achievements yet. Start taking quizzes to earn badges!</p> -->
                                <?php endif; ?>
                            </div>
                        </div>

                        <script>
                            // Initialize Bootstrap tooltips
                            document.addEventListener('DOMContentLoaded', function() {
                                const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                                tooltipTriggerList.forEach(tooltipTriggerEl => {
                                    new bootstrap.Tooltip(tooltipTriggerEl);
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <!-- Course Assessments and Attempt History -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Course Assessments & History</h5>
                    </div>
                    <div class="card-body p-3">
                        <?php if (empty($quizzes_by_section)): ?>
                            <div class="text-center p-4">
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
                            <style>
                                .quiz-card {
                                    transition: transform 0.2s, box-shadow 0.2s;
                                    border: none;
                                    border-radius: 8px;
                                    overflow: hidden;
                                    background-color: #ffffff;
                                    margin-bottom: 1rem;
                                    font-size: 0.95rem;
                                }

                                .quiz-card:hover {
                                    transform: translateY(-3px);
                                    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.08) !important;
                                }

                                .pass-mark-sidebar {
                                    background-color: #e9ecef;
                                    border-right: 2px solid #dee2e6;
                                    padding: 15px;
                                    border-radius: 8px 0 0 8px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    text-align: center;
                                }

                                .pass-mark-sidebar h6 {
                                    font-size: 0.85rem;
                                    margin-bottom: 0.5rem;
                                }

                                .pass-mark-sidebar h3 {
                                    font-size: 1.25rem;
                                    margin-bottom: 0;
                                }

                                .pass-mark-sidebar p {
                                    font-size: 0.75rem;
                                    margin-top: 0.5rem;
                                }

                                .attempt-toggle {
                                    cursor: pointer;
                                    color: #007bff;
                                    text-decoration: none;
                                    font-size: 0.9rem;
                                    font-weight: 500;
                                }

                                .attempt-toggle:hover {
                                    text-decoration: underline;
                                }

                                .badge-passed {
                                    background-color: #28a745;
                                    font-size: 0.8rem;
                                }

                                .badge-failed {
                                    background-color: #dc3545;
                                    font-size: 0.8rem;
                                }

                                .badge-in-progress {
                                    background-color: #ffc107;
                                    color: #212529;
                                    font-size: 0.8rem;
                                }

                                .attempt-card {
                                    border: 1px solid #dee2e6;
                                    border-radius: 6px;
                                    padding: 10px;
                                    background-color: #f8f9fa;
                                    margin-bottom: 8px;
                                    font-size: 0.85rem;
                                    transition: background-color 0.2s;
                                }

                                .attempt-card:hover {
                                    background-color: #e9ecef;
                                }

                                .section-header {
                                    background-color: #f1f3f5;
                                    padding: 8px 15px;
                                    border-radius: 6px;
                                    margin-bottom: 0.75rem;
                                    font-size: 1.1rem;
                                    font-weight: 600;
                                }

                                .card-body .small {
                                    font-size: 0.8rem;
                                }

                                .btn-sm {
                                    font-size: 0.8rem;
                                    padding: 0.3rem 0.6rem;
                                }

                                @media (max-width: 768px) {
                                    .pass-mark-sidebar {
                                        border-right: none;
                                        border-bottom: 2px solid #dee2e6;
                                        border-radius: 8px 8px 0 0;
                                        padding: 10px;
                                    }

                                    .quiz-card {
                                        font-size: 0.9rem;
                                    }

                                    .attempt-card {
                                        font-size: 0.8rem;
                                    }
                                }
                            </style>

<?php foreach ($quizzes_by_section as $section_id => $section): ?>
    <!-- Section Header -->
    <div class="section-header">
        <i class="bi bi-folder-fill me-2 text-primary"></i>
        <?php echo htmlspecialchars($section['title']); ?>
    </div>

    <?php foreach ($section['quizzes'] as $quiz): ?>
        <?php
        $has_passed = false;
        $is_incomplete = false;
        $status_class = "bg-secondary";
        $status_text = "Not Started";

        if (!empty($quiz['attempts'])) {
            // Get the latest attempt (highest attempt_number)
            usort($quiz['attempts'], function ($a, $b) {
                return $b['attempt_number'] <=> $a['attempt_number'];
            });
            $latest_attempt = $quiz['attempts'][0];

            if (!empty($latest_attempt['start_time']) && empty($latest_attempt['end_time'])) {
                $is_incomplete = true;
                $status_class = "badge-in-progress";
                $status_text = "In Progress";
            } elseif (!empty($latest_attempt['end_time'])) {
                // Attempt is completed
                $status_class = $latest_attempt['passed'] ? "badge-passed" : "badge-failed";
                $status_text = $latest_attempt['passed'] ? "Passed" : "Failed";
                if ($latest_attempt['passed']) {
                    $has_passed = true;
                }
            }
        }

        $attempts_remaining = $quiz['attempts_allowed'] - $quiz['attempts_used'];
        ?>
        <div class="card quiz-card shadow-sm">
            <div class="row g-0">
                <!-- Pass Mark Sidebar -->
                <div class="col-md-2 pass-mark-sidebar">
                    <div>
                        <h6 class="text-muted mb-2">Pass Mark</h6>
                        <h3 class="text-primary mb-0"><?php echo $quiz['pass_mark']; ?>%</h3>
                        <p class="small text-muted mt-2">
                            <i class="bi bi-clock me-1"></i><?php echo $quiz['time_limit']; ?> min
                        </p>
                    </div>
                </div>
                <!-- Quiz Details -->
                <div class="col-md-10">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-2" style="font-size: 1.1rem;">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>
                            <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                        </h5>
                        <div class="row g-2 mb-2">
                            <div class="col-6 col-md-4">
                                <div class="small text-muted">Status</div>
                                <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </div>
                            <div class="col-6 col-md-4">
                                <div class="small text-muted">Best Score</div>
                                <span class="<?php echo $has_passed ? 'text-success fw-bold' : 'text-danger'; ?>">
                                    <?php echo !empty($quiz['attempts']) ? $quiz['highest_score'] . '%' : '--'; ?>
                                </span>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="small text-muted">Attempts</div>
                                <span><?php echo $quiz['attempts_used']; ?>/<?php echo $quiz['attempts_allowed']; ?></span>
                                <?php if ($attempts_remaining > 0 && !$has_passed): ?>
                                    <span class="badge bg-light text-dark ms-1"><?php echo $attempts_remaining; ?> remaining</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">
                                <?php echo $quiz['total_questions']; ?> questions
                            </small>
                        </div>
                        <?php if (!empty($quiz['attempts'])): ?>
                            <p class="text-muted mb-2">
                                <a class="attempt-toggle" data-bs-toggle="collapse" href="#attempts-<?php echo $quiz['quiz_id']; ?>" role="button" aria-expanded="false" aria-controls="attempts-<?php echo $quiz['quiz_id']; ?>">
                                    View <?php echo count($quiz['attempts']); ?> Attempt(s) <i class="bi bi-chevron-down ms-1"></i>
                                </a>
                            </p>
                            <div class="collapse" id="attempts-<?php echo $quiz['quiz_id']; ?>">
                                <?php foreach ($quiz['attempts'] as $attempt): ?>
                                    <div class="attempt-card">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Attempt #<?php echo $attempt['attempt_number']; ?></div>
                                                <span class="badge <?php echo $attempt['passed'] ? 'badge-passed' : ($attempt['end_time'] ? 'badge-failed' : 'badge-in-progress'); ?>">
                                                    <?php echo $attempt['passed'] ? 'Passed' : ($attempt['end_time'] ? 'Failed' : 'In Progress'); ?>
                                                </span>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Score</div>
                                                <span class="<?php echo $attempt['passed'] ? 'text-success' : 'text-danger'; ?>">
                                                    <?php echo $attempt['score']; ?>%
                                                </span>
                                                <div class="small text-muted">
                                                    <?php echo $attempt['correct_answers']; ?>/<?php echo $attempt['total_questions']; ?> correct
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Date</div>
                                                <?php
                                                $date = $attempt['end_time'] ?? $attempt['start_time'];
                                                echo date('M j, Y - g:i A', strtotime($date));
                                                ?>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <div class="small text-muted">Time Spent</div>
                                                <?php
                                                if (!empty($attempt['time_spent'])) {
                                                    $minutes = floor($attempt['time_spent'] / 60);
                                                    $seconds = $attempt['time_spent'] % 60;
                                                    echo "{$minutes}m {$seconds}s";
                                                } else {
                                                    echo '--';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No attempts recorded for this quiz.</p>
                        <?php endif; ?>
                        <div class="mt-2">
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
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
    <script>
        // Toggle collapse icon for attempt details
        document.addEventListener('DOMContentLoaded', function() {
            const toggles = document.querySelectorAll('.attempt-toggle');
            toggles.forEach(toggle => {
                toggle.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-chevron-down');
                    icon.classList.toggle('bi-chevron-up');
                });
            });
        });
    </script>
<?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-gradient py-2" style="background: linear-gradient(90deg, #007bff, #0056b3); color: white;">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle-fill me-2"></i>Grading Guide</h5>
                    </div>
                    <div class="card-body p-3">
                        <style>
                            .grading-card {
                                background-color: #ffffff;
                                border-radius: 8px;
                                overflow: hidden;
                                font-size: 0.95rem;
                            }

                            .policy-item {
                                border: 1px solid #dee2e6;
                                border-radius: 6px;
                                padding: 10px;
                                margin-bottom: 8px;
                                background-color: #f8f9fa;
                                transition: transform 0.2s, box-shadow 0.2s;
                                display: flex;
                                align-items: center;
                                animation: fadeIn 0.5s ease-in;
                            }

                            .policy-item:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                            }

                            .policy-item i {
                                font-size: 1.2rem;
                                color: #28a745;
                                margin-right: 10px;
                            }

                            .policy-item span {
                                font-size: 0.9rem;
                                color: #343a40;
                            }

                            .intro-text {
                                font-size: 0.85rem;
                                color: #6c757d;
                                margin-bottom: 1rem;
                            }

                            @keyframes fadeIn {
                                from {
                                    opacity: 0;
                                    transform: translateY(5px);
                                }

                                to {
                                    opacity: 1;
                                    transform: translateY(0);
                                }
                            }

                            @media (max-width: 768px) {
                                .grading-card {
                                    font-size: 0.9rem;
                                }

                                .policy-item {
                                    padding: 8px;
                                    font-size: 0.85rem;
                                }

                                .policy-item i {
                                    font-size: 1rem;
                                }

                                .policy-item span {
                                    font-size: 0.8rem;
                                }

                                .intro-text {
                                    font-size: 0.8rem;
                                }
                            }
                        </style>

                        <!-- Introductory Text -->
                        <p class="intro-text">Understand how your quizzes are graded and the policies for achieving a certificate.</p>

                        <!-- Assessment Policies -->
                        <div class="grading-card">
                            <div class="policy-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Quizzes can be attempted up to <strong><?php echo $max_attempts; ?></strong> times</span>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Your <strong>highest score</strong> is recorded</span>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Most quizzes require <strong><?php echo $course['pass_mark'] ?? 70; ?>%</strong> to pass</span>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>Incomplete attempts</strong> may be resumed</span>
                            </div>
                            <div class="policy-item">
                                <i class="bi bi-check-circle-fill"></i>
                                <span><strong>All quizzes</strong> must be completed to earn a certificate</span>
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