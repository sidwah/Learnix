<?php
require '../session_start.php';
include_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response = ['status' => 'error', 'message' => 'Not logged in'];
    echo json_encode($response);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Fetch the current profile information
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $current_profile_pic = $user['profile_pic'];
    $stmt->close();
    
    // Define update duration limits (in seconds)
    $duration_limits = [
        'personal_info' => 86400,     // 24 hours (1 day)
        'experience' => 172800,       // 48 hours (2 days)
        'social_links' => 172800,     // 48 hours (2 days)
        'change_password' => 604800   // 7 days (1 week)
    ];
    
    // Note: The profile_section_updates table should be created manually before using this script
    
    // Check if the update is allowed based on time since last section update
    if (isset($duration_limits[$action])) {
        $query = "SELECT last_updated FROM profile_section_updates 
                  WHERE user_id = ? AND section = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $user_id, $action);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $section_data = $result->fetch_assoc();
            $time_since_update = time() - strtotime($section_data['last_updated']);
            
            if ($time_since_update < $duration_limits[$action]) {
                $time_remaining = $duration_limits[$action] - $time_since_update;
                $hours_remaining = ceil($time_remaining / 3600);
                
                $wait_message = 'You can update this information once every ';
                switch ($action) {
                    case 'personal_info':
                        $wait_message .= '24 hours';
                        break;
                    case 'experience':
                    case 'social_links':
                        $wait_message .= '48 hours';
                        break;
                    case 'change_password':
                        $wait_message .= '7 days';
                        break;
                }
                
                $wait_message .= ". Please try again in {$hours_remaining} hour(s).";
                
                $response = ['status' => 'error', 'message' => $wait_message];
                echo json_encode($response);
                exit;
            }
        }
    }
    
    try {
        // First, check if this user is an instructor
        $query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            $instructor = mysqli_fetch_assoc($result);
            $instructor_id = $instructor['instructor_id'];
        } else {
            // This user is not an instructor yet, so create a record
            $query = "INSERT INTO instructors (user_id) VALUES (?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            
            // Get the newly created instructor_id
            $instructor_id = mysqli_insert_id($conn);
        }
        
        // Process based on form action
        switch ($action) {
            case 'personal_info':
                // Update personal information
                $first_name = trim($_POST['firstname'] ?? '');
                $last_name = trim($_POST['lastname'] ?? '');
                $mobile = trim($_POST['mobile'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $bio = trim($_POST['userbio'] ?? '');
                
                // Basic validation
                if (empty($first_name) || empty($last_name)) {
                    $response = ['status' => 'error', 'message' => 'First name and last name are required'];
                    echo json_encode($response);
                    exit;
                }
                
                // Upload profile photo if provided
                $profile_pic = $current_profile_pic;
                if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $file_size = $_FILES['avatar']['size'];
                    $max_size = 5 * 1024 * 1024; // 5MB limit
                    
                    // Check file size before processing
                    if ($file_size > $max_size) {
                        $response = ['status' => 'error', 'message' => 'Profile picture is too large. Maximum allowed size is 5MB.'];
                        echo json_encode($response);
                        exit;
                    }
                    
                    $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                    $allowed_extensions = ['jpg', 'jpeg', 'png'];
                    
                    if (!in_array(strtolower($file_extension), $allowed_extensions)) {
                        $response = ['status' => 'error', 'message' => 'Only JPG, JPEG, and PNG files are allowed.'];
                        echo json_encode($response);
                        exit;
                    }
                    
                    $email = $_SESSION['email'] ?? $user_id;
                    $new_file_name = $email . '_profile.' . $file_extension;
                    $upload_dir = '../../uploads/instructor-profile/';
                    
                    // Create directory if it doesn't exist
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_path = $upload_dir . $new_file_name;
                    
                    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
                        $profile_pic = $new_file_name;
                    } else {
                        $response = ['status' => 'error', 'message' => 'Failed to upload profile picture. Please check file permissions.'];
                        echo json_encode($response);
                        exit;
                    }
                }
                
                // Update user table
                $query = "
                    UPDATE users SET 
                    first_name = ?, 
                    last_name = ?, 
                    phone = ?, 
                    location = ?,
                    profile_pic = ?,
                    updated_at = NOW()
                    WHERE user_id = ?
                ";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $mobile, $location, $profile_pic, $user_id);
                mysqli_stmt_execute($stmt);
                
                // Update instructor bio
                $query = "UPDATE instructors SET bio = ? WHERE instructor_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $bio, $instructor_id);
                mysqli_stmt_execute($stmt);
                
                $response = ['status' => 'success', 'message' => 'Personal information updated successfully'];
                break;
                
            case 'experience':
                // Clear existing experience entries
                $query = "DELETE FROM instructor_experience WHERE instructor_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $instructor_id);
                mysqli_stmt_execute($stmt);
                
                // Add new experience entries
                if (isset($_POST['job_titles']) && is_array($_POST['job_titles'])) {
                    $jobTitles = $_POST['job_titles'];
                    $companyNames = $_POST['company_names'] ?? [];
                    $yearsWorked = $_POST['years_worked'] ?? [];
                    $jobDescriptions = $_POST['job_descriptions'] ?? [];
                    
                    // Check if at least one valid entry exists
                    $hasValidEntry = false;
                    foreach ($jobTitles as $title) {
                        if (!empty($title)) {
                            $hasValidEntry = true;
                            break;
                        }
                    }
                    
                    if (!$hasValidEntry) {
                        $response = ['status' => 'error', 'message' => 'At least one job title is required'];
                        echo json_encode($response);
                        exit;
                    }
                    
                    $query = "
                        INSERT INTO instructor_experience 
                        (instructor_id, job_title, company_name, years_worked, job_description) 
                        VALUES (?, ?, ?, ?, ?)
                    ";
                    
                    foreach ($jobTitles as $index => $title) {
                        if (!empty($title)) {
                            $company = $companyNames[$index] ?? '';
                            $years = $yearsWorked[$index] ?? '';
                            $description = $jobDescriptions[$index] ?? '';
                            
                            $stmt = mysqli_prepare($conn, $query);
                            mysqli_stmt_bind_param($stmt, "issss", $instructor_id, $title, $company, $years, $description);
                            mysqli_stmt_execute($stmt);
                        }
                    }
                }
                
                $response = ['status' => 'success', 'message' => 'Experience information updated successfully'];
                break;
                
            case 'social_links':
                // Check if entry exists
                $query = "SELECT social_id FROM instructor_social_links WHERE instructor_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $instructor_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                
                $facebook = trim($_POST['social-fb'] ?? '');
                $twitter = trim($_POST['social-tw'] ?? '');
                $instagram = trim($_POST['social-ig'] ?? '');
                $linkedin = trim($_POST['social-li'] ?? '');
                $github = trim($_POST['social-gh'] ?? '');
                
                // Validate URLs if provided
                $urlFields = [
                    'Facebook' => $facebook,
                    'Twitter' => $twitter,
                    'Instagram' => $instagram,
                    'LinkedIn' => $linkedin,
                    'GitHub' => $github
                ];
                
                foreach ($urlFields as $platform => $url) {
                    if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                        $response = ['status' => 'error', 'message' => "Invalid URL for {$platform}. Please enter a valid URL including http:// or https://"];
                        echo json_encode($response);
                        exit;
                    }
                }
                
                if (mysqli_num_rows($result) > 0) {
                    // Update existing record
                    $query = "
                        UPDATE instructor_social_links SET 
                        facebook = ?, 
                        twitter = ?, 
                        instagram = ?, 
                        linkedin = ?, 
                        github = ? 
                        WHERE instructor_id = ?
                    ";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "sssssi", $facebook, $twitter, $instagram, $linkedin, $github, $instructor_id);
                } else {
                    // Insert new record
                    $query = "
                        INSERT INTO instructor_social_links 
                        (instructor_id, facebook, twitter, instagram, linkedin, github) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "isssss", $instructor_id, $facebook, $twitter, $instagram, $linkedin, $github);
                }
                
                mysqli_stmt_execute($stmt);
                
                $response = ['status' => 'success', 'message' => 'Social links updated successfully'];
                break;
                
            case 'change_password':
                $current_password = $_POST['currentpassword'] ?? '';
                $new_password = $_POST['newpassword'] ?? '';
                $confirm_password = $_POST['confirmpassword'] ?? '';
                
                // Verify passwords match
                if ($new_password !== $confirm_password) {
                    $response = ['status' => 'error', 'message' => 'New passwords do not match'];
                    break;
                }
                
                // Validate password strength
                if (strlen($new_password) < 8) {
                    $response = ['status' => 'error', 'message' => 'Password must be at least 8 characters long'];
                    break;
                }
                
                // Get current password hash
                $query = "SELECT password_hash FROM users WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);
                
                // Verify current password
                if (!password_verify($current_password, $user['password_hash'])) {
                    $response = ['status' => 'error', 'message' => 'Current password is incorrect'];
                    break;
                }
                
                // Check if new password is the same as the old one
                if (password_verify($new_password, $user['password_hash'])) {
                    $response = ['status' => 'error', 'message' => 'New password must be different from your current password'];
                    break;
                }
                
                // Update password
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE users SET password_hash = ? WHERE user_id = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $new_hash, $user_id);
                mysqli_stmt_execute($stmt);
                
                $response = ['status' => 'success', 'message' => 'Password updated successfully'];
                break;
                
            default:
                $response = ['status' => 'error', 'message' => 'Invalid action'];
        }
        
        // Update the section's last updated timestamp
        if (isset($duration_limits[$action]) && $response['status'] === 'success') {
            $query = "
                INSERT INTO profile_section_updates (user_id, section, last_updated) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE last_updated = NOW()
            ";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "is", $user_id, $action);
            mysqli_stmt_execute($stmt);
        }
        
    } catch (Exception $e) {
        $response = ['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()];
    }
    
    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not a POST request, return an error
header('Content-Type: application/json');
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
exit;