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
    'message' => 'Failed to update subcategory'
];

// Get and validate input
$subcategoryId = isset($_POST['subcategory_id']) ? intval($_POST['subcategory_id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

// Validate inputs
if ($subcategoryId <= 0) {
    $response['message'] = 'Invalid subcategory ID';
    echo json_encode($response);
    exit;
}

if (empty($name)) {
    $response['message'] = 'Subcategory name is required';
    echo json_encode($response);
    exit;
}

if (empty($slug)) {
    $response['message'] = 'Subcategory slug is required';
    echo json_encode($response);
    exit;
}

if ($categoryId <= 0) {
    $response['message'] = 'Parent category is required';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if subcategory exists
    $checkSubcategory = "SELECT s.subcategory_id, s.name, s.slug, s.category_id, c.name as category_name 
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
    
    $oldSubcategory = $result->fetch_assoc();
    
    // Check if parent category exists
    $checkCategory = "SELECT category_id, name FROM categories WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkCategory);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Parent category not found');
    }
    
    $newCategory = $result->fetch_assoc();
    
    // Check if slug already exists for another subcategory
    $checkSlug = "SELECT subcategory_id FROM subcategories WHERE slug = ? AND subcategory_id != ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkSlug);
    $stmt->bind_param("si", $slug, $subcategoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('A subcategory with this slug already exists. Please choose a different slug.');
    }
    
    // Update subcategory
    $updateQuery = "UPDATE subcategories SET category_id = ?, name = ?, slug = ?, updated_at = NOW() WHERE subcategory_id = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("issi", $categoryId, $name, $slug, $subcategoryId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update subcategory: ' . $stmt->error);
    }
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $activity_details = [
        'subcategory_id' => $subcategoryId,
        'old_name' => $oldSubcategory['name'],
        'new_name' => $name,
        'old_slug' => $oldSubcategory['slug'],
        'new_slug' => $slug,
        'old_category_id' => $oldSubcategory['category_id'],
        'new_category_id' => $categoryId,
        'old_category_name' => $oldSubcategory['category_name'],
        'new_category_name' => $newCategory['name']
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'update_subcategory', ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Subcategory updated successfully'
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