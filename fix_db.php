<?php
require_once 'api/db_connect.php';

try {
    // 1. Remove duplicate movies based on title
    $pdo->exec("DELETE c1 FROM content c1 INNER JOIN content c2 WHERE c1.id > c2.id AND c1.title = c2.title");
    echo "Removed duplicates from content table.\n";
    
    // 2. Fix admin access: make only one user 'admin'
    // First, set everyone to 'user'
    $pdo->exec("UPDATE users SET role = 'user'");
    // Then set the first user (id=1) to 'admin'
    $pdo->exec("UPDATE users SET role = 'admin' ORDER BY id ASC LIMIT 1");
    echo "Fixed admin roles.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
