<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = ""; // Kosongkan jika Anda menggunakan default XAMPP
$dbname = "logbook1";

$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek apakah ada ID yang diterima dari URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data dari database berdasarkan ID
    $result = $conn->query("SELECT t.*, u.role as user_role 
                           FROM tata_usaha t 
                           LEFT JOIN users u ON t.petugas = u.username 
                           WHERE t.id = $id");
    $row = $result->fetch_assoc();
    
    // Simpan nama petugas yang sedang diedit
    $editingPetugas = $row['petugas'];
}

// Dapatkan peran petugas
$sqlPetugasRole = "SELECT role FROM users WHERE username = ?";
$stmtPetugasRole = $conn->prepare($sqlPetugasRole);
$stmtPetugasRole->bind_param("s", $row['petugas']);
$stmtPetugasRole->execute();
$resultPetugasRole = $stmtPetugasRole->get_result();

if ($resultPetugasRole->num_rows > 0) {
    $petugasRole = $resultPetugasRole->fetch_assoc()['role'];
} else {
    // Jika petugas tidak ditemukan di tabel users, cek apakah ada admin dengan nama tersebut
    $sqlAdminCheck = "SELECT role FROM users WHERE role = 'admin'";
    $resultAdminCheck = $conn->query($sqlAdminCheck);
    if ($resultAdminCheck->num_rows > 0) {
        $petugasRole = 'admin';
    } else {
        $petugasRole = null;
    }
}

// Ambil semua status unik dari database tata_usaha
$sqlStatus = "SELECT DISTINCT status FROM tata_usaha WHERE status IS NOT NULL AND status != ''";
$statusResult = $conn->query($sqlStatus);
$availableStatuses = [];
while($statusRow = $statusResult->fetch_assoc()) {
    $availableStatuses[] = $statusRow['status'];
}

// Ambil daftar petugas dari tabel tata_usaha khusus untuk admin
$sqlPetugas = "SELECT DISTINCT t.petugas 
               FROM tata_usaha t 
               INNER JOIN users u ON t.petugas = u.username 
               WHERE u.role = 'admin'";
$petugasResult = $conn->query($sqlPetugas);
$availablePetugas = [];
while($petugasRow = $petugasResult->fetch_assoc()) {
    $availablePetugas[] = $petugasRow['petugas'];
}

// Proses pengeditan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $petugas = $_POST['petugas'];
    $tanggal = $_POST['tanggal'];
    $melaksanakan_tugas = $_POST['melaksanakan_tugas'];
    $output = $_POST['output'];
    $tugas_lainnya = $_POST['tugas_lainnya'];
    $catatan = $_POST['catatan'];
    
    // Status handling
    if ($petugasRole === 'admin' && isset($_POST['status'])) {
        $status = $_POST['status'];
        // Pastikan status sesuai dengan database
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
                $status = $row['status']; // Gunakan status yang ada jika tidak valid
                break;
        }
    } else {
        $status = $row['status']; // Pertahankan status yang ada
    }

    // Debug log
    error_log("Status sebelum update: " . $row['status']);
    error_log("Status yang akan diupdate: " . $status);

    // File handling
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

    // Update query
    if ($data_dukung_1 != '' || $data_dukung_2 != '') {
        $sql = "UPDATE tata_usaha SET 
            petugas = ?, 
            tanggal = ?, 
            melaksanakan_tugas = ?, 
            output = ?, 
            tugas_lainnya = ?, 
            catatan = ?,
            status = ?,
            data_dukung_1 = CASE WHEN ? != '' THEN ? ELSE data_dukung_1 END,
            data_dukung_2 = CASE WHEN ? != '' THEN ? ELSE data_dukung_2 END
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssi", 
            $petugas, 
            $tanggal, 
            $melaksanakan_tugas, 
            $output, 
            $tugas_lainnya, 
            $catatan,
            $status,
            $data_dukung_1,
            $data_dukung_1,
            $data_dukung_2,
            $data_dukung_2,
            $id
        );
    } else {
        $sql = "UPDATE tata_usaha SET 
            petugas = ?, 
            tanggal = ?, 
            melaksanakan_tugas = ?, 
            output = ?, 
            tugas_lainnya = ?, 
            catatan = ?,
            status = ?
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssi", 
            $petugas, 
            $tanggal, 
            $melaksanakan_tugas, 
            $output, 
            $tugas_lainnya, 
            $catatan,
            $status,
            $id
        );
    }

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Data berhasil diupdate dengan status: " . $status;
        header("Location: tata_usaha_detail.php?petugas=" . urlencode($petugas));
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
        header("Location: edit_disetujui.php?id=" . $id);
        exit();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .hover-scale {
            transition: transform 0.2s;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }

        .input-animation {
            transition: all 0.3s ease;
        }

        .input-animation:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .btn-animation {
            transition: all 0.3s ease;
        }

        .btn-animation:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen">
    <div class="container mx-auto p-8 fade-in">
        <a href="tata_usaha_detail.php?petugas=<?= urlencode($row['petugas']) ?>" 
           class="flex items-center mb-8 hover-scale inline-block">
            <i class="fas fa-arrow-left text-3xl text-teal-600 mr-4"></i>
            <h1 class="text-4xl font-bold text-gray-800">Edit Tata Usaha</h1>
        </a>

        <form method="POST" enctype="multipart/form-data" 
              class="bg-white shadow-xl rounded-2xl p-8 hover-scale">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Petugas</label>
                    <?php if ($petugasRole === 'tes'): ?>
                        <select name="petugas" 
                                class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                                required>
                            <?php
                            // Query untuk mendapatkan semua petugas admin
                            $sql_admin_petugas = "SELECT DISTINCT username FROM users WHERE role = 'admin'";
                            $result_admin_petugas = $conn->query($sql_admin_petugas);
                            while($admin_row = $result_admin_petugas->fetch_assoc()) {
                                $selected = ($editingPetugas === $admin_row['username']) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($admin_row['username']) . "' {$selected}>" 
                                     . htmlspecialchars($admin_row['username']) . "</option>";
                            }
                            ?>
                        </select>
                    <?php else: ?>
                        <input type="text" name="petugas" 
                               value="<?= htmlspecialchars($editingPetugas) ?>" 
                               class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                               readonly>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Tanggal</label>
                    <input type="date" name="tanggal" 
                           value="<?= htmlspecialchars($row['tanggal']) ?>" 
                           max="<?= date('Y-m-d'); ?>" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                           required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Melaksanakan Tugas</label>
                    <input type="text" name="melaksanakan_tugas" value="<?= htmlspecialchars($row['melaksanakan_tugas']) ?>" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Output</label>
                    <input type="text" name="output" value="<?= htmlspecialchars($row['output']) ?>" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Tugas Lainnya</label>
                    <input type="text" name="tugas_lainnya" value="<?= htmlspecialchars($row['tugas_lainnya']) ?>" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                </div>

                <?php if ($petugasRole === 'admin'): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Status</label>
                        <select name="status" 
                                class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                            <option value="belum_selesai" <?= $row['status'] === 'belum_selesai' ? 'selected' : '' ?>>
                                Belum Selesai
                            </option>
                            <option value="sudahselesai" <?= $row['status'] === 'sudahselesai' ? 'selected' : '' ?>>
                                Sudah Selesai
                            </option>
                            <option value="konsep" <?= $row['status'] === 'konsep' ? 'selected' : '' ?>>
                                Konsep
                            </option>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($row['status']) ?>">
                <?php endif; ?>

                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Catatan</label>
                    <textarea name="catatan" 
                              class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                              rows="4"><?= htmlspecialchars($row['catatan']) ?></textarea>
                </div>
            </div>

            <div class="mt-8 space-y-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block text-gray-700 font-semibold mb-2">Data Pendukung 1</label>
                    <input type="file" name="data_dukung_1" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                    <?php if (!empty($row['data_dukung_1'])): ?>
                        <p class="mt-2 text-teal-600">File saat ini: <?= htmlspecialchars(basename($row['data_dukung_1'])) ?></p>
                    <?php endif; ?>
                </div>

                <div class="p-4 bg-gray-50 rounded-lg">
                    <label class="block text-gray-700 font-semibold mb-2">Data Pendukung 2</label>
                    <input type="file" name="data_dukung_2" 
                           class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                    <?php if (!empty($row['data_dukung_2'])): ?>
                        <p class="mt-2 text-teal-600">File saat ini: <?= htmlspecialchars(basename($row['data_dukung_2'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-8 text-center">
                <button type="submit" 
                        class="btn-animation bg-teal-500 text-white font-bold py-3 px-8 rounded-full hover:bg-teal-600">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>