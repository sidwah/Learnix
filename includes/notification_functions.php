<?php
// backend/includes/notification_functions.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/Learnix/backend/config.php');
/**
 * Create a notification in the user_notifications table
 * 
 * @param int $userId The ID of the user to receive the notification
 * @param string $type The type of notification (e.g., 'invitation_sent', 'invitation_accepted')
 * @param string $title The title of the notification
 * @param string $message The detailed message for the notification
 * @param int|null $relatedId The ID of the related entity (e.g., invitation_id, course_id)
 * @param string|null $relatedType The type of the related entity (e.g., 'invitation', 'course')
 * @return bool Whether the notification was created successfully
 */
function createNotification($userId, $type, $title, $message, $relatedId = null, $relatedType = null) {
    global $conn;
    
    // If no connection is provided, create a new one
    $localConnection = false;
    if (!isset($conn) || $conn->connect_errno) {
        global $host, $username, $password, $db_name;
        $conn = new mysqli($host, $username, $password, $db_name);
        $localConnection = true;
        
        if ($conn->connect_error) {
            error_log("Failed to connect to database in createNotification(): " . $conn->connect_error);
            return false;
        }
    }
    
    try {
        // Check if there's a type_id associated with this notification type
        $typeId = null;
        $stmt = $conn->prepare("SELECT type_id FROM notification_types WHERE type_code = ?");
        if ($stmt) {
            $stmt->bind_param("s", $type);
            $stmt->execute();
            $stmt->bind_result($typeId);
            $stmt->fetch();
            $stmt->close();
        }
        
        // Insert the notification
        $stmt = $conn->prepare("INSERT INTO user_notifications 
                              (user_id, type_id, type, title, message, related_id, related_type, is_read, created_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())");
        
        if ($stmt) {
            $stmt->bind_param("iisssss", $userId, $typeId, $type, $title, $message, $relatedId, $relatedType);
            $result = $stmt->execute();
            $stmt->close();
            
            if ($localConnection) {
                $conn->close();
            }
            
            return $result;
        } else {
            error_log("Failed to prepare statement in createNotification(): " . $conn->error);
            
            if ($localConnection) {
                $conn->close();
            }
            
            return false;
        }
    } catch (Exception $e) {
        error_log("Error in createNotification(): " . $e->getMessage());
        
        if ($localConnection) {
            $conn->close();
        }
        
        return false;
    }
}

/**
 * Create notifications for all users with a specific role
 * 
 * @param string $role The role of users to notify (e.g., 'department_head', 'instructor')
 * @param string $type The type of notification
 * @param string $title The title of the notification
 * @param string $message The detailed message for the notification
 * @param int|null $departmentId If specified, only notify users in this department
 * @param int|null $relatedId The ID of the related entity
 * @param string|null $relatedType The type of the related entity
 * @return bool Whether all notifications were created successfully
 */
function notifyUsersByRole($role, $type, $title, $message, $departmentId = null, $relatedId = null, $relatedType = null) {
    global $conn;
    
    // If no connection is provided, create a new one
    $localConnection = false;
    if (!isset($conn) || $conn->connect_errno) {
        global $host, $username, $password, $db_name;
        $conn = new mysqli($host, $username, $password, $db_name);
        $localConnection = true;
        
        if ($conn->connect_error) {
            error_log("Failed to connect to database in notifyUsersByRole(): " . $conn->connect_error);
            return false;
        }
    }
    
    try {
        // Query to get users with the specified role, optionally in a specific department
        $query = "SELECT u.user_id FROM users u";
        
        // If department filtering is needed
        if ($departmentId !== null && in_array($role, ['department_head', 'department_secretary', 'instructor'])) {
            if ($role === 'instructor') {
                $query .= " INNER JOIN instructors i ON u.user_id = i.user_id
                           INNER JOIN department_instructors di ON i.instructor_id = di.instructor_id
                           WHERE u.role = ? AND di.department_id = ? AND u.deleted_at IS NULL AND di.deleted_at IS NULL";
            } else {
                $query .= " INNER JOIN department_staff ds ON u.user_id = ds.user_id
                           WHERE u.role = ? AND ds.department_id = ? AND u.deleted_at IS NULL AND ds.deleted_at IS NULL";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("si", $role, $departmentId);
        } else {
            // Just filter by role
            $query .= " WHERE u.role = ? AND u.deleted_at IS NULL";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $role);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        $success = true;
        
        while ($row = $result->fetch_assoc()) {
            $userId = $row['user_id'];
            $notificationSuccess = createNotification($userId, $type, $title, $message, $relatedId, $relatedType);
            $success = $success && $notificationSuccess;
        }
        
        if ($localConnection) {
            $conn->close();
        }
        
        return $success;
    } catch (Exception $e) {
        error_log("Error in notifyUsersByRole(): " . $e->getMessage());
        
        if ($localConnection) {
            $conn->close();
        }
        
        return false;
    }
}

/**
 * Create a notification about an instructor invitation
 * 
 * @param int $invitationId The ID of the invitation
 * @param string $email The email of the invited instructor
 * @param string $firstName The first name of the invited instructor
 * @param string $lastName The last name of the invited instructor
 * @param int $departmentId The ID of the department
 * @param int $departmentHeadId The ID of the department head who sent the invitation
 * @return bool Whether the notification was created successfully
 */
function notifyAboutInstructorInvitation($invitationId, $email, $firstName, $lastName, $departmentId, $departmentHeadId) {
    $fullName = $firstName . ' ' . $lastName;
    
    // Notify the department head who sent the invitation
    $title = 'Instructor Invitation Sent';
    $message = "You have sent an invitation to $fullName ($email) to join your department as an instructor.";
    $success = createNotification($departmentHeadId, 'invitation_sent', $title, $message, $invitationId, 'invitation');
    
    // Notify department secretaries
    $title = 'New Instructor Invitation';
    $message = "Department head has sent an invitation to $fullName ($email) to join as an instructor.";
    $success = notifyUsersByRole('department_secretary', 'invitation_sent', $title, $message, $departmentId, $invitationId, 'invitation') && $success;
    
    return $success;
}

/**
 * Create a notification about an instructor invitation being accepted
 * 
 * @param int $instructorId The ID of the instructor who accepted
 * @param string $email The email of the instructor
 * @param string $firstName The first name of the instructor
 * @param string $lastName The last name of the instructor
 * @param int $departmentId The ID of the department
 * @param int $invitedById The ID of the user who sent the invitation
 * @return bool Whether the notification was created successfully
 */
function notifyAboutInvitationAccepted($instructorId, $email, $firstName, $lastName, $departmentId, $invitedById) {
    $fullName = $firstName . ' ' . $lastName;
    
    // Notify the person who sent the invitation
    $title = 'Instructor Invitation Accepted';
    $message = "$fullName ($email) has accepted your invitation to join as an instructor.";
    $success = createNotification($invitedById, 'invitation_accepted', $title, $message, $instructorId, 'instructor');
    
    // Notify all department heads in this department
    $title = 'New Instructor Joined';
    $message = "$fullName ($email) has joined your department as an instructor.";
    $success = notifyUsersByRole('department_head', 'invitation_accepted', $title, $message, $departmentId, $instructorId, 'instructor') && $success;
    
    // Notify department secretaries
    $success = notifyUsersByRole('department_secretary', 'invitation_accepted', $title, $message, $departmentId, $instructorId, 'instructor') && $success;
    
    return $success;
}

/**
 * Create a notification about an instructor invitation being resent
 * 
 * @param int $invitationId The ID of the invitation
 * @param string $email The email of the invited instructor
 * @param int $departmentId The ID of the department
 * @param int $departmentHeadId The ID of the department head who resent the invitation
 * @return bool Whether the notification was created successfully
 */
function notifyAboutInvitationResend($invitationId, $email, $departmentId, $departmentHeadId) {
    // Notify the department head who resent the invitation
    $title = 'Instructor Invitation Resent';
    $message = "You have resent the invitation to $email to join your department as an instructor.";
    $success = createNotification($departmentHeadId, 'invitation_resent', $title, $message, $invitationId, 'invitation');
    
    // Notify department secretaries
    $title = 'Instructor Invitation Resent';
    $message = "Department head has resent the invitation to $email to join as an instructor.";
    $success = notifyUsersByRole('department_secretary', 'invitation_resent', $title, $message, $departmentId, $invitationId, 'invitation') && $success;
    
    return $success;
}

/**
 * Create a notification about an instructor invitation being cancelled
 * 
 * @param int $invitationId The ID of the invitation
 * @param string $email The email of the invited instructor
 * @param int $departmentId The ID of the department
 * @param int $departmentHeadId The ID of the department head who cancelled the invitation
 * @return bool Whether the notification was created successfully
 */
function notifyAboutInvitationCancelled($invitationId, $email, $departmentId, $departmentHeadId) {
    // Notify the department head who cancelled the invitation
    $title = 'Instructor Invitation Cancelled';
    $message = "You have cancelled the invitation to $email to join your department as an instructor.";
    $success = createNotification($departmentHeadId, 'invitation_cancelled', $title, $message, $invitationId, 'invitation');
    
    // Notify department secretaries
    $title = 'Instructor Invitation Cancelled';
    $message = "Department head has cancelled the invitation to $email to join as an instructor.";
    $success = notifyUsersByRole('department_secretary', 'invitation_cancelled', $title, $message, $departmentId, $invitationId, 'invitation') && $success;
    
    return $success;
}
?>