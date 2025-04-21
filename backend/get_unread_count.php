<?php
require 'session_start.php';
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT COUNT(*) as unread_count FROM user_notifications WHERE user_id = ? AND is_read = 0 AND is_hidden = 0");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'unread_count' => $row['unread_count']
]);

$stmt->close();
$conn->close();
?>