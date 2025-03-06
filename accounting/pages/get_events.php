<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT a.id, a.title, a.start, a.end, a.description, a.status, a.client_id, c.CompanyName
        FROM appointments a
        LEFT JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $events = array_map(function($appointment) {
        return [
            'id' => $appointment['id'],
            'title' => $appointment['title'],
            'start' => $appointment['start'],
            'end' => $appointment['end'],
            'description' => $appointment['description'],
            'status' => $appointment['status'],
            'extendedProps' => [
                'client_id' => $appointment['client_id'],
                'description' => $appointment['description'],
                'status' => $appointment['status']
            ]
        ];
    }, $appointments);

    header('Content-Type: application/json');
    echo json_encode($events);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>