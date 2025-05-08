<?php
require_once '../../config.php'; // Database connection file

// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = 'student'; // Fixed role for student signup

    if (empty($firstName) || empty($lastName) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo "All fields are required.";
        exit;
    }

    if ($password !== $confirmPassword) {
        echo "Passwords do not match.";
        exit;
    }

    // Hash password after validation
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Check if email already exists for student role
    $checkQuery = "SELECT user_id, is_verified FROM users WHERE email = ? AND role = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ss", $email, $role);
    $stmt->execute();
    $stmt->bind_result($existingUserId, $isVerified);
    $stmt->fetch();
    $stmt->close();
    
    if ($existingUserId) {
        echo $isVerified == 0 ? "unverified" : "Email already exists. Please use a different email.";
        exit;
    }
    
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
                echo "verification_sent";
            } else {
                echo "Failed to send verification email. Please try again.";
            }
        } else {
            throw new Exception("Database Error: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log($e->getMessage());
        echo "Server error. Please try again later.";
    }
    exit;
} else {
    echo "Invalid request method.";
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
        $mail->Body = "<p>Your verification code is: <strong>$verificationCode</strong></p>
                       <p>Please enter this code in the verification form to activate your account.</p>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
