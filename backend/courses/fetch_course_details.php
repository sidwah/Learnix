<?php
// backend/courses/fetch_course_details.php

// Include database connection
require_once '../config.php';
session_start();

// Set headers
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'course' => null,
    'outcomes' => [],
    'requirements' => []
];

// Check if user is logged in and has department head role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'department_head') {
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit;
}

// Check if course_id is provided
if (!isset($_GET['course_id']) || empty($_GET['course_id'])) {
    $response['message'] = 'Course ID is required';
    echo json_encode($response);
    exit;
}

$course_id = filter_var($_GET['course_id'], FILTER_VALIDATE_INT);
if ($course_id === false) {
    $response['message'] = 'Invalid Course ID';
    echo json_encode($response);
    exit;
}

try {
    // Get user's department
    $dept_query = "SELECT ds.department_id 
                   FROM department_staff ds 
                   WHERE ds.user_id = ? AND ds.role = 'head' AND ds.status = 'active' AND ds.deleted_at IS NULL
                   LIMIT 1";
    $dept_stmt = $conn->prepare($dept_query);
    $dept_stmt->bind_param("i", $_SESSION['user_id']);
    $dept_stmt->execute();
    $dept_result = $dept_stmt->get_result();
    
    if ($dept_result->num_rows === 0) {
        $response['message'] = 'Department not found';
        echo json_encode($response);
        exit;
    }
    
    $department = $dept_result->fetch_assoc();
    $department_id = $department['department_id'];
    
    // Fetch course details - make sure the course belongs to the user's department
    $query = "
        SELECT 
            c.course_id,
            c.title,
            c.short_description,
            c.full_description,
            c.subcategory_id,
            sc.category_id,
            c.course_level,
            c.price,
            c.thumbnail,
            c.status,
            c.certificate_enabled,
            c.access_level,
            cs.enrollment_limit,
            cs.visibility,
            cs.access_password,
            cs.estimated_duration
        FROM courses c
        LEFT JOIN course_settings cs ON c.course_id = cs.course_id
        LEFT JOIN subcategories sc ON c.subcategory_id = sc.subcategory_id
        WHERE c.course_id = ?
        AND c.department_id = ?
        AND c.deleted_at IS NULL
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ii', $course_id, $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();
    
    if (!$course) {
        $response['message'] = 'Course not found or unauthorized';
        echo json_encode($response);
        exit;
    }
    
    // Fetch learning outcomes
    $outcome_query = "
        SELECT outcome_id, outcome_text
        FROM course_learning_outcomes
        WHERE course_id = ?
        ORDER BY outcome_id
    ";
    
    $stmt = $conn->prepare($outcome_query);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $outcomes = [];
    while ($row = $result->fetch_assoc()) {
        $outcomes[] = $row['outcome_text'];
    }
    
    // Fetch requirements
    $requirement_query = "
        SELECT requirement_id, requirement_text
        FROM course_requirements
        WHERE course_id = ?
        ORDER BY requirement_id
    ";
    
    $stmt = $conn->prepare($requirement_query);
    $stmt->bind_param('i', $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $requirements = [];
    while ($row = $result->fetch_assoc()) {
        $requirements[] = $row['requirement_text'];
    }
    
    // Prepare response
    $response['success'] = true;
    $response['course'] = [
        'course_id' => $course['course_id'],
        'title' => $course['title'],
        'short_description' => $course['short_description'],
        'full_description' => $course['full_description'],
        'category_id' => $course['category_id'],
        'subcategory_id' => $course['subcategory_id'],
        'course_level' => $course['course_level'],
        'price' => $course['price'],
        'access_level' => $course['access_level'] ?? 'Public',
        'visibility' => $course['visibility'] ?? 'Public',
        'enrollment_limit' => $course['enrollment_limit'],
        'access_password' => $course['access_password'],
        'certificate_enabled' => $course['certificate_enabled'],
        'estimated_duration' => $course['estimated_duration'],
        'status' => $course['status']
    ];
    
    // Add thumbnail URL if exists
    if (!empty($course['thumbnail'])) {
        $response['course']['thumbnail'] = $course['thumbnail'];
        $response['course']['thumbnail_url'] = '../uploads/thumbnails/' . $course['thumbnail'];
    }
    
    $response['outcomes'] = $outcomes;
    $response['requirements'] = $requirements;
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Error in fetch_course_details.php: ' . $e->getMessage());
}

// Output response
echo json_encode($response);
exit;
?>