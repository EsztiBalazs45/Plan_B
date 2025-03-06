<?php
require_once '../includes/config.php';

try {
    // Read and execute the SQL files
    $sql_files = [
        'create_users_table.sql',
        'create_clients_table.sql',
        'create_appointments_table.sql',
        'create_services_table.sql',
        'create_subscriptions_table.sql',
        'create_newsletters_table.sql',
    ];
    
    foreach ($sql_files as $file) {
        $sql = file_get_contents(__DIR__ . '/' . $file);
        $conn->exec($sql);
        echo "Table created successfully from $file<br>";
    }
    
    echo "<br>All tables created successfully!";
} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>
