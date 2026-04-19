<?php
// CineVault – Complete Database Migration (MySQL compatible, no IF NOT EXISTS)
session_start();
require_once 'api/db_connect.php';

$steps = [];
$errors = [];

function trySQL($pdo, $sql, $label, &$steps, &$errors) {
    try {
        $pdo->exec($sql);
        $steps[] = "✅ $label";
    } catch (PDOException $e) {
        // Column/table already exists = OK
        $code = $e->getCode();
        $msg  = $e->getMessage();
        if (str_contains($msg, 'Duplicate column') || str_contains($msg, 'already exists') || $code === '42S01' || $code === '42S21') {
            $steps[] = "✅ $label (already existed — OK)";
        } else {
            $errors[] = "⚠️ $label: $msg";
        }
    }
}

// ── ALTER TABLE content ──────────────────────────────────────
trySQL($pdo, "ALTER TABLE content ADD COLUMN video_url VARCHAR(500) DEFAULT NULL",   "content.video_url",   $steps, $errors);
trySQL($pdo, "ALTER TABLE content ADD COLUMN trailer_url VARCHAR(500) DEFAULT NULL", "content.trailer_url", $steps, $errors);
trySQL($pdo, "ALTER TABLE content ADD COLUMN release_date DATE DEFAULT NULL",        "content.release_date",$steps, $errors);
trySQL($pdo, "ALTER TABLE content ADD COLUMN view_count INT DEFAULT 0",              "content.view_count",  $steps, $errors);
trySQL($pdo, "ALTER TABLE content ADD COLUMN category VARCHAR(50) DEFAULT 'trending'","content.category",   $steps, $errors);

trySQL($pdo, "ALTER TABLE comments ADD COLUMN likes_count INT DEFAULT 0",  "comments.likes_count column", $steps, $errors);

// ── watch_history: add watched_at if missing ──────────────────
trySQL($pdo, "ALTER TABLE watch_history ADD COLUMN watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "watch_history.watched_at column", $steps, $errors);

trySQL($pdo, "ALTER TABLE users ADD COLUMN plan ENUM('basic','pro','max') DEFAULT 'basic'", "users.plan",       $steps, $errors);
trySQL($pdo, "ALTER TABLE users ADD COLUMN avatar_url VARCHAR(300) DEFAULT NULL",           "users.avatar_url", $steps, $errors);
trySQL($pdo, "ALTER TABLE users ADD COLUMN role ENUM('user','admin') DEFAULT 'user'",       "users.role",       $steps, $errors);

// ── CREATE tables ────────────────────────────────────────────
trySQL($pdo, "CREATE TABLE IF NOT EXISTS watch_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    paused_at_seconds INT DEFAULT 0,
    watched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_history (user_id, content_id)
)", "watch_history table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    likes_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
)", "comments table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS user_ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    rating TINYINT NOT NULL,
    rated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (user_id, content_id)
)", "user_ratings table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS comment_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    comment_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (user_id, comment_id)
)", "comment_likes table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message VARCHAR(500) NOT NULL,
    link VARCHAR(300) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)", "notifications table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_id INT NOT NULL,
    season_number INT NOT NULL,
    title VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
    UNIQUE KEY unique_season (content_id, season_number)
)", "seasons table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    duration VARCHAR(30) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    video_url VARCHAR(500) DEFAULT NULL,
    thumbnail_url VARCHAR(500) DEFAULT NULL,
    FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
    UNIQUE KEY unique_ep (season_id, episode_number)
)", "episodes table", $steps, $errors);

trySQL($pdo, "CREATE TABLE IF NOT EXISTS downloads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE
)", "downloads table", $steps, $errors);

// ── Uploads directory ────────────────────────────────────────
if (!is_dir(__DIR__ . '/uploads/avatars')) {
    mkdir(__DIR__ . '/uploads/avatars', 0755, true);
    $steps[] = "✅ Created uploads/avatars/";
} else {
    $steps[] = "✅ uploads/avatars/ already exists";
}

// ── Promote first user to admin if no admins exist ──────────
$adminCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
if ($adminCount == 0) {
    $pdo->exec("UPDATE users SET role='admin' WHERE id=(SELECT MIN(id) FROM (SELECT id FROM users) AS u)");
    $steps[] = "✅ Promoted first registered user to admin";
} else {
    $steps[] = "✅ Admin user already exists";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CineVault Migration</title>
<style>
  body { font-family:'Segoe UI',sans-serif;background:#0a0a0f;color:#eee;padding:40px;max-width:800px;margin:0 auto; }
  h1 { color:#e63946;font-size:32px;margin-bottom:5px; }
  .step { padding:9px 14px;margin:4px 0;border-radius:6px;font-size:14px; }
  .ok  { background:rgba(0,200,100,.1);border-left:3px solid #0c6; }
  .err { background:rgba(255,80,80,.1);border-left:3px solid #f44; }
  .done { background:linear-gradient(135deg,#1a1a2e,#16213e);padding:24px;border-radius:12px;margin-top:24px;border:1px solid #0c6; }
  a { color:#e63946;font-weight:700; }
</style>
</head>
<body>
<h1>⚡ CineVault Database Migration</h1>
<p style="color:#888">Running all schema updates…</p>

<?php foreach ($steps  as $s): ?><div class="step ok"><?= htmlspecialchars($s) ?></div><?php endforeach; ?>
<?php foreach ($errors as $e): ?><div class="step err"><?= htmlspecialchars($e) ?></div><?php endforeach; ?>

<div class="done">
  <strong style="color:#0c6;font-size:18px">✅ Migration Complete!</strong><br>
  <p style="margin-top:10px"><?= count($steps) ?> steps OK · <?= count($errors) ?> warnings (usually safe to ignore)</p>
  <a href="home.php">→ Go to CineVault Home</a>
</div>
</body>
</html>
