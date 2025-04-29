<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is logged in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['user_id']; // Using user_id since templates are created by users

try {
    // Fetch templates created by this instructor
    $query = "SELECT t.*, 
              (SELECT COUNT(*) FROM course_announcements WHERE title LIKE CONCAT('%', t.title, '%') AND created_by = ?) as usage_count,
              (SELECT MAX(created_at) FROM course_announcements WHERE title LIKE CONCAT('%', t.title, '%') AND created_by = ?) as last_used
              FROM announcement_templates t 
              WHERE t.created_by = ? AND t.is_active = 1
              ORDER BY t.updated_at DESC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $instructor_id, $instructor_id, $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $templates = [];
    while ($row = $result->fetch_assoc()) {
        // Format the dates for display
        $row['created_at_formatted'] = date('M d, Y', strtotime($row['created_at']));
        $row['last_used_formatted'] = $row['last_used'] ? date('M d, Y', strtotime($row['last_used'])) : 'Never';
        
        // Truncate content for preview
        $row['content_preview'] = mb_substr(strip_tags($row['content']), 0, 100) . (mb_strlen(strip_tags($row['content'])) > 100 ? '...' : '');
        
        $templates[] = $row;
    }
    
    echo json_encode(['success' => true, 'templates' => $templates]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    exit;
}
?>