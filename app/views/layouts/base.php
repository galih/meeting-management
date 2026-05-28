<?php
$user = Auth::user();
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <meta name="description" content="Aplikasi Manajemen Meeting">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> &mdash; <?= APP_NAME ?></title>

  <!-- Tabler Core -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css"/>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= filemtime(ROOT_PATH . '/assets/css/custom.css') ?>">

  <?= $headScripts ?? '' ?>

  <style>
  /* ── Spotify Music Widget ── */
  #music-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1080;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #1DB954;
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform .2s, background .2s;
  }
  #music-fab:hover { transform: scale(1.1); background: #1ed760; }
  #music-fab svg { width:22px;height:22px; }

  #music-widget {
    position: fixed;
    bottom: 82px;
    right: 24px;
    z-index: 1080;
    width: 340px;
    background: #121212;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
    overflow: hidden;
    display: none;
    flex-direction: column;
    font-family: inherit;
  }
  #music-widget.open { display: flex; }

  #music-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 14px;
    background: #1DB954;
    color: #fff;
  }
  #music-header .title {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: 7px;
  }
  #music-header-actions { display:flex;gap:4px; }
  #music-header-actions button {
    background: rgba(0,0,0,.2);
    border: none;
    color: #fff;
    border-radius: 6px;
    padding: 3px 9px;
    font-size: 12px;
    cursor: pointer;
    transition: background .15s;
  }
  #music-header-actions button:hover { background: rgba(0,0,0,.4); }

  #music-iframe-wrap { width:100%; background:#000; }
  #music-iframe-wrap iframe { width:100%;border:none;display:block; }

  #music-input-wrap {
    padding: 10px 12px;
    background: #181818;
    display: flex;
    gap: 6px;
    align-items: center;
  }
  #music-input-wrap input {
    flex: 1;
    background: #282828;
    border: 1px solid rgba(255,255,255,.1);
    color: #fff;
    border-radius: 8px;
    padding: 6px 10px;
    font-size: 12px;
    outline: none;
    transition: border-color .15s;
  }
  #music-input-wrap input::placeholder { color: rgba(255,255,255,.35); }
  #music-input-wrap input:focus { border-color: #1DB954; }
  #music-input-wrap button {
    background: #1DB954;
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 6px 13px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
  }
  #music-input-wrap button:hover { background: #1ed760; }

  #music-hint {
    padding: 0 12px 10px;
    background: #181818;
    color: rgba(255,255,255,.3);
    font-size: 10.5px;
    line-height: 1.5;
  }
  #music-hint a { color: #1DB954; text-decoration: none; }
  </style>
</head>
<body class="antialiased">

  <!-- Top Navigation -->
  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- Page Body -->
  <div class="page-wrapper-topnav">
    <div class="page-body">
      <div class="container-xl">

        <!-- Flash messages -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible mt-3">
          <?= htmlspecialchars($_SESSION['flash_success']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_success']); endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger alert-dismissible mt-3">
          <?= htmlspecialchars($_SESSION['flash_error']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <?= $content ?? '' ?>
      </div>
    </div>

    <!-- Footer -->
    <footer class="footer footer-transparent d-print-none">
      <div class="container-xl">
        <div class="row text-center align-items-center">
          <div class="col-12">
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; PHP 8.5 &amp; Tabler</p>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- ── Spotify Music Widget ── -->
  <button id="music-fab" title="Spotify Music Player" aria-label="Buka Spotify Player">
    <!-- Spotify icon -->
    <svg viewBox="0 0 24 24" fill="currentColor">
      <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.516 17.314a.75.75 0 0 1-1.032.25c-2.826-1.727-6.38-2.117-10.566-1.16a.75.75 0 1 1-.334-1.462c4.583-1.047 8.516-.596 11.682 1.34a.75.75 0 0 1 .25 1.032zm1.47-3.27a.937.937 0 0 1-1.288.308C14.894 12.315 11.1 11.82 6.59 13.1a.937.937 0 0 1-.474-1.814c5.016-1.394 9.253-.792 12.562 1.47a.937.937 0 0 1 .308 1.288zm.126-3.404C15.533 8.387 10.64 8.227 7.25 9.27a1.125 1.125 0 0 1-.652-2.152c3.89-1.178 9.373-.95 13.072 1.47a1.125 1.125 0 0 1-1.558 1.052z"/>
    </svg>
  </button>

  <div id="music-widget" role="complementary" aria-label="Spotify Music Player">
    <div id="music-header">
      <span class="title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.516 17.314a.75.75 0 0 1-1.032.25c-2.826-1.727-6.38-2.117-10.566-1.16a.75.75 0 1 1-.334-1.462c4.583-1.047 8.516-.596 11.682 1.34a.75.75 0 0 1 .25 1.032zm1.47-3.27a.937.937 0 0 1-1.288.308C14.894 12.315 11.1 11.82 6.59 13.1a.937.937 0 0 1-.474-1.814c5.016-1.394 9.253-.792 12.562 1.47a.937.937 0 0 1 .308 1.288zm.126-3.404C15.533 8.387 10.64 8.227 7.25 9.27a1.125 1.125 0 0 1-.652-2.152c3.89-1.178 9.373-.95 13.072 1.47a1.125 1.125 0 0 1-1.558 1.052z"/>
        </svg>
        Spotify Player
      </span>
      <div id="music-header-actions">
        <button id="music-minimize" title="Minimize">&#8211;</button>
        <button id="music-close" title="Tutup &amp; matikan">&#10005;</button>
      </div>
    </div>

    <div id="music-iframe-wrap">
      <iframe id="music-iframe"
        allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
        loading="lazy"
        height="352"
        src=""></iframe>
    </div>

    <div id="music-input-wrap">
      <input type="text" id="music-url-input"
             placeholder="Paste URL Spotify playlist / album / track">
      <button id="music-load-btn">Putar</button>
    </div>
    <div id="music-hint">
      Contoh URL:<br>
      <code style="color:#1DB954">open.spotify.com/playlist/...</code><br>
      <code style="color:#1DB954">open.spotify.com/track/...</code><br>
      Preferensi disimpan otomatis di browser.
    </div>
  </div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/notifications.js"></script>
<?= $scripts ?? '' ?>

<script>
(function () {
  const LS_URL     = 'spotify_url';
  const LS_OPEN    = 'spotify_open';
  const LS_ENABLED = 'spotify_enabled';

  const fab      = document.getElementById('music-fab');
  const widget   = document.getElementById('music-widget');
  const iframe   = document.getElementById('music-iframe');
  const urlInput = document.getElementById('music-url-input');
  const loadBtn  = document.getElementById('music-load-btn');
  const minBtn   = document.getElementById('music-minimize');
  const closeBtn = document.getElementById('music-close');

  /**
   * Konversi URL Spotify biasa → Spotify Embed URL
   * open.spotify.com/playlist/ID  →  open.spotify.com/embed/playlist/ID
   * open.spotify.com/track/ID     →  open.spotify.com/embed/track/ID
   * open.spotify.com/album/ID     →  open.spotify.com/embed/album/ID
   */
  function toEmbedUrl(raw) {
    raw = raw.trim();
    if (!raw) return '';
    try {
      const u = new URL(raw);
      if (!u.hostname.includes('spotify.com')) return raw;
      // Ganti /playlist/, /track/, /album/ → /embed/playlist/ dst
      const path = u.pathname.replace(/^\/(intl-[a-z]+\/)?/, '/');
      const embedPath = path.replace(/^\/(playlist|track|album|episode|show)/, '/embed/$1');
      return 'https://open.spotify.com' + embedPath + '?utm_source=generator&theme=0';
    } catch (e) {
      return raw;
    }
  }

  function loadUrl(raw) {
    const embed = toEmbedUrl(raw);
    if (!embed) return;
    iframe.src = embed;
    localStorage.setItem(LS_URL, raw);
  }

  function openWidget() {
    widget.classList.add('open');
    localStorage.setItem(LS_OPEN, '1');
    localStorage.setItem(LS_ENABLED, '1');
  }

  function minimizeWidget() {
    widget.classList.remove('open');
    localStorage.setItem(LS_OPEN, '0');
  }

  function closeWidget() {
    widget.classList.remove('open');
    iframe.src = '';
    localStorage.setItem(LS_OPEN, '0');
    localStorage.setItem(LS_ENABLED, '0');
  }

  fab.addEventListener('click', () => {
    if (widget.classList.contains('open')) {
      minimizeWidget();
    } else {
      openWidget();
      const saved = localStorage.getItem(LS_URL);
      if (saved && !iframe.src.includes('spotify')) {
        urlInput.value = saved;
        loadUrl(saved);
      }
    }
  });

  minBtn.addEventListener('click', minimizeWidget);
  closeBtn.addEventListener('click', closeWidget);

  loadBtn.addEventListener('click', () => {
    const val = urlInput.value.trim();
    if (!val) return;
    loadUrl(val);
  });

  urlInput.addEventListener('keydown', e => {
    if (e.key === 'Enter') loadBtn.click();
  });

  // Restore state saat halaman load
  const savedUrl  = localStorage.getItem(LS_URL);
  const wasOpen   = localStorage.getItem(LS_OPEN) === '1';
  const isEnabled = localStorage.getItem(LS_ENABLED) !== '0';

  if (savedUrl) urlInput.value = savedUrl;

  if (isEnabled && wasOpen && savedUrl) {
    openWidget();
    loadUrl(savedUrl);
  }
})();
</script>
</body>
</html>
