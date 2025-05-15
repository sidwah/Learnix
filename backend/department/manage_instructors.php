<?php
// backend/department/manage_instructors.php
session_start();
require_once '../config.php';

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get user's department
$dept_query = "SELECT ds.department_id, d.name as department_name
               FROM department_staff ds 
               JOIN departments d ON ds.department_id = d.department_id
               WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
               LIMIT 1";
$dept_stmt = $conn->prepare($dept_query);
$dept_stmt->bind_param("i", $_SESSION['user_id']);
$dept_stmt->execute();
$dept_result = $dept_stmt->get_result();

if ($dept_result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Department not found']);
    exit;
}

$department = $dept_result->fetch_assoc();
$department_id = $department['department_id'];
$department_name = $department['department_name'];

// Get department head information
$head_query = "SELECT u.first_name, u.last_name, u.email
               FROM users u
               WHERE u.user_id = ?";
$head_stmt = $conn->prepare($head_query);
$head_stmt->bind_param("i", $_SESSION['user_id']);
$head_stmt->execute();
$head_result = $head_stmt->get_result();
$head_info = $head_result->fetch_assoc();

// Get action
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Function to send email
function sendEmail($recipient_email, $subject, $html_body, $plain_body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'barrock.sidwah@st.rmu.edu.gh'; // Replace with your email
        $mail->Password = 'mtltujmsmmlkkxtv'; // Replace with secure app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Sender and recipient details
        $mail->setFrom('no-reply@learnix.com', 'Learnix');
        $mail->addAddress($recipient_email);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $html_body;
        $mail->AltBody = $plain_body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Function to add notification
function addNotification($conn, $user_id, $type, $title, $message, $related_id = null, $related_type = null) {
    $notification_sql = "INSERT INTO user_notifications 
                        (user_id, type, title, message, related_id, related_type, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
    $notification_stmt = $conn->prepare($notification_sql);
    $notification_stmt->bind_param("isssss", $user_id, $type, $title, $message, $related_id, $related_type);
    return $notification_stmt->execute();
}

try {
    switch ($action) {
        case 'get_course_instructors':
            $course_id = $_GET['course_id'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id, c.title 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            // Get current instructors
            $instructors_query = "SELECT 
                                     u.user_id,
                                     u.first_name,
                                     u.last_name,
                                     u.email,
                                     u.profile_pic,
                                     i.instructor_id,
                                     ci.is_primary,
                                     ci.assigned_at
                                 FROM course_instructors ci
                                 JOIN instructors i ON ci.instructor_id = i.instructor_id
                                 JOIN users u ON i.user_id = u.user_id
                                 WHERE ci.course_id = ? AND ci.deleted_at IS NULL
                                 ORDER BY ci.is_primary DESC, u.first_name";
            
            $inst_stmt = $conn->prepare($instructors_query);
            $inst_stmt->bind_param("i", $course_id);
            $inst_stmt->execute();
            $inst_result = $inst_stmt->get_result();
            
            $instructors = [];
            while ($instructor = $inst_result->fetch_assoc()) {
                $instructors[] = $instructor;
            }
            
            echo json_encode([
                'success' => true,
                'instructors' => $instructors
            ]);
            break;
            
        case 'get_available_instructors':
            $course_id = $_GET['course_id'] ?? 0;
            
            // Get instructors that belong to the department but are not assigned to this course
            $available_query = "SELECT 
                                   u.user_id,
                                   u.first_name,
                                   u.last_name,
                                   u.email,
                                   u.profile_pic,
                                   i.instructor_id
                               FROM department_instructors di
                               JOIN instructors i ON di.instructor_id = i.instructor_id
                               JOIN users u ON i.user_id = u.user_id
                               WHERE di.department_id = ? 
                                   AND di.status = 'active' 
                                   AND di.deleted_at IS NULL
                                   AND i.instructor_id NOT IN (
                                       SELECT instructor_id 
                                       FROM course_instructors 
                                       WHERE course_id = ? AND deleted_at IS NULL
                                   )
                               ORDER BY u.first_name";
            
            $avail_stmt = $conn->prepare($available_query);
            $avail_stmt->bind_param("ii", $department_id, $course_id);
            $avail_stmt->execute();
            $avail_result = $avail_stmt->get_result();
            
            $available_instructors = [];
            while ($instructor = $avail_result->fetch_assoc()) {
                $available_instructors[] = $instructor;
            }
            
            echo json_encode([
                'success' => true,
                'instructors' => $available_instructors
            ]);
            break;
            
        case 'assign_instructor':
            $course_id = $_POST['course_id'] ?? 0;
            $instructor_id = $_POST['instructor_id'] ?? 0;
            $is_primary = $_POST['is_primary'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id, c.title 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            $course_data = $verify_result->fetch_assoc();
            $course_title = $course_data['title'];
            
            // Verify instructor belongs to department
            $inst_verify_query = "SELECT di.instructor_id, u.user_id, u.first_name, u.last_name, u.email 
                                 FROM department_instructors di
                                 JOIN instructors i ON di.instructor_id = i.instructor_id
                                 JOIN users u ON i.user_id = u.user_id
                                 WHERE di.instructor_id = ? AND di.department_id = ? 
                                     AND di.status = 'active' AND di.deleted_at IS NULL";
            $inst_verify_stmt = $conn->prepare($inst_verify_query);
            $inst_verify_stmt->bind_param("ii", $instructor_id, $department_id);
            $inst_verify_stmt->execute();
            $inst_verify_result = $inst_verify_stmt->get_result();
            
            if ($inst_verify_result->num_rows === 0) {
                throw new Exception('Instructor not found or not in department');
            }
            
            $instructor_data = $inst_verify_result->fetch_assoc();
            $instructor_user_id = $instructor_data['user_id'];
            $instructor_name = $instructor_data['first_name'] . ' ' . $instructor_data['last_name'];
            $instructor_email = $instructor_data['email'];
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // If making this instructor primary, remove primary status from others
                if ($is_primary) {
                    $remove_primary_sql = "UPDATE course_instructors 
                                          SET is_primary = 0 
                                          WHERE course_id = ? AND deleted_at IS NULL";
                    $remove_primary_stmt = $conn->prepare($remove_primary_sql);
                    $remove_primary_stmt->bind_param("i", $course_id);
                    $remove_primary_stmt->execute();
                }
                
                // Check for existing record (active or soft-deleted)
                $check_sql = "SELECT assignment_id, deleted_at 
                             FROM course_instructors 
                             WHERE course_id = ? AND instructor_id = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ii", $course_id, $instructor_id);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                $existing_record = $check_result->fetch_assoc();
                
                if ($existing_record) {
                    if ($existing_record['deleted_at'] === null) {
                        // Active record exists, update it
                        $update_sql = "UPDATE course_instructors 
                                      SET is_primary = ?, assigned_at = CURRENT_TIMESTAMP, assigned_by = ?
                                      WHERE assignment_id = ?";
                        $update_stmt = $conn->prepare($update_sql);
                        $update_stmt->bind_param("iii", $is_primary, $_SESSION['user_id'], $existing_record['assignment_id']);
                        $update_stmt->execute();
                        $action_message = $is_primary ? "made the primary instructor for" : "role updated for";
                    } else {
                        // Soft-deleted record exists, restore it
                        $restore_sql = "UPDATE course_instructors 
                                       SET is_primary = ?, assigned_at = CURRENT_TIMESTAMP, deleted_at = NULL, assigned_by = ?
                                       WHERE assignment_id = ?";
                        $restore_stmt = $conn->prepare($restore_sql);
                        $restore_stmt->bind_param("iii", $is_primary, $_SESSION['user_id'], $existing_record['assignment_id']);
                        $restore_stmt->execute();
                        $action_message = $is_primary ? "reassigned as the primary instructor for" : "reassigned to";
                    }
                } else {
                    // No record exists, insert a new one
                    $assign_sql = "INSERT INTO course_instructors 
                                  (course_id, instructor_id, assigned_by, is_primary, assigned_at)
                                  VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
                    $assign_stmt = $conn->prepare($assign_sql);
                    $assign_stmt->bind_param("iiii", $course_id, $instructor_id, $_SESSION['user_id'], $is_primary);
                    $assign_stmt->execute();
                    $action_message = $is_primary ? "assigned as the primary instructor for" : "assigned to";
                }
                
                // Commit transaction
                $conn->commit();
                
                // Add notification for the instructor
                $notification_title = "Course Assignment";
                $notification_message = "You have been {$action_message} the course \"{$course_title}\" in the {$department_name} department.";
                addNotification($conn, $instructor_user_id, 'course_assignment', $notification_title, $notification_message, $course_id, 'course');
                
                // Send email to instructor
                $subject = "Learnix - Course Assignment Notification";
                $html_body = '<!DOCTYPE html>
                <html>
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Course Assignment Notification</title>
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
                        
                        .highlight-box {
                            background-color: #f5f7fa;
                            border-radius: 6px;
                            padding: 20px;
                            margin: 24px 0;
                            border-left: 4px solid #3a66db;
                        }
                        
                        .highlight-box h3 {
                            margin-top: 0;
                            color: #3a66db;
                        }
                        
                        .button {
                            display: inline-block;
                            background-color: #3a66db;
                            color: #ffffff !important;
                            text-decoration: none;
                            padding: 12px 24px;
                            border-radius: 4px;
                            font-weight: 500;
                            margin-top: 20px;
                        }
                        
                        .support-note {
                            font-size: 14px;
                            color: #666666;
                            font-style: italic;
                            margin-top: 24px;
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
                            <h2>Course Assignment Notification</h2>
                            
                            <p>Hello ' . $instructor_name . ',</p>
                            
                            <p>You have been ' . $action_message . ' the following course:</p>
                            
                            <div class="highlight-box">
                                <h3>' . $course_title . '</h3>
                                <p><strong>Department:</strong> ' . $department_name . '</p>
                                <p><strong>Assigned by:</strong> ' . $head_info['first_name'] . ' ' . $head_info['last_name'] . '</p>
                                <p><strong>Role:</strong> ' . ($is_primary ? 'Primary Instructor' : 'Co-instructor') . '</p>
                            </div>
                            
                            <p>Please login to your Learnix account to access the course and begin managing your content.</p>
                            
                            <a href="' . $base_url . '/instructor/courses.php" class="button">Go to My Courses</a>
                            
                            <p class="support-note">If you have any questions, please contact your department head at <a href="mailto:' . $head_info['email'] . '">' . $head_info['email'] . '</a></p>
                        </div>
                        
                        <div class="email-footer">
                            <p>© 2025 Learnix. All rights reserved.</p>
                            <p>This is an automated email, please do not reply.</p>
                        </div>
                    </div>
                </body>
                </html>';
                
                $plain_body = "Hello {$instructor_name},\n\n" .
                            "You have been {$action_message} the course \"{$course_title}\" in the {$department_name} department.\n\n" .
                            "Role: " . ($is_primary ? 'Primary Instructor' : 'Co-instructor') . "\n" .
                            "Assigned by: {$head_info['first_name']} {$head_info['last_name']}\n\n" .
                            "Please login to your Learnix account to access the course and begin managing your content.\n\n" .
                            "If you have any questions, please contact your department head at {$head_info['email']}\n\n" .
                            "© 2025 Learnix. All rights reserved.";
                
                sendEmail($instructor_email, $subject, $html_body, $plain_body);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Instructor ' . ($existing_record ? 'updated/reassigned' : 'assigned') . ' successfully'
                ]);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
            break;
            
        case 'remove_instructor':
            $course_id = $_POST['course_id'] ?? 0;
            $instructor_id = $_POST['instructor_id'] ?? 0;
            
            // Verify course belongs to department
            $verify_query = "SELECT c.course_id, c.title 
                           FROM courses c
                           WHERE c.course_id = ? AND c.department_id = ? AND c.deleted_at IS NULL";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->bind_param("ii", $course_id, $department_id);
            $verify_stmt->execute();
            $verify_result = $verify_stmt->get_result();
            
            if ($verify_result->num_rows === 0) {
                throw new Exception('Course not found');
            }
            
            $course_data = $verify_result->fetch_assoc();
            $course_title = $course_data['title'];
            
            // Get instructor information
            $inst_query = "SELECT i.instructor_id, u.user_id, u.first_name, u.last_name, u.email, ci.is_primary
                          FROM course_instructors ci
                          JOIN instructors i ON ci.instructor_id = i.instructor_id
                          JOIN users u ON i.user_id = u.user_id
                          WHERE ci.course_id = ? AND ci.instructor_id = ? AND ci.deleted_at IS NULL";
            $inst_stmt = $conn->prepare($inst_query);
            $inst_stmt->bind_param("ii", $course_id, $instructor_id);
            $inst_stmt->execute();
            $inst_result = $inst_stmt->get_result();
            
            if ($inst_result->num_rows === 0) {
                throw new Exception('Instructor not found for this course');
            }
            
            $instructor_data = $inst_result->fetch_assoc();
            $instructor_user_id = $instructor_data['user_id'];
            $instructor_name = $instructor_data['first_name'] . ' ' . $instructor_data['last_name'];
            $instructor_email = $instructor_data['email'];
            $was_primary = $instructor_data['is_primary'];
            
            // Check if this is the last instructor
            $count_sql = "SELECT COUNT(*) as count FROM course_instructors 
                         WHERE course_id = ? AND deleted_at IS NULL";
            $count_stmt = $conn->prepare($count_sql);
            $count_stmt->bind_param("i", $course_id);
            $count_stmt->execute();
            $count_result = $count_stmt->get_result();
            $count_data = $count_result->fetch_assoc();
            
            if ($count_data['count'] <= 1) {
                throw new Exception('Cannot remove the last instructor from a course');
            }
            
            $conn->begin_transaction();
            
            // Soft delete the assignment
            $remove_sql = "UPDATE course_instructors 
                          SET deleted_at = CURRENT_TIMESTAMP 
                          WHERE course_id = ? AND instructor_id = ?";
            $remove_stmt = $conn->prepare($remove_sql);
            $remove_stmt->bind_param("ii", $course_id, $instructor_id);
            $remove_stmt->execute();
            
            // If this was the primary instructor, make another one primary
            if ($was_primary) {
                $make_primary_sql = "UPDATE course_instructors 
                                    SET is_primary = 1 
                                    WHERE course_id = ? AND deleted_at IS NULL 
                                    ORDER BY assigned_at ASC LIMIT 1";
                $make_primary_stmt = $conn->prepare($make_primary_sql);
                $make_primary_stmt->bind_param("i", $course_id);
                $make_primary_stmt->execute();
            }
            
            // Add notification for the instructor
            $notification_title = "Course Assignment Removed";
            $notification_message = "You have been removed from the course \"{$course_title}\" in the {$department_name} department.";
            addNotification($conn, $instructor_user_id, 'course_unassignment', $notification_title, $notification_message, $course_id, 'course');
            
            // Send email to instructor
            $subject = "Learnix - Course Assignment Removal";
            $html_body = '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Course Assignment Removal</title>
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
                        background: linear-gradient(135deg, #e74c3c 0%, #f39c12 100%);
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
                        color: #e74c3c;
                        margin-top: 0;
                        font-size: 20px;
                        font-weight: 500;
                    }
                    
                    p {
                        margin: 16px 0;
                        font-size: 15px;
                    }
                    
                    .highlight-box {
                        background-color: #fff8f8;
                        border-radius: 6px;
                        padding: 20px;
                        margin: 24px 0;
                        border-left: 4px solid #e74c3c;
                    }
                    
                    .highlight-box h3 {
                        margin-top: 0;
                        color: #e74c3c;
                    }
                    
                    .button {
                        display: inline-block;
                        background-color: #e74c3c;
                        color: #ffffff !important;
                        text-decoration: none;
                        padding: 12px 24px;
                        border-radius: 4px;
                        font-weight: 500;
                        margin-top: 20px;
                    }
                    
                    .support-note {
                        font-size: 14px;
                        color: #666666;
                        font-style: italic;
                        margin-top: 24px;
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
                        <h2>Course Assignment Removal</h2>
                        
                        <p>Hello ' . $instructor_name . ',</p>
                        
                        <p>You have been removed from the following course:</p>
                        
                        <div class="highlight-box">
                            <h3>' . $course_title . '</h3>
                            <p><strong>Department:</strong> ' . $department_name . '</p>
                            <p><strong>Action by:</strong> ' . $head_info['first_name'] . ' ' . $head_info['last_name'] . '</p>
                        </div>
                        
                        <p>You will no longer have access to manage this course content.</p>
                        
                        <a href="' . $base_url . '/instructor/courses.php" class="button">View My Courses</a>
                        
                        <p class="support-note">If you have any questions about this change, please contact your department head at <a href="mailto:' . $head_info['email'] . '">' . $head_info['email'] . '</a></p>
                    </div>
                    
                    <div class="email-footer">
                        <p>© 2025 Learnix. All rights reserved.</p>
                        <p>This is an automated email, please do not reply.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            $plain_body = "Hello {$instructor_name},\n\n" .
                        "You have been removed from the course \"{$course_title}\" in the {$department_name} department.\n\n" .
                        "Action by: {$head_info['first_name']} {$head_info['last_name']}\n\n" .
                        "You will no longer have access to manage this course content.\n\n" .
                        "If you have any questions about this change, please contact your department head at {$head_info['email']}\n\n" .
                        "© 2025 Learnix. All rights reserved.";
            
            sendEmail($instructor_email, $subject, $html_body, $plain_body);
            
            $conn->commit();
            
            echo json_encode([
                'success' => true,
                'message' => 'Instructor removed successfully'
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    if (isset($conn) && $conn->autocommit(false)) {
        $conn->rollback();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>