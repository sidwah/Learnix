<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response format

include '../../config.php';

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Function to generate random verification code
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to send verification email
function sendVerificationEmail($email, $verificationCode) {
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
        $mail->Subject = 'Your Learnix Admin Verification Code';
        $mail->isHTML(true);
        $mail->Body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Learnix Verification</title>
            <style>
                body {
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }
                .header {
                    background-color: #4a6cf7;
                    padding: 20px;
                    text-align: center;
                    border-radius: 8px 8px 0 0;
                }
                .header h1 {
                    color: white;
                    margin: 0;
                    font-size: 24px;
                }
                .content {
                    background-color: #ffffff;
                    padding: 30px;
                    border-left: 1px solid #e6e6e6;
                    border-right: 1px solid #e6e6e6;
                }
                .verification-code {
                    background-color: #f7f9fc;
                    border: 1px solid #e6e6e6;
                    border-radius: 6px;
                    font-size: 32px;
                    font-weight: bold;
                    letter-spacing: 5px;
                    color: #333;
                    padding: 15px;
                    margin: 20px 0;
                    text-align: center;
                }
                .info {
                    margin-bottom: 20px;
                    font-size: 16px;
                }
                .expire-warning {
                    color: #f44336;
                    font-size: 14px;
                    font-style: italic;
                }
                .footer {
                    background-color: #f7f9fc;
                    padding: 15px;
                    text-align: center;
                    font-size: 12px;
                    color: #666;
                    border-radius: 0 0 8px 8px;
                    border: 1px solid #e6e6e6;
                    border-top: none;
                }
                .logo {
                    max-width: 120px;
                    margin-bottom: 10px;
                }
                .button {
                    display: inline-block;
                    background-color: #4a6cf7;
                    color: white;
                    text-decoration: none;
                    padding: 12px 25px;
                    border-radius: 4px;
                    font-weight: bold;
                    margin: 20px 0;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>LEARNIX</h1>
                </div>
                <div class='content'>
                    <p class='info'>Hello Admin,</p>
                    <p class='info'>You've requested to sign in to your Learnix Admin account. For security purposes, please verify your identity by entering the code below:</p>
                    
                    <div class='verification-code'>$verificationCode</div>
                    
                    <p class='info'>Please enter this code in the verification form to complete your sign-in process.</p>
                    
                    <p class='expire-warning'>This verification code will expire in 10 minutes.</p>
                    
                    <p class='info'>If you did not request this code, please disregard this email or contact our support team immediately if you believe your account has been compromised.</p>
                </div>
                <div class='footer'>
                    <p>&copy; " . date('Y') . " Learnix Learning Platform. All rights reserved.</p>
                    <p>This is an automated message, please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Set plain text version for non-HTML mail clients
        $mail->AltBody = "Your verification code is: $verificationCode\n\nPlease enter this code in the verification form to complete your sign-in.\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Handle resend code request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend']) && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }
    
    // Check if user exists and is an admin
    $sql = "SELECT user_id FROM users WHERE email = ? AND role = 'admin'";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) === 0) {
        echo json_encode(["status" => "error", "message" => "User not found"]);
        exit;
    }
    
    // Generate a new verification code
    $verificationCode = generateVerificationCode();
    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    
    // Delete any existing verification codes for this user
    $deleteSQL = "DELETE FROM admin_verification_codes WHERE email = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteSQL);
    mysqli_stmt_bind_param($deleteStmt, "s", $email);
    mysqli_stmt_execute($deleteStmt);
    
    // Insert new verification code
    $insertSQL = "INSERT INTO admin_verification_codes (email, code, expiry_time) VALUES (?, ?, ?)";
    $insertStmt = mysqli_prepare($conn, $insertSQL);
    mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
    
    if (mysqli_stmt_execute($insertStmt)) {
        // Send verification email
        if (sendVerificationEmail($email, $verificationCode)) {
            echo json_encode(["status" => "success", "message" => "Verification code sent"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to send verification email"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to generate verification code"]);
    }
    
    exit;
}

// Main sign-in process
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required fields exist in POST request
    if (!isset($_POST['email']) || !isset($_POST['password'])) {
        echo json_encode(["status" => "error", "message" => "Email and password are required"]);
        exit;
    }

    $email = trim($_POST['email']);
    $password = $_POST['password']; // Don't trim passwords

    if (!empty($email) && !empty($password)) {
        $sql = "SELECT user_id, email, password_hash FROM users WHERE email = ? AND role = 'admin'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password_hash'])) { // Ensure column name matches DB
                // Credentials are valid, now generate and send verification code
                $verificationCode = generateVerificationCode();
                $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                // Store the verification code in database
                // First, delete any existing codes for this user
                $deleteSQL = "DELETE FROM admin_verification_codes WHERE email = ?";
                $deleteStmt = mysqli_prepare($conn, $deleteSQL);
                mysqli_stmt_bind_param($deleteStmt, "s", $email);
                mysqli_stmt_execute($deleteStmt);
                
                // Insert new verification code
                $insertSQL = "INSERT INTO admin_verification_codes (email, code, expiry_time) VALUES (?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertSQL);
                mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
                
                if (mysqli_stmt_execute($insertStmt)) {
                    // Store user_id in session but mark as not fully authenticated
                    $_SESSION['temp_user_id'] = $row['user_id'];
                    
                    // Send verification email
                    if (sendVerificationEmail($email, $verificationCode)) {
                        echo json_encode([
                            "status" => "success", 
                            "message" => "Verification code sent", 
                            "requireVerification" => true
                        ]);
                    } else {
                        echo json_encode(["status" => "error", "message" => "Failed to send verification email"]);
                    }
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to generate verification code"]);
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid password"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>