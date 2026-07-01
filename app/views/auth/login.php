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
  <title>Sign In &mdash; <?= APP_NAME ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green      : #3A5A2C;
      --green-dark : #2C4520;
      --green-light: rgba(58,90,44,.08);
      --gray-100   : #F5F5F5;
      --gray-200   : #E8E8E8;
      --gray-400   : #ACACAC;
      --gray-600   : #666666;
      --gray-900   : #111111;
      --blue-link  : #2563EB;
      --danger     : #DC2626;
      --success    : #16A34A;
    }

    html, body { height: 100%; }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
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
    }

    .auth-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 48px;
      text-decoration: none;
    }
    .auth-brand img   { height: 32px; object-fit: contain; }
    .auth-brand-name  { font-size: 15px; font-weight: 700; color: var(--gray-900); letter-spacing: -.02em; }

    .auth-heading {
      font-size: 30px;
      font-weight: 700;
      color: var(--gray-900);
      letter-spacing: -.03em;
      margin-bottom: 32px;
    }

    /* Alerts */
    .auth-alert {
      padding: 11px 14px;
      border-radius: 8px;
      font-size: 13.5px;
      font-weight: 500;
      margin-bottom: 20px;
    }
    .auth-alert-danger  { background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA; }
    .auth-alert-success { background: #F0FDF4; color: #166534; border: 1px solid #BBF7D0; }

    /* Form */
    .auth-field { margin-bottom: 20px; }
    .auth-label {
      display: block;
      font-size: 13.5px;
      font-weight: 500;
      color: var(--gray-900);
      margin-bottom: 7px;
    }
    .auth-input {
      width: 100%;
      height: 46px;
      border: 1.5px solid var(--gray-200);
      border-radius: 8px;
      padding: 0 14px;
      font-size: 14px;
      font-family: inherit;
      color: var(--gray-900);
      background: #fff;
      outline: none;
      transition: border-color .15s, box-shadow .15s;
    }
    .auth-input::placeholder { color: var(--gray-400); }
    .auth-input:focus {
      border-color: var(--green);
      box-shadow: 0 0 0 3px rgba(58,90,44,.10);
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
    .pwd-toggle:hover { color: var(--gray-600); }

    .auth-forgot {
      display: block;
      text-align: right;
      font-size: 12.5px;
      color: var(--blue-link);
      text-decoration: none;
      margin-top: -14px;
      margin-bottom: 20px;
    }
    .auth-forgot:hover { text-decoration: underline; }

    .auth-remember {
      display: flex;
      align-items: center;
      gap: 9px;
      margin-bottom: 26px;
    }
    .auth-remember input[type=checkbox] {
      width: 16px; height: 16px;
      accent-color: var(--green);
      cursor: pointer;
      border-radius: 4px;
    }
    .auth-remember label {
      font-size: 13px;
      color: var(--gray-600);
      cursor: pointer;
    }

    .auth-btn {
      width: 100%;
      height: 48px;
      background: var(--green);
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 15px;
      font-weight: 600;
      font-family: inherit;
      cursor: pointer;
      transition: background .15s, box-shadow .15s;
      letter-spacing: -.01em;
    }
    .auth-btn:hover  { background: var(--green-dark); box-shadow: 0 4px 14px rgba(58,90,44,.25); }
    .auth-btn:active { transform: translateY(1px); }

    .auth-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 24px 0;
      font-size: 13px;
      color: var(--gray-400);
    }
    .auth-divider::before,
    .auth-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--gray-200);
    }

    .auth-footer {
      margin-top: 28px;
      font-size: 13.5px;
      color: var(--gray-600);
      text-align: center;
    }
    .auth-footer a { color: var(--blue-link); font-weight: 600; text-decoration: none; }
    .auth-footer a:hover { text-decoration: underline; }

    .auth-copyright {
      margin-top: 48px;
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
    <?php if ($loginBg): ?>
    .auth-right { background: url('<?= htmlspecialchars($loginBg, ENT_QUOTES) ?>') center/cover no-repeat; }
    <?php endif; ?>

    /* ── RESPONSIVE ── */
    @media (max-width: 900px) {
      .auth-right { display: none; }
      .auth-left  { max-width: 100%; padding: 48px 28px; }
    }
    @media (max-width: 480px) {
      .auth-left    { padding: 36px 20px; }
      .auth-heading { font-size: 24px; }
    }
  </style>
</head>
<body>

  <!-- LEFT: Form -->
  <div class="auth-left">

    <!-- Brand / Logo -->
    <a href="<?= BASE_URL ?>" class="auth-brand">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>">
      <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
             fill="none" stroke="<?= htmlspecialchars('#3A5A2C') ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
      <?php endif; ?>
      <span class="auth-brand-name"><?= APP_NAME ?></span>
    </a>

    <h1 class="auth-heading">Get Started Now</h1>

    <?php if ($error): ?>
    <div class="auth-alert auth-alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="auth-alert auth-alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="on">

      <div class="auth-field">
        <label class="auth-label" for="username">Name</label>
        <input
          type="text" id="username" name="username"
          class="auth-input"
          placeholder="Enter your name"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          required autofocus>
      </div>

      <div class="auth-field">
        <label class="auth-label" for="email_addr">Email address</label>
        <input
          type="text" id="email_addr" name="email"
          class="auth-input"
          placeholder="Enter your email"
          autocomplete="username">
      </div>

      <div class="auth-field">
        <label class="auth-label" for="pwd">Password</label>
        <div class="pwd-wrap">
          <input
            type="password" id="pwd" name="password"
            class="auth-input"
            placeholder="Name"
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

      <div class="auth-remember">
        <input type="checkbox" id="terms" name="terms">
        <label for="terms">I agree to the <a href="#" style="color:var(--blue-link);text-underline-offset:2px;">terms &amp; policy</a></label>
      </div>

      <button type="submit" class="auth-btn">Signup</button>

      <div class="auth-divider">Or</div>

      <div style="display:flex;gap:12px;">
        <button type="button" style="flex:1;height:44px;border:1.5px solid var(--gray-200);border-radius:8px;background:#fff;font-size:13.5px;font-weight:500;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;color:var(--gray-900);transition:border-color .14s;" onmouseover="this.style.borderColor='#aaa'" onmouseout="this.style.borderColor='var(--gray-200)'">
          <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.6 20.1H42V20H24v8h11.3C33.7 32.7 29.3 36 24 36c-6.6 0-12-5.4-12-12s5.4-12 12-12c3.1 0 5.9 1.1 8 3l5.7-5.7C34.5 6.5 29.5 4 24 4 12.9 4 4 12.9 4 24s8.9 20 20 20 20-8.9 20-20c0-1.3-.1-2.6-.4-3.9z"/><path fill="#FF3D00" d="M6.3 14.7l6.6 4.8C14.5 15.9 18.9 12 24 12c3.1 0 5.9 1.1 8 3l5.7-5.7C34.5 6.5 29.5 4 24 4 16.3 4 9.7 8.3 6.3 14.7z"/><path fill="#4CAF50" d="M24 44c5.2 0 9.9-2 13.4-5.2l-6.2-5.2c-2 1.4-4.5 2.4-7.2 2.4-5.2 0-9.6-3.4-11.2-8H6.3C9.7 39.6 16.3 44 24 44z"/><path fill="#1976D2" d="M43.6 20.1H42V20H24v8h11.3c-.8 2.2-2.2 4.1-4 5.4l6.2 5.2C40.5 35.4 44 30.1 44 24c0-1.3-.1-2.6-.4-3.9z"/></svg>
          Sign in with Google
        </button>
        <button type="button" style="flex:1;height:44px;border:1.5px solid var(--gray-200);border-radius:8px;background:#fff;font-size:13.5px;font-weight:500;font-family:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;color:var(--gray-900);transition:border-color .14s;" onmouseover="this.style.borderColor='#aaa'" onmouseout="this.style.borderColor='var(--gray-200)'">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/></svg>
          Sign in with Apple
        </button>
      </div>

      <div class="auth-footer">
        Have an account? <a href="<?= BASE_URL ?>/login">Sign In</a>
      </div>

    </form>

    <div class="auth-copyright">&copy; <?= date('Y') ?> <?= APP_NAME ?></div>
  </div>

  <!-- RIGHT: Photo -->
  <div class="auth-right">
    <?php if (!$loginBg): ?>
    <img
      src="https://images.unsplash.com/photo-1512428813834-c702c7702b78?w=1200&q=80"
      alt=""
      class="auth-right-img">
    <?php endif; ?>
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
