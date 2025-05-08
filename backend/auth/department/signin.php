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

// Configuration for login attempts
$maxAttempts = 5; // Maximum attempts allowed
$lockoutDuration = 15; // Minutes

// Function to generate random verification code
function generateVerificationCode() {
    return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to send verification email
function sendVerificationEmail($email, $firstName, $verificationCode, $role) {
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
        $mail->Subject = 'Your Learnix Department Verification Code';
        
        // Role-specific greeting
        $roleTitle = ($role == 'department_head') ? 'Department Head' : 'Department Secretary';
        
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
                    <p class='info'>Hello $firstName,</p>
                    <p class='info'>You've requested to sign in to your Learnix $roleTitle account. For security purposes, please verify your identity by entering the code below:</p>
                    
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

// Make sure necessary tables exist
try {
    // Create lockout tracking table if it doesn't exist
    $createLockoutTable = $conn->prepare("CREATE TABLE IF NOT EXISTS department_lockouts (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        lockout_until DATETIME NOT NULL,
        attempts INT NOT NULL DEFAULT 0,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY email (email),
        KEY lockout_until (lockout_until)
    )");
    $createLockoutTable->execute();
    
    // Create verification codes table if it doesn't exist
    $createVerificationTable = $conn->prepare("CREATE TABLE IF NOT EXISTS department_verification_codes (
        id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) NOT NULL,
        code VARCHAR(6) NOT NULL,
        expiry_time DATETIME NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        KEY email (email),
        KEY code (code),
        KEY expiry_time (expiry_time)
    )");
    $createVerificationTable->execute();
    
    // Add MFA preference column to department_staff table if it doesn't exist
    $checkMfaColumn = $conn->prepare("SHOW COLUMNS FROM department_staff LIKE 'mfa_enabled'");
    $checkMfaColumn->execute();
    if ($checkMfaColumn->get_result()->num_rows == 0) {
        $addMfaColumn = $conn->prepare("ALTER TABLE department_staff ADD COLUMN mfa_enabled TINYINT(1) DEFAULT 1");
        $addMfaColumn->execute();
    }
} catch (Exception $e) {
    error_log("Error setting up tables: " . $e->getMessage());
}

// Handle resend code request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['resend']) && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format"]);
        exit;
    }
    
    // Check if user exists and is a department staff
    $sql = "SELECT u.user_id, u.first_name, u.role FROM users u 
            WHERE u.email = ? AND (u.role = 'department_head' OR u.role = 'department_secretary')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $firstName = $row['first_name'];
        $role = $row['role'];
        
        // Generate a new verification code
        $verificationCode = generateVerificationCode();
        $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Delete any existing verification codes for this user
        $deleteSQL = "DELETE FROM department_verification_codes WHERE email = ?";
        $deleteStmt = mysqli_prepare($conn, $deleteSQL);
        mysqli_stmt_bind_param($deleteStmt, "s", $email);
        mysqli_stmt_execute($deleteStmt);
        
        // Insert new verification code
        $insertSQL = "INSERT INTO department_verification_codes (email, code, expiry_time) VALUES (?, ?, ?)";
        $insertStmt = mysqli_prepare($conn, $insertSQL);
        mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
        
        if (mysqli_stmt_execute($insertStmt)) {
            // Send verification email
            if (sendVerificationEmail($email, $firstName, $verificationCode, $role)) {
                echo json_encode(["status" => "success", "message" => "Verification code sent"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to send verification email"]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to generate verification code"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "User not found"]);
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
    $ip = $_SERVER['REMOTE_ADDR'];

    // Check for account lockout
    $checkLockout = $conn->prepare("SELECT lockout_until, attempts FROM department_lockouts WHERE email = ? AND lockout_until > NOW()");
    $checkLockout->bind_param("s", $email);
    $checkLockout->execute();
    $lockoutResult = $checkLockout->get_result();
    
    if ($lockoutResult->num_rows > 0) {
        $lockoutData = $lockoutResult->fetch_assoc();
        $remainingTime = strtotime($lockoutData['lockout_until']) - time();
        $remainingMinutes = ceil($remainingTime / 60);
        
        echo json_encode([
            "status" => "error", 
            "message" => "Account is locked due to too many failed attempts. Try again in {$remainingMinutes} minutes.",
            "lockout" => true,
            "remaining_minutes" => $remainingMinutes
        ]);
        exit;
    }

    if (!empty($email) && !empty($password)) {
        // Get user details including department staff info and MFA preference
        $sql = "SELECT u.user_id, u.email, u.password_hash, u.first_name, u.last_name, u.role, 
                       d.staff_id, d.department_id, d.mfa_enabled, dept.name as department_name  
                FROM users u
                JOIN department_staff d ON u.user_id = d.user_id
                JOIN departments dept ON d.department_id = dept.department_id
                WHERE u.email = ? AND (u.role = 'department_head' OR u.role = 'department_secretary')";
                
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $row['password_hash'])) {
                // Reset failed attempts on successful login
                $resetLockout = $conn->prepare("DELETE FROM department_lockouts WHERE email = ?");
                $resetLockout->bind_param("s", $email);
                $resetLockout->execute();
                
                // Check if MFA is enabled for this user
                $mfaEnabled = (bool)$row['mfa_enabled'];
                
                if ($mfaEnabled) {
                    // Generate and send verification code
                    $verificationCode = generateVerificationCode();
                    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    
                    // Store the verification code in database
                    // First, delete any existing codes for this user
                    $deleteSQL = "DELETE FROM department_verification_codes WHERE email = ?";
                    $deleteStmt = mysqli_prepare($conn, $deleteSQL);
                    mysqli_stmt_bind_param($deleteStmt, "s", $email);
                    mysqli_stmt_execute($deleteStmt);
                    
                    // Insert new verification code
                    $insertSQL = "INSERT INTO department_verification_codes (email, code, expiry_time) VALUES (?, ?, ?)";
                    $insertStmt = mysqli_prepare($conn, $insertSQL);
                    mysqli_stmt_bind_param($insertStmt, "sss", $email, $verificationCode, $expiryTime);
                    
                    if (mysqli_stmt_execute($insertStmt)) {
                        // Store user data in session but mark as not fully authenticated
                        $_SESSION['temp_user_id'] = $row['user_id'];
                        
                        // Send verification email
                        if (sendVerificationEmail($email, $row['first_name'], $verificationCode, $row['role'])) {
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
                    // MFA is disabled, complete login immediately
                    // Create session data
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['first_name'] = $row['first_name'];
                    $_SESSION['last_name'] = $row['last_name'];
                    $_SESSION['role'] = $row['role'];
                    $_SESSION['staff_id'] = $row['staff_id'];
                    $_SESSION['department_id'] = $row['department_id'];
                    $_SESSION['department_name'] = $row['department_name'];
                    $_SESSION['signin'] = true;
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    // Set last login timestamp
                    $updateLogin = $conn->prepare("UPDATE users SET updated_at = NOW() WHERE user_id = ?");
                    $updateLogin->bind_param("i", $row['user_id']);
                    $updateLogin->execute();

                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "requireVerification" => false,
                        "user" => [
                            "first_name" => $row['first_name'],
                            "last_name" => $row['last_name'],
                            "role" => $row['role'],
                            "department" => $row['department_name']
                        ]
                    ]);
                }
            } else {
                // Invalid password - increment failed attempts
                handleFailedLoginAttempt($conn, $email, $maxAttempts, $lockoutDuration);
                
                // Get updated attempt information
                $attemptInfo = getAttemptInfo($conn, $email, $maxAttempts);
                
                echo json_encode([
                    "status" => "error", 
                    "message" => "Invalid password", 
                    "attempts_remaining" => $attemptInfo['remaining']
                ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "User not found or not a department staff member"]);
        }
        mysqli_stmt_close($stmt);
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

// Function to handle failed login attempts
function handleFailedLoginAttempt($conn, $email, $maxAttempts, $lockoutDuration) {
    // Check if entry exists
    $checkStmt = $conn->prepare("SELECT attempts FROM department_lockouts WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing record
        $data = $result->fetch_assoc();
        $newAttempts = $data['attempts'] + 1;
        
        // If max attempts reached, set lockout time
        if ($newAttempts >= $maxAttempts) {
            $lockoutUntil = date('Y-m-d H:i:s', strtotime("+{$lockoutDuration} minutes"));
            $updateStmt = $conn->prepare("UPDATE department_lockouts SET attempts = ?, lockout_until = ? WHERE email = ?");
            $updateStmt->bind_param("iss", $newAttempts, $lockoutUntil, $email);
        } else {
            $updateStmt = $conn->prepare("UPDATE department_lockouts SET attempts = ? WHERE email = ?");
            $updateStmt->bind_param("is", $newAttempts, $email);
        }
        $updateStmt->execute();
    } else {
        // Create new record
        $attempts = 1;
        $insertStmt = $conn->prepare("INSERT INTO department_lockouts (email, attempts, lockout_until) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $insertStmt->bind_param("si", $email, $attempts);
        $insertStmt->execute();
    }
}

// Function to get attempt information
function getAttemptInfo($conn, $email, $maxAttempts) {
    $stmt = $conn->prepare("SELECT attempts FROM department_lockouts WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        $attempts = $data['attempts'];
        $remaining = $maxAttempts - $attempts;
        
        return [
            'attempts' => $attempts,
            'remaining' => $remaining > 0 ? $remaining : 0
        ];
    }
    
    return [
        'attempts' => 0,
        'remaining' => $maxAttempts
    ];
}

mysqli_close($conn);
?>