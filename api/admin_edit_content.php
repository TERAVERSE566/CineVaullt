<?php
// api/admin_edit_content.php — Edit existing content (admin only)
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Verify admin role
$check = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
if ($check->fetchColumn() !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Admin only']);
    exit;
}

$id       = (int)($_POST['id']           ?? 0);
$title    = trim($_POST['title']         ?? '');
$year     = trim($_POST['year']          ?? '');
$duration = trim($_POST['duration']      ?? '');
$rating   = trim($_POST['rating']        ?? '');
$genre    = trim($_POST['genre']         ?? '');
$desc     = trim($_POST['desc']          ?? '');
$poster   = trim($_POST['poster_url']    ?? '');
$video    = trim($_POST['video_url']     ?? '');
$trailer  = trim($_POST['trailer_url']   ?? '');
$type     = trim($_POST['content_type']  ?? 'movie');
$category = trim($_POST['category']      ?? 'trending');

if (!$id || empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'ID and title required']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE content SET
        title = ?, release_year = ?, duration = ?, rating = ?, genre = ?,
        description = ?, poster_url = ?, video_url = ?, trailer_url = ?,
        content_type = ?, category = ?
        WHERE id = ?");
    $stmt->execute([$title, $year, $duration, $rating, $genre, $desc, $poster, $video, $trailer, $type, $category, $id]);
    echo json_encode(['status' => 'success', 'message' => "Content '$title' updated!"]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
