<?php
// ajax/department/mark_all_notifications_read.php
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Ensure only admins can access this endpoint
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$admin_id = $_SESSION['user_id'];

try {
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');

    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Update all notifications as read
    $query = "UPDATE user_notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0 AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();

    echo json_encode(['success' => true, 'count' => $stmt->affected_rows]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
