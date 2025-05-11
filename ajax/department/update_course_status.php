<?php
// ajax/department/update_course_status.php
session_start();
require_once '../../backend/config.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$course_id = $_POST['course_id'] ?? 0;
$status = $_POST['status'] ?? '';
$approval_status = $_POST['approval_status'] ?? '';

if (!$course_id || (!$status && !$approval_status)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id 
               FROM department_staff ds 
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];

// Verify course belongs to department
$verify_query = "SELECT c.course_id, c.title, c.status, c.approval_status 
                 FROM courses c
                 WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
$verify_stmt = $conn->prepare($verify_query);
$verify_stmt->bind_param("ii", $course_id, $department_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Course not found']);
    exit;
}

$course = $verify_result->fetch_assoc();

try {
    $conn->begin_transaction();
    
    // Update course status
    $update_fields = [];
    $update_values = [];
    $param_types = "";
    
    if ($status) {
        $update_fields[] = "status = ?";
        $update_values[] = $status;
        $param_types .= "s";
    }
    
    if ($approval_status) {
        $update_fields[] = "approval_status = ?";
        $update_values[] = $approval_status;
        $param_types .= "s";
    }
    
    $update_fields[] = "updated_at = CURRENT_TIMESTAMP";
    $update_values[] = $course_id;
    $param_types .= "i";
    
    $update_sql = "UPDATE courses SET " . implode(", ", $update_fields) . " WHERE course_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param($param_types, ...$update_values);
    $update_stmt->execute();
    
    // Log the status change
    $log_sql = "INSERT INTO course_review_history 
               (course_id, reviewed_by, previous_status, new_status, comments, review_date)
               VALUES (?, ?, ?, ?, 'Status updated via quick action', CURRENT_TIMESTAMP)";
    $log_stmt = $conn->prepare($log_sql);
    $previous_status = $approval_status ? $course['approval_status'] : $course['status'];
    $new_status = $approval_status ?: $status;
    $log_stmt->bind_param("iiss", $course_id, $_SESSION['user_id'], $previous_status, $new_status);
    $log_stmt->execute();
    
    // Log activity
    $activity_sql = "INSERT INTO user_activity_logs 
                    (user_id, activity_type, activity_details, ip_address, user_agent) 
                    VALUES (?, 'course_status_update', ?, ?, ?)";
    $activity_stmt = $conn->prepare($activity_sql);
    $activity_details = "Updated status of course '{$course['title']}' to: " . ($new_status);
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $activity_stmt->bind_param("isss", $_SESSION['user_id'], $activity_details, $ip_address, $user_agent);
    $activity_stmt->execute();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Course status updated successfully',
        'course_id' => $course_id,
        'new_status' => $new_status
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update status: ' . $e->getMessage()
    ]);
}

// $conn->close();
?>