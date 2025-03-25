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

// Szerkesztéshez adatlekérés (ha szükséges az oldal betöltésekor)
$edit_appointment = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    $edit_appointment = $stmt->fetch(PDO::FETCH_ASSOC);
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
</head>

<body>
    <div class="content-wrapper">
        <?php if ($action === 'new' || $action === 'edit'): ?>
            <div class="card">
                <div class="card-header">
                    <h5><?php echo $action === 'new' ? 'Új időpont' : 'Időpont szerkesztése'; ?></h5>
                </div>
                <div class="card-body">
                    <form id="appointmentForm" class="needs-validation" novalidate>
                        <input type="hidden" name="appointment_id" value="<?php echo $edit_appointment['id'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">Cím</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo $edit_appointment['title'] ?? ''; ?>" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : 'required'; ?>>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Kezdés dátuma</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['start'])) : date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : 'required'; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="start_time" class="form-label">Kezdés ideje</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['start'])) : '07:00'; ?>" min="07:00" max="16:00" step="1800" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : 'required'; ?>>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">Befejezés dátuma</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $edit_appointment ? date('Y-m-d', strtotime($edit_appointment['end'])) : date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : 'required'; ?>>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_time" class="form-label">Befejezés ideje</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" value="<?php echo $edit_appointment ? date('H:i', strtotime($edit_appointment['end'])) : '07:30'; ?>" min="07:00" max="16:00" step="1800" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : 'required'; ?>>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="client_id" class="form-label">Ügyfél</label>
                            <select class="form-select" id="client_id" name="client_id" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'disabled' : 'required'; ?>>
                                <option value="">Válasszon ügyfelet...</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id']; ?>" <?php echo $edit_appointment && $edit_appointment['client_id'] == $client['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($client['CompanyName']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Leírás</label>
                            <textarea class="form-control" id="description" name="description" <?php echo ($action === 'edit' && $edit_appointment['status'] === 'confirmed') ? 'readonly' : ''; ?>><?php echo $edit_appointment['description'] ?? ''; ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Státusz</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php if ($action === 'new'): ?>
                                    <option value="pending" selected>Függőben</option>
                                <?php elseif ($edit_appointment['status'] === 'confirmed'): ?>
                                    <option value="confirmed" selected disabled>Megerősítve</option>
                                    <option value="canceled">Lemondva</option>
                                <?php else: ?>
                                    <option value="pending" <?php echo $edit_appointment && $edit_appointment['status'] === 'pending' ? 'selected' : ''; ?>>Függőben</option>
                                    <option value="canceled" <?php echo $edit_appointment && $edit_appointment['status'] === 'canceled' ? 'selected' : ''; ?>>Lemondva</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Mentés</button>
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
                    <div id="appointmentsTable"></div>
                </div>
            </div>
        <?php endif; ?>

        <div id="messageContainer"></div>

        <!-- Ütközés esetén modal -->
        <div class="modal fade" id="conflictModal" tabindex="-1" aria-labelledby="conflictModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="conflictModalLabel">Időpont ütközés</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="conflictModalBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Bezárás</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const appointmentForm = document.getElementById('appointmentForm');
            const appointmentsTable = document.getElementById('appointmentsTable');
            const messageContainer = document.getElementById('messageContainer');

            if (appointmentsTable) {
                loadAppointments();
            }

            if (appointmentForm) {
                appointmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (!this.checkValidity()) {
                        this.classList.add('was-validated');
                        return;
                    }

                    const formData = new FormData(this);
                    const data = {
                        id: formData.get('appointment_id'),
                        title: formData.get('title'),
                        start: `${formData.get('start_date')} ${formData.get('start_time')}:00`,
                        end: `${formData.get('end_date')} ${formData.get('end_time')}:00`,
                        client_id: formData.get('client_id'),
                        description: formData.get('description'),
                        status: formData.get('status')
                    };

                    const method = data.id ? 'PUT' : 'POST';
                    fetch('../api/appointments.php', {
                            method: method,
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.error) {
                                if (result.error === 'Időpont ütközés!') {
                                    showConflictModal(data, result.available_slots);
                                } else {
                                    showMessage(result.error, 'danger');
                                }
                            } else {
                                showMessage(result.message, 'success');
                                setTimeout(() => window.location.href = 'appointments.php', 1000);
                            }
                        })
                        .catch(error => showMessage('Hiba történt: ' + error, 'danger'));
                });
            }

            function loadAppointments() {
                fetch('../api/appointments.php')
                    .then(response => response.json())
                    .then(appointments => {
                        if (appointments.length === 0) {
                            appointmentsTable.innerHTML = '<p class="text-muted">Még nincsenek időpontok.</p>';
                            return;
                        }

                        let html = `
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
                        `;
                        appointments.forEach(appointment => {
                            html += `
                                <tr>
                                    <td>${new Date(appointment.start).toLocaleString('hu-HU', { dateStyle: 'short', timeStyle: 'short' })}</td>
                                    <td>${new Date(appointment.end).toLocaleString('hu-HU', { dateStyle: 'short', timeStyle: 'short' })}</td>
                                    <td>${appointment.title}</td>
                                    <td>${appointment.CompanyName || ''}</td>
                                    <td>${appointment.description || ''}</td>
                                    <td class="status-${appointment.status}">${appointment.status === 'pending' ? 'Függőben' : 'Megerősítve'}</td>
                                    <td>
                                        <a href="?action=edit&id=${appointment.id}" class="btn btn-sm btn-primary">Szerkesztés</a>
                                        <button class="btn btn-sm btn-danger" onclick="deleteAppointment(${appointment.id})">Törlés</button>
                                    </td>
                                </tr>
                            `;
                        });
                        html += '</tbody></table></div>';
                        appointmentsTable.innerHTML = html;
                    });
            }

            window.deleteAppointment = function(id) {
                if (!confirm('Biztosan törlöd ezt az időpontot?')) return;

                fetch('api.php', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            id
                        })
                    })
                    .then(response => response.json())
                    .then(result => {
                        showMessage(result.message, 'success');
                        loadAppointments();
                    })
                    .catch(error => showMessage('Hiba történt: ' + error, 'danger'));
            };

            function showMessage(message, type) {
                messageContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
                setTimeout(() => messageContainer.innerHTML = '', 3000);
            }

            function showConflictModal(data, slots) {
                const modalBody = document.getElementById('conflictModalBody');
                let html = `
                    <p>A kiválasztott időpont (<strong>${data.start} - ${data.end}</strong>) már foglalt!</p>
                    <h6>Szabad időpontok az adott napon:</h6>
                `;
                if (slots.length > 0) {
                    html += '<ul class="list-group">';
                    slots.forEach(slot => {
                        html += `
                            <li class="list-group-item">
                                ${new Date(slot.start).toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' })} - 
                                ${new Date(slot.end).toLocaleTimeString('hu-HU', { hour: '2-digit', minute: '2-digit' })}
                                <button class="btn btn-sm btn-primary float-end" onclick="bookNewSlot('${slot.start}', '${slot.end}')">Foglalás</button>
                            </li>
                        `;
                    });
                    html += '</ul>';
                } else {
                    html += '<p>Nincsenek szabad időpontok ezen a napon.</p>';
                }
                modalBody.innerHTML = html;
                const conflictModal = new bootstrap.Modal(document.getElementById('conflictModal'));
                conflictModal.show();

                window.bookNewSlot = function(start, end) {
                    data.start = start;
                    data.end = end;
                    fetch('api.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(data)
                        })
                        .then(response => response.json())
                        .then(result => {
                            showMessage(result.message, 'success');
                            conflictModal.hide();
                            setTimeout(() => window.location.href = 'appointments.php', 1000);
                        });
                };
            }
        });
    </script>

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


    <?php require_once '../includes/footer.php'; ?>
</body>

</html>