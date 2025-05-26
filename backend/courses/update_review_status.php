<?php
session_start();
require_once '../config.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in and has proper role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit();
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['course_id']) || !isset($input['action'])) {
        throw new Exception('Invalid request data');
    }
    
    $course_id = (int)$input['course_id'];
    $action = $input['action'];
    $user_id = $_SESSION['user_id'];
    
    if ($action !== 'start_review') {
        throw new Exception('Invalid action');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Get department info for the logged-in user
    $dept_query = "SELECT d.department_id 
                   FROM departments d 
                   INNER JOIN department_staff ds ON d.department_id = ds.department_id 
                   WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->bind_param("i", $user_id);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        throw new Exception('Department head not found');
    }
    
    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];
    
    // Verify course belongs to this department and check current status
    $course_query = "SELECT 
                        course_id,
                        title,
                        approval_status,
                        financial_approval_date,
                        department_id
                     FROM courses 
                     WHERE course_id = ? AND department_id = ? AND deleted_at IS NULL";
    
    $course_stmt = $conn->prepare($course_query);
    $course_stmt->bind_param("ii", $course_id, $department_id);
    $course_stmt->execute();
    $course_result = $course_stmt->get_result();
    
    if ($course_result->num_rows === 0) {
        throw new Exception('Course not found or you do not have permission to review this course');
    }
    
    $course = $course_result->fetch_assoc();
    
    // Validate course can be reviewed
    if (empty($course['financial_approval_date'])) {
        throw new Exception('Course must have financial approval before review');
    }
    
    if ($course['approval_status'] !== 'submitted_for_review') {
        throw new Exception('Course must be submitted for review to start the review process');
    }
    
    // Update course status to 'under_review'
    $update_query = "UPDATE courses 
                     SET approval_status = 'under_review', 
                         updated_at = CURRENT_TIMESTAMP 
                     WHERE course_id = ?";
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $course_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update course status');
    }
    
    // Log the status change in course review history
    $history_query = "INSERT INTO course_review_history 
                      (course_id, reviewed_by, previous_status, new_status, comments, review_date) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
    
    $comments = "Review process started by department head";
    $previous_status = 'submitted_for_review';
    $new_status = 'under_review';
    
    $history_stmt = $conn->prepare($history_query);
    $history_stmt->bind_param("iisss", 
        $course_id, 
        $user_id, 
        $previous_status, 
        $new_status, 
        $comments
    );
    $history_stmt->execute();
    
    // Log the activity in course activity logs
    $activity_query = "INSERT INTO course_activity_logs 
                       (course_id, instructor_id, action_type, entity_type, change_details, performed_at) 
                       VALUES (?, ?, 'update', 'course', ?, NOW())";
    
    $change_details = json_encode([
        'action' => 'start_review',
        'previous_status' => $previous_status,
        'new_status' => $new_status,
        'reviewed_by' => $user_id,
        'reviewed_by_role' => 'department_head'
    ]);
    
    $activity_stmt = $conn->prepare($activity_query);
    $activity_stmt->bind_param("iis", $course_id, $user_id, $change_details);
    $activity_stmt->execute();
    
    // Create notification for all course instructors
    $instructor_query = "SELECT DISTINCT u.user_id, u.first_name, u.last_name, u.email
                         FROM course_instructors ci
                         INNER JOIN instructors i ON ci.instructor_id = i.instructor_id
                         INNER JOIN users u ON i.user_id = u.user_id
                         WHERE ci.course_id = ? AND ci.deleted_at IS NULL AND i.deleted_at IS NULL";
    
    $instructor_stmt = $conn->prepare($instructor_query);
    $instructor_stmt->bind_param("i", $course_id);
    $instructor_stmt->execute();
    $instructor_result = $instructor_stmt->get_result();
    
    // Send notifications to instructors
    $notification_query = "INSERT INTO user_notifications 
                          (user_id, type, title, message, related_id, related_type, created_at) 
                          VALUES (?, 'course_review_started', ?, ?, ?, 'course', NOW())";
    
    $notification_stmt = $conn->prepare($notification_query);
    
    while ($instructor = $instructor_result->fetch_assoc()) {
        $title = "Course Review Started";
        $message = "The review process for your course '{$course['title']}' has been started by the department head.";
        
        $notification_stmt->bind_param("issi", 
            $instructor['user_id'], 
            $title, 
            $message, 
            $course_id
        );
        $notification_stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Course review started successfully',
        'data' => [
            'course_id' => $course_id,
            'course_title' => $course['title'],
            'previous_status' => $previous_status,
            'new_status' => $new_status,
            'review_started_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    // mysqli::rollback() is safe to call even if no transaction is active
    $conn->rollback();
    
    error_log("Course review status update error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} finally {
    // Close statements if they exist
    if (isset($dept_stmt)) $dept_stmt->close();
    if (isset($course_stmt)) $course_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    if (isset($history_stmt)) $history_stmt->close();
    if (isset($activity_stmt)) $activity_stmt->close();
    if (isset($instructor_stmt)) $instructor_stmt->close();
    if (isset($notification_stmt)) $notification_stmt->close();
}
?>