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

// Ambil nama petugas dari URL
$petugas = isset($_GET['petugas']) ? $_GET['petugas'] : '';

// Get selected month and year from URL parameters, default to current month/year if not set
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fungsi untuk menghapus tugas
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $deleteId = $_POST['delete_id'];
    
    // Mulai transaction
    $conn->begin_transaction();
    
    try {
        // Hapus data dari database
        $deleteSql = "DELETE FROM tata_usaha WHERE id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("i", $deleteId);
        
        if (!$deleteStmt->execute()) {
            throw new Exception("Error deleting record: " . $conn->error);
        }
        
        // Commit transaction
        $conn->commit();
        
        // Redirect dengan pesan sukses
        $_SESSION['success_message'] = "Laporan berhasil dihapus!";
        header("Location: " . $_SERVER['PHP_SELF'] . "?petugas=" . urlencode($petugas));
        exit();
        
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $_SESSION['error_message'] = "Error: " . $e->getMessage();
    }
}

// Get data from tata_usaha table with month and year filter
$sql = "SELECT t.*, 
        u.username as approved_by_name,
        u.role as user_role,
        DATE_FORMAT(t.tanggal, '%d %M %Y') as formatted_date,
        DATE_FORMAT(t.approved_at, '%d %M %Y %H:%i') as approved_date,
        CASE 
            WHEN t.status IS NULL THEN 'konsep'
            ELSE t.status 
        END as status
        FROM tata_usaha t
        LEFT JOIN users u ON t.approved_by = u.id 
        WHERE t.petugas = ? 
        AND MONTH(t.tanggal) = ? 
        AND YEAR(t.tanggal) = ?
        ORDER BY t.tanggal DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $petugas, $selectedMonth, $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

// Initialize array for admin data
$allData = [];

// Process data
while ($row = $result->fetch_assoc()) {
    $row['data_dukung_1'] = basename($row['data_dukung_1']);
    $row['data_dukung_2'] = basename($row['data_dukung_2']);
    $allData[] = $row;
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
    </style>
    <script>
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
                <a href="donload_rekap.php?petugas=<?= urlencode($petugas) ?>&month=<?= $selectedMonth ?>&year=<?= $selectedYear ?>" class="btn btn-info">
                    <i class="fas fa-file-alt me-2"></i>Rekap
                </a>
            </div>
        </div>

        <!-- Single table for admin's data -->
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
                                        <span class="badge" style="background-color: <?php
                                            switch($row['status']) {
                                                case 'sudahselesai':
                                                case 'disetujui':
                                                    echo '#28a745'; // Hijau untuk sudah selesai
                                                    $statusText = 'Sudah Selesai';
                                                    break;
                                                case 'belum_selesai':
                                                case 'belum_disetujui':
                                                    echo '#ffa500'; // Orange untuk belum selesai
                                                    $statusText = 'Belum Selesai';
                                                    break;
                                                case 'konsep':
                                                    echo '#6f42c1'; // Ungu untuk konsep
                                                    $statusText = 'Konsep';
                                                    break;
                                                default:
                                                    echo '#ffa500';
                                                    $statusText = 'Belum Selesai';
                                                    break;
                                            }
                                        ?>;">
                                            <?= $statusText ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_disetujui.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm" data-bs-toggle="tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?= $row['id'] ?>)" data-bs-toggle="tooltip" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>