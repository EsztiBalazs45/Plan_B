<?php
session_start();

require_once '../includes/config.php';

header('Content-Type: application/json');

// CSRF token generálása, ha még nem létezik
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Csak POST kéréseket fogadunk
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Csak POST kérés engedélyezett']);
    exit();
}

// Bemeneti adatok fogadása JSON formátumban
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Érvénytelen bemenet']);
    exit();
}

// CSRF token ellenőrzése
if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['error' => 'Érvénytelen CSRF token']);
    exit();
}

$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Minden mező kitöltése kötelező!']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Érvénytelen email cím!']);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['error' => 'A jelszónak legalább 8 karakter hosszúnak kell lennie!']);
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=asd", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Sikeres bejelentkezés!',
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email']
            ]
        ]);
        exit();
    } else {
        http_response_code(401);
        echo json_encode(['error' => 'Hibás email cím vagy jelszó!']);
        exit();
    }
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Adatbázis hiba történt!']);
    exit();
}
?>