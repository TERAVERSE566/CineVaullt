<?php
// api/db_connect.php — Database connection for CineVault
$host     = getenv('DB_HOST') ?: ($_SERVER['DB_HOST'] ?? 'localhost');
$db_name  = getenv('DB_NAME') ?: ($_SERVER['DB_NAME'] ?? 'cinevault_db');
$username = getenv('DB_USER') ?: ($_SERVER['DB_USER'] ?? 'root');
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($_SERVER['DB_PASS'] ?? 'Anish566@@');
$port     = getenv('DB_PORT') ?: ($_SERVER['DB_PORT'] ?? '3306');

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
    
    // Explicit error if running on Render without DB_HOST
    if ($host === 'localhost' && (getenv('RENDER') || isset($_SERVER['RENDER']))) {
        die('<h1 style="color:red;text-align:center;padding:50px">Environment Variables Missing! Please add DB_HOST, DB_USER, DB_PASS, DB_PORT, and DB_NAME in your Render Dashboard.</h1>');
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