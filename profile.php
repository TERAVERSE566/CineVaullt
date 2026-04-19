<?php
session_start();
require_once 'api/db_connect.php';
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$userId = $_SESSION['user_id'];
$stmt   = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user   = $stmt->fetch();

// Watchlist
$wlStmt = $pdo->prepare("SELECT c.* FROM watchlist w JOIN content c ON w.content_id=c.id WHERE w.user_id=? ORDER BY w.added_at DESC");
$wlStmt->execute([$userId]);
$watchlistItems = $wlStmt->fetchAll();

// Watch History (real data — try new schema, fall back if columns missing)
$historyItems = [];
try {
    $histStmt = $pdo->prepare("SELECT c.*, wh.paused_at_seconds, wh.watched_at FROM watch_history wh JOIN content c ON wh.content_id=c.id WHERE wh.user_id=? ORDER BY wh.watched_at DESC LIMIT 10");
    $histStmt->execute([$userId]);
    $historyItems = $histStmt->fetchAll();
} catch (PDOException $e) {
    // watched_at column may not exist yet — try simpler query
    try {
        $histStmt = $pdo->prepare("SELECT c.*, wh.paused_at_seconds FROM watch_history wh JOIN content c ON wh.content_id=c.id WHERE wh.user_id=? LIMIT 10");
        $histStmt->execute([$userId]);
        $historyItems = $histStmt->fetchAll();
    } catch (PDOException $e2) { $historyItems = []; }
}

// Recent Reviews
$cmtStmt = $pdo->prepare("SELECT c.comment_text, c.created_at, COALESCE(c.likes_count,0) as likes_count, ct.title, ct.id as content_id FROM comments c JOIN content ct ON c.content_id=ct.id WHERE c.user_id=? ORDER BY c.created_at DESC LIMIT 5");
$cmtStmt->execute([$userId]);
$recentComments = $cmtStmt->fetchAll();

// Stats
$watchCount  = $pdo->prepare("SELECT COUNT(*) FROM watch_history WHERE user_id=?"); $watchCount->execute([$userId]); $watchCount = $watchCount->fetchColumn();
$reviewCount = $pdo->prepare("SELECT COUNT(*) FROM comments WHERE user_id=?"); $reviewCount->execute([$userId]); $reviewCount = $reviewCount->fetchColumn();
$ratingAvg   = $pdo->prepare("SELECT ROUND(AVG(rating),1) FROM user_ratings WHERE user_id=?"); $ratingAvg->execute([$userId]); $ratingAvg = $ratingAvg->fetchColumn() ?: '–';
$wlCount     = count($watchlistItems);

$pageTitle  = 'CineVault – My Profile';
$activePage = 'profile';
include 'includes/header.php';
?>
<style>
.profile-wrap { max-width:1200px; margin:40px auto; padding:0 20px 80px; }
.profile-header { display:flex; align-items:center; gap:30px; background:rgba(255,255,255,0.04);
    padding:36px; border-radius:18px; border:1px solid rgba(255,255,255,0.07); margin-bottom:36px;
    border-left:4px solid var(--red); }
.profile-avatar-large { width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,var(--red),#800);
    display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;
    overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.5);flex-shrink:0; }
.profile-avatar-large img { width:100%;height:100%;object-fit:cover; }
.profile-info h1 { font-family:var(--font-display);font-size:42px;margin-bottom:5px; }
.profile-info p  { color:#888;font-size:15px;margin-top:3px; }
.profile-badge { display:inline-block;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:700;margin-top:8px; }
.badge-basic { background:rgba(255,255,255,.1);color:#aaa; }
.badge-pro   { background:rgba(230,57,70,.2);color:var(--red); }
.badge-max   { background:rgba(255,193,7,.2);color:var(--gold); }
.stats-row { display:flex;gap:24px;flex-wrap:wrap;margin-top:16px; }
.stat-box  { background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.07);border-radius:12px;
    padding:14px 22px;text-align:center;min-width:90px; }
.stat-num  { font-size:28px;font-weight:800;color:var(--gold); }
.stat-label{ font-size:11px;color:#777;text-transform:uppercase;margin-top:2px; }
/* Continue watching progress */
.progress-bar-bg  { height:3px;background:rgba(255,255,255,0.1);border-radius:2px;margin-top:6px; }
.progress-bar-fill{ height:100%;background:var(--red);border-radius:2px; }
</style>

<div class="profile-wrap">
    <!-- Header -->
    <div class="profile-header">
        <div class="profile-avatar-large">
            <?php if ($user['avatar_url']): ?>
            <img src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="<?= htmlspecialchars($user['username']) ?>">
            <?php else: ?><?= strtoupper(substr($user['username'],0,1)) ?>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p>📧 <?= htmlspecialchars($user['email']) ?></p>
            <p>🗓 Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>
            <span class="profile-badge badge-<?= $user['plan'] ?? 'basic' ?>"><?= strtoupper($user['plan'] ?? 'BASIC') ?> PLAN</span>
            <div class="stats-row">
                <div class="stat-box"><div class="stat-num"><?= $wlCount ?></div><div class="stat-label">Watchlist</div></div>
                <div class="stat-box"><div class="stat-num"><?= $watchCount ?></div><div class="stat-label">Watched</div></div>
                <div class="stat-box"><div class="stat-num"><?= $reviewCount ?></div><div class="stat-label">Reviews</div></div>
                <div class="stat-box"><div class="stat-num"><?= $ratingAvg ?></div><div class="stat-label">Avg Rating</div></div>
            </div>
        </div>
        <div style="margin-left:auto;align-self:flex-start">
            <a href="settings.php" class="glow-btn" style="text-decoration:none;padding:12px 22px;white-space:nowrap">⚙️ Settings</a>
        </div>
    </div>

    <!-- Continue Watching -->
    <?php if (count($historyItems) > 0): ?>
    <section class="content-section" style="margin-bottom:40px">
        <div class="section-header"><h2 class="section-title">▶️ Continue Watching</h2></div>
        <div class="row-scroll">
            <?php foreach ($historyItems as $h):
                $dur = $h['duration'] ?? '0';
                // Estimate total seconds from duration string for progress %
                preg_match('/(\d+)h\s*(\d+)m/', $dur, $m);
                $totalSec = isset($m[1]) ? ($m[1]*3600 + $m[2]*60) : 7200;
                $pct = min(99, round(($h['paused_at_seconds'] / max(1,$totalSec)) * 100));
                if ($h['paused_at_seconds'] < 5) $pct = max(3, rand(10,30)); // fallback visual
            ?>
            <div class="movie-card" onclick="window.location.href='watch.php?id=<?= $h['id'] ?>'">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($h['poster_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($h['title']) ?>">
                    <div class="card-overlay"><div class="card-play-btn"></div></div>
                    <div class="progress-bar-bg" style="position:absolute;bottom:0;left:0;right:0;margin:0;border-radius:0">
                        <div class="progress-bar-fill" style="width:<?= $pct ?>%"></div>
                    </div>
                </div>
                <div class="card-info"><span class="card-title"><?= htmlspecialchars($h['title']) ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Watchlist -->
    <section class="content-section" style="margin-bottom:40px">
        <div class="section-header"><h2 class="section-title">🔖 My Watchlist <span style="font-size:16px;color:#666">(<?= $wlCount ?>)</span></h2></div>
        <?php if ($wlCount > 0): ?>
        <div class="row-scroll">
            <?php foreach ($watchlistItems as $w): ?>
            <div class="movie-card" onclick="window.location.href='watch.php?id=<?= $w['id'] ?>'">
                <div class="card-img-wrap">
                    <img src="<?= htmlspecialchars($w['poster_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($w['title']) ?>">
                    <div class="card-overlay"><div class="card-play-btn"></div></div>
                </div>
                <div class="card-info"><span class="card-title"><?= htmlspecialchars($w['title']) ?></span></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="color:#555">Your watchlist is empty. Browse and add titles to watch later!</p>
        <?php endif; ?>
    </section>

    <!-- Recent Reviews -->
    <section class="content-section" style="margin-bottom:40px">
        <div class="section-header"><h2 class="section-title">💬 My Reviews</h2></div>
        <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);border-radius:14px;padding:20px">
            <?php if (count($recentComments) > 0): ?>
            <?php foreach ($recentComments as $cmt): ?>
            <div style="padding:16px 0;border-bottom:1px solid rgba(255,255,255,0.04)">
                <a href="watch.php?id=<?= $cmt['content_id'] ?>" style="color:var(--red);text-decoration:none;font-weight:700;font-size:17px"><?= htmlspecialchars($cmt['title']) ?></a>
                <span style="color:#555;font-size:12px;margin-left:10px"><?= date('M j, Y', strtotime($cmt['created_at'])) ?></span>
                <span style="color:#777;font-size:12px;margin-left:8px">· ❤️ <?= (int)$cmt['likes_count'] ?></span>
                <p style="color:#ccc;margin-top:8px;line-height:1.5;font-size:15px">"<?= htmlspecialchars($cmt['comment_text']) ?>"</p>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="color:#555;margin:0">No reviews yet. Watch something and share your thoughts!</p>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php include 'includes/footer.php'; ?>
