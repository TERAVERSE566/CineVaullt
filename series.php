<?php
session_start();
$pageTitle  = 'CineVault – TV Shows';
$activePage = 'series';

require_once 'api/db_connect.php';

$catFilter = $_GET['cat']      ?? 'all';
$sortBy    = $_GET['sort']     ?? 'newest';
$minRating = (float)($_GET['min_rating'] ?? 0);
$yearFrom  = (int)($_GET['year_from']    ?? 0);
$yearTo    = (int)($_GET['year_to']      ?? 0);

$query  = "SELECT id, title, release_year as year, duration, rating, genre, description as `desc`, poster_url as poster FROM content WHERE content_type = 'series'";
$params = [];

$catMap = [
    'trending'  => "AND CAST(rating AS DECIMAL(4,1)) >= 8.5",
    'action'    => "AND genre LIKE '%Action%'",
    'comedy'    => "AND genre LIKE '%Comedy%'",
    'thriller'  => "AND (genre LIKE '%Thriller%' OR genre LIKE '%Horror%')",
    'sci-fi'    => "AND (genre LIKE '%Sci-Fi%' OR genre LIKE '%Fantasy%')",
    'romance'   => "AND genre LIKE '%Romance%'",
    'drama'     => "AND (genre LIKE '%Drama%' OR genre LIKE '%Crime%')",
    'animation' => "AND genre LIKE '%Animat%'",
];
if (isset($catMap[$catFilter])) $query .= " " . $catMap[$catFilter];

if ($minRating > 0) { $query .= " AND CAST(rating AS DECIMAL(4,1)) >= ?"; $params[] = $minRating; }
if ($yearFrom > 0)  { $query .= " AND CAST(release_year AS UNSIGNED) >= ?"; $params[] = $yearFrom; }
if ($yearTo > 0)    { $query .= " AND CAST(release_year AS UNSIGNED) <= ?"; $params[] = $yearTo; }

// Title search
$q = trim($_GET['q'] ?? '');
if ($q !== '') {
    $query .= " AND title LIKE ?";
    $params[] = '%' . $q . '%';
}

switch ($sortBy) {
    case 'rating': $query .= " ORDER BY CAST(rating AS DECIMAL(4,1)) DESC"; break;
    case 'oldest': $query .= " ORDER BY CAST(release_year AS UNSIGNED) ASC"; break;
    case 'az':     $query .= " ORDER BY title ASC"; break;
    default:       $query .= " ORDER BY CAST(release_year AS UNSIGNED) DESC"; break;
}
$query .= " LIMIT 200";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();

// showDB for backdrop images (single clean assignment — bug fixed)
$pageScript = <<<'JSEND'
var showDB = {
  'Breaking Bad':     { backdrop: 'https://image.tmdb.org/t/p/original/ggFHVNu6YYI5L9pCfOacjizRGt.jpg' },
  'Stranger Things':  { backdrop: 'https://image.tmdb.org/t/p/original/49WJfeN0moxb9IPfGn8AIqMGskD.jpg' },
  'Game of Thrones':  { backdrop: 'https://image.tmdb.org/t/p/original/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg' },
  'Money Heist':      { backdrop: 'https://image.tmdb.org/t/p/original/reEMJA1uzsc8lNas4H7nZyd5pr4.jpg' },
  'Squid Game':       { backdrop: 'https://image.tmdb.org/t/p/original/dDlEmu3EZ0Pgg93K2SVNLCjCSvE.jpg' },
  'Dark':             { backdrop: 'https://image.tmdb.org/t/p/original/apbrbWs5aANEQpX9CN4Ycyr4OsB.jpg' },
  'The Last of Us':   { backdrop: 'https://image.tmdb.org/t/p/original/uKvVjHNqB5VmOrdxqAt2F7J78ED.jpg' },
  'Wednesday':        { backdrop: 'https://image.tmdb.org/t/p/original/9PFonBhy4cQy7Jz20NpMygczOkv.jpg' },
  'Attack on Titan':  { backdrop: 'https://image.tmdb.org/t/p/original/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg' },
  'Peaky Blinders':   { backdrop: 'https://image.tmdb.org/t/p/original/vUUqzWa2LnHIVqkaKVlVGkPaQvT.jpg' },
  'Succession':       { backdrop: 'https://image.tmdb.org/t/p/original/e2X8M3PNBDIRRKMi9MHDcaFGNFf.jpg' },
  'The Boys':         { backdrop: 'https://image.tmdb.org/t/p/original/2zmTngn1tYC1AvfnrFLhxeD82hz.jpg' },
  'Arcane':           { backdrop: 'https://image.tmdb.org/t/p/original/fqldf2t8ztc9aiwn3k6mlX3tvRT.jpg' },
  'Mirzapur':         { backdrop: 'https://image.tmdb.org/t/p/original/cGPSFxAVGTkFO5OKQi8h6sRJJCi.jpg' },
  'Ozark':            { backdrop: 'https://image.tmdb.org/t/p/original/pCGyPECgCqRqFDkknvaD7DcNMHK.jpg' },
  'Severance':        { backdrop: 'https://image.tmdb.org/t/p/original/26OeSAOBmNRWAEpNwJlGpWkOCKX.jpg' },
  'Better Call Saul': { backdrop: 'https://image.tmdb.org/t/p/original/fC2HDm5t0kH7mTm7jxMR31b7by.jpg' },
  'House of the Dragon':{ backdrop: 'https://image.tmdb.org/t/p/original/z2yahl2uefxDCl0nogcRBstwruJ.jpg' },
  'The Wire':         { backdrop: 'https://image.tmdb.org/t/p/original/4sHeTAp65WrSSuc05HRpJKBFUoL.jpg' },
  'Chernobyl':        { backdrop: 'https://image.tmdb.org/t/p/original/hlLXt2tOPT6RRnjiUmoxyG1LTFi.jpg' }
};

/* Override openModal to load series backdrops */
function openModal(title, year, duration, rating, genre, desc, posterUrl) {
  currentMovie = { title:title, year:year, duration:duration, rating:rating, genre:genre, desc:desc, poster:posterUrl };
  var info = showDB[title] || {};
  document.getElementById('modalTitle').textContent     = title;
  document.getElementById('modalYear').textContent      = year;
  document.getElementById('modalDuration').textContent  = duration;
  document.getElementById('modalRating').textContent    = rating;
  document.getElementById('modalDesc').textContent      = desc;
  document.getElementById('modalGenrePill').textContent = genre;
  var poster = document.getElementById('modalPoster');
  poster.src = posterUrl;
  poster.onerror = function() {
    poster.src = 'https://placehold.co/680x380/1c1c23/e63946?text=' + encodeURIComponent(title);
  };
  if (info.backdrop && info.backdrop !== posterUrl) {
    var test = new Image();
    test.onload = function() { if (currentMovie && currentMovie.title === title) poster.src = info.backdrop; };
    test.src = info.backdrop;
  }
  var wlBtn = document.querySelector('.modal-btn-list');
  if (wlBtn) wlBtn.textContent = isInWatchlist(title) ? '✓ In Watchlist' : '＋ Watchlist';
  document.getElementById('modalOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

var CAT_LABELS = {
  all:'📺 All', trending:'🔥 Trending', action:'💥 Action', comedy:'😂 Comedy',
  thriller:'🔪 Thriller & Horror', 'sci-fi':'🚀 Sci-Fi & Fantasy',
  romance:'❤️ Romance', drama:'🎭 Drama & Crime', animation:'🎌 Animation'
};
JSEND;

include 'includes/header.php';
?>

<style>
.adv-filter-bar { background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;
    padding:16px 20px;margin:0 20px 20px;display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end; }
.adv-filter-bar label { font-size:11px;color:#888;text-transform:uppercase;font-weight:700;display:block;margin-bottom:4px; }
.adv-filter-bar select, .adv-filter-bar input {
    padding:8px 12px;background:#111;border:1px solid rgba(255,255,255,0.1);
    border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:13px;width:110px; }
.adv-filter-bar select:focus, .adv-filter-bar input:focus { outline:none;border-color:var(--red); }
.adv-filter-apply { padding:8px 18px;background:var(--red);color:#fff;border:none;border-radius:8px;
    font-size:13px;font-weight:700;cursor:pointer;transition:.2s;font-family:'Nunito',sans-serif;align-self:flex-end; }
</style>

<!-- Category Bar -->
<div class="category-bar" id="categoryBar">
    <div class="cat-chips" id="catChips">
        <?php
        $cats = ['all'=>'📺 All','trending'=>'🔥 Trending','action'=>'💥 Action','comedy'=>'😂 Comedy',
                 'thriller'=>'🔪 Thriller & Horror','sci-fi'=>'🚀 Sci-Fi','romance'=>'❤️ Romance',
                 'drama'=>'🎭 Drama & Crime','animation'=>'🎌 Animation'];
        foreach ($cats as $key => $label):
            $active = $catFilter === $key ? 'active' : '';
        ?>
        <button class="cat-chip <?= $active ?>" onclick="window.location.href='series.php?cat=<?= $key ?>&sort=<?= $sortBy ?>&min_rating=<?= $minRating ?>&year_from=<?= $yearFrom ?>&year_to=<?= $yearTo ?>'"><?= $label ?></button>
        <?php endforeach; ?>
    </div>
</div>

<!-- Advanced Filters -->
<form method="GET" class="adv-filter-bar">
    <input type="hidden" name="cat" value="<?= htmlspecialchars($catFilter) ?>">
    <div>
        <label>Sort By</label>
        <select name="sort">
            <option value="newest" <?= $sortBy==='newest' ? 'selected':'' ?>>Newest</option>
            <option value="rating" <?= $sortBy==='rating' ? 'selected':'' ?>>Top Rated</option>
            <option value="oldest" <?= $sortBy==='oldest' ? 'selected':'' ?>>Oldest</option>
            <option value="az"     <?= $sortBy==='az'     ? 'selected':'' ?>>A–Z</option>
        </select>
    </div>
    <div>
        <label>Min Rating</label>
        <input type="number" name="min_rating" min="0" max="10" step="0.5" value="<?= $minRating ?: '' ?>" placeholder="0.0">
    </div>
    <div>
        <label>Year From</label>
        <input type="number" name="year_from" min="1900" max="2030" value="<?= $yearFrom ?: '' ?>" placeholder="2000">
    </div>
    <div>
        <label>Year To</label>
        <input type="number" name="year_to" min="1900" max="2030" value="<?= $yearTo ?: '' ?>" placeholder="2025">
    </div>
    <div style="flex-grow:1;max-width:250px">
        <label>Title</label>
        <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Search series..." style="width:100%">
    </div>
    <button type="submit" class="adv-filter-apply">Apply</button>
    <?php if ($minRating || $yearFrom || $yearTo || $q): ?>
    <a href="series.php?cat=<?= $catFilter ?>" style="color:#666;font-size:12px;align-self:center;text-decoration:none">✕ Reset</a>
    <?php endif; ?>
</form>

<!-- Content Grid -->
<section class="content-section" data-section="all">
    <div class="section-header">
        <h2 class="section-title">📺 TV Shows <span style="font-size:14px;color:#666;font-weight:400">(<?= count($items) ?> titles)</span></h2>
    </div>
    <div class="row" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:20px;padding:0 20px">
        <?php foreach ($items as $m): ?>
        <div class="movie-card" onclick="window.location.href='watch.php?id=<?= $m['id'] ?>'">
            <div class="card-img-wrap">
                <img src="<?= htmlspecialchars($m['poster'], ENT_QUOTES) ?>" loading="lazy"
                    alt="<?= htmlspecialchars($m['title'], ENT_QUOTES) ?>"
                    onerror="this.src='https://placehold.co/300x450/111/fff?text=<?= urlencode($m['title']) ?>'">
                <div class="card-overlay"><div class="card-play-btn"></div></div>
                <div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);padding:2px 7px;border-radius:6px;font-size:11px;color:var(--gold)">
                    ⭐ <?= htmlspecialchars($m['rating']) ?>
                </div>
            </div>
            <div class="card-info">
                <span class="card-title"><?= htmlspecialchars($m['title']) ?></span>
                <span style="font-size:11px;color:#555;display:block;margin-top:2px"><?= htmlspecialchars($m['year']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($items)): ?>
        <div style="grid-column:1/-1;text-align:center;padding:80px;color:#555">
            <div style="font-size:48px;margin-bottom:16px">📺</div>
            <h3>No series match your filters.</h3>
            <a href="series.php" style="color:var(--red);text-decoration:none;margin-top:12px;display:inline-block">Clear Filters</a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>