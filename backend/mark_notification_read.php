<?php
require 'session_start.php';
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'No notification ID provided']);
    exit;
}

$notification_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Verify the notification belongs to the user and update
$stmt = $conn->prepare("UPDATE user_notifications SET is_read = 1 WHERE notification_id = ? AND user_id = ?");
$stmt->bind_param("ii", $notification_id, $user_id);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

$stmt->close();
$conn->close();
?>