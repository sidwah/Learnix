<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';


// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to add instructor'
];

// Get and validate input
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
$send_invitation = isset($_POST['send_invitation']) ? true : false;

// Validate inputs
if (empty($first_name) || empty($last_name) || empty($email)) {
    $response['message'] = 'Please fill all required fields.';
    echo json_encode($response);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Check if email already exists
    $query = "SELECT user_id FROM users WHERE email = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('This email address is already in use.');
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Generate a random password for new instructors
    $temp_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 12);
    $password_hash = password_hash($temp_password, PASSWORD_DEFAULT);
    
    // Generate a unique username
    $username_base = strtolower($first_name . '.' . $last_name);
    $username = $username_base;
    $counter = 1;
    
    $checkUsername = "SELECT user_id FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkUsername);
    
    do {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $username = $username_base . $counter;
            $counter++;
        } else {
            break;
        }
    } while (true);
    
    // Insert user
    $insertUser = "INSERT INTO users (first_name, last_name, username, email, password_hash, role, status) 
                   VALUES (?, ?, ?, ?, ?, 'instructor', ?)";
    $stmt = $conn->prepare($insertUser);
    $status = $send_invitation ? 'pending' : 'active';
    $stmt->bind_param("ssssss", $first_name, $last_name, $username, $email, $password_hash, $status);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create user account: ' . $stmt->error);
    }
    
    $user_id = $stmt->insert_id;
    
    // Insert instructor record
    $insertInstructor = "INSERT INTO instructors (user_id, bio) VALUES (?, ?)";
    $stmt = $conn->prepare($insertInstructor);
    $stmt->bind_param("is", $user_id, $bio);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create instructor profile: ' . $stmt->error);
    }
    
    $instructor_id = $stmt->insert_id;
    
    // Associate with department if provided
    if ($department_id > 0) {
        $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
        
        $insertDeptInstructor = "INSERT INTO department_instructors (department_id, instructor_id, added_by, status) 
                               VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertDeptInstructor);
        $deptStatus = $send_invitation ? 'pending' : 'active';
        $stmt->bind_param("iiis", $department_id, $instructor_id, $admin_id, $deptStatus);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to associate with department: ' . $stmt->error);
        }
    }
    
    // If sending invitation
   if ($send_invitation) {
       // Create an invitation record in instructor_invitations table
       $insertInvitation = "INSERT INTO instructor_invitations 
                          (email, first_name, last_name, temp_password_hash, department_id, invited_by, expiry_time) 
                          VALUES (?, ?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 48 HOUR))";
       $stmt = $conn->prepare($insertInvitation);
       $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
       $stmt->bind_param("ssssis", $email, $first_name, $last_name, $password_hash, $department_id, $admin_id);
       
       if (!$stmt->execute()) {
           throw new Exception('Failed to create invitation: ' . $stmt->error);
       }
       
       // Send email notification
       $to = $email;
       $subject = "Learnix - Instructor Account Invitation";
       
       // HTML email message
       $message = '
       <!DOCTYPE html>
       <html>
       <head>
           <meta charset="UTF-8">
           <meta name="viewport" content="width=device-width, initial-scale=1.0">
           <title>Instructor Account Invitation</title>
           <style>
               @import url(\'https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap\');
               
               body {
                   font-family: \'Poppins\', Arial, sans-serif;
                   line-height: 1.6;
                   color: #333333;
                   margin: 0;
                   padding: 0;
                   background-color: #f9f9f9;
               }
               
               .email-container {
                   max-width: 600px;
                   margin: 0 auto;
                   background-color: #ffffff;
                   border-radius: 8px;
                   overflow: hidden;
                   box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
               }
               
               .email-header {
                   background: linear-gradient(135deg, #3a66db 0%, #5f85e5 100%);
                   padding: 30px;
                   text-align: center;
               }
               
               .email-header h1 {
                   color: #ffffff;
                   margin: 0;
                   font-size: 24px;
                   font-weight: 600;
               }
               
               .email-body {
                   padding: 30px;
               }
               
               .email-footer {
                   background-color: #f5f5f5;
                   padding: 20px;
                   text-align: center;
                   font-size: 12px;
                   color: #666666;
               }
               
               h2 {
                   color: #3a66db;
                   margin-top: 0;
                   font-size: 20px;
                   font-weight: 500;
               }
               
               p {
                   margin: 16px 0;
                   font-size: 15px;
               }
               
               .button {
                   display: inline-block;
                   background-color: #3a66db;
                   color: #ffffff !important;
                   text-decoration: none;
                   padding: 12px 24px;
                   border-radius: 4px;
                   font-weight: 500;
                   margin: 20px 0;
               }
               
               .credentials-box {
                   background-color: #f5f7fa;
                   border-radius: 6px;
                   padding: 15px;
                   margin: 20px 0;
               }
               
               .credentials-box p {
                   margin: 5px 0;
               }
               
               .credentials-box .label {
                   font-weight: 600;
                   color: #3a66db;
               }
               
               .expiry-alert {
                   background-color: #fff8e1;
                   border-left: 4px solid #ffc107;
                   padding: 12px 15px;
                   margin: 24px 0;
                   font-size: 14px;
                   color: #856404;
               }
           </style>
       </head>
       <body>
           <div class="email-container">
               <div class="email-header">
                   <h1>Learnix</h1>
               </div>
               
               <div class="email-body">
                   <h2>Instructor Account Invitation</h2>
                   
                   <p>Hello ' . htmlspecialchars($first_name) . ',</p>
                   
                   <p>You have been invited to join Learnix as an instructor. We are excited to welcome you to our platform!</p>
                   
                   <div class="credentials-box">
                       <p><span class="label">Email:</span> ' . htmlspecialchars($email) . '</p>
                       <p><span class="label">Temporary Password:</span> ' . $temp_password . '</p>
                   </div>
                   
                   <p>Please use these credentials to log in at <a href="https://' . $_SERVER['HTTP_HOST'] . '/instructor/sign-in.php">Learnix Instructor Portal</a>. Upon first login, you will be prompted to set a new password and complete your profile.</p>
                   
                   <div class="expiry-alert">
                       <strong>⏱️ Time Sensitive:</strong> This invitation will expire in 48 hours.
                   </div>
                   
                   <p>If you have any questions or need assistance, please contact our support team.</p>
               </div>
               
               <div class="email-footer">
                   <p>&copy; 2025 Learnix. All rights reserved.</p>
               </div>
           </div>
       </body>
       </html>
       ';
       
       // Plain text alternative
       $text_message = "Hello " . $first_name . ",\n\n" .
                       "You have been invited to join Learnix as an instructor. We are excited to welcome you to our platform!\n\n" .
                       "Email: " . $email . "\n" .
                       "Temporary Password: " . $temp_password . "\n\n" .
                       "Please use these credentials to log in at Learnix Instructor Portal: https://" . $_SERVER['HTTP_HOST'] . "/instructor/sign-in.php\n" .
                       "Upon first login, you will be prompted to set a new password and complete your profile.\n\n" .
                       "This invitation will expire in 48 hours.\n\n" .
                       "If you have any questions or need assistance, please contact our support team.\n\n" .
                       "Learnix";
       
       // Set email headers
       $headers = "MIME-Version: 1.0" . "\r\n";
       $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
       $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
       
       // Send email
       mail($to, $subject, $message, $headers);
   }
   
   // Create in-app notification for instructors
   $notification_title = "Welcome to Learnix";
   $notification_message = "Welcome to Learnix as an instructor. ";
   
   if ($department_id > 0) {
       // Get department name
       $deptQuery = "SELECT name FROM departments WHERE department_id = ?";
       $stmt = $conn->prepare($deptQuery);
       $stmt->bind_param("i", $department_id);
       $stmt->execute();
       $result = $stmt->get_result();
       $dept = $result->fetch_assoc();
       
       if ($dept) {
           $notification_message .= "You have been assigned to the " . $dept['name'] . " department.";
       }
   }
   
   $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                         VALUES (?, 'welcome', ?, ?, 'instructor')";
   $stmt = $conn->prepare($notification_query);
   $stmt->bind_param("iss", $user_id, $notification_title, $notification_message);
   $stmt->execute();
   
   // Log activity
   $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
   $log_details = json_encode([
       'instructor_name' => $first_name . ' ' . $last_name,
       'instructor_email' => $email,
       'department_id' => $department_id,
       'send_invitation' => $send_invitation
   ]);
   
   $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                 VALUES (?, 'instructor_created', ?, ?)";
   $stmt = $conn->prepare($log_query);
   $activity_type = "instructor_created";
   $ip = $_SERVER['REMOTE_ADDR'];
   $stmt->bind_param("iss", $admin_id, $log_details, $ip);
   $stmt->execute();
   
   // Commit transaction
   $conn->commit();
   
   // Set success response
   $response = [
       'status' => 'success',
       'message' => 'Instructor added successfully' . ($send_invitation ? ' and invitation sent' : ''),
       'data' => [
           'instructor_id' => $instructor_id,
           'user_id' => $user_id
       ]
   ];
   
} catch (Exception $e) {
   // Rollback transaction
   $conn->rollback();
   $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;