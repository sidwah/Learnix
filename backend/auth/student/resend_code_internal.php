<?php

    
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';
// resend_code_internal.php - For internal use by other scripts
function sendVerificationCodeToEmail($email, $conn) {
    
    
    // Check if the email exists and is unverified
    $stmt = $conn->prepare("SELECT user_id, role, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [
            'status' => 'error',
            'message' => 'No account found with this email.'
        ];
    }
    
    $user = $result->fetch_assoc();
    $userId = $user['user_id'];
    
    // If user is already verified, return an appropriate message
    if ($user['is_verified'] == 1) {
        return [
            'status' => 'error',
            'message' => 'Your account is already verified. Please sign in.'
        ];
    }
    
    // Check rate limiting (prevent abuse)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM user_verification WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($recentAttempts);
    $stmt->fetch();
    $stmt->close();
    
    if ($recentAttempts >= 3) {
        return [
            'status' => 'error',
            'message' => 'Too many verification attempts. Please try again in 15 minutes.'
        ];
    }
    
    // Generate new verification code
    $verificationCode = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    // Delete any existing verification codes for this user
    $stmt = $conn->prepare("DELETE FROM user_verification WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    
    // Insert new verification code
    $stmt = $conn->prepare("INSERT INTO user_verification (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $verificationCode, $expiresAt);
    
    if (!$stmt->execute()) {
        return [
            'status' => 'error',
            'message' => 'Failed to generate verification code: ' . $stmt->error
        ];
    }
    $stmt->close();

    // Send the email with the code
    return sendEmail($email, $verificationCode);
}

function sendEmail($email, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Your Gmail
        $mail->Password = 'mtltujmsmmlkkxtv'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        
        // Email Details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Your Learnix Verification Code';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #333;">Welcome to Learnix!</h2>
                </div>
                <p>Thank you for creating an account. To verify your email address, use the verification code below:</p>
                <div style="background-color: #f5f5f5; padding: 15px; text-align: center; font-size: 24px; letter-spacing: 5px; margin: 20px 0; border-radius: 5px;">
                    <strong>'.$verificationCode.'</strong>
                </div>
                <p>This code will expire in 15 minutes. If you did not create an account with Learnix, please ignore this email.</p>
                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #777;">
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        ';
        
        $mail->send();
        return [
            'status' => 'success',
            'message' => 'verification_resent'
        ];
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return [
            'status' => 'error',
            'message' => 'Failed to send verification email: ' . $e->getMessage()
        ];
    }
}
?>