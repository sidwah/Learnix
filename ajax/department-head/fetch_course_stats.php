<?php
// Include database connection
require_once '../../backend/config.php';
// Check if admin is logged in
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
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
?>