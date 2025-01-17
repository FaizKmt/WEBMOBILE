<?php
session_start();

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

// Cek apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil peran pengguna dari sesi
$userRole = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Query untuk admin: melihat semua data yang diinput oleh admin
$sqlAdmin = "SELECT DISTINCT t.petugas, u.foto_profil, u.jabatan 
            FROM tata_usaha t 
            JOIN users u ON t.user_id = u.id 
            WHERE t.user_id = ? AND u.role = 'admin'";
$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("s", $userId);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();

// Query untuk user: melihat semua data yang diinput oleh user
$sqlUser = "SELECT DISTINCT t.petugas, u.foto_profil, u.jabatan 
           FROM tata_usaha t 
           JOIN users u ON t.user_id = u.id 
           WHERE u.role = 'user'";
$stmtUser = $conn->prepare($sqlUser);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tata Usaha - Daftar Petugas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            transition: all 0.3s ease;
        }
        .header {
            background:#1663DE;
            color: #ffffff;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: slideDown 0.5s ease-out;
        }
        @keyframes slideDown {
            from {
                transform: translateY(-100%);
            }
            to {
                transform: translateY(0);
            }
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .header .logo {
            height: 48px;
            width: 48px;
            border-radius: 8px;
            background: white;
            padding: 4px;
            transition: transform 0.3s;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        .header .logo:hover {
            transform: scale(1.1);
        }
        .header .nav-icons {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 10px;
        }
        .header .nav-icons i {
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 20px;
            padding: 10px;
            border-radius: 50%;
        }
        .header .nav-icons i:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }
        @media (max-width: 640px) {
            .header .nav-icons i {
                font-size: 16px;
                padding: 8px;
            }
            .header .logo {
                height: 32px;
                width: 32px;
            }
        }
        .container {
            max-width: 1450px;
            margin: 50px auto;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.8s ease-out;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .petugas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 25px;
            padding: 25px;
        }
        .petugas-button {
            background: linear-gradient(135deg, #7CB9E8, #6495ED);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 16px;
            font-weight: 500;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .petugas-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .petugas-button::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        .petugas-button:hover::after {
            left: 100%;
        }
        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .profile-image i {
            font-size: 20px;
            color: #7CB9E8;
        }
        .add-button-container {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            gap: 10px;
        }
        .add-button, .add-petugas-button, .edit-users-button {
            background: linear-gradient(135deg, #7CB9E8, #6495ED);
            color: #ffffff;
            border: none;
            border-radius: 50%;
            width: 65px;
            height: 65px;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
        }
        .add-button:hover, .add-petugas-button:hover, .edit-users-button:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }
        h2 {
            color: #7CB9E8;
            font-size: 24px;
            font-weight: 600;
            margin: 20px 0;
            padding-left: 25px;
            position: relative;
        }
        h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 5px;
            height: 25px;
            background: linear-gradient(135deg, #7CB9E8, #6495ED);
            transform: translateY(-50%);
            border-radius: 3px;
        }
        .petugas-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .petugas-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .profile-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 15px;
            border: 3px solid #7CB9E8;
        }

        .profile-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .petugas-info {
            width: 100%;
        }

        .petugas-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .petugas-jabatan {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .status-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
        }

        .notification-badge {
            background-color: #dc3545;
            color: white;
            font-size: 12px;
            padding: 2px 6px;
            border-radius: 10px;
            position: relative;
            margin-left: 5px;
            top: -10px;
            font-weight: 600;
            display: inline-block;
            min-width: 18px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .nav-icons {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-icons a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="img/logo.png" alt="Logo" class="logo">
        <h1>Kinerja Tata Usaha</h1>
        <div class="nav-icons">
            <a href="index.php" class="text-white position-relative" style="color: white; text-decoration: none; margin-right: 15px;">
                <i class="fas fa-arrow-left" style="color: white; font-size: 20px;"></i>
            </a>
            <?php if ($userRole !== 'admin'): ?>
                <a href="notifications.php" class="text-white position-relative" style="color: white; text-decoration: none;">
                    <i class="fas fa-bell" style="color: white; font-size: 20px;"></i>
                    <?php
                    // Hitung notifikasi yang belum dibaca
                    $unreadCount = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $userId AND is_read = FALSE")->fetch_assoc()['count'];
                    if ($unreadCount > 0):
                    ?>
                        <span class="notification-badge">
                            <?= $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>
            <i class="fas fa-sign-out-alt" onclick="window.location.href='logout.php'" style="color: white; font-size: 20px; margin-left: 15px; cursor: pointer;"></i>
        </div>
    </div>
    <div class="container">
        <?php if ($userRole == 'admin'): ?>
            <h2>Admin</h2>
            <div class="petugas-grid">
                <?php
                $petugasAdmin = array();
                if ($resultAdmin->num_rows > 0) {
                    while ($row = $resultAdmin->fetch_assoc()) {
                        if (!in_array($row['petugas'], $petugasAdmin)) {
                            $petugasAdmin[] = $row['petugas'];
                            ?>
                            <div class="petugas-card" onclick="window.location.href='tata_usaha_detail.php?petugas=<?= urlencode($row['petugas']) ?>'">
                                <div class="profile-image">
                                    <?php if (!empty($row['foto_profil'])): ?>
                                        <img src="uploads/profile/<?= htmlspecialchars($row['foto_profil']) ?>" alt="Foto Profil">
                                    <?php else: ?>
                                        <img src="assets/default-profile.png" alt="Default Profile">
                                    <?php endif; ?>
                                </div>
                                <div class="petugas-info">
                                    <div class="petugas-name"><?= htmlspecialchars($row['petugas']) ?></div>
                                    <div class="petugas-jabatan"><?= htmlspecialchars($row['jabatan'] ?? 'Jabatan belum diatur') ?></div>
                                    <span class="status-badge">Admin</span>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } else {
                    echo '<p>Tidak ada data yang diinput admin.</p>';
                }
                ?>
            </div>
        <?php endif; ?>

        <h2>Staf Tata Usaha</h2>
        <div class="petugas-grid">
            <?php
            $petugasUser = array();
            if ($resultUser->num_rows > 0) {
                while ($row = $resultUser->fetch_assoc()) {
                    if (!in_array($row['petugas'], $petugasUser)) {
                        $petugasUser[] = $row['petugas'];
                        ?>
                        <div class="petugas-card" onclick="window.location.href='tata_usaha_detail1.php?petugas=<?= urlencode($row['petugas']) ?>'">
                            <div class="profile-image">
                                <?php if (!empty($row['foto_profil'])): ?>
                                    <img src="uploads/profile/<?= htmlspecialchars($row['foto_profil']) ?>" alt="Foto Profil">
                                <?php else: ?>
                                    <img src="assets/default-profile.png" alt="Default Profile">
                                <?php endif; ?>
                            </div>
                            <div class="petugas-info">
                                <div class="petugas-name"><?= htmlspecialchars($row['petugas']) ?></div>
                                <div class="petugas-jabatan"><?= htmlspecialchars($row['jabatan'] ?? 'Jabatan belum diatur') ?></div>
                                <span class="status-badge">User</span>
                            </div>
                        </div>
                        <?php
                    }
                }
            } else {
                echo '<p>Tidak ada data yang diinput oleh pengguna.</p>';
            }
            ?>
        </div>
        <?php if ($userRole == 'admin'): ?>
            <div class="add-button-container">
                <button class="add-button" onclick="redirectToForm()">
                    <i class="fas fa-plus"></i>
                </button>
                <button class="add-petugas-button" onclick="window.location.href='add_petugas.php'">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button class="edit-users-button" onclick="window.location.href='edit_users.php'">
                    <i class="fas fa-users-cog"></i>
                </button>
            </div>
        <?php else: ?>
            <div class="add-button-container">
                <button class="add-button" onclick="redirectToForm()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>
    <script>
        function redirectToForm() {
            <?php if ($userRole == 'admin'): ?>
                window.location.href = 'form_tata_usaha.php';
            <?php else: ?>
                window.location.href = 'form_tata_usaha1.php';
            <?php endif; ?>
        }
    </script>
</body>
</html>

<?php
$stmtAdmin->close();
$stmtUser->close();
$conn->close();
?>
