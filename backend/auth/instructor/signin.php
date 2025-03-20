<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../config.php'; // Database connection
header('Content-Type: application/json'); // Ensure JSON response

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email address."]);
        exit;
    }

    // Database connection
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');

    if ($conn->connect_error) {
        error_log("Database Connection Failed: " . $conn->connect_error);
        echo json_encode(["status" => "error", "message" => "Database connection error."]);
        exit;
    }

    try {
        // Check if account is locked out
        $stmt = $conn->prepare("SELECT lockout_until FROM instructor_lockouts WHERE email = ? AND lockout_until > NOW()");
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($lockoutUntil);
            $stmt->fetch();
            $stmt->close();
            
            echo json_encode([
                "status" => "error", 
                "message" => "Account is locked due to multiple failed attempts. Please try again later or reset your password."
            ]);
            exit;
        }
        $stmt->close();

        // Prepare the SQL statement for user verification
        $stmt = $conn->prepare("SELECT u.user_id, u.password_hash, u.is_verified, u.status, u.force_password_reset, i.instructor_id
                            FROM users u
                            JOIN instructors i ON u.user_id = i.user_id
                            WHERE LOWER(u.email) = LOWER(?) AND u.role = 'instructor'");
        
        if (!$stmt) {
            error_log("SQL Prepare Error: " . $conn->error);
            echo json_encode(["status" => "error", "message" => "Database query failed."]);
            exit;
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $stmt->bind_result($userId, $passwordHash, $isVerified, $status, $forcePasswordReset, $instructorId);
            $stmt->fetch();
            $stmt->close();
            
            // Check if the account is verified
            if ($isVerified == 0) {
                echo json_encode(["status" => "unverified", "message" => "Your account is not verified. Resend verification code?"]);
                exit;
            }
            
            // Check if account is active
            if ($status !== 'active') {
                echo json_encode(["status" => "error", "message" => "Your account is currently $status."]);
                exit;
            }
            
            // Verify password
            if (password_verify($password, $passwordHash)) {
                // Reset failed login attempts if successful
                $deleteStmt = $conn->prepare("DELETE FROM instructor_lockouts WHERE email = ?");
                $deleteStmt->bind_param("s", $email);
                $deleteStmt->execute();
                $deleteStmt->close();
                
                // Record successful login attempt
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, 1, NOW())");
                $logStmt->bind_param("ss", $ipAddress, $email);
                $logStmt->execute();
                $logStmt->close();
                
                // Start session
                session_start();
                session_regenerate_id(true); // Prevents session fixation attacks
                $_SESSION['user_id'] = $userId;
                $_SESSION['instructor_id'] = $instructorId;
                $_SESSION['role'] = 'instructor';
                $_SESSION['signin'] = true;
                
                // Check if the user must reset their password
                if ($forcePasswordReset == 1) {
                    echo json_encode(["status" => "reset_required", "message" => "You must reset your password before continuing."]);
                    exit;
                }
                
                echo json_encode(["status" => "success", "message" => "Sign in successful. Redirecting..."]);
            } else {
                // Record failed login attempt
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, 0, NOW())");
                $logStmt->bind_param("ss", $ipAddress, $email);
                $logStmt->execute();
                $logStmt->close();
                
                // Check for multiple failed attempts
                $attemptStmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
                $attemptStmt->bind_param("s", $email);
                $attemptStmt->execute();
                $attemptStmt->bind_result($failedAttempts);
                $attemptStmt->fetch();
                $attemptStmt->close();
                
                // If too many failed attempts, lock the account
                if ($failedAttempts >= 5) {
                    $lockoutTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $lockoutStmt = $conn->prepare("INSERT INTO instructor_lockouts (email, lockout_until, attempts) VALUES (?, ?, ?) 
                                              ON DUPLICATE KEY UPDATE lockout_until = VALUES(lockout_until), attempts = attempts + 1");
                    $lockoutStmt->bind_param("ssi", $email, $lockoutTime, $failedAttempts);
                    $lockoutStmt->execute();
                    $lockoutStmt->close();
                    
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Too many failed login attempts. Your account has been temporarily locked. Please try again later or reset your password."
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
                }
            }
        } else {
            // Check if user exists but is not an instructor
            $userCheckStmt = $conn->prepare("SELECT role FROM users WHERE LOWER(email) = LOWER(?)");
            $userCheckStmt->bind_param("s", $email);
            $userCheckStmt->execute();
            $userCheckStmt->store_result();
            
            if ($userCheckStmt->num_rows > 0) {
                $userCheckStmt->bind_result($userRole);
                $userCheckStmt->fetch();
                $userCheckStmt->close();
                
                echo json_encode(["status" => "error", "message" => "This email is registered as a $userRole, not as an instructor."]);
            } else {
                echo json_encode(["status" => "error", "message" => "No account found with this email."]);
            }
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "An error occurred. Please try again later."]);
    } finally {
        $conn->close();
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>