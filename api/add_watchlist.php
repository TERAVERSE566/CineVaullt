<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$contentId = $_POST['content_id'] ?? 0;

if (!$contentId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid ID']);
    exit;
}

try {
    // Check if it already exists
    $stmt = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$userId, $contentId]);
    
    if ($stmt->rowCount() > 0) {
        // Toggle off
        $del = $pdo->prepare("DELETE FROM watchlist WHERE user_id = ? AND content_id = ?");
        $del->execute([$userId, $contentId]);
        echo json_encode(['status' => 'removed']);
    } else {
        // Toggle on
        $ins = $pdo->prepare("INSERT INTO watchlist (user_id, content_id) VALUES (?, ?)");
        $ins->execute([$userId, $contentId]);
        echo json_encode(['status' => 'added']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error']);
}
?>
