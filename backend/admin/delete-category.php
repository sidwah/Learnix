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
    'message' => 'Failed to delete category'
];

// Get and validate input
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

// Validate inputs
if ($categoryId <= 0) {
    $response['message'] = 'Invalid category ID';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if category exists
    $checkCategory = "SELECT category_id, name FROM categories WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkCategory);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Category not found');
    }
    
    $category = $result->fetch_assoc();
    
    // Get counts for related entities
    $subcategoryQuery = "SELECT COUNT(*) as count FROM subcategories WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($subcategoryQuery);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $subcategoryCount = $result->fetch_assoc()['count'];
    
    $courseQuery = "SELECT COUNT(*) as count FROM courses co 
                   JOIN subcategories s ON co.subcategory_id = s.subcategory_id 
                   WHERE s.category_id = ? AND co.deleted_at IS NULL AND s.deleted_at IS NULL";
    $stmt = $conn->prepare($courseQuery);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    $courseCount = $result->fetch_assoc()['count'];
    
    // Soft delete category
    $deleteQuery = "UPDATE categories SET deleted_at = NOW() WHERE category_id = ?";
    $stmt = $conn->prepare($deleteQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $categoryId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete category: ' . $stmt->error);
    }
    
    // Soft delete department mappings
    $deleteMappingsQuery = "UPDATE department_category_mapping SET deleted_at = NOW() WHERE category_id = ?";
    $stmt = $conn->prepare($deleteMappingsQuery);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $activity_details = [
        'category_id' => $categoryId,
        'category_name' => $category['name'],
        'subcategory_count' => $subcategoryCount,
        'course_count' => $courseCount
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'delete_category', ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Category deleted successfully'
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