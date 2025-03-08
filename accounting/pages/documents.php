<?php
require_once '../includes/header.php'; // Tartalmazza a meglévő fejlécet
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
} catch(PDOException $e) {
    echo "Kapcsolódási hiba: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Könyvelő Iroda - Dokumentumok</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 40px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .download-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .download-btn:hover {
            background-color: #218838;
        }
        p {
            text-align: center;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Letölthető Dokumentumok</h1>
        
        <?php if (count($documents) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Cím</th>
                        <th>Leírás</th>
                        <th>Letöltés</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($documents as $doc): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['title']); ?></td>
                            <td><?php echo htmlspecialchars($doc['description']); ?></td>
                            <td><a href="download.php?file=<?php echo urlencode($doc['DataFile']); ?>" class="download-btn" download>Töltse le</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Jelenleg nincsenek letölthető dokumentumok.</p>
        <?php endif; ?>
    </div>
</body>
</html>