<?php
session_start();
require_once '../config.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Get and validate input data
$department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$department_description = trim($_POST['department_description'] ?? '');
$original_description = trim($_POST['original_description'] ?? '');

// Validate required fields
if (!$department_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Verify user has permission to update this department
if ($department_id != $_SESSION['department_id'] || $user_id != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Validate description length
if (strlen($department_description) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Description cannot exceed 1000 characters']);
    exit;
}

try {
    // Start transaction
    $conn->autocommit(false);
    
    // Verify user's role in department (head or secretary)
    $role_check = "SELECT ds.role, ds.status FROM department_staff ds 
                   WHERE ds.user_id = ? AND ds.department_id = ? AND ds.deleted_at IS NULL AND ds.status = 'active'";
    $stmt = $conn->prepare($role_check);
    $stmt->bind_param("ii", $user_id, $department_id);
    $stmt->execute();
    $role_result = $stmt->get_result();
    $user_role = $role_result->fetch_assoc();
    $stmt->close();
    
    if (!$user_role) {
        throw new Exception('You do not have permission to update this department');
    }
    
    // Check if description actually changed
    if ($department_description === $original_description) {
        echo json_encode(['success' => true, 'message' => 'No changes detected']);
        exit;
    }
    
    // Update department description
    $update_query = "UPDATE departments SET 
                     description = ?, 
                     updated_at = CURRENT_TIMESTAMP 
                     WHERE department_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $department_description, $department_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update department description');
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception('Department not found or no changes made');
    }
    
    // Log the activity
    $log_query = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details, performed_at) 
                  VALUES (?, ?, 'update', ?, NOW())";
    $stmt = $conn->prepare($log_query);
    $log_details = json_encode([
        'field' => 'description',
        'old_value' => $original_description,
        'new_value' => $department_description,
        'user_role' => $user_role['role']
    ]);
    $stmt->bind_param("iis", $department_id, $user_id, $log_details);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    $conn->autocommit(true);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Department description updated successfully',
        'data' => [
            'new_description' => $department_description,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $conn->autocommit(true);
    
    // Log error for debugging
    error_log("Department update error: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    
} catch (mysqli_sql_exception $e) {
    // Rollback transaction on SQL error
    $conn->rollback();
    $conn->autocommit(true);
    
    // Log SQL error for debugging
    error_log("SQL error in department update: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
?>