<?php
// api/mark_notification_read.php — Mark notifications as read
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")->execute([$userId]);
    echo json_encode(['status' => 'ok']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error']);
}
?>
