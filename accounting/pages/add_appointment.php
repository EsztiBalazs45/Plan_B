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
        
        // Combine date and time
        $appointment_date = date('Y-m-d H:i:s', strtotime("$date $time"));
        
        $stmt = $conn->prepare("
            INSERT INTO appointments 
            (user_id, client_id, appointment_date, description, status, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        if ($stmt->execute([$_SESSION['user_id'], $client_id, $appointment_date, $description, $status , $title])) {
            echo json_encode(['success' => true, 'message' => 'Időpont sikeresen létrehozva!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Hiba történt az időpont létrehozása során.']);
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
