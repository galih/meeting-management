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
  <title>Lupa Password &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --maroon      : #7B1C1C;
      --maroon-dark : #5C1212;
      --maroon-deep : #3D0A0A;
      --maroon-light: rgba(123,28,28,.08);
      --gold        : #C9A84C;
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
    .auth-brand-text { line-height: 1.15; }
    .auth-brand-name { font-size: 15px; font-weight: 800; color: var(--maroon-deep); letter-spacing: -.02em; display: block; }
    .auth-brand-sub  { font-size: 10.5px; color: var(--gray-500); font-weight: 500; letter-spacing: .06em; text-transform: uppercase; }

    /* Ornamen */
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
      line-height: 1.6;
    }

    /* Alerts */
    .auth-alert {
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 20px;
      border-left: 3px solid;
      line-height: 1.5;
    }
    .auth-alert-danger  { background: rgba(123,28,28,.07); color: var(--maroon-dark); border-color: var(--maroon); }
    .auth-alert-success {
      background: #F0FDF4; color: #166534; border-color: #22C55E;
      display: flex; gap: 10px; align-items: flex-start;
    }
    .auth-alert-success svg { flex-shrink: 0; margin-top: 1px; }

    /* Form fields */
    .auth-field { margin-bottom: 20px; }
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
      margin-bottom: 18px;
    }
    .auth-btn:hover  { background: var(--maroon-dark); box-shadow: 0 4px 16px rgba(123,28,28,.28); }
    .auth-btn:active { transform: translateY(1px); }
    .auth-btn:disabled { opacity: .6; cursor: not-allowed; }

    /* Back link */
    .auth-back {
      text-align: center;
      font-size: 13px;
      color: var(--gray-600);
    }
    .auth-back a {
      color: var(--maroon);
      font-weight: 700;
      text-decoration: none;
    }
    .auth-back a:hover { text-decoration: underline; }

    .auth-copyright {
      margin-top: 36px;
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
      position: absolute; inset: 0;
      width: 100%; height: 100%;
      object-fit: cover; object-position: center;
    }
    .auth-right::after {
      content: '';
      position: absolute; inset: 0;
      background: linear-gradient(135deg,
        rgba(123,28,28,.18) 0%,
        rgba(61,10,10,.08) 40%,
        transparent 70%);
      pointer-events: none; z-index: 1;
    }
    .auth-right-badge {
      position: absolute;
      bottom: 32px; left: 40px;
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
    .auth-right-badge-title  { font-size: 14px; font-weight: 700; color: #fff; margin-bottom: 5px; letter-spacing: -.01em; }
    .auth-right-badge-desc   { font-size: 12px; color: rgba(255,255,255,.65); line-height: 1.55; }
    .auth-right-dots         { display: flex; gap: 5px; margin-top: 11px; }
    .auth-right-dots span    { width: 24px; height: 3px; border-radius: 2px; background: rgba(201,168,76,.4); }
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

    <!-- Icon ilustrasi kunci -->
    <div style="width:52px;height:52px;border-radius:14px;background:var(--maroon-light);display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
           fill="none" stroke="var(--maroon)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
    </div>

    <!-- Ornamen + Heading -->
    <div class="auth-ornament">
      <span class="o1"></span><span class="o2"></span><span class="o3"></span>
    </div>
    <h1 class="auth-heading">Lupa Password?</h1>
    <p class="auth-subheading">
      Masukkan alamat email yang terdaftar. Kami akan mengirimkan tautan untuk mengatur ulang password Anda.
    </p>

    <?php if ($error): ?>
    <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
    <div class="auth-alert auth-alert-success">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
           fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      <span><?= htmlspecialchars($success) ?></span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/forgot-password" id="forgotForm">

      <div class="auth-field">
        <label class="auth-label" for="email">Alamat Email</label>
        <input
          type="email" id="email" name="email"
          class="auth-input"
          placeholder="contoh@email.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required autofocus
          autocomplete="email">
      </div>

      <button type="submit" class="auth-btn" id="submitBtn">
        Kirim Tautan Reset
      </button>

      <div class="auth-back">
        <a href="<?= BASE_URL ?>/login">&larr; Kembali ke halaman masuk</a>
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
    <div class="auth-right-badge">
      <div class="auth-right-badge-title"><?= APP_NAME ?></div>
      <div class="auth-right-badge-desc">Platform manajemen kegiatan terpadu &mdash; notulen, tindak lanjut, dan kalender dalam satu sistem.</div>
      <div class="auth-right-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>

  <script>
  document.getElementById('forgotForm').addEventListener('submit', function () {
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.textContent = 'Mengirim\u2026';
  });
  </script>

</body>
</html>
