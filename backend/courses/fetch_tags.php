<?php
require '../config.php'; // Database connection

$sql = "SELECT tag_name FROM tags";
$result = $conn->query($sql);

$tags = [];
while ($row = $result->fetch_assoc()) {
    $tags[] = $row['tag_name'];
}

echo json_encode($tags);
?>
