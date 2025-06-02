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

// Simulate success without saving to database
echo json_encode([
    'success' => true,
    'notification_id' => null, // or any dummy value if needed
    'message' => 'Notification queued (not saved to database)'
]);
exit;
?>
