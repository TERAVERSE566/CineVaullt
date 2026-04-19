<?php
require_once 'api/db_connect.php';

try {
    // 1. Add video_url to content if it doesn't exist
    $pdo->exec("ALTER TABLE content ADD COLUMN video_url VARCHAR(500) DEFAULT ''");
} catch (PDOException $e) {
    // Column might already exist, ignore error safely
}

try {
    // 2. Create the comments table linking user and content
    $sql = "
    CREATE TABLE IF NOT EXISTS comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content_id INT NOT NULL,
        user_id INT NOT NULL,
        comment_text TEXT NOT NULL,
        rating_score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    );
    ";
    $pdo->exec($sql);
    
    // Default video setup so everything has a fallback video.
    $pdo->exec("UPDATE content SET video_url = 'https://www.w3schools.com/html/mov_bbb.mp4' WHERE video_url = '' OR video_url IS NULL");

    echo "Database schema successfully upgraded for Phase 3 Player and Comments!\n";
} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>
