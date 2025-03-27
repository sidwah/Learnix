<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['title']) || !isset($_POST['short_description']) || 
    !isset($_POST['subcategory_id']) || !isset($_POST['course_level'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$title = trim($_POST['title']);
$short_description = trim($_POST['short_description']);
$subcategory_id = intval($_POST['subcategory_id']);
$course_level = $_POST['course_level'];

// Validate inputs
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Course title cannot be empty']);
    exit;
}

if (empty($short_description)) {
    echo json_encode(['success' => false, 'message' => 'Short description cannot be empty']);
    exit;
}

if ($subcategory_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid subcategory']);
    exit;
}

// Validate course level
$valid_levels = ['Beginner', 'Intermediate', 'Advanced', 'All Levels'];
if (!in_array($course_level, $valid_levels)) {
    echo json_encode(['success' => false, 'message' => 'Please select a valid course level']);
    exit;
}

// Verify that the course belongs to the current instructor
$stmt = $conn->prepare("SELECT instructor_id FROM courses WHERE course_id = ?");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();
$stmt->close();

if (!$course || $course['instructor_id'] != $_SESSION['instructor_id']) {
    echo json_encode(['success' => false, 'message' => 'Course not found or not authorized']);
    exit;
}

// Update the course basic information
$stmt = $conn->prepare("UPDATE courses SET 
                        title = ?, 
                        short_description = ?, 
                        subcategory_id = ?, 
                        course_level = ?,
                        updated_at = NOW() 
                        WHERE course_id = ?");
                        
$stmt->bind_param("ssisi", $title, $short_description, $subcategory_id, $course_level, $course_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Course basics saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>