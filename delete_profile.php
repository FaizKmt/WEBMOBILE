<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1"; 

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID profil dari form
$id = $_POST['id'];

// Hapus profil dari database
$sql = "DELETE FROM profile_details WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo "Profil berhasil dihapus.";
} else {
    echo "Terjadi kesalahan: " . $conn->error;
}

$stmt->close();
$conn->close();

// Redirect kembali ke halaman profil
header("Location: profil.php");
exit();
?> 