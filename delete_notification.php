<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$notificationId = $_POST['id'];
$userId = $_SESSION['user_id'];

try {
    // Pastikan hanya menghapus notifikasi milik user yang sedang login
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = :id AND user_id = :user_id");
    $result = $stmt->execute([
        'id' => $notificationId,
        'user_id' => $userId
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete notification']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 