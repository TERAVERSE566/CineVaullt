<?php
// api/rate_content.php — User Ratings API
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

$userId    = $_SESSION['user_id'];
$contentId = (int)($_POST['content_id'] ?? 0);
$rating    = (int)($_POST['rating']     ?? 0);

if (!$contentId || $rating < 1 || $rating > 10) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO user_ratings (user_id, content_id, rating) VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), rated_at = CURRENT_TIMESTAMP");
    $stmt->execute([$userId, $contentId, $rating]);

    // Get new community average
    $avg = $pdo->prepare("SELECT ROUND(AVG(rating),1) as avg_rating, COUNT(*) as total FROM user_ratings WHERE content_id = ?");
    $avg->execute([$contentId]);
    $result = $avg->fetch();

    echo json_encode([
        'status'     => 'success',
        'message'    => 'Rating saved!',
        'avg_rating' => $result['avg_rating'],
        'total'      => $result['total'],
        'your_rating'=> $rating
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
