<?php
require_once '../includes/config.php'; // Tartalmazza a konfigurációt

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asd";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT * FROM dowloaddata");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    header('Content-Type: application/json');
    echo json_encode($documents);
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Kapcsolódási hiba: ' . $e->getMessage()]);
}
?>