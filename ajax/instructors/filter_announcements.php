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
$status = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // Connect to database
    $conn = new mysqli('localhost', 'root', 'root', 'learnix_db');
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Base query
    $query = "
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
    ";
    
    // Add status filter if not 'all'
    $params = [$user_id];
    $types = "i";
    
    if ($status !== 'all') {
        $query .= " AND a.status = ?";
        $params[] = $status;
        $types .= "s";
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $myAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $myAnnouncements[] = $row;
    }
    
    echo json_encode([
        'myAnnouncements' => $myAnnouncements
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to filter announcements']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>