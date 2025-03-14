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

function fetchAll($result)
{
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

$result = $conn->query("SELECT * FROM services");
$services = fetchAll($result);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['subscribe'])) {
    $service_id = (int)$_POST['service_id'];
    $cardholder_name = htmlspecialchars($_POST['input-name']);
    $card_number = htmlspecialchars($_POST['cardNumber']);
    $expiry_date = htmlspecialchars($_POST['expiryDate']);
    $cvv = htmlspecialchars($_POST['cvv']);

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date, status) VALUES (?, ?, NOW(), 'active')");
        $stmt->bind_param("ii", $user_id, $service_id);
        $stmt->execute();
        $subscription_id = $conn->insert_id;

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

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: fit-content;
            height: fit-content;
            background: #FFFFFF;
            box-shadow: 0px 187px 75px rgba(0, 0, 0, 0.01), 0px 105px 63px rgba(0, 0, 0, 0.05), 0px 47px 47px rgba(0, 0, 0, 0.09), 0px 12px 26px rgba(0, 0, 0, 0.1), 0px 0px 0px rgba(0, 0, 0, 0.1);
            border-radius: 26px;
            max-width: 450px;
            z-index: 1000;
        }

        .modal.show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
        }

        .payment--options {
            width: calc(100% - 40px);
            display: grid;
            grid-template-columns: 33% 34% 33%;
            gap: 20px;
            padding: 10px;
        }

        .payment--options button {
            height: 55px;
            background: #F2F2F2;
            border-radius: 11px;
            padding: 0;
            border: 0;
            outline: none;
            cursor: pointer;
        }

        .separator {
            width: calc(100% - 20px);
            display: grid;
            grid-template-columns: 1fr 2fr 1fr;
            gap: 10px;
            color: #8B8E98;
            margin: 0 10px;
        }

        .separator>p {
            word-break: keep-all;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            margin: auto;
        }

        .separator .line {
            width: 100%;
            height: 1px;
            border: 0;
            background-color: #e8e8e8;
            margin: auto;
        }

        .credit-card-info--form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .input_container {
            width: 100%;
            height: fit-content;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .split {
            display: grid;
            grid-template-columns: 4fr 2fr;
            gap: 15px;
        }

        .split input {
            width: 100%;
        }

        .input_label {
            font-size: 10px;
            color: #8B8E98;
            font-weight: 600;
        }

        .input_field {
            width: auto;
            height: 40px;
            padding: 0 0 0 16px;
            border-radius: 9px;
            outline: none;
            background-color: #F2F2F2;
            border: 1px solid #e5e5e500;
            transition: all 0.3s cubic-bezier(0.15, 0.83, 0.66, 1);
        }

        .input_field:focus {
            border: 1px solid transparent;
            box-shadow: 0px 0px 0px 2px #242424;
            background-color: transparent;
        }

        .purchase--btn {
            height: 55px;
            background: linear-gradient(180deg, #363636 0%, #1B1B1B 50%, #000000 100%);
            border-radius: 11px;
            border: 0;
            outline: none;
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.15, 0.83, 0.66, 1);
        }

        .purchase--btn:hover {
            box-shadow: 0px 0px 0px 2px #FFFFFF, 0px 0px 0px 4px #0000003a;
        }

        .input_field::-webkit-outer-spin-button,
        .input_field::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .input_field[type=number] {
            -moz-appearance: textfield;
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

    <div class="modal" id="paymentModal">
        <form class="form" id="paymentForm" method="POST">
            <input type="hidden" name="service_id" id="serviceId">
            <div class="credit-card-info--form">
                <div class="input_container">
                    <label for="name" class="input_label">Card holder full name</label>
                    <input id="name" class="input_field" type="text" name="input-name" placeholder="Enter your full name" required>
                </div>
                <div class="input_container">
                    <label for="cardNumber" class="input_label">Card Number</label>
                    <input id="cardNumber" class="input_field" type="number" name="cardNumber" placeholder="0000 0000 0000 0000" required>
                </div>
                <div class="input_container">
                    <label for="expiryDate" class="input_label">Expiry Date / CVV</label>
                    <div class="split">
                        <input id="expiryDate" class="input_field" type="text" name="expiryDate" placeholder="01/23" required>
                        <input id="cvv" class="input_field" type="number" name="cvv" placeholder="CVV" required>
                    </div>
                </div>
            </div>
            <button type="submit" name="subscribe" class="purchase--btn">Checkout</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            // Modal megnyitása
            $(document).on("click", ".open-payment-modal", function() {
                let serviceId = $(this).data("id");
                $("#serviceId").val(serviceId);
                $("#paymentModal").addClass("show");
            });

            // Modal bezárása, ha a modalon kívülre kattintanak
            $(document).on("click", function(e) {
                if ($(e.target).is("#paymentModal") && !$(e.target).closest(".form").length) {
                    $("#paymentModal").removeClass("show");
                }
            });

            // Űrlap beküldése ellenőrzéssel
            $("#paymentForm").on("submit", function(e) {
                e.preventDefault(); // Megakadályozzuk az alapértelmezett küldést

                let cardNumber = $("#cardNumber").val();
                if (!/^\d{16}$/.test(cardNumber)) {
                    alert("A kártyaszámnak pontosan 16 számjegyből kell állnia!");
                    return;
                }

                let expiryDate = $("#expiryDate").val();
                if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
                    alert("A lejárati dátum formátuma nem megfelelő! Használd a MM/YY formátumot.");
                    return;
                }

                let [month, year] = expiryDate.split('/');
                let currentDate = new Date();
                let currentYear = currentDate.getFullYear() % 100;
                let currentMonth = currentDate.getMonth() + 1;
                if (year < currentYear || (year == currentYear && month < currentMonth)) {
                    alert("A megadott lejárati dátum múltbeli dátum!");
                    return;
                }

                let cvv = $("#cvv").val();
                if (!/^\d{3}$/.test(cvv)) {
                    alert("A CVV kódnak pontosan 3 számjegyből kell állnia!");
                    return;
                }

                // Modal bezárása és űrlap elküldése
                $("#paymentModal").removeClass("show");
                this.submit(); // Az űrlap elküldése
            });
        });
    </script>
</body>

</html>
<?php require_once '../includes/footer.php'; ?>