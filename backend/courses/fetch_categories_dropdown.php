<?php
require_once '../config.php';  // Ensure this path correctly points to your database config file

header('Content-Type: application/json');

// Fetch all categories for dropdown
$query = "SELECT category_id, name FROM categories ORDER BY name";
$result = mysqli_query($conn, $query);

if (!$result) {
    echo json_encode(["error" => "Failed to fetch categories: " . mysqli_error($conn)]);
    exit;
}

$categories = [];
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

echo json_encode($categories);
?>