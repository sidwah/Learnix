<?php
// backend/admin/delete-chatbot-response.php
header('Content-Type: application/json');

// Database connection
require_once '../config.php';

try {
    // Input validation
    $responseId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    // Validate input
    if ($responseId === false || $responseId === null) {
        throw new Exception("Invalid response ID");
    }

    // Prepare SQL statement
    $stmt = $conn->prepare("DELETE FROM chatbot_responses WHERE id = ?");
    
    $stmt->bind_param("i", $responseId);

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete response: " . $stmt->error);
    }

    // Check if any row was actually deleted
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