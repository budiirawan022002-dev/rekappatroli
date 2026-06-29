<?php
/**
 * Authentication check - include this at the top of protected pages
 */
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database (to ensure it's initialized)
require_once(__DIR__ . '/config/database.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Optional: Verify user still exists in database
if (isset($_SESSION['username'])) {
    require_once(__DIR__ . '/config/auth.php');
    $user = getUserByUsername($_SESSION['username']);
    if (!$user) {
        // User was deleted, logout
        session_destroy();
        header('Location: login.php');
        exit;
    }
}
?>

