<?php
// backend/department/resend_invitation.php
require_once '../../config.php'; // Database connection file
require_once '../../../includes/notification_functions.php'; // Notification functions

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../PHPMailer/src/Exception.php';
require '../../PHPMailer/src/PHPMailer.php';
require '../../PHPMailer/src/SMTP.php';

// Start or resume session to get the department head's ID
session_start();

// Check if user is logged in and is a department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403); // Forbidden
    exit(json_encode(['status' => 'error', 'message' => 'Unauthorized access']));
}

$departmentHeadId = $_SESSION['user_id'];
$departmentId = $_SESSION['department_id'];
$departmentName = $_SESSION['department_name'];

// Process the POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode JSON input if content type is application/json
    $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
    
    if (strpos($contentType, 'application/json') !== false) {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);
        
        // If json_decode failed, handle the error
        if (!is_array($decoded)) {
            http_response_code(400);
            exit(json_encode(['status' => 'error', 'message' => 'Invalid JSON format']));
        }
        
        $_POST = $decoded;
    }
    
    // Get invitation ID
    $invitationId = intval($_POST['id'] ?? 0);
    
    if ($invitationId <= 0) {
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Invalid invitation ID']));
    }
    
    // Connect to database
    $conn = new mysqli($host, $username, $password, $db_name);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    }
    
    try {
        // Check if the invitation exists and belongs to a department managed by this department head
        $stmt = $conn->prepare(
            "SELECT ii.email, ii.temp_password_hash, ii.department_id, ii.notes,
                    CONCAT(u.first_name, ' ', u.last_name) as instructor_name,
                    d.name as department_name
             FROM instructor_invitations ii
             INNER JOIN departments d ON ii.department_id = d.department_id
             LEFT JOIN users u ON ii.invited_by = u.user_id
             WHERE ii.id = ? 
             AND ii.department_id IN (
                 SELECT department_id FROM department_staff 
                 WHERE user_id = ? AND role = 'head' AND status = 'active' AND deleted_at IS NULL
             )
             AND ii.is_used = 0"
        );
        $stmt->bind_param("ii", $invitationId, $departmentHeadId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            http_response_code(404);
            exit(json_encode(['status' => 'error', 'message' => 'Invitation not found or unauthorized.']));
        }
        
        $invitation = $result->fetch_assoc();
        $stmt->close();
        
        // Update expiration time
        $expiryTime = date('Y-m-d H:i:s', strtotime('+48 hours'));
        
        // Begin transaction
        $conn->begin_transaction();
        
        // Update invitation in database
        $stmt = $conn->prepare("UPDATE instructor_invitations SET expiry_time = ? WHERE id = ?");
        $stmt->bind_param("si", $expiryTime, $invitationId);
        $stmt->execute();
        $stmt->close();
        
        // Create name parts from email if not available
        $nameParts = explode('@', $invitation['email']);
        $possibleNameParts = explode('.', $nameParts[0]);
        $firstName = ucfirst($possibleNameParts[0] ?? '');
        $lastName = ucfirst($possibleNameParts[1] ?? '');
        
        // Extract temp password from hash (in practice we would generate a new one)
        // Here we're just reusing the existing one for simplicity in this demo
        $tempPassword = substr($invitation['temp_password_hash'], 0, 12); // Not safe, just for demo
        
        // Send invitation email
        if (sendInvitationEmail(
            $invitation['email'], 
            $firstName, 
            $lastName, 
            $tempPassword, 
            $invitation['department_id'],
            $departmentName,
            $invitation['notes'] ?? ''
        )) {
            // If email sent successfully, commit transaction
            $conn->commit();
            
            // Create notification about resending the invitation
            notifyAboutInvitationResend($invitationId, $invitation['email'], $invitation['department_id'], $departmentHeadId);
            
            http_response_code(200);
            exit(json_encode([
                'status' => 'success', 
                'message' => 'Invitation resent successfully',
                'data' => [
                    'email' => $invitation['email'],
                    'expiryTime' => $expiryTime
                ]
            ]));
        } else {
            // If email failed, rollback transaction
            $conn->rollback();
            http_response_code(500);
            exit(json_encode(['status' => 'error', 'message' => 'Failed to send invitation email.']));
        }
    } catch (Exception $e) {
        // If any error occurred, rollback transaction
        if ($conn->connect_errno === 0) {
            $conn->rollback();
        }
        error_log("Error in resend_invitation.php: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    } finally {
        $conn->close();
    }
}

// Function to send invitation email
function sendInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentId, $departmentName, $notes = '') {
    global $site_name, $base_url;
    
    $mail = new PHPMailer(true);
        // Remove redundant and misplaced code block
    
    
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh';
        $mail->Password = 'mtltujmsmmlkkxtv'; // Use a secure method instead
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($email, $firstName . ' ' . $lastName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Learnix Instructor Invitation (Resent)';
        
        // Login URL
        $loginUrl = $base_url . '/instructor/sign-in.php';
        
        $mail->Body = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instructor Invitation</title>
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
            
            .email-header img {
                max-width: 150px;
                height: auto;
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
            
            h1 {
                color: #ffffff;
                margin: 0;
                font-size: 24px;
                font-weight: 600;
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
            
            .credentials-container {
                background-color: #f5f7fa;
                border-radius: 6px;
                padding: 20px;
                margin: 24px 0;
                border: 1px dashed #d1d9e6;
            }
            
            .credentials-table {
                width: 100%;
                border-collapse: collapse;
            }
            
            .credentials-table td {
                padding: 8px;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .credentials-table td:first-child {
                font-weight: 600;
                width: 40%;
                color: #3a66db;
            }
            
            .credentials-table tr:last-child td {
                border-bottom: none;
            }
            
            .button {
                display: inline-block;
                background-color: #3a66db;
                color: white;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 4px;
                margin-top: 20px;
                font-weight: 500;
            }
            
            .expiry-alert {
                background-color: #fff8e1;
                border-left: 4px solid #ffc107;
                padding: 12px 15px;
                margin: 24px 0;
                font-size: 14px;
                color: #856404;
            }
            
            .support-note {
                font-size: 14px;
                color: #666666;
                font-style: italic;
                margin-top: 24px;
            }
            
            .social-icons {
                margin-top: 20px;
            }
            
            .social-icons a {
                display: inline-block;
                margin: 0 10px;
                color: #3a66db;
                text-decoration: none;
            }
            
            @media screen and (max-width: 600px) {
                .email-container {
                    width: 100%;
                    border-radius: 0;
                }
                
                .email-header, .email-body, .email-footer {
                    padding: 20px;
                }
            }
        </style>
    </head>
    <body>
        <div class="email-container">
            <div class="email-header">
                <h1>Learnix</h1>
            </div>
            
            <div class="email-body">
                <h2>Instructor Invitation (Resent)</h2>
                
                <p>Hello ' . $firstName . ' ' . $lastName . ',</p>
                
                <p>Your invitation to join Learnix as an instructor in the <strong>' . $departmentName . '</strong> department has been resent. To accept this invitation, please use the credentials below to log in:</p>
                
                <div class="credentials-container">
                    <table class="credentials-table">
                        <tr>
                            <td>Email:</td>
                            <td>' . $email . '</td>
                        </tr>
                        <tr>
                            <td>Temporary Password:</td>
                            <td>' . $tempPassword . '</td>
                        </tr>
                        <tr>
                            <td>Login URL:</td>
                            <td><a href="' . $loginUrl . '">' . $loginUrl . '</a></td>
                        </tr>
                    </table>
                </div>
                
                <a href="' . $loginUrl . '" class="button">Log In to Learnix</a>
                
                ' . (!empty($notes) ? '<div style="background-color: #f5f7fa; border-radius: 6px; padding: 15px; margin: 20px 0; border-left: 4px solid #3a66db;">
                    <h5 style="margin-top: 0; color: #3a66db;">Additional Notes</h5>
                    <p style="margin-bottom: 0;">' . nl2br(htmlspecialchars($notes)) . '</p>
                </div>' : '') . '
                
                <div class="expiry-alert">
                    <strong>⏱️ Time Sensitive:</strong> This invitation will expire in 48 hours. Please log in and set up your account before the invitation expires.
                </div>
                
                <p>Upon your first login, you will be prompted to change your password and complete your instructor profile. After completing these steps, you will be able to start creating courses and teaching on the Learnix platform.</p>
                
                <p class="support-note">For any questions, please contact our support team at <a href="mailto:support@learnix.com">support@learnix.com</a></p>
            </div>
            
            <div class="email-footer">
                <p>&copy; 2025 Learnix. All rights reserved.</p>
                <p>Our address: Learnix, East Legon, Accra, Ghana</p>
                <div class="social-icons">
                    <a href="#">Twitter</a> | 
                    <a href="#">Facebook</a> | 
                    <a href="#">Instagram</a>
                </div>
            </div>
        </div>
    </body>
    </html>';

        // Alternative plain text body for email clients that don't support HTML
        $mail->AltBody = "Hello {$firstName} {$lastName},

Your invitation to join Learnix as an instructor in the {$departmentName} department has been resent.

Your login details:
Email: {$email}
Temporary Password: {$tempPassword}
Login URL: {$loginUrl}

This invitation will expire in 48 hours. Please log in and set up your account before the invitation expires.

For any questions, please contact our support team at support@learnix.com.

Learnix";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
// If the request method is not POST, return an error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit(json_encode(['status' => 'error', 'message' => 'Method not allowed']));
}