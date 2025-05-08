<?php
session_start();

// Check if user is logged in and has department role
if (!isset($_SESSION['signin']) || $_SESSION['signin'] !== true || 
    !isset($_SESSION['role']) || ($_SESSION['role'] !== 'department_head' && $_SESSION['role'] !== 'department_secretary')) {
    // Not logged in or not department staff, redirect to login page
    header("Location: signin.php");
    exit;
}

// For pages specific to department heads only
if ($_SESSION['role'] !== 'department_head') {
    // Not a department head, redirect to access denied page or dashboard
    header("Location: access-denied.php");
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