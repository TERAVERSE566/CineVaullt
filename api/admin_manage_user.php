<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'admin') { echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

$action    = $_POST['action']  ?? '';
$targetId  = (int)($_POST['user_id'] ?? 0);

if (!$targetId) { echo json_encode(['status'=>'error','message'=>'No user_id provided']); exit; }

switch ($action) {
    // ── Change plan ──────────────────────────────────────────────────────────
    case 'change_plan':
        $plan = $_POST['plan'] ?? '';
        if (!in_array($plan, ['basic','pro','max'])) {
            echo json_encode(['status'=>'error','message'=>'Invalid plan']); exit;
        }
        $pdo->prepare("UPDATE users SET plan = ? WHERE id = ?")->execute([$plan, $targetId]);
        echo json_encode(['status'=>'success','message'=>'Plan updated to '.strtoupper($plan)]);
        break;

    // ── Toggle ban ───────────────────────────────────────────────────────────
    case 'toggle_ban':
        // Read current ban status
        $cur = (int)$pdo->prepare("SELECT is_banned FROM users WHERE id = ?")->execute([$targetId]) ?: 0;
        $row = $pdo->prepare("SELECT is_banned FROM users WHERE id = ?");
        $row->execute([$targetId]);
        $current = (int)($row->fetchColumn() ?? 0);
        $new = $current ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_banned = ? WHERE id = ?")->execute([$new, $targetId]);
        echo json_encode(['status'=>'success','is_banned'=>$new,'message'=> $new ? 'User banned' : 'User activated']);
        break;

    // ── Get all users (paginated) ─────────────────────────────────────────────
    case 'get_users':
        $page  = max(1, (int)($_POST['page'] ?? 1));
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $total = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $users = $pdo->query("
            SELECT u.id, u.username, u.email, u.plan, u.created_at, u.is_banned,
                   (SELECT COUNT(*) FROM watch_history wh WHERE wh.user_id=u.id) as watch_count,
                   (SELECT COUNT(*) FROM watchlist wl WHERE wl.user_id=u.id) as watchlist_count
            FROM users u
            ORDER BY u.created_at DESC
            LIMIT $limit OFFSET $offset
        ")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['status'=>'success','users'=>$users,'total'=>$total,'page'=>$page,'limit'=>$limit]);
        break;

    default:
        echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
