<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get course_id from POST
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

// Validate course ownership
$stmt = $conn->prepare("SELECT course_id, title FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION['instructor_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Course not found or you do not have permission']);
    exit;
}

$course = $result->fetch_assoc();
$course_title = $course['title'];

// Begin transaction
$conn->begin_transaction();

try {
    // Update course status to pending
    $stmt = $conn->prepare("UPDATE courses SET approval_status = 'Pending', updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to update course status");
    }
    
    // Create a review request
    $current_date = date('Y-m-d H:i:s');
    $notes = "Instructor has requested review for course publication.";
    
    $stmt = $conn->prepare("
        INSERT INTO course_review_requests 
        (course_id, requested_by, request_notes, status, created_at) 
        VALUES (?, ?, ?, 'Pending', ?)
    ");
    $stmt->bind_param("iiss", $course_id, $_SESSION['instructor_id'], $notes, $current_date);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to create review request");
    }
    
    // Find admins to notify (in a real system, you would send emails or notifications here)
    $stmt = $conn->prepare("SELECT user_id, email FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->get_result();
    
    // Log the submission for auditing
    error_log("Course review requested by instructor ID " . $_SESSION['instructor_id'] . 
              " for course ID " . $course_id . " (" . $course_title . ")");
    
    // Commit the transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true, 
        'message' => 'Course submitted for review successfully',
        'course_title' => $course_title
    ]);
    
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    
    // Error response
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $conn->error
    ]);
}