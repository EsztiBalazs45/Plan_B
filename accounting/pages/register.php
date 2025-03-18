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

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff, #1e293b);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            max-width: 1200px; /* Nagyobb maximális szélesség */
        }
        .card {
            border: none;
            border-radius: 30px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.2);
            background: #ffffff;
            animation: fadeInUp 0.5s ease-out;
            overflow: hidden;
            max-width: 700px; /* Nagyobb szélesség */
            width: 100%;
        }
        .card-header {
            background: linear-gradient(135deg, #93c5fd, #3b82f6);
            color: #ffffff;
            border-radius: 30px 30px 0 0;
            padding: 2rem;
            text-align: center;
            font-weight: 600;
            font-size: 2rem; /* Nagyobb fejléc betűméret */
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        .card-body {
            padding: 3rem; /* Nagyobb belső padding */
        }
        h2 {
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 2rem;
            font-size: 2.5rem; /* Nagyobb címsor */
            text-align: center;
        }
        .form-label {
            color: #1e293b;
            font-weight: 500;
            font-size: 1.2rem; /* Nagyobb címke betűméret */
        }
        .form-control {
            border-radius: 50px;
            padding: 1rem 2rem; /* Nagyobb padding */
            border: 1px solid #d1d5db;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-size: 1.1rem; /* Nagyobb betűméret */
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.4);
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6, #1e40af);
            border: none;
            border-radius: 50px;
            padding: 1rem 2rem; /* Nagyobb gomb */
            font-weight: 500;
            font-size: 1.2rem; /* Nagyobb betűméret */
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1e3a8a);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .alert {
            border-radius: 20px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            font-size: 1.1rem; /* Nagyobb betűméret */
            text-align: center;
        }
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }
        .alert-success {
            background: #d1fae5;
            color: #047857;
            border: 1px solid #34d399;
        }
        .text-center a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem; /* Nagyobb betűméret */
            transition: color 0.3s ease;
        }
        .text-center a:hover {
            color: #1e40af;
            text-decoration: underline;
        }
        .invalid-feedback {
            font-size: 0.95rem; /* Nagyobb betűméret */
            color: #b91c1c;
            margin-top: 0.5rem;
            text-align: center;
        }
        @media (max-width: 768px) {
            .card {
                max-width: 90%; /* Reszponzív szélesség */
            }
            .card-body {
                padding: 2rem;
            }
            .card-header {
                font-size: 1.5rem;
                padding: 1.5rem;
            }
            h2 {
                font-size: 2rem;
            }
            .form-control {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
            .btn-primary {
                padding: 0.8rem 1.5rem;
                font-size: 1rem;
            }
            .form-label {
                font-size: 1rem;
            }
            .alert {
                font-size: 1rem;
            }
            .text-center a {
                font-size: 1rem;
            }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                Regisztráció
            </div>
            <div class="card-body">
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
                    <div class="mb-4">
                        <label for="name" class="form-label">Teljes név</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               value="<?= isset($_SESSION['form_data']['name']) ? htmlspecialchars($_SESSION['form_data']['name']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adja meg a nevét!</div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= isset($_SESSION['form_data']['email']) ? htmlspecialchars($_SESSION['form_data']['email']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy érvényes email címet!</div>
                    </div>

                    <div class="mb-4">
                        <label for="username" class="form-label">Felhasználónév</label>
                        <input type="text" class="form-control" id="username" name="username" required
                               value="<?= isset($_SESSION['form_data']['username']) ? htmlspecialchars($_SESSION['form_data']['username']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy felhasználónevet!</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                        <div class="invalid-feedback">A jelszónak legalább 8 karakter hosszúnak kell lennie!</div>
                    </div>

                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Jelszó megerősítése</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Kérjük, erősítse meg a jelszavát!</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Regisztráció</button>
                </form>

                <div class="text-center mt-4">
                    <a href="login.php">Már van fiókja? Jelentkezzen be!</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Bootstrap űrlap validáció
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>

<?php
// Töröljük a mentett adatokat, ha már nem szükségesek
if (isset($_SESSION['form_data'])) {
    unset($_SESSION['form_data']);
}
require_once '../includes/footer.php';
?>