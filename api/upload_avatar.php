<?php
// api/upload_avatar.php — Profile picture upload handler
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Login required']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'No file uploaded']);
    exit;
}

$userId  = $_SESSION['user_id'];
$file    = $_FILES['avatar'];
$allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// Validate
if (!in_array($file['type'], $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Only JPG, PNG, GIF, WEBP allowed']);
    exit;
}

if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'Max file size is 5MB']);
    exit;
}

// Save
$ext     = pathinfo($file['name'], PATHINFO_EXTENSION);
$dir     = __DIR__ . '/../uploads/avatars/';
if (!is_dir($dir)) mkdir($dir, 0755, true);
$filename = 'user_' . $userId . '_' . time() . '.' . $ext;
$dest    = $dir . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['status' => 'error', 'message' => 'Upload failed']);
    exit;
}

$url = 'uploads/avatars/' . $filename;

try {
    $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?")->execute([$url, $userId]);
    $_SESSION['avatar_url'] = $url;
    echo json_encode(['status' => 'success', 'avatar_url' => $url]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
