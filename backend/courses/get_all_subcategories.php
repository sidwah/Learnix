<?php
/**
 * File: get_all_subcategories.php
 * Description: Fetches all subcategories with their category information
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
    // Prepare SQL to fetch all subcategories with their category info
    $sql = "SELECT s.subcategory_id, s.name, s.category_id, c.name AS category_name
            FROM subcategories s
            JOIN categories c ON s.category_id = c.category_id
            ORDER BY c.name ASC, s.name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = [
                'subcategory_id' => $row['subcategory_id'],
                'name' => $row['name'],
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name']
            ];
        }
    }
    
    // Return subcategories as JSON
    echo json_encode($subcategories);
    
} catch (Exception $e) {
    // Log error
    error_log('Error fetching subcategories: ' . $e->getMessage());
    
    // Return error message
    echo json_encode(['error' => 'Failed to fetch subcategories']);
} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>