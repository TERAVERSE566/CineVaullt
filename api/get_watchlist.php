<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, c.poster_url, c.release_year, c.duration 
        FROM watchlist w 
        JOIN content c ON w.content_id = c.id 
        WHERE w.user_id = ? 
        ORDER BY w.added_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    echo json_encode([]);
}
?>
