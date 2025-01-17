<?php
// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1"; // Ganti dengan nama database Anda

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Mendapatkan data dari form
$name = $_POST['name'];
$jabatan = $_POST['jabatan']; // Diubah dari level ke jabatan
$nip = $_POST['nip'];
$no_hp = $_POST['no_hp'];

// Mengelola file gambar
$foto_url = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $targetDir = "uploads/"; // Folder penyimpanan gambar
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    $fileName = basename($_FILES['foto']['name']);
    $targetFilePath = $targetDir . uniqid() . "_" . $fileName;
    
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetFilePath)) {
        $foto_url = $targetFilePath;
    }
}

// Query untuk memasukkan data ke database
$sql = "INSERT INTO profile_details (nama, jabatan, nip, no_hp, foto_url) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssss", $name, $jabatan, $nip, $no_hp, $foto_url);

if ($stmt->execute()) {
    echo "Profil berhasil ditambahkan.";
} else {
    echo "Terjadi kesalahan: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
