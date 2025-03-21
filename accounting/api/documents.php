<?php
require_once '../includes/config.php';

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asd";

header('Content-Type: application/json');

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM dowloaddata");
    $stmt->execute();
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    http_response_code(200);
    echo json_encode($documents);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Kapcsolódási hiba: ' . $e->getMessage()]);
}
?>