<?php
require_once '../includes/config.php';

if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['id'])) {
            $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ? AND user_id = ?");
            
            if ($stmt->execute([$data['id'], $_SESSION['user_id']])) {
                echo json_encode(['success' => true, 'message' => 'Időpont sikeresen törölve']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Hiba történt az időpont törlése során']);
            }
        }
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>