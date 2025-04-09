<?php
// ajax/students/mark-topic-completed.php
// This endpoint marks a topic as completed when a video finishes playing

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Send error as JSON
function sendError($message, $code = 400) {
    header('Content-Type: application/json');
    http_response_code($code);
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    sendError('Unauthorized', 401);
}

// Check if required parameters are provided
if (!isset($_POST['enrollment_id']) || !isset($_POST['topic_id'])) {
    sendError('Missing required parameters');
}

// Get parameters
$enrollment_id = intval($_POST['enrollment_id']);
$topic_id = intval($_POST['topic_id']);
$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../../backend/config.php';

// Verify enrollment belongs to user
$verify_query = "SELECT e.enrollment_id, 
                       (SELECT completion_status FROM progress 
                        WHERE enrollment_id = e.enrollment_id AND topic_id = ?) as is_completed
                FROM enrollments e 
                WHERE e.enrollment_id = ? AND e.user_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("iii", $topic_id, $enrollment_id, $user_id);
$stmt->execute();
$verify_result = $stmt->get_result();

if ($verify_result->num_rows === 0) {
    sendError('Invalid enrollment');
}

$enrollment_data = $verify_result->fetch_assoc();
$is_completed = $enrollment_data['is_completed'] === 'Completed';

// If already marked as completed, just return success
if ($is_completed) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Topic already marked as completed',
        'already_completed' => true
    ]);
    exit();
}

// Check if there's already a progress record
$check_progress = "SELECT progress_id FROM progress WHERE enrollment_id = ? AND topic_id = ?";
$stmt = $conn->prepare($check_progress);
$stmt->bind_param("ii", $enrollment_id, $topic_id);
$stmt->execute();
$progress_check = $stmt->get_result();

if ($progress_check->num_rows > 0) {
    // Update existing progress
    $update_progress = "UPDATE progress 
                       SET completion_status = 'Completed', 
                           completion_date = NOW() 
                       WHERE enrollment_id = ? AND topic_id = ?";
    $stmt = $conn->prepare($update_progress);
    $stmt->bind_param("ii", $enrollment_id, $topic_id);
    $stmt->execute();
} else {
    // Insert new progress record
    $insert_progress = "INSERT INTO progress 
                      (enrollment_id, topic_id, completion_status, completion_date) 
                      VALUES (?, ?, 'Completed', NOW())";
    $stmt = $conn->prepare($insert_progress);
    $stmt->bind_param("ii", $enrollment_id, $topic_id);
    $stmt->execute();
}

// Calculate overall course progress to update enrollments table
$progress_query = "SELECT 
                    COUNT(DISTINCT CASE WHEN p.completion_status = 'Completed' THEN st.topic_id END) as completed_topics,
                    COUNT(DISTINCT st.topic_id) as total_topics
                   FROM course_sections cs
                   JOIN section_topics st ON cs.section_id = st.section_id
                   LEFT JOIN progress p ON st.topic_id = p.topic_id AND p.enrollment_id = ?
                   WHERE cs.course_id = (SELECT course_id FROM enrollments WHERE enrollment_id = ?)";
$stmt = $conn->prepare($progress_query);
$stmt->bind_param("ii", $enrollment_id, $enrollment_id);
$stmt->execute();
$progress_result = $stmt->get_result();
$progress_data = $progress_result->fetch_assoc();

$completed_percentage = 0;
if ($progress_data['total_topics'] > 0) {
    $completed_percentage = round(($progress_data['completed_topics'] / $progress_data['total_topics']) * 100);
}

// Update the completion percentage in enrollments table
$update_enrollment = "UPDATE enrollments 
                     SET completion_percentage = ?, 
                         last_accessed = NOW()
                     WHERE enrollment_id = ?";
$stmt = $conn->prepare($update_enrollment);
$stmt->bind_param("di", $completed_percentage, $enrollment_id);
$update_success = $stmt->execute();

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => $update_success, 
    'message' => $update_success ? 'Topic marked as completed' : 'Failed to update enrollment',
    'completion_percentage' => $completed_percentage,
    'completed_topics' => $progress_data['completed_topics'],
    'total_topics' => $progress_data['total_topics']
]);

// Close database connection
$stmt->close();
$conn->close();
?>