<?php
// backend/auth/instructor/change_password.php
session_start();
require_once '../../config.php';
header('Content-Type: application/json');

// Check if user is logged in as instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON data."]);
        exit;
    }
    
    // Extract form data
    $newPassword = $input['newPassword'] ?? '';
    $confirmPassword = $input['confirmPassword'] ?? '';
    $isFirstTime = $input['isFirstTime'] ?? false;
    
    // Validate passwords
    if (empty($newPassword) || empty($confirmPassword)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit;
    }
    
    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
        exit;
    }
    
    // Validate password strength
    if (strlen($newPassword) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long."]);
        exit;
    }
    
    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || 
        !preg_match('/[0-9]/', $newPassword) || !preg_match('/[^A-Za-z0-9]/', $newPassword)) {
        echo json_encode(["status" => "error", "message" => "Password must include uppercase, lowercase, number, and special character."]);
        exit;
    }
    
    try {
        $userId = $_SESSION['user_id'];
        
        // Hash the new password
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        
        // Update the password and reset the force_password_reset flag
        $stmt = $conn->prepare("UPDATE users SET password_hash = ?, force_password_reset = 0 WHERE user_id = ?");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("si", $newPasswordHash, $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // If this is the first time setting password (coming from invitation)
            if ($isFirstTime) {
                // Get user details for notifications
                $userStmt = $conn->prepare("SELECT u.email, u.first_name, u.last_name, i.instructor_id 
                                          FROM users u
                                          JOIN instructors i ON u.user_id = i.user_id 
                                          WHERE u.user_id = ?");
                $userStmt->bind_param("i", $userId);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $userData = $userResult->fetch_assoc();
                $userStmt->close();
                
                // Get the invitation details
                $invitationStmt = $conn->prepare("SELECT invited_by, department_id FROM instructor_invitations 
                                                 WHERE email = ? AND is_used = 1 
                                                 ORDER BY created_at DESC LIMIT 1");
                $invitationStmt->bind_param("s", $userData['email']);
                $invitationStmt->execute();
                $invitationResult = $invitationStmt->get_result();
                $invitationData = $invitationResult->fetch_assoc();
                $invitationStmt->close();
                
                // Create notification about password being set
                if ($invitationData) {
                    require_once '../../../includes/notification_functions.php';
                    notifyAboutInvitationAccepted(
                        $userData['instructor_id'], 
                        $userData['email'], 
                        $userData['first_name'], 
                        $userData['last_name'], 
                        $invitationData['department_id'], 
                        $invitationData['invited_by']
                    );
                }
            }
            
            $stmt->close();
            
            // Create a security notification
            $deviceInfo = $_SERVER['HTTP_USER_AGENT'];
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            
            // Create a notification about the password change
            $notificationStmt = $conn->prepare("INSERT INTO user_notifications 
                                                (user_id, type, title, message, created_at) 
                                                VALUES (?, 'password_changed', 'Password Changed', ?, NOW())");
            $message = "Your password has been successfully changed. If you did not make this change, please contact support immediately.";
            $notificationStmt->bind_param("is", $userId, $message);
            $notificationStmt->execute();
            $notificationStmt->close();
            
            // Get all user session data for proper setup
            $sessionStmt = $conn->prepare("SELECT u.*, i.instructor_id, d.department_id, d.name as department_name
                                         FROM users u
                                         LEFT JOIN instructors i ON u.user_id = i.user_id
                                         LEFT JOIN department_instructors di ON i.instructor_id = di.instructor_id AND di.status = 'active'
                                         LEFT JOIN departments d ON di.department_id = d.department_id
                                         WHERE u.user_id = ?");
            $sessionStmt->bind_param("i", $userId);
            $sessionStmt->execute();
            $sessionResult = $sessionStmt->get_result();
            $sessionData = $sessionResult->fetch_assoc();
            $sessionStmt->close();
            
            // Update session with full user data
            if ($sessionData) {
                $_SESSION['email'] = $sessionData['email'];
                $_SESSION['first_name'] = $sessionData['first_name'];
                $_SESSION['last_name'] = $sessionData['last_name'];
                $_SESSION['instructor_id'] = $sessionData['instructor_id'];
                $_SESSION['department_id'] = $sessionData['department_id'];
                $_SESSION['department_name'] = $sessionData['department_name'];
                $_SESSION['profile_pic'] = $sessionData['profile_pic'] ?? 'default.png';
            }
            
            echo json_encode([
                "status" => "success", 
                "message" => "Password set successfully. Taking you to your dashboard...",
                "redirect" => "../instructor/index.php"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update password. Please try again."]);
        }
    } catch (Exception $e) {
        error_log("Password change error: " . $e->getMessage());
        echo json_encode(["status" => "error", "message" => "An error occurred. Please try again later."]);
    } finally {
        if (isset($conn)) {
            $conn->close();
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>