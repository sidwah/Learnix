<?php
// backend/department/add_instructor.php
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

// If the department ID is not in the session, return an error
if (!$departmentId) {
    http_response_code(403);
    exit(json_encode(['status' => 'error', 'message' => 'Department information not found. Please try again.']));
}

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
    
    // Capture input values
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email)) {
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'First name, last name, and email are required.']));
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        exit(json_encode(['status' => 'error', 'message' => 'Invalid email address.']));
    }

    // Database connection
    $conn = new mysqli($host, $username, $password, $db_name);
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    }

    try {
        // Check if email already exists for any role
        $stmt = $conn->prepare("SELECT user_id, role, is_verified FROM users WHERE LOWER(email) = LOWER(?)");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // Check if this is an existing instructor
            if ($user['role'] === 'instructor') {
                // Check if this instructor is already associated with this department
                $stmt = $conn->prepare("SELECT di.id FROM department_instructors di 
                                        INNER JOIN instructors i ON di.instructor_id = i.instructor_id
                                        WHERE i.user_id = ? AND di.department_id = ? 
                                        AND di.deleted_at IS NULL");
                $stmt->bind_param("ii", $user['user_id'], $departmentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Already associated with this department
                    $stmt->close();
                    http_response_code(400);
                    exit(json_encode([
                        'status' => 'error', 
                        'message' => 'This instructor is already associated with your department.',
                        'code' => 'instructor_exists'
                    ]));
                }
                $stmt->close();
                
                // If instructor exists but is not associated with this department, we can add them
                $stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
                $stmt->bind_param("i", $user['user_id']);
                $stmt->execute();
                $stmt->bind_result($instructorId);
                $stmt->fetch();
                $stmt->close();
                
                // Associate with this department
                $stmt = $conn->prepare("INSERT INTO department_instructors (department_id, instructor_id, added_by, status) 
                                        VALUES (?, ?, ?, 'active')");
                $stmt->bind_param("iii", $departmentId, $instructorId, $departmentHeadId);
                $stmt->execute();
                $stmt->close();
                
                // Create notification about existing instructor added to department
                $title = 'Instructor Added to Department';
                $message = "$firstName $lastName ($email) has been added to your department as an instructor.";
                createNotification($departmentHeadId, 'instructor_added', $title, $message, $instructorId, 'instructor');
                
                // Notify department secretaries
                $title = 'New Instructor Added';
                $message = "Department head has added $firstName $lastName ($email) to the department as an instructor.";
                notifyUsersByRole('department_secretary', 'instructor_added', $title, $message, $departmentId, $instructorId, 'instructor');
                
                http_response_code(200);
                exit(json_encode(['status' => 'success', 'message' => 'Existing instructor added to your department.']));
            } else {
                // User exists but is not an instructor
                http_response_code(400);
                exit(json_encode(['status' => 'error', 'message' => 'Email is already registered with a different role.']));
            }
        }
        
        // Generate a temporary password for the invitation
        $tempPassword = generateRandomPassword(12);
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);
        
        // Begin transaction for data integrity
        $conn->begin_transaction();
        
        // Store the invitation in the instructor_invitations table
        $expiryTime = date('Y-m-d H:i:s', strtotime('+48 hours'));
        
        try {
            $stmt = $conn->prepare("INSERT INTO instructor_invitations 
                                    (email, temp_password_hash, department_id, invited_by, expiry_time, notes) 
                                    VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssiss", $email, $passwordHash, $departmentId, $departmentHeadId, $expiryTime, $notes);
            $stmt->execute();
            $invitationId = $stmt->insert_id;
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // Check if this is a duplicate entry error
            if ($e->getCode() == 1062) { // MySQL error code for duplicate entry
                // Check if it's an unused invitation that hasn't expired yet
                $stmt = $conn->prepare("SELECT id, expiry_time FROM instructor_invitations 
                                        WHERE email = ? AND department_id = ? AND is_used = 0");
                $stmt->bind_param("si", $email, $departmentId);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $existingInvitation = $result->fetch_assoc();
                    $stmt->close();
                    
                    // Check if the invitation is still valid
                    if (strtotime($existingInvitation['expiry_time']) > time()) {
                        $conn->rollback();
                        http_response_code(400);
                        exit(json_encode([
                            'status' => 'error', 
                            'message' => 'An invitation has already been sent to this email address and is still valid. You can resend it from the pending invitations section.',
                            'code' => 'duplicate_invitation'
                        ]));
                    } else {
                        // Invitation exists but has expired, update it
                        $stmt = $conn->prepare("UPDATE instructor_invitations 
                                               SET temp_password_hash = ?, invited_by = ?, expiry_time = ?, notes = ? 
                                               WHERE id = ?");
                        $stmt->bind_param("sissi", $passwordHash, $departmentHeadId, $expiryTime, $notes, $existingInvitation['id']);
                        $stmt->execute();
                        $invitationId = $existingInvitation['id'];
                        $stmt->close();
                    }
                } else {
                    // This should not happen based on the error, but just in case
                    $stmt->close();
                    $conn->rollback();
                    http_response_code(500);
                    exit(json_encode(['status' => 'error', 'message' => 'An error occurred while processing your request.']));
                }
            } else {
                // For other SQL errors
                throw $e;
            }
        }
        
        // Send invitation email
        if (sendInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentId, $notes)) {
            // If email sent successfully, commit transaction
            $conn->commit();
            
            // Create notification about the invitation
            notifyAboutInstructorInvitation($invitationId, $email, $firstName, $lastName, $departmentId, $departmentHeadId);
            
            http_response_code(200);
            exit(json_encode([
                'status' => 'success', 
                'message' => 'Invitation sent successfully',
                'data' => [
                    'email' => $email,
                    'firstName' => $firstName,
                    'lastName' => $lastName,
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
        error_log("Error in add_instructor.php: " . $e->getMessage());
        http_response_code(500);
        exit(json_encode(['status' => 'error', 'message' => 'Server error. Please try again later.']));
    } finally {
        $conn->close();
    }
}

// Function to generate a random password
function generateRandomPassword($length = 10) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+';
    $password = '';
    $max = strlen($chars) - 1;
    
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[rand(0, $max)];
    }
    
    return $password;
}

// Function to send invitation email
function sendInvitationEmail($email, $firstName, $lastName, $tempPassword, $departmentId, $notes = '') {
    global $conn, $site_name, $base_url, $departmentName;
    
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
        $mail->Subject = 'Learnix Instructor Invitation';
        
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
            
            .notes-container {
                background-color: #f5f7fa;
                border-radius: 6px;
                padding: 15px;
                margin: 20px 0;
                border-left: 4px solid #3a66db;
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
                <h2>Instructor Invitation</h2>
                
                <p>Hello ' . $firstName . ' ' . $lastName . ',</p>
                
                <p>You have been invited to join Learnix as an instructor in the <strong>' . $departmentName . '</strong> department. To accept this invitation, please use the credentials below to log in:</p>
                
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
                
                ' . (!empty($notes) ? '<div class="notes-container">
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

You have been invited to join Learnix as an instructor in the {$departmentName} department.

Your login details:
Email: {$email}
Temporary Password: {$tempPassword}
Login URL: {$loginUrl}

" . (!empty($notes) ? "Additional Notes:
{$notes}

" : "") . "This invitation will expire in 48 hours. Please log in and set up your account before the invitation expires.

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
?>