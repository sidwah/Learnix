<?php
// Include database connection
require_once '../../backend/config.php';

// Check if admin is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred'
];

// Check if course_id is set
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $courseId = intval($_POST['course_id']);
    $adminId = $_SESSION['user_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update course status to Draft but keep the approval status
    $updateCourseQuery = "
        UPDATE courses 
        SET status = 'Draft'
        WHERE course_id = ?
    ";
    
    $stmt = $conn->prepare($updateCourseQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    
    // Insert log entry for suspension
    $logData = json_encode([
        'status' => 'suspended',
        'suspended_by' => $adminId,
        'suspended_date' => date('Y-m-d H:i:s'),
        'note' => 'Course suspended by administrator'
    ]);
    
    $logQuery = "
        INSERT INTO content_validation_logs 
        (course_id, validation_type, validation_results, validation_date, validated_by) 
        VALUES (?, 'Manual', ?, NOW(), ?)
    ";
    
    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param('isi', $courseId, $logData, $adminId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    $response['status'] = 'success';
    $response['message'] = 'Course suspended successfully';
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response['message'] = 'Error suspending course: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);