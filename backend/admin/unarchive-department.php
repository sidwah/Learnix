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
        // Check if the department exists and is archived (inactive)
        $deptQuery = "SELECT * FROM departments WHERE department_id = ? AND is_active = 0 AND deleted_at IS NULL";
        $deptStmt = $conn->prepare($deptQuery);
        $deptStmt->bind_param("i", $departmentId);
        $deptStmt->execute();
        $deptResult = $deptStmt->get_result();
        
        if ($deptResult->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Department not found or not archived.'
            ]);
            exit;
        }
        
        // Unarchive the department by setting is_active = 1
        $updateQuery = "UPDATE departments SET is_active = 1, archived_by = NULL, archived_at = NULL WHERE department_id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $departmentId);
        $updateStmt->execute();
        
        // Log activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'unarchive', NULL)";
        $activityStmt = $conn->prepare($activityQuery);
        $userId = $_SESSION['user_id'];
        $activityStmt->bind_param("ii", $departmentId, $userId);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department successfully unarchived.'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to unarchive department: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}
?>