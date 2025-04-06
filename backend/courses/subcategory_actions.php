<?php
require_once '../config.php';  // Ensure this path correctly points to your database config file

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    switch ($action) {
        case "edit":
            editSubcategory($conn);
            break;
        case "delete":
            deleteSubcategory($conn);
            break;
        default:
            echo json_encode(["error" => "Invalid action specified."]);
            break;
    }
} else {
    echo json_encode(["error" => "Invalid request method."]);
}

function editSubcategory($conn) {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    $name = mysqli_real_escape_string($conn, $_POST["name"]);
    $slug = mysqli_real_escape_string($conn, $_POST["slug"]);
    $category_id = mysqli_real_escape_string($conn, $_POST["category_id"]);

    // Check if another subcategory with the same name exists under the same category
    $query = mysqli_prepare($conn, "SELECT COUNT(*) FROM subcategories WHERE name = ? AND category_id = ? AND subcategory_id != ?");
    mysqli_stmt_bind_param($query, "sii", $name, $category_id, $id);
    mysqli_stmt_execute($query);
    mysqli_stmt_bind_result($query, $count);
    mysqli_stmt_fetch($query);
    mysqli_stmt_close($query);

    if ($count > 0) {
        echo json_encode(["error" => "Another subcategory with this name already exists under this category."]);
        return;
    }

    $stmt = mysqli_prepare($conn, "UPDATE subcategories SET name = ?, slug = ?, category_id = ? WHERE subcategory_id = ?");
    mysqli_stmt_bind_param($stmt, "ssii", $name, $slug, $category_id, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => "Subcategory updated successfully."]);
    } else {
        echo json_encode(["error" => "Failed to update subcategory: " . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
}

function deleteSubcategory($conn) {
    $id = mysqli_real_escape_string($conn, $_POST["id"]);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM subcategories WHERE subcategory_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["success" => "Subcategory deleted successfully."]);
    } else {
        echo json_encode(["error" => "Failed to delete subcategory: " . mysqli_error($conn)]);
    }
    mysqli_stmt_close($stmt);
}
?>