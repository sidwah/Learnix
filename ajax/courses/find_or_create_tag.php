<?php
require '../../backend/session_start.php';
require '../../backend/config.php';

// Check if the user is signed in
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Validate input
if (!isset($_POST['tag_name']) || empty($_POST['tag_name'])) {
    echo json_encode(['success' => false, 'message' => 'Tag name is required']);
    exit;
}

$tag_name = trim($_POST['tag_name']);

// Check if tag already exists (case insensitive)
$stmt = $conn->prepare("SELECT tag_id, tag_name FROM tags WHERE LOWER(tag_name) = LOWER(?)");
$stmt->bind_param("s", $tag_name);
$stmt->execute();
$result = $stmt->get_result();
$existing_tag = $result->fetch_assoc();
$stmt->close();

if ($existing_tag) {
    // Tag already exists, return it
    echo json_encode([
        'success' => true,
        'tag_id' => $existing_tag['tag_id'],
        'tag_name' => $existing_tag['tag_name'],
        'existing' => true
    ]);
    exit;
}

// Create new tag
$stmt = $conn->prepare("INSERT INTO tags (tag_name) VALUES (?)");
$stmt->bind_param("s", $tag_name);
$success = $stmt->execute();
$tag_id = $stmt->insert_id;
$stmt->close();

if ($success) {
    echo json_encode([
        'success' => true,
        'tag_id' => $tag_id,
        'tag_name' => $tag_name,
        'existing' => false
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
?>