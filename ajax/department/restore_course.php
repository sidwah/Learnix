<?php
// ajax/department/restore_course.php
// Include database connection
require_once '../../backend/config.php';

// Check if admin is logged in
session_start();
// Check if the user is signed in and is a department staff member
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || !isset($_SESSION['department_id']) || !isset($_SESSION['role']) || !in_array($_SESSION['role'], ['department_head', 'department_secretary'])) {
    // Log unauthorized access attempt for security auditing
    error_log("Unauthorized access attempt to protected page: " . $_SERVER['REQUEST_URI'] . " | IP: " . $_SERVER['REMOTE_ADDR']);

    // Redirect unauthorized users to the sign-in page
    header('Location: signin.php');
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'An unknown error occurred'
];

// Check if course_id is set
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $courseId = intval($_POST['course_id']);
    $adminId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    // Check if the course was previously approved
    $checkQuery = "
        SELECT approval_status 
        FROM courses 
        WHERE course_id = ?
    ";

    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $course = $result->fetch_assoc();

    if ($course && $course['approval_status'] === 'Approved') {
        // Restore course to published status
        $updateCourseQuery = "
            UPDATE courses 
            SET status = 'Published'
            WHERE course_id = ?
        ";

        $stmt = $conn->prepare($updateCourseQuery);
        $stmt->bind_param('i', $courseId);
        $stmt->execute();

        // Insert log entry for restoration
        $logData = json_encode([
            'status' => 'restored',
            'restored_by' => $adminId,
            'restored_date' => date('Y-m-d H:i:s'),
            'note' => 'Course restored by administrator'
        ]);

        $logQuery = "
            INSERT INTO content_validation_logs 
            (course_id, validation_type, validation_results, validation_date, validated_by) 
            VALUES (?, 'Manual', ?, NOW(), ?)
        ";

        $stmt = $conn->prepare($logQuery);
        $stmt->bind_param('isi', $courseId, $logData, $adminId);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        $response['status'] = 'success';
        $response['message'] = 'Course restored successfully';
    } else {
        $response['message'] = 'Unable to restore course. Course must be previously approved.';
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response['message'] = 'Error restoring course: ' . $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
