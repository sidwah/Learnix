<?php
include("../config.php");


// Fetch Instructors
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS name, email, status, profile_pic FROM users WHERE role = 'instructor'";
$result = $conn->query($sql);

$instructors = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $instructors[] = $row;
    }
}

// Check if data exists
if (empty($instructors)) {
    echo json_encode(["error" => "No Instructor records found"]);
} else {
    echo json_encode($instructors, JSON_PRETTY_PRINT);
}

$conn->close();
?>
