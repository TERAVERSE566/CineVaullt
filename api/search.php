<?php
// api/search.php — Search API for navbar dropdown
require_once 'db_connect.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) {
    echo json_encode(['results' => []]);
    exit;
}

$like = '%' . $q . '%';
try {
    $stmt = $pdo->prepare("SELECT id, title, release_year, content_type, rating, poster_url FROM content WHERE LOWER(title) LIKE LOWER(?) OR LOWER(genre) LIKE LOWER(?) ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 8");
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
    echo json_encode(['results' => $results]);
} catch (PDOException $e) {
    echo json_encode(['results' => [], 'error' => $e->getMessage()]);
}
?>
