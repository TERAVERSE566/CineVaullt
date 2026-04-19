<?php
require_once 'api/db_connect.php';

$files = [
    'movie' => 'mv.php',
    'series' => 'series.php'
];

$pdo->beginTransaction();
try {
    // Clear existing to avoid duplicates if rerun
    $pdo->exec("DELETE FROM content WHERE id > 0");

    foreach ($files as $type => $file) {
        $content = file_get_contents($file);
        if (preg_match('/\$items = json_decode\(\'(.*?)\', true\);/s', $content, $matches)) {
            $jsonStr = str_replace(['\\\\', '\\\''], ['\\', '\''], $matches[1]);
            $items = json_decode($jsonStr, true);
            
            if ($items) {
                foreach ($items as $m) {
                    $stmt = $pdo->prepare("INSERT INTO content (title, release_year, duration, rating, genre, description, poster_url, content_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        $m['title'],
                        $m['year'],
                        $m['duration'],
                        $m['rating'],
                        $m['genre'],
                        $m['desc'],
                        $m['poster'],
                        $type
                    ]);
                }
            }
        }
    }
    $pdo->commit();
    echo "Data migration successful! Total movies and series inserted.\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed: " . $e->getMessage() . "\n";
}
