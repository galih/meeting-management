<?php
$user    = Auth::user();
$baseUrl = rtrim(BASE_URL, '/');

$scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$fullUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// PHP 7.4 compat: ganti str_starts_with dengan strncmp
$currentUri = ($scriptDir !== '' && strncmp($fullUri, $scriptDir, strlen($scriptDir)) === 0)
    ? substr($fullUri, strlen($scriptDir))
    : $fullUri;
$currentUri = $currentUri ?: '/';

function isActive(string $path, string $current): string {
    if ($path === '/') return $current === '/' ? 'active' : '';
    // PHP 7.4 compat: ganti str_starts_with dengan strncmp
    return strncmp($current, $path, strlen($path)) === 0 ? 'active' : '';
}

$appLogo = SettingController::get('app_logo');
?>
<header class="navbar navbar-expand-lg topnav sticky-top">
  <div class="container-xl">

    <!-- Brand -->
    <a href="<?= $baseUrl ?>/" class="navbar-brand me-4">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="<?= APP_NAME ?>" style="height:30px;width:auto;object-fit:contain;">
      <?php else: ?>
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             class="text-gold me-1" style="stroke:var(--gold);">
          <rect x="3" y="4" width="18" height="18" rx="2"/>
          <line x1="16" y1="2" x2="16" y2="6"/>
          <line x1="8" y1="2" x2="8" y2="6"/>
          <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span class="navbar-brand-text"><?= htmlspecialchars(APP_NAME) ?></span>
      <?php endif; ?>
    </a>

    <!-- Hamburger mobile -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Menu utama -->
    <div class="collapse navbar-collapse" id="topnav-menu">
      <ul class="navbar-nav me-auto">

        <li class="nav-item">
          <a class="nav-link <?= isActive('/', $currentUri) ?>" href="<?= $baseUrl ?>/">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
              <polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Dashboard
          </a>
        </li>

        <!-- Dokumen: tampil untuk semua user yang login -->
        <li class="nav-item">
          <a class="nav-link <?= isActive('/dokumen', $currentUri) ?>" href="<?= $baseUrl ?>/dokumen">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            Dokumen
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/meetings', $currentUri) ?>" href="<?= $baseUrl ?>/meetings">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/>
              <line x1="16" y1="2" x2="16" y2="6"/>
              <line x1="8" y1="2" x2="8" y2="6"/>
              <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Kegiatan
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link <?= isActive('/tindak-lanjut', $currentUri) ?>" href="<?= $baseUrl ?>/tindak-lanjut">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="9 11 12 14 22 4"/>
              <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Tindak Lanjut
          </a>
        </li>

        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= (isActive('/users',$currentUri) || isActive('/departments',$currentUri) || isActive('/recurring',$currentUri) || isActive('/notulen-templates',$currentUri) || isActive('/settings',$currentUri) || isActive('/admin/activity-log',$currentUri)) ? 'active' : '' ?>"
             href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="3"/>
              <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            Administrasi
          </a>
          <ul class="dropdown-menu">

            <?php if (Auth::isAdmin()): ?>
            <li>
              <a class="dropdown-item <?= isActive('/users',$currentUri) ?>" href="<?= $baseUrl ?>/users">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Manajemen User
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= isActive('/departments',$currentUri) ?>" href="<?= $baseUrl ?>/departments">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="2" y="3" width="20" height="14" rx="2"/>
                  <line x1="8" y1="21" x2="16" y2="21"/>
                  <line x1="12" y1="17" x2="12" y2="21"/>
                </svg>
                Unit Kerja
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= isActive('/recurring',$currentUri) ?>" href="<?= $baseUrl ?>/recurring">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/>
                  <polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/>
                </svg>
                Recurring Kegiatan
              </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <?php endif; ?>

            <li>
              <a class="dropdown-item <?= isActive('/notulen-templates',$currentUri) ?>" href="<?= $baseUrl ?>/notulen-templates">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                </svg>
                Template Notulen
              </a>
            </li>

            <?php if (Auth::isAdmin()): ?>
            <li><hr class="dropdown-divider"></li>
            <li>
              <a class="dropdown-item <?= isActive('/admin/activity-log',$currentUri) ?>" href="<?= $baseUrl ?>/admin/activity-log">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                  <polyline points="14 2 14 8 20 8"/>
                  <line x1="16" y1="13" x2="8" y2="13"/>
                  <line x1="16" y1="17" x2="8" y2="17"/>
                  <polyline points="10 9 9 9 8 9"/>
                </svg>
                Log Aktivitas
              </a>
            </li>
            <li>
              <a class="dropdown-item <?= isActive('/settings',$currentUri) ?>" href="<?= $baseUrl ?>/settings">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3"/>
                  <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Pengaturan
              </a>
            </li>
            <?php endif; ?>

          </ul>
        </li>
        <?php endif; ?>

      </ul>

      <!-- Kanan: Notif bell + User dropdown -->
      <div class="navbar-nav flex-row align-items-center gap-2 ms-auto">

        <!-- Bell notifikasi -->
        <div class="nav-item dropdown">
          <a href="#" class="nav-link px-2 position-relative" data-bs-toggle="dropdown" aria-label="Notifikasi">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <?php $unreadCount = $user ? \Notification::countUnread((int)$user['id']) : 0; ?>
            <?php if ($unreadCount > 0): ?>
            <span id="notif-badge" class="badge bg-danger badge-notification"
                  style="position:absolute;top:2px;right:2px;font-size:9px;min-width:16px;">
              <?= $unreadCount > 9 ? '9+' : $unreadCount ?>
            </span>
            <?php else: ?>
            <span id="notif-badge" class="badge bg-danger badge-notification"
                  style="position:absolute;top:2px;right:2px;font-size:9px;min-width:16px;display:none;"></span>
            <?php endif; ?>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-card" style="width:360px;">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Notifikasi</h3>
                <div class="card-options">
                  <a href="#" id="mark-all-read" class="btn btn-sm btn-link">Baca Semua</a>
                </div>
              </div>
              <div class="list-group list-group-flush" id="notif-list" style="max-height:360px;overflow-y:auto;">
                <div class="list-group-item text-center text-muted py-4">
                  <div class="spinner-border spinner-border-sm"></div>
                </div>
              </div>
              <div class="card-footer text-center py-2">
                <a href="<?= $baseUrl ?>/notifications" class="small">Lihat semua notifikasi &rarr;</a>
              </div>
            </div>
          </div>
        </div>

        <!-- User dropdown -->
        <div class="nav-item dropdown">
          <a href="#" class="nav-link d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
            <span class="topnav-avatar">
              <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
            </span>
            <span class="d-none d-xl-block" style="font-size:13px;font-weight:600;color:rgba(255,255,255,.9);">
              <?= htmlspecialchars($user['name'] ?? '') ?>
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,.7)" stroke-width="2" class="d-none d-xl-block">
              <polyline points="6 9 12 15 18 9"/>
            </svg>
          </a>
          <div class="dropdown-menu dropdown-menu-end">
            <div class="dropdown-header" style="font-size:11px;color:var(--text-muted);">
              <?= ucfirst($user['role'] ?? '') ?>
            </div>
            <a href="<?= $baseUrl ?>/profile" class="dropdown-item <?= isActive('/profile', $currentUri) ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Profil Saya
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?= $baseUrl ?>/logout" class="dropdown-item text-danger">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="15" height="15"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              Logout
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>
</header>
