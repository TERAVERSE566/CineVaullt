<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentId = $_POST['content_id'] ?? 0;
    $currentTime = $_POST['current_time'] ?? 0;
    
    if (!$contentId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid content ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO watch_history (user_id, content_id, paused_at_seconds) 
            VALUES (?, ?, ?) 
            ON DUPLICATE KEY UPDATE paused_at_seconds = VALUES(paused_at_seconds)
        ");
        $stmt->execute([$_SESSION['user_id'], $contentId, $currentTime]);
        
        echo json_encode(['status' => 'success']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database exception']);
    }
}
?>
