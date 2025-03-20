<?php
/**
 * Get Available Tags API Endpoint
 * File: ../backend/courses/get_available_tags.php
 * 
 * Returns a list of all available tags from the database
 */

// Include database connection
require_once '../config.php';

// Set response headers
header('Content-Type: application/json');

try {
    // Query to get all tags
    $sql = "SELECT tag_id, tag_name FROM tags ORDER BY tag_name ASC";
    $result = $conn->query($sql);
    
    if ($result) {
        $tags = array();
        
        while ($row = $result->fetch_assoc()) {
            $tags[] = array(
                'tag_id' => $row['tag_id'],
                'tag_name' => $row['tag_name']
            );
        }
        
        // Return success with tags
        echo json_encode(array(
            'success' => true,
            'tags' => $tags
        ));
    } else {
        // Database error
        echo json_encode(array(
            'success' => false,
            'message' => 'Failed to fetch tags'
        ));
    }
} catch (Exception $e) {
    // Exception error
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}

// Close connection
$conn->close();
