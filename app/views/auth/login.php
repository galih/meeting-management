<?php
if (Auth::check()) { header('Location: ' . BASE_URL . '/'); exit; }
$error   = $_SESSION['login_error']   ?? null; unset($_SESSION['login_error']);
$success = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$appLogo = SettingController::get('app_logo');
$loginBg = SettingController::get('login_bg');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Masuk &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    /* ══ PALET KEMENBUD ══
       Merah Marun  : #7B1C1C  (primer)
       Merah Tua    : #5C1212  (hover)
       Emas         : #C9A84C  (aksen)
       Krem         : #FAF6EF  (background lembut)
       Krem Border  : #E8DDD0  (garis)
    */
    :root {
      --maroon      : #7B1C1C;
      --maroon-dark : #5C1212;
      --maroon-deep : #3D0A0A;
      --maroon-light: rgba(123,28,28,.08);
      --gold        : #C9A84C;
      --gold-dark   : #A8882E;
      --gold-light  : rgba(201,168,76,.14);
      --cream       : #FAF6EF;
      --cream-border: #E8DDD0;
      --gray-400    : #ACACAC;
      --gray-500    : #8B8B8B;
      --gray-600    : #5A5A5A;
      --gray-900    : #1A1A1A;
    }

    html, body { height: 100%; }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      display: flex;
      min-height: 100vh;
      background: #fff;
    }

    /* ── LEFT PANEL ── */
    .auth-left {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 72px;
      max-width: 580px;
      background: #fff;
    }

    /* Brand */
    .auth-brand {
      display: flex;
      align-items: center;
      gap: 11px;
      margin-bottom: 44px;
      text-decoration: none;
    }
    .auth-brand img { height: 36px; object-fit: contain; }
    .auth-brand-logo {
      width: 40px; height: 40px; border-radius: 10px;
      background: var(--maroon-light);
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .auth-brand-logo svg { color: var(--maroon); }
    .auth-brand-text { line-height: 1.15; }
    .auth-brand-name { font-size: 15px; font-weight: 800; color: var(--maroon-deep); letter-spacing: -.02em; display: block; }
    .auth-brand-sub  { font-size: 10.5px; color: var(--gray-500); font-weight: 500; letter-spacing: .06em; text-transform: uppercase; }

    /* Ornamen aksen */
    .auth-ornament { display: flex; gap: 6px; align-items: center; margin-bottom: 18px; }
    .auth-ornament span { display: block; height: 3px; border-radius: 2px; }
    .auth-ornament .o1 { width: 32px; background: var(--maroon); }
    .auth-ornament .o2 { width: 14px; background: var(--gold); }
    .auth-ornament .o3 { width: 7px; background: var(--gold-light); }

    /* Heading */
    .auth-heading {
      font-size: 28px;
      font-weight: 800;
      color: var(--maroon-deep);
      letter-spacing: -.03em;
      margin-bottom: 6px;
    }
    .auth-subheading {
      font-size: 13.5px;
      color: var(--gray-500);
      margin-bottom: 28px;
      line-height: 1.55;
    }

    /* Alerts */
    .auth-alert {
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 20px;
      border-left: 3px solid;
    }
    .auth-alert-danger  { background: rgba(123,28,28,.07); color: var(--maroon-dark); border-color: var(--maroon); }
    .auth-alert-success { background: #F0FDF4; color: #166534; border-color: #22C55E; }

    /* Form fields */
    .auth-field { margin-bottom: 18px; }
    .auth-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--gray-900);
      margin-bottom: 7px;
    }
    .auth-input {
      width: 100%;
      height: 46px;
      border: 1.5px solid var(--cream-border);
      border-radius: 9px;
      padding: 0 14px;
      font-size: 14px;
      font-family: inherit;
      color: var(--gray-900);
      background: var(--cream);
      outline: none;
      transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .auth-input::placeholder { color: var(--gray-400); }
    .auth-input:focus {
      border-color: var(--maroon);
      background: #fff;
      box-shadow: 0 0 0 3px rgba(123,28,28,.10);
    }

    .pwd-wrap { position: relative; }
    .pwd-wrap .auth-input { padding-right: 44px; }
    .pwd-toggle {
      position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--gray-400); padding: 0;
      display: flex; align-items: center;
      transition: color .13s;
    }
    .pwd-toggle:hover { color: var(--maroon); }

    /* Label row with forgot */
    .auth-label-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 7px;
    }
    .auth-label-row .auth-label { margin-bottom: 0; }
    .auth-forgot {
      font-size: 12px;
      color: var(--maroon);
      text-decoration: none;
      font-weight: 600;
    }
    .auth-forgot:hover { text-decoration: underline; }

    /* Remember */
    .auth-remember {
      display: flex;
      align-items: center;
      gap: 9px;
      margin-bottom: 26px;
    }
    .auth-remember input[type=checkbox] {
      width: 16px; height: 16px;
      accent-color: var(--maroon);
      cursor: pointer;
    }
    .auth-remember label { font-size: 13px; color: var(--gray-600); cursor: pointer; }

    /* Submit button */
    .auth-btn {
      width: 100%;
      height: 48px;
      background: var(--maroon);
      color: #fff;
      border: none;
      border-radius: 9px;
      font-size: 14.5px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      transition: background .15s, box-shadow .15s;
      letter-spacing: .02em;
    }
    .auth-btn:hover  { background: var(--maroon-dark); box-shadow: 0 4px 16px rgba(123,28,28,.28); }
    .auth-btn:active { transform: translateY(1px); }

    /* Divider */
    .auth-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 22px 0;
      font-size: 13px;
      color: var(--gray-400);
    }
    .auth-divider::before,
    .auth-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--cream-border);
    }

    /* OAuth buttons */
    .auth-oauth {
      display: flex;
      gap: 11px;
      margin-bottom: 0;
    }
    .auth-oauth-btn {
      flex: 1; height: 44px;
      border: 1.5px solid var(--cream-border);
      border-radius: 9px;
      background: #fff;
      font-size: 13px;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      color: var(--gray-900);
      transition: border-color .14s, background .14s;
    }
    .auth-oauth-btn:hover { border-color: var(--maroon); background: var(--maroon-light); }

    /* Footer */
    .auth-footer {
      margin-top: 26px;
      font-size: 13.5px;
      color: var(--gray-600);
      text-align: center;
    }
    .auth-footer a { color: var(--maroon); font-weight: 700; text-decoration: none; }
    .auth-footer a:hover { text-decoration: underline; }

    .auth-copyright {
      margin-top: 44px;
      font-size: 11.5px;
      color: var(--gray-400);
    }

    /* ── RIGHT PANEL ── */
    .auth-right {
      flex: 1;
      position: relative;
      overflow: hidden;
      min-height: 100vh;
    }
    .auth-right-img {
      position: absolute;
      inset: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
    /* Overlay gradien tipis agar panel terasa menyatu */
    .auth-right::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg,
        rgba(123,28,28,.18) 0%,
        rgba(61,10,10,.08) 40%,
        transparent 70%);
      pointer-events: none;
      z-index: 1;
    }
    /* Badge branding di sudut kanan bawah */
    .auth-right-badge {
      position: absolute;
      bottom: 32px;
      left: 40px;
      z-index: 2;
      background: rgba(10,5,5,.55);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border: 1px solid rgba(201,168,76,.3);
      border-left: 3px solid var(--gold);
      border-radius: 10px;
      padding: 14px 18px;
      max-width: 300px;
    }
    .auth-right-badge-title {
      font-size: 14px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 5px;
      letter-spacing: -.01em;
    }
    .auth-right-badge-desc {
      font-size: 12px;
      color: rgba(255,255,255,.65);
      line-height: 1.55;
    }
    .auth-right-dots {
      display: flex; gap: 5px; margin-top: 11px;
    }
    .auth-right-dots span {
      width: 24px; height: 3px; border-radius: 2px;
      background: rgba(201,168,76,.4);
    }
    .auth-right-dots span:first-child { background: var(--gold); width: 32px; }

    <?php if ($loginBg): ?>
    .auth-right-img-default { display: none; }
    .auth-right { background: url('<?= htmlspecialchars($loginBg, ENT_QUOTES) ?>') center/cover no-repeat; }
    <?php endif; ?>

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .auth-right { display: none; }
      .auth-left  { max-width: 100%; padding: 48px 28px; }
    }
    @media (max-width: 480px) {
      .auth-left    { padding: 36px 20px; }
      .auth-heading { font-size: 23px; }
    }
  </style>
</head>
<body>

  <!-- LEFT: Form -->
  <div class="auth-left">

    <!-- Brand -->
    <a href="<?= BASE_URL ?>" class="auth-brand">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>">
      <?php else: ?>
        <span class="auth-brand-logo">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
               fill="none" stroke="#7B1C1C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </span>
      <?php endif; ?>
      <span class="auth-brand-text">
        <span class="auth-brand-name"><?= APP_NAME ?></span>
        <span class="auth-brand-sub">Manajemen Kegiatan</span>
      </span>
    </a>

    <!-- Ornamen + Heading -->
    <div class="auth-ornament">
      <span class="o1"></span><span class="o2"></span><span class="o3"></span>
    </div>
    <h1 class="auth-heading">Masuk ke Akun Anda</h1>
    <p class="auth-subheading">Silakan masukkan kredensial Anda untuk mengakses <?= APP_NAME ?></p>

    <?php if ($error): ?>
    <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="auth-alert auth-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="on">

      <!-- Username -->
      <div class="auth-field">
        <label class="auth-label" for="username">Username</label>
        <input
          type="text" id="username" name="username"
          class="auth-input"
          placeholder="Masukkan username Anda"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          required autofocus>
      </div>

      <!-- Password -->
      <div class="auth-field">
        <div class="auth-label-row">
          <label class="auth-label" for="pwd">Password</label>
          <a href="<?= BASE_URL ?>/forgot-password" class="auth-forgot">Lupa password?</a>
        </div>
        <div class="pwd-wrap">
          <input
            type="password" id="pwd" name="password"
            class="auth-input"
            placeholder="Masukkan password Anda"
            autocomplete="current-password"
            required>
          <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Lihat password">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <!-- Remember -->
      <div class="auth-remember">
        <input type="checkbox" id="remember" name="remember">
        <label for="remember">Ingat saya selama 30 hari</label>
      </div>

      <!-- Submit -->
      <button type="submit" class="auth-btn">Masuk ke Sistem</button>

      <!-- Divider -->
      <div class="auth-divider">atau</div>

      <!-- Placeholder OAuth (opsional) -->
      <div class="auth-oauth">
        <button type="button" class="auth-oauth-btn">
          <svg width="17" height="17" viewBox="0 0 48 48">
            <path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.1 8 3l5.7-5.7C34.5 6.5 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/>
            <path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.5 15.9 18.9 12 24 12c3.1 0 5.9 1.1 8 3l5.7-5.7C34.5 6.5 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/>
            <path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-2 1.4-4.5 2.4-7.2 2.4-5.2 0-9.6-3.4-11.2-8H6.3C9.7 39.6 16.3 44 24 44z"/>
            <path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4 5.4l6.2 5.2C40.5 35.4 44 30.1 44 24c0-1.3-.1-2.6-.4-3.9z"/>
          </svg>
          Masuk dengan Google
        </button>
        <button type="button" class="auth-oauth-btn">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
          </svg>
          Masuk dengan Apple
        </button>
      </div>

      <div class="auth-footer">
        Belum punya akun? <a href="<?= BASE_URL ?>/register">Daftar sekarang</a>
      </div>

    </form>

    <div class="auth-copyright">&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></div>
  </div>

  <!-- RIGHT: Photo -->
  <div class="auth-right">
    <?php if (!$loginBg): ?>
    <img
      src="https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=1400&q=85"
      alt=""
      class="auth-right-img auth-right-img-default">
    <?php endif; ?>
    <!-- Branding badge -->
    <div class="auth-right-badge">
      <div class="auth-right-badge-title"><?= APP_NAME ?></div>
      <div class="auth-right-badge-desc">Platform manajemen kegiatan terpadu &mdash; notulen, tindak lanjut, dan kalender dalam satu sistem.</div>
      <div class="auth-right-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>

  <script>
  function togglePwd() {
    var inp  = document.getElementById('pwd');
    var icon = document.getElementById('eye-icon');
    if (inp.type === 'password') {
      inp.type = 'text';
      icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>';
    } else {
      inp.type = 'password';
      icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
    }
  }
  </script>
</body>
</html>
