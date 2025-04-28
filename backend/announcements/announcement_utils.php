<?php
/**
 * Announcement Utilities
 * 
 * This file contains utility functions for working with announcements
 * in the Learnix LMS.
 * 
 * @package Learnix
 * @subpackage Announcements
 */

require_once __DIR__ . '/../../config.php';

/**
 * Gets announcement statistics
 * 
 * @param int|null $course_id Optional course ID to filter by
 * @param string|null $timeframe Optional timeframe: 'week', 'month', 'year', 'all'
 * @return array Announcement statistics
 */
function getAnnouncementStatistics($course_id = null, $timeframe = 'all') {
    global $conn;
    
    // Determine date range based on timeframe
    $dateCondition = '';
    if ($timeframe !== 'all') {
        $dateCondition = "AND a.created_at >= ";
        
        switch ($timeframe) {
            case 'week':
                $dateCondition .= "DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $dateCondition .= "DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'year':
                $dateCondition .= "DATE_SUB(NOW(), INTERVAL 1 YEAR)";
                break;
            default:
                $dateCondition = ''; // Invalid timeframe, ignore condition
        }
    }
    
    // Course filter
    $courseCondition = '';
    $params = [];
    $types = '';
    
    if ($course_id !== null) {
        $courseCondition = "AND (a.course_id = ? OR a.is_system_wide = 1)";
        $params[] = $course_id;
        $types .= 'i';
    }
    
    // Base query for announcement counts
    $query = "
        SELECT 
            COUNT(*) as total_announcements,
            SUM(CASE WHEN a.importance = 'Low' THEN 1 ELSE 0 END) as low_importance,
            SUM(CASE WHEN a.importance = 'Medium' THEN 1 ELSE 0 END) as medium_importance,
            SUM(CASE WHEN a.importance = 'High' THEN 1 ELSE 0 END) as high_importance,
            SUM(CASE WHEN a.importance = 'Critical' THEN 1 ELSE 0 END) as critical_importance,
            SUM(CASE WHEN a.is_system_wide = 1 THEN 1 ELSE 0 END) as system_wide,
            SUM(CASE WHEN a.status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled,
            SUM(s.total_recipients) as total_recipients,
            SUM(s.delivery_count) as delivery_count,
            SUM(s.read_count) as read_count,
            SUM(s.interaction_count) as interaction_count
        FROM course_announcements a
        LEFT JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        WHERE 1=1
        $courseCondition
        $dateCondition
    ";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $statsResult = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    // Get announcement engagement rate
    $stats = $statsResult;
    $stats['delivery_rate'] = $stats['total_recipients'] > 0 ? 
        round(($stats['delivery_count'] / $stats['total_recipients']) * 100, 2) : 0;
    
    $stats['read_rate'] = $stats['delivery_count'] > 0 ? 
        round(($stats['read_count'] / $stats['delivery_count']) * 100, 2) : 0;
    
    $stats['interaction_rate'] = $stats['read_count'] > 0 ? 
        round(($stats['interaction_count'] / $stats['read_count']) * 100, 2) : 0;
    
    // Get top 5 most read announcements
    $topQuery = "
        SELECT a.announcement_id, a.title, a.importance, a.created_at,
               s.read_count, s.total_recipients,
               CASE WHEN s.total_recipients > 0 
                    THEN (s.read_count / s.total_recipients) * 100 
                    ELSE 0 
               END as read_percentage
        FROM course_announcements a
        JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        WHERE s.total_recipients > 0
        $courseCondition
        $dateCondition
        ORDER BY read_percentage DESC, s.read_count DESC
        LIMIT 5
    ";
    
    $topStmt = $conn->prepare($topQuery);
    if (!empty($params)) {
        $topStmt->bind_param($types, ...$params);
    }
    $topStmt->execute();
    $topResult = $topStmt->get_result();
    $topStmt->close();
    
    $stats['top_announcements'] = [];
    while ($row = $topResult->fetch_assoc()) {
        $stats['top_announcements'][] = $row;
    }
    
    // Get announcement count by day for the past 30 days
    $dayStatsQuery = "
        SELECT 
            DATE(a.created_at) as date,
            COUNT(*) as count
        FROM course_announcements a
        WHERE a.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        $courseCondition
        GROUP BY DATE(a.created_at)
        ORDER BY date ASC
    ";
    
    $dayStmt = $conn->prepare($dayStatsQuery);
    if (!empty($params)) {
        $dayStmt->bind_param($types, ...$params);
    }
    $dayStmt->execute();
    $dayResult = $dayStmt->get_result();
    $dayStmt->close();
    
    $stats['daily_counts'] = [];
    while ($row = $dayResult->fetch_assoc()) {
        $stats['daily_counts'][] = $row;
    }
    
    return $stats;
}

/**
 * Gets announcement delivery statistics for a specific announcement
 * 
 * @param int $announcement_id The announcement ID
 * @return array Delivery statistics
 */
function getAnnouncementDeliveryStats($announcement_id) {
    global $conn;
    
    $stats = [
        'delivery_channels' => [
            'Email' => ['sent' => 0, 'read' => 0],
            'In-App' => ['sent' => 0, 'read' => 0],
'Push' => ['sent' => 0, 'read' => 0],
        ],
        'interactions' => [
            'None' => 0,
            'Viewed' => 0,
            'Clicked' => 0,
            'Responded' => 0
        ],
        'hourly_reads' => []
    ];
    
    try {
        // Get delivery stats by channel
        $stmt = $conn->prepare("
            SELECT 
                delivery_channel,
                COUNT(*) as total,
                SUM(CASE WHEN delivery_status = 'Sent' OR delivery_status = 'Read' THEN 1 ELSE 0 END) as sent,
                SUM(CASE WHEN delivery_status = 'Read' THEN 1 ELSE 0 END) as read
            FROM announcement_delivery_logs
            WHERE announcement_id = ?
            GROUP BY delivery_channel
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        while ($row = $result->fetch_assoc()) {
            $channel = $row['delivery_channel'];
            $stats['delivery_channels'][$channel] = [
                'sent' => (int)$row['sent'],
                'read' => (int)$row['read']
            ];
        }
        
        // Get interaction statistics
        $stmt = $conn->prepare("
            SELECT 
                interaction_type,
                COUNT(*) as count
            FROM announcement_delivery_logs
            WHERE announcement_id = ?
            GROUP BY interaction_type
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        while ($row = $result->fetch_assoc()) {
            $interactionType = $row['interaction_type'] ?: 'None';
            $stats['interactions'][$interactionType] = (int)$row['count'];
        }
        
        // Get hourly read statistics for the past 48 hours
        $stmt = $conn->prepare("
            SELECT 
                HOUR(read_at) as hour,
                COUNT(*) as read_count
            FROM announcement_delivery_logs
            WHERE announcement_id = ?
            AND read_at IS NOT NULL
            AND read_at >= DATE_SUB(NOW(), INTERVAL 48 HOUR)
            GROUP BY HOUR(read_at)
            ORDER BY HOUR(read_at)
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        // Initialize hourly reads array with zeros
        for ($i = 0; $i < 24; $i++) {
            $stats['hourly_reads'][$i] = 0;
        }
        
        while ($row = $result->fetch_assoc()) {
            $hour = (int)$row['hour'];
            $stats['hourly_reads'][$hour] = (int)$row['read_count'];
        }
        
        // Get overall statistics
        $stmt = $conn->prepare("
            SELECT * FROM announcement_statistics
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows > 0) {
            $overallStats = $result->fetch_assoc();
            $stats['total_recipients'] = (int)$overallStats['total_recipients'];
            $stats['delivery_count'] = (int)$overallStats['delivery_count'];
            $stats['read_count'] = (int)$overallStats['read_count'];
            $stats['interaction_count'] = (int)$overallStats['interaction_count'];
            
            // Calculate rates
            $stats['delivery_rate'] = $stats['total_recipients'] > 0 ? 
                round(($stats['delivery_count'] / $stats['total_recipients']) * 100, 2) : 0;
            
            $stats['read_rate'] = $stats['delivery_count'] > 0 ? 
                round(($stats['read_count'] / $stats['delivery_count']) * 100, 2) : 0;
            
            $stats['interaction_rate'] = $stats['read_count'] > 0 ? 
                round(($stats['interaction_count'] / $stats['read_count']) * 100, 2) : 0;
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Error getting announcement delivery stats: " . $e->getMessage());
        return $stats;
    }
}

/**
 * Creates an announcement from a template
 * 
 * @param int $template_id The template ID
 * @param array $customData Custom data to override template values
 * @param int $created_by User ID of the creator
 * @return array|bool Returns the created announcement data or false on failure
 */
function createAnnouncementFromTemplate($template_id, $customData, $created_by) {
    global $conn;
    
    // Get template data
    $stmt = $conn->prepare("
        SELECT * FROM announcement_templates
        WHERE template_id = ? AND is_active = 1
    ");
    $stmt->bind_param("i", $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Template not found or inactive.'];
    }
    
    $template = $result->fetch_assoc();
    
    // Merge template data with custom data
    $announcementData = [
        'title' => $customData['title'] ?? $template['title'],
        'content' => $customData['content'] ?? $template['content'],
        'importance' => $customData['importance'] ?? 'Medium',
        'course_id' => $customData['course_id'] ?? null,
        'is_system_wide' => $customData['is_system_wide'] ?? 0,
        'target_roles' => $customData['target_roles'] ?? null,
        'is_pinned' => $customData['is_pinned'] ?? 0,
        'scheduled_at' => $customData['scheduled_at'] ?? null,
        'expires_at' => $customData['expires_at'] ?? null,
        'status' => !empty($customData['scheduled_at']) ? 'Scheduled' : ($customData['status'] ?? 'Published')
    ];
    
    // Call the standard announcement creation function
    return createAnnouncement($announcementData, $created_by);
}

/**
 * Creates a new announcement template
 * 
 * @param array $data Template data
 * @param int $created_by User ID of the creator
 * @return array|bool Returns the created template data or false on failure
 */
function createAnnouncementTemplate($data, $created_by) {
    global $conn;
    
    if (empty($data['title']) || empty($data['content'])) {
        return ['error' => 'Title and content are required.'];
    }
    
    $stmt = $conn->prepare("
        INSERT INTO announcement_templates
        (title, content, created_by, is_active)
        VALUES (?, ?, ?, ?)
    ");
    
    $isActive = isset($data['is_active']) ? $data['is_active'] : 1;
    $stmt->bind_param("ssii", $data['title'], $data['content'], $created_by, $isActive);
    
    if ($stmt->execute()) {
        $template_id = $stmt->insert_id;
        $stmt->close();
        
        return getAnnouncementTemplate($template_id);
    } else {
        $stmt->close();
        return ['error' => 'Failed to create template.'];
    }
}

/**
 * Gets an announcement template by ID
 * 
 * @param int $template_id The template ID
 * @return array|bool Returns the template data or false if not found
 */
function getAnnouncementTemplate($template_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT t.*, u.first_name, u.last_name
        FROM announcement_templates t
        JOIN users u ON t.created_by = u.user_id
        WHERE t.template_id = ?
    ");
    $stmt->bind_param("i", $template_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    return $result->fetch_assoc();
}

/**
 * Gets a list of announcement templates
 * 
 * @param array $filters Optional filters
 * @param int $page Page number
 * @param int $limit Items per page
 * @return array List of templates
 */
function getAnnouncementTemplates($filters = [], $page = 1, $limit = 20) {
    global $conn;
    
    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;
    
    // Build query
    $query = "
        SELECT t.*, u.first_name, u.last_name
        FROM announcement_templates t
        JOIN users u ON t.created_by = u.user_id
        WHERE 1=1
    ";
    
    $params = [];
    $types = "";
    
    // Apply filters
    if (isset($filters['is_active'])) {
        $query .= " AND t.is_active = ?";
        $params[] = $filters['is_active'];
        $types .= "i";
    }
    
    if (!empty($filters['created_by'])) {
        $query .= " AND t.created_by = ?";
        $params[] = $filters['created_by'];
        $types .= "i";
    }
    
    if (!empty($filters['search'])) {
        $searchTerm = "%" . $filters['search'] . "%";
        $query .= " AND (t.title LIKE ? OR t.content LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }
    
    // Add ordering
    $query .= " ORDER BY t.created_at DESC";
    
    // Add pagination
    $query .= " LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $limit;
    $types .= "ii";
    
    // Execute query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    $templates = [];
    while ($row = $result->fetch_assoc()) {
        $templates[] = $row;
    }
    
    // Get total count for pagination
    $countQuery = str_replace("SELECT t.*, u.first_name, u.last_name", "SELECT COUNT(*) as total", $query);
    $countQuery = preg_replace('/LIMIT \?, \?/', '', $countQuery);
    
    $stmt = $conn->prepare($countQuery);
    // Remove the last two params (offset and limit)
    array_pop($params);
    array_pop($params);
    $types = substr($types, 0, -2);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    $stmt->close();
    
    return [
        'templates' => $templates,
        'total' => $totalCount,
        'page' => $page,
        'limit' => $limit,
        'pages' => ceil($totalCount / $limit)
    ];
}

/**
 * Updates an announcement template
 * 
 * @param int $template_id The template ID
 * @param array $data Updated template data
 * @return array|bool Returns the updated template data or false on failure
 */
function updateAnnouncementTemplate($template_id, $data) {
    global $conn;
    
    $updateFields = [];
    $params = [];
    $types = "";
    
    if (isset($data['title'])) {
        $updateFields[] = "title = ?";
        $params[] = $data['title'];
        $types .= "s";
    }
    
    if (isset($data['content'])) {
        $updateFields[] = "content = ?";
        $params[] = $data['content'];
        $types .= "s";
    }
    
    if (isset($data['is_active'])) {
        $updateFields[] = "is_active = ?";
        $params[] = $data['is_active'];
        $types .= "i";
    }
    
    if (empty($updateFields)) {
        return ['error' => 'No fields to update.'];
    }
    
    // Add template_id to params
    $params[] = $template_id;
    $types .= "i";
    
    $query = "UPDATE announcement_templates SET " . implode(", ", $updateFields) . 
             ", updated_at = CURRENT_TIMESTAMP WHERE template_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        $stmt->close();
        return getAnnouncementTemplate($template_id);
    } else {
        $stmt->close();
        return ['error' => 'Failed to update template.'];
    }
}

/**
 * Deletes an announcement template
 * 
 * @param int $template_id The template ID
 * @return bool True on success, false on failure
 */
function deleteAnnouncementTemplate($template_id) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM announcement_templates WHERE template_id = ?");
    $stmt->bind_param("i", $template_id);
    $result = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return $result && $affected > 0;
}

/**
 * Calculates and updates statistics for all announcements
 * This should be run periodically to ensure statistics are accurate
 * 
 * @return array Stats about the update operation
 */
function updateAllAnnouncementStatistics() {
    global $conn;
    
    $stats = [
        'processed' => 0,
        'updated' => 0
    ];
    
    // Get all announcements
    $stmt = $conn->prepare("SELECT announcement_id FROM course_announcements");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    
    while ($row = $result->fetch_assoc()) {
        $announcement_id = $row['announcement_id'];
        $stats['processed']++;
        
        if (updateAnnouncementStatistics($announcement_id)) {
            $stats['updated']++;
        }
    }
    
    return $stats;
}

/**
 * Updates statistics for a specific announcement
 * 
 * @param int $announcement_id The announcement ID
 * @return bool True on success, false on failure
 */
function updateAnnouncementStatistics($announcement_id) {
    global $conn;
    
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Calculate total recipients
        $recipients = getAnnouncementRecipients($announcement_id);
        $totalRecipients = count($recipients);
        
        // Get delivery counts
        $stmt = $conn->prepare("
            SELECT 
                COUNT(*) as delivery_count,
                SUM(CASE WHEN delivery_status = 'Read' THEN 1 ELSE 0 END) as read_count,
                SUM(CASE WHEN interaction_type != 'None' AND interaction_type IS NOT NULL THEN 1 ELSE 0 END) as interaction_count
            FROM announcement_delivery_logs
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $counts = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        // Check if statistics record exists
        $stmt = $conn->prepare("SELECT stat_id FROM announcement_statistics WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $statExists = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        
        if ($statExists) {
            // Update existing record
            $stmt = $conn->prepare("
                UPDATE announcement_statistics
                SET total_recipients = ?,
                    delivery_count = ?,
                    read_count = ?,
                    interaction_count = ?,
                    last_calculated = CURRENT_TIMESTAMP
                WHERE announcement_id = ?
            ");
            $stmt->bind_param("iiiii", 
                $totalRecipients, 
                $counts['delivery_count'], 
                $counts['read_count'], 
                $counts['interaction_count'],
                $announcement_id
            );
        } else {
            // Insert new record
            $stmt = $conn->prepare("
                INSERT INTO announcement_statistics
                (announcement_id, total_recipients, delivery_count, read_count, interaction_count)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiii", 
                $announcement_id, 
                $totalRecipients, 
                $counts['delivery_count'], 
                $counts['read_count'], 
                $counts['interaction_count']
            );
        }
        
        $result = $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        return $result;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error updating announcement statistics: " . $e->getMessage());
        return false;
    }
}
?>