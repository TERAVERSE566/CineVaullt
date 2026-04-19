<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contentId = $_POST['content_id'] ?? 0;
    $commentText = trim($_POST['comment_text'] ?? '');

    if (empty($contentId) || empty($commentText)) {
        echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comments (content_id, user_id, comment_text) VALUES (?, ?, ?)");
        $stmt->execute([$contentId, $_SESSION['user_id'], $commentText]);
        
        $newCommentId = $pdo->lastInsertId();
        echo json_encode([
            'status'     => 'success',
            'username'   => $_SESSION['username'],
            'initials'   => strtoupper(substr($_SESSION['username'], 0, 1)),
            'comment_id' => (int)$newCommentId
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
