<?php
// learn.php
// ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/signin-header.php';

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
    header("Location: courses.php");
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
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
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
    header("Location: courses.php");
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
$progress_query = "SELECT 
                    COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                    COUNT(DISTINCT st.topic_id) as total_topics
                   FROM course_sections cs
                   JOIN section_topics st ON cs.section_id = st.section_id
                   LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                   WHERE cs.course_id = ?";
$stmt = $conn->prepare($progress_query);
$stmt->bind_param("ii", $enrollment_id, $course_id);
$stmt->execute();
$progress_result = $stmt->get_result();
$progress = $progress_result->fetch_assoc();

$completed_percentage = 0;
if ($progress['total_topics'] > 0) {
    $completed_percentage = round(($progress['completed_topics'] / $progress['total_topics']) * 100);
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
                    <p class="text-muted d-flex align-items-center">
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
                        <ul class="list-group list-group-flush course-sections-list">
                            <?php foreach ($sections as $section): ?>
                                <li class="list-group-item border-0 py-2 px-3 <?php echo $current_section_id == $section['section_id'] ? 'active bg-light' : ''; ?>">
                                    <a href="learn.php?course_id=<?php echo $course_id; ?>&section=<?php echo $section['section_id']; ?>"
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
                            <li class="list-group-item border-0 py-3">
                                <a href="grades.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-award me-2 text-warning"></i> Grades</span>
                                    <!-- Add a badge for grades (example) -->
                                    <span class="badge bg-light text-dark">2 Graded</span>
                                </a>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <a href="notes.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-journal-text me-2 text-info"></i> Notes</span>
                                    <!-- Dynamically add a badge for number of notes -->
                                    <?php if (isset($notes_count) && $notes_count > 0): ?>
                                        <span class="badge bg-info"><?php echo $notes_count; ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <a href="discussion.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-chat-dots me-2 text-primary"></i> Discussion Forums</span>
                                    <!-- Add a badge for new discussions -->
                                    <span class="badge bg-danger">3 New</span>
                                </a>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <a href="messages.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark d-flex justify-content-between align-items-center">
                                    <span><i class="bi bi-envelope me-2 text-success"></i> Messages</span>
                                </a>
                            </li>
                            <li class="list-group-item border-0 py-3">
                                <a href="course-overview.php?id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                                    <i class="bi bi-info-circle me-2 text-secondary"></i> Course Info
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
                <!-- Assessment Items -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <button class="btn btn-link text-decoration-none text-dark p-0 d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#endOfCourseCollapse" aria-expanded="true" aria-controls="endOfCourseCollapse">
                            <span class="me-2">
                                <i class="bi bi-caret-down-fill"></i>
                            </span>
                            <h5 class="mb-0 fw-bold">Final Module Assessment</h5>
                        </button>
                    </div>
                    <div class="collapse show" id="endOfCourseCollapse">
                        <div class="card-body">
                            <!-- Progress Stats -->
                            <div class="d-flex flex-wrap mb-4 text-muted small">
                                <?php if ($video_count > 0): ?>
                                    <div class="me-4">
                                        <i class="bi bi-camera-video me-1"></i>
                                        <span><?php echo $video_count; ?> video<?php echo $video_count > 1 ? 's' : ''; ?> left</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($reading_count > 0): ?>
                                    <div class="me-4">
                                        <i class="bi bi-book me-1"></i>
                                        <span><?php echo $reading_count; ?> reading<?php echo $reading_count > 1 ? 's' : ''; ?> left</span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($quiz_count > 0): ?>
                                    <div>
                                        <i class="bi bi-clipboard-check me-1"></i>
                                        <span><?php echo $quiz_count; ?> graded assessment<?php echo $quiz_count > 1 ? 's' : ''; ?> left</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Description -->
                            <p>In the final module, you'll synthesize the skills you gained from the course to create a practical project. After completing the individual units in this module, you will be able to take the final assessment and reflect on your learning journey.</p>

                            <!-- Learning Objectives -->
                            <div class="mb-3">
                                <button class="btn btn-link text-primary p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#learningObjectivesCollapse">
                                    <i class="bi bi-chevron-down me-1"></i> Show Learning Objectives
                                </button>
                                <div class="collapse mt-2" id="learningObjectivesCollapse">
                                    <div class="card card-body bg-light">
                                        <ul class="mb-0">
                                            <?php foreach ($learning_outcomes as $outcome): ?>
                                                <li><?php echo htmlspecialchars($outcome); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


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

                                        // For quizzes, we use a different URL
                                        if ($is_quiz) {
                                            $item_url = "take_quiz.php?course_id={$course_id}&quiz_id={$item['quiz_id']}";
                                        } else {
                                            $link_available = (isset($item['is_previewable']) && $item['is_previewable'] || $item['completion_status'] !== 'Completed');
                                            $item_url = ($item['content_type'] === 'link' && !empty($item['external_url']))
                                                ? htmlspecialchars($item['external_url'])
                                                : "topic-content.php?course_id={$course_id}&topic={$item['topic_id']}";
                                        }
                                        ?>

                                        <h6 class="mb-0">
                                            <a href="<?php echo $item_url; ?>"
                                                target="<?php echo (!$is_quiz && $item['content_type'] === 'link') ? '_blank' : '_self'; ?>"
                                                class="text-decoration-none text-dark">
                                                <?php echo $title; ?>
                                                <?php if ($is_quiz && isset($item['pass_mark'])): ?>
                                                    <span class="badge bg-light text-dark ms-1">Pass: <?php echo $item['pass_mark']; ?>%</span>
                                                <?php endif; ?>
                                            </a>
                                        </h6>

                                        <p class="mb-0 small text-muted">
                                            <?php echo ucfirst($content_type); ?> • <?php echo $time_limit; ?> min
                                        </p>
                                    </div>

                                    <div class="ms-2">
                                        <?php if ($item['completion_status'] === 'Completed'): ?>
                                            <!-- For completed items -->
                                            <?php if ($is_quiz): ?>
                                                <a href="quiz_results.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-clipboard-check me-1"></i> Results
                                                </a>
                                            <?php else: ?>
                                                <a href="topic-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                    class="btn btn-outline-success btn-sm">
                                                    <i class="bi bi-check-circle me-1"></i> Review
                                                </a>
                                            <?php endif; ?>
                                        <?php elseif (($is_quiz && isset($item['quiz_id']) && isset($current_quiz_id) && $item['quiz_id'] == $current_quiz_id) ||
                                            (!$is_quiz && isset($item['topic_id']) && isset($current_topic_id) && $item['topic_id'] == $current_topic_id)
                                        ): ?>
                                            <!-- For the current item that's not completed -->
                                            <?php if ($is_quiz): ?>
                                                <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-play-fill me-1"></i> Resume
                                                </a>
                                            <?php else: ?>
                                                <a href="topic-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                    class="btn btn-primary btn-sm">
                                                    <i class="bi bi-play-fill me-1"></i> Resume
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- For other uncompleted items -->
                                            <?php if ($is_quiz): ?>
                                                <a href="take_quiz.php?course_id=<?php echo $course_id; ?>&quiz_id=<?php echo $item['quiz_id']; ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil-square me-1"></i> Take Quiz
                                                </a>
                                            <?php else: ?>
                                                <a href="topic-content.php?course_id=<?php echo $course_id; ?>&topic=<?php echo $item['topic_id']; ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-arrow-right me-1"></i> Start
                                                </a>
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