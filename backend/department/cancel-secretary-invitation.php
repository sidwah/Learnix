<?php
// backend/department/cancel-secretary-invitation.php

require_once '../config.php';
require_once '../auth/department/department-auth-check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get department head's department
    $currentUserId = $_SESSION['user_id'];
    $deptQuery = "SELECT ds.department_id, d.name as department_name 
                  FROM department_staff ds 
                  JOIN departments d ON ds.department_id = d.department_id 
                  WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL";
    $deptStmt = $conn->prepare($deptQuery);
    $deptStmt->bind_param("i", $currentUserId);
    $deptStmt->execute();
    $deptResult = $deptStmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'You are not authorized to manage secretary invitations.'
        ]);
        exit;
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $departmentId = $deptRow['department_id'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get pending invitation
        $inviteQuery = "SELECT * FROM department_staff_invitations 
                       WHERE department_id = ? AND role = 'secretary' AND is_used = 0 
                       ORDER BY created_at DESC LIMIT 1";
        $inviteStmt = $conn->prepare($inviteQuery);
        $inviteStmt->bind_param("i", $departmentId);
        $inviteStmt->execute();
        $inviteResult = $inviteStmt->get_result();
        
        if ($inviteResult->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No pending secretary invitation found to cancel.'
            ]);
            exit;
        }
        
        $invitation = $inviteResult->fetch_assoc();
        $email = $invitation['email'];
        
        // Mark invitation as used (effectively canceling it)
        $cancelInviteQuery = "UPDATE department_staff_invitations 
                             SET is_used = 1 
                             WHERE id = ?";
        $cancelInviteStmt = $conn->prepare($cancelInviteQuery);
        $cancelInviteStmt->bind_param("i", $invitation['id']);
        $cancelInviteStmt->execute();
        
        // Remove from department_staff if exists (soft delete)
        $removeStaffQuery = "UPDATE department_staff 
                            SET status = 'inactive', deleted_at = NOW() 
                            WHERE department_id = ? AND role = 'secretary' AND status = 'active'";
        $removeStaffStmt = $conn->prepare($removeStaffQuery);
        $removeStaffStmt->bind_param("i", $departmentId);
        $removeStaffStmt->execute();
        
        // Log activity - FIXED: Use shorter action_type
        // Log activity - Use the correct ENUM value
$activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                  VALUES (?, ?, 'secretary_invite_cancel', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'secretary_email' => $email,
            'cancelled_time' => date('Y-m-d H:i:s')
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Secretary invitation canceled successfully.'
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to cancel invitation: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}
?>