<?php
session_start();
require_once 'db_connect.php';
header('Content-Type: application/json');

$type     = $_GET['type'] ?? 'all';
$category = $_GET['category'] ?? 'all';

try {
    $sql    = "SELECT * FROM content WHERE 1=1";
    $params = [];

    if ($type !== 'all') {
        $sql .= " AND content_type = ?";
        $params[] = $type;
    }

    if ($category !== 'all') {
        if ($category === 'trending' || $category === 'top10') {
            $sql .= " AND category IN ('trending', 'top10')";
        } else {
            $sql .= " AND (category = ? OR genre LIKE ?)";
            $params[] = $category;
            $params[] = "%$category%";
        }
    }

    $sql .= " ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();

    echo json_encode(['status' => 'success', 'data' => $results]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to fetch content.']);
}
?>
