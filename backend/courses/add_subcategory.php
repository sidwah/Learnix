<?php
require_once '../config.php';  // Ensure this path correctly points to your database config file

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST["name"]));
    $slug = mysqli_real_escape_string($conn, trim($_POST["slug"]));
    $category_id = mysqli_real_escape_string($conn, trim($_POST["category_id"]));

    // Validate inputs
    if (empty($name) || empty($slug) || empty($category_id)) {
        echo json_encode(["error" => "Subcategory name, slug, and parent category are required."]);
        exit;
    }

    // Check if subcategory already exists
    $query = mysqli_prepare($conn, "SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ?");
    mysqli_stmt_bind_param($query, "si", $name, $category_id);
    mysqli_stmt_execute($query);
    mysqli_stmt_bind_result($query, $count);
    mysqli_stmt_fetch($query);
    mysqli_stmt_close($query);

    if ($count > 0) {
        echo json_encode(["error" => "Subcategory name already exists under this category."]);
        exit;
    }

    // Insert new subcategory
    $stmt = mysqli_prepare($conn, "INSERT INTO subcategories (category_id, name, slug, created_at) VALUES (?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "iss", $category_id, $name, $slug);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => "Subcategory added successfully."]);
    } else {
        echo json_encode(["error" => "Failed to add subcategory: " . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["error" => "Invalid request method."]);
}
?>