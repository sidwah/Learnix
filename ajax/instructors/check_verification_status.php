<?php
// ajax/instructors/check_verification_status.php - UPDATED for institutional LMS
require_once '../../backend/config.php';
require_once '../../backend/session_start.php';

// Check if user is signed in and is an instructor
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id for the current user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Instructor profile not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];
$stmt->close();

// Check if course_id was provided
$course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;

if ($course_id) {
    // Check if instructor is the primary instructor for this course
    $stmt = $conn->prepare("
        SELECT is_primary 
        FROM course_instructors 
        WHERE course_id = ? 
        AND instructor_id = ?
        AND deleted_at IS NULL
    ");
    $stmt->bind_param("ii", $course_id, $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'authorized' => false,
            'isPrimary' => false,
            'message' => 'You are not assigned to this course.'
        ]);
        exit;
    }
    
    $instructor_role = $result->fetch_assoc();
    $is_primary = (bool)$instructor_role['is_primary'];
    
    // Return instructor status for this course
    echo json_encode([
        'success' => true,
        'authorized' => true,
        'isPrimary' => $is_primary,
        'message' => $is_primary ? 'You are the primary instructor for this course.' : 'You are a co-instructor for this course.'
    ]);
} else {
    // Check if instructor belongs to any department
    $stmt = $conn->prepare("
        SELECT department_id 
        FROM department_instructors 
        WHERE instructor_id = ? 
        AND status = 'active'
        AND deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $has_department = ($result->num_rows > 0);
    
    // Return general instructor status
    echo json_encode([
        'success' => true,
        'authorized' => $has_department,
        'hasDepartment' => $has_department,
        'message' => $has_department ? 'Instructor is assigned to at least one department.' : 'Instructor is not assigned to any department.'
    ]);
}
?>