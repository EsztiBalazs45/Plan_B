<?php
$servername = "localhost";
$username = "root";  // Állítsd be az adatbázis felhasználót
$password = "";
$dbname = "asd";  // Adatbázis neve

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kapcsolódási hiba: " . $conn->connect_error);
}

$sql = "SELECT service_name, service_description, service_price, service_id FROM services";
$result = $conn->query($sql);

$csomagok = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $csomagok[] = [
            "nev" => $row["service_name"],
            "ar" => $row["service_price"],
            "leiras" => $row["service_description"],
            "id" => $row["service_id"]
        ];
    }
}

$conn->close();
?>
