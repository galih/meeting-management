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
$currentUserInitial = strtoupper(mb_substr(Auth::user()['name'] ?? 'U', 0, 1));

$editorJsPath  = ROOT_PATH . '/assets/js/notulen-editor.js';
$editorJsVer   = file_exists($editorJsPath) ? filemtime($editorJsPath) : time();

$tlUsers = $users ?? $allUsers ?? [];

// Hitung durasi kegiatan
$start   = new DateTime($meeting['start_datetime']);
$end     = new DateTime($meeting['end_datetime']);
$durMins = max(0, (int)(($end->getTimestamp() - $start->getTimestamp()) / 60));
$durStr  = $durMins >= 60
  ? floor($durMins/60) . ' jam' . ($durMins%60 ? ' ' . ($durMins%60) . ' mnt' : '')
  : $durMins . ' menit';
?>

<style>
/* ── Design Tokens ─────────────────────────────────────────────── */
:root {
  --kb-primary:        #7B1C1C;
  --kb-primary-dark:   #5A1212;
  --kb-primary-deep:   #3D0A0A;
  --kb-primary-light:  rgba(123,28,28,.08);
  --kb-primary-ring:   rgba(123,28,28,.18);
  --kb-gold:           #C9A84C;
  --kb-gold-dark:      #A8872F;
  --kb-gold-light:     rgba(201,168,76,.14);
  --kb-surface:        #FBF8F3;
  --kb-surface-2:      #F5F0E8;
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
  --kb-gray-bg:        rgba(100,100,100,.10);
  --kb-radius:         12px;
  --kb-radius-sm:      8px;
  --kb-radius-xs:      6px;
  --kb-shadow-sm:      0 1px 4px rgba(28,23,20,.07);
  --kb-shadow-md:      0 3px 12px rgba(28,23,20,.09);
  --kb-shadow-lg:      0 6px 24px rgba(28,23,20,.12);
  --kb-tr:             180ms cubic-bezier(.16,1,.3,1);
}
.ned-wrap * { box-sizing: border-box; }

/* ── Hero ──────────────────────────────────────────────────────── */
.ned-hero {
  background: linear-gradient(135deg, var(--kb-primary) 0%, #9B2020 55%, #6A1515 100%);
  border-radius: var(--kb-radius);
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  overflow: hidden;
  position: relative;
  margin-bottom: .75rem;
}
.ned-hero::before {
  content:''; position:absolute; bottom:-40px; right:-40px;
  width:200px; height:200px; border-radius:50%;
  background:rgba(201,168,76,.08); pointer-events:none;
}
.ned-hero::after {
  content:''; position:absolute; inset:0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.022'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.ned-hero-inner {
  padding: 1.4rem 1.6rem 1.15rem;
  position: relative; z-index: 1;
}
.ned-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:11.5px; color:rgba(255,255,255,.55); margin-bottom:.5rem;
}
.ned-breadcrumb a {
  color:rgba(255,255,255,.72); text-decoration:none;
  transition: color var(--kb-tr);
}
.ned-breadcrumb a:hover { color:#fff; }
.ned-breadcrumb svg { opacity:.6; }

.ned-hero-row {
  display: flex; flex-wrap: wrap;
  align-items: flex-start;
  justify-content: space-between;
  gap: .75rem;
}
.ned-hero-left { flex: 1; min-width: 0; }

.ned-hero-title {
  font-size: clamp(15px, 2.2vw, 22px);
  font-weight: 800; color: #fff;
  margin: 0; letter-spacing: -.025em; line-height: 1.22;
  display: flex; align-items: flex-start; gap: .45rem;
}
.ned-hero-title svg { opacity: .8; flex-shrink: 0; margin-top: 3px; }

.ned-hero-meta {
  display: flex; flex-wrap: wrap;
  align-items: center; gap: .45rem;
  margin-top: .55rem;
}
.ned-live-badge {
  display:inline-flex; align-items:center; gap:.32rem;
  background:rgba(74,222,128,.16); color:#86efac;
  font-size:10.5px; font-weight:700;
  padding:.22em .7em; border-radius:20px;
  border:1px solid rgba(74,222,128,.22);
}
.ned-live-dot {
  width:6px; height:6px; border-radius:50%; background:#4ade80;
  animation: ned-pulse 1.8s ease-in-out infinite;
}
@keyframes ned-pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.8)} }
.ned-save-status { font-size:11px; color:rgba(255,255,255,.6); }
.ned-chip {
  display:inline-flex; align-items:center; gap:.28rem;
  background:rgba(255,255,255,.11); color:rgba(255,255,255,.78);
  font-size:11px; padding:.22em .65em; border-radius:20px;
  border:1px solid rgba(255,255,255,.13);
}
.ned-chip-link { color:var(--kb-gold); text-decoration:none; }
.ned-chip-link:hover { text-decoration:underline; }

/* Hero action buttons */
.ned-hero-actions { display:flex; flex-wrap:wrap; gap:.4rem; align-items:center; }
.ned-hero-actions .btn {
  font-size:12px; font-weight:700; border-radius:var(--kb-radius-sm);
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.38rem .85rem; transition:all var(--kb-tr);
  white-space:nowrap;
}
.ned-btn-save {
  background:var(--kb-gold); border:1.5px solid var(--kb-gold-dark);
  color:#2D1A00; box-shadow:0 2px 8px rgba(201,168,76,.35);
}
.ned-btn-save:hover { background:var(--kb-gold-dark); color:#fff; }
.ned-btn-save:disabled { opacity:.6; cursor:not-allowed; }
.ned-btn-tpl {
  background:rgba(255,255,255,.13); border:1.5px solid rgba(255,255,255,.26); color:#fff;
}
.ned-btn-tpl:hover { background:rgba(255,255,255,.22); color:#fff; }
.ned-btn-export {
  background:rgba(255,255,255,.09); border:1.5px solid rgba(255,255,255,.2); color:#fff;
}
.ned-btn-export:hover { background:rgba(255,255,255,.18); color:#fff; }
.ned-btn-hist {
  background:transparent; border:1.5px solid rgba(255,255,255,.26);
  color:rgba(255,255,255,.78);
}
.ned-btn-hist:hover { border-color:rgba(255,255,255,.65); color:#fff; }

.ned-hero-bar {
  height: 3px;
  background: linear-gradient(90deg, var(--kb-gold) 0%, rgba(201,168,76,.6) 70%, transparent 100%);
}

/* Readonly banner */
.ned-readonly-banner {
  display:flex; align-items:center; gap:.65rem;
  background: linear-gradient(90deg, #FBF6EC, #FFF9ED);
  border: 1px solid #E8D9A0;
  border-left: 3px solid var(--kb-gold);
  border-radius: var(--kb-radius-sm);
  padding: .6rem 1rem;
  font-size: 12.5px;
  color: #6B5000;
  margin-bottom: .75rem;
}
.ned-readonly-banner svg { color: var(--kb-gold); flex-shrink: 0; }

/* ── Layout ────────────────────────────────────────────────────── */
.ned-layout {
  display: grid;
  grid-template-columns: 1fr 300px;
  gap: 1rem;
  align-items: start;
}
@media (max-width: 991.98px) {
  .ned-layout { grid-template-columns: 1fr; }
  .ned-sidebar { order: -1; }
}

/* ── Editor area ───────────────────────────────────────────────── */
.ned-main-col {}

.ned-editor-wrap {
  border: 1px solid var(--kb-border-light);
  border-radius: var(--kb-radius);
  overflow: hidden;
  box-shadow: var(--kb-shadow-md);
  background: #fff;
}
.ned-editor-wrap .ql-toolbar.ql-snow {
  border: none;
  border-bottom: 1px solid var(--kb-border-light);
  background: var(--kb-surface);
  padding: .45rem .75rem;
  position: sticky;
  top: 0;
  z-index: 10;
}
.ned-editor-wrap .ql-container.ql-snow {
  border: none;
  font-size: 15px;
}
.ned-quill-area { min-height: 520px; }

/* word count footer */
.ned-editor-footer {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: .45rem .85rem;
  background: var(--kb-surface);
  border-top: 1px solid var(--kb-border-light);
  font-size: 11.5px;
  color: var(--kb-text-faint);
}

/* ── Diskusi ───────────────────────────────────────────────────── */
.ned-comment-wrap {
  margin-top: .75rem;
  border: 1px solid var(--kb-border-light);
  border-radius: var(--kb-radius);
  overflow: hidden;
  box-shadow: var(--kb-shadow-sm);
  background: #fff;
}
.ned-comment-head {
  display: flex; align-items: center;
  justify-content: space-between;
  padding: .65rem 1rem;
  border-bottom: 1px solid var(--kb-border-light);
  background: var(--kb-surface);
}
.ned-comment-head-left { display:flex; align-items:center; gap:.5rem; }
.ned-comment-title { font-size:13px; font-weight:700; color:var(--kb-primary); }
.ned-count-pill {
  background: var(--kb-primary-light); color: var(--kb-primary);
  font-size: 10.5px; font-weight: 800;
  padding: .15em .5em; border-radius: 20px;
}
.ned-btn-outline-xs {
  background: transparent; border: 1.5px solid var(--kb-border);
  color: var(--kb-text-muted); font-size: 11.5px; font-weight: 600;
  border-radius: var(--kb-radius-xs); padding: .25rem .65rem;
  cursor: pointer; transition: all var(--kb-tr);
  white-space: nowrap;
}
.ned-btn-outline-xs:hover { border-color: var(--kb-primary); color: var(--kb-primary); }

#comment-list { min-height: 52px; }

.ned-comment-box {
  padding: .7rem 1rem;
  border-top: 1px solid var(--kb-border-light);
  background: #fff;
}
.ned-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  color: #fff; font-size: 12px; font-weight: 800;
  display: inline-flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 2px 6px rgba(123,28,28,.2);
}
.ned-comment-input {
  border: 1.5px solid var(--kb-border);
  border-radius: var(--kb-radius-sm);
  padding: .4rem .75rem;
  font-size: 13px; font-family: inherit;
  width: 100%; outline: none;
  resize: none; background: #fff;
  color: var(--kb-text);
  transition: border-color var(--kb-tr), box-shadow var(--kb-tr);
  line-height: 1.5;
}
.ned-comment-input:focus {
  border-color: var(--kb-primary);
  box-shadow: 0 0 0 3px var(--kb-primary-ring);
}
.ned-btn-send {
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  border: none; color: #fff;
  font-size: 12px; font-weight: 700;
  border-radius: var(--kb-radius-xs);
  padding: .32rem .8rem;
  display: inline-flex; align-items: center; gap: .28rem;
  cursor: pointer; transition: all var(--kb-tr);
  box-shadow: 0 2px 8px rgba(123,28,28,.22);
}
.ned-btn-send:hover { filter: brightness(1.1); }
.ned-reply-indicator {
  font-size: 11px; color: var(--kb-text-muted);
  display: inline-flex; align-items: center; gap: .25rem;
}

#mention-dropdown {
  position: absolute; z-index: 1050;
  bottom: calc(100% + 4px); left: 0;
  min-width: 180px; max-height: 180px; overflow-y: auto;
  background: #fff;
  border: 1px solid var(--kb-border);
  border-radius: var(--kb-radius-sm);
  box-shadow: var(--kb-shadow-md);
}

/* ── Sidebar ───────────────────────────────────────────────────── */
.ned-sidebar { display: flex; flex-direction: column; gap: .75rem; }

.ned-card {
  border: 1px solid var(--kb-border-light);
  border-radius: var(--kb-radius);
  overflow: hidden;
  box-shadow: var(--kb-shadow-sm);
  background: #fff;
}
.ned-card-head {
  display: flex; align-items: center; gap: .38rem;
  font-size: 10.5px; font-weight: 800;
  letter-spacing: .07em; text-transform: uppercase;
  color: var(--kb-primary);
  background: var(--kb-surface);
  padding: .5rem .9rem;
  border-bottom: 1px solid var(--kb-border-light);
}
.ned-card-head svg { opacity: .7; flex-shrink: 0; }
.ned-card-head .ms-auto { margin-left: auto; }
.ned-card-body { padding: .75rem .9rem; }
.ned-card-foot {
  padding: .55rem .9rem;
  background: var(--kb-surface);
  border-top: 1px solid var(--kb-border-light);
}

/* Info dl */
.ned-info-dl {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: .3rem .75rem;
  font-size: 12.5px; margin: 0;
}
.ned-info-dl dt { color: var(--kb-text-muted); font-weight: 700; white-space: nowrap; padding-top: .05rem; }
.ned-info-dl dd { color: var(--kb-text); margin: 0; }
.ned-link { color: var(--kb-primary); text-decoration: none; }
.ned-link:hover { text-decoration: underline; }

/* Durasi pill */
.ned-dur-pill {
  display: inline-flex; align-items: center; gap: .3rem;
  background: var(--kb-primary-light); color: var(--kb-primary);
  font-size: 11px; font-weight: 700;
  padding: .2em .65em; border-radius: 20px;
}

/* Back button */
.ned-btn-back {
  display: flex; align-items: center; justify-content: center; gap: .35rem;
  width: 100%; padding: .42rem;
  background: transparent;
  border: 1.5px solid var(--kb-border);
  border-radius: var(--kb-radius-sm);
  font-size: 12.5px; font-weight: 600;
  color: var(--kb-text);
  cursor: pointer; transition: all var(--kb-tr);
  text-decoration: none;
}
.ned-btn-back:hover {
  border-color: var(--kb-primary);
  color: var(--kb-primary);
  background: var(--kb-primary-light);
}

/* Upload form */
.ned-upload-form {
  padding: .75rem .9rem;
  border-bottom: 1px solid var(--kb-border-light);
  background: #FFFDF8;
}
.ned-form-label { font-size: 12px; font-weight: 700; color: var(--kb-text); display: block; margin-bottom: .2rem; }
.ned-form-hint  { font-size: 11px; color: var(--kb-text-faint); margin-top: .2rem; }
.ned-btn-upload {
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  border: none; color: #fff;
  font-size: 12px; font-weight: 700;
  border-radius: var(--kb-radius-sm);
  padding: .38rem .75rem;
  display: inline-flex; align-items: center; gap: .28rem;
  cursor: pointer; transition: all var(--kb-tr);
}
.ned-btn-upload:hover { filter: brightness(1.1); }
.ned-btn-cancel {
  background: transparent;
  border: 1.5px solid var(--kb-border);
  color: var(--kb-text-muted);
  font-size: 12px; border-radius: var(--kb-radius-sm);
  padding: .38rem .75rem; cursor: pointer;
  transition: all var(--kb-tr);
}
.ned-btn-cancel:hover { border-color: var(--kb-text-muted); }

/* Attachment list */
.ned-attach-list { max-height: 240px; overflow-y: auto; }
.ned-attach-loading { padding: .8rem; text-align: center; font-size: 12.5px; color: var(--kb-text-muted); }

/* TL items */
.ned-tl-item {
  padding: .6rem .9rem;
  border-bottom: 1px solid var(--kb-border-light);
  background: #fff;
  transition: background var(--kb-tr);
}
.ned-tl-item:last-child { border-bottom: none; }
.ned-tl-item:hover { background: var(--kb-surface); }
.ned-tl-desc {
  font-size: 12.5px; font-weight: 600;
  color: var(--kb-text); line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.ned-tl-meta {
  font-size: 11px; color: var(--kb-text-muted);
  display: flex; align-items: center; gap: .22rem;
  margin-top: .22rem;
}
.ned-tl-del {
  background: transparent; border: none;
  color: var(--kb-red); cursor: pointer;
  width: 22px; height: 22px;
  border-radius: var(--kb-radius-xs);
  display: inline-flex; align-items: center; justify-content: center;
  transition: background var(--kb-tr); padding: 0; flex-shrink: 0;
}
.ned-tl-del:hover { background: var(--kb-red-bg); }
.ned-tl-empty {
  padding: 1.2rem; text-align: center;
  font-size: 12.5px; color: var(--kb-text-faint);
}

/* Add TL button inline */
.ned-btn-add {
  background: linear-gradient(135deg, var(--kb-primary), #9B2020);
  border: none; color: #fff;
  font-size: 10.5px; font-weight: 800;
  border-radius: var(--kb-radius-xs);
  padding: .25rem .55rem;
  display: inline-flex; align-items: center; gap: .25rem;
  cursor: pointer; transition: all var(--kb-tr);
  box-shadow: 0 1px 5px rgba(123,28,28,.18);
}
.ned-btn-add:hover { filter: brightness(1.1); }

/* Badges */
.ned-badge {
  display: inline-flex; align-items: center;
  font-size: 10px; font-weight: 700;
  padding: .18em .6em; border-radius: 20px; white-space: nowrap;
}
.kb-badge-red    { background:var(--kb-red-bg);    color:var(--kb-red); }
.kb-badge-gold   { background:var(--kb-gold-light); color:#7A5C00; }
.kb-badge-green  { background:var(--kb-green-bg);  color:var(--kb-green); }
.kb-badge-blue   { background:var(--kb-blue-bg);   color:var(--kb-blue); }
.kb-badge-gray   { background:var(--kb-gray-bg);   color:var(--kb-text-muted); }

/* Modal icon */
.ned-modal-icon {
  width: 30px; height: 30px;
  background: var(--kb-primary-light);
  border-radius: var(--kb-radius-xs);
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--kb-primary); flex-shrink: 0;
}

/* ── Responsive ────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
  .ned-hero-inner  { padding: .9rem 1rem; }
  .ned-hero-title  { font-size: 14.5px; }
  .ned-hero-actions .btn { font-size: 11.5px; padding: .32rem .7rem; }
}
</style>

<!-- ============================================================
     HERO
============================================================ -->
<div class="ned-hero">
  <div class="ned-hero-inner">
    <div class="ned-hero-row">

      <div class="ned-hero-left">
        <nav class="ned-breadcrumb" aria-label="breadcrumb">
          <a href="<?= $backUrl ?>">Detail Kegiatan</a>
          <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Notulen</span>
        </nav>
        <h1 class="ned-hero-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <?= htmlspecialchars($meeting['title']) ?>
        </h1>
        <div class="ned-hero-meta">
          <span id="sync-status" class="ned-live-badge">
            <span class="ned-live-dot"></span>Live
          </span>
          <span id="save-status" class="ned-save-status">Tersimpan</span>
          <span class="ned-chip">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y · H:i', strtotime($meeting['start_datetime'])) ?>
          </span>
          <?php if (!empty($loc)): ?>
          <span class="ned-chip">
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ned-chip-link">🔗 Link</a>
            <?php else: ?>
            <?= htmlspecialchars($loc) ?>
            <?php endif; ?>
          </span>
          <?php endif; ?>
          <?php
          $ms   = $meetingBadge[$meeting['status']] ?? 'kb-badge-gray';
          $mslb = $meetingStatusLabel[$meeting['status']] ?? ucfirst($meeting['status']);
          ?>
          <span class="ned-badge <?= $ms ?>" style="font-size:10.5px;"><?= $mslb ?></span>
        </div>
      </div>

      <div class="ned-hero-actions">
        <?php if ($canEdit): ?>
        <button type="button" class="btn ned-btn-tpl"
                data-bs-toggle="modal" data-bs-target="#modalPickTemplate">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Template
        </button>
        <button id="btn-save-manual" class="btn ned-btn-save">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan
        </button>
        <?php endif; ?>
        <a href="<?= $docxUrl ?>" class="btn ned-btn-export">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          Export Word
        </a>
        <?php if ($canEdit): ?>
        <a href="<?= $histUrl ?>" class="btn ned-btn-hist">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
          Riwayat
        </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
  <div class="ned-hero-bar"></div>
</div>

<?php if (!$canEdit): ?>
<div class="ned-readonly-banner" role="alert" id="readonly-banner">
  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  <span>Anda hanya dapat membaca notulen ini. Pengeditan tidak tersedia untuk peran Anda.</span>
  <button type="button" style="margin-left:auto;background:none;border:none;color:#6B5000;cursor:pointer;padding:0;line-height:1;font-size:16px;opacity:.7;" onclick="document.getElementById('readonly-banner').remove()" aria-label="Tutup">&times;</button>
</div>
<?php endif; ?>

<!-- ============================================================
     BODY — GRID LAYOUT
============================================================ -->
<div class="ned-layout">

  <!-- ═══════════════ MAIN COLUMN ═══════════════ -->
  <div class="ned-main-col">

    <!-- Editor -->
    <div class="ned-editor-wrap">
      <div id="quill-editor" class="ned-quill-area"></div>
      <div class="ned-editor-footer">
        <span id="word-count">0 kata</span>
        <span>Ctrl+S untuk simpan</span>
      </div>
    </div>

    <!-- Diskusi -->
    <div class="ned-comment-wrap" id="comment-panel">
      <div class="ned-comment-head">
        <div class="ned-comment-head-left">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--kb-primary)"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span class="ned-comment-title">Diskusi</span>
          <span class="ned-count-pill" id="comment-count">0</span>
        </div>
        <button class="ned-btn-outline-xs" id="btn-toggle-resolved">Tampilkan Selesai</button>
      </div>

      <div id="comment-list"></div>

      <div class="ned-comment-box">
        <div style="display:flex;gap:.6rem;align-items:flex-start;">
          <span class="ned-avatar"><?= $currentUserInitial ?></span>
          <div style="flex:1;">
            <div class="position-relative">
              <div id="mention-dropdown" class="dropdown-menu"></div>
              <textarea id="comment-input" class="ned-comment-input" rows="2"
                        placeholder="Tulis komentar… (@ untuk mention)"></textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:.35rem;">
              <span class="ned-reply-indicator" id="reply-indicator"></span>
              <button class="ned-btn-send" id="btn-submit-comment">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Kirim
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
  <!-- ═══════════════ END MAIN ═══════════════ -->

  <!-- ═══════════════ SIDEBAR ═══════════════ -->
  <div class="ned-sidebar">

    <!-- Info Kegiatan -->
    <div class="ned-card">
      <div class="ned-card-head">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Info Kegiatan
      </div>
      <div class="ned-card-body">
        <dl class="ned-info-dl">
          <dt>Mulai</dt>
          <dd><?= date('d M Y', strtotime($meeting['start_datetime'])) ?><br>
              <span style="color:var(--kb-text-faint);font-size:11px;"><?= date('H:i', strtotime($meeting['start_datetime'])) ?> &mdash; <?= date('H:i', strtotime($meeting['end_datetime'])) ?></span></dd>
          <dt>Durasi</dt>
          <dd><span class="ned-dur-pill">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <?= $durStr ?>
          </span></dd>
          <?php if (!empty($loc)): ?>
          <dt>Lokasi</dt>
          <dd>
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc, ENT_QUOTES) ?>" target="_blank" rel="noopener" class="ned-link">Buka Link &rarr;</a>
            <?php else: ?>
            <?= htmlspecialchars($loc) ?>
            <?php endif; ?>
          </dd>
          <?php endif; ?>
          <dt>Status</dt>
          <dd>
            <?php $ms = $meetingBadge[$meeting['status']] ?? 'kb-badge-gray'; ?>
            <span class="ned-badge <?= $ms ?>">
              <?= $meetingStatusLabel[$meeting['status']] ?? ucfirst($meeting['status']) ?>
            </span>
          </dd>
        </dl>
      </div>
      <div class="ned-card-foot">
        <a href="<?= $backUrl ?>" class="ned-btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Detail
        </a>
      </div>
    </div>

    <!-- Lampiran -->
    <div class="ned-card" id="attachment-panel" data-meeting-id="<?= (int)$meeting['id'] ?>">
      <div class="ned-card-head">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        Lampiran
        <span class="ned-count-pill" id="attach-count">0</span>
        <?php if ($canEdit): ?>
        <button class="ned-btn-add ms-auto" id="btn-show-upload-form">
          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload
        </button>
        <?php endif; ?>
      </div>

      <?php if ($canEdit): ?>
      <div id="upload-form-wrapper" style="display:none;" class="ned-upload-form">
        <form id="form-upload-attachment" enctype="multipart/form-data">
          <div class="mb-2">
            <label class="ned-form-label">Pilih File <span style="color:var(--kb-red);">*</span></label>
            <input type="file" id="attach-file" class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
            <div class="ned-form-hint">PDF, Office, Gambar, ZIP &middot; maks. 10 MB</div>
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
            <button type="submit" class="ned-btn-upload flex-fill" id="btn-do-upload">
              <span id="upload-spinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
              Upload
            </button>
            <button type="button" class="ned-btn-cancel" id="btn-cancel-upload">Batal</button>
          </div>
          <div id="upload-alert" class="d-none mt-2"></div>
        </form>
      </div>
      <?php endif; ?>

      <div id="attachment-list" class="ned-attach-list">
        <div class="ned-attach-loading">
          <span class="spinner-border spinner-border-sm"></span> Memuat…
        </div>
      </div>
    </div>

    <!-- Tindak Lanjut -->
    <div class="ned-card">
      <div class="ned-card-head">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tindak Lanjut
        <?php if ($canEdit): ?>
        <button class="ned-btn-add ms-auto"
                data-bs-toggle="modal" data-bs-target="#modalTL">
          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah
        </button>
        <?php endif; ?>
      </div>
      <div id="tl-list">
        <?php if (empty($tindakLanjutList)): ?>
        <div class="ned-tl-empty" id="tl-empty">Belum ada tindak lanjut</div>
        <?php else: ?>
        <?php foreach (($tindakLanjutList ?? []) as $tl):
          $pc   = $priorityBadge[$tl['priority']] ?? 'kb-badge-gray';
          $plbl = $priorityLabel[$tl['priority']]  ?? ucfirst($tl['priority']);
          $sc   = $statusBadge[$tl['status']]       ?? 'kb-badge-gray';
          $slbl = $statusLabel[$tl['status']]        ?? ucfirst(str_replace('_',' ',$tl['status']));
        ?>
        <div class="ned-tl-item" id="tl-item-<?= (int)$tl['id'] ?>">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
            <span class="ned-tl-desc"><?= htmlspecialchars($tl['description']) ?></span>
            <?php if ($canEdit): ?>
            <button class="ned-tl-del btn-tl-del"
                    data-id="<?= (int)$tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>/delete"
                    title="Hapus tindak lanjut">
              <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>
          </div>
          <div class="ned-tl-meta">
            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($tl['assigned_name'] ?? '—') ?>
            <?php if (!empty($tl['due_date'])): ?>
            &middot;
            <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y', strtotime($tl['due_date'])) ?>
            <?php endif; ?>
          </div>
          <div style="display:flex;gap:.28rem;margin-top:.28rem;">
            <span class="ned-badge <?= $pc ?>"><?= $plbl ?></span>
            <span class="ned-badge <?= $sc ?>"><?= $slbl ?></span>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>
  <!-- ═══════════════ END SIDEBAR ═══════════════ -->

</div>

<!-- ============================================================
     MODALS
============================================================ -->
<?php if ($canEdit): ?>

<!-- Modal Template -->
<div class="modal modal-blur fade" id="modalPickTemplate" tabindex="-1" aria-labelledby="modalPickTemplateLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--kb-surface);border-bottom:1px solid var(--kb-border-light);">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </span>
          <h5 class="modal-title" id="modalPickTemplateLabel" style="font-size:15px;font-weight:700;color:var(--kb-text);">Pilih Template Notulen</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 d-flex align-items-start gap-2 mb-3" style="font-size:13px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <span>Memilih template akan <strong>mengganti seluruh isi</strong> notulen. Simpan dulu jika ada perubahan penting.</span>
        </div>
        <div id="tpl-list-loading" class="text-center py-5">
          <span class="spinner-border spinner-border-sm" style="color:var(--kb-primary);"></span>
          <div style="font-size:12.5px;color:var(--kb-text-muted);margin-top:.5rem;">Memuat template…</div>
        </div>
        <div id="tpl-list-container" class="row g-3" style="display:none;"></div>
      </div>
      <div class="modal-footer" style="background:var(--kb-surface);border-top:1px solid var(--kb-border-light);">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah TL -->
<div class="modal modal-blur fade" id="modalTL" tabindex="-1" aria-labelledby="modalTLLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header" style="background:var(--kb-surface);border-bottom:1px solid var(--kb-border-light);">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </span>
          <h5 class="modal-title" id="modalTLLabel" style="font-size:15px;font-weight:700;color:var(--kb-text);">Tambah Tindak Lanjut</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="ned-form-label" for="tl2-desk">Deskripsi Tugas <span style="color:var(--kb-red);">*</span></label>
          <textarea id="tl2-desk" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan hasil evaluasi Q2…"
                    style="font-size:13.5px;" required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="ned-form-label" for="tl2-assign">Ditugaskan ke</label>
            <select id="tl2-assign" class="form-select form-select-sm">
              <option value="">— Pilih —</option>
              <?php foreach ($tlUsers as $u): ?>
              <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="ned-form-label" for="tl2-deadline">Deadline</label>
            <input type="date" id="tl2-deadline" class="form-control form-control-sm"
                   min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-3">
          <label class="ned-form-label" for="tl2-priority">Prioritas</label>
          <div style="display:flex;gap:.5rem;margin-top:.3rem;" id="tl2-priority-group">
            <label style="flex:1;cursor:pointer;">
              <input type="radio" name="tl2-priority" value="low" style="display:none;"> 
              <span class="ned-badge kb-badge-green" style="width:100%;justify-content:center;padding:.35em;cursor:pointer;border:1.5px solid transparent;" data-val="low">Rendah</span>
            </label>
            <label style="flex:1;cursor:pointer;">
              <input type="radio" name="tl2-priority" value="medium" checked style="display:none;">
              <span class="ned-badge kb-badge-gold" style="width:100%;justify-content:center;padding:.35em;cursor:pointer;border:1.5px solid var(--kb-gold);" data-val="medium">Sedang</span>
            </label>
            <label style="flex:1;cursor:pointer;">
              <input type="radio" name="tl2-priority" value="high" style="display:none;">
              <span class="ned-badge kb-badge-red" style="width:100%;justify-content:center;padding:.35em;cursor:pointer;border:1.5px solid transparent;" data-val="high">Tinggi</span>
            </label>
          </div>
          <input type="hidden" id="tl2-priority" value="medium">
        </div>
      </div>
      <div class="modal-footer" style="background:var(--kb-surface);border-top:1px solid var(--kb-border-light);">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-tl2-save"
                style="background:linear-gradient(135deg,var(--kb-primary),#9B2020);border:none;color:#fff;font-size:13px;font-weight:700;border-radius:8px;padding:.42rem 1.15rem;display:inline-flex;align-items:center;gap:.35rem;cursor:pointer;">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<?php endif; ?>

<!-- ============================================================
     GLOBALS + BOOTSTRAP SCRIPT
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

  /* ── Ctrl+S shortcut ── */
  document.addEventListener('keydown', function (e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
      e.preventDefault();
      var btn = document.getElementById('btn-save-manual');
      if (btn && !btn.disabled) btn.click();
    }
  });

  /* ── Priority radio pills (modal TL) ── */
  var prioGroup = document.getElementById('tl2-priority-group');
  var prioHidden = document.getElementById('tl2-priority');
  if (prioGroup) {
    prioGroup.querySelectorAll('input[type=radio]').forEach(function(radio) {
      radio.addEventListener('change', function() {
        if (prioHidden) prioHidden.value = this.value;
        prioGroup.querySelectorAll('span[data-val]').forEach(function(s) {
          s.style.border = '1.5px solid transparent';
        });
        var lbl = prioGroup.querySelector('span[data-val="' + this.value + '"]');
        if (lbl) lbl.style.border = '1.5px solid currentColor';
      });
    });
  }

<?php if ($canEdit): ?>
  /* ── Template picker ── */
  var TPL_API_URL    = window.BASE_URL + '/api/notulen-templates';
  var TPL_MANAGE_URL = window.BASE_URL + '/notulen-templates';
  var tplListLoaded  = false;

  var modalEl = document.getElementById('modalPickTemplate');
  if (modalEl) {
    modalEl.addEventListener('show.bs.modal', function () {
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
            container.innerHTML =
              '<div class="col-12 text-center py-4" style="color:var(--kb-text-muted);font-size:13px;">' +
              'Belum ada template. <a href="' + TPL_MANAGE_URL + '" target="_blank" style="color:var(--kb-primary);font-weight:700;">Buat template</a></div>';
            return;
          }

          data.templates.forEach(function (tpl) {
            var col = document.createElement('div');
            col.className = 'col-md-6';
            col.innerHTML =
              '<div style="background:#fff;border:1px solid var(--kb-border-light);border-radius:var(--kb-radius);overflow:hidden;box-shadow:var(--kb-shadow-sm);height:100%;display:flex;flex-direction:column;">' +
                '<div style="padding:.75rem 1rem;flex:1;">' +
                  '<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.28rem;">' +
                    '<span style="font-size:13.5px;font-weight:700;color:var(--kb-text);">' + tpl.name + '</span>' +
                    (tpl.is_default == 1 ? '<span class="ned-badge kb-badge-green">Default</span>' : '') +
                  '</div>' +
                  '<p style="font-size:12px;color:var(--kb-text-muted);margin:0;line-height:1.5;">' + (tpl.description || '—') + '</p>' +
                '</div>' +
                '<div style="padding:.5rem 1rem;background:var(--kb-surface);border-top:1px solid var(--kb-border-light);">' +
                  '<button style="width:100%;background:linear-gradient(135deg,var(--kb-primary),#9B2020);border:none;color:#fff;font-size:12px;font-weight:700;border-radius:7px;padding:.35rem;cursor:pointer;transition:filter .15s;" ' +
                  'class="btn-apply-tpl" data-tpl-id="' + tpl.id + '">Gunakan Template Ini</button>' +
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
                  var closeBtn = modalEl.querySelector('[data-bs-dismiss="modal"]');
                  if (closeBtn) closeBtn.click();
                  var ss = document.getElementById('save-status');
                  if (ss) { ss.textContent = '● Belum disimpan'; ss.style.color = 'rgba(255,200,50,.9)'; }
                })
                .catch(function () { alert('Gagal memuat template.'); });
            });
          });
        })
        .catch(function () {
          var loading = document.getElementById('tpl-list-loading');
          if (loading) loading.innerHTML = '<p class="text-danger small mb-0 text-center">Gagal memuat template. Coba refresh halaman.</p>';
        });
    });
  }
<?php endif; ?>

  /* ── Load Quill + editor script ── */
  function loadEditorScript() {
    var es   = document.createElement('script');
    es.src   = <?= json_encode(rtrim(BASE_URL, '/') . '/assets/js/notulen-editor.js?v=' . $editorJsVer) ?>;
    es.onerror = function () { console.error('Gagal memuat notulen-editor.js'); };
    document.body.appendChild(es);
  }

  if (typeof window.Quill === 'undefined') {
    var qs     = document.createElement('script');
    qs.src     = 'https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js';
    qs.onload  = loadEditorScript;
    qs.onerror = function () { console.error('Gagal memuat Quill dari CDN.'); };
    document.head.appendChild(qs);
  } else {
    loadEditorScript();
  }
})();
</script>
