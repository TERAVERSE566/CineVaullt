<?php
// api/fetch_cinema_details.php
// Auto-fills movie/series details using OMDB API (free tier)
// OMDB API Key: use a free one from omdbapi.com (or use TVMaze for series)

header('Content-Type: application/json');

$title = trim($_GET['title'] ?? '');
$type  = trim($_GET['type'] ?? 'movie'); // 'movie' or 'series'

if (empty($title)) {
    echo json_encode(['status' => 'error', 'message' => 'No title provided']);
    exit;
}

// ── Try OMDB first (free, no API key needed for basic calls) ──────────────────
// We'll use the free TVMaze for series and iTunes/OMDB for movies
$result = null;

if ($type === 'series') {
    // TVMaze — completely free, no key needed
    $url = 'https://api.tvmaze.com/singlesearch/shows?q=' . urlencode($title) . '&embed=seasons';
    $json = @file_get_contents($url);
    if ($json) {
        $data = json_decode($json, true);
        if (!empty($data['id'])) {
            $poster = $data['image']['original'] ?? ($data['image']['medium'] ?? '');
            $genres = implode(' · ', $data['genres'] ?? []);
            $seasons = count($data['_embedded']['seasons'] ?? []);
            $result = [
                'status'       => 'ok',
                'title'        => $data['name'] ?? $title,
                'year'         => substr($data['premiered'] ?? '', 0, 4),
                'rating'       => number_format(($data['rating']['average'] ?? 0), 1),
                'genre'        => $genres,
                'description'  => strip_tags($data['summary'] ?? ''),
                'poster_url'   => $poster,
                'content_type' => 'series',
                'seasons'      => $seasons,
                'language'     => $data['language'] ?? '',
                'status'       => $data['status'] ?? '',
                'trailer_url'  => '',
            ];
        }
    }
} else {
    // OMDB free API — needs a key, but has 1000/day free limit
    // Using free API key embedded (public demo key from OMDB docs)
    $apiKey = 'trilogy';  // demo key — replace with your own from omdbapi.com
    $url = 'https://www.omdbapi.com/?t=' . urlencode($title) . '&type=movie&apikey=' . $apiKey;
    $json = @file_get_contents($url);
    if ($json) {
        $data = json_decode($json, true);
        if (($data['Response'] ?? '') === 'True') {
            $rating = str_replace('/', '', $data['imdbRating'] ?? '0');
            $result = [
                'status'       => 'ok',
                'title'        => $data['Title'] ?? $title,
                'year'         => $data['Year'] ?? '',
                'rating'       => $rating,
                'genre'        => str_replace(', ', ' · ', $data['Genre'] ?? ''),
                'description'  => $data['Plot'] ?? '',
                'poster_url'   => ($data['Poster'] !== 'N/A') ? $data['Poster'] : '',
                'content_type' => 'movie',
                'duration'     => $data['Runtime'] ?? '',
                'trailer_url'  => '',
            ];
        }
    }
}

// ── Fallback: iTunes Search ────────────────────────────────────────────────────
if (!$result && $type === 'movie') {
    $url = 'https://itunes.apple.com/search?term=' . urlencode($title) . '&entity=movie&limit=1';
    $json = @file_get_contents($url);
    if ($json) {
        $data = json_decode($json, true);
        $item = $data['results'][0] ?? null;
        if ($item) {
            $poster = str_replace('100x100bb', '600x900bb', $item['artworkUrl100'] ?? '');
            $result = [
                'status'       => 'ok',
                'title'        => $item['trackName'] ?? $title,
                'year'         => substr($item['releaseDate'] ?? '', 0, 4),
                'rating'       => '',
                'genre'        => $item['primaryGenreName'] ?? '',
                'description'  => $item['longDescription'] ?? ($item['shortDescription'] ?? ''),
                'poster_url'   => $poster,
                'content_type' => 'movie',
                'duration'     => isset($item['trackTimeMillis']) ? round($item['trackTimeMillis']/60000) . ' min' : '',
                'trailer_url'  => '',
            ];
        }
    }
}

if (!$result) {
    echo json_encode(['status' => 'error', 'message' => 'No results found for "' . htmlspecialchars($title) . '"']);
} else {
    // Add status field back (it was overwritten above in series)
    if (!isset($result['status'])) $result['status'] = 'ok';
    $result['status'] = 'ok';
    echo json_encode($result);
}
