<?php
// backend/session_start.php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
