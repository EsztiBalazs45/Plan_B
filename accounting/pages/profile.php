<?php
ob_start();
require_once '../includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Adatbázis kapcsolat (mysqli)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asd";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

// Egyedi fetchAll függvény
function fetchAll($result)
{
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get all active subscriptions with payment details
$stmt = $conn->prepare("
    SELECT s.*, srv.service_name, pd.cardholder_name, pd.card_number, pd.expiry_date, pd.cvv
    FROM subscriptions s
    JOIN services srv ON s.service_id = srv.service_id
    LEFT JOIN payment_details pd ON s.id = pd.subscription_id AND s.user_id = pd.user_id
    WHERE s.user_id = ? AND s.status = 'active'
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$subscriptions = fetchAll($result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = htmlspecialchars($_POST['name']);
        $email = htmlspecialchars($_POST['email']);
        $username = htmlspecialchars($_POST['username']);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['message'] = 'Ez az email cím már foglalt!';
            $_SESSION['message_type'] = 'danger';
        } else {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $_SESSION['message'] = 'Ez a felhasználónév már foglalt!';
                $_SESSION['message_type'] = 'danger';
            } else {
                $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, username = ? WHERE id = ?");
                $stmt->bind_param("sssi", $name, $email, $username, $user_id);
                if ($stmt->execute()) {
                    $_SESSION['name'] = $name;
                    $_SESSION['message'] = 'Profil sikeresen frissítve!';
                    $_SESSION['message_type'] = 'success';
                    header('Location: profile.php');
                    exit();
                }
            }
        }
    }

    // Handle subscription cancellation
if (isset($_POST['cancel_subscription'])) {
    $subscription_id = (int)$_POST['subscription_id'];
    $conn->begin_transaction();
    try {
        // Először töröljük a payment_details rekordokat
        $stmt = $conn->prepare("DELETE FROM payment_details WHERE subscription_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $subscription_id, $user_id);
        $stmt->execute();

        // Utána töröljük a subscriptions rekordot
        $stmt = $conn->prepare("DELETE FROM subscriptions WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $subscription_id, $user_id);
        $stmt->execute();

        $conn->commit();
        $_SESSION['message'] = 'Előfizetés sikeresen lemondva és törölve!';
        $_SESSION['message_type'] = 'success';
        header('Location: profile.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Hiba az előfizetés törlése során: " . $e->getMessage());
        $_SESSION['message'] = 'Hiba történt az előfizetés törlése közben!';
        $_SESSION['message_type'] = 'danger';
    }
}
    // Handle payment details update
    if (isset($_POST['update_payment'])) {
        $subscription_id = (int)$_POST['subscription_id'];
        $cardholder_name = htmlspecialchars($_POST['cardholder_name']);
        $card_number = htmlspecialchars($_POST['card_number']);
        $expiry_date = htmlspecialchars($_POST['expiry_date']);
        $cvv = htmlspecialchars($_POST['cvv']);

        $stmt = $conn->prepare("UPDATE payment_details SET cardholder_name = ?, card_number = ?, expiry_date = ?, cvv = ? WHERE subscription_id = ? AND user_id = ?");
        $stmt->bind_param("ssssii", $cardholder_name, $card_number, $expiry_date, $cvv, $subscription_id, $user_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Fizetési adatok sikeresen frissítve!';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Hiba történt a fizetési adatok frissítése közben!';
            $_SESSION['message_type'] = 'danger';
        }
        header('Location: profile.php');
        exit();
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $_SESSION['message'] = 'Az új jelszavak nem egyeznek!';
            $_SESSION['message_type'] = 'danger';
        } elseif (!password_verify($current_password, $user['password'])) {
            $_SESSION['message'] = 'A jelenlegi jelszó helytelen!';
            $_SESSION['message_type'] = 'danger';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = 'Jelszó sikeresen megváltoztatva!';
                $_SESSION['message_type'] = 'success';
                header('Location: profile.php');
                exit();
            }
        }
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        .card {
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: scale(1.05);
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary,
        .btn-danger {
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-primary:hover,
        .btn-danger:hover {
            transform: scale(1.05);
        }

        .payment-details-form {
            display: none;
        }

        .payment-details-form.active {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message'], $_SESSION['message_type']); ?>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <img src="https://via.placeholder.com/150" alt="Profile" class="rounded-circle mb-3 profile-avatar">
                        <h4><?php echo htmlspecialchars($user['name']); ?></h4>
                        <p class="text-muted"><?php echo ucfirst($user['role']); ?></p>
                        <p class="text-muted"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p class="text-muted"><i class="fas fa-user"></i> <?php echo htmlspecialchars($user['username']); ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Profil szerkesztése -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Profil szerkesztése</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Teljes név</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email cím</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Felhasználónév</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Profil mentése</button>
                        </form>
                    </div>
                </div>

                <!-- Előfizetések kezelése -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Aktív előfizetéseim</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($subscriptions)): ?>
                            <p>Nincs aktív előfizetésed. <a href="services.php">Válassz egyet itt!</a></p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($subscriptions as $sub): ?>
                                    <div class="col-md-6">
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <h6><?php echo htmlspecialchars($sub['service_name']); ?></h6>
                                                <p>Kezdete: <?php echo date('Y-m-d', strtotime($sub['start_date'])); ?></p>
                                                <?php if ($sub['cardholder_name']): ?>
                                                    <p>Fizetési adatok:</p>
                                                    <p>Kártyatulajdonos: <?php echo htmlspecialchars($sub['cardholder_name']); ?></p>
                                                    <p>Kártyaszám: ****-****-****-<?php echo substr(htmlspecialchars($sub['card_number']), -4); ?></p>
                                                    <p>Lejárat: <?php echo htmlspecialchars($sub['expiry_date']); ?></p>
                                                    <p>CVV: ***</p>
                                                    <button class="btn btn-primary mt-2 edit-payment-btn" data-subscription-id="<?php echo $sub['id']; ?>">Fizetési adatok szerkesztése</button>
                                                <?php endif; ?>
                                                <form method="POST" style="margin-top: 10px;">
                                                    <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                    <button type="submit" name="cancel_subscription" class="btn btn-danger" onclick="return confirm('Biztosan lemondod ezt az előfizetést?');">Lemondás</button>
                                                </form>
                                            </div>
                                        </div>
                                        <!-- Fizetési adatok szerkesztése form -->
                                        <div class="payment-details-form" id="payment-form-<?php echo $sub['id']; ?>">
                                            <form method="POST" class="mt-3">
                                                <input type="hidden" name="subscription_id" value="<?php echo $sub['id']; ?>">
                                                <div class="mb-3">
                                                    <label for="cardholder_name_<?php echo $sub['id']; ?>" class="form-label">Kártyatulajdonos neve</label>
                                                    <input type="text" class="form-control" id="cardholder_name_<?php echo $sub['id']; ?>" name="cardholder_name" value="<?php echo htmlspecialchars($sub['cardholder_name'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="card_number_<?php echo $sub['id']; ?>" class="form-label">Kártyaszám</label>
                                                    <input type="number" class="form-control" id="card_number_<?php echo $sub['id']; ?>" name="card_number" value="<?php echo htmlspecialchars($sub['card_number'] ?? ''); ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="expiry_date_<?php echo $sub['id']; ?>" class="form-label">Lejárati dátum</label>
                                                    <input type="text" class="form-control" id="expiry_date_<?php echo $sub['id']; ?>" name="expiry_date" value="<?php echo htmlspecialchars($sub['expiry_date'] ?? ''); ?>" placeholder="MM/YY" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="cvv_<?php echo $sub['id']; ?>" class="form-label">CVV</label>
                                                    <input type="number" class="form-control" id="cvv_<?php echo $sub['id']; ?>" name="cvv" value="<?php echo htmlspecialchars($sub['cvv'] ?? ''); ?>" required>
                                                </div>
                                                <button type="submit" name="update_payment" class="btn btn-primary">Mentés</button>
                                                <button type="button" class="btn btn-secondary cancel-edit-btn" data-subscription-id="<?php echo $sub['id']; ?>">Mégse</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <p><a href="services.php" class="btn btn-primary">Új előfizetés hozzáadása</a></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Jelszó módosítása -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Jelszó módosítása</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Jelenlegi jelszó</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Új jelszó</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" required>
                                <div class="form-text">A jelszónak legalább 8 karaktert, egy számot, egy kis- és egy nagybetűt kell tartalmaznia.</div>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Új jelszó megerősítése</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">Jelszó módosítása</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    if (form.querySelector('#new_password')) {
                        var password = form.querySelector('#new_password');
                        var confirm = form.querySelector('#confirm_password');
                        if (password.value !== confirm.value) {
                            confirm.setCustomValidity('A jelszavak nem egyeznek!');
                            event.preventDefault();
                            event.stopPropagation();
                        } else {
                            confirm.setCustomValidity('');
                        }
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Fizetési adatok szerkesztése
        $('.edit-payment-btn').on('click', function() {
            const subscriptionId = $(this).data('subscription-id');
            $(`#payment-form-${subscriptionId}`).addClass('active');
        });

        $('.cancel-edit-btn').on('click', function() {
            const subscriptionId = $(this).data('subscription-id');
            $(`#payment-form-${subscriptionId}`).removeClass('active');
        });
    </script>

    <?php require_once '../includes/footer.php'; ?>
</body>

</html>