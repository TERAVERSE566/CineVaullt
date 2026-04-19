<?php
require_once 'api/db_connect.php';

try {
    $stmt = $pdo->query("SELECT id, title, poster_url FROM content");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updates = 0;
    foreach ($items as $item) {
        $url = $item['poster_url'];
        $newUrl = $url;
        $title = $item['title'];

        // 1. Upgrade TMDB endpoints to HD 'original'
        if (strpos($newUrl, 'image.tmdb.org') !== false) {
            $newUrl = preg_replace('/\/w\d+\//', '/original/', $newUrl);
        }

        // 2. Fix specific known low-quality overriding manually
        if ($title === '3 Idiots') {
            $newUrl = 'https://image.tmdb.org/t/p/original/mSXzOdAOUoU7L5TzKz2R7tqYgq.jpg';
        }
        if ($title === 'The Shawshank Redemption') {
            $newUrl = 'https://image.tmdb.org/t/p/original/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg';
        }

        if ($url !== $newUrl) {
            $update = $pdo->prepare("UPDATE content SET poster_url = ? WHERE id = ?");
            $update->execute([$newUrl, $item['id']]);
            $updates++;
        }
    }
    
    echo "Successfully upgraded $updates images to True HD (original resolution). \n";

} catch (PDOException $e) {
    echo "Error upgrading images: " . $e->getMessage() . "\n";
}
?>
