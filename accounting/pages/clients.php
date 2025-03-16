<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_client']) || isset($_POST['edit_client'])) {
        $company_name = sanitize($_POST['company_name']);
        $tax_number = sanitize($_POST['tax_number']);
        $reg_number = sanitize($_POST['reg_number']);
        $headquarters = sanitize($_POST['headquarters']);
        $contact_person = sanitize($_POST['contact_person']);
        $contact_number = sanitize($_POST['contact_number']);
        
        if (isset($_POST['add_client'])) {
            $stmt = $conn->prepare("INSERT INTO clients (user_id, CompanyName, tax_number, registration_number, headquarters, contact_person, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $company_name, $tax_number, $reg_number, $headquarters, $contact_person, $contact_number])) {
                $_SESSION['message'] = 'Ügyfél sikeresen hozzáadva!';
                $_SESSION['message_type'] = 'success';
            }
        } else {
            $client_id = $_POST['client_id'];
            $stmt = $conn->prepare("UPDATE clients SET CompanyName = ?, tax_number = ?, registration_number = ?, headquarters = ?, contact_person = ?, contact_number = ? WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$company_name, $tax_number, $reg_number, $headquarters, $contact_person, $contact_number, $client_id, $user_id])) {
                $_SESSION['message'] = 'Ügyfél adatai sikeresen frissítve!';
                $_SESSION['message_type'] = 'success';
            }
        }
        header('Location: clients.php');
        exit();
    }
    
    if (isset($_POST['delete_client'])) {
        $client_id = $_POST['client_id'];
        $stmt = $conn->prepare("DELETE FROM clients WHERE id = ? AND user_id = ?");
        if ($stmt->execute([$client_id, $user_id])) {
            $_SESSION['message'] = 'Ügyfél sikeresen törölve!';
            $_SESSION['message_type'] = 'success';
        }
        header('Location: clients.php');
        exit();
    }
}

require_once '../includes/header.php';

// Get client data for editing
$edit_client = null;
if ($action === 'edit' && isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $user_id]);
    $edit_client = $stmt->fetch();
    
    if (!$edit_client) {
        header('Location: clients.php');
        exit();
    }
}

// Get all clients for listing
$clients = [];
if ($action === 'list') {
    $stmt = $conn->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY CompanyName");
    $stmt->execute([$user_id]);
    $clients = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ügyfelek</title>
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
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            background: #ffffff;
            animation: fadeInUp 0.5s ease-out;
        }
        .card-header {
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color: #ffffff;
            border-radius: 20px 20px 0 0;
            padding: 1.5rem;
            font-weight: 600;
        }
        .card-body {
            padding: 2rem;
        }
        .form-label {
            font-weight: 500;
            color: #263238;
        }
        .form-control {
            border-radius: 10px;
            border: 1px solid #b0bec5;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #1976d2;
            box-shadow: 0 0 5px rgba(25, 118, 210, 0.3);
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
        .btn-secondary {
            background: #607d8b;
            border: none;
        }
        .btn-danger {
            background: #d32f2f;
            border: none;
        }
        .table {
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .table thead {
            background: #e3f2fd;
            color: #0d47a1;
        }
        .table th, .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        .table tbody tr {
            transition: background 0.3s ease;
        }
        .table tbody tr:hover {
            background: #f5f7fa;
        }
        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.875rem;
        }
        .text-muted {
            color: #78909c;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 1rem;
            }
            .card-body {
                padding: 1.5rem;
            }
            .btn {
                padding: 0.5rem 1.2rem;
            }
        }
    </style>
</head>
<body>
    <div class="content-wrapper">
        <?php if ($action === 'new' || $action === 'edit'): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $action === 'new' ? 'Új ügyfél hozzáadása' : 'Ügyfél szerkesztése'; ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="client_id" value="<?php echo $edit_client['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="company_name" class="form-label">Cégnév</label>
                                <input type="text" class="form-control" id="company_name" name="company_name" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['CompanyName']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tax_number" class="form-label">Adószám</label>
                                <input type="text" class="form-control" id="tax_number" name="tax_number" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['tax_number']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="reg_number" class="form-label">Cégjegyzékszám</label>
                                <input type="text" class="form-control" id="reg_number" name="reg_number" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['registration_number']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="headquarters" class="form-label">Székhely</label>
                                <input type="text" class="form-control" id="headquarters" name="headquarters" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['headquarters']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="contact_person" class="form-label">Kapcsolattartó neve</label>
                                <input type="text" class="form-control" id="contact_person" name="contact_person" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['contact_person']) : ''; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="contact_number" class="form-label">Kapcsolattartó telefonszáma</label>
                                <input type="text" class="form-control" id="contact_number" name="contact_number" 
                                       value="<?php echo $edit_client ? htmlspecialchars($edit_client['contact_number']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="clients.php" class="btn btn-secondary">Vissza</a>
                            <button type="submit" name="<?php echo $action === 'new' ? 'add_client' : 'edit_client'; ?>" class="btn btn-primary">
                                <?php echo $action === 'new' ? 'Ügyfél hozzáadása' : 'Módosítások mentése'; ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Ügyfelek kezelése</h5>
                    <a href="?action=new" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Új ügyfél
                    </a>
                </div>
                <div class="card-body">
                    <?php if (empty($clients)): ?>
                        <p class="text-center text-muted">Még nincsenek ügyfelek.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Cégnév</th>
                                        <th>Adószám</th>
                                        <th>Kapcsolattartó</th>
                                        <th>Telefonszám</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($clients as $client): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($client['CompanyName']); ?></td>
                                            <td><?php echo htmlspecialchars($client['tax_number']); ?></td>
                                            <td><?php echo htmlspecialchars($client['contact_person']); ?></td>
                                            <td><?php echo htmlspecialchars($client['contact_number']); ?></td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="?action=edit&id=<?php echo $client['id']; ?>" 
                                                       class="btn btn-sm btn-primary" title="Szerkesztés">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" action="" class="d-inline" 
                                                          onsubmit="return confirm('Biztosan törölni szeretné ezt az ügyfelet?');">
                                                        <input type="hidden" name="client_id" value="<?php echo $client['id']; ?>">
                                                        <button type="submit" name="delete_client" class="btn btn-sm btn-danger" title="Törlés">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>
</html>

<?php require_once '../includes/footer.php'; ?>