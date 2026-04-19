<?php
session_start();
$q          = trim($_GET['q'] ?? '');
$filterType = $_GET['type'] ?? 'all';
$sortBy     = $_GET['sort'] ?? 'relevance';
$minRating  = (float)($_GET['min_rating'] ?? 0);
$maxRating  = (float)($_GET['max_rating'] ?? 10);
$yearFrom   = (int)($_GET['year_from'] ?? 0);
$yearTo     = (int)($_GET['year_to']   ?? 0);

$pageTitle  = $q ? 'Search: ' . htmlspecialchars($q) . ' – CineVault' : 'Browse – CineVault';
$activePage = 'search';

require_once 'api/db_connect.php';

$params = [];
$where  = [];

if ($q !== '') {
    $where[]  = "(LOWER(title) LIKE LOWER(?) OR LOWER(genre) LIKE LOWER(?) OR LOWER(description) LIKE LOWER(?))";
    $like = "%$q%";
    $params[] = $like; $params[] = $like; $params[] = $like;
}
if ($filterType !== 'all') { $where[] = "content_type = ?"; $params[] = $filterType; }
if ($minRating > 0) { $where[] = "CAST(rating AS DECIMAL(4,1)) >= ?"; $params[] = $minRating; }
if ($maxRating < 10) { $where[] = "CAST(rating AS DECIMAL(4,1)) <= ?"; $params[] = $maxRating; }
if ($yearFrom > 0)  { $where[] = "CAST(release_year AS UNSIGNED) >= ?"; $params[] = $yearFrom; }
if ($yearTo > 0)    { $where[] = "CAST(release_year AS UNSIGNED) <= ?"; $params[] = $yearTo; }

$sql = "SELECT * FROM content";
if ($where) $sql .= " WHERE " . implode(" AND ", $where);

switch ($sortBy) {
    case 'rating':   $sql .= " ORDER BY CAST(rating AS DECIMAL(4,1)) DESC"; break;
    case 'newest':   $sql .= " ORDER BY CAST(release_year AS UNSIGNED) DESC"; break;
    case 'oldest':   $sql .= " ORDER BY CAST(release_year AS UNSIGNED) ASC";  break;
    case 'az':       $sql .= " ORDER BY title ASC"; break;
    default:         $sql .= " ORDER BY CAST(rating AS DECIMAL(4,1)) DESC"; break;
}
$sql .= " LIMIT 60";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

include 'includes/header.php';
?>
<style>
.search-page { max-width:1300px; margin:40px auto; padding:0 20px 80px; }
.search-hero  { text-align:center; padding:40px 20px 30px; }
.search-hero h1 { font-family:var(--font-display); font-size:50px; color:var(--red); }
.search-hero p  { color:#888; margin-top:8px; }
.search-big-form { display:flex; gap:10px; max-width:700px; margin:24px auto 0; }
.search-big-input { flex:1; padding:15px 20px; background:rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.15);
    border-radius:12px; color:#fff; font-size:18px; font-family:'Nunito',sans-serif; }
.search-big-input:focus { outline:none; border-color:var(--red); }
.search-big-btn { padding:15px 30px; background:var(--red); color:#fff; border:none; border-radius:12px;
    font-size:16px; font-weight:700; cursor:pointer; transition:.2s; font-family:'Nunito',sans-serif; }
.search-big-btn:hover { background:#c72030; }
/* Filters */
.filter-bar { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:14px;
    padding:20px 24px; margin-bottom:28px; display:flex; flex-wrap:wrap; gap:20px; align-items:flex-end; }
.filter-group { display:flex; flex-direction:column; gap:6px; }
.filter-group label { font-size:12px; color:#888; text-transform:uppercase; font-weight:700; }
.filter-select, .filter-input { padding:9px 14px; background:#111; border:1px solid rgba(255,255,255,0.1);
    border-radius:8px; color:#fff; font-family:'Nunito',sans-serif; font-size:14px; }
.filter-select:focus, .filter-input:focus { outline:none; border-color:var(--red); }
.filter-input { width:90px; }
.filter-apply { padding:9px 22px; background:var(--red); color:#fff; border:none; border-radius:8px;
    font-size:14px; font-weight:700; cursor:pointer; transition:.2s; font-family:'Nunito',sans-serif; align-self:flex-end; }
.filter-apply:hover { background:#c72030; }
/* Results */
.results-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
.results-count  { color:#888; font-size:15px; }
.results-grid   { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:20px; }
.no-results     { text-align:center; padding:80px 20px; }
.no-results-icon{ font-size:64px; margin-bottom:16px; }
.no-results h2  { font-size:24px; color:#888; }
/* Mood picker */
.mood-section { margin-bottom:36px; }
.mood-section h2 { font-size:22px; margin-bottom:16px; }
.mood-grid { display:flex; flex-wrap:wrap; gap:12px; }
.mood-btn { padding:12px 22px; border:1px solid rgba(255,255,255,0.1); border-radius:30px; background:rgba(255,255,255,0.05);
    color:#ddd; cursor:pointer; font-size:15px; transition:all .2s; font-family:'Nunito',sans-serif; }
.mood-btn:hover { border-color:var(--red); color:#fff; background:rgba(230,57,70,0.1); transform:translateY(-2px); }
/* Genre pills */
.genre-pills { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:24px; }
.genre-pill { padding:7px 18px; border:1px solid rgba(255,255,255,0.1); border-radius:20px;
    background:rgba(255,255,255,0.04); color:#ccc; cursor:pointer; font-size:13px;
    transition:all .2s; font-family:'Nunito',sans-serif; text-decoration:none; }
.genre-pill:hover, .genre-pill.active { border-color:var(--red); background:rgba(230,57,70,0.15); color:#fff; }
</style>

<div class="search-page">
    <!-- Hero Search -->
    <div class="search-hero">
        <h1>🔍 Browse Vault</h1>
        <p>Search 1000s of movies and shows. Filter by type, rating, year, and more.</p>
        <form method="GET" class="search-big-form">
            <input class="search-big-input" type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search movies, shows, genres…" autofocus>
            <button class="search-big-btn" type="submit">Search</button>
        </form>
    </div>

    <!-- Mood Picker (only shown when no query) -->
    <?php if ($q === ''): ?>
    <div class="mood-section">
        <h2>🎭 What are you in the mood for?</h2>
        <div class="mood-grid">
            <?php
            $moods = [
                ['😂 Something Funny',       'q=comedy&type=all'],
                ['😱 Something Scary',       'q=horror&type=all'],
                ['💥 Epic Action',           'q=action&type=all'],
                ['❤️ A Good Romance',        'q=romance&type=all'],
                ['🚀 Sci-Fi Adventure',      'q=sci-fi&type=all'],
                ['🧩 Mind-Bending Thriller', 'q=thriller&type=all'],
                ['🏆 Award Winners',         'min_rating=8.5&sort=rating'],
                ['🇮🇳 Bollywood Hits',        'q=bollywood&type=movie'],
                ['📺 Binge-Worthy Series',   'type=series&sort=rating'],
                ['🆕 New Releases',          'sort=newest'],
            ];
            foreach ($moods as [$label, $params]):
            ?>
            <button class="mood-btn" onclick="window.location.href='search.php?<?= $params ?>'">
                <?= $label ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <form method="GET" class="filter-bar">
        <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">
        <div class="filter-group">
            <label>Content Type</label>
            <select name="type" class="filter-select">
                <option value="all"    <?= $filterType==='all'    ? 'selected':'' ?>>All</option>
                <option value="movie"  <?= $filterType==='movie'  ? 'selected':'' ?>>Movies</option>
                <option value="series" <?= $filterType==='series' ? 'selected':'' ?>>TV Shows</option>
                <option value="anime"  <?= $filterType==='anime'  ? 'selected':'' ?>>🎌 Anime</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Sort By</label>
            <select name="sort" class="filter-select">
                <option value="relevance" <?= $sortBy==='relevance' ? 'selected':'' ?>>Most Relevant</option>
                <option value="rating"    <?= $sortBy==='rating'    ? 'selected':'' ?>>Highest Rated</option>
                <option value="newest"    <?= $sortBy==='newest'    ? 'selected':'' ?>>Newest First</option>
                <option value="oldest"    <?= $sortBy==='oldest'    ? 'selected':'' ?>>Oldest First</option>
                <option value="az"        <?= $sortBy==='az'        ? 'selected':'' ?>>A – Z</option>
            </select>
        </div>
        <div class="filter-group">
            <label>Min Rating</label>
            <input type="number" name="min_rating" class="filter-input" min="0" max="10" step="0.5" value="<?= $minRating ?: '' ?>" placeholder="0">
        </div>
        <div class="filter-group">
            <label>Max Rating</label>
            <input type="number" name="max_rating" class="filter-input" min="0" max="10" step="0.5" value="<?= $maxRating < 10 ? $maxRating : '' ?>" placeholder="10">
        </div>
        <div class="filter-group">
            <label>Year From</label>
            <input type="number" name="year_from" class="filter-input" min="1900" max="2030" value="<?= $yearFrom ?: '' ?>" placeholder="1900">
        </div>
        <div class="filter-group">
            <label>Year To</label>
            <input type="number" name="year_to" class="filter-input" min="1900" max="2030" value="<?= $yearTo ?: '' ?>" placeholder="2025">
        </div>
        <button type="submit" class="filter-apply">🔍 Apply Filters</button>
        <?php if ($q || $filterType !== 'all' || $sortBy !== 'relevance' || $minRating || $yearFrom): ?>
        <a href="search.php" style="color:#666;font-size:13px;align-self:center;text-decoration:none">✕ Reset</a>
        <?php endif; ?>
    </form>

    <!-- Genre Quick-Filter Pills -->
    <?php
    $genrePills = [
        ['💥 Action',   'q=action'],
        ['😂 Comedy',   'q=comedy'],
        ['👻 Horror',   'q=horror'],
        ['❤️ Romance',  'q=romance'],
        ['🚀 Sci-Fi',   'q=sci-fi'],
        ['🧩 Thriller', 'q=thriller'],
        ['🎌 Anime',    'type=anime'],
        ['📺 Series',   'type=series'],
        ['🇮🇳 Bollywood','q=bollywood&type=movie'],
        ['🌟 Hollywood','q=hollywood&type=movie'],
    ];
    ?>
    <div class="genre-pills">
        <?php foreach ($genrePills as [$label, $params]):
            $isActive = false;
            parse_str($params, $pArr);
            if (isset($pArr['q']) && strtolower($q) === strtolower($pArr['q'])) $isActive = true;
            if (isset($pArr['type']) && $filterType === $pArr['type'] && !isset($pArr['q'])) $isActive = true;
        ?>
        <a href="search.php?<?= $params ?>" class="genre-pill<?= $isActive ? ' active' : '' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <!-- Results -->
    <div class="results-header">
        <?php if ($q): ?>
        <div class="results-count">Found <strong><?= count($results) ?></strong> results for "<strong><?= htmlspecialchars($q) ?></strong>"</div>
        <?php else: ?>
        <div class="results-count">Showing <strong><?= count($results) ?></strong> titles</div>
        <?php endif; ?>
    </div>

    <?php if (count($results) > 0): ?>
    <div class="results-grid">
        <?php foreach ($results as $m): ?>
        <div class="movie-card" onclick="window.location.href='watch.php?id=<?= $m['id'] ?>'">
            <div class="card-img-wrap">
                <img src="<?= htmlspecialchars($m['poster_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($m['title']) ?>"
                    onerror="this.src='https://placehold.co/300x450/111/fff?text=<?= urlencode($m['title']) ?>'">
                <div class="card-overlay"><div class="card-play-btn"></div></div>
                <div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);padding:2px 8px;border-radius:8px;font-size:11px;color:var(--gold)">
                    ⭐ <?= htmlspecialchars($m['rating']) ?>
                </div>
            </div>
            <div class="card-info">
                <span class="card-title"><?= htmlspecialchars($m['title']) ?></span>
                <span style="font-size:11px;color:#666;display:block;margin-top:2px"><?= htmlspecialchars($m['release_year']) ?> · <?= $m['content_type'] === 'series' ? '📺' : '🎬' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="no-results">
        <div class="no-results-icon">🎬</div>
        <h2>No results found<?= $q ? ' for "'.htmlspecialchars($q).'"' : '' ?></h2>
        <p style="color:#555;margin-top:12px">Try different keywords or adjust your filters.</p>
        <a href="search.php" class="glow-btn" style="text-decoration:none;display:inline-block;margin-top:20px;padding:12px 28px">Browse All</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
