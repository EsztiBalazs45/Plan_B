<?php
ob_start();
require_once '../includes/header.php';

if (!isLoggedIn() || !isAdmin()) {
    header('Location: ../index.php');
    exit();
}

if (!isset($conn)) {
    die("Adatbázis kapcsolat hiányzik.");
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = $_SESSION['message'] ?? '';
$message_type = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);

// Lapozás beállítása
$per_page = 15;
$user_page = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;
$appointment_page = isset($_GET['appointment_page']) ? (int)$_GET['appointment_page'] : 1;
$user_offset = ($user_page - 1) * $per_page;
$appointment_offset = ($appointment_page - 1) * $per_page;

// Keresési és rendezési paraméterek
$sort_by_name = isset($_GET['sort']) && $_GET['sort'] === 'name_asc' ? 'ASC' : 'DESC';

// Form submissions
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
                $conn->prepare("DELETE FROM appointments WHERE user_id = ?")->execute([$user_id]);
                $conn->prepare("DELETE FROM clients WHERE user_id = ?")->execute([$user_id]);
                $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $conn->commit();
                $_SESSION['message'] = 'Felhasználó sikeresen törölve!';
                $_SESSION['message_type'] = 'success';
            } catch (Exception $e) {
                $conn->rollBack();
                $_SESSION['message'] = 'Hiba: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        }
        header('Location: admin.php');
        exit();
    }

    // Szerepkör módosítása
    if (isset($_POST['update_user_role'])) {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $new_role = htmlspecialchars(trim($_POST['new_role'] ?? ''), ENT_QUOTES, 'UTF-8');
        $allowed_roles = ['user', 'admin'];
        if ($user_id && $new_role && in_array($new_role, $allowed_roles)) {
            if ($user_id == $_SESSION['user_id'] && $new_role !== 'admin') {
                $_SESSION['message'] = 'Nem csökkentheti saját admin jogosultságát!';
                $_SESSION['message_type'] = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$new_role, $user_id]);
                $_SESSION['message'] = 'Szerepkör sikeresen módosítva!';
                $_SESSION['message_type'] = 'success';
            }
        }
        header('Location: admin.php');
        exit();
    }

    // Ügyfél törlése
    if (isset($_POST['delete_client'])) {
        $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
        $stmt->execute([$client_id]);
        $_SESSION['message'] = 'Ügyfél törölve!';
        $_SESSION['message_type'] = 'success';
        header('Location: admin.php?tab=clients');
        exit();
    }

    // Ügyfél szerkesztése
    if (isset($_POST['edit_client'])) {
        $client_id = filter_input(INPUT_POST, 'client_id', FILTER_VALIDATE_INT);
        $name = htmlspecialchars(trim($_POST['name'] ?? ''), ENT_QUOTES, 'UTF-8');
        $stmt = $conn->prepare("UPDATE clients SET CompanyName = ? WHERE id = ?");
        $stmt->execute([$name, $client_id]);
        $_SESSION['message'] = 'Ügyfél frissítve!';
        $_SESSION['message_type'] = 'success';
        header('Location: admin.php?tab=clients');
        exit();
    }

    // Időpont státusz módosítása
    if (isset($_POST['update_appointment_status'])) {
        $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $new_status = htmlspecialchars(trim($_POST['new_status'] ?? ''), ENT_QUOTES, 'UTF-8');
        $allowed_statuses = ['confirmed', 'canceled'];
        if ($appointment_id && in_array($new_status, $allowed_statuses)) {
            $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $appointment_id]);
            $_SESSION['message'] = "Időpont $new_status státuszra frissítve!";
            $_SESSION['message_type'] = 'success';
        }
        header('Location: admin.php?tab=appointments');
        exit();
    }

    // Időpont törlése
    if (isset($_POST['delete_appointment'])) {
        $appointment_id = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->execute([$appointment_id]);
        $_SESSION['message'] = 'Időpont törölve!';
        $_SESSION['message_type'] = 'success';
        header('Location: admin.php?tab=appointments');
        exit();
    }
}

$tab = $_GET['tab'] ?? 'users';

// Lekérdezések
$stats = $conn->query("SELECT (SELECT COUNT(*) FROM users) AS total_users, 
                       (SELECT COUNT(*) FROM clients) AS total_clients, 
                       (SELECT COUNT(*) FROM appointments) AS total_appointments,
                       (SELECT COUNT(*) FROM subscriptions WHERE status = 'active') AS total_subscriptions")->fetch(PDO::FETCH_ASSOC);

// Felhasználók lekérdezése lapozással
$user_query = "SELECT * FROM users ORDER BY name $sort_by_name LIMIT :offset, :per_page";
$stmt = $conn->prepare($user_query);
$stmt->bindValue(':offset', $user_offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_user_pages = ceil($total_users / $per_page);

// Ügyfelek lekérdezése
$clients = $conn->query("SELECT c.*, u.name AS user_name FROM clients c LEFT JOIN users u ON c.user_id = u.id")->fetchAll(PDO::FETCH_ASSOC);

// Időpontok lekérdezése lapozással
$appointment_query = "SELECT a.*, u.name AS user_name, c.CompanyName FROM appointments a 
                     LEFT JOIN users u ON a.user_id = u.id 
                     LEFT JOIN clients c ON a.client_id = c.id 
                     ORDER BY a.start DESC LIMIT :offset, :per_page";
$stmt = $conn->prepare($appointment_query);
$stmt->bindValue(':offset', $appointment_offset, PDO::PARAM_INT);
$stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
$stmt->execute();
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_appointments = $conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
$total_appointment_pages = ceil($total_appointments / $per_page);

// Előfizetések lekérdezése
$subscriptions_query = "SELECT s.*, c.CompanyName AS client_name, u.name AS user_name, serv.service_name 
                        FROM subscriptions s 
                        LEFT JOIN clients c ON s.user_id = c.user_id 
                        LEFT JOIN users u ON c.user_id = u.id 
                        LEFT JOIN services serv ON s.service_id = serv.service_id 
                        ORDER BY s.start_date DESC";
$stmt = $conn->prepare($subscriptions_query);
$stmt->execute();
$subscriptions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Naptárhoz szükséges időpontok lekérdezése
$calendar_query = "SELECT a.id, a.start, a.end, u.name AS user_name 
                   FROM appointments a 
                   LEFT JOIN users u ON a.user_id = u.id 
                   WHERE a.status != 'canceled'";
$stmt = $conn->prepare($calendar_query);
$stmt->execute();
$calendar_events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Események formázása a naptárhoz
$formatted_events = array_map(function ($event) {
    return [
        'id' => $event['id'],
        'title' => $event['user_name'] ?? 'Nincs hozzárendelve',
        'start' => $event['start'],
        'end' => $event['end'],
        'extendedProps' => [
            'user_name' => $event['user_name'] ?? 'Nincs hozzárendelve'
        ]
    ];
}, $calendar_events);
$events_json = json_encode($formatted_events);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Felület</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #e0e7ff, #1e293b);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding: 2rem;
            color: #1e293b;
        }

        .navbar-custom {
            background: linear-gradient(135deg, #1e40af, #60a5fa);
            border-radius: 30px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            padding: 1.2rem;
            transition: all 0.3s ease;
        }

        .navbar-custom .navbar-brand,
        .navbar-custom .nav-link {
            color: #ffffff;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            color: #dbeafe;
            transform: scale(1.05);
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            animation: fadeInUp 0.5s ease-out;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, #93c5fd, #3b82f6);
            color: #ffffff;
            border-radius: 25px 25px 0 0;
            padding: 1.8rem;
            font-weight: 600;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }

        .dashboard-card {
            border-radius: 25px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: linear-gradient(135deg, #ffffff, #f8fafc);
        }

        .dashboard-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card i {
            color: #3b82f6;
            font-size: 2.5rem;
            margin-bottom: 0.8rem;
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover i {
            transform: scale(1.1);
        }

        .table {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: #bfdbfe;
            color: #1e3a8a;
        }

        .table th,
        .table td {
            padding: 1.2rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f1f5f9;
        }

        .subscription-card {
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
            background: linear-gradient(135deg, #ffffff, #f1f5f9);
            border: 1px solid #e5e7eb;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .subscription-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.15);
        }

        .subscription-card h6 {
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }

        .subscription-card p {
            margin: 0.6rem 0;
            color: #374151;
            font-size: 0.95rem;
        }

        .subscription-card strong {
            color: #1e293b;
            font-weight: 600;
        }

        .btn {
            border-radius: 50px;
            padding: 0.7rem 2rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #3b82f6;
            border: none;
        }

        .btn-primary:hover {
            background: #1e40af;
            transform: scale(1.05);
        }

        .btn-success {
            background: #10b981;
            border: none;
        }

        .btn-success:hover {
            background: #047857;
            transform: scale(1.05);
        }

        .btn-danger {
            background: #ef4444;
            border: none;
        }

        .btn-danger:hover {
            background: #b91c1c;
            transform: scale(1.05);
        }

        .search-bar {
            border-radius: 50px;
            padding: 0.8rem 1.8rem;
            border: 1px solid #d1d5db;
            width: 100%;
            max-width: 450px;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .search-bar:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 8px rgba(59, 130, 246, 0.4);
            outline: none;
        }

        .pagination .page-link {
            border-radius: 50px;
            margin: 0 0.3rem;
            color: #3b82f6;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .pagination .page-link:hover {
            background: #dbeafe;
            color: #1e3a8a;
            transform: scale(1.05);
        }

        .pagination .page-item.active .page-link {
            background: #3b82f6;
            color: #ffffff;
            border-color: #3b82f6;
        }

        .alert {
            border-radius: 15px;
            margin-top: 2rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
        }

        .status-active {
            color: #059669;
            font-weight: 600;
        }

        .status-expired {
            color: #d97706;
            font-weight: 600;
        }

        .status-canceled {
            color: #dc2626;
            font-weight: 600;
        }

        #calendar {
            max-width: 1500px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .fc-toolbar {
            background: linear-gradient(135deg, #93c5fd, #3b82f6);
            color: #ffffff;
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .fc-button {
            background: #1e40af !important;
            border: none !important;
            border-radius: 50px !important;
            padding: 0.7rem 1.8rem !important;
            transition: all 0.3s ease !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
            font-size: 1rem !important;
        }

        .fc-button:hover {
            background: #2563eb !important;
            transform: scale(1.05) !important;
        }

        .fc-button.fc-button-active {
            background: #10b981 !important;
        }

        .fc-event {
            background: linear-gradient(135deg, #60a5fa, #3b82f6);
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            color: #ffffff;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
            font-size: 1.2rem;
            line-height: 1.5;
            overflow: hidden;
            white-space: normal;
            word-wrap: break-word;
        }

        .fc-event:hover {
            transform: scale(1.03);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .fc-timegrid-slot {
            height: 2.5rem !important; /* Nagyobb slot-magasság a heti nézetben */
        }

        .fc-daygrid-day {
            height: 150px !important; /* Nagyobb napi cellák a havi nézetben */
        }

        .fc-timegrid-event {
            min-height: 2.5rem !important; /* Minimum magasság az eseményeknek */
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .navbar-custom {
                padding: 0.8rem;
            }

            .dashboard-card {
                margin-bottom: 1.5rem;
                padding: 1.5rem;
            }

            .subscription-card {
                padding: 1.5rem;
            }

            .btn {
                padding: 0.6rem 1.5rem;
            }

            .search-bar {
                max-width: 100%;
            }

            #calendar {
                padding: 1rem;
            }

            .fc-event {
                font-size: 1rem;
                padding: 0.6rem;
            }

            .fc-timegrid-slot {
                height: 2rem !important;
            }

            .fc-daygrid-day {
                height: 120px !important;
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
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
                    <li class="nav-item"><a class="nav-link <?php echo $tab === 'calendar' ? 'active' : ''; ?>" href="?tab=calendar">Naptár</a></li>
                    <li class="nav-item"><a class="nav-link <?php echo $tab === 'subscriptions' ? 'active' : ''; ?>" href="?tab=subscriptions">Előfizetések</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="content-wrapper">
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row mt-4 mb-5">
            <div class="col-md-3">
                <div class="dashboard-card"><i class="fas fa-users"></i>
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Felhasználók</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card"><i class="fas fa-building"></i>
                    <h3><?php echo $stats['total_clients']; ?></h3>
                    <p>Ügyfelek</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card"><i class="fas fa-calendar-check"></i>
                    <h3><?php echo $stats['total_appointments']; ?></h3>
                    <p>Időpontok</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-card"><i class="fas fa-money-bill"></i>
                    <h3><?php echo $stats['total_subscriptions']; ?></h3>
                    <p>Aktív előfizetések</p>
                </div>
            </div>
        </div>

        <!-- Dinamikus keresés -->
        <input type="text" class="search-bar" id="searchInput" placeholder="Keresés név vagy email alapján...">

        <?php if ($tab === 'users'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Felhasználók kezelése</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><a href="?tab=users&sort=<?php echo $sort_by_name === 'ASC' ? 'name_desc' : 'name_asc'; ?>&user_page=<?php echo $user_page; ?>" style="color: #1e3a8a; text-decoration: none;">Név <?php echo $sort_by_name === 'ASC' ? '↑' : '↓'; ?></a></th>
                                    <th>Email</th>
                                    <th>Szerepkör</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody id="userTable">
                                <?php foreach ($users as $user): ?>
                                    <tr data-name="<?php echo htmlspecialchars($user['name']); ?>" data-email="<?php echo htmlspecialchars($user['email']); ?>">
                                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" class="d-inline">
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
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Biztosan törli ezt a felhasználót?');">
                                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <button type="submit" name="delete_user" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <!-- Lapozás -->
                    <nav aria-label="Felhasználók lapozása">
                        <ul class="pagination justify-content-center mt-3">
                            <?php for ($i = 1; $i <= $total_user_pages; $i++): ?>
                                <li class="page-item <?php echo $user_page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?tab=users&user_page=<?php echo $i; ?>&sort=<?php echo $sort_by_name === 'ASC' ? 'name_asc' : 'name_desc'; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php elseif ($tab === 'clients'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Ügyfelek kezelése</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Név</th>
                                    <th>Felhasználó</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody id="clientTable">
                                <?php foreach ($clients as $client): ?>
                                    <tr data-name="<?php echo htmlspecialchars($client['CompanyName']); ?>" data-user="<?php echo htmlspecialchars($client['user_name'] ?? ''); ?>">
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="text" name="name" value="<?php echo htmlspecialchars($client['CompanyName']); ?>" class="form-control form-control-sm d-inline-block w-auto">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="edit_client" class="btn btn-primary btn-sm"><i class="fas fa-save"></i></button>
                                            </form>
                                        </td>
                                        <td><?php echo htmlspecialchars($client['user_name'] ?? 'Nincs hozzárendelve'); ?></td>
                                        <td>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Biztosan törli ezt az ügyfelet?');">
                                                <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="delete_client" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
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
            <div class="card">
                <div class="card-header">
                    <h5>Időpontok kezelése</h5>
                </div>
                <div class="card-body">
                    <div id="appointmentList">
                        <?php foreach ($appointments as $appointment): ?>
                            <div class="subscription-card"
                                data-title christ="<?php echo htmlspecialchars($appointment['title']); ?>"
                                data-user="<?php echo htmlspecialchars($appointment['user_name'] ?? ''); ?>"
                                data-client="<?php echo htmlspecialchars($appointment['CompanyName'] ?? ''); ?>"
                                data-description="<?php echo htmlspecialchars($appointment['description'] ?? ''); ?>">
                                <h6><strong>Címke:</strong> <?php echo htmlspecialchars($appointment['title']); ?></h6>
                                <p><strong>Foglalta:</strong> <?php echo htmlspecialchars($appointment['user_name'] ?? 'Nincs hozzárendelve'); ?></p>
                                <p><strong>Ügyfél:</strong> <?php echo htmlspecialchars($appointment['CompanyName'] ?? 'Nincs megadva'); ?></p>
                                <p><strong>Időpont:</strong> <?php echo date('Y.m.d H:i', strtotime($appointment['start'])) . ' - ' . date('H:i', strtotime($appointment['end'])); ?></p>
                                <p><strong>Leírás:</strong> <?php echo htmlspecialchars($appointment['description'] ?? 'Nincs leírás'); ?></p>
                                <p><strong>Státusz:</strong> <span class="status-<?php echo $appointment['status']; ?>">
                                        <?php echo $appointment['status'] === 'pending' ? 'Függőben' : ($appointment['status'] === 'confirmed' ? 'Megerősítve' : 'Lemondva'); ?>
                                    </span></p>
                                <?php if ($appointment['status'] === 'pending'): ?>
                                    <form method="POST" class="d-inline" style="margin-right: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="new_status" value="confirmed">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" name="update_appointment_status" class="btn btn-success btn-sm"><i class="fas fa-check"></i> Megerősítés</button>
                                    </form>
                                    <form method="POST" class="d-inline" style="margin-right: 0.5rem;">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <input type="hidden" name="new_status" value="canceled">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" name="update_appointment_status" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Lemondás</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Biztosan törli ezt az időpontot?');">
                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" name="delete_appointment" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Törlés</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Lapozás -->
                    <nav aria-label="Időpontok lapozása">
                        <ul class="pagination justify-content-center mt-3">
                            <?php for ($i = 1; $i <= $total_appointment_pages; $i++): ?>
                                <li class="page-item <?php echo $appointment_page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?tab=appointments&appointment_page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        <?php elseif ($tab === 'subscriptions'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Előfizetések kezelése</h5>
                </div>
                <div class="card-body">
                    <div id="subscriptionList">
                        <?php foreach ($subscriptions as $subscription): ?>
                            <div class="subscription-card"
                                data-client="<?php echo htmlspecialchars($subscription['client_name'] ?? ''); ?>"
                                data-user="<?php echo htmlspecialchars($subscription['user_name'] ?? ''); ?>"
                                data-service="<?php echo htmlspecialchars($subscription['service_name']); ?>">
                                <h6><strong>Ügyfél:</strong> <?php echo htmlspecialchars($subscription['client_name'] ?? 'Nincs megadva'); ?></h6>
                                <p><strong>Felhasználó:</strong> <?php echo htmlspecialchars($subscription['user_name'] ?? 'Nincs hozzárendelve'); ?></p>
                                <p><strong>Előfizetés típusa:</strong> <?php echo htmlspecialchars($subscription['service_name']); ?></p>
                                <p><strong>Státusz:</strong> <span class="status-<?php echo $subscription['status']; ?>">
                                        <?php echo $subscription['status'] === 'active' ? 'Aktív' : ($subscription['status'] === 'expired' ? 'Lejárt' : 'Lemondva'); ?>
                                    </span></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php elseif ($tab === 'calendar'): ?>
            <div class="card">
                <div class="card-header">
                    <h5>Naptár</h5>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const tab = '<?php echo $tab; ?>';

            if (tab === 'users') {
                const rows = document.querySelectorAll('#userTable tr');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name').toLowerCase();
                    const email = row.getAttribute('data-email').toLowerCase();
                    row.style.display = (name.includes(query) || email.includes(query)) ? '' : 'none';
                });
            } else if (tab === 'clients') {
                const rows = document.querySelectorAll('#clientTable tr');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name').toLowerCase();
                    const user = row.getAttribute('data-user').toLowerCase();
                    row.style.display = (name.includes(query) || user.includes(query)) ? '' : 'none';
                });
            } else if (tab === 'appointments') {
                const cards = document.querySelectorAll('#appointmentList .subscription-card');
                cards.forEach(card => {
                    const title = card.getAttribute('data-title').toLowerCase();
                    const user = card.getAttribute('data-user').toLowerCase();
                    const client = card.getAttribute('data-client').toLowerCase();
                    const description = card.getAttribute('data-description').toLowerCase();
                    card.style.display = (title.includes(query) || user.includes(query) || client.includes(query) || description.includes(query)) ? '' : 'none';
                });
            } else if (tab === 'subscriptions') {
                const cards = document.querySelectorAll('#subscriptionList .subscription-card');
                cards.forEach(card => {
                    const client = card.getAttribute('data-client').toLowerCase();
                    const user = card.getAttribute('data-user').toLowerCase();
                    const service = card.getAttribute('data-service').toLowerCase();
                    card.style.display = (client.includes(query) || user.includes(query) || service.includes(query)) ? '' : 'none';
                });
            }
        });

        <?php if ($tab === 'calendar'): ?>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'timeGridWeek',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,timeGridDay'
                    },
                    events: <?php echo $events_json; ?>,
                    eventTimeFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    slotMinTime: '07:00:00',
                    slotMaxTime: '17:00:00',
                    allDaySlot: false,
                    slotDuration: '00:30:00', 
                    slotLabelInterval: '01:00:00', 
                    slotHeight: 50, 
                    height: 'auto', 
                    expandRows: true, 
                    eventMinHeight: 50, 
                    eventContent: function(arg) {
                        var startTime = arg.event.start.toLocaleTimeString('hu-HU', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        var endTime = arg.event.end ? arg.event.end.toLocaleTimeString('hu-HU', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '';
                        return {
                            html: `
                            <div class="fc-event-main" style="padding: 5px;">
                                <strong style="font-size: 1.2rem;">${arg.event.extendedProps.user_name}</strong><br>
                                <small style="font-size: 1rem;">${startTime} - ${endTime}</small>
                            </div>
                        `
                        };
                    },
                    eventDidMount: function(info) {
                        var startTime = info.event.start.toLocaleTimeString('hu-HU', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        var endTime = info.event.end ? info.event.end.toLocaleTimeString('hu-HU', {
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '';
                        info.el.setAttribute('title', `
                        Foglalta: ${info.event.extendedProps.user_name}
                        Mikor: ${startTime} - ${endTime}
                    `);
                    }
                });
                calendar.render();
            });
        <?php endif; ?>
    </script>
</body>

</html>

<?php
ob_end_flush();
require_once '../includes/footer.php';
?>