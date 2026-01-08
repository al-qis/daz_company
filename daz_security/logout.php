<?php
session_start();

// Log logout event if user was logged in
if (isset($_SESSION['user'])) {
    require_once __DIR__ . '/includes/security.php';
    logSecurityEvent($_SESSION['user']['username'], 'LOGOUT', 'User logged out', getClientIp());
}

// Destroy all session data
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?>