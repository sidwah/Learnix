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
    'message' => 'Failed to update instructor status'
];

// Get and validate input
$instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 0;
$new_status = isset($_POST['status']) ? $_POST['status'] : '';
$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';

// Validate inputs
if ($instructor_id <= 0) {
    $response['message'] = 'Invalid instructor ID';
    echo json_encode($response);
    exit;
}

// Check for valid status values
if (!in_array($new_status, ['active', 'inactive', 'suspended', 'banned'])) {
    $response['message'] = 'Invalid status';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get current instructor details
    $query = "SELECT i.instructor_id, u.user_id, u.email, u.status, u.first_name, u.last_name
              FROM instructors i
              JOIN users u ON i.user_id = u.user_id
              WHERE i.instructor_id = ? AND i.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Instructor not found');
    }
    
    $instructor = $result->fetch_assoc();
    $current_status = $instructor['status'];
    
    // Start transaction
    $conn->begin_transaction();
    
    // Update user status
    $update_user = "UPDATE users SET status = ? WHERE user_id = ?";
    $stmt = $conn->prepare($update_user);
    $stmt->bind_param("si", $new_status, $instructor['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update user status: ' . $stmt->error);
    }
    
    // Log the activity
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    $log_details = json_encode([
        'instructor_id' => $instructor_id,
        'instructor_name' => $instructor['first_name'] . ' ' . $instructor['last_name'],
        'previous_status' => $current_status,
        'new_status' => $new_status
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "instructor_status_changed";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Create in-app notification for the instructor
    $notification_title = "Account Status Updated";
    $notification_message = "Your account status has been changed to: " . ucfirst($new_status);
    
    if ($new_status === 'active') {
        $notification_message .= ". You now have full access to the system.";
    } else if ($new_status === 'suspended') {
        $notification_message .= ". Your access has been temporarily suspended. Please contact the administrator for more information.";
    } else if ($new_status === 'banned') {
        $notification_message .= ". Your account has been banned.";
    } else if ($new_status === 'inactive') {
        $notification_message .= ". Your account has been deactivated.";
    }
    
    $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                          VALUES (?, 'status_change', ?, ?, 'instructor')";
    $stmt = $conn->prepare($notification_query);
    $stmt->bind_param("iss", $instructor['user_id'], $notification_title, $notification_message);
    $stmt->execute();
    
    // Send email notification
    $to = $instructor['email'];
    $subject = "Learnix - Account Status Update";
    
    // Determine badge color
    $badge_color = '#28a745'; // Green for active
    if ($new_status === 'inactive') {
        $badge_color = '#6c757d'; // Gray for inactive
    } else if ($new_status === 'suspended' || $new_status === 'banned') {
        $badge_color = '#dc3545'; // Red for suspended and banned
    }
    
    // HTML email message
    $message = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Account Status Update</title>
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
            
            .status-badge {
                display: inline-block;
                padding: 6px 12px;
                border-radius: 4px;
                font-weight: 500;
                font-size: 14px;
                color: #ffffff;
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Learnix</h1>
            </div>
            
            <div class="email-body">
                <h2>Account Status Update</h2>
                
                <p>Hello ' . htmlspecialchars($instructor['first_name']) . ',</p>
                
                <p>This is to inform you that your instructor account status has been updated.</p>
                
                <p>Your account is now: <span class="status-badge" style="background-color: ' . $badge_color . ';">' . ucfirst($new_status) . '</span></p>
                ';
                
    if ($new_status === 'active') {
        $message .= '<p>You now have full access to the Learnix instructor platform and can log in normally.</p>';
    } else if ($new_status === 'banned') {
        $message .= '<p>Your account has been banned. Please contact the administrator for more information.</p>';
    } else if ($new_status === 'suspended') {
        $message .= '<p>Your account has been suspended. Please contact the administrator for more information.</p>';
    } else if ($new_status === 'inactive') {
        $message .= '<p>Your account has been deactivated. Please contact the administrator for more information.</p>';
    }
    
    $message .= '
                <p>If you have any questions, please contact the system administrator.</p>
            </div>
            
            <div class="email-footer">
                <p>© 2025 Learnix. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
    
    // Plain text alternative
    $text_message = "Hello " . $instructor['first_name'] . ",\n\n" .
                    "This is to inform you that your instructor account status has been updated.\n\n" .
                    "Your account is now: " . ucfirst($new_status) . "\n\n";
                    
    if ($new_status === 'active') {
        $text_message .= "You now have full access to the Learnix instructor platform and can log in normally.\n\n";
    } else if ($new_status === 'banned') {
        $text_message .= "Your account has been banned. Please contact the administrator for more information.\n\n";
    } else if ($new_status === 'suspended') {
        $text_message .= "Your account has been suspended. Please contact the administrator for more information.\n\n";
    } else if ($new_status === 'inactive') {
        $text_message .= "Your account has been deactivated. Please contact the administrator for more information.\n\n";
    }
    
    $text_message .= "If you have any questions, please contact the system administrator.\n\n" .
                     "Learnix";
    
    // Set email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
    
    // Send email
    mail($to, $subject, $message, $headers);
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Instructor status updated to ' . ucfirst($new_status) . ' successfully'
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