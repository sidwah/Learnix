<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include header
include '../includes/student-header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../");
    exit();
}

// Check if we have a course_id
if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    header("Location: courses.php");
    exit();
}

$course_id = intval($_GET['course_id']);

// Connect to database
require_once '../backend/config.php';

// Fetch course details
$sql = "SELECT c.*, u.first_name, u.last_name, u.profile_pic
        FROM courses c
        JOIN instructors i ON c.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        WHERE c.course_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: courses.php");
    exit();
}

$course = $result->fetch_assoc();
$course_title = htmlspecialchars($course['title']);
$instructor_name = htmlspecialchars($course['first_name'] . ' ' . $course['last_name']);
?>

<!-- ========== MAIN CONTENT ========== -->
<main id="content" role="main">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow border-0 rounded-lg">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-success-subtle text-success d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3">
                                <i class="bi-check-lg fs-1"></i>
                            </div>
                            <h1 class="mb-2">Payment Successful!</h1>
                            <p class="text-muted">Thank you for your purchase.</p>
                        </div>
                        
                        <div class="d-flex align-items-center justify-content-center mb-4">
                            <?php if (!empty($course['thumbnail'])): ?>
                                <div class="me-3">
                                    <img class="avatar avatar-lg" src="../uploads/thumbnails/<?php echo htmlspecialchars($course['thumbnail']); ?>" alt="<?php echo $course_title; ?>">
                                </div>
                            <?php endif; ?>
                            <div class="text-start">
                                <h5 class="mb-1"><?php echo $course_title; ?></h5>
                                <p class="text-muted small mb-0">By <?php echo $instructor_name; ?></p>
                            </div>
                        </div>
                        
                        <p>You now have full access to this course. You can start learning immediately or access it anytime from your dashboard.</p>
                        
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                            <a href="lesson.php?course_id=<?php echo $course_id; ?>" class="btn btn-primary btn-lg px-4 gap-3">
                                <i class="bi-play-circle me-2"></i> Start Learning
                            </a>
                            <a href="dashboard.php" class="btn btn-outline-secondary btn-lg px-4">
                                <i class="bi-grid me-2"></i> Go to Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- ========== END MAIN CONTENT ========== -->

<?php include '../includes/student-footer.php'; ?>