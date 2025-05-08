<?php
// Initialize the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database connection
require_once('../config.php');

// Get request data
$data = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($data['student_id']) || !isset($data['status'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$student_id = intval($data['student_id']);
$new_status = $data['status'];
$reason = isset($data['reason']) ? $data['reason'] : null;

// Validate status value
$valid_statuses = ['active', 'suspended', 'banned'];
if (!in_array($new_status, $valid_statuses)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // Update user status
    $stmt = $conn->prepare("UPDATE users SET status = ? WHERE user_id = ? AND role = 'student'");
    $stmt->bind_param('si', $new_status, $student_id);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        // No rows were updated, student not found or not a student
        throw new Exception('Student not found or not a student');
    }
    
    // If a reason is provided for suspension or ban, log it
    if ($reason && ($new_status === 'suspended' || $new_status === 'banned')) {
        // Log the status change reason in an appropriate table
        $admin_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO user_status_logs (user_id, changed_by, old_status, new_status, reason, change_date) 
                              VALUES (?, ?, (SELECT status FROM users WHERE user_id = ?), ?, ?, NOW())");
        $stmt->bind_param('iisss', $student_id, $admin_id, $student_id, $new_status, $reason);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Send success response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Student status updated successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
?>