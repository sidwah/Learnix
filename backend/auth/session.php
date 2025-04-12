<?php
/**
 * Session Management
 * 
 * Handles user session management and authentication functions.
 * 
 * @package Learnix
 * @subpackage Authentication
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has required role
 * 
 * @param string|array $roles Role or roles to check
 * @return bool True if user has required role, false otherwise
 */
function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($roles)) {
        return in_array($_SESSION['role'], $roles);
    } else {
        return $_SESSION['role'] === $roles;
    }
}

/**
 * Redirect user if not logged in
 * 
 * @param string $redirect URL to redirect to
 * @return void
 */
function requireLogin($redirect = 'index.php') {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Redirect user if not in required role
 * 
 * @param string|array $roles Role or roles to check
 * @param string $redirect URL to redirect to
 * @return void
 */
function requireRole($roles, $redirect = 'index.php') {
    if (!hasRole($roles)) {
        header("Location: $redirect");
        exit;
    }
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 * 
 * @return string|null User role or null if not logged in
 */
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

/**
 * Log user out
 * 
 * @return void
 */
function logout() {
    // Unset all session variables
    $_SESSION = array();
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}