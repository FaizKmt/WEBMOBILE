<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $jabatan = $_POST['jabatan'];
    
    // Initialize foto_profil variable
    $foto_profil = null;

    // Handle photo upload
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['foto_profil']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        if (in_array(strtolower($filetype), $allowed)) {
            $newFilename = uniqid() . '.' . $filetype;
            $uploadDir = 'uploads/profile/';
            
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['foto_profil']['tmp_name'], $uploadDir . $newFilename)) {
                $foto_profil = $newFilename;
            }
        }
    }

    // Insert new user
    $sql = "INSERT INTO users (username, password, role, jabatan, foto_profil) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $username, $password, $role, $jabatan, $foto_profil);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User baru berhasil ditambahkan!";
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan user: " . $conn->error;
    }
    
    header('Location: edit_users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            opacity: 0;
            animation: fadeIn 0.5s ease-in forwards;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .header {
            background: linear-gradient(135deg, #1663DE, #3B82F6);
            color: white;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(22, 99, 222, 0.2);
            height: 60px;
        }

        .header .logo img {
            height: 48px;
            width: 48px;
            border-radius: 8px;
            background: white;
            padding: 4px;
            transition: transform 0.3s;
        }

        .header .logo img:hover {
            transform: scale(1.1);
        }

        .container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1663DE;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #1663DE;
            box-shadow: 0 0 0 2px rgba(22, 99, 222, 0.2);
            outline: none;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
        }

        .btn-primary {
            background-color: #1663DE;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1255c4;
            transform: translateY(-2px);
        }

        .home-button {
            background-color: #79BAEC;
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
            background-color: #64B5F6;
            transform: scale(1.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #842029;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="img/logo.png" alt="Logo">
        </div>
        <h1>Tambah User Baru</h1>
        <a href="tata_usaha.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
                <?= $_SESSION['success_message'] ?>
                <?php unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?= $_SESSION['error_message'] ?>
                <?php unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Username:</label>
                <input type="text" name="username" required>
            </div>

            <div class="form-group">
                <label>Password:</label>
                <input type="password" name="password" required>
            </div>

            <div class="form-group">
                <label>Role:</label>
                <select name="role" required>
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>
            </div>

            <div class="form-group">
                <label>Jabatan:</label>
                <select name="jabatan" required>
                    <option value="ADMINISTRASI UMUM">ADMINISTRASI UMUM</option>
                    <option value="KASUBAG TATA USAHA">KASUBAG TATA USAHA</option>
                    <option value="STAF TATA USAHA">STAF TATA USAHA</option>
                    <option value="ARSIPARIS">ARSIPARIS</option>
                    <option value="BENDAHARA">BENDAHARA</option>
                    <option value="PENGELOLAH PERSEDIAAN">PENGELOLAH PERSEDIAAN</option>
                </select>
            </div>

            <div class="form-group">
                <label>Foto Profil:</label>
                <input type="file" name="foto_profil" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Tambah User</button>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>