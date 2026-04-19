<?php
// api/upload_media.php — handles image/video uploads for admin (chunked + simple)
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

require_once 'db_connect.php';

$chk = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$chk->execute([$_SESSION['user_id']]);
if ($chk->fetchColumn() !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$type = $_GET['type'] ?? 'poster'; // 'poster' or 'video'

// ── Determine paths (works on both Windows/XAMPP and Linux) ──────────────────
$projectRoot = realpath(__DIR__ . '/..');   // e.g. C:/xampp/htdocs/moviz
$subDir      = ($type === 'video') ? 'videos' : 'posters';
$uploadDir   = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR;

// Detect the web path automatically (handles both /moviz/ and /)
$scriptPath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])); // e.g. /moviz/api
$webRoot    = rtrim(dirname($scriptPath), '/'); // e.g. /moviz
$urlBase    = $webRoot . '/uploads/' . $subDir . '/'; // e.g. /moviz/uploads/videos/

// Create folder if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ── Allowed types & size limits ──────────────────────────────────────────────
if ($type === 'video') {
    $allowed = ['mp4', 'mkv', 'webm', 'mov', 'avi'];
    $maxSize = 2048 * 1024 * 1024; // 2 GB
} else {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxSize = 20 * 1024 * 1024; // 20 MB
}

// ── Chunked upload support ───────────────────────────────────────────────────
$chunkIndex  = isset($_POST['chunk_index'])  ? (int)$_POST['chunk_index']  : -1;
$totalChunks = isset($_POST['total_chunks']) ? (int)$_POST['total_chunks'] : 1;
$uploadId    = isset($_POST['upload_id'])    ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['upload_id']) : '';

if (empty($_FILES['file']['name'])) {
    echo json_encode(['status' => 'error', 'message' => 'No file received']); exit;
}

$file    = $_FILES['file'];
$origExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($origExt, $allowed)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type: ' . $origExt]); exit;
}

// ── CHUNKED MODE ─────────────────────────────────────────────────────────────
if ($chunkIndex >= 0 && $uploadId !== '') {
    $chunkDir = $uploadDir . 'tmp_' . $uploadId . DIRECTORY_SEPARATOR;
    if (!is_dir($chunkDir)) mkdir($chunkDir, 0777, true);

    $chunkFile = $chunkDir . 'chunk_' . $chunkIndex;
    if (!move_uploaded_file($file['tmp_name'], $chunkFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save chunk ' . $chunkIndex]); exit;
    }

    // Check if all chunks arrived
    $arrived = glob($chunkDir . 'chunk_*');
    if (count($arrived) < $totalChunks) {
        echo json_encode(['status' => 'chunk_ok', 'chunk' => $chunkIndex, 'total' => $totalChunks]); exit;
    }

    // ── Assemble final file ───────────────────────────────────────────────────
    $origName  = isset($_POST['original_name']) ? preg_replace('/[^a-zA-Z0-9_.-]/', '_', $_POST['original_name']) : 'upload';
    $safeName  = time() . '_' . $origName;
    $finalPath = $uploadDir . $safeName;
    $out       = fopen($finalPath, 'wb');
    for ($i = 0; $i < $totalChunks; $i++) {
        $in = fopen($chunkDir . 'chunk_' . $i, 'rb');
        while ($buf = fread($in, 65536)) fwrite($out, $buf);
        fclose($in);
    }
    fclose($out);

    // Cleanup temp chunks
    array_map('unlink', glob($chunkDir . 'chunk_*'));
    rmdir($chunkDir);

    echo json_encode(['status' => 'success', 'url' => $urlBase . $safeName, 'filename' => $safeName]); exit;
}

// ── SIMPLE MODE (small files) ─────────────────────────────────────────────────
if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'File too large (' . round($file['size']/1048576) . ' MB). Max: ' . round($maxSize/1048576) . ' MB']); exit;
}

$safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $file['name']);
$destPath = $uploadDir . $safeName;

if (move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['status' => 'success', 'url' => $urlBase . $safeName, 'filename' => $safeName]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save file. Check folder permissions: ' . $uploadDir]);
}
