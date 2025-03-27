<?php
// Include necessary files
require_once '../../backend/config.php';
session_start();

// Check if user is logged in and is an instructor
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'instructor') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

// Get instructor_id from user_id
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT instructor_id, verification_status FROM instructors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Instructor not found']);
    exit;
}

$instructor = $result->fetch_assoc();
$instructor_id = $instructor['instructor_id'];
$verification_status = $instructor['verification_status'];

// Check if instructor is verified
if ($verification_status !== 'verified') {
    echo json_encode([
        'status' => 'error', 
        'message' => 'You need to be verified as an instructor before publishing courses',
        'code' => 'verification_required'
    ]);
    exit;
}

// Validate input data
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Course ID is required']);
    exit;
}

$course_id = intval($_POST['course_id']);
$notes = isset($_POST['notes']) ? $_POST['notes'] : '';
$terms_accepted = isset($_POST['terms_accepted']) && $_POST['terms_accepted'] === 'true';

// Check if terms were accepted
if (!$terms_accepted) {
    echo json_encode(['status' => 'error', 'message' => 'You must accept the terms and conditions']);
    exit;
}

// Check if course exists and belongs to instructor
$stmt = $conn->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->bind_param("ii", $course_id, $instructor_id);
$stmt->execute();
$course_result = $stmt->get_result();

if ($course_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Course not found or not authorized']);
    exit;
}

$course = $course_result->fetch_assoc();

// Validate course content automatically
$stmt = $conn->prepare("
    SELECT * FROM content_validation_logs 
    WHERE course_id = ? 
    ORDER BY validation_date DESC LIMIT 1
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$validation_result = $stmt->get_result();

if ($validation_result->num_rows > 0) {
    $validation = $validation_result->fetch_assoc();
    $validation_data = json_decode($validation['validation_results'], true);
    
    // Check if there are any issues
    if (!$validation_data['valid']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fix all validation issues before publishing',
            'code' => 'validation_failed',
            'issues' => $validation_data['issues'],
            'issue_count' => count($validation_data['issues'])
        ]);
        exit;
    }
} else {
    // No validation has been performed, run validation now
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => rtrim($config['base_url'], '/') . '/ajax/courses/validate.php',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => ['course_id' => $course_id],
        CURLOPT_COOKIE => session_name() . '=' . session_id()
    ]);
    
    $response = curl_exec($curl);
    curl_close($curl);
    
    $validation_data = json_decode($response, true);
    
    if ($validation_data['status'] === 'success' && !$validation_data['valid']) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fix all validation issues before publishing',
            'code' => 'validation_failed',
            'issues' => $validation_data['issues'],
            'issue_count' => $validation_data['issue_count']
        ]);
        exit;
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // Update course status to Published
    $stmt = $conn->prepare("
        UPDATE courses 
        SET status = 'Published', updated_at = NOW() 
        WHERE course_id = ?
    ");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    
    // Create review request if needed
    if ($course['approval_status'] === 'Pending') {
        $stmt = $conn->prepare("
            INSERT INTO course_review_requests 
            (course_id, requested_by, request_notes, status, created_at)
            VALUES (?, ?, ?, 'Pending', NOW())
        ");
        $stmt->bind_param("iis", $course_id, $user_id, $notes);
        $stmt->execute();
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Course submitted for publication successfully',
        'approval_status' => $course['approval_status']
    ]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => 'Failed to publish course: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>