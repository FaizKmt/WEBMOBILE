<?php
session_start();

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    echo "Anda belum login.";
    exit();
}

// Ambil ID pengguna yang sedang login dari session
$user_id = $_SESSION['user_id'];
$created_by = $_SESSION['user_id'];

// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $petugas = $_POST['petugas'];
    $tanggal = $_POST['tanggal'];
    $melaksanakan_tugas = $_POST['melaksanakan_tugas'];
    $output = $_POST['output'];
    $tugas_lainnya = $_POST['tugas_lainnya'];
    $catatan = $_POST['catatan'];
    
    // Tangani status dari form
    $status = isset($_POST['status']) ? $_POST['status'] : 'belum_selesai';
    
    // Pastikan nilai status sesuai dengan yang diharapkan
    switch ($status) {
        case 'sudahselesai':
            $status = 'sudahselesai';
            break;
        case 'belum_selesai':
            $status = 'belum_selesai';
            break;
        case 'konsep':
            $status = 'konsep';
            break;
        default:
            $status = 'belum_selesai';
    }

    // Handle file uploads
    $data_dukung_1 = '';
    if(isset($_FILES['data_dukung_1']) && $_FILES['data_dukung_1']['error'] == 0) {
        $target_dir = "uploads/";
        $data_dukung_1 = $target_dir . basename($_FILES["data_dukung_1"]["name"]);
        move_uploaded_file($_FILES["data_dukung_1"]["tmp_name"], $data_dukung_1);
    }

    $data_dukung_2 = '';
    if(isset($_FILES['data_dukung_2']) && $_FILES['data_dukung_2']['error'] == 0) {
        $target_dir = "uploads/";
        $data_dukung_2 = $target_dir . basename($_FILES["data_dukung_2"]["name"]);
        move_uploaded_file($_FILES["data_dukung_2"]["tmp_name"], $data_dukung_2);
    }

    // Debug: Print status sebelum disimpan
    error_log("Status yang akan disimpan: " . $status);

    // Prepare and execute SQL query
    $sql = "INSERT INTO tata_usaha (user_id, created_by, petugas, tanggal, melaksanakan_tugas, output, tugas_lainnya, catatan, status, data_dukung_1, data_dukung_2) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssssss", 
        $user_id,
        $created_by,
        $petugas,
        $tanggal,
        $melaksanakan_tugas,
        $output,
        $tugas_lainnya,
        $catatan,
        $status,
        $data_dukung_1,
        $data_dukung_2
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data berhasil disimpan!";
        header("Location: tata_usaha.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: form_tata_usaha.php");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>