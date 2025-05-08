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
        // Set department to inactive (archive)
        $query = "UPDATE departments SET is_active = 0, archived_by = ?, archived_at = NOW() WHERE department_id = ?";
        $stmt = $conn->prepare($query);
        $userId = $_SESSION['user_id'];
        $stmt->bind_param("ii", $userId, $departmentId);
        $stmt->execute();
        
        // Log archive activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'archive', NULL)";
        $activityStmt = $conn->prepare($activityQuery);
        $activityStmt->bind_param("ii", $departmentId, $userId);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department archived successfully.'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to archive department: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}