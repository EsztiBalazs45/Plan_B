<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

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

switch ($method) {
    case 'GET':
        $result = $conn->query("SELECT * FROM services");
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'SQL hiba: ' . $conn->error]);
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
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen JSON adat!']);
            exit();
        }

        $service_id = filter_var($data['service_id'] ?? '', FILTER_VALIDATE_INT);
        $payment_method = $data['payment_method'] ?? '';
        $price = filter_var($data['price'] ?? 0, FILTER_VALIDATE_FLOAT);

        if (!$service_id || !in_array($payment_method, ['local', 'bank_transfer']) || !$price) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen adatok!', 'data' => $data]);
            exit();
        }

        $conn->begin_transaction();
        try {
            // Ellenőrizzük, hogy létezik-e a szolgáltatás
            $stmt = $conn->prepare("SELECT COUNT(*) FROM services WHERE id = ?"); // Javítva
            $stmt->bind_param("i", $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_row()[0] == 0) {
                throw new Exception("Érvénytelen szolgáltatás azonosító!");
            }

            // Ellenőrizzük, hogy a felhasználónak van-e már aktív előfizetése erre a szolgáltatásra
            $stmt = $conn->prepare("SELECT COUNT(*) FROM subscriptions WHERE user_id = ? AND service_id = ? AND status = 'active'");
            $stmt->bind_param("ii", $user_id, $service_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->fetch_row()[0] > 0) {
                throw new Exception("Már van aktív előfizetésed erre a szolgáltatásra!");
            }

            // Új előfizetés beszúrása
            $stmt = $conn->prepare("INSERT INTO subscriptions (user_id, service_id, start_date, status) VALUES (?, ?, NOW(), 'active')");
            if (!$stmt) {
                throw new Exception("Előkészítési hiba (subscriptions): " . $conn->error);
            }
            $stmt->bind_param("ii", $user_id, $service_id);
            if (!$stmt->execute()) {
                throw new Exception("Végrehajtási hiba (subscriptions): " . $stmt->error);
            }
            $subscription_id = $conn->insert_id;

            // Fizetési adatok beszúrása
            if ($payment_method === 'local') {
                $name = filter_var($data['name'] ?? '', FILTER_SANITIZE_STRING);
                $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
                if (!$name || !$email) {
                    throw new Exception("Név és email szükséges a helybeli fizetéshez!");
                }
                $stmt = $conn->prepare("INSERT INTO payment_details (user_id, subscription_id, payment_method, name, email, price) VALUES (?, ?, 'local', ?, ?, ?)");
                if (!$stmt) {
                    throw new Exception("Előkészítési hiba (payment_details): " . $conn->error);
                }
                $stmt->bind_param("iissd", $user_id, $subscription_id, $name, $email, $price);
            } 
            if (!$stmt->execute()) {
                throw new Exception("Végrehajtási hiba (payment_details): " . $stmt->error);
            }

            $conn->commit();
            echo json_encode(['message' => 'Sikeresen előfizettél!', 'redirect' => 'profile.php']);
        } catch (Exception $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['error' => 'Hiba történt: ' . $e->getMessage(), 'data' => $data]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus!']);
        break;
}

$conn->close();
?>