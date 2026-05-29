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
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; }

    body {
      min-height: 100vh;
      margin: 0;
      background: #eef0f5;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      display: flex;
      flex-direction: column;
    }

    /* ── Top bar logo ── */
    .login-topbar {
      padding: 20px 32px;
    }
    .login-topbar .brand {
      font-size: 18px;
      font-weight: 700;
      color: #1a1a2e;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .login-topbar .brand img {
      max-height: 36px;
      object-fit: contain;
    }

    /* ── Wrapper tengah ── */
    .login-wrapper {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px 48px;
    }

    /* ── Card utama ── */
    .login-card {
      display: flex;
      width: 100%;
      max-width: 900px;
      background: #fff;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 4px 32px rgba(0,0,0,.08);
    }

    /* ── Panel kiri: form ── */
    .login-form-panel {
      flex: 1;
      padding: 52px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .login-form-panel h1 {
      font-size: 26px;
      font-weight: 700;
      color: #111;
      margin: 0 0 6px;
    }
    .login-form-panel .subtitle {
      font-size: 14px;
      color: #6b7280;
      margin: 0 0 32px;
    }

    /* Form elements */
    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #374151;
      margin-bottom: 6px;
    }
    .form-group .label-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 6px;
    }
    .form-group .label-row a {
      font-size: 12px;
      color: #f76707;
      text-decoration: none;
    }
    .form-group .label-row a:hover { text-decoration: underline; }
    .form-control-login {
      width: 100%;
      height: 44px;
      border: 1.5px solid #e5e7eb;
      border-radius: 8px;
      padding: 0 14px;
      font-size: 14px;
      color: #111;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
      background: #fff;
    }
    .form-control-login::placeholder { color: #9ca3af; }
    .form-control-login:focus {
      border-color: #f76707;
      box-shadow: 0 0 0 3px rgba(247,103,7,.12);
    }
    .pwd-wrap {
      position: relative;
    }
    .pwd-wrap .form-control-login { padding-right: 44px; }
    .pwd-toggle {
      position: absolute;
      right: 12px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #9ca3af;
      padding: 0;
      display: flex;
      align-items: center;
    }
    .pwd-toggle:hover { color: #374151; }

    .remember-row {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 24px;
    }
    .remember-row input[type=checkbox] {
      width: 16px;
      height: 16px;
      accent-color: #f76707;
      cursor: pointer;
    }
    .remember-row label {
      font-size: 13px;
      color: #6b7280;
      cursor: pointer;
      margin: 0;
    }

    .btn-login {
      width: 100%;
      height: 46px;
      background: #f76707;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      transition: background .2s, transform .1s;
      letter-spacing: .01em;
    }
    .btn-login:hover { background: #e05e00; }
    .btn-login:active { transform: scale(.99); }

    .form-footer {
      margin-top: 20px;
      font-size: 12px;
      color: #9ca3af;
      text-align: center;
      line-height: 1.6;
    }

    /* Alert */
    .alert-login {
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .alert-danger  { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    /* ── Panel kanan: branding ── */
    .login-brand-panel {
      width: 340px;
      flex-shrink: 0;
      background: linear-gradient(145deg, #1a2744 0%, #0f1b38 60%, #0a1628 100%);
      padding: 52px 40px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      color: #fff;
    }
    .brand-content .brand-title {
      font-size: 22px;
      font-weight: 700;
      line-height: 1.3;
      margin: 0 0 14px;
      color: #fff;
    }
    .brand-content .brand-desc {
      font-size: 14px;
      color: rgba(255,255,255,.65);
      line-height: 1.6;
      margin: 0 0 28px;
    }
    .brand-features {
      list-style: none;
      padding: 0;
      margin: 0 0 40px;
    }
    .brand-features li {
      font-size: 14px;
      color: rgba(255,255,255,.85);
      padding: 6px 0;
      padding-left: 20px;
      position: relative;
      line-height: 1.5;
    }
    .brand-features li::before {
      content: '';
      position: absolute;
      left: 0;
      top: 13px;
      width: 7px;
      height: 7px;
      border-radius: 50%;
      background: #f76707;
    }

    /* Testimoni */
    .brand-testimonial {
      background: rgba(255,255,255,.07);
      border-radius: 12px;
      padding: 20px;
    }
    .brand-stars {
      display: flex;
      gap: 3px;
      margin-bottom: 12px;
    }
    .brand-stars svg { color: #fbbf24; }
    .brand-testimonial blockquote {
      margin: 0 0 12px;
      font-size: 13px;
      color: rgba(255,255,255,.8);
      line-height: 1.6;
      font-style: italic;
    }
    .brand-testimonial cite {
      font-size: 12px;
      color: rgba(255,255,255,.5);
      font-style: normal;
    }

    /* ── Responsive ── */
    @media (max-width: 720px) {
      .login-brand-panel { display: none; }
      .login-form-panel { padding: 40px 28px; }
      .login-topbar { padding: 16px 20px; }
    }
  </style>
</head>
<body>

  <!-- Top bar logo -->
  <div class="login-topbar">
    <a href="<?= BASE_URL ?>" class="brand">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>">
      <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
             fill="none" stroke="#f76707" stroke-width="2.2">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span><?= APP_NAME ?></span>
      <?php endif; ?>
    </a>
  </div>

  <!-- Main wrapper -->
  <div class="login-wrapper">
    <div class="login-card">

      <!-- Kiri: Form -->
      <div class="login-form-panel">
        <h1>Masuk ke akun Anda</h1>
        <p class="subtitle">Selamat datang kembali di <?= APP_NAME ?></p>

        <?php if ($error): ?>
        <div class="alert-login alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert-login alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="on">

          <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   class="form-control-login"
                   placeholder="Masukkan username"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   required autofocus>
          </div>

          <div class="form-group">
            <div class="label-row">
              <label for="pwd">Password</label>
              <a href="<?= BASE_URL ?>/forgot-password">Lupa password?</a>
            </div>
            <div class="pwd-wrap">
              <input type="password" id="pwd" name="password"
                     class="form-control-login"
                     placeholder="Masukkan password"
                     required>
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

          <button type="submit" class="btn-login">Masuk</button>

          <div class="form-footer">
            &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?>
          </div>
        </form>
      </div>

      <!-- Kanan: Branding -->
      <div class="login-brand-panel">
        <div class="brand-content">
          <h2 class="brand-title">Kelola kegiatan tim Anda dengan mudah dan terstruktur</h2>
          <p class="brand-desc">Platform manajemen kegiatan terpadu untuk mencatat, mendokumentasikan, dan menindaklanjuti setiap rapat.</p>
          <ul class="brand-features">
            <li>Notulen real-time yang tersinkron otomatis</li>
            <li>Tindak lanjut dengan deadline & prioritas</li>
            <li>Kalender kegiatan interaktif terintegrasi</li>
            <li>Log aktivitas lengkap untuk audit trail</li>
            <li>Export notulen ke PDF & DOCX</li>
          </ul>
        </div>

        <div class="brand-testimonial">
          <div class="brand-stars">
            <?php for ($i = 0; $i < 5; $i++): ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <?php endfor; ?>
          </div>
          <blockquote>
            &ldquo;Wicara membantu tim kami mencatat dan menindaklanjuti setiap kegiatan dengan lebih terstruktur dan efisien.&rdquo;
          </blockquote>
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
        icon.innerHTML = `
          <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/>
          <line x1="1" y1="1" x2="23" y2="23"/>`;
      } else {
        input.type = 'password';
        icon.innerHTML = `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
          <circle cx="12" cy="12" r="3"/>`;
      }
    }
  </script>
</body>
</html>
