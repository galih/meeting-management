<?php
if (Auth::check()) { header('Location: ' . BASE_URL . '/'); exit; }
$error   = $_SESSION['login_error']   ?? null; unset($_SESSION['login_error']);
$success = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$appLogo = SettingController::get('app_logo');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Login &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --red:       #C0392B;
      --red-dark:  #3D0A0A;
      --red-light: #FADBD8;
      --gold:      #C9A84C;
      --navy:      #1B2A4A;
      --white:     #ffffff;
      --gray-50:   #F8F4EE;
      --gray-100:  #f8f4ee;
      --gray-300:  #DEE2E6;
      --gray-500:  #868E96;
      --gray-700:  #495057;
      --gray-900:  #212529;
    }

    body {
      min-height: 100vh;
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f8f4ee;
      display: flex;
      flex-direction: column;
    }

    /* HEADER */
    .login-header {
      display: flex;
      align-items: stretch;
      height: 64px;
      background: #3D0A0A;
      box-shadow: 0 2px 8px rgba(61,10,10,.3);
      position: relative;
      z-index: 10;
    }
    .login-header::before {
      content: '';
      display: block;
      width: 6px;
      background: var(--red);
      flex-shrink: 0;
    }
    .header-inner {
      flex: 1;
      display: flex;
      align-items: center;
      padding: 0 32px;
    }
    .header-brand {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }
    .header-brand img { max-height: 40px; object-fit: contain; }
    .header-brand-text { display: flex; flex-direction: column; }
    .header-brand-text .app-name {
      font-size: 16px;
      font-weight: 800;
      color: #fff;
      line-height: 1.1;
      letter-spacing: -.02em;
    }
    .header-brand-text .app-tagline {
      font-size: 10.5px;
      color: rgba(255,255,255,.5);
      font-weight: 500;
      text-transform: uppercase;
      letter-spacing: .06em;
    }

    /* WRAPPER */
    .login-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 16px 56px;
      background-image: radial-gradient(circle, rgba(192,57,43,.06) 1px, transparent 1px);
      background-size: 28px 28px;
    }

    /* CARD */
    .login-card {
      display: flex;
      width: 100%;
      max-width: 920px;
      background: var(--white);
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 2px 24px rgba(0,0,0,.10);
      border-top: 3px solid var(--gold);
    }

    /* PANEL KIRI */
    .login-form-panel {
      flex: 1;
      padding: 52px 52px 44px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      border-right: 1px solid #f0ebe3;
    }
    .form-ornament {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }
    .form-ornament .line-red  { width: 36px; height: 3px; background: var(--red); border-radius: 2px; }
    .form-ornament .line-gold { width: 12px; height: 3px; background: var(--gold); border-radius: 2px; }
    .form-title {
      font-size: 24px;
      font-weight: 800;
      color: #3D0A0A;
      letter-spacing: -.03em;
      margin-bottom: 6px;
    }
    .form-subtitle {
      font-size: 13.5px;
      color: var(--gray-500);
      margin-bottom: 32px;
      line-height: 1.5;
    }

    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block;
      font-size: 12.5px;
      font-weight: 700;
      color: var(--gray-700);
      margin-bottom: 7px;
      text-transform: uppercase;
      letter-spacing: .05em;
    }
    .label-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
    .label-row label { margin-bottom: 0; }
    .label-row a { font-size: 12px; color: var(--red); text-decoration: none; font-weight: 600; }
    .label-row a:hover { text-decoration: underline; }

    .form-control-login {
      width: 100%;
      height: 46px;
      border: 1.5px solid var(--gray-300);
      border-radius: 4px;
      padding: 0 14px;
      font-size: 14px;
      font-family: inherit;
      color: var(--gray-900);
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      background: #faf7f3;
    }
    .form-control-login::placeholder { color: #ADB5BD; }
    .form-control-login:focus {
      border-color: var(--red);
      background: var(--white);
      box-shadow: 0 0 0 3px rgba(192,57,43,.10);
    }

    .pwd-wrap { position: relative; }
    .pwd-wrap .form-control-login { padding-right: 46px; }
    .pwd-toggle {
      position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer; color: #ADB5BD; padding: 0;
      display: flex; align-items: center;
    }
    .pwd-toggle:hover { color: var(--gray-700); }

    .remember-row { display: flex; align-items: center; gap: 9px; margin-bottom: 26px; }
    .remember-row input[type=checkbox] { width: 16px; height: 16px; accent-color: var(--red); cursor: pointer; }
    .remember-row label { font-size: 13px; color: var(--gray-500); cursor: pointer; font-weight: 500; }

    .btn-login {
      width: 100%; height: 48px;
      background: #3D0A0A;
      color: var(--white);
      border: none; border-radius: 4px;
      font-size: 14px; font-weight: 700; font-family: inherit;
      cursor: pointer;
      transition: background .2s, box-shadow .2s;
      letter-spacing: .04em; text-transform: uppercase;
    }
    .btn-login:hover { background: #5a1010; box-shadow: 0 4px 16px rgba(61,10,10,.3); }
    .btn-login:active { transform: translateY(1px); }

    .alert-login {
      padding: 11px 14px; border-radius: 4px;
      font-size: 13px; margin-bottom: 22px; font-weight: 500;
    }
    .alert-danger  { background: var(--red-light); color: #7B241C; border-left: 3px solid var(--red); }
    .alert-success { background: #D5F5E3; color: #1D6A3A; border-left: 3px solid #27AE60; }

    .form-footer {
      margin-top: 24px; font-size: 11.5px;
      color: var(--gray-500); text-align: center; line-height: 1.6;
    }

    /* PANEL KANAN */
    .login-brand-panel {
      width: 360px; flex-shrink: 0;
      background: var(--navy);
      padding: 52px 40px 44px;
      display: flex; flex-direction: column; justify-content: space-between;
      color: var(--white);
      position: relative; overflow: hidden;
    }
    .login-brand-panel::before {
      content: ''; position: absolute;
      width: 300px; height: 300px; border-radius: 50%;
      border: 40px solid rgba(201,168,76,.07);
      bottom: -80px; right: -80px; pointer-events: none;
    }
    .login-brand-panel::after {
      content: ''; position: absolute;
      width: 160px; height: 160px; border-radius: 50%;
      border: 28px solid rgba(192,57,43,.10);
      top: -40px; left: -40px; pointer-events: none;
    }
    .brand-accent { display: flex; gap: 6px; align-items: center; margin-bottom: 20px; }
    .brand-accent span { display: block; height: 3px; border-radius: 2px; }
    .brand-accent .a1 { width: 32px; background: var(--gold); }
    .brand-accent .a2 { width: 12px; background: rgba(201,168,76,.4); }
    .brand-accent .a3 { width: 6px;  background: rgba(201,168,76,.2); }
    .brand-title { font-size: 20px; font-weight: 800; line-height: 1.35; margin-bottom: 12px; color: #fff; letter-spacing: -.02em; }
    .brand-desc  { font-size: 13.5px; color: rgba(255,255,255,.55); line-height: 1.65; margin-bottom: 28px; }
    .brand-features { list-style: none; padding: 0; margin: 0 0 36px; }
    .brand-features li {
      font-size: 13.5px; color: rgba(255,255,255,.8);
      padding: 7px 0 7px 22px; position: relative; line-height: 1.5;
      border-bottom: 1px solid rgba(255,255,255,.05);
    }
    .brand-features li:last-child { border-bottom: none; }
    .brand-features li::before {
      content: ''; position: absolute; left: 0; top: 14px;
      width: 8px; height: 8px; border-radius: 2px; background: var(--gold);
    }
    .brand-testimonial {
      background: rgba(255,255,255,.05);
      border: 1px solid rgba(255,255,255,.1);
      border-left: 3px solid var(--gold);
      border-radius: 4px; padding: 18px 20px;
      position: relative; z-index: 1;
    }
    .brand-stars { display: flex; gap: 3px; margin-bottom: 10px; }
    .brand-stars svg { fill: var(--gold); }
    .brand-testimonial blockquote { font-size: 12.5px; color: rgba(255,255,255,.75); line-height: 1.65; font-style: italic; margin: 0 0 10px; }
    .brand-testimonial cite { font-size: 11.5px; color: rgba(255,255,255,.4); font-style: normal; font-weight: 600; }

    @media (max-width: 768px) {
      .login-brand-panel { display: none; }
      .login-form-panel { padding: 40px 28px 36px; border-right: none; }
      .header-inner { padding: 0 20px; }
    }
  </style>
</head>
<body>
  <header class="login-header">
    <div class="header-inner">
      <a href="<?= BASE_URL ?>" class="header-brand">
        <?php if ($appLogo): ?>
          <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>">
        <?php else: ?>
          <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
               fill="none" stroke="#C9A84C" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <div class="header-brand-text">
            <span class="app-name"><?= APP_NAME ?></span>
            <span class="app-tagline">Manajemen Kegiatan</span>
          </div>
        <?php endif; ?>
      </a>
    </div>
  </header>

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-form-panel">
        <div class="form-ornament">
          <span class="line-red"></span>
          <span class="line-gold"></span>
        </div>
        <h1 class="form-title">Masuk ke Akun Anda</h1>
        <p class="form-subtitle">Silakan masukkan kredensial Anda untuk mengakses sistem <?= APP_NAME ?></p>

        <?php if ($error): ?>
        <div class="alert-login alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert-login alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="on">
          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" class="form-control-login"
                   placeholder="Masukkan username Anda"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autofocus>
          </div>
          <div class="form-group">
            <div class="label-row">
              <label for="pwd">Password</label>
              <a href="<?= BASE_URL ?>/forgot-password">Lupa password?</a>
            </div>
            <div class="pwd-wrap">
              <input type="password" id="pwd" name="password" class="form-control-login"
                     placeholder="Masukkan password Anda" required>
              <button type="button" class="pwd-toggle" onclick="togglePwd()" aria-label="Tampilkan password">
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="remember-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Ingat saya selama 30 hari</label>
          </div>
          <button type="submit" class="btn-login">Masuk ke Sistem</button>
          <div class="form-footer">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?>
          </div>
        </form>
      </div>

      <div class="login-brand-panel">
        <div>
          <div class="brand-accent">
            <span class="a1"></span><span class="a2"></span><span class="a3"></span>
          </div>
          <h2 class="brand-title">Kelola Kegiatan Tim Anda Secara Terstruktur</h2>
          <p class="brand-desc">Platform digital manajemen kegiatan terpadu &mdash; dari penjadwalan, notulen, hingga tindak lanjut.</p>
          <ul class="brand-features">
            <li>Notulen real-time tersinkron antar pengguna</li>
            <li>Tindak lanjut dengan deadline &amp; prioritas</li>
            <li>Kalender kegiatan interaktif terintegrasi</li>
            <li>Log aktivitas lengkap untuk audit trail</li>
            <li>Export notulen ke PDF &amp; DOCX</li>
          </ul>
        </div>
        <div class="brand-testimonial">
          <div class="brand-stars">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <svg width="14" height="14" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <?php endfor; ?>
          </div>
          <blockquote>&ldquo;Wicara membantu tim kami mencatat dan menindaklanjuti setiap kegiatan dengan lebih terstruktur dan efisien.&rdquo;</blockquote>
          <cite>&mdash; Tim <?= APP_NAME ?></cite>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePwd() {
      const input = document.getElementById('pwd');
      const icon  = document.getElementById('eye-icon');
      if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
      } else {
        input.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
      }
    }
  </script>
</body>
</html>