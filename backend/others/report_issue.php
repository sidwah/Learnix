<?php
include '../session_start.php'; 
include '../config.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; // Get user ID from session
    $issue_type = $_POST['issue_type'];
    $description = $_POST['description'];
    $file_path = null; // Default file path

    // File upload handling
    if (!empty($_FILES['issue_file']['name'])) {
        $target_dir = "../uploads/issues/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create directory if not exists
        }

        $file_name = time() . "_" . basename($_FILES["issue_file"]["name"]);
        $target_file = $target_dir . $file_name;

        if (move_uploaded_file($_FILES["issue_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        } else {
            echo json_encode(["status" => "error", "message" => "File upload failed"]);
            exit;
        }
    }

    // Insert issue into database
    $stmt = $conn->prepare("INSERT INTO issue_reports (user_id, issue_type, description, file_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $issue_type, $description, $file_path);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Issue reported successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to report issue"]);
    }

    $stmt->close();
    $conn->close();
}
?>
