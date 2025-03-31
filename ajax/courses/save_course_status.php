<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get course_id and status from POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

// Validate status
$allowed_statuses = ['Draft', 'Published'];
if (!in_array($status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit;
}

// Validate course ownership
$stmt = $conn->prepare("SELECT course_id FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission']);
    exit;
}

// Update course status
$stmt = $conn->prepare("UPDATE courses SET status = ?, updated_at = NOW() WHERE course_id = ?");
$stmt->bind_param("si", $status, $course_id);
$result = $stmt->execute();

if ($result) {
    echo json_encode(['success' => true, 'message' => 'Course status updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating course status: ' . $conn->error]);
}