<?php
session_start();

header("Content-Type: application/json"); // Ensure JSON response format

include '../../config.php';

// Check if the user is in a temporary authenticated state
if (!isset($_SESSION['temp_user_id']) && !isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Authentication required"]);
    exit;
}

// Get the user ID (either from temporary session or regular session)
$user_id = isset($_SESSION['temp_user_id']) ? $_SESSION['temp_user_id'] : $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if required fields exist in POST request
    if (!isset($_POST['newPassword']) || !isset($_POST['confirmPassword'])) {
        echo json_encode(["status" => "error", "message" => "New password and confirmation are required"]);
        exit;
    }

    $newPassword = $_POST['newPassword'];
    $confirmPassword = $_POST['confirmPassword'];

    // Validate passwords
    if (strlen($newPassword) < 8) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long"]);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
        exit;
    }

    // Hash the new password
    $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);

    // Update the user's password and reset the force_password_reset flag
    $sql = "UPDATE users SET password_hash = ?, force_password_reset = 0, updated_at = NOW() WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $passwordHash, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        // If user was in temporary session, complete the login process
        if (isset($_SESSION['temp_user_id'])) {
            // Fetch the user data to create a complete session
            $userSql = "SELECT u.user_id, u.email, u.first_name, u.last_name, u.role, 
                         d.staff_id, d.department_id, dept.name as department_name  
                  FROM users u
                  JOIN department_staff d ON u.user_id = d.user_id
                  JOIN departments dept ON d.department_id = dept.department_id
                  WHERE u.user_id = ?";
            
            $userStmt = mysqli_prepare($conn, $userSql);
            mysqli_stmt_bind_param($userStmt, "i", $user_id);
            mysqli_stmt_execute($userStmt);
            $result = mysqli_stmt_get_result($userStmt);
            
            if ($row = mysqli_fetch_assoc($result)) {
                // Create full session
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['email'] = $row['email'];
                $_SESSION['first_name'] = $row['first_name'];
                $_SESSION['last_name'] = $row['last_name'];
                $_SESSION['role'] = $row['role'];
                $_SESSION['staff_id'] = $row['staff_id'];
                $_SESSION['department_id'] = $row['department_id'];
                $_SESSION['department_name'] = $row['department_name'];
                $_SESSION['signin'] = true;
                
                // Remove temporary user ID
                unset($_SESSION['temp_user_id']);
                
                // Regenerate session ID for security
                session_regenerate_id(true);
            }
        }
        
        echo json_encode([
            "status" => "success",
            "message" => "Password updated successfully"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update password: " . mysqli_error($conn)
        ]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}

mysqli_close($conn);
?>