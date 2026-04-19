<?php
session_start();
$pageTitle = 'CineVault – Home';
$activePage = 'home';
$pageScript = <<<'JSEND'
const heroMovies = [
    { title: "ONE PIECE", year: "2023", type: "Series", rating: "8.5", genre: "Action & Adventure", desc: "Luffy and the Straw Hats set sail for the Grand Line.", poster: "https://image.tmdb.org/t/p/original/r0Q6eeN9L1BgqTBVDilUW7CEHQZ.jpg" },
    { title: "JUJUTSU KAISEN", year: "2020", type: "Series", rating: "8.7", genre: "Animation, Action & Adventure", desc: "A boy swallows a cursed talisman and becomes cursed himself.", poster: "https://image.tmdb.org/t/p/original/hTP1DtLGFamjfu8WqjnuQdP1n4i.jpg" },
    { title: "ATTACK ON TITAN", year: "2013", type: "Series", rating: "9.0", genre: "Animation, Sci-Fi & Fantasy", desc: "Humanity fights back against the majestic, flesh-eating Titans.", poster: "https://image.tmdb.org/t/p/original/801v2KvewJps7lbC3EajgVheIfz.jpg" },
    { title: "THE LAST OF US", year: "2023", type: "Series", rating: "8.8", genre: "Drama, Sci-Fi & Survival", desc: "Joel and Ellie journey across a post-apocalyptic America.", poster: "https://image.tmdb.org/t/p/original/uKvVjHNqB5VmOrdxqAt2F7J78ED.jpg" },
    { title: "BREAKING BAD", year: "2008", type: "Series", rating: "9.5", genre: "Crime & Drama", desc: "A chemistry teacher turns to drug manufacturing to secure his family's future.", poster: "https://image.tmdb.org/t/p/original/ggFHVNu6YYI5L9pCfOacjizRGt.jpg" },
    { title: "STRANGER THINGS", year: "2016", type: "Series", rating: "8.7", genre: "Sci-Fi, Horror & Drama", desc: "A small town uncovers a supernatural mystery involving secret experiments.", poster: "https://image.tmdb.org/t/p/original/49WJfeN0moxb9IPfGn8AIqMGskD.jpg" },
    { title: "HOUSE OF THE DRAGON", year: "2022", type: "Series", rating: "8.4", genre: "Fantasy & Drama", desc: "The Targaryen civil war unfolds 200 years before Game of Thrones.", poster: "https://image.tmdb.org/t/p/original/z2yahl2uefxDCl0nogcRBstwruJ.jpg" },
    { title: "INTERSTELLAR", year: "2014", type: "Movie", rating: "8.6", genre: "Sci-Fi & Drama", desc: "A team of explorers travel through a wormhole in space in an attempt to ensure humanity's survival.", poster: "https://image.tmdb.org/t/p/original/rAiYTfKGqDCRIIqo664sY9XZIvQ.jpg" },
    { title: "THE DARK KNIGHT", year: "2008", type: "Movie", rating: "9.0", genre: "Action & Crime", desc: "Batman faces his greatest psychological and physical test against the Joker.", poster: "https://image.tmdb.org/t/p/original/nMKdUUepR0i5zn0y1T4CsSB5chy.jpg" },
    { title: "INCEPTION", year: "2010", type: "Movie", rating: "8.8", genre: "Sci-Fi & Thriller", desc: "A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task.", poster: "https://image.tmdb.org/t/p/original/8ZTVqvKDQ8emSGUEMjsS4yHAwrp.jpg" },
    { title: "DEADPOOL & WOLVERINE", year: "2024", type: "Movie", rating: "8.1", genre: "Action & Comedy", desc: "Deadpool reunites with Wolverine for a multiverse-spanning adventure.", poster: "https://image.tmdb.org/t/p/original/9l1eZiJHmhr5jIlthMdJN5WYoff.jpg" },
    { title: "AVENGERS: ENDGAME", year: "2019", type: "Movie", rating: "8.4", genre: "Action & Sci-Fi", desc: "The remaining Avengers assemble once more to reverse Thanos's actions.", poster: "https://image.tmdb.org/t/p/original/orjiB3oUIsyz60hoEqkiGpy5CeO.jpg" }
];
let heroIdx = 0;
function changeBanner() {
    var m = heroMovies[heroIdx];
    var bg = document.getElementById("heroBanner");
    if(bg) bg.style.backgroundImage = "url('" + m.poster + "')";
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
    var m = heroMovies[heroIdx > 0 ? heroIdx - 1 : 0];
    if (m) openModal(m.title, m.year, m.type, m.rating, m.genre, m.desc, m.poster);
}

function heroInfo() { heroPlay(); }

document.addEventListener("DOMContentLoaded", function() {
    changeBanner();
    setInterval(changeBanner, 5000);
});
JSEND;
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
                <span id="movieType">Series</span>
                <span class="meta-sep">�</span>
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

// Other dynamic sections
$sections = [
    'Trending Now'        => 'SELECT * FROM content WHERE CAST(rating AS DECIMAL(4,1)) > 8.0 ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 8',
    'Action Blockbusters' => 'SELECT * FROM content WHERE genre LIKE "%Action%" ORDER BY CAST(release_year AS UNSIGNED) DESC LIMIT 8',
    'Sci-Fi & Fantasy'    => 'SELECT * FROM content WHERE genre LIKE "%Sci-Fi%" OR genre LIKE "%Fantasy%" LIMIT 8',
    'Binge-Worthy Series' => 'SELECT * FROM content WHERE content_type = "series" ORDER BY CAST(rating AS DECIMAL(4,1)) DESC LIMIT 8',
    'New Releases'        => 'SELECT * FROM content ORDER BY CAST(release_year AS UNSIGNED) DESC LIMIT 8',
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
