<?php

 // File: ajax/instructors/get_announcement_stats.php 
require_once '../../backend/config.php';
require '../../backend/session_start.php';

// Ensure no whitespace/output before headers and JSON
header('Content-Type: application/json');

// Check authorization
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];

try {
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get total announcements
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_count 
        FROM course_announcements 
        WHERE created_by = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total_count'] ?? 0;
    
    // Get scheduled announcements
    $stmt = $conn->prepare("
        SELECT COUNT(*) as scheduled_count 
        FROM course_announcements 
        WHERE created_by = ? AND status = 'Scheduled'
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $scheduled = $stmt->get_result()->fetch_assoc()['scheduled_count'] ?? 0;
    
    // Get read rate
    $stmt = $conn->prepare("
        SELECT AVG(CASE WHEN s.total_recipients > 0 
                THEN (s.read_count / s.total_recipients) * 100
                ELSE 0 
            END) as avg_read_rate
        FROM course_announcements a
        JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        WHERE a.created_by = ?
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $readRate = $stmt->get_result()->fetch_assoc()['avg_read_rate'] ?? 0;
    
    // Get email open rate
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN delivery_status = 'Read' THEN 1 ELSE 0 END) as opened,
            COUNT(*) as total
        FROM announcement_delivery_logs 
        WHERE delivery_channel = 'Email'
        AND announcement_id IN (SELECT announcement_id FROM course_announcements WHERE created_by = ?)
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $emailOpenRate = ($result['total'] > 0) ? ($result['opened'] / $result['total']) * 100 : 0;
    
    echo json_encode([
        'total_announcements' => $total,
        'scheduled_count' => $scheduled,
        'read_rate' => round($readRate, 0),
        'email_open_rate' => round($emailOpenRate, 0)
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to fetch announcement stats']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>