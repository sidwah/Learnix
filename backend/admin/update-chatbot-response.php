<?php
// backend/admin/update-chatbot-response.php
header('Content-Type: application/json');

// Database connection
require_once '../config.php';

try {
    // Input validation
    $responseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $userQuery = trim($_POST['user_query'] ?? '');
    $botResponse = trim($_POST['bot_response'] ?? '');
    $suggestions = trim($_POST['suggestions'] ?? '');

    // Validate required fields
    if ($responseId === false || $responseId === null) {
        throw new Exception("Invalid response ID");
    }
    if (empty($userQuery)) {
        throw new Exception("User query cannot be empty");
    }
    if (empty($botResponse)) {
        throw new Exception("Bot response cannot be empty");
    }

    // Prepare suggestions (convert comma-separated to a clean string)
    if (!empty($suggestions)) {
        $suggestions = implode(', ', array_map('trim', explode(',', $suggestions)));
    } else {
        $suggestions = null;
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("UPDATE chatbot_responses 
        SET user_query = ?, 
            bot_response = ?, 
            suggestions = ? 
        WHERE id = ?");
    
    $stmt->bind_param(
        "sssi", 
        $userQuery, 
        $botResponse, 
        $suggestions,
        $responseId
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to update response: " . $stmt->error);
    }

    // Check if any row was actually updated
    if ($stmt->affected_rows === 0) {
        throw new Exception("No response found with the given ID");
    }

    // Return success response
    echo json_encode([
        'success' => true
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