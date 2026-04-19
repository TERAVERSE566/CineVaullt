<?php
// api/db_connect.php — Database connection for CineVault
$host     = getenv('DB_HOST') ?: 'localhost';
$db_name  = getenv('DB_NAME') ?: 'cinevault_db';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Anish566@@';
$port     = getenv('DB_PORT') ?: '3306';

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    // TiDB Serverless requires SSL
    if (strpos($host, 'tidbcloud.com') !== false) {
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
        $options[PDO::MYSQL_ATTR_SSL_CA] = '';
    }
    
    $pdo = new PDO($dsn, $username, $password, $options);
} catch (PDOException $e) {
    // Return JSON-safe error for API calls, or HTML for page calls
    $isApi = (strpos($_SERVER['PHP_SELF'] ?? '', '/api/') !== false);
    if ($isApi) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'message' => 'DB connection failed: ' . $e->getMessage()]));
    } else {
        die('<h1 style="color:red;text-align:center;padding:50px">Database connection failed. Make sure XAMPP MySQL is running.</h1>');
    }
}
?>