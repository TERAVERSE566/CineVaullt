<?php
// api/upload_media.php — handles image/video file uploads
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

require_once 'db_connect.php';

// Verify admin
$chk = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$chk->execute([$_SESSION['user_id']]);
if ($chk->fetchColumn() !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']);
    exit;
}

$type = $_GET['type'] ?? 'poster'; // 'poster' or 'video'

if (empty($_FILES['file']['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file received']);
    exit;
}

$file     = $_FILES['file'];
$ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$maxSize  = ($type === 'video') ? 500 * 1024 * 1024 : 10 * 1024 * 1024; // 500MB video, 10MB image

if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'File too large.']);
    exit;
}

if ($type === 'video') {
    $allowed = ['mp4', 'mkv', 'webm', 'mov', 'avi'];
    $dir     = '/var/www/html/uploads/videos/';
    $urlBase = '/uploads/videos/';
} else {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $dir     = '/var/www/html/uploads/posters/';
    $urlBase = '/uploads/posters/';
}

// Local path for development
if (!is_dir($dir)) {
    $dir = __DIR__ . '/../uploads/' . ($type === 'video' ? 'videos' : 'posters') . '/';
    $urlBase = 'uploads/' . ($type === 'video' ? 'videos' : 'posters') . '/';
}

if (!in_array($ext, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type: ' . $ext]);
    exit;
}

// Create upload dir
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

$safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
$destPath = $dir . $safeName;

if (move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['status' => 'success', 'url' => $urlBase . $safeName, 'filename' => $safeName]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
}
