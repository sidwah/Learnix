<?php
// backend/department/replace-secretary.php

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
    $firstName = mysqli_real_escape_string($conn, trim($input['first_name'] ?? ''));
    $lastName = mysqli_real_escape_string($conn, trim($input['last_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($input['email'] ?? ''));
    $reason = mysqli_real_escape_string($conn, trim($input['reason'] ?? ''));
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($reason)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required.'
        ]);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address format.'
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
            'message' => 'You are not authorized to replace secretaries.'
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
        
        $currentSecretary = null;
        if ($currentSecResult->num_rows > 0) {
            $currentSecretary = $currentSecResult->fetch_assoc();
            
            // Remove current secretary (soft delete)
            $removeSecQuery = "UPDATE department_staff 
                              SET status = 'inactive', deleted_at = NOW() 
                              WHERE department_id = ? AND role = 'secretary' AND status = 'active'";
            $removeSecStmt = $conn->prepare($removeSecQuery);
            $removeSecStmt->bind_param("i", $departmentId);
            $removeSecStmt->execute();
        }
        
        // Check if new secretary user exists
        $userQuery = "SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        // Generate temporary password for new secretary
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        if ($userResult->num_rows > 0) {
            // User exists, update them
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['user_id'];
            
            $updateUserQuery = "UPDATE users SET 
                               first_name = ?, last_name = ?,
                               role = CASE WHEN role != 'department_secretary' THEN 'department_secretary' ELSE role END,
                               force_password_reset = 1,
                               password_hash = ?
                               WHERE user_id = ?";
            $updateUserStmt = $conn->prepare($updateUserQuery);
            $updateUserStmt->bind_param("sssi", $firstName, $lastName, $passwordHash, $userId);
            $updateUserStmt->execute();
        } else {
            // Create new user
            $insertUserQuery = "INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_verified, force_password_reset) 
                                VALUES (?, ?, ?, ?, ?, 'department_secretary', 1, 1)";
            $username = strtolower($firstName . '.' . $lastName . rand(100, 999));
            $insertUserStmt = $conn->prepare($insertUserQuery);
            $insertUserStmt->bind_param("sssss", $firstName, $lastName, $email, $username, $passwordHash);
            $insertUserStmt->execute();
            $userId = $insertUserStmt->insert_id;
        }
        
        // Get department settings for expiry time
        $settingsQuery = "SELECT invitation_expiry_hours FROM department_settings WHERE department_id = ?";
        $settingsStmt = $conn->prepare($settingsQuery);
        $settingsStmt->bind_param("i", $departmentId);
        $settingsStmt->execute();
        $settingsResult = $settingsStmt->get_result();
        
        $expiryHours = 48; // Default fallback
        if ($settingsResult->num_rows > 0) {
            $settingsRow = $settingsResult->fetch_assoc();
            $expiryHours = $settingsRow['invitation_expiry_hours'] ?? 48;
        }
        
        // Create invitation for new secretary
        $expiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
        $tempPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        $inviteQuery = "INSERT INTO department_staff_invitations 
                       (email, temp_password_hash, department_id, role, invited_by, expiry_time) 
                       VALUES (?, ?, ?, 'secretary', ?, ?)";
        $inviteStmt = $conn->prepare($inviteQuery);
        $inviteStmt->bind_param("ssiis", $email, $tempPasswordHash, $departmentId, $currentUserId, $expiryTime);
        $inviteStmt->execute();
        
        // Create new department staff entry
        $staffQuery = "INSERT INTO department_staff (user_id, department_id, role, appointment_date) 
                      VALUES (?, ?, 'secretary', CURDATE())";
        $staffStmt = $conn->prepare($staffQuery);
        $staffStmt->bind_param("ii", $userId, $departmentId);
        $staffStmt->execute();
        
        // Log replacement activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'secretary_replace', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'old_secretary' => $currentSecretary,
            'new_secretary_email' => $email,
            'new_secretary_name' => $firstName . ' ' . $lastName,
            'reason' => $reason,
            'replacement_time' => date('Y-m-d H:i:s')
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Add notification to new secretary
        $notificationTypeId = 5; // secretary_appointed
        $notificationTitle = "Department Secretary Appointment";
        $notificationMessage = str_replace('{department_name}', $departmentName, "You have been appointed as secretary of {department_name} department.");
        
        $notificationQuery = "INSERT INTO user_notifications (user_id, type_id, type, title, message, related_id, related_type) 
                             VALUES (?, ?, 'Department', ?, ?, ?, 'department')";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationStmt->bind_param("iissi", $userId, $notificationTypeId, $notificationTitle, $notificationMessage, $departmentId);
        $notificationStmt->execute();
        
        // Send email to new secretary
        $emailSent = sendSecretaryInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime);
        
        // Optionally notify old secretary about replacement
        if ($currentSecretary) {
            sendReplacementNotificationEmail($currentSecretary['email'], $currentSecretary['first_name'], $currentSecretary['last_name'], $departmentName, $reason);
        }
        
        // Commit transaction
        $conn->commit();
        
        $emailStatus = $emailSent ? "An email with signin instructions has been sent to the new secretary." : "Replacement completed but there was an issue sending the email.";
        
        echo json_encode([
            'success' => true,
            'message' => "Secretary replaced successfully. $emailStatus"
        ]);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false,
            'message' => 'Failed to replace secretary: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

// Send invitation email to new secretary (reuse from appoint-secretary.php)
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
        $mail->Subject = "Department Secretary Appointment - Learnix";
        
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
            <h2>Department Secretary Appointment</h2>
            <p>Dear $firstName $lastName,</p>
            <p>You have been appointed as Secretary for the <strong>$departmentName</strong> department at Learnix.</p>
            <p>As Department Secretary, your responsibilities will include:</p>
            <ul>
                <li>Processing instructor requests and documentation</li>
                <li>Assisting with course management tasks</li>
                <li>Handling department communications</li>
                <li>Generating departmental reports</li>
            </ul>
            <div class="credentials-container">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">$email</div>
                <div class="credential-label">Temporary Password:</div>
                <div class="credential-value">$tempPassword</div>
            </div>
            <a href="http://localhost:8888/learnix/department-head/signin.php" class="signin-button">Sign in to Learnix</a>
            <div class="expiry-alert">
                <strong>⏱️ Time Sensitive:</strong> These credentials will expire on $expiryDate.
            </div>
        </div>
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->AltBody = "You have been appointed as Secretary for the $departmentName department. Temporary password: $tempPassword. Expires: $expiryDate";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send secretary invitation email: " . $mail->ErrorInfo);
        return false;
    }
}

// Send notification email to replaced secretary
function sendReplacementNotificationEmail($email, $firstName, $lastName, $departmentName, $reason) {
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
        $mail->Subject = "Secretary Position Update - Learnix";
        
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
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>Learnix</h1>
        </div>
        <div class="email-body">
            <h2>Secretary Position Update</h2>
            <p>Dear $firstName $lastName,</p>
            <p>We are writing to inform you that your position as Secretary for the <strong>$departmentName</strong> department has been changed.</p>
            <div class="reason-box">
                <strong>Reason:</strong> $reason
            </div>
            <p>Your access to the department secretary functions has been revoked. If you have any questions about this change, please contact the department head or our support team.</p>
            <p>Thank you for your service to the department.</p>
        </div>
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;

        $mail->AltBody = "Your position as Secretary for the $departmentName department has been changed. Reason: $reason";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send replacement notification email: " . $mail->ErrorInfo);
        return false;
    }
}
?>