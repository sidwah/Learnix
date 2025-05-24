<?php
// backend/department/remove-secretary.php

require_once '../config.php';
require_once '../auth/department/department-auth-check.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitize input
    $reason = mysqli_real_escape_string($conn, trim($input['reason'] ?? ''));
    
    // Validate input
    if (empty($reason)) {
        echo json_encode([
            'success' => false,
            'message' => 'Reason for removal is required.'
        ]);
        exit;
    }
    
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
            'message' => 'You are not authorized to remove secretaries.'
        ]);
        exit;
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $departmentId = $deptRow['department_id'];
    $departmentName = $deptRow['department_name'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Get current secretary details
        $currentSecQuery = "SELECT u.user_id, u.first_name, u.last_name, u.email 
                           FROM department_staff ds 
                           JOIN users u ON ds.user_id = u.user_id 
                           WHERE ds.department_id = ? AND ds.role = 'secretary' AND ds.status = 'active' AND ds.deleted_at IS NULL";
        $currentSecStmt = $conn->prepare($currentSecQuery);
        $currentSecStmt->bind_param("i", $departmentId);
        $currentSecStmt->execute();
        $currentSecResult = $currentSecStmt->get_result();
        
        if ($currentSecResult->num_rows === 0) {
            echo json_encode([
                'success' => false,
                'message' => 'No active secretary found to remove.'
            ]);
            exit;
        }
        
        $secretary = $currentSecResult->fetch_assoc();
        
        // Remove secretary (soft delete)
        $removeSecQuery = "UPDATE department_staff 
                          SET status = 'inactive', deleted_at = NOW() 
                          WHERE department_id = ? AND role = 'secretary' AND status = 'active'";
        $removeSecStmt = $conn->prepare($removeSecQuery);
        $removeSecStmt->bind_param("i", $departmentId);
        $removeSecStmt->execute();
        
        // Cancel any pending invitations
        $cancelInviteQuery = "UPDATE department_staff_invitations 
                             SET is_used = 1 
                             WHERE department_id = ? AND role = 'secretary' AND is_used = 0";
        $cancelInviteStmt = $conn->prepare($cancelInviteQuery);
        $cancelInviteStmt->bind_param("i", $departmentId);
        $cancelInviteStmt->execute();
        
        // Log removal activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'secretary_remove', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'secretary_user_id' => $secretary['user_id'],
            'secretary_email' => $secretary['email'],
            'secretary_name' => $secretary['first_name'] . ' ' . $secretary['last_name'],
            'reason' => $reason,
            'removal_time' => date('Y-m-d H:i:s')
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Send notification email to removed secretary
        $emailSent = sendRemovalNotificationEmail($secretary['email'], $secretary['first_name'], $secretary['last_name'], $departmentName, $reason);
        
        // Commit transaction
        $conn->commit();
        
        $emailStatus = $emailSent ? "A notification email has been sent to the removed secretary." : "Secretary removed but there was an issue sending the notification email.";
        
        echo json_encode([
            'success' => true,
            'message' => "Secretary removed successfully. $emailStatus"
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to remove secretary: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

// Send notification email to removed secretary
function sendRemovalNotificationEmail($email, $firstName, $lastName, $departmentName, $reason) {
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
        $mail->Subject = "Secretary Position Removal - Learnix";
        
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
       body { font-family: 'Poppins', Arial, sans-serif; line-height: 1.6; color: #333333; margin: 0; padding: 0; background-color: #f9f9f9; }
       .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); }
       .email-header { background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%); padding: 30px; text-align: center; }
       .email-body { padding: 30px; }
       .email-footer { background-color: #f5f5f5; padding: 20px; text-align: center; font-size: 12px; color: #666666; }
       h1 { color: #ffffff; margin: 0; font-size: 24px; font-weight: 600; }
       h2 { color: #dc3545; margin-top: 0; font-size: 20px; font-weight: 500; }
       p { margin: 16px 0; font-size: 15px; }
       .reason-box { background-color: #f8f9fa; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0; }
       .support-note { font-size: 14px; color: #666666; font-style: italic; margin-top: 24px; }
   </style>
</head>
<body>
   <div class="email-container">
       <div class="email-header">
           <h1>Learnix</h1>
       </div>
       <div class="email-body">
           <h2>Secretary Position Removal</h2>
           <p>Dear $firstName $lastName,</p>
           <p>We are writing to inform you that your position as Secretary for the <strong>$departmentName</strong> department has been removed.</p>
           <div class="reason-box">
               <strong>Reason for Removal:</strong><br>
               $reason
           </div>
           <p>Your access to the department secretary functions has been revoked effective immediately. You will no longer be able to access the secretary dashboard or perform secretary-related tasks.</p>
           <p>If you believe this removal was made in error or if you have any questions about this decision, please contact the department head or our support team.</p>
           <p>Thank you for your service to the $departmentName department.</p>
           <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.edu">support@learnix.edu</a></p>
       </div>
       <div class="email-footer">
           <p>&copy; 2025 Learnix. All rights reserved.</p>
           <p>Our address: 123 Education Lane, Learning City, ED 12345</p>
       </div>
   </div>
</body>
</html>
HTML;

       $mail->AltBody = "Your position as Secretary for the $departmentName department has been removed. Reason: $reason. If you have questions, please contact support@learnix.edu";

       $mail->send();
       return true;
   } catch (Exception $e) {
       error_log("Failed to send removal notification email: " . $mail->ErrorInfo);
       return false;
   }
}
?>