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

// Get available services
$result = $conn->query("SELECT * FROM services");
$services = fetchAll($result);

// Handle subscription request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $service_id = (int)$_POST['service_id'];
    $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $user_id, $service_id);
    if ($stmt->execute()) {
        $_SESSION['message'] = 'Sikeresen előfizettél!';
        $_SESSION['message_type'] = 'success';
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['message'] = 'Hiba történt az előfizetés során!';
        $_SESSION['message_type'] = 'danger';
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="hu">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Szolgáltatások</title>
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

        .modal-content {
            border-radius: 15px;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn-primary,
        .btn-success {
            border-radius: 10px;
            transition: background-color 0.3s, transform 0.3s;
        }

        .btn-primary:hover,
        .btn-success:hover {
            transform: scale(1.05);
        }

        /* Sikeres fizetés animáció */
        .payment-success {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #5de2a3;
            color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.2);
            display: none;
            z-index: 1000;
        }

        .payment-success.show {
            display: block;
            animation: fadeInOut 3s ease-in-out;
        }

        @keyframes fadeInOut {
            0% {
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                opacity: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Szolgáltatási csomagok</h2>
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-4">
                    <div class="card mb-4 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($service["service_name"]); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($service["service_description"])); ?></p>
                            <h6 class="card-subtitle mb-2 text-muted"><?php echo number_format($service["service_price"], 0, ',', ' '); ?> Ft/hó</h6>
                            <button class="btn btn-primary mt-3 w-100 open-payment-modal"
                                data-id="<?php echo $service["service_id"]; ?>"
                                data-name="<?php echo htmlspecialchars($service["service_name"]); ?>"
                                data-price="<?php echo $service["service_price"]; ?>">
                                Feliratkozás
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Fizetési modális ablak -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fizetés</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Választott csomag: <strong id="selectedPackageName"></strong></p>
                    <p>Havi díj: <strong id="selectedPackagePrice"></strong> Ft</p>
                    <form id="paymentForm" method="POST">
                        <input type="hidden" name="service_id" id="serviceId">
                        <div class="mb-3">
                            <label for="cardNumber" class="form-label">Bankkártya szám</label>
                            <input type="text" class="form-control" id="cardNumber" required pattern="\d{16}" placeholder="1234 5678 9012 3456">
                            <small class="text-muted">Pontosan 16 számjegyből kell állnia.</small>
                        </div>
                        <div class="mb-3">
                            <label for="cardExpiry" class="form-label">Lejárati dátum</label>
                            <input type="text" class="form-control" id="cardExpiry" required placeholder="MM/YY">
                            <small class="text-muted">Formátum: MM/YY, és nem lehet múltbeli dátum.</small>
                        </div>
                        <div class="mb-3">
                            <label for="cardCVC" class="form-label">CVC</label>
                            <input type="text" class="form-control" id="cardCVC" required pattern="\d{3}" placeholder="123">
                            <small class="text-muted">Pontosan 3 számjegyből kell állnia.</small>
                        </div>
                        <button type="submit" name="subscribe" class="btn btn-success w-100">Fizetés</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sikeres fizetés animáció -->
    <div class="payment-success" id="paymentSuccess">
        <div class="container">
            <div class="left-side">
                <div class="card">
                    <div class="card-line"></div>
                    <div class="buttons"></div>
                </div>
                <div class="post">
                    <div class="post-line"></div>
                    <div class="screen">
                        <div class="dollar">$</div>
                    </div>
                    <div class="numbers"></div>
                    <div class="numbers-line2"></div>
                </div>
            </div>
            <div class="right-side">
                <div class="new">Sikeres fizetés!</div>
                <svg viewBox="0 0 451.846 451.847" height="512" width="512" xmlns="http://www.w3.org/2000/svg" class="arrow"><path fill="#cfcfcf" data-old_color="#000000" class="active-path" data-original="#000000" d="M345.441 248.292L151.154 442.573c-12.359 12.365-32.397 12.365-44.75 0-12.354-12.354-12.354-32.391 0-44.744L278.318 225.92 106.409 54.017c-12.354-12.359-12.354-32.394 0-44.748 12.354-12.359 32.391-12.359 44.75 0l194.287 194.284c6.177 6.18 9.262 14.271 9.262 22.366 0 8.099-3.091 16.196-9.267 22.373z"></path></svg>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(document).on("click", ".open-payment-modal", function() {
                let serviceId = $(this).data("id");
                let serviceName = $(this).data("name");
                let servicePrice = $(this).data("price");
                $("#serviceId").val(serviceId);
                $("#selectedPackageName").text(serviceName);
                $("#selectedPackagePrice").text(new Intl.NumberFormat('hu-HU').format(servicePrice));
                $("#paymentModal").modal("show");
            });

            $("#paymentForm").on("submit", function(e) {
                let cardNumber = $("#cardNumber").val();
                if (!/^\d{16}$/.test(cardNumber)) {
                    alert("A bankkártya számnak pontosan 16 számjegyből kell állnia!");
                    e.preventDefault();
                    return;
                }

                let cardExpiry = $("#cardExpiry").val();
                if (!/^\d{2}\/\d{2}$/.test(cardExpiry)) {
                    alert("A lejárati dátum formátuma nem megfelelő! Használd a MM/YY formátumot.");
                    e.preventDefault();
                    return;
                }

                let [month, year] = cardExpiry.split('/');
                let currentDate = new Date();
                let currentYear = currentDate.getFullYear() % 100;
                let currentMonth = currentDate.getMonth() + 1;
                if (year < currentYear || (year == currentYear && month < currentMonth)) {
                    alert("A megadott lejárati dátum múltbeli dátum!");
                    e.preventDefault();
                    return;
                }

                let cardCVC = $("#cardCVC").val();
                if (!/^\d{3}$/.test(cardCVC)) {
                    alert("A CVC kódnak pontosan 3 számjegyből kell állnia!");
                    e.preventDefault();
                    return;
                }

                // Sikeres fizetés animáció megjelenítése
                $("#paymentModal").modal("hide");
                $("#paymentSuccess").addClass("show");

                // Animáció eltüntetése 3 másodperc után
                setTimeout(function() {
                    $("#paymentSuccess").removeClass("show");
                }, 3000);
            });
        });
    </script>
</body>

</html>
<?php require_once '../includes/footer.php'; ?>