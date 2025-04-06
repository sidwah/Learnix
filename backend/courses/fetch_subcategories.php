<?php
require_once '../config.php';  // Ensure this path correctly points to your database config file

header('Content-Type: application/json');

// Fetch subcategories with their parent category names
$query = "SELECT s.subcategory_id, s.name, s.slug, s.created_at, c.name as category_name, s.category_id 
          FROM subcategories s 
          JOIN categories c ON s.category_id = c.category_id 
          ORDER BY c.name, s.name";

$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["error" => "Failed to fetch subcategories: " . mysqli_error($conn)]);
    exit;
}

$subcategories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $subcategories[] = $row;
}

echo json_encode($subcategories);
?>