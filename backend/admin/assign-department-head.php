<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_log('Starting assign-department-head.php');
error_reporting(E_ALL);

// Authentication check
require_once '../config.php';
require_once '../auth/admin/admin-auth-check.php';

// Include PHPMailer for email sending
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../PHPMailer/src/Exception.php';
require_once '../PHPMailer/src/PHPMailer.php';
require_once '../PHPMailer/src/SMTP.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $departmentId = mysqli_real_escape_string($conn, trim($_POST['department_id']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $firstName = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $lastName = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
    
    // Validate input
    if (empty($departmentId) || empty($email) || empty($firstName) || empty($lastName)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department ID, email, first name, and last name are required.'
        ]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if the department exists
        $deptQuery = "SELECT * FROM departments WHERE department_id = ? AND deleted_at IS NULL";
        $deptStmt = $conn->prepare($deptQuery);
        $deptStmt->bind_param("i", $departmentId);
        $deptStmt->execute();
        $deptResult = $deptStmt->get_result();
        
        if ($deptResult->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Department not found.'
            ]);
            exit;
        }
        
        // Get department details for notifications
        $departmentRow = $deptResult->fetch_assoc();
        $departmentName = $departmentRow['name'];
        
        // Check if there's already an active head
        $headQuery = "SELECT * FROM department_staff 
                      WHERE department_id = ? AND role = 'head' AND status = 'active' AND deleted_at IS NULL";
        $headStmt = $conn->prepare($headQuery);
        $headStmt->bind_param("i", $departmentId);
        $headStmt->execute();
        $headResult = $headStmt->get_result();
        
        if ($headResult->num_rows > 0) {
            // Soft delete the current head
            $updateQuery = "UPDATE department_staff SET status = 'inactive', deleted_at = NOW() 
                            WHERE department_id = ? AND role = 'head' AND status = 'active'";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $departmentId);
            $updateStmt->execute();
        }
        
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
                               role = CASE WHEN role != 'department_head' THEN 'department_head' ELSE role END,
                               force_password_reset = 1,
                               password_hash = ?
                               WHERE user_id = ?";
            $updateUserStmt = $conn->prepare($updateUserQuery);
            $updateUserStmt->bind_param("si", $passwordHash, $userId);
            $updateUserStmt->execute();
        } else {
            // Insert new user
            $insertUserQuery = "INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_verified, force_password_reset) 
                                VALUES (?, ?, ?, ?, ?, 'department_head', 1, 1)";
            $username = strtolower($firstName . '.' . $lastName . rand(100, 999));
            $insertUserStmt = $conn->prepare($insertUserQuery);
            $insertUserStmt->bind_param("sssss", $firstName, $lastName, $email, $username, $passwordHash);
            $insertUserStmt->execute();
            $userId = $insertUserStmt->insert_id;
        }
        
        // Create invitation
        $expiryTime = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $tempPasswordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
        
        $inviteQuery = "INSERT INTO department_staff_invitations 
                       (email, temp_password_hash, department_id, role, invited_by, expiry_time) 
                       VALUES (?, ?, ?, 'head', ?, ?)";
        $inviteStmt = $conn->prepare($inviteQuery);
        $currentUserId = $_SESSION['user_id'];
        $inviteStmt->bind_param("ssiis", $email, $tempPasswordHash, $departmentId, $currentUserId, $expiryTime);
        $inviteStmt->execute();
        
        // Create department staff entry with notes
        $staffQuery = "INSERT INTO department_staff (user_id, department_id, role, appointment_date, notes) 
                      VALUES (?, ?, 'head', CURDATE(), ?)";
        $staffStmt = $conn->prepare($staffQuery);
        $staffStmt->bind_param("iis", $userId, $departmentId, $notes);
        $staffStmt->execute();
        
        // Log activity with notes
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'head_assign', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'head_user_id' => $userId,
            'head_email' => $email,
            'notes' => $notes
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Add notification to the user_notifications table
        $notificationTypeId = 4; // department_head_appointed
        $notificationTitle = "Department Head Appointment";
        $notificationMessage = str_replace('{department_name}', $departmentName, "You have been appointed as head of {department_name} department.");
        
        $notificationQuery = "INSERT INTO user_notifications (user_id, type_id, type, title, message, related_id, related_type) 
                             VALUES (?, ?, 'Department', ?, ?, ?, 'department')";
        $notificationStmt = $conn->prepare($notificationQuery);
        $notificationStmt->bind_param("iissi", $userId, $notificationTypeId, $notificationTitle, $notificationMessage, $departmentId);
        $notificationStmt->execute();
        
        // Send email notification
        $emailSent = sendDepartmentHeadEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime, $notes);
        
        // Commit transaction
        $conn->commit();
        
        $emailStatus = $emailSent ? "An email with signin instructions has been sent." : "User was added but there was an issue sending the email.";
        
        echo json_encode([
            'status' => 'success',
            'message' => "Department head invitation sent successfully. $emailStatus",
            'password' => $tempPassword // Temporary - only for development, remove in production
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to assign department head: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}

/**
 * Send email to the new department head
 */
function sendDepartmentHeadEmail($email, $firstName, $lastName, $tempPassword, $departmentName, $expiryTime, $notes = '') {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Your email
        $mail->Password = 'mtltujmsmmlkkxtv'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.edu', 'Learnix Administration');
        $mail->addAddress($email, "$firstName $lastName");

        // Email content
        $mail->isHTML(true);
        $mail->Subject = "Learnix Department Head Appointment";
        
        // Get formatted expiry date
        $expiryDate = date('F j, Y, g:i a', strtotime($expiryTime));
        
        // Create notes section if notes provided
        $notesSection = '';
        if (!empty($notes)) {
            $notesSection = <<<HTML
            <div style="margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
                <p style="margin-top: 0;"><strong>Additional Notes:</strong></p>
                <p style="margin-bottom: 0;">$notes</p>
            </div>
HTML;
        }
        
        $mail->Body = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Head Appointment</title>
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
        
        .email-header img {
            max-width: 150px;
            height: auto;
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
        
        .support-note {
            font-size: 14px;
            color: #666666;
            font-style: italic;
            margin-top: 24px;
        }
        
        .social-icons {
            margin-top: 20px;
        }
        
        .social-icons a {
            display: inline-block;
            margin: 0 10px;
            color: #3a66db;
            text-decoration: none;
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
            <h2>Department Head Appointment</h2>
            
            <p>Dear $firstName $lastName,</p>
            
            <p>Congratulations! You have been appointed as the Head of the <strong>$departmentName</strong> department at Learnix.</p>
            
            <p>As Department Head, you will be responsible for:</p>
            <ul>
                <li>Managing department instructors</li>
                <li>Initiating and approving courses</li>
                <li>Creating department announcements</li>
                <li>Monitoring department performance</li>
            </ul>
            
            $notesSection
            
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
            
            <p>If you did not expect this appointment or believe this email was sent in error, please contact our support team.</p>
            
            <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.edu">support@learnix.edu</a></p>
        </div>
        
        <div class="email-footer">
            <p>&copy; 2025 Learnix. All rights reserved.</p>
            <p>Our address: 123 Education Lane, Learning City, ED 12345</p>
            <div class="social-icons">
                <a href="#">Twitter</a> | 
                <a href="#">Facebook</a> | 
                <a href="#">Instagram</a>
            </div>
        </div>
    </div>
</body>
</html>
HTML;

        // Plain text alternative
        $mail->AltBody = "
Dear $firstName $lastName,

Congratulations! You have been appointed as the Head of the $departmentName department at Learnix.

As Department Head, you will be responsible for:
- Managing department instructors
- Initiating and approving courses
- Creating department announcements
- Monitoring department performance
" . (!empty($notes) ? "\nAdditional Notes:\n$notes\n" : "") . "

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
        // Log the error
        error_log("Failed to send department head email: " . $mail->ErrorInfo);
        return false;
    }
}
?>