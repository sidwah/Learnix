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
    'message' => 'Failed to update category'
];

// Get and validate input
$categoryId = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$slug = isset($_POST['slug']) ? trim($_POST['slug']) : '';
$departments = isset($_POST['departments']) ? $_POST['departments'] : [];

// Validate inputs
if ($categoryId <= 0) {
    $response['message'] = 'Invalid category ID';
    echo json_encode($response);
    exit;
}

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
    
    // Check if category exists
    $checkCategory = "SELECT category_id, name, slug FROM categories WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkCategory);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Category not found');
    }
    
    $oldCategory = $result->fetch_assoc();
    
    // Check if slug already exists for another category
    $checkSlug = "SELECT category_id FROM categories WHERE slug = ? AND category_id != ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($checkSlug);
    $stmt->bind_param("si", $slug, $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        throw new Exception('A category with this slug already exists. Please choose a different slug.');
    }
    
    // Update category
    $updateQuery = "UPDATE categories SET name = ?, slug = ?, updated_at = NOW() WHERE category_id = ?";
    $stmt = $conn->prepare($updateQuery);
    
    if (!$stmt) {
        throw new Exception('Error preparing statement: ' . $conn->error);
    }
    
    $stmt->bind_param("ssi", $name, $slug, $categoryId);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update category: ' . $stmt->error);
    }
    
    // Get current department mappings
    $currentDeptQuery = "SELECT department_id FROM department_category_mapping WHERE category_id = ? AND deleted_at IS NULL";
    $stmt = $conn->prepare($currentDeptQuery);
    $stmt->bind_param("i", $categoryId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $currentDepartments = [];
    while ($row = $result->fetch_assoc()) {
        $currentDepartments[] = $row['department_id'];
    }
    
    // Soft delete removed department mappings
    $admin_id = $_SESSION['user_id'];
    $departmentsToRemove = array_diff($currentDepartments, $departments);
    
    if (!empty($departmentsToRemove)) {
        $deptIds = implode(',', $departmentsToRemove);
        $softDeleteQuery = "UPDATE department_category_mapping SET deleted_at = NOW() WHERE category_id = ? AND department_id IN ($deptIds)";
        $stmt = $conn->prepare($softDeleteQuery);
        $stmt->bind_param("i", $categoryId);
        $stmt->execute();
    }
    
    // Add new department mappings
    $departmentsToAdd = array_diff($departments, $currentDepartments);
    
    if (!empty($departmentsToAdd)) {
        $insertDeptStmt = $conn->prepare("INSERT INTO department_category_mapping (department_id, category_id, created_at, created_by, is_active) VALUES (?, ?, NOW(), ?, 1)");
        
        foreach ($departmentsToAdd as $departmentId) {
            $insertDeptStmt->bind_param("iii", $departmentId, $categoryId, $admin_id);
            $insertDeptStmt->execute();
        }
    }
    
    // Log the activity
    $activity_details = [
        'category_id' => $categoryId,
        'old_name' => $oldCategory['name'],
        'new_name' => $name,
        'old_slug' => $oldCategory['slug'],
        'new_slug' => $slug,
        'old_departments' => $currentDepartments,
        'new_departments' => $departments
    ];
    
    $log_details = json_encode($activity_details);
    $log_query = "INSERT INTO user_activity_logs (user_id, activity_type, activity_details, ip_address) 
                  VALUES (?, 'update_category', ?, ?)";
    $log_stmt = $conn->prepare($log_query);
    $ip = $_SERVER['REMOTE_ADDR'];
    $log_stmt->bind_param("iss", $admin_id, $log_details, $ip);
    $log_stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Set success response
    $response = [
        'status' => 'success',
        'message' => 'Category updated successfully'
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