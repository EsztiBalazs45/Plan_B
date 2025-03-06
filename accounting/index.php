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
    // Get total clients for current user
    $stmt = $conn->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(id) as ids FROM clients WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClients = $result['count'];
    error_log("Found clients with IDs: " . $result['ids'] . " for user_id: " . $_SESSION['user_id']);

    // Get today's appointments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(start) = CURDATE() AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $todayAppointments = $stmt->fetchColumn();

    // Get pending appointments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending' AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $pendingAppointments = $stmt->fetchColumn();

    // Get this month's appointments
    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE MONTH(start) = MONTH(CURDATE()) AND YEAR(start) = YEAR(CURDATE()) AND user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $monthAppointments = $stmt->fetchColumn();

} catch(PDOException $e) {
    error_log("Error getting statistics: " . $e->getMessage());
    $totalClients = $todayAppointments = $pendingAppointments = $monthAppointments = 0;
}
?>

<div class="content-wrapper">
    <div class="welcome-section mb-5">
        <h1 class="display-4 mb-3">Üdvözöljük, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
        <p class="lead text-muted">Professzionális megoldás az Ön vállalkozásának könyvelési feladataihoz.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-users fa-3x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $totalClients; ?></h3>
                    <p class="card-text">Összes ügyfél</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-day fa-3x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $todayAppointments; ?></h3>
                    <p class="card-text">Mai időpontok</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-clock fa-3x mb-3"></i>
                    </div>
                    <h3 class="card-title h2 mb-3"><?php echo $pendingAppointments; ?></h3>
                    <p class="card-text">Függőben lévő időpontok</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
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
        <div class="col-md-4">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-primary mb-3">
                        <i class="fas fa-calendar-plus fa-2x"></i>
                    </div>
                    <h5 class="card-title">Időpontkezelés</h5>
                    <p class="card-text">Kezelje időpontjait és találkozóit egyszerűen és hatékonyan.</p>
                    <a href="pages/calendar.php" class="btn btn-primary mt-3">
                        <i class="fas fa-arrow-right"></i> Időpontok kezelése
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-success mb-3">
                        <i class="fas fa-user-plus fa-2x"></i>
                    </div>
                    <h5 class="card-title">Ügyfelek</h5>
                    <p class="card-text">Tartsa nyilván ügyfeleit és azok adatait egy helyen.</p>
                    <a href="pages/clients.php" class="btn btn-success mt-3">
                        <i class="fas fa-arrow-right"></i> Ügyfelek kezelése
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card action-card h-100">
                <div class="card-body">
                    <div class="action-icon text-info mb-3">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h5 class="card-title">Számlák</h5>
                    <p class="card-text">Készítsen és kezeljen számlákat egyszerűen és gyorsan.</p>
                    <a href="pages/invoices.php" class="btn btn-info text-white mt-3">
                        <i class="fas fa-arrow-right"></i> Számlák kezelése
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
}

.stat-card {
    border: none;
    border-radius: 15px;
    transition: transform 0.3s;
}

.stat-card:hover {
    transform: translateY(-5px);
}

.stat-icon {
    text-align: center;
    opacity: 0.8;
}

.action-card {
    border: none;
    border-radius: 15px;
    transition: all 0.3s;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
}

.action-icon {
    text-align: center;
}

.btn {
    border-radius: 10px;
    padding: 0.5rem 1.5rem;
    transition: all 0.3s;
}

.btn:hover {
    transform: translateX(5px);
}

.display-4 {
    font-weight: 600;
}

.lead {
    font-size: 1.2rem;
    opacity: 0.9;
}
</style>

<?php require_once 'includes/footer.php'; ?>
