<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];

// Proses penghapusan notifikasi yang sudah dibaca
if (isset($_POST['clear_notifications'])) {
    $deleteStmt = $conn->prepare("DELETE FROM notifications WHERE user_id = :user_id AND is_read = TRUE");
    $deleteStmt->execute(['user_id' => $userId]);
    header('Location: notifications.php');
    exit();
}

// Ambil notifikasi hanya untuk user yang sedang login
$stmt = $conn->prepare("
    SELECT n.* 
    FROM notifications n
    WHERE n.user_id = :user_id 
    ORDER BY n.created_at DESC
");
$stmt->execute(['user_id' => $userId]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tandai semua notifikasi user ini sebagai telah dibaca
$updateStmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = :user_id");
$updateStmt->execute(['user_id' => $userId]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
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
            background: #1663DE;
            color: #ffffff;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            animation: slideDown 0.5s ease-out;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        @keyframes slideDown {
            from { transform: translateY(-100%); }
            to { transform: translateY(0); }
        }
        .header h1 {
            margin: 0;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            font-size: 28px;
            font-weight: 600;
        }
        .back-button {
            color: white;
            font-size: 24px;
            text-decoration: none;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
        }
        .back-button:hover {
            transform: translateX(-5px);
        }
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 30px;
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
        .notification-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }
        .notification-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .notification-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .notification-icon {
            background: #1663DE;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .notification-time {
            color: #666;
            font-size: 0.9em;
        }
        .preview-data {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .preview-title {
            color: #1663DE;
            font-size: 1.1em;
            font-weight: 600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }
        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .preview-item {
            margin-bottom: 15px;
        }
        .preview-item strong {
            color: #444;
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .preview-item span,
        .preview-item p {
            color: #666;
            line-height: 1.5;
            display: block;
        }
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 48px;
            color: #1663DE;
            margin-bottom: 20px;
        }
        .clear-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            margin-left: auto;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .clear-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .clear-button i {
            font-size: 14px;
        }

        .delete-button {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e0e0e0;
            z-index: 1;
        }

        .delete-button:hover {
            background: #dc3545;
            color: white;
            transform: rotate(90deg);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .delete-button i {
            font-size: 14px;
        }

        /* Custom style untuk SweetAlert */
        .swal-custom-popup {
            font-family: 'Poppins', sans-serif !important;
            padding: 2em;
        }

        .swal-custom-title {
            font-size: 1.5em !important;
            font-weight: 600 !important;
            color: #333 !important;
        }

        .swal-custom-content {
            font-size: 1.1em !important;
            color: #666 !important;
        }

        .swal-custom-confirm {
            padding: 12px 30px !important;
            font-weight: 500 !important;
            letter-spacing: 0.5px !important;
            color: white !important;
        }

        .swal-custom-cancel {
            padding: 12px 30px !important;
            font-weight: 500 !important;
            letter-spacing: 0.5px !important;
            color: white !important;
        }

        .swal2-popup {
            border-radius: 15px !important;
        }

        .swal2-actions {
            margin-top: 2em !important;
        }

        .swal2-confirm, .swal2-cancel {
            color: white !important;
        }

        @media (max-width: 768px) {
            .header {
                padding: 20px 15px;
            }

            .header h1 {
                font-size: 20px;
            }

            .clear-button {
                padding: 6px 12px;
                font-size: 12px;
            }

            .clear-button i {
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="tata_usaha.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1>Notifikasi</h1>
        <?php if (!empty($notifications)): ?>
            <button type="button" onclick="confirmClearAll()" class="clear-button">
                <i class="fas fa-trash-alt"></i> Hapus semua notifikasi
            </button>
            <form id="clearNotificationsForm" method="post" style="display: none;">
                <input type="hidden" name="clear_notifications" value="1">
            </form>
        <?php endif; ?>
    </div>

    <div class="container">
        <?php if (empty($notifications)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <p>Tidak ada notifikasi</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-card" data-notification-id="<?= $notification['id'] ?>">
                    <div class="delete-button" onclick="deleteNotification(<?= $notification['id'] ?>)">
                        <i class="fas fa-times"></i>
                    </div>
                    <div class="notification-header">
                        <div class="notification-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800">
                                <?= htmlspecialchars($notification['message']) ?>
                            </p>
                            <p class="notification-time">
                                <?= date('d M Y H:i', strtotime($notification['created_at'])) ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($notification['preview_data']): ?>
                        <?php 
                        $previewData = json_decode($notification['preview_data'], true);
                        // Cek apakah ini notifikasi penghapusan atau revisi
                        $isDeleteNotification = strpos($notification['message'], 'dihapus') !== false;
                        ?>
                        
                        <div class="preview-data">
                            <?php if ($isDeleteNotification): ?>
                                <!-- Tampilan untuk notifikasi penghapusan -->
                                <div class="preview-grid">
                                    <div class="preview-item">
                                        <strong>Tanggal:</strong> 
                                        <?= htmlspecialchars($previewData['tanggal'] ?? '-') ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Tugas:</strong> 
                                        <?= htmlspecialchars($previewData['melaksanakan_tugas'] ?? '-') ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Output:</strong> 
                                        <?= htmlspecialchars($previewData['output'] ?? '-') ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Tugas Lainnya:</strong> 
                                        <?= htmlspecialchars($previewData['tugas_lainnya'] ?? '-') ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Catatan:</strong> 
                                        <?= htmlspecialchars($previewData['catatan'] ?? '-') ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Data Dukung 1:</strong> 
                                        <?php if (!empty($previewData['data_dukung_1'])): ?>
                                            <span><?= htmlspecialchars($previewData['data_dukung_1']) ?></span>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="preview-item">
                                        <strong>Data Dukung 2:</strong> 
                                        <?php if (!empty($previewData['data_dukung_2'])): ?>
                                            <span><?= htmlspecialchars($previewData['data_dukung_2']) ?></span>
                                        <?php else: ?>
                                            <span>-</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- Tampilan untuk notifikasi revisi -->
                                <div class="preview-data">
                                    <div class="preview-item">
                                        <?php if (isset($previewData['status'])): ?>
                                            <?php 
                                            // Cek apakah status adalah array atau string
                                            $status = is_array($previewData['status']) ? $previewData['status']['value'] : $previewData['status'];
                                            ?>
                                            <p><strong>Status:</strong> 
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if (isset($previewData['catatan'])): ?>
                                            <?php 
                                            // Cek apakah catatan adalah array atau string
                                            $catatan = is_array($previewData['catatan']) ? $previewData['catatan']['value'] : $previewData['catatan'];
                                            ?>
                                            <p><strong>Catatan:</strong> 
                                                <?= htmlspecialchars($catatan) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-material-ui/material-ui.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    function deleteNotification(id) {
        Swal.fire({
            title: 'Hapus Notifikasi?',
            text: "Anda yakin ingin menghapus notifikasi ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1663DE',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                content: 'swal-custom-content',
                confirmButton: 'swal-custom-confirm',
                cancelButton: 'swal-custom-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('delete_notification.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'id=' + id
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hapus elemen notifikasi dari DOM
                        const notificationCard = document.querySelector(`[data-notification-id="${id}"]`);
                        if (notificationCard) {
                            notificationCard.remove();
                        }
                        // Reload halaman jika tidak ada notifikasi tersisa
                        if (document.querySelectorAll('.notification-card').length === 0) {
                            location.reload();
                        }
                    }
                });
            }
        });
    }

    function confirmClearAll() {
        Swal.fire({
            title: 'Hapus Semua Notifikasi?',
            text: "Anda yakin ingin menghapus semua notifikasi yang sudah dibaca?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#1663DE',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'swal-custom-popup',
                title: 'swal-custom-title',
                content: 'swal-custom-content',
                confirmButton: 'swal-custom-confirm',
                cancelButton: 'swal-custom-cancel'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('clearNotificationsForm').submit();
            }
        });
    }
    </script>
</body>
</html> 