<?php
// DB upgrade – Phase 5: Add is_banned column to users table
require_once 'api/db_connect.php';
$upgrades = [
    "ALTER TABLE users ADD COLUMN IF NOT EXISTS is_banned TINYINT(1) NOT NULL DEFAULT 0",
    "ALTER TABLE content MODIFY COLUMN content_type ENUM('movie','series','anime') DEFAULT 'movie'",
];
foreach ($upgrades as $sql) {
    try {
        $pdo->exec($sql);
        echo "<p style='color:lime'>✅ OK: <code>" . htmlspecialchars($sql) . "</code></p>";
    } catch (PDOException $e) {
        echo "<p style='color:orange'>⚠️ " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
echo "<p><strong>Done! You can delete this file now.</strong></p>";
