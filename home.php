<?php
session_start();
require_once 'api/db_connect.php';

// Fetch top 10 trending/top rated for hero banner
$heroStmt = $pdo->query("SELECT id, title, release_year, content_type, rating, genre, description, poster_url, backdrop_url FROM content ORDER BY RAND() LIMIT 10");
$heroMovies = $heroStmt->fetchAll();
$heroJsArr = [];
foreach ($heroMovies as $m) {
    $heroJsArr[] = [
        'id'     => $m['id'],
        'title'  => $m['title'],
        'year'   => $m['release_year'],
        'type'   => $m['content_type'] === 'series' ? 'Series' : 'Movie',
        'rating' => $m['rating'],
        'genre'  => $m['genre'],
        'desc'   => $m['description'],
        'poster' => $m['backdrop_url'] ?: $m['poster_url']
    ];
}

$pageTitle = 'CineVault – Home';
$activePage = 'home';
$pageScript = 'const heroMovies = ' . json_encode($heroJsArr) . ';
let heroIdx = 0;
function changeBanner() {
    if (heroMovies.length === 0) return;
    var m = heroMovies[heroIdx];
    var bg = document.getElementById("heroBanner");
    if(bg) bg.style.backgroundImage = "url(\'" + m.poster + "\')";
    var titleEl = document.getElementById("movieTitle");
    if(titleEl) titleEl.innerText = m.title;
    var typeEl = document.getElementById("movieType");
    if(typeEl) typeEl.innerText = m.type;
    var descEl = document.getElementById("movieDesc");
    if(descEl) descEl.innerText = m.desc;
    var genreEl = document.getElementById("movieGenre");
    if(genreEl) genreEl.innerText = m.genre;
    var ratingEl = document.getElementById("movieRating");
    if(ratingEl) ratingEl.innerText = m.rating;
    var yearEl = document.getElementById("movieYear");
    if(yearEl) yearEl.innerText = m.year;
    heroIdx = (heroIdx + 1) % heroMovies.length;
}

function heroPlay() {
    var m = heroMovies[heroIdx > 0 ? heroIdx - 1 : (heroMovies.length > 0 ? heroMovies.length - 1 : 0)];
    if (m && m.id) window.location.href = "watch.php?id=" + m.id;
}

function heroInfo() { heroPlay(); }

document.addEventListener("DOMContentLoaded", function() {
    changeBanner();
    setInterval(changeBanner, 5000);
});
';
include 'includes/header.php';
?>

<!-- -- HERO BANNER -- -->
<section class="hero" id="heroBanner">
    <div class="hero-inner">
        <div class="hero-content">
            <h1 id="movieTitle">ONE PIECE</h1>
            <div class="hero-meta">
                <span class="rating" style="color:#f5c518">? <span id="movieRating">8.5</span></span>
                <span class="meta-sep">�</span>
                <span id="movieYear">2023</span>
                <span class="meta-sep">�</span>
                <span class="meta-sep">●</span>
                <span id="movieType">Series</span>
                <span class="meta-sep">●</span>
                <span id="movieGenre">Action &amp; Adventure</span>
            </div>
            <p id="movieDesc">Luffy and the Straw Hats set sail for the Grand Line.</p>
            <div class="hero-btns">
                <button class="hero-btn-play" onclick="heroPlay()"><svg style="width:18px;height:18px;margin-right:8px;vertical-align:-3px" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg> Play</button>
                <button class="hero-btn-info" onclick="heroInfo()"><svg style="width:18px;height:18px;margin-right:8px;vertical-align:-3px" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg> More Info</button>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'api/db_connect.php';

// Continue Watching (real data for logged-in users)
$userId = $_SESSION['user_id'] ?? 0;
if ($userId) {
    $cwStmt = $pdo->prepare("SELECT c.*, wh.paused_at_seconds FROM watch_history wh JOIN content c ON wh.content_id=c.id WHERE wh.user_id=? ORDER BY wh.watched_at DESC LIMIT 8");
    $cwStmt->execute([$userId]);
    $cwItems = $cwStmt->fetchAll();
    if (count($cwItems) > 0) {
        echo '<section class="content-section">';
        echo '<div class="section-header"><h2 class="section-title">▶️ Continue Watching</h2></div>';
        echo '<div class="row-scroll">';
        foreach ($cwItems as $row) {
            preg_match('/(\d+)h\s*(\d+)m/', $row['duration'] ?? '', $dm);
            $totalSec = isset($dm[1]) ? ($dm[1]*3600+$dm[2]*60) : 7200;
            $pct = min(98, max(2, round(($row['paused_at_seconds'] / max(1,$totalSec)) * 100)));
            echo '<div class="movie-card" onclick="window.location.href=\'watch.php?id='.(int)$row['id'].'\'">';
            echo '<div class="card-img-wrap">';
            echo '<img src="'.htmlspecialchars($row['poster_url']).'" loading="lazy" alt="'.htmlspecialchars($row['title']).'" onerror="this.src=\'https://placehold.co/300x450/111/fff?text='.urlencode($row['title']).'\'">'; 
            echo '<div class="card-overlay"><div class="card-play-btn"></div></div>';
            echo '<div class="progress-bar-bg" style="position:absolute;bottom:0;left:0;right:0;margin:0;border-radius:0"><div class="progress-bar-fill" style="width:'.$pct.'%"></div></div>';
            echo '</div><div class="card-info"><span class="card-title">'.htmlspecialchars($row['title']).'</span></div></div>';
        }
        echo '</div></section>';
    }
}

// ── Smart Recommendations ─────────────────────────────────────────────────────
if ($userId) {
    // Find top genre from user's watch history
    $topGenreStmt = $pdo->prepare("
        SELECT c.genre FROM watch_history wh
        JOIN content c ON wh.content_id = c.id
        WHERE wh.user_id = ? AND c.genre IS NOT NULL AND c.genre != ''
        GROUP BY c.genre ORDER BY COUNT(*) DESC LIMIT 1
    ");
    $topGenreStmt->execute([$userId]);
    $topGenre = $topGenreStmt->fetchColumn();

    if ($topGenre) {
        // Get first keyword from genre string (e.g. "Action · Sci-Fi" → "Action")
        $genreKeyword = preg_split('/[\s·,|]+/', trim($topGenre))[0];
        $recStmt = $pdo->prepare("
            SELECT * FROM content
            WHERE genre LIKE ?
            AND id NOT IN (SELECT content_id FROM watch_history WHERE user_id = ?)
            ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 8
        ");
        $recStmt->execute(["%$genreKeyword%", $userId]);
        $recItems = $recStmt->fetchAll();

        if (count($recItems) > 0) {
            echo '<section class="content-section">';
            echo '<div class="section-header"><h2 class="section-title">🎯 Recommended For You</h2><span style="color:#888;font-size:13px;margin-left:12px">Based on your taste in ' . htmlspecialchars($genreKeyword) . '</span></div>';
            echo '<div class="row-scroll">';
            foreach ($recItems as $row) {
                echo '<div class="movie-card" onclick="window.location.href=\'watch.php?id='.(int)$row['id'].'\'">';
                echo '<div class="card-img-wrap">';
                echo '<img src="'.htmlspecialchars($row['poster_url']).'" loading="lazy" alt="'.htmlspecialchars($row['title']).'" onerror="this.src=\'https://placehold.co/300x450/111/fff?text='.urlencode($row['title']).'\'">'; 
                echo '<div class="card-overlay"><div class="card-play-btn"></div></div>';
                echo '<div style="position:absolute;top:8px;left:8px;background:rgba(230,57,70,.85);padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;color:#fff;letter-spacing:.5px">FOR YOU</div>';
                echo '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);padding:2px 7px;border-radius:6px;font-size:11px;color:#f5c518">⭐ '.htmlspecialchars($row['rating']).'</div>';
                echo '</div><div class="card-info"><span class="card-title">'.htmlspecialchars($row['title']).'</span></div></div>';
            }
            echo '</div></section>';
        }
    }
} else {
    // Fallback for guests: top-rated overall (label differently)
    $recStmt = $pdo->query("SELECT * FROM content ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 8");
    $recItems = $recStmt->fetchAll();
    if (count($recItems) > 0) {
        echo '<section class="content-section">';
        echo '<div class="section-header"><h2 class="section-title">⭐ Top Picks For You</h2><span style="color:#888;font-size:13px;margin-left:12px"><a href="login.php" style="color:var(--red);text-decoration:none">Sign in</a> to get personalised recommendations</span></div>';
        echo '<div class="row-scroll">';
        foreach ($recItems as $row) {
            echo '<div class="movie-card" onclick="window.location.href=\'watch.php?id='.(int)$row['id'].'\'">';
            echo '<div class="card-img-wrap">';
            echo '<img src="'.htmlspecialchars($row['poster_url']).'" loading="lazy" alt="'.htmlspecialchars($row['title']).'" onerror="this.src=\'https://placehold.co/300x450/111/fff?text='.urlencode($row['title']).'\'">'; 
            echo '<div class="card-overlay"><div class="card-play-btn"></div></div>';
            echo '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);padding:2px 7px;border-radius:6px;font-size:11px;color:#f5c518">⭐ '.htmlspecialchars($row['rating']).'</div>';
            echo '</div><div class="card-info"><span class="card-title">'.htmlspecialchars($row['title']).'</span></div></div>';
        }
        echo '</div></section>';
    }
}

// Other dynamic sections
$sections = [
    'Trending Now'        => 'SELECT * FROM content WHERE CAST(rating AS DECIMAL(4,1)) > 8.0 ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 12',
    'Action Blockbusters' => 'SELECT * FROM content WHERE genre LIKE "%Action%" ORDER BY CAST(release_year AS UNSIGNED) DESC LIMIT 12',
    '🎌 Top Anime'        => 'SELECT * FROM content WHERE content_type = "anime" OR category = "anime" ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 12',
    'Sci-Fi & Fantasy'    => 'SELECT * FROM content WHERE genre LIKE "%Sci-Fi%" OR genre LIKE "%Fantasy%" LIMIT 12',
    'Binge-Worthy Series' => 'SELECT * FROM content WHERE content_type = "series" ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 12',
    'New Releases'        => 'SELECT * FROM content ORDER BY CAST(release_year AS UNSIGNED) DESC LIMIT 12',
];

foreach ($sections as $title => $query) {
    $stmt    = $pdo->query($query);
    $results = $stmt->fetchAll();
    if (count($results) > 0) {
        echo '<section class="content-section">';
        echo '<div class="section-header"><h2 class="section-title">' . htmlspecialchars($title) . '</h2></div>';
        echo '<div class="row-scroll">';
        foreach ($results as $row) {
            echo '<div class="movie-card" onclick="window.location.href=\'watch.php?id='.(int)$row['id'].'\'">';
            echo '<div class="card-img-wrap">';
            echo '<img src="'.htmlspecialchars($row['poster_url']).'" loading="lazy" alt="'.htmlspecialchars($row['title']).'" onerror="this.src=\'https://placehold.co/300x450/111/fff?text='.urlencode($row['title']).'\'">'; 
            echo '<div class="card-overlay"><div class="card-play-btn"></div></div>';
            echo '<div style="position:absolute;top:8px;right:8px;background:rgba(0,0,0,.7);padding:2px 7px;border-radius:6px;font-size:11px;color:#f5c518">⭐ '.htmlspecialchars($row['rating']).'</div>';
            echo '</div><div class="card-info"><span class="card-title">'.htmlspecialchars($row['title']).'</span></div></div>';
        }
        echo '</div></section>';
    }
}
?>

<!-- ═══════════════════════════════════════════════════════
     PREMIUM MEMBERSHIP
     ═══════════════════════════════════════════════════════ -->
<section class="pricing-section">
    <div class="section-header" style="justify-content:center">
        <h2 class="section-title"> Upgrade to CineVault+</h2>
    </div>
    <div class="pricing-grid">
        <div class="price-card">
            <div class="price-title">Basic</div>
            <div class="price-amount">$0<span>/mo</span></div>
            <ul class="price-features">
                <li>Access 100+ Free Titles</li>
                <li>720p HD Streaming</li>
                <li>Ad-Supported Environment</li>
            </ul>
            <button class="glow-btn" style="background:#333;box-shadow:none;">Current Plan</button>
        </div>
        <div class="price-card premium">
            <div class="price-badge">POPULAR</div>
            <div class="price-title">Pro</div>
            <div class="price-amount">$9<span>/mo</span></div>
            <ul class="price-features">
                <li>Access Entire Vault</li>
                <li>1080p Ultra HD Streaming</li>
                <li>Ad-Free Environment</li>
                <li>Watch Party Privileges</li>
            </ul>
            <button class="glow-btn">Upgrade Now</button>
        </div>
        <div class="price-card">
            <div class="price-title">Family Max</div>
            <div class="price-amount">$14<span>/mo</span></div>
            <ul class="price-features">
                <li>Access Entire Vault</li>
                <li>4K HDR10+ Quality</li>
                <li>Offline Downloads</li>
                <li>5 Concurrent Screens</li>
            </ul>
            <button class="glow-btn" style="background:#555;box-shadow:none;">Choose Max</button>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
