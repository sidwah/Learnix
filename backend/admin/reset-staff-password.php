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
    'message' => 'Failed to reset password'
];

// Get and validate input
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';
$user_role = isset($_POST['user_role']) ? $_POST['user_role'] : '';

// Validate inputs
if ($user_id <= 0) {
    $response['message'] = 'Invalid user ID';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get user details first
    $query = "SELECT u.email, u.first_name, u.last_name, u.role
              FROM users u
              WHERE u.user_id = ? AND (u.role = 'department_head' OR u.role = 'department_secretary')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Department staff member not found');
    }
    
    $user = $result->fetch_assoc();
    
    // Generate a unique reset code
    $reset_code = substr(str_shuffle("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    
    // Set expiration time (24 hours from now)
    $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
    
    // Insert reset code into database
    $insert_query = "INSERT INTO password_resets (user_id, reset_code, expires_at) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $user_id, $reset_code, $expires_at);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create password reset: ' . $stmt->error);
    }
    
    // Create in-app notification for the staff member
    $notification_title = "Password Reset Initiated";
    $notification_message = "A password reset has been initiated for your account by the administrator. Please check your email for instructions.";
    
    $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                          VALUES (?, 'password_reset', ?, ?, 'department_staff')";
    $stmt = $conn->prepare($notification_query);
    $stmt->bind_param("iss", $user_id, $notification_title, $notification_message);
    $stmt->execute();
    
    // Send email with reset link
    $reset_url = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?code=" . $reset_code;
    
    $to = $user['email'];
    $subject = "Learnix - Password Reset Link";
    
    // HTML email message
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Password Reset Request</title>
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
            
            .expiry-alert {
                background-color: #fff8e1;
                border-left: 4px solid #ffc107;
                padding: 12px 15px;
                margin: 24px 0;
                font-size: 14px;
                color: #856404;
            }
            
            .code-display {
                background-color: #f5f7fa;
                border-radius: 6px;
                padding: 12px 15px;
                margin: 24px 0;
                font-family: monospace;
                font-size: 16px;
                text-align: center;
                color: #3a66db;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Learnix</h1>
            </div>
            
            <div class="email-body">
                <h2>Password Reset</h2>
                
                <p>Hello ' . htmlspecialchars($user['first_name']) . ',</p>
                
                <p>We received a request to reset your password for your department ' . strtolower($user['role'] == 'department_head' ? 'head' : 'secretary') . ' account at Learnix. To proceed with the password reset, please click the button below:</p>
                
                <div style="text-align: center;">
                    <a href="' . $reset_url . '" class="button">Reset Your Password</a>
                </div>
                
                <p>Alternatively, you can copy and paste the following URL into your browser:</p>
                
                <div class="code-display">' . $reset_url . '</div>
                
                <div class="expiry-alert">
                    <strong>⏱️ Time Sensitive:</strong> This password reset link will expire in 24 hours.
                </div>
                
                <p>If you did not request a password reset, please ignore this email or contact the administrator if you believe this is an error.</p>
            </div>
            
            <div class="email-footer">
                <p>&copy; 2025 Learnix. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Plain text alternative
    $text_message = "Hello " . $user['first_name'] . ",\n\n" .
                    "We received a request to reset your password for your department " . 
                    strtolower($user['role'] == 'department_head' ? 'head' : 'secretary') . 
                    " account at Learnix. To proceed with the password reset, please visit the following URL:\n\n" .
                    $reset_url . "\n\n" .
                    "This link will expire in 24 hours.\n\n" .
                    "If you did not request a password reset, please ignore this email or contact the administrator.\n\n" .
                    "Learnix";
    
    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
    
    // Send email
    if (!mail($to, $subject, $message, $headers)) {
        throw new Exception('Failed to send password reset email');
    }
    
    // Log the activity
    // Use a default admin ID or get it from session if available
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $log_details = json_encode([
        'user_email' => $user['email'],
        'user_name' => $user['first_name'] . ' ' . $user['last_name'],
        'role' => $user['role']
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "password_reset_initiated";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Password reset link has been sent to ' . $user['email']
    ];
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;