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
    'message' => 'Failed to add category'
];

// Get and validate input
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$departments = isset($_POST['departments']) ? $_POST['departments'] : [];

// Validate inputs
if (empty($name)) {
    $response['message'] = 'Category name is required';
    echo json_encode($response);
    exit;
}

if (empty($slug)) {
    $response['message'] = 'Category slug is required';
    echo json_encode($response);
    exit;
}

// Process the request
try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if slug already exists
    $checkSlug = "SELECT category_id FROM categories WHERE slug = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkSlug);
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('A category with this slug already exists. Please choose a different slug.');
    }
    
    // Insert new category
    $insertQuery = "INSERT INTO categories (name, slug, created_at, updated_at) VALUES (?, ?, NOW(), NOW())";
    $stmt = $conn->prepare($insertQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ss", $name, $slug);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to add category: ' . $stmt->error);
    }
    
    $categoryId = $conn->insert_id;
    
    // Associate with departments if provided
    if (!empty($departments)) {
        $admin_id = $_SESSION['user_id'];
        $insertDeptStmt = $conn->prepare("INSERT INTO department_category_mapping (department_id, category_id, created_at, created_by, is_active) VALUES (?, ?, NOW(), ?, 1)");
        
        foreach ($departments as $departmentId) {
            $insertDeptStmt->bind_param("iii", $departmentId, $categoryId, $admin_id);
            $insertDeptStmt->execute();
        }
    }
    
    // Log the activity
    $admin_id = $_SESSION['user_id'];
    $activity_details = [
        'category_id' => $categoryId,
        'category_name' => $name,
        'category_slug' => $slug,
        'departments' => $departments
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'add_category', ?, ?)";
   $log_stmt = $conn->prepare($log_query);
   $ip = $_SERVER['REMOTE_ADDR'];
   $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
   $log_stmt->execute();
   
   // Commit transaction
   $conn->commit();
   
   // Set success response
   $response = [
       'status' => 'success',
       'message' => 'Category added successfully',
       'category_id' => $categoryId
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