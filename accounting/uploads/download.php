<?php
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = "uploads/" . $filename;

    if (file_exists($filepath)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    } else {
        die("A fájl nem található!");
    }
} else {
    die("Nincs fájl megadva!");
}
?>