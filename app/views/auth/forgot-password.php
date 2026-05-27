<?php
$success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <title>Lupa Password &mdash; <?= APP_NAME ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css"/>
  <link rel="stylesheet" href="/assets/css/custom.css"/>
</head>
<body class="d-flex flex-column">
<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
           fill="none" stroke="#f76707" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <h1 class="mt-2 mb-0 fw-bold" style="color:#f76707;font-size:22px;"><?= APP_NAME ?></h1>
    </div>
    <div class="card card-md shadow-sm">
      <div class="card-body">
        <h2 class="h3 text-center mb-2">Lupa Password</h2>
        <p class="text-muted text-center mb-4 small">Masukkan email Anda, kami akan mengirimkan link reset.</p>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" action="/forgot-password">
          <div class="mb-3">
            <label class="form-label required">Alamat Email</label>
            <input type="email" name="email" class="form-control" required
                   placeholder="email@domain.com" autofocus>
          </div>
          <button type="submit" class="btn btn-primary w-100">Kirim Link Reset</button>
          <div class="text-center mt-3">
            <a href="/login" style="color:#f76707;">Kembali ke Login</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
