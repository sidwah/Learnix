<?php
// backend/admin/add-chatbot-response.php
header('Content-Type: application/json');

// Database connection
require_once '../config.php';

try {
    // Input validation
    $userQuery = trim($_POST['user_query'] ?? '');
    $botResponse = trim($_POST['bot_response'] ?? '');
    $suggestions = trim($_POST['suggestions'] ?? '');

    // Validate required fields
    if (empty($userQuery)) {
        throw new Exception("User query cannot be empty");
    }
    if (empty($botResponse)) {
        throw new Exception("Bot response cannot be empty");
    }

    // Prepare suggestions (convert comma-separated to a clean string)
    if (!empty($suggestions)) {
        $suggestions = implode(', ', array_map('trim', explode(',', $suggestions)));
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO chatbot_responses 
        (user_query, bot_response, suggestions, created_at) 
        VALUES (?, ?, ?, NOW())");
    
    $stmt->bind_param(
        "sss", 
        $userQuery, 
        $botResponse, 
        $suggestions
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to add response: " . $stmt->error);
    }

    // Return success response
    echo json_encode([
        'success' => true, 
        'id' => $stmt->insert_id
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage()
    ]);
}

$stmt->close();
$conn->close();