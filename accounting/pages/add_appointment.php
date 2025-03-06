<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $client_id = $_POST['client_id'];
        $date = $_POST['date'];
        $time = $_POST['time'];
        $description = $_POST['description'];
        $status = $_POST['status'];
        $title = $_POST['title'];
        
        $start = date('Y-m-d H:i:s', strtotime("$date $time"));
        $end = date('Y-m-d H:i:s', strtotime("$date $time") + 3600); // Például 1 óra hozzáadása
        
        $stmt = $conn->prepare("
            INSERT INTO appointments 
            (user_id, client_id, title, start, end, description, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $client_id, $title, $start, $end, $description, $status])) {
            $newId = $conn->lastInsertId();
            echo json_encode([
                'success' => true,
                'message' => 'Időpont sikeresen létrehozva',
                'event' => [
                    'id' => $newId,
                    'title' => $title,
                    'start' => $start,
                    'end' => $end,
                    'client_id' => $client_id,
                    'description' => $description,
                    'status' => $status
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt az időpont létrehozása során']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>