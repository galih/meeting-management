<?php
/** @var array $roles @var array $modules @var array $rolePerms */
$baseUrl = rtrim(BASE_URL, '/');
?>
<!-- ============================================================
     KEMENBUD ROLE & PERMISSION MANAGER
     Primary  : #7B1C1C  (Merah Marun)
     Accent   : #C9A84C  (Emas)
     Surface  : #FBF8F3  (Krem Hangat)
============================================================ -->
<style>
:root {
  --kb-primary:        #7B1C1C;
  --kb-primary-dark:   #5A1212;
  --kb-primary-light:  rgba(123,28,28,.08);
  --kb-primary-ring:   rgba(123,28,28,.18);
  --kb-gold:           #C9A84C;
  --kb-gold-dark:      #A8872F;
  --kb-surface:        #FBF8F3;
  --kb-surface-2:      #F5F0E8;
  --kb-surface-3:      #EDE6D6;
  --kb-border:         #DDD5C4;
  --kb-border-light:   #EDE8DE;
  --kb-text:           #1C1714;
  --kb-text-muted:     #6B6055;
  --kb-text-faint:     #A89E90;
  --kb-green:          #2A6B3A;
  --kb-green-bg:       rgba(42,107,58,.10);
  --kb-blue:           #1B4F82;
  --kb-blue-bg:        rgba(27,79,130,.10);
  --kb-red:            #A8251A;
  --kb-red-bg:         rgba(168,37,26,.10);
  --kb-orange:         #C05621;
  --kb-orange-bg:      rgba(192,86,33,.10);
  --kb-purple:         #6B3A8A;
  --kb-purple-bg:      rgba(107,58,138,.10);
  --kb-gray-bg:        rgba(100,100,100,.08);
  --kb-radius:         12px;
  --kb-radius-sm:      8px;
  --kb-radius-xs:      6px;
  --kb-shadow-sm:      0 1px 4px rgba(28,23,20,.07);
  --kb-shadow-md:      0 3px 12px rgba(28,23,20,.09);
  --kb-transition:     180ms cubic-bezier(.16,1,.3,1);
}
.rol-wrap * { box-sizing: border-box; }

/* ── Hero ──────────────────────────────────────────────────────── */
.rol-hero {
  background: linear-gradient(135deg,#7B1C1C 0%,#9B2020 50%,#6A1515 100%);
  border-radius: var(--kb-radius);
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative; overflow: hidden; margin-bottom: 1.25rem;
}
.rol-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.rol-hero::after {
  content:''; position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.rol-hero-inner {
  padding: 1.25rem 1.5rem 1.1rem; position: relative; z-index: 1;
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
}
.rol-hero-left  { display:flex; align-items:center; gap:.85rem; }
.rol-hero-icon  {
  width:44px; height:44px; border-radius:var(--kb-radius-sm);
  background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.22);
  display:flex; align-items:center; justify-content:center;
  color:rgba(255,255,255,.9); flex-shrink:0;
}
.rol-hero-title { font-size:clamp(16px,2vw,22px); font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.rol-hero-sub   { font-size:12.5px; color:rgba(255,255,255,.65); margin-top:.15rem; }
.rol-hero-bar   {
  height:3px;
  background:linear-gradient(90deg,var(--kb-gold) 0%,var(--kb-gold-dark) 60%,transparent 100%);
}

/* ── Buttons ────────────────────────────────────────────────────── */
.rol-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:700; border-radius:var(--kb-radius-sm);
  padding:.38rem .9rem; cursor:pointer;
  transition:all var(--kb-transition); border:1.5px solid transparent;
  text-decoration:none; white-space:nowrap;
}
.rol-btn-primary {
  background:linear-gradient(135deg,var(--kb-primary),#9B2020); color:#fff;
  border-color:var(--kb-primary-dark);
  box-shadow:0 2px 8px rgba(123,28,28,.25);
}
.rol-btn-primary:hover { background:linear-gradient(135deg,#9B2020,var(--kb-primary-dark)); color:#fff; }
.rol-btn-success {
  background:linear-gradient(135deg,var(--kb-green),#1E5228); color:#fff;
  border-color:#1E5228; box-shadow:0 2px 8px rgba(42,107,58,.25);
}
.rol-btn-success:hover { background:linear-gradient(135deg,#1E5228,#163D1E); color:#fff; }
.rol-btn-outline { background:#fff; color:var(--kb-text-muted); border-color:var(--kb-border); }
.rol-btn-outline:hover { border-color:var(--kb-primary); color:var(--kb-primary); }
.rol-btn-ghost   { background:transparent; color:var(--kb-text-muted); border-color:var(--kb-border); }
.rol-btn-ghost:hover { background:var(--kb-primary-light); border-color:var(--kb-primary); color:var(--kb-primary); }
.rol-btn-danger  { background:var(--kb-red-bg); color:var(--kb-red); border-color:rgba(168,37,26,.18); }
.rol-btn-danger:hover { background:var(--kb-red); color:#fff; }
.rol-btn:disabled { opacity:.55; cursor:not-allowed; }

/* ── Role Cards ─────────────────────────────────────────────────── */
.rol-cards {
  display:grid;
  grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
  gap:.75rem; margin-bottom:1.25rem;
}
.rol-card {
  background:#fff; border:1.5px solid var(--kb-border-light);
  border-radius:var(--kb-radius); padding:1rem 1.1rem;
  box-shadow:var(--kb-shadow-sm);
  transition:box-shadow var(--kb-transition), border-color var(--kb-transition);
  position:relative;
}
.rol-card:hover { box-shadow:var(--kb-shadow-md); border-color:var(--kb-border); }
.rol-card-top    { display:flex; align-items:flex-start; justify-content:space-between; gap:.5rem; }
.rol-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.25em .7em; border-radius:20px;
  font-size:12px; font-weight:700; color:#fff;
  box-shadow:0 1px 4px rgba(0,0,0,.18);
}
.rol-badge-color-dot { width:7px; height:7px; border-radius:50%; background:rgba(255,255,255,.6); }
.rol-system-tag {
  display:inline-flex; align-items:center; gap:.25rem;
  font-size:10.5px; font-weight:700; color:var(--kb-text-faint);
  background:var(--kb-surface-2); border:1px solid var(--kb-border);
  border-radius:4px; padding:.1em .45em; margin-top:.3rem;
}
.rol-card-slug {
  font-family:monospace; font-size:11.5px;
  color:var(--kb-text-muted); margin-top:.35rem;
  background:var(--kb-surface-2); display:inline-block;
  padding:.1em .4em; border-radius:4px;
}
.rol-card-stats {
  display:flex; gap:.75rem; margin-top:.75rem;
  padding-top:.65rem; border-top:1px solid var(--kb-border-light);
}
.rol-stat-pill {
  display:flex; align-items:center; gap:.3rem;
  font-size:11.5px; color:var(--kb-text-muted);
}
.rol-stat-pill svg { color:var(--kb-text-faint); flex-shrink:0; }
.rol-stat-pill strong { color:var(--kb-text); font-weight:800; }

/* Dropdown actions */
.rol-dropdown { position:relative; }
.rol-dropdown-menu {
  position:absolute; right:0; top:calc(100% + 4px); z-index:100;
  background:#fff; border:1.5px solid var(--kb-border-light);
  border-radius:var(--kb-radius-sm); box-shadow:var(--kb-shadow-md);
  min-width:140px; padding:.3rem 0;
  opacity:0; visibility:hidden; transform:translateY(-6px);
  transition:all var(--kb-transition);
}
.rol-dropdown.open .rol-dropdown-menu { opacity:1; visibility:visible; transform:translateY(0); }
.rol-dropdown-item {
  display:flex; align-items:center; gap:.5rem;
  padding:.45rem .9rem; font-size:12.5px; font-weight:600;
  color:var(--kb-text); cursor:pointer; border:none; background:none; width:100%;
  transition:background var(--kb-transition);
}
.rol-dropdown-item:hover { background:var(--kb-surface); }
.rol-dropdown-item.danger { color:var(--kb-red); }
.rol-dropdown-item.danger:hover { background:var(--kb-red-bg); }
.rol-toggle-btn {
  display:flex; align-items:center; justify-content:center;
  width:28px; height:28px; border-radius:var(--kb-radius-xs);
  background:var(--kb-surface-2); border:1.5px solid var(--kb-border);
  cursor:pointer; color:var(--kb-text-muted); flex-shrink:0;
  transition:all var(--kb-transition);
}
.rol-toggle-btn:hover { background:var(--kb-primary-light); border-color:var(--kb-primary); color:var(--kb-primary); }

/* ── Matrix Card ─────────────────────────────────────────────────── */
.rol-matrix-card {
  background:#fff; border:1px solid var(--kb-border-light);
  border-radius:var(--kb-radius); overflow:hidden; box-shadow:var(--kb-shadow-md);
}
.rol-matrix-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:.85rem 1.25rem; background:var(--kb-surface);
  border-bottom:1px solid var(--kb-border-light); gap:1rem; flex-wrap:wrap;
}
.rol-matrix-title {
  display:flex; align-items:center; gap:.5rem;
  font-size:14px; font-weight:800; color:var(--kb-primary);
}
.rol-matrix-actions { display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.rol-matrix-hint {
  font-size:11.5px; color:var(--kb-text-faint); margin-left:.3rem;
}

/* Table */
.rol-matrix-scroller { overflow-x:auto; }
.rol-matrix-table {
  width:100%; border-collapse:collapse;
  font-size:13px;
}
.rol-matrix-table thead th {
  padding:.65rem 1rem; background:var(--kb-surface);
  border-bottom:2px solid var(--kb-border);
  font-size:11px; font-weight:800; letter-spacing:.06em;
  text-transform:uppercase; color:var(--kb-text-muted);
  white-space:nowrap;
}
.rol-matrix-table thead th.col-role {
  text-align:center; min-width:110px;
}
.rol-matrix-table thead th.col-perm { min-width:200px; }
.rol-matrix-table tbody tr {
  border-bottom:1px solid var(--kb-border-light);
  transition:background var(--kb-transition);
}
.rol-matrix-table tbody tr:last-child { border-bottom:none; }
.rol-matrix-table tbody tr:hover { background:var(--kb-surface); }
.rol-matrix-table td { padding:.6rem 1rem; vertical-align:middle; }
.col-perm-cell {
  color:var(--kb-text); font-weight:600;
}
.col-perm-slug {
  font-family:monospace; font-size:11px; color:var(--kb-text-faint);
  display:block; margin-top:.1rem;
}
.col-role-cell { text-align:center; }

/* Module header row */
.module-row td {
  background: linear-gradient(90deg, var(--kb-surface-2) 0%, var(--kb-surface) 100%);
  font-size:11px; font-weight:800; text-transform:uppercase;
  letter-spacing:.08em; color:var(--kb-text-muted);
  border-top:2px solid var(--kb-border);
  padding:.5rem 1rem;
}

/* Custom checkbox */
.rol-check {
  appearance:none; -webkit-appearance:none;
  width:18px; height:18px; border:2px solid var(--kb-border);
  border-radius:5px; cursor:pointer; position:relative;
  background:#fff; flex-shrink:0;
  transition:all var(--kb-transition); display:inline-block; vertical-align:middle;
}
.rol-check:hover:not(:disabled) { border-color:var(--kb-primary); }
.rol-check:checked {
  background:var(--kb-primary); border-color:var(--kb-primary);
}
.rol-check:checked::after {
  content:''; position:absolute; left:4px; top:1px;
  width:7px; height:11px; border:2.5px solid #fff;
  border-top:none; border-left:none;
  transform:rotate(45deg);
}
.rol-check:disabled {
  cursor:not-allowed; opacity:.45;
  background:var(--kb-surface-2); border-color:var(--kb-border);
}
.rol-check.admin-locked {
  background:var(--kb-green); border-color:var(--kb-green); opacity:.5; cursor:not-allowed;
}
.rol-check.admin-locked::after {
  content:''; position:absolute; left:4px; top:1px;
  width:7px; height:11px; border:2.5px solid #fff;
  border-top:none; border-left:none; transform:rotate(45deg);
}

/* Dirty indicator */
.rol-dirty-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:11px; font-weight:700; padding:.2em .55em;
  border-radius:10px; background:var(--kb-orange-bg); color:var(--kb-orange);
  animation: rol-pulse 1.4s ease infinite;
}
@keyframes rol-pulse {
  0%,100% { opacity:1; } 50% { opacity:.55; }
}

/* Save spinner */
.rol-save-spinner { display:none; }

/* ── Alerts ──────────────────────────────────────────────────────── */
.rol-alert {
  display:flex; align-items:center; gap:.6rem;
  padding:.65rem 1rem; border-radius:var(--kb-radius-sm);
  font-size:13px; margin-bottom:.85rem; border:1px solid;
}
.rol-alert-success { background:var(--kb-green-bg); color:var(--kb-green); border-color:rgba(42,107,58,.2); }
.rol-alert-danger  { background:var(--kb-red-bg);   color:var(--kb-red);   border-color:rgba(168,37,26,.2); }
.rol-alert-close { margin-left:auto; cursor:pointer; background:none; border:none; color:inherit; opacity:.6; font-size:16px; line-height:1; padding:0; }
.rol-alert-close:hover { opacity:1; }

/* ── Modals ──────────────────────────────────────────────────────── */
.rol-modal .modal-content {
  border:1px solid var(--kb-border); border-radius:var(--kb-radius);
  box-shadow:var(--kb-shadow-md); overflow:hidden;
}
.rol-modal .modal-header {
  background:var(--kb-surface); border-bottom:1px solid var(--kb-border-light);
  padding:.9rem 1.25rem;
}
.rol-modal .modal-title {
  font-size:15px; font-weight:800; color:var(--kb-primary);
  display:flex; align-items:center; gap:.5rem;
}
.rol-modal .modal-body   { padding:1.25rem; }
.rol-modal .modal-footer {
  background:var(--kb-surface); border-top:1px solid var(--kb-border-light);
  padding:.75rem 1.25rem; gap:.5rem;
}
.rol-form-label { font-size:12px; font-weight:700; color:var(--kb-text); display:block; margin-bottom:.3rem; }
.rol-form-label .req { color:var(--kb-red); margin-left:.15rem; }
.rol-form-control {
  width:100%; border:1.5px solid var(--kb-border); border-radius:var(--kb-radius-sm);
  padding:.42rem .8rem; font-size:13.5px; color:var(--kb-text);
  background:#fff; outline:none;
  transition:border-color var(--kb-transition), box-shadow var(--kb-transition);
}
.rol-form-control:focus { border-color:var(--kb-primary); box-shadow:0 0 0 3px var(--kb-primary-ring); }
.rol-form-hint { font-size:11px; color:var(--kb-text-faint); margin-top:.2rem; }
.rol-modal-danger .modal-header { background:var(--kb-red-bg); border-color:rgba(168,37,26,.18); }
.rol-modal-danger .modal-title  { color:var(--kb-red); }

/* Color preview */
.rol-color-preview {
  width:28px; height:28px; border-radius:6px;
  border:2px solid var(--kb-border); flex-shrink:0;
}

/* Select-all row checkboxes */
.rol-select-all-cell { cursor:pointer; }
.rol-col-header {
  display:flex; flex-direction:column; align-items:center; gap:.35rem;
}
.rol-col-dot {
  width:10px; height:10px; border-radius:50%; flex-shrink:0;
}

/* Toast */
.rol-toast {
  position:fixed; bottom:1.25rem; right:1.25rem; z-index:9999;
  display:flex; align-items:center; gap:.6rem;
  padding:.75rem 1.1rem; border-radius:var(--kb-radius-sm);
  font-size:13px; font-weight:600;
  box-shadow:0 4px 20px rgba(0,0,0,.15);
  animation:rol-slideIn .25s ease;
}
.rol-toast-success { background:var(--kb-green); color:#fff; }
.rol-toast-error   { background:var(--kb-red); color:#fff; }
@keyframes rol-slideIn {
  from { transform:translateX(2rem); opacity:0; }
  to   { transform:translateX(0);    opacity:1; }
}

/* Responsive */
@media(max-width:767.98px) {
  .rol-hero-inner { flex-direction:column; align-items:flex-start; }
  .rol-cards { grid-template-columns: repeat(2,1fr); }
  .rol-matrix-header { flex-direction:column; align-items:flex-start; }
  .rol-matrix-table thead th.col-role { min-width:80px; }
}
</style>

<div class="rol-wrap">

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="rol-alert rol-alert-success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="rol-alert-close" onclick="this.closest('.rol-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="rol-alert rol-alert-danger">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="rol-alert-close" onclick="this.closest('.rol-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- ── Hero ───────────────────────────────────────────────────────── -->
<div class="rol-hero">
  <div class="rol-hero-inner">
    <div class="rol-hero-left">
      <div class="rol-hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </div>
      <div>
        <div class="rol-hero-title">Manajemen Role &amp; Permission</div>
        <div class="rol-hero-sub">Buat role baru dan atur hak akses per-role secara visual</div>
      </div>
    </div>
    <button class="rol-btn rol-btn-primary" id="btnOpenAddRole">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Role
    </button>
  </div>
  <div class="rol-hero-bar"></div>
</div>

<!-- ── Role Cards ─────────────────────────────────────────────────── -->
<div class="rol-cards" id="roleCardContainer">
  <?php foreach ($roles as $r): ?>
  <div class="rol-card" id="role-card-<?= $r['id'] ?>">
    <div class="rol-card-top">
      <div>
        <span class="rol-badge" style="background:<?= htmlspecialchars($r['color']) ?>">
          <span class="rol-badge-color-dot"></span>
          <?= htmlspecialchars($r['label']) ?>
        </span>
        <?php if ($r['is_system']): ?>
        <div class="rol-system-tag">
          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
          System Role
        </div>
        <?php endif; ?>
        <div class="rol-card-slug"><?= htmlspecialchars($r['name']) ?></div>
      </div>
      <?php if (!$r['is_system']): ?>
      <div class="rol-dropdown" id="dd-<?= $r['id'] ?>">
        <button class="rol-toggle-btn" onclick="toggleDropdown(<?= $r['id'] ?>)" type="button">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
        </button>
        <div class="rol-dropdown-menu">
          <button class="rol-dropdown-item" onclick="openEditRole(<?= $r['id'] ?>, '<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['color'], ENT_QUOTES) ?>')">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Role
          </button>
          <button class="rol-dropdown-item danger" onclick="confirmDeleteRole(<?= $r['id'] ?>, '<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>', <?= (int)$r['user_count'] ?>)">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Hapus Role
          </button>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <div class="rol-card-stats">
      <div class="rol-stat-pill">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <strong><?= (int)$r['user_count'] ?></strong> pengguna
      </div>
      <div class="rol-stat-pill">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        <strong id="pcount-<?= $r['id'] ?>"><?= (int)$r['perm_count'] ?></strong> permission
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ── Permission Matrix ──────────────────────────────────────────── -->
<div class="rol-matrix-card">
  <div class="rol-matrix-header">
    <div class="rol-matrix-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      Matrix Permission
      <span class="rol-dirty-badge" id="dirtyBadge" style="display:none;">
        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Ada perubahan belum disimpan
      </span>
    </div>
    <div class="rol-matrix-actions">
      <span class="rol-matrix-hint" id="matrixHint">Klik checkbox untuk mengubah permission</span>
      <button class="rol-btn rol-btn-success" id="btnSave" onclick="saveAllPermissions()" disabled>
        <span class="rol-save-spinner spinner-border spinner-border-sm" id="saveSpinner"></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="saveIcon"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Perubahan
      </button>
    </div>
  </div>

  <div class="rol-matrix-scroller">
    <table class="rol-matrix-table" id="matrixTable">
      <thead>
        <tr>
          <th class="col-perm">Permission</th>
          <?php foreach ($roles as $r): ?>
          <th class="col-role">
            <div class="rol-col-header">
              <span class="rol-col-dot" style="background:<?= htmlspecialchars($r['color']) ?>"></span>
              <span style="font-size:11px;"><?= htmlspecialchars($r['label']) ?></span>
              <?php if (!$r['is_system'] || $r['name'] !== 'admin'): ?>
              <button type="button" class="rol-btn rol-btn-ghost" style="font-size:10px;padding:.15rem .4rem;margin-top:.1rem;"
                      onclick="selectAllForRole(<?= $r['id'] ?>)" title="Pilih semua untuk role ini">
                Semua
              </button>
              <?php endif; ?>
            </div>
          </th>
          <?php endforeach; ?>
        </tr>
      </thead>
      <tbody>
        <?php
        $moduleLabels = [
          'meeting'      => '📅 Meeting',
          'notulen'      => '📝 Notulen',
          'tindaklanjut' => '✅ Tindak Lanjut',
          'dokumen'      => '📁 Dokumen',
          'user'         => '👥 User & Role',
          'settings'     => '⚙️ Settings',
        ];
        foreach ($modules as $moduleName => $perms):
        ?>
        <tr class="module-row">
          <td colspan="<?= count($roles) + 1 ?>">
            <?= $moduleLabels[$moduleName] ?? strtoupper($moduleName) ?>
            &nbsp;<span style="font-weight:400;opacity:.6;">(<?= count($perms) ?> permission)</span>
          </td>
        </tr>
        <?php foreach ($perms as $perm):
          $isAdminRole = function($r) { return $r['is_system'] && $r['name'] === 'admin'; };
        ?>
        <tr>
          <td class="col-perm-cell">
            <?= htmlspecialchars($perm['label']) ?>
            <code class="col-perm-slug"><?= htmlspecialchars($perm['name']) ?></code>
          </td>
          <?php foreach ($roles as $r):
            $checked  = in_array($perm['name'], $rolePerms[$r['id']] ?? []);
            $isAdmin  = $r['is_system'] && $r['name'] === 'admin';
          ?>
          <td class="col-role-cell">
            <input type="checkbox"
              class="rol-check <?= $isAdmin ? 'admin-locked' : 'perm-checkbox' ?>"
              data-role-id="<?= $r['id'] ?>"
              data-perm="<?= htmlspecialchars($perm['name']) ?>"
              <?= $checked || $isAdmin ? 'checked' : '' ?>
              <?= $isAdmin ? 'disabled title="Admin selalu memiliki semua permission"' : '' ?>
            >
          </td>
          <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

</div><!-- /.rol-wrap -->

<!-- ================================================================
     MODAL: Tambah Role
================================================================ -->
<div class="modal modal-blur fade rol-modal" id="modalAddRole" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/roles" id="formAddRole">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            Tambah Role Baru
          </div>
          <button type="button" class="btn-close" id="btnCloseAdd"></button>
        </div>
        <div class="modal-body">
          <div style="margin-bottom:.85rem;">
            <label class="rol-form-label">Slug Role <span class="req">*</span></label>
            <input type="text" name="name" class="rol-form-control" id="addRoleName"
                   required pattern="[a-zA-Z0-9_]+"
                   placeholder="contoh: supervisor"
                   title="Huruf, angka, underscore saja">
            <div class="rol-form-hint">Huruf/angka/underscore. <strong>Tidak bisa diubah</strong> setelah disimpan.</div>
          </div>
          <div style="margin-bottom:.85rem;">
            <label class="rol-form-label">Label Tampil <span class="req">*</span></label>
            <input type="text" name="label" class="rol-form-control" id="addRoleLabel"
                   required placeholder="contoh: Supervisor">
          </div>
          <div>
            <label class="rol-form-label">Warna Badge</label>
            <div style="display:flex;align-items:center;gap:.75rem;">
              <input type="color" name="color" id="addRoleColor"
                     class="rol-form-control" value="#6c757d"
                     style="width:46px;height:36px;padding:.2rem;cursor:pointer;">
              <div class="rol-color-preview" id="addColorPreview" style="background:#6c757d;"></div>
              <span id="addRoleBadgePreview" class="rol-badge" style="background:#6c757d;">Preview</span>
            </div>
            <div class="rol-form-hint">Warna badge di tabel pengguna</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="rol-btn rol-btn-ghost" id="btnBatalAdd">Batal</button>
          <button type="submit" class="rol-btn rol-btn-primary">Buat Role</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Edit Role
================================================================ -->
<div class="modal modal-blur fade rol-modal" id="modalEditRole" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditRole" action="">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Role
          </div>
          <button type="button" class="btn-close" id="btnCloseEdit"></button>
        </div>
        <div class="modal-body">
          <div style="margin-bottom:.85rem;">
            <label class="rol-form-label">Label Tampil <span class="req">*</span></label>
            <input type="text" name="label" id="editRoleLabel" class="rol-form-control" required>
          </div>
          <div>
            <label class="rol-form-label">Warna Badge</label>
            <div style="display:flex;align-items:center;gap:.75rem;">
              <input type="color" name="color" id="editRoleColor"
                     class="rol-form-control"
                     style="width:46px;height:36px;padding:.2rem;cursor:pointer;">
              <div class="rol-color-preview" id="editColorPreview"></div>
              <span id="editRoleBadgePreview" class="rol-badge">Preview</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="rol-btn rol-btn-ghost" id="btnBatalEdit">Batal</button>
          <button type="submit" class="rol-btn rol-btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Hapus Role
================================================================ -->
<div class="modal modal-blur fade rol-modal rol-modal-danger" id="modalDeleteRole" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          Hapus Role
        </div>
        <button type="button" class="btn-close" id="btnCloseDelete"></button>
      </div>
      <div class="modal-body" style="font-size:13.5px;color:var(--kb-text);">
        <p>Yakin hapus role <strong id="deleteRoleLabel"></strong>?</p>
        <p id="deleteRoleWarn" style="font-size:12.5px;color:var(--kb-orange);display:none;">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Role ini dipakai oleh <strong id="deleteRoleUserCount"></strong> pengguna. Pastikan sudah dipindahkan ke role lain.
        </p>
        <p style="font-size:12px;color:var(--kb-red);">&#9888; Tindakan ini tidak dapat dibatalkan.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="rol-btn rol-btn-ghost" id="btnBatalDelete">Batal</button>
        <button type="button" id="btnKonfirmasiDelete" class="rol-btn rol-btn-danger"
                style="background:var(--kb-red);color:#fff;border-color:var(--kb-red);">
          Ya, Hapus Role
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';
  const BASE = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  const CSRF = document.querySelector('meta[name=csrf-token]')?.content ?? '';

  /* ── Modal helper ────────────────────────────────────────────── */
  function openModal(id) {
    const el = document.getElementById(id); if (!el) return;
    const BS = window.bootstrap;
    if (BS?.Modal) {
      let inst = BS.Modal.getInstance(el) ?? new BS.Modal(el);
      inst.show();
    } else { el.classList.add('show'); el.style.display='block'; }
  }
  function closeModal(id) {
    const el = document.getElementById(id); if (!el) return;
    const inst = window.bootstrap?.Modal?.getInstance(el);
    if (inst) inst.hide();
    else { el.classList.remove('show'); el.style.display=''; }
  }

  /* ── Toast ───────────────────────────────────────────────────── */
  function showToast(msg, type='success') {
    const t = document.createElement('div');
    t.className = 'rol-toast rol-toast-' + type;
    t.innerHTML = (type==='success'
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
    ) + msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3000);
  }

  /* ── Dropdown ────────────────────────────────────────────────── */
  window.toggleDropdown = function(id) {
    const dd = document.getElementById('dd-' + id);
    const isOpen = dd.classList.contains('open');
    document.querySelectorAll('.rol-dropdown.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dd.classList.add('open');
  };
  document.addEventListener('click', e => {
    if (!e.target.closest('.rol-dropdown')) {
      document.querySelectorAll('.rol-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });

  /* ── Color preview binding ───────────────────────────────────── */
  function bindColorPreview(inputId, previewId, badgeId) {
    const input   = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const badge   = document.getElementById(badgeId);
    if (!input) return;
    const update = () => {
      const c = input.value;
      if (preview) preview.style.background = c;
      if (badge) { badge.style.background = c; badge.textContent = document.getElementById(inputId.replace('Color','Label'))?.value || 'Preview'; }
    };
    input.addEventListener('input', update);
    update();
  }
  bindColorPreview('addRoleColor', 'addColorPreview', 'addRoleBadgePreview');
  document.getElementById('addRoleLabel')?.addEventListener('input', () => {
    const v = document.getElementById('addRoleLabel').value;
    const b = document.getElementById('addRoleBadgePreview');
    if (b) b.textContent = v || 'Preview';
  });
  bindColorPreview('editRoleColor', 'editColorPreview', 'editRoleBadgePreview');
  document.getElementById('editRoleLabel')?.addEventListener('input', () => {
    const v = document.getElementById('editRoleLabel').value;
    const b = document.getElementById('editRoleBadgePreview');
    if (b) b.textContent = v || 'Preview';
  });

  /* ── Open Add Modal ──────────────────────────────────────────── */
  document.getElementById('btnOpenAddRole').addEventListener('click', () => openModal('modalAddRole'));
  document.getElementById('btnBatalAdd').addEventListener('click',    () => closeModal('modalAddRole'));
  document.getElementById('btnCloseAdd').addEventListener('click',    () => closeModal('modalAddRole'));

  /* ── Open Edit Modal ─────────────────────────────────────────── */
  window.openEditRole = function(id, label, color) {
    document.querySelectorAll('.rol-dropdown.open').forEach(d => d.classList.remove('open'));
    document.getElementById('editRoleLabel').value = label;
    document.getElementById('editRoleColor').value = color;
    document.getElementById('formEditRole').action = BASE + '/roles/' + id + '/update';
    // update preview
    const prev = document.getElementById('editColorPreview');
    const badge = document.getElementById('editRoleBadgePreview');
    if (prev)  prev.style.background  = color;
    if (badge) { badge.style.background = color; badge.textContent = label; }
    openModal('modalEditRole');
  };
  document.getElementById('btnBatalEdit').addEventListener('click',  () => closeModal('modalEditRole'));
  document.getElementById('btnCloseEdit').addEventListener('click',  () => closeModal('modalEditRole'));

  /* ── Delete Role ─────────────────────────────────────────────── */
  let _deleteId = null;
  window.confirmDeleteRole = function(id, label, userCount) {
    document.querySelectorAll('.rol-dropdown.open').forEach(d => d.classList.remove('open'));
    _deleteId = id;
    document.getElementById('deleteRoleLabel').textContent = label;
    const warn = document.getElementById('deleteRoleWarn');
    const cnt  = document.getElementById('deleteRoleUserCount');
    if (userCount > 0) {
      warn.style.display = 'flex';
      cnt.textContent    = userCount + ' pengguna';
    } else {
      warn.style.display = 'none';
    }
    openModal('modalDeleteRole');
  };
  document.getElementById('btnBatalDelete').addEventListener('click',  () => closeModal('modalDeleteRole'));
  document.getElementById('btnCloseDelete').addEventListener('click',  () => closeModal('modalDeleteRole'));
  document.getElementById('btnKonfirmasiDelete').addEventListener('click', async function () {
    if (!_deleteId) return;
    this.disabled = true; this.textContent = 'Menghapus\u2026';
    try {
      const res = await fetch(BASE + '/roles/' + _deleteId + '/delete', {
        method: 'POST',
        headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
      });
      const d = await res.json();
      closeModal('modalDeleteRole');
      if (d.success) {
        document.getElementById('role-card-' + _deleteId)?.remove();
        showToast('Role berhasil dihapus.');
      } else {
        showToast(d.message || 'Gagal menghapus role.', 'error');
      }
    } catch(e) {
      showToast('Terjadi kesalahan: ' + e.message, 'error');
    }
    this.disabled = false; this.textContent = 'Ya, Hapus Role';
  });

  /* ── Dirty tracking ─────────────────────────────────────────── */
  let isDirty = false;
  function markDirty() {
    isDirty = true;
    document.getElementById('dirtyBadge').style.display = 'inline-flex';
    document.getElementById('btnSave').disabled = false;
    document.getElementById('matrixHint').textContent = 'Ada perubahan belum disimpan';
  }
  document.querySelectorAll('.perm-checkbox').forEach(cb => {
    cb.addEventListener('change', markDirty);
  });

  /* ── Select all for role ─────────────────────────────────────── */
  window.selectAllForRole = function(roleId) {
    const boxes = document.querySelectorAll('.perm-checkbox[data-role-id="' + roleId + '"]');
    const allChecked = [...boxes].every(cb => cb.checked);
    boxes.forEach(cb => { cb.checked = !allChecked; });
    markDirty();
  };

  /* ── Save all permissions ────────────────────────────────────── */
  window.saveAllPermissions = async function() {
    const btn     = document.getElementById('btnSave');
    const spinner = document.getElementById('saveSpinner');
    const icon    = document.getElementById('saveIcon');
    btn.disabled          = true;
    spinner.style.display = 'inline-block';
    icon.style.display    = 'none';

    const byRole = {};
    document.querySelectorAll('.perm-checkbox').forEach(cb => {
      const rid = cb.dataset.roleId;
      if (!byRole[rid]) byRole[rid] = [];
      if (cb.checked) byRole[rid].push(cb.dataset.perm);
    });

    try {
      const results = await Promise.all(
        Object.entries(byRole).map(([roleId, perms]) =>
          fetch(BASE + '/api/roles/' + roleId + '/permissions', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify({ permissions: perms })
          }).then(r => r.json().then(d => ({ ...d, roleId })))
        )
      );

      const failed = results.filter(r => !r.success);
      if (failed.length) {
        showToast('Gagal menyimpan: ' + failed.map(r => r.message).join(', '), 'error');
      } else {
        // Update perm count badges on cards
        results.forEach(r => {
          const countEl = document.getElementById('pcount-' + r.roleId);
          if (countEl && r.count !== undefined) countEl.textContent = r.count;
        });
        isDirty = false;
        document.getElementById('dirtyBadge').style.display = 'none';
        document.getElementById('matrixHint').textContent = 'Klik checkbox untuk mengubah permission';
        showToast('Permission berhasil disimpan!');
      }
    } catch (e) {
      showToast('Terjadi kesalahan: ' + e.message, 'error');
    }

    btn.disabled          = false;
    spinner.style.display = 'none';
    icon.style.display    = '';
    if (!isDirty) btn.disabled = true;
  };

  /* ── Warn before leave if dirty ─────────────────────────────── */
  window.addEventListener('beforeunload', e => {
    if (isDirty) { e.preventDefault(); e.returnValue = ''; }
  });

}());
</script>
