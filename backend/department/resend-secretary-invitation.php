<?php
// backend/department/resend-secretary-invitation.php

require_once '../config.php';
require_once '../auth/department/department-auth-check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

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
    $departmentName = $deptRow['department_name'];
    
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
                'message' => 'No pending secretary invitation found.'
            ]);
            exit;
        }
        
        $invitation = $inviteResult->fetch_assoc();
        $email = $invitation['email'];
        
        // Generate new temporary password
        $tempPassword = bin2hex(random_bytes(5));
        $tempPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        // Get department settings for expiry time
        $settingsQuery = "SELECT invitation_expiry_hours FROM department_settings WHERE department_id = ?";
        $settingsStmt = $conn->prepare($settingsQuery);
        $settingsStmt->bind_param("i", $departmentId);
        $settingsStmt->execute();
        $settingsResult = $settingsStmt->get_result();
        
        $expiryHours = 48; // Default
        if ($settingsResult->num_rows > 0) {
            $settingsRow = $settingsResult->fetch_assoc();
            $expiryHours = $settingsRow['invitation_expiry_hours'] ?? 48;
        }
        
        $expiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
        
        // Update invitation with new password and expiry
        $updateInviteQuery = "UPDATE department_staff_invitations 
                             SET temp_password_hash = ?, expiry_time = ?, created_at = NOW() 
                             WHERE id = ?";
        $updateInviteStmt = $conn->prepare($updateInviteQuery);
        $updateInviteStmt->bind_param("ssi", $tempPasswordHash, $expiryTime, $invitation['id']);
        $updateInviteStmt->execute();
        
        // Update user password hash
        $updateUserQuery = "UPDATE users SET password_hash = ?, force_password_reset = 1 WHERE email = ?";
        $updateUserStmt = $conn->prepare($updateUserQuery);
        $newPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        $updateUserStmt->bind_param("ss", $newPasswordHash, $email);
        $updateUserStmt->execute();
        
        // Get user name for email
        $userQuery = "SELECT first_name, last_name FROM users WHERE email = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        $user = $userResult->fetch_assoc();
        
        // Log activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'secretary_invite_resend', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'secretary_email' => $email,
            'resend_time' => date('Y-m-d H:i:s')
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Send email notification
        $emailSent = sendSecretaryInvitationEmail($email, $user['first_name'], $user['last_name'], $tempPassword, $departmentName, $expiryTime);
        
        // Commit transaction
        $conn->commit();
        
        $emailStatus = $emailSent ? "A new email with updated signin instructions has been sent." : "Invitation updated but there was an issue sending the email.";
        
        echo json_encode([
            'success' => true,
            'message' => "Secretary invitation resent successfully. $emailStatus"
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to resend invitation: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

// Reuse the same email function from appoint-secretary.php
function sendSecretaryInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('no-reply@learnix.edu', 'Learnix Administration');
        $mail->addAddress($email, "$firstName $lastName");

        $mail->isHTML(true);
        $mail->Subject = "Department Secretary Invitation (Resent) - Learnix";
        
        $expiryDate = date('F j, Y, g:i a', strtotime($expiryTime));
        
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f9f9f9; }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
        .email-header { background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%); padding: 30px; text-align: center; }
        .email-body { padding: 30px; }
        .email-footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666666; }
        h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; }
        h2 { color: #3a66db; margin-top: 0; font-size: 20px; font-weight: 500; }
        p { margin: 16px 0; font-size: 15px; }
        .credentials-container { background-color: #f5f7fa; border-radius: 6px; padding: 20px; margin: 24px 0; border: 1px dashed #d1d9e6; }
        .credential-label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .credential-value { font-family: monospace; font-size: 16px; color: #3a66db; font-weight: 600; margin-bottom: 15px; }
        .expiry-alert { background-color: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 15px; margin: 24px 0; font-size: 14px; color: #856404; }
        .signin-button { display: inline-block; background-color: #3a66db; color: white !important; text-decoration: none; padding: 12px 24px; border-radius: 4px; font-weight: 500; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Learnix</h1>
        </div>
        <div class="email-body">
            <h2>Updated Secretary Invitation</h2>
            <p>Dear $firstName $lastName,</p>
            <p>Your secretary invitation for the <strong>$departmentName</strong> department has been resent with updated credentials.</p>
            <div class="credentials-container">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">$email</div>
                <div class="credential-label">New Temporary Password:</div>
                <div class="credential-value">$tempPassword</div>
            </div>
            <a href="http://localhost:8888/learnix/department-head/signin.php" class="signin-button">Sign in to Learnix</a>
            <div class="expiry-alert">
                <strong>⏱️ Time Sensitive:</strong> These new credentials will expire on $expiryDate.
            </div>
            <p>Any previous credentials have been invalidated. Please use only the credentials provided in this email.</p>
        </div>
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->AltBody = "Your secretary invitation for the $departmentName department has been resent. New temporary password: $tempPassword. Expires: $expiryDate";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send secretary invitation email: " . $mail->ErrorInfo);
        return false;
    }
}
?>