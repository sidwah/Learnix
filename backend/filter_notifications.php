<?php
require 'session_start.php';
require 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$user_id = $_SESSION['user_id'];
$type = isset($_GET['type']) ? $_GET['type'] : 'all';
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build the query with filters
$query = "SELECT notification_id, title, message, type, is_read, is_hidden, created_at 
          FROM user_notifications 
          WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if ($type !== 'all') {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= "s";
}
if ($status !== 'all') {
    if ($status === 'hidden') {
        $query .= " AND is_hidden = 1";
    } else {
        $query .= " AND is_hidden = 0 AND is_read = ?";
        $params[] = $status === 'unread' ? 0 : 1;
        $types .= "i";
    }
} else {
    $query .= " AND is_hidden = 0"; // Default: exclude hidden notifications
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = [
        'notification_id' => $row['notification_id'],
        'title' => $row['title'],
        'message' => $row['message'],
        'type' => $row['type'],
        'is_read' => $row['is_read'],
        'is_hidden' => $row['is_hidden'],
        'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at']))
    ];
}

// Get unread count for non-hidden notifications
$stmt_count = $conn->prepare("SELECT COUNT(*) as unread_count FROM user_notifications WHERE user_id = ? AND is_read = 0 AND is_hidden = 0");
$stmt_count->bind_param("i", $user_id);
$stmt_count->execute();
$count_result = $stmt_count->get_result();
$unread_count = $count_result->fetch_assoc()['unread_count'];

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'unread_count' => $unread_count
]);

$stmt->close();
$stmt_count->close();
$conn->close();
?>