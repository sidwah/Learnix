<?php
/**
 * Announcement Targeting
 * 
 * This file contains functions for determining the target recipients
 * of announcements in the Learnix LMS.
 * 
 * @package Learnix
 * @subpackage Announcements
 */

require_once __DIR__ . '/../../config.php';

/**
 * Gets the list of recipient user IDs for an announcement
 * 
 * @param int $announcement_id The announcement ID
 * @return array Array of user IDs that should receive the announcement
 */
function getAnnouncementRecipients($announcement_id) {
    global $conn;
    
    // Get announcement details
    $stmt = $conn->prepare("
        SELECT course_id, is_system_wide, target_roles 
        FROM course_announcements 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return [];
    }
    
    $announcement = $result->fetch_assoc();
    $stmt->close();
    
    // Get target groups if any
    $stmt = $conn->prepare("
        SELECT target_type, target_id 
        FROM announcement_target_groups 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $groupResult = $stmt->get_result();
    $targetGroups = [];
    
    while ($group = $groupResult->fetch_assoc()) {
        $targetGroups[] = $group;
    }
    $stmt->close();
    
    $recipients = [];
    
    // System-wide announcement handling
    if ($announcement['is_system_wide']) {
        // Filter by roles if specified
        if (!empty($announcement['target_roles'])) {
            $roles = explode(',', $announcement['target_roles']);
            $placeholders = str_repeat('?,', count($roles) - 1) . '?';
            
            $query = "SELECT user_id FROM users WHERE role IN ($placeholders) AND status = 'active'";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($roles)), ...$roles);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($user = $result->fetch_assoc()) {
                $recipients[] = $user['user_id'];
            }
            $stmt->close();
        } else {
            // All active users
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE status = 'active'");
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($user = $result->fetch_assoc()) {
                $recipients[] = $user['user_id'];
            }
            $stmt->close();
        }
    } 
    // Course-specific announcement
    elseif (!empty($announcement['course_id'])) {
        // Get all enrolled students in the course
        $stmt = $conn->prepare("
            SELECT user_id 
            FROM enrollments 
            WHERE course_id = ? AND status = 'Active'
        ");
        $stmt->bind_param("i", $announcement['course_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($enrollment = $result->fetch_assoc()) {
            $recipients[] = $enrollment['user_id'];
        }
        $stmt->close();
        
        // Include course instructor
        $stmt = $conn->prepare("
            SELECT i.user_id 
            FROM instructors i
            JOIN courses c ON i.instructor_id = c.instructor_id
            WHERE c.course_id = ?
        ");
        $stmt->bind_param("i", $announcement['course_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($instructor = $result->fetch_assoc()) {
            $recipients[] = $instructor['user_id'];
        }
        $stmt->close();
    }
    
    // Process specific target groups
    if (!empty($targetGroups)) {
        $groupRecipients = [];
        
        foreach ($targetGroups as $group) {
            switch ($group['target_type']) {
                case 'Course':
                    // All users enrolled in a specific course
                    $stmt = $conn->prepare("
                        SELECT user_id 
                        FROM enrollments 
                        WHERE course_id = ? AND status = 'Active'
                    ");
                    $stmt->bind_param("i", $group['target_id']);
                    break;
                    
                case 'Section':
                    // All users with progress in a specific section
                    $stmt = $conn->prepare("
                        SELECT DISTINCT e.user_id
                        FROM enrollments e
                        JOIN progress p ON e.enrollment_id = p.enrollment_id
                        JOIN section_topics t ON p.topic_id = t.topic_id
                        WHERE t.section_id = ? AND e.status = 'Active'
                    ");
                    $stmt->bind_param("i", $group['target_id']);
                    break;
                    
                case 'Group':
                    // All users in a study group
                    $stmt = $conn->prepare("
                        SELECT user_id 
                        FROM study_group_members 
                        WHERE group_id = ?
                    ");
                    $stmt->bind_param("i", $group['target_id']);
                    break;
                    
                case 'Custom':
                    // Custom logic based on target_id can be implemented here
                    continue 2; // Skip to next iteration
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($user = $result->fetch_assoc()) {
                $groupRecipients[] = $user['user_id'];
            }
            $stmt->close();
        }
        
        // If we have general recipients AND group recipients,
        // we only want users who are in BOTH (intersection)
        if (!empty($recipients) && !empty($groupRecipients)) {
            $recipients = array_intersect($recipients, $groupRecipients);
        }
        // If we only have group recipients, use those
        elseif (empty($recipients) && !empty($groupRecipients)) {
            $recipients = $groupRecipients;
        }
        // If no recipients after filtering, something went wrong
        elseif (empty($recipients)) {
            // Log an error or handle this case
            error_log("No recipients found for announcement ID: $announcement_id with targeting");
        }
    }
    
    // Remove duplicates and return
    return array_unique($recipients);
}

/**
 * Checks if a user should receive a specific announcement
 * 
 * @param int $announcement_id The announcement ID
 * @param int $user_id The user ID
 * @return bool True if the user should receive the announcement
 */
function shouldUserReceiveAnnouncement($announcement_id, $user_id) {
    $recipients = getAnnouncementRecipients($announcement_id);
    return in_array($user_id, $recipients);
}

/**
 * Gets announcements for a specific user
 * 
 * @param int $user_id The user ID
 * @param array $filters Additional filters
 * @param int $page Page number
 * @param int $limit Items per page
 * @return array List of announcements for the user
 */
function getUserAnnouncements($user_id, $filters = [], $page = 1, $limit = 20) {
    global $conn;
    
    // Get user details for role-based targeting
    $stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $userResult = $stmt->get_result();
    
    if ($userResult->num_rows === 0) {
        return ['announcements' => [], 'total' => 0, 'page' => $page, 'limit' => $limit, 'pages' => 0];
    }
    
    $user = $userResult->fetch_assoc();
    $stmt->close();
    
    // Get enrolled courses for course-specific announcements
    $enrolledCourses = [];
    $stmt = $conn->prepare("
        SELECT course_id 
        FROM enrollments 
        WHERE user_id = ? AND status = 'Active'
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $enrollmentResult = $stmt->get_result();
    
    while ($enrollment = $enrollmentResult->fetch_assoc()) {
        $enrolledCourses[] = $enrollment['course_id'];
    }
    $stmt->close();
    
    // Get instructor courses
    $instructorCourses = [];
    if ($user['role'] === 'instructor') {
        $stmt = $conn->prepare("
            SELECT c.course_id 
            FROM courses c
            JOIN instructors i ON c.instructor_id = i.instructor_id
            WHERE i.user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $instructorResult = $stmt->get_result();
        
        while ($course = $instructorResult->fetch_assoc()) {
            $instructorCourses[] = $course['course_id'];
        }
        $stmt->close();
    }
    
    // Combine enrolled and instructor courses
    $userCourses = array_unique(array_merge($enrolledCourses, $instructorCourses));
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Build the query
    $query = "
        SELECT DISTINCT a.*, 
               u.first_name, u.last_name,
               CASE WHEN dl.read_at IS NOT NULL THEN 1 ELSE 0 END as is_read
        FROM course_announcements a
        LEFT JOIN users u ON a.created_by = u.user_id
        LEFT JOIN announcement_delivery_logs dl ON a.announcement_id = dl.announcement_id AND dl.user_id = ?
        WHERE a.status = 'Published'
        AND (
            -- System-wide announcements for all users
            (a.is_system_wide = 1 AND (a.target_roles IS NULL OR FIND_IN_SET(?, a.target_roles) > 0))
            
            -- Course-specific announcements for enrolled courses
            OR (a.is_system_wide = 0 AND a.course_id IN (
    ";
    
    $params = [$user_id, $user['role']];
    $types = "is";
    
    // Add course placeholders
    if (!empty($userCourses)) {
        $placeholders = str_repeat('?,', count($userCourses) - 1) . '?';
        $query .= $placeholders . "))";
        
        foreach ($userCourses as $courseId) {
            $params[] = $courseId;
            $types .= "i";
        }
    } else {
        $query .= "-1))"; // No courses, so use an impossible ID
    }
    
    // Check for announcements targeting specific groups user is in
    $query .= "
            -- Targeted group announcements
            OR a.announcement_id IN (
                SELECT DISTINCT atg.announcement_id
                FROM announcement_target_groups atg
                WHERE 
                    -- User is in a targeted study group
                    (atg.target_type = 'Group' AND atg.target_id IN (
                        SELECT group_id FROM study_group_members WHERE user_id = ?
                    ))
                    
                    -- User has activity in a targeted section
                    OR (atg.target_type = 'Section' AND atg.target_id IN (
                        SELECT DISTINCT t.section_id
                        FROM progress p
                        JOIN enrollments e ON p.enrollment_id = e.enrollment_id
                        JOIN section_topics t ON p.topic_id = t.topic_id
                        WHERE e.user_id = ?
                    ))
            )
    ";
    
    $params[] = $user_id;
    $params[] = $user_id;
    $types .= "ii";
    
    // Apply additional filters
    if (!empty($filters['is_read'])) {
        if ($filters['is_read'] == 1) {
            $query .= " AND dl.read_at IS NOT NULL";
        } else {
            $query .= " AND (dl.read_at IS NULL OR dl.delivery_id IS NULL)";
        }
    }
    
    if (!empty($filters['course_id'])) {
        $query .= " AND (a.course_id = ? OR a.is_system_wide = 1)";
        $params[] = $filters['course_id'];
        $types .= "i";
    }
    
    if (!empty($filters['importance'])) {
        $query .= " AND a.importance = ?";
        $params[] = $filters['importance'];
        $types .= "s";
    }
    
    if (!empty($filters['search'])) {
        $searchTerm = "%" . $filters['search'] . "%";
        $query .= " AND (a.title LIKE ? OR a.content LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    if (!empty($filters['from_date'])) {
        $query .= " AND a.created_at >= ?";
        $params[] = $filters['from_date'];
        $types .= "s";
    }
    
    if (!empty($filters['to_date'])) {
        $query .= " AND a.created_at <= ?";
        $params[] = $filters['to_date'];
        $types .= "s";
    }
    
    // Don't show expired announcements
    $query .= " AND (a.expires_at IS NULL OR a.expires_at > NOW())";
    
    // Add ordering
    $query .= " ORDER BY a.is_pinned DESC, a.created_at DESC";
    
    // Add pagination
    $query .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    // Execute query
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();
    
    // Get total count for pagination
    $countQuery = str_replace("SELECT DISTINCT a.*, u.first_name, u.last_name, CASE WHEN dl.read_at IS NOT NULL THEN 1 ELSE 0 END as is_read", "SELECT COUNT(DISTINCT a.announcement_id) as total", $query);
    $countQuery = preg_replace('/LIMIT \?, \?/', '', $countQuery);
    
    $stmt = $conn->prepare($countQuery);
    // Remove the last two params (offset and limit)
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    $stmt->close();
    
    return [
        'announcements' => $announcements,
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'pages' => ceil($totalCount / $limit)
    ];
}

/**
 * Gets unread announcement count for a user
 * 
 * @param int $user_id The user ID
 * @return int Number of unread announcements
 */
function getUnreadAnnouncementCount($user_id) {
    $result = getUserAnnouncements($user_id, ['is_read' => 0], 1, 1);
    return $result['total'];
}

?>