<?php
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get statistics
$user_id = $_SESSION['user_id'];

// Get total clients
$stmt = $conn->prepare("SELECT COUNT(*) as total_clients FROM clients WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_clients = $stmt->fetch()['total_clients'];

// Get upcoming appointments
$stmt = $conn->prepare("SELECT COUNT(*) as upcoming_appointments FROM appointments WHERE user_id = ? AND date >= CURDATE() AND status != 'canceled'");
$stmt->execute([$user_id]);
$upcoming_appointments = $stmt->fetch()['upcoming_appointments'];

// Get recent appointments
$stmt = $conn->prepare("
    SELECT a.*, c.CompanyName 
    FROM appointments a 
    LEFT JOIN clients c ON a.user_id = c.user_id 
    WHERE a.user_id = ? 
    ORDER BY a.date DESC, a.timeline DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_appointments = $stmt->fetchAll();

// Get recent clients
$stmt = $conn->prepare("
    SELECT * FROM clients 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
");
$stmt->execute([$user_id]);
$recent_clients = $stmt->fetchAll();
?>

<div class="row mb-4">
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="dashboard-card">
            <i class="fas fa-users fa-2x mb-2"></i>
            <h3><?php echo $total_clients; ?></h3>
            <p>Összes ügyfél</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="dashboard-card">
            <i class="fas fa-calendar-check fa-2x mb-2"></i>
            <h3><?php echo $upcoming_appointments; ?></h3>
            <p>Közelgő időpontok</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="dashboard-card">
            <i class="fas fa-chart-line fa-2x mb-2"></i>
            <h3>0</h3>
            <p>Mai bevétel</p>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 mb-3">
        <div class="dashboard-card">
            <i class="fas fa-tasks fa-2x mb-2"></i>
            <h3>0</h3>
            <p>Függő feladatok</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Legutóbbi időpontok</h5>
                <a href="appointments.php" class="btn btn-sm btn-primary">Mind megtekintése</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_appointments)): ?>
                    <p class="text-muted text-center">Nincsenek időpontok.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dátum</th>
                                    <th>Időpont</th>
                                    <th>Ügyfél</th>
                                    <th>Státusz</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_appointments as $appointment): ?>
                                    <tr>
                                        <td><?php echo date('Y.m.d', strtotime($appointment['date'])); ?></td>
                                        <td><?php echo date('H:i', strtotime($appointment['timeline'])); ?></td>
                                        <td><?php echo htmlspecialchars($appointment['CompanyName']); ?></td>
                                        <td>
                                            <span class="badge status-<?php echo $appointment['status']; ?>">
                                                <?php 
                                                    switch($appointment['status']) {
                                                        case 'pending':
                                                            echo 'Függőben';
                                                            break;
                                                        case 'confirmed':
                                                            echo 'Megerősítve';
                                                            break;
                                                        case 'canceled':
                                                            echo 'Lemondva';
                                                            break;
                                                    }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Legutóbbi ügyfelek</h5>
                <a href="clients.php" class="btn btn-sm btn-primary">Mind megtekintése</a>
            </div>
            <div class="card-body">
                <?php if (empty($recent_clients)): ?>
                    <p class="text-muted text-center">Nincsenek ügyfelek.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Cégnév</th>
                                    <th>Kapcsolattartó</th>
                                    <th>Telefonszám</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_clients as $client): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($client['CompanyName']); ?></td>
                                        <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                                        <td><?php echo htmlspecialchars($client['contact_number']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Gyors műveletek</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="clients.php?action=new" class="btn btn-primary d-block">
                            <i class="fas fa-user-plus"></i> Új ügyfél
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="appointments.php?action=new" class="btn btn-primary d-block">
                            <i class="fas fa-calendar-plus"></i> Új időpont
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-primary d-block">
                            <i class="fas fa-file-invoice"></i> Új számla
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-primary d-block">
                            <i class="fas fa-chart-bar"></i> Jelentések
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
