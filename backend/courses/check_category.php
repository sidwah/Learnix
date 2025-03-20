<?php
require_once "../config.php"; // Adjust to your actual DB connection file

header("Content-Type: application/json"); // Ensure JSON response

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['name']) || empty(trim($_POST['name']))) {
        echo json_encode(["error" => "Category name is required."]);
        exit;
    }

    $name = trim($_POST['name']);

    // Prepare the SQL statement
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE name = ?");
    if (!$stmt) {
        echo json_encode(["error" => "Database error."]);
        exit;
    }

    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    
    echo json_encode(["exists" => $count > 0]); // Return whether the category exists
    $stmt->close();
    $conn->close();
}
