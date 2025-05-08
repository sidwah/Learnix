<?php
// Authentication check
require_once '../config.php';
require_once '../auth/admin/admin-auth-check.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $departmentId = mysqli_real_escape_string($conn, trim($_POST['department_id']));
    
    // Validate input
    if (empty($departmentId)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department ID is required.'
        ]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Soft delete by setting deleted_at
        $query = "UPDATE departments SET deleted_at = NOW() WHERE department_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        
        // Also mark department staff as deleted
        $staffQuery = "UPDATE department_staff SET deleted_at = NOW() WHERE department_id = ? AND deleted_at IS NULL";
        $staffStmt = $conn->prepare($staffQuery);
        $staffStmt->bind_param("i", $departmentId);
        $staffStmt->execute();
        
        // Log delete activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'delete', NULL)";
        $activityStmt = $conn->prepare($activityQuery);
        $userId = $_SESSION['user_id'];
        $activityStmt->bind_param("ii", $departmentId, $userId);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department deleted successfully.'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete department: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}