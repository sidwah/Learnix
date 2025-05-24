<?php
// backend/department/appoint-secretary.php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Authentication check
require_once '../config.php';
require_once '../auth/department/department-auth-check.php';

// Include PHPMailer for email sending
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Sanitize input
    $firstName = mysqli_real_escape_string($conn, trim($input['first_name'] ?? ''));
    $lastName = mysqli_real_escape_string($conn, trim($input['last_name'] ?? ''));
    $email = mysqli_real_escape_string($conn, trim($input['email'] ?? ''));
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email)) {
        echo json_encode([
            'success' => false,
            'message' => 'First name, last name, and email are required.'
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
            'message' => 'You are not authorized to appoint secretaries.'
        ]);
        exit;
    }
    
    $deptRow = $deptResult->fetch_assoc();
    $departmentId = $deptRow['department_id'];
    $departmentName = $deptRow['department_name'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if there's already an active secretary
        $secQuery = "SELECT * FROM department_staff 
                     WHERE department_id = ? AND role = 'secretary' AND status = 'active' AND deleted_at IS NULL";
        $secStmt = $conn->prepare($secQuery);
        $secStmt->bind_param("i", $departmentId);
        $secStmt->execute();
        $secResult = $secStmt->get_result();
        
        if ($secResult->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'A secretary is already appointed for this department.'
            ]);
            exit;
        }
        
        // Check for existing pending invitation
        $inviteCheckQuery = "SELECT * FROM department_staff_invitations 
                            WHERE department_id = ? AND role = 'secretary' AND is_used = 0 AND expiry_time > NOW()";
        $inviteCheckStmt = $conn->prepare($inviteCheckQuery);
        $inviteCheckStmt->bind_param("i", $departmentId);
        $inviteCheckStmt->execute();
        $inviteCheckResult = $inviteCheckStmt->get_result();
        
        if ($inviteCheckResult->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'A secretary invitation is already pending for this department.'
            ]);
            exit;
        }
        
        // Clean up expired invitations
        $cleanupQuery = "UPDATE department_staff_invitations SET is_used = 1 
                        WHERE department_id = ? AND role = 'secretary' AND expiry_time <= NOW()";
        $cleanupStmt = $conn->prepare($cleanupQuery);
        $cleanupStmt->bind_param("i", $departmentId);
        $cleanupStmt->execute();
        
        // Check if the user exists
        $userQuery = "SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        // Generate temporary password
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        if ($userResult->num_rows > 0) {
            // User exists, get user_id
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['user_id'];
            
            // Update existing user
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
            // Insert new user
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
        
        $expiryHours = 48; // Default
        if ($settingsResult->num_rows > 0) {
            $settingsRow = $settingsResult->fetch_assoc();
            $expiryHours = $settingsRow['invitation_expiry_hours'] ?? 48;
        }
        
        // Create invitation
        $expiryTime = date('Y-m-d H:i:s', strtotime("+{$expiryHours} hours"));
        $tempPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        $inviteQuery = "INSERT INTO department_staff_invitations 
                       (email, temp_password_hash, department_id, role, invited_by, expiry_time) 
                       VALUES (?, ?, ?, 'secretary', ?, ?)";
        $inviteStmt = $conn->prepare($inviteQuery);
        $inviteStmt->bind_param("ssiis", $email, $tempPasswordHash, $departmentId, $currentUserId, $expiryTime);
        $inviteStmt->execute();
        
        // Create department staff entry
        $staffQuery = "INSERT INTO department_staff (user_id, department_id, role, appointment_date) 
                      VALUES (?, ?, 'secretary', CURDATE())";
        $staffStmt = $conn->prepare($staffQuery);
        $staffStmt->bind_param("ii", $userId, $departmentId);
        $staffStmt->execute();
        
        // Log activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'secretary_invite', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'secretary_user_id' => $userId,
            'secretary_email' => $email,
            'secretary_name' => $firstName . ' ' . $lastName
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Add notification to the user_notifications table
        $notificationTypeId = 5; // secretary_appointed (you may need to add this to notification_types)
        $notificationTitle = "Department Secretary Appointment";
        $notificationMessage = str_replace('{department_name}', $departmentName, "You have been appointed as secretary of {department_name} department.");
        
        $notificationQuery = "INSERT INTO user_notifications (user_id, type_id, type, title, message, related_id, related_type) 
                             VALUES (?, ?, 'Department', ?, ?, ?, 'department')";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationStmt->bind_param("iissi", $userId, $notificationTypeId, $notificationTitle, $notificationMessage, $departmentId);
        $notificationStmt->execute();
        
        // Send email notification
        $emailSent = sendSecretaryInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime);
        
        // Commit transaction
        $conn->commit();
        
        $emailStatus = $emailSent ? "An email with signin instructions has been sent." : "Invitation created but there was an issue sending the email.";
        
        echo json_encode([
            'success' => true,
            'message' => "Secretary invitation sent successfully. $emailStatus"
        ]);
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'success' => false,
            'message' => 'Failed to send secretary invitation: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);
}

/**
 * Send email to the new secretary
 */
function sendSecretaryInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.edu', 'Learnix Administration');
        $mail->addAddress($email, "$firstName $lastName");

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Department Secretary Appointment - Learnix";
        
        // Get formatted expiry date
        $expiryDate = date('F j, Y, g:i a', strtotime($expiryTime));
        
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Secretary Appointment</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Poppins', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .email-header {
            background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
            padding: 30px;
            text-align: center;
        }
        
        .email-body {
            padding: 30px;
        }
        
        .email-footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666666;
        }
        
        h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        
        h2 {
            color: #3a66db;
            margin-top: 0;
            font-size: 20px;
            font-weight: 500;
        }
        
        p {
            margin: 16px 0;
            font-size: 15px;
        }
        
        .credentials-container {
            background-color: #f5f7fa;
            border-radius: 6px;
            padding: 20px;
            margin: 24px 0;
            border: 1px dashed #d1d9e6;
        }
        
        .credential-label {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .credential-value {
            font-family: monospace;
            font-size: 16px;
            color: #3a66db;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .expiry-alert {
            background-color: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin: 24px 0;
            font-size: 14px;
            color: #856404;
        }
        
        .signin-button {
            display: inline-block;
            background-color: #3a66db;
            color: white !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 4px;
            font-weight: 500;
            margin: 20px 0;
        }
        
        @media screen and (max-width: 600px) {
            .email-container {
                width: 100%;
                border-radius: 0;
            }
            
            .email-header, .email-body, .email-footer {
                padding: 20px;
            }
        }
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
            
            <p>To get started, please use the following temporary credentials to sign in:</p>
            
            <div class="credentials-container">
                <div class="credential-label">Email Address:</div>
                <div class="credential-value">$email</div>
                
                <div class="credential-label">Temporary Password:</div>
                <div class="credential-value">$tempPassword</div>
            </div>
            
            <a href="http://localhost:8888/learnix/department-head/signin.php" class="signin-button">Sign in to Learnix</a>
            
            <div class="expiry-alert">
                <strong>⏱️ Time Sensitive:</strong> These credentials will expire on $expiryDate. Please sign in before then to set your permanent password.
            </div>
            
            <p>Upon your first sign in, you will be prompted to change your password and complete your profile information.</p>
            
            <p>If you did not expect this appointment or believe this email was sent in error, please contact the department head or our support team.</p>
            
            <p style="font-size: 14px; color: #666666; font-style: italic; margin-top: 24px;">For any questions, please contact our support team at <a href="mailto:support@learnix.edu">support@learnix.edu</a></p>
        </div>
        
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
            <p>Our address: 123 Education Lane, Learning City, ED 12345</p>
        </div>
    </div>
</body>
</html>
HTML;

        // Plain text alternative
        $mail->AltBody = "
Dear $firstName $lastName,

You have been appointed as Secretary for the $departmentName department at Learnix.

As Department Secretary, your responsibilities will include:
- Processing instructor requests and documentation
- Assisting with course management tasks
- Handling department communications
- Generating departmental reports

To get started, please use the following temporary credentials to sign in:

Email Address: $email
Temporary Password: $tempPassword

IMPORTANT: These credentials will expire on $expiryDate. Please sign in before then.

Upon your first sign in, you will be prompted to change your password and complete your profile information.

For any questions, please contact our support team at support@learnix.edu

Regards,
Learnix Administration
";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Failed to send secretary invitation email: " . $mail->ErrorInfo);
        return false;
    }
}
?>