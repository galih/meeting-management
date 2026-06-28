<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusIcon = [
  'scheduled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];

// ── Resolve departemen chain ──────────────────────────────────────
$selDeptId = (int)($meeting['department_id'] ?? 0);
$selDept   = $selDeptId
  ? Database::queryOne('SELECT id, name, level, parent_id FROM departments WHERE id = ?', [$selDeptId])
  : null;

$sel = [1 => 0, 2 => 0, 3 => 0];
if ($selDept) {
  $sel[$selDept['level']] = (int)$selDept['id'];
  if ($selDept['level'] > 1) {
    $par = Database::queryOne('SELECT id, level, parent_id FROM departments WHERE id = ?', [$selDept['parent_id']]);
    if ($par) {
      $sel[$par['level']] = (int)$par['id'];
      if ($par['level'] > 1) {
        $par2 = Database::queryOne('SELECT id, level FROM departments WHERE id = ?', [$par['parent_id']]);
        if ($par2) $sel[$par2['level']] = (int)$par2['id'];
      }
    }
  }
}

$deptByParent = [];
foreach (($departments ?? []) as $d) {
  $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
}

$colorPresets   = ['#7B1C1C','#1a6e9b','#2d7a2d','#8b5e00','#6b2fa0','#c0392b','#2c7a6e','#5a5a5a'];
$currentColor   = strtolower(trim($meeting['color'] ?? '#7b1c1c'));
$participantIds = array_map('intval', $participantIds ?? []);
$allUsers       = $allUsers ?? [];
$avPalette      = ['#7B1C1C','#1a6e9b','#2d7a2d','#6b2fa0','#8b5e00','#2c7a6e'];
$flashError     = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
$childrenUrl    = $baseUrl . '/api/departments/children';
$curStatus      = $meeting['status'] ?? 'scheduled';
?>

<!-- ══ STYLES ═══════════════════════════════════════════════════════ -->
<style>
/* ── Variabel palet kemenbud (selaras index.php) ── */
:root {
  --ed-maroon:      #7B1C1C;
  --ed-maroon-dark: #5a1414;
  --ed-maroon-mid:  #9B2020;
  --ed-maroon-lite: rgba(123,28,28,.08);
  --ed-maroon-ring: rgba(123,28,28,.18);
  --ed-gold:        #C9A84C;
  --ed-warm-bg:     #faf6ef;
  --ed-warm-bg2:    #faf4eb;
  --ed-border:      #dcd5c8;
  --ed-border-lite: #ede8e0;
  --ed-text:        #2c1a1a;
  --ed-muted:       #8c7a6b;
  --ed-faint:       #bfb3a8;
  --ed-error:       #a82515;
  --ed-error-bg:    rgba(168,37,21,.07);
  --ed-error-ring:  rgba(168,37,21,.15);
  --ed-success:     #2d7a2d;
  --ed-radius:      10px;
  --ed-radius-sm:   7px;
  --ed-shadow-sm:   0 1px 3px rgba(44,26,26,.06);
  --ed-shadow-md:   0 4px 16px rgba(44,26,26,.09);
}

/* ── Toast ── */
.ed-toast {
  position: fixed; top: 1rem; right: 1rem; z-index: 9999;
  display: flex; align-items: center; gap: .55rem;
  padding: .7rem 1rem; border-radius: var(--ed-radius-sm);
  font-size: 13.5px; font-weight: 500; max-width: 360px;
  box-shadow: var(--ed-shadow-md); animation: edToastIn .25s ease;
}
@keyframes edToastIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }
.ed-toast-err  { background:#fff1f0; border:1.5px solid #ffd6d0; color: var(--ed-error); }
.ed-toast-ok   { background:#f0faf0; border:1.5px solid #c3e6c3; color: var(--ed-success); }
.ed-toast-close { background:none; border:none; font-size:16px; cursor:pointer; color:inherit; opacity:.6; padding:0; line-height:1; margin-left:auto; }
.ed-toast-close:hover { opacity:1; }

/* ── Hero ── */
.ed-hero {
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
  padding: 1.35rem 1.75rem;
  border-radius: var(--ed-radius) var(--ed-radius) 0 0;
  background: linear-gradient(135deg, var(--ed-maroon) 0%, var(--ed-maroon-mid) 55%, #A83218 100%);
  transition: background .3s ease;
}
.ed-hero-left { display:flex; align-items:center; gap: .85rem; min-width:0; }
.ed-hero-icon {
  width:44px; height:44px; border-radius:10px; flex-shrink:0;
  background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
  color:#fff;
}
.ed-hero-title { font-size:17px; font-weight:700; color:#fff; line-height:1.2; margin:0; }
.ed-hero-sub   { font-size:12.5px; color:rgba(255,255,255,.72); margin:.15rem 0 0; }
.ed-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:11.5px; color:rgba(255,255,255,.6); margin-bottom:.2rem;
}
.ed-breadcrumb a { color:rgba(255,255,255,.8); text-decoration:none; }
.ed-breadcrumb a:hover { color:#fff; }
.ed-breadcrumb svg { flex-shrink:0; }
.ed-back-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:600;
  background:rgba(255,255,255,.14); border:1.5px solid rgba(255,255,255,.28);
  color:#fff; padding:.42rem .9rem; border-radius:8px;
  text-decoration:none; transition:background .18s; white-space:nowrap; flex-shrink:0;
}
.ed-back-btn:hover { background:rgba(255,255,255,.26); color:#fff; }

/* ── Panel wrapper ── */
.ed-panel {
  background:#fff; border:1.5px solid var(--ed-border-lite);
  border-top:none; border-radius:0 0 var(--ed-radius) var(--ed-radius);
  box-shadow: var(--ed-shadow-md); overflow:hidden;
}

/* ── Step nav ── */
.ed-steps {
  display:flex; border-bottom:1.5px solid var(--ed-border-lite);
  background: var(--ed-warm-bg); overflow-x:auto; scrollbar-width:none;
}
.ed-steps::-webkit-scrollbar { display:none; }
.ed-step {
  display:flex; align-items:center; gap:.45rem;
  padding:.72rem 1.15rem; font-size:12.5px; font-weight:600;
  color: var(--ed-muted); border:none; background:none; cursor:pointer;
  border-bottom:2.5px solid transparent; white-space:nowrap;
  transition:color .18s, border-color .18s;
  font-family:inherit;
}
.ed-step:hover { color: var(--ed-text); }
.ed-step.active { color: var(--ed-maroon); border-bottom-color: var(--ed-maroon); }
.ed-step-num {
  width:20px; height:20px; border-radius:50%;
  background: var(--ed-border-lite); color: var(--ed-muted);
  font-size:11px; font-weight:800;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
  transition:background .18s, color .18s;
}
.ed-step.active   .ed-step-num { background: var(--ed-maroon); color:#fff; }
.ed-step.complete .ed-step-num { background: var(--ed-success); color:#fff; }
.ed-step.complete { color: var(--ed-success); }

/* ── Step pages ── */
.ed-page { display:none; padding:1.5rem 1.75rem; }
.ed-page.active { display:block; }

/* ── Section header inside page ── */
.ed-sec-head {
  display:flex; align-items:center; gap:.55rem;
  margin-bottom:1rem; padding-bottom:.6rem;
  border-bottom:1.5px solid var(--ed-border-lite);
}
.ed-sec-icon {
  width:28px; height:28px; border-radius:7px;
  background: var(--ed-maroon-lite);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; color: var(--ed-maroon);
}
.ed-sec-label {
  font-size:10.5px; font-weight:700; letter-spacing:.07em;
  text-transform:uppercase; color: var(--ed-maroon);
}

/* ── Grid ── */
.ed-grid {
  display:grid; grid-template-columns:1fr 1fr; gap:.85rem;
}
.ed-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.ed-full   { grid-column:1/-1; }

/* ── Field ── */
.ed-field { display:flex; flex-direction:column; gap:.28rem; }
.ed-lbl {
  font-size:12px; font-weight:700; color: var(--ed-text);
  display:flex; align-items:center; gap:.3rem;
}
.ed-lbl .req { color: var(--ed-maroon); }
.ed-opt { font-weight:400; color: var(--ed-faint); font-size:11px; }

/* ── Inputs ── */
.ed-input, .ed-select, .ed-textarea {
  width:100%; padding:.52rem .75rem;
  border:1.5px solid var(--ed-border);
  border-radius: var(--ed-radius-sm);
  font-size:13.5px; font-family:inherit;
  color: var(--ed-text); background:#fff;
  transition:border-color .18s, box-shadow .18s;
  outline:none;
}
.ed-input:focus, .ed-select:focus, .ed-textarea:focus {
  border-color: var(--ed-maroon);
  box-shadow:0 0 0 3px var(--ed-maroon-ring);
}
.ed-input::placeholder, .ed-textarea::placeholder { color: var(--ed-faint); }
.ed-textarea { resize:vertical; min-height:90px; line-height:1.55; }
.ed-select { appearance:none; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%238c7a6b' stroke-width='2.5'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right .75rem center; padding-right:2rem;
}
.ed-select:disabled { background-color: var(--ed-warm-bg); color: var(--ed-faint); cursor:not-allowed; }

/* icon inside input */
.ed-ico-wrap { position:relative; }
.ed-ico {
  position:absolute; left:.65rem; top:50%; transform:translateY(-50%);
  color: var(--ed-muted); pointer-events:none;
}
.ed-ico-input { padding-left:2.05rem; }

/* ── Validation ── */
.ed-input.invalid, .ed-select.invalid, .ed-textarea.invalid {
  border-color: var(--ed-error) !important;
  box-shadow:0 0 0 3px var(--ed-error-ring) !important;
}
.ed-errmsg { font-size:11.5px; color: var(--ed-error); display:none; }
.invalid + .ed-errmsg,
.invalid ~ .ed-errmsg { display:block; }
.ed-hint { font-size:11.5px; color: var(--ed-muted); }

/* ── Status pills for select ── */
.ed-status-indicator {
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.2rem .65rem; border-radius:999px;
  font-size:11.5px; font-weight:600;
}
.st-scheduled { background:#e8f0fb; color:#1a6e9b; }
.st-ongoing   { background:#fef6e4; color:#8b5e00; }
.st-done      { background:#e8f5e8; color:#2d7a2d; }
.st-cancelled { background:#fde8e8; color:#a82515; }

/* ── Status select wrapper ── */
.ed-status-wrap { position:relative; }
.ed-status-pill {
  position:absolute; left:.65rem; top:50%; transform:translateY(-50%);
  pointer-events:none; display:flex; align-items:center; gap:.3rem;
}

/* ── Dept cascade ── */
.ed-dept-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:.85rem; }

/* ── Participants ── */
.ed-p-wrap {
  border:1.5px solid var(--ed-border); border-radius: var(--ed-radius-sm);
  overflow:hidden;
}
.ed-p-search {
  display:flex; align-items:center; gap:.4rem;
  padding:.5rem .8rem; background: var(--ed-warm-bg2);
  border-bottom:1px solid var(--ed-border-lite);
}
.ed-p-search svg { flex-shrink:0; color: var(--ed-muted); }
.ed-p-search input {
  border:none; background:none; outline:none;
  font-size:13px; width:100%; color: var(--ed-text); font-family:inherit;
}
.ed-p-search input::placeholder { color: var(--ed-faint); }
.ed-p-clr {
  background:none; border:none; font-size:15px; cursor:pointer;
  color: var(--ed-muted); line-height:1; padding:0; display:none;
  transition:color .15s;
}
.ed-p-clr:hover { color: var(--ed-text); }
.ed-p-list {
  max-height:230px; overflow-y:auto; padding:.35rem 0;
  scrollbar-width:thin; scrollbar-color:var(--ed-border-lite) transparent;
}
.ed-p-item {
  display:flex; align-items:center; gap:.55rem;
  padding:.42rem .8rem; cursor:pointer;
  transition:background .15s;
}
.ed-p-item:hover { background: var(--ed-warm-bg); }
.ed-p-item input { accent-color: var(--ed-maroon); cursor:pointer; flex-shrink:0; }
.ed-p-av {
  width:26px; height:26px; border-radius:50%;
  font-size:10.5px; font-weight:800; color:#fff;
  display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ed-p-name { font-size:13px; color: var(--ed-text); flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ed-p-dept { font-size:11px; color: var(--ed-muted); white-space:nowrap; }
.ed-p-item:has(input:checked) .ed-p-name { color: var(--ed-maroon); font-weight:600; }
.ed-p-item:has(input:checked) { background: rgba(123,28,28,.04); }
.ed-p-foot {
  display:flex; align-items:center; justify-content:space-between;
  padding:.4rem .8rem;
  background: var(--ed-warm-bg2); border-top:1px solid var(--ed-border-lite);
}
.ed-p-count { font-size:12px; color: var(--ed-muted); }
.ed-p-count strong { color: var(--ed-maroon); }
.ed-p-desel {
  background:none; border:none; font-size:11.5px;
  color: var(--ed-error); cursor:pointer; font-family:inherit; font-weight:600; padding:0;
}
.ed-p-desel:hover { text-decoration:underline; }
.ed-p-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  background: var(--ed-maroon-lite); color: var(--ed-maroon);
  font-size:11.5px; font-weight:700; padding:.15rem .55rem;
  border-radius:999px; margin-left:.4rem;
}
.ed-p-empty { padding:1.5rem; text-align:center; color: var(--ed-muted); font-size:13px; }

/* ── Color picker ── */
.ed-color-row { display:flex; align-items:center; gap:.65rem; flex-wrap:wrap; margin-top:.5rem; }
.ed-color-input {
  width:36px; height:36px; padding:2px; border-radius:8px;
  border:1.5px solid var(--ed-border); cursor:pointer;
  background:none; flex-shrink:0;
}
.ed-presets { display:flex; gap:.4rem; flex-wrap:wrap; }
.ed-preset {
  width:26px; height:26px; border-radius:6px; border:2px solid transparent;
  cursor:pointer; transition:transform .15s, border-color .15s;
  box-shadow:0 1px 3px rgba(0,0,0,.18);
}
.ed-preset:hover { transform:scale(1.12); }
.ed-preset.active { border-color:#fff; box-shadow:0 0 0 2.5px var(--ed-maroon); }
.ed-color-preview {
  display:flex; align-items:center; gap:.45rem;
  padding:.35rem .65rem; border-radius: var(--ed-radius-sm);
  background: var(--ed-warm-bg); border:1px solid var(--ed-border-lite);
  margin-top:.5rem; width:fit-content;
}
.ed-color-dot  { width:14px; height:14px; border-radius:50%; border:1px solid rgba(0,0,0,.12); flex-shrink:0; }
.ed-color-hex  { font-size:12px; color: var(--ed-muted); font-family:monospace; }
.ed-color-cal  { font-size:11.5px; color: var(--ed-faint); }

/* ── Step nav buttons ── */
.ed-nav {
  display:flex; align-items:center; justify-content:space-between;
  padding:1rem 1.75rem;
  background: var(--ed-warm-bg); border-top:1.5px solid var(--ed-border-lite);
}
.ed-nav-left, .ed-nav-right { display:flex; gap:.55rem; }
.ed-btn-prev {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.5rem 1.05rem; border-radius:8px;
  font-size:13px; font-weight:600; cursor:pointer; font-family:inherit;
  background:#fff; border:1.5px solid var(--ed-border);
  color: var(--ed-muted); transition:all .18s;
}
.ed-btn-prev:hover { border-color: var(--ed-maroon); color: var(--ed-maroon); }
.ed-btn-next {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.5rem 1.25rem; border-radius:8px;
  font-size:13px; font-weight:600; cursor:pointer; font-family:inherit;
  background: var(--ed-maroon); border:1.5px solid var(--ed-maroon);
  color:#fff; transition:background .18s;
}
.ed-btn-next:hover { background: var(--ed-maroon-dark); }
.ed-btn-cancel {
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.5rem 1.05rem; border-radius:8px;
  font-size:13px; font-weight:600; text-decoration:none;
  background:#fff; border:1.5px solid var(--ed-border);
  color: var(--ed-muted); transition:all .18s;
}
.ed-btn-cancel:hover { border-color: var(--ed-maroon); color: var(--ed-maroon); }
.ed-btn-save {
  display:inline-flex; align-items:center; gap:.38rem;
  padding:.52rem 1.3rem; border-radius:8px;
  font-size:13px; font-weight:700; cursor:pointer; font-family:inherit;
  background: var(--ed-maroon); border:none; color:#fff;
  transition:background .18s; box-shadow:0 2px 8px rgba(123,28,28,.28);
}
.ed-btn-save:hover { background: var(--ed-maroon-dark); }
.ed-btn-save:disabled { opacity:.65; cursor:not-allowed; }
.ed-spinner {
  display:inline-block; width:13px; height:13px;
  border:2px solid rgba(255,255,255,.35); border-top-color:#fff;
  border-radius:50%; animation:edSpin .6s linear infinite;
}
@keyframes edSpin { to { transform:rotate(360deg); } }

/* ── Summary sidebar on step 3 ── */
.ed-summary {
  background: var(--ed-warm-bg); border:1.5px solid var(--ed-border-lite);
  border-radius: var(--ed-radius-sm); padding:.85rem 1rem;
  font-size:12.5px; display:flex; flex-direction:column; gap:.45rem;
}
.ed-sum-row { display:flex; align-items:flex-start; gap:.5rem; color: var(--ed-text); }
.ed-sum-ico { color: var(--ed-muted); flex-shrink:0; margin-top:1px; }
.ed-sum-key { color: var(--ed-muted); white-space:nowrap; }
.ed-sum-val { font-weight:600; min-width:0; }

/* ── Responsive ── */
@media (max-width:900px) {
  .ed-dept-grid { grid-template-columns:1fr 1fr; }
}
@media (max-width:640px) {
  .ed-hero { padding:1rem 1.1rem; border-radius: var(--ed-radius) var(--ed-radius) 0 0; }
  .ed-page { padding:1.1rem 1rem; }
  .ed-nav  { padding:.85rem 1rem; }
  .ed-grid, .ed-grid-3, .ed-dept-grid { grid-template-columns:1fr !important; }
  .ed-full { grid-column:span 1; }
  .ed-step span { display:none; }
  .ed-back-btn { width:100%; justify-content:center; margin-top:.5rem; }
  .ed-hero { flex-direction:column; align-items:flex-start; }
  .ed-steps { gap:0; }
}
</style>

<!-- ══ FLASH TOAST ════════════════════════════════════════════════════ -->
<?php if ($flashError): ?>
<div class="ed-toast ed-toast-err" id="edToast">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button class="ed-toast-close" onclick="this.closest('.ed-toast').remove()">×</button>
</div>
<?php endif; ?>

<!-- ══ HERO ═══════════════════════════════════════════════════════════ -->
<div class="ed-hero" id="edHero" style="background:linear-gradient(135deg,<?= htmlspecialchars($currentColor) ?> 0%,#9B2020 55%,#A83218 100%)">
  <div class="ed-hero-left">
    <div class="ed-hero-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </div>
    <div>
      <nav class="ed-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>"><?= htmlspecialchars(mb_strimwidth($meeting['title'],0,32,'…')) ?></a>
        <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Edit</span>
      </nav>
      <h1 class="ed-hero-title">Edit Kegiatan</h1>
      <p class="ed-hero-sub">Perbarui informasi, peserta, dan tampilan kegiatan</p>
    </div>
  </div>
  <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-back-btn">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail
  </a>
</div>

<!-- ══ PANEL ══════════════════════════════════════════════════════════ -->
<div class="ed-panel">
  <form method="POST"
        action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/update"
        id="edForm" novalidate>
    <?= Auth::csrfField() ?>

    <!-- Step Tabs -->
    <div class="ed-steps" role="tablist">
      <button type="button" class="ed-step active" data-step="1" role="tab" aria-selected="true">
        <span class="ed-step-num">1</span>
        <span>Informasi Dasar</span>
      </button>
      <button type="button" class="ed-step" data-step="2" role="tab" aria-selected="false">
        <span class="ed-step-num">2</span>
        <span>Unit &amp; Peserta</span>
      </button>
      <button type="button" class="ed-step" data-step="3" role="tab" aria-selected="false">
        <span class="ed-step-num">3</span>
        <span>Tampilan &amp; Simpan</span>
      </button>
    </div>

    <!-- ══ STEP 1 — Informasi Dasar ════════════════════════════════ -->
    <div class="ed-page active" id="edPage1" role="tabpanel">

      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="ed-sec-label">Informasi Dasar</span>
      </div>

      <div class="ed-grid">
        <!-- Judul -->
        <div class="ed-field ed-full">
          <label class="ed-lbl" for="fTitle">Judul Kegiatan <span class="req">*</span></label>
          <input type="text" id="fTitle" name="title" class="ed-input" maxlength="255"
                 autocomplete="off" required placeholder="Contoh: Rapat Evaluasi Bulanan Q2"
                 value="<?= htmlspecialchars($meeting['title']) ?>">
          <span class="ed-errmsg">Judul kegiatan wajib diisi.</span>
        </div>

        <!-- Mulai -->
        <div class="ed-field">
          <label class="ed-lbl" for="fStart">Mulai <span class="req">*</span></label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fStart" name="start_datetime"
                   class="ed-input ed-ico-input" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
          </div>
          <span class="ed-errmsg">Waktu mulai wajib diisi.</span>
        </div>

        <!-- Selesai -->
        <div class="ed-field">
          <label class="ed-lbl" for="fEnd">Selesai <span class="req">*</span></label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fEnd" name="end_datetime"
                   class="ed-input ed-ico-input" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
          </div>
          <span class="ed-errmsg">Waktu selesai wajib diisi &amp; harus setelah waktu mulai.</span>
        </div>

        <!-- Status -->
        <div class="ed-field">
          <label class="ed-lbl" for="fStatus">Status <span class="req">*</span></label>
          <select id="fStatus" name="status" class="ed-select" required>
            <?php foreach ($statusLabel as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= $curStatus === $val ? 'selected' : '' ?>>
              <?= htmlspecialchars($statusIcon[$val] ?? '') ?> <?= htmlspecialchars($lbl) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <span class="ed-errmsg">Status wajib dipilih.</span>
        </div>

        <!-- Lokasi -->
        <div class="ed-field">
          <label class="ed-lbl" for="fLocation">Lokasi / Tautan Video</label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <input type="text" id="fLocation" name="location"
                   class="ed-input ed-ico-input"
                   placeholder="Ruang Rapat A  atau  https://meet.google.com/…"
                   value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
          </div>
          <span class="ed-hint">URL akan ditampilkan sebagai tautan di halaman detail.</span>
        </div>

        <!-- Deskripsi -->
        <div class="ed-field ed-full">
          <label class="ed-lbl" for="fDesc">
            Deskripsi / Agenda
            <span class="ed-opt">(opsional)</span>
          </label>
          <textarea id="fDesc" name="description" class="ed-textarea"
                    placeholder="Tulis poin-poin agenda kegiatan…"><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <!-- ══ STEP 2 — Unit Kerja & Peserta ════════════════════════════ -->
    <div class="ed-page" id="edPage2" role="tabpanel">

      <!-- Unit Kerja -->
      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <span class="ed-sec-label">Unit Kerja <span class="ed-opt">(opsional)</span></span>
      </div>

      <div class="ed-dept-grid" style="margin-bottom:1.4rem">
        <div class="ed-field">
          <label class="ed-lbl" for="fU1">Unit Kerja</label>
          <select id="fU1" name="_u1" class="ed-select" onchange="edCascade(1)">
            <option value="">— Semua Unit Kerja —</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[1] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ed-field">
          <label class="ed-lbl" for="fU2">Bidang / Bagian</label>
          <select id="fU2" name="_u2" class="ed-select" onchange="edCascade(2)"
                  <?= $sel[1] ? '' : 'disabled' ?>>
            <option value="">— Semua Bidang —</option>
            <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[2] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ed-field">
          <label class="ed-lbl" for="fU3">Sub Bidang <span class="ed-opt">(opsional)</span></label>
          <select id="fU3" name="_u3" class="ed-select" onchange="edCascade(3)"
                  <?= $sel[2] ? '' : 'disabled' ?>>
            <option value="">— Opsional —</option>
            <?php foreach ($deptByParent[$sel[2]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[3] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <input type="hidden" id="fDeptId" name="department_id" value="<?= $selDeptId ?: '' ?>">

      <!-- Peserta -->
      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <span class="ed-sec-label">
          Pilih Peserta
          <span class="ed-p-badge" id="edPBadge">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <span id="edPNum"><?= count($participantIds) ?></span>
          </span>
        </span>
      </div>

      <div class="ed-p-wrap">
        <!-- Search -->
        <div class="ed-p-search">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="edPSearch" placeholder="Cari nama peserta…" autocomplete="off" aria-label="Cari peserta">
          <button type="button" id="edPClr" class="ed-p-clr" title="Hapus pencarian">×</button>
        </div>
        <!-- List -->
        <div class="ed-p-list" id="edPList">
          <?php if (empty($allUsers)): ?>
          <div class="ed-p-empty">Tidak ada pengguna aktif.</div>
          <?php else: ?>
          <?php foreach ($allUsers as $u):
            $avBg = $avPalette[abs(crc32($u['name'])) % count($avPalette)];
          ?>
          <label class="ed-p-item">
            <input type="checkbox" name="participants[]"
                   value="<?= (int)$u['id'] ?>"
                   <?= in_array((int)$u['id'], $participantIds) ? 'checked' : '' ?>>
            <span class="ed-p-av" style="background:<?= $avBg ?>"><?= strtoupper(mb_substr($u['name'],0,1)) ?></span>
            <span class="ed-p-name"><?= htmlspecialchars($u['name']) ?></span>
            <?php if (!empty($u['dept_name'])): ?>
            <span class="ed-p-dept"><?= htmlspecialchars($u['dept_name']) ?></span>
            <?php endif; ?>
          </label>
          <?php endforeach; ?>
          <div class="ed-p-empty" id="edPEmpty" style="display:none">Tidak ada peserta yang cocok.</div>
          <?php endif; ?>
        </div>
        <!-- Footer -->
        <div class="ed-p-foot">
          <span class="ed-p-count"><strong id="edPCount"><?= count($participantIds) ?></strong> peserta dipilih</span>
          <button type="button" class="ed-p-desel" id="edPDesel">Hapus semua pilihan</button>
        </div>
      </div>
    </div>

    <!-- ══ STEP 3 — Tampilan & Simpan ══════════════════════════════ -->
    <div class="ed-page" id="edPage3" role="tabpanel">

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">

        <!-- Kiri: Warna -->
        <div>
          <div class="ed-sec-head">
            <div class="ed-sec-icon">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M2 12h10"/></svg>
            </div>
            <span class="ed-sec-label">Warna Kalender</span>
          </div>
          <div class="ed-field">
            <label class="ed-lbl">Pilih Warna</label>
            <div class="ed-color-row">
              <input type="color" id="edColorPicker" class="ed-color-input"
                     value="<?= htmlspecialchars($currentColor) ?>"
                     onchange="edPickColor(this.value)" title="Warna kustom">
              <div class="ed-presets">
                <?php foreach ($colorPresets as $hex):
                  $active = strtolower($hex) === $currentColor;
                ?>
                <button type="button"
                        class="ed-preset<?= $active ? ' active' : '' ?>"
                        style="background:<?= $hex ?>"
                        data-color="<?= $hex ?>"
                        onclick="edPickColor('<?= $hex ?>')"
                        title="<?= $hex ?>"></button>
                <?php endforeach; ?>
              </div>
            </div>
            <input type="hidden" id="fColor" name="color" value="<?= htmlspecialchars($currentColor) ?>">
            <div class="ed-color-preview">
              <span class="ed-color-dot" id="edColorDot" style="background:<?= htmlspecialchars($currentColor) ?>"></span>
              <span class="ed-color-hex" id="edColorHex"><?= htmlspecialchars($currentColor) ?></span>
              <span class="ed-color-cal">· tampil di kalender</span>
            </div>
          </div>
        </div>

        <!-- Kanan: Ringkasan -->
        <div>
          <div class="ed-sec-head">
            <div class="ed-sec-icon">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <span class="ed-sec-label">Ringkasan Perubahan</span>
          </div>
          <div class="ed-summary" id="edSummary">
            <div class="ed-sum-row">
              <svg class="ed-sum-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
              <span class="ed-sum-key">Judul:&nbsp;</span>
              <span class="ed-sum-val" id="sumTitle">—</span>
            </div>
            <div class="ed-sum-row">
              <svg class="ed-sum-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span class="ed-sum-key">Waktu:&nbsp;</span>
              <span class="ed-sum-val" id="sumTime">—</span>
            </div>
            <div class="ed-sum-row">
              <svg class="ed-sum-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <span class="ed-sum-key">Peserta:&nbsp;</span>
              <span class="ed-sum-val" id="sumPeserta">—</span>
            </div>
            <div class="ed-sum-row">
              <svg class="ed-sum-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/></svg>
              <span class="ed-sum-key">Lokasi:&nbsp;</span>
              <span class="ed-sum-val" id="sumLokasi">—</span>
            </div>
            <div class="ed-sum-row">
              <span class="ed-sum-ico" id="sumStatusDot" style="width:10px;height:10px;border-radius:50%;flex-shrink:0;display:inline-block;background:#ccc;margin-top:2px"></span>
              <span class="ed-sum-key">Status:&nbsp;</span>
              <span class="ed-sum-val" id="sumStatus">—</span>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- ══ NAV FOOTER ══════════════════════════════════════════════ -->
    <div class="ed-nav">
      <div class="ed-nav-left">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-btn-cancel">Batal</a>
      </div>
      <div class="ed-nav-right">
        <button type="button" class="ed-btn-prev" id="edBtnPrev" style="display:none">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
          Sebelumnya
        </button>
        <button type="button" class="ed-btn-next" id="edBtnNext">
          Selanjutnya
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <button type="submit" class="ed-btn-save" id="edBtnSave" style="display:none">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan Perubahan
        </button>
      </div>
    </div>

  </form>
</div><!-- /.ed-panel -->

<script>
(function () {
  'use strict';

  /* ── Toast auto-dismiss ── */
  var toast = document.getElementById('edToast');
  if (toast) {
    setTimeout(function(){ toast.style.transition='opacity .4s'; toast.style.opacity='0'; }, 4000);
    setTimeout(function(){ if(toast.parentNode) toast.remove(); }, 4500);
  }

  /* ── Step machine ── */
  var TOTAL = 3, cur = 1;
  var steps = document.querySelectorAll('.ed-step');
  var pages = document.querySelectorAll('.ed-page');
  var btnPrev = document.getElementById('edBtnPrev');
  var btnNext = document.getElementById('edBtnNext');
  var btnSave = document.getElementById('edBtnSave');

  function goTo(n) {
    if (n === 3) buildSummary();
    cur = n;
    steps.forEach(function(s, i) {
      s.classList.toggle('active', i + 1 === n);
      if (i + 1 < n) s.classList.add('complete'); else s.classList.remove('complete');
      s.setAttribute('aria-selected', i + 1 === n ? 'true' : 'false');
    });
    pages.forEach(function(p, i) { p.classList.toggle('active', i + 1 === n); });
    btnPrev.style.display = n > 1 ? '' : 'none';
    btnNext.style.display = n < TOTAL ? '' : 'none';
    btnSave.style.display = n === TOTAL ? '' : 'none';
  }

  steps.forEach(function(s) {
    s.addEventListener('click', function() {
      var t = parseInt(s.dataset.step);
      if (t === cur) return;
      if (t > cur && !validatePage(cur)) return;
      goTo(t);
    });
  });

  btnNext.addEventListener('click', function() {
    if (validatePage(cur)) goTo(cur + 1);
  });
  btnPrev.addEventListener('click', function() { goTo(cur - 1); });

  /* ── Validation ── */
  function validatePage(n) {
    if (n !== 1) return true;
    var ok = true;
    var s = document.getElementById('fStart');
    var e = document.getElementById('fEnd');

    // required fields on page 1
    ['fTitle','fStart','fEnd','fStatus'].forEach(function(id) {
      var el = document.getElementById(id);
      if (!el) return;
      el.classList.remove('invalid');
      if (!el.value.trim()) { el.classList.add('invalid'); ok = false; }
    });

    // end > start
    if (s && e && s.value && e.value && e.value <= s.value) {
      e.classList.add('invalid'); ok = false;
    }

    if (!ok) {
      var first = document.querySelector('.ed-page.active .invalid');
      if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    return ok;
  }

  // Remove invalid on change
  document.querySelectorAll('.ed-input,.ed-select,.ed-textarea').forEach(function(el) {
    el.addEventListener('input',  function(){ el.classList.remove('invalid'); });
    el.addEventListener('change', function(){ el.classList.remove('invalid'); });
  });

  /* ── Auto-fill end = start + 1h ── */
  var fStart = document.getElementById('fStart');
  var fEnd   = document.getElementById('fEnd');
  if (fStart && fEnd) {
    fStart.addEventListener('change', function() {
      if (!fEnd.value || fEnd.value <= fStart.value) {
        var d = new Date(fStart.value);
        if (!isNaN(d)) {
          d.setHours(d.getHours() + 1);
          var p = function(n){ return String(n).padStart(2,'0'); };
          fEnd.value = d.getFullYear()+'-'+p(d.getMonth()+1)+'-'+p(d.getDate())+'T'+p(d.getHours())+':'+p(d.getMinutes());
          fEnd.classList.remove('invalid');
        }
      }
    });
  }

  /* ── Cascade dept ── */
  var CURL = <?= json_encode($childrenUrl) ?>;
  function fetchKids(pid) {
    return fetch(CURL + '?parent_id=' + encodeURIComponent(pid))
      .then(function(r){ return r.ok ? r.json() : []; })
      .catch(function(){ return []; });
  }
  function buildOpts(sel, items, ph) {
    sel.innerHTML = '<option value="">'+ph+'</option>';
    items.forEach(function(d){ var o=document.createElement('option'); o.value=d.id; o.textContent=d.name; sel.appendChild(o); });
  }
  function syncDept() {
    var v3=document.getElementById('fU3').value,
        v2=document.getElementById('fU2').value,
        v1=document.getElementById('fU1').value;
    document.getElementById('fDeptId').value = v3||v2||v1||'';
  }
  window.edCascade = function(level) {
    var s1=document.getElementById('fU1'),s2=document.getElementById('fU2'),s3=document.getElementById('fU3');
    if (level===1) {
      buildOpts(s2,[],'— Semua Bidang —'); buildOpts(s3,[],'— Opsional —');
      s2.disabled=s3.disabled=true; syncDept();
      if (!s1.value) return;
      fetchKids(s1.value).then(function(k){ if(k.length){ buildOpts(s2,k,'— Semua Bidang —'); s2.disabled=false; } syncDept(); });
    } else if (level===2) {
      buildOpts(s3,[],'— Opsional —'); s3.disabled=true; syncDept();
      if (!s2.value) return;
      fetchKids(s2.value).then(function(k){ if(k.length){ buildOpts(s3,k,'— Opsional —'); s3.disabled=false; } syncDept(); });
    } else { syncDept(); }
  };

  /* ── Color picker ── */
  window.edPickColor = function(hex) {
    document.getElementById('fColor').value = hex;
    document.getElementById('edColorPicker').value = hex;
    document.getElementById('edColorDot').style.background = hex;
    document.getElementById('edColorHex').textContent = hex;
    document.querySelectorAll('.ed-preset').forEach(function(b){
      b.classList.toggle('active', b.dataset.color.toLowerCase() === hex.toLowerCase());
    });
    document.getElementById('edHero').style.background =
      'linear-gradient(135deg,'+hex+' 0%,#9B2020 55%,#A83218 100%)';
  };

  /* ── Participant search & count ── */
  var pSearch = document.getElementById('edPSearch');
  var pClr    = document.getElementById('edPClr');
  var pList   = document.getElementById('edPList');
  var pNum    = document.getElementById('edPNum');
  var pCount  = document.getElementById('edPCount');
  var pDesel  = document.getElementById('edPDesel');
  var pEmpty  = document.getElementById('edPEmpty');

  function updatePCount() {
    var n = pList ? pList.querySelectorAll('input[type=checkbox]:checked').length : 0;
    if (pNum)   pNum.textContent   = n;
    if (pCount) pCount.textContent = n;
  }

  if (pSearch && pList) {
    pSearch.addEventListener('input', function() {
      var q = this.value.trim().toLowerCase();
      if (pClr) pClr.style.display = q ? 'flex' : 'none';
      var vis = 0;
      pList.querySelectorAll('.ed-p-item').forEach(function(item) {
        var nm = item.querySelector('.ed-p-name');
        var show = !q || (nm && nm.textContent.toLowerCase().includes(q));
        item.style.display = show ? '' : 'none';
        if (show) vis++;
      });
      if (pEmpty) pEmpty.style.display = vis === 0 ? '' : 'none';
    });
    pList.addEventListener('change', function(e) {
      if (e.target.type === 'checkbox') updatePCount();
    });
  }
  if (pClr) pClr.addEventListener('click', function() {
    pSearch.value = ''; this.style.display = 'none';
    pList.querySelectorAll('.ed-p-item').forEach(function(i){ i.style.display = ''; });
    if (pEmpty) pEmpty.style.display = 'none';
    pSearch.focus();
  });
  if (pDesel) pDesel.addEventListener('click', function() {
    pList.querySelectorAll('input[type=checkbox]').forEach(function(c){ c.checked = false; });
    updatePCount();
  });

  /* ── Build summary for step 3 ── */
  var statusNames = <?= json_encode($statusLabel) ?>;
  var statusColors = { scheduled:'#1a6e9b', ongoing:'#8b5e00', done:'#2d7a2d', cancelled:'#a82515' };

  function fmt(dtStr) {
    if (!dtStr) return '—';
    var d = new Date(dtStr);
    if (isNaN(d)) return dtStr;
    return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) + ' ' +
           d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  }

  function buildSummary() {
    var t = document.getElementById('fTitle');
    var s = document.getElementById('fStart');
    var e = document.getElementById('fEnd');
    var l = document.getElementById('fLocation');
    var st = document.getElementById('fStatus');
    var n = pList ? pList.querySelectorAll('input[type=checkbox]:checked').length : 0;
    document.getElementById('sumTitle').textContent   = (t&&t.value) ? t.value : '—';
    document.getElementById('sumTime').textContent    = (s&&s.value) ? fmt(s.value)+' – '+fmt(e&&e.value) : '—';
    document.getElementById('sumPeserta').textContent = n + ' orang';
    document.getElementById('sumLokasi').textContent  = (l&&l.value) ? l.value : '—';
    document.getElementById('sumStatus').textContent  = (st&&st.value) ? (statusNames[st.value]||st.value) : '—';
    var dot = document.getElementById('sumStatusDot');
    if (dot && st) dot.style.background = statusColors[st.value] || '#ccc';
  }

  /* ── Submit + spinner ── */
  document.getElementById('edForm').addEventListener('submit', function(e) {
    if (!validatePage(1)) { e.preventDefault(); goTo(1); return; }
    btnSave.disabled = true;
    btnSave.innerHTML = '<span class="ed-spinner"></span> Menyimpan…';
  });

  /* Init */
  goTo(1);

}());
</script>
