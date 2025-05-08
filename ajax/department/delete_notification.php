<?php
// ajax/department/delete_notification.php
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Ensure only admins can access this endpoint
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if notification ID is provided
if (!isset($_POST['notification_id']) || empty($_POST['notification_id'])) {
    echo json_encode(['success' => false, 'error' => 'Notification ID is required']);
    exit;
}

$admin_id = $_SESSION['user_id'];
$notification_id = intval(str_replace('notification-', '', $_POST['notification_id']));

try {
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Instead of hard-deleting, we set is_deleted flag
    $query = "UPDATE user_notifications SET is_deleted = 1 WHERE notification_id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $notification_id, $admin_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Notification not found or already deleted']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
