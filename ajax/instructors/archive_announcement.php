<?php
require_once '../../backend/config.php';
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$user_id = $_SESSION['user_id'];
$announcement_id = isset($_POST['announcement_id']) ? (int)$_POST['announcement_id'] : 0;

if (!$announcement_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit;
}

try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Verify ownership
    $stmt = $conn->prepare("SELECT created_by FROM course_announcements WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found']);
        exit;
    }
    
    $row = $result->fetch_assoc();
    if ($row['created_by'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You are not authorized to archive this announcement']);
        exit;
    }
    
    // Archive the announcement
    $stmt = $conn->prepare("UPDATE course_announcements SET status = 'Archived' WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to archive announcement']);
    }
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again later.']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>