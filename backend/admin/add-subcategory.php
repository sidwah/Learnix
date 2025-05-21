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
    'message' => 'Failed to add subcategory'
];

// Get and validate input
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

// Validate inputs
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
    
    // Check if parent category exists
    $checkCategory = "SELECT category_id, name FROM categories WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkCategory);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Parent category not found');
    }
    
    $parentCategory = $result->fetch_assoc();
    
    // Check if slug already exists
    $checkSlug = "SELECT subcategory_id FROM subcategories WHERE slug = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkSlug);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('A subcategory with this slug already exists. Please choose a different slug.');
    }
    
    // Insert new subcategory
    $insertQuery = "INSERT INTO subcategories (category_id, name, slug, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("iss", $categoryId, $name, $slug);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to add subcategory: ' . $stmt->error);
    }
    
    $subcategoryId = $conn->insert_id;
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $activity_details = [
        'subcategory_id' => $subcategoryId,
        'subcategory_name' => $name,
        'subcategory_slug' => $slug,
        'parent_category_id' => $categoryId,
        'parent_category_name' => $parentCategory['name']
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'add_subcategory', ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Subcategory added successfully',
        'subcategory_id' => $subcategoryId
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