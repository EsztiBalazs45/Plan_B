<?php
ob_start(); // Output buffering indítása
require_once '../includes/header.php';

// Jogosultságellenőrzés
if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit();
}

// Adatbázis kapcsolat ellenőrzése
if (!isset($conn)) {
    die("Adatbázis kapcsolat hiányzik.");
}

// CSRF token generálás
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Üzenetek kezelése
$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Érvénytelen CSRF token.");
    }

    // Felhasználó törlése
    if (isset($_POST['delete_user'])) {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        if ($user_id && $user_id != $_SESSION['user_id']) {
            try {
                $conn->beginTransaction();
                $stmt = $conn->prepare("DELETE FROM appointments WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stmt = $conn->prepare("DELETE FROM clients WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $conn->commit();
                $_SESSION['message'] = 'Felhasználó sikeresen törölve!';
                $_SESSION['message_type'] = 'success';
            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['message'] = 'Hiba: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'Nem törölheti saját magát!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: admin.php');
        exit();
    }

    // Felhasználó szerepkör módosítása
    if (isset($_POST['update_user_role'])) {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $new_role = htmlspecialchars(trim($_POST['new_role'] ?? ''), ENT_QUOTES, 'UTF-8');
        $allowed_roles = ['user', 'admin'];

        if ($user_id && $new_role && in_array($new_role, $allowed_roles)) {
            if ($user_id == $_SESSION['user_id'] && $new_role !== 'admin') {
                $_SESSION['message'] = 'Nem csökkentheti saját admin jogosultságát!';
                $_SESSION['message_type'] = 'danger';
            } else {
                try {
                    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                    if ($stmt->execute([$new_role, $user_id])) {
                        $_SESSION['message'] = 'Szerepkör sikeresen módosítva!';
                        $_SESSION['message_type'] = 'success';
                    } else {
                        $_SESSION['message'] = 'Nem sikerült a szerepkör módosítása!';
                        $_SESSION['message_type'] = 'danger';
                    }
                } catch (Exception $e) {
                    $_SESSION['message'] = 'Hiba a szerepkör módosításakor: ' . $e->getMessage();
                    $_SESSION['message_type'] = 'danger';
                }
            }
        } else {
            $_SESSION['message'] = 'Érvénytelen szerepkör vagy felhasználó!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: admin.php');
        exit();
    }

    // Ügyfél törlése
    if (isset($_POST['delete_client'])) {
        $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
        if ($client_id) {
            $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
            if ($stmt->execute([$client_id])) {
                $_SESSION['message'] = 'Ügyfél törölve!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Hiba az ügyfél törlése közben!';
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: admin.php?tab=clients');
        exit();
    }

    // Ügyfél szerkesztése
    if (isset($_POST['edit_client'])) {
        $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
        $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        if ($client_id && $name) {
            $stmt = $conn->prepare("UPDATE clients SET CompanyName = ? WHERE id = ?");
            if ($stmt->execute([$name, $client_id])) {
                $_SESSION['message'] = 'Ügyfél frissítve!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Hiba az ügyfél frissítése közben!';
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'Érvénytelen adat az ügyfél szerkesztéséhez!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: admin.php?tab=clients');
        exit();
    }

    // Időpont törlése
    if (isset($_POST['delete_appointment'])) {
        $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        if ($appointment_id) {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
            if ($stmt->execute([$appointment_id])) {
                $_SESSION['message'] = 'Időpont törölve!';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Hiba az időpont törlése közben!';
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: admin.php?tab=appointments');
        exit();
    }
}

// Aktuális fül meghatározása
$tab = $_GET['tab'] ?? 'users';

// Lekérdezések
$stmt = $conn->query("SELECT (SELECT COUNT(*) FROM users) AS total_users, 
                      (SELECT COUNT(*) FROM clients) AS total_clients, 
                      (SELECT COUNT(*) FROM appointments) AS total_appointments");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

// Felhasználók
$users = $conn->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Ügyfelek
$clients = $conn->query("SELECT c.*, u.name AS user_name FROM clients c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.id DESC")->fetchAll(PDO::FETCH_ASSOC);

// Időpontok
$appointments = $conn->query("SELECT a.*, u.name AS user_name FROM appointments a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Felület</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #eceff1, #cfd8dc);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
        }
        .navbar-custom {
            background: #263238;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
            padding: 1rem;
        }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link {
            color: #ffffff;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .navbar-custom .nav-link:hover, .navbar-custom .nav-link.active {
            color: #42a5f5;
        }
        .container {
            max-width: 1200px;
            margin-top: 2rem;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: #1976d2;
            color: #ffffff;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            font-weight: 600;
        }
        .dashboard-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .dashboard-card i {
            color: #1976d2;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .dashboard-card h3 {
            font-weight: 600;
            color: #263238;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .table thead th {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 600;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background: #f9fafb;
        }
        .table-hover tbody tr:hover {
            background: #e3f2fd;
            transition: background 0.2s ease;
        }
        .btn-custom {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        .btn-primary {
            background: #1976d2;
            border: none;
        }
        .btn-danger {
            background: #ef5350;
            border: none;
        }
        .role-admin {
            color: #2ecc71;
            font-weight: 600;
        }
        .role-user {
            color: #78909c;
            font-weight: 500;
        }
        .alert {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {
            .dashboard-card {
                margin-bottom: 1rem;
            }
            .table-responsive {
                font-size: 0.9rem;
            }
            .btn-custom {
                padding: 0.4rem 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigáció -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link <?php echo $tab === 'users' ? 'active' : ''; ?>" href="?tab=users">Felhasználók</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $tab === 'clients' ? 'active' : ''; ?>" href="?tab=clients">Ügyfelek</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $tab === 'appointments' ? 'active' : ''; ?>" href="?tab=appointments">Időpontok</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Üzenetek -->
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show mt-4" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Statisztikák -->
        <div class="row mt-4 mb-5">
            <div class="col-md-4"><div class="dashboard-card"><i class="fas fa-users"></i><h3><?php echo $stats['total_users']; ?></h3><p>Felhasználók</p></div></div>
            <div class="col-md-4"><div class="dashboard-card"><i class="fas fa-building"></i><h3><?php echo $stats['total_clients']; ?></h3><p>Ügyfelek</p></div></div>
            <div class="col-md-4"><div class="dashboard-card"><i class="fas fa-calendar-check"></i><h3><?php echo $stats['total_appointments']; ?></h3><p>Időpontok</p></div></div>
        </div>

        <!-- Tartalom -->
        <?php if ($tab === 'users'): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Felhasználók kezelése</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr><th>Név</th><th>Email</th><th>Szerepkör</th><th>ID</th><th>Műveletek</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" action="" class="d-inline">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <select name="new_role" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                                        <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Felhasználó</option>
                                                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Adminisztrátor</option>
                                                    </select>
                                                    <input type="hidden" name="update_user_role" value="1">
                                                </form>
                                            <?php else: ?>
                                                <span class="role-<?php echo $user['role']; ?>">
                                                    <?php echo $user['role'] === 'admin' ? 'Adminisztrátor' : 'Felhasználó'; ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" action="" class="d-inline" onsubmit="return confirm('Biztosan törli ezt a felhasználót?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-custom btn-sm"><i class="fas fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($tab === 'clients'): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Ügyfelek kezelése</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr><th>Név</th><th>Felhasználó</th><th>ID</th><th>Műveletek</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($client['CompanyName']); ?>" class="form-control form-control-sm d-inline-block w-auto">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="edit_client" class="btn btn-primary btn-custom btn-sm"><i class="fas fa-save"></i></button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($client['user_name'] ?? 'Nincs hozzárendelve'); ?></td>
                                        <td><?php echo $client['id']; ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Biztosan törli ezt az ügyfelet?');">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="delete_client" class="btn btn-danger btn-custom btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($tab === 'appointments'): ?>
            <div class="card mb-4">
                <div class="card-header"><h5 class="mb-0">Időpontok kezelése</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr><th>Leírás</th><th>Felhasználó</th><th>ID</th><th>Műveletek</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($appointment['description'] ?? 'Nincs leírás'); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['user_name'] ?? 'Nincs hozzárendelve'); ?></td>
                                        <td><?php echo $appointment['id']; ?></td>
                                        <td>
                                            <form method="POST" action="" class="d-inline" onsubmit="return confirm('Biztosan törli ezt az időpontot?');">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="delete_appointment" class="btn btn-danger btn-custom btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php 
ob_end_flush(); // Output buffering lezárása
require_once '../includes/footer.php'; 
?>