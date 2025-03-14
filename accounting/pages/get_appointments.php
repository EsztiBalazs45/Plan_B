<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.id,
            a.description,
            a.appointment_date,
            a.status,
            a.client_id,
            c.CompanyName as title
        FROM appointments a
        JOIN clients c ON a.client_id = c.id
        WHERE a.user_id = ?
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $events = array_map(function($appointment) {
        $statusColors = [
            'pending' => '#ffc107',
            'confirmed' => '#28a745',
            'canceled' => '#dc3545'
        ];
        
        return [
            'id' => $appointment['id'],
            'title' => $appointment['title'],
            'start' => $appointment['appointment_date'],
            'description' => $appointment['description'],
            'client_id' => $appointment['client_id'],
            'status' => $appointment['status'],
            'backgroundColor' => $statusColors[$appointment['status']] ?? '#6c757d',
            'borderColor' => $statusColors[$appointment['status']] ?? '#6c757d',
            'className' => 'status-' . $appointment['status']
        ];
    }, $appointments);
    
    header('Content-Type: application/json');
    echo json_encode($events);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
