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
            echo json_encode(getAvailableSlots($conn, $date));
        } elseif (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
            $stmt->execute([$_GET['id'], $user_id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
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
        $title = $data['title'] ?? '';
        $start = $data['start'] ?? '';
        $end = $data['end'] ?? '';
        $client_id = $data['client_id'] ?? '';
        $description = $data['description'] ?? '';
        $status = $data['status'] ?? 'pending';

        $stmt = $conn->prepare("
            SELECT COUNT(*) 
            FROM appointments 
            WHERE status != 'canceled'
            AND (
                (start <= ? AND end > ?) OR 
                (start < ? AND end >= ?) OR 
                (start >= ? AND end <= ?)
            )
        ");
        $stmt->execute([$start, $start, $end, $end, $start, $end]);
        if ($stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(['error' => 'Időpont ütközés!', 'available_slots' => getAvailableSlots($conn, date('Y-m-d', strtotime($start)))]);
            exit();
        }

        $stmt = $conn->prepare("
            INSERT INTO appointments (user_id, client_id, title, start, end, description, status)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $client_id, $title, $start, $end, $description, $status]);
        echo json_encode(['message' => 'Időpont sikeresen létrehozva!', 'id' => $conn->lastInsertId()]);
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        $appointment_id = $data['id'] ?? 0;
        $title = $data['title'] ?? '';
        $start = $data['start'] ?? '';
        $end = $data['end'] ?? '';
        $client_id = $data['client_id'] ?? '';
        $description = $data['description'] ?? '';
        $status = $data['status'] ?? '';

        $stmt = $conn->prepare("SELECT status FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        $current_status = $stmt->fetchColumn();

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
        $appointment_id = $data['id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
        $stmt->execute([$appointment_id, $user_id]);
        echo json_encode(['message' => 'Időpont sikeresen törölve!']);
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