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
    echo json_encode(['success' => false, 'message' => 'Nem módosíthatsz múltbeli időpontot']);
    exit();
}

// Ütközés ellenőrzése (kivéve az aktuális eseményt)
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM appointments 
    WHERE user_id = ? 
    AND id != ? 
    AND status != 'canceled'
    AND (
        (start <= ? AND end > ?) OR 
        (start < ? AND end >= ?) OR 
        (start >= ? AND end <= ?)
    )
");
$stmt->execute([
    $_SESSION['user_id'],
    $data['id'],
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

    echo json_encode(['success' => true, 'message' => 'Időpont sikeresen frissítve']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>