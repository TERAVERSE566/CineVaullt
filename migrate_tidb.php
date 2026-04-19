<?php
$host     = 'gateway01.ap-southeast-1.prod.aws.tidbcloud.com';
$port     = 4000;
$db_name  = 'test'; // Connect to test first
$username = 'uGHbY7uvVYrgr8U.root';
$password = 'zBKYgl8eQl0GD3Q7';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => ''
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Try creating a new database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS cinevault_db");
    echo "Created database cinevault_db successfully!\n";
    
    // Now switch to it
    $pdo->exec("USE cinevault_db");
    
    // Read schema.sql and execute
    $sql = file_get_contents('schema.sql');
    $pdo->exec($sql);
    echo "Imported schema.sql successfully!\n";
    
    // Also run the upgrade scripts if any
    echo "Running upgrade scripts...\n";
    require 'upgrade_db.php';
    echo "upgrade_db.php done.\n";
    require 'super_hd_resolver.php';
    echo "super_hd_resolver.php done.\n";
    require 'upgrade_db_p4.php';
    echo "upgrade_db_p4.php done.\n";

} catch (PDOException $e) {
    echo "Connection/Execution failed: " . $e->getMessage() . "\n";
}
