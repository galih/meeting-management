<?php
$user    = Auth::user();
$baseUrl = rtrim(BASE_URL, '/');

$scriptDir  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$fullUri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$currentUri = ($scriptDir !== '' && strncmp($fullUri, $scriptDir, strlen($scriptDir)) === 0)
    ? substr($fullUri, strlen($scriptDir))
    : $fullUri;
$currentUri = $currentUri ?: '/';

function sbActive(string $path, string $current): bool {
    if ($path === '/') return $current === '/';
    return strncmp($current, $path, strlen($path)) === 0;
}

$appLogo  = SettingController::get('app_logo');
$unread   = $user ? \Notification::countUnread((int)$user['id']) : 0;
$isAdmin  = Auth::isAdmin();
$isMgr    = Auth::hasRole('admin','sekretaris');

// Tentukan apakah grup Administrasi aktif
$adminPaths  = ['/users','/departments','/recurring','/notulen-templates','/settings','/admin/activity-log','/roles'];
$adminActive = false;
foreach ($adminPaths as $ap) {
    if (sbActive($ap, $currentUri)) { $adminActive = true; break; }
}
?>
<style>
/* ===================================================
   KEMENBUD SIDEBAR
   Fixed vertical sidebar — 240px
=================================================== */
:root {
  --sb-w:         240px;
  --sb-w-col:      56px;   /* collapsed icon-only */
  --sb-bg:        #6A1010;
  --sb-bg-dark:   #4E0C0C;
  --sb-bg-item:   rgba(255,255,255,.07);
  --sb-bg-active: rgba(255,255,255,.15);
  --sb-accent:    #C9A84C;
  --sb-text:      rgba(255,255,255,.85);
  --sb-text-muted:rgba(255,255,255,.50);
  --sb-border:    rgba(255,255,255,.10);
  --sb-radius:    8px;
  --sb-transition:200ms cubic-bezier(.16,1,.3,1);

  /* Topbar */
  --tb-h:    52px;
  --tb-bg:   #fff;
  --tb-border: #EDE8DE;
}

/* ── Layout shell ──────────────────────────────────── */
.sb-layout {
  display: flex;
  min-height: 100vh;
}
.sb-sidebar {
  width: var(--sb-w);
  min-height: 100vh;
  background: linear-gradient(180deg, var(--sb-bg) 0%, var(--sb-bg-dark) 100%);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0; left: 0; bottom: 0;
  z-index: 200;
  transition: width var(--sb-transition), transform var(--sb-transition);
  overflow: hidden;
}
.sb-sidebar.collapsed {
  width: var(--sb-w-col);
}
.sb-main {
  flex: 1;
  margin-left: var(--sb-w);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  transition: margin-left var(--sb-transition);
  background: #F5F0E8;
}
.sb-sidebar.collapsed ~ .sb-main {
  margin-left: var(--sb-w-col);
}

/* ── Sidebar Header ────────────────────────────────── */
.sb-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .9rem 1rem .8rem;
  border-bottom: 1px solid var(--sb-border);
  flex-shrink: 0;
}
.sb-brand {
  display: flex;
  align-items: center;
  gap: .6rem;
  text-decoration: none;
  overflow: hidden;
  flex: 1;
  min-width: 0;
}
.sb-brand-logo {
  width: 30px; height: 30px; flex-shrink: 0;
  object-fit: contain;
}
.sb-brand-icon {
  width: 30px; height: 30px; flex-shrink: 0;
  background: rgba(255,255,255,.15);
  border-radius: 7px;
  display: flex; align-items: center; justify-content: center;
  color: var(--sb-accent);
}
.sb-brand-text {
  font-size: 13.5px; font-weight: 800;
  color: #fff; white-space: nowrap;
  overflow: hidden; text-overflow: ellipsis;
  letter-spacing: -.01em;
}
.sb-brand-sub {
  font-size: 10px; color: var(--sb-text-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  line-height: 1.2;
}
.sb-collapse-btn {
  width: 26px; height: 26px; flex-shrink: 0;
  background: rgba(255,255,255,.1);
  border: 1px solid var(--sb-border);
  border-radius: 6px; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--sb-text-muted); transition: all var(--sb-transition);
}
.sb-collapse-btn:hover { background: rgba(255,255,255,.2); color: #fff; }
.sb-collapse-btn svg { transition: transform var(--sb-transition); }
.sb-sidebar.collapsed .sb-collapse-btn svg { transform: rotate(180deg); }

/* ── Nav ──────────────────────────────────────────── */
.sb-nav {
  flex: 1;
  overflow-y: auto;
  overflow-x: hidden;
  padding: .5rem 0;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,.15) transparent;
}
.sb-nav::-webkit-scrollbar { width: 4px; }
.sb-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 4px; }

.sb-section-label {
  font-size: 9.5px; font-weight: 800; text-transform: uppercase;
  letter-spacing: .1em; color: var(--sb-text-muted);
  padding: .75rem 1rem .25rem;
  white-space: nowrap; overflow: hidden;
  transition: opacity var(--sb-transition);
}
.sb-sidebar.collapsed .sb-section-label { opacity: 0; }

.sb-item {
  display: flex;
  align-items: center;
  gap: .6rem;
  padding: .48rem .9rem;
  margin: .1rem .5rem;
  border-radius: var(--sb-radius);
  cursor: pointer;
  text-decoration: none;
  color: var(--sb-text);
  font-size: 13px; font-weight: 600;
  transition: background var(--sb-transition), color var(--sb-transition);
  white-space: nowrap;
  overflow: hidden;
  position: relative;
}
.sb-item:hover {
  background: var(--sb-bg-item);
  color: #fff;
}
.sb-item.active {
  background: var(--sb-bg-active);
  color: #fff;
}
.sb-item.active::before {
  content: '';
  position: absolute;
  left: 0; top: 20%; bottom: 20%;
  width: 3px;
  background: var(--sb-accent);
  border-radius: 0 3px 3px 0;
  margin-left: -.5rem;
}
.sb-item-icon {
  width: 18px; height: 18px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.sb-item-label {
  flex: 1; overflow: hidden; text-overflow: ellipsis;
  transition: opacity var(--sb-transition), width var(--sb-transition);
}
.sb-sidebar.collapsed .sb-item-label,
.sb-sidebar.collapsed .sb-item-chevron,
.sb-sidebar.collapsed .sb-item-badge { opacity: 0; width: 0; pointer-events: none; }
.sb-sidebar.collapsed .sb-item { justify-content: center; padding: .48rem .6rem; margin: .1rem .3rem; }
.sb-sidebar.collapsed .sb-item.active::before { left: 0; margin-left: -.3rem; }
.sb-sidebar.collapsed .sb-section-label { padding: .5rem 0; }

/* Badge */
.sb-item-badge {
  font-size: 10px; font-weight: 800;
  background: #e53e3e; color: #fff;
  padding: .1em .45em; border-radius: 10px;
  min-width: 18px; text-align: center; flex-shrink: 0;
  transition: opacity var(--sb-transition);
}

/* Chevron for groups */
.sb-item-chevron {
  width: 14px; height: 14px; flex-shrink: 0;
  color: var(--sb-text-muted);
  transition: transform var(--sb-transition), opacity var(--sb-transition);
}
.sb-group.open > .sb-group-toggle .sb-item-chevron {
  transform: rotate(90deg);
}

/* Group children */
.sb-group-children {
  overflow: hidden;
  max-height: 0;
  transition: max-height var(--sb-transition);
}
.sb-group.open > .sb-group-children {
  max-height: 400px;
}
.sb-group-children .sb-item {
  padding-left: 2.5rem;
  font-size: 12.5px;
  font-weight: 500;
}
.sb-sidebar.collapsed .sb-group-children { display: none; }

/* Divider */
.sb-divider {
  height: 1px;
  background: var(--sb-border);
  margin: .4rem .5rem;
}

/* Tooltip on collapsed */
.sb-sidebar.collapsed .sb-item {
  position: relative;
}
.sb-sidebar.collapsed .sb-item:hover::after {
  content: attr(data-label);
  position: absolute;
  left: calc(var(--sb-w-col) + 6px);
  top: 50%; transform: translateY(-50%);
  background: #1C1714;
  color: #fff;
  font-size: 12px; font-weight: 600;
  padding: .3em .7em;
  border-radius: 6px;
  white-space: nowrap;
  z-index: 300;
  pointer-events: none;
  box-shadow: 0 2px 12px rgba(0,0,0,.25);
}

/* ── Sidebar Footer (user) ─────────────────────────── */
.sb-footer {
  border-top: 1px solid var(--sb-border);
  padding: .65rem .5rem;
  flex-shrink: 0;
}
.sb-user {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .4rem .5rem;
  border-radius: var(--sb-radius);
  cursor: pointer;
  text-decoration: none;
  transition: background var(--sb-transition);
  overflow: hidden;
  position: relative;
}
.sb-user:hover { background: var(--sb-bg-item); }
.sb-avatar {
  width: 30px; height: 30px; border-radius: 50%; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800; color: #fff;
  background: var(--sb-accent);
  border: 1.5px solid rgba(255,255,255,.25);
}
.sb-user-info { overflow: hidden; flex: 1; min-width: 0; }
.sb-user-name {
  font-size: 12.5px; font-weight: 700; color: #fff;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sb-user-role {
  font-size: 10.5px; color: var(--sb-text-muted);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sb-sidebar.collapsed .sb-user-info { opacity:0; width:0; pointer-events:none; }
.sb-sidebar.collapsed .sb-user { justify-content: center; }

/* ── Top bar ──────────────────────────────────────── */
.sb-topbar {
  height: var(--tb-h);
  background: var(--tb-bg);
  border-bottom: 1px solid var(--tb-border);
  display: flex;
  align-items: center;
  padding: 0 1.25rem;
  gap: .75rem;
  flex-shrink: 0;
  position: sticky; top: 0; z-index: 100;
  box-shadow: 0 1px 4px rgba(28,23,20,.06);
}
.sb-topbar-title {
  flex: 1;
  font-size: 14px; font-weight: 800;
  color: #1C1714;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.sb-topbar-right { display:flex; align-items:center; gap:.5rem; }
.sb-topbar-btn {
  width: 34px; height: 34px;
  border-radius: 8px; border: 1.5px solid #EDE8DE;
  background: #fff; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: #6B6055; position: relative;
  transition: all 200ms;
}
.sb-topbar-btn:hover { background: #F5F0E8; border-color: #DDD5C4; color: #1C1714; }
.sb-topbar-notif-badge {
  position: absolute; top: 3px; right: 3px;
  width: 8px; height: 8px; border-radius: 50%;
  background: #e53e3e;
  border: 1.5px solid #fff;
}

/* Notif dropdown */
.sb-notif-dropdown {
  position: fixed;
  top: calc(var(--tb-h) + 6px);
  right: 1.25rem;
  width: 340px;
  background: #fff;
  border: 1.5px solid #EDE8DE;
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(28,23,20,.12);
  z-index: 500;
  display: none;
  overflow: hidden;
}
.sb-notif-dropdown.open { display: flex; flex-direction: column; }
.sb-notif-hdr {
  display: flex; align-items: center; justify-content: space-between;
  padding: .75rem 1rem;
  border-bottom: 1px solid #EDE8DE;
}
.sb-notif-hdr-title { font-size: 13.5px; font-weight: 800; color: #1C1714; }
.sb-notif-hdr-action {
  font-size: 11.5px; font-weight: 700; color: #7B1C1C;
  cursor: pointer; background: none; border: none; padding: 0;
}
.sb-notif-hdr-action:hover { text-decoration: underline; }
.sb-notif-list { max-height: 340px; overflow-y: auto; }
.sb-notif-footer {
  padding: .55rem 1rem;
  border-top: 1px solid #EDE8DE;
  text-align: center;
}
.sb-notif-footer a {
  font-size: 12px; font-weight: 700; color: #7B1C1C; text-decoration: none;
}
.sb-notif-footer a:hover { text-decoration: underline; }

/* User menu dropdown */
.sb-user-dropdown {
  position: fixed;
  bottom: calc(100% - var(--tb-h) + 6px);
  right: 1.25rem;
  width: 200px;
  background: #fff;
  border: 1.5px solid #EDE8DE;
  border-radius: 10px;
  box-shadow: 0 4px 24px rgba(28,23,20,.12);
  z-index: 500;
  display: none;
  overflow: hidden;
  /* Reset to topbar based */
  bottom: auto;
  top: calc(var(--tb-h) + 6px);
}
.sb-user-dropdown.open { display: block; }
.sb-user-dropdown-header {
  padding: .65rem 1rem;
  border-bottom: 1px solid #EDE8DE;
  background: #FBF8F3;
}
.sb-user-dropdown-name { font-size: 13px; font-weight: 800; color: #1C1714; }
.sb-user-dropdown-role { font-size: 11px; color: #A89E90; margin-top: .1rem; }
.sb-user-dropdown-item {
  display: flex; align-items: center; gap: .5rem;
  padding: .5rem 1rem; font-size: 13px; font-weight: 600; color: #1C1714;
  cursor: pointer; text-decoration: none;
  transition: background 150ms;
}
.sb-user-dropdown-item:hover { background: #F5F0E8; }
.sb-user-dropdown-item.danger { color: #A8251A; }
.sb-user-dropdown-item.danger:hover { background: rgba(168,37,26,.06); }

/* Mobile overlay */
.sb-overlay {
  display: none;
  position: fixed; inset: 0; z-index: 190;
  background: rgba(0,0,0,.45);
}
.sb-overlay.open { display: block; }

/* Mobile: hide sidebar off-screen */
@media (max-width: 767.98px) {
  .sb-sidebar {
    transform: translateX(-100%);
    width: var(--sb-w) !important;
  }
  .sb-sidebar.mobile-open {
    transform: translateX(0);
  }
  .sb-main { margin-left: 0 !important; }
  .sb-topbar-mobile-btn { display: flex !important; }
}
.sb-topbar-mobile-btn { display: none; }
</style>

<div class="sb-layout">

<!-- ── Mobile overlay ──────────────────────────────── -->
<div class="sb-overlay" id="sbOverlay"></div>

<!-- ════════════════════════════════════════════════════
     SIDEBAR
════════════════════════════════════════════════════ -->
<aside class="sb-sidebar" id="sbSidebar">

  <!-- Header / Brand -->
  <div class="sb-header">
    <a href="<?= $baseUrl ?>/" class="sb-brand" title="<?= htmlspecialchars(APP_NAME) ?>">
      <?php if ($appLogo): ?>
        <img src="<?= htmlspecialchars($appLogo) ?>" alt="Logo" class="sb-brand-logo">
      <?php else: ?>
        <div class="sb-brand-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
      <?php endif; ?>
      <div style="overflow:hidden;min-width:0;">
        <div class="sb-brand-text"><?= htmlspecialchars(APP_NAME) ?></div>
        <div class="sb-brand-sub">Manajemen Kegiatan</div>
      </div>
    </a>
    <button class="sb-collapse-btn" id="sbCollapseBtn" title="Perkecil sidebar">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </button>
  </div>

  <!-- Nav -->
  <nav class="sb-nav">

    <!-- Main -->
    <div class="sb-section-label">Menu Utama</div>

    <a href="<?= $baseUrl ?>/"
       class="sb-item <?= sbActive('/', $currentUri) ? 'active' : '' ?>"
       data-label="Dashboard">
      <span class="sb-item-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      </span>
      <span class="sb-item-label">Dashboard</span>
    </a>

    <a href="<?= $baseUrl ?>/meetings"
       class="sb-item <?= sbActive('/meetings', $currentUri) ? 'active' : '' ?>"
       data-label="Kegiatan">
      <span class="sb-item-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </span>
      <span class="sb-item-label">Kegiatan</span>
    </a>

    <a href="<?= $baseUrl ?>/tindak-lanjut"
       class="sb-item <?= sbActive('/tindak-lanjut', $currentUri) ? 'active' : '' ?>"
       data-label="Tindak Lanjut">
      <span class="sb-item-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </span>
      <span class="sb-item-label">Tindak Lanjut</span>
    </a>

    <a href="<?= $baseUrl ?>/dokumen"
       class="sb-item <?= sbActive('/dokumen', $currentUri) ? 'active' : '' ?>"
       data-label="Dokumen">
      <span class="sb-item-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      </span>
      <span class="sb-item-label">Dokumen</span>
    </a>

    <a href="<?= $baseUrl ?>/notifications"
       class="sb-item <?= sbActive('/notifications', $currentUri) ? 'active' : '' ?>"
       data-label="Notifikasi">
      <span class="sb-item-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      </span>
      <span class="sb-item-label">Notifikasi</span>
      <?php if ($unread > 0): ?>
      <span class="sb-item-badge"><?= $unread > 99 ? '99+' : $unread ?></span>
      <?php endif; ?>
    </a>

    <?php if ($isMgr): ?>
    <div class="sb-divider"></div>
    <div class="sb-section-label">Administrasi</div>

    <!-- Grup Administrasi -->
    <div class="sb-group <?= $adminActive ? 'open' : '' ?>" id="sbGroupAdmin">
      <div class="sb-item sb-group-toggle <?= $adminActive ? 'active' : '' ?>"
           data-label="Administrasi"
           onclick="toggleSbGroup('sbGroupAdmin')">
        <span class="sb-item-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        </span>
        <span class="sb-item-label">Administrasi</span>
        <svg class="sb-item-chevron" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      </div>
      <div class="sb-group-children">

        <?php if ($isAdmin): ?>
        <a href="<?= $baseUrl ?>/users"
           class="sb-item <?= sbActive('/users', $currentUri) ? 'active' : '' ?>"
           data-label="Manajemen User">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </span>
          <span class="sb-item-label">Pengguna</span>
        </a>

        <a href="<?= $baseUrl ?>/roles"
           class="sb-item <?= sbActive('/roles', $currentUri) ? 'active' : '' ?>"
           data-label="Role & Permission">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          </span>
          <span class="sb-item-label">Role &amp; Permission</span>
        </a>

        <a href="<?= $baseUrl ?>/departments"
           class="sb-item <?= sbActive('/departments', $currentUri) ? 'active' : '' ?>"
           data-label="Unit Kerja">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          </span>
          <span class="sb-item-label">Unit Kerja</span>
        </a>

        <a href="<?= $baseUrl ?>/recurring"
           class="sb-item <?= sbActive('/recurring', $currentUri) ? 'active' : '' ?>"
           data-label="Recurring Kegiatan">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
          </span>
          <span class="sb-item-label">Recurring</span>
        </a>
        <?php endif; ?>

        <a href="<?= $baseUrl ?>/notulen-templates"
           class="sb-item <?= sbActive('/notulen-templates', $currentUri) ? 'active' : '' ?>"
           data-label="Template Notulen">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          </span>
          <span class="sb-item-label">Template Notulen</span>
        </a>

        <?php if ($isAdmin): ?>
        <a href="<?= $baseUrl ?>/admin/activity-log"
           class="sb-item <?= sbActive('/admin/activity-log', $currentUri) ? 'active' : '' ?>"
           data-label="Log Aktivitas">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><polyline points="10 9 9 9 8 9"/></svg>
          </span>
          <span class="sb-item-label">Log Aktivitas</span>
        </a>

        <a href="<?= $baseUrl ?>/settings"
           class="sb-item <?= sbActive('/settings', $currentUri) ? 'active' : '' ?>"
           data-label="Pengaturan">
          <span class="sb-item-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
          </span>
          <span class="sb-item-label">Pengaturan</span>
        </a>
        <?php endif; ?>

      </div>
    </div>
    <?php endif; ?>

  </nav>

  <!-- Sidebar Footer: User -->
  <div class="sb-footer">
    <a href="<?= $baseUrl ?>/profile" class="sb-user" title="Profil Saya">
      <div class="sb-avatar"><?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?></div>
      <div class="sb-user-info">
        <div class="sb-user-name"><?= htmlspecialchars($user['name'] ?? '') ?></div>
        <div class="sb-user-role"><?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></div>
      </div>
    </a>
  </div>

</aside>
<!-- END SIDEBAR -->

<!-- ════════════════════════════════════════════════════
     MAIN AREA
════════════════════════════════════════════════════ -->
<div class="sb-main" id="sbMain">

  <!-- Top Bar -->
  <div class="sb-topbar">
    <!-- Mobile menu toggle -->
    <button class="sb-topbar-btn sb-topbar-mobile-btn" id="sbMobileToggle" title="Buka menu">
      <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <!-- Page title (resolved by JS) -->
    <div class="sb-topbar-title" id="sbPageTitle"></div>

    <div class="sb-topbar-right">
      <!-- Notification bell -->
      <button class="sb-topbar-btn" id="sbNotifBtn" title="Notifikasi">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        <?php if ($unread > 0): ?>
        <span class="sb-topbar-notif-badge" id="notifDot"></span>
        <?php else: ?>
        <span class="sb-topbar-notif-badge" id="notifDot" style="display:none;"></span>
        <?php endif; ?>
      </button>

      <!-- User avatar button -->
      <button class="sb-topbar-btn" id="sbUserBtn" title="Akun saya" style="width:auto;padding:0 .5rem;gap:.4rem;">
        <div style="width:26px;height:26px;border-radius:50%;background:#7B1C1C;color:#fff;font-size:11px;font-weight:800;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
          <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <span style="font-size:12.5px;font-weight:700;color:#1C1714;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
          <?= htmlspecialchars($user['name'] ?? '') ?>
        </span>
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#A89E90" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
    </div>
  </div>

  <!-- Notification dropdown -->
  <div class="sb-notif-dropdown" id="sbNotifDropdown">
    <div class="sb-notif-hdr">
      <span class="sb-notif-hdr-title">Notifikasi</span>
      <button class="sb-notif-hdr-action" id="sbMarkAllRead">Baca Semua</button>
    </div>
    <div class="sb-notif-list list-group list-group-flush" id="notif-list" style="max-height:340px;overflow-y:auto;">
      <div class="list-group-item text-center text-muted py-4">
        <div class="spinner-border spinner-border-sm"></div>
      </div>
    </div>
    <div class="sb-notif-footer">
      <a href="<?= $baseUrl ?>/notifications">Lihat semua notifikasi &rarr;</a>
    </div>
  </div>

  <!-- User dropdown -->
  <div class="sb-user-dropdown" id="sbUserDropdown">
    <div class="sb-user-dropdown-header">
      <div class="sb-user-dropdown-name"><?= htmlspecialchars($user['name'] ?? '') ?></div>
      <div class="sb-user-dropdown-role"><?= htmlspecialchars(ucfirst($user['role'] ?? '')) ?></div>
    </div>
    <a href="<?= $baseUrl ?>/profile" class="sb-user-dropdown-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Profil Saya
    </a>
    <div style="height:1px;background:#EDE8DE;margin:.2rem 0;"></div>
    <a href="<?= $baseUrl ?>/logout" class="sb-user-dropdown-item danger">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>

  <!-- CONTENT will be injected here by base.php -->
<?php
// base.php closes </div> (.sb-main) and </div> (.sb-layout) after $content
// We close the tags in base.php, so do NOT close here.
// However, since sidebar.php is included BEFORE $content in base.php,
// we must NOT close .sb-main here.
?>

<script>
(function () {
  'use strict';
  const BASE   = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  const LS_COL = 'sb_collapsed';

  const sidebar   = document.getElementById('sbSidebar');
  const colBtn    = document.getElementById('sbCollapseBtn');
  const overlay   = document.getElementById('sbOverlay');
  const mobileBtn = document.getElementById('sbMobileToggle');

  /* ── Collapse state (desktop) ──────────────────────────────── */
  if (localStorage.getItem(LS_COL) === '1') {
    sidebar.classList.add('collapsed');
  }
  colBtn.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
    localStorage.setItem(LS_COL, sidebar.classList.contains('collapsed') ? '1' : '0');
  });

  /* ── Mobile toggle ─────────────────────────────────────────── */
  mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('open');
  });
  overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('open');
  });

  /* ── Group toggle ───────────────────────────────────────────── */
  window.toggleSbGroup = function(id) {
    document.getElementById(id)?.classList.toggle('open');
  };

  /* ── Page title from active item ──────────────────────────── */
  const activeItem = document.querySelector('.sb-item.active:not(.sb-group-toggle)');
  const titleEl    = document.getElementById('sbPageTitle');
  if (titleEl) {
    if (activeItem) {
      titleEl.textContent = activeItem.dataset.label ||
        activeItem.querySelector('.sb-item-label')?.textContent?.trim() || '';
    } else {
      titleEl.textContent = document.title.split('\u2014')[0].trim();
    }
  }

  /* ── Close dropdowns on outside click ─────────────────────── */
  function closeDropdowns(except) {
    const ids = ['sbNotifDropdown', 'sbUserDropdown'];
    ids.forEach(id => { if (id !== except) document.getElementById(id)?.classList.remove('open'); });
  }
  document.addEventListener('click', e => {
    if (!e.target.closest('#sbNotifBtn') && !e.target.closest('#sbNotifDropdown')) {
      document.getElementById('sbNotifDropdown')?.classList.remove('open');
    }
    if (!e.target.closest('#sbUserBtn') && !e.target.closest('#sbUserDropdown')) {
      document.getElementById('sbUserDropdown')?.classList.remove('open');
    }
  });

  /* ── Notification dropdown ────────────────────────────────── */
  const notifBtn  = document.getElementById('sbNotifBtn');
  const notifDrop = document.getElementById('sbNotifDropdown');
  const notifDot  = document.getElementById('notifDot');
  let notifLoaded = false;

  notifBtn.addEventListener('click', async () => {
    closeDropdowns('sbNotifDropdown');
    notifDrop.classList.toggle('open');
    if (notifDrop.classList.contains('open') && !notifLoaded) {
      notifLoaded = true;
      try {
        const res = await fetch(BASE + '/api/notifications');
        const d   = await res.json();
        const list = document.getElementById('notif-list');
        if (!d.data || d.data.length === 0) {
          list.innerHTML = '<div class="list-group-item text-center text-muted py-4" style="font-size:13px;">Tidak ada notifikasi</div>';
        } else {
          list.innerHTML = d.data.map(n => `
            <a href="${n.link || '#'}" class="list-group-item list-group-item-action${n.is_read ? '' : ' fw-bold'}" style="font-size:12.5px;">
              <div>${n.message}</div>
              <small class="text-muted">${n.created_at_human ?? ''}</small>
            </a>
          `).join('');
        }
        if (d.unread_count !== undefined) {
          const dot = document.getElementById('notifDot');
          if (dot) dot.style.display = d.unread_count > 0 ? '' : 'none';
        }
      } catch(e) {
        document.getElementById('notif-list').innerHTML =
          '<div class="list-group-item text-danger py-3" style="font-size:12.5px;">Gagal memuat notifikasi.</div>';
      }
    }
  });

  /* Mark all read */
  document.getElementById('sbMarkAllRead')?.addEventListener('click', async () => {
    await fetch(BASE + '/api/notifications/read', { method: 'POST' });
    const dot = document.getElementById('notifDot');
    if (dot) dot.style.display = 'none';
    document.querySelectorAll('#notif-list .fw-bold').forEach(el => el.classList.remove('fw-bold'));
  });

  /* ── User dropdown ───────────────────────────────────────────── */
  document.getElementById('sbUserBtn')?.addEventListener('click', () => {
    closeDropdowns('sbUserDropdown');
    document.getElementById('sbUserDropdown')?.classList.toggle('open');
  });

  /* Position dropdowns based on button position */
  function positionDropdown(btnId, dropId) {
    const btn  = document.getElementById(btnId);
    const drop = document.getElementById(dropId);
    if (!btn || !drop) return;
    const rect = btn.getBoundingClientRect();
    drop.style.top   = (rect.bottom + 6) + 'px';
    drop.style.right = (window.innerWidth - rect.right) + 'px';
    drop.style.left  = 'auto';
  }
  notifBtn.addEventListener('click', () => positionDropdown('sbNotifBtn', 'sbNotifDropdown'));
  document.getElementById('sbUserBtn')?.addEventListener('click', () => positionDropdown('sbUserBtn', 'sbUserDropdown'));

}());
</script>
