<?php
session_start();
header("Content-Type: application/json"); // Ensure JSON response format

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../phpmailer/src/Exception.php';
require '../../phpmailer/src/PHPMailer.php';
require '../../phpmailer/src/SMTP.php';
include '../../config.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required fields exist in POST request
    if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['confirm_password'])) {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
        exit;
    }
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Check if the reset process was previously verified
    if (!isset($_SESSION['reset_verified']) || $_SESSION['reset_verified'] !== true ||
        !isset($_SESSION['reset_email']) || $_SESSION['reset_email'] !== $email ||
        !isset($_SESSION['reset_time']) || (time() - $_SESSION['reset_time']) > 600) { // 10 minute timeout
        // Session expired or invalid
        echo json_encode(["status" => "error", "message" => "Reset session expired or invalid. Please try again."]);
        exit;
    }
    
    // Validate passwords
    if (strlen($password) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long"]);
        exit;
    }
    
    if ($password !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }
    
    // Get user details for email
    $getUserSql = "SELECT first_name, last_name FROM users WHERE email = ? AND role = 'admin'";
    $getUserStmt = mysqli_prepare($conn, $getUserSql);
    mysqli_stmt_bind_param($getUserStmt, "s", $email);
    mysqli_stmt_execute($getUserStmt);
    $result = mysqli_stmt_get_result($getUserStmt);
    
    if (mysqli_num_rows($result) == 0) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }
    
    $userDetails = mysqli_fetch_assoc($result);
    $firstName = $userDetails['first_name'];
    $lastName = $userDetails['last_name'];
    
    // Hash the new password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    // Update the password in the database
    $sql = "UPDATE users SET password_hash = ? WHERE email = ? AND role = 'admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ss", $passwordHash, $email);
    
    if (mysqli_stmt_execute($stmt)) {
        // Password updated successfully
        
        // Clear any reset codes
        $clearCodesSql = "DELETE FROM admin_reset_codes WHERE email = ?";
        $clearCodesStmt = mysqli_prepare($conn, $clearCodesSql);
        mysqli_stmt_bind_param($clearCodesStmt, "s", $email);
        mysqli_stmt_execute($clearCodesStmt);
        
        // Clear any reset attempts
        $clearAttemptsSql = "DELETE FROM admin_reset_attempts WHERE email = ?";
        $clearAttemptsStmt = mysqli_prepare($conn, $clearAttemptsSql);
        mysqli_stmt_bind_param($clearAttemptsStmt, "s", $email);
        mysqli_stmt_execute($clearAttemptsStmt);
        
        // Clear any reset lockouts
        $clearLockoutsSql = "DELETE FROM admin_reset_lockouts WHERE email = ?";
        $clearLockoutsStmt = mysqli_prepare($conn, $clearLockoutsSql);
        mysqli_stmt_bind_param($clearLockoutsStmt, "s", $email);
        mysqli_stmt_execute($clearLockoutsStmt);
        
        // Record this reset in the profile_section_updates table
        $section = 'password_reset';
        $resetLogSql = "
            INSERT INTO profile_section_updates (user_id, section, last_updated) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_updated = NOW()
        ";
        $resetLogStmt = mysqli_prepare($conn, $resetLogSql);
        
        // Get user_id if not already in session
        $user_id = $_SESSION['reset_user_id'] ?? null;
        if ($user_id === null) {
            $getUserIdSql = "SELECT user_id FROM users WHERE email = ? AND role = 'admin'";
            $getUserIdStmt = mysqli_prepare($conn, $getUserIdSql);
            mysqli_stmt_bind_param($getUserIdStmt, "s", $email);
            mysqli_stmt_execute($getUserIdStmt);
            $userIdResult = mysqli_stmt_get_result($getUserIdStmt);
            $userIdRow = mysqli_fetch_assoc($userIdResult);
            $user_id = $userIdRow['user_id'];
            mysqli_stmt_close($getUserIdStmt);
        }
        
        mysqli_stmt_bind_param($resetLogStmt, "is", $user_id, $section);
        mysqli_stmt_execute($resetLogStmt);
        mysqli_stmt_close($resetLogStmt);
        
        // Send email notification
        $emailSent = sendPasswordResetEmail($email, $firstName, $lastName);
        
        // Clear the reset session variables
        unset($_SESSION['reset_verified']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_time']);
        
        if ($emailSent) {
            echo json_encode(["status" => "success", "message" => "Password reset successful. A confirmation email has been sent."]);
        } else {
            echo json_encode(["status" => "success", "message" => "Password reset successful, but we couldn't send a confirmation email."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update password"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);

/**
 * Send an email notification about the password reset.
 */
function sendPasswordResetEmail($email, $firstName, $lastName) {
    $mail = new PHPMailer(true);
    
    try {
        // SMTP Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // App password for Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Email settings
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Password Has Been Reset';
        $mail->Body = "
        <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;\">
            <div style=\"text-align: center; margin-bottom: 20px;\">
                <h2 style=\"color: #333;\">Password Reset Confirmation</h2>
            </div>
            <p>Hi {$firstName} {$lastName},</p>
            <p>This email confirms that your password has been successfully reset.</p>
            <p>If you did not initiate this password reset, please contact our support team immediately to secure your account.</p>
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
?>