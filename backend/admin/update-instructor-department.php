<?php
// Authentication check
require_once '../../backend/auth/admin/admin-auth-check.php';
require_once '../../backend/config.php';

// Ensure we're processing a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Initialize response array
$response = [
    'status' => 'error',
    'message' => 'Failed to update instructor department'
];

// Get and validate input
$instructor_id = isset($_POST['instructor_id']) ? intval($_POST['instructor_id']) : 0;
$department_id = isset($_POST['department_id']) ? intval($_POST['department_id']) : 0;
$user_email = isset($_POST['user_email']) ? $_POST['user_email'] : '';
$user_name = isset($_POST['user_name']) ? $_POST['user_name'] : '';

// Validate inputs
if ($instructor_id <= 0) {
    $response['message'] = 'Invalid instructor ID';
    echo json_encode($response);
    exit;
}

if ($department_id <= 0) {
    $response['message'] = 'Please select a department';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Get instructor details
    $query = "SELECT i.instructor_id, u.user_id, u.email, u.first_name, u.last_name
              FROM instructors i
              JOIN users u ON i.user_id = u.user_id
              WHERE i.instructor_id = ? AND i.deleted_at IS NULL";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Instructor not found');
    }
    
    $instructor = $result->fetch_assoc();
    
    // Get department details
    $deptQuery = "SELECT department_id, name FROM departments WHERE department_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($deptQuery);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $deptResult = $stmt->get_result();
    
    if ($deptResult->num_rows === 0) {
        throw new Exception('Department not found');
    }
    
    $department = $deptResult->fetch_assoc();
    
    // Start transaction
    $conn->begin_transaction();
    
    // Check if instructor already has department assignment
    $checkQuery = "SELECT id FROM department_instructors 
                   WHERE instructor_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $checkResult = $stmt->get_result();
    
    $admin_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1; // Fallback to ID 1 if not set
    
    // If already has assignment, update it
    if ($checkResult->num_rows > 0) {
        $dept_assignment = $checkResult->fetch_assoc();
        
        // First, mark old assignment as deleted
        $deleteOld = "UPDATE department_instructors 
                       SET deleted_at = NOW() 
                       WHERE instructor_id = ? AND deleted_at IS NULL";
        $stmt = $conn->prepare($deleteOld);
        $stmt->bind_param("i", $instructor_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update department assignment: ' . $stmt->error);
        }
        
        // Then create new assignment
        $insertNew = "INSERT INTO department_instructors 
                      (department_id, instructor_id, added_by, status) 
                      VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($insertNew);
        $stmt->bind_param("iii", $department_id, $instructor_id, $admin_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create department assignment: ' . $stmt->error);
        }
    } else {
        // Create new assignment
        $insertNew = "INSERT INTO department_instructors 
                      (department_id, instructor_id, added_by, status) 
                      VALUES (?, ?, ?, 'active')";
        $stmt = $conn->prepare($insertNew);
        $stmt->bind_param("iii", $department_id, $instructor_id, $admin_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create department assignment: ' . $stmt->error);
        }
    }
    
    // Log the activity
    $log_details = json_encode([
        'instructor_id' => $instructor_id,
        'instructor_name' => $instructor['first_name'] . ' ' . $instructor['last_name'],
        'department_id' => $department_id,
        'department_name' => $department['name']
    ]);
    
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($log_query);
    $activity_type = "instructor_department_changed";
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt->bind_param("isss", $admin_id, $activity_type, $log_details, $ip);
    $stmt->execute();
    
    // Create in-app notification for the instructor
    $notification_title = "Department Assignment Updated";
    $notification_message = "You have been assigned to the {$department['name']} department.";
    
    $notification_query = "INSERT INTO user_notifications (user_id, type, title, message, related_type) 
                         VALUES (?, 'department_change', ?, ?, 'instructor')";
    $stmt = $conn->prepare($notification_query);
    $stmt->bind_param("iss", $instructor['user_id'], $notification_title, $notification_message);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Department assignment updated successfully',
        'data' => [
            'department_id' => $department_id,
            'department_name' => $department['name']
        ]
    ];
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    $response['message'] = $e->getMessage();
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;