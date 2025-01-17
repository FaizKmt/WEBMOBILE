<?php
session_start();
if (!isset($_SESSION['user_id'])) {
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

$userRole = $_SESSION['role']; // Mendapatkan role dari sesi

// Ambil data profil dari database
$sql = "SELECT * FROM profile_details";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f7ff;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 30px;
            background-color: #166ed5;
            border-bottom: 1px solid #1e88e5;
        }
        .header-left {
            display: flex;
            align-items: center;
        }
        .header img {
            height: 48px;
            width: 48px;
            margin-right: 10px;
            border-radius: 8px;
            background: white;
            padding: 4px;
            transition: transform 0.3s;
        }
        .header img:hover {
            transform: scale(1.1);
        }
        .header h1 {
            font-size: 20px;
            margin: 0;
            color: #ffffff;
        }
        .home-button {
            background-color: #1e88e5;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .home-button:hover {
            background-color: #1565c0;
            transform: scale(1.1);
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background-color: #ffffff;
            border: 1px solid #166ed5;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(22, 110, 213, 0.1);
            margin: 10px;
            padding: 20px;
            width: 200px;
            text-align: center;
            transition: transform 0.2s;
            color: inherit;
            cursor: pointer;
            text-decoration: none;
            position: relative;
        }
        .card:hover {
            transform: scale(1.05);
            box-shadow: 0 6px 12px rgba(22, 110, 213, 0.15);
        }
        .card img {
            border-radius: 50%;
            height: 80px;
            width: 80px;
            margin-bottom: 15px;
            object-fit: cover;
        }
        .fab-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #1e88e5;
            color: #ffffff;
            border: none;
            border-radius: 50%;
            width: 56px;
            height: 56px;
            font-size: 24px;
            box-shadow: 0 2px 4px rgba(22, 110, 213, 0.2);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .fab-button:hover {
            transform: scale(1.1);
            background-color: #1565c0;
        }
        #modalForm {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.7);
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(22, 110, 213, 0.2);
            border-radius: 15px;
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s ease;
            max-width: 500px;
            width: 90%;
        }
        #modalForm.active {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
        #modalForm h3 {
            color: #166ed5;
            text-align: center;
            margin-bottom: 25px;
            font-size: 24px;
        }
        #modalForm form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        #modalForm label {
            font-weight: 500;
            color: #333;
            margin-bottom: 5px;
        }
        #modalForm input, #modalForm select {
            padding: 12px;
            border: 2px solid #166ed5;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        #modalForm input:focus, #modalForm select:focus {
            border-color: #1565c0;
            outline: none;
        }
        #modalForm button {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        #modalForm button[type="submit"] {
            background-color: #166ed5;
            color: white;
        }
        #modalForm button[type="submit"]:hover {
            background-color: #1565c0;
        }
        #modalForm button[type="button"] {
            background-color: #e3f2fd;
            color: #166ed5;
        }
        #modalForm button[type="button"]:hover {
            background-color: #bbdefb;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(22, 110, 213, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .overlay.active {
            opacity: 1;
        }
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            animation: slideIn 0.3s ease;
        }
        .alert-success {
            background-color: #e3f2fd;
            color: #166ed5;
            border: 1px solid #166ed5;
        }
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #c62828;
        }
        @keyframes slideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .form-group {
            margin-bottom: 20px;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        .delete-button {
            position: absolute;
            top: -10px;
            right: -10px;
            background-color: #c62828;
            color: white;
            border: none;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(198, 40, 40, 0.2);
            transition: background-color 0.3s ease;
            z-index: 2;
        }
        .delete-button:hover {
            background-color: #b71c1c;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="img/logo.png" alt="Logo" height="30" width="30">
            <h1>Profil</h1>
        </div>
        <a href="index.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>

    <div class="container" id="profileContainer">
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="card">';
                if ($userRole == 'admin') {
                    echo '<form action="delete_profile.php" method="post" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus profil ini?\');">';
                    echo '<input type="hidden" name="id" value="' . $row['id'] . '">';
                    echo '<button type="submit" class="delete-button"><i class="fas fa-times"></i></button>';
                    echo '</form>';
                }
                echo '<a href="detail_profile.php?id=' . $row['id'] . '" class="card-link">';
                echo '<img src="' . htmlspecialchars($row['foto_url']) . '" alt="Profile Photo">';
                echo '<h3>' . htmlspecialchars($row['nama']) . '</h3>';
                echo '<p>' . htmlspecialchars($row['jabatan']) . '</p>';
                echo '</a>';
                echo '</div>';
            }
        }
        ?>
    </div>

    <?php if ($userRole == 'admin'): ?>
        <button class="fab-button" onclick="openForm()">
            <i class="fas fa-plus"></i>
        </button>
    <?php endif; ?>

    <div class="overlay" id="overlay" onclick="closeForm()"></div>

    <!-- Modal Form untuk Menambahkan Data Baru -->
    <div id="modalForm">
        <h3>Tambah Profil Baru</h3>
        <div id="alertMessage"></div>
        <form id="profileForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Nama:</label>
                <input type="text" id="name" name="name" required placeholder="Masukkan nama lengkap">
            </div>
            <div class="form-group">
                <label>Jabatan:</label>
                <select id="jabatan" name="jabatan">
                    <option value="KEPALA STASIUN">KEPALA STASIUN</option>
                    <option value="KASUBAG TATA USAHA">KASUBAG TATA USAHA</option>
                    <option value="STAF TATA USAHA">STAF TATA USAHA</option>
                    <option value="KOORBID DATA DAN INFORMASI">KOORBID DATA DAN INFORMASI</option>
                    <option value="KOORBID OBSERVASI">KOORBID OBSERVASI</option>
                    <option value="STAF FUNGSIONAL">STAF FUNGSIONAL</option>
                </select>
            </div>
            <div class="form-group">
                <label>NIP:</label>
                <input type="text" id="nip" name="nip" required placeholder="Masukkan NIP">
            </div>
            <div class="form-group">
                <label>No HP:</label>
                <input type="text" id="no_hp" name="no_hp" placeholder="Masukkan nomor HP">
            </div>
            <div class="form-group">
                <label>Foto:</label>
                <input type="file" id="foto" name="foto" accept="image/*">
            </div>
            <div class="button-group">
                <button type="submit">Simpan</button>
                <button type="button" onclick="closeForm()">Batal</button>
            </div>
        </form>
    </div>

    <script>
        function openForm() {
            document.getElementById('modalForm').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
            setTimeout(() => {
                document.getElementById('modalForm').classList.add('active');
                document.getElementById('overlay').classList.add('active');
            }, 10);
        }

        function closeForm() {
            document.getElementById('modalForm').classList.remove('active');
            document.getElementById('overlay').classList.remove('active');
            setTimeout(() => {
                document.getElementById('modalForm').style.display = 'none';
                document.getElementById('overlay').style.display = 'none';
                document.getElementById('alertMessage').innerHTML = '';
                document.getElementById('profileForm').reset();
            }, 300);
        }

        document.getElementById('profileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            // Validasi form
            if (!formData.get('name') || !formData.get('nip')) {
                showAlert('Nama dan NIP harus diisi!', 'error');
                return;
            }

            fetch('add_profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(data => {
                if (data.includes("berhasil")) {
                    showAlert('Profil berhasil ditambahkan!', 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Gagal menambahkan profil: ' + data, 'error');
                }
            })
            .catch(error => {
                showAlert('Terjadi kesalahan: ' + error.message, 'error');
            });
        });

        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
        }
    </script>
</body>
</html>