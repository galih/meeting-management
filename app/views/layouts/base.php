<?php
// base.php hanya dipakai saat user sudah login
if (!Auth::check()) {
    header('Location: ' . BASE_URL . '/login'); exit;
}
$user          = Auth::user();
$currentUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$unreadCount   = Notification::countUnread((int)$user['id']);
?>
<!doctype html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
  <meta name="description" content="Aplikasi Manajemen Meeting">
  <title><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?> &mdash; <?= APP_NAME ?></title>

  <!-- Tabler Core -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css"/>
  <!-- FullCalendar -->
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet"/>
  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css"/>

  <style>
    :root {
      --tblr-primary:        #f76707;
      --tblr-primary-rgb:    247, 103, 7;
      --tblr-link-color:     #f76707;
      --tblr-link-hover-color: #e05e00;
    }
    .btn-primary  { background-color: #f76707 !important; border-color: #f76707 !important; }
    .btn-primary:hover { background-color: #e05e00 !important; border-color: #e05e00 !important; }
    .bg-primary   { background-color: #f76707 !important; }
    .text-primary, .text-orange { color: #f76707 !important; }
    .border-primary { border-color: #f76707 !important; }
    .nav-link.active { color: #f76707 !important; border-color: #f76707 !important; }
    .navbar-brand  { text-decoration: none; }
    .badge.bg-orange { background-color: #f76707 !important; }
    .status-dot.bg-orange { background-color: #f76707 !important; }
    .navbar-vertical { scrollbar-width: thin; }
    .navbar-vertical .nav-link { transition: background .15s, color .15s; }
    .navbar-vertical .nav-link:hover { background: rgba(247,103,7,.08); }
  </style>
</head>
<body class="antialiased">
<div class="wrapper">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="page-wrapper">

    <!-- Top Navbar -->
    <header class="navbar navbar-expand-md d-none d-lg-flex d-print-none sticky-top bg-white border-bottom">
      <div class="container-xl">
        <div class="me-auto">
          <div class="page-pretitle text-muted small">MeetingApp</div>
          <h2 class="page-title mb-0"><?= htmlspecialchars($pageTitle ?? '') ?></h2>
        </div>
        <div class="navbar-nav flex-row align-items-center order-md-last gap-3">

          <!-- Notifikasi Bell -->
          <div class="nav-item dropdown">
            <a href="#" class="nav-link px-0 position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              <?php if ($unreadCount > 0): ?>
              <span id="notif-badge" class="badge bg-danger badge-notification"
                    style="position:absolute;top:0;right:0;font-size:9px;min-width:16px;">
                <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
              </span>
              <?php else: ?>
              <span id="notif-badge" class="badge bg-danger badge-notification"
                    style="position:absolute;top:0;right:0;font-size:9px;min-width:16px;display:none;"></span>
              <?php endif; ?>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-card" style="width:360px;">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title">Notifikasi</h3>
                  <div class="card-options">
                    <a href="#" id="mark-all-read" class="btn btn-sm btn-link" style="color:#f76707;">Baca Semua</a>
                  </div>
                </div>
                <div class="list-group list-group-flush" id="notif-list" style="max-height:360px;overflow-y:auto;">
                  <div class="list-group-item text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm"></div>
                  </div>
                </div>
                <div class="card-footer text-center py-2">
                  <a href="<?= BASE_URL ?>/notifications" style="color:#f76707;" class="small">Lihat semua notifikasi &rarr;</a>
                </div>
              </div>
            </div>
          </div>

          <!-- User Dropdown -->
          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex align-items-center lh-1 text-reset p-0" data-bs-toggle="dropdown">
              <span class="avatar avatar-sm me-2" style="background:#f76707;color:white;font-weight:700;">
                <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
              </span>
              <div class="d-none d-xl-block">
                <div class="fw-semibold" style="font-size:13px;"><?= htmlspecialchars($user['name']) ?></div>
                <div class="text-muted" style="font-size:11px;"><?= ucfirst($user['role']) ?></div>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end">
              <a href="<?= BASE_URL ?>/profile" class="dropdown-item">Profil Saya</a>
              <div class="dropdown-divider"></div>
              <a href="<?= BASE_URL ?>/logout" class="dropdown-item text-danger">Logout</a>
            </div>
          </div>

        </div>
      </div>
    </header>

    <!-- Page Body -->
    <div class="page-body">
      <div class="container-xl">
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
          <div class="col-12 col-lg-auto">
            <p class="text-muted mb-0">&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; PHP 8.5 &amp; Tabler</p>
          </div>
        </div>
      </div>
    </footer>

  </div><!-- .page-wrapper -->
</div><!-- .wrapper -->

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/editorjs@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/header@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/list@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/checklist@latest"></script>
<script src="https://cdn.jsdelivr.net/npm/@editorjs/table@latest"></script>
<!-- Inject BASE_URL ke JS global -->
<script>const BASE_URL = '<?= BASE_URL ?>';</script>
<script src="<?= BASE_URL ?>/assets/js/notifications.js"></script>
<?= $scripts ?? '' ?>
</body>
</html>
