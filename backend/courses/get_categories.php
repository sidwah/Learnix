<?php
/**
 * File: get_categories.php
 * Description: Fetches all active categories for dropdown selection
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
    // Prepare SQL to fetch all categories
    $sql = "SELECT category_id, name 
            FROM categories 
            ORDER BY name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $categories = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'category_id' => $row['category_id'],
                'name' => $row['name']
            ];
        }
    }
    
    // Return categories as JSON
    echo json_encode($categories);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching categories: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch categories']);
} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>