<?php
// Support standard DATABASE_URL (e.g., mysql://user:pass@host:port/dbname)
// Hardcoded fallback for TiDB Cloud to guarantee connection without Render Env Vars
$hardcoded_dbUrl = 'mysql://uGHbY7uvVYrgr8U.root:zBKYgl8eQl0GD3Q7@gateway01.ap-southeast-1.prod.aws.tidbcloud.com:4000/cinevault_db';

if ($dbUrl = getenv('DATABASE_URL') ?: ($_SERVER['DATABASE_URL'] ?? $hardcoded_dbUrl)) {
    $parsed = parse_url($dbUrl);
    $host     = $parsed['host'] ?? 'localhost';
    $port     = $parsed['port'] ?? '3306';
    $username = urldecode($parsed['user'] ?? 'root');
    $password = urldecode($parsed['pass'] ?? 'Anish566@@');
    $db_name  = ltrim($parsed['path'] ?? '/cinevault_db', '/');
} else {
    $host     = 'localhost';
    $db_name  = 'cinevault_db';
    $username = 'root';
    $password = 'Anish566@@';
    $port     = '3306';
}

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