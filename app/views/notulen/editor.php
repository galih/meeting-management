<?php
$baseUrl  = rtrim(BASE_URL, '/');
$docxUrl  = $baseUrl . '/notulen/' . (int)$meeting['id'] . '/export-docx';
$histUrl  = $baseUrl . '/notulen/' . (int)$meeting['id'] . '/history';
$backUrl  = $baseUrl . '/meetings/'  . (int)$meeting['id'];
$canEdit  = Auth::hasRole('admin', 'sekretaris');

$statusBadge   = ['pending'=>'kb-badge-gray','in_progress'=>'kb-badge-blue','done'=>'kb-badge-green','cancelled'=>'kb-badge-red'];
$statusLabel   = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];
$priorityBadge = ['high'=>'kb-badge-red','medium'=>'kb-badge-gold','low'=>'kb-badge-green'];
$priorityLabel = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$meetingBadge  = ['scheduled'=>'kb-badge-blue','ongoing'=>'kb-badge-gold','done'=>'kb-badge-green','cancelled'=>'kb-badge-red'];
$meetingStatusLabel = ['scheduled'=>'Terjadwal','ongoing'=>'Sedang Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];
$loc    = $meeting['location'] ?? '';
$isLink = !empty($loc) && (strncmp($loc,'http://',7)===0 || strncmp($loc,'https://',8)===0);

$initialContent = $notulen['content'] ?? '';
$saveUrl        = $baseUrl . '/api/notulen/save';
$syncUrl        = $baseUrl . '/api/notulen/sync';
$currentUserId  = Auth::user()['id'] ?? 0;

$editorJsPath  = ROOT_PATH . '/assets/js/notulen-editor.js';
$editorJsVer   = file_exists($editorJsPath) ? filemtime($editorJsPath) : time();

$tlUsers = $users ?? $allUsers ?? [];
?>

<style>
/* ── Kemenbud Tokens ──────────────────────────────────────────── */
:root {
  --kb-primary:       #7B1C1C;
  --kb-primary-dark:  #5A1212;
  --kb-primary-light: rgba(123,28,28,.08);
  --kb-primary-ring:  rgba(123,28,28,.18);
  --kb-gold:          #C9A84C;
  --kb-gold-dark:     #A8872F;
  --kb-gold-light:    rgba(201,168,76,.14);
  --kb-surface:       #FBF8F3;
  --kb-surface-2:     #F5F0E8;
  --kb-surface-3:     #EDE6D6;
  --kb-border:        #DDD5C4;
  --kb-border-light:  #EDE8DE;
  --kb-text:          #1C1714;
  --kb-text-muted:    #6B6055;
  --kb-text-faint:    #A89E90;
  --kb-green:         #2A6B3A;
  --kb-green-bg:      rgba(42,107,58,.10);
  --kb-blue:          #1B4F82;
  --kb-blue-bg:       rgba(27,79,130,.10);
  --kb-red:           #A8251A;
  --kb-red-bg:        rgba(168,37,26,.10);
  --kb-gray-bg:       rgba(100,100,100,.10);
  --kb-radius:        12px;
  --kb-radius-sm:     8px;
  --kb-radius-xs:     6px;
  --kb-shadow-sm:     0 1px 4px rgba(28,23,20,.07);
  --kb-shadow-md:     0 3px 12px rgba(28,23,20,.09);
  --kb-shadow-lg:     0 6px 24px rgba(28,23,20,.12);
  --kb-transition:    180ms cubic-bezier(.16,1,.3,1);
}

.ned-wrap * { box-sizing: border-box; }

/* ── Hero ─────────────────────────────────────────────────────── */
.ned-hero {
  background: linear-gradient(135deg, #7B1C1C 0%, #9B2020 50%, #6A1515 100%);
  border-radius: var(--kb-radius); overflow: hidden;
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative;
}
.ned-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.ned-hero::after {
  content:''; position:absolute; top:0; left:0; right:0; bottom:0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.ned-hero-inner { padding: 1.35rem 1.5rem 1.1rem; position:relative; z-index:1; }

.ned-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:11.5px; color:rgba(255,255,255,.6); margin-bottom:.45rem;
}
.ned-breadcrumb a { color:rgba(255,255,255,.75); text-decoration:none; transition:color var(--kb-transition); }
.ned-breadcrumb a:hover { color:#fff; }

.ned-hero-title {
  font-size: clamp(15px, 2vw, 21px); font-weight:800; color:#fff;
  margin:0; display:flex; align-items:center; gap:.5rem;
  letter-spacing:-.02em; line-height:1.25;
}
.ned-hero-title svg { opacity:.85; flex-shrink:0; }

.ned-hero-meta { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-top:.6rem; }

.ned-live-badge {
  display:inline-flex; align-items:center; gap:.35rem;
  background:rgba(47,200,80,.18); color:#86efac;
  font-size:11px; font-weight:700; padding:.25em .75em; border-radius:20px;
  border: 1px solid rgba(74,222,128,.25);
}
.ned-live-dot {
  width:7px; height:7px; border-radius:50%; background:#4ade80;
  animation: ned-pulse 1.6s ease-in-out infinite;
}
@keyframes ned-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.45;transform:scale(.85)} }

.ned-save-status { font-size:11.5px; color:rgba(255,255,255,.65); }

.ned-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  background:rgba(255,255,255,.12); color:rgba(255,255,255,.82);
  font-size:11.5px; padding:.25em .7em; border-radius:20px;
  border:1px solid rgba(255,255,255,.15);
}
.ned-chip-link { color:var(--kb-gold); text-decoration:none; }
.ned-chip-link:hover { text-decoration:underline; }

/* Hero buttons */
.ned-hero-actions { display:flex; flex-wrap:wrap; gap:.5rem; }
.ned-hero-actions .btn {
  font-size:12.5px; font-weight:700; border-radius:var(--kb-radius-sm);
  display:inline-flex; align-items:center; gap:.35rem;
  padding:.42rem .95rem; transition:all var(--kb-transition);
}
.ned-btn-save {
  background:var(--kb-gold); border:1.5px solid var(--kb-gold-dark);
  color:#2D1A00; box-shadow:0 2px 8px rgba(201,168,76,.35);
}
.ned-btn-save:hover { background:var(--kb-gold-dark); color:#fff; box-shadow:0 3px 12px rgba(168,135,47,.4); }
.ned-btn-tpl {
  background:rgba(255,255,255,.14); border:1.5px solid rgba(255,255,255,.28); color:#fff;
}
.ned-btn-tpl:hover { background:rgba(255,255,255,.24); color:#fff; }
.ned-btn-export {
  background:rgba(255,255,255,.11); border:1.5px solid rgba(255,255,255,.22); color:#fff;
}
.ned-btn-export:hover { background:rgba(255,255,255,.2); color:#fff; }
.ned-btn-hist {
  background:transparent; border:1.5px solid rgba(255,255,255,.28);
  color:rgba(255,255,255,.82);
}
.ned-btn-hist:hover { border-color:rgba(255,255,255,.7); color:#fff; }

.ned-hero-bar {
  height:4px;
  background: linear-gradient(90deg, var(--kb-gold) 0%, var(--kb-gold-dark) 60%, transparent 100%);
}

/* Readonly alert */
.ned-readonly-alert {
  display:flex; align-items:center; gap:.6rem;
  background:#FBF6EC; border:1px solid var(--kb-border);
  border-left:3px solid var(--kb-gold);
  color:var(--kb-text); border-radius:var(--kb-radius-sm);
  padding:.65rem 1rem; font-size:13px;
}

/* ── Editor card ──────────────────────────────────────────────── */
.ned-editor-card {
  border:1px solid var(--kb-border-light); border-radius:var(--kb-radius);
  overflow:hidden; box-shadow:var(--kb-shadow-md); background:#fff;
}
.ned-editor-card .ql-container.ql-snow { border:none; }
.ned-editor-card .ql-toolbar.ql-snow   { border:none; border-bottom:1px solid var(--kb-border-light); }
.ned-quill-area { min-height:490px; font-size:15px; }

/* ── Comment card ────────────────────────────────────────────── */
.ned-comment-card {
  border:1px solid var(--kb-border-light); border-radius:var(--kb-radius);
  overflow:hidden; box-shadow:var(--kb-shadow-sm); background:#fff;
}
.ned-comment-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:.7rem 1rem; border-bottom:1px solid var(--kb-border-light);
  background:var(--kb-surface);
}
.ned-comment-title { font-size:13.5px; font-weight:700; color:var(--kb-primary); }
.ned-count-badge {
  background:var(--kb-primary-light); color:var(--kb-primary);
  font-size:11px; font-weight:700; padding:.1em .55em; border-radius:20px;
}
.ned-comment-list { padding:.5rem .85rem; min-height:60px; }
.ned-comment-footer {
  padding:.75rem 1rem; border-top:1px solid var(--kb-border-light); background:#fff;
}
.ned-user-avatar {
  width:32px; height:32px; border-radius:50%;
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  color:#fff; font-size:13px; font-weight:800;
  display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
  box-shadow:0 2px 6px rgba(123,28,28,.25);
}
.ned-comment-input {
  border:1.5px solid var(--kb-border); border-radius:var(--kb-radius-sm);
  padding:.45rem .8rem; font-size:13px; width:100%;
  outline:none; resize:none; background:#fff; color:var(--kb-text);
  transition:border-color var(--kb-transition), box-shadow var(--kb-transition);
}
.ned-comment-input:focus {
  border-color:var(--kb-primary); box-shadow:0 0 0 3px var(--kb-primary-ring);
}
.ned-btn-send {
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  border:none; color:#fff; font-size:12.5px; font-weight:700;
  border-radius:var(--kb-radius-xs); padding:.38rem .9rem;
  display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; transition:all var(--kb-transition);
  box-shadow:0 2px 8px rgba(123,28,28,.25);
}
.ned-btn-send:hover { background:linear-gradient(135deg,#9B2020,var(--kb-primary-dark)); color:#fff; }
.ned-btn-sm-outline {
  background:transparent; border:1.5px solid var(--kb-border);
  color:var(--kb-text-muted); font-size:12px; font-weight:600;
  border-radius:var(--kb-radius-xs); padding:.3rem .75rem;
  cursor:pointer; transition:all var(--kb-transition);
}
.ned-btn-sm-outline:hover { border-color:var(--kb-primary); color:var(--kb-primary); }

/* ── Sidebar ─────────────────────────────────────────────────── */
.ned-sidebar-card {
  border:1px solid var(--kb-border-light); border-radius:var(--kb-radius);
  overflow:hidden; box-shadow:var(--kb-shadow-sm); background:#fff;
}
.ned-sidebar-header {
  display:flex; align-items:center; gap:.4rem;
  font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase;
  color:var(--kb-primary); background:var(--kb-surface);
  padding:.55rem .9rem; border-bottom:1px solid var(--kb-border-light);
}
.ned-sidebar-header svg { opacity:.75; flex-shrink:0; }
.ned-sidebar-body   { padding:.75rem .9rem; background:#fff; }
.ned-sidebar-footer { padding:.6rem .9rem; background:var(--kb-surface); border-top:1px solid var(--kb-border-light); }

.ned-btn-add-sm {
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  border:none; color:#fff; font-size:11px; font-weight:800;
  border-radius:var(--kb-radius-xs); padding:.28rem .6rem;
  display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; transition:all var(--kb-transition);
  box-shadow:0 1px 5px rgba(123,28,28,.2);
}
.ned-btn-add-sm:hover { background:linear-gradient(135deg,#9B2020,var(--kb-primary-dark)); color:#fff; }

.ned-btn-back {
  background:transparent; border:1.5px solid var(--kb-border);
  color:var(--kb-text); font-size:12.5px; font-weight:600;
  border-radius:var(--kb-radius-sm); padding:.4rem .8rem;
  display:inline-flex; align-items:center; gap:.35rem;
  transition:all var(--kb-transition);
}
.ned-btn-back:hover { border-color:var(--kb-primary); color:var(--kb-primary); background:var(--kb-primary-light); }

.ned-dl {
  display:grid; grid-template-columns:auto 1fr;
  gap:.25rem .8rem; margin:0; font-size:13px;
}
.ned-dl dt { color:var(--kb-text-muted); font-weight:700; white-space:nowrap; }
.ned-dl dd { color:var(--kb-text); margin:0; }
.ned-link { color:var(--kb-primary); text-decoration:none; }
.ned-link:hover { text-decoration:underline; }

.ned-upload-form {
  padding:.8rem .9rem; border-bottom:1px solid var(--kb-border-light); background:#fff;
}
.ned-form-label { font-size:12px; font-weight:700; color:var(--kb-text); display:block; margin-bottom:.25rem; }
.ned-form-hint  { font-size:11px; color:var(--kb-text-faint); margin-top:.2rem; }
.ned-btn-upload {
  background:linear-gradient(135deg,var(--kb-primary),#9B2020); border:none; color:#fff;
  font-size:12.5px; font-weight:700; border-radius:var(--kb-radius-sm);
  padding:.4rem .8rem; display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; transition:all var(--kb-transition);
}
.ned-btn-upload:hover { background:linear-gradient(135deg,#9B2020,var(--kb-primary-dark)); color:#fff; }
.ned-btn-cancel {
  background:transparent; border:1.5px solid var(--kb-border);
  color:var(--kb-text-muted); font-size:12.5px;
  border-radius:var(--kb-radius-sm); padding:.4rem .8rem; cursor:pointer;
  transition:all var(--kb-transition);
}
.ned-btn-cancel:hover { border-color:var(--kb-text-muted); }

.ned-attachment-list { max-height:260px; overflow-y:auto; }
.ned-attach-loading { padding:.9rem; text-align:center; font-size:12.5px; color:var(--kb-text-muted); }

.ned-tl-item {
  padding:.65rem .9rem; border-bottom:1px solid var(--kb-border-light);
  background:#fff; transition:background var(--kb-transition);
}
.ned-tl-item:last-child { border-bottom:none; }
.ned-tl-item:hover { background:var(--kb-surface); }
.ned-tl-desc {
  font-size:13px; font-weight:600; color:var(--kb-text); line-height:1.4;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.ned-tl-meta {
  font-size:11px; color:var(--kb-text-muted);
  display:flex; align-items:center; gap:.25rem; margin:.25rem 0;
}
.ned-tl-del {
  background:transparent; border:none; color:var(--kb-red); cursor:pointer;
  width:22px; height:22px; border-radius:var(--kb-radius-xs); flex-shrink:0;
  display:inline-flex; align-items:center; justify-content:center;
  transition:background var(--kb-transition); padding:0;
}
.ned-tl-del:hover { background:var(--kb-red-bg); }
.ned-tl-empty { padding:.9rem; text-align:center; font-size:12.5px; color:var(--kb-text-faint); }

.ned-badge {
  display:inline-flex; align-items:center;
  font-size:10.5px; font-weight:700; padding:.2em .65em;
  border-radius:20px; white-space:nowrap;
}
.kb-badge-red    { background:var(--kb-red-bg);    color:var(--kb-red); }
.kb-badge-gold   { background:var(--kb-gold-light); color:#7A5C00; }
.kb-badge-green  { background:var(--kb-green-bg);  color:var(--kb-green); }
.kb-badge-blue   { background:var(--kb-blue-bg);   color:var(--kb-blue); }
.kb-badge-gray   { background:var(--kb-gray-bg);   color:var(--kb-text-muted); }

.ned-modal-icon {
  width:32px; height:32px; background:var(--kb-primary-light);
  border-radius:var(--kb-radius-xs);
  display:inline-flex; align-items:center; justify-content:center;
  color:var(--kb-primary);
}

#mention-dropdown {
  position:absolute; z-index:1050; bottom:calc(100% + 4px); left:0;
  min-width:180px; max-height:180px; overflow-y:auto;
  background:#fff; border:1px solid var(--kb-border);
  border-radius:var(--kb-radius-sm); box-shadow:var(--kb-shadow-md);
}

@media(max-width:767.98px) {
  .ned-hero-inner { padding:1rem; }
  .ned-hero-title { font-size:14.5px; }
  .ned-hero-actions { width:100%; }
  .ned-hero-actions .btn { font-size:12px; padding:.38rem .75rem; }
}
</style>

<!-- ============================================================
     HERO HEADER
============================================================ -->
<div class="ned-hero mb-1">
  <div class="ned-hero-inner">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">

      <!-- Left: breadcrumb + title + meta -->
      <div style="flex:1; min-width:0;">
        <nav class="ned-breadcrumb">
          <a href="<?= $backUrl ?>">Detail Kegiatan</a>
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Notulen</span>
        </nav>
        <h1 class="ned-hero-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <?= htmlspecialchars($meeting['title']) ?>
        </h1>
        <div class="ned-hero-meta">
          <span id="sync-status" class="ned-live-badge">
            <span class="ned-live-dot"></span>Live
          </span>
          <span id="save-status" class="ned-save-status">Tersimpan</span>
          <span class="ned-chip">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y · H:i', strtotime($meeting['start_datetime'])) ?>
          </span>
          <?php if (!empty($loc)): ?>
          <span class="ned-chip">
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ned-chip-link">🔗 Link Kegiatan</a>
            <?php else: ?>
            <?= htmlspecialchars($loc) ?>
            <?php endif; ?>
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: action buttons -->
      <div class="ned-hero-actions">
        <?php if ($canEdit): ?>
        <button type="button" class="btn ned-btn-tpl" id="btn-pick-template">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Template
        </button>
        <button id="btn-save-manual" class="btn ned-btn-save">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan
        </button>
        <?php endif; ?>
        <a href="<?= $docxUrl ?>" class="btn ned-btn-export">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Export Word
        </a>
        <?php if ($canEdit): ?>
        <a href="<?= $histUrl ?>" class="btn ned-btn-hist">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
          Riwayat
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="ned-hero-bar"></div>
</div>

<?php if (!$canEdit): ?>
<div class="ned-readonly-alert alert mb-3" role="alert">
  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  Anda hanya bisa membaca notulen ini. Edit tidak tersedia.
  <button type="button" class="btn-close btn-close-sm ms-auto"
          data-bs-dismiss="alert"
          onclick="this.closest('.ned-readonly-alert')?.remove()"
          aria-label="Tutup"></button>
</div>
<?php endif; ?>

<!-- ============================================================
     BODY
============================================================ -->
<div class="row g-3">

  <!-- === EDITOR + DISKUSI === -->
  <div class="col-lg-8">

    <!-- Editor Card -->
    <div class="card ned-editor-card">
      <div class="card-body p-0">
        <div id="quill-editor" class="ned-quill-area"></div>
      </div>
    </div>

    <!-- Diskusi -->
    <div class="card ned-comment-card mt-3" id="comment-panel">
      <div class="ned-comment-header">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--kb-primary)"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span class="ned-comment-title">Diskusi</span>
          <span class="ned-count-badge" id="comment-count">0</span>
        </div>
        <button class="btn ned-btn-sm-outline" id="btn-toggle-resolved">Tampilkan Selesai</button>
      </div>
      <div id="comment-list" class="ned-comment-list"></div>
      <div class="ned-comment-footer">
        <div class="d-flex gap-2 align-items-start">
          <span class="ned-user-avatar"><?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?></span>
          <div class="flex-fill">
            <div class="position-relative">
              <div id="mention-dropdown" class="dropdown-menu"></div>
              <textarea id="comment-input" class="ned-comment-input" rows="2"
                        placeholder="Tulis komentar… (@ untuk mention, Enter untuk kirim)"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <small class="text-muted" id="reply-indicator" style="font-size:11px;"></small>
              <button class="btn ned-btn-send" id="btn-submit-comment">Kirim</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- === SIDEBAR KANAN === -->
  <div class="col-lg-4">

    <!-- Info Kegiatan -->
    <div class="card ned-sidebar-card mb-3">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Info Kegiatan
      </div>
      <div class="ned-sidebar-body">
        <dl class="ned-dl">
          <dt>Lokasi</dt>
          <dd>
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ned-link">Buka Link</a>
            <?php else: ?>
            <?= htmlspecialchars($loc ?: '—') ?>
            <?php endif; ?>
          </dd>
          <dt>Mulai</dt><dd><?= date('d M Y H:i', strtotime($meeting['start_datetime'])) ?></dd>
          <dt>Selesai</dt><dd><?= date('d M Y H:i', strtotime($meeting['end_datetime'])) ?></dd>
          <dt>Status</dt>
          <dd>
            <?php $ms = $meetingBadge[$meeting['status']] ?? 'kb-badge-gray'; ?>
            <span class="ned-badge <?= $ms ?>">
              <?= $meetingStatusLabel[$meeting['status']] ?? ucfirst($meeting['status']) ?>
            </span>
          </dd>
        </dl>
      </div>
      <div class="ned-sidebar-footer">
        <a href="<?= $backUrl ?>" class="btn ned-btn-back w-100">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Detail Kegiatan
        </a>
      </div>
    </div>

    <!-- Lampiran -->
    <div class="card ned-sidebar-card mb-3" id="attachment-panel" data-meeting-id="<?= (int)$meeting['id'] ?>">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        Lampiran
        <span class="ned-count-badge ms-1" id="attach-count">0</span>
        <?php if ($canEdit): ?>
        <button class="btn ned-btn-add-sm ms-auto" id="btn-show-upload-form">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload
        </button>
        <?php endif; ?>
      </div>

      <?php if ($canEdit): ?>
      <div id="upload-form-wrapper" style="display:none;" class="ned-upload-form">
        <form id="form-upload-attachment" enctype="multipart/form-data">
          <div class="mb-2">
            <label class="ned-form-label">Pilih File <span class="text-danger">*</span></label>
            <input type="file" id="attach-file" class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
            <div class="ned-form-hint">PDF, Office, Gambar, ZIP · maks. 10 MB</div>
          </div>
          <div class="mb-2">
            <label class="ned-form-label">Kategori</label>
            <select id="attach-category" class="form-select form-select-sm">
              <option value="dokumen">Dokumen</option>
              <option value="presentasi">Presentasi</option>
              <option value="gambar">Gambar</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn ned-btn-upload flex-fill" id="btn-do-upload">
              <span id="upload-spinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
              Upload
            </button>
            <button type="button" class="btn ned-btn-cancel" id="btn-cancel-upload">Batal</button>
          </div>
          <div id="upload-alert" class="d-none mt-2"></div>
        </form>
      </div>
      <?php endif; ?>

      <div id="attachment-list" class="ned-attachment-list">
        <div class="ned-attach-loading">
          <span class="spinner-border spinner-border-sm"></span> Memuat…
        </div>
      </div>
    </div>

    <!-- Tindak Lanjut -->
    <div class="card ned-sidebar-card">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tindak Lanjut
        <?php if ($canEdit): ?>
        <button class="btn ned-btn-add-sm ms-auto" data-bs-toggle="modal" data-bs-target="#modalTL">
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah
        </button>
        <?php endif; ?>
      </div>
      <div id="tl-list">
        <?php if (empty($tindakLanjutList)): ?>
        <div class="ned-tl-empty" id="tl-empty">Belum ada tindak lanjut</div>
        <?php endif; ?>
        <?php foreach (($tindakLanjutList ?? []) as $tl):
          $pc   = $priorityBadge[$tl['priority']] ?? 'kb-badge-gray';
          $plbl = $priorityLabel[$tl['priority']]  ?? ucfirst($tl['priority']);
          $sc   = $statusBadge[$tl['status']]       ?? 'kb-badge-gray';
          $slbl = $statusLabel[$tl['status']]        ?? ucfirst(str_replace('_',' ',$tl['status']));
        ?>
        <div class="ned-tl-item" id="tl-item-<?= (int)$tl['id'] ?>">
          <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
            <span class="ned-tl-desc"><?= htmlspecialchars($tl['description']) ?></span>
            <?php if ($canEdit): ?>
            <button class="ned-tl-del btn-tl-del"
                    data-id="<?= (int)$tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>/delete"
                    title="Hapus">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>
          </div>
          <div class="ned-tl-meta">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($tl['assigned_name'] ?? '—') ?>
            <?php if (!empty($tl['due_date'])): ?>
            &middot;
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y', strtotime($tl['due_date'])) ?>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-1 mt-1">
            <span class="ned-badge <?= $pc ?>"><?= $plbl ?></span>
            <span class="ned-badge <?= $sc ?>"><?= $slbl ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============================================================
     MODALS
============================================================ -->
<?php if ($canEdit): ?>

<!-- Modal Template -->
<div class="modal modal-blur fade" id="modalPickTemplate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--kb-surface); border-bottom:1px solid var(--kb-border-light);">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </span>
          <h5 class="modal-title" style="font-size:15px; font-weight:700; color:var(--kb-text);">Pilih Template Notulen</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 small mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Memilih template akan <strong>mengganti</strong> seluruh isi notulen. Simpan dulu jika ada perubahan penting.
        </div>
        <div id="tpl-list-loading" class="text-center py-4">
          <span class="spinner-border spinner-border-sm" style="color:var(--kb-primary);"></span> Memuat template…
        </div>
        <div id="tpl-list-container" class="row g-3" style="display:none;"></div>
      </div>
      <div class="modal-footer" style="background:var(--kb-surface); border-top:1px solid var(--kb-border-light);">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah TL -->
<div class="modal modal-blur fade" id="modalTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--kb-surface); border-bottom:1px solid var(--kb-border-light);">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </span>
          <h5 class="modal-title" style="font-size:15px; font-weight:700; color:var(--kb-text);">Tambah Tindak Lanjut</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="ned-form-label">Deskripsi Tugas <span class="text-danger">*</span></label>
          <textarea id="tl2-desk" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan hasil evaluasi Q2…" required
                    style="font-size:13.5px;"></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="ned-form-label">Ditugaskan ke</label>
            <select id="tl2-assign" class="form-select form-select-sm">
              <option value="">-- Pilih --</option>
              <?php foreach ($tlUsers as $u): ?>
              <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="ned-form-label">Deadline</label>
            <input type="date" id="tl2-deadline" class="form-control form-control-sm" min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-3">
          <label class="ned-form-label">Prioritas</label>
          <select id="tl2-priority" class="form-select form-select-sm">
            <option value="low">Rendah</option>
            <option value="medium" selected>Sedang</option>
            <option value="high">Tinggi</option>
          </select>
        </div>
      </div>
      <div class="modal-footer" style="background:var(--kb-surface); border-top:1px solid var(--kb-border-light);">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-tl2-save"
                style="background:linear-gradient(135deg,var(--kb-primary),#9B2020);border:none;color:#fff;font-size:13px;font-weight:700;border-radius:8px;padding:.42rem 1.1rem;display:inline-flex;align-items:center;gap:.35rem;">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============================================================
     GLOBALS + SCRIPT LOADER
============================================================ -->
<script>
(function () {
  window.BASE_URL        = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  window.MEETING_ID      = <?= (int)$meeting['id'] ?>;
  window.CURRENT_USER_ID = <?= (int)$currentUserId ?>;
  window.IS_EDITOR       = <?= $canEdit ? 'true' : 'false' ?>;
  window.INITIAL_CONTENT = <?= json_encode($initialContent) ?>;
  window.SAVE_URL        = <?= json_encode($saveUrl) ?>;
  window.SYNC_URL        = <?= json_encode($syncUrl) ?>;

  /* ── Loader ─────────────────────────────────────────────────── */
  function initTemplatePicker() {
<?php if ($canEdit): ?>
    /* FIX: listener dipasang di sini, SETELAH notulen-editor.js selesai load,
       sehingga tidak terkena race condition / overwrite oleh editor script. */
    var TPL_API_URL    = window.BASE_URL + '/api/notulen-templates';
    var TPL_MANAGE_URL = window.BASE_URL + '/notulen-templates';
    var tplListLoaded  = false;

    var btnTpl = document.getElementById('btn-pick-template');
    if (!btnTpl) return;

    btnTpl.addEventListener('click', function () {
      var modalEl = document.getElementById('modalPickTemplate');
      if (!modalEl) return;
      var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
      modal.show();
      if (tplListLoaded) return;

      fetch(TPL_API_URL)
        .then(function (r) { return r.json(); })
        .then(function (data) {
          tplListLoaded = true;
          var loading   = document.getElementById('tpl-list-loading');
          var container = document.getElementById('tpl-list-container');
          if (loading)   loading.style.display = 'none';
          if (container) container.style.display = '';

          if (!data.templates || !data.templates.length) {
            container.innerHTML = '<div class="col-12 text-center py-3" style="color:var(--kb-text-muted);font-size:13px;">Belum ada template. <a href="' + TPL_MANAGE_URL + '" target="_blank" style="color:var(--kb-primary);">Buat template</a></div>';
            return;
          }

          data.templates.forEach(function (tpl) {
            var col = document.createElement('div');
            col.className = 'col-md-6';
            col.innerHTML =
              '<div style="background:#fff;border:1px solid var(--kb-border-light);border-radius:var(--kb-radius);overflow:hidden;box-shadow:var(--kb-shadow-sm);height:100%;display:flex;flex-direction:column;">' +
                '<div style="padding:.8rem 1rem;flex:1;">' +
                  '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.3rem;">' +
                    '<span style="font-size:14px;font-weight:700;color:var(--kb-text);">' + tpl.name + '</span>' +
                    (tpl.is_default == 1 ? '<span class="ned-badge kb-badge-green">Default</span>' : '') +
                  '</div>' +
                  '<p style="font-size:12px;color:var(--kb-text-muted);margin:0;">' + (tpl.description || '—') + '</p>' +
                '</div>' +
                '<div style="padding:.55rem 1rem;background:var(--kb-surface);border-top:1px solid var(--kb-border-light);">' +
                  '<button style="width:100%;background:linear-gradient(135deg,var(--kb-primary),#9B2020);border:none;color:#fff;font-size:12.5px;font-weight:700;border-radius:7px;padding:.38rem .75rem;cursor:pointer;" class="btn-apply-tpl" data-tpl-id="' + tpl.id + '">Gunakan Template Ini</button>' +
                '</div>' +
              '</div>';
            container.appendChild(col);
          });

          container.querySelectorAll('.btn-apply-tpl').forEach(function (btn) {
            btn.addEventListener('click', function () {
              var tplId = this.dataset.tplId;
              fetch(TPL_API_URL + '/' + tplId)
                .then(function (r) { return r.json(); })
                .then(function (d) {
                  if (!d.success) { alert(d.message || 'Gagal memuat template.'); return; }
                  if (!window.quill) { alert('Editor belum siap.'); return; }
                  window.quill.clipboard.dangerouslyPasteHTML(d.template.content);
                  bootstrap.Modal.getInstance(document.getElementById('modalPickTemplate')).hide();
                  var ss = document.getElementById('save-status');
                  if (ss) { ss.textContent = '● Belum disimpan'; ss.style.color = 'rgba(255,200,50,.9)'; }
                })
                .catch(function () { alert('Gagal memuat template.'); });
            });
          });
        })
        .catch(function () {
          var loading = document.getElementById('tpl-list-loading');
          if (loading) loading.innerHTML = '<p class="text-danger small">Gagal memuat template.</p>';
        });
    });
<?php endif; ?>
  }

  function loadEditorScript() {
    var es   = document.createElement('script');
    es.src   = <?= json_encode(rtrim(BASE_URL, '/') . '/assets/js/notulen-editor.js?v=' . $editorJsVer) ?>;
    /* FIX: initTemplatePicker dipanggil di onload — SETELAH editor script
       selesai dieksekusi, sehingga listener tombol Template tidak ditimpa. */
    es.onload  = initTemplatePicker;
    es.onerror = function () { console.error('Gagal memuat notulen-editor.js'); };
    document.body.appendChild(es);
  }

  if (typeof window.Quill === 'undefined') {
    var qs   = document.createElement('script');
    qs.src   = 'https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js';
    qs.onload  = loadEditorScript;
    qs.onerror = function () { console.error('Gagal memuat Quill dari CDN.'); };
    document.head.appendChild(qs);
  } else {
    loadEditorScript();
  }
})();
</script>
