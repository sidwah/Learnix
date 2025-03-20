<?php
include("../config.php");


// Fetch students
$sql = "SELECT CONCAT(first_name, ' ', last_name) AS name, email, status, profile_pic FROM users WHERE role = 'student'";
$result = $conn->query($sql);

$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Check if data exists
if (empty($students)) {
    echo json_encode(["error" => "No student records found"]);
} else {
    echo json_encode($students, JSON_PRETTY_PRINT);
}

$conn->close();
?>
