<?php
$host     = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port     = 4000;
$db_name  = 'test';
$username = 'uGHbY7uvVYrgr8U.root';
$password = 'zBKYgl8eQl0GD3Q7';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // TiDB Cloud requires SSL
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ];
    // Some PHP versions need SSL_CA to be set to a valid path, or use empty string/null
    $options[PDO::MYSQL_ATTR_SSL_CA] = ''; 

    $pdo = new PDO($dsn, $username, $password, $options);
    echo "Connected successfully to TiDB!\n";
    
    // Check if tables exist
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Tables in DB: " . implode(', ', $tables) . "\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
