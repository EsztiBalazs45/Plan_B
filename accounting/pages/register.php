<?php
session_start(); // Session indítása
require_once '../includes/config.php'; // Konfiguráció betöltése

// Ellenőrizzük, hogy a felhasználó már be van-e jelentkezve
if (isLoggedIn()) {
    header('Location: /Bozont_cucc/accounting/index.php');
    exit();
}

// CSRF token generálása, ha még nem létezik
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Űrlap feldolgozása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token ellenőrzése
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $_SESSION['error'] = 'Érvénytelen kérés!';
        header('Location: register.php');
        exit();
    }

    // Kötelező mezők ellenőrzése
    $required_fields = ['name', 'email', 'username', 'password', 'confirm_password'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['error'] = 'Minden mező kitöltése kötelező!';
            $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
            header('Location: register.php');
            exit();
        }
    }

    // Adatok tisztítása
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Email formátum ellenőrzése
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Érvénytelen email cím!';
        $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
        header('Location: register.php');
        exit();
    }

    // Jelszó hossz ellenőrzése
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'A jelszónak legalább 8 karakter hosszúnak kell lennie!';
        $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
        header('Location: register.php');
        exit();
    }

    // Jelszó egyezőség ellenőrzése
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'A jelszavak nem egyeznek!';
        $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
        header('Location: register.php');
        exit();
    }

    try {
        // Ellenőrizzük, hogy az email cím már létezik-e
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'Ez az email cím már regisztrálva van!';
            $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
            header('Location: register.php');
            exit();
        }

        // Ellenőrizzük, hogy a felhasználónév már létezik-e
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = 'Ez a felhasználónév már foglalt!';
            $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
            header('Location: register.php');
            exit();
        }

        // Új felhasználó létrehozása
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (name, email, username, password) VALUES (?, ?, ?, ?)");

        if ($stmt->execute([$name, $email, $username, $hashed_password])) {
            $_SESSION['success'] = 'Sikeres regisztráció! Most már bejelentkezhet.';
            unset($_SESSION['form_data']); // Töröljük a mentett adatokat, ha sikeres a regisztráció
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['error'] = 'Hiba történt a regisztráció során!';
            $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
            header('Location: register.php');
            exit();
        }
    } catch (PDOException $e) {
        // Hibanaplózás (ne jelenjen meg a felhasználónak)
        error_log("Hiba a regisztráció során: " . $e->getMessage());
        $_SESSION['error'] = 'Hiba történt a regisztráció során!';
        $_SESSION['form_data'] = $_POST; // Mentjük a felhasználó által megadott adatokat
        header('Location: register.php');
        exit();
    }
}


require_once '../includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-body">
                <h2 class="text-center mb-4">Regisztráció</h2>

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
                        <label for="name" class="form-label">Teljes név</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adja meg a nevét!</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy érvényes email címet!</div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Felhasználónév</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?= isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy felhasználónevet!</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="invalid-feedback">A jelszónak legalább 8 karakter hosszúnak kell lennie!</div>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Jelszó megerősítése</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Kérjük, erősítse meg a jelszavát!</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Regisztráció</button>
                </form>

                <div class="text-center mt-3">
                    <a href="login.php">Már van fiókja? Jelentkezzen be!</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Töröljük a mentett adatokat, ha már nem szükségesek
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
require_once '../includes/footer.php';
?>