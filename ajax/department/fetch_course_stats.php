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
// Initialize response array with default values
$response = [
    'total' => 0,
    'published' => 0,
    'published_percentage' => 0,
    'pending' => 0,
    'rejected' => 0,
    'suspended' => 0
];
try {
    // Get total course count - exclude courses that are both Draft and Pending
    $totalQuery = "SELECT COUNT(*) as total FROM courses
                  WHERE (status = 'Published') OR 
                        (status = 'Draft' AND approval_status = 'Approved') OR
                        (status != 'Draft' AND approval_status = 'Pending') OR
                        (approval_status = 'Rejected')";
    $result = $conn->query($totalQuery);
    $row = $result->fetch_assoc();
    $response['total'] = $row['total'];

    // Get published course count
    $publishedQuery = "SELECT COUNT(*) as published FROM courses
                      WHERE status = 'Published' AND approval_status = 'Approved'";
    $result = $conn->query($publishedQuery);
    $row = $result->fetch_assoc();
    $response['published'] = $row['published'];

    // Calculate published percentage
    if ($response['total'] > 0) {
        $response['published_percentage'] = round(($response['published'] / $response['total']) * 100, 1);
    }

    // Get pending course count - exclude those in Draft status
    $pendingQuery = "SELECT COUNT(*) as pending FROM courses
                    WHERE approval_status = 'Pending' AND status != 'Draft'";
    $result = $conn->query($pendingQuery);
    $row = $result->fetch_assoc();
    $response['pending'] = $row['pending'];

    // Get rejected course count
    $rejectedQuery = "SELECT COUNT(*) as rejected FROM courses
                     WHERE approval_status = 'Rejected'";
    $result = $conn->query($rejectedQuery);
    $row = $result->fetch_assoc();
    $response['rejected'] = $row['rejected'];

    // Get suspended course count
    $suspendedQuery = "SELECT COUNT(*) as suspended FROM courses
                      WHERE status = 'Draft' AND approval_status = 'Approved'";
    $result = $conn->query($suspendedQuery);
    $row = $result->fetch_assoc();
    $response['suspended'] = $row['suspended'];
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error fetching course stats: ' . $e->getMessage();
}
// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
