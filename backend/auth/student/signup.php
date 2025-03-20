<?php
// signup.php - Improved with consistent JSON responses
require_once '../../config.php'; // Database connection file

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

header('Content-Type: application/json'); // All responses are JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirmPassword'] ?? '');
    $role = 'student'; // Fixed role for student signup

    // Validate required fields
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'All fields are required.'
        ]);
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please enter a valid email address.'
        ]);
        exit;
    }

    // Validate password match
    if ($password !== $confirmPassword) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Passwords do not match.'
        ]);
        exit;
    }

    // Validate password strength
    if (strlen($password) < 8) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Password must be at least 8 characters long.'
        ]);
        exit;
    }

    // Hash password after validation
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists for student role
    $checkQuery = "SELECT user_id, is_verified FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $stmt->store_result();
    $existingUser = $stmt->num_rows > 0;
    
    if ($existingUser) {
        $stmt->bind_result($existingUserId, $isVerified);
        $stmt->fetch();
        $stmt->close();
        
        if ($isVerified == 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'unverified'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email already exists. Please use a different email address.'
            ]);
        }
        exit;
    }
    $stmt->close();
    
    // Generate a unique username
    $usernameBase = strtolower($firstName . '.' . $lastName);
    $uniqueUsername = $usernameBase;
    $count = 0;
    
    do {
        $testUsername = ($count === 0) ? $usernameBase : $usernameBase . $count;
        $usernameCheckQuery = "SELECT COUNT(*) FROM users WHERE username = ?";
        $stmt = $conn->prepare($usernameCheckQuery);
        $stmt->bind_param("s", $testUsername);
        $stmt->execute();
        $stmt->bind_result($exists);
        $stmt->fetch();
        $stmt->close();
        $count++;
        
        if ($count > 100) { // Safety break to prevent infinite loops
            echo json_encode([
                'status' => 'error',
                'message' => 'Unable to generate a unique username. Please try again later.'
            ]);
            exit;
        }
    } while ($exists > 0);
    
    $uniqueUsername = $testUsername;

    // Insert user into users table
    $insertQuery = "INSERT INTO users (first_name, last_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("ssssss", $firstName, $lastName, $uniqueUsername, $email, $hashedPassword, $role);

    try {
        if ($stmt->execute()) {
            $userId = $stmt->insert_id;
            $stmt->close();

            // Generate 5-digit verification code
            $verificationCode = str_pad(rand(0, 99999), 5, "0", STR_PAD_LEFT);
            $expiresAt = date('Y-m-d H:i:s', strtotime('+15 minutes'));

            // Insert into user_verification table
            $verificationQuery = "INSERT INTO user_verification (user_id, token, expires_at) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($verificationQuery);
            $stmt->bind_param("iss", $userId, $verificationCode, $expiresAt);
            $stmt->execute();
            $stmt->close();
            
            // Send verification email
            if (sendVerificationEmail($email, $verificationCode)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'verification_sent'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to send verification email. Please try again.'
                ]);
            }
        } else {
            throw new Exception("Database Error: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Server error. Please try again later.'
        ]);
    }
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}

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
        $mail->Subject = 'Your Learnix Verification Code';
        $mail->Body = '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 5px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2 style="color: #333;">Welcome to Learnix!</h2>
                </div>
                <p>Thank you for creating an account. To get started, please verify your email address using the verification code below:</p>
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
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>