<?php
/**
 * Backend script for retrieving course sections
 * File: ../backend/courses/get_course_sections.php
 */

// Start session and include config
require '../session_start.php';
require '../config.php';

// Set response header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'sections' => []
];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Session expired. Please login again.';
    echo json_encode($response);
    exit;
}

// Check if request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'Invalid request method.';
    echo json_encode($response);
    exit;
}

// Validate course ID
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    $response['message'] = 'Course ID is required.';
    echo json_encode($response);
    exit;
}

$courseId = intval($_GET['course_id']);

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
$stmtVerifyCourse = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmtVerifyCourse->bind_param("ii", $courseId, $instructorId);
$stmtVerifyCourse->execute();
$stmtVerifyCourse->store_result();

if ($stmtVerifyCourse->num_rows === 0) {
    $response['message'] = 'You do not have permission to access this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

try {
    // Fetch sections for this course
    $stmtSections = $conn->prepare("
        SELECT section_id, title, position
        FROM course_sections
        WHERE course_id = ?
        ORDER BY position
    ");
    $stmtSections->bind_param("i", $courseId);
    $stmtSections->execute();
    $result = $stmtSections->get_result();
    
    $sections = [];
    while ($row = $result->fetch_assoc()) {
        $sections[] = $row;
    }
    $stmtSections->close();
    
    $response['success'] = true;
    $response['sections'] = $sections;
    
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