<?php
session_start();

// Check if the user is an admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "logbook1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission for adding a new petugas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_petugas'])) {
    $newPetugas = $_POST['new_petugas'];
    if (!empty($newPetugas)) {
        $stmt = $conn->prepare("INSERT INTO petugas (nama) VALUES (?)");
        $stmt->bind_param("s", $newPetugas);
        $stmt->execute();
        $stmt->close();
        header('Location: add_petugas.php');
        exit();
    }
}

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_petugas'])) {
    $petugasId = $_POST['petugas_id'];
    $stmtDelete = $conn->prepare("DELETE FROM petugas WHERE id = ?");
    $stmtDelete->bind_param("i", $petugasId);
    $stmtDelete->execute();
    $stmtDelete->close();
    header('Location: add_petugas.php');
    exit();
}

// Fetch existing petugas from the database
$petugasList = [];
$result = $conn->query("SELECT * FROM petugas");
while ($row = $result->fetch_assoc()) {
    $petugasList[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Petugas Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f6f8fc 0%, #e9f0f7 100%);
            min-height: 100vh;
        }
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .input-field {
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #7C3AED;
            box-shadow: 0 0 0 3px rgba(124,58,237,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #7C3AED, #6D28D9);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(124,58,237,0.4);
        }
        .btn-delete {
            background: linear-gradient(135deg, #EF4444, #DC2626);
            transition: all 0.3s ease;
        }
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(239,68,68,0.4);
        }
        .petugas-item {
            border-left: 4px solid #7C3AED;
            transition: all 0.3s ease;
        }
        .petugas-item:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body class="font-sans">
    <div class="container mx-auto p-8">
        <div class="card p-8 max-w-2xl mx-auto">
            <div class="flex items-center mb-8">
                <i class="fas fa-arrow-left text-2xl text-gray-600 mr-3 cursor-pointer hover:text-gray-800" onclick="window.location.href='tata_usaha.php'"></i>
                <h1 class="text-3xl font-bold text-gray-800">Tambah Petugas Baru</h1>
            </div>

            <form method="post" class="mb-8">
                <div class="mb-6">
                    <label for="new_petugas" class="block text-gray-700 font-semibold mb-2">Nama Petugas Baru</label>
                    <div class="flex gap-4">
                        <input type="text" id="new_petugas" name="new_petugas" 
                            placeholder="Masukkan nama petugas" required 
                            class="input-field flex-1 px-4 py-3 rounded-lg focus:outline-none">
                        <button type="submit" name="add_petugas" 
                            class="btn-primary px-6 py-3 rounded-lg text-white font-semibold flex items-center">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah
                        </button>
                    </div>
                </div>
            </form>

            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Daftar Petugas</h2>
                <div class="space-y-3">
                    <?php foreach ($petugasList as $petugas): ?>
                        <div class="petugas-item bg-white p-4 rounded-lg flex justify-between items-center">
                            <div class="flex items-center">
                                <i class="fas fa-user text-purple-600 mr-3"></i>
                                <span class="text-gray-700"><?= htmlspecialchars($petugas['nama']) ?></span>
                            </div>
                            <form method="post" class="inline">
                                <input type="hidden" name="petugas_id" value="<?= $petugas['id'] ?>">
                                <button type="submit" name="delete_petugas" 
                                    class="btn-delete px-4 py-2 rounded-lg text-white font-semibold flex items-center">
                                    <i class="fas fa-trash-alt mr-2"></i>
                                    Hapus
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>