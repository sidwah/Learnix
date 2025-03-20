<?php
// publish_course.php - Changes course status from Draft to Published

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'course_id' => null
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please login again.';
    echo json_encode($response);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate course ID
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_POST['course_id']);

// Get instructor ID for the logged-in user
$userId = $_SESSION['user_id'];
$stmtInstructor = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmtInstructor->bind_param("i", $userId);
$stmtInstructor->execute();
$stmtInstructor->bind_result($instructorId);
$stmtInstructor->fetch();
$stmtInstructor->close();

if (!$instructorId) {
    $response['message'] = 'Instructor not found. Please make sure your account has instructor privileges.';
    echo json_encode($response);
    exit;
}

// Verify course belongs to this instructor
$stmtVerifyCourse = $conn->prepare("SELECT course_id, title, status FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
$stmtVerifyCourse->execute();
$result = $stmtVerifyCourse->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'You do not have permission to modify this course.';
    echo json_encode($response);
    exit;
}

$courseData = $result->fetch_assoc();
$stmtVerifyCourse->close();

// Check if course is already published
if ($courseData['status'] === 'Published') {
    $response['success'] = true;
    $response['message'] = 'Course is already published.';
    $response['course_id'] = $courseId;
    echo json_encode($response);
    exit;
}

// Verify course is complete
$stmtCheckSections = $conn->prepare("SELECT COUNT(*) AS section_count FROM course_sections WHERE course_id = ?");
$stmtCheckSections->bind_param("i", $courseId);
$stmtCheckSections->execute();
$resultSections = $stmtCheckSections->get_result();
$sectionCount = $resultSections->fetch_assoc()['section_count'];
$stmtCheckSections->close();

if ($sectionCount === 0) {
    $response['message'] = 'Course must have at least one section before publishing.';
    echo json_encode($response);
    exit;
}

// Check if topics and/or quizzes exist
$stmtCheckContent = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM section_topics t
         JOIN course_sections s ON t.section_id = s.section_id
         WHERE s.course_id = ?) AS topic_count,
        (SELECT COUNT(*) FROM section_quizzes q
         JOIN course_sections s ON q.section_id = s.section_id
         WHERE s.course_id = ?) AS quiz_count
");
$stmtCheckContent->bind_param("ii", $courseId, $courseId);
$stmtCheckContent->execute();
$resultContent = $stmtCheckContent->get_result();
$contentCounts = $resultContent->fetch_assoc();
$stmtCheckContent->close();

if ($contentCounts['topic_count'] === 0 && $contentCounts['quiz_count'] === 0) {
    $response['message'] = 'Course must have at least one topic or quiz before publishing.';
    echo json_encode($response);
    exit;
}

try {
    // Update course status to Published
    $stmtPublish = $conn->prepare("UPDATE courses SET status = 'Published', creation_step = 6 WHERE course_id = ?");
    $stmtPublish->bind_param("i", $courseId);
    $stmtPublish->execute();
    
    if ($stmtPublish->affected_rows === 0) {
        throw new Exception('Failed to publish course.');
    }
    
    $stmtPublish->close();
    
    // Return success
    $response['success'] = true;
    $response['message'] = 'Course published successfully!';
    $response['course_id'] = $courseId;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Add detailed error info for debugging
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $response['debug'] = [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;