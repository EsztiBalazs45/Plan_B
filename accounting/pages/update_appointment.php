<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get JSON data
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            // If it's a drag-and-drop update
            if (isset($data['date']) && isset($data['time'])) {
                $appointment_date = date('Y-m-d H:i:s', strtotime("{$data['date']} {$data['time']}"));
                $stmt = $conn->prepare("
                    UPDATE appointments 
                    SET appointment_date = ?
                    WHERE id = ? AND user_id = ?
                ");
                $success = $stmt->execute([$appointment_date, $data['id'], $_SESSION['user_id']]);
            } else {
                // If it's a form update
                $appointment_date = date('Y-m-d H:i:s', strtotime("{$_POST['date']} {$_POST['time']}"));
                $stmt = $conn->prepare("
                    UPDATE appointments 
                    SET client_id = ?, 
                        appointment_date = ?, 
                        description = ?, 
                        status = ?
                    WHERE id = ? AND user_id = ?
                ");
                $success = $stmt->execute([
                    $_POST['client_id'],
                    $appointment_date,
                    $_POST['description'],
                    $_POST['status'],
                    $_POST['appointmentId'],
                    $_SESSION['user_id']
                ]);
            }
            
            if ($success) {
                echo json_encode(['success' => true, 'message' => 'Időpont sikeresen frissítve!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Hiba történt az időpont frissítése során.']);
            }
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
