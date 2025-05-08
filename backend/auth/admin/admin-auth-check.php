<?php
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || 
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    // Not logged in or not an admin, redirect to login page
    header("Location: signin.php");
    exit;
}

// Optional: Add session timeout check for enhanced security
$sessionTimeout = 3600; // 1 hour in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $sessionTimeout)) {
    // Session has expired, destroy session and redirect to login
    session_unset();
    session_destroy();
    header("Location: signin.php?timeout=1");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Now the page content continues...
?>