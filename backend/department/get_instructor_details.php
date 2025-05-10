<?php
require_once '../config.php';
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is department head
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

$departmentId = $_SESSION['department_id'];
$instructorId = intval($_GET['instructor_id'] ?? 0);

if ($instructorId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid instructor ID']);
    exit;
}

// Log the request for debugging
error_log("get_instructor_details.php - Instructor ID: $instructorId, Department ID: $departmentId");

try {
    // Get instructor details - also check inactive instructors
    $stmt = $conn->prepare("
        SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.profile_pic,
            i.instructor_id,
            di.status,
            di.created_at as joined_date,
            d.name as department_name
        FROM department_instructors di
        JOIN instructors i ON di.instructor_id = i.instructor_id
        JOIN users u ON i.user_id = u.user_id
        JOIN departments d ON di.department_id = d.department_id
        WHERE di.instructor_id = ? AND di.department_id = ?
    ");
    
    $stmt->bind_param("ii", $instructorId, $departmentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Instructor not found']);
        exit;
    }
    
    $instructor = $result->fetch_assoc();
    $stmt->close();
    
    // Get course statistics - only active courses
    $stmt = $conn->prepare("
        SELECT 
            COUNT(DISTINCT c.course_id) as active_courses,
            COUNT(DISTINCT e.user_id) as total_students
        FROM course_instructors ci
        JOIN courses c ON ci.course_id = c.course_id
        LEFT JOIN enrollments e ON c.course_id = e.course_id AND e.status = 'Active'
        WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
        AND c.deleted_at IS NULL
    ");
    
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
    
    // Get course ratings
    $stmt = $conn->prepare("
        SELECT AVG(rating) as average_rating
        FROM course_ratings cr
        JOIN course_instructors ci ON cr.course_id = ci.course_id
        WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
    ");
    
    $stmt->bind_param("i", $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $rating = $result->fetch_assoc();
    $stmt->close();
    
    // Get recent activity with better error handling
    $stmt = $conn->prepare("
        SELECT 
            'login' as activity_type,
            'Last login' as activity_description,
            u.updated_at as activity_date
        FROM users u
        WHERE u.user_id = ?
        
        UNION ALL
        
        SELECT 
            'course' as activity_type,
            CONCAT('Added course: ', c.title) as activity_description,
            c.created_at as activity_date
        FROM courses c
        JOIN course_instructors ci ON c.course_id = ci.course_id
        WHERE ci.instructor_id = ? AND ci.deleted_at IS NULL
        AND c.deleted_at IS NULL
        
        ORDER BY activity_date DESC
        LIMIT 5
    ");
    
    $stmt->bind_param("ii", $instructor['user_id'], $instructorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'type' => $row['activity_type'],
            'description' => $row['activity_description'],
            'date' => date('M d, Y H:i', strtotime($row['activity_date']))
        ];
    }
    $stmt->close();
    
    // Prepare response
    $response = [
        'instructor' => [
            'name' => $instructor['first_name'] . ' ' . $instructor['last_name'],
            'email' => $instructor['email'],
            'profile_pic' => $instructor['profile_pic'] ?? 'default.png',
            'status' => $instructor['status'],
            'joined_date' => date('M d, Y', strtotime($instructor['joined_date']))
        ],
        'stats' => [
            'active_courses' => $stats['active_courses'] ?? 0,
            'total_students' => $stats['total_students'] ?? 0,
            'average_rating' => $rating['average_rating'] ? number_format($rating['average_rating'], 1) : '0.0',
            'department' => $instructor['department_name'] ?? $_SESSION['department_name'] ?? 'N/A'
        ],
        'recent_activity' => $activities
    ];
    
    // Log the response for debugging
    error_log("get_instructor_details.php - Response prepared successfully for instructor $instructorId");
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_instructor_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error occurred', 'message' => $e->getMessage()]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>