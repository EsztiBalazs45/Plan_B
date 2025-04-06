<?php
require_once '../includes/config.php';

// Session indítása
session_start();

// Session változók törlése
$_SESSION = array();

// Session törlése
session_destroy();

// Session cookie törlése
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '', 
        time() - 42000, 
        $params["path"], 
        $params["domain"], 
        $params["secure"], 
        $params["httponly"] 
    );
}

header('Location: /Bozont_cucc/home.php');
exit();
?>