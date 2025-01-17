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

// Ambil nama petugas dari URL
$petugas = isset($_GET['petugas']) ? $_GET['petugas'] : '';

// Tambahkan fungsi untuk menyimpan notifikasi
function saveNotification($conn, $userId, $data) {
    try {
        $message = "Laporan Anda telah dihapus oleh admin";
        $previewData = json_encode($data);
        
        // Ubah query untuk menggunakan mysqli
        $stmt = $conn->prepare("INSERT INTO notifications (user_id, message, preview_data) VALUES (?, ?, ?)");
        if ($stmt === false) {
            throw new Exception("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("iss", $userId, $message, $previewData);
        $result = $stmt->execute();
        
        if ($result === false) {
            throw new Exception("Error executing statement: " . $stmt->error);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Error in saveNotification: " . $e->getMessage());
        return false;
    }
}

// Fungsi untuk menyetujui tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['approve_id'])) {
    $approveId = $_POST['approve_id'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Update status dan tambahkan informasi approval
        $updateSql = "UPDATE tata_usaha 
                     SET status = 'sudah disetujui',
                         approved_at = NOW(),
                         approved_by = ?
                     WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("ii", $_SESSION['user_id'], $approveId);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error updating record: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect dengan pesan sukses
        $_SESSION['success_message'] = "Tugas berhasil disetujui!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
        exit();
        
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Fungsi untuk membatalkan persetujuan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cancel_approval_id'])) {
    $cancelId = $_POST['cancel_approval_id'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Update status dan hapus informasi approval
        $updateSql = "UPDATE tata_usaha 
                     SET status = NULL,
                         approved_at = NULL,
                         approved_by = NULL
                     WHERE id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $cancelId);
        
        if (!$updateStmt->execute()) {
            throw new Exception("Error canceling approval: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect dengan pesan sukses
        $_SESSION['success_message'] = "Persetujuan berhasil dibatalkan!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
        exit();
        
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Modifikasi bagian penghapusan data
if (isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    try {
        $conn->begin_transaction();
        
        // Cek data yang akan dihapus
        $stmt = $conn->prepare("SELECT t.*, u.role 
                               FROM tata_usaha t 
                               LEFT JOIN users u ON t.user_id = u.id 
                               WHERE t.id = ?");
        $stmt->bind_param("i", $delete_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            // Jika admin yang menghapus data user lain, buat notifikasi
            if ($_SESSION['role'] === 'admin' && $data['user_id'] != $_SESSION['user_id']) {
                $notificationData = [
                    'tanggal' => $data['tanggal'],
                    'melaksanakan_tugas' => $data['melaksanakan_tugas'],
                    'hasil' => $data['output'],
                    'tugas_lainnya' => $data['tugas_lainnya'],
                    'catatan' => $data['catatan'],
                    'data_dukung_1' => $data['data_dukung_1'],
                    'data_dukung_2' => $data['data_dukung_2']
                ];
                
                // Simpan notifikasi untuk user yang datanya dihapus
                saveNotification($conn, $data['user_id'], $notificationData);
            }
            
            // Hapus file data dukung jika ada
            if (!empty($data['data_dukung_1'])) {
                $file_path = 'uploads/' . $data['data_dukung_1'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            if (!empty($data['data_dukung_2'])) {
                $file_path = 'uploads/' . $data['data_dukung_2'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Hapus data dari tabel revisions jika ada
            $deleteRevisionStmt = $conn->prepare("DELETE FROM revisions WHERE task_id = ?");
            $deleteRevisionStmt->bind_param("i", $delete_id);
            $deleteRevisionStmt->execute();
            
            // Proses penghapusan data dari tabel tata_usaha
            if ($_SESSION['role'] === 'admin') {
                // Admin dapat menghapus semua data
                $deleteStmt = $conn->prepare("DELETE FROM tata_usaha WHERE id = ?");
                $deleteStmt->bind_param("i", $delete_id);
            } else {
                // User menghapus data miliknya
                $deleteStmt = $conn->prepare("DELETE FROM tata_usaha WHERE id = ? AND user_id = ?");
                $deleteStmt->bind_param("ii", $delete_id, $_SESSION['user_id']);
            }
            
            if (!$deleteStmt->execute()) {
                throw new Exception("Gagal menghapus data: " . $deleteStmt->error);
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Data berhasil dihapus!";
            
        } else {
            throw new Exception("Data tidak ditemukan.");
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        error_log("Error in delete process: " . $e->getMessage());
    }
    
    // Redirect kembali ke halaman yang sama
    header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
    exit();
}

// Modifikasi bagian handler POST untuk revisi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['revisi_id'])) {
    $revisiId = $_POST['revisi_id'];
    $catatan = $_POST['catatan'];
    $status = $_POST['status'];
    
    try {
        $conn->begin_transaction();
        
        // Ambil data laporan yang akan direvisi
        $getTaskQuery = "SELECT t.*, u.id as user_id, u.username 
                        FROM tata_usaha t 
                        LEFT JOIN users u ON t.user_id = u.id 
                        WHERE t.id = ?";
        
        $getTaskStmt = $conn->prepare($getTaskQuery);
        if ($getTaskStmt === false) {
            throw new Exception("Error preparing task statement: " . $conn->error);
        }
        
        $getTaskStmt->bind_param("i", $revisiId);
        if (!$getTaskStmt->execute()) {
            throw new Exception("Error executing task query: " . $getTaskStmt->error);
        }
        
        $taskResult = $getTaskStmt->get_result();
        $taskData = $taskResult->fetch_assoc();
        
        if (!$taskData) {
            throw new Exception("Data laporan tidak ditemukan");
        }
        
        // Update status di tabel tata_usaha
        $updateTaskQuery = "UPDATE tata_usaha SET status = ?, status_user = ? WHERE id = ?";
        $updateTaskStmt = $conn->prepare($updateTaskQuery);
        if ($updateTaskStmt === false) {
            throw new Exception("Error preparing update statement: " . $conn->error);
        }
        
        $updateTaskStmt->bind_param("ssi", $status, $status, $revisiId);
        if (!$updateTaskStmt->execute()) {
            throw new Exception("Gagal mengupdate status di tata_usaha: " . $updateTaskStmt->error);
        }
        
        // Cek apakah sudah ada data di tabel revisions
        $checkQuery = "SELECT id FROM revisions WHERE task_id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        if ($checkStmt === false) {
            throw new Exception("Error preparing check statement: " . $conn->error);
        }
        
        $checkStmt->bind_param("i", $revisiId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        // Update atau insert ke tabel revisions dengan status yang sama
        if ($checkResult->num_rows > 0) {
            // Update existing revision
            $updateRevisionQuery = "UPDATE revisions SET catatan = ?, status = ? WHERE task_id = ?";
            $revisionStmt = $conn->prepare($updateRevisionQuery);
            if ($revisionStmt === false) {
                throw new Exception("Error preparing revision update statement: " . $conn->error);
            }
            
            $revisionStmt->bind_param("ssi", $catatan, $status, $revisiId);
        } else {
            // Insert new revision
            $insertRevisionQuery = "INSERT INTO revisions (task_id, catatan, status) VALUES (?, ?, ?)";
            $revisionStmt = $conn->prepare($insertRevisionQuery);
            if ($revisionStmt === false) {
                throw new Exception("Error preparing revision insert statement: " . $conn->error);
            }
            
            $revisionStmt->bind_param("iss", $revisiId, $catatan, $status);
        }
        
        if (!$revisionStmt->execute()) {
            throw new Exception("Gagal memperbarui data revisi: " . $revisionStmt->error);
        }
        
        // Buat notifikasi untuk user jika admin yang merevisi
        if ($_SESSION['role'] === 'admin' && $taskData['user_id']) {
            $notificationTitle = "Revisi tugas: " . $taskData['melaksanakan_tugas'];
            
            // Buat pesan notifikasi
            $message = $notificationTitle . "\n" . $formattedDate;
            
            // Data preview untuk tampilan detail
            $previewData = json_encode([
                'title' => $notificationTitle,
                'date' => $formattedDate,
                'status' => $status,
                'catatan' => $catatan
            ]);
            
            // Insert ke tabel notifications
            $insertNotifQuery = "INSERT INTO notifications (user_id, message, preview_data, is_read, created_at) 
                               VALUES (?, ?, ?, 0, NOW())";
            $insertNotifStmt = $conn->prepare($insertNotifQuery);
            if ($insertNotifStmt === false) {
                throw new Exception("Error preparing notification statement: " . $conn->error);
            }
            
            $insertNotifStmt->bind_param("iss", $taskData['user_id'], $message, $previewData);
            if (!$insertNotifStmt->execute()) {
                throw new Exception("Gagal membuat notifikasi: " . $insertNotifStmt->error);
            }
        }
        
        $conn->commit();
        $_SESSION['success_message'] = "Status dan catatan revisi berhasil diperbarui!";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
        error_log("Error in revision process: " . $e->getMessage());
    }
    
    // Redirect kembali ke halaman yang sama
    header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
    exit();
}

// Get role from users table for the current petugas
$sql_user = "SELECT role FROM users WHERE username = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("s", $petugas);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$current_user_role = '';

if ($result_user && $row_user = $result_user->fetch_assoc()) {
    $current_user_role = $row_user['role'];
}
$stmt_user->close();

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Get selected month and year from URL parameters, default to current
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : $currentMonth;
$selectedYear = isset($_GET['year']) ? $_GET['year'] : $currentYear;

// Modify the SQL query to include month and year filter
$sql = "SELECT t.*, 
        u.username as approved_by_name,
        u2.role as petugas_role,
        DATE_FORMAT(t.tanggal, '%d %M %Y') as formatted_date,
        DATE_FORMAT(t.approved_at, '%d %M %Y %H:%i') as approved_date,
        r.catatan as revision_note,
        r.status as revision_status
        FROM tata_usaha t
        LEFT JOIN users u ON t.approved_by = u.id 
        LEFT JOIN users u2 ON t.petugas = u2.username
        LEFT JOIN revisions r ON t.id = r.task_id
        WHERE t.petugas = ? 
        AND MONTH(t.tanggal) = ?
        AND YEAR(t.tanggal) = ?
        ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $petugas, $selectedMonth, $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

// Initialize arrays
$allData = [];        // For all data (admin view)
$belumDisetujui = []; // For user view
$sudahDisetujui = []; // For user view

// Process data based on current user's role
while ($row = $result->fetch_assoc()) {
    $row['data_dukung_1'] = basename($row['data_dukung_1']);
    $row['data_dukung_2'] = basename($row['data_dukung_2']);
    
    if ($current_user_role == 'admin') {
        // All data goes to single array for admin role users
        $allData[] = $row;
    } else {
        // Split data for non-admin role users
        if ($row['status'] == 'disetujui' || !empty($row['approved_by']) || !empty($row['approved_at'])) {
            $sudahDisetujui[] = $row;
        } else {
            $belumDisetujui[] = $row;
        }
    }
}

// Tambahkan fungsi helper untuk menentukan class status
function getStatusClass($status) {
    switch($status) {
        case 'Menunggu Revisi':
            return 'bg-warning text-dark';
            $statusText = 'Menunggu Revisi';
            break;
        case 'Sudah Direvisi':
            return 'bg-success text-white'; // sChanged to green
            $statusText = 'Sudah Direvisi';
            break;
        case 'disetujui':
            return 'bg-success text-white';
            $statusText = 'Disetujui';
            break;
        case 'ditolak':
            return 'bg-danger text-white';
            $statusText = 'Ditolak';
            break;
        case 'Menunggu Persetujuan':
            return 'bg-warning text-dark';
            $statusText = 'Menunggu Persetujuan'; // Changed text
            break;
        default:
            return 'bg-secondary text-white';
            $statusText = 'Menunggu Persetujuan';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kinerja Petugas</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .header {
            background: linear-gradient(135deg, #1663DE, #3B82F6);
            color: white;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(22, 99, 222, 0.2);
        }

        .container {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 15px rgba(22, 99, 222, 0.08);
        }

        .table {
            background: white;
            margin-top: 1rem;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }

        .table thead {
            background: linear-gradient(135deg, #1663DE, #3B82F6);
            color: white;
        }

        .table th,
        .table td {
            border: 1px solid #dee2e6;
        }

        .btn {
            margin: 0.2rem;
            border-radius: 5px;
        }

        .badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-weight: 500;
        }

        .nav-icons i {
            font-size: 1.3rem;
            margin-left: 1.5rem;
            cursor: pointer;
            color: white;
        }

        .card {
            margin-bottom: 2rem;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(22, 99, 222, 0.08);
            overflow: hidden;
        }

        .card-header {
            padding: 1rem 1.5rem;
            font-weight: 600;
            border-bottom: 1px solid #dee2e6;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .table td, .table th {
            vertical-align: middle;
            padding: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .data-dukung {
            position: relative;
            padding: 8px;
            border-radius: 4px;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px solid #dee2e6;
        }

        .file-name {
            display: block;
            margin-bottom: 4px;
            word-break: break-all;
            text-align: center;
        }

        .download-btn {
            position: absolute;
            opacity: 0;
            transition: opacity 0.3s ease;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            white-space: nowrap;
            z-index: 2;
        }

        .data-dukung:hover .file-name {
            opacity: 0.3;
        }

        .data-dukung:hover .download-btn {
            opacity: 1;
        }

        @media (max-width: 768px) {
            .data-dukung {
                min-height: auto;
                padding: 15px;
            }

            .download-btn {
                position: static;
                opacity: 1;
                transform: none;
                margin-top: 8px;
                width: auto;
            }

            .data-dukung:hover .file-name {
                opacity: 1;
            }

            .file-name {
                margin-bottom: 8px;
            }
        }

        .btn-group {
            display: flex;
            gap: 5px;
            flex-wrap: nowrap;
        }

        .btn-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .btn-group {
                flex-direction: column;
                width: 100%;
            }
            
            .btn-group .btn {
                width: 100%;
                margin-bottom: 0.25rem;
            }
            
            .table td {
                max-width: 200px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
        }

        .notification-item {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .notification-header {
            margin-bottom: 15px;
        }

        .notification-header h4 {
            margin: 0;
            color: #333;
            font-size: 18px;
        }

        .notification-date {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }

        .notification-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .notification-content p {
            margin: 10px 0;
        }

        .notification-content strong {
            color: #555;
        }
    </style>
    <script>
        function confirmApprove(id, task) {
            return Swal.fire({
                title: 'Konfirmasi Persetujuan',
                text: `Apakah Anda yakin ingin menyetujui tugas "${task}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#87CEEB',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Ya, Setujui',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('approveForm' + id).submit();
                }
            });
        }

        function confirmCancelApproval(id, task) {
            return Swal.fire({
                title: 'Konfirmasi Pembatalan',
                text: `Apakah Anda yakin ingin membatalkan persetujuan tugas "${task}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('cancelApprovalForm' + id).submit();
                }
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: "Apakah anda ingin hapus laporan ini?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form untuk menghapus
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = window.location.href;
                    
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'delete_id';
                    input.value = id;
                    
                    form.appendChild(input);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        $(document).ready(function() {
            // Animasi untuk pesan sukses/error
            $('.alert').fadeIn('slow').delay(3000).fadeOut('slow');
            
            // Tooltip
            $('[data-bs-toggle="tooltip"]').tooltip();
            
            // DataTable
            $('.table').DataTable({
                "pageLength": 10,
                "ordering": true,
                "responsive": true,
                "language": {
                    "search": "Cari:",
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Data tidak ditemukan",
                    "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data yang tersedia",
                    "infoFiltered": "(difilter dari _MAX_ total data)"
                }
            });
        });

        // Add this to your existing JavaScript
        function showRevisionModal(id, existingNote = '') {
            document.getElementById('revisi_id').value = id;
            document.getElementById('catatan').value = existingNote;
            document.getElementById('status').value = 'Menunggu Revisi'; // Set default status
            new bootstrap.Modal(document.getElementById('revisionModal')).show();
        }
    </script>
</head>
<body>
    <div class="header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0">Detail Kinerja Petugas</h4>
                </div>
                <div class="col-auto">
                    <div class="nav-icons">
                        <i class="fas fa-arrow-left" onclick="window.location.href='tata_usaha.php'" data-bs-toggle="tooltip" title="Kembali"></i>
                        <i class="fas fa-home" onclick="window.location.href='tata_usaha.php'" data-bs-toggle="tooltip" title="index"></i>
                        <i class="fas fa-sign-out-alt" onclick="window.location.href='logout.php'" data-bs-toggle="tooltip" title="Logout"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>
                <?= $_SESSION['success_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?= $_SESSION['error_message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="mb-0">
                <i class="fas fa-user me-2"></i>
                Petugas: <?= htmlspecialchars($petugas) ?>
            </h5>
            <div class="d-flex align-items-center">
                <form method="get" class="d-flex align-items-center">
                    <input type="hidden" name="petugas" value="<?= htmlspecialchars($petugas) ?>">
                    <select name="month" class="form-select me-2">
                        <?php for($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= sprintf("%02d", $m) ?>" <?= $selectedMonth == sprintf("%02d", $m) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="form-select me-2">
                        <?php 
                        $startYear = 2020;
                        $endYear = date('Y');
                        for($y = $endYear; $y >= $startYear; $y--): 
                        ?>
                            <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>>
                                <?= $y ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                </form>
                <a href="donload_rekap.php?petugas=<?= urlencode($petugas) ?>&month=<?= $selectedMonth ?>&year=<?= $selectedYear ?>" 
                   class="btn btn-primary">
                    <i class="fas fa-file-alt me-2"></i>Rekap
                </a>
            </div>
        </div>

        <?php if ($current_user_role == 'admin'): ?>
            <!-- Single table for admin role users -->
            <div class="card">
                <div class="card-header" style="background: #79BAEC; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-tasks me-2"></i>
                            Daftar Tugas
                        </h6>
                        <span class="badge bg-light text-primary"><?= count($allData) ?> tugas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tugas</th>
                                    <th>Output</th>
                                    <th>Tugas Lainnya</th>
                                    <th>Catatan</th>
                                    <th>Data Dukung 1</th>
                                    <th>Data Dukung 2</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allData as $row): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                                        <td><?= htmlspecialchars($row['melaksanakan_tugas']) ?></td>
                                        <td><?= htmlspecialchars($row['output']) ?></td>
                                        <td><?= htmlspecialchars($row['tugas_lainnya']) ?></td>
                                        <td><?= htmlspecialchars($row['catatan']) ?></td>
                                        <td>
                                            <?php if($row['data_dukung_1']): ?>
                                                <div class="data-dukung">
                                                    <span class="file-name"><?= htmlspecialchars($row['data_dukung_1']) ?></span>
                                                    <a href="uploads/<?= htmlspecialchars($row['data_dukung_1']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                        <i class="fas fa-download"></i> Unduh
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($row['data_dukung_2']): ?>
                                                <div class="data-dukung">
                                                    <span class="file-name"><?= htmlspecialchars($row['data_dukung_2']) ?></span>
                                                    <a href="uploads/<?= htmlspecialchars($row['data_dukung_2']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                        <i class="fas fa-download"></i> Unduh
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            // Ambil status dari kedua tabel (tata_usaha dan revisions)
                                            $status = '';
                                            if ($row['petugas_role'] === 'user') {
                                                // Untuk user, gunakan status dari tabel revisions
                                                $revisionQuery = "SELECT status FROM revisions WHERE task_id = ?";
                                                $stmtRevision = $conn->prepare($revisionQuery);
                                                $stmtRevision->bind_param("i", $row['id']);
                                                $stmtRevision->execute();
                                                $revisionResult = $stmtRevision->get_result();
                                                
                                                if ($revisionResult->num_rows > 0) {
                                                    $status = $revisionResult->fetch_assoc()['status'];
                                                } else {
                                                    $status = $row['status_user'];
                                                }
                                            } else {
                                                // Untuk admin, gunakan status dari tata_usaha
                                                $status = $row['status'];
                                            }
                                            
                                            // Tentukan class dan text berdasarkan status
                                            switch($status) {
                                                case 'Menunggu Revisi':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    $statusText = 'Menunggu Revisi';
                                                    break;
                                                case 'Sudah Direvisi':
                                                    $badgeClass = 'bg-info text-white';
                                                    $statusText = 'Sudah Direvisi';
                                                    break;
                                                case 'disetujui':
                                                    $badgeClass = 'bg-success text-white';
                                                    $statusText = 'Disetujui';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-secondary text-white';
                                                    $statusText = 'Menunggu Persetujuan';
                                            }
                                            ?>
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= htmlspecialchars($statusText) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <?php if ($_SESSION['user_id'] == $row['user_id'] || $userRole == 'admin'): ?>
                                                    <?php if ($userRole == 'admin'): ?>
                                                        <form id="approveForm<?= $row['id'] ?>" method="post" class="d-inline">
                                                            <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                                                            <button type="button" 
                                                                    onclick="confirmApprove(<?= $row['id'] ?>, '<?= htmlspecialchars($row['melaksanakan_tugas']) ?>')" 
                                                                    class="btn btn-success" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Setujui">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($userRole == 'user' || $userRole == 'admin'): ?>
                                                        <a href="edit_disetujui1.php?id=<?= $row['id'] ?>" 
                                                           class="btn btn-primary" 
                                                           data-bs-toggle="tooltip" 
                                                           title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>

                                                        <!-- Modified delete button for both admin and user -->
                                                        <form method="post" class="d-inline">
                                                            <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                            <button type="button" 
                                                                    onclick="confirmDelete(<?= $row['id'] ?>)" 
                                                                    class="btn btn-danger" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Hapus">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <?php if ($userRole == 'admin'): ?>
                                                        <button type="button" 
                                                                class="btn btn-info" 
                                                                onclick="showRevisionModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['revision_note'] ?? '') ?>', 'Menunggu Revisi')" 
                                                                data-bs-toggle="tooltip" 
                                                                title="Revisi">
                                                            <i class="fas fa-sync-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Two tables for non-admin role users -->
            <div class="card">
                <div class="card-header" style="background: #79BAEC; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-clock me-2"></i>
                            Belum Disetujui
                        </h6>
                        <span class="badge bg-light text-primary"><?= count($belumDisetujui) ?> tugas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tugas</th>
                                    <th>Output</th>
                                    <th>Tugas Lainnya</th>
                                    <th>Catatan</th>
                                    <th>Data Dukung 1</th>
                                    <th>Data Dukung 2</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($belumDisetujui)): ?>
                                    <?php foreach ($belumDisetujui as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                                            <td><?= htmlspecialchars($row['melaksanakan_tugas']) ?></td>
                                            <td><?= htmlspecialchars($row['output']) ?></td>
                                            <td><?= htmlspecialchars($row['tugas_lainnya']) ?></td>
                                            <td><?= htmlspecialchars($row['catatan']) ?></td>
                                            <td>
                                                <?php if($row['data_dukung_1']): ?>
                                                    <div class="data-dukung">
                                                        <span class="file-name"><?= htmlspecialchars($row['data_dukung_1']) ?></span>
                                                        <a href="uploads/<?= htmlspecialchars($row['data_dukung_1']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($row['data_dukung_2']): ?>
                                                    <div class="data-dukung">
                                                        <span class="file-name"><?= htmlspecialchars($row['data_dukung_2']) ?></span>
                                                        <a href="uploads/<?= htmlspecialchars($row['data_dukung_2']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($row['petugas_role'] === 'user') {
                                                    echo '<span class="badge ' . getStatusClass($row['status_user']) . '">';
                                                    echo htmlspecialchars($row['status_user'] ?: 'Menunggu Persetujuan');
                                                    echo '</span>';
                                                } else {
                                                    echo '<span class="badge ' . getStatusClass($row['status']) . '">';
                                                    echo htmlspecialchars($row['status'] ?: 'Menunggu Persetujuan');
                                                    echo '</span>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <?php if ($_SESSION['user_id'] == $row['user_id'] || $userRole == 'admin'): ?>
                                                        <?php if ($userRole == 'admin'): ?>
                                                            <form id="approveForm<?= $row['id'] ?>" method="post" class="d-inline">
                                                                <input type="hidden" name="approve_id" value="<?= $row['id'] ?>">
                                                                <button type="button" 
                                                                        onclick="confirmApprove(<?= $row['id'] ?>, '<?= htmlspecialchars($row['melaksanakan_tugas']) ?>')" 
                                                                        class="btn btn-success" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Setujui">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if ($userRole == 'user' || $userRole == 'admin'): ?>
                                                            <a href="edit_disetujui1.php?id=<?= $row['id'] ?>" 
                                                               class="btn btn-primary" 
                                                               data-bs-toggle="tooltip" 
                                                               title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>

                                                            <!-- Modified delete button for both admin and user -->
                                                            <form method="post" class="d-inline">
                                                                <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                                                <button type="button" 
                                                                        onclick="confirmDelete(<?= $row['id'] ?>)" 
                                                                        class="btn btn-danger" 
                                                                        data-bs-toggle="tooltip" 
                                                                        title="Hapus">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>

                                                        <?php if ($userRole == 'admin'): ?>
                                                            <button type="button" 
                                                                    class="btn btn-info" 
                                                                    onclick="showRevisionModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['revision_note'] ?? '') ?>', 'Menunggu Revisi')" 
                                                                    data-bs-toggle="tooltip" 
                                                                    title="Revisi">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="empty-state">
                                            <i class="fas fa-clipboard-check"></i>
                                            <p>Tidak ada tugas yang menunggu persetujuan.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header" style="background: #79BAEC; color: white;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            Sudah Disetujui
                        </h6>
                        <span class="badge bg-light text-primary"><?= count($sudahDisetujui) ?> tugas</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tugas</th>
                                    <th>Output</th>
                                    <th>Tugas Lainnya</th>
                                    <th>Catatan</th>
                                    <th>Data Dukung 1</th>
                                    <th>Data Dukung 2</th>
                                    <th>Disetujui Oleh</th>
                                    <th>Tanggal Disetujui</th>
                                    <?php if ($userRole == 'admin'): ?>
                                        <th>Aksi</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($sudahDisetujui)): ?>
                                    <?php foreach ($sudahDisetujui as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['formatted_date']) ?></td>
                                            <td><?= htmlspecialchars($row['melaksanakan_tugas']) ?></td>
                                            <td><?= htmlspecialchars($row['output']) ?></td>
                                            <td><?= htmlspecialchars($row['tugas_lainnya']) ?></td>
                                            <td><?= htmlspecialchars($row['catatan']) ?></td>
                                            <td>
                                                <?php if($row['data_dukung_1']): ?>
                                                    <div class="data-dukung">
                                                        <span class="file-name"><?= htmlspecialchars($row['data_dukung_1']) ?></span>
                                                        <a href="uploads/<?= htmlspecialchars($row['data_dukung_1']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($row['data_dukung_2']): ?>
                                                    <div class="data-dukung">
                                                        <span class="file-name"><?= htmlspecialchars($row['data_dukung_2']) ?></span>
                                                        <a href="uploads/<?= htmlspecialchars($row['data_dukung_2']) ?>" class="btn btn-info btn-sm download-btn" download>
                                                            <i class="fas fa-download"></i> Unduh
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>Admin</td>
                                            <td><?= htmlspecialchars($row['approved_date']) ?></td>
                                            <?php if ($userRole == 'admin'): ?>
                                                <td>
                                                    <form id="cancelApprovalForm<?= $row['id'] ?>" method="post" class="d-inline">
                                                        <input type="hidden" name="cancel_approval_id" value="<?= $row['id'] ?>">
                                                        <button type="button" onclick="confirmCancelApproval(<?= $row['id'] ?>, '<?= htmlspecialchars($row['melaksanakan_tugas']) ?>')" 
                                                                class="btn btn-warning btn-sm" data-bs-toggle="tooltip" title="Batalkan Persetujuan">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="10" class="empty-state">
                                            <i class="fas fa-tasks"></i>
                                            <p>Belum ada tugas yang disetujui.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Modal for Revision -->
    <div class="modal fade" id="revisionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revisi Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="revisionForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="revisi_id" id="revisi_id">
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan Revisi</label>
                            <textarea class="form-control" name="catatan" id="catatan" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="Menunggu Revisi">Menunggu Revisi</option>
                                <option value="Sudah Direvisi">Sudah Direvisi</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tambahkan Modal Revisi khusus untuk User -->
    <div class="modal fade" id="userRevisionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Revisi Tugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="userRevisionForm" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="revisi_id" id="user_revisi_id">
                        <input type="hidden" name="status_revisi" value="Sudah Direvisi">
                        <div class="mb-3">
                            <label for="catatan" class="form-label">Catatan Revisi</label>
                            <textarea class="form-control" name="catatan_revisi" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" name="submit_user_revision" class="btn btn-primary">Simpan Revisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Tambahkan JavaScript untuk modal user -->
    <script>
    function showUserRevisionModal(id) {
        document.getElementById('user_revisi_id').value = id;
        new bootstrap.Modal(document.getElementById('userRevisionModal')).show();
    }
    </script>

    <?php
    // Tambahkan handler untuk revisi user
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_user_revision'])) {
        $revisiId = $_POST['revisi_id'];
        $catatan = $_POST['catatan_revisi'];
        $status = 'Sudah Direvisi'; // Status tetap untuk user
        
        try {
            $conn->begin_transaction();
            
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
            $updateStatusStmt = $conn->prepare("UPDATE tata_usaha SET status_user = ? WHERE id = ?");
            $updateStatusStmt->bind_param("si", $status, $revisiId);
            
            if (!$updateStatusStmt->execute()) {
                throw new Exception("Failed to update task status");
            }
            
            $conn->commit();
            
            $_SESSION['success_message'] = "Status revisi berhasil diperbarui!";
            header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in user revision process: " . $e->getMessage());
        }
    }
    ?>

    <?php
    // Tambahkan handler untuk update status user
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_user_status'])) {
        $taskId = $_POST['user_revisi_id'];
        $newStatus = $_POST['new_status'];
        
        try {
            $conn->begin_transaction();
            
            // Update status di tabel tata_usaha
            $updateTaskStmt = $conn->prepare("UPDATE tata_usaha SET status_user = ? WHERE id = ?");
            $updateTaskStmt->bind_param("si", $newStatus, $taskId);
            
            if (!$updateTaskStmt->execute()) {
                throw new Exception("Gagal mengupdate status di tata_usaha");
            }
            
            // Update status di tabel revisions
            $updateRevisionStmt = $conn->prepare("UPDATE revisions SET status = ? WHERE task_id = ?");
            $updateRevisionStmt->bind_param("si", $newStatus, $taskId);
            
            if (!$updateRevisionStmt->execute()) {
                throw new Exception("Gagal mengupdate status di revisions");
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "Status berhasil diperbarui menjadi Sudah Revisi!";
            
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error_message'] = "Error: " . $e->getMessage();
            error_log("Error in updating revision status: " . $e->getMessage());
        }
        
        // Redirect kembali ke halaman yang sama
        header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
        exit();
    }
    ?>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>