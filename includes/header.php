<?php
// includes/header.php — CineVault Shared Header
// Auth guard (disabled for public browsing; enable per-page as needed)
// if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

$_cv_user    = $_SESSION['username']   ?? 'Guest';
$_cv_initial = strtoupper(substr($_cv_user, 0, 1));
$_cv_avatar  = $_SESSION['avatar_url'] ?? null;

// Resolve avatar from DB if session doesn't have it yet
if (!$_cv_avatar && isset($_SESSION['user_id'])) {
    require_once 'api/db_connect.php';
    $avStmt = $pdo->prepare("SELECT avatar_url FROM users WHERE id = ?");
    $avStmt->execute([$_SESSION['user_id']]);
    $_cv_avatar = $avStmt->fetchColumn() ?: null;
    if ($_cv_avatar) $_SESSION['avatar_url'] = $_cv_avatar;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'CineVault'; ?></title>
    <meta name="description" content="<?php echo $pageDesc ?? 'CineVault – Watch the latest movies and TV shows online'; ?>">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="cinevault.css">
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Nunito:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php if (!empty($extraCSS)): ?>
        <link rel="stylesheet" href="<?php echo $extraCSS; ?>">
    <?php endif; ?>
</head>
<body<?php echo !empty($bodyClass) ? ' class="'.$bodyClass.'"' : ''; ?>>

<!-- ══ NAVBAR ══ -->
<nav class="navbar" <?php echo !empty($navId) ? 'id="'.$navId.'"' : ''; ?>>
    <a class="nav-logo" href="home.php">
        <div class="logo-icon">▶</div>
        CineVault
    </a>
    <div class="nav-links">
        <a href="home.php"   <?php echo ($activePage ?? '') === 'home'   ? 'class="active"' : ''; ?>>Home</a>
        <a href="mv.php"     <?php echo ($activePage ?? '') === 'movies' ? 'class="active"' : ''; ?>>Movies</a>
        <a href="series.php" <?php echo ($activePage ?? '') === 'series' ? 'class="active"' : ''; ?>>TV Shows</a>
        <a href="anime.php"  <?php echo ($activePage ?? '') === 'anime'  ? 'class="active"' : ''; ?>>Anime</a>
        <a href="search.php" <?php echo ($activePage ?? '') === 'search' ? 'class="active"' : ''; ?>>Browse</a>
    </div>
    <div class="nav-right">
        <!-- Search -->
        <div class="search-wrap">
            <button class="icon-btn" id="searchToggle" title="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.35-4.35"/></svg>
            </button>
            <div class="search-box" id="searchBox">
                <input type="text" id="searchInput" placeholder="Search movies, shows…" autocomplete="off"/>
                <button id="searchClear">✕</button>
                <div class="search-dropdown" id="searchDropdown"></div>
            </div>
        </div>

        <!-- Watchlist -->
        <button class="icon-btn watchlist-nav-btn" id="watchlistNavBtn" title="My Watchlist">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
            <span class="watchlist-count" id="watchlistCount" style="display:none">0</span>
        </button>

        <!-- Notification Bell -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <button class="icon-btn notif-btn" id="notifBtn" title="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
        </button>
        <!-- Notification Dropdown -->
        <div class="notif-dropdown" id="notifDropdown">
            <div class="notif-header">
                <span>Notifications</span>
                <button id="markAllRead" onclick="markAllNotifRead()">Mark all read</button>
            </div>
            <div class="notif-list" id="notifList">
                <div class="notif-empty">🔔 No new notifications</div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Avatar -->
        <div class="avatar-wrap">
            <div class="avatar" id="avatarBtn">
                <?php if ($_cv_avatar): ?>
                    <img src="<?= htmlspecialchars($_cv_avatar) ?>" alt="<?= htmlspecialchars($_cv_user) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                <?php else: ?>
                    <?php echo $_cv_initial; ?>
                <?php endif; ?>
            </div>
            <div class="avatar-dropdown" id="avatarDropdown">
                <?php
                if (isset($_SESSION['user_id'])) {
                    if (!isset($pdo)) require_once 'api/db_connect.php';
                    $roleCheck = $pdo->prepare("SELECT role FROM users WHERE id = :id");
                    $roleCheck->execute(['id' => $_SESSION['user_id']]);
                    if ($roleCheck->fetchColumn() === 'admin') {
                        echo '<a href="admin.php" style="color:var(--gold); border-bottom:1px solid rgba(255,255,255,0.05);">⚡ Admin Console</a>';
                    }
                    echo '<div class="dropdown-user"><strong>' . htmlspecialchars($_cv_user) . '</strong></div>';
                    echo '<a href="profile.php">👤 My Profile</a>';
                    echo '<a href="settings.php">⚙️ Settings</a>';
                    echo '<a href="api/logout.php" class="logout" style="color:var(--red);">🚪 Sign Out</a>';
                } else {
                    echo '<a href="login.php">🔑 Sign In</a>';
                    echo '<a href="register.php">📝 Register</a>';
                }
                ?>
            </div>
        </div>
    </div>
</nav>

<!-- ══ WATCHLIST PANEL ══ -->
<div class="wl-backdrop" id="wlBackdrop">
    <div class="wl-panel" id="wlPanel">
        <div class="wl-header">
            <div class="wl-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="18" height="18"><path d="M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2z"/></svg>
                My Watchlist
            </div>
            <button class="wl-close" id="wlClose">✕</button>
        </div>
        <div class="wl-body" id="wlBody">
            <div class="wl-empty" id="wlEmpty">
                <div class="wl-empty-icon">🎬</div>
                <p>Your watchlist is empty</p>
                <span>Add movies &amp; shows to watch later</span>
            </div>
            <div class="wl-list" id="wlList"></div>
        </div>
        <div class="wl-footer" id="wlFooter" style="display:none">
            <button class="wl-clear-btn" id="wlClearBtn">🗑 Clear All</button>
        </div>
    </div>
</div>

<!-- ══ VIDEO PLAYER MODAL ══ -->
<div class="player-overlay" id="playerOverlay">
    <div class="player-box">
        <div class="player-header">
            <div class="player-title-wrap">
                <span class="player-now">▶ NOW PLAYING</span>
                <h3 class="player-movie-title" id="playerTitle">Movie Title</h3>
            </div>
            <button class="player-close" id="playerClose">✕</button>
        </div>
        <div class="player-screen">
            <div class="player-fake" id="playerFake">
                <div class="player-fake-bg" id="playerFakeBg"></div>
                <div class="player-fake-content">
                    <div class="player-big-play" id="playerBigPlay">▶</div>
                    <div class="player-fake-title" id="playerFakeTitle"></div>
                    <div class="player-fake-sub">Official Trailer</div>
                    <div class="player-fake-note">Click play to watch the trailer</div>
                </div>
            </div>
            <iframe id="playerIframe" src="" frameborder="0"
                allow="autoplay; encrypted-media; fullscreen" allowfullscreen
                style="display:none;width:100%;height:100%;border:none"></iframe>
        </div>
        <div class="player-info-bar">
            <div class="player-meta" id="playerMeta"></div>
            <div class="player-btns">
                <button class="player-wl-btn" id="playerWlBtn">＋ Watchlist</button>
                <button class="player-share-btn" onclick="shareCurrentPage()">↗ Share</button>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL ══ -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="modal-box" id="modalBox">
        <div class="modal-top">
            <img id="modalPoster" src="" alt="">
            <div class="modal-gradient"></div>
            <button class="modal-close" id="modalClose" onclick="closeModal()">✕</button>
            <div class="modal-top-info">
                <div class="modal-genre-pill" id="modalGenrePill"></div>
                <h2 id="modalTitle"></h2>
                <div class="modal-quick-meta">
                    <span class="modal-star">⭐ <span id="modalRating"></span></span>
                    <span class="mdot">·</span>
                    <span id="modalYear"></span>
                    <span class="mdot">·</span>
                    <span id="modalDuration"></span>
                    <span class="mdot">·</span>
                    <span class="modal-hd">HD</span>
                </div>
            </div>
        </div>
        <div class="modal-body">
            <p id="modalDesc"></p>
            <div class="modal-actions">
                <button class="modal-btn-play" onclick="playMovie()">▶ Play</button>
                <button class="modal-btn-list" onclick="addToWatchlist()">＋ Watchlist</button>
                <button class="modal-btn-download" onclick="showToast('Download started! 📥')">⬇ Download</button>
                <button class="modal-btn-share" onclick="shareCurrentPage()">↗ Share</button>
            </div>
        </div>
    </div>
</div>

<!-- ══ TOAST ══ -->
<div id="toast"></div>

<!-- ══ SCRIPTS ══ -->
<script src="script.js"></script>
<?php if (!empty($pageScript)): ?>
<script><?php echo $pageScript; ?></script>
<?php endif; ?>
