/* ══════════════════════════════════════════
   CINEVAULT — Shared Utilities (script.js)
   ══════════════════════════════════════════ */

/* ══ YOUTUBE TRAILER MAP ════════════════ */
var TRAILERS = {
  'War': 'KKFanta3JJ4',
  'Pathaan': 'vqu4z34wENw',
  'KGF: Chapter 2': 'YIE4TrjWIZI',
  'RRR': 'f_vbAtFSEc0',
  'Baahubali 2': 'WNel_N6dp20',
  'Baahubali 2: The Conclusion': 'WNel_N6dp20',
  'Tiger Zinda Hai': 'bFYnlT7PqZA',
  'Deva': 'JbIdHOyp5R8',
  'Fighter': 'D-YF2QSOSB4',
  'Uri: The Surgical Strike': '0R-I3mI4f1I',
  'Pushpa: The Rise': 'Q1NKMPhP8PY',
  'Pushpa 2: The Rule': 'a9__D2ZLJOY',
  'Jawan': 'TJ4mFnWBkEs',
  'Dhoom 3': 'XWPIz0BHKVA',
  'Kalki 2898 AD': 'xmPaHFpOCow',
  'Animal': 'oEBFJEVFbP4',
  '3 Idiots': 'xvszmNXdM4w',
  'PK': 'wogHMToy4FI',
  'Tumbbad': 'xKOFB_sCNgA',
  'Bhag Milkha Bhag': 'OuMBIBLCM60',
  'Sholay': 'kK4yHCNFqhY',
  'Phir Hera Pheri': 'aDxfqKVSars',
  'Chup Chup Ke': 'RBHrMmPDVcg',
  'Happy New Year': 'b_BI3FEPOVE',
  'Dangal': 'x_7YlGv9u1g',
  'Andhadhun': 'YFGM8HYyOlE',
  'Gangs of Wasseypur': 'mIBJ5FqbYIQ',
  'Dilwale Dulhania Le Jayenge': 'csFHVqLMhcA',
  'John Wick: Chapter 4': 'qEVUtrk8_B4',
  'Mission: Impossible FR': 'avz06PDqgOE',
  'Nobody': 'pM_IiN7MgDs',
  'Extraction 2': '73IyOlsGGgY',
  'Top Gun: Maverick': 'giXco2jaZ_4',
  'The Dark Knight': 'EXeTwQWrcwY',
  'Mad Max: Fury Road': 'hEJnMQG9ev8',
  'Avengers: Endgame': 'TcMBFSGVi1c',
  'Avengers: Infinity War': '6ZfuNTqbHE8',
  'Gladiator II': 't8jIcBFnHOA',
  'Tron: Ares': 'lP6MBl5mMSa',
  'The Shawshank Redemption': '6hB3S9bIaco',
  'The Godfather': 'sY1S34973zA',
  'Inception': 'YoHD9XEInc0',
  'Interstellar': 'zSWdZVtXT7E',
  'Oppenheimer': 'uYPbbksJxIg',
  'Dune: Part Two': 'Way9Dexny3w',
  'The Matrix': 'm8e-FF8MsqU',
  'Pirates of the Caribbean': 'naQr0uTLTHs',
  'Fast & Furious 7': 'hgRQOgNRHKw',
  'Captain America: Winter Soldier': 'tbayiPxHpWI',
  'Forrest Gump': 'bLvqoHBptjg',
  'Pulp Fiction': 's7EdQ4FqbAQ',
  "Schindler's List": 'gG22XNhtnoY',
  'Goodfellas': 'qo5jJpHtI1Y',
  'The Silence of the Lambs': 'W6Mm8Sbe__o',
  'Zindagi Na Milegi Dobara': 'IMdTVGXPnMY',
  'Article 15': 'v4EoOlGRBlI',
  'Alice in Borderland': 'PKwPNsAiHaQ',
  'All of Us Are Dead': 'IN8IbkTcJ5g',
  'Wednesday': 'Di310WS8zLk',
  'Peaky Blinders': 'oVzVdvGIC7U',
  'Breaking Bad': 'HhesaQXLuRY',
  'Game of Thrones': 'KPLWWIOCOOQ',
  'Better Call Saul': 'fC2HDm5t0kH',
  'Stranger Things': 'mnd7P9a4IxI',
  'Squid Game': 'oqxAJKy0ii4',
  'Money Heist': 'reEMJA1uzsc',
  'The Witcher': 'ndl8DOL3GBM',
  'Ozark': 'pCGyPECgCqR',
  'Succession': 'OsJRwYUDidk',
  'The Boys': 'M8ZDgcSH8Vk',
  'Dark': 'NN4BBluCMms'
};

/* ══ TOAST ══════════════════════════════ */
var _toastTimer = null;
function showToast(msg) {
  var t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(_toastTimer);
  _toastTimer = setTimeout(function() { t.classList.remove('show'); }, 3000);
}

/* ══ WATCHLIST ══════════════════════════ */
var watchlist = [];

function loadWatchlist() {
  try { watchlist = JSON.parse(localStorage.getItem('cv_wl') || '[]'); }
  catch(e) { watchlist = []; }
}

function saveWatchlist() {
  try { localStorage.setItem('cv_wl', JSON.stringify(watchlist)); }
  catch(e) {}
}

function isInWatchlist(title) {
  for (var i = 0; i < watchlist.length; i++) {
    if (watchlist[i].title === title) return true;
  }
  return false;
}

function addToWatchlist() {
  if (!currentMovie) return;
  if (isInWatchlist(currentMovie.title)) {
    showToast('Already in Watchlist'); return;
  }
  watchlist.push({
    title: currentMovie.title, year: currentMovie.year,
    duration: currentMovie.duration, rating: currentMovie.rating,
    genre: currentMovie.genre, desc: currentMovie.desc, poster: currentMovie.poster
  });
  saveWatchlist();
  updateWatchlistCount();
  renderWatchlistPanel();
  showToast('"' + currentMovie.title + '" added');
  var btn = document.querySelector('.modal-btn-list');
  if (btn) btn.textContent = '✓ In Watchlist';
}

function removeFromWatchlist(title) {
  watchlist = watchlist.filter(function(m) { return m.title !== title; });
  saveWatchlist();
  updateWatchlistCount();
  renderWatchlistPanel();
  showToast('"' + title + '" removed');
}

function updateWatchlistCount() {
  var badge = document.getElementById('watchlistCount');
  if (!badge) return;
  if (watchlist.length > 0) {
    badge.textContent = watchlist.length > 99 ? '99+' : String(watchlist.length);
    badge.style.display = 'flex';
  } else {
    badge.style.display = 'none';
  }
}

function renderWatchlistPanel() {
  var empty = document.getElementById('wlEmpty');
  var list = document.getElementById('wlList');
  var footer = document.getElementById('wlFooter');
  if (!empty || !list || !footer) return;
  if (watchlist.length === 0) {
    empty.style.display = ''; list.style.display = 'none'; footer.style.display = 'none'; return;
  }
  empty.style.display = 'none'; list.style.display = ''; footer.style.display = '';
  var html = '';
  for (var i = 0; i < watchlist.length; i++) {
    var m = watchlist[i];
    var st = m.title.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
    var sg = m.genre.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
    var sd = (m.desc||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'");
    var genreFirst = m.genre.split('·')[0].trim();
    html +=
      '<div class="wl-item">' +
      '<img class="wl-item-img" src="' + m.poster + '" alt="' + m.title + '" onerror="this.src=\'https://placehold.co/52x78/1c1c23/e63946?text=' + encodeURIComponent(m.title.slice(0,6)) + '\'">' +
      '<div class="wl-item-info">' +
      '<div class="wl-item-title">' + m.title + '</div>' +
      '<div class="wl-item-meta">⭐ ' + m.rating + ' · ' + m.year + ' · ' + m.duration + '</div>' +
      '<span class="wl-item-genre">' + genreFirst + '</span>' +
      '</div>' +
      '<div class="wl-item-actions">' +
      '<button class="wl-play-btn" onclick="closeWatchlist();openPlayerFromWL(\'' + st + "','" + m.year + "','" + m.duration + "','" + m.rating + "','" + sg + "','" + sd + "','" + m.poster + '\')">▶ Play</button>' +
      '<button class="wl-remove-btn" onclick="removeFromWatchlist(\'' + st + '\')">✕ Remove</button>' +
      '</div>' +
      '</div>';
  }
  list.innerHTML = html;
}

function openWatchlist() {
  renderWatchlistPanel();
  document.getElementById('wlBackdrop').classList.add('open');
}

function closeWatchlist() {
  document.getElementById('wlBackdrop').classList.remove('open');
}

/* ══ CURRENT MOVIE STATE ════════════════ */
var currentMovie = null;

/* ══ MODAL ══════════════════════════════ */
function openModal(title, year, duration, rating, genre, desc, posterUrl) {
  currentMovie = { title:title, year:year, duration:duration, rating:rating, genre:genre, desc:desc, poster:posterUrl };
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalYear').textContent = year;
  document.getElementById('modalDuration').textContent = duration;
  document.getElementById('modalRating').textContent = rating;
  document.getElementById('modalDesc').textContent = desc;
  document.getElementById('modalGenrePill').textContent = genre;
  var poster = document.getElementById('modalPoster');
  poster.src = posterUrl;
  poster.onerror = function() {
    poster.src = 'https://placehold.co/680x320/1c1c23/e63946?text=' + encodeURIComponent(title);
  };
  var wlBtn = document.querySelector('.modal-btn-list');
  if (wlBtn) wlBtn.textContent = isInWatchlist(title) ? '✓ In Watchlist' : '＋ Watchlist';
  document.getElementById('modalOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  document.getElementById('modalBox').scrollTop = 0;
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function closeModalOutside(e) {
  if (e.target === document.getElementById('modalOverlay')) closeModal();
}

/* ══ VIDEO PLAYER ════════════════════════ */
function playMovie() {
  if (!currentMovie) return;
  closeModal();
  setTimeout(function() {
    openPlayer(currentMovie.title, currentMovie.year, currentMovie.duration, currentMovie.rating, currentMovie.genre, currentMovie.poster);
  }, 220);
}

function openPlayerFromWL(title, year, duration, rating, genre, desc, poster) {
  currentMovie = { title:title, year:year, duration:duration, rating:rating, genre:genre, desc:desc, poster:poster };
  openPlayer(title, year, duration, rating, genre, poster);
}

function openPlayer(title, year, duration, rating, genre, poster) {
  document.getElementById('playerTitle').textContent = title;
  document.getElementById('playerFakeTitle').textContent = title;
  document.getElementById('playerMeta').innerHTML =
    '<strong style="color:var(--gold)">⭐ ' + rating + '</strong>' +
    ' <span style="opacity:.4">·</span> ' + year +
    ' <span style="opacity:.4">·</span> ' + duration +
    ' <span style="opacity:.4">·</span> ' + genre;
  var wlBtn = document.getElementById('playerWlBtn');
  if (isInWatchlist(title)) {
    wlBtn.textContent = '✓ In Watchlist'; wlBtn.classList.add('added');
  } else {
    wlBtn.textContent = '＋ Watchlist'; wlBtn.classList.remove('added');
  }
  document.getElementById('playerFakeBg').style.backgroundImage = 'url(' + poster + ')';
  document.getElementById('playerFake').style.display = 'flex';
  document.getElementById('playerIframe').style.display = 'none';
  document.getElementById('playerIframe').src = '';
  document.getElementById('playerOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closePlayer() {
  document.getElementById('playerIframe').src = '';
  document.getElementById('playerIframe').style.display = 'none';
  document.getElementById('playerFake').style.display = 'flex';
  document.getElementById('playerOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

/* ══ CATEGORY FILTER ════════════════════ */
var GENRE_KEYWORDS = {
  action: ['action'],
  comedy: ['comedy'],
  thriller: ['thriller'],
  'sci-fi': ['sci-fi','sci fi','science'],
  romance: ['romance','romantic'],
  horror: ['horror'],
  drama: ['drama'],
  crime: ['crime']
};

var CAT_LABELS = {
  all: '🎬 All', trending: '🔥 Trending', bollywood: '🇮🇳 Bollywood', hollywood: '🇺🇸 Hollywood',
  action: '💥 Action', comedy: '😂 Comedy', thriller: '🔪 Thriller', horror: '👻 Horror',
  drama: '🎭 Drama', 'sci-fi': '🚀 Sci-Fi', romance: '❤️ Romance', crime: '🔫 Crime'
};

function getAllMovies() {
  var movies = [], seen = {};
  var cards = document.querySelectorAll('.movie-card[onclick]');
  for (var i = 0; i < cards.length; i++) {
    var el = cards[i];
    var fn = el.getAttribute('onclick') || '';
    var rex = /openModal\(\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*)'\s*,\s*'((?:[^'\\]|\\.)*?)'\s*\)/;
    var match = fn.match(rex);
    if (!match) continue;
    var title = match[1].replace(/\\'/g,"'");
    if (seen[title]) continue;
    seen[title] = true;
    var img = el.querySelector('img');
    var section = el.closest('section');
    var ds = section ? (section.getAttribute('data-section') || '') : '';
    var imgSrc = (img && img.src && img.src.indexOf('placehold') === -1) ? img.src : match[7];
    movies.push({
      title: title, year: match[2], dur: match[3], rating: match[4],
      genre: match[5].replace(/\\'/g,"'"), desc: match[6].replace(/\\'/g,"'"),
      poster: match[7], imgSrc: imgSrc, sk: ds
    });
  }
  return movies;
}

function filterByCategory(cat) {
  var chips = document.querySelectorAll('.cat-chip');
  for (var i = 0; i < chips.length; i++) {
    chips[i].classList.toggle('active', chips[i].getAttribute('data-cat') === cat);
  }
  var sections = document.querySelectorAll('.content-section, .section-divider');
  var panel = document.getElementById('catResultsPanel');
  var grid = document.getElementById('catResultsGrid');
  var titleEl = document.getElementById('catResultsTitle');
  if (cat === 'all') {
    panel.style.display = 'none';
    for (var j = 0; j < sections.length; j++) sections[j].style.display = '';
    window.scrollTo({ top: 0, behavior: 'smooth' }); return;
  }
  var all = getAllMovies(), filtered = [];
  for (var k = 0; k < all.length; k++) {
    var m = all[k];
    var gl = m.genre.toLowerCase();
    if (cat === 'bollywood') {
      if (m.sk === 'bollywood' || gl.indexOf('bollywood') > -1) filtered.push(m);
    } else if (cat === 'hollywood') {
      if (m.sk === 'hollywood' || gl.indexOf('hollywood') > -1) filtered.push(m);
    } else if (cat === 'trending') {
      if (m.sk === 'trending') filtered.push(m);
    } else if (cat === 'action') {
      if (m.sk === 'action' || gl.indexOf('action') > -1) filtered.push(m);
    } else {
      var kws = GENRE_KEYWORDS[cat] || [cat];
      for (var w = 0; w < kws.length; w++) {
        if (gl.indexOf(kws[w]) > -1) { filtered.push(m); break; }
      }
    }
  }
  for (var s = 0; s < sections.length; s++) sections[s].style.display = 'none';
  panel.style.display = '';
  titleEl.innerHTML = (CAT_LABELS[cat] || cat) + ' &nbsp;<span class="cat-count-badge">' + filtered.length + ' titles</span>';
  if (!filtered.length) {
    grid.innerHTML = '<div class="cat-no-results">No titles found.</div>';
    panel.scrollIntoView({ behavior:'smooth', block:'start' }); return;
  }
  var ghtml = '';
  for (var f = 0; f < filtered.length; f++) {
    var fm = filtered[f];
    var fst = fm.title.replace(/'/g,"\\'");
    var fsg = fm.genre.replace(/'/g,"\\'");
    var fsd = fm.desc.replace(/'/g,"\\'");
    var r = parseFloat(fm.rating);
    var bc = r >= 8.5 ? 'badge-top' : (r >= 7 ? 'badge-hd' : 'badge-free');
    var bl = r >= 8.5 ? ('⭐ ' + fm.rating) : (r >= 7 ? 'HD' : 'FREE');
    ghtml +=
      '<div class="movie-card" onclick="openModal(\'' + fst + "','" + fm.year + "','" + fm.dur + "','" + fm.rating + "','" + fsg + "','" + fsd + "','" + fm.poster + '\')">' +
      '<div class="card-img-wrap">' +
      '<img src="' + fm.imgSrc + '" alt="' + fm.title + '" onerror="this.src=\'https://placehold.co/300x450/1c1c23/e63946?text=' + encodeURIComponent(fm.title.slice(0,12)) + '\'">' +
      '<div class="card-overlay"><div class="card-play-btn"></div></div>' +
      '</div>' +
      '<div class="card-info">' +
      '<span class="card-title">' + fm.title + '</span>' +
      '<span class="card-rating">⭐ ' + fm.rating + ' · ' + fm.year + '</span>' +
      '</div>' +
      '</div>';
  }
  grid.innerHTML = ghtml;
  panel.scrollIntoView({ behavior:'smooth', block:'start' });
}

/* ══ SEARCH ═════════════════════════════ */
function buildCatHTML() {
  var h = '', keys = Object.keys(CAT_LABELS).filter(function(k){ return k !== 'all'; });
  for (var i = 0; i < keys.length; i++) {
    h += '<button class="search-cat-chip" data-cat="' + keys[i] + '">' + CAT_LABELS[keys[i]] + '</button>';
  }
  return h;
}

function bindSearchChips() {
  var chips = document.querySelectorAll('#searchDropdown .search-cat-chip');
  for (var i = 0; i < chips.length; i++) {
    (function(chip) {
      chip.addEventListener('click', function() {
        filterByCategory(chip.getAttribute('data-cat')); closeSearch();
      });
    })(chips[i]);
  }
}

function showSearchDefault() {
  var dd = document.getElementById('searchDropdown');
  dd.innerHTML = '<div class="search-cat-section"><div class="search-cat-label">Browse by Category</div><div class="search-cat-chips">' + buildCatHTML() + '</div></div>';
  dd.classList.add('open');
  bindSearchChips();
}

function runSearch(q) {
  var dd = document.getElementById('searchDropdown');
  if (!q) { showSearchDefault(); return; }
  var all = getAllMovies(), results = [], ql = q.toLowerCase();
  for (var i = 0; i < all.length && results.length < 10; i++) {
    var m = all[i];
    if (m.title.toLowerCase().indexOf(ql) > -1 || m.genre.toLowerCase().indexOf(ql) > -1 || m.year.indexOf(q) > -1) results.push(m);
  }
  if (!results.length) {
    dd.innerHTML = '<div class="search-empty">No results for "<strong>' + q + '</strong>"</div>' +
      '<div class="search-cat-section"><div class="search-cat-label">Try a Category</div>' +
      '<div class="search-cat-chips">' + buildCatHTML() + '</div></div>';
    dd.classList.add('open'); bindSearchChips(); return;
  }
  var re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g,'\\$&') + ')', 'gi');
  var h = '<div class="search-results-count">' + results.length + ' result' + (results.length > 1 ? 's' : '') + ' for "<strong>' + q + '</strong>"</div>';
  for (var j = 0; j < results.length; j++) {
    var r = results[j];
    h += '<div class="search-item" data-idx="' + j + '">' +
      '<img src="' + r.imgSrc + '" alt="' + r.title + '" onerror="this.src=\'https://placehold.co/36x54/1c1c23/fff?text=?\'">' +
      '<div class="search-item-info">' +
      '<div class="search-item-title">' + r.title.replace(re,'<mark>$1</mark>') + '</div>' +
      '<div class="search-item-meta">⭐ ' + r.rating + ' · ' + r.year + ' · ' + r.genre + '</div>' +
      '</div></div>';
  }
  dd.innerHTML = h; dd.classList.add('open');
  var items = dd.querySelectorAll('.search-item');
  for (var k = 0; k < items.length; k++) {
    (function(item, movie) {
      item.addEventListener('click', function() {
        openModal(movie.title, movie.year, movie.dur, movie.rating, movie.genre, movie.desc, movie.poster);
        closeSearch();
      });
    })(items[k], results[k]);
  }
}

function closeSearch() {
  var sb = document.getElementById('searchBox');
  var dd = document.getElementById('searchDropdown');
  var si = document.getElementById('searchInput');
  if (sb) sb.classList.remove('open');
  if (dd) { dd.classList.remove('open'); dd.innerHTML = ''; }
  if (si) si.value = '';
}

/* ══ BACK TO TOP ════════════════════════ */
function initBackToTop() {
  var btn = document.getElementById('backTop');
  if (!btn) return;
  window.addEventListener('scroll', function() {
    btn.classList.toggle('visible', window.scrollY > 400);
  });
}

/* ══ FADE-IN SECTIONS ═══════════════════ */
function initFadeIn() {
  var sections = document.querySelectorAll('.content-section');
  if (!window.IntersectionObserver) return;
  var obs = new IntersectionObserver(function(entries) {
    entries.forEach(function(entry) {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.06 });
  for (var i = 0; i < sections.length; i++) {
    sections[i].style.cssText += 'opacity:0;transform:translateY(24px);transition:opacity .5s ease,transform .5s ease;';
    obs.observe(sections[i]);
  }
}

/* ══ BROKEN IMAGE FIXER ═════════════════ */
var IMG_COLORS = [
  ['1a1a2e','e63946'],['0d1b2a','4cc9f0'],['1a0a2e','a78bfa'],
  ['0a2a1a','06d6a0'],['1a0800','ffd166'],['1a0a0a','ff6b6b']
];
var _imgIdx = 0;

function fixBrokenImages() {
  var imgs = document.querySelectorAll('img:not([data-fx])');
  for (var i = 0; i < imgs.length; i++) {
    var img = imgs[i];
    img.setAttribute('data-fx','1');
    (function(img) {
      img.addEventListener('error', function() {
        var lbl = (img.alt || 'Movie').slice(0,10).toUpperCase();
        var c = IMG_COLORS[_imgIdx++ % IMG_COLORS.length];
        img.src = 'https://placehold.co/300x450/' + c[0] + '/' + c[1] + '?text=' + encodeURIComponent(lbl);
      });
      if (img.complete && img.naturalWidth === 0) img.dispatchEvent(new Event('error'));
    })(img);
  }
}

/* ══ INIT ════════════════════════════════ */
function init() {
  loadWatchlist();
  updateWatchlistCount();
  renderWatchlistPanel();

  /* Watchlist */
  var wlNavBtn = document.getElementById('watchlistNavBtn');
  if (wlNavBtn) wlNavBtn.addEventListener('click', function(e) { e.stopPropagation(); openWatchlist(); });
  var wlClose = document.getElementById('wlClose');
  if (wlClose) wlClose.addEventListener('click', closeWatchlist);
  var wlBackdrop = document.getElementById('wlBackdrop');
  if (wlBackdrop) wlBackdrop.addEventListener('click', function(e) { if (e.target === wlBackdrop) closeWatchlist(); });
  var wlClear = document.getElementById('wlClearBtn');
  if (wlClear) {
    wlClear.addEventListener('click', function() {
      if (confirm('Clear your entire watchlist?')) {
        watchlist = []; saveWatchlist(); updateWatchlistCount(); renderWatchlistPanel();
        showToast('Watchlist cleared');
      }
    });
  }

  /* Player */
  var playerClose = document.getElementById('playerClose');
  if (playerClose) playerClose.addEventListener('click', closePlayer);
  var playerOverlay = document.getElementById('playerOverlay');
  if (playerOverlay) playerOverlay.addEventListener('click', function(e) { if (e.target === playerOverlay) closePlayer(); });
  var bigPlay = document.getElementById('playerBigPlay');
  if (bigPlay) {
    bigPlay.addEventListener('click', function() {
      var title = document.getElementById('playerTitle').textContent;
      var ytId = TRAILERS[title];
      if (ytId) {
        var iframe = document.getElementById('playerIframe');
        iframe.src = 'https://www.youtube.com/embed/' + ytId + '?autoplay=1&rel=0&modestbranding=1';
        document.getElementById('playerFake').style.display = 'none';
        iframe.style.display = 'block';
      } else {
        window.open('https://www.youtube.com/results?search_query=' + encodeURIComponent(title + ' official trailer'), '_blank');
        showToast('Opening trailer on YouTube');
      }
    });
  }

  var playerWlBtn = document.getElementById('playerWlBtn');
  if (playerWlBtn) {
    playerWlBtn.addEventListener('click', function() {
      if (!currentMovie) return;
      if (isInWatchlist(currentMovie.title)) { showToast('Already in Watchlist'); return; }
      watchlist.push(currentMovie);
      saveWatchlist(); updateWatchlistCount(); renderWatchlistPanel();
      showToast('"' + currentMovie.title + '" added');
      playerWlBtn.textContent = '✓ In Watchlist';
      playerWlBtn.classList.add('added');
    });
  }

  /* Search */
  var searchToggle = document.getElementById('searchToggle');
  var searchBox = document.getElementById('searchBox');
  var searchInput = document.getElementById('searchInput');
  var searchClear = document.getElementById('searchClear');
  if (searchToggle) {
    searchToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      if (searchBox.classList.contains('open')) {
        closeSearch();
      } else {
        searchBox.classList.add('open');
        setTimeout(function() { searchInput.focus(); showSearchDefault(); }, 80);
      }
    });
  }
  document.addEventListener('click', function(e) {
    if (searchBox && !searchBox.contains(e.target) && e.target !== searchToggle) closeSearch();
  });
  if (searchClear) {
    searchClear.addEventListener('click', function() {
      searchInput.value = ''; showSearchDefault(); searchInput.focus();
    });
  }
  if (searchInput) {
    searchInput.addEventListener('input', function() { runSearch(searchInput.value.trim().toLowerCase()); });
    searchInput.addEventListener('focus', function() { if (!searchInput.value.trim()) showSearchDefault(); });
  }

  /* Category chips */
  var catChips = document.getElementById('catChips');
  if (catChips) {
    catChips.addEventListener('click', function(e) {
      var chip = e.target.closest('.cat-chip');
      if (chip) filterByCategory(chip.getAttribute('data-cat'));
    });
  }
  var catClose = document.getElementById('catResultsClose');
  if (catClose) catClose.addEventListener('click', function() { filterByCategory('all'); });

  /* Keyboard shortcuts */
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeModal(); closeSearch(); closePlayer(); closeWatchlist(); }
    if (e.key === '/' && document.activeElement.tagName !== 'INPUT') {
      e.preventDefault();
      if (searchBox) { searchBox.classList.add('open'); setTimeout(function() { searchInput.focus(); showSearchDefault(); }, 80); }
    }
    if ((e.key === 'w' || e.key === 'W') && document.activeElement.tagName !== 'INPUT') {
      var bd = document.getElementById('wlBackdrop');
      if (bd) bd.classList.contains('open') ? closeWatchlist() : openWatchlist();
    }
  });

  initFadeIn();
  initBackToTop();
  fixBrokenImages();
  setTimeout(fixBrokenImages, 800);
  console.log('CineVault ready | / = Search | W = Watchlist | ESC = Close');
}

/* ── BOOT ── */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}

/* -- LIVE SEARCH API LOGIC -- */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const searchDropdown = document.getElementById('searchDropdown');
    let debounceTimer;
    
    if(searchInput) {
        searchInput.addEventListener('keyup', (e) => {
            clearTimeout(debounceTimer);
            const query = e.target.value.trim();
            
            if(query.length < 2) {
                searchDropdown.style.display = 'none';
                searchDropdown.innerHTML = '';
                return;
            }
            
            debounceTimer = setTimeout(async () => {
                try {
                    const res = await fetch('api/search.php?q=' + encodeURIComponent(query));
                    const data = await res.json();
                    
                    searchDropdown.innerHTML = '';
                    if(data.length > 0) {
                        data.forEach(item => {
                            const a = document.createElement('a');
                            a.href = 'watch.php?id=' + item.id;
                            a.style = 'display:flex; align-items:center; gap:10px; padding:10px; border-bottom:1px solid rgba(255,255,255,0.05); text-decoration:none; color:#fff;';
                            a.innerHTML = \<img src='\' style='width:40px; height:60px; object-fit:cover; border-radius:4px;'><div><div style='font-weight:bold;'>\</div><div style='font-size:12px; color:#aaa;'>\ � \</div></div>\;
                            
                            // Add hover effect dynamically
                            a.addEventListener('mouseover', () => a.style.background = 'rgba(230,0,0,0.1)');
                            a.addEventListener('mouseout', () => a.style.background = 'transparent');
                            
                            searchDropdown.appendChild(a);
                        });
                        searchDropdown.style.display = 'block';
                    } else {
                        searchDropdown.innerHTML = '<div style="padding:15px; color:#aaa; text-align:center;">No results found</div>';
                        searchDropdown.style.display = 'block';
                    }
                } catch(err) { console.error('Search API error'); }
            }, 300);
        });
    }
});


/* -- WATCHLIST PANEL ENGINE -- */
document.addEventListener('DOMContentLoaded', () => {
    const wlNavBtn = document.getElementById('watchlistNavBtn');
    const wlBackdrop = document.getElementById('wlBackdrop');
    const wlClose = document.getElementById('wlClose');
    const wlList = document.getElementById('wlList');
    const wlEmpty = document.getElementById('wlEmpty');
    const wlCount = document.getElementById('watchlistCount');
    
    if(wlNavBtn) {
        wlNavBtn.addEventListener('click', async () => {
            wlBackdrop.classList.add('open');
            document.body.style.overflow = 'hidden';
            
            // Fetch dynamically
            try {
                wlList.innerHTML = '<div style="color:#aaa;text-align:center;padding:20px;">Loading...</div>';
                wlEmpty.style.display = 'none';
                const res = await fetch('api/get_watchlist.php');
                const data = await res.json();
                
                wlList.innerHTML = '';
                if(data.length > 0) {
                    if(wlCount) { wlCount.style.display = 'flex'; wlCount.textContent = data.length; }
                    data.forEach(item => {
                        const el = document.createElement('div');
                        el.style = 'display:flex; gap:15px; margin-bottom:15px; cursor:pointer; background:rgba(255,255,255,0.03); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05);';
                        el.onclick = () => window.location.href = 'watch.php?id=' + item.id;
                        el.innerHTML = \<img src='\' style='width:60px; height:90px; object-fit:cover; border-radius:5px;'><div><h4 style='color:#fff; margin-bottom:5px;'>\</h4><span style='color:#aaa; font-size:12px;'>\ � \</span></div>\;
                        wlList.appendChild(el);
                    });
                } else {
                    wlEmpty.style.display = 'flex';
                    if(wlCount) wlCount.style.display = 'none';
                }
            } catch(e) { /* silent */ }
        });
    }
    
    if(wlClose) {
        wlClose.addEventListener('click', () => {
            wlBackdrop.classList.remove('open');
            document.body.style.overflow = '';
        });
    }
});


/* -- SHARE FUNCTION ------------------------------------------ */
function shareCurrentPage() {
  var url = window.location.href;
  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url).then(function() {
      showToast('?? Link copied to clipboard!');
    }).catch(function() {
      prompt('Copy this link:', url);
    });
  } else {
    prompt('Copy this link:', url);
  }
}

/* -- NOTIFICATIONS ------------------------------------------ */
function loadNotifications() {
  var btn = document.getElementById('notifBtn');
  if (!btn) return;
  fetch('api/get_notifications.php')
    .then(function(r) { return r.json(); })
    .then(function(data) {
      var badge = document.getElementById('notifBadge');
      var list  = document.getElementById('notifList');
      if (!badge || !list) return;
      if (data.unread > 0) {
        badge.textContent = data.unread > 9 ? '9+' : data.unread;
        badge.style.display = 'flex';
      } else {
        badge.style.display = 'none';
      }
      if (data.notifications && data.notifications.length > 0) {
        var html = '';
        data.notifications.forEach(function(n) {
          var link = n.link ? 'onclick="window.location.href=\'' + n.link + '\'"' : '';
          html += '<div class="notif-item' + (n.is_read == '0' ? ' unread' : '') + '" ' + link + '>' +
            '<div class="notif-msg">' + n.message + '</div>' +
            '<div class="notif-time">' + n.created_at + '</div></div>';
        });
        list.innerHTML = html;
      }
    }).catch(function() {});
}

function markAllNotifRead() {
  fetch('api/mark_notification_read.php', { method: 'POST' })
    .then(function() {
      var badge = document.getElementById('notifBadge');
      if (badge) badge.style.display = 'none';
      document.querySelectorAll('.notif-item.unread').forEach(function(el) { el.classList.remove('unread'); });
    });
}

/* -- INIT ---------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function() {
  loadWatchlist();
  renderWatchlistPanel();

  /* Watchlist badge */
  var wlCount = document.getElementById('watchlistCount');
  if (wlCount) {
    if (watchlist.length > 0) { wlCount.textContent = watchlist.length; wlCount.style.display = 'flex'; }
    else { wlCount.style.display = 'none'; }
  }

  /* Watchlist panel btn */
  var wlNavBtn = document.getElementById('watchlistNavBtn');
  if (wlNavBtn) wlNavBtn.addEventListener('click', openWatchlist);
  var wlClose = document.getElementById('wlClose');
  if (wlClose) wlClose.addEventListener('click', closeWatchlist);
  var wlBackdrop = document.getElementById('wlBackdrop');
  if (wlBackdrop) wlBackdrop.addEventListener('click', function(e) { if (e.target === wlBackdrop) closeWatchlist(); });
  var wlClearBtn = document.getElementById('wlClearBtn');
  if (wlClearBtn) wlClearBtn.addEventListener('click', function() { watchlist = []; saveWatchlist(); renderWatchlistPanel(); });

  /* Avatar dropdown */
  var avatarBtn = document.getElementById('avatarBtn');
  var avatarDropdown = document.getElementById('avatarDropdown');
  if (avatarBtn && avatarDropdown) {
    avatarBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      avatarDropdown.classList.toggle('open');
    });
    document.addEventListener('click', function() { avatarDropdown.classList.remove('open'); });
    avatarDropdown.addEventListener('click', function(e) { e.stopPropagation(); });
  }

  /* Search toggle */
  var searchToggle = document.getElementById('searchToggle');
  var searchBox    = document.getElementById('searchBox');
  var searchInput  = document.getElementById('searchInput');
  var searchClear  = document.getElementById('searchClear');
  var searchDrop   = document.getElementById('searchDropdown');
  if (searchToggle && searchBox) {
    searchToggle.addEventListener('click', function(e) {
      e.stopPropagation();
      searchBox.classList.toggle('open');
      if (searchBox.classList.contains('open') && searchInput) searchInput.focus();
    });
    document.addEventListener('click', function() { searchBox.classList.remove('open'); if (searchDrop) searchDrop.innerHTML = ''; });
    searchBox.addEventListener('click', function(e) { e.stopPropagation(); });
  }
  if (searchClear && searchInput) {
    searchClear.addEventListener('click', function() { searchInput.value = ''; if (searchDrop) searchDrop.innerHTML = ''; searchInput.focus(); });
  }
  if (searchInput && searchDrop) {
    var _st = null;
    searchInput.addEventListener('input', function() {
      clearTimeout(_st);
      var q = searchInput.value.trim();
      if (q.length < 2) { searchDrop.innerHTML = ''; return; }
      _st = setTimeout(function() {
        fetch('api/search.php?q=' + encodeURIComponent(q))
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (!data.results || data.results.length === 0) { searchDrop.innerHTML = '<div style="padding:16px;color:#666;text-align:center">No results</div>'; return; }
            var html = '';
            data.results.slice(0, 6).forEach(function(m) {
              html += '<div class="search-result-item" onclick="window.location.href=\'watch.php?id=' + m.id + '\'">' +
                '<img src="' + (m.poster_url || '') + '" onerror="this.src=\'https://placehold.co/36x54/111/fff?text=?\'" style="width:36px;height:54px;object-fit:cover;border-radius:4px;flex-shrink:0">' +
                '<div><div style="font-weight:700;font-size:14px">' + m.title + '</div>' +
                '<div style="color:#888;font-size:12px">' + m.release_year + ' � ' + m.content_type + ' � ? ' + m.rating + '</div></div></div>';
            });
            searchDrop.innerHTML = html;
          }).catch(function() {});
      }, 280);
    });
    searchInput.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' && searchInput.value.trim()) {
        window.location.href = 'search.php?q=' + encodeURIComponent(searchInput.value.trim());
      }
    });
  }

  /* Player close */
  var playerClose = document.getElementById('playerClose');
  if (playerClose) playerClose.addEventListener('click', closePlayer);
  var playerOverlay = document.getElementById('playerOverlay');
  if (playerOverlay) playerOverlay.addEventListener('click', function(e) { if (e.target === playerOverlay) closePlayer(); });

  /* Player big play */
  var playerBigPlay = document.getElementById('playerBigPlay');
  if (playerBigPlay) {
    playerBigPlay.addEventListener('click', function() {
      if (!currentMovie) return;
      var yid = TRAILERS[currentMovie.title] || '';
      if (!yid) { showToast('No trailer available for this title'); return; }
      document.getElementById('playerFake').style.display = 'none';
      var iframe = document.getElementById('playerIframe');
      iframe.src = 'https://www.youtube.com/embed/' + yid + '?autoplay=1&rel=0';
      iframe.style.display = 'block';
    });
  }

  /* Player WL btn */
  var playerWlBtn = document.getElementById('playerWlBtn');
  if (playerWlBtn) {
    playerWlBtn.addEventListener('click', function() {
      if (!currentMovie) return;
      addToWatchlist();
      playerWlBtn.textContent = isInWatchlist(currentMovie.title) ? '? In Watchlist' : '+ Watchlist';
    });
  }

  /* Notification bell */
  var notifBtn = document.getElementById('notifBtn');
  var notifDrop = document.getElementById('notifDropdown');
  if (notifBtn && notifDrop) {
    loadNotifications();
    notifBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      notifDrop.classList.toggle('open');
    });
    document.addEventListener('click', function() { notifDrop.classList.remove('open'); });
    notifDrop.addEventListener('click', function(e) { e.stopPropagation(); });
  }
});
