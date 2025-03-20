<?php
/**
 * File: get_tags.php
 * Description: Fetches all available tags for course tagging
 * Location: ../backend/courses/
 */

// Include database connection
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

try {
    // Prepare SQL to fetch all tags
    $sql = "SELECT tag_id, tag_name 
            FROM tags 
            ORDER BY tag_name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tags = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tags[] = [
                'tag_id' => $row['tag_id'],
                'tag_name' => $row['tag_name']
            ];
        }
    }
    
    // Return tags as JSON
    echo json_encode($tags);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching tags: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch tags']);
} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>