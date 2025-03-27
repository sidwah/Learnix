<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['course_id']) || !isset($_POST['step'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$course_id = intval($_POST['course_id']);
$step = intval($_POST['step']);

// Validate the step value
if ($step < 1 || $step > 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid step value']);
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

// Update the course creation_step
$stmt = $conn->prepare("UPDATE courses SET creation_step = ?, updated_at = NOW() WHERE course_id = ?");
$stmt->bind_param("ii", $step, $course_id);
$success = $stmt->execute();
$stmt->close();

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Step updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>