<?php
// api/get_notifications.php — Fetch user notifications
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'notifications' => [], 'unread' => 0]);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 15");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll();

    $unread = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unread->execute([$userId]);
    $unreadCount = (int)$unread->fetchColumn();

    echo json_encode(['status' => 'ok', 'notifications' => $notifications, 'unread' => $unreadCount]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'notifications' => [], 'unread' => 0]);
}
?>
