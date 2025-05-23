<?php
// backend/department/update-notification-settings.php
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
$email_notifications_enabled = isset($_POST['email_notifications_enabled']) ? 1 : 0;
$original_email_notifications = filter_input(INPUT_POST, 'original_email_notifications', FILTER_VALIDATE_INT);

if (!$department_id || $department_id != $_SESSION['department_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
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
    
    // Check if settings actually changed
    if ($email_notifications_enabled === $original_email_notifications) {
        echo json_encode(['success' => true, 'message' => 'No changes detected']);
        exit;
    }
    
    // Check if settings record exists
    $check_query = "SELECT setting_id FROM department_settings WHERE department_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $check_result = $stmt->get_result();
    $setting_exists = $check_result->num_rows > 0;
    $stmt->close();
    
    if ($setting_exists) {
        // Update existing settings
        $update_query = "UPDATE department_settings SET 
                        email_notifications_enabled = ?,
                        updated_at = CURRENT_TIMESTAMP,
                        updated_by = ?
                        WHERE department_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("iii", $email_notifications_enabled, $user_id, $department_id);
    } else {
        // Create new settings record
        $insert_query = "INSERT INTO department_settings (department_id, email_notifications_enabled, created_by) 
                        VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iii", $department_id, $email_notifications_enabled, $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update notification settings');
    }
    $stmt->close();
    
    // Log the activity
    $log_query = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details, performed_at) 
                  VALUES (?, ?, 'update', ?, NOW())";
    $stmt = $conn->prepare($log_query);
    $log_details = json_encode([
        'setting_type' => 'notification_settings',
        'changes' => [
            'email_notifications_enabled' => ['old' => $original_email_notifications, 'new' => $email_notifications_enabled]
        ],
        'action' => 'notification_settings_update'
    ]);
    $stmt->bind_param("iis", $department_id, $user_id, $log_details);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Notification settings updated successfully',
        'data' => [
            'email_notifications_enabled' => $email_notifications_enabled,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(true);
    error_log("Notification settings update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>