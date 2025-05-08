<?php
// Authentication check
require_once '../config.php';
require_once '../auth/admin/admin-auth-check.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $departmentId = mysqli_real_escape_string($conn, trim($_POST['department_id']));
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $code = mysqli_real_escape_string($conn, trim($_POST['code']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    $removeHead = isset($_POST['remove_head']) && $_POST['remove_head'] == '1';
    
    // Validate input
    if (empty($departmentId) || empty($name) || empty($code)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department ID, name, and code are required.'
        ]);
        exit;
    }
    
    // Check if code already exists for other departments
    $checkQuery = "SELECT department_id FROM departments WHERE code = ? AND department_id != ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $code, $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department code already exists. Please use a different code.'
        ]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update department
        $query = "UPDATE departments SET name = ?, code = ?, description = ?, is_active = ? WHERE department_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssii", $name, $code, $description, $isActive, $departmentId);
        $stmt->execute();
        
        // If remove head is requested
        if ($removeHead) {
            $removeHeadQuery = "UPDATE department_staff SET status = 'inactive', deleted_at = NOW() 
                                WHERE department_id = ? AND role = 'head' AND deleted_at IS NULL";
            $removeHeadStmt = $conn->prepare($removeHeadQuery);
            $removeHeadStmt->bind_param("i", $departmentId);
            $removeHeadStmt->execute();
            
            // Log head removal
            $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                              VALUES (?, ?, 'head_remove', NULL)";
            $activityStmt = $conn->prepare($activityQuery);
            $userId = $_SESSION['user_id'];
            $activityStmt->bind_param("ii", $departmentId, $userId);
            $activityStmt->execute();
        }
        
        // Log update activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'update', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $userId = $_SESSION['user_id'];
        $details = json_encode([
            'name' => $name,
            'code' => $code,
            'is_active' => $isActive,
            'removed_head' => $removeHead
        ]);
        $activityStmt->bind_param("iis", $departmentId, $userId, $details);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department updated successfully.'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update department: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}