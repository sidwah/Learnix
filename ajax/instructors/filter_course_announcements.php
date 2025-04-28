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
$course_id = isset($_GET['course_id']) ? $_GET['course_id'] : 'all';

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
            a.created_at,
            c.title AS course_title,
            c.course_id,
            u.first_name,
            u.last_name
        FROM course_announcements a
        JOIN courses c ON a.course_id = c.course_id
        JOIN users u ON a.created_by = u.user_id
        WHERE c.instructor_id = ? AND a.created_by != ? AND a.status = 'Published'
    ";
    
    // Add course filter if not 'all'
    $params = [$instructor_id, $user_id];
    $types = "ii";
    
    if ($course_id !== 'all') {
        $query .= " AND c.course_id = ?";
        $params[] = $course_id;
        $types .= "i";
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courseAnnouncements = [];
    while ($row = $result->fetch_assoc()) {
        $courseAnnouncements[] = $row;
    }
    
    echo json_encode([
        'courseAnnouncements' => $courseAnnouncements
    ]);
    
} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode(['error' => 'Failed to filter course announcements']);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>