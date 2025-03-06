<?php
require_once 'config.php';

// Destroy session
session_destroy();

// Clear all session variables
$_SESSION = array();

// Delete the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page
header('Location: /Bozont_cucc/accounting/pages/login.php');
exit();
