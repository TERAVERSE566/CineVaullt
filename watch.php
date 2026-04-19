<?php
session_start();
require_once 'api/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: home.php'); exit; }

$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
$stmt->execute([$id]);
$item = $stmt->fetch();
if (!$item) { echo "<h1 style='color:#fff;text-align:center;padding:80px'>Content not found. <a href='home.php' style='color:#e63946'>Go Back</a></h1>"; exit; }

// Update view count (safe — silently fails if column not added yet)
try { $pdo->prepare("UPDATE content SET view_count = view_count + 1 WHERE id = ?")->execute([$id]); } catch (PDOException $e) {}

$pageTitle = htmlspecialchars($item['title']) . ' – CineVault';
$activePage = 'watch';

// Watch history
$pausedAt = 0;
if (isset($_SESSION['user_id'])) {
    $h = $pdo->prepare("SELECT paused_at_seconds FROM watch_history WHERE user_id = ? AND content_id = ?");
    $h->execute([$_SESSION['user_id'], $id]);
    $pausedAt = $h->fetchColumn() ?: 0;
}

// Recommendations
$genres   = explode('·', $item['genre']);
$g1       = trim($genres[0] ?? 'Action');
$recStmt  = $pdo->prepare("SELECT * FROM content WHERE genre LIKE ? AND id != ? ORDER BY RAND() LIMIT 10");
$recStmt->execute(['%'.$g1.'%', $id]);
$recommendations = $recStmt->fetchAll();

// Comments with like info
$userId = $_SESSION['user_id'] ?? 0;
$commentStmt = $pdo->prepare("
    SELECT c.id, c.comment_text, c.created_at,
           COALESCE(c.likes_count, 0) as likes_count,
           u.username,
           COALESCE(u.avatar_url, '') as avatar_url,
           (SELECT COUNT(*) FROM comment_likes cl WHERE cl.comment_id = c.id AND cl.user_id = ?) as user_liked
    FROM comments c JOIN users u ON c.user_id = u.id
    WHERE c.content_id = ? ORDER BY c.created_at DESC
");
$commentStmt->execute([$userId, $id]);
$comments = $commentStmt->fetchAll();

// Watchlist check
$inWatchlist = false;
if ($userId) {
    $wl = $pdo->prepare("SELECT id FROM watchlist WHERE user_id = ? AND content_id = ?");
    $wl->execute([$userId, $id]);
    $inWatchlist = $wl->rowCount() > 0;
}

// User rating
$userRating = 0;
$communityRating = ['avg_rating' => 0, 'total' => 0];
if ($userId) {
    $ur = $pdo->prepare("SELECT rating FROM user_ratings WHERE user_id = ? AND content_id = ?");
    $ur->execute([$userId, $id]);
    $userRating = $ur->fetchColumn() ?: 0;
}
$cr = $pdo->prepare("SELECT ROUND(AVG(rating),1) as avg_rating, COUNT(*) as total FROM user_ratings WHERE content_id = ?");
$cr->execute([$id]);
$communityRating = $cr->fetch();

// Seasons & Episodes (for series)
$seasons = [];
if ($item['content_type'] === 'series') {
    $sStmt = $pdo->prepare("SELECT * FROM seasons WHERE content_id = ? ORDER BY season_number");
    $sStmt->execute([$id]);
    $seasons = $sStmt->fetchAll();
    foreach ($seasons as &$season) {
        $eStmt = $pdo->prepare("SELECT * FROM episodes WHERE season_id = ? ORDER BY episode_number");
        $eStmt->execute([$season['id']]);
        $season['episodes'] = $eStmt->fetchAll();
    }
    unset($season);
}

include 'includes/header.php';
?>

<style>
.watch-hero { background: rgba(0,0,0,0.3); padding: 40px 0 0; }
.watch-container { max-width: 1200px; margin: 0 auto; padding: 30px 20px 0; }
.watch-player-wrapper { background: #000; border-radius: 16px; overflow: hidden; aspect-ratio: 16/9; position: relative; }
.watch-video { width: 100%; height: 100%; display: block; }
.watch-meta { margin-top: 28px; }
.watch-title { font-family: var(--font-display); font-size: 42px; margin-bottom: 12px; }
.watch-stats { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; color: #aaa; font-size: 15px; margin-bottom: 16px; }
.watch-rating { color: var(--gold); font-weight: 800; }
.watch-genre { background: rgba(255,255,255,0.08); padding: 3px 12px; border-radius: 20px; font-size: 13px; }
.watch-desc { color: #bbb; line-height: 1.7; font-size: 16px; max-width: 800px; margin-bottom: 24px; }
.watch-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 40px; }

/* Community Rating */
.rating-section { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 16px; padding: 24px; margin-bottom: 32px; }
.rating-section h3 { font-size: 18px; margin-bottom: 16px; }
.star-row { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
.star-btn { background: none; border: none; font-size: 28px; cursor: pointer; transition: transform .15s; color: #444; padding: 2px; line-height: 1; }
.star-btn:hover, .star-btn.active { color: var(--gold); transform: scale(1.2); }
.community-rating-display { margin-top: 14px; display: flex; align-items: center; gap: 16px; }
.community-avg { font-size: 36px; font-weight: 800; color: var(--gold); }
.community-info { color: #888; font-size: 14px; }
.your-rating-label { color: #aaa; font-size: 14px; margin-bottom: 8px; }
#ratingMsg { font-size: 13px; margin-top: 8px; color: #0c6; min-height: 18px; }

/* Seasons/Episodes */
.episodes-section { margin-bottom: 40px; }
.season-tabs { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.season-tab { padding: 8px 20px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);
    border-radius: 20px; color: #aaa; cursor: pointer; font-size: 14px; transition: all .2s; font-family:'Nunito',sans-serif; }
.season-tab.active { background: var(--red); color: #fff; border-color: var(--red); }
.episode-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(280px,1fr)); gap: 16px; }
.ep-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px;
    overflow: hidden; cursor: pointer; transition: all .2s; }
.ep-card:hover { border-color: var(--red); transform: translateY(-2px); }
.ep-thumb { aspect-ratio: 16/9; background: #111; overflow: hidden; position: relative; }
.ep-thumb img { width:100%;height:100%;object-fit:cover; }
.ep-thumb-play { position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
    background:rgba(0,0,0,.5);opacity:0;transition:.2s;font-size:32px; }
.ep-card:hover .ep-thumb-play { opacity:1; }
.ep-info { padding: 12px; }
.ep-num { font-size: 11px; color: #666; margin-bottom: 4px; }
.ep-title { font-weight: 700; font-size: 14px; margin-bottom: 4px; }
.ep-dur { font-size: 12px; color: #777; }
.no-episodes { color: #555; padding: 20px; text-align: center; }

/* Comments */
.comments-section { max-width: 1200px; margin: 0 auto; padding: 0 20px 60px; }
.comment-item { display: flex; gap: 14px; padding: 18px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
.comment-avatar { width: 42px; height: 42px; min-width: 42px; border-radius: 50%; background: linear-gradient(135deg,var(--red),#800);
    display:flex;align-items:center;justify-content:center;font-weight:800;font-size:16px;overflow:hidden; }
.comment-avatar img { width:100%;height:100%;object-fit:cover; }
.comment-body { flex: 1; }
.comment-author { font-weight: 700; margin-bottom: 5px; }
.comment-time { color: #666; font-size: 12px; margin-left: 8px; font-weight: 400; }
.comment-text { color: #ccc; line-height: 1.6; font-size: 15px; }
.comment-actions { display: flex; gap: 10px; margin-top: 10px; align-items: center; }
.like-btn { background: none; border: 1px solid rgba(255,255,255,0.1); color: #888; padding: 4px 12px;
    border-radius: 20px; cursor: pointer; font-size: 13px; transition: all .2s; font-family:'Nunito',sans-serif; }
.like-btn:hover, .like-btn.liked { border-color: var(--red); color: var(--red); background: rgba(230,57,70,0.08); }
.comment-form-container { background: rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:12px; padding:20px; margin-bottom:24px; }
.comment-form-container textarea { width:100%;padding:12px;background:#0e0e16;border:1px solid rgba(255,255,255,0.1);
    border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:14px;resize:vertical;min-height:90px; }
.comment-form-container textarea:focus { outline:none;border-color:var(--red); }
</style>

<div class="watch-container">
    <!-- Player -->
    <div class="watch-player-wrapper">
        <?php if (!empty($item['video_url'])): ?>
        <video class="watch-video" id="mainVideoPlayer" controls <?= $pausedAt > 0 ? '' : 'autoplay' ?>>
            <source src="<?= htmlspecialchars($item['video_url']) ?>" type="video/mp4">
            Your browser does not support video.
        </video>
        <?php else: ?>
        <div style="display:flex;align-items:center;justify-content:center;height:100%;background:#0a0a14;flex-direction:column;gap:16px;min-height:400px">
            <?php
            // Try to load trailer from TRAILERS map in JS or trailer_url field
            $trailerId = $item['trailer_url'] ?? '';
            if ($trailerId):
            ?>
            <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($trailerId) ?>?autoplay=1&rel=0"
                style="width:100%;height:100%;position:absolute;inset:0;border:none"
                allow="autoplay;encrypted-media;fullscreen" allowfullscreen></iframe>
            <?php else: ?>
            <div style="font-size:60px">🎬</div>
            <p style="color:#666;font-size:18px">No video file assigned yet.</p>
            <p style="color:#444;font-size:14px">An admin can add a video URL in the Admin Console.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Metadata -->
    <div class="watch-meta">
        <h1 class="watch-title"><?= htmlspecialchars($item['title']) ?></h1>
        <div class="watch-stats">
            <span class="watch-rating">⭐ <?= htmlspecialchars($item['rating']) ?></span>
            <span class="meta-sep">·</span>
            <span><?= htmlspecialchars($item['release_year']) ?></span>
            <span class="meta-sep">·</span>
            <span><?= htmlspecialchars($item['duration']) ?></span>
            <span class="meta-sep">·</span>
            <span class="watch-genre"><?= htmlspecialchars($item['genre']) ?></span>
            <?php if ($item['content_type'] === 'series'): ?><span class="watch-genre">📺 Series</span><?php else: ?><span class="watch-genre">🎬 Movie</span><?php endif; ?>
        </div>
        <p class="watch-desc"><?= htmlspecialchars($item['description']) ?></p>

        <div class="watch-actions">
            <?php if ($userId): ?>
            <button id="addWatchlistBtn" class="glow-btn" style="padding:10px 25px;font-size:14px;<?= $inWatchlist ? 'background:#0a0;color:#fff;' : '' ?>">
                <?= $inWatchlist ? '✓ In Watchlist' : '＋ Add to Watchlist' ?>
            </button>
            <?php else: ?>
            <button class="glow-btn" onclick="window.location.href='login.php'" style="padding:10px 25px;font-size:14px">＋ Add to Watchlist</button>
            <?php endif; ?>
            <button class="glow-btn" style="padding:10px 25px;font-size:14px;background:#333;box-shadow:none" onclick="shareCurrentPage()">↗ Share</button>
            <?php if (($user['plan'] ?? 'basic') !== 'basic' || !$userId): ?>
            <button class="glow-btn" style="padding:10px 25px;font-size:14px;background:#225;box-shadow:none" onclick="showToast('⬇️ Download starting…')">⬇ Download</button>
            <?php else: ?>
            <button class="glow-btn" style="padding:10px 25px;font-size:14px;background:#222;box-shadow:none;cursor:not-allowed" onclick="showToast('💎 Upgrade to Pro to download!')">⬇ Download (Pro)</button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Community Rating -->
    <div class="rating-section">
        <h3>⭐ Rate This <?= ucfirst($item['content_type']) ?></h3>
        <?php if ($userId): ?>
        <p class="your-rating-label">Your rating: <strong id="yourRatingText"><?= $userRating ? "$userRating/10" : 'Not rated yet' ?></strong></p>
        <div class="star-row" id="starRow">
            <?php for ($s = 1; $s <= 10; $s++): ?>
            <button class="star-btn <?= $userRating >= $s ? 'active' : '' ?>" data-val="<?= $s ?>" onclick="rateContent(<?= $s ?>)">★</button>
            <?php endfor; ?>
        </div>
        <div id="ratingMsg"></div>
        <?php else: ?>
        <p style="color:#666">Please <a href="login.php" style="color:var(--red)">sign in</a> to rate this content.</p>
        <?php endif; ?>
        <div class="community-rating-display">
            <div class="community-avg" id="communityAvg"><?= $communityRating['avg_rating'] ?: '–' ?></div>
            <div class="community-info">
                Community Average<br>
                <span id="communityTotal"><?= $communityRating['total'] ?></span> ratings
            </div>
        </div>
    </div>

    <!-- Seasons & Episodes (Series only) -->
    <?php if ($item['content_type'] === 'series'): ?>
    <div class="episodes-section">
        <div class="section-header"><h2 class="section-title">📺 Episodes</h2></div>
        <?php if (count($seasons) > 0): ?>
        <div class="season-tabs">
            <?php foreach ($seasons as $i => $season): ?>
            <button class="season-tab <?= $i === 0 ? 'active' : '' ?>" onclick="showSeason(<?= $season['id'] ?>, this)">
                Season <?= $season['season_number'] ?><?= $season['title'] ? ': '.$season['title'] : '' ?>
            </button>
            <?php endforeach; ?>
        </div>
        <?php foreach ($seasons as $i => $season): ?>
        <div class="episode-grid season-panel" id="season-<?= $season['id'] ?>" style="<?= $i > 0 ? 'display:none' : '' ?>">
            <?php if (count($season['episodes']) > 0): ?>
            <?php foreach ($season['episodes'] as $ep): ?>
            <div class="ep-card" onclick="playEpisode('<?= htmlspecialchars(addslashes($ep['video_url'] ?? '')) ?>', '<?= htmlspecialchars(addslashes($ep['title'])) ?>')">
                <div class="ep-thumb">
                    <img src="<?= htmlspecialchars($ep['thumbnail_url'] ?? $item['poster_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($ep['title']) ?>">
                    <div class="ep-thumb-play">▶</div>
                </div>
                <div class="ep-info">
                    <div class="ep-num">Episode <?= $ep['episode_number'] ?></div>
                    <div class="ep-title"><?= htmlspecialchars($ep['title']) ?></div>
                    <?php if ($ep['duration']): ?><div class="ep-dur">⏱ <?= htmlspecialchars($ep['duration']) ?></div><?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?><div class="no-episodes">No episodes added yet. Check back soon!</div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="no-episodes">No episode data available yet for this series.</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Recommendations -->
<?php if (count($recommendations) > 0): ?>
<section class="content-section" style="padding:0 calc((100% - 1200px)/2 + 20px)">
    <div class="section-header"><h2 class="section-title">More Like This</h2></div>
    <div class="row-scroll">
        <?php foreach ($recommendations as $rec): ?>
        <div class="movie-card" onclick="window.location.href='watch.php?id=<?= $rec['id'] ?>'">
            <div class="card-img-wrap">
                <img src="<?= htmlspecialchars($rec['poster_url']) ?>" loading="lazy" alt="<?= htmlspecialchars($rec['title']) ?>">
                <div class="card-overlay"><div class="card-play-btn"></div></div>
            </div>
            <div class="card-info"><span class="card-title"><?= htmlspecialchars($rec['title']) ?></span></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- Comments -->
<section class="comments-section">
    <div class="section-header">
        <h2 class="section-title">Community Reviews <span style="font-size:14px;color:#666">(<?= count($comments) ?>)</span></h2>
    </div>

    <?php if ($userId): ?>
    <div class="comment-form-container">
        <form id="commentForm">
            <input type="hidden" id="content_id" value="<?= $id ?>">
            <textarea id="comment_text" placeholder="Write your review for <?= htmlspecialchars($item['title']) ?>…" required></textarea>
            <button type="submit" class="glow-btn" style="padding:10px 22px;font-size:14px;margin-top:10px">Post Review</button>
            <p id="comment_msg" style="display:none;margin-top:8px;color:#0c6;font-size:13px"></p>
        </form>
    </div>
    <?php else: ?>
    <p style="color:#666;margin-bottom:24px">Please <a href="login.php" style="color:var(--red)">sign in</a> to leave a review.</p>
    <?php endif; ?>

    <div class="comments-list" id="commentsList">
        <?php foreach ($comments as $c): ?>
        <div class="comment-item" id="comment-<?= $c['id'] ?>">
            <div class="comment-avatar">
                <?php if ($c['avatar_url']): ?>
                <img src="<?= htmlspecialchars($c['avatar_url']) ?>" alt="<?= htmlspecialchars($c['username']) ?>">
                <?php else: ?><?= strtoupper(substr($c['username'],0,1)) ?>
                <?php endif; ?>
            </div>
            <div class="comment-body">
                <div class="comment-author">
                    <?= htmlspecialchars($c['username']) ?>
                    <span class="comment-time"><?= date('M j, Y', strtotime($c['created_at'])) ?></span>
                </div>
                <div class="comment-text"><?= nl2br(htmlspecialchars($c['comment_text'])) ?></div>
                <div class="comment-actions">
                    <?php if ($userId): ?>
                    <button class="like-btn <?= $c['user_liked'] ? 'liked' : '' ?>" onclick="likeComment(<?= $c['id'] ?>, this)" id="like-<?= $c['id'] ?>">
                        ❤️ <span class="like-count"><?= (int)$c['likes_count'] ?></span>
                    </button>
                    <?php else: ?>
                    <span style="color:#555;font-size:13px">❤️ <?= (int)$c['likes_count'] ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if (count($comments) === 0): ?>
        <p class="no-comments" style="color:#555">No reviews yet. Be the first!</p>
        <?php endif; ?>
    </div>
</section>

<script>
const contentId = <?= $id ?>;
const isLogged  = <?= $userId ? 'true' : 'false' ?>;

// ── Video progress tracker ──
(function() {
    const video = document.getElementById('mainVideoPlayer');
    if (!video || !isLogged) return;
    const saved = <?= (int)$pausedAt ?>;
    if (saved > 5) video.currentTime = saved;
    setInterval(() => {
        if (!video.paused && video.currentTime > 0) {
            const fd = new FormData();
            fd.append('content_id', contentId);
            fd.append('current_time', Math.floor(video.currentTime));
            fetch('api/track_progress.php', { method:'POST', body:fd }).catch(()=>{});
        }
    }, 10000);
})();

// ── Watchlist toggle ──
const wlBtn = document.getElementById('addWatchlistBtn');
if (wlBtn) {
    wlBtn.addEventListener('click', async () => {
        const fd = new FormData();
        fd.append('content_id', contentId);
        const res  = await fetch('api/add_watchlist.php', { method:'POST', body:fd });
        const json = await res.json();
        if (json.status === 'added') {
            wlBtn.textContent = '✓ In Watchlist';
            wlBtn.style.background = '#0a0';
        } else if (json.status === 'removed') {
            wlBtn.textContent = '＋ Add to Watchlist';
            wlBtn.style.background = '';
        }
    });
}

// ── User Rating ──
async function rateContent(val) {
    const msg = document.getElementById('ratingMsg');
    const fd  = new FormData();
    fd.append('content_id', contentId);
    fd.append('rating', val);
    try {
        const res  = await fetch('api/rate_content.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status === 'success') {
            document.getElementById('yourRatingText').textContent = val + '/10';
            document.getElementById('communityAvg').textContent  = data.avg_rating;
            document.getElementById('communityTotal').textContent = data.total;
            msg.textContent = '✅ Rating saved!';
            document.querySelectorAll('.star-btn').forEach(b => {
                b.classList.toggle('active', parseInt(b.dataset.val) <= val);
            });
        } else { msg.textContent = '❌ ' + data.message; }
    } catch(e) { msg.textContent = '❌ Error'; }
}

// Star hover
document.querySelectorAll('.star-btn').forEach(b => {
    b.addEventListener('mouseover', () => {
        const v = parseInt(b.dataset.val);
        document.querySelectorAll('.star-btn').forEach(s => s.style.color = parseInt(s.dataset.val) <= v ? 'var(--gold)' : '#444');
    });
    b.addEventListener('mouseleave', () => {
        document.querySelectorAll('.star-btn').forEach(s => s.style.color = '');
    });
});

// ── Comment likes ──
async function likeComment(commentId, btn) {
    const fd = new FormData();
    fd.append('comment_id', commentId);
    const res  = await fetch('api/like_comment.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.status === 'liked' || data.status === 'unliked') {
        btn.querySelector('.like-count').textContent = data.likes;
        btn.classList.toggle('liked', data.status === 'liked');
    }
}

// ── Comment form ──
const commentForm = document.getElementById('commentForm');
if (commentForm) {
    commentForm.addEventListener('submit', async e => {
        e.preventDefault();
        const text = document.getElementById('comment_text').value;
        const msg  = document.getElementById('comment_msg');
        const fd   = new FormData();
        fd.append('content_id', contentId);
        fd.append('comment_text', text);
        const res  = await fetch('api/post_comment.php', { method:'POST', body:fd });
        const data = await res.json();
        if (data.status === 'success') {
            const list = document.getElementById('commentsList');
            const div  = document.createElement('div');
            div.className = 'comment-item';
            div.innerHTML = `
                <div class="comment-avatar">${data.initials}</div>
                <div class="comment-body">
                    <div class="comment-author">${data.username} <span class="comment-time">Just now</span></div>
                    <div class="comment-text">${text.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                    <div class="comment-actions"><button class="like-btn" onclick="likeComment(${data.comment_id},this)">❤️ <span class="like-count">0</span></button></div>
                </div>`;
            list.insertBefore(div, list.firstChild);
            commentForm.reset();
            const noC = list.querySelector('.no-comments');
            if (noC) noC.remove();
            msg.style.display = 'block';
            msg.textContent = '✅ Review posted!';
            setTimeout(() => msg.style.display='none', 3000);
        } else {
            msg.style.display='block'; msg.style.color='var(--red)'; msg.textContent = data.message || 'Failed.';
        }
    });
}

// ── Episode handling ──
function showSeason(id, btn) {
    document.querySelectorAll('.season-panel').forEach(p => p.style.display='none');
    document.querySelectorAll('.season-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('season-' + id).style.display = 'grid';
    btn.classList.add('active');
}

function playEpisode(url, title) {
    if (!url) { showToast('No video file for this episode yet'); return; }
    const v = document.getElementById('mainVideoPlayer');
    if (v) { v.src = url; v.play(); window.scrollTo({top:0,behavior:'smooth'}); }
    showToast('▶ Now playing: ' + title);
}
</script>

<?php include 'includes/footer.php'; ?>
