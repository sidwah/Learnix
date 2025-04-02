<?php
// Initialize the session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Include database connection
require_once('../config.php');

// Get student ID from query parameter
if (!isset($_GET['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing student ID'
    ]);
    exit;
}

$student_id = intval($_GET['student_id']);

try {
    // Query to get login activity
    $login_query = "SELECT 
                    'login' as activity_type,
                    'Login Activity' as activity_title,
                    CONCAT('Logged in from IP: ', ip_address) as activity_details,
                    attempt_time as created_at
                FROM 
                    login_attempts
                WHERE 
                    email = (SELECT email FROM users WHERE user_id = ?) 
                    AND success = 1
                ORDER BY 
                    attempt_time DESC
                LIMIT 10";

    // Query to get status change logs
    $status_query = "SELECT 
                    'status_change' as activity_type,
                    CONCAT('Status changed to ', new_status) as activity_title,
                    CONCAT('Changed by ', (SELECT CONCAT(first_name, ' ', last_name) FROM users WHERE user_id = changed_by), 
                          IF(reason IS NOT NULL AND reason != '', CONCAT('. Reason: ', reason), '')) as activity_details,
                    new_status,
                    change_date as created_at
                FROM 
                    user_status_logs
                WHERE 
                    user_id = ?
                ORDER BY 
                    change_date DESC
                LIMIT 10";

    // Execute login query
    $stmt = $conn->prepare($login_query);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $login_result = $stmt->get_result();
    $login_logs = $login_result->fetch_all(MYSQLI_ASSOC);

    // Execute status change query
    $stmt = $conn->prepare($status_query);
    $stmt->bind_param('i', $student_id);
    $stmt->execute();
    $status_result = $stmt->get_result();
    $status_logs = $status_result->fetch_all(MYSQLI_ASSOC);

    // Combine logs and sort by date
    $all_logs = array_merge($login_logs, $status_logs);
    usort($all_logs, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    // Return combined logs
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'logs' => $all_logs
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

// Close connection
$conn->close();
