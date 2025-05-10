<?php
// backend/auth/instructor/signin.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../../config.php'; // Database connection
header('Content-Type: application/json'); // Ensure JSON response


/**
 * Sends an MFA code to the specified email address.
 *
 * @param string $email The recipient's email address.
 * @param string $code The MFA code to send.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function send_mfa_code_email($email, $code) {
    $subject = "Your MFA Code for Learnix";
    $message = "Hello,\n\nYour Multi-Factor Authentication (MFA) code is: $code\n\n"
             . "This code will expire in 10 minutes. If you did not request this, please ignore this email.\n\n"
             . "Thank you,\nLearnix Team";
    $headers = "From: no-reply@learnix.com\r\n"
             . "Reply-To: support@learnix.com\r\n"
             . "X-Mailer: PHP/" . phpversion();

    // Use PHP's mail function to send the email
    return mail($email, $subject, $message, $headers);
}

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

        // Check for invitation login
        $inviteStmt = $conn->prepare("SELECT ii.id, ii.temp_password_hash, ii.department_id, ii.is_used, ii.expiry_time,
                                            d.name as department_name, ii.invited_by, ii.first_name, ii.last_name
                                     FROM instructor_invitations ii
                                     INNER JOIN departments d ON ii.department_id = d.department_id
                                     WHERE LOWER(ii.email) = LOWER(?) 
                                     AND ii.is_used = 0 
                                     AND ii.expiry_time > NOW()");
        $inviteStmt->bind_param("s", $email);
        $inviteStmt->execute();
        $inviteResult = $inviteStmt->get_result();
        
        if ($inviteResult->num_rows > 0) {
            // Invitation login flow
            $invitation = $inviteResult->fetch_assoc();
            $inviteStmt->close();
            
            if (password_verify($password, $invitation['temp_password_hash'])) {
                $conn->begin_transaction();
                
                try {
                    // Use first_name and last_name from instructor_invitations
                    $firstName = $invitation['first_name'] ?? 'Instructor';
                    $lastName = $invitation['last_name'] ?? 'User';
                    
                    // Generate unique username
                    $usernameBase = strtolower($firstName . '.' . $lastName);
                    $uniqueUsername = $usernameBase;
                    
                    $usernameStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
                    while (true) {
                        $usernameStmt->bind_param("s", $uniqueUsername);
                        $usernameStmt->execute();
                        if (!$usernameStmt->fetch()) break;
                        $uniqueUsername = $usernameBase . rand(1000, 9999);
                        $usernameStmt->close();
                        $usernameStmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
                    }
                    $usernameStmt->close();
                    
                    // Create user with mfa_enabled = 0
                    $userStmt = $conn->prepare("INSERT INTO users (first_name, last_name, username, email, password_hash, role, is_verified, force_password_reset, mfa_enabled) 
                                               VALUES (?, ?, ?, ?, ?, 'instructor', 1, 1, 0)");
                    $userStmt->bind_param("sssss", $firstName, $lastName, $uniqueUsername, $email, $invitation['temp_password_hash']);
                    $userStmt->execute();
                    $userId = $userStmt->insert_id;
                    $userStmt->close();
                    
                    // Create instructor record
                    $instructorStmt = $conn->prepare("INSERT INTO instructors (user_id) VALUES (?)");
                    $instructorStmt->bind_param("i", $userId);
                    $instructorStmt->execute();
                    $instructorId = $instructorStmt->insert_id;
                    $instructorStmt->close();
                    
                    // Associate with department
                    $deptStmt = $conn->prepare("INSERT INTO department_instructors (department_id, instructor_id, added_by, status) 
                                               VALUES (?, ?, ?, 'active')");
                    $deptStmt->bind_param("iii", $invitation['department_id'], $instructorId, $invitation['invited_by']);
                    $deptStmt->execute();
                    $deptStmt->close();
                    
                    // Mark invitation as used
                    $updateInviteStmt = $conn->prepare("UPDATE instructor_invitations SET is_used = 1 WHERE id = ?");
                    $updateInviteStmt->bind_param("i", $invitation['id']);
                    $updateInviteStmt->execute();
                    $updateInviteStmt->close();
                    
                    $conn->commit();
                    
                    // Start session
                    session_start();
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $email;
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['role'] = 'instructor';
                    $_SESSION['instructor_id'] = $instructorId;
                    $_SESSION['department_id'] = $invitation['department_id'];
                    $_SESSION['department_name'] = $invitation['department_name'];
                    $_SESSION['force_password_reset'] = 1;
                    $_SESSION['signin'] = true;
                    
                    echo json_encode([
                        "status" => "reset_required",
                        "message" => "Please set your permanent password to continue."
                    ]);
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    throw $e;
                }
            } else {
                echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
                exit;
            }
        }
        $inviteStmt->close();

        // Regular login flow
        $stmt = $conn->prepare("SELECT u.user_id, u.first_name, u.last_name, u.email, u.password_hash, u.is_verified, 
                                       u.status, u.force_password_reset, i.instructor_id,
                                       di.department_id, d.name as department_name, u.profile_pic, u.mfa_enabled
                                FROM users u
                                JOIN instructors i ON u.user_id = i.user_id
                                LEFT JOIN department_instructors di ON i.instructor_id = di.instructor_id AND di.status = 'active' AND di.deleted_at IS NULL
                                LEFT JOIN departments d ON di.department_id = d.department_id
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
            $stmt->bind_result($userId, $firstName, $lastName, $userEmail, $passwordHash, $isVerified, $status, 
                              $forcePasswordReset, $instructorId, $departmentId, $departmentName, $profilePic, $mfaEnabled);
            $stmt->fetch();
            $stmt->close();
            
            if ($isVerified == 0) {
                echo json_encode(["status" => "unverified", "message" => "Your account is not verified. Resend verification code?"]);
                exit;
            }
            
            if ($status !== 'active') {
                echo json_encode(["status" => "error", "message" => "Your account is currently $status."]);
                exit;
            }
            
            if (password_verify($password, $passwordHash)) {
                // Reset lockouts
                $deleteStmt = $conn->prepare("DELETE FROM instructor_lockouts WHERE email = ?");
                $deleteStmt->bind_param("s", $email);
                $deleteStmt->execute();
                $deleteStmt->close();
                
                // Log success
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, 1, NOW())");
                $logStmt->bind_param("ss", $ipAddress, $email);
                $logStmt->execute();
                $logStmt->close();
                
                if ($forcePasswordReset == 1) {
                    session_start();
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $userEmail;
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['instructor_id'] = $instructorId;
                    $_SESSION['role'] = 'instructor';
                    $_SESSION['department_id'] = $departmentId;
                    $_SESSION['department_name'] = $departmentName;
                    $_SESSION['profile_pic'] = $profilePic ?? 'default.png';
                    $_SESSION['force_password_reset'] = 1;
                    $_SESSION['signin'] = true;
                    echo json_encode(["status" => "reset_required", "message" => "You must reset your password before continuing."]);
                } else if ($mfaEnabled == 1) {
                    // Generate and store MFA code
                    $mfaCode = rand(100000, 999999);
                    $expiryTime = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    $mfaStmt = $conn->prepare("UPDATE users SET mfa_code = ?, mfa_code_expiry = ? WHERE user_id = ?");
                    $mfaStmt->bind_param("ssi", $mfaCode, $expiryTime, $userId);
                    $mfaStmt->execute();
                    $mfaStmt->close();
                    
                    // Send MFA code
                    send_mfa_code_email($userEmail, $mfaCode);
                    echo json_encode(["status" => "mfa_required", "message" => "Please enter the verification code sent to your email."]);
                } else {
                    session_start();
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $userEmail;
                    $_SESSION['first_name'] = $firstName;
                    $_SESSION['last_name'] = $lastName;
                    $_SESSION['instructor_id'] = $instructorId;
                    $_SESSION['role'] = 'instructor';
                    $_SESSION['department_id'] = $departmentId;
                    $_SESSION['department_name'] = $departmentName;
                    $_SESSION['profile_pic'] = $profilePic ?? 'default.png';
                    $_SESSION['signin'] = true;
                    echo json_encode(["status" => "success", "message" => "Sign in successful. Redirecting..."]);
                }
            } else {
                // Handle failed login
                $ipAddress = $_SERVER['REMOTE_ADDR'];
                $logStmt = $conn->prepare("INSERT INTO login_attempts (ip_address, email, success, attempt_time) VALUES (?, ?, 0, NOW())");
                $logStmt->bind_param("ss", $ipAddress, $email);
                $logStmt->execute();
                $logStmt->close();
                
                $attemptStmt = $conn->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ? AND success = 0 AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
                $attemptStmt->bind_param("s", $email);
                $attemptStmt->execute();
                $attemptStmt->bind_result($failedAttempts);
                $attemptStmt->fetch();
                $attemptStmt->close();
                
                if ($failedAttempts >= 5) {
                    $lockoutTime = date('Y-m-d H:i:s', strtotime('+15 minutes'));
                    $lockoutStmt = $conn->prepare("INSERT INTO instructor_lockouts (email, lockout_until, attempts) VALUES (?, ?, ?) 
                                                  ON DUPLICATE KEY UPDATE lockout_until = VALUES(lockout_until), attempts = attempts + 1");
                    $lockoutStmt->bind_param("ssi", $email, $lockoutTime, $failedAttempts);
                    $lockoutStmt->execute();
                    $lockoutStmt->close();
                    
                    echo json_encode([
                        "status" => "error", 
                        "message" => "Too many failed login attempts. Account locked. Try again later or reset password."
                    ]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
                }
            }
        } else {
            $userCheckStmt = $conn->prepare("SELECT role FROM users WHERE LOWER(email) = LOWER(?)");
            $userCheckStmt->bind_param("s", $email);
            $userCheckStmt->execute();
            $userCheckStmt->store_result();
            
            if ($userCheckStmt->num_rows > 0) {
                $userCheckStmt->bind_result($userRole);
                $userCheckStmt->fetch();
                $userCheckStmt->close();
                
                echo json_encode(["status" => "error", "message" => "This email is registered as a $userRole, not an instructor."]);
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