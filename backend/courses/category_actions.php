<?php
require '../config.php';

header("Content-Type: application/json"); // Ensure responses are sent as JSON

// Function to check if a category exists, excluding the current category if editing

function categoryExists($conn, $name, $id = null) {
    $sql = $id ? "SELECT COUNT(*) FROM categories WHERE name = ? AND category_id != ?" : "SELECT COUNT(*) FROM categories WHERE name = ?";
    $stmt = $conn->prepare($sql);
    $count = "" ;
    if ($id) {
        $stmt->bind_param("si", $name, $id);
    } else {
        $stmt->bind_param("s", $name);
    }
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    return $count > 0;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            addCategory($conn);
            break;
        case 'edit':
            editCategory($conn);
            break;
        case 'delete':
            deleteCategory($conn);
            break;
        default:
            echo json_encode(["error" => "Unsupported action"]);
            break;
    }
}

function addCategory($conn) {
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    if (categoryExists($conn, $name)) {
        echo json_encode(["error" => "Category already exists!"]);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $slug);
    $result = $stmt->execute();
    $stmt->close();

    echo $result ? json_encode(["success" => true]) : json_encode(["error" => "Failed to add category"]);
}

function editCategory($conn) {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $slug = trim($_POST['slug']);

    if (categoryExists($conn, $name, $id)) {
        echo json_encode(["error" => "Another category with the same name already exists!"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE categories SET name=?, slug=? WHERE category_id=?");
    $stmt->bind_param("ssi", $name, $slug, $id);
    $result = $stmt->execute();
    $stmt->close();

    echo $result ? json_encode(["success" => true]) : json_encode(["error" => "Failed to update category"]);
}

function deleteCategory($conn) {
    $id = $_POST['id'];

    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id=?");
    $stmt->bind_param("i", $id);
    $result = $stmt->execute();
    $stmt->close();

    echo $result ? json_encode(["success" => true]) : json_encode(["error" => "Failed to delete category"]);
}
?>
