<?php

/**
 * Announcement Controller
 * 
 * This file contains the main functions for creating, reading, updating and deleting
 * course announcements in the Learnix LMS.
 * 
 * @package Learnix
 * @subpackage Announcements
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/announcement_targeting.php';
require_once __DIR__ . '/announcement_delivery.php';

/**
 * Creates a new announcement
 * 
 * @param array $data Announcement data including title, content, importance, etc.
 * @param int $created_by User ID of the creator
 * @return array|bool Returns the created announcement data or false on failure
 */
function createAnnouncement($data, $created_by)
{
    global $conn;

    // Validate required fields
    if (empty($data['title']) || empty($data['content'])) {
        return ['error' => 'Title and content are required.'];
    }

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Prepare statement for inserting announcement
        $stmt = $conn->prepare("INSERT INTO course_announcements 
            (course_id, is_system_wide, target_roles, title, content, importance, 
            is_pinned, created_by, scheduled_at, expires_at, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Set default values if not provided
        $course_id = $data['course_id'] ?? null;
        $is_system_wide = $data['is_system_wide'] ?? 0;
        $target_roles = $data['target_roles'] ?? null;
        $importance = $data['importance'] ?? 'Medium';
        $is_pinned = $data['is_pinned'] ?? 0;
        $scheduled_at = !empty($data['scheduled_at']) ? $data['scheduled_at'] : null;
        $expires_at = !empty($data['expires_at']) ? $data['expires_at'] : null;
        $status = !empty($scheduled_at) ? 'Scheduled' : ($data['status'] ?? 'Published');

        // Bind parameters and execute
        $stmt->bind_param(
            "iissssisiss",
            $course_id,
            $is_system_wide,
            $target_roles,
            $data['title'],
            $data['content'],
            $importance,
            $is_pinned,
            $created_by,
            $scheduled_at,
            $expires_at,
            $status
        );

        $stmt->execute();
        $announcement_id = $stmt->insert_id;
        $stmt->close();

        // If targeting specific groups, handle those
        if (!empty($data['target_groups'])) {
            foreach ($data['target_groups'] as $target) {
                $stmt = $conn->prepare("INSERT INTO announcement_target_groups 
                    (announcement_id, target_type, target_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $announcement_id, $target['type'], $target['id']);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Handle file attachments if provided
        if (!empty($data['attachments'])) {
            foreach ($data['attachments'] as $attachment) {
                $stmt = $conn->prepare("INSERT INTO announcement_attachments 
                    (announcement_id, file_path, file_name, file_size, file_type) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "issis",
                    $announcement_id,
                    $attachment['path'],
                    $attachment['name'],
                    $attachment['size'],
                    $attachment['type']
                );
                $stmt->execute();
                $stmt->close();
            }
        }

        // Create initial statistics record
        $stmt = $conn->prepare("INSERT INTO announcement_statistics 
            (announcement_id, total_recipients, delivery_count, read_count) 
            VALUES (?, 0, 0, 0)");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $stmt->close();

        // Handle recurring schedule if set
        if (!empty($data['recurring'])) {
            // Extract recurring data into variables
            $frequency = $data['recurring']['frequency'];
            $dayOfWeek = $data['recurring']['day_of_week'] ?? null;
            $dayOfMonth = $data['recurring']['day_of_month'] ?? null;
            $startDate = $data['recurring']['start_date'];
            $endDate = $data['recurring']['end_date'] ?? null;

            $stmt = $conn->prepare("INSERT INTO recurring_announcements 
                (announcement_id, frequency, day_of_week, day_of_month, start_date, end_date, is_active) 
                VALUES (?, ?, ?, ?, ?, ?, 1)");
            $stmt->bind_param(
                "isiiss",
                $announcement_id,
                $frequency,
                $dayOfWeek,
                $dayOfMonth,
                $startDate,
                $endDate
            );
            $stmt->execute();
            $stmt->close();
        }

        // If announcement is published (not draft or scheduled), process delivery
        if ($status === 'Published') {
            // Get target recipients
            $recipients = getAnnouncementRecipients($announcement_id);

            // Queue announcement for delivery
            queueAnnouncementDelivery($announcement_id, $recipients);

            // Update statistics with recipient count
            $recipientCount = count($recipients);
            $stmt = $conn->prepare("UPDATE announcement_statistics 
                SET total_recipients = ? WHERE announcement_id = ?");
            $stmt->bind_param("ii", $recipientCount, $announcement_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Return the created announcement data
        return getAnnouncementById($announcement_id);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error creating announcement: " . $e->getMessage());
        return ['error' => 'Failed to create announcement. Please try again.'];
    }
}

/**
 * Retrieves an announcement by ID
 * 
 * @param int $announcement_id The announcement ID
 * @return array|bool The announcement data or false if not found
 */
function getAnnouncementById($announcement_id)
{
    global $conn;

    $stmt = $conn->prepare("
        SELECT a.*, 
               IFNULL(s.total_recipients, 0) as total_recipients,
               IFNULL(s.delivery_count, 0) as delivery_count,
               IFNULL(s.read_count, 0) as read_count,
               IFNULL(s.interaction_count, 0) as interaction_count,
               u.first_name, u.last_name
        FROM course_announcements a
        LEFT JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        LEFT JOIN users u ON a.created_by = u.user_id
        WHERE a.announcement_id = ?
    ");

    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    $announcement = $result->fetch_assoc();
    $stmt->close();

    // Get target groups
    $stmt = $conn->prepare("
        SELECT * FROM announcement_target_groups 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $targetResult = $stmt->get_result();
    $announcement['target_groups'] = [];

    while ($target = $targetResult->fetch_assoc()) {
        $announcement['target_groups'][] = $target;
    }
    $stmt->close();

    // Get attachments
    $stmt = $conn->prepare("
        SELECT * FROM announcement_attachments 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $attachmentResult = $stmt->get_result();
    $announcement['attachments'] = [];

    while ($attachment = $attachmentResult->fetch_assoc()) {
        $announcement['attachments'][] = $attachment;
    }
    $stmt->close();

    // Check if recurring
    $stmt = $conn->prepare("
        SELECT * FROM recurring_announcements 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $recurringResult = $stmt->get_result();

    if ($recurringResult->num_rows > 0) {
        $announcement['recurring'] = $recurringResult->fetch_assoc();
    }
    $stmt->close();

    return $announcement;
}

/**
 * Updates an existing announcement
 * 
 * @param int $announcement_id The announcement ID
 * @param array $data The updated announcement data
 * @return array|bool The updated announcement or false on failure
 */
function updateAnnouncement($announcement_id, $data)
{
    global $conn;

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Get current announcement data
        $current = getAnnouncementById($announcement_id);
        if (!$current) {
            return ['error' => 'Announcement not found.'];
        }

        // Check if we're publishing a draft or scheduled announcement
        $isBeingPublished = ($current['status'] !== 'Published' &&
            ($data['status'] ?? $current['status']) === 'Published');

        // Build update query based on provided data
        $updateFields = [];
        $params = [];
        $types = "";

        // Only update fields that are provided
        if (isset($data['course_id'])) {
            $updateFields[] = "course_id = ?";
            $params[] = $data['course_id'];
            $types .= "i";
        }

        if (isset($data['is_system_wide'])) {
            $updateFields[] = "is_system_wide = ?";
            $params[] = $data['is_system_wide'];
            $types .= "i";
        }

        if (isset($data['target_roles'])) {
            $updateFields[] = "target_roles = ?";
            $params[] = $data['target_roles'];
            $types .= "s";
        }

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

        if (isset($data['importance'])) {
            $updateFields[] = "importance = ?";
            $params[] = $data['importance'];
            $types .= "s";
        }

        if (isset($data['is_pinned'])) {
            $updateFields[] = "is_pinned = ?";
            $params[] = $data['is_pinned'];
            $types .= "i";
        }

        if (isset($data['scheduled_at'])) {
            $updateFields[] = "scheduled_at = ?";
            $params[] = $data['scheduled_at'];
            $types .= "s";
        }

        if (isset($data['expires_at'])) {
            $updateFields[] = "expires_at = ?";
            $params[] = $data['expires_at'];
            $types .= "s";
        }

        if (isset($data['status'])) {
            $updateFields[] = "status = ?";
            $params[] = $data['status'];
            $types .= "s";
        }

        // Add announcement_id to params
        $params[] = $announcement_id;
        $types .= "i";

        if (!empty($updateFields)) {
            $query = "UPDATE course_announcements SET " . implode(", ", $updateFields) .
                ", updated_at = CURRENT_TIMESTAMP WHERE announcement_id = ?";

            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }

        // Update target groups if provided
        if (isset($data['target_groups'])) {
            // First delete existing target groups
            $stmt = $conn->prepare("DELETE FROM announcement_target_groups WHERE announcement_id = ?");
            $stmt->bind_param("i", $announcement_id);
            $stmt->execute();
            $stmt->close();

            // Then insert new ones
            foreach ($data['target_groups'] as $target) {
                $stmt = $conn->prepare("INSERT INTO announcement_target_groups 
                    (announcement_id, target_type, target_id) VALUES (?, ?, ?)");
                $stmt->bind_param("isi", $announcement_id, $target['type'], $target['id']);
                $stmt->execute();
                $stmt->close();
            }
        }

        // Update recurring settings if provided
        if (isset($data['recurring'])) {
            // Check if recurring record already exists
            $stmt = $conn->prepare("SELECT recurring_id FROM recurring_announcements WHERE announcement_id = ?");
            $stmt->bind_param("i", $announcement_id);
            $stmt->execute();
            $recurringResult = $stmt->get_result();
            $stmt->close();

            if ($recurringResult->num_rows > 0) {
                // Update existing record
                // Extract recurring data into variables
                $frequency = $data['recurring']['frequency'];
                $dayOfWeek = $data['recurring']['day_of_week'] ?? null;
                $dayOfMonth = $data['recurring']['day_of_month'] ?? null;
                $startDate = $data['recurring']['start_date'];
                $endDate = $data['recurring']['end_date'] ?? null;
                $isActive = $data['recurring']['is_active'] ?? 1;

                $stmt = $conn->prepare("UPDATE recurring_announcements SET 
                    frequency = ?, day_of_week = ?, day_of_month = ?, start_date = ?, end_date = ?, is_active = ? 
                    WHERE announcement_id = ?");
                $stmt->bind_param(
                    "siissii",
                    $frequency,
                    $dayOfWeek,
                    $dayOfMonth,
                    $startDate,
                    $endDate,
                    $isActive,
                    $announcement_id
                );
            } else {
                // Insert new record
                // Extract recurring data into variables
                $frequency = $data['recurring']['frequency'];
                $dayOfWeek = $data['recurring']['day_of_week'] ?? null;
                $dayOfMonth = $data['recurring']['day_of_month'] ?? null;
                $startDate = $data['recurring']['start_date'];
                $endDate = $data['recurring']['end_date'] ?? null;
                $isActive = $data['recurring']['is_active'] ?? 1;

                $stmt = $conn->prepare("INSERT INTO recurring_announcements 
                    (announcement_id, frequency, day_of_week, day_of_month, start_date, end_date, is_active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param(
                    "isiissi",
                    $announcement_id,
                    $frequency,
                    $dayOfWeek,
                    $dayOfMonth,
                    $startDate,
                    $endDate,
                    $isActive
                );
            }
            $stmt->execute();
            $stmt->close();
        }

        // If announcement is being published now, process delivery
        if ($isBeingPublished) {
            // Get target recipients
            $recipients = getAnnouncementRecipients($announcement_id);

            // Queue announcement for delivery
            queueAnnouncementDelivery($announcement_id, $recipients);

            // Update statistics with recipient count
            $recipientCount = count($recipients);
            $stmt = $conn->prepare("UPDATE announcement_statistics 
                SET total_recipients = ? WHERE announcement_id = ?");
            $stmt->bind_param("ii", $recipientCount, $announcement_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        // Return the updated announcement
        return getAnnouncementById($announcement_id);
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error updating announcement: " . $e->getMessage());
        return ['error' => 'Failed to update announcement. Please try again.'];
    }
}

/**
 * Deletes an announcement
 * 
 * @param int $announcement_id The announcement ID
 * @return bool True on success, false on failure
 */
function deleteAnnouncement($announcement_id)
{
    global $conn;

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Delete related records first
        $tables = [
            'announcement_delivery_logs',
            'announcement_target_groups',
            'announcement_attachments',
            'announcement_statistics',
            'recurring_announcements'
        ];

        foreach ($tables as $table) {
            $stmt = $conn->prepare("DELETE FROM {$table} WHERE announcement_id = ?");
            $stmt->bind_param("i", $announcement_id);
            $stmt->execute();
            $stmt->close();
        }

        // Delete the announcement itself
        $stmt = $conn->prepare("DELETE FROM course_announcements WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        // Commit transaction
        $conn->commit();

        return $affected > 0;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error deleting announcement: " . $e->getMessage());
        return false;
    }
}

/**
 * Gets a list of announcements based on filters
 * 
 * @param array $filters Filtering criteria (course_id, status, search, etc.)
 * @param int $page Page number for pagination
 * @param int $limit Items per page
 * @return array List of announcements
 */
function getAnnouncements($filters = [], $page = 1, $limit = 20)
{
    global $conn;

    // Calculate offset for pagination
    $offset = ($page - 1) * $limit;

    // Build the query
    $query = "
        SELECT a.*, 
               IFNULL(s.total_recipients, 0) as total_recipients,
               IFNULL(s.delivery_count, 0) as delivery_count,
               IFNULL(s.read_count, 0) as read_count,
               u.first_name, u.last_name
        FROM course_announcements a
        LEFT JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        LEFT JOIN users u ON a.created_by = u.user_id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Apply filters
    if (!empty($filters['course_id'])) {
        $query .= " AND (a.course_id = ? OR a.is_system_wide = 1)";
        $params[] = $filters['course_id'];
        $types .= "i";
    }

    if (!empty($filters['status'])) {
        $query .= " AND a.status = ?";
        $params[] = $filters['status'];
        $types .= "s";
    }

    if (!empty($filters['created_by'])) {
        $query .= " AND a.created_by = ?";
        $params[] = $filters['created_by'];
        $types .= "i";
    }

    if (!empty($filters['search'])) {
        $searchTerm = "%" . $filters['search'] . "%";
        $query .= " AND (a.title LIKE ? OR a.content LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    if (isset($filters['is_system_wide'])) {
        $query .= " AND a.is_system_wide = ?";
        $params[] = $filters['is_system_wide'];
        $types .= "i";
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

    // Add ordering
    $query .= " ORDER BY ";
    if (!empty($filters['order_by'])) {
        $validColumns = ['created_at', 'title', 'status', 'importance'];
        $orderBy = in_array($filters['order_by'], $validColumns) ? $filters['order_by'] : 'created_at';
        $direction = (!empty($filters['order_direction']) && strtoupper($filters['order_direction']) === 'ASC') ? 'ASC' : 'DESC';
        $query .= "a.{$orderBy} {$direction}, ";
    }
    $query .= "a.is_pinned DESC, a.created_at DESC";

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

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();

    // Get total count for pagination
    $countQuery = str_replace("SELECT a.*, IFNULL(s.total_recipients, 0) as total_recipients, IFNULL(s.delivery_count, 0) as delivery_count, IFNULL(s.read_count, 0) as read_count, u.first_name, u.last_name", "SELECT COUNT(*) as total", $query);
    $countQuery = preg_replace('/LIMIT \?, \?/', '', $countQuery);

    $stmt = $conn->prepare($countQuery);
    if (!empty($params)) {
        // Remove the last two params (offset and limit)
        array_pop($params);
        array_pop($params);
        $types = substr($types, 0, -2);

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
    }
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
 * Marks an announcement as read for a specific user
 * 
 * @param int $announcement_id The announcement ID
 * @param int $user_id The user ID
 * @return bool True on success, false on failure
 */
function markAnnouncementAsRead($announcement_id, $user_id)
{
    global $conn;

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Check if delivery log exists
        $stmt = $conn->prepare("
            SELECT delivery_id, delivery_status 
            FROM announcement_delivery_logs 
            WHERE announcement_id = ? AND user_id = ? AND delivery_channel = 'In-App'
        ");
        $stmt->bind_param("ii", $announcement_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $now = date('Y-m-d H:i:s');

        if ($result->num_rows > 0) {
            $log = $result->fetch_assoc();

            // Only update if not already read
            if ($log['delivery_status'] !== 'Read') {
                $stmt = $conn->prepare("
                    UPDATE announcement_delivery_logs 
                    SET delivery_status = 'Read', read_at = ? 
                    WHERE delivery_id = ?
                ");
                $stmt->bind_param("si", $now, $log['delivery_id']);
                $stmt->execute();
                $stmt->close();

                // Update statistics
                $stmt = $conn->prepare("
                    UPDATE announcement_statistics 
                    SET read_count = read_count + 1 
                    WHERE announcement_id = ?
                ");
                $stmt->bind_param("i", $announcement_id);
                $stmt->execute();
                $stmt->close();
            }
        } else {
            // Create new delivery log entry if it doesn't exist
            $stmt = $conn->prepare("
                INSERT INTO announcement_delivery_logs 
                (announcement_id, user_id, delivery_channel, delivery_status, delivery_time, read_at) 
                VALUES (?, ?, 'In-App', 'Read', ?, ?)
            ");
            $stmt->bind_param("iiss", $announcement_id, $user_id, $now, $now);
            $stmt->execute();
            $stmt->close();

            // Update statistics
            $stmt = $conn->prepare("
                UPDATE announcement_statistics 
                SET delivery_count = delivery_count + 1, read_count = read_count + 1 
                WHERE announcement_id = ?
            ");
            $stmt->bind_param("i", $announcement_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error marking announcement as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Records an interaction with an announcement
 * 
 * @param int $announcement_id The announcement ID
 * @param int $user_id The user ID
 * @param string $interaction_type The type of interaction (Viewed, Clicked, Responded)
 * @return bool True on success, false on failure
 */
function recordAnnouncementInteraction($announcement_id, $user_id, $interaction_type)
{
    global $conn;

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Update delivery log
        $stmt = $conn->prepare("
            UPDATE announcement_delivery_logs 
            SET interaction_type = ? 
            WHERE announcement_id = ? AND user_id = ?
        ");
        $stmt->bind_param("sii", $interaction_type, $announcement_id, $user_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        // If no existing record was updated, create a new one
        if ($affected === 0) {
            $now = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("
                INSERT INTO announcement_delivery_logs 
                (announcement_id, user_id, delivery_channel, delivery_status, delivery_time, interaction_type) 
                VALUES (?, ?, 'In-App', 'Sent', ?, ?)
            ");
            $stmt->bind_param("iiss", $announcement_id, $user_id, $now, $interaction_type);
            $stmt->execute();
            $stmt->close();
        }

        // Update statistics
        $stmt = $conn->prepare("
            UPDATE announcement_statistics 
            SET interaction_count = interaction_count + 1 
            WHERE announcement_id = ?
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        return true;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error recording announcement interaction: " . $e->getMessage());
        return false;
    }
}

/**
 * Archives an announcement
 * 
 * @param int $announcement_id The announcement ID
 * @return bool True on success, false on failure
 */
function archiveAnnouncement($announcement_id)
{
    global $conn;

    $stmt = $conn->prepare("
        UPDATE course_announcements 
        SET status = 'Archived', updated_at = CURRENT_TIMESTAMP 
        WHERE announcement_id = ?
    ");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    return $affected > 0;
}

/**
 * Publishes a draft or scheduled announcement immediately
 * 
 * @param int $announcement_id The announcement ID
 * @return array|bool The updated announcement or false on failure
 */
function publishAnnouncement($announcement_id)
{
    global $conn;

    try {
        // Begin transaction
        $conn->begin_transaction();

        // Update status to Published
        $stmt = $conn->prepare("
            UPDATE course_announcements 
            SET status = 'Published', scheduled_at = NULL, updated_at = CURRENT_TIMESTAMP 
            WHERE announcement_id = ? AND status IN ('Draft', 'Scheduled')
        ");
        $stmt->bind_param("i", $announcement_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            // Get target recipients
            $recipients = getAnnouncementRecipients($announcement_id);

            // Queue announcement for delivery
            queueAnnouncementDelivery($announcement_id, $recipients);

            // Update statistics with recipient count
            $recipientCount = count($recipients);
            $stmt = $conn->prepare("
                UPDATE announcement_statistics 
                SET total_recipients = ? 
                WHERE announcement_id = ?
            ");
            $stmt->bind_param("ii", $recipientCount, $announcement_id);
            $stmt->execute();
            $stmt->close();
        }

        // Commit transaction
        $conn->commit();

        return $affected > 0 ? getAnnouncementById($announcement_id) : false;
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Error publishing announcement: " . $e->getMessage());
        return false;
    }
}

// Add more functions as needed for announcement management
