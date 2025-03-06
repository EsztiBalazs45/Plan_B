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
        session_name(), // Session cookie neve
        '', // Üres érték
        time() - 42000, // Lejárati dátum a múltban
        $params["path"], // Cookie útvonala
        $params["domain"], // Cookie domainje
        $params["secure"], // HTTPS csak, ha szükséges
        $params["httponly"] // Csak HTTP-n keresztül elérhető
    );
}

// Átirányítás a bejelentkezési oldalra
header('Location: /Bozont_cucc/home.php');
exit();
?>