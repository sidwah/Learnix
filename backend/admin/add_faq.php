<?php
require_once '../config.php';
header('Content-Type: application/json');

// Authentication check
require_once '../auth/admin/admin-auth-check.php';

// Initialize response array
$response = array(
    'success' => false,
    'message' => '',
    'faq_id' => null
);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize POST data
    $category = trim(mysqli_real_escape_string($conn, $_POST['category'] ?? ''));
    $question = trim(mysqli_real_escape_string($conn, $_POST['question'] ?? ''));
    $answer = trim(mysqli_real_escape_string($conn, $_POST['answer'] ?? ''));
    $role_visibility = trim(mysqli_real_escape_string($conn, $_POST['role_visibility'] ?? 'all'));
    $status = ($_POST['status'] === 'true') ? 'active' : 'inactive';
    
    // Validate required fields
    if (empty($category) || empty($question) || empty($answer)) {
        $response['message'] = 'All required fields must be filled out.';
        echo json_encode($response);
        exit;
    }
    
    // Current timestamp
    $current_time = date('Y-m-d H:i:s');
    
    // Prepare and execute the SQL query
    $stmt = $conn->prepare("INSERT INTO faqs (category, question, answer, role_visibility, status, created_at, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $category, $question, $answer, $role_visibility, $status, $current_time, $current_time);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'FAQ added successfully.';
        $response['faq_id'] = $conn->insert_id;
    } else {
        $response['message'] = 'Error adding FAQ: ' . $stmt->error;
    }
    
    $stmt->close();
} else {
    $response['message'] = 'Invalid request method.';
}

// Send the response
echo json_encode($response);