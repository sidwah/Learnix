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
$search = isset($_GET['search']) ? $_GET['search'] : '';

if (empty($search)) {
    echo json_encode(['error' => 'Search term is required']);
    exit;
}

try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Search in my announcements
    $searchParam = "%$search%";
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
        WHERE a.created_by = ? AND (a.title LIKE ? OR a.content LIKE ?)
        ORDER BY a.created_at DESC
    ");
    
    $stmt->bind_param("iss", $user_id, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $myAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $myAnnouncements[] = $row;
    }
    
    // Search in course announcements
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
        WHERE c.instructor_id = ? AND a.created_by != ? 
        AND a.status = 'Published' AND (a.title LIKE ? OR a.content LIKE ?)
        ORDER BY a.created_at DESC
    ");
    
    $stmt->bind_param("iiss", $instructor_id, $user_id, $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courseAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $courseAnnouncements[] = $row;
    }
    
    echo json_encode([
        'myAnnouncements' => $myAnnouncements,
        'courseAnnouncements' => $courseAnnouncements
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to search announcements']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>