<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isLoggedIn() || !isAdmin()) {
    http_response_code(401);
    echo json_encode(['error' => 'Nem vagy bejelentkezve vagy nincs admin jogosultságod']);
    exit();
}

if (!isset($conn)) {
    http_response_code(500);
    echo json_encode(['error' => 'Adatbázis kapcsolat hiányzik']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $tab = $_GET['tab'] ?? 'users';
        $per_page = 15;
        $user_page = isset($_GET['user_page']) ? (int)$_GET['user_page'] : 1;
        $appointment_page = isset($_GET['appointment_page']) ? (int)$_GET['appointment_page'] : 1;
        $user_offset = ($user_page - 1) * $per_page;
        $appointment_offset = ($appointment_page - 1) * $per_page;
        $sort_by_name = isset($_GET['sort']) && $_GET['sort'] === 'name_asc' ? 'ASC' : 'DESC';

        if ($tab === 'stats') {
            $stats = $conn->query("SELECT (SELECT COUNT(*) FROM users) AS total_users, 
                                   (SELECT COUNT(*) FROM clients) AS total_clients, 
                                   (SELECT COUNT(*) FROM appointments) AS total_appointments,
                                   (SELECT COUNT(*) FROM subscriptions WHERE status = 'active') AS total_subscriptions")
                          ->fetch(PDO::FETCH_ASSOC);
            echo json_encode($stats);
        } elseif ($tab === 'users') {
            $stmt = $conn->prepare("SELECT * FROM users ORDER BY name $sort_by_name LIMIT :offset, :per_page");
            $stmt->bindValue(':offset', $user_offset, PDO::PARAM_INT);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
            echo json_encode(['users' => $users, 'total_pages' => ceil($total_users / $per_page)]);
        } elseif ($tab === 'clients') {
            $clients = $conn->query("SELECT c.*, u.name AS user_name FROM clients c LEFT JOIN users u ON c.user_id = u.id")
                            ->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($clients);
        } elseif ($tab === 'appointments') {
            $stmt = $conn->prepare("SELECT a.*, u.name AS user_name, c.CompanyName FROM appointments a 
                                    LEFT JOIN users u ON a.user_id = u.id 
                                    LEFT JOIN clients c ON a.client_id = c.id 
                                    ORDER BY a.start DESC LIMIT :offset, :per_page");
            $stmt->bindValue(':offset', $appointment_offset, PDO::PARAM_INT);
            $stmt->bindValue(':per_page', $per_page, PDO::PARAM_INT);
            $stmt->execute();
            $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $total_appointments = $conn->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
            echo json_encode(['appointments' => $appointments, 'total_pages' => ceil($total_appointments / $per_page)]);
        } elseif ($tab === 'subscriptions') {
            $subscriptions = $conn->query("SELECT s.*, c.CompanyName AS client_name, u.name AS user_name, serv.service_name 
                                           FROM subscriptions s 
                                           LEFT JOIN clients c ON s.user_id = c.user_id 
                                           LEFT JOIN users u ON c.user_id = u.id 
                                           LEFT JOIN services serv ON s.service_id = serv.id 
                                           ORDER BY s.start_date DESC")
                                 ->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($subscriptions);
        } elseif ($tab === 'calendar') {
            $calendar_events = $conn->query("SELECT a.id, a.start, a.end, u.name AS user_name 
                                             FROM appointments a 
                                             LEFT JOIN users u ON a.user_id = u.id 
                                             WHERE a.status != 'canceled'")
                                    ->fetchAll(PDO::FETCH_ASSOC);
            $formatted_events = array_map(function ($event) {
                return [
                    'id' => $event['id'],
                    'title' => $event['user_name'] ?? 'Nincs hozzárendelve',
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'extendedProps' => ['user_name' => $event['user_name'] ?? 'Nincs hozzárendelve']
                ];
            }, $calendar_events);
            echo json_encode($formatted_events);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (isset($data['delete_user'])) {
            $user_id = $data['user_id'];
            if ($user_id && $user_id != $_SESSION['user_id']) {
                $conn->beginTransaction();
                $conn->prepare("DELETE FROM appointments WHERE user_id = ?")->execute([$user_id]);
                $conn->prepare("DELETE FROM clients WHERE user_id = ?")->execute([$user_id]);
                $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
                $conn->commit();
                echo json_encode(['message' => 'Felhasználó sikeresen törölve']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Saját felhasználót nem törölhetsz']);
            }
        } elseif (isset($data['update_user_role'])) {
            $user_id = $data['user_id'];
            $new_role = $data['new_role'];
            $allowed_roles = ['user', 'admin'];
            if ($user_id && in_array($new_role, $allowed_roles)) {
                if ($user_id == $_SESSION['user_id'] && $new_role !== 'admin') {
                    http_response_code(403);
                    echo json_encode(['error' => 'Nem csökkentheted saját admin jogosultságodat']);
                } else {
                    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
                    $stmt->execute([$new_role, $user_id]);
                    echo json_encode(['message' => 'Szerepkör sikeresen módosítva']);
                }
            }
        } elseif (isset($data['delete_client'])) {
            $client_id = $data['client_id'];
            $stmt = $conn->prepare("DELETE FROM clients WHERE id = ?");
            $stmt->execute([$client_id]);
            echo json_encode(['message' => 'Ügyfél törölve']);
        } elseif (isset($data['edit_client'])) {
            $client_id = $data['client_id'];
            $name = $data['name'];
            $stmt = $conn->prepare("UPDATE clients SET CompanyName = ? WHERE id = ?");
            $stmt->execute([$name, $client_id]);
            echo json_encode(['message' => 'Ügyfél frissítve']);
        } elseif (isset($data['update_appointment_status'])) {
            $appointment_id = $data['appointment_id'];
            $new_status = $data['new_status'];
            $allowed_statuses = ['confirmed', 'canceled'];
            if (in_array($new_status, $allowed_statuses)) {
                $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
                $stmt->execute([$new_status, $appointment_id]);
                echo json_encode(['message' => "Időpont $new_status státuszra frissítve"]);
            }
        } elseif (isset($data['delete_appointment'])) {
            $appointment_id = $data['appointment_id'];
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
            $stmt->execute([$appointment_id]);
            echo json_encode(['message' => 'Időpont törölve']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Nem támogatott metódus']);
        break;
}
?>