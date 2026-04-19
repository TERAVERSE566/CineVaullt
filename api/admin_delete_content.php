<?php
// api/admin_delete_content.php — Delete content (admin only)
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
if ($check->fetchColumn() !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin only']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

try {
    $pdo->prepare("DELETE FROM content WHERE id = ?")->execute([$id]);
    echo json_encode(['status' => 'success', 'message' => 'Content deleted']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
