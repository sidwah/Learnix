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
    
    if ($action !== 'publish') {
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
                        status,
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
        throw new Exception('Course not found or you do not have permission to publish this course');
    }
    
    $course = $course_result->fetch_assoc();
    
    // Validate course can be published
    if (empty($course['financial_approval_date'])) {
        throw new Exception('Course must have financial approval before publishing');
    }
    
    if ($course['approval_status'] !== 'approved') {
        throw new Exception('Course must be approved before publishing');
    }
    
    if ($course['status'] === 'Published') {
        throw new Exception('Course is already published');
    }
    
    // Update course status to Published
    $update_query = "UPDATE courses 
                     SET status = 'Published', 
                         updated_at = CURRENT_TIMESTAMP 
                     WHERE course_id = ?";
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $course_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to publish course');
    }
    
    // Log the activity
    $log_query = "INSERT INTO course_activity_logs 
                  (course_id, instructor_id, action_type, entity_type, change_details, performed_at) 
                  VALUES (?, ?, 'update', 'course', ?, NOW())";
    
    $change_details = json_encode([
        'action' => 'publish_course',
        'previous_status' => $course['status'],
        'new_status' => 'Published',
        'published_by' => $user_id,
        'published_by_role' => 'department_head'
    ]);
    
    $log_stmt = $conn->prepare($log_query);
    $log_stmt->bind_param("iis", $course_id, $user_id, $change_details);
    $log_stmt->execute();
    
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
                          VALUES (?, 'course_published', ?, ?, ?, 'course', NOW())";
    
    $notification_stmt = $conn->prepare($notification_query);
    
    while ($instructor = $instructor_result->fetch_assoc()) {
        $title = "Course Published Successfully";
        $message = "Your course '{$course['title']}' has been published and is now available to students.";
        
        $notification_stmt->bind_param("issi", 
            $instructor['user_id'], 
            $title, 
            $message, 
            $course_id
        );
        $notification_stmt->execute();
    }
    
    // Update course analytics (set initial values)
    $analytics_query = "INSERT INTO course_analytics 
                       (course_id, total_students, active_students, completion_rate, 
                        average_rating, revenue_total, revenue_month, views_total, views_month, last_updated)
                       VALUES (?, 0, 0, 0.00, 0.00, 0.00, 0.00, 0, 0, NOW())
                       ON DUPLICATE KEY UPDATE last_updated = NOW()";
    
    $analytics_stmt = $conn->prepare($analytics_query);
    $analytics_stmt->bind_param("i", $course_id);
    $analytics_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Course published successfully',
        'data' => [
            'course_id' => $course_id,
            'course_title' => $course['title'],
            'new_status' => 'Published',
            'published_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Course publish error: " . $e->getMessage());
    
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
    if (isset($log_stmt)) $log_stmt->close();
    if (isset($instructor_stmt)) $instructor_stmt->close();
    if (isset($notification_stmt)) $notification_stmt->close();
    if (isset($analytics_stmt)) $analytics_stmt->close();
}
?>