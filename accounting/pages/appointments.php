<?php
ob_start();
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

// Szabad időpontok keresése egy adott napra
function getAvailableSlots($conn, $date) {
    $start_hour = 7; // 07:00
    $end_hour = 16; // 16:00
    $interval = 30; // 30 perces intervallumok
    $slots = [];
    $date_start = new DateTime("$date $start_hour:00");
    $date_end = new DateTime("$date $end_hour:00");

    // Összes időpont lekérdezése az adott napra
    $stmt = $conn->prepare("
        SELECT start, end 
        FROM appointments 
        WHERE DATE(start) = ? AND status != 'canceled'
    ");
    $stmt->execute([$date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    while ($date_start <= $date_end) {
        $slot_start = $date_start->format('Y-m-d H:i:s');
        $date_start->modify("+{$interval} minutes");
        $slot_end = $date_start->format('Y-m-d H:i:s');
        $is_booked = false;

        foreach ($booked_slots as $booked) {
            if (
                ($slot_start < $booked['end'] && $slot_end > $booked['start'])
            ) {
                $is_booked = true;
                break;
            }
        }

        if (!$is_booked) {
            $slots[] = ['start' => $slot_start, 'end' => $slot_end];
        }
    }
    return $slots;
}

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

        if ($start < $now) {
            $_SESSION['error'] = 'Nem foglalhatsz múltbeli időpontot!';
            header('Location: appointments.php');
            exit();
        }

        // Ütközés ellenőrzése az összes felhasználó időpontjával
        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE status != 'canceled'
            AND id != ?
            AND (
                (start <= ? AND end > ?) OR 
                (start < ? AND end >= ?) OR 
                (start >= ? AND end <= ?)
            )
        ");
        $stmt->execute([
            isset($_POST['appointment_id']) ? $_POST['appointment_id'] : 0,
            $start,
            $start,
            $end,
            $end,
            $start,
            $end
        ]);
        $conflict_count = $stmt->fetchColumn();

        if ($conflict_count > 0) {
            $_SESSION['conflict'] = [
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_date' => $end_date,
                'end_time' => $end_time,
                'available_slots' => getAvailableSlots($conn, $start_date)
            ];
            header('Location: appointments.php?action=new');
            exit();
        }

        try {
            if (isset($_POST['add_appointment'])) {
                $status = 'pending';
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
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <title>Időpontok</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(135deg, #f9fafb, #334155);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #374151;
            padding: 2rem;
        }

        .content-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            animation: fadeInUp 0.5s ease-out;
        }

        .card-header {
            background: linear-gradient(135deg, #dbeafe, #bfdbfe);
            color: #1e3a8a;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: #1e3a8a;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            max-width: 300px;
            transition: border-color 0.3s ease;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 5px rgba(59, 130, 246, 0.3);
        }

        .btn {
            border-radius: 50px;
            padding: 0.6rem 1.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #3b82f6;
            border: none;
        }

        .btn-primary:hover {
            background: #2563eb;
            transform: scale(1.05);
        }

        .btn-secondary {
            background: #6b7280;
            border: none;
        }

        .btn-secondary:hover {
            background: #4b5563;
            transform: scale(1.05);
        }

        .btn-danger {
            background: #ef4444;
            border: none;
        }

        .btn-danger:hover {
            background: #dc2626;
            transform: scale(1.05);
        }

        .table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .table thead {
            background: #dbeafe;
            color: #1e3a8a;
        }

        .table th,
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background: #f1f5f9;
        }

        .alert {
            border-radius: 10px;
            margin-top: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .status-pending {
            color: #d97706;
        }

        .status-confirmed {
            color: #059669;
        }

        .status-canceled {
            color: #dc2626;
        }

        .modal-content {
            border-radius: 15px;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .card-body {
                padding: 1.5rem;
            }

            .form-control,
            .form-select {
                max-width: 100%;
            }

            .btn {
                padding: 0.5rem 1.2rem;
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
    <div class="content-wrapper">
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
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['start'])) : date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Kezdés ideje</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['start'])) : '07:00'; ?>" min="07:00" max="16:00" step="1800" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Befejezés dátuma</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['end'])) : date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">Befejezés ideje</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['end'])) : '07:30'; ?>" min="07:00" max="16:00" step="1800" required>
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
                            <textarea class="form-control" id="description" name="description"><?php echo $edit_appointment['description'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Státusz</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php if ($action === 'new'): ?>
                                    <option value="pending" selected>Függőben</option>
                                <?php else: ?>
                                    <option value="pending" <?php echo $edit_appointment && $edit_appointment['status'] === 'pending' ? 'selected' : ''; ?>>Függőben</option>
                                    <option value="canceled" <?php echo $edit_appointment && $edit_appointment['status'] === 'canceled' ? 'selected' : ''; ?>>Lemondva</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" name="<?php echo $action === 'new' ? 'add_appointment' : 'edit_appointment'; ?>" class="btn btn-primary">Mentés</button>
                        <a href="appointments.php" class="btn btn-secondary">Vissza</a>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Időpontok</h5>
                    <a href="?action=new" class="btn btn-primary">Új időpont</a>
                </div>
                <div class="card-body">
                    <?php if (empty($appointments)): ?>
                        <p class="text-muted">Még nincsenek időpontok.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                            <td><?php echo htmlspecialchars($appointment['CompanyName'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($appointment['description'] ?? ''); ?></td>
                                            <td class="status-<?php echo $appointment['status']; ?>">
                                                <?php
                                                $statusLabels = ['pending' => 'Függőben', 'confirmed' => 'Megerősítve', 'canceled' => 'Lemondva'];
                                                echo $statusLabels[$appointment['status']] ?? ucfirst($appointment['status']);
                                                ?>
                                            </td>
                                            <td>
                                                <a href="?action=edit&id=<?php echo $appointment['id']; ?>" class="btn btn-sm btn-primary">Szerkesztés</a>
                                                <form method="POST" style="display:inline;" onsubmit="return confirm('Biztosan törlöd ezt az időpontot?');">
                                                    <input type="hidden" name="appointment_id" value="<?php echo $appointment['id']; ?>">
                                                    <button type="submit" name="delete_appointment" class="btn btn-sm btn-danger">Törlés</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type'] ?? 'info'; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <!-- Ütközés esetén modal -->
        <?php if (isset($_SESSION['conflict'])): ?>
            <div class="modal fade" id="conflictModal" tabindex="-1" aria-labelledby="conflictModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="conflictModalLabel">Időpont ütközés</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p>A kiválasztott időpont (<strong><?php echo date('Y.m.d H:i', strtotime($_SESSION['conflict']['start_date'] . ' ' . $_SESSION['conflict']['start_time'])); ?> - <?php echo date('H:i', strtotime($_SESSION['conflict']['end_date'] . ' ' . $_SESSION['conflict']['end_time'])); ?></strong>) már foglalt!</p>
                            <h6>Szabad időpontok az adott napon:</h6>
                            <?php if (!empty($_SESSION['conflict']['available_slots'])): ?>
                                <ul class="list-group">
                                    <?php foreach ($_SESSION['conflict']['available_slots'] as $slot): ?>
                                        <li class="list-group-item">
                                            <?php echo date('H:i', strtotime($slot['start'])) . ' - ' . date('H:i', strtotime($slot['end'])); ?>
                                            <button class="btn btn-sm btn-primary float-end" onclick="selectSlot('<?php echo date('Y-m-d', strtotime($slot['start'])); ?>', '<?php echo date('H:i', strtotime($slot['start'])); ?>', '<?php echo date('Y-m-d', strtotime($slot['end'])); ?>', '<?php echo date('H:i', strtotime($slot['end'])); ?>')">Kiválaszt</button>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>Nincsenek szabad időpontok ezen a napon.</p>
                            <?php endif; ?>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            startDateInput.addEventListener('change', function() {
                endDateInput.value = this.value;
            });

            // Bootstrap űrlap validáció
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            // Modal automatikus megjelenítése ütközés esetén
            <?php if (isset($_SESSION['conflict'])): ?>
                const conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
                conflictModal.show();
                <?php unset($_SESSION['conflict']); ?>
            <?php endif; ?>
        });

        function selectSlot(startDate, startTime, endDate, endTime) {
            document.getElementById('start_date').value = startDate;
            document.getElementById('start_time').value = startTime;
            document.getElementById('end_date').value = endDate;
            document.getElementById('end_time').value = endTime;
            bootstrap.Modal.getInstance(document.getElementById('conflictModal')).hide();
        }
    </script>
    <?php require_once '../includes/footer.php'; ?>
</body>

</html>