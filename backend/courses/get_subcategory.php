<?php
// backend/courses/get_subcategories.php
require_once '../config.php';

header('Content-Type: application/json');

$category_id = $_GET['category_id'] ?? null;

if (!$category_id) {
    echo json_encode([]);
    exit;
}

try {
    $sql = "SELECT subcategory_id, name, slug 
            FROM subcategories 
            WHERE category_id = ? AND deleted_at IS NULL 
            ORDER BY name";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subcategories = [];
    while ($row = $result->fetch_assoc()) {
        $subcategories[] = $row;
    }
    
    echo json_encode($subcategories);
} catch (Exception $e) {
    echo json_encode([]);
}

$conn->close();
?>