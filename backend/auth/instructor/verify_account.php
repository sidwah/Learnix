<?php
// verify_account.php
require_once '../../config.php'; // Database connection
header('Content-Type: application/json'); // ✅ Ensure JSON response

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $code = trim($data['code'] ?? '');
    
    if (empty($code)) {
        echo json_encode(["status" => "error", "message" => "Verification code is required."]);
        exit;
    }
    
    // Create overlay for processing
    createOverlay("Verifying your account...");
    
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        removeOverlay();
        echo json_encode(["status" => "error", "message" => "Server error. Please try again later."]);
        exit;
    }
    
    // Find user by verification code
    $stmt = $conn->prepare("SELECT user_verification.user_id, users.email, users.first_name FROM user_verification 
                           JOIN users ON user_verification.user_id = users.user_id 
                           WHERE token = ? AND expires_at > NOW() AND verified = 0");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $stmt->bind_result($userId, $userEmail, $firstName);
    
    if ($stmt->fetch()) {
        $stmt->close();
        
        // Mark user as verified
        $stmt = $conn->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Mark token as used
        $stmt = $conn->prepare("UPDATE user_verification SET verified = 1 WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->close();
        
        // Send welcome email
        sendWelcomeEmail($userEmail, $firstName);
        
        removeOverlay();
        echo json_encode(["status" => "success", "message" => "Account verified successfully."]);
    } else {
        $stmt->close();
        removeOverlay();
        echo json_encode(["status" => "error", "message" => "Invalid or expired verification code."]);
    }
    
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}

// Create and apply page overlay for loading effect with optional message
function createOverlay($message = null) {
    // This is just a placeholder since the actual overlay is created on the client side
    // The JavaScript will handle the visual overlay
}

// Remove overlay
function removeOverlay() {
    // This is just a placeholder since the actual overlay is removed on the client side
    // The JavaScript will handle the visual overlay removal
}

// Function to send welcome email after successful verification
function sendWelcomeEmail($email, $firstName) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method instead
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Learnix - Your Account is Verified!';
        $mail->Body = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome to Learnix</title>
        <style>
            @import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap\');
            
            body {
                font-family: \'Poppins\', Arial, sans-serif;
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
            
            .button-container {
                text-align: center;
                margin: 30px 0;
            }
            
            .button {
                display: inline-block;
                background-color: #3a66db;
                color: #ffffff;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 4px;
                font-weight: 500;
                font-size: 16px;
            }
            
            .next-steps {
                background-color: #f5f7fa;
                border-radius: 6px;
                padding: 20px;
                margin: 24px 0;
            }
            
            .next-steps h3 {
                color: #3a66db;
                margin-top: 0;
                font-size: 18px;
            }
            
            .next-steps ul {
                margin: 0;
                padding-left: 20px;
            }
            
            .next-steps li {
                margin-bottom: 10px;
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
                <h2>Welcome to Learnix, ' . $firstName . '!</h2>
                
                <p>Thank you for joining Learnix as an instructor. Your account has been successfully verified and you can now start creating and publishing courses.</p>
                
                <div class="button-container">
                    <a href="https://learnix.com/instructor/dashboard" class="button">Go to Your Dashboard</a>
                </div>
                
                <div class="next-steps">
                    <h3>Here are your next steps:</h3>
                    <ul>
                        <li>Complete your instructor profile with your bio and credentials</li>
                        <li>Create your first course by clicking on "New Course"</li>
                        <li>Upload your course content, including videos, documents, and quizzes</li>
                        <li>Publish your course and start sharing your knowledge!</li>
                    </ul>
                </div>
                
                <p>We\'re excited to have you as part of our growing community of instructors. If you have any questions or need assistance, our support team is here to help you succeed.</p>
                
                <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a></p>
            </div>
            
            <div class="email-footer">
                <p>&copy; ' . date('Y') . ' Learnix. All rights reserved.</p>
                <p>Our address: 123 Education Lane, Learning City, ED 12345</p>
                <div class="social-icons">
                    <a href="#">Twitter</a> | 
                    <a href="#">Facebook</a> | 
                    <a href="#">Instagram</a>
                </div>
            </div>
        </div>
    </body>
    </html>';

        // Alternative plain text body for email clients that don't support HTML
        $mail->AltBody = "Welcome to Learnix, $firstName! Your account has been successfully verified. You can now log in and start creating your courses. If you have any questions, please contact our support team at support@learnix.com.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>