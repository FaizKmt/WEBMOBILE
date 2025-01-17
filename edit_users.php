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
    $userId = $_POST['user_id'];
    $newUsername = $_POST['username'];
    $newRole = $_POST['role'];
    $newJabatan = $_POST['jabatan'];

    $updateSql = "UPDATE users SET username = ?, role = ?, jabatan = ?";
    $params = [$newUsername, $newRole, $newJabatan];
    $types = "sss";

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
                $updateSql .= ", foto_profil = ?";
                $params[] = $newFilename;
                $types .= "s";
            }
        }
    }

    // Only update password if a new one is provided and not empty
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $updateSql .= ", password = ?";
        $params[] = $hashedPassword;
        $types .= "s";
    }

    $updateSql .= " WHERE id = ?";
    $params[] = $userId;
    $types .= "i";

    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User berhasil diperbarui!";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui user: " . $conn->error;
    }
    
    header('Location: edit_users.php');
    exit();
}

// Fetch all users
$sql = "SELECT * FROM users ORDER BY username";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Users</title>
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
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .user-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .user-card:hover {
            transform: translateY(-5px);
        }

        .form-group {
            margin-bottom: 1rem;
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
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        .profile-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            overflow: hidden;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #79BAEC;
            color: white;
        }

        .btn-primary:hover {
            background-color: #64B5F6;
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

        .add-user-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 20px 0;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .add-user-button:hover {
            background-color: #45a049;
            transform: translateY(-2px);
        }

        @media (max-width: 640px) {
            .header .logo img {
                height: 32px;
                width: 32px;
            }

            .users-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">
            <img src="img/logo.png" alt="Logo">
        </div>
        <h1>Edit Users</h1>
        <a href="tata_usaha.php" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>

    <div class="container">
        <a href="tambah_user.php" class="add-user-button">
            <i class="fas fa-user-plus"></i>
            Tambah User Baru
        </a>

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

        <div class="users-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="user-card">
                    <form method="POST" action="edit_users.php" enctype="multipart/form-data">
                        <input type="hidden" name="user_id" value="<?= $row['id'] ?>">
                        
                        <div class="profile-image">
                            <?php if (!empty($row['foto_profil'])): ?>
                                <img src="uploads/profile/<?= htmlspecialchars($row['foto_profil']) ?>" alt="Profile Photo">
                            <?php else: ?>
                                <img src="assets/default-profile.png" alt="Default Profile">
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label>Foto Profil:</label>
                            <input type="file" name="foto_profil" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($row['username']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Role:</label>
                            <select name="role" required>
                                <option value="admin" <?= $row['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="user" <?= $row['role'] === 'user' ? 'selected' : '' ?>>User</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Jabatan:</label>
                            <select name="jabatan" required>
                                <option value="ADMINISTRASI UMUM" <?= $row['jabatan'] === 'ADMINISTRASI UMUM' ? 'selected' : '' ?>>ADMINISTRASI UMUM</option>
                                <option value="KASUBAG TATA USAHA" <?= $row['jabatan'] === 'KASUBAG TATA USAHA' ? 'selected' : '' ?>>KASUBAG TATA USAHA</option>
                                <option value="STAF TATA USAHA" <?= $row['jabatan'] === 'STAF TATA USAHA' ? 'selected' : '' ?>>STAF TATA USAHA</option>
                                <option value="ARSIPARIS" <?= $row['jabatan'] === 'ARSIPARIS' ? 'selected' : '' ?>>ARSIPARIS</option>
                                <option value="BENDAHARA" <?= $row['jabatan'] === 'BENDAHARA' ? 'selected' : '' ?>>BENDAHARA</option>
                                 <option value="PENGELOLAH BMN" <?= $row['jabatan'] === 'PENGELOLAH BMN' ? 'selected' : '' ?>>PENGELOLAH BMN</option>
                                <option value="PENGELOLAH PERSEDIAAN" <?= $row['jabatan'] === 'PENGELOLAH PERSEDIAAN' ? 'selected' : '' ?>>PENGELOLAH PERSEDIAAN</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>New Password (kosongkan jika tidak ingin mengubah):</label>
                            <input type="password" name="password" placeholder="Masukkan password baru">
                        </div>

                        <button type="submit" class="btn btn-primary">Update User</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?> 