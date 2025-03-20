<?php
require( "../config.php");

$user_input = strtolower(trim($_POST['query']));

// Fetch the closest response using a basic LIKE query (or use Levenshtein in SQL)
$sql = "SELECT bot_response, suggestions FROM chatbot_responses WHERE LOWER(user_query) LIKE '%$user_input%' LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(["bot_response" => "I'm not sure, please contact support.", "suggestions" => "Try asking about enrollment, quizzes, or certificates."]);
}

$conn->close();
