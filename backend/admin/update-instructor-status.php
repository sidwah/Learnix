<?php
require_once '../config.php'; // Adjust the path as needed

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $instructorId = $_POST['id'];
    $newStatus = $_POST['status'];

    $allowedStatuses = ['active', 'suspended', 'banned'];

    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(["error" => "Invalid status"]);
        exit;
    }

    $stmt = $conn->prepare("UPDATE instructors SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $instructorId);

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "newStatus" => $newStatus]);
    } else {
        echo json_encode(["error" => "Failed to update instructor status"]);
    }

    $stmt->close();
    $conn->close();
}
?>
