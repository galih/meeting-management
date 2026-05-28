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

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/notifications.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
