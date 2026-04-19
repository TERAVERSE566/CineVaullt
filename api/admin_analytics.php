<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

// Auth check
if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'Unauthorized']); exit; }
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'admin') { echo json_encode(['error' => 'Forbidden']); exit; }

// ── 1. Daily signups – last 30 days ──────────────────────────────────────────
$signupRows = $pdo->query("
    SELECT DATE(created_at) as day, COUNT(*) as cnt
    FROM users
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Build a full 30-day array (fill gaps with 0)
$signupMap = [];
foreach ($signupRows as $r) $signupMap[$r['day']] = (int)$r['cnt'];
$signupLabels = [];
$signupData   = [];
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $signupLabels[] = date('M j', strtotime($d));
    $signupData[]   = $signupMap[$d] ?? 0;
}

// ── 2. Content breakdown (Movies / Series / Anime) ───────────────────────────
$movieCount  = (int)$pdo->query("SELECT COUNT(*) FROM content WHERE content_type='movie'")->fetchColumn();
$seriesCount = (int)$pdo->query("SELECT COUNT(*) FROM content WHERE content_type='series'")->fetchColumn();
$animeCount  = (int)$pdo->query("SELECT COUNT(*) FROM content WHERE content_type='anime'")->fetchColumn();

// ── 3. Top 5 most-watched ─────────────────────────────────────────────────────
$topWatched = $pdo->query("
    SELECT c.title, COUNT(w.id) as watches
    FROM watch_history w
    JOIN content c ON c.id = w.content_id
    GROUP BY w.content_id
    ORDER BY watches DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
$topLabels  = array_column($topWatched, 'title');
$topData    = array_map('intval', array_column($topWatched, 'watches'));

echo json_encode([
    'signup_labels'  => $signupLabels,
    'signup_data'    => $signupData,
    'content_counts' => [$movieCount, $seriesCount, $animeCount],
    'top_labels'     => $topLabels,
    'top_data'       => $topData,
]);
