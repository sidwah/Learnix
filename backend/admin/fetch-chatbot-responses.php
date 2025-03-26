<?php
// backend/admin/fetch-chatbot-responses.php
header('Content-Type: application/json');

// Database connection
require_once '../config.php';

try {
    // Fetch all chatbot responses with optional ordering
    $query = "SELECT 
        id, 
        user_query, 
        bot_response, 
        suggestions, 
        created_at 
    FROM chatbot_responses 
    ORDER BY id DESC";

    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $responses = [];
    while ($row = $result->fetch_assoc()) {
        // Truncate very long responses for better display
        $row['user_query'] = strlen($row['user_query']) > 100 
            ? substr($row['user_query'], 0, 100) . '...' 
            : $row['user_query'];
        
        $row['bot_response'] = strlen($row['bot_response']) > 200 
            ? substr($row['bot_response'], 0, 200) . '...' 
            : $row['bot_response'];

        $responses[] = $row;
    }

    echo json_encode($responses);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();