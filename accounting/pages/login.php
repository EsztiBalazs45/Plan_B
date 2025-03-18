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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bejelentkezés</title>
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
                Bejelentkezés
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
                        <label for="email" class="form-label">Email cím</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
                        <div class="invalid-feedback">Kérjük, adjon meg egy érvényes email címet!</div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Jelszó</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Kérjük, adja meg a jelszavát!</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Bejelentkezés</button>
                </form>

                <div class="text-center mt-4">
                    <a href="register.php">Még nem regisztrált? Regisztráljon most!</a>
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

<?php require_once '../includes/footer.php'; ?>