<?php
ob_start();
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: pages/login.php');
    exit();
}
// Ha a felhasználó admin, azonnal átirányítjuk az admin felületre
if (isAdmin()) {
    header('Location: pages/admin.php');
    exit();
}

// Debug session info
error_log("Current user_id: " . $_SESSION['user_id']);

// Get statistics
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(id) as ids FROM clients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClients = $result['count'];
    error_log("Found clients with IDs: " . $result['ids'] . " for user_id: " . $_SESSION['user_id']);

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(start) = CURDATE() AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $todayAppointments = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending' AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingAppointments = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE MONTH(start) = MONTH(CURDATE()) AND YEAR(start) = YEAR(CURDATE()) AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $monthAppointments = $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error getting statistics: " . $e->getMessage());
    $totalClients = $todayAppointments = $pendingAppointments = $monthAppointments = 0;
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f0f4f8, #334155);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #263238;
        }

        .content-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome-section {
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            padding: 3rem;
            border-radius: 20px;
            color: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            animation: fadeIn 1s ease-out;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, rgba(255, 255, 255, 0) 70%);
            transform: rotate(30deg);
            pointer-events: none;
        }

        .welcome-section h1 {
            font-weight: 600;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .welcome-section p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .stat-card {
            border: none;
            border-radius: 20px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
            position: relative;
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-card.bg-primary {
            background: #f0f4f8;
            color: #0d47a1;
            /* Sötétebb kék */
        }

        .stat-card.bg-success {
            background: #e8f5e9;
            color: #1b5e20;
            /* Sötétebb zöld */
        }

        .stat-card.bg-warning {
            background: #fff8e1;
            color: #e65100;
            /* Sötétebb narancs */
        }

        .stat-card.bg-info {
            background: #e0f7fa;
            color: #01579b;
            /* Sötétebb cián */
        }

        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .stat-card h3 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .stat-card p {
            font-size: 1rem;
            font-weight: 600;
            opacity: 1;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .action-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            animation: fadeInUp 0.7s ease-out;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .action-card .card-body {
            padding: 2rem;
            text-align: center;
        }

        .action-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
        }

        .action-card h5 {
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        .action-card p {
            font-size: 0.95rem;
            color: #607d8b;
        }

        .btn {
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-primary {
            background: #1976d2;
            border: none;
        }

        .btn-success {
            background: #2e7d32;
            border: none;
        }

        .btn-info {
            background: #0288d1;
            border: none;
        }

        .btn-warning {
            background: #f57c00;
            border: none;
        }

        h2 {
            font-weight: 600;
            color: #263238;
            margin-bottom: 2rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
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

        @media (max-width: 768px) {
            .welcome-section {
                padding: 2rem;
            }

            .welcome-section h1 {
                font-size: 2rem;
            }

            .stat-card,
            .action-card {
                margin-bottom: 1.5rem;
            }

            .stat-card h3 {
                font-size: 1.5rem;
            }

            .action-card .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="content-wrapper">
        <!-- Welcome Section -->
        <div class="welcome-section mb-5">
            <h1>Üdvözöljük, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Professzionális megoldás az Ön vállalkozásának könyvelési feladataihoz.</p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-primary">
                    <div class="card-body">
                        <i class="fas fa-users"></i>
                        <h3><?php echo $totalClients; ?></h3>
                        <p>Összes ügyfél</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-success">
                    <div class="card-body">
                        <i class="fas fa-calendar-day"></i>
                        <h3><?php echo $todayAppointments; ?></h3>
                        <p>Mai időpontok</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-warning">
                    <div class="card-body">
                        <i class="fas fa-clock"></i>
                        <h3><?php echo $pendingAppointments; ?></h3>
                        <p>Függőben lévő időpontok</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="card stat-card bg-info">
                    <div class="card-body">
                        <i class="fas fa-calendar-alt"></i>
                        <h3><?php echo $monthAppointments; ?></h3>
                        <p>Havi időpontok</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2>Gyors műveletek</h2>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <div class="action-icon text-primary">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <h5>Időpontkezelés</h5>
                        <p>Kezelje időpontjait és találkozóit egyszerűen és hatékonyan.</p>
                        <a href="pages/appointments.php" class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-right"></i> Időpontok kezelése
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <div class="action-icon text-success">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h5>Ügyfelek</h5>
                        <p>Tartsa nyilván ügyfeleit és azok adatait egy helyen.</p>
                        <a href="pages/clients.php" class="btn btn-success mt-3">
                            <i class="fas fa-arrow-right"></i> Ügyfelek kezelése
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <div class="action-icon text-info">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h5>Számlák</h5>
                        <p>Készítsen és kezeljen számlákat egyszerűen és gyorsan.</p>
                        <a href="pages/invoices.php" class="btn btn-info mt-3">
                            <i class="fas fa-arrow-right"></i> Számlák kezelése
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card action-card">
                    <div class="card-body">
                        <div class="action-icon text-warning">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h5>Dokumentumok</h5>
                        <p>Segítséget nyújtó dokumentumok letöltése.</p>
                        <a href="pages/documents.php" class="btn btn-warning mt-3">
                            <i class="fas fa-arrow-right"></i> Letölthető dokumentumok
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php require_once 'includes/footer.php'; ?>