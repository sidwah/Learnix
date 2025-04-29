<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];

// Check if announcement_id is provided
if (!isset($_GET['announcement_id'])) {
    echo json_encode(['success' => false, 'message' => 'Announcement ID is required']);
    exit;
}

$announcement_id = intval($_GET['announcement_id']);

try {
    // First, check if the announcement belongs to this instructor
    $stmt = $conn->prepare("SELECT a.*, c.title AS course_title 
                           FROM course_announcements a 
                           LEFT JOIN courses c ON a.course_id = c.course_id 
                           WHERE a.announcement_id = ? AND a.created_by = ?");
    $stmt->bind_param("ii", $announcement_id, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Announcement not found or you do not have permission to edit it']);
        exit;
    }
    
    $announcement = $result->fetch_assoc();
    
    // Get any attachments
    $attachments = [];
    $stmt = $conn->prepare("SELECT * FROM announcement_attachments WHERE announcement_id = ?");
    $stmt->bind_param("i", $announcement_id);
    $stmt->execute();
    $attachmentResult = $stmt->get_result();
    
    while ($row = $attachmentResult->fetch_assoc()) {
        $attachments[] = $row;
    }
    
    echo json_encode([
        'success' => true, 
        'announcement' => $announcement,
        'attachments' => $attachments
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>