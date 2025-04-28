<?php
require_once '../../backend/config.php';
require '../../backend/session_start.php';

// Check if user is signed in as instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$instructor_id = $_SESSION['instructor_id'];
$user_id = $_SESSION['user_id'];

try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get instructor's announcements
    $stmt = $conn->prepare("
        SELECT 
            a.announcement_id, 
            a.title, 
            a.content, 
            a.importance, 
            a.status, 
            a.is_pinned,
            a.created_at,
            a.scheduled_at,
            a.expires_at,
            c.title AS course_title,
            c.course_id,
            s.read_count,
            s.total_recipients,
            CASE WHEN s.total_recipients > 0 
                THEN ROUND((s.read_count / s.total_recipients) * 100, 0)
                ELSE 0 
            END AS read_percentage
        FROM course_announcements a
        LEFT JOIN courses c ON a.course_id = c.course_id
        LEFT JOIN announcement_statistics s ON a.announcement_id = s.announcement_id
        WHERE a.created_by = ?
        ORDER BY a.created_at DESC
    ");
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $myAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $myAnnouncements[] = $row;
    }
    
    // Get course announcements (from other instructors)
    $stmt = $conn->prepare("
        SELECT 
            a.announcement_id, 
            a.title, 
            a.content, 
            a.importance, 
            a.status,
            a.created_at,
            c.title AS course_title,
            c.course_id,
            u.first_name,
            u.last_name
        FROM course_announcements a
        JOIN courses c ON a.course_id = c.course_id
        JOIN users u ON a.created_by = u.user_id
        WHERE c.instructor_id = ? AND a.created_by != ? AND a.status = 'Published'
        ORDER BY a.created_at DESC
    ");
    
    $stmt->bind_param("ii", $instructor_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courseAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $courseAnnouncements[] = $row;
    }
    
    // Get system announcements
    $stmt = $conn->prepare("
        SELECT 
            a.announcement_id, 
            a.title, 
            a.content, 
            a.importance, 
            a.status,
            a.created_at,
            u.first_name,
            u.last_name
        FROM course_announcements a
        JOIN users u ON a.created_by = u.user_id
        WHERE a.is_system_wide = 1 
          AND (a.target_roles IS NULL OR a.target_roles LIKE '%instructor%')
          AND a.status = 'Published'
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $systemAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $systemAnnouncements[] = $row;
    }
    
    echo json_encode([
        'myAnnouncements' => $myAnnouncements,
        'courseAnnouncements' => $courseAnnouncements,
        'systemAnnouncements' => $systemAnnouncements
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to load announcements']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>