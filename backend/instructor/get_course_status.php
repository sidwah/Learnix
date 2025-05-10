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
if (!$stmt) {
    $response["error"] = "Failed to prepare instructor query: " . $conn->error;
    echo json_encode($response);
    exit;
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($instructor_id);
    $stmt->fetch();
} else {
    $response["error"] = "Instructor not found.";
    echo json_encode($response);
    $stmt->close();
    exit;
}
$stmt->close();

// Count course statuses
$query = "SELECT 
            COUNT(CASE WHEN c.status = 'Published' THEN 1 END) AS published,
            COUNT(CASE WHEN c.status = 'Draft' THEN 1 END) AS draft
          FROM courses c
          JOIN course_instructors ci ON c.course_id = ci.course_id
          WHERE ci.instructor_id = ?";

$stmt = $conn->prepare($query);
if (!$stmt) {
    $response["error"] = "Failed to prepare course status query: " . $conn->error;
    echo json_encode($response);
    $conn->close();
    exit;
}
$stmt->bind_param("i", $instructor_id);
if (!$stmt->execute()) {
    $response["error"] = "Query execution failed: " . $stmt->error;
    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}
$result = $stmt->get_result();
$data = $result->fetch_assoc();

$response["published"] = isset($data["published"]) ? (int)$data["published"] : 0;
$response["draft"] = isset($data["draft"]) ? (int)$data["draft"] : 0;
$response["pending"] = 0; // Set to 0 since approval_status doesn't exist

// Output clean JSON
echo json_encode($response);

$stmt->close();
$conn->close();
?>