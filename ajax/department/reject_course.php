<?php
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

// Check if required parameters are set
if (!isset($_POST['course_id']) || empty($_POST['course_id'])) {
    $response['message'] = 'Course ID is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if (!isset($_POST['reason']) || empty(trim($_POST['reason']))) {
    $response['message'] = 'Rejection reason is required';
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

try {
    $courseId = intval($_POST['course_id']);
    $rejectionReason = trim($_POST['reason']);
    $adminId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    // Update course status
    $updateCourseQuery = "
        UPDATE courses 
        SET approval_status = 'Rejected' 
        WHERE course_id = ?
    ";

    $stmt = $conn->prepare($updateCourseQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();

    // Check if there is an existing review request
    $checkRequestQuery = "
        SELECT request_id 
        FROM course_review_requests 
        WHERE course_id = ? AND status = 'Pending'
    ";

    $stmt = $conn->prepare($checkRequestQuery);
    $stmt->bind_param('i', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing request
        $row = $result->fetch_assoc();
        $requestId = $row['request_id'];

        $updateRequestQuery = "
            UPDATE course_review_requests 
            SET status = 'Rejected', 
                reviewer_id = ?, 
                review_notes = ?, 
                updated_at = NOW() 
            WHERE request_id = ?
        ";

        $stmt = $conn->prepare($updateRequestQuery);
        $stmt->bind_param('isi', $adminId, $rejectionReason, $requestId);
        $stmt->execute();
    } else {
        // Insert new request record
        $insertRequestQuery = "
            INSERT INTO course_review_requests 
            (course_id, requested_by, request_notes, status, reviewer_id, review_notes, created_at, updated_at) 
            VALUES (?, ?, 'System generated rejection request', 'Rejected', ?, ?, NOW(), NOW())
        ";

        $stmt = $conn->prepare($insertRequestQuery);
        $stmt->bind_param('iisi', $courseId, $adminId, $adminId, $rejectionReason);
        $stmt->execute();
    }

    // Insert validation log
    $validationData = json_encode([
        'status' => 'failed',
        'rejected_by' => $adminId,
        'rejection_date' => date('Y-m-d H:i:s'),
        'rejection_reason' => $rejectionReason
    ]);

    $logQuery = "
        INSERT INTO content_validation_logs 
        (course_id, validation_type, validation_results, validation_date, validated_by) 
        VALUES (?, 'Manual', ?, NOW(), ?)
    ";

    $stmt = $conn->prepare($logQuery);
    $stmt->bind_param('isi', $courseId, $validationData, $adminId);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    $response['status'] = 'success';
    $response['message'] = 'Course rejected successfully';
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    $response['message'] = 'Error rejecting course: ' . $e->getMessage();
    error_log('Course rejection error: ' . $e->getMessage());
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
