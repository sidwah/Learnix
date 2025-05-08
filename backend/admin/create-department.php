<?php
// Authentication check
require_once '../config.php';
require_once '../auth/admin/admin-auth-check.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize input
    $name = mysqli_real_escape_string($conn, trim($_POST['name']));
    $code = mysqli_real_escape_string($conn, trim($_POST['code']));
    $description = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validate input
    if (empty($name) || empty($code)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department name and code are required.'
        ]);
        exit;
    }
    
    // Check if code already exists
    $checkQuery = "SELECT department_id FROM departments WHERE code = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Department code already exists. Please use a different code.'
        ]);
        exit;
    }
    
    // Insert department
    $query = "INSERT INTO departments (name, code, description, is_active) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $name, $code, $description, $isActive);
    
    if ($stmt->execute()) {
        $departmentId = $stmt->insert_id;
        
        // Log activity
        $activityQuery = "INSERT INTO department_activity_logs (department_id, user_id, action_type, details) 
                          VALUES (?, ?, 'create', ?)";
        $activityStmt = $conn->prepare($activityQuery);
        $userId = $_SESSION['user_id'];
        $details = json_encode([
            'name' => $name,
            'code' => $code,
            'is_active' => $isActive
        ]);
        $activityStmt->bind_param("iis", $departmentId, $userId, $details);
        $activityStmt->execute();
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Department created successfully.',
            'department_id' => $departmentId
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to create department: ' . $stmt->error
        ]);
    }
    
    $stmt->close();
} else {
    // Not a POST request
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method.'
    ]);
}