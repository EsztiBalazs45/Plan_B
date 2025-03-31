<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Nem vagy bejelentkezve!']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$conn = new mysqli("localhost", "root", "", "asd");

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Adatbázis kapcsolódási hiba: ' . $conn->connect_error]);
    exit();
}

\Stripe\Stripe::setApiKey('sk_test_51R5NbyHUv7jEVnHmYVlHmBjKx6mbmqQtxWkqEKOp06JvQdAK4jx0IfGnhZdll4zKA3ee4knG1HWC3DJFmYTioA1D006q3pwsbW');

switch ($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM services");
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'SQL hiba a szolgáltatások lekérdezésekor: ' . $conn->error]);
            exit();
        }
        $services = [];
        while ($row = $result->fetch_assoc()) {
            $services[] = $row;
        }
        echo json_encode($services);
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data) || empty($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen vagy üres JSON adat!', 'received_data' => $data]);
            exit();
        }

        $service_id = filter_var($data['service_id'] ?? '', FILTER_VALIDATE_INT);
        $price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);

        if (!$service_id || $service_id === false || !$price || $price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen service_id/price!', 'data' => $data]);
            exit();
        }

        $conn->begin_transaction();
        try {
            // Szolgáltatás adatainak lekérdezése
            $stmt = $conn->prepare("SELECT service_name, service_price FROM services WHERE id = ?");
            if (!$stmt) {
                throw new Exception("SQL előkészítési hiba (services): " . $conn->error);
            }
            $stmt->bind_param("i", $service_id);
            if (!$stmt->execute()) {
                throw new Exception("SQL végrehajtási hiba (services): " . $stmt->error);
            }
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                throw new Exception("Érvénytelen szolgáltatás azonosító: " . $service_id);
            }
            $service = $result->fetch_assoc();

            // Ár ellenőrzése
            if ($price != $service['service_price']) {
                throw new Exception("Az ár nem egyezik a szolgáltatás árával! Küldött: $price, Elvárt: " . $service['service_price']);
            }

            // Aktív előfizetés ellenőrzése
            $stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND service_id = ? AND status = 'active'");
            if (!$stmt) {
                throw new Exception("SQL előkészítési hiba (subscriptions check): " . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $service_id);
            if (!$stmt->execute()) {
                throw new Exception("SQL végrehajtási hiba (subscriptions check): " . $stmt->error);
            }
            $result = $stmt->get_result();
            if ($result->fetch_row()[0] > 0) {
                throw new Exception("Már van aktív előfizetésed erre a szolgáltatásra!");
            }

            // Új előfizetés beszúrása "pending" státusszal
            $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date, status) VALUES (?, ?, NOW(), 'pending')");
            if (!$stmt) {
                throw new Exception("SQL előkészítési hiba (subscriptions insert): " . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $service_id);
            if (!$stmt->execute()) {
                throw new Exception("SQL végrehajtási hiba (subscriptions insert): " . $stmt->error);
            }
            $subscription_id = $conn->insert_id;

            // Stripe Checkout Session létrehozása
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'huf',
                        'product_data' => [
                            'name' => $service['service_name'],
                        ],
                        'unit_amount' => (int)($service['service_price'] * 100),
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://localhost/Bozont_cucc/accounting/pages/profile.php?subscription_id=' . $subscription_id,
                'cancel_url' => 'http://localhost/Bozont_cucc/accounting/pages/services.php',
                'client_reference_id' => (string)$subscription_id,
            ]);

            $conn->commit();
            http_response_code(200);
            echo json_encode([
                'message' => 'Továbbítunk a fizetési oldalra!',
                'payment_url' => $session->url
            ]);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode([
                'error' => 'Hiba történt: ' . $e->getMessage(),
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus!']);
        break;
}

$conn->close();
?>