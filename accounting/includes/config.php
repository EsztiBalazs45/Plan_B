<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'asd');

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8mb4");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header('Location: /Bozont_cucc/accounting/pages/login.php');
        exit();
    }
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function clearMessages() {
    unset($_SESSION['error']);
    unset($_SESSION['success']);
}

function isAdmin() {
    if (!isset($_SESSION['user_id'])) {
        return false; // Nincs bejelentkezett felhasználó
    }
    
    global $conn; // Hozzáférés a globális $conn változóhoz
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return ($user && $user['role'] === 'admin');
}

// Debug function
function debug($data) {
    error_log(print_r($data, true));
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Add debug info
if (isLoggedIn()) {
    debug([
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['user_name'] ?? 'not set',
        'request_uri' => $_SERVER['REQUEST_URI']
    ]);
}

// Ensure session variables are set
if (isset($_SESSION['user_id'])) {
    try {
        $stmt = $conn->prepare("SELECT id, name, email FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
        }
    } catch(PDOException $e) {
        error_log("Error refreshing user session: " . $e->getMessage());
    }
}
?>