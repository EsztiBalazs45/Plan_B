<?php
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: pages/login.php');
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
} catch(PDOException $e) {
    error_log("Error getting statistics: " . $e->getMessage());
    $totalClients = $todayAppointments = $pendingAppointments = $monthAppointments = 0;
}
?>

<div class="content-wrapper" style="background: #f8f9fc;">
    <div class="welcome-section mb-5">
        <h1 class="display-4 mb-3">Üdvözöljük, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="lead text-muted">Professzionális megoldás az Ön vállalkozásának könyvelési feladataihoz.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-primary text-dark h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users fa-2.5x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $totalClients; ?></h3>
                    <p class="card-text">Összes ügyfél</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-success text-dark h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day fa-2.5x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $todayAppointments; ?></h3>
                    <p class="card-text">Mai időpontok</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-warning text-dark h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clock fa-2.5x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $pendingAppointments; ?></h3>
                    <p class="card-text">Függőben lévő időpontok</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card bg-info text-dark h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt fa-2.5x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $monthAppointments; ?></h3>
                    <p class="card-text">Havi időpontok</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <h2 class="mb-4">Gyors műveletek</h2>
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-primary mb-3">
                        <i class="fas fa-calendar-plus fa-1.5x"></i>
                    </div>
                    <h5 class="card-title">Időpontkezelés</h5>
                    <p class="card-text">Kezelje időpontjait és találkozóit egyszerűen és hatékonyan.</p>
                    <a href="pages/appointments.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-right"></i> Időpontok kezelése
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-success mb-3">
                        <i class="fas fa-user-plus fa-1.5x"></i>
                    </div>
                    <h5 class="card-title">Ügyfelek</h5>
                    <p class="card-text">Tartsa nyilván ügyfeleit és azok adatait egy helyen.</p>
                    <a href="pages/clients.php" class="btn btn-success mt-3">
                        <i class="fas fa-arrow-right"></i> Ügyfelek kezelése
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-info mb-3">
                        <i class="fas fa-file-invoice-dollar fa-1.5x"></i>
                    </div>
                    <h5 class="card-title">Számlák</h5>
                    <p class="card-text">Készítsen és kezeljen számlákat egyszerűen és gyorsan.</p>
                    <a href="pages/invoices.php" class="btn btn-info text-white mt-3">
                        <i class="fas fa-arrow-right"></i> Számlák kezelése
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-warning mb-3">
                        <i class="fas fa-file-invoice-dollar fa-1.5x"></i>
                    </div>
                    <h5 class="card-title">Dokumentumok</h5>
                    <p class="card-text">Segítséget nyújtó Dokumentumok</p>
                    <a href="pages/documents.php" class="btn btn-warning text-white mt-3">
                        <i class="fas fa-arrow-right"></i> Letölthető Dokumentumok
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.welcome-section {
    background: linear-gradient(135deg, var(--sidebar-bg) 0%, var(--sidebar-hover) 100%);
    padding: 3rem;
    border-radius: 15px;
    color: white;
    margin-bottom: 2rem;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}

.welcome-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    pointer-events: none;
}

.stat-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s;
    animation: fadeInUp 0.5s ease-out;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-card.bg-primary { background-color: #dbe2ef; color: #112d4e; }
.stat-card.bg-success { background-color: #d4f4dd; color: #3c8960; }
.stat-card.bg-warning { background-color: #fef6e4; color: #856d2f; }
.stat-card.bg-info { background-color: #d9f0f7; color: #2d8297; }

.stat-icon {
    text-align: center;
    opacity: 0.8;
}

.stat-icon i {
    font-size: 2.5rem;
}

.action-card {
    border: none;
    border-radius: 15px;
    transition: all 0.3s;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    animation: fadeInUp 0.5s ease-out;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.action-icon {
    text-align: center;
}

.action-icon i {
    font-size: 1.5rem;
}

.btn {
    border-radius: 10px;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s;
}

.btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.display-4 {
    font-weight: 600;
}

.lead {
    font-size: 1.2rem;
    opacity: 0.9;
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
    .stat-card, .action-card {
        margin-bottom: 1.5rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>