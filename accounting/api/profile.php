<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
require_once '../includes/config.php';

\Stripe\Stripe::setApiKey('sk_test_51R5NbyHUv7jEVnHmYVlHmBjKx6mbmqQtxWkqEKOp06JvQdAK4jx0IfGnhZdll4zKA3ee4knG1HWC3DJFmYTioA1D006q3pwsbW');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nincs bejelentkezve']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Kapcsolódási hiba: ' . $conn->connect_error]);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'SQL előkészítési hiba (users): ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("
            SELECT s.id, s.service_id, s.start_date, 
                   srv.service_name, srv.service_price
            FROM subscriptions s
            JOIN services srv ON s.service_id = srv.id
            WHERE s.user_id = ?
        ");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['error' => 'SQL előkészítési hiba (subscriptions): ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $subscriptions = [];
        while ($row = $result->fetch_assoc()) {
            $subscriptions[] = $row;
        }
        $stmt->close();

        http_response_code(200);
        echo json_encode(['user' => $user, 'subscriptions' => $subscriptions]);
        break;

    // A többi ág (POST, DELETE, PUT) változatlan maradhat
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (isset($data['create_subscription'])) {
            $service_id = (int)$data['service_id'];

            $stmt = $conn->prepare("SELECT service_name, service_price FROM services WHERE id = ?");
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen szolgáltatás azonosító']);
                exit;
            }
            $service = $result->fetch_assoc();
            $stmt->close();

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'huf',
                        'product_data' => ['name' => $service['service_name']],
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
            echo json_encode(['session_id' => $session->id]);
        } elseif (isset($data['change_password'])) {
            $current_password = $data['current_password'];
            $new_password = $data['new_password'];
            $confirm_password = $data['confirm_password'];

            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($new_password !== $confirm_password) {
                http_response_code(400);
                echo json_encode(['error' => 'Az új jelszavak nem egyeznek!']);
            } elseif (!password_verify($current_password, $user['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'A jelenlegi jelszó helytelen!']);
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $user_id);
                if ($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(['message' => 'Jelszó sikeresen megváltoztatva!']);
                } else {
                    http_response_code(500);
                    echo json_encode(['error' => 'Hiba történt a jelszó módosítása közben!']);
                }
                $stmt->close();
            }
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $subscription_id = (int)$data['subscription_id'];

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $subscription_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_row()[0] == 0) {
                throw new Exception("Érvénytelen vagy nem létező előfizetés!");
            }
            $stmt->close();

            $stmt = $conn->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ii", $subscription_id, $user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            http_response_code(200);
            echo json_encode(['message' => 'Előfizetés sikeresen lemondva!']);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Hiba történt: ' . $e->getMessage()]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $name = htmlspecialchars($data['name']);
        $email = htmlspecialchars($data['email']);
        $username = htmlspecialchars($data['username']);

        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Ez az email cím már foglalt!']);
            exit;
        }
        $stmt->close();

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->bind_param("si", $username, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Ez a felhasználónév már foglalt!']);
            exit;
        }
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, username = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $username, $user_id);
        if ($stmt->execute()) {
            $_SESSION['name'] = $name;
            http_response_code(200);
            echo json_encode(['message' => 'Profil sikeresen frissítve!']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Hiba történt a frissítés során!']);
        }
        $stmt->close();
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott HTTP metódus']);
        break;
}

$conn->close();
exit;
?>