<?php
// Path: ajax/department/notification_helper.php

/**
 * Sends notification for course review actions
 * 
 * @param mysqli $conn Database connection
 * @param array $data Notification data (type, user_id, course_id, message, etc.)
 * @return bool Success status
 */
function sendCourseReviewNotification($conn, $data) {
    // Create in-app notification
    $success = createInAppNotification($conn, $data);
    
    // Send email notification
    if ($success) {
        sendEmailNotification($data);
    }
    
    return $success;
}

/**
 * Creates an in-app notification for the user
 * 
 * @param mysqli $conn Database connection
 * @param array $data Notification data
 * @return bool Success status
 */
function createInAppNotification($conn, $data) {
    // Check if necessary data is provided
    if (!isset($data['user_id']) || !isset($data['type']) || !isset($data['message'])) {
        error_log("Missing required notification data");
        return false;
    }
    
    // Set related_id and related_type if course_id is provided
    $related_id = isset($data['course_id']) ? $data['course_id'] : null;
    $related_type = isset($data['course_id']) ? 'course' : null;
    
    // Create the notification
    $query = "INSERT INTO user_notifications (user_id, type, title, message, related_id, related_type, created_at) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    // Prepare title based on notification type
    $title = '';
    switch ($data['type']) {
        case 'course_approved':
            $title = 'Course Approved';
            break;
        case 'course_revision':
            $title = 'Course Revision Requested';
            break;
        case 'course_rejected':
            $title = 'Course Rejected';
            break;
        default:
            $title = 'Course Review Update';
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", 
        $data['user_id'], 
        $data['type'], 
        $title, 
        $data['message'], 
        $related_id, 
        $related_type
    );
    
    $result = $stmt->execute();
    $stmt->close();
    
    if (!$result) {
        error_log("Failed to create notification: " . $conn->error);
    }
    
    return $result;
}

/**
 * Sends an email notification
 * 
 * @param array $data Notification data
 * @return bool Success status
 */
function sendEmailNotification($data) {
    // Check if we have the required email data
    if (!isset($data['instructor_email']) || !isset($data['type'])) {
        error_log("Missing required email data");
        return false;
    }
    
    // Get email content and subject based on notification type
    list($subject, $message) = getEmailContent($data);
    
    // Set up email headers
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@learnix.com" . "\r\n";
    
    // Send the email
    $mail_sent = mail($data['instructor_email'], $subject, $message, $headers);
    
    if (!$mail_sent) {
        error_log("Failed to send email to {$data['instructor_email']}");
    }
    
    return $mail_sent;
}

/**
 * Generates the email content based on notification type
 * 
 * @param array $data Notification data
 * @return array [subject, message]
 */
function getEmailContent($data) {
    $subject = '';
    $message = '';
    
    // Get instructor name or use "Instructor" as fallback
    $instructor_name = isset($data['instructor_name']) ? $data['instructor_name'] : "Instructor";
    
    // Get course title or use "your course" as fallback
    $course_title = isset($data['course_title']) ? htmlspecialchars($data['course_title']) : "your course";
    
    // Get reviewer name or use "The department" as fallback
    $reviewer_name = isset($data['reviewer_name']) ? $data['reviewer_name'] : "The department";
    
    // Get course ID for link
    $course_id = isset($data['course_id']) ? $data['course_id'] : 0;
    
    // Get comments or use default message
    $comments = isset($data['comments']) && !empty($data['comments']) ? 
        htmlspecialchars($data['comments']) : 
        "No additional comments provided.";
    
    switch ($data['type']) {
        case 'course_approved':
            $subject = "Course Approved: $course_title";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>
                        <h2 style='color: #198754;'>Course Approved!</h2>
                        <p>Hello $instructor_name,</p>
                        <p>Good news! Your course <strong>\"$course_title\"</strong> has been approved by $reviewer_name.</p>
                        <p>You can now publish your course to make it available to students.</p>
                        
                        <div style='margin: 20px 0; padding: 15px; background-color: #e8f5e9; border-left: 4px solid #198754;'>
                            <h3 style='margin-top: 0;'>Reviewer Comments:</h3>
                            <p>$comments</p>
                        </div>
                        
                        <p>
                            <a href='http://localhost:8888/learnix/instructor/courses.php' 
                               style='background-color: #198754; color: white; padding: 10px 15px; 
                                      text-decoration: none; border-radius: 4px; display: inline-block;'>
                                View Your Course
                            </a>
                        </p>
                        
                        <p style='margin-top: 30px; font-size: 12px; color: #6c757d;'>
                            This is an automated notification from the Learnix system. Please do not reply to this email.
                        </p>
                    </div>
                </body>
                </html>
            ";
            break;
            
        case 'course_revision':
            $subject = "Revisions Requested: $course_title";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>
                        <h2 style='color: #fd7e14;'>Course Revision Requested</h2>
                        <p>Hello $instructor_name,</p>
                        <p>Your course <strong>\"$course_title\"</strong> has been reviewed by $reviewer_name, and revisions are needed before it can be approved.</p>
                        
                        <div style='margin: 20px 0; padding: 15px; background-color: #fff3cd; border-left: 4px solid #fd7e14;'>
                            <h3 style='margin-top: 0;'>Revision Details:</h3>
                            <p>$comments</p>
                        </div>
                        
                        <p>Please update your course content to address these issues and resubmit for approval.</p>
                        
                        <p>
                            <a href='http://localhost:8888/learnix/instructor/courses.php' 
                               style='background-color: #fd7e14; color: white; padding: 10px 15px; 
                                      text-decoration: none; border-radius: 4px; display: inline-block;'>
                                Edit Your Course
                            </a>
                        </p>
                        
                        <p style='margin-top: 30px; font-size: 12px; color: #6c757d;'>
                            This is an automated notification from the Learnix system. Please do not reply to this email.
                        </p>
                    </div>
                </body>
                </html>
            ";
            break;
            
        case 'course_rejected':
            $subject = "Course Rejected: $course_title";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>
                        <h2 style='color: #dc3545;'>Course Rejected</h2>
                        <p>Hello $instructor_name,</p>
                        <p>We regret to inform you that your course <strong>\"$course_title\"</strong> has been rejected by $reviewer_name.</p>
                        
                        <div style='margin: 20px 0; padding: 15px; background-color: #f8d7da; border-left: 4px solid #dc3545;'>
                            <h3 style='margin-top: 0;'>Rejection Reason:</h3>
                            <p>$comments</p>
                        </div>
                        
                        <p>If you believe this decision was made in error or you would like to discuss it further, please contact your department head.</p>
                        
                        <p style='margin-top: 30px; font-size: 12px; color: #6c757d;'>
                            This is an automated notification from the Learnix system. Please do not reply to this email.
                        </p>
                    </div>
                </body>
                </html>
            ";
            break;
            
        default:
            $subject = "Course Review Update: $course_title";
            $message = "
                <html>
                <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <div style='background-color: #f8f9fa; padding: 20px; border-radius: 5px;'>
                        <h2>Course Review Update</h2>
                        <p>Hello $instructor_name,</p>
                        <p>There has been an update regarding your course <strong>\"$course_title\"</strong>.</p>
                        <p>Please log in to your instructor dashboard to view the details.</p>
                        
                        <p>
                            <a href='http://localhost:8888/learnix/instructor/courses.php' 
                               style='background-color: #0d6efd; color: white; padding: 10px 15px; 
                                      text-decoration: none; border-radius: 4px; display: inline-block;'>
                                View Your Dashboard
                            </a>
                        </p>
                        
                        <p style='margin-top: 30px; font-size: 12px; color: #6c757d;'>
                            This is an automated notification from the Learnix system. Please do not reply to this email.
                        </p>
                    </div>
                </body>
                </html>
            ";
    }
    
    return [$subject, $message];
}
?>