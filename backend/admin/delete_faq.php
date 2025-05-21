<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Initialize response array
$response = array(
    'success' => false,
    'message' => ''
);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize POST data
    $faq_id = intval($_POST['faq_id'] ?? 0);
    
    // Validate required fields
    if (empty($faq_id)) {
        $response['message'] = 'FAQ ID is required.';
        echo json_encode($response);
        exit;
    }
    
    // Verify FAQ exists
    $check_stmt = $conn->prepare("SELECT id FROM faqs WHERE id = ? AND deleted_at IS NULL");
    $check_stmt->bind_param("i", $faq_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        $response['message'] = 'FAQ not found.';
        echo json_encode($response);
        exit;
    }
    $check_stmt->close();
    
    // Current timestamp for soft delete
    $current_time = date('Y-m-d H:i:s');
    
    // Soft delete the FAQ (set deleted_at timestamp)
    $stmt = $conn->prepare("UPDATE faqs SET deleted_at = ? WHERE id = ?");
    $stmt->bind_param("si", $current_time, $faq_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'FAQ deleted successfully.';
    } else {
        $response['message'] = 'Error deleting FAQ: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

// Send the response
echo json_encode($response);