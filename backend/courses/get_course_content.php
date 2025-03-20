<?php
// Start session and include necessary config files
require '../session_start.php';
require '../config.php';

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => '',
    'content' => null
];

if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please log in again.';
    echo json_encode($response);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_GET['course_id']);
$userId = $_SESSION['user_id'];

// Prepare to verify if the course belongs to the logged-in instructor
$stmtVerifyCourse = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ? AND instructor_id = (
    SELECT instructor_id FROM instructors WHERE user_id = ?
)");
$stmtVerifyCourse->bind_param("ii", $courseId, $userId);
$stmtVerifyCourse->execute();
$stmtVerifyCourse->store_result();

if ($stmtVerifyCourse->num_rows === 0) {
    $response['message'] = 'You do not have permission to access this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

try {
    // Fetch course content details
    $stmtCourseContent = $conn->prepare("
        SELECT 
            section_id, title, content_type, details
        FROM 
            course_sections 
        WHERE 
            course_id = ?
    ");
    $stmtCourseContent->bind_param("i", $courseId);
    $stmtCourseContent->execute();
    $result = $stmtCourseContent->get_result();

    $content = [];
    while ($row = $result->fetch_assoc()) {
        $content[] = $row;
    }

    if (count($content) > 0) {
        $response['success'] = true;
        $response['content'] = $content;
    } else {
        $response['message'] = 'No content found for this course.';
    }
} catch (Exception $e) {
    $response['success'] = false;
    $response['message'] = 'Failed to retrieve course content: ' . $e->getMessage();
} finally {
    $conn->close();
}

echo json_encode($response);
exit;
?>
