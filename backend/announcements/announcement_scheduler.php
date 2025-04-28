<?php
/**
 * Announcement Scheduler
 * 
 * This file contains functions for managing scheduled and recurring announcements
 * in the Learnix LMS.
 * 
 * @package Learnix
 * @subpackage Announcements
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/announcement_targeting.php';
require_once __DIR__ . '/announcement_delivery.php';

/**
 * Schedules an announcement for future publication
 * 
 * @param array $data Announcement data including scheduled_at date
 * @param int $created_by User ID of the creator
 * @return array|bool Returns the created announcement data or false on failure
 */
function scheduleAnnouncement($data, $created_by) {
    // Validate scheduled date
    if (empty($data['scheduled_at'])) {
        return ['error' => 'Scheduled date is required.'];
    }
    
    $scheduledTime = strtotime($data['scheduled_at']);
    if ($scheduledTime === false || $scheduledTime <= time()) {
        return ['error' => 'Scheduled date must be in the future.'];
    }
    
    // Set status to Scheduled
    $data['status'] = 'Scheduled';
    
    // Use the existing createAnnouncement function
    return createAnnouncement($data, $created_by);
}

/**
 * Creates a recurring announcement
 * 
 * @param array $data Announcement data
 * @param array $recurringData Recurring settings
 * @param int $created_by User ID of the creator
 * @return array|bool Returns the created announcement data or false on failure
 */
function createRecurringAnnouncement($data, $recurringData, $created_by) {
    global $conn;
    
    // Validate recurring data
    if (empty($recurringData['frequency'])) {
        return ['error' => 'Frequency is required for recurring announcements.'];
    }
    
    if ($recurringData['frequency'] === 'Weekly' && !isset($recurringData['day_of_week'])) {
        return ['error' => 'Day of week is required for weekly announcements.'];
    }
    
    if ($recurringData['frequency'] === 'Monthly' && !isset($recurringData['day_of_month'])) {
        return ['error' => 'Day of month is required for monthly announcements.'];
    }
    
    if (empty($recurringData['start_date'])) {
        return ['error' => 'Start date is required for recurring announcements.'];
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Create base announcement
        $announcementResult = createAnnouncement($data, $created_by);
        
        if (isset($announcementResult['error'])) {
            $conn->rollback();
            return $announcementResult;
        }
        
        $announcement_id = $announcementResult['announcement_id'];
        
        // Create recurring record
        $stmt = $conn->prepare("
            INSERT INTO recurring_announcements 
            (announcement_id, frequency, day_of_week, day_of_month, start_date, end_date, is_active) 
            VALUES (?, ?, ?, ?, ?, ?, 1)
        ");
        
        $dayOfWeek = isset($recurringData['day_of_week']) ? $recurringData['day_of_week'] : null;
        $dayOfMonth = isset($recurringData['day_of_month']) ? $recurringData['day_of_month'] : null;
        $endDate = isset($recurringData['end_date']) ? $recurringData['end_date'] : null;
        
        $stmt->bind_param(
            "isiiss",
            $announcement_id,
            $recurringData['frequency'],
            $dayOfWeek,
            $dayOfMonth,
            $recurringData['start_date'],
            $endDate
        );
        
        $stmt->execute();
        $stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Return the announcement with recurring information
        $result = getAnnouncementById($announcement_id);
        return $result;
        
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        error_log("Error creating recurring announcement: " . $e->getMessage());
        return ['error' => 'Failed to create recurring announcement. Please try again.'];
    }
}

/**
 * Updates a recurring announcement's schedule
 * 
 * @param int $recurring_id The recurring announcement ID
 * @param array $recurringData New recurring settings
 * @return array|bool Returns the updated announcement data or false on failure
 */
function updateRecurringSchedule($recurring_id, $recurringData) {
    global $conn;
    
    try {
        // Get existing recurring record
        $stmt = $conn->prepare("
            SELECT announcement_id FROM recurring_announcements 
            WHERE recurring_id = ?
        ");
        $stmt->bind_param("i", $recurring_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Recurring announcement not found.'];
        }
        
        $announcement_id = $result->fetch_assoc()['announcement_id'];
        $stmt->close();
        
        // Update recurring record
        $updateFields = [];
        $params = [];
        $types = "";
        
        if (isset($recurringData['frequency'])) {
            $updateFields[] = "frequency = ?";
            $params[] = $recurringData['frequency'];
            $types .= "s";
        }
        
        if (isset($recurringData['day_of_week'])) {
            $updateFields[] = "day_of_week = ?";
            $params[] = $recurringData['day_of_week'];
            $types .= "i";
        }
        
        if (isset($recurringData['day_of_month'])) {
            $updateFields[] = "day_of_month = ?";
            $params[] = $recurringData['day_of_month'];
            $types .= "i";
        }
        
        if (isset($recurringData['start_date'])) {
            $updateFields[] = "start_date = ?";
            $params[] = $recurringData['start_date'];
            $types .= "s";
        }
        
        if (isset($recurringData['end_date'])) {
            $updateFields[] = "end_date = ?";
            $params[] = $recurringData['end_date'];
            $types .= "s";
        }
        
        if (isset($recurringData['is_active'])) {
            $updateFields[] = "is_active = ?";
            $params[] = $recurringData['is_active'];
            $types .= "i";
        }
        
        if (!empty($updateFields)) {
            // Add recurring_id to params
            $params[] = $recurring_id;
            $types .= "i";
            
            $query = "UPDATE recurring_announcements SET " . implode(", ", $updateFields) . " WHERE recurring_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();
        }
        
        // Return the updated announcement
        return getAnnouncementById($announcement_id);
        
    } catch (Exception $e) {
        error_log("Error updating recurring schedule: " . $e->getMessage());
        return ['error' => 'Failed to update recurring schedule. Please try again.'];
    }
}

/**
 * Enables or disables a recurring announcement
 * 
 * @param int $recurring_id The recurring announcement ID
 * @param bool $active Whether to enable or disable
 * @return bool True on success, false on failure
 */
function setRecurringActive($recurring_id, $active) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE recurring_announcements 
        SET is_active = ? 
        WHERE recurring_id = ?
    ");
    $isActive = $active ? 1 : 0;
    $stmt->bind_param("ii", $isActive, $recurring_id);
    $success = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    
    return $success && $affected > 0;
}

/**
 * Gets a list of upcoming scheduled announcements
 * 
 * @param int $limit Maximum number of announcements to return
 * @return array List of upcoming scheduled announcements
 */
function getUpcomingScheduledAnnouncements($limit = 10) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT a.*, u.first_name, u.last_name
        FROM course_announcements a
        JOIN users u ON a.created_by = u.user_id
        WHERE a.status = 'Scheduled'
        AND a.scheduled_at > NOW()
        ORDER BY a.scheduled_at ASC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();
    
    return $announcements;
}

/**
 * Gets a list of active recurring announcements
 * 
 * @return array List of active recurring announcements
 */
function getActiveRecurringAnnouncements() {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT r.*, a.title, a.course_id, a.is_system_wide, a.target_roles,
               u.first_name, u.last_name
        FROM recurring_announcements r
        JOIN course_announcements a ON r.announcement_id = a.announcement_id
        JOIN users u ON a.created_by = u.user_id
        WHERE r.is_active = 1
        AND (r.end_date IS NULL OR r.end_date >= CURDATE())
        ORDER BY r.frequency, r.last_triggered
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
    $stmt->close();
    
    return $announcements;
}

?>