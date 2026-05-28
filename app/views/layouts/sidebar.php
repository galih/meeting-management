<?php
$user    = Auth::user();
$baseUrl = rtrim(BASE_URL, '/');

$scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$fullUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentUri = ($scriptDir !== '' && str_starts_with($fullUri, $scriptDir))
    ? substr($fullUri, strlen($scriptDir))
    : $fullUri;
$currentUri = $currentUri ?: '/';

function isActive(string $path, string $current): string {
    if ($path === '/') return $current === '/' ? 'active' : '';
    return str_starts_with($current, $path) ? 'active' : '';
}

$appLogo = SettingController::get('app_logo');
?>
<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="light">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Brand -->
    <a href="<?= $baseUrl ?>/" class="navbar-brand navbar-brand-autodark d-flex align-items-center gap-2">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>" style="height:32px;width:auto;object-fit:contain;">
      <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24"
             fill="none" stroke="#f76707" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span><?= htmlspecialchars(APP_NAME) ?></span>
      <?php endif; ?>
    </a>

    <div class="collapse navbar-collapse" id="sidebar-menu">
      <ul class="navbar-nav pt-2">

        <li class="nav-item">
          <a class="nav-link <?= isActive('/', $currentUri) ?>" href="<?= $baseUrl ?>/">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </span>
            <span class="nav-link-title">Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/meetings', $currentUri) ?>" href="<?= $baseUrl ?>/meetings">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
            </span>
            <span class="nav-link-title">Kalender Meeting</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/tindak-lanjut', $currentUri) ?>" href="<?= $baseUrl ?>/tindak-lanjut">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 11 12 14 22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
              </svg>
            </span>
            <span class="nav-link-title">Tindak Lanjut</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/notifications', $currentUri) ?>" href="<?= $baseUrl ?>/notifications">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
            </span>
            <span class="nav-link-title">Notifikasi</span>
          </a>
        </li>

        <?php if (Auth::isAdmin()): ?>
        <li class="nav-item mt-3">
          <span class="sidebar-label">Administrasi</span>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/users', $currentUri) ?>" href="<?= $baseUrl ?>/users">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
            </span>
            <span class="nav-link-title">Manajemen User</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/departments', $currentUri) ?>" href="<?= $baseUrl ?>/departments">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="2" y="7" width="20" height="14" rx="2"/>
                <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
              </svg>
            </span>
            <span class="nav-link-title">Departemen</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/recurring', $currentUri) ?>" href="<?= $baseUrl ?>/recurring">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="17 1 21 5 17 9"/>
                <path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                <polyline points="7 23 3 19 7 15"/>
                <path d="M21 13v2a4 4 0 0 1-4 4H3"/>
              </svg>
            </span>
            <span class="nav-link-title">Recurring Meeting</span>
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/settings', $currentUri) ?>" href="<?= $baseUrl ?>/settings">
            <span class="nav-link-icon d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
              </svg>
            </span>
            <span class="nav-link-title">Pengaturan</span>
          </a>
        </li>
        <?php endif; ?>

      </ul>

      <!-- User footer di bawah sidebar -->
      <div class="sidebar-user-footer mt-auto">
        <div class="avatar">
          <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="flex-fill overflow-hidden">
          <div class="user-name text-truncate"><?= htmlspecialchars($user['name'] ?? '') ?></div>
          <div class="user-role"><?= ucfirst($user['role'] ?? '') ?></div>
        </div>
        <a href="<?= $baseUrl ?>/logout" class="btn btn-ghost-danger btn-icon btn-sm" title="Logout">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </a>
      </div>
    </div>
  </div>
</aside>
