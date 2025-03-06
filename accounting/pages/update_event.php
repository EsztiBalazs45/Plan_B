<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$now = date('Y-m-d H:i:s');

if ($data['start'] < $now) {
    echo json_encode(['success' => false, 'message' => 'Nem foglalhatsz múltbeli időpontot']);
    exit();
}

try {
    $stmt = $conn->prepare("
        UPDATE appointments
        SET title = ?, client_id = ?, start = ?, end = ?, description = ?, status = ?
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([
        $data['title'],
        $data['clientId'],
        $data['start'],
        $data['end'],
        $data['description'] ?? '',
        $data['status'] ?? 'pending',
        $data['id'],
        $_SESSION['user_id']
    ]);
    echo json_encode([
        'success' => true,
        'message' => 'Időpont sikeresen frissítve',
        'event' => [
            'id' => $data['id'],
            'title' => $data['title'],
            'start' => $data['start'],
            'end' => $data['end'],
            'client_id' => $data['clientId'],
            'description' => $data['description'] ?? '',
            'status' => $data['status'] ?? 'pending'
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>