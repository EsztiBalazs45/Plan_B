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
        body {
            background: linear-gradient(135deg, #f8fafc, #334155);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            color: #1e293b;
        }

        .service-container {
            padding: 60px 20px;
            min-height: 100vh;
            max-width: 1200px;
            margin: 0 auto;
        }

        .service-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            text-align: center;
            margin-bottom: 50px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .service-card {
            border: none;
            border-radius: 20px;
            background: #ffffff;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1.8rem;
            color: #1e40af;
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }

        .card-text {
            font-size: 1rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .card-subtitle {
            font-size: 1.3rem;
            color: #e11d48;
            font-weight: 700;
            text-align: center;
            margin-bottom: 25px;
        }

        .btn-primary {
            border-radius: 50px;
            padding: 12px 35px;
            font-size: 1.1rem;
            font-weight: 500;
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            border: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            transform: scale(1.05);
            box-shadow: 0 6px 18px rgba(59, 130, 246, 0.3);
        }

        .modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            max-width: 450px;
            background: #ffffff;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
            border-radius: 20px;
            z-index: 1000;
            padding: 20px;
        }

        .modal.show {
            display: block;
            animation: fadeIn 0.3s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translate(-50%, -45%); }
            to { opacity: 1; transform: translate(-50%, -50%); }
        }

        .form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
        }

        .credit-card-info--form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .input_container {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .split {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
        }

        .input_label {
            font-size: 12px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
        }

        .input_field {
            width: 100%;
            height: 45px;
            padding: 0 15px;
            border-radius: 12px;
            outline: none;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .input_field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            background-color: #ffffff;
        }

        .purchase--btn {
            height: 50px;
            background: linear-gradient(90deg, #3b82f6, #1e40af);
            border-radius: 12px;
            border: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            transition: all 0.3s ease;
        }

        .purchase--btn:hover {
            background: linear-gradient(90deg, #1e40af, #3b82f6);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .input_field::-webkit-outer-spin-button,
        .input_field::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .input_field[type=number] {
            -moz-appearance: textfield;
        }

        @media (max-width: 768px) {
            .service-title {
                font-size: 2rem;
            }
            .card-title {
                font-size: 1.5rem;
            }
            .modal {
                max-width: 90%;
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

    <div class="modal" id="paymentModal">
        <form class="form" id="paymentForm" method="POST">
            <button type="button" class="close-modal" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer;">×</button>
            <input type="hidden" name="service_id" id="serviceId">
            <div class="credit-card-info--form">
                <div class="input_container">
                    <label for="name" class="input_label">Kártyatulajdonos neve</label>
                    <input id="name" class="input_field" type="text" name="input-name" placeholder="Teljes név megadása" required>
                </div>
                <div class="input_container">
                    <label for="cardNumber" class="input_label">Kártyaszám</label>
                    <input id="cardNumber" class="input_field" type="number" name="cardNumber" placeholder="1234 5678 9123 4567" required>
                </div>
                <div class="input_container">
                    <label for="expiryDate" class="input_label">Lejárati dátum / CVV</label>
                    <div class="split">
                        <input id="expiryDate" class="input_field" type="text" name="expiryDate" placeholder="MM/YY" required>
                        <input id="cvv" class="input_field" type="number" name="cvv" placeholder="123" required>
                    </div>
                </div>
            </div>
            <button type="submit" name="subscribe" class="purchase--btn">Fizetés</button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $(document).on("click", ".open-payment-modal", function() {
                let serviceId = $(this).data("id");
                $("#serviceId").val(serviceId);
                $("#paymentModal").addClass("show");
            });

            $(document).on("click", ".close-modal", function() {
                $("#paymentModal").removeClass("show");
            });

            $(document).on("click", function(e) {
                if ($(e.target).is("#paymentModal") && !$(e.target).closest(".form").length) {
                    $("#paymentModal").removeClass("show");
                }
            });

            $("#paymentForm").on("submit", function(e) {
                e.preventDefault();

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

                $("#paymentModal").removeClass("show");
                this.submit();
            });
        });
    </script>
</body>

</html>
<?php require_once '../includes/footer.php'; ?>