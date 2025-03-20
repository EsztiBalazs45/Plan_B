<?php
session_start();
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); 

require_once '../includes/config.php'; 

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nincs bejelentkezve']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asd"; 

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $userRole = $stmt->fetchColumn();

    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(id) as ids FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $totalClients = $result['count'];

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE DATE(start) = CURDATE() AND user_id = ?");
    $stmt->execute([$user_id]);
    $todayAppointments = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'pending' AND user_id = ?");
    $stmt->execute([$user_id]);
    $pendingAppointments = $stmt->fetchColumn();

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE MONTH(start) = MONTH(CURDATE()) AND YEAR(start) = YEAR(CURDATE()) AND user_id = ?");
    $stmt->execute([$user_id]);
    $monthAppointments = $stmt->fetchColumn();

    http_response_code(200); 
    echo json_encode([
        'total_clients' => $totalClients,
        'today_appointments' => $todayAppointments,
        'pending_appointments' => $pendingAppointments,
        'month_appointments' => $monthAppointments,
        'user_name' => $_SESSION['user_name'],
        'role' => $userRole,
    ]);
} catch (PDOException $e) {
    error_log("Error getting statistics: " . $e->getMessage());
    http_response_code(500); 
    echo json_encode(['error' => 'Adatbázis hiba: ' . $e->getMessage()]);
}

exit;
?>