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
    $cardholder_name = htmlspecialchars($_POST['name']);
    $card_number = htmlspecialchars($_POST['cardNumber']);
    $expiry_date = htmlspecialchars($_POST['expiryDate']);
    $cvv = htmlspecialchars($_POST['cvv']);

    // Előfizetés rögzítése
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date, status) VALUES (?, ?, NOW(), 'active')");
        $stmt->bind_param("ii", $user_id, $service_id);
        $stmt->execute();
        $subscription_id = $conn->insert_id;

        // Fizetési adatok rögzítése
        $stmt = $conn->prepare("INSERT INTO payment_details (user_id, subscription_id, cardholder_name, card_number, expiry_date, cvv) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $user_id, $subscription_id, $cardholder_name, $card_number, $expiry_date, $cvv);
        $stmt->execute();

        $conn->commit();
        $_SESSION['message'] = 'Sikeresen előfizettél!';
        $_SESSION['message_type'] = 'success';
        header('Location: profile.php');
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Hiba az előfizetés során: " . $e->getMessage());
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
        /* Szolgáltatási kártyák új dizájnja */
        .service-container {
            padding: 60px 0;
            min-height: 100vh;
        }

        .service-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1a2e44;
            text-align: center;
            margin-bottom: 40px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .service-card {
            border: none;
            border-radius: 20px;
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.2);
        }

        .card-body {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card-title {
            font-size: 1.8rem;
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }

        .card-text {
            font-size: 1rem;
            color: #7f8c8d;
            text-align: center;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .card-subtitle {
            font-size: 1.2rem;
            color: #e74c3c;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            border-radius: 25px;
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 500;
            background: linear-gradient(90deg, #3498db, #2980b9);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #2980b9, #3498db);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        /* Fizetési modális ablak új dizájnja */
        .modal-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-in-out;
        }

        .modal-custom.show {
            display: flex;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-content {
            width: 400px;
            background: #0c0f14;
            box-shadow: 0px 187px 75px rgba(0, 0, 0, 0.01),
                0px 105px 63px rgba(0, 0, 0, 0.05), 0px 47px 47px rgba(0, 0, 0, 0.09),
                0px 12px 26px rgba(0, 0, 0, 0.1), 0px 0px 0px rgba(0, 0, 0, 0.1);
            border-radius: 25px;
            padding: 30px;
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .modal-header {
            color: #d17842;
            font-size: 1.5rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #21262e;
        }

        .form .label {
            display: flex;
            flex-direction: column;
            gap: 5px;
            height: fit-content;
        }

        .form .label:has(input:focus) .title {
            top: 0;
            left: 0;
            color: #d17842;
        }

        .form .label .title {
            padding: 0 10px;
            transition: all 300ms;
            font-size: 12px;
            color: #8b8e98;
            font-weight: 600;
            width: fit-content;
            top: 14px;
            position: relative;
            left: 15px;
            background: #0c0f14;
        }

        .form .input-field {
            width: auto;
            height: 50px;
            text-indent: 15px;
            border-radius: 15px;
            outline: none;
            background-color: transparent;
            border: 1px solid #21262e;
            transition: all 0.3s;
            caret-color: #d17842;
            color: #aeaeae;
        }

        .form .input-field:hover {
            border-color: rgba(209, 121, 66, 0.5);
        }

        .form .input-field:focus {
            border-color: #d17842;
        }

        .form .split {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            width: 100%;
            gap: 15px;
        }

        .form .split label {
            width: 130px;
        }

        .form .checkout-btn {
            margin-top: 20px;
            padding: 15px;
            border-radius: 25px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.15, 0.83, 0.66, 1);
            cursor: pointer;
            font-size: 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            border: none;
            justify-content: center;
            color: #fff;
            border: 2px solid transparent;
            background: #d17842;
        }

        .form .checkout-btn:hover {
            color: #d17842;
            border: 2px solid #d17842;
            background: transparent;
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
    <div class="service-container">
        <h2 class="service-title">Szolgáltatási csomagok</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4 justify-content-center">
            <?php foreach ($services as $service): ?>
                <div class="col">
                    <div class="service-card h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($service["service_name"]); ?></h5>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars($service["service_description"])); ?></p>
                            <h6 class="card-subtitle"><?php echo number_format($service["service_price"], 0, ',', ' '); ?> Ft/hó</h6>
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

    <!-- Új fizetési modális ablak -->
    <div class="modal-custom" id="paymentModal">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Fizetés - <span id="selectedPackageName"></span> (<span id="selectedPackagePrice"></span> Ft/hó)</h5>
            </div>
            <form class="form" id="paymentForm" method="POST">
                <input type="hidden" name="service_id" id="serviceId">
                <label for="name" class="label">
                    <span class="title">Kártyatulajdonos neve</span>
                    <input class="input-field" type="text" name="name" placeholder="Teljes név" required>
                </label>
                <label for="serialCardNumber" class="label">
                    <span class="title">Kártyaszám</span>
                    <input id="serialCardNumber" class="input-field" type="number" name="cardNumber" placeholder="0000 0000 0000 0000" required>
                </label>
                <div class="split">
                    <label for="ExDate" class="label">
                        <span class="title">Lejárati dátum</span>
                        <input id="ExDate" class="input-field" type="text" name="expiryDate" placeholder="01/23" required>
                    </label>
                    <label for="cvv" class="label">
                        <span class="title">CVV</span>
                        <input id="cvv" class="input-field" type="number" name="cvv" placeholder="CVV" required>
                    </label>
                </div>
                <button type="submit" name="subscribe" class="checkout-btn">Fizetés</button>
            </form>
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
                $("#paymentModal").addClass("show");
            });

            // Modális ablak bezárása (pl. háttérre kattintással)
            $(document).on("click", ".modal-custom", function(e) {
                if (e.target === this) {
                    $("#paymentModal").removeClass("show");
                }
            });

            $("#paymentForm").on("submit", function(e) {
                let cardNumber = $("#serialCardNumber").val();
                if (!/^\d{16}$/.test(cardNumber)) {
                    alert("A kártyaszámnak pontosan 16 számjegyből kell állnia!");
                    e.preventDefault();
                    return;
                }

                let expiryDate = $("#ExDate").val();
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    alert("A lejárati dátum formátuma nem megfelelő! Használd a MM/YY formátumot.");
                    e.preventDefault();
                    return;
                }

                let [month, year] = expiryDate.split('/');
                let currentDate = new Date();
                let currentYear = currentDate.getFullYear() % 100;
                let currentMonth = currentDate.getMonth() + 1;
                if (year < currentYear || (year == currentYear && month < currentMonth)) {
                    alert("A megadott lejárati dátum múltbeli dátum!");
                    e.preventDefault();
                    return;
                }

                let cvv = $("#cvv").val();
                if (!/^\d{3}$/.test(cvv)) {
                    alert("A CVV kódnak pontosan 3 számjegyből kell állnia!");
                    e.preventDefault();
                    return;
                }

                // Sikeres fizetés animáció megjelenítése
                $("#paymentModal").removeClass("show");
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