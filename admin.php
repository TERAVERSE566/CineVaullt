<?php
session_start();
require_once 'api/db_connect.php';

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetchColumn() !== 'admin') {
    die("<h1 style='color:red;text-align:center;padding:50px'>ACCESS DENIED</h1>");
}

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_content') {
    $title   = $_POST['title']        ?? '';
    $year    = $_POST['year']         ?? '';
    $dur     = $_POST['duration']     ?? '';
    $rating  = $_POST['rating']       ?? '';
    $genre   = $_POST['genre']        ?? '';
    $desc    = $_POST['desc']         ?? '';
    $poster  = $_POST['poster_url']   ?? '';
    $video   = $_POST['video_url']    ?? '';
    $trailer = $_POST['trailer_url']  ?? '';
    $type    = $_POST['content_type'] ?? 'movie';
    $cat     = $_POST['category']     ?? 'trending';
    if (!empty($title)) {
        try {
            $pdo->prepare("INSERT INTO content (title,release_year,duration,rating,genre,description,poster_url,video_url,trailer_url,content_type,category)
                VALUES (?,?,?,?,?,?,?,?,?,?,?)")->execute([$title,$year,$dur,$rating,$genre,$desc,$poster,$video,$trailer,$type,$cat]);
            $msg = "<div class='admin-msg ok'>✅ '$title' added successfully!</div>";
        } catch (PDOException $e) {
            $msg = "<div class='admin-msg err'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Analytics
$movieCount   = $pdo->query("SELECT COUNT(*) FROM content WHERE content_type='movie'")->fetchColumn();
$seriesCount  = $pdo->query("SELECT COUNT(*) FROM content WHERE content_type='series'")->fetchColumn();
$userCount    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$commentCount = $pdo->query("SELECT COUNT(*) FROM comments")->fetchColumn() ?? 0;
$wlCount      = $pdo->query("SELECT COUNT(*) FROM watchlist")->fetchColumn() ?? 0;
$ratingCount  = $pdo->query("SELECT COUNT(*) FROM user_ratings")->fetchColumn() ?? 0;

// Most watched
$topWatched = $pdo->query("SELECT c.title, COUNT(w.id) as watches FROM watch_history w JOIN content c ON c.id=w.content_id GROUP BY w.content_id ORDER BY watches DESC LIMIT 5")->fetchAll();
// Top rated
$topRated = $pdo->query("SELECT c.title, ROUND(AVG(r.rating),1) as avg_r, COUNT(r.id) as votes FROM user_ratings r JOIN content c ON c.id=r.content_id GROUP BY r.content_id ORDER BY avg_r DESC LIMIT 5")->fetchAll();
// Recent users
$recentUsers = $pdo->query("SELECT username, email, plan, created_at FROM users ORDER BY created_at DESC LIMIT 6")->fetchAll();
// All content for management table
$allContent  = $pdo->query("SELECT * FROM content ORDER BY id DESC LIMIT 50")->fetchAll();

$pageTitle  = 'CineVault – Admin';
$activePage = 'admin';
include 'includes/header.php';
?>
<style>
.admin-wrap { max-width: 1300px; margin: 40px auto; padding: 0 20px 80px; }
.admin-title { font-family: var(--font-display); font-size: 48px; color: var(--red); }
.admin-tabs { display: flex; gap: 10px; margin: 30px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
.admin-tab  { padding: 12px 24px; background: transparent; border: none; color: #888; font-size: 15px;
    cursor: pointer; border-bottom: 2px solid transparent; transition: all .2s; font-family: 'Nunito',sans-serif; font-weight:700; }
.admin-tab.active, .admin-tab:hover { color: #fff; border-bottom-color: var(--red); }
.tab-pane { display: none; } .tab-pane.active { display: block; }
/* Metrics */
.metrics-grid { display: grid; grid-template-columns: repeat(auto-fit,minmax(160px,1fr)); gap: 16px; margin-bottom: 36px; }
.metric-card { background: rgba(255,255,255,0.04); padding: 22px; border-radius: 12px; border-top: 3px solid; }
.metric-label { font-size: 12px; color: #aaa; text-transform: uppercase; letter-spacing: 1px; }
.metric-value { font-size: 38px; font-weight: 800; margin-top: 6px; }
/* Admin form */
.admin-form-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 16px; padding: 32px; }
.admin-form-card h2 { font-family: var(--font-display); font-size: 28px; margin-bottom: 24px; }
.admin-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
.admin-field { display: flex; flex-direction: column; gap: 6px; }
.admin-field label { color: #aaa; font-size: 13px; font-weight: 700; }
.admin-field input, .admin-field select, .admin-field textarea {
    padding: 11px 14px; background: #0e0e16; border: 1px solid rgba(255,255,255,0.1);
    border-radius: 8px; color: #fff; font-family:'Nunito',sans-serif; font-size:14px; }
.admin-field input:focus,.admin-field select:focus,.admin-field textarea:focus { outline:none;border-color:var(--red); }
.span2 { grid-column: span 2; }
.admin-msg { padding:12px 16px;border-radius:8px;margin-bottom:20px;font-weight:700;font-size:14px; }
.admin-msg.ok  { background:rgba(0,200,100,.1);border:1px solid rgba(0,200,100,.3);color:#0c6; }
.admin-msg.err { background:rgba(230,57,70,.1); border:1px solid rgba(230,57,70,.3); color:var(--red); }
/* Content management table */
.content-table { width:100%;border-collapse:collapse;font-size:13px; }
.content-table th { background:rgba(255,255,255,.05);padding:12px 10px;text-align:left;color:#aaa;font-size:12px;text-transform:uppercase; }
.content-table td { padding:10px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:middle; }
.content-table tr:hover td { background:rgba(255,255,255,.02); }
.tbl-poster { width:40px;height:56px;object-fit:cover;border-radius:4px; }
.edit-btn { background:rgba(255,193,7,.15);color:var(--gold);border:1px solid rgba(255,193,7,.3);padding:5px 12px;border-radius:5px;cursor:pointer;font-size:12px;transition:.2s; }
.edit-btn:hover { background:rgba(255,193,7,.3); }
.del-btn  { background:rgba(230,57,70,.15);color:var(--red); border:1px solid rgba(230,57,70,.3); padding:5px 12px;border-radius:5px;cursor:pointer;font-size:12px;transition:.2s;margin-left:5px; }
.del-btn:hover  { background:rgba(230,57,70,.3); }
/* Analytics tables */
.analytic-table { width:100%;border-collapse:collapse;font-size:14px; }
.analytic-table th,.analytic-table td { padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.05);text-align:left; }
.analytic-table th { color:#aaa;font-size:12px;text-transform:uppercase; }
.analytic-two { display:grid;grid-template-columns:1fr 1fr;gap:24px; }
.analytic-box { background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px; }
.analytic-box h3 { font-size:16px;margin-bottom:16px;color:#ddd; }
/* Users table */
.users-table { width:100%;border-collapse:collapse;font-size:13px; }
.users-table th,.users-table td { padding:12px;border-bottom:1px solid rgba(255,255,255,.05); }
.users-table th { color:#aaa;font-size:12px;text-transform:uppercase; }
.plan-badge { display:inline-block;padding:3px 8px;border-radius:12px;font-size:11px;font-weight:700; }
.plan-basic { background:rgba(255,255,255,.1);color:#aaa; }
.plan-pro   { background:rgba(230,57,70,.2);color:var(--red); }
.plan-max   { background:rgba(255,193,7,.2);color:var(--gold); }
/* Edit Modal */
.edit-modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,.75);z-index:9999;align-items:center;justify-content:center; }
.edit-modal-overlay.open { display:flex; }
.edit-modal { background:#16161f;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;width:min(700px,95vw);max-height:90vh;overflow-y:auto; }
.edit-modal h2 { font-family:var(--font-display);font-size:28px;margin-bottom:24px;color:var(--gold); }
/* Upload UI */
.upload-row { display:flex;gap:8px;align-items:center; }
.upload-row input[type=text] { flex:1; }
.upload-btn { padding:8px 14px;background:rgba(255,255,255,.07);color:#ccc;border:1px solid rgba(255,255,255,.12);border-radius:8px;font-size:13px;cursor:pointer;white-space:nowrap;transition:.2s;font-family:'Nunito',sans-serif; }
.upload-btn:hover { background:rgba(255,255,255,.13);color:#fff; }
/* Episode manager */
.ep-season { background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:10px;margin-bottom:16px;overflow:hidden; }
.ep-season-hdr { background:rgba(255,255,255,.05);padding:12px 16px;font-weight:700;display:flex;align-items:center;justify-content:space-between; }
.ep-row { display:flex;align-items:center;gap:10px;padding:10px 16px;border-top:1px solid rgba(255,255,255,.04);font-size:13px; }
.ep-row:hover { background:rgba(255,255,255,.03); }
</style>

<div class="admin-wrap">
    <h1 class="admin-title">⚡ Admin Console</h1>
    <p style="color:#888;margin-bottom:10px">Full platform control — manage content, users, and analytics.</p>

    <div class="admin-tabs">
        <button class="admin-tab active" onclick="switchTab('overview')">📊 Overview</button>
        <button class="admin-tab" onclick="switchTab('content')">🎬 Content</button>
        <button class="admin-tab" onclick="switchTab('add')">➕ Add Content</button>
        <button class="admin-tab" onclick="switchTab('users')">👥 Users</button>
    </div>

    <!-- ═══ OVERVIEW TAB ═══ -->
    <div class="tab-pane active" id="tab-overview">
        <div class="metrics-grid">
            <div class="metric-card" style="border-color:var(--red)">
                <div class="metric-label">Total Movies</div>
                <div class="metric-value"><?= $movieCount ?></div>
            </div>
            <div class="metric-card" style="border-color:var(--gold)">
                <div class="metric-label">Total Series</div>
                <div class="metric-value"><?= $seriesCount ?></div>
            </div>
            <div class="metric-card" style="border-color:#0cf">
                <div class="metric-label">Registered Users</div>
                <div class="metric-value"><?= $userCount ?></div>
            </div>
            <div class="metric-card" style="border-color:#0c6">
                <div class="metric-label">Reviews Posted</div>
                <div class="metric-value"><?= $commentCount ?></div>
            </div>
            <div class="metric-card" style="border-color:#a0f">
                <div class="metric-label">Watchlist Saves</div>
                <div class="metric-value"><?= $wlCount ?></div>
            </div>
            <div class="metric-card" style="border-color:#f60">
                <div class="metric-label">Ratings Given</div>
                <div class="metric-value"><?= $ratingCount ?></div>
            </div>
        </div>

        <div class="analytic-two">
            <div class="analytic-box">
                <h3>🔥 Most Watched Content</h3>
                <?php if (count($topWatched)): ?>
                <table class="analytic-table">
                    <thead><tr><th>#</th><th>Title</th><th>Watches</th></tr></thead>
                    <tbody>
                    <?php foreach ($topWatched as $i => $r): ?>
                    <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($r['title']) ?></td><td><?= $r['watches'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: echo '<p style="color:#555">No watch history yet.</p>'; endif; ?>
            </div>
            <div class="analytic-box">
                <h3>⭐ Top Rated (Community)</h3>
                <?php if (count($topRated)): ?>
                <table class="analytic-table">
                    <thead><tr><th>#</th><th>Title</th><th>Avg</th><th>Votes</th></tr></thead>
                    <tbody>
                    <?php foreach ($topRated as $i => $r): ?>
                    <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($r['title']) ?></td><td><?= $r['avg_r'] ?>/10</td><td><?= $r['votes'] ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: echo '<p style="color:#555">No community ratings yet.</p>'; endif; ?>
            </div>
        </div>
    </div>

    <!-- ═══ CONTENT MANAGEMENT TAB ═══ -->
    <div class="tab-pane" id="tab-content">
        <div class="admin-form-card">
            <h2>🎬 Content Library (Last 50)</h2>
            <div id="contentTableMsg"></div>
            <div style="overflow-x:auto">
            <table class="content-table">
                <thead>
                    <tr><th>Poster</th><th>ID</th><th>Title</th><th>Type</th><th>Year</th><th>Rating</th><th>Actions</th></tr>
                </thead>
                <tbody>
                <?php foreach ($allContent as $c): ?>
                <tr id="row-<?= $c['id'] ?>">
                    <td><img class="tbl-poster" src="<?= htmlspecialchars($c['poster_url']) ?>" onerror="this.src='https://placehold.co/40x56/111/fff?text=?'" alt=""></td>
                    <td style="color:#666">#<?= $c['id'] ?></td>
                    <td style="font-weight:700;max-width:180px"><?= htmlspecialchars($c['title']) ?></td>
                    <td><span style="font-size:11px;background:rgba(255,255,255,.07);padding:3px 8px;border-radius:10px"><?= $c['content_type'] ?></span></td>
                    <td><?= htmlspecialchars($c['release_year']) ?></td>
                    <td style="color:var(--gold)">⭐ <?= htmlspecialchars($c['rating']) ?></td>
                    <td>
                        <button class="edit-btn" onclick='openEditModal(<?= json_encode($c) ?>)'>✏️ Edit</button>
                        <button class="del-btn"  onclick="deleteContent(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['title'])) ?>')">🗑️</button>
                        <?php if ($c['content_type'] === 'series'): ?>
                        <button class="edit-btn" style="background:rgba(0,200,255,.1);color:#0cf;border-color:rgba(0,200,255,.3)" onclick="openEpModal(<?= $c['id'] ?>, '<?= htmlspecialchars(addslashes($c['title'])) ?>')">📺 Episodes</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ═══ ADD CONTENT TAB ═══ -->
    <div class="tab-pane" id="tab-add">
        <div class="admin-form-card">
            <h2>➕ Add New Content</h2>
            <?= $msg ?>

            <!-- Cinema API Auto-Fill -->
            <div style="background:rgba(255,193,7,.07);border:1px solid rgba(255,193,7,.2);border-radius:12px;padding:20px;margin-bottom:28px">
                <div style="font-size:13px;color:var(--gold);font-weight:700;margin-bottom:10px">🎬 Auto-Fill from Cinema Database (OMDB / TVMaze)</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                    <select id="apiFetchType" style="padding:10px 14px;background:#0e0e16;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:14px">
                        <option value="movie">🎬 Movie</option>
                        <option value="series">📺 Series</option>
                    </select>
                    <input type="text" id="apiTitleInput" placeholder="Enter title e.g. Inception" style="flex:1;min-width:200px;padding:10px 14px;background:#0e0e16;border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;font-family:'Nunito',sans-serif;font-size:14px">
                    <button type="button" onclick="fetchCinemaDetails()" style="padding:10px 20px;background:var(--gold);color:#000;border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:14px" id="apiFetchBtn">🔍 Fetch Details</button>
                </div>
                <div id="apiFetchStatus" style="margin-top:8px;font-size:13px;color:#888"></div>
            </div>

            <form method="POST" id="addContentForm">
                <input type="hidden" name="action" value="add_content">
                <div class="admin-grid">
                    <div class="admin-field span2">
                        <label>Title *</label>
                        <input type="text" name="title" id="f_title" required placeholder="e.g. Inception">
                    </div>
                    <div class="admin-field">
                        <label>Type</label>
                        <select name="content_type" id="f_type" onchange="toggleSeriesFields()">
                            <option value="movie">🎬 Movie</option>
                            <option value="series">📺 Series</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label>Category</label>
                        <select name="category" id="f_cat">
                            <option value="trending">🔥 Trending</option>
                            <option value="top10">🏆 Top 10</option>
                            <option value="action">💥 Action</option>
                            <option value="bollywood">🎭 Bollywood</option>
                            <option value="hollywood">🌟 Hollywood</option>
                            <option value="horror">👻 Horror</option>
                            <option value="comedy">😂 Comedy</option>
                            <option value="romance">❤️ Romance</option>
                        </select>
                    </div>
                    <div class="admin-field">
                        <label>Release Year</label>
                        <input type="text" name="year" id="f_year" placeholder="2024">
                    </div>
                    <div class="admin-field" id="movieDurField">
                        <label>Duration</label>
                        <input type="text" name="duration" id="f_duration" placeholder="2h 15m">
                    </div>
                    <!-- Series-only fields -->
                    <div class="admin-field" id="seriesSeasonField" style="display:none">
                        <label>Number of Seasons</label>
                        <input type="text" name="duration" id="f_seasons" placeholder="e.g. 3 Seasons">
                    </div>
                    <div class="admin-field">
                        <label>Rating (1–10)</label>
                        <input type="text" name="rating" id="f_rating" placeholder="8.5">
                    </div>
                    <div class="admin-field">
                        <label>Genre</label>
                        <input type="text" name="genre" id="f_genre" placeholder="Action · Sci-Fi">
                    </div>
                    <div class="admin-field span2">
                        <label>Synopsis / Description</label>
                        <textarea name="desc" id="f_desc" rows="4" placeholder="Brief plot description…"></textarea>
                    </div>

                    <!-- Poster Upload -->
                    <div class="admin-field span2">
                        <label>Poster Image</label>
                        <div class="upload-row">
                            <input type="text" name="poster_url" id="f_poster" placeholder="https://image.tmdb.org/… or upload below">
                            <label class="upload-btn" for="uploadPosterFile">📁 Upload Image</label>
                            <input type="file" id="uploadPosterFile" accept="image/*" style="display:none" onchange="uploadFile(this,'poster','f_poster','posterUploadStatus')">
                        </div>
                        <div id="posterUploadStatus" style="font-size:12px;color:#0c6;margin-top:4px"></div>
                        <div id="posterPreviewWrap" style="margin-top:8px;display:none"><img id="posterPreview" style="height:120px;border-radius:8px;border:1px solid rgba(255,255,255,.1)" src=""></div>
                    </div>

                    <!-- Video Upload (Movie only) -->
                    <div class="admin-field" id="videoField">
                        <label>Video URL or Upload</label>
                        <div class="upload-row">
                            <input type="text" name="video_url" id="f_video" placeholder="https://watchluna.com/…">
                            <label class="upload-btn" for="uploadVideoFile">📁 Upload Video</label>
                            <input type="file" id="uploadVideoFile" accept="video/*" style="display:none" onchange="uploadFile(this,'video','f_video','videoUploadStatus')">
                        </div>
                        <div id="videoUploadStatus" style="font-size:12px;color:#0c6;margin-top:4px"></div>
                    </div>

                    <div class="admin-field">
                        <label>YouTube Trailer ID</label>
                        <div class="upload-row">
                            <input type="text" name="trailer_url" id="f_trailer" placeholder="e.g. YoHD9XEInc0">
                            <a id="trailerPreviewBtn" href="#" onclick="previewTrailer()" style="padding:8px 14px;background:rgba(255,0,0,.15);color:#f66;border:1px solid rgba(255,0,0,.3);border-radius:8px;font-size:12px;text-decoration:none;white-space:nowrap">▶ Preview</a>
                        </div>
                    </div>

                    <div class="admin-field span2">
                        <button type="submit" class="glow-btn" style="width:100%;padding:15px;font-size:17px">⚡ Add to Database</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Episode Manager Modal -->
    <div id="epModalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.8);z-index:9999;align-items:center;justify-content:center;overflow-y:auto;padding:20px">
      <div style="background:#16161f;border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:32px;width:min(800px,95vw);max-height:90vh;overflow-y:auto;position:relative">
        <h2 style="font-family:var(--font-display);font-size:28px;margin-bottom:4px;color:var(--gold)">📺 Manage Episodes</h2>
        <p id="epModalSeriesTitle" style="color:#888;margin-bottom:20px"></p>
        <button onclick="closeEpModal()" style="position:absolute;top:16px;right:16px;background:none;border:none;color:#fff;font-size:24px;cursor:pointer">✕</button>
        <div id="epContent">Loading...</div>
      </div>
    </div>

    <!-- ═══ USERS TAB ═══ -->
    <div class="tab-pane" id="tab-users">
        <div class="admin-form-card">
            <h2>👥 Recent Users</h2>
            <table class="users-table">
                <thead>
                    <tr><th>Username</th><th>Email</th><th>Plan</th><th>Joined</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentUsers as $u): ?>
                <tr>
                    <td style="font-weight:700"><?= htmlspecialchars($u['username']) ?></td>
                    <td style="color:#aaa"><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="plan-badge plan-<?= htmlspecialchars($u['plan']??'basic') ?>"><?= strtoupper($u['plan']??'BASIC') ?></span></td>
                    <td style="color:#666"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ═══ EDIT MODAL ═══ -->
<div class="edit-modal-overlay" id="editModalOverlay">
    <div class="edit-modal">
        <h2>✏️ Edit Content</h2>
        <div id="editMsg"></div>
        <form id="editForm" onsubmit="submitEdit(event)">
            <input type="hidden" id="editId" name="id">
            <div class="admin-grid">
                <div class="admin-field span2">
                    <label>Title</label>
                    <input type="text" id="editTitle" name="title" required>
                </div>
                <div class="admin-field">
                    <label>Type</label>
                    <select id="editType" name="content_type">
                        <option value="movie">🎬 Movie</option>
                        <option value="series">📺 Series</option>
                    </select>
                </div>
                <div class="admin-field">
                    <label>Category</label>
                    <input type="text" id="editCat" name="category">
                </div>
                <div class="admin-field">
                    <label>Year</label>
                    <input type="text" id="editYear" name="year">
                </div>
                <div class="admin-field">
                    <label>Duration</label>
                    <input type="text" id="editDur" name="duration">
                </div>
                <div class="admin-field">
                    <label>Rating</label>
                    <input type="text" id="editRating" name="rating">
                </div>
                <div class="admin-field">
                    <label>Genre</label>
                    <input type="text" id="editGenre" name="genre">
                </div>
                <div class="admin-field span2">
                    <label>Description</label>
                    <textarea id="editDesc" name="desc" rows="3"></textarea>
                </div>
                <div class="admin-field span2">
                    <label>Poster URL</label>
                    <input type="text" id="editPoster" name="poster_url" placeholder="https://in.pinterest.com/…">
                </div>
                <div class="admin-field">
                    <label>Video URL</label>
                    <div class="upload-row">
                        <input type="text" id="editVideo" name="video_url" placeholder="https://watchluna.com/…">
                        <label class="upload-btn" for="uploadEditVideoFile">📁 Upload Video</label>
                        <input type="file" id="uploadEditVideoFile" accept="video/*" style="display:none" onchange="uploadFile(this,'video','editVideo','editVideoUploadStatus')">
                    </div>
                    <div id="editVideoUploadStatus" style="font-size:12px;color:#0c6;margin-top:4px"></div>
                </div>
                <div class="admin-field">
                    <label>Trailer YouTube ID</label>
                    <input type="text" id="editTrailer" name="trailer_url">
                </div>
                <div class="admin-field span2" style="display:flex;gap:12px">
                    <button type="submit" class="glow-btn" style="flex:1;padding:13px">💾 Save Changes</button>
                    <button type="button" class="glow-btn" style="flex:1;padding:13px;background:#333;box-shadow:none" onclick="closeEditModal()">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    document.querySelectorAll('.admin-tab').forEach(t=>t.classList.remove('active'));
    document.getElementById('tab-'+tab).classList.add('active');
    event.currentTarget.classList.add('active');
}
function toggleSeriesFields() {
    var isSeries = document.getElementById('f_type').value === 'series';
    document.getElementById('movieDurField').style.display   = isSeries ? 'none' : '';
    document.getElementById('seriesSeasonField').style.display = isSeries ? '' : 'none';
    document.getElementById('videoField').style.display      = isSeries ? 'none' : '';
    if (isSeries) { document.getElementById('f_duration').name=''; document.getElementById('f_seasons').name='duration'; }
    else          { document.getElementById('f_duration').name='duration'; document.getElementById('f_seasons').name=''; }
}
async function fetchCinemaDetails() {
    var title = document.getElementById('apiTitleInput').value.trim();
    var type  = document.getElementById('apiFetchType').value;
    var st    = document.getElementById('apiFetchStatus');
    var btn   = document.getElementById('apiFetchBtn');
    if (!title) { st.textContent='Please enter a title.'; return; }
    btn.textContent='⏳ Fetching...'; btn.disabled=true;
    st.textContent='';
    try {
        var r = await fetch('api/fetch_cinema_details.php?title='+encodeURIComponent(title)+'&type='+type);
        var d = await r.json();
        if (d.status === 'ok') {
            document.getElementById('f_title').value   = d.title  || '';
            document.getElementById('f_year').value    = d.year   || '';
            document.getElementById('f_rating').value  = d.rating || '';
            document.getElementById('f_genre').value   = d.genre  || '';
            document.getElementById('f_desc').value    = d.description || '';
            document.getElementById('f_poster').value  = d.poster_url  || '';
            if (d.trailer_url) document.getElementById('f_trailer').value = d.trailer_url;
            if (d.content_type) document.getElementById('f_type').value = d.content_type;
            toggleSeriesFields();
            if (d.seasons) document.getElementById('f_seasons').value = d.seasons + ' Season' + (d.seasons > 1 ? 's' : '');
            // Show poster preview
            if (d.poster_url) { document.getElementById('posterPreview').src=d.poster_url; document.getElementById('posterPreviewWrap').style.display=''; }
            st.style.color='#0c6'; st.textContent='✅ Details filled! Review and click Add to Database.';
        } else {
            st.style.color='#f55'; st.textContent='❌ '+d.message;
        }
    } catch(e) { st.style.color='#f55'; st.textContent='❌ Network error'; }
    btn.textContent='🔍 Fetch Details'; btn.disabled=false;
}
async function uploadFile(input, type, targetFieldId, statusId) {
    if (!input.files[0]) return;
    var status = document.getElementById(statusId);
    status.textContent='⏳ Uploading...';
    var fd = new FormData();
    fd.append('file', input.files[0]);
    try {
        var r = await fetch('api/upload_media.php?type='+type, {method:'POST',body:fd});
        var d = await r.json();
        if (d.status === 'success') {
            document.getElementById(targetFieldId).value = d.url;
            status.style.color='#0c6'; status.textContent='✅ Uploaded: '+d.filename;
            if (type==='poster') { document.getElementById('posterPreview').src=d.url; document.getElementById('posterPreviewWrap').style.display=''; }
        } else { status.style.color='#f55'; status.textContent='❌ '+d.message; }
    } catch(e) { status.style.color='#f55'; status.textContent='❌ Upload failed'; }
}
function previewTrailer() {
    var id = document.getElementById('f_trailer').value.trim();
    if (!id) return;
    window.open('https://www.youtube.com/watch?v='+id,'_blank');
    return false;
}

// ── Episode Manager ──────────────────────────────────────────────────────────
var _epSeriesId = 0;
function openEpModal(seriesId, seriesTitle) {
    _epSeriesId = seriesId;
    document.getElementById('epModalSeriesTitle').textContent = seriesTitle;
    document.getElementById('epModalOverlay').style.display = 'flex';
    loadEpisodes();
}
function closeEpModal() { document.getElementById('epModalOverlay').style.display='none'; }
async function loadEpisodes() {
    var r = await fetch('api/manage_episodes.php?action=get_episodes&series_id='+_epSeriesId);
    var d = await r.json();
    var html = '';
    (d.seasons||[]).forEach(function(s) {
        html += '<div class="ep-season"><div class="ep-season-hdr"><span>Season '+s.season_number+': '+s.title+'</span><button onclick="deleteSeason('+s.id+')" style="background:rgba(230,57,70,.2);color:var(--red);border:1px solid rgba(230,57,70,.3);padding:4px 10px;border-radius:6px;cursor:pointer;font-size:12px">🗑 Delete Season</button></div>';
        (s.episodes||[]).forEach(function(e){
            html += '<div class="ep-row"><span style="width:30px;color:#666">E'+e.episode_number+'</span><span style="flex:1;font-weight:600">'+e.title+'</span><span style="color:#888;font-size:12px">'+e.duration+'</span><button onclick="deleteEp('+e.id+')" style="background:rgba(230,57,70,.1);color:var(--red);border:none;padding:3px 8px;border-radius:5px;cursor:pointer;font-size:11px">✕</button></div>';
        });
        html += '</div>';
    });
    html += '<hr style="border-color:rgba(255,255,255,.07);margin:20px 0">';
    html += '<h4 style="margin-bottom:12px;color:#ddd">Add Season</h4><div style="display:flex;gap:8px;margin-bottom:20px"><input id="newSeasonNum" type="number" placeholder="Season #" style="width:100px;padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff"><input id="newSeasonTitle" type="text" placeholder="Season 1" style="flex:1;padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff"><button onclick="addSeason()" style="padding:8px 18px;background:var(--gold);color:#000;border:none;border-radius:8px;font-weight:700;cursor:pointer">+ Add</button></div>';
    if (d.seasons && d.seasons.length>0) {
        html += '<h4 style="margin-bottom:12px;color:#ddd">Add Episode</h4><div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">';
        html += '<select id="newEpSeason" style="padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff">';
        d.seasons.forEach(function(s){ html += '<option value="'+s.id+'">Season '+s.season_number+'</option>'; });
        html += '</select>';
        html += '<input id="newEpNum" type="number" placeholder="Episode #" style="padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff">';
        html += '<input id="newEpTitle" type="text" placeholder="Episode Title" style="padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff;grid-column:span 2">';
        html += '<div style="grid-column:span 2; display:flex; gap:8px"><input id="newEpVideo" type="text" placeholder="Video URL" style="flex:1;padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff"><label class="upload-btn" for="uploadEpVideoFile" style="padding:8px 14px; margin:0; line-height:20px; align-self:center">📁 Upload</label><input type="file" id="uploadEpVideoFile" accept="video/*" style="display:none" onchange="uploadFile(this,\'video\',\'newEpVideo\',\'epVideoUploadStatus\')"></div><div id="epVideoUploadStatus" style="grid-column:span 2; font-size:12px; color:#0c6; margin-top:-5px;"></div>';
        html += '<input id="newEpDur" type="text" placeholder="Duration e.g. 45m" style="padding:8px;background:#0e0e16;border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#fff">';
        html += '<button onclick="addEpisode()" style="padding:8px 18px;background:var(--red);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer">+ Add Episode</button></div>';
    }
    document.getElementById('epContent').innerHTML = html;
}
async function addSeason() {
    var fd=new FormData(); fd.append('action','add_season'); fd.append('series_id',_epSeriesId);
    fd.append('season_number',document.getElementById('newSeasonNum').value);
    fd.append('title',document.getElementById('newSeasonTitle').value||'Season '+document.getElementById('newSeasonNum').value);
    await fetch('api/manage_episodes.php',{method:'POST',body:fd}); loadEpisodes();
}
async function addEpisode() {
    var fd=new FormData(); fd.append('action','add_episode');
    fd.append('season_id',document.getElementById('newEpSeason').value);
    fd.append('episode_number',document.getElementById('newEpNum').value);
    fd.append('title',document.getElementById('newEpTitle').value);
    fd.append('video_url',document.getElementById('newEpVideo').value);
    fd.append('duration',document.getElementById('newEpDur').value);
    await fetch('api/manage_episodes.php',{method:'POST',body:fd}); loadEpisodes();
}
async function deleteSeason(id) {
    if(!confirm('Delete this season and all its episodes?')) return;
    var fd=new FormData(); fd.append('action','delete_season'); fd.append('season_id',id);
    await fetch('api/manage_episodes.php',{method:'POST',body:fd}); loadEpisodes();
}
async function deleteEp(id) {
    var fd=new FormData(); fd.append('action','delete_episode'); fd.append('episode_id',id);
    await fetch('api/manage_episodes.php',{method:'POST',body:fd}); loadEpisodes();
}

// ── Edit + Delete (existing) ─────────────────────────────────────────────────
function openEditModal(data) {
    document.getElementById('editId').value      = data.id;
    document.getElementById('editTitle').value   = data.title || '';
    document.getElementById('editType').value    = data.content_type || 'movie';
    document.getElementById('editCat').value     = data.category || '';
    document.getElementById('editYear').value    = data.release_year || '';
    document.getElementById('editDur').value     = data.duration || '';
    document.getElementById('editRating').value  = data.rating || '';
    document.getElementById('editGenre').value   = data.genre || '';
    document.getElementById('editDesc').value    = data.description || '';
    document.getElementById('editPoster').value  = data.poster_url || '';
    document.getElementById('editVideo').value   = data.video_url || '';
    document.getElementById('editTrailer').value = data.trailer_url || '';
    document.getElementById('editMsg').innerHTML = '';
    document.getElementById('editModalOverlay').classList.add('open');
}
function closeEditModal() { document.getElementById('editModalOverlay').classList.remove('open'); }
async function submitEdit(e) {
    e.preventDefault();
    var fd=new FormData(document.getElementById('editForm'));
    var msg=document.getElementById('editMsg');
    try {
        var res=await fetch('api/admin_edit_content.php',{method:'POST',body:fd});
        var data=await res.json();
        if(data.status==='success'){msg.innerHTML='<div class="admin-msg ok">✅ '+data.message+'</div>';setTimeout(closeEditModal,1000);setTimeout(()=>location.reload(),1200);}
        else{msg.innerHTML='<div class="admin-msg err">❌ '+data.message+'</div>';}
    }catch(err){msg.innerHTML='<div class="admin-msg err">❌ Connection error</div>';}
}
async function deleteContent(id,title){
    if(!confirm('Delete "'+title+'"?')) return;
    var fd=new FormData(); fd.append('id',id);
    try{
        var res=await fetch('api/admin_delete_content.php',{method:'POST',body:fd});
        var data=await res.json();
        var msgEl=document.getElementById('contentTableMsg');
        if(data.status==='success'){var row=document.getElementById('row-'+id);if(row){row.style.opacity='0';row.style.transition='opacity .4s';setTimeout(()=>row.remove(),400);}msgEl.innerHTML='<div class="admin-msg ok" style="margin-bottom:14px">✅ Deleted "'+title+'"</div>';}
        else{msgEl.innerHTML='<div class="admin-msg err" style="margin-bottom:14px">❌ '+data.message+'</div>';}
        setTimeout(()=>msgEl.innerHTML='',3000);
    }catch(e){alert('Connection error');}
}
document.getElementById('editModalOverlay').addEventListener('click',function(e){if(e.target===this)closeEditModal();});
</script>

function openEditModal(data) {
    document.getElementById('editId').value      = data.id;
    document.getElementById('editTitle').value   = data.title || '';
    document.getElementById('editType').value    = data.content_type || 'movie';
    document.getElementById('editCat').value     = data.category || '';
    document.getElementById('editYear').value    = data.release_year || '';
    document.getElementById('editDur').value     = data.duration || '';
    document.getElementById('editRating').value  = data.rating || '';
    document.getElementById('editGenre').value   = data.genre || '';
    document.getElementById('editDesc').value    = data.description || '';
    document.getElementById('editPoster').value  = data.poster_url || '';
    document.getElementById('editVideo').value   = data.video_url || '';
    document.getElementById('editTrailer').value = data.trailer_url || '';
    document.getElementById('editMsg').innerHTML = '';
    document.getElementById('editModalOverlay').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModalOverlay').classList.remove('open');
}

async function submitEdit(e) {
    e.preventDefault();
    const fd   = new FormData(document.getElementById('editForm'));
    const msg  = document.getElementById('editMsg');
    try {
        const res  = await fetch('api/admin_edit_content.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.status === 'success') {
            msg.innerHTML = '<div class="admin-msg ok">✅ ' + data.message + '</div>';
            setTimeout(closeEditModal, 1000);
            setTimeout(() => location.reload(), 1200);
        } else {
            msg.innerHTML = '<div class="admin-msg err">❌ ' + data.message + '</div>';
        }
    } catch(err) {
        msg.innerHTML = '<div class="admin-msg err">❌ Connection error</div>';
    }
}

async function deleteContent(id, title) {
    if (!confirm(`Are you sure you want to DELETE "${title}"? This cannot be undone.`)) return;
    const fd = new FormData();
    fd.append('id', id);
    try {
        const res  = await fetch('api/admin_delete_content.php', { method: 'POST', body: fd });
        const data = await res.json();
        const msgEl = document.getElementById('contentTableMsg');
        if (data.status === 'success') {
            const row = document.getElementById('row-' + id);
            if (row) { row.style.opacity='0'; row.style.transition='opacity .4s'; setTimeout(()=>row.remove(),400); }
            msgEl.innerHTML = '<div class="admin-msg ok" style="margin-bottom:14px">✅ Deleted "' + title + '"</div>';
        } else {
            msgEl.innerHTML = '<div class="admin-msg err" style="margin-bottom:14px">❌ ' + data.message + '</div>';
        }
        setTimeout(()=>msgEl.innerHTML='', 3000);
    } catch(e) {
        alert('Connection error');
    }
}

document.getElementById('editModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

<?php include 'includes/footer.php'; ?>
