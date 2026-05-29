<?php
$appLogo = SettingController::get('app_logo');
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Reset Password &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --red: #C0392B; --red-dark: #3D0A0A; --red-light: #FADBD8;
      --gold: #C9A84C; --white: #ffffff;
      --gray-300: #DEE2E6; --gray-500: #868E96; --gray-700: #495057; --gray-900: #212529;
    }
    body {
      min-height: 100vh;
      font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
      background: #f8f4ee;
      background-image: radial-gradient(circle, rgba(192,57,43,.06) 1px, transparent 1px);
      background-size: 28px 28px;
      display: flex; flex-direction: column;
    }
    /* HEADER */
    .login-header {
      display: flex; align-items: stretch; height: 64px;
      background: #3D0A0A;
      box-shadow: 0 2px 8px rgba(61,10,10,.3);
    }
    .login-header::before {
      content: ''; display: block; width: 6px;
      background: var(--red); flex-shrink: 0;
    }
    .header-inner { flex: 1; display: flex; align-items: center; padding: 0 32px; }
    .header-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; }
    .header-brand img { max-height: 40px; object-fit: contain; }
    .app-name { font-size: 16px; font-weight: 800; color: #fff; letter-spacing: -.02em; line-height: 1.1; }
    .app-tagline { font-size: 10.5px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .06em; }
    /* WRAPPER */
    .auth-wrapper {
      flex: 1; display: flex; align-items: center; justify-content: center;
      padding: 48px 16px 64px;
    }
    /* CARD */
    .auth-card {
      width: 100%; max-width: 440px;
      background: var(--white);
      border-radius: 4px;
      border-top: 3px solid var(--gold);
      box-shadow: 0 2px 24px rgba(0,0,0,.10);
      padding: 44px 44px 36px;
    }
    .form-ornament { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .form-ornament .line-red  { width: 36px; height: 3px; background: var(--red); border-radius: 2px; }
    .form-ornament .line-gold { width: 12px; height: 3px; background: var(--gold); border-radius: 2px; }
    .form-title {
      font-size: 22px; font-weight: 800;
      color: #3D0A0A; letter-spacing: -.03em; margin-bottom: 6px;
    }
    .form-subtitle {
      font-size: 13px; color: var(--gray-500);
      margin-bottom: 28px; line-height: 1.5;
    }
    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block; font-size: 12.5px; font-weight: 700;
      color: var(--gray-700); margin-bottom: 7px;
      text-transform: uppercase; letter-spacing: .05em;
    }
    .pwd-wrap { position: relative; }
    .form-control-auth {
      width: 100%; height: 46px;
      border: 1.5px solid var(--gray-300); border-radius: 4px;
      padding: 0 46px 0 14px; font-size: 14px; font-family: inherit;
      color: var(--gray-900); outline: none;
      background: #faf7f3;
      transition: border-color .2s, box-shadow .2s;
    }
    .form-control-auth::placeholder { color: #ADB5BD; }
    .form-control-auth:focus {
      border-color: var(--red); background: var(--white);
      box-shadow: 0 0 0 3px rgba(192,57,43,.10);
    }
    .pwd-toggle {
      position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: #ADB5BD; padding: 0; display: flex; align-items: center;
    }
    .pwd-toggle:hover { color: var(--gray-700); }
    /* Strength bar */
    .pwd-strength { margin-top: 6px; height: 4px; border-radius: 2px; background: var(--gray-300); overflow: hidden; }
    .pwd-strength-bar { height: 100%; width: 0; border-radius: 2px; transition: width .3s, background .3s; }
    .pwd-hint { font-size: 11.5px; color: var(--gray-500); margin-top: 5px; }
    .btn-auth {
      width: 100%; height: 48px;
      background: #3D0A0A; color: var(--white);
      border: none; border-radius: 4px;
      font-size: 14px; font-weight: 700; font-family: inherit;
      cursor: pointer; letter-spacing: .04em; text-transform: uppercase;
      transition: background .2s, box-shadow .2s;
      margin-bottom: 18px;
    }
    .btn-auth:hover { background: #5a1010; box-shadow: 0 4px 16px rgba(61,10,10,.25); }
    .btn-auth:active { transform: translateY(1px); }
    .alert-auth {
      padding: 11px 14px; border-radius: 4px;
      font-size: 13px; margin-bottom: 22px; font-weight: 500;
    }
    .alert-danger  { background: var(--red-light); color: #7B241C; border-left: 3px solid var(--red); }
    .alert-success { background: #D5F5E3; color: #1D6A3A; border-left: 3px solid #27AE60; }
    .form-footer { margin-top: 28px; font-size: 11.5px; color: var(--gray-500); text-align: center; }
    @media (max-width: 480px) {
      .auth-card { padding: 32px 24px 28px; }
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
          <div>
            <div class="app-name"><?= APP_NAME ?></div>
            <div class="app-tagline">Manajemen Kegiatan</div>
          </div>
        <?php endif; ?>
      </a>
    </div>
  </header>

  <div class="auth-wrapper">
    <div class="auth-card">
      <div class="form-ornament">
        <span class="line-red"></span>
        <span class="line-gold"></span>
      </div>
      <h1 class="form-title">Buat Password Baru</h1>
      <p class="form-subtitle">Masukkan password baru untuk akun: <strong><?= htmlspecialchars($reset['email']) ?></strong></p>

      <?php if (!empty($error)): ?>
      <div class="alert-auth alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>/reset-password?token=<?= urlencode($token) ?>">
        <div class="form-group">
          <label for="pwd1">Password Baru</label>
          <div class="pwd-wrap">
            <input type="password" id="pwd1" name="password" class="form-control-auth"
                   placeholder="Minimal 8 karakter" minlength="8" required autofocus
                   oninput="checkStrength(this.value)">
            <button type="button" class="pwd-toggle" onclick="togglePwd('pwd1','eye1')" aria-label="Tampilkan">
              <svg id="eye1" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
          <div class="pwd-strength"><div class="pwd-strength-bar" id="strength-bar"></div></div>
          <div class="pwd-hint" id="strength-label">Minimal 8 karakter</div>
        </div>
        <div class="form-group">
          <label for="pwd2">Konfirmasi Password</label>
          <div class="pwd-wrap">
            <input type="password" id="pwd2" name="password_confirm" class="form-control-auth"
                   placeholder="Ulangi password baru" minlength="8" required>
            <button type="button" class="pwd-toggle" onclick="togglePwd('pwd2','eye2')" aria-label="Tampilkan">
              <svg id="eye2" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                <circle cx="12" cy="12" r="3"/>
              </svg>
            </button>
          </div>
        </div>
        <button type="submit" class="btn-auth">Simpan Password Baru</button>
      </form>

      <div class="form-footer">
        &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?>
      </div>
    </div>
  </div>

  <script>
    function togglePwd(inputId, iconId) {
      const input = document.getElementById(inputId);
      const icon  = document.getElementById(iconId);
      if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
      } else {
        input.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
      }
    }
    function checkStrength(val) {
      const bar   = document.getElementById('strength-bar');
      const label = document.getElementById('strength-label');
      let score = 0;
      if (val.length >= 8)  score++;
      if (val.length >= 12) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      const levels = [
        { pct: '0%',   color: '#DEE2E6', text: 'Minimal 8 karakter' },
        { pct: '25%',  color: '#E74C3C', text: 'Lemah' },
        { pct: '50%',  color: '#E67E22', text: 'Cukup' },
        { pct: '75%',  color: '#F1C40F', text: 'Kuat' },
        { pct: '90%',  color: '#27AE60', text: 'Sangat kuat' },
        { pct: '100%', color: '#1ABC9C', text: 'Sangat kuat sekali' },
      ];
      const l = levels[score] || levels[0];
      bar.style.width = l.pct;
      bar.style.background = l.color;
      label.textContent = l.text;
      label.style.color = score >= 3 ? l.color : '#868E96';
    }
  </script>
</body>
</html>