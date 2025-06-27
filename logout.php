<?php
session_start(); // Start the session if it's not already started

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
// Note: This will destroy the session, and not just the session data!
// If you're using session cookies, they will be deleted too.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy(); // Destroy the session

// Redirect to the login page or homepage
header("Location: login.php"); // Assuming login.php is in the root directory
exit(); // It's crucial to call exit() after header() to prevent further script execution
?>