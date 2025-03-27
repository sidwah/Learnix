<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true) {
    echo json_encode([]);
    exit;
}

// Validate input
if (!isset($_POST['search']) || strlen($_POST['search']) < 2) {
    echo json_encode([]);
    exit;
}

$search_term = '%' . $conn->real_escape_string($_POST['search']) . '%';

// Search for tags that match the search term
$stmt = $conn->prepare("SELECT tag_id, tag_name FROM tags WHERE tag_name LIKE ? ORDER BY tag_name ASC LIMIT 15");
$stmt->bind_param("s", $search_term);
$stmt->execute();
$result = $stmt->get_result();

$tags = [];
while ($row = $result->fetch_assoc()) {
    $tags[] = $row;
}

$stmt->close();

// Return tags as JSON
echo json_encode($tags);
?>