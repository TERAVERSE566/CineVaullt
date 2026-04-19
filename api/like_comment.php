<?php
// api/like_comment.php — Toggle comment like
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

$userId    = $_SESSION['user_id'];
$commentId = (int)($_POST['comment_id'] ?? 0);

if (!$commentId) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid comment']);
    exit;
}

try {
    // Check if already liked
    $check = $pdo->prepare("SELECT id FROM comment_likes WHERE user_id = ? AND comment_id = ?");
    $check->execute([$userId, $commentId]);

    if ($check->rowCount() > 0) {
        // Unlike
        $pdo->prepare("DELETE FROM comment_likes WHERE user_id = ? AND comment_id = ?")->execute([$userId, $commentId]);
        $pdo->prepare("UPDATE comments SET likes_count = GREATEST(0, likes_count - 1) WHERE id = ?")->execute([$commentId]);
        $action = 'unliked';
    } else {
        // Like
        $pdo->prepare("INSERT INTO comment_likes (user_id, comment_id) VALUES (?, ?)")->execute([$userId, $commentId]);
        $pdo->prepare("UPDATE comments SET likes_count = likes_count + 1 WHERE id = ?")->execute([$commentId]);
        $action = 'liked';
    }

    $likeCount = $pdo->prepare("SELECT likes_count FROM comments WHERE id = ?");
    $likeCount->execute([$commentId]);
    $count = $likeCount->fetchColumn();

    echo json_encode(['status' => $action, 'likes' => (int)$count]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
