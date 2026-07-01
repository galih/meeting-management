<?php
/**
 * View: Fitur Dokumen — Fase 1 + 2
 * Variabel dari DokumenController::index():
 *   $folders, $files, $stats, $breadcrumb
 *   $section (my-files|shared|recent)
 *   $folderId, $filterType, $search
 */
$base      = rtrim(BASE_URL, '/');
$isAdmin   = Auth::hasRole('admin', 'sekretaris');
$canUpload = Auth::hasRole('admin', 'sekretaris');
$myId      = (int)(Auth::user()['id'] ?? 0);
?>
<style>
/* ===== DM: Dokumen Namespace ===== */
.dm-wrap   { display:flex; gap:0; min-height:calc(100vh - 64px); }

/* Sidebar */
.dm-sidebar {
  width:220px; flex-shrink:0;
  background:#fff;
  border-right:1px solid #E8E2D9;
  padding:1.25rem .85rem;
  display:flex; flex-direction:column; gap:.15rem;
}
.dm-sidebar-section { font-size:10.5px; font-weight:800; color:#A89E90; letter-spacing:.06em; text-transform:uppercase; padding:.55rem .6rem .25rem; margin-top:.5rem; }
.dm-sidebar-link {
  display:flex; align-items:center; gap:.6rem;
  padding:.5rem .75rem; border-radius:8px;
  font-size:13.5px; font-weight:600; color:#4A5568;
  text-decoration:none; cursor:pointer; border:none; background:none; width:100%;
  transition:background 140ms, color 140ms;
}
.dm-sidebar-link:hover  { background:#F5F0E8; color:#1C1714; }
.dm-sidebar-link.active { background:#7B1C1C; color:#fff; }
.dm-sidebar-link.active svg { stroke:#fff; opacity:1; }
.dm-sidebar-link svg { opacity:.6; flex-shrink:0; }
.dm-sidebar-badge {
  margin-left:auto; background:rgba(123,28,28,.12); color:#7B1C1C;
  font-size:10.5px; font-weight:800; border-radius:20px; padding:.1em .55em;
}
.dm-sidebar-link.active .dm-sidebar-badge { background:rgba(255,255,255,.25); color:#fff; }

.dm-upload-btn {
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  width:100%; height:40px; border-radius:10px;
  background:#7B1C1C; color:#fff; font-size:13.5px; font-weight:700;
  border:none; cursor:pointer; margin-bottom:.75rem;
  transition:background 180ms;
}
.dm-upload-btn:hover { background:#5A1212; }

/* Main */
.dm-main    { flex:1; min-width:0; padding:1.5rem; overflow-y:auto; }
.dm-toolbar {
  display:flex; align-items:center; gap:.65rem; flex-wrap:wrap;
  margin-bottom:1.25rem;
}
.dm-search {
  flex:1; min-width:180px; max-width:340px;
  display:flex; align-items:center;
  border:1.5px solid #DDD5C4; border-radius:9px;
  background:#FDFCFA; overflow:hidden; transition:border-color 180ms;
}
.dm-search:focus-within { border-color:#7B1C1C; box-shadow:0 0 0 3px rgba(123,28,28,.07); }
.dm-search svg    { flex-shrink:0; margin:0 .6rem; color:#A89E90; }
.dm-search input  { border:none; background:none; outline:none; font-size:13.5px; color:#1C1714; height:38px; flex:1; padding-right:.75rem; }
.dm-filter-select {
  height:38px; border:1.5px solid #DDD5C4; border-radius:9px;
  background:#FDFCFA; font-size:13px; color:#1C1714; padding:0 2rem 0 .75rem;
  appearance:none; -webkit-appearance:none; outline:none; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B6055' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right .6rem center;
  transition:border-color 180ms;
}
.dm-filter-select:focus { border-color:#7B1C1C; }

/* Breadcrumb */
.dm-breadcrumb { display:flex; align-items:center; gap:.35rem; flex-wrap:wrap; margin-bottom:1rem; }
.dm-breadcrumb a  { font-size:12.5px; font-weight:600; color:#7B1C1C; text-decoration:none; }
.dm-breadcrumb a:hover { text-decoration:underline; }
.dm-breadcrumb span { font-size:12px; color:#A89E90; }
.dm-breadcrumb-cur { font-size:12.5px; font-weight:700; color:#1C1714; }

/* Stats bar */
.dm-stats {
  display:flex; gap:.75rem; flex-wrap:wrap;
  padding:.85rem 1rem;
  background:#F5F0E8; border:1px solid #E8E2D9; border-radius:10px;
  margin-bottom:1.25rem;
}
.dm-stat-item { font-size:12px; color:#6B6055; }
.dm-stat-item strong { font-weight:800; color:#1C1714; }

/* Section title */
.dm-section-title {
  font-size:13.5px; font-weight:800; color:#1C1714;
  display:flex; align-items:center; gap:.5rem; margin-bottom:.85rem;
}
.dm-section-title svg { color:#7B1C1C; }

/* Folder grid */
.dm-folder-grid {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(200px, 1fr));
  gap:.75rem; margin-bottom:1.5rem;
}
.dm-folder-card {
  background:#fff; border:1px solid #E8E2D9; border-radius:10px;
  padding:.85rem 1rem;
  display:flex; align-items:center; gap:.75rem;
  cursor:pointer; text-decoration:none;
  transition:border-color 180ms, box-shadow 180ms;
  position:relative;
}
.dm-folder-card:hover  { border-color:#7B1C1C; box-shadow:0 3px 12px rgba(123,28,28,.08); }
.dm-folder-icon {
  width:40px; height:40px; flex-shrink:0;
  background:#FEF3C7; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
}
.dm-folder-name { font-size:13px; font-weight:700; color:#1C1714; word-break:break-word; }
.dm-folder-meta { font-size:11px; color:#A89E90; margin-top:.1rem; }
.dm-folder-menu {
  position:absolute; top:.5rem; right:.5rem;
  width:26px; height:26px; display:flex; align-items:center; justify-content:center;
  border-radius:6px; background:none; border:none; cursor:pointer; color:#A89E90;
  opacity:0; transition:opacity 140ms, background 140ms;
}
.dm-folder-card:hover .dm-folder-menu { opacity:1; }
.dm-folder-menu:hover { background:#F5F0E8; color:#1C1714; }

/* File table */
.dm-table-wrap {
  background:#fff; border:1px solid #E8E2D9;
  border-radius:12px; overflow:hidden;
  box-shadow:0 2px 10px rgba(28,23,20,.05);
}
.dm-table { width:100%; border-collapse:collapse; }
.dm-table thead th {
  padding:.75rem 1rem; font-size:11px; font-weight:800;
  color:#6B6055; letter-spacing:.04em; text-transform:uppercase;
  background:#F9F7F4; border-bottom:1px solid #E8E2D9;
  white-space:nowrap;
}
.dm-table tbody tr {
  border-bottom:1px solid #F0EBE2;
  transition:background 120ms;
}
.dm-table tbody tr:last-child { border-bottom:none; }
.dm-table tbody tr:hover { background:#FDFCFA; }
.dm-table td { padding:.75rem 1rem; font-size:13.5px; color:#1C1714; vertical-align:middle; }
.dm-file-icon {
  width:34px; height:34px; border-radius:7px;
  display:inline-flex; align-items:center; justify-content:center;
  font-size:10.5px; font-weight:800; color:#fff; flex-shrink:0;
}
.dm-file-name { font-weight:600; color:#1C1714; word-break:break-word; }
.dm-file-size  { font-size:12px; color:#A89E90; }
.dm-badge {
  display:inline-flex; align-items:center;
  font-size:10.5px; font-weight:700; border-radius:5px;
  padding:.2em .55em; color:#fff;
}
.dm-perm-badge {
  display:inline-flex; align-items:center; gap:.25rem;
  font-size:11px; font-weight:700; border-radius:6px;
  padding:.2em .6em;
}
.dm-perm-view     { background:#EBF8FF; color:#2B6CB0; }
.dm-perm-download { background:#F0FFF4; color:#276749; }

/* Shared-by chip */
.dm-shared-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  background:#F5F0E8; border-radius:20px;
  font-size:11.5px; font-weight:600; color:#6B6055;
  padding:.15em .65em;
}

/* Buttons */
.dm-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:700;
  height:32px; padding:0 .85rem;
  border-radius:7px; cursor:pointer;
  border:1.5px solid transparent;
  transition:all 180ms; white-space:nowrap;
}
.dm-btn-outline { background:#fff; color:#6B6055; border-color:#DDD5C4; }
.dm-btn-outline:hover { border-color:#7B1C1C; color:#7B1C1C; }
.dm-btn-danger  { background:#fff; color:#C05621; border-color:#DDD5C4; }
.dm-btn-danger:hover  { background:#C05621; color:#fff; }
.dm-btn-share   { background:#fff; color:#2B6CB0; border-color:#DDD5C4; }
.dm-btn-share:hover   { background:#2B6CB0; color:#fff; }
.dm-btn-sm { height:28px; font-size:12px; padding:0 .65rem; }

/* Msg */
.dm-msg { margin-top:.6rem; font-size:12.5px; }
.dm-msg-ok  { color:#27A155; display:flex; align-items:center; gap:.35rem; }
.dm-msg-err { color:#C05621; display:flex; align-items:center; gap:.35rem; }

/* Empty state */
.dm-empty {
  text-align:center; padding:3.5rem 1rem;
  color:#A89E90;
}
.dm-empty svg { display:block; margin:0 auto 1rem; color:#DDD5C4; }
.dm-empty p { font-size:13.5px; margin:.25rem 0 0; }

/* Modal */
.dm-modal-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.45);
  z-index:1050; display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none; transition:opacity 180ms;
}
.dm-modal-overlay.open { opacity:1; pointer-events:auto; }
.dm-modal {
  background:#fff; border-radius:14px;
  width:100%; max-width:460px; max-height:90vh; overflow-y:auto;
  box-shadow:0 20px 60px rgba(0,0,0,.18);
  transform:translateY(16px) scale(.97); transition:transform 180ms;
}
.dm-modal-overlay.open .dm-modal { transform:none; }
.dm-modal-header {
  padding:1.1rem 1.25rem .85rem;
  border-bottom:1px solid #F0EBE2;
  display:flex; align-items:center; justify-content:space-between;
}
.dm-modal-title { font-size:15px; font-weight:800; color:#1C1714; }
.dm-modal-close {
  background:none; border:none; cursor:pointer; color:#A89E90;
  width:28px; height:28px; border-radius:6px; display:flex; align-items:center; justify-content:center;
}
.dm-modal-close:hover { background:#F5F0E8; color:#1C1714; }
.dm-modal-body { padding:1.25rem; }
.dm-modal-footer { padding:.85rem 1.25rem 1.25rem; display:flex; gap:.5rem; justify-content:flex-end; }

/* Label / Input in modal */
.dm-label { display:block; font-size:11.5px; font-weight:700; color:#6B6055; letter-spacing:.03em; text-transform:uppercase; margin-bottom:.3rem; }
.dm-ctrl  { width:100%; height:40px; border:1.5px solid #DDD5C4; border-radius:8px; padding:0 .85rem; font-size:13.5px; color:#1C1714; background:#FDFCFA; outline:none; transition:border-color 180ms; }
.dm-ctrl:focus { border-color:#7B1C1C; box-shadow:0 0 0 3px rgba(123,28,28,.07); }

/* User search dropdown */
.dm-user-search-wrap { position:relative; }
.dm-user-dropdown {
  position:absolute; top:calc(100% + 4px); left:0; right:0;
  background:#fff; border:1.5px solid #DDD5C4; border-radius:8px;
  box-shadow:0 8px 24px rgba(0,0,0,.1); z-index:100;
  max-height:200px; overflow-y:auto; display:none;
}
.dm-user-dropdown.open { display:block; }
.dm-user-option {
  padding:.55rem .85rem; cursor:pointer;
  font-size:13px; color:#1C1714;
  display:flex; align-items:center; gap:.5rem;
  transition:background 120ms;
}
.dm-user-option:hover { background:#F5F0E8; }
.dm-user-option small { color:#A89E90; font-size:11.5px; }

/* Share list */
.dm-share-list { margin-top:1rem; display:flex; flex-direction:column; gap:.4rem; }
.dm-share-item {
  display:flex; align-items:center; gap:.65rem;
  background:#F9F7F4; border:1px solid #EDE8DF;
  border-radius:8px; padding:.55rem .75rem;
}
.dm-share-avatar {
  width:30px; height:30px; border-radius:50%;
  background:#7B1C1C; color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:12px; font-weight:800; flex-shrink:0;
}
.dm-share-info { flex:1; min-width:0; }
.dm-share-name { font-size:13px; font-weight:700; color:#1C1714; }
.dm-share-role { font-size:11px; color:#A89E90; }
.dm-share-perm-select {
  border:1.5px solid #DDD5C4; border-radius:6px;
  background:#fff; font-size:12px; font-weight:700;
  padding:.25rem .5rem; cursor:pointer; outline:none;
  transition:border-color 180ms;
}
.dm-share-perm-select:focus { border-color:#7B1C1C; }
.dm-share-revoke {
  background:none; border:none; cursor:pointer;
  color:#A89E90; width:24px; height:24px;
  border-radius:5px; display:flex; align-items:center; justify-content:center;
  flex-shrink:0; transition:background 120ms, color 120ms;
}
.dm-share-revoke:hover { background:#FEE2E2; color:#C05621; }

/* Dropzone */
.dm-dropzone {
  border:2px dashed #DDD5C4; border-radius:10px;
  background:#FDFCFA; padding:2rem 1.25rem; text-align:center;
  cursor:pointer; transition:border-color 180ms, background 180ms;
}
.dm-dropzone:hover,.dm-dropzone.dragover { border-color:#7B1C1C; background:rgba(123,28,28,.025); }
.dm-dropzone svg   { display:block; margin:0 auto .75rem; color:#DDD5C4; }
.dm-dropzone p     { margin:0; font-size:13.5px; color:#6B6055; }
.dm-dropzone small { font-size:11.5px; color:#A89E90; }

/* Progress bar */
.dm-progress { background:#E8E2D9; border-radius:99px; height:6px; margin-top:.85rem; overflow:hidden; display:none; }
.dm-progress-bar { height:100%; background:#7B1C1C; border-radius:99px; transition:width 120ms; width:0%; }

@media(max-width:768px) {
  .dm-wrap     { flex-direction:column; }
  .dm-sidebar  { width:100%; flex-direction:row; flex-wrap:wrap; padding:.75rem; border-right:none; border-bottom:1px solid #E8E2D9; }
  .dm-main     { padding:1rem; }
  .dm-folder-grid { grid-template-columns:repeat(auto-fill,minmax(150px,1fr)); }
  .dm-table thead { display:none; }
  .dm-table td    { display:block; padding:.4rem 1rem; }
  .dm-table td:first-child { padding-top:.75rem; }
  .dm-table td:last-child  { padding-bottom:.75rem; }
}
</style>

<div class="dm-wrap">

  <!-- ======================== SIDEBAR ======================== -->
  <aside class="dm-sidebar">

    <?php if ($canUpload): ?>
    <button class="dm-upload-btn" id="btn-open-upload">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
      Upload File
    </button>
    <?php endif; ?>

    <a href="<?= $base ?>/dokumen" class="dm-sidebar-link <?= $section==='my-files' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      My Files
      <span class="dm-sidebar-badge"><?= $stats['total_files'] ?></span>
    </a>

    <a href="<?= $base ?>/dokumen?section=shared" class="dm-sidebar-link <?= $section==='shared' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      Shared with Me
      <?php if ($stats['shared_count'] > 0): ?>
      <span class="dm-sidebar-badge"><?= $stats['shared_count'] ?></span>
      <?php endif; ?>
    </a>

    <a href="<?= $base ?>/dokumen?section=recent" class="dm-sidebar-link <?= $section==='recent' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Recent
    </a>

    <div class="dm-sidebar-section">Filter Tipe</div>
    <?php
    $types = [
      ''          => ['label'=>'Semua',   'icon'=>'<path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/>'],
      'pdf'       => ['label'=>'PDF',     'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>'],
      'word'      => ['label'=>'Word',    'icon'=>'<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>'],
      'sheet'     => ['label'=>'Excel',   'icon'=>'<rect x="3" y="3" width="18" height="18" rx="2"/>'],
      'image'     => ['label'=>'Gambar',  'icon'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/>'],
      'video'     => ['label'=>'Video',   'icon'=>'<polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/>'],
      'zip'       => ['label'=>'ZIP',     'icon'=>'<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>'],
    ];
    foreach ($types as $val => $t):
    ?>
    <a href="<?= $base ?>/dokumen?type=<?= $val ?><?= $folderId ? '&folder='.$folderId : '' ?>"
       class="dm-sidebar-link <?= $filterType===$val ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $t['icon'] ?></svg>
      <?= $t['label'] ?>
    </a>
    <?php endforeach; ?>

  </aside>

  <!-- ======================== MAIN ======================== -->
  <main class="dm-main">

    <!-- Toolbar -->
    <div class="dm-toolbar">
      <form method="GET" action="<?= $base ?>/dokumen" style="display:contents">
        <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
        <?php if ($folderId): ?><input type="hidden" name="folder" value="<?= $folderId ?>"> <?php endif; ?>
        <div class="dm-search">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari file...">
        </div>
        <select name="type" class="dm-filter-select" onchange="this.form.submit()">
          <option value="">Semua Tipe</option>
          <option value="pdf"   <?= $filterType==='pdf'   ?'selected':''?>>PDF</option>
          <option value="word"  <?= $filterType==='word'  ?'selected':''?>>Word</option>
          <option value="sheet" <?= $filterType==='sheet' ?'selected':''?>>Excel</option>
          <option value="image" <?= $filterType==='image' ?'selected':''?>>Gambar</option>
          <option value="video" <?= $filterType==='video' ?'selected':''?>>Video</option>
          <option value="zip"   <?= $filterType==='zip'   ?'selected':''?>>ZIP</option>
        </select>
        <?php if ($canUpload): ?>
        <button type="button" class="dm-btn dm-btn-outline" id="btn-open-folder">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
          Buat Folder
        </button>
        <?php endif; ?>
      </form>
    </div>

    <!-- Breadcrumb -->
    <?php if (!empty($breadcrumb)): ?>
    <div class="dm-breadcrumb">
      <a href="<?= $base ?>/dokumen">My Files</a>
      <?php foreach ($breadcrumb as $crumb): ?>
      <span>›</span>
      <?php if ($crumb['id'] == $folderId): ?>
        <span class="dm-breadcrumb-cur"><?= htmlspecialchars($crumb['name']) ?></span>
      <?php else: ?>
        <a href="<?= $base ?>/dokumen?folder=<?= $crumb['id'] ?>"><?= htmlspecialchars($crumb['name']) ?></a>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Stats bar -->
    <div class="dm-stats">
      <div class="dm-stat-item"><strong><?= $stats['total_files'] ?></strong> file</div>
      <div class="dm-stat-item"><strong><?= $stats['total_size_fmt'] ?></strong> digunakan</div>
      <?php if ($stats['shared_count'] > 0): ?>
      <div class="dm-stat-item"><strong><?= $stats['shared_count'] ?></strong> dibagikan ke saya</div>
      <?php endif; ?>
    </div>

    <!-- Folder grid -->
    <?php if ($section === 'my-files' && !empty($folders)): ?>
    <div class="dm-section-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
      Folder
    </div>
    <div class="dm-folder-grid" id="folder-grid">
      <?php foreach ($folders as $folder): ?>
      <a href="<?= $base ?>/dokumen?folder=<?= $folder['id'] ?>" class="dm-folder-card" data-folder-id="<?= $folder['id'] ?>">
        <div class="dm-folder-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#D97706" stroke="#D97706" stroke-width="0"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div style="flex:1;min-width:0">
          <div class="dm-folder-name"><?= htmlspecialchars($folder['name']) ?></div>
          <div class="dm-folder-meta"><?= $folder['file_count'] ?> file · <?= DokumenModel::formatSize((int)$folder['total_size']) ?></div>
        </div>
        <?php if ($canUpload): ?>
        <button class="dm-folder-menu" data-folder-id="<?= $folder['id'] ?>" data-folder-name="<?= htmlspecialchars($folder['name']) ?>" title="Opsi" onclick="event.preventDefault();openFolderMenu(this)">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
        </button>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- File table -->
    <div class="dm-section-title" style="margin-top:<?= (!empty($folders) && $section==='my-files') ? '.5rem' : '0' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
      <?= $section==='shared' ? 'Dibagikan ke Saya' : ($section==='recent' ? 'Baru Diakses' : 'File') ?>
    </div>

    <?php if (empty($files)): ?>
    <div class="dm-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
      <p><?= $search ? 'Tidak ada file yang cocok.' : 'Belum ada file di sini.' ?></p>
      <?php if ($canUpload && !$search && $section==='my-files'): ?>
      <p style="margin-top:.5rem"><button class="dm-btn dm-btn-outline" onclick="openUploadModal()">Upload File Pertama</button></p>
      <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="dm-table-wrap">
      <table class="dm-table">
        <thead>
          <tr>
            <th style="width:40px"></th>
            <th>Nama File</th>
            <th>Tipe</th>
            <th>Ukuran</th>
            <th>Diupload Oleh</th>
            <?php if ($section==='shared'): ?>
            <th>Akses</th>
            <?php endif; ?>
            <th>Tanggal</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody id="file-tbody">
        <?php foreach ($files as $f):
          $isOwner    = (int)$f['uploaded_by'] === $myId;
          $canShare   = $isOwner || Auth::hasRole('admin');
          $canDl      = $isOwner || Auth::hasRole('admin') || ($f['share_permission'] ?? '') === 'download';
          $canDelete  = $f['can_delete'];
        ?>
        <tr id="file-row-<?= $f['id'] ?>">
          <td>
            <span class="dm-file-icon" style="background:<?= htmlspecialchars($f['mime_color']) ?>">
              <?= htmlspecialchars($f['mime_label']) ?>
            </span>
          </td>
          <td>
            <div class="dm-file-name"><?= htmlspecialchars($f['original_name']) ?></div>
          </td>
          <td><span style="font-size:12px;color:#6B6055"><?= htmlspecialchars($f['mime_label']) ?></span></td>
          <td class="dm-file-size"><?= htmlspecialchars($f['size_fmt']) ?></td>
          <td style="font-size:12.5px;color:#6B6055"><?= htmlspecialchars($f['uploader_name'] ?? '-') ?></td>
          <?php if ($section==='shared'): ?>
          <td>
            <?php $perm = $f['share_permission'] ?? 'view'; ?>
            <span class="dm-perm-badge <?= $perm==='download' ? 'dm-perm-download' : 'dm-perm-view' ?>">
              <?php if ($perm==='download'): ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Download
              <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              View Only
              <?php endif; ?>
            </span>
          </td>
          <?php endif; ?>
          <td style="font-size:12px;color:#A89E90;white-space:nowrap"><?= date('d M Y', strtotime($f['created_at'])) ?></td>
          <td style="text-align:right;white-space:nowrap">
            <?php if ($canShare && $section !== 'shared'): ?>
            <button class="dm-btn dm-btn-share dm-btn-sm"
                    onclick="openShareModal(<?= $f['id'] ?>, '<?= addslashes($f['original_name']) ?>')"
                    title="Bagikan">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            </button>
            <?php endif; ?>
            <?php if ($canDl): ?>
            <a href="<?= $base ?>/dokumen/<?= $f['id'] ?>/download" class="dm-btn dm-btn-outline dm-btn-sm" title="Download">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </a>
            <?php endif; ?>
            <?php if ($canDelete): ?>
            <button class="dm-btn dm-btn-danger dm-btn-sm" onclick="deleteFile(<?= $f['id'] ?>, '<?= addslashes($f['original_name']) ?>')" title="Hapus">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
            </button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <div id="page-msg" class="dm-msg" style="margin-top:.85rem"></div>

  </main>
</div>

<!-- ======================== MODAL: UPLOAD ======================== -->
<div class="dm-modal-overlay" id="modal-upload">
  <div class="dm-modal">
    <div class="dm-modal-header">
      <div class="dm-modal-title">Upload File</div>
      <button class="dm-modal-close" onclick="closeModal('modal-upload')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div class="dm-dropzone" id="upload-dropzone">
        <input type="file" id="input-file-upload" multiple style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p>Klik atau <strong>seret file</strong> ke sini</p>
        <small>Maks 50 MB/file · PDF, Word, Excel, PPT, Gambar, Video, ZIP</small>
      </div>
      <div class="dm-progress" id="upload-progress"><div class="dm-progress-bar" id="upload-progress-bar"></div></div>
      <div id="upload-file-list" style="margin-top:.75rem"></div>
      <div id="upload-msg" class="dm-msg"></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-upload')">Tutup</button>
      <button class="dm-btn" id="btn-do-upload" style="background:#7B1C1C;color:#fff;border-color:#5A1212" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload
      </button>
    </div>
  </div>
</div>

<!-- ======================== MODAL: BUAT FOLDER ======================== -->
<div class="dm-modal-overlay" id="modal-folder">
  <div class="dm-modal">
    <div class="dm-modal-header">
      <div class="dm-modal-title" id="folder-modal-title">Buat Folder Baru</div>
      <button class="dm-modal-close" onclick="closeModal('modal-folder')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div style="margin-bottom:1rem">
        <label class="dm-label">Nama Folder <span style="color:#C05621">*</span></label>
        <input type="text" class="dm-ctrl" id="input-folder-name" placeholder="contoh: Laporan 2025" maxlength="100">
      </div>
      <input type="hidden" id="folder-action-id" value="">
      <div id="folder-msg" class="dm-msg"></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-folder')">Batal</button>
      <button class="dm-btn" id="btn-do-folder" style="background:#7B1C1C;color:#fff;border-color:#5A1212" onclick="submitFolder()">Simpan</button>
    </div>
  </div>
</div>

<!-- ======================== MODAL: SHARE FILE ======================== -->
<div class="dm-modal-overlay" id="modal-share">
  <div class="dm-modal" style="max-width:500px">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title">Bagikan File</div>
        <div id="share-modal-filename" style="font-size:12px;color:#A89E90;margin-top:.15rem"></div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-share')">&times;</button>
    </div>
    <div class="dm-modal-body">

      <!-- Form tambah share -->
      <div style="display:flex;gap:.5rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:.75rem">
        <div style="flex:1;min-width:160px">
          <label class="dm-label">Cari User</label>
          <div class="dm-user-search-wrap">
            <input type="text" class="dm-ctrl" id="share-user-search"
                   placeholder="Ketik nama / username..." autocomplete="off">
            <div class="dm-user-dropdown" id="share-user-dropdown"></div>
            <input type="hidden" id="share-selected-user-id">
            <div id="share-selected-user-name" style="font-size:12px;color:#7B1C1C;font-weight:700;margin-top:.25rem;min-height:16px"></div>
          </div>
        </div>
        <div>
          <label class="dm-label">Akses</label>
          <select class="dm-ctrl" id="share-permission" style="width:130px">
            <option value="view">View Only</option>
            <option value="download">View + Download</option>
          </select>
        </div>
        <button class="dm-btn" id="btn-do-share"
                style="background:#2B6CB0;color:#fff;border-color:#2a4a7f;height:40px"
                onclick="submitShare()">
          Bagikan
        </button>
      </div>

      <div id="share-msg" class="dm-msg"></div>

      <!-- Daftar yang sudah di-share -->
      <div style="font-size:11.5px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem;margin-top:1rem">
        Sudah Dibagikan ke
      </div>
      <div class="dm-share-list" id="share-list">
        <div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0">
          <div class="spinner-border spinner-border-sm"></div>
        </div>
      </div>

    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-share')">Tutup</button>
    </div>
  </div>
</div>

<script>
(function(){
  const BASE    = '<?= $base ?>';
  const FOLDER_ID = <?= $folderId ?? 'null' ?>;
  const MY_ID   = <?= $myId ?>;

  /* ── Helpers ── */
  function openModal(id)  { document.getElementById(id).classList.add('open'); }
  function closeModal(id) { document.getElementById(id).classList.remove('open'); }
  window.closeModal = closeModal;

  function setMsg(elId, html, ok) {
    const el = document.getElementById(elId); if (!el) return;
    const icon = ok
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
    el.innerHTML = '<span class="dm-msg-' + (ok?'ok':'err') + '">' + icon + ' ' + html + '</span>';
  }
  function escHtml(s) { const d=document.createElement('div'); d.textContent=s; return d.innerHTML; }
  function fmtSize(b) {
    if (b<1024) return b+'B';
    if (b<1048576) return (b/1024).toFixed(1)+'KB';
    return (b/1048576).toFixed(2)+'MB';
  }

  /* ── Upload modal ── */
  const dropzone   = document.getElementById('upload-dropzone');
  const fileInput  = document.getElementById('input-file-upload');
  const uploadBtn  = document.getElementById('btn-do-upload');
  const fileList   = document.getElementById('upload-file-list');
  const progressWrap = document.getElementById('upload-progress');
  const progressBar  = document.getElementById('upload-progress-bar');
  let pendingFiles   = [];

  function openUploadModal() {
    pendingFiles = []; fileList.innerHTML = ''; progressWrap.style.display='none';
    document.getElementById('upload-msg').innerHTML = '';
    fileInput.value = ''; uploadBtn.disabled = true;
    openModal('modal-upload');
  }
  window.openUploadModal = openUploadModal;

  document.getElementById('btn-open-upload')?.addEventListener('click', openUploadModal);

  dropzone.addEventListener('click', () => fileInput.click());
  ['dragenter','dragover'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('dragover'); }));
  ['dragleave','drop'].forEach(ev => dropzone.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('dragover'); }));
  dropzone.addEventListener('drop', e => addFiles(e.dataTransfer.files));
  fileInput.addEventListener('change', () => addFiles(fileInput.files));

  function addFiles(list) {
    Array.from(list).forEach(f => pendingFiles.push(f));
    renderFileList();
    uploadBtn.disabled = pendingFiles.length === 0;
  }
  function renderFileList() {
    fileList.innerHTML = pendingFiles.map((f,i) =>
      '<div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .5rem;background:#F9F7F4;border-radius:6px;margin-bottom:.3rem;font-size:12.5px">'
      + '<span style="flex:1;color:#1C1714;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + escHtml(f.name) + '</span>'
      + '<span style="color:#A89E90;flex-shrink:0">' + fmtSize(f.size) + '</span>'
      + '<button onclick="removePending(' + i + ')" style="background:none;border:none;cursor:pointer;color:#A89E90;padding:0 .2rem">×</button>'
      + '</div>'
    ).join('');
  }
  window.removePending = function(i) { pendingFiles.splice(i,1); renderFileList(); uploadBtn.disabled=pendingFiles.length===0; };

  uploadBtn?.addEventListener('click', async () => {
    if (!pendingFiles.length) return;
    uploadBtn.disabled = true;
    progressWrap.style.display = 'block';
    let done = 0;
    const total = pendingFiles.length;

    for (const file of pendingFiles) {
      const fd = new FormData();
      fd.append('file', file);
      if (FOLDER_ID) fd.append('folder_id', FOLDER_ID);
      try {
        const res  = await fetch(BASE + '/api/dokumen/upload', { method:'POST', body:fd });
        const data = await res.json();
        if (data.success && data.file) appendFileRow(data.file);
      } catch(e) {}
      done++;
      progressBar.style.width = Math.round(done/total*100) + '%';
    }
    setMsg('upload-msg', 'Upload selesai.', true);
    setTimeout(() => { closeModal('modal-upload'); location.reload(); }, 1000);
  });

  function appendFileRow(f) {
    const tbody = document.getElementById('file-tbody'); if (!tbody) return;
    const tr = document.createElement('tr');
    tr.id = 'file-row-' + f.id;
    tr.innerHTML =
      '<td><span class="dm-file-icon" style="background:'+escHtml(f.mime_color)+'">'+escHtml(f.mime_label)+'</span></td>'
      +'<td><div class="dm-file-name">'+escHtml(f.original_name)+'</div></td>'
      +'<td><span style="font-size:12px;color:#6B6055">'+escHtml(f.mime_label)+'</span></td>'
      +'<td class="dm-file-size">'+escHtml(f.size_fmt)+'</td>'
      +'<td style="font-size:12.5px;color:#6B6055">—</td>'
      +'<td style="font-size:12px;color:#A89E90">'+new Date().toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})+'</td>'
      +'<td style="text-align:right">'
      +'<button class="dm-btn dm-btn-share dm-btn-sm" onclick="openShareModal('+f.id+',\''+f.original_name.replace(/'/g,"\\'")+'\')" title="Bagikan">&#x2197;</button> '
      +'<a href="'+BASE+'/dokumen/'+f.id+'/download" class="dm-btn dm-btn-outline dm-btn-sm" title="Download">↓</a> '
      +'<button class="dm-btn dm-btn-danger dm-btn-sm" onclick="deleteFile('+f.id+',\''+f.original_name.replace(/'/g,"\\'")+'\')" title="Hapus">🗑</button>'
      +'</td>';
    tbody.insertBefore(tr, tbody.firstChild);
  }

  /* ── Buat/Rename Folder ── */
  let folderMode = 'create';
  document.getElementById('btn-open-folder')?.addEventListener('click', () => {
    folderMode = 'create';
    document.getElementById('folder-modal-title').textContent = 'Buat Folder Baru';
    document.getElementById('input-folder-name').value = '';
    document.getElementById('folder-action-id').value = '';
    document.getElementById('folder-msg').innerHTML = '';
    openModal('modal-folder');
  });
  window.openFolderMenu = function(btn) {
    folderMode = 'rename';
    document.getElementById('folder-modal-title').textContent = 'Ubah Nama Folder';
    document.getElementById('input-folder-name').value = btn.dataset.folderName;
    document.getElementById('folder-action-id').value = btn.dataset.folderId;
    document.getElementById('folder-msg').innerHTML = '';
    openModal('modal-folder');
  };
  window.submitFolder = async function() {
    const name = document.getElementById('input-folder-name').value.trim();
    if (!name) { setMsg('folder-msg','Nama tidak boleh kosong.',false); return; }
    const btn = document.getElementById('btn-do-folder');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const fd = new FormData(); fd.append('name', name);
    let url;
    if (folderMode === 'create') {
      url = BASE + '/api/dokumen/folder/create';
      if (FOLDER_ID) fd.append('parent_id', FOLDER_ID);
    } else {
      url = BASE + '/api/dokumen/folder/' + document.getElementById('folder-action-id').value + '/rename';
    }
    try {
      const data = await (await fetch(url,{method:'POST',body:fd})).json();
      if (data.success) { setMsg('folder-msg',data.message,true); setTimeout(()=>location.reload(),900); }
      else { setMsg('folder-msg',data.message,false); btn.disabled=false; btn.textContent='Simpan'; }
    } catch(e) { setMsg('folder-msg','Gagal koneksi.',false); btn.disabled=false; btn.textContent='Simpan'; }
  };

  /* ── Hapus File ── */
  window.deleteFile = function(id, name) {
    if (!confirm('Hapus file "' + name + '"?')) return;
    fetch(BASE + '/api/dokumen/' + id + '/delete', {method:'POST'})
      .then(r=>r.json())
      .then(data => {
        if (data.success) { document.getElementById('file-row-'+id)?.remove(); setMsg('page-msg','File dihapus.',true); }
        else setMsg('page-msg', data.message, false);
      }).catch(()=>setMsg('page-msg','Gagal koneksi.',false));
  };

  /* ═══════════════════════════════════════════════════════
     FASE 2 — SHARE MODAL
  ═══════════════════════════════════════════════════════ */
  let shareFileId   = null;
  let shareDebounce = null;

  window.openShareModal = function(fileId, fileName) {
    shareFileId = fileId;
    document.getElementById('share-modal-filename').textContent = fileName;
    document.getElementById('share-msg').innerHTML = '';
    document.getElementById('share-user-search').value = '';
    document.getElementById('share-selected-user-id').value = '';
    document.getElementById('share-selected-user-name').textContent = '';
    document.getElementById('share-user-dropdown').classList.remove('open');
    loadShareList(fileId);
    openModal('modal-share');
  };

  /* Live search user */
  document.getElementById('share-user-search').addEventListener('input', function() {
    clearTimeout(shareDebounce);
    const q = this.value.trim();
    if (q.length < 1) { document.getElementById('share-user-dropdown').classList.remove('open'); return; }
    shareDebounce = setTimeout(() => searchUsers(q), 280);
  });

  async function searchUsers(q) {
    try {
      const data = await (await fetch(BASE + '/api/users?q=' + encodeURIComponent(q))).json();
      const dd   = document.getElementById('share-user-dropdown');
      if (!data.users || !data.users.length) {
        dd.innerHTML = '<div class="dm-user-option" style="color:#A89E90;cursor:default">Tidak ada user ditemukan</div>';
        dd.classList.add('open'); return;
      }
      dd.innerHTML = data.users.map(u =>
        '<div class="dm-user-option" data-id="'+u.id+'" data-name="'+escHtml(u.name)+'" onclick="selectUser(this)">'
        + '<strong>'+escHtml(u.name)+'</strong>'
        + ' <small>@'+escHtml(u.username)+' · '+escHtml(u.role)+'</small>'
        + '</div>'
      ).join('');
      dd.classList.add('open');
    } catch(e) {}
  }

  window.selectUser = function(el) {
    document.getElementById('share-selected-user-id').value  = el.dataset.id;
    document.getElementById('share-selected-user-name').textContent = '✓ ' + el.dataset.name;
    document.getElementById('share-user-search').value = el.dataset.name;
    document.getElementById('share-user-dropdown').classList.remove('open');
  };

  /* Submit share */
  window.submitShare = async function() {
    const userId = document.getElementById('share-selected-user-id').value;
    const perm   = document.getElementById('share-permission').value;
    if (!userId) { setMsg('share-msg','Pilih user terlebih dahulu.',false); return; }
    const btn = document.getElementById('btn-do-share');
    btn.disabled = true; btn.textContent = 'Membagikan...';
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('permission', perm);
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+shareFileId+'/shares',{method:'POST',body:fd})).json();
      if (data.success) {
        setMsg('share-msg', data.message, true);
        renderShareList(data.shares);
        document.getElementById('share-user-search').value = '';
        document.getElementById('share-selected-user-id').value = '';
        document.getElementById('share-selected-user-name').textContent = '';
      } else {
        setMsg('share-msg', data.message, false);
      }
    } catch(e) { setMsg('share-msg','Gagal koneksi.',false); }
    btn.disabled = false; btn.textContent = 'Bagikan';
  };

  /* Load daftar share */
  async function loadShareList(fileId) {
    document.getElementById('share-list').innerHTML =
      '<div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0"><div class="spinner-border spinner-border-sm"></div></div>';
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/shares')).json();
      renderShareList(data.shares || []);
    } catch(e) {
      document.getElementById('share-list').innerHTML = '<div style="color:#C05621;font-size:13px">Gagal memuat data.</div>';
    }
  }

  function renderShareList(shares) {
    const el = document.getElementById('share-list');
    if (!shares || !shares.length) {
      el.innerHTML = '<div style="text-align:center;color:#A89E90;font-size:13px;padding:.5rem">Belum dibagikan ke siapapun.</div>';
      return;
    }
    el.innerHTML = shares.map(s =>
      '<div class="dm-share-item" id="share-item-'+s.shared_to+'">'  
      + '<div class="dm-share-avatar">'+escHtml(s.user_name.charAt(0).toUpperCase())+'</div>'
      + '<div class="dm-share-info">'
      + '<div class="dm-share-name">'+escHtml(s.user_name)+'</div>'
      + '<div class="dm-share-role">@'+escHtml(s.username)+' · '+escHtml(s.role)+'</div>'
      + '</div>'
      + '<select class="dm-share-perm-select" onchange="updatePerm('+shareFileId+','+s.shared_to+',this.value)">'
      + '<option value="view"'+(s.permission==='view'?' selected':'')+'>View Only</option>'
      + '<option value="download"'+(s.permission==='download'?' selected':'')+'>Download</option>'
      + '</select>'
      + '<button class="dm-share-revoke" title="Cabut akses" onclick="revokeShare('+shareFileId+','+s.shared_to+')">'  
      + '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
      + '</button>'
      + '</div>'
    ).join('');
  }

  /* Update permission */
  window.updatePerm = async function(fileId, userId, perm) {
    const fd = new FormData(); fd.append('permission', perm);
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/shares/'+userId+'/permission',{method:'POST',body:fd})).json();
      if (!data.success) alert(data.message);
      else renderShareList(data.shares);
    } catch(e) { alert('Gagal koneksi.'); }
  };

  /* Cabut share */
  window.revokeShare = async function(fileId, userId) {
    if (!confirm('Cabut akses user ini?')) return;
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/shares/'+userId+'/delete',{method:'POST'})).json();
      if (data.success) renderShareList(data.shares);
      else alert(data.message);
    } catch(e) { alert('Gagal koneksi.'); }
  };

  /* Close on overlay / ESC */
  document.querySelectorAll('.dm-modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target===ov) closeModal(ov.id); });
  });
  document.addEventListener('keydown', e => {
    if (e.key==='Escape') document.querySelectorAll('.dm-modal-overlay.open').forEach(m=>m.classList.remove('open'));
  });
  document.addEventListener('click', e => {
    const dd = document.getElementById('share-user-dropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'share-user-search') {
      dd.classList.remove('open');
    }
  });

})();
</script>
