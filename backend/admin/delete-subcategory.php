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
    'message' => 'Failed to delete subcategory'
];

// Get and validate input
$subcategoryId = isset($_POST['subcategory_id']) ? intval($_POST['subcategory_id']) : 0;

// Validate inputs
if ($subcategoryId <= 0) {
    $response['message'] = 'Invalid subcategory ID';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if subcategory exists
    $checkSubcategory = "SELECT s.subcategory_id, s.name, s.category_id, c.name as category_name 
                         FROM subcategories s
                         JOIN categories c ON s.category_id = c.category_id
                         WHERE s.subcategory_id = ? AND s.deleted_at IS NULL";
    $stmt = $conn->prepare($checkSubcategory);
    $stmt->bind_param("i", $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Subcategory not found');
    }
    
    $subcategory = $result->fetch_assoc();
    
    // Get count of related courses
    $courseQuery = "SELECT COUNT(*) as count FROM courses WHERE subcategory_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param("i", $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courseCount = $result->fetch_assoc()['count'];
    
    // Soft delete subcategory
    $deleteQuery = "UPDATE subcategories SET deleted_at = NOW() WHERE subcategory_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $subcategoryId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete subcategory: ' . $stmt->error);
    }
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $activity_details = [
        'subcategory_id' => $subcategoryId,
        'subcategory_name' => $subcategory['name'],
        'parent_category_id' => $subcategory['category_id'],
        'parent_category_name' => $subcategory['category_name'],
        'course_count' => $courseCount
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'delete_subcategory', ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Subcategory deleted successfully'
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