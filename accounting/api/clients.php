<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Nem vagy bejelentkezve']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

// Hibakeresési logolás (ellenőrizd a logs/error.log fájlt)
error_log("Kérés: $method, user_id: $user_id");

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['id'], $user_id]);
            $client = $stmt->fetch();
            if ($client) {
                http_response_code(200);
                echo json_encode($client);
            } else {
                http_response_code(404);
                echo json_encode(['error' => 'Ügyfél nem található']);
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM clients WHERE user_id = ? ORDER BY CompanyName");
            $stmt->execute([$user_id]);
            $clients = $stmt->fetchAll();
            http_response_code(200);
            echo json_encode($clients);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("POST adat: " . print_r($data, true)); // Hibakeresés
        $company_name = sanitize($data['company_name'] ?? '');
        $tax_number = sanitize($data['tax_number'] ?? '');
        $reg_number = sanitize($data['registration_number'] ?? '');
        $headquarters = sanitize($data['headquarters'] ?? '');
        $contact_person = sanitize($data['contact_person'] ?? '');
        $contact_number = sanitize($data['contact_number'] ?? '');

        if ($company_name && $tax_number && $reg_number && $headquarters && $contact_person && $contact_number) {
            $stmt = $conn->prepare("INSERT INTO clients (user_id, CompanyName, tax_number, registration_number, headquarters, contact_person, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $success = $stmt->execute([$user_id, $company_name, $tax_number, $reg_number, $headquarters, $contact_person, $contact_number]);
            if ($success) {
                http_response_code(201);
                echo json_encode(['message' => 'Ügyfél sikeresen hozzáadva']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Hiba az ügyfél hozzáadása közben: ' . $stmt->errorInfo()[2]]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen adatok', 'data' => $data]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        error_log("PUT adat: " . print_r($data, true)); // Hibakeresés
        $client_id = $data['client_id'] ?? '';
        $company_name = sanitize($data['company_name'] ?? '');
        $tax_number = sanitize($data['tax_number'] ?? '');
        $reg_number = sanitize($data['registration_number'] ?? '');
        $headquarters = sanitize($data['headquarters'] ?? '');
        $contact_person = sanitize($data['contact_person'] ?? '');
        $contact_number = sanitize($data['contact_number'] ?? '');

        if ($client_id && $company_name && $tax_number && $reg_number && $headquarters && $contact_person && $contact_number) {
            $stmt = $conn->prepare("UPDATE clients SET CompanyName = ?, tax_number = ?, registration_number = ?, headquarters = ?, contact_person = ?, contact_number = ? WHERE id = ? AND user_id = ?");
            $success = $stmt->execute([$company_name, $tax_number, $reg_number, $headquarters, $contact_person, $contact_number, $client_id, $user_id]);
            if ($success) {
                http_response_code(200);
                echo json_encode(['message' => 'Ügyfél adatai sikeresen frissítve']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Hiba az ügyfél frissítése közben: ' . $stmt->errorInfo()[2]]);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen adatok', 'data' => $data]);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $client_id = $data['client_id'] ?? '';
        if ($client_id) {
            $stmt = $conn->prepare("DELETE FROM clients WHERE id = ? AND user_id = ?");
            if ($stmt->execute([$client_id, $user_id])) {
                http_response_code(200);
                echo json_encode(['message' => 'Ügyfél sikeresen törölve']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Hiba az ügyfél törlése közben']);
            }
        } else {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó ügyfél azonosító']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus']);
        break;
}
?>