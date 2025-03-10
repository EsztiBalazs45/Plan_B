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

// Ütközés ellenőrzése
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM appointments 
    WHERE user_id = ? 
    AND status != 'canceled'
    AND (
        (start <= ? AND end > ?) OR 
        (start < ? AND end >= ?) OR 
        (start >= ? AND end <= ?)
    )
");
$stmt->execute([
    $_SESSION['user_id'],
    $data['start'], $data['start'],
    $data['end'], $data['end'],
    $data['start'], $data['end']
]);
if ($stmt->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Ez az idősáv már foglalt!']);
    exit();
}

try {
    $stmt = $conn->prepare("
        INSERT INTO appointments (user_id, client_id, title, start, end, description, status)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $_SESSION['user_id'],
        $data['clientId'],
        $data['title'],
        $data['start'],
        $data['end'],
        $data['description'] ?? '',
        $data['status'] ?? 'pending'
    ]);
    $newId = $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Időpont sikeresen létrehozva',
        'event' => [
            'id' => $newId,
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