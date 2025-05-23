<?php
// backend/department/update-general-settings.php
session_start();
require_once '../config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['department_id'])) {
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

$department_id = filter_input(INPUT_POST, 'department_id', FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];
$department_description = trim($_POST['department_description'] ?? '');
$original_description = trim($_POST['original_description'] ?? '');

if (!$department_id || $department_id != $_SESSION['department_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}

if (strlen($department_description) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Description cannot exceed 1000 characters']);
    exit;
}

try {
    $conn->autocommit(false);
    
    // Verify user is department head
    $role_check = "SELECT ds.role FROM department_staff ds 
                   WHERE ds.user_id = ? AND ds.department_id = ? AND ds.role = 'head' 
                   AND ds.status = 'active' AND ds.deleted_at IS NULL";
    $stmt = $conn->prepare($role_check);
    $stmt->bind_param("ii", $user_id, $department_id);
    $stmt->execute();
    $role_result = $stmt->get_result();
    
    if ($role_result->num_rows === 0) {
        throw new Exception('Access denied. Only department heads can update settings.');
    }
    $stmt->close();
    
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
        'action' => 'general_settings_update'
    ]);
    $stmt->bind_param("iis", $department_id, $user_id, $log_details);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'General settings updated successfully',
        'data' => [
            'new_description' => $department_description,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(true);
    error_log("General settings update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>