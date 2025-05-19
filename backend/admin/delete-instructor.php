<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';


// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to delete instructor'
];

// Get and validate input
$instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 0;

// Validate inputs
if ($instructor_id <= 0) {
    $response['message'] = 'Invalid instructor ID';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get instructor details
    $query = "SELECT i.instructor_id, u.user_id, u.email, u.first_name, u.last_name
              FROM instructors i
              JOIN users u ON i.user_id = u.user_id
              WHERE i.instructor_id = ? AND i.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Instructor not found');
    }
    
    $instructor = $result->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Set deleted_at timestamp for instructor
    $now = date('Y-m-d H:i:s');
    
    $update_instructor = "UPDATE instructors SET deleted_at = ? WHERE instructor_id = ?";
    $stmt = $conn->prepare($update_instructor);
    $stmt->bind_param("si", $now, $instructor_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete instructor record: ' . $stmt->error);
    }
    
    // Set deleted_at timestamp for user
    $update_user = "UPDATE users SET deleted_at = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("si", $now, $instructor['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete user record: ' . $stmt->error);
    }
    
    // Remove from department assignments
    $update_dept = "UPDATE department_instructors SET deleted_at = ? WHERE instructor_id = ?";
    $stmt = $conn->prepare($update_dept);
    $stmt->bind_param("si", $now, $instructor_id);
    $stmt->execute();
    
    // Remove from course assignments
    $update_courses = "UPDATE course_instructors SET deleted_at = ? WHERE instructor_id = ?";
    $stmt = $conn->prepare($update_courses);
    $stmt->bind_param("si", $now, $instructor_id);
    $stmt->execute();
    
    // Log the activity
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $log_details = json_encode([
        'instructor_id' => $instructor_id,
        'instructor_name' => $instructor['first_name'] . ' ' . $instructor['last_name'],
        'instructor_email' => $instructor['email']
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "instructor_deleted";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Instructor deleted successfully'
    ];
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;