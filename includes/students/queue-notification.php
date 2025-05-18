<?php
// includes/students/queue-notification.php

require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

header('Content-Type: application/json');

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
$user_id = isset($data['user_id']) ? (int)$data['user_id'] : 0;
$notification_type = $data['notification_type'] ?? '';
$message = $data['message'] ?? '';
$title = $data['title'] ?? 'System Notification';

// Validate session user matches requested user_id
if ($_SESSION['user_id'] != $user_id) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!$user_id || !$notification_type || !$message) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

try {
    // Insert notification into database
    $query = "INSERT INTO user_notifications 
              (user_id, type, title, message, related_id, related_type) 
              VALUES (?, ?, ?, ?, ?, ?)";
    
    $related_id = $data['related_id'] ?? null;
    $related_type = $data['related_type'] ?? null;
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssss", $user_id, $notification_type, $title, $message, $related_id, $related_type);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success) {
        echo json_encode(['success' => true, 'notification_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save notification']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
}

$conn->close();
?>