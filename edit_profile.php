<?php
// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1"; // Ganti dengan nama database Anda

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID dari parameter GET (misalnya ?id=1)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query untuk mengambil data pengguna berdasarkan ID
$sql = "SELECT nama, jabatan, nip, no_hp, foto_url FROM profile_details WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("Data tidak ditemukan.");
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #E3F2FD 0%, #BBDEFB 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }
        .header {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            background-color: #ffffff;
            border-bottom: 1px solid #E3F2FD;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .header i {
            font-size: 24px;
            color: #64B5F6;
            margin-right: 15px;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        .header i:hover {
            color: #1E88E5;
        }
        .header h1 {
            font-size: 22px;
            margin: 0;
            color: #1976D2;
            font-weight: 500;
        }
        .container {
            max-width: 700px;
            margin: 30px auto;
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(25, 118, 210, 0.1);
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #1976D2;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #E3F2FD;
            border-radius: 8px;
            font-size: 15px;
            color: #424242;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #64B5F6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(100, 181, 246, 0.2);
        }
        .form-group input[readonly] {
            background-color: #F5F5F5;
        }
        .form-group .photo {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 2px dashed #90CAF9;
            padding: 25px;
            border-radius: 12px;
            cursor: pointer;
            background-color: #F8FDFF;
            margin: 15px 0;
            transition: all 0.3s ease;
        }
        .form-group .photo:hover {
            border-color: #64B5F6;
            background-color: #F1F8FF;
        }
        .form-group .photo img {
            width: 100%;
            height: auto;
            max-width: 580px;
            max-height: 580px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .footer {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
            background-color: #ffffff;
            border-top: 1px solid #E3F2FD;
            position: fixed;
            bottom: 0;
            width: 100%;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .footer button {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 22px;
            color: #64B5F6;
            transition: all 0.3s ease;
            padding: 10px 20px;
            border-radius: 8px;
        }
        .footer button:hover {
            color: #1E88E5;
            background-color: #E3F2FD;
        }
        .form-group .file-input {
            display: none;
        }
        .form-group .file-label {
            display: inline-block;
            padding: 12px 25px;
            background-color: #64B5F6;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 14px;
            box-shadow: 0 2px 5px rgba(100, 181, 246, 0.3);
        }
        .form-group .file-label:hover {
            background-color: #1E88E5;
            transform: translateY(-1px);
        }
        .file-label-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 15px;
        }
        .container p {
            color: #1976D2;
            text-align: center;
            font-size: 14px;
            margin-bottom: 25px;
            padding: 10px;
            background-color: #E3F2FD;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <div class="header" onclick="window.history.back()">
        <i class="fas fa-arrow-left"></i>
        <h1>EDIT PROFIL SAYA</h1>
    </div>

    <div class="container">
               <form id="profileForm" action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">NAMA*</label>
                <input id="name" name="name" type="text" value="<?php echo htmlspecialchars($user['nama']); ?>" required/>
            </div>
            <div class="form-group">
                <label for="jabatan">JABATAN*</label>
                <select id="jabatan" name="jabatan" required>
                    <option value="KEPALA STASIUN" <?php echo ($user['jabatan'] == 'KEPALA STASIUN') ? 'selected' : ''; ?>>KEPALA STASIUN</option>
                    <option value="KASUBAG TATA USAHA" <?php echo ($user['jabatan'] == 'KASUBAG TATA USAHA') ? 'selected' : ''; ?>>KASUBAG TATA USAHA</option>
                    <option value="STAF TATA USAHA" <?php echo ($user['jabatan'] == 'STAF TATA USAHA') ? 'selected' : ''; ?>>STAF TATA USAHA</option>
                    <option value="KOORBID DATA DAN INFORMASI" <?php echo ($user['jabatan'] == 'KOORBID DATA DAN INFORMASI') ? 'selected' : ''; ?>>KOORBID DATA DAN INFORMASI</option>
                    <option value="KOORBID OBSERVASI" <?php echo ($user['jabatan'] == 'KOORBID OBSERVASI') ? 'selected' : ''; ?>>KOORBID OBSERVASI</option>
                    <option value="STAF FUNGSIONAL" <?php echo ($user['jabatan'] == 'STAF FUNGSIONAL') ? 'selected' : ''; ?>>STAF FUNGSIONAL</option>
                </select>
            </div>
            <div class="form-group">
                <label for="nip">NIP*</label>
                <input id="nip" name="nip" type="text" value="<?php echo htmlspecialchars($user['nip']); ?>" required/>
            </div>
            <div class="form-group">
                <label for="phone">NO. HP*</label>
                <input id="phone" name="phone" type="text" value="<?php echo htmlspecialchars($user['no_hp']); ?>" required/>
            </div>
            <div class="form-group">
                <label for="photo">FOTO*</label>
                <div class="photo" onclick="document.getElementById('file').click();">
                    <img id="photoPreview" alt="User photo" src="<?php echo htmlspecialchars($user['foto_url']); ?>"/>
                </div>
                <div class="file-label-container">
                    <span class="file-label" onclick="document.getElementById('file').click();">Ganti Foto</span>
                </div>
                <input type="file" id="file" name="photo" class="file-input" accept="image/*" onchange="previewImage(event)"/>
            </div>
            <input type="hidden" name="id" value="<?php echo $id; ?>"/>
        </form>
    </div>

    <div class="footer">
        <button class="cancel" onclick="window.history.back()">
            <i class="fas fa-times"></i>
        </button>
        <button class="save" form="profileForm" type="submit">
            <i class="fas fa-save"></i>
        </button>
    </div>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('photoPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>