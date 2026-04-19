<?php
require_once 'api/db_connect.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS watch_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        content_id INT NOT NULL,
        paused_at_seconds INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (content_id) REFERENCES content(id) ON DELETE CASCADE,
        UNIQUE KEY unique_history (user_id, content_id)
    );
    ";
    
    // Add role to user if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user'");
        // Make the first user an admin by default
        $pdo->exec("UPDATE users SET role = 'admin' LIMIT 1");
    } catch (PDOException $e) { }

    $pdo->exec($sql);
    echo "Phase 4 Database enhancements successfully applied. \n";

} catch (PDOException $e) {
    echo "Error updating schema: " . $e->getMessage() . "\n";
}
?>
