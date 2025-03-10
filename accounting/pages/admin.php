<?php
require_once '../includes/header.php';

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Érvénytelen CSRF token.");
    }

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
                $_SESSION['message'] = 'Hiba történt a törlés során: ' . $e->getMessage();
                $_SESSION['message_type'] = 'danger';
            }
        } else {
            $_SESSION['message'] = 'Nem törölheti saját magát!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: admin.php');
        exit();
    }

    if (isset($_POST['update_user_role'])) {
        $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
        $new_role = filter_input(INPUT_POST, 'new_role', FILTER_SANITIZE_STRING);

        $allowed_roles = ['user', 'admin'];
        if ($user_id && $new_role && in_array($new_role, $allowed_roles) && $user_id != $_SESSION['user_id']) {
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$new_role, $user_id])) {
                $_SESSION['message'] = 'Felhasználó szerepköre sikeresen módosítva!';
                $_SESSION['message_type'] = 'success';
            }
        } else {
            $_SESSION['message'] = 'Érvénytelen művelet!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: admin.php');
        exit();
    }
}

// Statisztikai adatok egyszerre lekérdezése
$stmt = $conn->query("SELECT 
    (SELECT COUNT(*) FROM users) AS total_users,
    (SELECT COUNT(*) FROM clients) AS total_clients,
    (SELECT COUNT(*) FROM appointments) AS total_appointments");
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
$total_users = $stats['total_users'];
$total_clients = $stats['total_clients'];
$total_appointments = $stats['total_appointments'];

// Felhasználók lekérdezése
$stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Legutóbbi aktivitások lekérdezése
$stmt = $conn->prepare("SELECT 'appointment' as type, id, created_at, 'Új időpont létrehozva' as description FROM appointments
    UNION ALL
    SELECT 'client' as type, id, created_at, 'Új ügyfél hozzáadva' as description FROM clients
    ORDER BY created_at DESC
    LIMIT 10");
$stmt->execute();
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-users fa-2x mb-2"></i>
            <h3><?php echo $total_users; ?></h3>
            <p>Regisztrált felhasználók</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-building fa-2x mb-2"></i>
            <h3><?php echo $total_clients; ?></h3>
            <p>Összes ügyfél</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-card">
            <i class="fas fa-calendar-check fa-2x mb-2"></i>
            <h3><?php echo $total_appointments; ?></h3>
            <p>Összes időpont</p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Felhasználók kezelése</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Név</th>
                                <th>Email</th>
                                <th>Szerepkör</th>
                                <th>Regisztráció dátuma</th>
                                <th>Műveletek</th>
                            </tr>
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
                                                <select name="new_role" class="form-select form-select-sm d-inline-block w-auto"
                                                    onchange="this.form.submit()" style="width: 100px;">
                                                    <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Felhasználó</option>
                                                    <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                </select>
                                                <input type="hidden" name="update_user_role" value="1">
                                            </form>
                                        <?php else: ?>
                                            <?php echo ucfirst($user['role']); ?>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('Y.m.d H:i', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="POST" action="" class="d-inline"
                                                onsubmit="return confirm('Biztosan törölni szeretné ezt a felhasználót?');">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" name="delete_user" class="btn btn-sm btn-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Legutóbbi aktivitások</h5>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    <?php foreach ($recent_activities as $activity): ?>
                        <div class="activity-item d-flex align-items-center mb-3">
                            <div class="activity-icon me-3">
                                <?php if ($activity['type'] === 'appointment'): ?>
                                    <i class="fas fa-calendar text-primary"></i>
                                <?php else: ?>
                                    <i class="fas fa-user-plus text-success"></i>
                                <?php endif; ?>
                            </div>
                            <div class="activity-content">
                                <div class="activity-text"><?php echo htmlspecialchars($activity['description']); ?></div>
                                <small class="text-muted"><?php echo date('Y.m.d H:i', strtotime($activity['created_at'])); ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .activity-feed {
        max-height: 400px;
        overflow-y: auto;
    }
    .activity-item {
        padding: 10px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
    }
    .activity-item:hover {
        background-color: #f8f9fa;
    }
    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .activity-icon i {
        font-size: 1.2rem;
    }
</style>

<?php require_once '../includes/footer.php'; ?>