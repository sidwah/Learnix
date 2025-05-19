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
    'message' => 'Failed to update staff status'
];

// Get and validate input
$staff_id = isset($_POST['staff_id']) ? intval($_POST['staff_id']) : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
$user_role = isset($_POST['user_role']) ? $_POST['user_role'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';

// Validate inputs
if ($staff_id <= 0) {
    $response['message'] = 'Invalid staff ID';
    echo json_encode($response);
    exit;
}

if (!in_array($action, ['activate', 'deactivate'])) {
    $response['message'] = 'Invalid action';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Set the new status based on the action
    $new_status = ($action === 'activate') ? 'active' : 'inactive';
    
    // Get current staff details first for logging
    $query = "SELECT ds.*, u.user_id, u.email, u.first_name, u.last_name, d.name as department_name 
              FROM department_staff ds
              JOIN users u ON ds.user_id = u.user_id
              JOIN departments d ON ds.department_id = d.department_id
              WHERE ds.staff_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Staff member not found');
    }
    
    $staff = $result->fetch_assoc();
    
    // Update the staff status
    $update_query = "UPDATE department_staff SET status = ? WHERE staff_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("si", $new_status, $staff_id);
    
    if ($stmt->execute()) {
        // Log the activity
        // Use a default admin ID or get it from session if available
        $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
        
        $log_details = json_encode([
            'previous_status' => $staff['status'],
            'new_status' => $new_status,
            'staff_email' => $staff['email'],
            'department' => $staff['department_name']
        ]);
        
        $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                      VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($log_query);
        $activity_type = "department_staff_{$action}";
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
        $stmt->execute();
        
        // Create in-app notification for the staff member
        $notification_title = "Account Status Changed";
        $notification_message = "Your account has been " . ($action === 'activate' ? 'activated' : 'deactivated') . 
                               " by the administrator. Status: " . ucfirst($new_status);
        
        $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                              VALUES (?, 'status_change', ?, ?, 'department_staff')";
        $stmt = $conn->prepare($notification_query);
        $stmt->bind_param("iss", $staff['user_id'], $notification_title, $notification_message);
        $stmt->execute();
        
        // Send email notification
        $to = $staff['email'];
        $subject = "Learnix - Account Status Update";
        
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
                    background-color: ' . ($new_status === 'active' ? '#28a745' : '#ffc107') . ';
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
                    
                    <p>Hello ' . htmlspecialchars($staff['first_name']) . ',</p>
                    
                    <p>This is to inform you that your status as a ' . ($staff['role'] === 'head' ? 'Department Head' : 'Department Secretary') . ' for the ' . htmlspecialchars($staff['department_name']) . ' department has been updated.</p>
                    
                    <p>Your account is now: <span class="status-badge">' . ucfirst($new_status) . '</span></p>
                    
                    <p>'. ($new_status === 'active' ? 
                        'You now have full access to your department functions and can log in normally.' : 
                        'Your access to department functions has been temporarily suspended. Please contact the administrator for more information.') . '</p>
                    
                    <p>If you have any questions, please contact the system administrator.</p>
                </div>
                
                <div class="email-footer">
                    <p>&copy; 2025 Learnix. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ';
        
        // Plain text alternative
        $text_message = "Hello " . $staff['first_name'] . ",\n\n" .
                        "This is to inform you that your status as a " . ($staff['role'] === 'head' ? 'Department Head' : 'Department Secretary') . 
                        " for the " . $staff['department_name'] . " department has been updated.\n\n" .
                        "Your account is now: " . ucfirst($new_status) . "\n\n" .
                        ($new_status === 'active' ? 
                        "You now have full access to your department functions and can log in normally." : 
                        "Your access to department functions has been temporarily suspended. Please contact the administrator for more information.") . "\n\n" .
                        "If you have any questions, please contact the system administrator.\n\n" .
                        "Learnix";
        
        // Set email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Learnix <no-reply@learnix.com>" . "\r\n";
        
        // Send email
        mail($to, $subject, $message, $headers);
        
        // Set success response
        $response = [
            'status' => 'success',
            'message' => 'Department staff ' . ($action === 'activate' ? 'activated' : 'deactivated') . ' successfully'
        ];
    } else {
        throw new Exception('Failed to update staff status: ' . $stmt->error);
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;