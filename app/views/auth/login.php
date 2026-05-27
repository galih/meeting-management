<?php
if (Auth::check()) { header('Location: ' . BASE_URL . '/dashboard'); exit; }
$error   = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Login &mdash; <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css"/>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css"/>
</head>
<body class="d-flex flex-column">
<div class="page page-center">
  <div class="container container-tight py-4">

    <!-- Logo -->
    <div class="text-center mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24"
           fill="none" stroke="#f76707" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8"  y1="2" x2="8"  y2="6"/>
        <line x1="3"  y1="10" x2="21" y2="10"/>
      </svg>
      <h1 class="mt-2 mb-0 fw-bold" style="color:#f76707;font-size:24px;"><?= APP_NAME ?></h1>
      <p class="text-muted small">Manajemen Meeting Profesional</p>
    </div>

    <div class="card card-md shadow-sm">
      <div class="card-body">
        <h2 class="h3 text-center mb-4">Masuk ke Akun Anda</h2>

        <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon alert-icon" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/>
            <line x1="12" y1="16" x2="12.01" y2="16"/>
          </svg>
          <?= htmlspecialchars($error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" autocomplete="on">
          <div class="mb-3">
            <label class="form-label required">Username</label>
            <input type="text" name="username" class="form-control" required
                   placeholder="Masukkan username" autofocus
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
          <div class="mb-2">
            <label class="form-label">
              Password
              <span class="form-label-description">
                <a href="<?= BASE_URL ?>/forgot-password" style="color:#f76707;">Lupa password?</a>
              </span>
            </label>
            <div class="input-group input-group-flat">
              <input type="password" name="password" id="pwd"
                     class="form-control" required placeholder="Masukkan password">
              <span class="input-group-text" style="cursor:pointer;" onclick="togglePwd()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </span>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-check">
              <input type="checkbox" name="remember" class="form-check-input">
              <span class="form-check-label">Ingat saya selama 30 hari</span>
            </label>
          </div>
          <button type="submit" class="btn btn-primary w-100">Masuk</button>
        </form>
      </div>
    </div>

    <div class="text-center text-muted mt-3 small">
      &copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; PHP 8.5 &amp; Tabler
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
<script>
function togglePwd() {
  const i = document.getElementById('pwd');
  i.type  = i.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
