<?php
// Koneksi ke database
$conn = new mysqli('localhost', 'root', '', 'logbook1');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek apakah form telah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $jabatan = isset($_POST['jabatan']) ? $_POST['jabatan'] : '';
    $nip = isset($_POST['nip']) ? $_POST['nip'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    
    // Variabel untuk menyimpan URL foto
    $foto_url = null;

    // Cek apakah ada file foto yang diunggah
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileSize = $_FILES['photo']['size'];
        $fileType = $_FILES['photo']['type'];
        
        // Tentukan ekstensi file yang diizinkan
        $allowedfileExtensions = array('.jpg', '.gif', '.png', '.jpeg');
        $fileExtension = strrchr($fileName, '.');

        // Validasi ekstensi file
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Tentukan direktori untuk menyimpan foto
            $uploadFileDir = './uploads/';
            $dest_path = $uploadFileDir . $fileName;

            // Pindahkan file ke direktori tujuan
            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $foto_url = $dest_path; // Simpan URL foto
            } else {
                echo "Error moving the uploaded file.";
                exit();
            }
        } else {
            echo "Invalid file extension.";
            exit();
        }
    }

    // Query untuk memperbarui atau memasukkan data pengguna
    if ($id > 0) {
        // Update data jika ID ada
        if ($foto_url) {
            // Jika ada foto, ikat 6 parameter
            $sql = "UPDATE profile_details SET nama = ?, jabatan = ?, nip = ?, no_hp = ?, foto_url = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $name, $jabatan, $nip, $phone, $foto_url, $id);
        } else {
            // Jika tidak ada foto, ikat 5 parameter
            $sql = "UPDATE profile_details SET nama = ?, jabatan = ?, nip = ?, no_hp = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $name, $jabatan, $nip, $phone, $id);
        }
    } else {
        // Insert data jika ID tidak ada
        $sql = "INSERT INTO profile_details (nama, jabatan, nip, no_hp, foto_url) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $jabatan, $nip, $phone, $foto_url);
    }

    if ($stmt->execute()) {
        // Jika berhasil, arahkan ke detail_profil.php
        header("Location: detail_profile.php?id=" . $id);
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>