<?php
$host = 'localhost';
$dbname = 'logbook1';
$username = 'root'; // ganti dengan username database Anda
$password = ''; // ganti dengan password database Anda

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>