<?php
// backend/department/update-invitation-settings.php
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
$invitation_expiry_hours = filter_input(INPUT_POST, 'invitation_expiry_hours', FILTER_VALIDATE_INT);
$auto_approve_instructors = isset($_POST['auto_approve_instructors']) ? 1 : 0;
$original_expiry_hours = filter_input(INPUT_POST, 'original_expiry_hours', FILTER_VALIDATE_INT);
$original_auto_approve = filter_input(INPUT_POST, 'original_auto_approve', FILTER_VALIDATE_INT);

if (!$department_id || $department_id != $_SESSION['department_id']) {
    echo json_encode(['success' => false, 'message' => 'Invalid department']);
    exit;
}

// Validate expiry hours
$valid_expiry_hours = [24, 48, 72, 168, 336];
if (!in_array($invitation_expiry_hours, $valid_expiry_hours)) {
    echo json_encode(['success' => false, 'message' => 'Invalid expiry time selected']);
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
    if ($invitation_expiry_hours === $original_expiry_hours && $auto_approve_instructors === $original_auto_approve) {
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
                        invitation_expiry_hours = ?, 
                        auto_approve_instructors = ?,
                        updated_at = CURRENT_TIMESTAMP,
                        updated_by = ?
                        WHERE department_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("iiii", $invitation_expiry_hours, $auto_approve_instructors, $user_id, $department_id);
    } else {
        // Create new settings record
        $insert_query = "INSERT INTO department_settings (department_id, invitation_expiry_hours, auto_approve_instructors, created_by) 
                        VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iiii", $department_id, $invitation_expiry_hours, $auto_approve_instructors, $user_id);
    }
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update invitation settings');
    }
    $stmt->close();
    
    // Log the activity
    $log_query = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details, performed_at) 
                  VALUES (?, ?, 'update', ?, NOW())";
    $stmt = $conn->prepare($log_query);
    $log_details = json_encode([
        'setting_type' => 'invitation_settings',
        'changes' => [
            'invitation_expiry_hours' => ['old' => $original_expiry_hours, 'new' => $invitation_expiry_hours],
            'auto_approve_instructors' => ['old' => $original_auto_approve, 'new' => $auto_approve_instructors]
        ],
        'action' => 'invitation_settings_update'
    ]);
    $stmt->bind_param("iis", $department_id, $user_id, $log_details);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true,
        'message' => 'Invitation settings updated successfully',
        'data' => [
            'invitation_expiry_hours' => $invitation_expiry_hours,
            'auto_approve_instructors' => $auto_approve_instructors,
            'updated_at' => date('Y-m-d H:i:s')
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->autocommit(true);
    error_log("Invitation settings update error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>