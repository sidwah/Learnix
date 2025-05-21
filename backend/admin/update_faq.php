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
    $category = trim(mysqli_real_escape_string($conn, $_POST['category'] ?? ''));
    $question = trim(mysqli_real_escape_string($conn, $_POST['question'] ?? ''));
    $answer = trim(mysqli_real_escape_string($conn, $_POST['answer'] ?? ''));
    $role_visibility = trim(mysqli_real_escape_string($conn, $_POST['role_visibility'] ?? 'all'));
    $status = ($_POST['status'] === 'true') ? 'active' : 'inactive';
    
    // Validate required fields
    if (empty($faq_id) || empty($category) || empty($question) || empty($answer)) {
        $response['message'] = 'All required fields must be filled out.';
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
    
    // Current timestamp
    $current_time = date('Y-m-d H:i:s');
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare("UPDATE faqs SET category = ?, question = ?, answer = ?, role_visibility = ?, status = ?, last_updated = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $category, $question, $answer, $role_visibility, $status, $current_time, $faq_id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'FAQ updated successfully.';
    } else {
        $response['message'] = 'Error updating FAQ: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

// Send the response
echo json_encode($response);