<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting (for debugging)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../../phpmailer/src/Exception.php';
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';
require '../../config.php';

// Start session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $current_password = $_POST['currentPassword'] ?? '';
    $new_password = $_POST['newPassword'] ?? '';

    // Check if inputs are empty
    if (empty($current_password) || empty($new_password)) {
        die("All fields are required.");
    }

    // Define section name and time limit
    $section = 'password_change';
    $time_limit = 300; // 5 minutes in seconds

    // Check if this update is allowed based on time since last password change
    $query = "SELECT last_updated FROM profile_section_updates 
              WHERE user_id = ? AND section = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("is", $user_id, $section);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $section_data = $result->fetch_assoc();
        $time_since_update = time() - strtotime($section_data['last_updated']);
        
        if ($time_since_update < $time_limit) {
            $time_remaining = $time_limit - $time_since_update;
            $minutes_remaining = ceil($time_remaining / 60);
            
            die("You must wait at least 5 minutes before changing your password again. Please try again in {$minutes_remaining} minute(s).");
        }
    }
    $stmt->close();

    // Fetch the current password hash from the database
    $stmt = $conn->prepare("SELECT password_hash, email, first_name, last_name FROM users WHERE user_id = ?");
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($stored_password_hash, $email, $firstName, $lastName);
    $stmt->fetch();
    $stmt->close();

    // Check if user exists
    if (empty($stored_password_hash)) {
        die("No matching user found.");
    }

    // Verify the current password
    if (!password_verify($current_password, $stored_password_hash)) {
        die("Current password is incorrect.");
    }

    // Prevent reusing the previous password
    if (password_verify($new_password, $stored_password_hash)) {
        die("You cannot use your previous password.");
    }

    // Validate password complexity
    if (strlen($new_password) < 8) {
        die("Password must be at least 8 characters long.");
    }

    // Hash the new password
    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);

    // Update the password in the database
    $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
    $update_stmt->bind_param("si", $new_password_hash, $user_id);

    if ($update_stmt->execute()) {
        // Record the password change in the section updates table
        $update_time_query = "
            INSERT INTO profile_section_updates (user_id, section, last_updated) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_updated = NOW()
        ";
        $update_time_stmt = $conn->prepare($update_time_query);
        $update_time_stmt->bind_param("is", $user_id, $section);
        $update_time_stmt->execute();
        $update_time_stmt->close();
    
        // Create in-app notification for password change
        $notification_query = "
            INSERT INTO `user_notifications` (
              `user_id`, 
              `type`, 
              `title`, 
              `message`, 
              `related_id`, 
              `related_type`, 
              `is_read`
            ) VALUES (
              ?, 
              'security', 
              'Password Successfully Changed', 
              'Your account password was changed successfully. If you did not make this change, please contact support immediately.', 
              NULL, 
              NULL, 
              0
            )
        ";
        $notification_stmt = $conn->prepare($notification_query);
        $notification_stmt->bind_param("i", $user_id);
        $notification_stmt->execute();
        $notification_stmt->close();
    
        // Send email notification
        $emailSent = sendPasswordChangeEmail($email, $firstName, $lastName);
        
        if ($emailSent) {
            echo "Password updated successfully. Notification email sent.";
        } else {
            echo "Password updated successfully, but we couldn't send a notification email.";
        }
    } else {
        die("Error updating password: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    $conn->close();
}

/**
 * Send an email notification about the password change.
 */
function sendPasswordChangeEmail($email, $firstName, $lastName) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // App password for Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Using constant instead of 'ssl'
        $mail->Port = 465;

        // Email settings
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Has Been Changed';
        $mail->Body = "
        <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;\">
            <div style=\"text-align: center; margin-bottom: 20px;\">
                <h2 style=\"color: #333;\">Password Changed</h2>
            </div>
            <p>Hi {$firstName} {$lastName},</p>
            <p>We wanted to let you know that your password was successfully changed.</p>
            <p>If you did not make this change, please contact our support team immediately to secure your account.</p>
            <p>Thank you for using Learnix!</p>
            <div style=\"margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;\">
                <p>Best regards,<br>The Learnix Team</p>
            </div>
            <div style=\"margin-top: 20px; font-size: 12px; color: #777;\">
                <p>This is an automated message, please do not reply to this email.</p>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email could not be sent. Error: {$mail->ErrorInfo}");
        return false;
    }
}