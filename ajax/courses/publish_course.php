<?php
// ajax/courses/publish_course.php - UPDATED for institutional LMS
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// This file now redirects to submit_for_review.php for institutional workflow
// Direct publishing is not allowed in institutional setting

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id for the current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor profile not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];
$stmt->close();

// Get course_id from POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// In institutional LMS, direct publishing is not allowed
// Instead, redirect to the review submission process
echo json_encode([
    'success' => false,
    'redirect' => true,
    'message' => 'Direct publishing is not allowed. Redirecting to review submission...',
    'course_id' => $course_id
]);

// Log the attempt for auditing
error_log("Direct publish attempt was redirected to review workflow for course ID: {$course_id} by instructor ID: {$instructor_id}");
?>