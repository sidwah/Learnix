<?php
require '../backend/session_start.php';
require '../backend/config.php';

// Check if the user is signed in
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true) {
    echo json_encode([]);
    exit;
}

// Validate input
if (!isset($_POST['category_id']) || !is_numeric($_POST['category_id'])) {
    echo json_encode([]);
    exit;
}

$category_id = intval($_POST['category_id']);

// Get subcategories for the selected category
$stmt = $conn->prepare("SELECT subcategory_id, name, slug FROM subcategories WHERE category_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$subcategories = [];
while ($row = $result->fetch_assoc()) {
    $subcategories[] = $row;
}

$stmt->close();

// Return subcategories as JSON
echo json_encode($subcategories);
?>