<?php
require '../../session_start.php';
include_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? null;
    $first_name = $_POST['firstName'] ?? null;
    $last_name = $_POST['lastName'] ?? null;
    $profile_pic = null;

    // Fetch the current profile picture, created_at, and updated_at timestamps
    $stmt = $conn->prepare("SELECT profile_pic, created_at, updated_at FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $current_profile_pic = $user['profile_pic'];
    $created_at = $user['created_at'];
    $updated_at = $user['updated_at'];
    $stmt->close();

    // Allow updates if it's the first time (created_at == updated_at)
    if ($created_at !== $updated_at && (time() - strtotime($updated_at)) < 604800) {
        echo "You can update your profile only once every 7 days. Please try again later.";
        exit;
    }

    // Check if a new file is uploaded
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file_size = $_FILES['avatar']['size'];
        $max_size = 5 * 1024 * 1024; // 5MB limit

        if ($file_size > $max_size) {
            echo "Error: Profile picture is too large. Maximum allowed size is 5MB.";
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

    // Update database with new values and update timestamp
    $sql = "UPDATE users 
            SET first_name = ?, last_name = ?, profile_pic = ?, updated_at = NOW() 
            WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $first_name, $last_name, $profile_pic, $user_id);

    if ($stmt->execute()) {
        echo "Profile updated successfully.";
    } else {
        echo "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
