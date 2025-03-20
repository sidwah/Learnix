<?php
// Disable error display but log errors
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Set JSON header
header('Content-Type: application/json');

// Start output buffering to capture any unwanted output
ob_start();

try {
    // Process form data
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        
        if (empty($email) || empty($password)) {
            throw new Exception("All fields are required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }
        
        // Database connection
        require_once '../../config.php';
        $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Check for instructor lockouts
        $tableCheckResult = $conn->query("SHOW TABLES LIKE 'instructor_lockouts'");
        $tableExists = $tableCheckResult->num_rows > 0;
        
        // Check for lockouts if table exists
        if ($tableExists) {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM instructor_lockouts WHERE email = ? AND lockout_until > NOW()");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->bind_result($lockedOut);
                $stmt->fetch();
                $stmt->close();
                
                if ($lockedOut > 0) {
                    throw new Exception("Too many failed login attempts. Please try again later.");
                }
            }
        }
        
        // Look up user
        $stmt = $conn->prepare("SELECT user_id, password_hash, is_verified, status, force_password_reset
                                FROM users
                                WHERE LOWER(email) = LOWER(?) AND role = 'instructor'");
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 0) {
            throw new Exception("No instructor account found with this email.");
        }
        
        $stmt->bind_result($userId, $passwordHash, $isVerified, $status, $forcePasswordReset);
        $stmt->fetch();
        $stmt->close();
        
        // Check verification
        if ($isVerified == 0) {
            // Clear output buffer before sending JSON
            ob_end_clean();
            echo json_encode(["status" => "unverified", "message" => "Your account is not verified."]);
            exit;
        }
        
        // Check account status
        if ($status !== 'active') {
            throw new Exception("Your account is currently $status.");
        }
        
        // Verify password
        if (!password_verify($password, $passwordHash)) {
            // Record failed login attempt if table exists
            if ($tableExists) {
                $stmt = $conn->prepare("INSERT INTO instructor_lockouts (email, lockout_until, attempts) 
                                       VALUES (?, DATE_ADD(NOW(), INTERVAL 15 MINUTE), 1)
                                       ON DUPLICATE KEY UPDATE 
                                       attempts = attempts + 1, 
                                       lockout_until = CASE WHEN attempts >= 5 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE) ELSE lockout_until END");
                if ($stmt) {
                    $stmt->bind_param("s", $email);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            throw new Exception("Invalid email or password.");
        }
        
        // Clear failed login attempts if table exists
        if ($tableExists) {
            $stmt = $conn->prepare("DELETE FROM instructor_lockouts WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        // Start session
        session_start();
        session_regenerate_id(true);
        
        // Set session security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', 1);
        }
        
        // Create session
        $_SESSION['user_id'] = $userId;
        $_SESSION['role'] = 'instructor';
        $_SESSION['signin'] = true;
        $_SESSION['last_activity'] = time();
        
        // Check if password reset is required
        if ($forcePasswordReset == 1) {
            // Clear output buffer before sending JSON
            ob_end_clean();
            echo json_encode(["status" => "reset_required", "message" => "You must reset your password before continuing."]);
            exit;
        }
        
        // Close database connection
        $conn->close();
        
        // Clear output buffer before sending JSON
        ob_end_clean();
        echo json_encode(["status" => "success", "message" => "Sign in successful. Redirecting..."]);
    } else {
        throw new Exception("This endpoint requires a POST request.");
    }
} catch (Exception $e) {
    // Log the error
    error_log("Login Error: " . $e->getMessage());
    
    // Clear output buffer before sending JSON
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
exit;
?>