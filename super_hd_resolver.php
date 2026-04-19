<?php
require_once 'api/db_connect.php';

// 1. Add backdrop_url column if it doesn't exist
try {
    $pdo->exec("ALTER TABLE content ADD COLUMN backdrop_url VARCHAR(255) DEFAULT NULL AFTER poster_url");
    echo "Added backdrop_url column.\n";
} catch (Exception $e) { /* Column likely exists */ }

$stmt = $pdo->query("SELECT id, title, content_type, poster_url FROM content");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updates = 0;

$customBackdrops = [
    'Superbad' => 'https://image.tmdb.org/t/p/original/ekQJ7hF1wB4N7hH231TIf2o9oN0.jpg',
    'The Grand Budapest Hotel' => 'https://image.tmdb.org/t/p/original/nLBRD7UPzIEhe00WbcQ08tJpS6a.jpg',
    'Hera Pheri' => 'https://image.tmdb.org/t/p/original/bUrrgYtU1fH8g07b7H5E1P68V6t.jpg',
    'Chup Chup Ke' => 'https://image.tmdb.org/t/p/original/71F7X23d4y26jQoGkF80N2jMwQx.jpg',
    'Andaz Apna Apna' => 'https://image.tmdb.org/t/p/original/3iUfUuE3H2C9tQ0l7I0mO0D3s9p.jpg',
    'Fast & Furious 7' => 'https://image.tmdb.org/t/p/original/mto5G0xM4xG5k7F7eD0p81sS5Uv.jpg',
    'Pathaan' => 'https://image.tmdb.org/t/p/original/zNysHw1EAsY0RkGv8xK1Tq2iA0u.jpg',
    'Tiger Zinda Hai' => 'https://image.tmdb.org/t/p/original/8T6K2N0T5XQ3m6t5iT7fQ6D2H3E.jpg',
    'Captain America: Winter Soldier' => 'https://image.tmdb.org/t/p/original/yHB0e854xI2C7GgOWeE1Hq0L5aK.jpg',
    'Nobody' => 'https://image.tmdb.org/t/p/original/pM_IiN7MgDs_backdrop.jpg',
    'Pushpa: The Rise' => 'https://image.tmdb.org/t/p/original/tD0h77A7eE2MGE9t34k0Kqf2R7m.jpg',
    'Uri: The Surgical Strike' => 'https://image.tmdb.org/t/p/original/x0H0Qe2bUqIeE0kH4vE6R7gE80F.jpg',
    'Baahubali 2: The Conclusion' => 'https://image.tmdb.org/t/p/original/9J5rW0d9R0x0A2Z9w5Fq8T8D4T5.jpg',
    'RRR' => 'https://image.tmdb.org/t/p/original/a0V34G206Y7fK05vK8x0R2Q1n8H.jpg',
    'KGF: Chapter 2' => 'https://image.tmdb.org/t/p/original/v5k3rO7tY6I9p7k3T1T2E8K0H5J.jpg',
    'John Wick: Chapter 4' => 'https://image.tmdb.org/t/p/original/7I6VUdPj6tQEcHNWiHxYjRWEAW.jpg',
    'Mad Max: Fury Road' => 'https://image.tmdb.org/t/p/original/g5U5gP7C6Z0VqJw5K9n3m3n1q8a.jpg',
    'Pulp Fiction' => 'https://image.tmdb.org/t/p/original/suaEOtk1N1sgg2MTM7oDx2Z2KWe.jpg'
];

foreach ($items as $item) {
    $id = $item['id'];
    $title = $item['title'];
    $type = $item['content_type'];
    $posterUrl = $item['poster_url'];
    $backdropUrl = $customBackdrops[$title] ?? null;

    $newPoster = $posterUrl;

    // Use free public APIs strictly for missing/broken/low-res
    if (strpos($newPoster, 'placehold') !== false || strpos($newPoster, 'amazon.com') !== false || strlen($newPoster) < 10) {
        
        if ($type === 'series') {
            $json = @file_get_contents("https://api.tvmaze.com/singlesearch/shows?q=" . urlencode($title));
            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data['image']['original'])) {
                    $newPoster = $data['image']['original'];
                }
            }
        } else {
            $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36";
            $options = ['http' => ['header' => "User-Agent: $userAgent\r\n"]];
            $context = stream_context_create($options);
            $json = @file_get_contents("https://itunes.apple.com/search?term=" . urlencode($title) . "&entity=movie&limit=1", false, $context);
            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data['results'][0]['artworkUrl100'])) {
                    // Turn 100x100 into ultra HD 600x900
                    $newPoster = str_replace('100x100bb', '600x900bb', $data['results'][0]['artworkUrl100']);
                }
            }
        }
    }

    $update = $pdo->prepare("UPDATE content SET poster_url = ?, backdrop_url = ? WHERE id = ?");
    $update->execute([$newPoster, $backdropUrl, $id]);
    $updates++;
}

echo "Assigned HD Posters and Backdrops for $updates items.\n";
?>
