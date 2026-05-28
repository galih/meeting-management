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
  /* ── Lofi Player Widget ── */
  #lofi-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1080;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #7B1C1C;
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform .2s;
  }
  #lofi-fab:hover { transform: scale(1.1); }
  #lofi-fab svg { width:20px;height:20px; }

  #lofi-widget {
    position: fixed;
    bottom: 80px;
    right: 24px;
    z-index: 1080;
    width: 320px;
    background: #1a1a2e;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
    overflow: hidden;
    display: none;
    flex-direction: column;
    font-family: inherit;
  }
  #lofi-widget.open { display: flex; }

  #lofi-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #7B1C1C;
    color: #fff;
  }
  #lofi-header span {
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .03em;
    display: flex;
    align-items: center;
    gap: 6px;
  }
  #lofi-header-actions { display:flex;gap:4px; }
  #lofi-header-actions button {
    background: rgba(255,255,255,.15);
    border: none;
    color: #fff;
    border-radius: 6px;
    padding: 3px 8px;
    font-size: 11px;
    cursor: pointer;
    transition: background .15s;
  }
  #lofi-header-actions button:hover { background: rgba(255,255,255,.3); }

  #lofi-iframe-wrap { position:relative;width:100%;aspect-ratio:16/9;background:#000; }
  #lofi-iframe-wrap iframe { width:100%;height:100%;border:none;display:block; }

  #lofi-input-wrap {
    padding: 10px 12px;
    background: #16213e;
    display: flex;
    gap: 6px;
    align-items: center;
  }
  #lofi-input-wrap input {
    flex: 1;
    background: #0f3460;
    border: 1px solid rgba(255,255,255,.1);
    color: #fff;
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 12px;
    outline: none;
  }
  #lofi-input-wrap input::placeholder { color: rgba(255,255,255,.4); }
  #lofi-input-wrap input:focus { border-color: #7B1C1C; }
  #lofi-input-wrap button {
    background: #7B1C1C;
    border: none;
    color: #fff;
    border-radius: 8px;
    padding: 5px 12px;
    font-size: 12px;
    cursor: pointer;
    white-space: nowrap;
    transition: background .15s;
  }
  #lofi-input-wrap button:hover { background: #9b2c2c; }

  #lofi-hint {
    padding: 0 12px 10px;
    background: #16213e;
    color: rgba(255,255,255,.35);
    font-size: 10.5px;
    line-height: 1.4;
  }
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

  <!-- ── Lofi Music Player Widget ── -->
  <!-- FAB Button -->
  <button id="lofi-fab" title="Lofi Music Player" aria-label="Buka Lofi Player">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <path d="M9 18V5l12-2v13"/>
      <circle cx="6" cy="18" r="3"/>
      <circle cx="18" cy="16" r="3"/>
    </svg>
  </button>

  <!-- Widget Panel -->
  <div id="lofi-widget" role="complementary" aria-label="Lofi Music Player">
    <div id="lofi-header">
      <span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>
        </svg>
        Lofi Music
      </span>
      <div id="lofi-header-actions">
        <button id="lofi-minimize" title="Minimize">&#8211;</button>
        <button id="lofi-close" title="Tutup &amp; matikan">&#10005;</button>
      </div>
    </div>

    <div id="lofi-iframe-wrap">
      <iframe id="lofi-iframe"
        allow="autoplay; encrypted-media"
        allowfullscreen
        referrerpolicy="no-referrer"
        src=""></iframe>
    </div>

    <div id="lofi-input-wrap">
      <input type="text" id="lofi-url-input"
             placeholder="Paste URL YouTube playlist / video">
      <button id="lofi-load-btn">Putar</button>
    </div>
    <div id="lofi-hint">
      Contoh: https://www.youtube.com/playlist?list=... atau URL video biasa.
      Playlist &amp; preferensi disimpan otomatis.
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
  const LS_URL     = 'lofi_playlist_url';
  const LS_OPEN    = 'lofi_open';
  const LS_ENABLED = 'lofi_enabled';

  const fab      = document.getElementById('lofi-fab');
  const widget   = document.getElementById('lofi-widget');
  const iframe   = document.getElementById('lofi-iframe');
  const urlInput = document.getElementById('lofi-url-input');
  const loadBtn  = document.getElementById('lofi-load-btn');
  const minBtn   = document.getElementById('lofi-minimize');
  const closeBtn = document.getElementById('lofi-close');

  // Konversi URL YouTube biasa → embed URL
  function toEmbedUrl(raw) {
    raw = raw.trim();
    if (!raw) return '';
    try {
      const u = new URL(raw);
      const list = u.searchParams.get('list');
      const v    = u.searchParams.get('v');
      let embed  = 'https://www.youtube.com/embed/';
      if (list) {
        // playlist
        embed += (v ? v : 'videoseries') + '?list=' + list + '&autoplay=1';
      } else if (v) {
        // video biasa
        embed += v + '?autoplay=1';
      } else if (u.hostname === 'youtu.be') {
        // short URL
        embed += u.pathname.replace('/', '') + '?autoplay=1';
      } else {
        return raw; // fallback: langsung pakai
      }
      return embed;
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
    iframe.src = ''; // stop playback
    localStorage.setItem(LS_OPEN, '0');
    localStorage.setItem(LS_ENABLED, '0');
  }

  // Event listeners
  fab.addEventListener('click', () => {
    if (widget.classList.contains('open')) {
      minimizeWidget();
    } else {
      openWidget();
      const saved = localStorage.getItem(LS_URL);
      if (saved && !iframe.src.includes('youtube')) {
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
  const savedUrl     = localStorage.getItem(LS_URL);
  const wasOpen      = localStorage.getItem(LS_OPEN) === '1';
  const isEnabled    = localStorage.getItem(LS_ENABLED) !== '0'; // default enabled

  if (savedUrl) urlInput.value = savedUrl;

  if (isEnabled && wasOpen && savedUrl) {
    openWidget();
    loadUrl(savedUrl);
  }
})();
</script>
</body>
</html>
