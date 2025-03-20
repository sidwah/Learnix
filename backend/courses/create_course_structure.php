<?php
/**
 * Backend script for handling course structure (Step 2)
 * File: ../backend/courses/create_course_structure.php
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
    'section_ids' => []
];

// Function to sanitize input
function sanitizeInput($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

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

// Validate sections data
if (!isset($_POST['sections']) || !is_array($_POST['sections']) || count($_POST['sections']) === 0) {
    $response['message'] = 'At least one section is required.';
    echo json_encode($response);
    exit;
}

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
    $response['message'] = 'You do not have permission to modify this course.';
    echo json_encode($response);
    exit;
}
$stmtVerifyCourse->close();

// Start transaction
$conn->begin_transaction();

try {
    // First, delete all existing sections (this will cascade to topics, etc. if foreign key constraints are set up)
    $stmtDeleteSections = $conn->prepare("DELETE FROM course_sections WHERE course_id = ?");
    $stmtDeleteSections->bind_param("i", $courseId);
    $stmtDeleteSections->execute();
    $stmtDeleteSections->close();
    
    // Insert new sections
    $stmtSection = $conn->prepare("INSERT INTO course_sections (course_id, title, position) VALUES (?, ?, ?)");
    
    $sections = $_POST['sections'];
    $positions = isset($_POST['section_positions']) ? $_POST['section_positions'] : [];
    
    foreach ($sections as $index => $title) {
        $sectionTitle = sanitizeInput($title);
        
        // Use provided position if available, otherwise use the index
        $position = isset($positions[$index]) ? intval($positions[$index]) : $index + 1;
        
        $stmtSection->bind_param("isi", $courseId, $sectionTitle, $position);
        $stmtSection->execute();
        
        // Store section ID for the response
        $response['section_ids'][$index] = $stmtSection->insert_id;
    }
    $stmtSection->close();
    
    // Update course creation step - first check if creation_step column exists
    $checkColumnQuery = "SHOW COLUMNS FROM courses LIKE 'creation_step'";
    $checkColumnResult = $conn->query($checkColumnQuery);
    
    if ($checkColumnResult->num_rows > 0) {
        // Column exists, update it
        $stmtStep = $conn->prepare("UPDATE courses SET creation_step = 2 WHERE course_id = ?");
        $stmtStep->bind_param("i", $courseId);
        $stmtStep->execute();
        $stmtStep->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success
    $response['success'] = true;
    $response['message'] = 'Course structure saved successfully.';
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    $response['success'] = false;
    $response['message'] = $e->getMessage();
    
    // Add detailed error info for debugging
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        $response['debug'] = [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'post' => $_POST
        ];
    }
} finally {
    // Close connection
    $conn->close();
}

// Send JSON response
echo json_encode($response);
exit;