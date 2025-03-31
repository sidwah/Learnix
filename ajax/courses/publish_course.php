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
    // Update course status to Published
    $stmt = $conn->prepare("UPDATE courses SET status = 'Published', updated_at = NOW() WHERE course_id = ?");
    $stmt->bind_param("i", $course_id);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to update course status");
    }
    
    // Create validation log entry
    $current_date = date('Y-m-d H:i:s');
    $validation_type = 'Automatic';
    $validation_results = json_encode([
        'status' => 'passed',
        'published_date' => $current_date,
        'published_by' => $_SESSION['instructor_id']
    ]);
    
    $stmt = $conn->prepare("
        INSERT INTO content_validation_logs 
        (course_id, validation_type, validation_results, validation_date, validated_by) 
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssi", $course_id, $validation_type, $validation_results, $current_date, $_SESSION['instructor_id']);
    $result = $stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to create validation log");
    }
    
    // Commit the transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true, 
        'message' => 'Course published successfully',
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