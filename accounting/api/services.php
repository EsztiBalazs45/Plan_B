<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../vendor/autoload.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nem vagy bejelentkezve!']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Adatbázis kapcsolódási hiba: ' . $conn->connect_error]);
    exit();
}

\Stripe\Stripe::setApiKey('asd');

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
            echo json_encode(['error' => 'Érvénytelen vagy üres JSON adat!']);
            exit();
        }

        $service_id = filter_var($data['service_id'] ?? '', FILTER_VALIDATE_INT);
        $price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);

        if (!$service_id || !$price || $price <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen service_id/price!']);
            exit();
        }

        $stmt = $conn->prepare("SELECT service_name, service_price FROM services WHERE id = ?");
        $stmt->bind_param("i", $service_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows == 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen szolgáltatás azonosító: ' . $service_id]);
            exit();
        }
        $service = $result->fetch_assoc();

        if ($price != $service['service_price']) {
            http_response_code(400);
            echo json_encode(['error' => 'Az ár nem egyezik a szolgáltatás árával!']);
            exit();
        }

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
            'success_url' => 'http://localhost/Bozont_cucc/accounting/pages/profile.php',
            'cancel_url' => 'http://localhost/Bozont_cucc/accounting/pages/services.php',
            'client_reference_id' => $user_id . '|' . $service_id,
            'metadata' => [
                'user_id' => $user_id,
                'service_id' => $service_id
            ]
        ]);

        http_response_code(200);
        echo json_encode([
            'message' => 'Továbbítunk a fizetési oldalra!',
            'payment_url' => $session->url
        ]);
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus!']);
        break;
}

$conn->close();
?>