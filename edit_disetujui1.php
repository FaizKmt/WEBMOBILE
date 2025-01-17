<?php
session_start();
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
    
    // Cek role dari petugas yang datanya sedang diedit
    $editedUserRole = $row['user_role'];
}

// Dapatkan peran petugas yang sedang login
$sqlPetugasRole = "SELECT role FROM users WHERE username = ?";
$stmtPetugasRole = $conn->prepare($sqlPetugasRole);
$stmtPetugasRole->bind_param("s", $_SESSION['username']);
$stmtPetugasRole->execute();
$resultPetugasRole = $stmtPetugasRole->get_result();

if ($resultPetugasRole->num_rows > 0) {
    $petugasRole = $resultPetugasRole->fetch_assoc()['role'];
} else {
    $petugasRole = null;
}

// Jika admin mencoba mengedit data user, redirect ke halaman sebelumnya
if ($petugasRole === 'admin' && $editedUserRole === 'user') {
    $_SESSION['error_message'] = "Admin tidak dapat mengedit data petugas user.";
    header("Location: tata_usaha_detail1.php?petugas=" . urlencode($editingPetugas));
    exit();
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

// Pastikan user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Ambil role user dari session
$userRole = $_SESSION['role'] ?? '';

// Ambil data user, role, dan status revisi dari database
$getUserDataQuery = "SELECT t.*, u.role as petugas_role, t.user_id as data_user_id,
                           r.status as revision_status, r.catatan as revision_note
                    FROM tata_usaha t 
                    LEFT JOIN users u ON t.petugas = u.username 
                    LEFT JOIN revisions r ON t.id = r.task_id
                    WHERE t.id = ?";
$stmtUserData = $conn->prepare($getUserDataQuery);
$stmtUserData->bind_param("i", $id);
$stmtUserData->execute();
$resultUserData = $stmtUserData->get_result();
$userData = $resultUserData->fetch_assoc();

if (!$userData) {
    $_SESSION['error_message'] = "Data tidak ditemukan.";
    header('Location: tata_usaha.php');
    exit();
}

// Cek role dan kepemilikan data
$isOwner = ($_SESSION['user_id'] == $userData['data_user_id']);
$isAdmin = ($petugasRole === 'admin');

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($userRole === 'user' && isset($_POST['status_user'])) {
        $petugas = $_POST['petugas'];
        $tanggal = $_POST['tanggal'];
        $melaksanakan_tugas = $_POST['melaksanakan_tugas'];
        $output = $_POST['output'];
        $tugas_lainnya = $_POST['tugas_lainnya'];
        $catatan = $_POST['catatan'];
        $newStatus = $_POST['status_user'];
        
        try {
            $conn->begin_transaction();
            
            // Cek kepemilikan data berdasarkan user_id
            $checkOwnership = $conn->prepare("SELECT user_id FROM tata_usaha WHERE id = ? AND user_id = ?");
            $checkOwnership->bind_param("ii", $id, $_SESSION['user_id']);
            $checkOwnership->execute();
            $ownershipResult = $checkOwnership->get_result();
            
            if ($ownershipResult->num_rows === 0) {
                throw new Exception("Anda tidak memiliki akses untuk mengedit data ini.");
            }
            
            // Handle file uploads
            $data_dukung_1 = '';
            $data_dukung_2 = '';
            
            if (isset($_FILES['data_dukung_1']) && $_FILES['data_dukung_1']['error'] == 0) {
                $fileTmpPath = $_FILES['data_dukung_1']['tmp_name'];
                $fileName = $_FILES['data_dukung_1']['name'];
                $filePath = 'uploads/' . $fileName;
                move_uploaded_file($fileTmpPath, $filePath);
                $data_dukung_1 = $fileName;
            }

            if (isset($_FILES['data_dukung_2']) && $_FILES['data_dukung_2']['error'] == 0) {
                $fileTmpPath = $_FILES['data_dukung_2']['tmp_name'];
                $fileName = $_FILES['data_dukung_2']['name'];
                $filePath = 'uploads/' . $fileName;
                move_uploaded_file($fileTmpPath, $filePath);
                $data_dukung_2 = $fileName;
            }

            // Update data
            $updateQuery = "UPDATE tata_usaha SET 
                petugas = ?,
                tanggal = ?,
                melaksanakan_tugas = ?,
                output = ?,
                tugas_lainnya = ?,
                catatan = ?";

            $params = [$petugas, $tanggal, $melaksanakan_tugas, $output, $tugas_lainnya, $catatan];
            $types = "ssssss";

            // Add file paths to query if files were uploaded
            if ($data_dukung_1 !== '') {
                $updateQuery .= ", data_dukung_1 = ?";
                $params[] = $data_dukung_1;
                $types .= "s";
            }
            if ($data_dukung_2 !== '') {
                $updateQuery .= ", data_dukung_2 = ?";
                $params[] = $data_dukung_2;
                $types .= "s";
            }

            $updateQuery .= " WHERE id = ? AND user_id = ?";
            $params[] = $id;
            $params[] = $_SESSION['user_id'];
            $types .= "ii";

            $stmt = $conn->prepare($updateQuery);
            $stmt->bind_param($types, ...$params);

            if (!$stmt->execute()) {
                throw new Exception("Gagal mengupdate data: " . $stmt->error);
            }

            // Update status di tata_usaha dan revisions jika status berubah menjadi "Sudah Direvisi"
            if ($newStatus === 'Sudah Direvisi') {
                // Update status di tata_usaha
                $updateStatusStmt = $conn->prepare("UPDATE tata_usaha SET status = ?, status_user = ? WHERE id = ? AND user_id = ?");
                $updateStatusStmt->bind_param("ssii", $newStatus, $newStatus, $id, $_SESSION['user_id']);
                
                if (!$updateStatusStmt->execute()) {
                    throw new Exception("Gagal mengupdate status di tata_usaha");
                }
                
                // Update status di revisions
                $updateRevisionStmt = $conn->prepare("UPDATE revisions SET status = ? WHERE task_id = ?");
                $updateRevisionStmt->bind_param("si", $newStatus, $id);
                
                if (!$updateRevisionStmt->execute()) {
                    throw new Exception("Gagal mengupdate status di revisions");
                }
            }

            $conn->commit();
            $_SESSION['success_message'] = "Data berhasil diperbarui!";
            header("Location: tata_usaha_detail1.php?petugas=" . urlencode($petugas));
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in updating data: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['status_user'])) {
        $newStatus = $_POST['status_user'];
        $taskId = $id; // ID dari task yang sedang diedit
        
        try {
            $conn->begin_transaction();
            
            // Update status di tata_usaha
            $updateTaskStmt = $conn->prepare("UPDATE tata_usaha SET status = ?, status_user = ? WHERE id = ?");
            $updateTaskStmt->bind_param("ssi", $newStatus, $newStatus, $taskId);
            
            if (!$updateTaskStmt->execute()) {
                throw new Exception("Gagal mengupdate status di tata_usaha");
            }
            
            // Update status di revisions jika status "Sudah Direvisi"
            if ($newStatus === 'Sudah Direvisi') {
                $updateRevisionStmt = $conn->prepare("UPDATE revisions SET status = ? WHERE task_id = ?");
                $updateRevisionStmt->bind_param("si", $newStatus, $taskId);
                
                if (!$updateRevisionStmt->execute()) {
                    throw new Exception("Gagal mengupdate status di revisions");
                }
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Status berhasil diperbarui!";
            
            // Redirect ke halaman detail
            header("Location: tata_usaha_detail1.php?petugas=" . urlencode($userData['petugas']));
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in updating revision status: " . $e->getMessage());
        }
    }
    
    if (isset($_POST['submit_revision'])) {
        $revisiId = $id;
        $catatan = $_POST['catatan_revisi'];
        
        try {
            $conn->begin_transaction();
            
            // Get user role and task details
            $getUserStmt = $conn->prepare("SELECT t.user_id, t.melaksanakan_tugas, t.petugas, u.role 
                                         FROM tata_usaha t 
                                         LEFT JOIN users u ON t.petugas = u.username
                                         WHERE t.id = ?");
            
            $getUserStmt->bind_param("i", $revisiId);
            $getUserStmt->execute();
            $result = $getUserStmt->get_result();
            $taskData = $result->fetch_assoc();
            
            // Set status based on user role
            $status = ($taskData['role'] === 'admin') ? 'Menunggu Revisi' : 'Sudah Direvisi';
            
            // Update or insert revision
            $checkStmt = $conn->prepare("SELECT id FROM revisions WHERE task_id = ?");
            $checkStmt->bind_param("i", $revisiId);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows > 0) {
                $updateStmt = $conn->prepare("UPDATE revisions SET catatan = ?, status = ? WHERE task_id = ?");
                $updateStmt->bind_param("ssi", $catatan, $status, $revisiId);
                $revisionSuccess = $updateStmt->execute();
            } else {
                $insertStmt = $conn->prepare("INSERT INTO revisions (task_id, catatan, status) VALUES (?, ?, ?)");
                $insertStmt->bind_param("iss", $revisiId, $catatan, $status);
                $revisionSuccess = $insertStmt->execute();
            }
            
            // Update status in tata_usaha
            if ($taskData['role'] === 'user') {
                $updateStatusStmt = $conn->prepare("UPDATE tata_usaha SET status_user = ? WHERE id = ?");
            } else {
                $updateStatusStmt = $conn->prepare("UPDATE tata_usaha SET status = ? WHERE id = ?");
            }
            
            $updateStatusStmt->bind_param("si", $status, $revisiId);
            
            if (!$updateStatusStmt->execute()) {
                throw new Exception("Failed to update task status");
            }
            
            // Create notification
            $message = "Revisi tugas: " . $taskData['melaksanakan_tugas'];
            
            $previewData = json_encode([
                'catatan' => $catatan,
                'status' => $status
            ]);
            
            $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, message, preview_data, is_read) VALUES (?, ?, ?, 0)");
            $notifStmt->bind_param("iss", $taskData['user_id'], $message, $previewData);
            
            if (!$notifStmt->execute()) {
                throw new Exception("Failed to create notification");
            }
            
            $conn->commit();
            
            $_SESSION['success_message'] = "Revisi berhasil disimpan dan notifikasi telah dikirim!";
            header("Location: tata_usaha_detail1.php?petugas=" . urlencode($taskData['petugas']));
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in revision process: " . $e->getMessage());
        }
    }
    if ($isAdmin) {
        // Admin hanya bisa update status
        $status = isset($_POST['status']) ? $_POST['status'] : $userData['status'];
        
        $stmt = $conn->prepare("UPDATE tata_usaha SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            // Redirect ke tata_usaha_detail1.php setelah admin menyimpan perubahan
            header("Location: tata_usaha_detail1.php");
            exit();
        }
    } elseif ($isOwner) {
        // User bisa update semua field jika pemilik data
        $petugas = $_POST['petugas'];
        $tanggal = $_POST['tanggal'];
        $melaksanakan_tugas = $_POST['melaksanakan_tugas'];
        $output = $_POST['output'];
        $tugas_lainnya = $_POST['tugas_lainnya'];
        $catatan = $_POST['catatan'];
        
        // Handle file uploads
        $data_dukung_1 = '';
        $data_dukung_2 = '';
        
        if (isset($_FILES['data_dukung_1']) && $_FILES['data_dukung_1']['error'] == 0) {
            $fileTmpPath = $_FILES['data_dukung_1']['tmp_name'];
            $fileName = $_FILES['data_dukung_1']['name'];
            $filePath = 'uploads/' . $fileName;
            move_uploaded_file($fileTmpPath, $filePath);
            $data_dukung_1 = $filePath;
        }

        if (isset($_FILES['data_dukung_2']) && $_FILES['data_dukung_2']['error'] == 0) {
            $fileTmpPath = $_FILES['data_dukung_2']['tmp_name'];
            $fileName = $_FILES['data_dukung_2']['name'];
            $filePath = 'uploads/' . $fileName;
            move_uploaded_file($fileTmpPath, $filePath);
            $data_dukung_2 = $filePath;
        }

        $stmt = $conn->prepare("UPDATE tata_usaha SET 
            petugas = ?, 
            tanggal = ?, 
            melaksanakan_tugas = ?, 
            output = ?, 
            tugas_lainnya = ?, 
            catatan = ?,
            data_dukung_1 = IF(? != '', ?, data_dukung_1),
            data_dukung_2 = IF(? != '', ?, data_dukung_2)
            WHERE id = ? AND user_id = ?");
            
        $stmt->bind_param("ssssssssssii", 
            $petugas, 
            $tanggal, 
            $melaksanakan_tugas, 
            $output, 
            $tugas_lainnya, 
            $catatan,
            $data_dukung_1, $data_dukung_1,
            $data_dukung_2, $data_dukung_2,
            $id,
            $_SESSION['user_id']
        );
    }
    
    if (isset($stmt) && $stmt->execute()) {
        // Redirect berdasarkan role yang mengedit
        if ($isAdmin) {
            header("Location: tata_usaha_detail1.php");
        } else {
            header("Location: tata_usaha_detail1.php?petugas=" . urlencode($userData['petugas']));
        }
        exit();
    } else {
        $_SESSION['error_message'] = "Maaf Anda tidak dapat mengedit data ini " . $conn->error;
    }
}

// Form HTML
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
        <a href="tata_usaha_detail1.php?petugas=<?= urlencode($row['petugas']) ?>" 
           class="flex items-center mb-8 hover-scale inline-block">
            <i class="fas fa-arrow-left text-3xl text-teal-600 mr-4"></i>
            <h1 class="text-4xl font-bold text-gray-800">Edit Tata Usaha</h1>
        </a>

        <form method="POST" enctype="multipart/form-data" class="bg-white shadow-xl rounded-2xl p-8 hover-scale">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Input fields -->
                <?php 
                $fields = [
                    'petugas' => 'text',
                    'tanggal' => 'date',
                    'melaksanakan_tugas' => 'text',
                    'output' => 'text',
                    'tugas_lainnya' => 'text'
                ];
                
                foreach($fields as $field => $type): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            <?= ucwords(str_replace('_', ' ', $field)) ?>
                        </label>
                        <?php if ($isAdmin): ?>
                            <input type="text" 
                                   value="<?= htmlspecialchars($userData[$field]) ?>" 
                                   class="input-animation w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-100" 
                                   readonly>
                            <input type="hidden" name="<?= $field ?>" value="<?= htmlspecialchars($userData[$field]) ?>">
                        <?php else: ?>
                            <input type="<?= $type ?>" 
                                   name="<?= $field ?>" 
                                   value="<?= htmlspecialchars($userData[$field]) ?>" 
                                   class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none"
                                   <?= $type === 'date' ? 'max="' . date('Y-m-d') . '"' : '' ?>>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Status field (only for admin) -->
                <?php if ($isAdmin): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Status</label>
                        <select name="status" class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                            <?php foreach($availableStatuses as $status): ?>
                                <option value="<?= htmlspecialchars($status) ?>" 
                                        <?= $userData['status'] === $status ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $status)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="status" value="<?= htmlspecialchars($userData['status']) ?>">
                <?php endif; ?>

                <!-- Catatan field -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">Catatan</label>
                    <?php if ($isAdmin): ?>
                        <textarea class="input-animation w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-100" 
                                  rows="4" readonly><?= htmlspecialchars($userData['catatan']) ?></textarea>
                        <input type="hidden" name="catatan" value="<?= htmlspecialchars($userData['catatan']) ?>">
                    <?php else: ?>
                        <textarea name="catatan" 
                                  class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                                  rows="4"><?= htmlspecialchars($userData['catatan']) ?></textarea>
                    <?php endif; ?>
                </div>
            </div>

            <!-- File upload fields (only for user) -->
            <?php if (!$isAdmin && $isOwner): ?>
                <div class="mt-8 space-y-6">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <label class="block text-gray-700 font-semibold mb-2">Data Pendukung 1</label>
                        <input type="file" name="data_dukung_1" 
                               class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                        <?php if (!empty($userData['data_dukung_1'])): ?>
                            <p class="mt-2 text-teal-600">File saat ini: <?= htmlspecialchars(basename($userData['data_dukung_1'])) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <label class="block text-gray-700 font-semibold mb-2">Data Pendukung 2</label>
                        <input type="file" name="data_dukung_2" 
                               class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                        <?php if (!empty($userData['data_dukung_2'])): ?>
                            <p class="mt-2 text-teal-600">File saat ini: <?= htmlspecialchars(basename($userData['data_dukung_2'])) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($userRole === 'user'): ?>
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-700 mb-4">Status Revisi</h3>
                    
                    <?php
                    // Ambil status dari kedua tabel
                    $currentStatus = $userData['status_user'];
                    $revisionStatus = null;
                    $revisionNote = null;
                    
                    // Cek status di tabel revisions
                    $revisionQuery = "SELECT status, catatan FROM revisions WHERE task_id = ?";
                    $stmtRevision = $conn->prepare($revisionQuery);
                    $stmtRevision->bind_param("i", $id);
                    $stmtRevision->execute();
                    $revisionResult = $stmtRevision->get_result();
                    
                    if ($revisionResult->num_rows > 0) {
                        $revisionRow = $revisionResult->fetch_assoc();
                        $revisionStatus = $revisionRow['status'];
                        $revisionNote = $revisionRow['catatan'];
                    }
                    
                    // Gunakan status dari revisions jika ada
                    $currentStatus = $revisionStatus ?? $currentStatus;
                    ?>

                    <?php if ($currentStatus === 'Menunggu Revisi'): ?>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">Ubah Status</label>
                            <select name="status_user" 
                                    class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                                <option value="Menunggu Revisi" <?= $currentStatus === 'Menunggu Revisi' ? 'selected' : '' ?>>
                                    Menunggu Revisi
                                </option>
                                <option value="Sudah Direvisi" <?= $currentStatus === 'Sudah Direvisi' ? 'selected' : '' ?>>
                                    Sudah Revisi
                                </option>
                            </select>
                        </div>

                        <?php if ($revisionNote): ?>
                        <div class="mb-4">
                            <label class="block text-gray-700 font-semibold mb-2">Catatan Revisi dari Admin</label>
                            <textarea class="w-full border-2 border-gray-200 rounded-lg p-3 bg-gray-100" rows="4" readonly><?= htmlspecialchars($revisionNote) ?></textarea>
                        </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-600">Status saat ini: <?= ucwords(str_replace('_', ' ', $currentStatus ?? 'Belum ada status')) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-8 text-center space-x-4">
                <button type="submit" name="submit_normal"
                        class="btn-animation bg-teal-500 text-white font-bold py-3 px-8 rounded-full hover:bg-teal-600">
                    Simpan Perubahan
                </button>
                
                <?php if ($isAdmin): ?>
                <!-- Form Revisi -->
                <div class="mt-8 p-6 bg-gray-50 rounded-lg">
                    <h3 class="text-xl font-bold text-gray-700 mb-4">Form Revisi</h3>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Status Revisi</label>
                        <select name="status_revisi" 
                                class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none">
                            <?php if ($petugasRole === 'admin'): ?>
                                <option value="Menunggu Revisi">Menunggu Revisi</option>
                            <?php else: ?>
                                <option value="Sudah Direvisi">Sudah Revisi</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">Catatan Revisi</label>
                        <textarea name="catatan_revisi" 
                                  class="input-animation w-full border-2 border-gray-300 rounded-lg p-3 focus:border-teal-500 focus:outline-none" 
                                  rows="4"
                                  placeholder="Masukkan catatan revisi"></textarea>
                    </div>
                    
                    <button type="submit" name="submit_revision"
                            class="btn-animation bg-blue-500 text-white font-bold py-3 px-8 rounded-full hover:bg-blue-600">
                        Simpan Revisi
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($userRole === 'user'): ?>
                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
            <?php endif; ?>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>