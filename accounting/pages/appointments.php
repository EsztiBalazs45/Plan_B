<?php
require_once '../includes/header.php';
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Ügyfelek lekérdezése
$stmt = $conn->prepare("SELECT id, CompanyName FROM clients WHERE user_id = ? ORDER BY CompanyName");
$stmt->execute([$user_id]);
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Időpontok kezelése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_appointment']) || isset($_POST['edit_appointment'])) {
        $title = $_POST['title'] ?? '';
        $start_date = $_POST['start_date'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $client_id = $_POST['client_id'] ?? '';
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'pending';

        $start = date('Y-m-d H:i:s', strtotime("$start_date $start_time"));
        $end = date('Y-m-d H:i:s', strtotime("$end_date $end_time"));
        $now = date('Y-m-d H:i:s');

        // Múltbeli időpont tiltása
        if ($start < $now) {
            $_SESSION['error'] = 'Nem foglalhatsz múltbeli időpontot!';
            header('Location: appointments.php');
            exit();
        }

        try {
            if (isset($_POST['add_appointment'])) {
                $stmt = $conn->prepare("
                    INSERT INTO appointments (user_id, client_id, title, start, end, description, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$user_id, $client_id, $title, $start, $end, $description, $status]);
                $_SESSION['message'] = 'Időpont sikeresen létrehozva!';
            } else {
                $appointment_id = $_POST['appointment_id'];
                $stmt = $conn->prepare("
                    UPDATE appointments
                    SET title = ?, client_id = ?, start = ?, end = ?, description = ?, status = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->execute([$title, $client_id, $start, $end, $description, $status, $appointment_id, $user_id]);
                $_SESSION['message'] = 'Időpont sikeresen frissítve!';
            }
            $_SESSION['message_type'] = 'success';
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Adatbázis hiba: ' . $e->getMessage();
        }
        header('Location: appointments.php');
        exit();
    }

    if (isset($_POST['delete_appointment'])) {
        $appointment_id = $_POST['appointment_id'];
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        $_SESSION['message'] = 'Időpont sikeresen törölve!';
        $_SESSION['message_type'] = 'success';
        header('Location: appointments.php');
        exit();
    }
}

// Szerkesztéshez adatlekérés
$edit_appointment = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    $edit_appointment = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Összes időpont lekérdezése
$appointments = [];
if ($action === 'list') {
    $stmt = $conn->prepare("
        SELECT a.*, c.CompanyName 
        FROM appointments a 
        LEFT JOIN clients c ON a.client_id = c.id 
        WHERE a.user_id = ? 
        ORDER BY a.start DESC
    ");
    $stmt->execute([$user_id]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>Időpontok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<?php if ($action === 'new' || $action === 'edit'): ?>
    <div class="card">
        <div class="card-header">
            <h5><?php echo $action === 'new' ? 'Új időpont' : 'Időpont szerkesztése'; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="appointment_id" value="<?php echo $edit_appointment['id'] ?? ''; ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Cím</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo $edit_appointment['title'] ?? ''; ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Kezdés dátuma</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['start'])) : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="start_time" class="form-label">Kezdés ideje</label>
                        <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['start'])) : '09:00'; ?>" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Befejezés dátuma</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['end'])) : date('Y-m-d'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_time" class="form-label">Befejezés ideje</label>
                        <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['end'])) : '10:00'; ?>" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="client_id" class="form-label">Ügyfél</label>
                    <select class="form-select" id="client_id" name="client_id" required>
                        <option value="">Válasszon ügyfelet...</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>" <?php echo $edit_appointment && $edit_appointment['client_id'] == $client['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($client['CompanyName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Leírás</label>
                    <textarea class="form-control" id="description" name="description" required><?php echo $edit_appointment['description'] ?? ''; ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Státusz</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="pending" <?php echo $edit_appointment && $edit_appointment['status'] === 'pending' ? 'selected' : ''; ?>>Függőben</option>
                        <option value="confirmed" <?php echo $edit_appointment && $edit_appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>Megerősítve</option>
                        <option value="canceled" <?php echo $edit_appointment && $edit_appointment['status'] === 'canceled' ? 'selected' : ''; ?>>Lemondva</option>
                    </select>
                </div>
                <button type="submit" name="<?php echo $action === 'new' ? 'add_appointment' : 'edit_appointment'; ?>" class="btn btn-primary">Mentés</button>
            </form>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-header">
            <h5>Időpontok</h5>
            <a href="?action=new" class="btn btn-primary">Új időpont</a>
        </div>
        <div class="card-body">
            <?php if (empty($appointments)): ?>
                <p>Még nincsenek időpontok.</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kezdés</th>
                            <th>Befejezés</th>
                            <th>Cím</th>
                            <th>Ügyfél</th>
                            <th>Leírás</th>
                            <th>Státusz</th>
                            <th>Műveletek</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?php echo date('Y.m.d H:i', strtotime($appointment['start'])); ?></td>
                                <td><?php echo date('Y.m.d H:i', strtotime($appointment['end'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['title']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['CompanyName']); ?></td>
                                <td><?php echo htmlspecialchars($appointment['description']); ?></td>
                                <td><?php echo $appointment['status']; ?></td>
                                <td>
                                    <a href="?action=edit&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">Szerkesztés</a>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Biztosan törlöd?');">
                                        <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                        <button type="submit" name="delete_appointment" class="btn btn-sm btn-danger">Törlés</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>