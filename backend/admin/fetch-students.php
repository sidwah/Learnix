<?php
include("../config.php");
// Fetch students with their course enrollment count
$sql = "SELECT
    u.user_id AS id,
    CONCAT(u.first_name, ' ', u.last_name) AS name,
    u.email,
    u.status,
    u.profile_pic,
    u.created_at,
    u.updated_at,
    COUNT(DISTINCT e.course_id) AS courses_enrolled
FROM
    users u
LEFT JOIN
    enrollments e ON u.user_id = e.user_id
WHERE
    u.role = 'student'
GROUP BY
    u.user_id, u.first_name, u.last_name, u.email, u.status, u.profile_pic, u.created_at, u.updated_at";
$result = $conn->query($sql);
$students = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Ensure profile_pic is not null or empty, otherwise use a default
        $row['profile_pic'] = !empty($row['profile_pic']) ? $row['profile_pic'] : 'default.png';
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