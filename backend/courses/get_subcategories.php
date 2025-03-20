<?php
/**
 * File: get_subcategories.php
 * Description: Fetches subcategories for a specific category
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

// Check if category_id is provided
if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    echo json_encode(['error' => 'Category ID is required']);
    exit;
}

// Sanitize input
$category_id = intval($_GET['category_id']);

try {
    // Prepare SQL to fetch subcategories
    $sql = "SELECT subcategory_id, name 
            FROM subcategories 
            WHERE category_id = ?
            ORDER BY name ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $subcategories[] = [
                'subcategory_id' => $row['subcategory_id'],
                'name' => $row['name']
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