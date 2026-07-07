<?php
$baseUrl    = rtrim(BASE_URL, '/');
$csrfToken  = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES);
$levelLabel = [1 => 'Unit Kerja', 2 => 'Bidang / Bagian', 3 => 'Sub Bidang'];
$levelColor = [
    1 => ['badge' => 'dept-badge-red',   'accent' => '#7B1C1C', 'bg' => 'rgba(123,28,28,.08)'],
    2 => ['badge' => 'dept-badge-blue',  'accent' => '#1B4F82', 'bg' => 'rgba(27,79,130,.08)'],
    3 => ['badge' => 'dept-badge-green', 'accent' => '#2A6B3A', 'bg' => 'rgba(42,107,58,.08)'],
];

function countNodes(array $nodes): int {
    $c = 0;
    foreach ($nodes as $n) {
        $c++;
        if (!empty($n['children'])) $c += countNodes($n['children']);
    }
    return $c;
}
$totalDepts  = countNodes($tree ?? []);
$level1Count = count($tree ?? []);
$level2Count = 0; $level3Count = 0;
foreach ($tree ?? [] as $l1) {
    $level2Count += count($l1['children'] ?? []);
    foreach ($l1['children'] ?? [] as $l2) {
        $level3Count += count($l2['children'] ?? []);
    }
}
?>

<!-- BUG FIX #1: CSRF inline JS -->
<script>var _DEPT_CSRF = '<?= $csrfToken ?>';</script>

<style>
:root {
  --dp-primary:       #7B1C1C;
  --dp-primary-dark:  #5A1212;
  --dp-primary-light: rgba(123,28,28,.08);
  --dp-primary-ring:  rgba(123,28,28,.18);
  --dp-gold:          #C9A84C;
  --dp-gold-dark:     #A8872F;
  --dp-surface:       #FBF8F3;
  --dp-surface-2:     #F5F0E8;
  --dp-border:        #DDD5C4;
  --dp-border-light:  #EDE8DE;
  --dp-text:          #1C1714;
  --dp-text-muted:    #6B6055;
  --dp-text-faint:    #A89E90;
  --dp-red:           #7B1C1C;
  --dp-red-bg:        rgba(123,28,28,.08);
  --dp-blue:          #1B4F82;
  --dp-blue-bg:       rgba(27,79,130,.08);
  --dp-green:         #2A6B3A;
  --dp-green-bg:      rgba(42,107,58,.08);
  --dp-radius:        12px;
  --dp-radius-sm:     8px;
  --dp-radius-xs:     6px;
  --dp-shadow-sm:     0 1px 4px rgba(28,23,20,.07);
  --dp-shadow-md:     0 3px 12px rgba(28,23,20,.09);
  --dp-transition:    180ms cubic-bezier(.16,1,.3,1);
}
.dp-wrap * { box-sizing: border-box; }

/* ── Hero ─────────────────────────────────────────────────── */
.dp-hero {
  background: linear-gradient(135deg,#7B1C1C 0%,#9B2020 50%,#6A1515 100%);
  border-radius: var(--dp-radius);
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative; overflow: hidden;
  margin-bottom: 1.25rem;
}
.dp-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.dp-hero::after {
  content:''; position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.dp-hero-inner {
  padding: 1.25rem 1.5rem 1.1rem;
  position: relative; z-index: 1;
  display: flex; align-items: center;
  justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
.dp-hero-left  { display:flex; align-items:center; gap:.85rem; }
.dp-hero-icon  {
  width:44px; height:44px; border-radius:var(--dp-radius-sm);
  background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.22);
  display:flex; align-items:center; justify-content:center;
  color:rgba(255,255,255,.9); flex-shrink:0;
}
.dp-hero-title { font-size:clamp(16px,2vw,22px); font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.dp-hero-sub   { font-size:12.5px; color:rgba(255,255,255,.65); margin-top:.15rem; }
.dp-hero-bar   { height:3px; background:linear-gradient(90deg,var(--dp-gold) 0%,var(--dp-gold-dark) 60%,transparent 100%); }

/* ── Buttons ──────────────────────────────────────────────── */
.dp-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:700; border-radius:var(--dp-radius-sm);
  padding:.38rem .9rem; cursor:pointer; transition:all var(--dp-transition);
  border:1.5px solid transparent; text-decoration:none; background:none;
}
.dp-btn-primary {
  background:linear-gradient(135deg,var(--dp-primary),#9B2020);
  color:#fff; border-color:var(--dp-primary-dark);
  box-shadow:0 2px 8px rgba(123,28,28,.25);
}
.dp-btn-primary:hover { background:linear-gradient(135deg,#9B2020,var(--dp-primary-dark)); color:#fff; }
.dp-btn-ghost { background:transparent; color:var(--dp-text-muted); border-color:var(--dp-border); }
.dp-btn-ghost:hover { background:var(--dp-primary-light); color:var(--dp-primary); border-color:var(--dp-primary); }
.dp-btn-sm { padding:.28rem .65rem; font-size:11.5px; }

/* ── Stats ────────────────────────────────────────────────── */
.dp-stats {
  display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr));
  gap:.65rem; margin-bottom:1rem;
}
/* UX FIX #8: stat card klikable sebagai filter level */
.dp-stat-card {
  background:#fff; border:1.5px solid var(--dp-border-light);
  border-radius:var(--dp-radius-sm); padding:.75rem 1rem;
  box-shadow:var(--dp-shadow-sm);
  cursor:pointer; transition:all var(--dp-transition);
  user-select:none;
}
.dp-stat-card:hover        { border-color:var(--dp-primary); box-shadow:var(--dp-shadow-md); transform:translateY(-1px); }
.dp-stat-card.active       { border-color:var(--dp-primary); background:var(--dp-primary-light); }
.dp-stat-card.active .dp-stat-label { color:var(--dp-primary); }
.dp-stat-label { font-size:11px; font-weight:700; color:var(--dp-text-faint); text-transform:uppercase; letter-spacing:.06em; }
.dp-stat-value { font-size:22px; font-weight:800; color:var(--dp-text); line-height:1.15; margin-top:.15rem; }
.dp-stat-sub   { font-size:11px; color:var(--dp-text-muted); margin-top:.1rem; }
.dp-stat-card-hint { font-size:10px; color:var(--dp-text-faint); margin-top:.25rem; font-style:italic; }

/* ── Toolbar ──────────────────────────────────────────────── */
.dp-toolbar {
  display:flex; flex-wrap:wrap; align-items:center; gap:.6rem;
  margin-bottom:.85rem;
}
.dp-search-wrap { position:relative; flex:1; min-width:180px; }
.dp-search-ico  { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:var(--dp-text-faint); pointer-events:none; }
.dp-search-input {
  width:100%; border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  padding:.4rem .75rem .4rem 2rem; font-size:13px; color:var(--dp-text);
  background:#fff; outline:none; transition:border-color var(--dp-transition);
}
.dp-search-input:focus { border-color:var(--dp-primary); box-shadow:0 0 0 3px var(--dp-primary-ring); }
.dp-filter-info { font-size:12px; color:var(--dp-text-muted); }
.dp-filter-clear { font-size:12px; color:var(--dp-primary); cursor:pointer; text-decoration:underline; }

/* ── Legend pills ─────────────────────────────────────────── */
.dp-legend { display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1rem; align-items:center; }
.dp-legend-label { font-size:11.5px; color:var(--dp-text-muted); margin-right:.25rem; }

/* ── Badges ───────────────────────────────────────────────── */
.dp-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:10.5px; font-weight:700; padding:.2em .65em;
  border-radius:20px; white-space:nowrap;
}
.dept-badge-red   { background:var(--dp-red-bg);   color:var(--dp-red); }
.dept-badge-blue  { background:var(--dp-blue-bg);  color:var(--dp-blue); }
.dept-badge-green { background:var(--dp-green-bg); color:var(--dp-green); }

/* ── Tree ─────────────────────────────────────────────────── */
.dp-tree { display:flex; flex-direction:column; gap:.5rem; }
.dp-node { position:relative; }
.dp-node-children {
  margin-left:1.5rem; margin-top:.4rem;
  padding-left:1rem;
  border-left:2px solid var(--dp-border);
  display:flex; flex-direction:column; gap:.4rem;
  /* UX FIX #7: animasi collapse smooth */
  overflow:hidden;
  transition:max-height .28s cubic-bezier(.16,1,.3,1), opacity .22s ease;
  max-height:9999px; opacity:1;
}
.dp-node-children.collapsed { max-height:0 !important; opacity:0; pointer-events:none; }

.dp-card {
  background:#fff; border:1px solid var(--dp-border-light);
  border-radius:var(--dp-radius-sm); overflow:hidden;
  box-shadow:var(--dp-shadow-sm);
  transition:box-shadow var(--dp-transition), transform var(--dp-transition);
}
.dp-card:hover { box-shadow:var(--dp-shadow-md); transform:translateY(-1px); }
.dp-card.dp-highlight { outline:2px solid var(--dp-gold); outline-offset:1px; }

/* Level accent stripe */
.dp-card[data-level="1"] { border-left:3.5px solid var(--dp-red); }
.dp-card[data-level="2"] { border-left:3.5px solid var(--dp-blue); }
.dp-card[data-level="3"] { border-left:3.5px solid var(--dp-green); }

/* Dimmed node saat filter aktif */
.dp-node.dp-dimmed { opacity:.25; pointer-events:none; }
.dp-node.dp-matched { opacity:1 !important; pointer-events:auto !important; }

.dp-card-body {
  display:flex; align-items:center; gap:.85rem;
  padding:.65rem .9rem;
}

.dp-node-icon {
  width:36px; height:36px; border-radius:var(--dp-radius-xs);
  display:flex; align-items:center; justify-content:center;
  font-size:12px; font-weight:800; color:#fff; flex-shrink:0;
}
.dp-node-info { flex:1; min-width:0; }
.dp-node-name {
  font-size:13.5px; font-weight:700; color:var(--dp-text);
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
}
.dp-node-meta { font-size:11.5px; color:var(--dp-text-muted); margin-top:.1rem; display:flex; flex-wrap:wrap; gap:.3rem .75rem; }
.dp-node-meta span { display:inline-flex; align-items:center; gap:.2rem; }

/* BUG FIX #2: Toggle menggunakan CSS rotate, bukan ganti innerHTML */
.dp-toggle {
  width:22px; height:22px; border-radius:var(--dp-radius-xs); border:1.5px solid var(--dp-border);
  display:flex; align-items:center; justify-content:center; cursor:pointer;
  color:var(--dp-text-muted); background:#fff; flex-shrink:0;
  transition:all var(--dp-transition);
}
.dp-toggle svg { transition:transform var(--dp-transition); transform:rotate(0deg); }
.dp-toggle:hover { border-color:var(--dp-primary); color:var(--dp-primary); }
.dp-toggle.open  { background:var(--dp-primary-light); border-color:var(--dp-primary); color:var(--dp-primary); }
.dp-toggle.open svg { transform:rotate(180deg); }

/* Action buttons */
.dp-actions { display:flex; gap:.3rem; flex-shrink:0; }
.dp-action-btn {
  display:inline-flex; align-items:center; gap:.2rem;
  font-size:11px; font-weight:700; padding:.25rem .6rem;
  border-radius:var(--dp-radius-xs); cursor:pointer;
  transition:all var(--dp-transition); border:1.5px solid transparent;
  white-space:nowrap; background:none;
}
.da-edit { background:var(--dp-blue-bg); color:var(--dp-blue); border-color:rgba(27,79,130,.18); }
.da-edit:hover { background:var(--dp-blue); color:#fff; }
.da-del  { background:var(--dp-red-bg); color:var(--dp-red); border-color:rgba(123,28,28,.18); }
.da-del:hover { background:var(--dp-red); color:#fff; }

/* UX FIX #5: kebab dropdown di mobile */
.dp-kebab { display:none; position:relative; }
.dp-kebab-btn {
  width:28px; height:28px; border-radius:var(--dp-radius-xs);
  border:1.5px solid var(--dp-border); background:#fff;
  display:flex; align-items:center; justify-content:center;
  cursor:pointer; color:var(--dp-text-muted); transition:all var(--dp-transition);
}
.dp-kebab-btn:hover { border-color:var(--dp-primary); color:var(--dp-primary); }
.dp-kebab-menu {
  position:absolute; right:0; top:calc(100% + 4px); z-index:99;
  background:#fff; border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  box-shadow:var(--dp-shadow-md); min-width:130px; overflow:hidden;
  display:none;
}
.dp-kebab-menu.open { display:block; }
.dp-kebab-item {
  display:flex; align-items:center; gap:.5rem;
  padding:.55rem .85rem; font-size:13px; font-weight:600;
  cursor:pointer; transition:background var(--dp-transition); border:none; background:none; width:100%;
}
.dp-kebab-item:hover { background:var(--dp-primary-light); }
.dp-kebab-item.danger { color:var(--dp-red); }
.dp-kebab-item.danger:hover { background:var(--dp-red-bg); }

/* Empty */
.dp-empty { padding:3.5rem 1rem; text-align:center; color:var(--dp-text-faint); }
.dp-empty-icon { margin:0 auto .75rem; opacity:.45; }
.dp-empty h4 { font-size:15px; color:var(--dp-text-muted); margin:0 0 .3rem; font-weight:700; }
.dp-empty p  { font-size:13px; margin:0; }

/* No-result */
.dp-no-result { padding:2rem 1rem; text-align:center; color:var(--dp-text-faint); font-size:13px; display:none; }

/* Alert */
.dp-alert {
  display:flex; align-items:center; gap:.6rem;
  padding:.65rem 1rem; border-radius:var(--dp-radius-sm);
  font-size:13px; margin-bottom:.85rem; border:1px solid;
}
.dp-alert-success { background:var(--dp-green-bg); color:var(--dp-green); border-color:rgba(42,107,58,.2); }
.dp-alert-danger  { background:var(--dp-red-bg);   color:var(--dp-red);   border-color:rgba(123,28,28,.2); }
.dp-alert-close   { margin-left:auto; cursor:pointer; background:none; border:none; color:inherit; opacity:.6; font-size:16px; line-height:1; padding:0; }
.dp-alert-close:hover { opacity:1; }

/* Modals */
.dp-modal .modal-content {
  border:1px solid var(--dp-border); border-radius:var(--dp-radius);
  box-shadow:var(--dp-shadow-md); overflow:hidden;
}
.dp-modal .modal-header {
  background:var(--dp-surface); border-bottom:1px solid var(--dp-border-light);
  padding:.9rem 1.25rem;
}
.dp-modal .modal-title { font-size:15px; font-weight:800; color:var(--dp-primary); display:flex; align-items:center; gap:.5rem; }
.dp-modal .modal-body   { padding:1.25rem; }
.dp-modal .modal-footer { background:var(--dp-surface); border-top:1px solid var(--dp-border-light); padding:.75rem 1.25rem; gap:.5rem; }
.dp-form-label { font-size:12px; font-weight:700; color:var(--dp-text); display:block; margin-bottom:.3rem; }
.dp-form-label .req { color:var(--dp-red); margin-left:.15rem; }
.dp-form-control {
  width:100%; border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  padding:.42rem .8rem; font-size:13.5px; color:var(--dp-text);
  background:#fff; outline:none;
  transition:border-color var(--dp-transition), box-shadow var(--dp-transition);
}
.dp-form-control:focus { border-color:var(--dp-primary); box-shadow:0 0 0 3px var(--dp-primary-ring); }
/* UX FIX #6: validasi visual */
.dp-form-control.is-invalid { border-color:#dc2626 !important; box-shadow:0 0 0 3px rgba(220,38,38,.15) !important; }
.dp-invalid-msg { font-size:11.5px; color:#dc2626; margin-top:.25rem; display:none; }
.dp-invalid-msg.show { display:block; }
select.dp-form-control { appearance:auto; }
.dp-form-hint { font-size:11px; color:var(--dp-text-faint); margin-top:.2rem; }
.dp-level-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:11.5px; font-weight:700; padding:.3em .8em;
  border-radius:20px; margin-top:.3rem;
}
.dp-modal-danger .modal-header { background:var(--dp-red-bg); border-color:rgba(123,28,28,.18); }
.dp-modal-danger .modal-title  { color:var(--dp-red); }

@media(max-width:767.98px){
  .dp-hero-inner { flex-direction:column; align-items:flex-start; }
  .dp-stats { grid-template-columns:repeat(2,1fr); }
  .dp-node-children { margin-left:.75rem; }
  /* UX FIX #5: tampilkan kebab, sembunyikan tombol aksi normal di mobile */
  .dp-actions { display:none !important; }
  .dp-kebab   { display:block; }
}
</style>

<div class="dp-wrap">

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="dp-alert dp-alert-success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="dp-alert-close" onclick="this.closest('.dp-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="dp-alert dp-alert-danger">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="dp-alert-close" onclick="this.closest('.dp-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- ── Hero ── -->
<div class="dp-hero">
  <div class="dp-hero-inner">
    <div class="dp-hero-left">
      <div class="dp-hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
      </div>
      <div>
        <div class="dp-hero-title">Unit Kerja</div>
        <div class="dp-hero-sub">Kelola struktur organisasi — unit kerja, bidang, dan sub bidang</div>
      </div>
    </div>
    <button class="dp-btn dp-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUnit">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Unit
    </button>
  </div>
  <div class="dp-hero-bar"></div>
</div>

<!-- ── Stats (UX FIX #8: klikable filter level) ── -->
<div class="dp-stats">
  <div class="dp-stat-card" data-filter-level="0" id="stat-all" title="Tampilkan semua level">
    <div class="dp-stat-label">Total Unit</div>
    <div class="dp-stat-value"><?= $totalDepts ?></div>
    <div class="dp-stat-sub">seluruh level</div>
    <div class="dp-stat-card-hint">Klik untuk filter</div>
  </div>
  <div class="dp-stat-card" data-filter-level="1" id="stat-lv1" title="Filter Unit Kerja (Level 1)">
    <div class="dp-stat-label">Unit Kerja</div>
    <div class="dp-stat-value" style="color:var(--dp-red)"><?= $level1Count ?></div>
    <div class="dp-stat-sub">level 1</div>
    <div class="dp-stat-card-hint">Klik untuk filter</div>
  </div>
  <div class="dp-stat-card" data-filter-level="2" id="stat-lv2" title="Filter Bidang / Bagian (Level 2)">
    <div class="dp-stat-label">Bidang / Bagian</div>
    <div class="dp-stat-value" style="color:var(--dp-blue)"><?= $level2Count ?></div>
    <div class="dp-stat-sub">level 2</div>
    <div class="dp-stat-card-hint">Klik untuk filter</div>
  </div>
  <div class="dp-stat-card" data-filter-level="3" id="stat-lv3" title="Filter Sub Bidang (Level 3)">
    <div class="dp-stat-label">Sub Bidang</div>
    <div class="dp-stat-value" style="color:var(--dp-green)"><?= $level3Count ?></div>
    <div class="dp-stat-sub">level 3</div>
    <div class="dp-stat-card-hint">Klik untuk filter</div>
  </div>
</div>

<!-- ── Legend ── -->
<div class="dp-legend">
  <span class="dp-legend-label">Level:</span>
  <span class="dp-badge dept-badge-red">
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
    Unit Kerja
  </span>
  <span class="dp-badge dept-badge-blue">
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
    Bidang / Bagian
  </span>
  <span class="dp-badge dept-badge-green">
    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.22 4.22l2.12 2.12M17.66 17.66l2.12 2.12M2 12h3M19 12h3M4.22 19.78l2.12-2.12M17.66 6.34l2.12-2.12"/></svg>
    Sub Bidang
  </span>
</div>

<!-- ── Toolbar: Search + Collapse All / Expand All (UX FIX #3 & #4) ── -->
<div class="dp-toolbar">
  <div class="dp-search-wrap">
    <svg class="dp-search-ico" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" id="dept-search" class="dp-search-input" placeholder="Cari unit kerja…" autocomplete="off">
  </div>
  <span id="dept-filter-info" class="dp-filter-info" style="display:none"></span>
  <span id="dept-filter-clear" class="dp-filter-clear" style="display:none" onclick="clearDeptFilter()">&#x2715; Reset filter</span>
  <button class="dp-btn dp-btn-ghost dp-btn-sm" id="btn-expand-all" title="Expand All">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 13 12 18 17 13"/><polyline points="7 6 12 11 17 6"/></svg>
    Expand All
  </button>
  <button class="dp-btn dp-btn-ghost dp-btn-sm" id="btn-collapse-all" title="Collapse All">
    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="7 11 12 6 17 11"/><polyline points="7 18 12 13 17 18"/></svg>
    Collapse All
  </button>
</div>

<!-- ── Tree ── -->
<?php
$iconColors = [1 => '#7B1C1C', 2 => '#1B4F82', 3 => '#2A6B3A'];

function renderDeptTree(array $nodes, array $levelLabel, array $levelColor, array $iconColors, string $baseUrl, int $depth = 0): void {
    foreach ($nodes as $unit):
        $lbl         = $levelLabel[$unit['level']] ?? 'Unit';
        $lc          = $levelColor[$unit['level']] ?? $levelColor[1];
        $ic          = $iconColors[$unit['level']] ?? '#7B1C1C';
        $initials    = htmlspecialchars($unit['code'] ?? mb_strtoupper(mb_substr($unit['name'], 0, 2)));
        $hasChildren = !empty($unit['children']);
        $uJson       = htmlspecialchars(json_encode($unit, JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_TAG|JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
?>
<div class="dp-node" id="dpnode-<?= $unit['id'] ?>" data-level="<?= $unit['level'] ?>" data-name="<?= htmlspecialchars(mb_strtolower($unit['name'])) ?>">
  <div class="dp-card" data-level="<?= $unit['level'] ?>">
    <div class="dp-card-body">
      <?php if ($hasChildren): ?>
      <!-- BUG FIX #2: toggle pakai CSS rotate via class .open, bukan ganti innerHTML -->
      <button class="dp-toggle open" data-target="dpchildren-<?= $unit['id'] ?>" aria-label="Collapse"
              onclick="toggleChildren(this, 'dpchildren-<?= $unit['id'] ?>')">
        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
      </button>
      <?php else: ?>
      <div style="width:22px;flex-shrink:0;"></div>
      <?php endif; ?>

      <div class="dp-node-icon" style="background:<?= $ic ?>;">
        <?= $initials ?>
      </div>

      <div class="dp-node-info">
        <div class="dp-node-name"><?= htmlspecialchars($unit['name']) ?></div>
        <div class="dp-node-meta">
          <span><span class="dp-badge <?= $lc['badge'] ?>"><?= $lbl ?></span></span>
          <?php if ($unit['head_name']): ?>
          <span>
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($unit['head_name']) ?>
          </span>
          <?php endif; ?>
          <span>
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <?= $unit['total_users'] ?> anggota
          </span>
          <?php if ($hasChildren): ?>
          <span style="color:var(--dp-text-faint);"><?= count($unit['children']) ?> sub-unit</span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Desktop actions -->
      <div class="dp-actions">
        <button class="dp-action-btn da-edit btn-edit-dept" data-unit="<?= $uJson ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </button>
        <button class="dp-action-btn da-del btn-del-dept"
                data-id="<?= $unit['id'] ?>"
                data-name="<?= htmlspecialchars($unit['name']) ?>"
                data-url="<?= $baseUrl ?>/departments/<?= $unit['id'] ?>/delete">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          Hapus
        </button>
      </div>

      <!-- UX FIX #5: Kebab menu mobile -->
      <div class="dp-kebab">
        <button class="dp-kebab-btn" onclick="toggleKebab(this)" aria-label="Aksi">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
        </button>
        <div class="dp-kebab-menu">
          <button class="dp-kebab-item btn-edit-dept" data-unit="<?= $uJson ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit
          </button>
          <button class="dp-kebab-item danger btn-del-dept"
                  data-id="<?= $unit['id'] ?>"
                  data-name="<?= htmlspecialchars($unit['name']) ?>"
                  data-url="<?= $baseUrl ?>/departments/<?= $unit['id'] ?>/delete">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php if ($hasChildren): ?>
  <div class="dp-node-children" id="dpchildren-<?= $unit['id'] ?>">
    <?php renderDeptTree($unit['children'], $levelLabel, $levelColor, $iconColors, $baseUrl, $depth + 1); ?>
  </div>
  <?php endif; ?>
</div>
<?php
    endforeach;
}
?>

<div class="dp-tree" id="dept-tree">
  <?php if (empty($tree)): ?>
  <div class="dp-empty">
    <div class="dp-empty-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
    </div>
    <h4>Belum ada unit kerja</h4>
    <p>Klik <strong>+ Tambah Unit</strong> untuk memulai struktur organisasi.</p>
  </div>
  <?php else: ?>
  <?php renderDeptTree($tree, $levelLabel, $levelColor, $iconColors, $baseUrl, 0); ?>
  <?php endif; ?>
</div>
<div class="dp-no-result" id="dept-no-result">
  <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.4;display:block;margin:0 auto .5rem"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
  Tidak ada unit yang cocok dengan pencarian.
</div>

</div><!-- /.dp-wrap -->

<!-- ================================================================
     MODAL: Hapus
================================================================ -->
<div class="modal modal-blur fade dp-modal dp-modal-danger" id="modalHapusDept" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Hapus Unit Kerja
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:13.5px;color:var(--dp-text);padding:1.25rem;">
        Yakin ingin menghapus <strong id="hapus-dept-nama"></strong>?
        <div style="margin-top:.5rem;font-size:12px;color:var(--dp-red);">&#9888; Sub-unit di dalamnya juga akan dihapus. Tindakan ini tidak dapat dibatalkan.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="dp-btn dp-btn-ghost" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-konfirmasi-hapus-dept" class="dp-btn dp-btn-primary"
                style="background:linear-gradient(135deg,var(--dp-red),#5A1212);">
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Tambah Unit
================================================================ -->
<div class="modal modal-blur fade dp-modal" id="modalAddUnit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/departments" id="formAddUnit" novalidate>
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Unit Kerja
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div style="display:grid;gap:.75rem;">
            <div>
              <label class="dp-form-label">Induk Unit</label>
              <select name="parent_id" id="add-parent" class="dp-form-control" onchange="updateLevelPreview('add')">
                <option value="">— Tidak ada (Level 1: Unit Kerja) —</option>
                <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>" data-level="<?= $p['level'] ?>">
                  <?= str_repeat('　', $p['level'] - 1) ?>[Lv.<?= $p['level'] ?>] <?= htmlspecialchars($p['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div id="add-level-preview" class="dp-form-hint" style="margin-top:.4rem;">
                <span class="dp-level-badge dept-badge-red">Level 1 — Unit Kerja</span>
              </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:start;">
              <div>
                <label class="dp-form-label">Nama <span class="req">*</span></label>
                <input type="text" name="name" id="add-unit-name" class="dp-form-control" required placeholder="cth: Bidang Perencanaan">
                <!-- UX FIX #6: pesan error inline -->
                <div class="dp-invalid-msg" id="add-name-err">Nama wajib diisi.</div>
              </div>
              <div>
                <label class="dp-form-label">Kode</label>
                <input type="text" name="code" class="dp-form-control" maxlength="10" placeholder="BP" style="width:80px;">
              </div>
            </div>
            <div>
              <label class="dp-form-label">Deskripsi</label>
              <input type="text" name="description" class="dp-form-control" placeholder="Opsional">
            </div>
            <div>
              <label class="dp-form-label">Kepala / Penanggung Jawab</label>
              <select name="head_id" class="dp-form-control">
                <option value="">— Pilih Pengguna —</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($allUsers)): ?>
              <!-- UX FIX #9: hint jika allUsers kosong -->
              <div class="dp-form-hint" style="color:#b45309;">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Belum ada pengguna terdaftar. Tambah pengguna terlebih dahulu di menu <a href="<?= $baseUrl ?>/users" style="color:var(--dp-primary);">Pengguna</a>.
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="dp-btn dp-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="dp-btn dp-btn-primary" id="btn-add-submit">Simpan Unit</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Edit Unit
================================================================ -->
<div class="modal modal-blur fade dp-modal" id="modalEditUnit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditUnit" novalidate>
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Unit Kerja
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div style="display:grid;gap:.75rem;">
            <div>
              <label class="dp-form-label">Induk Unit</label>
              <select name="parent_id" id="edit-parent" class="dp-form-control" onchange="updateLevelPreview('edit')">
                <option value="">— Tidak ada (Level 1: Unit Kerja) —</option>
                <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>" data-level="<?= $p['level'] ?>">
                  <?= str_repeat('　', $p['level'] - 1) ?>[Lv.<?= $p['level'] ?>] <?= htmlspecialchars($p['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div id="edit-level-preview" class="dp-form-hint" style="margin-top:.4rem;"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr auto;gap:.75rem;align-items:start;">
              <div>
                <label class="dp-form-label">Nama <span class="req">*</span></label>
                <input type="text" name="name" id="edit-unit-name" class="dp-form-control" required>
                <!-- UX FIX #6: pesan error inline -->
                <div class="dp-invalid-msg" id="edit-name-err">Nama wajib diisi.</div>
              </div>
              <div>
                <label class="dp-form-label">Kode</label>
                <input type="text" name="code" id="edit-unit-code" class="dp-form-control" maxlength="10" style="width:80px;">
              </div>
            </div>
            <div>
              <label class="dp-form-label">Deskripsi</label>
              <input type="text" name="description" id="edit-unit-desc" class="dp-form-control">
            </div>
            <div>
              <label class="dp-form-label">Kepala / Penanggung Jawab</label>
              <select name="head_id" id="edit-unit-head" class="dp-form-control">
                <option value="">— Pilih Pengguna —</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (empty($allUsers)): ?>
              <div class="dp-form-hint" style="color:#b45309;">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Belum ada pengguna. Tambah di <a href="<?= $baseUrl ?>/users" style="color:var(--dp-primary);">Pengguna</a>.
              </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="dp-btn dp-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="dp-btn dp-btn-primary" id="btn-edit-submit">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
const BASE       = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
const LV_NAMES   = <?= json_encode($levelLabel) ?>;
const LV_BADGE   = {1:'dept-badge-red',2:'dept-badge-blue',3:'dept-badge-green'};
// BUG FIX #1: ambil CSRF dari variabel inline
const CSRF       = (typeof _DEPT_CSRF !== 'undefined') ? _DEPT_CSRF : '';

/* ── Safe Bootstrap Modal ─────────────────────────────────── */
function bsModal(el){
  const BS=window.bootstrap;
  if(BS&&BS.Modal)return new BS.Modal(el);
  return{show(){el.classList.add('show');el.style.display='block';document.body.classList.add('modal-open');},
         hide(){el.classList.remove('show');el.style.display='';document.body.classList.remove('modal-open');}};
}
function bsModalGet(el){
  const BS=window.bootstrap;
  if(BS&&BS.Modal)return BS.Modal.getInstance(el)||bsModal(el);
  return{hide(){el.classList.remove('show');el.style.display='';document.body.classList.remove('modal-open');}};
}

/* ── Level preview ────────────────────────────────────────── */
function updateLevelPreview(prefix){
  const sel=document.getElementById(prefix+'-parent');
  const prev=document.getElementById(prefix+'-level-preview');
  const opt=sel.options[sel.selectedIndex];
  const lv=opt.value?(parseInt(opt.dataset.level||1)+1):1;
  const lbl=LV_NAMES[lv]||'Unit';
  const cls=LV_BADGE[lv]||'dept-badge-red';
  prev.innerHTML=`<span class="dp-level-badge dp-badge ${cls}">Level ${lv} — ${lbl}</span>`;
}
window.updateLevelPreview = updateLevelPreview;
updateLevelPreview('add');

/* ── BUG FIX #2: Collapse — CSS rotate, tidak ganti innerHTML ─ */
function toggleChildren(btn, targetId){
  const el=document.getElementById(targetId);
  if(!el)return;
  const isOpen=!el.classList.contains('collapsed');
  el.classList.toggle('collapsed', isOpen);
  btn.classList.toggle('open', !isOpen);
  btn.setAttribute('aria-label', isOpen?'Expand':'Collapse');
}
window.toggleChildren = toggleChildren;

/* ── UX FIX #4: Collapse All / Expand All ────────────────── */
document.getElementById('btn-expand-all').addEventListener('click',function(){
  document.querySelectorAll('.dp-node-children').forEach(function(el){
    el.classList.remove('collapsed');
  });
  document.querySelectorAll('.dp-toggle').forEach(function(btn){
    btn.classList.add('open');
    btn.setAttribute('aria-label','Collapse');
  });
});
document.getElementById('btn-collapse-all').addEventListener('click',function(){
  document.querySelectorAll('.dp-node-children').forEach(function(el){
    el.classList.add('collapsed');
  });
  document.querySelectorAll('.dp-toggle').forEach(function(btn){
    btn.classList.remove('open');
    btn.setAttribute('aria-label','Expand');
  });
});

/* ── UX FIX #3: Search/filter tree real-time ─────────────── */
var _searchQ='', _filterLevel=0;

function applyDeptFilter(){
  var q=_searchQ.toLowerCase().trim();
  var lv=_filterLevel;
  var nodes=document.querySelectorAll('#dept-tree .dp-node');
  var matchCount=0;

  nodes.forEach(function(node){
    var name=node.dataset.name||'';
    var nodeLv=parseInt(node.dataset.level||0);
    var matchSearch=!q||name.includes(q);
    var matchLevel=!lv||nodeLv===lv;
    if(matchSearch&&matchLevel){
      node.classList.remove('dp-dimmed');
      node.classList.add('dp-matched');
      matchCount++;
      // expand parent jika ada query
      if(q||lv){
        var parent=node.closest('.dp-node-children');
        while(parent){
          parent.classList.remove('collapsed');
          var toggle=parent.previousElementSibling&&parent.previousElementSibling.querySelector('.dp-toggle');
          if(toggle)toggle.classList.add('open');
          parent=parent.parentElement?parent.parentElement.closest('.dp-node-children'):null;
        }
      }
    } else {
      node.classList.add('dp-dimmed');
      node.classList.remove('dp-matched');
    }
  });

  var noResult=document.getElementById('dept-no-result');
  var filterInfo=document.getElementById('dept-filter-info');
  var filterClear=document.getElementById('dept-filter-clear');
  var hasFilter=q||lv;

  if(noResult)noResult.style.display=(matchCount===0&&hasFilter)?'block':'none';
  if(filterInfo){
    if(hasFilter){
      filterInfo.style.display='';
      filterInfo.textContent=matchCount+' unit ditemukan';
    } else {
      filterInfo.style.display='none';
    }
  }
  if(filterClear)filterClear.style.display=hasFilter?'':'none';
}

var searchInput=document.getElementById('dept-search');
if(searchInput){
  searchInput.addEventListener('input',function(){
    _searchQ=this.value;
    applyDeptFilter();
  });
}

/* ── UX FIX #8: Stat cards sebagai filter level ──────────── */
document.querySelectorAll('.dp-stat-card').forEach(function(card){
  card.addEventListener('click',function(){
    var lv=parseInt(this.dataset.filterLevel||0);
    var isActive=this.classList.contains('active');
    document.querySelectorAll('.dp-stat-card').forEach(function(c){c.classList.remove('active');});
    if(!isActive&&lv!==0){
      this.classList.add('active');
      _filterLevel=lv;
    } else {
      _filterLevel=0;
    }
    applyDeptFilter();
  });
});

function clearDeptFilter(){
  _searchQ=''; _filterLevel=0;
  if(searchInput)searchInput.value='';
  document.querySelectorAll('.dp-stat-card').forEach(function(c){c.classList.remove('active');});
  applyDeptFilter();
}
window.clearDeptFilter = clearDeptFilter;

/* ── UX FIX #5: Kebab menu mobile ────────────────────────── */
function toggleKebab(btn){
  var menu=btn.nextElementSibling;
  var isOpen=menu.classList.contains('open');
  // tutup semua kebab yang lain
  document.querySelectorAll('.dp-kebab-menu.open').forEach(function(m){m.classList.remove('open');});
  if(!isOpen)menu.classList.add('open');
}
window.toggleKebab = toggleKebab;
// tutup kebab saat klik di luar
document.addEventListener('click',function(e){
  if(!e.target.closest('.dp-kebab')){
    document.querySelectorAll('.dp-kebab-menu.open').forEach(function(m){m.classList.remove('open');});
  }
});

/* ── UX FIX #6: Validasi client-side inline ──────────────── */
function validateDeptForm(formId, nameInputId, nameErrId){
  var form=document.getElementById(formId);
  var input=document.getElementById(nameInputId);
  var errMsg=document.getElementById(nameErrId);
  if(!form||!input)return;
  form.addEventListener('submit',function(e){
    var valid=true;
    if(!input.value.trim()){
      input.classList.add('is-invalid');
      if(errMsg)errMsg.classList.add('show');
      valid=false;
    } else {
      input.classList.remove('is-invalid');
      if(errMsg)errMsg.classList.remove('show');
    }
    if(!valid)e.preventDefault();
  });
  input.addEventListener('input',function(){
    if(this.value.trim()){
      this.classList.remove('is-invalid');
      if(errMsg)errMsg.classList.remove('show');
    }
  });
}
validateDeptForm('formAddUnit', 'add-unit-name', 'add-name-err');
validateDeptForm('formEditUnit','edit-unit-name','edit-name-err');

/* ── Edit ─────────────────────────────────────────────────── */
function bindEditBtns(){
  document.querySelectorAll('.btn-edit-dept').forEach(function(btn){
    if(btn._bound)return; btn._bound=true;
    btn.addEventListener('click',function(){
      var d;
      try{d=JSON.parse(this.getAttribute('data-unit'));}
      catch(e){alert('Gagal membaca data unit.');return;}
      document.getElementById('edit-unit-name').value=d.name        ||'';
      document.getElementById('edit-unit-code').value=d.code        ||'';
      document.getElementById('edit-unit-desc').value=d.description ||'';
      document.getElementById('edit-unit-head').value=d.head_id     ||'';
      document.getElementById('edit-parent').value   =d.parent_id   ||'';
      document.getElementById('formEditUnit').action =BASE+'/departments/'+d.id+'/update';
      updateLevelPreview('edit');
      // reset validasi
      document.getElementById('edit-unit-name').classList.remove('is-invalid');
      document.getElementById('edit-name-err').classList.remove('show');
      bsModal(document.getElementById('modalEditUnit')).show();
    });
  });
}
bindEditBtns();

/* ── BUG FIX #1 + Hapus ──────────────────────────────────── */
var hapusDeptUrl='', hapusDeptId='';
function bindDelBtns(){
  document.querySelectorAll('.btn-del-dept').forEach(function(btn){
    if(btn._bound)return; btn._bound=true;
    btn.addEventListener('click',function(){
      hapusDeptUrl=this.dataset.url;
      hapusDeptId =this.dataset.id;
      document.getElementById('hapus-dept-nama').textContent=this.dataset.name;
      bsModal(document.getElementById('modalHapusDept')).show();
    });
  });
}
bindDelBtns();

document.getElementById('btn-konfirmasi-hapus-dept').addEventListener('click',async function(){
  var btn=this;
  btn.disabled=true; btn.textContent='Menghapus\u2026';
  try{
    // BUG FIX #1: sertakan CSRF di header dan body
    var res=await fetch(hapusDeptUrl,{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF},
      body:JSON.stringify({_csrf:CSRF})
    });
    var d=await res.json();
    bsModalGet(document.getElementById('modalHapusDept')).hide();
    if(d.success){
      var node=document.getElementById('dpnode-'+hapusDeptId);
      if(node)node.remove();
    } else {
      alert(d.message||'Gagal menghapus unit');
    }
  }catch(e){
    alert('Terjadi kesalahan jaringan.');
  }finally{
    btn.disabled=false; btn.textContent='Ya, Hapus';
  }
});

})();
</script>
