<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/signin-header.php';

// // Check if course_id is provided in the URL
// if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
//     // Redirect to courses page if no valid ID is provided
//     header("Location: courses.php");
//     exit();
// }

// Get course ID from URL
$course_id = 131;

// Connect to database
require_once '../backend/config.php';

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

// Get course modules/sections
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

// Get current section (assuming we're looking at the last section - end of course assessment)
$current_section = end($sections);
$section_id = $current_section['section_id'];

// Check if user is enrolled in this course
$is_enrolled = false;
if (isset($_SESSION['user_id'])) {
    // Check if the user is enrolled in this course
    $user_id = $_SESSION['user_id'];

    $sql = "SELECT * FROM enrollments WHERE user_id = ? AND course_id = ? AND status = 'Active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $user_id, $course_id);
    $stmt->execute();
    $enrollment_result = $stmt->get_result();

    $is_enrolled = ($enrollment_result->num_rows > 0);
}

// Get assignments and other content for the current section
$sql = "SELECT st.topic_id, st.title as topic_title, st.is_previewable,
               tc.content_id, tc.content_type, tc.title as content_title, 
               tc.video_url, tc.content_text, tc.external_url,
               sq.quiz_id, sq.quiz_title, sq.pass_mark, sq.time_limit
        FROM section_topics st
        LEFT JOIN topic_content tc ON st.topic_id = tc.topic_id
        LEFT JOIN section_quizzes sq ON st.topic_id = sq.topic_id
        WHERE st.section_id = ?
        ORDER BY st.position";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $section_id);
$stmt->execute();
$topics_result = $stmt->get_result();
$topics = [];

while ($topic = $topics_result->fetch_assoc()) {
    $topics[] = $topic;
}

// Get course progress for overdue items
$sql = "SELECT COUNT(*) as completed_topics, 
               (SELECT COUNT(*) FROM section_topics WHERE section_id IN 
                (SELECT section_id FROM course_sections WHERE course_id = ?)) as total_topics
        FROM progress p
        JOIN enrollments e ON p.enrollment_id = e.enrollment_id
        WHERE e.user_id = ? AND e.course_id = ? AND p.completion_status = 'Completed'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $course_id, $user_id, $course_id);
$stmt->execute();
$progress_result = $stmt->get_result();
$progress = $progress_result->fetch_assoc();

$completed_percentage = 0;
if (isset($progress['total_topics']) && $progress['total_topics'] > 0) {
    $completed_percentage = round(($progress['completed_topics'] / $progress['total_topics']) * 100);
}

// Close database connection
$stmt->close();
$conn->close();

// Helper function to generate assignment status HTML
function getAssignmentStatusHTML($status, $date = null) {
    if ($status === 'Completed') {
        return '<span class="badge bg-success">Completed</span>';
    } else if ($status === 'Overdue') {
        return '<span class="badge bg-danger">Overdue</span>';
    } else if ($status === 'Locked') {
        return '<span class="badge bg-secondary">Locked</span>';
    } else {
        return '<span class="badge bg-primary">In Progress</span>';
    }
}

// Format time (e.g., "4 min")
function formatTime($minutes) {
    return $minutes . ' min';
}
?>

<!-- Main Content -->
<main id="content" role="main" class="bg-light">
    <div class="container py-5">
        <div class="row">
            <!-- Left Sidebar -->
            <div class="col-lg-3">
                <!-- Meta Logo -->
                <div class="mb-4">
                    <img src="../assets/img/logo-meta.svg" alt="Meta" width="120">
                </div>

                <!-- Course Title -->
                <div class="mb-4">
                    <h5 class="fw-bold">Programming with JavaScript</h5>
                    <p class="text-muted"><?php echo $course['category_name']; ?></p>
                </div>

                <!-- Navigation -->
                <div class="accordion mb-4" id="courseNavAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#courseMaterialCollapse" aria-expanded="false" aria-controls="courseMaterialCollapse">
                                <i class="bi bi-book me-2"></i> Course Material
                            </button>
                        </h2>
                        <div id="courseMaterialCollapse" class="accordion-collapse collapse" data-bs-parent="#courseNavAccordion">
                            <div class="accordion-body p-0">
                                <ul class="list-group list-group-flush">
                                    <?php foreach ($sections as $section): ?>
                                        <li class="list-group-item border-0">
                                            <a href="course-section.php?id=<?php echo $course_id; ?>&section=<?php echo $section['section_id']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($section['title']); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Links -->
                <ul class="list-group mb-4">
                    <li class="list-group-item border-0 bg-transparent">
                        <a href="grades.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                            <i class="bi bi-award me-2"></i> Grades
                        </a>
                    </li>
                    <li class="list-group-item border-0 bg-transparent">
                        <a href="notes.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                            <i class="bi bi-journal-text me-2"></i> Notes
                        </a>
                    </li>
                    <li class="list-group-item border-0 bg-transparent">
                        <a href="discussion.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                            <i class="bi bi-chat-dots me-2"></i> Discussion Forums
                        </a>
                    </li>
                    <li class="list-group-item border-0 bg-transparent">
                        <a href="messages.php?course_id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                            <i class="bi bi-envelope me-2"></i> Messages
                        </a>
                    </li>
                    <li class="list-group-item border-0 bg-transparent">
                        <a href="course-details.php?id=<?php echo $course_id; ?>" class="text-decoration-none text-dark">
                            <i class="bi bi-info-circle me-2"></i> Course Info
                        </a>
                    </li>
                </ul>
            </div>
            <!-- End Left Sidebar -->

            <!-- Main Content -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <button class="btn btn-link text-decoration-none text-dark p-0 d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#endOfCourseCollapse" aria-expanded="true" aria-controls="endOfCourseCollapse">
                            <span class="me-2">
                                <i class="bi bi-caret-down-fill"></i>
                            </span>
                            <h5 class="mb-0 fw-bold">End-of-Course Graded Assessment</h5>
                        </button>
                    </div>
                    <div class="collapse show" id="endOfCourseCollapse">
                        <div class="card-body">
                            <!-- Progress Stats -->
                            <div class="d-flex flex-wrap mb-4 text-muted small">
                                <div class="me-4">
                                    <i class="bi bi-camera-video me-1"></i>
                                    <span>1 min of videos left</span>
                                </div>
                                <div class="me-4">
                                    <i class="bi bi-book me-1"></i>
                                    <span>3 min of readings left</span>
                                </div>
                                <div>
                                    <i class="bi bi-clipboard-check me-1"></i>
                                    <span>2 graded assessments left</span>
                                </div>
                            </div>

                            <!-- Description -->
                            <p>In the final module, you'll synthesize the skills you gained from the course to create code for the "Little lemon receipt maker. After you complete the individual units in this module, you will be able to take the graded assessment. You'll also have to opportunity to reflect on the course content and the learning path that lies ahead.</p>

                            <!-- Learning Objectives -->
                            <div class="mb-3">
                                <button class="btn btn-link text-primary p-0 text-decoration-none" type="button" data-bs-toggle="collapse" data-bs-target="#learningObjectivesCollapse">
                                    <i class="bi bi-chevron-down me-1"></i> Show Learning Objectives
                                </button>
                                <div class="collapse mt-2" id="learningObjectivesCollapse">
                                    <div class="card card-body bg-light">
                                        <ul class="mb-0">
                                            <li>Create a functioning receipt maker using JavaScript</li>
                                            <li>Apply concepts learned throughout the course</li>
                                            <li>Demonstrate proficiency in JavaScript programming</li>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">End-of-Course Graded Assessment</h5>
                            <span class="badge bg-warning text-dark">1 Overdue</span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            <!-- Item 1: Video -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center text-success me-3">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Recap Programming with JavaScript</h6>
                                    <p class="mb-0 small text-muted">Video • 4 min</p>
                                </div>
                            </li>

                            <!-- Item 2: Reading -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center text-success me-3">
                                    <i class="bi bi-check-circle-fill fs-5"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">About the Little Lemon receipt maker exercise</h6>
                                    <p class="mb-0 small text-muted">Reading • 10 min</p>
                                </div>
                            </li>

                            <!-- Item 3: Assignment (Overdue) -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center me-3">
                                    <i class="bi bi-lock-fill fs-5 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Little Lemon Receipt Maker</h6>
                                        <span class="badge bg-danger">Overdue</span>
                                    </div>
                                    <p class="mb-0 small text-muted">Programming Assignment • 3h • Grade: --</p>
                                </div>
                                <div class="ms-2">
                                    <a href="assignment.php?id=<?php echo $course_id; ?>&topic=<?php echo isset($topics[2]) ? $topics[2]['topic_id'] : ''; ?>" class="btn btn-primary">Resume</a>
                                </div>
                            </li>

                            <!-- Item 4: Self Review -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center me-3">
                                    <i class="bi bi-lock-fill fs-5 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Self review: Little Lemon receipt maker</h6>
                                    <p class="mb-0 small text-muted">Practice Assignment • 5 min • Grade: --</p>
                                </div>
                            </li>

                            <!-- Item 5: Final Graded Quiz -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center me-3">
                                    <i class="bi bi-lock-fill fs-5 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">Final graded quiz: Programming with JavaScript</h6>
                                    <p class="mb-0 small text-muted">Graded Assignment • Submitted • Grade: --</p>
                                </div>
                            </li>

                            <!-- Item 6: Discussion Prompt -->
                            <li class="list-group-item d-flex align-items-center py-3">
                                <div class="d-flex align-items-center me-3">
                                    <i class="bi bi-lock-fill fs-5 text-secondary"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0">What challenges did you encounter during the assignment?</h6>
                                    <p class="mb-0 small text-muted">Discussion Prompt • 10 min</p>
                                </div>
                            </li>
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

            <!-- Right Sidebar -->
            <div class="col-lg-3">
                <!-- Weekly Goal Tracker -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Weekly goal progress tracker</h5>
                        <p class="card-text small">Learners with goals are 75% more likely to complete their courses. Set a weekly goal now to take charge of your learning journey and boost your success!</p>
                        <a href="#" class="btn btn-outline-primary w-100">Set your weekly goal</a>
                    </div>
                </div>

                <!-- Course Timeline -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <h5 class="card-title fw-bold">Course timeline</h5>
                        
                        <!-- Overdue Alert -->
                        <div class="alert alert-warning mb-3">
                            <h6 class="alert-heading">Assessment overdue!</h6>
                            <p class="small mb-1">You can still get back on track. Just reset your deadlines and ensure you pass your assessments by April 8 at 10:59 PM.</p>
                            <a href="#" class="small">What does this mean?</a>
                            <div class="mt-2">
                                <a href="#" class="btn btn-sm btn-outline-primary">Reset</a>
                            </div>
                        </div>

                        <!-- Timeline -->
                        <div class="position-relative ps-4 mt-4">
                            <!-- Timeline Line -->
                            <div class="position-absolute top-0 bottom-0 start-0 ms-2 border-start border-2"></div>
                            
                            <!-- Start Date -->
                            <div class="position-relative mb-4">
                                <div class="position-absolute top-0 start-0 translate-middle-x">
                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 16px; height: 16px;">
                                        <i class="bi bi-calendar-event fs-6"></i>
                                    </div>
                                </div>
                                <div class="ms-4">
                                    <h6 class="mb-1 fs-6">Start date: June 14, 2024</h6>
                                </div>
                            </div>

                            <!-- Next Deadlines -->
                            <div class="ms-4 mb-4">
                                <p class="mb-2 fw-bold small">Your next two deadlines</p>
                                
                                <!-- Module Quiz 1 -->
                                <div class="mb-2">
                                    <a href="#" class="text-decoration-none text-primary">Module quiz: Introduction to JavaScript</a>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="badge bg-danger me-2">Overdue</span>
                                        <span class="small text-muted">Graded Assignment</span>
                                    </div>
                                </div>
                                
                                <!-- Module Quiz 2 -->
                                <div>
                                    <a href="#" class="text-decoration-none text-primary">Module quiz: The Building Blocks of a Program</a>
                                    <div class="d-flex align-items-center mt-1">
                                        <span class="badge bg-danger me-2">Overdue</span>
                                        <span class="small text-muted">Graded Assignment</span>
                                    </div>
                                </div>
                            </div>

                            <!-- End Date -->
                            <div class="position-relative">
                                <div class="position-absolute top-0 start-0 translate-middle-x">
                                    <div class="rounded-circle bg-light text-dark border d-flex align-items-center justify-content-center" style="width: 16px; height: 16px;">
                                        <i class="bi bi-flag fs-6"></i>
                                    </div>
                                </div>
                                <div class="ms-4">
                                    <h6 class="mb-1 fs-6 small">Estimated end date: November 6, 2024</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Right Sidebar -->
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