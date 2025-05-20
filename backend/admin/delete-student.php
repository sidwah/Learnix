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
    'message' => 'Failed to delete student'
];

// Get and validate input
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

// Validate inputs
if ($user_id <= 0) {
    $response['message'] = 'Invalid user ID';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get student details
    $query = "SELECT u.user_id, u.email, u.first_name, u.last_name
              FROM users u
              WHERE u.user_id = ? AND u.role = 'student' AND u.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Student not found');
    }
    
    $student = $result->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Set deleted_at timestamp for user
    $now = date('Y-m-d H:i:s');
    
    // Soft delete user record
    $update_user = "UPDATE users SET deleted_at = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("si", $now, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete user record: ' . $stmt->error);
    }
    
    // Soft delete enrollments
    $update_enrollments = "UPDATE enrollments SET deleted_at = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_enrollments);
    $stmt->bind_param("si", $now, $user_id);
    $stmt->execute();
    
    // Get enrollment IDs for this user to update related records
    $enrollment_ids_query = "SELECT enrollment_id FROM enrollments WHERE user_id = ?";
    $stmt = $conn->prepare($enrollment_ids_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $enrollment_result = $stmt->get_result();
    $enrollment_ids = [];
    
    while ($row = $enrollment_result->fetch_assoc()) {
        $enrollment_ids[] = $row['enrollment_id'];
    }
    
    // If there are enrollments, soft delete related progress records
    if (!empty($enrollment_ids)) {
        $enrollment_id_list = implode(',', $enrollment_ids);
        
        // Soft delete progress
        $update_progress = "UPDATE progress SET deleted_at = ? WHERE enrollment_id IN ($enrollment_id_list)";
        $stmt = $conn->prepare($update_progress);
        $stmt->bind_param("s", $now);
        $stmt->execute();
        
        // Soft delete certificates
        $update_certificates = "UPDATE certificates SET deleted_at = ? WHERE enrollment_id IN ($enrollment_id_list)";
        $stmt = $conn->prepare($update_certificates);
        $stmt->bind_param("s", $now);
        $stmt->execute();
    }
    
    // Log the activity
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $log_details = json_encode([
        'student_id' => $user_id,
        'student_name' => $student['first_name'] . ' ' . $student['last_name'],
        'student_email' => $student['email']
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "student_deleted";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Student deleted successfully'
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