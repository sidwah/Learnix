<?php
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if the user is signed in and has the 'instructor' role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Unauthorized access',
        'data' => []
    ]);
    exit;
}

// Get instructor ID from POST data (sent by all-students.php)
$instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 0;

// Validate instructor ID by mapping user_id to instructor_id
$user_id = $_SESSION['user_id'];
$query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($db_instructor_id);
    $stmt->fetch();
    // Ensure the POST instructor_id matches the database instructor_id
    if ($instructor_id !== $db_instructor_id) {
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Invalid instructor ID',
            'data' => []
        ]);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Instructor not found',
        'data' => []
    ]);
    exit;
}
$stmt->close();

// Get filters if provided
$course_filter = isset($_POST['course']) ? intval($_POST['course']) : 0;
$status_filter = isset($_POST['status']) ? trim($_POST['status']) : '';
$activity_filter = isset($_POST['activity']) ? trim($_POST['activity']) : '';

// Log the received parameters for debugging
error_log("get_students.php received filters: course=$course_filter, status=$status_filter, activity=$activity_filter, instructor_id=$instructor_id");

// Base query for fetching all student data
$query = "SELECT 
            u.user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.profile_pic,
            MAX(e.enrolled_at) as enrolled_at,
            MAX(e.last_accessed) as last_activity,
            COUNT(DISTINCT e.course_id) as enrolled_courses,
            AVG(e.completion_percentage) as avg_completion,
            (
                SELECT AVG(sqa.score) 
                FROM student_quiz_attempts sqa
                JOIN section_quizzes sq ON sqa.quiz_id = sq.quiz_id
                JOIN course_sections cs ON sq.section_id = cs.section_id
                JOIN courses c_sub ON cs.course_id = c_sub.course_id
                JOIN course_instructors ci_sub ON c_sub.course_id = ci_sub.course_id
                WHERE sqa.user_id = u.user_id AND ci_sub.instructor_id = ?
            ) as quiz_avg,
            MAX(e.status) as status
          FROM users u
          JOIN enrollments e ON u.user_id = e.user_id
          JOIN courses c ON e.course_id = c.course_id
          JOIN course_instructors ci ON c.course_id = ci.course_id
          WHERE ci.instructor_id = ? ";

// Parameters array with types
$params = [$instructor_id, $instructor_id];
$param_types = "ii";

// Add course filter if provided
if ($course_filter > 0) {
    $query .= " AND c.course_id = ? ";
    $params[] = $course_filter;
    $param_types .= "i";
}

// Add status filter if provided
if (!empty($status_filter)) {
    $query .= " AND e.status = ? ";
    $params[] = $status_filter;
    $param_types .= "s"; // Status is a string
    error_log("Applied status filter: '$status_filter'");
}

// Add activity filter if provided
if (!empty($activity_filter)) {
    switch ($activity_filter) {
        case 'active-now':
            $query .= " AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 7 DAY) ";
            break;
        case 'active-recent':
            $query .= " AND e.last_accessed >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                       AND e.last_accessed < DATE_SUB(NOW(), INTERVAL 7 DAY) ";
            break;
        case 'inactive':
            $query .= " AND (e.last_accessed < DATE_SUB(NOW(), INTERVAL 30 DAY) OR e.last_accessed IS NULL) ";
            break;
    }
}

// Group by user and add order
$query .= " GROUP BY u.user_id ORDER BY enrolled_at DESC";

// Log the final query and parameters for debugging
error_log("Final SQL query: $query");
error_log("Parameters: " . json_encode($params));

try {
    // Prepare and execute query
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    
    // Bind parameters
    if (count($params) > 0) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all records
    $records = [];
    while ($row = $result->fetch_assoc()) {
        // Format data for display
        $records[] = [
            'user_id' => $row['user_id'],
            'first_name' => htmlspecialchars($row['first_name']),
            'last_name' => htmlspecialchars($row['last_name']),
            'email' => htmlspecialchars($row['email']),
            'profile_pic' => $row['profile_pic'] ?: 'default.png',
            'enrolled_at' => $row['enrolled_at'],
            'last_activity' => $row['last_activity'],
            'enrolled_courses' => intval($row['enrolled_courses']),
            'avg_completion' => number_format(floatval($row['avg_completion'] ?? 0), 1),
            'quiz_avg' => number_format(floatval($row['quiz_avg'] ?? 0), 1),
            'status' => $row['status'] ?: 'Unknown'
        ];
    }
    
    // Close statement and connection
    $stmt->close();
    $conn->close();
    
    // Return success response with data
    header('Content-Type: application/json');
    echo json_encode([
        'data' => $records
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log('Error in get_students.php: ' . $e->getMessage());
    
    // Close connection if open
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Database error',
        'message' => 'An error occurred while fetching student data: ' . $e->getMessage(),
        'data' => []
    ]);
}
?>