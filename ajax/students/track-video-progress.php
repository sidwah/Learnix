<?php
// ajax/students/track-video-progress.php
// This endpoint tracks student progress through video content

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
if (!isset($_POST['enrollment_id']) || !isset($_POST['topic_id']) || !isset($_POST['position'])) {
    sendError('Missing required parameters');
}

// Get parameters
$enrollment_id = intval($_POST['enrollment_id']);
$topic_id = intval($_POST['topic_id']);
$position = intval($_POST['position']);
$completed = isset($_POST['completed']) && $_POST['completed'] == '1';
$user_id = $_SESSION['user_id'];

// Connect to database
require_once '../../backend/config.php';

// Verify enrollment belongs to user
$verify_query = "SELECT e.enrollment_id 
                FROM enrollments e 
                WHERE e.enrollment_id = ? AND e.user_id = ?";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $enrollment_id, $user_id);
$stmt->execute();
$verify_result = $stmt->get_result();

if ($verify_result->num_rows === 0) {
    sendError('Invalid enrollment');
}

// Check if progress record exists and get current status
$check_query = "SELECT progress_id, completion_status FROM progress WHERE enrollment_id = ? AND topic_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $enrollment_id, $topic_id);
$stmt->execute();
$check_result = $stmt->get_result();

if ($check_result->num_rows > 0) {
    // Update existing progress
    $progress_data = $check_result->fetch_assoc();
    $progress_id = $progress_data['progress_id'];
    $current_status = $progress_data['completion_status'];
    
    // Determine new status - keep completed if already completed
    $new_status = ($current_status == 'Completed' || $completed) ? 'Completed' : 'In Progress';
    
    // Update the progress record
    $update_query = "UPDATE progress SET 
                    completion_status = ?,
                    last_position = ?,
                    " . ($completed && $current_status != 'Completed' ? "completion_date = NOW()," : "") . "
                    time_spent = time_spent + 10
                    WHERE progress_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sii", $new_status, $position, $progress_id);
    $success = $stmt->execute();
} else {
    // Create new progress record
    $new_status = $completed ? 'Completed' : 'In Progress';
    $insert_query = "INSERT INTO progress 
                    (enrollment_id, topic_id, completion_status, last_position, time_spent" . ($completed ? ", completion_date" : "") . ") 
                    VALUES (?, ?, ?, ?, 10" . ($completed ? ", NOW()" : "") . ")";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iisi", $enrollment_id, $topic_id, $new_status, $position);
    $success = $stmt->execute();
}

// Update the enrollments table if completed
if ($completed && $success) {
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

    // Update the completion percentage and last access in enrollments table
    $update_enrollment = "UPDATE enrollments 
                         SET completion_percentage = ?, 
                             last_accessed = NOW(),
                             current_topic_id = ?
                         WHERE enrollment_id = ?";
    $stmt = $conn->prepare($update_enrollment);
    $stmt->bind_param("dii", $completed_percentage, $topic_id, $enrollment_id);
    $stmt->execute();
}

// Update the last_accessed timestamp in enrollments table
$update_last_accessed = "UPDATE enrollments 
                       SET last_accessed = NOW(),
                           current_topic_id = ?
                       WHERE enrollment_id = ?";
$stmt = $conn->prepare($update_last_accessed);
$stmt->bind_param("ii", $topic_id, $enrollment_id);
$stmt->execute();

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'success' => $success, 
    'message' => $success ? 'Progress updated' : 'Failed to update progress',
    'position' => $position,
    'status' => $new_status,
    'completed' => $completed,
    'auto_completed' => $completed
]);

// Close database connection
$stmt->close();
$conn->close();
?>