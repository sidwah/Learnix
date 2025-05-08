<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response format

include '../../config.php';

// Maximum allowed failed attempts
define('MAX_RESET_ATTEMPTS', 5);
// Lockout duration in minutes
define('RESET_LOCKOUT_DURATION_MINUTES', 30);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required fields exist in POST request
    if (!isset($_POST['email']) || !isset($_POST['code'])) {
        echo json_encode(["status" => "error", "message" => "Email and verification code are required"]);
        exit;
    }

    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    if (!empty($email) && !empty($code)) {
        // First check if account is locked
        $lockCheckSql = "SELECT * FROM admin_reset_lockouts WHERE email = ? AND lockout_until > NOW()";
        $lockCheckStmt = mysqli_prepare($conn, $lockCheckSql);
        mysqli_stmt_bind_param($lockCheckStmt, "s", $email);
        mysqli_stmt_execute($lockCheckStmt);
        $lockCheckResult = mysqli_stmt_get_result($lockCheckStmt);
        
        if ($lockRow = mysqli_fetch_assoc($lockCheckResult)) {
            // Account is locked
            $lockoutUntil = new DateTime($lockRow['lockout_until']);
            $now = new DateTime();
            $minutesRemaining = ceil(($lockoutUntil->getTimestamp() - $now->getTimestamp()) / 60);
            
            echo json_encode([
                "status" => "error", 
                "message" => "Too many failed attempts. Please try again in " . $minutesRemaining . " minutes.",
                "locked" => true,
                "minutes_remaining" => $minutesRemaining
            ]);
            exit;
        }
        
        // Check if the code exists and is valid
        $sql = "SELECT * FROM admin_reset_codes WHERE email = ? AND code = ? AND expiry_time > NOW()";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $email, $code);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Code is valid, now fetch user details to confirm user exists
            $userSql = "SELECT user_id FROM users WHERE email = ? AND role = 'admin'";
            $userStmt = mysqli_prepare($conn, $userSql);
            mysqli_stmt_bind_param($userStmt, "s", $email);
            mysqli_stmt_execute($userStmt);
            $userResult = mysqli_stmt_get_result($userStmt);
            
            if ($userRow = mysqli_fetch_assoc($userResult)) {
                // Store the user_id in session temporarily for the reset process
                $_SESSION['reset_user_id'] = $userRow['user_id'];
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_verified'] = true;
                $_SESSION['reset_time'] = time();
                
                // Reset failed attempts counter
                $resetAttemptsSql = "DELETE FROM admin_reset_attempts WHERE email = ?";
                $resetAttemptsStmt = mysqli_prepare($conn, $resetAttemptsSql);
                mysqli_stmt_bind_param($resetAttemptsStmt, "s", $email);
                mysqli_stmt_execute($resetAttemptsStmt);
                
                // Clear any lockouts
                $clearLockoutSql = "DELETE FROM admin_reset_lockouts WHERE email = ?";
                $clearLockoutStmt = mysqli_prepare($conn, $clearLockoutSql);
                mysqli_stmt_bind_param($clearLockoutStmt, "s", $email);
                mysqli_stmt_execute($clearLockoutStmt);
                
                echo json_encode(["status" => "success", "message" => "Verification successful"]);
            } else {
                echo json_encode(["status" => "error", "message" => "User not found"]);
            }
        } else {
            // Verification failed - increment the attempt counter
            $checkAttemptsSql = "SELECT * FROM admin_reset_attempts WHERE email = ?";
            $checkAttemptsStmt = mysqli_prepare($conn, $checkAttemptsSql);
            mysqli_stmt_bind_param($checkAttemptsStmt, "s", $email);
            mysqli_stmt_execute($checkAttemptsStmt);
            $checkAttemptsResult = mysqli_stmt_get_result($checkAttemptsStmt);
            
            if ($attemptsRow = mysqli_fetch_assoc($checkAttemptsResult)) {
                // Increment existing attempts
                $newAttempts = $attemptsRow['attempts'] + 1;
                $updateAttemptsSql = "UPDATE admin_reset_attempts SET attempts = ?, last_attempt = NOW() WHERE email = ?";
                $updateAttemptsStmt = mysqli_prepare($conn, $updateAttemptsSql);
                mysqli_stmt_bind_param($updateAttemptsStmt, "is", $newAttempts, $email);
                mysqli_stmt_execute($updateAttemptsStmt);
                
                // Check if we need to lock the account
                if ($newAttempts >= MAX_RESET_ATTEMPTS) {
                    // Lock the account
                    $lockoutUntil = date('Y-m-d H:i:s', strtotime('+' . RESET_LOCKOUT_DURATION_MINUTES . ' minutes'));
                    
                    // Check if a lockout record already exists
                    $checkLockoutSql = "SELECT * FROM admin_reset_lockouts WHERE email = ?";
                    $checkLockoutStmt = mysqli_prepare($conn, $checkLockoutSql);
                    mysqli_stmt_bind_param($checkLockoutStmt, "s", $email);
                    mysqli_stmt_execute($checkLockoutStmt);
                    $checkLockoutResult = mysqli_stmt_get_result($checkLockoutStmt);
                    
                    if (mysqli_fetch_assoc($checkLockoutResult)) {
                        // Update existing lockout
                        $updateLockoutSql = "UPDATE admin_reset_lockouts SET lockout_until = ?, attempts = attempts + 1 WHERE email = ?";
                        $updateLockoutStmt = mysqli_prepare($conn, $updateLockoutSql);
                        mysqli_stmt_bind_param($updateLockoutStmt, "ss", $lockoutUntil, $email);
                        mysqli_stmt_execute($updateLockoutStmt);
                    } else {
                        // Create new lockout
                        $createLockoutSql = "INSERT INTO admin_reset_lockouts (email, lockout_until, attempts) VALUES (?, ?, 1)";
                        $createLockoutStmt = mysqli_prepare($conn, $createLockoutSql);
                        mysqli_stmt_bind_param($createLockoutStmt, "ss", $email, $lockoutUntil);
                        mysqli_stmt_execute($createLockoutStmt);
                    }
                    
                    // Return lockout message
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Too many failed attempts. Password reset has been temporarily locked for " . RESET_LOCKOUT_DURATION_MINUTES . " minutes.",
                        "locked" => true,
                        "minutes_remaining" => RESET_LOCKOUT_DURATION_MINUTES
                    ]);
                } else {
                    // Just return error with attempts remaining
                    $attemptsRemaining = MAX_RESET_ATTEMPTS - $newAttempts;
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Invalid or expired verification code. " . $attemptsRemaining . " attempts remaining before lockout.",
                        "attempts_remaining" => $attemptsRemaining
                    ]);
                }
            } else {
                // First failed attempt
                $createAttemptSql = "INSERT INTO admin_reset_attempts (email, attempts, last_attempt) VALUES (?, 1, NOW())";
                $createAttemptStmt = mysqli_prepare($conn, $createAttemptSql);
                mysqli_stmt_bind_param($createAttemptStmt, "s", $email);
                mysqli_stmt_execute($createAttemptStmt);
                
                // Return error with attempts remaining
                $attemptsRemaining = MAX_RESET_ATTEMPTS - 1;
                echo json_encode([
                    "status" => "error", 
                    "message" => "Invalid or expired verification code. " . $attemptsRemaining . " attempts remaining before lockout.",
                    "attempts_remaining" => $attemptsRemaining
                ]);
            }
        }
    } else {
        echo json_encode(["status" => "error", "message" => "All fields are required"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>