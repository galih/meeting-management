<?php
$user = Auth::user();
$csrfToken = Auth::csrfToken(); // generate sekali per request
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <meta name="description" content="Aplikasi Manajemen Kegiatan">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> &mdash; <?= APP_NAME ?></title>

  <!-- Tabler Core -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css"/>
  <!-- FullCalendar v7 CSS (skeleton + theme monarch + palette blue) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/skeleton.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/themes/monarch/theme.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/themes/monarch/palettes/blue.css"/>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= filemtime(ROOT_PATH . '/assets/css/custom.css') ?>">

  <?= $headScripts ?? '' ?>

  <style>
  /* ── YouTube Music Widget ── */
  #yt-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1080;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    background: #FF0000;
    color: #fff;
    border: none;
    box-shadow: 0 4px 16px rgba(0,0,0,.3);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform .2s, background .2s;
  }
  #yt-fab:hover { transform: scale(1.1); background: #cc0000; }
  #yt-fab svg { width:22px;height:22px; }

  #yt-widget {
    position: fixed;
    bottom: 82px;
    right: 24px;
    z-index: 1080;
    width: 340px;
    background: #0f0f0f;
    border-radius: 16px;
    box-shadow: 0 8px 40px rgba(0,0,0,.5);
    overflow: hidden;
    display: none;
    flex-direction: column;
    font-family: inherit;
  }
  #yt-widget.open { display: flex; }

  #yt-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 11px 14px;
    background: #FF0000;
    color: #fff;
  }
  #yt-header .title {
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .04em;
    display: flex;
    align-items: center;
    gap: 7px;
  }
  #yt-header-actions { display:flex;gap:4px; }
  #yt-header-actions button {
    background: rgba(0,0,0,.25);
    border: none;
    color: #fff;
    border-radius: 6px;
    padding: 3px 9px;
    font-size: 12px;
    cursor: pointer;
    transition: background .15s;
  }
  #yt-header-actions button:hover { background: rgba(0,0,0,.45); }

  #yt-iframe-wrap {
    position: relative;
    width: 100%;
    aspect-ratio: 16/9;
    background: #000;
  }
  #yt-iframe-wrap iframe {
    width: 100%;
    height: 100%;
    border: none;
    display: block;
  }
  #yt-error-msg {
    display: none;
    position: absolute;
    inset: 0;
    background: #111;
    color: rgba(255,255,255,.7);
    font-size: 12px;
    text-align: center;
    padding: 20px 16px;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    line-height: 1.5;
  }
  #yt-error-msg.show { display: flex; }
  #yt-error-msg .err-icon { font-size: 28px; }
  #yt-error-msg a { color: #FF0000; text-decoration: none; font-weight: 600; }

  #yt-input-wrap {
    padding: 10px 12px;
    background: #181818;
    display: flex;
    gap: 6px;
    align-items: center;
  }
  #yt-input-wrap input {
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
  #yt-input-wrap input::placeholder { color: rgba(255,255,255,.35); }
  #yt-input-wrap input:focus { border-color: #FF0000; }
  #yt-input-wrap button {
    background: #FF0000;
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
  #yt-input-wrap button:hover { background: #cc0000; }

  #yt-hint {
    padding: 0 12px 10px;
    background: #181818;
    color: rgba(255,255,255,.3);
    font-size: 10.5px;
    line-height: 1.5;
  }
  #yt-hint code { color: #FF0000; }
  </style>
</head>
<body class="antialiased">

  <!-- CSRF meta tag — dipakai fetch() JS via header X-CSRF-Token -->
  <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">

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
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></p>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <!-- ── YouTube Music Widget ── -->
  <button id="yt-fab" title="YouTube Music Player" aria-label="Buka YouTube Player">
    <svg viewBox="0 0 24 24" fill="currentColor">
      <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
    </svg>
  </button>

  <div id="yt-widget" role="complementary" aria-label="YouTube Music Player">
    <div id="yt-header">
      <span class="title">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
          <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
        </svg>
        YouTube Player
      </span>
      <div id="yt-header-actions">
        <button id="yt-minimize" title="Minimize">&#8211;</button>
        <button id="yt-close" title="Tutup &amp; matikan">&#10005;</button>
      </div>
    </div>

    <div id="yt-iframe-wrap">
      <iframe id="yt-iframe"
        allow="autoplay; encrypted-media"
        allowfullscreen
        src=""></iframe>
      <div id="yt-error-msg">
        <span class="err-icon">🚫</span>
        <span>Video/playlist ini tidak bisa di-embed.<br>Coba URL lain atau buka langsung di
          <a id="yt-error-link" href="#" target="_blank" rel="noopener">YouTube</a>.
        </span>
      </div>
    </div>

    <div id="yt-input-wrap">
      <input type="text" id="yt-url-input"
             placeholder="Paste URL YouTube video / playlist">
      <button id="yt-load-btn">Putar</button>
    </div>
    <div id="yt-hint">
      Contoh URL yang bisa dipakai:<br>
      <code>youtu.be/jfKfPfyJRdk</code> &nbsp;&middot;&nbsp;
      <code>youtube.com/watch?v=...</code><br>
      <code>youtube.com/playlist?list=...</code><br>
      Preferensi disimpan otomatis di browser.
    </div>
  </div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<!-- FullCalendar v7 JS -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/all/global.js"></script>
<!-- FullCalendar v7 Theme JS (monarch) -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/themes/monarch/global.js"></script>
<!-- FullCalendar v7 Locale Indonesia -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@7.0.0/locales/id/global.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<!-- Inject CSRF token ke semua fetch() POST secara global -->
<script>
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '';
  const _origFetch = window.fetch;
  window.fetch = function(url, opts = {}) {
    if (!opts.method || opts.method.toUpperCase() === 'GET') return _origFetch(url, opts);
    opts.headers = Object.assign({ 'X-CSRF-Token': CSRF_TOKEN }, opts.headers || {});
    if (opts.body instanceof FormData && !opts.body.has('_csrf')) {
      opts.body.append('_csrf', CSRF_TOKEN);
    }
    return _origFetch(url, opts);
  };
</script>
<script src="<?= BASE_URL ?>/assets/js/notifications.js"></script>
<?= $scripts ?? '' ?>

<script>
(function () {
  const LS_URL     = 'yt_music_url';
  const LS_OPEN    = 'yt_music_open';
  const LS_ENABLED = 'yt_music_enabled';

  const fab      = document.getElementById('yt-fab');
  const widget   = document.getElementById('yt-widget');
  const iframe   = document.getElementById('yt-iframe');
  const errMsg   = document.getElementById('yt-error-msg');
  const errLink  = document.getElementById('yt-error-link');
  const urlInput = document.getElementById('yt-url-input');
  const loadBtn  = document.getElementById('yt-load-btn');
  const minBtn   = document.getElementById('yt-minimize');
  const closeBtn = document.getElementById('yt-close');

  function toEmbedUrl(raw) {
    raw = raw.trim();
    if (!raw) return '';
    try {
      const u    = new URL(raw);
      const list = u.searchParams.get('list');
      const v    = u.searchParams.get('v');
      let embed  = 'https://www.youtube-nocookie.com/embed/';

      if (list && v)  embed += v + '?list=' + list + '&autoplay=1&rel=0';
      else if (list)  embed += 'videoseries?list=' + list + '&autoplay=1&rel=0';
      else if (v)     embed += v + '?autoplay=1&rel=0';
      else if (u.hostname === 'youtu.be') {
        const id = u.pathname.replace(/^\//, '');
        embed += id + '?autoplay=1&rel=0';
      } else return raw;

      return embed;
    } catch (e) { return raw; }
  }

  function loadUrl(raw) {
    const embed = toEmbedUrl(raw);
    if (!embed) return;
    errMsg.classList.remove('show');
    errLink.href = raw;
    iframe.src   = embed;
    localStorage.setItem(LS_URL, raw);
  }

  window.addEventListener('message', e => {
    if (e.origin.includes('youtube') && e.data && e.data.event === 'onError') {
      if ([100, 101, 150].includes(e.data.info)) {
        errMsg.classList.add('show');
      }
    }
  });

  iframe.addEventListener('load', () => {
    try {
      if (!iframe.src || iframe.src === window.location.href) return;
      errMsg.classList.remove('show');
    } catch (e) {}
  });

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
    errMsg.classList.remove('show');
    localStorage.setItem(LS_OPEN, '0');
    localStorage.setItem(LS_ENABLED, '0');
  }

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
