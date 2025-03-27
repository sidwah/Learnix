<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate required input
if (!isset($_POST['course_id']) || !isset($_POST['full_description'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$full_description = trim($_POST['full_description']);

// Validate inputs
if (empty($full_description)) {
    echo json_encode(['success' => false, 'message' => 'Course description cannot be empty']);
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

// Update the course description
$stmt = $conn->prepare("UPDATE courses SET 
                        full_description = ?, 
                        updated_at = NOW() 
                        WHERE course_id = ?");
                        
$stmt->bind_param("si", $full_description, $course_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Course description saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>