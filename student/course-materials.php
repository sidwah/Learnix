<?php
// student/course-materials.php
// ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';
include '../includes/toast.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login if not logged in
    header("Location: index.php");
    exit();
}
$user_id = $_SESSION['user_id'];

// Check if course_id is provided in the URL
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    // Redirect to courses page if no valid ID is provided
    echo "<script>window.location.href ='courses.php'; </script>";
    exit();
}

// Get course ID from URL
$course_id = intval($_GET['course_id']);

// Connect to database
require_once '../backend/config.php';

// First, check if user is enrolled in this course
$enrollment_query = "SELECT enrollment_id, status, current_topic_id FROM enrollments 
                     WHERE user_id = ? AND course_id = ? AND status = 'Active'";
$stmt = $conn->prepare($enrollment_query);
$stmt->bind_param("ii", $user_id, $course_id);
$stmt->execute();
$enrollment_result = $stmt->get_result();

if ($enrollment_result->num_rows === 0) {
    // User is not enrolled in this course
    header("Location: courses.php");
    exit();
}
$enrollment = $enrollment_result->fetch_assoc();
$enrollment_id = $enrollment['enrollment_id'];
$current_enrolled_topic_id = $enrollment['current_topic_id'];

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, u.profile_pic, u.username, 
               i.bio, cat.name AS category_name, cat.slug AS category_slug,
               sub.name AS subcategory_name, sub.slug AS subcategory_slug
        FROM courses c
        LEFT JOIN course_instructors ci ON c.course_id = ci.course_id AND ci.is_primary = 1
        LEFT JOIN instructors i ON ci.instructor_id = i.instructor_id
        LEFT JOIN users u ON i.user_id = u.user_id
        JOIN subcategories sub ON c.subcategory_id = sub.subcategory_id
        JOIN categories cat ON sub.category_id = cat.category_id
        WHERE c.course_id = ? AND c.status = 'Published'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if course exists and is published
if ($result->num_rows === 0) {
    // Redirect to courses page if course not found
    echo "<script>window.location.href ='courses.php'; </script>";
    exit();
}

// Get course data
$course = $result->fetch_assoc();

// Get course sections
$sql = "SELECT * FROM course_sections 
        WHERE course_id = ? 
        ORDER BY position";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$sections_result = $stmt->get_result();
$sections = [];

while ($section = $sections_result->fetch_assoc()) {
    $sections[] = $section;
}

// Determine the current section
// First, check if a specific section is requested
$current_section_id = isset($_GET['section']) ? intval($_GET['section']) : null;

// If no section specified, find the last incomplete section or the first section
if ($current_section_id === null) {
    // If there's a current topic, get its section
    if ($current_enrolled_topic_id) {
        $current_section_query = "SELECT section_id FROM section_topics 
                                  WHERE topic_id = ?";
        $stmt = $conn->prepare($current_section_query);
        $stmt->bind_param("i", $current_enrolled_topic_id);
        $stmt->execute();
        $current_section_result = $stmt->get_result();

        if ($current_section_result->num_rows > 0) {
            $current_section = $current_section_result->fetch_assoc();
            $current_section_id = $current_section['section_id'];
        }
    }

    // If still no section, find the last incomplete section
    if ($current_section_id === null) {
        $progress_query = "SELECT s.section_id, s.position 
                           FROM course_sections s
                           LEFT JOIN section_topics st ON s.section_id = st.section_id
                           LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                           WHERE s.course_id = ? AND (p.completion_status IS NULL OR p.completion_status != 'Completed')
                           GROUP BY s.section_id
                           ORDER BY s.position
                           LIMIT 1";
        $stmt = $conn->prepare($progress_query);
        $stmt->bind_param("ii", $enrollment_id, $course_id);
        $stmt->execute();
        $last_incomplete_result = $stmt->get_result();

        if ($last_incomplete_result->num_rows > 0) {
            $last_incomplete_section = $last_incomplete_result->fetch_assoc();
            $current_section_id = $last_incomplete_section['section_id'];
        } else {
            // If all sections are complete, use the first section
            $current_section_id = $sections[0]['section_id'];
        }
    }
}

// Fetch topics for the current section
$topics_query = "SELECT st.topic_id, st.title as topic_title, st.is_previewable,
                        tc.content_id, tc.content_type, tc.title as content_title, 
                        tc.video_url, tc.content_text, tc.external_url,
                        sq.quiz_id, sq.quiz_title, sq.pass_mark, sq.time_limit,
                        COALESCE(p.completion_status, 'Not Started') as completion_status,
                        st.topic_id = ? as is_current_topic
                 FROM section_topics st
                 LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
                 LEFT JOIN section_quizzes sq ON st.topic_id = sq.topic_id
                 LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                 WHERE st.section_id = ?
                 ORDER BY st.position";
$stmt = $conn->prepare($topics_query);
$stmt->bind_param("iii", $current_enrolled_topic_id, $enrollment_id, $current_section_id);
$stmt->execute();
$topics_result = $stmt->get_result();
$topics = [];

while ($topic = $topics_result->fetch_assoc()) {
    $topics[] = $topic;
}

// In the topic selection logic, add:
// Improved current topic selection logic
if ($current_section_id) {
    // First check if there's a manually selected topic in the URL
    if (isset($_GET['topic']) && is_numeric($_GET['topic'])) {
        $current_topic_id = intval($_GET['topic']);
    } else {
        // Find the first incomplete topic for this enrollment and section
        $current_topic_query = "SELECT st.topic_id 
            FROM section_topics st
            LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
            WHERE st.section_id = ? AND 
                  (p.completion_status IS NULL OR p.completion_status != 'Completed')
            ORDER BY st.position
            LIMIT 1";

        $stmt = $conn->prepare($current_topic_query);
        $stmt->bind_param("ii", $enrollment_id, $current_section_id);
        $stmt->execute();
        $current_topic_result = $stmt->get_result();

        if ($current_topic_result->num_rows > 0) {
            $current_topic = $current_topic_result->fetch_assoc();
            $current_topic_id = $current_topic['topic_id'];
        } else {
            // If all topics are completed, use the last topic in the section
            $last_topic_query = "SELECT st.topic_id 
                FROM section_topics st
                WHERE st.section_id = ?
                ORDER BY st.position DESC
                LIMIT 1";
            $stmt = $conn->prepare($last_topic_query);
            $stmt->bind_param("i", $current_section_id);
            $stmt->execute();
            $last_topic_result = $stmt->get_result();

            if ($last_topic_result->num_rows > 0) {
                $last_topic = $last_topic_result->fetch_assoc();
                $current_topic_id = $last_topic['topic_id'];
            }
        }

        // Update the enrollment's current topic
        if (isset($current_topic_id)) {
            $update_current_topic = "UPDATE enrollments 
                SET current_topic_id = ? 
                WHERE enrollment_id = ?";
            $update_stmt = $conn->prepare($update_current_topic);
            $update_stmt->bind_param("ii", $current_topic_id, $enrollment_id);
            $update_stmt->execute();
        }
    }
}

// Calculate overall course progress
// Calculate overall course progress - Updated to include quizzes
$progress_query = "SELECT 
                    COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                    COUNT(DISTINCT st.topic_id) as total_topics,
                    COUNT(DISTINCT CASE WHEN sqa.is_completed = 1 THEN sq.quiz_id END) as completed_quizzes,
                    COUNT(DISTINCT sq.quiz_id) as total_quizzes
                   FROM course_sections cs
                   JOIN section_topics st ON cs.section_id = st.section_id
                   LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                   LEFT JOIN section_quizzes sq ON cs.section_id = sq.section_id
                   LEFT JOIN (
                       SELECT quiz_id, MAX(is_completed) as is_completed 
                       FROM student_quiz_attempts 
                       WHERE user_id = ? 
                       GROUP BY quiz_id
                   ) sqa ON sq.quiz_id = sqa.quiz_id
                   WHERE cs.course_id = ?";
$stmt = $conn->prepare($progress_query);
$stmt->bind_param("iii", $enrollment_id, $user_id, $course_id);
$stmt->execute();
$progress_result = $stmt->get_result();
$progress = $progress_result->fetch_assoc();

$completed_percentage = 0;
$total_items = ($progress['total_topics'] + $progress['total_quizzes']);
$completed_items = ($progress['completed_topics'] + $progress['completed_quizzes']);

if ($total_items > 0) {
    $completed_percentage = round(($completed_items / $total_items) * 100);
}

// Add these queries to your existing script, before rendering the HTML

// Count of incomplete video topics
$video_count_query = "SELECT COUNT(*) as video_count 
    FROM section_topics st
    JOIN topic_content tc ON st.topic_id = tc.topic_id
    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
    WHERE tc.content_type = 'video' 
    AND (p.completion_status IS NULL OR p.completion_status != 'Completed')
    AND st.section_id = ?";
$stmt = $conn->prepare($video_count_query);
$stmt->bind_param("ii", $enrollment_id, $current_section_id);
$stmt->execute();
$video_result = $stmt->get_result();
$video_count = $video_result->fetch_assoc()['video_count'];

// Count of incomplete reading/text topics
$reading_count_query = "SELECT COUNT(*) as reading_count 
    FROM section_topics st
    JOIN topic_content tc ON st.topic_id = tc.topic_id
    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
    WHERE (tc.content_type = 'text' OR tc.content_type = 'reading')
    AND (p.completion_status IS NULL OR p.completion_status != 'Completed')
    AND st.section_id = ?";
$stmt = $conn->prepare($reading_count_query);
$stmt->bind_param("ii", $enrollment_id, $current_section_id);
$stmt->execute();
$reading_result = $stmt->get_result();
$reading_count = $reading_result->fetch_assoc()['reading_count'];

// Count of incomplete quizzes
$quiz_count_query = "SELECT COUNT(*) as quiz_count 
    FROM section_topics st
    JOIN section_quizzes sq ON st.topic_id = sq.topic_id
    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
    WHERE (p.completion_status IS NULL OR p.completion_status != 'Completed')
    AND st.section_id = ?";
$stmt = $conn->prepare($quiz_count_query);
$stmt->bind_param("ii", $enrollment_id, $current_section_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
$quiz_count = $quiz_result->fetch_assoc()['quiz_count'];

// Fetch course learning outcomes
$outcomes_query = "SELECT outcome_text 
    FROM course_learning_outcomes 
    WHERE course_id = ?";
$stmt = $conn->prepare($outcomes_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$outcomes_result = $stmt->get_result();
$learning_outcomes = [];
while ($outcome = $outcomes_result->fetch_assoc()) {
    $learning_outcomes[] = $outcome['outcome_text'];
}

// Close database connection
$stmt->close();
// $conn->close();

// Helper function for determining topic status
function getTopicStatus($topic)
{
    if ($topic['completion_status'] === 'Completed') {
        return 'completed';
    } elseif ($topic['is_previewable']) {
        return 'preview';
    } else {
        return 'locked';
    }
}

// Helper function to generate assignment status HTML
function getAssignmentStatusHTML($status)
{
    switch ($status) {
        case 'Completed':
            return '<span class="badge bg-success">Completed</span>';
        case 'Overdue':
            return '<span class="badge bg-danger">Overdue</span>';
        case 'Locked':
            return '<span class="badge bg-secondary">Locked</span>';
        default:
            return '<span class="badge bg-primary">In Progress</span>';
    }
}
// Helper functions
function formatTime($minutes)
{
    return $minutes . ' min';
}

function getTopicStatusIcon($topic)
{
    if ($topic['completion_status'] === 'Completed') {
        return '<i class="bi bi-check-circle-fill text-success"></i>';
    } elseif ($topic['is_previewable']) {
        return '<i class="bi bi-unlock-fill text-primary"></i>';
    } else {
        return '<i class="bi bi-lock-fill text-secondary"></i>';
    }
}

function formatContentType($content_type, $time_limit = null)
{
    $formatted = ucfirst($content_type);
    if ($time_limit) {
        $formatted .= " • " . formatTime($time_limit);
    }
    return $formatted;
}
$current_section_title = '';
foreach ($sections as $sec) {
    if ($sec['section_id'] == $current_section_id) {
        $current_section_title = $sec['title'];
        break;
    }
}

?>

<!-- Rest of the HTML remains the same as the previous implementation -->
<?php
// Include helper functions
require_once '../includes/helpers/course-progress-helpers.php';

// Get course completion requirements
$requirements = checkCourseCompletionRequirements($user_id, $course_id, $enrollment_id, $conn);
$all_requirements_met = $requirements['all_requirements_met'];
$all_topics_completed = $requirements['topics_status']['all_completed'];
$quiz_requirements = $requirements['quiz_status'];
$all_quizzes_passed = $quiz_requirements['all_passed'];

// Check if course has certificate enabled
$certificate_query = "SELECT certificate_enabled FROM courses WHERE course_id = ?";
$stmt = $conn->prepare($certificate_query);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$certificate_result = $stmt->get_result();
$certificate_data = $certificate_result->fetch_assoc();
$certificate_enabled = $certificate_data['certificate_enabled'] == 1;

// Check if user already has a certificate
$has_certificate = false;
if ($certificate_enabled) {
    $certificate_check_query = "SELECT c.certificate_id 
                             FROM certificates c
                             JOIN enrollments e ON c.enrollment_id = e.enrollment_id
                             WHERE e.user_id = ? AND e.course_id = ?";
    $stmt = $conn->prepare($certificate_check_query);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $certificate_check_result = $stmt->get_result();
    $has_certificate = $certificate_check_result->num_rows > 0;
}

// Add this right after the $has_certificate check
// Check for completion notification
if (isset($_GET['completed']) && $_GET['completed'] == 'true') {
    // Course was just completed - show success message
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const toast = new bootstrap.Toast(document.getElementById('courseCompletedToast'));
            toast.show();
            
            // After 2 seconds, scroll to completion section
            setTimeout(function() {
                document.getElementById('endOfCourseCollapse').scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }, 2000);
        });
    </script>";
}

// If user just completed the course for the first time
if ($all_requirements_met && !$has_certificate && !isset($_SESSION['certificate_generated'])) {
    // Redirect to certificate generation page
    $_SESSION['certificate_generated'] = true;
    echo "<script>
        setTimeout(function() {
            window.location.href = '../generate-certificate.php?course_id=$course_id&enrollment_id=$enrollment_id';
        }, 5000);
    </script>";
}
?>



<!-- Main Content -->
<main id="content" role="main" class="bg-light">
    <!-- Breadcrumb -->
    <div class="container content-space-t-1 pb-3 ">
        <div class="row align-items-lg-center">
            <div class="col-lg mb-2 mb-lg-0">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb bg-primary ">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="courses.php">Courses</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($course['title']); ?></li>
                    </ol>
                </nav>
                <!-- End Breadcrumb -->
            </div>
            <!-- End Col -->
        </div>
        <!-- End Row -->
    </div>
    <!-- End Breadcrumb -->

    <div class="container py-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-4">
                <!-- Course Thumbnail with Overlay on Hover -->
                <div class="mb-4 position-relative rounded-2 overflow-hidden course-thumbnail-container">
                    <img class="img-fluid w-100" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                    <div class="course-thumbnail-overlay d-flex align-items-center justify-content-center">
                        <a href="course-overview.php?id=<?php echo $course_id; ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-info-circle me-1"></i> Course Details
                        </a>
                    </div>
                </div>

                <!-- Course Title with Progress Indicator -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($course['title']); ?></h5>
                        <?php if (isset($completed_percentage)): ?>
                            <span class="badge bg-primary"><?php echo $completed_percentage; ?>% Complete</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-muted d-flex align-items-center small">
                        <i class="bi bi-folder me-2"></i>
                        <?php echo htmlspecialchars($course['category_name']); ?>
                    </p>

                    <!-- Add a simple progress bar -->
                    <?php if (isset($completed_percentage)): ?>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $completed_percentage; ?>%"
                                aria-valuenow="<?php echo $completed_percentage; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Course Material Section (Always Displayed) -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-light py-3">
                        <h5 class="mb-0 fw-bold ">
                            <i class="bi bi-book me-2"></i> Course Material
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush course-sections-list small ">
                            <?php foreach ($sections as $section): ?>
                                <li class="list-group-item border-0 py-2 px-3 <?php echo $current_section_id == $section['section_id'] ? 'active bg-light' : ''; ?>">
                                    <a href="course-materials.php?course_id=<?php echo $course_id; ?>&section=<?php echo $section['section_id']; ?>"
                                        class="text-decoration-none <?php echo $current_section_id == $section['section_id'] ? 'text-primary fw-bold' : 'text-dark'; ?> d-flex align-items-center">
                                        <?php echo htmlspecialchars($section['title']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <!-- Improved Additional Links with Counters -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <!-- In the sidebar of course-materials.php -->
                            <li class="list-group-item border-0 py-3">
                                <a href="grades.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-award me-2 text-warning"></i> Grades</span>
                                    <?php
                                    // Get count of graded quizzes
                                    $graded_count_query = "SELECT 
                              COUNT(DISTINCT sq.quiz_id) as total_quizzes,
                              COUNT(DISTINCT CASE WHEN sqa.is_completed = 1 THEN sq.quiz_id END) as graded_quizzes
                            FROM section_quizzes sq 
                            JOIN course_sections cs ON sq.section_id = cs.section_id
                            LEFT JOIN student_quiz_attempts sqa ON sq.quiz_id = sqa.quiz_id AND sqa.user_id = ?
                            WHERE cs.course_id = ?";
                                    $grade_stmt = $conn->prepare($graded_count_query);
                                    $grade_stmt->bind_param("ii", $user_id, $course_id);
                                    $grade_stmt->execute();
                                    $grade_result = $grade_stmt->get_result();
                                    $grade_counts = $grade_result->fetch_assoc();

                                    $graded_count = $grade_counts['graded_quizzes'];
                                    $total_quizzes = $grade_counts['total_quizzes'];

                                    if ($total_quizzes > 0) {
                                        $badge_class = ($graded_count < $total_quizzes) ? "bg-primary" : "bg-success";
                                        echo "<span class=\"badge {$badge_class}\">{$graded_count}/{$total_quizzes} Graded</span>";
                                    } else {
                                        echo "<span class=\"badge bg-light text-dark\">No Quizzes</span>";
                                    }
                                    ?>
                                </a>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <a href="discussion.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-chat-dots me-2 text-primary"></i> Discussion Forums</span>
                                    <!-- Add a badge for new discussions -->
                                    <span class="badge bg-danger">3 New</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- End Left Sidebar -->

            <!-- Add custom CSS for the improved sidebar -->
            <style>
                .course-thumbnail-container {
                    transition: all 0.3s ease;
                    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                }

                .course-thumbnail-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background-color: rgba(0, 0, 0, 0.5);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                .course-thumbnail-container:hover .course-thumbnail-overlay {
                    opacity: 1;
                }

                .course-sections-list .list-group-item {
                    transition: all 0.2s ease;
                }

                .course-sections-list .list-group-item:hover {
                    background-color: #f8f9fa;
                }

                .course-sections-list .list-group-item.active {
                    border-left: 3px solid #0d6efd;
                }

                #showMoreSections {
                    transition: all 0.2s ease;
                }
            </style>

            <!-- JavaScript for the "See more" button functionality -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const showMoreBtn = document.getElementById('showMoreSections');
                    const additionalSections = document.querySelectorAll('.additional-section');

                    // Check if we need to auto-show additional sections (if current section is in them)
                    const currentSectionActive = document.querySelector('.additional-section.active');
                    if (currentSectionActive) {
                        additionalSections.forEach(section => {
                            section.style.display = 'block';
                        });

                        if (showMoreBtn) {
                            showMoreBtn.innerHTML = '<span>See fewer sections</span> <i class="bi bi-chevron-up ms-1"></i>';

                            // Scroll to the active section with smooth behavior
                            setTimeout(() => {
                                currentSectionActive.scrollIntoView({
                                    behavior: 'smooth',
                                    block: 'center'
                                });
                            }, 300);
                        }
                    }

                    if (showMoreBtn) {
                        showMoreBtn.addEventListener('click', function() {
                            const isHidden = additionalSections[0].style.display === 'none';

                            additionalSections.forEach(section => {
                                section.style.display = isHidden ? 'block' : 'none';
                            });

                            showMoreBtn.innerHTML = isHidden ?
                                '<span>See fewer sections</span> <i class="bi bi-chevron-up ms-1"></i>' :
                                '<span>See more sections</span> <i class="bi bi-chevron-down ms-1"></i>';

                            // If showing sections, animate scroll to the first additional section
                            if (isHidden && additionalSections.length > 0) {
                                setTimeout(() => {
                                    additionalSections[0].scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'nearest'
                                    });
                                }, 100);
                            }
                        });
                    }
                });
            </script>

            <!-- Main Content -->
            <div class="col-lg-8">
                <?php
                // Get quiz completion status if not already done
                if (!isset($quiz_requirements)) {
                    require_once '../includes/helpers/course-progress-helpers.php';
                    $quiz_requirements = checkQuizzesCompleted($user_id, $course_id, $conn);
                }

                // If there are failed REQUIRED quizzes and course is almost complete
                if (!$quiz_requirements['all_required_passed'] && $completed_percentage >= 90) {
                    $failedQuizCount = count($quiz_requirements['failed_required_quizzes']);
                    // Store failed quiz HTML to insert into toast via JavaScript
                    ob_start();
                ?>
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="bi-trophy-fill fs-3 me-2 text-warning"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-bold">Almost There!</h6>
                            <p class="small mb-2">Complete <?php echo $failedQuizCount; ?> more quiz<?php echo $failedQuizCount > 1 ? 'zes' : ''; ?> to earn your certificate.</p>

                            <?php if ($failedQuizCount <= 3): ?>
                                <div class="bg-light rounded-2 overflow-hidden">
                                    <?php foreach ($quiz_requirements['failed_required_quizzes'] as $index => $quiz): ?>
                                        <div class="d-flex justify-content-between align-items-center px-2 py-1 <?php echo $index % 2 == 0 ? 'bg-light' : 'bg-white'; ?> border-bottom small">
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $quiz['quiz_id']; ?>" class="text-decoration-none text-truncate me-2">
                                                <?php echo htmlspecialchars($quiz['quiz_title']); ?>
                                            </a>
                                            <?php if ($quiz['highest_score'] > 0): ?>
                                                <span class="badge rounded-pill bg-danger bg-opacity-75" style="min-width: 40px;">
                                                    <?php echo $quiz['highest_score']; ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="badge rounded-pill bg-secondary bg-opacity-50">New</span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                    $toastContent = ob_get_clean();

                    // Convert PHP content to JavaScript-safe string
                    $jsContent = str_replace(["\r", "\n"], '', $toastContent);
                    $jsContent = str_replace("'", "\\'", $jsContent);
                    ?>

                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Get toast elements
                            const toast = document.getElementById("liveToast");
                            const toastBody = toast.querySelector(".toast-body");

                            // Update toast header with soft warning background
                            const toastHeader = toast.querySelector(".toast-header");
                            toastHeader.classList.add("bg-warning", "bg-opacity-10");

                            // Add a subtle left border to indicate status
                            toast.classList.add("border-start", "border-3", "border-warning");

                            // Update toast title
                            const toastTitle = toast.querySelector(".toast-header h5");
                            toastTitle.textContent = "Quiz Alert";

                            // Update toast content
                            toastBody.innerHTML = '<?php echo $jsContent; ?>';

                            // Adjust toast size - wider but not too wide
                            toast.style.maxWidth = "380px";
                            toast.style.width = "380px";

                            // Initialize and show the toast
                            const bsToast = new bootstrap.Toast(toast, {
                                autohide: false
                            });
                            bsToast.show();
                        });
                    </script>

                <?php
                }
                ?>
                <!-- Final Module Assessment Card -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-2">
                        <button class="btn btn-link text-decoration-none text-dark p-0 d-flex align-items-center w-100"
                            type="button" data-bs-toggle="collapse" data-bs-target="#endOfCourseCollapse"
                            aria-expanded="true" aria-controls="endOfCourseCollapse">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div class="d-flex align-items-center">
                                    <span class="me-2">
                                        <i class="bi bi-trophy-fill text-primary"></i>
                                    </span>
                                    <h5 class="fw-bold mb-0 fs-6">Final Module Assessment</h5>
                                </div>
                                <i class="bi bi-chevron-down small"></i>
                            </div>
                        </button>
                    </div>

                    <div class="collapse show" id="endOfCourseCollapse">
                        <div class="card-body p-3">
                            <!-- Remaining Content Stats -->
                            <?php if ($video_count > 0 || $reading_count > 0 || $quiz_count > 0): ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php if ($video_count > 0): ?>
                                        <div class="badge bg-light text-dark py-1 px-2 d-flex align-items-center">
                                            <i class="bi bi-camera-video text-primary me-1 small"></i>
                                            <span class="small"><?php echo $video_count; ?> video<?php echo $video_count > 1 ? 's' : ''; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($reading_count > 0): ?>
                                        <div class="badge bg-light text-dark py-1 px-2 d-flex align-items-center">
                                            <i class="bi bi-book text-primary me-1 small"></i>
                                            <span class="small"><?php echo $reading_count; ?> reading<?php echo $reading_count > 1 ? 's' : ''; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($quiz_count > 0): ?>
                                        <div class="badge bg-light text-dark py-1 px-2 d-flex align-items-center">
                                            <i class="bi bi-clipboard-check text-primary me-1 small"></i>
                                            <span class="small"><?php echo $quiz_count; ?> quiz<?php echo $quiz_count > 1 ? 'zes' : ''; ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Course Requirements -->
                            <div class="card border bg-light mb-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3 fs-6">Course Completion Requirements</h6>

                                    <div class="row g-3">
                                        <!-- Progress Column -->
                                        <div class="col-lg-5">
                                            <!-- Topics Progress -->
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-book-half text-primary me-2 small"></i>
                                                        <span class="small fw-semibold">Complete Topics</span>
                                                    </div>
                                                    <span class="badge <?php echo $all_topics_completed ? 'bg-success' : 'bg-secondary'; ?> rounded-pill small">
                                                        <?php echo $requirements['topics_status']['completed_topics']; ?>/<?php echo $requirements['topics_status']['total_topics']; ?>
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar <?php echo $all_topics_completed ? 'bg-success' : 'bg-primary'; ?>"
                                                        role="progressbar"
                                                        style="width: <?php echo ($requirements['topics_status']['total_topics'] > 0) ? ($requirements['topics_status']['completed_topics'] / $requirements['topics_status']['total_topics']) * 100 : 0; ?>%"
                                                        aria-valuenow="<?php echo $requirements['topics_status']['completed_topics']; ?>"
                                                        aria-valuemin="0"
                                                        aria-valuemax="<?php echo $requirements['topics_status']['total_topics']; ?>">
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Required Quizzes Progress -->
                                            <?php if ($quiz_requirements['total_required_quizzes'] > 0): ?>
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-question-circle text-primary me-2 small"></i>
                                                            <span class="small fw-semibold">Pass Required Quizzes</span>
                                                        </div>
                                                        <span class="badge <?php echo $all_quizzes_passed ? 'bg-success' : 'bg-secondary'; ?> rounded-pill small">
                                                            <?php echo $quiz_requirements['passed_required_quizzes']; ?>/<?php echo $quiz_requirements['total_required_quizzes']; ?>
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar <?php echo $all_quizzes_passed ? 'bg-success' : 'bg-primary'; ?>"
                                                            role="progressbar"
                                                            style="width: <?php echo ($quiz_requirements['total_required_quizzes'] > 0) ? ($quiz_requirements['passed_required_quizzes'] / $quiz_requirements['total_required_quizzes']) * 100 : 0; ?>%"
                                                            aria-valuenow="<?php echo $quiz_requirements['passed_required_quizzes']; ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="<?php echo $quiz_requirements['total_required_quizzes']; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Optional Quizzes Progress (only show if there are optional quizzes) -->
                                            <?php
                                            $total_optional = $quiz_requirements['total_quizzes'] - $quiz_requirements['total_required_quizzes'];
                                            $passed_optional = $quiz_requirements['passed_quizzes'] - $quiz_requirements['passed_required_quizzes'];
                                            if ($total_optional > 0):
                                            ?>
                                                <div class="mt-2">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-star text-primary me-2 small"></i>
                                                            <span class="small">Optional Quizzes</span>
                                                        </div>
                                                        <span class="badge bg-info rounded-pill small">
                                                            <?php echo $passed_optional; ?>/<?php echo $total_optional; ?>
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-info"
                                                            role="progressbar"
                                                            style="width: <?php echo ($total_optional > 0) ? ($passed_optional / $total_optional) * 100 : 0; ?>%"
                                                            aria-valuenow="<?php echo $passed_optional; ?>"
                                                            aria-valuemin="0"
                                                            aria-valuemax="<?php echo $total_optional; ?>">
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Achievements Column -->
                                        <div class="col-lg-7">
                                            <div class="row g-2">
                                                <!-- Certificate Card -->
                                                <?php if ($certificate_enabled): ?>
                                                    <div class="col-md-6">
                                                        <div class="card border <?php echo $has_certificate ? 'border-success bg-success bg-opacity-10' : ($all_requirements_met ? 'border-success bg-success bg-opacity-10' : 'border-secondary'); ?> h-100">
                                                            <div class="card-body p-2">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <i class="bi bi-award-fill <?php echo $has_certificate || $all_requirements_met ? 'text-success' : 'text-secondary'; ?> me-2"></i>
                                                                        <span class="small fw-semibold">Certificate</span>
                                                                    </div>
                                                                    <?php if ($has_certificate): ?>
                                                                        <a href="my-certifications.php" class="btn btn-sm btn-success py-0 px-2"><small>View</small></a>
                                                                    <?php elseif ($all_requirements_met): ?>
                                                                        <span class="badge bg-success p-1"><i class="bi bi-check-lg"></i></span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-secondary p-1"><i class="bi bi-lock"></i></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>

                                                <!-- Badge Card -->
                                                <!-- <div class="col-md-6">
                                                    <div class="card border <?php /* echo $all_requirements_met ? 'border-success bg-success bg-opacity-10' : 'border-secondary'; ?> h-100">
                                                        <div class="card-body p-2">
                                                            <div class="d-flex align-items-center justify-content-between">
                                                                <div class="d-flex align-items-center">
                                                                    <i class="bi bi-trophy-fill <?php echo $all_requirements_met ? 'text-success' : 'text-secondary'; ?> me-2"></i>
                                                                    <span class="small fw-semibold">Course Badge</span>
                                                                </div>
                                                                <?php if ($all_requirements_met): ?>
                                                                    <a href="my-badges.php" class="btn btn-sm btn-success py-0 px-2"><small>View</small></a>
                                                                <?php else: ?>
                                                                    <span class="badge bg-secondary p-1"><i class="bi bi-lock"></i></span>
                                                                <?php endif; */ ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> -->
                                            </div>

                                            <!-- Alert Message only shown if requirements not met -->
                                            <?php if (!$all_requirements_met): ?>
                                                <div class="alert alert-primary py-2 px-3 mt-2 mb-0 small">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-info-circle-fill me-2"></i>
                                                        <div>
                                                            <?php if (!$all_topics_completed && !$all_quizzes_passed): ?>
                                                                Complete topics and pass quizzes to earn your certificate.
                                                            <?php elseif (!$all_topics_completed): ?>
                                                                Complete remaining topics for your certificate.
                                                            <?php elseif (!$all_quizzes_passed): ?>
                                                                Pass remaining quizzes for your certificate.
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Learning Objectives Accordion -->
                            <div class="accordion" id="learningObjectivesAccordion">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="headingLearningObjectives">
                                        <button class="accordion-button collapsed bg-white text-primary border-0 p-0 shadow-none"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#learningObjectivesCollapse"
                                            aria-expanded="false"
                                            aria-controls="learningObjectivesCollapse">
                                            <i class="bi bi-lightbulb-fill me-2 small"></i> <span class="small">Learning Objectives</span>
                                        </button>
                                    </h2>
                                    <div id="learningObjectivesCollapse"
                                        class="accordion-collapse collapse"
                                        aria-labelledby="headingLearningObjectives"
                                        data-bs-parent="#learningObjectivesAccordion">
                                        <div class="accordion-body bg-light rounded-3 mt-2 p-3">
                                            <ul class="mb-0 small">
                                                <?php foreach ($learning_outcomes as $outcome): ?>
                                                    <li class="mb-1"><?php echo htmlspecialchars($outcome); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Access Denied Message Display -->
                <?php if (isset($_SESSION['access_denied_message'])): ?>
                    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $_SESSION['access_denied_message']; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['access_denied_message']); ?>
                <?php endif; ?>

                <!-- Assessment Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0 fw-bold"> <?php echo htmlspecialchars($current_section_title); ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <?php
                            // Merge topics and quizzes
                            $merged_items = $topics;

                            // Get quizzes for this section
                            if (isset($current_section_id)) {
                                $quiz_sql = "SELECT 
                    sq.quiz_id, 
                    sq.quiz_title as topic_title, 
                    'quiz' as content_type, 
                    sq.time_limit,
                    sq.pass_mark,
                    sq.is_required,
                    COALESCE(sqa.is_completed, 0) as is_completed,
                    IF(sqa.is_completed = 1, 'Completed', 'Not Started') as completion_status
                FROM section_quizzes sq
                LEFT JOIN (
                    SELECT quiz_id, MAX(is_completed) as is_completed 
                    FROM student_quiz_attempts 
                    WHERE user_id = ? 
                    GROUP BY quiz_id
                ) sqa ON sq.quiz_id = sqa.quiz_id
                WHERE sq.section_id = ?";

                                $quiz_stmt = $conn->prepare($quiz_sql);
                                $quiz_stmt->bind_param("ii", $user_id, $current_section_id);
                                $quiz_stmt->execute();
                                $quiz_result = $quiz_stmt->get_result();

                                while ($quiz = $quiz_result->fetch_assoc()) {
                                    $merged_items[] = $quiz;
                                }
                            }

                            // Sort by position if available, or just preserve the original order
                            // Here you would add logic to sort by position if needed

                            foreach ($merged_items as $item):
                                $is_quiz = ($item['content_type'] === 'quiz');
                            ?>
                                <li class="list-group-item d-flex align-items-center py-3
                <?php echo $item['completion_status'] === 'Completed' ? ' bg-success-subtle' : ''; ?>">

                                    <!-- Icon -->
                                    <div class="d-flex align-items-center me-3">
                                        <i class="bi <?php
                                                        if ($item['completion_status'] === 'Completed') {
                                                            echo 'bi-check-circle-fill text-success';
                                                        } else {
                                                            switch ($item['content_type']) {
                                                                case 'video':
                                                                    echo 'bi-play-circle text-primary';
                                                                    break;
                                                                case 'text':
                                                                case 'reading':
                                                                    echo 'bi-book text-info';
                                                                    break;
                                                                case 'quiz':
                                                                    echo 'bi-clipboard-check text-warning';
                                                                    break;
                                                                case 'link':
                                                                    echo 'bi-link-45deg text-secondary';
                                                                    break;
                                                                case 'document':
                                                                    echo 'bi-file-earmark-text text-info';
                                                                    break;
                                                                default:
                                                                    echo 'bi-circle text-muted';
                                                            }
                                                        }
                                                        ?> fs-5"></i>
                                    </div>

                                    <!-- Title and Info -->
                                    <div class="flex-grow-1">
                                        <?php
                                        $title = htmlspecialchars($item['topic_title'] ?? $item['content_title']);
                                        $content_type = $item['content_type'] ?? 'Topic';
                                        $time_limit = $item['time_limit'] ?? 10;

                                        // Both quizzes and regular content go to course-content.php, but with different parameters
                                        if ($is_quiz) {
                                            $item_url = "course-content.php?course_id={$course_id}&quiz_id={$item['quiz_id']}";
                                        } else {
                                            $link_available = (isset($item['is_previewable']) && $item['is_previewable'] || $item['completion_status'] !== 'Completed');
                                            $item_url = ($item['content_type'] === 'link' && !empty($item['external_url']))
                                                ? htmlspecialchars($item['external_url'])
                                                : "course-content.php?course_id={$course_id}&topic={$item['topic_id']}";
                                        }
                                        ?>

                                        <h6 class="mb-0">
                                            <a href="<?php echo $item_url; ?>"
                                                target="<?php echo (!$is_quiz && $item['content_type'] === 'link') ? '_blank' : '_self'; ?>"
                                                class="text-decoration-none text-dark">
                                                <?php echo $title; ?>
                                                <?php if ($is_quiz && isset($item['pass_mark'])): ?>
                                                    <span class="badge bg-light text-dark ms-1">Pass: <?php echo $item['pass_mark']; ?>%</span>
                                                    <?php if (isset($item['is_required']) && $item['is_required'] == 1): ?>
                                                        <span class="badge bg-danger text-white ms-1">Required</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary text-white ms-1">Optional </span>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </a>
                                        </h6>

                                        <p class="mb-0 small text-muted">
                                            <?php echo ucfirst($content_type); ?> • <?php echo $time_limit; ?> min
                                        </p>
                                    </div>
                                    <div class="ms-2">
                                        <?php
                                        // Get the first topic ID in the course sequence
                                        $first_topic_query = "SELECT st.topic_id 
                          FROM section_topics st
                          JOIN course_sections cs ON st.section_id = cs.section_id
                          WHERE cs.course_id = ?
                          ORDER BY cs.position, st.position
                          LIMIT 1";

                                        $first_stmt = $conn->prepare($first_topic_query);
                                        $first_stmt->bind_param("i", $course_id);
                                        $first_stmt->execute();
                                        $first_result = $first_stmt->get_result();
                                        $first_topic_id = $first_result->fetch_assoc()['topic_id'] ?? 0;

                                        // Get the section and position info for this item
                                        $current_section_position = 0;
                                        $current_item_position = 0;

                                        if ($is_quiz && isset($item['quiz_id'])) {
                                            // For quizzes, get the section position
                                            $item_section_query = "SELECT cs.position as section_position
                              FROM section_quizzes sq
                              JOIN course_sections cs ON sq.section_id = cs.section_id
                              WHERE sq.quiz_id = ?";
                                            $item_stmt = $conn->prepare($item_section_query);
                                            $item_stmt->bind_param("i", $item['quiz_id']);
                                            $item_stmt->execute();
                                            $item_result = $item_stmt->get_result();
                                            if ($item_result->num_rows > 0) {
                                                $item_info = $item_result->fetch_assoc();
                                                $current_section_position = $item_info['section_position'];
                                                $current_item_position = 999; // Position quizzes after topics in the section
                                            }
                                        } else if (!$is_quiz && isset($item['topic_id'])) {
                                            // For topics, get the section position
                                            $item_section_query = "SELECT cs.position as section_position, st.position as item_position
                              FROM section_topics st
                              JOIN course_sections cs ON st.section_id = cs.section_id
                              WHERE st.topic_id = ?";
                                            $item_stmt = $conn->prepare($item_section_query);
                                            $item_stmt->bind_param("i", $item['topic_id']);
                                            $item_stmt->execute();
                                            $item_result = $item_stmt->get_result();
                                            if ($item_result->num_rows > 0) {
                                                $item_info = $item_result->fetch_assoc();
                                                $current_section_position = $item_info['section_position'];
                                                $current_item_position = $item_info['item_position'];
                                            }
                                        }

                                        // For quizzes: check if all previous topics in this section are completed
                                        $all_previous_topics_completed = true;
                                        if ($is_quiz) {
                                            $prev_topics_query = "SELECT COUNT(st.topic_id) as total_topics, 
                                    COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) as completed_topics
                             FROM section_topics st
                             LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                             JOIN course_sections cs ON st.section_id = cs.section_id
                             WHERE cs.course_id = ? AND cs.position = ?";
                                            $prev_topics_stmt = $conn->prepare($prev_topics_query);
                                            $prev_topics_stmt->bind_param("iii", $enrollment_id, $course_id, $current_section_position);
                                            $prev_topics_stmt->execute();
                                            $prev_topics_result = $prev_topics_stmt->get_result();
                                            if ($prev_topics_result->num_rows > 0) {
                                                $topics_info = $prev_topics_result->fetch_assoc();
                                                $all_previous_topics_completed = ($topics_info['completed_topics'] == $topics_info['total_topics'] && $topics_info['total_topics'] > 0);
                                            }
                                        }

                                        // For topics in later sections: check if all previous sections are completed properly
                                        $previous_sections_completed = true;
                                        $has_previous_quizzes = false;

                                        if (!$is_quiz && $current_section_position > 1) {
                                            // First, check if previous sections have quizzes
                                            $has_quizzes_query = "SELECT COUNT(sq.quiz_id) as quiz_count
                             FROM section_quizzes sq
                             JOIN course_sections cs ON sq.section_id = cs.section_id
                             WHERE cs.course_id = ? AND cs.position < ?";
                                            $has_quizzes_stmt = $conn->prepare($has_quizzes_query);
                                            $has_quizzes_stmt->bind_param("ii", $course_id, $current_section_position);
                                            $has_quizzes_stmt->execute();
                                            $has_quizzes_result = $has_quizzes_stmt->get_result();
                                            $quiz_count = $has_quizzes_result->fetch_assoc()['quiz_count'];
                                            $has_previous_quizzes = ($quiz_count > 0);

                                            if ($has_previous_quizzes) {
                                                // If there are quizzes, check if they are passed
                                                $prev_quizzes_query = "SELECT COUNT(sq.quiz_id) as total_quizzes,
                                        COUNT(CASE WHEN sqa.passed = 1 THEN 1 END) as passed_quizzes
                                  FROM section_quizzes sq
                                  JOIN course_sections cs ON sq.section_id = cs.section_id
                                  LEFT JOIN (
                                      SELECT quiz_id, MAX(passed) as passed 
                                      FROM student_quiz_attempts 
                                      WHERE user_id = ? 
                                      GROUP BY quiz_id
                                  ) sqa ON sq.quiz_id = sqa.quiz_id
                                  WHERE cs.course_id = ? AND cs.position < ?";
                                                $prev_quizzes_stmt = $conn->prepare($prev_quizzes_query);
                                                $prev_quizzes_stmt->bind_param("iii", $user_id, $course_id, $current_section_position);
                                                $prev_quizzes_stmt->execute();
                                                $prev_quizzes_result = $prev_quizzes_stmt->get_result();
                                                if ($prev_quizzes_result->num_rows > 0) {
                                                    $quizzes_info = $prev_quizzes_result->fetch_assoc();
                                                    $previous_sections_completed = ($quizzes_info['passed_quizzes'] == $quizzes_info['total_quizzes'] && $quizzes_info['total_quizzes'] > 0);
                                                }
                                            } else {
                                                // If there are no quizzes, check if all topics in previous sections are completed
                                                $prev_sections_topics_query = "SELECT COUNT(st.topic_id) as total_topics,
                                          COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) as completed_topics
                                        FROM section_topics st
                                        LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                        JOIN course_sections cs ON st.section_id = cs.section_id
                                        WHERE cs.course_id = ? AND cs.position < ?";
                                                $prev_sections_topics_stmt = $conn->prepare($prev_sections_topics_query);
                                                $prev_sections_topics_stmt->bind_param("iii", $enrollment_id, $course_id, $current_section_position);
                                                $prev_sections_topics_stmt->execute();
                                                $prev_sections_topics_result = $prev_sections_topics_stmt->get_result();
                                                if ($prev_sections_topics_result->num_rows > 0) {
                                                    $prev_sections_topics_info = $prev_sections_topics_result->fetch_assoc();
                                                    $previous_sections_completed = ($prev_sections_topics_info['completed_topics'] == $prev_sections_topics_info['total_topics'] && $prev_sections_topics_info['total_topics'] > 0);
                                                }
                                            }
                                        }

                                        // For topics within a section: check if all previous topics in this section are completed
                                        $all_previous_section_topics_completed = true;
                                        if (!$is_quiz && $current_item_position > 1) {
                                            $prev_section_topics_query = "SELECT COUNT(st.topic_id) as total_topics, 
                                           COUNT(CASE WHEN p.completion_status = 'Completed' THEN 1 END) as completed_topics
                                    FROM section_topics st
                                    LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                                    JOIN course_sections cs ON st.section_id = cs.section_id
                                    WHERE cs.course_id = ? AND cs.position = ? AND st.position < ?";
                                            $prev_section_topics_stmt = $conn->prepare($prev_section_topics_query);
                                            $prev_section_topics_stmt->bind_param("iiii", $enrollment_id, $course_id, $current_section_position, $current_item_position);
                                            $prev_section_topics_stmt->execute();
                                            $prev_section_topics_result = $prev_section_topics_stmt->get_result();
                                            if ($prev_section_topics_result->num_rows > 0) {
                                                $section_topics_info = $prev_section_topics_result->fetch_assoc();
                                                $all_previous_section_topics_completed = ($section_topics_info['completed_topics'] == $section_topics_info['total_topics'] && $section_topics_info['total_topics'] > 0);
                                            }
                                        }

                                        // Get the next uncompleted topic in the course sequence
                                        $next_topic_query = "SELECT st.topic_id 
                         FROM section_topics st
                         JOIN course_sections cs ON st.section_id = cs.section_id
                         LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                         WHERE cs.course_id = ? 
                         AND (p.completion_status IS NULL OR p.completion_status != 'Completed')
                         ORDER BY cs.position, st.position
                         LIMIT 1";

                                        $next_stmt = $conn->prepare($next_topic_query);
                                        $next_stmt->bind_param("ii", $enrollment_id, $course_id);
                                        $next_stmt->execute();
                                        $next_result = $next_stmt->get_result();
                                        $next_topic_id = $next_result->fetch_assoc()['topic_id'] ?? 0;

                                        // Check if this item has been started but not completed
                                        $is_in_progress = false;
                                        if ($is_quiz && isset($item['start_time']) && empty($item['end_time'])) {
                                            $is_in_progress = true;
                                        } else if (!$is_quiz && $item['completion_status'] === 'In Progress') {
                                            $is_in_progress = true;
                                        }

                                        // Check if this is the very first topic of the course
                                        $is_first_topic = (!$is_quiz && isset($item['topic_id']) && $item['topic_id'] == $first_topic_id);

                                        // Determine if this item is accessible based on the course sequence
                                        $is_accessible = true;
                                        $is_next_item = false;

                                        if ($is_quiz) {
                                            // Quizzes are accessible if all previous topics in this section are completed
                                            $is_accessible = $all_previous_topics_completed;
                                        } else {
                                            // First topic is always accessible
                                            if ($is_first_topic) {
                                                $is_accessible = true;
                                            }
                                            // Topics in section 1 are accessible if previous topics in same section are completed
                                            else if ($current_section_position == 1) {
                                                $is_accessible = $all_previous_section_topics_completed;
                                            }
                                            // Topics in later sections depend on previous sections being completed
                                            else {
                                                $is_accessible = $previous_sections_completed && $all_previous_section_topics_completed;
                                            }
                                        }

                                        // Determine if this is the next item in sequence
                                        if (!$is_quiz && isset($item['topic_id']) && $item['topic_id'] == $next_topic_id && $is_accessible) {
                                            $is_next_item = true;
                                        }
                                        ?>

                                        <?php if ($item['completion_status'] === 'Completed'): ?>
                                            <!-- For completed items -->
                                            <?php if ($is_quiz): ?>
                                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-clipboard-check me-1"></i> Review
                                                </a>
                                            <?php else: ?>
                                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-check-circle me-1"></i> Review
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($is_in_progress && $is_accessible): ?>
                                            <!-- For items that are in progress and accessible -->
                                            <?php if ($is_quiz): ?>
                                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-arrow-clockwise me-1"></i> Continue
                                                </a>
                                            <?php else: ?>
                                                <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-play-fill me-1"></i> Resume
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif ($is_first_topic && $item['completion_status'] === 'Not Started'): ?>
                                            <!-- Special case for the very first topic of the course -->
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                class="btn btn-primary btn-sm">
                                                <i class="bi bi-play-fill me-1"></i> Start
                                            </a>
                                        <?php elseif ($is_next_item): ?>
                                            <!-- For the next uncompleted item in the sequence -->
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-arrow-right me-1"></i> Next
                                            </a>
                                        <?php elseif ($is_quiz && $is_accessible && $item['completion_status'] !== 'Completed'): ?>
                                            <!-- For quizzes that are accessible but not completed -->
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-pencil-square me-1"></i> Take Quiz
                                            </a>
                                        <?php elseif ($is_accessible && $item['completion_status'] !== 'Completed'): ?>
                                            <!-- For accessible topics that are not completed -->
                                            <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-play-fill me-1"></i> Start
                                            </a>
                                        <?php else: ?>
                                            <!-- For inaccessible items -->
                                            <?php if ($is_quiz): ?>
                                                <?php if (!$all_previous_topics_completed): ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                                        <i class="bi bi-lock me-1"></i> Complete Topics First
                                                    </button>
                                                <?php else: ?>
                                                    <a href="course-content.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                        class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php if (!$previous_sections_completed): ?>
                                                    <?php if ($has_previous_quizzes): ?>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                                            <i class="bi bi-lock me-1"></i> Pass Previous Quiz
                                                        </button>
                                                    <?php else: ?>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                                            <i class="bi bi-lock me-1"></i> Complete Previous Section
                                                        </button>
                                                    <?php endif; ?>
                                                <?php elseif (!$all_previous_section_topics_completed): ?>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                                        <i class="bi bi-lock me-1"></i> Complete Previous Topics
                                                    </button>
                                                <?php else: ?>
                                                    <a href="course-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                        class="btn btn-outline-secondary btn-sm">
                                                        <i class="bi bi-eye me-1"></i> View
                                                    </a>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>

                                </li>
                            <?php endforeach; ?>
                        </ul>

                    </div>
                </div>


                <!-- Course Wrap Up Section -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <button class="btn btn-link text-decoration-none text-dark p-0 collapsed d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#courseWrapUpCollapse" aria-expanded="false" aria-controls="courseWrapUpCollapse">
                            <span class="me-2">
                                <i class="bi bi-caret-right-fill"></i>
                            </span>
                            <h5 class="mb-0 fw-bold">Course wrap up</h5>
                        </button>
                    </div>
                    <div class="collapse" id="courseWrapUpCollapse">
                        <div class="card-body">
                            <p>Congratulations on completing the course! Take some time to reflect on your learning journey and consider next steps in your programming path.</p>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content -->
            <!-- Course completion toast -->
            <div class="toast-container position-fixed top-0 end-0 p-3">
                <div id="courseCompletedToast" class="toast border-success" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="toast-header bg-success bg-opacity-10 border-bottom border-success">
                        <i class="bi bi-check-circle-fill text-success me-2"></i>
                        <strong class="me-auto">Course Completed!</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        <div class="d-flex">
                            <div class="me-3 fs-1 text-success">
                                <i class="bi bi-trophy-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold">Congratulations!</h6>
                                <p>You've successfully completed this course. Your certificate is being generated...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Help Chat Button -->
<div class="position-fixed bottom-0 end-0 m-4">
    <button class="btn btn-primary btn-lg rounded-circle shadow" type="button">
        <i class="bi bi-chat-fill"></i>
    </button>
</div>


<?php include '../includes/student-footer.php'; ?>