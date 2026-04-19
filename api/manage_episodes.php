<?php
// api/manage_episodes.php — CRUD for seasons and episodes
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$chk = $pdo->prepare("SELECT role FROM users WHERE id=?");
$chk->execute([$_SESSION['user_id']]);
if ($chk->fetchColumn() !== 'admin') { echo json_encode(['status'=>'error','message'=>'Access denied']); exit; }

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$seriesId = (int)($_POST['series_id'] ?? $_GET['series_id'] ?? 0);

if ($action === 'get_episodes') {
    // Get all seasons+episodes for a series
    $seasons = $pdo->prepare("SELECT * FROM seasons WHERE content_id=? ORDER BY season_number ASC");
    $seasons->execute([$seriesId]);
    $seasonList = $seasons->fetchAll();
    $out = [];
    foreach ($seasonList as $s) {
        $eps = $pdo->prepare("SELECT * FROM episodes WHERE season_id=? ORDER BY episode_number ASC");
        $eps->execute([$s['id']]);
        $s['episodes'] = $eps->fetchAll();
        $out[] = $s;
    }
    echo json_encode(['status'=>'ok','seasons'=>$out]);

} elseif ($action === 'add_season') {
    $num   = (int)($_POST['season_number'] ?? 1);
    $title = trim($_POST['title'] ?? 'Season ' . $num);
    $pdo->prepare("INSERT INTO seasons (content_id, season_number, title) VALUES (?,?,?)")
        ->execute([$seriesId, $num, $title]);
    echo json_encode(['status'=>'ok','id'=>$pdo->lastInsertId(),'message'=>'Season added']);

} elseif ($action === 'add_episode') {
    $seasonId  = (int)($_POST['season_id'] ?? 0);
    $epNum     = (int)($_POST['episode_number'] ?? 1);
    $epTitle   = trim($_POST['title'] ?? 'Episode ' . $epNum);
    $videoUrl  = trim($_POST['video_url'] ?? '');
    $duration  = trim($_POST['duration'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $pdo->prepare("INSERT INTO episodes (season_id, episode_number, title, video_url, duration, description) VALUES (?,?,?,?,?,?)")
        ->execute([$seasonId, $epNum, $epTitle, $videoUrl, $duration, $desc]);
    echo json_encode(['status'=>'ok','id'=>$pdo->lastInsertId(),'message'=>'Episode added']);

} elseif ($action === 'delete_episode') {
    $epId = (int)($_POST['episode_id'] ?? 0);
    $pdo->prepare("DELETE FROM episodes WHERE id=?")->execute([$epId]);
    echo json_encode(['status'=>'ok','message'=>'Episode deleted']);

} elseif ($action === 'delete_season') {
    $seasonId = (int)($_POST['season_id'] ?? 0);
    $pdo->prepare("DELETE FROM episodes WHERE season_id=?")->execute([$seasonId]);
    $pdo->prepare("DELETE FROM seasons WHERE id=?")->execute([$seasonId]);
    echo json_encode(['status'=>'ok','message'=>'Season deleted']);

} else {
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
