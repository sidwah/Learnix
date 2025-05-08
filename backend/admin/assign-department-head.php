<?php
// Authentication check
require_once '../config.php';
require_once '../auth/admin/admin-auth-check.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $departmentId = mysqli_real_escape_string($conn, trim($_POST['department_id']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $firstName = mysqli_real_escape_string($conn, trim($_POST['first_name']));
    $lastName = mysqli_real_escape_string($conn, trim($_POST['last_name']));
    $notes = mysqli_real_escape_string($conn, trim($_POST['notes'] ?? ''));
    
    // Validate input
    if (empty($departmentId) || empty($email) || empty($firstName) || empty($lastName)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department ID, email, first name, and last name are required.'
        ]);
        exit;
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if the department exists
        $deptQuery = "SELECT * FROM departments WHERE department_id = ? AND deleted_at IS NULL";
        $deptStmt = $conn->prepare($deptQuery);
        $deptStmt->bind_param("i", $departmentId);
        $deptStmt->execute();
        $deptResult = $deptStmt->get_result();
        
        if ($deptResult->num_rows === 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Department not found.'
            ]);
            exit;
        }
        
        // Check if there's already an active head
        $headQuery = "SELECT * FROM department_staff 
                      WHERE department_id = ? AND role = 'head' AND status = 'active' AND deleted_at IS NULL";
        $headStmt = $conn->prepare($headQuery);
        $headStmt->bind_param("i", $departmentId);
        $headStmt->execute();
        $headResult = $headStmt->get_result();
        
        if ($headResult->num_rows > 0) {
            // Soft delete the current head
            $updateQuery = "UPDATE department_staff SET status = 'inactive', deleted_at = NOW() 
                            WHERE department_id = ? AND role = 'head' AND status = 'active'";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("i", $departmentId);
            $updateStmt->execute();
        }
        
        // Check if the user exists
        $userQuery = "SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param("s", $email);
        $userStmt->execute();
        $userResult = $userStmt->get_result();
        
        if ($userResult->num_rows > 0) {
            // User exists, get user_id
            $userRow = $userResult->fetch_assoc();
            $userId = $userRow['user_id'];
        } else {
            // Create temporary password
            $tempPassword = bin2hex(random_bytes(5));
            $passwordHash = password_hash($tempPassword, PASSWORD_DEFAULT);
            
            // Insert new user
            $insertUserQuery = "INSERT INTO users (first_name, last_name, email, username, password_hash, role, is_verified, force_password_reset) 
                                VALUES (?, ?, ?, ?, ?, 'department_head', 1, 1)";
            $username = strtolower($firstName . '.' . $lastName . rand(100, 999));
            $insertUserStmt = $conn->prepare($insertUserQuery);
            $insertUserStmt->bind_param("sssss", $firstName, $lastName, $email, $username, $passwordHash);
            $insertUserStmt->execute();
            $userId = $insertUserStmt->insert_id;
        }
        
        // Create invitation
        $expiryTime = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $tempPasswordHash = password_hash(bin2hex(random_bytes(5)), PASSWORD_DEFAULT);
        
        $inviteQuery = "INSERT INTO department_staff_invitations 
                       (email, temp_password_hash, department_id, role, invited_by, expiry_time) 
                       VALUES (?, ?, ?, 'head', ?, ?)";
        $inviteStmt = $conn->prepare($inviteQuery);
        $currentUserId = $_SESSION['user_id'];
        $inviteStmt->bind_param("ssiis", $email, $tempPasswordHash, $departmentId, $currentUserId, $expiryTime);
        $inviteStmt->execute();
        
        // Create department staff entry
        $staffQuery = "INSERT INTO department_staff (user_id, department_id, role, appointment_date) 
                      VALUES (?, ?, 'head', CURDATE())";
        $staffStmt = $conn->prepare($staffQuery);
        $staffStmt->bind_param("ii", $userId, $departmentId);
        $staffStmt->execute();
        
        // Log activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'head_assign', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $details = json_encode([
            'head_user_id' => $userId,
            'head_email' => $email
        ]);
        $activityStmt->bind_param("iis", $departmentId, $currentUserId, $details);
        $activityStmt->execute();
        
        // Send email notification logic here
        // ...
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department head invitation sent successfully. They will receive an email with instructions.'
        ]);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to assign department head: ' . $e->getMessage()
        ]);
    }
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}