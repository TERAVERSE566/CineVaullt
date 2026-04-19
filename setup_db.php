<?php
try {
    $pdo = new PDO('mysql:host=localhost;charset=utf8mb4', 'root', 'Anish566@@');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql = file_get_contents('schema.sql');
    $pdo->exec($sql);
    echo "Schema executed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
