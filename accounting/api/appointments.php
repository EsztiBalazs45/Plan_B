<?php
header('Content-Type: application/json');
require_once '../includes/config.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Nem vagy bejelentkezve!']);
    exit();
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['action']) && $_GET['action'] === 'slots') {
            $date = $_GET['date'] ?? date('Y-m-d');
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen dátum formátum!']);
                exit();
            }
            echo json_encode(getAvailableSlots($conn, $date));
        } elseif (isset($_GET['id'])) {
            $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
            if ($id === false || $id <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Érvénytelen ID!']);
                exit();
            }
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
            $stmt->execute([$id, $user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result === false) {
                http_response_code(404);
                echo json_encode(['error' => 'Időpont nem található!']);
            } else {
                echo json_encode($result);
            }
        } else {
            $stmt = $conn->prepare("
                SELECT a.*, c.CompanyName 
                FROM appointments a 
                LEFT JOIN clients c ON a.client_id = c.id 
                WHERE a.user_id = ? AND a.status != 'canceled'
                ORDER BY a.start DESC
            ");
            $stmt->execute([$user_id]);
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen JSON adat!']);
            exit();
        }

        $title = trim($data['title'] ?? '');
        $start = $data['start'] ?? '';
        $end = $data['end'] ?? '';
        $client_id = filter_var($data['client_id'] ?? '', FILTER_VALIDATE_INT);
        $description = trim($data['description'] ?? '');
        $status = $data['status'] ?? 'pending';

        // Validáció
        if (empty($title) || empty($start) || empty($end) || !$client_id || !in_array($status, ['pending', 'confirmed', 'canceled'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen adatok!', 'received' => $data]);
            exit();
        }
        if (!DateTime::createFromFormat('Y-m-d H:i:s', $start) || !DateTime::createFromFormat('Y-m-d H:i:s', $end)) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen dátum formátum!']);
            exit();
        }
        if (strtotime($end) <= strtotime($start)) {
            http_response_code(400);
            echo json_encode(['error' => 'A befejezési időnek később kell lennie, mint a kezdési idő!']);
            exit();
        }

        // Ütközés ellenőrzés
        $stmt = $conn->prepare("
            SELECT id, start, end 
            FROM appointments 
            WHERE status != 'canceled'
            AND user_id = ?
            AND (
                (start <= ? AND end > ?) OR 
                (start < ? AND end >= ?) OR 
                (start >= ? AND end <= ?)
            )
        ");
        $stmt->execute([$user_id, $start, $start, $end, $end, $start, $end]);
        $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($conflicts) > 0) {
            http_response_code(404); // Kérésed szerint 404-re módosítva
            echo json_encode([
                'error' => 'Időpont ütközés!',
                'available_slots' => getAvailableSlots($conn, date('Y-m-d', strtotime($start))),
                'conflicting_appointments' => $conflicts
            ]);
            exit();
        }

        // Időpont mentése
        $stmt = $conn->prepare("
            INSERT INTO appointments (user_id, client_id, title, start, end, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $success = $stmt->execute([$user_id, $client_id, $title, $start, $end, $description, $status]);
        if ($success) {
            http_response_code(201);
            echo json_encode(['message' => 'Időpont sikeresen létrehozva!', 'id' => $conn->lastInsertId()]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Hiba történt az időpont mentésekor!']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data) || !isset($data['id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen ID!']);
            exit();
        }

        $appointment_id = filter_var($data['id'], FILTER_VALIDATE_INT);
        $title = trim($data['title'] ?? '');
        $start = $data['start'] ?? '';
        $end = $data['end'] ?? '';
        $client_id = filter_var($data['client_id'] ?? '', FILTER_VALIDATE_INT);
        $description = trim($data['description'] ?? '');
        $status = $data['status'] ?? '';

        if ($appointment_id === false || $appointment_id <= 0 || empty($title) || empty($start) || empty($end) || !$client_id || !in_array($status, ['pending', 'confirmed', 'canceled'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Hiányzó vagy érvénytelen adatok!', 'received' => $data]);
            exit();
        }

        $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        $current_status = $stmt->fetchColumn();

        if ($current_status === false) {
            http_response_code(404);
            echo json_encode(['error' => 'Időpont nem található!']);
            exit();
        }

        if ($current_status === 'confirmed' && $status !== 'canceled') {
            http_response_code(403);
            echo json_encode(['error' => 'Megerősített időpontot csak lemondani lehet!']);
            exit();
        }

        if ($status === 'canceled') {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
            $stmt->execute([$appointment_id, $user_id]);
            echo json_encode(['message' => 'Időpont sikeresen lemondva és törölve!']);
        } else {
            // Ütközés ellenőrzés frissítés esetén is
            $stmt = $conn->prepare("
                SELECT id, start, end 
                FROM appointments 
                WHERE status != 'canceled'
                AND user_id = ?
                AND id != ?
                AND (
                    (start <= ? AND end > ?) OR 
                    (start < ? AND end >= ?) OR 
                    (start >= ? AND end <= ?)
                )
            ");
            $stmt->execute([$user_id, $appointment_id, $start, $start, $end, $end, $start, $end]);
            $conflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($conflicts) > 0) {
                http_response_code(404); // Kérésed szerint 404
                echo json_encode([
                    'error' => 'Időpont ütközés!',
                    'available_slots' => getAvailableSlots($conn, date('Y-m-d', strtotime($start))),
                    'conflicting_appointments' => $conflicts
                ]);
                exit();
            }

            $stmt = $conn->prepare("
                UPDATE appointments
                SET title = ?, client_id = ?, start = ?, end = ?, description = ?, status = ?
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$title, $client_id, $start, $end, $description, $status, $appointment_id, $user_id]);
            echo json_encode(['message' => 'Időpont sikeresen frissítve!']);
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true);
        $appointment_id = filter_var($data['id'] ?? 0, FILTER_VALIDATE_INT);
        if ($appointment_id === false || $appointment_id <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'Érvénytelen ID!']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['message' => 'Időpont sikeresen törölve!']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Időpont nem található!']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus!']);
        break;
}

function getAvailableSlots($conn, $date) {
    $start_hour = 7;
    $end_hour = 16;
    $interval = 30;
    $slots = [];
    $date_start = new DateTime("$date $start_hour:00");
    $date_end = new DateTime("$date $end_hour:00");

    $stmt = $conn->prepare("
        SELECT start, end 
        FROM appointments 
        WHERE DATE(start) = ? AND status != 'canceled'
    ");
    $stmt->execute([$date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    while ($date_start <= $date_end) {
        $slot_start = $date_start->format('Y-m-d H:i:s');
        $date_start->modify("+{$interval} minutes");
        $slot_end = $date_start->format('Y-m-d H:i:s');
        $is_booked = false;

        foreach ($booked_slots as $booked) {
            if (($slot_start < $booked['end'] && $slot_end > $booked['start'])) {
                $is_booked = true;
                break;
            }
        }

        if (!$is_booked) {
            $slots[] = ['start' => $slot_start, 'end' => $slot_end];
        }
    }
    return $slots;
}
?>