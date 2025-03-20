<?php
require_once '../config.php';  // Ensure this path correctly points to your database config file

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, trim($_POST["name"]));
    $slug = mysqli_real_escape_string($conn, trim($_POST["slug"]));

    // Validate inputs
    if (empty($name) || empty($slug)) {
        echo json_encode(["error" => "Category name and slug are required."]);
        exit;
    }

    // Check if category already exists
    $query = mysqli_prepare($conn, "SELECT COUNT(*) FROM categories WHERE name = ?");
    mysqli_stmt_bind_param($query, "s", $name);
    mysqli_stmt_execute($query);
    mysqli_stmt_bind_result($query, $count);
    mysqli_stmt_fetch($query);
    mysqli_stmt_close($query);

    if ($count > 0) {
        echo json_encode(["error" => "Category name already exists."]);
        exit;
    }

    // Insert new category
    $stmt = mysqli_prepare($conn, "INSERT INTO categories (name, slug, created_at) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ss", $name, $slug);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => "Category added successfully."]);
    } else {
        echo json_encode(["error" => "Failed to add category: " . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["error" => "Invalid request method."]);
}
?>
