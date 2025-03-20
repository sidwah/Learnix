<?php
require '../../session_start.php';
include_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $first_name = $_POST['firstName'] ?? null;
    $last_name = $_POST['lastName'] ?? null;
    $profile_pic = null;
    
    // Define the section name and update limit
    $section = 'profile_update';
    $update_limit = 604800; // 7 days in seconds
    
    // Check if this update is allowed based on time since last update
    $query = "SELECT last_updated FROM profile_section_updates 
              WHERE user_id = ? AND section = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $section);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $section_data = $result->fetch_assoc();
        $time_since_update = time() - strtotime($section_data['last_updated']);
        
        if ($time_since_update < $update_limit) {
            $time_remaining = $update_limit - $time_since_update;
            $days_remaining = ceil($time_remaining / 86400); // Convert to days
            
            echo "You can update your profile only once every 7 days. Please try again in {$days_remaining} day(s).";
            exit;
        }
    }
    
    // Fetch the current profile picture
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $current_profile_pic = $user['profile_pic'];
    $stmt->close();
    
    // Check if a file is uploaded
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_size = $_FILES['avatar']['size'];
        $max_size = 24 * 1024 * 1024; // 24MB limit
        
        // Check file size before processing
        if ($file_size > $max_size) {
            echo "Error: Profile picture is too large. Maximum allowed size is 24MB.";
            exit;
        }
        
        $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $email = $_SESSION['email'];
        $new_file_name = $email . '_profile.' . $file_extension;
        $upload_dir = '../../../uploads/profile/';
        $file_path = $upload_dir . $new_file_name;
        
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
            $profile_pic = $new_file_name;
        } else {
            echo "Failed to upload profile picture.";
            exit;
        }
    } else {
        // Keep the existing profile picture if no new file is uploaded
        $profile_pic = $current_profile_pic;
    }
    
    // Update database with new values
    $sql = "UPDATE users 
            SET first_name = ?, last_name = ?, profile_pic = ? 
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $first_name, $last_name, $profile_pic, $user_id);
    
    if ($stmt->execute()) {
        // Record the section update time
        $query = "
            INSERT INTO profile_section_updates (user_id, section, last_updated) 
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE last_updated = NOW()
        ";
        $update_stmt = $conn->prepare($query);
        $update_stmt->bind_param("is", $user_id, $section);
        $update_stmt->execute();
        $update_stmt->close();
        
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}