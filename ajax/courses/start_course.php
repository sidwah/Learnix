<?php
// Strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent any previous output
ob_clean();

// Include necessary files
require_once '../../backend/session_start.php';
require_once '../../backend/config.php';

// Set JSON content type
header('Content-Type: application/json; charset=utf-8');

// Log error function
function log_error($message) {
    error_log("[Course Creation Error] " . $message);
}

// Send JSON response function
function send_json_response($success, $message, $additional_data = []) {
    // Prepare response array
    $response = [
        'success' => $success,
        'message' => $message
    ];

    // Merge additional data
    if (!empty($additional_data)) {
        $response = array_merge($response, $additional_data);
    }

    // Output JSON
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit;
}

// Validate session
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true) {
    log_error('Not signed in');
    send_json_response(false, 'Please log in to create a course.');
}

// Check instructor role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'instructor') {
    log_error('Not an instructor');
    send_json_response(false, 'You must be an instructor to create a course.');
}

// Verify instructor ID
if (!isset($_SESSION['instructor_id'])) {
    log_error('Instructor ID not found');
    send_json_response(false, 'Instructor ID is missing. Please log in again.');
}

// Validate database connection
if (!$conn) {
    log_error('Database connection failed');
    send_json_response(false, 'Database connection error.');
}

// First, find a default subcategory
$default_subcategory_query = $conn->prepare("SELECT subcategory_id FROM subcategories LIMIT 1");
$default_subcategory_query->execute();
$default_subcategory_result = $default_subcategory_query->get_result();

if (!$default_subcategory_result || $default_subcategory_result->num_rows === 0) {
    log_error('No subcategories found');
    send_json_response(false, 'No subcategories available. Please contact support.');
}

$default_subcategory = $default_subcategory_result->fetch_assoc()['subcategory_id'];
$default_subcategory_query->close();

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO courses (
    instructor_id, 
    title, 
    short_description, 
    status, 
    creation_step, 
    subcategory_id,
    created_at, 
    updated_at
) VALUES (
    ?, 
    'Untitled Course', 
    'Draft course description', 
    'Draft', 
    1, 
    ?,
    NOW(), 
    NOW()
)");

// Check statement preparation
if (!$stmt) {
    log_error('Statement preparation failed: ' . $conn->error);
    send_json_response(false, 'Failed to prepare course creation statement.');
}

// Bind parameters
$instructor_id = $_SESSION['instructor_id'];
$stmt->bind_param("ii", $instructor_id, $default_subcategory);

// Start transaction
$conn->autocommit(FALSE);

// Execute statement
if (!$stmt->execute()) {
    // Rollback transaction
    $conn->rollback();
    
    log_error('Course insertion failed: ' . $stmt->error);
    send_json_response(false, 'Failed to create course: ' . $stmt->error);
}

// Get the ID of the newly created course
$new_course_id = $conn->insert_id;

// Commit transaction
$conn->commit();

// Restore autocommit
$conn->autocommit(TRUE);

// Close statement
$stmt->close();

// Send successful response
send_json_response(true, 'Course created successfully', [
    'course_id' => $new_course_id,
    'default_subcategory' => $default_subcategory,
    'redirect' => 'course-creator.php?course_id=' . $new_course_id
]);

// Close connection
$conn->close();