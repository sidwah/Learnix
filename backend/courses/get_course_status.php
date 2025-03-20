<?php
require '../session_start.php';
require '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = [];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response["error"] = "User not logged in.";
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get instructor ID
$query = "SELECT instructor_id FROM instructors WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($instructor_id);
    $stmt->fetch();
    $stmt->close();
} else {
    $response["error"] = "Instructor not found.";
    echo json_encode($response);
    exit;
}

// Count course statuses
$query = "SELECT 
            COUNT(CASE WHEN status = 'Published' THEN 1 END) AS published,
            COUNT(CASE WHEN status = 'Draft' THEN 1 END) AS draft,
            COUNT(CASE WHEN approval_status = 'Pending' THEN 1 END) AS pending
          FROM courses WHERE instructor_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$response["published"] = isset($data["published"]) ? (int)$data["published"] : 0;
$response["draft"] = isset($data["draft"]) ? (int)$data["draft"] : 0;
$response["pending"] = isset($data["pending"]) ? (int)$data["pending"] : 0;

// Output clean JSON
echo json_encode($response);
$conn->close();
