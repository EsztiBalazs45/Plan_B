<?php
// Session indítása
session_start();

// CSRF token generálása, ha még nem létezik
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once '../includes/config.php';
require_once '../includes/header.php';

if (isLoggedIn()) {
    header('Location: /Bozont_cucc/accounting/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token ellenőrzése
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Érvénytelen kérés!';
        header('Location: login.php');
        exit();
    }

    // Kötelező mezők ellenőrzése
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
        header('Location: login.php');
        exit();
    }

    // Email formátum ellenőrzése
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Érvénytelen email cím!';
        header('Location: login.php');
        exit();
    }

    // Jelszó hossz ellenőrzése
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'A jelszónak legalább 8 karakter hosszúnak kell lennie!';
        header('Location: login.php');
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];

            debug([
                'login_success' => true,
                'user_id' => $user['id'],
                'user_name' => $user['name']
            ]);

            $_SESSION['success'] = 'Sikeres bejelentkezés!';
            header('Location: /Bozont_cucc/accounting/index.php');
            exit();
        } else {
            $_SESSION['error'] = 'Hibás email cím vagy jelszó!';
            header('Location: login.php');
            exit();
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = 'Adatbázis hiba történt!';
        header('Location: login.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Bejelentkezés</title>
    <link rel="stylesheet" href="/Bozont_cucc/accounting/assets/css/styles.css">
</head>
<body>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Bejelentkezés</h2>

                <!-- Hibák megjelenítése -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']); ?></div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <!-- Sikeres üzenet megjelenítése -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']); ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <form method="POST" action="" class="needs-validation" novalidate>
                    <!-- CSRF token -->
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']); ?>">

                    <!-- Űrlap mezők -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy érvényes email címet!</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Kérjük, adja meg a jelszavát!</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Bejelentkezés</button>
                </form>

                <div class="text-center mt-3">
                    <a href="register.php">Még nem regisztrált? Regisztráljon most!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
</body>
</html>