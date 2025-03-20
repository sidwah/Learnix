<?php
// Helper functions for the project

// Sanitize user input to prevent XSS and SQL injection
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Redirect to another page
function redirect($url) {
    header("Location: $url");
    exit();
}

// Display a flash message (can be extended for a session-based flash system)
function flashMessage($message, $type = 'success') {
    echo "<div class='alert alert-$type'>$message</div>";
}
?>
