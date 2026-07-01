<?php
$baseUrl   = rtrim(BASE_URL, '/');
$curSection = $section ?? 'my-files';
$isAdmin    = $isAdmin ?? false;

$sectionLabels = [
    'my-files' => 'Dokumen Saya',
    'shared'   => 'Dibagikan ke Saya',
    'recent'   => 'Terbaru',
    'trash'    => 'Sampah',
];
$pageTitle = $sectionLabels[$curSection] ?? 'Dokumen';

// Helper: build URL query
function dkUrl(string $sec, ?int $folder = null, string $q = '', string $type = ''): string {
    global $baseUrl;
    $p = ['section' => $sec];
    if ($folder) $p['folder'] = $folder;
    if ($q !== '') $p['q']   = $q;
    if ($type !== '') $p['type'] = $type;
    return $baseUrl . '/dokumen?' . http_build_query($p);
}
?>
<style>
/* ===== DK namespace ===== */
.dk-layout   { display:flex; gap:0; min-height:calc(100vh - 64px); }

/* Sidebar kiri */
.dk-sidebar {
  width: 220px; flex-shrink: 0;
  background: #fff;
  border-right: 1px solid #E8E2D9;
  padding: 1.25rem .75rem;
  display: flex; flex-direction: column; gap: .2rem;
}
.dk-sidebar-section { font-size: 10.5px; font-weight: 800; color: #A89E90; letter-spacing:.06em; text-transform:uppercase; padding: .6rem .6rem .3rem; margin-top:.5rem; }
.dk-nav-item {
  display: flex; align-items: center; gap: .55rem;
  padding: .5rem .75rem;
  border-radius: 8px;
  font-size: 13.5px; font-weight: 600; color: #4A3F35;
  text-decoration: none; cursor: pointer;
  transition: background 140ms, color 140ms;
  border: none; background: none; width: 100%; text-align: left;
}
.dk-nav-item svg { opacity: .55; flex-shrink: 0; }
.dk-nav-item:hover { background: #F5F0E8; color: #7B1C1C; }
.dk-nav-item:hover svg { opacity: 1; }
.dk-nav-item.active { background: rgba(123,28,28,.08); color: #7B1C1C; font-weight: 700; }
.dk-nav-item.active svg { opacity: 1; }
.dk-nav-badge {
  margin-left: auto;
  font-size: 10.5px; font-weight: 700;
  background: #7B1C1C; color: #fff;
  border-radius: 20px; padding: .1em .55em;
}
.dk-upload-btn {
  display: flex; align-items: center; justify-content: center; gap: .45rem;
  width: 100%; height: 40px; border-radius: 9px;
  background: #7B1C1C; color: #fff;
  font-size: 13.5px; font-weight: 700;
  border: none; cursor: pointer;
  transition: background 160ms;
  margin-bottom: .5rem;
}
.dk-upload-btn:hover { background: #5A1212; }

/* Storage bar */
.dk-storage {
  margin-top: auto; padding-top: .75rem;
  border-top: 1px solid #F0EBE2;
}
.dk-storage-label { font-size: 11px; color: #A89E90; margin-bottom: .3rem; }
.dk-storage-bar { height: 5px; border-radius: 3px; background: #EDE6DC; overflow: hidden; }
.dk-storage-fill { height: 100%; background: linear-gradient(90deg,#7B1C1C,#C9A84C); border-radius: 3px; }
.dk-storage-info  { font-size: 11px; color: #6B6055; margin-top: .3rem; }

/* Main area */
.dk-main { flex: 1; padding: 1.5rem; overflow-x: hidden; }

/* Toolbar */
.dk-toolbar { display:flex; align-items:center; gap:.65rem; flex-wrap:wrap; margin-bottom:1.25rem; }
.dk-toolbar-title { font-size: 1.15rem; font-weight: 800; color: #1C1714; flex: 1; min-width: 0; }
.dk-search {
  display: flex; align-items: center;
  border: 1.5px solid #DDD5C4; border-radius: 8px;
  background: #FDFCFA; overflow: hidden;
  transition: border-color 180ms;
  height: 36px;
}
.dk-search:focus-within { border-color: #7B1C1C; }
.dk-search svg { margin: 0 .5rem; color: #A89E90; flex-shrink:0; }
.dk-search input { border:none; background:transparent; outline:none; font-size:13px; width:180px; color:#1C1714; padding-right:.5rem; }
.dk-filter-select {
  height: 36px; border: 1.5px solid #DDD5C4; border-radius: 8px;
  background: #FDFCFA; font-size: 13px; color: #4A3F35;
  padding: 0 2rem 0 .75rem; appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='11' height='11' viewBox='0 0 24 24' fill='none' stroke='%236B6055' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat; background-position: right .6rem center;
  cursor: pointer;
}
.dk-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  height: 36px; padding: 0 .9rem; border-radius: 8px;
  font-size: 13px; font-weight: 700; cursor: pointer;
  border: 1.5px solid transparent; transition: all 160ms;
  white-space: nowrap;
}
.dk-btn-primary { background: #7B1C1C; color: #fff; border-color: #5A1212; }
.dk-btn-primary:hover { background: #5A1212; }
.dk-btn-outline { background: #fff; color: #6B6055; border-color: #DDD5C4; }
.dk-btn-outline:hover { border-color: #7B1C1C; color: #7B1C1C; }
.dk-btn-ghost  { background: transparent; color: #6B6055; border-color: transparent; }
.dk-btn-ghost:hover  { background: #F5F0E8; color: #7B1C1C; }
.dk-btn-danger { background:#fff; color:#C05621; border-color:#DDD5C4; }
.dk-btn-danger:hover { background:#C05621; color:#fff; }

/* Breadcrumb */
.dk-breadcrumb { display:flex; align-items:center; gap:.35rem; font-size:12.5px; color:#6B6055; margin-bottom:.85rem; flex-wrap:wrap; }
.dk-breadcrumb a { color:#7B1C1C; text-decoration:none; font-weight:600; }
.dk-breadcrumb a:hover { text-decoration:underline; }
.dk-breadcrumb span { color:#A89E90; }

/* Folder grid */
.dk-folder-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:.85rem; margin-bottom:1.5rem; }
.dk-folder-card {
  background: #fff; border: 1.5px solid #E8E2D9;
  border-radius: 10px; padding: .85rem 1rem;
  display: flex; align-items: center; gap: .65rem;
  cursor: pointer; transition: border-color 160ms, box-shadow 160ms;
  position: relative;
}
.dk-folder-card:hover { border-color: #C9A84C; box-shadow: 0 2px 10px rgba(28,23,20,.06); }
.dk-folder-icon {
  width: 38px; height: 38px; border-radius: 8px;
  background: linear-gradient(135deg,#FFF3CD,#FFE082);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.dk-folder-name { font-size: 13px; font-weight: 700; color: #1C1714; word-break: break-word; }
.dk-folder-meta { font-size: 11.5px; color: #A89E90; margin-top: .1rem; }
.dk-folder-menu {
  position: absolute; top: .5rem; right: .5rem;
  opacity: 0; transition: opacity 140ms;
}
.dk-folder-card:hover .dk-folder-menu { opacity: 1; }
.dk-folder-menu button {
  background: none; border: none; cursor: pointer;
  color: #6B6055; padding: .2rem; border-radius: 4px;
}
.dk-folder-menu button:hover { background: #F5F0E8; color: #7B1C1C; }

/* File table */
.dk-table-wrap { background: #fff; border: 1px solid #E8E2D9; border-radius: 12px; overflow: hidden; }
.dk-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.dk-table thead th {
  padding: .7rem 1rem;
  text-align: left;
  font-size: 11px; font-weight: 800;
  color: #A89E90; letter-spacing: .05em; text-transform: uppercase;
  border-bottom: 1px solid #F0EBE2;
  background: #FDFCFA;
}
.dk-table thead th:first-child { padding-left: 1.25rem; }
.dk-table tbody tr { transition: background 120ms; }
.dk-table tbody tr:hover { background: #FDFCFA; }
.dk-table tbody tr + tr { border-top: 1px solid #F5F0E8; }
.dk-table td { padding: .75rem 1rem; vertical-align: middle; }
.dk-table td:first-child { padding-left: 1.25rem; }
.dk-file-name { font-weight: 700; color: #1C1714; font-size: 13.5px; }
.dk-file-size { font-size: 12px; color: #A89E90; margin-top: .1rem; }
.dk-type-badge {
  display: inline-block; font-size: 11px; font-weight: 700;
  padding: .15em .6em; border-radius: 20px;
  background: #F0EBE2; color: #6B6055;
}
.dk-avatar-stack { display:flex; align-items:center; }
.dk-avatar-stack .av {
  width: 24px; height: 24px; border-radius: 50%;
  background: #C9A84C; color: #fff;
  font-size: 10px; font-weight: 700;
  display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
  margin-left: -6px;
  flex-shrink: 0;
}
.dk-avatar-stack .av:first-child { margin-left: 0; }
.dk-row-actions { display:flex; align-items:center; gap:.3rem; opacity:0; transition:opacity 140ms; }
.dk-table tbody tr:hover .dk-row-actions { opacity: 1; }

/* Empty state */
.dk-empty { text-align:center; padding:3rem 1rem; color:#A89E90; }
.dk-empty svg { color: #DDD5C4; margin: 0 auto .75rem; display: block; }
.dk-empty p { font-size: 13.5px; margin: 0; }
.dk-empty small { font-size: 12px; }

/* Modals */
.dk-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,.45);
  display: flex; align-items: center; justify-content: center;
  z-index: 1060; padding: 1rem;
}
.dk-modal {
  background: #fff; border-radius: 14px;
  width: 100%; max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,.18);
  overflow: hidden;
}
.dk-modal-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #F0EBE2;
  display: flex; align-items: center; justify-content: space-between;
}
.dk-modal-title { font-size: 15px; font-weight: 800; color: #1C1714; }
.dk-modal-close {
  background: none; border: none; cursor: pointer;
  color: #A89E90; padding: .25rem;
  border-radius: 6px; transition: background 140ms;
}
.dk-modal-close:hover { background: #F5F0E8; color: #7B1C1C; }
.dk-modal-body  { padding: 1.25rem; }
.dk-modal-footer{ padding: .85rem 1.25rem; border-top: 1px solid #F0EBE2; display:flex; justify-content:flex-end; gap:.5rem; }

/* Drop zone upload */
.dk-dropzone {
  border: 2px dashed #DDD5C4; border-radius: 10px;
  background: #FDFCFA; padding: 2rem;
  text-align: center; cursor: pointer;
  transition: border-color 180ms, background 180ms;
}
.dk-dropzone:hover, .dk-dropzone.dragover { border-color: #7B1C1C; background: rgba(123,28,28,.03); }
.dk-dropzone p { margin: .5rem 0 0; font-size: 13px; color: #6B6055; }
.dk-dropzone small { font-size: 11.5px; color: #A89E90; }
.dk-progress-wrap { margin-top: .85rem; }
.dk-progress { height: 6px; background: #EDE6DC; border-radius: 3px; overflow: hidden; }
.dk-progress-bar { height: 100%; background: #7B1C1C; border-radius: 3px; transition: width .25s; }

/* Label & ctrl */
.dk-label { display:block; font-size:11.5px; font-weight:700; color:#6B6055; letter-spacing:.03em; text-transform:uppercase; margin-bottom:.3rem; }
.dk-ctrl { width:100%; height:40px; border:1.5px solid #DDD5C4; border-radius:8px; padding:0 .85rem; font-size:13.5px; color:#1C1714; background:#FDFCFA; outline:none; }
.dk-ctrl:focus { border-color:#7B1C1C; box-shadow:0 0 0 3px rgba(123,28,28,.08); }

/* Msg */
.dk-msg { font-size:13px; margin-top:.65rem; }
.dk-msg-ok  { color:#27A155; display:flex; align-items:center; gap:.35rem; }
.dk-msg-err { color:#C05621; display:flex; align-items:center; gap:.35rem; }

@media(max-width:768px){
  .dk-sidebar { display:none; }
  .dk-main { padding:1rem; }
  .dk-search input { width:120px; }
}
</style>

<div class="dk-layout">

  <!-- ── Sidebar ── -->
  <aside class="dk-sidebar">

    <?php if ($isAdmin || Auth::hasRole('sekretaris')): ?>
    <button class="dk-upload-btn" id="dk-open-upload">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Upload File
    </button>
    <?php endif; ?>

    <a href="<?= dkUrl('my-files', $folderId ?? null) ?>" class="dk-nav-item <?= $curSection==='my-files' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Dokumen Saya
      <span class="dk-nav-badge"><?= (int)($summary['total_files'] ?? 0) ?></span>
    </a>

    <a href="<?= dkUrl('shared') ?>" class="dk-nav-item <?= $curSection==='shared' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
      Dibagikan ke Saya
    </a>

    <a href="<?= dkUrl('recent') ?>" class="dk-nav-item <?= $curSection==='recent' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Terbaru
    </a>

    <a href="<?= dkUrl('trash') ?>" class="dk-nav-item <?= $curSection==='trash' ? 'active' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
      Sampah
    </a>

    <!-- Storage info -->
    <div class="dk-storage">
      <div class="dk-storage-label">Storage</div>
      <?php
        $maxBytes = 1073741824; // 1 GB quota (bisa dikonfigurasi)
        $usedPct  = min(100, round((int)($summary['total_bytes'] ?? 0) / $maxBytes * 100, 1));
      ?>
      <div class="dk-storage-bar">
        <div class="dk-storage-fill" style="width:<?= $usedPct ?>%"></div>
      </div>
      <div class="dk-storage-info">
        <?= htmlspecialchars($summary['total_bytes_fmt'] ?? '0 B') ?> / 1 GB
      </div>
    </div>

  </aside>

  <!-- ── Main ── -->
  <main class="dk-main">

    <!-- Toolbar -->
    <div class="dk-toolbar">
      <h1 class="dk-toolbar-title"><?= htmlspecialchars($pageTitle) ?></h1>

      <?php if ($curSection === 'my-files'): ?>
      <!-- Search -->
      <form method="GET" action="" style="display:contents">
        <input type="hidden" name="section" value="my-files">
        <?php if ($folderId): ?><input type="hidden" name="folder" value="<?= $folderId ?>"> <?php endif; ?>
        <div class="dk-search">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Cari file...">
        </div>
        <select name="type" class="dk-filter-select" onchange="this.form.submit()">
          <option value="" <?= $typeFilter==='' ? 'selected':'' ?>>Semua Tipe</option>
          <option value="pdf"    <?= $typeFilter==='pdf'    ? 'selected':'' ?>>PDF</option>
          <option value="wordprocessingml" <?= $typeFilter==='wordprocessingml' ? 'selected':'' ?>>Word</option>
          <option value="spreadsheetml"    <?= $typeFilter==='spreadsheetml'    ? 'selected':'' ?>>Excel</option>
          <option value="presentationml"   <?= $typeFilter==='presentationml'   ? 'selected':'' ?>>PowerPoint</option>
          <option value="video"  <?= $typeFilter==='video'  ? 'selected':'' ?>>Video</option>
          <option value="image"  <?= $typeFilter==='image'  ? 'selected':'' ?>>Gambar</option>
          <option value="zip"    <?= $typeFilter==='zip'    ? 'selected':'' ?>>Arsip</option>
        </select>
      </form>

      <?php if ($isAdmin || Auth::hasRole('sekretaris')): ?>
      <button class="dk-btn dk-btn-outline" id="dk-open-folder">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        Folder Baru
      </button>
      <?php endif; ?>
      <?php endif; ?>
    </div>

    <!-- Breadcrumb -->
    <?php if (!empty($breadcrumb)): ?>
    <div class="dk-breadcrumb">
      <a href="<?= dkUrl('my-files') ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        Dokumen Saya
      </a>
      <?php foreach ($breadcrumb as $i => $crumb): ?>
      <span>/</span>
      <?php if ($i < count($breadcrumb) - 1): ?>
        <a href="<?= dkUrl('my-files', (int)$crumb['id']) ?>"><?= htmlspecialchars($crumb['name']) ?></a>
      <?php else: ?>
        <strong style="color:#1C1714"><?= htmlspecialchars($crumb['name']) ?></strong>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Folder cards ── -->
    <?php if (!empty($folders) && $curSection === 'my-files'): ?>
    <div class="dk-folder-grid" id="dk-folder-grid">
      <?php foreach ($folders as $folder): ?>
      <div class="dk-folder-card" data-folder-id="<?= $folder['id'] ?>"
           onclick="window.location='<?= dkUrl('my-files', (int)$folder['id']) ?>'">
        <div class="dk-folder-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#C9A84C" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <div style="min-width:0;flex:1">
          <div class="dk-folder-name"><?= htmlspecialchars($folder['name']) ?></div>
          <div class="dk-folder-meta">oleh <?= htmlspecialchars($folder['creator_name'] ?? '-') ?></div>
        </div>
        <?php if ($isAdmin || Auth::hasRole('sekretaris') || $folder['created_by'] == Auth::id()): ?>
        <div class="dk-folder-menu" onclick="event.stopPropagation()">
          <button title="Rename" onclick="dkRenameFolder(<?= $folder['id'] ?>, '<?= addslashes($folder['name']) ?>')">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button title="Hapus" onclick="dkDeleteFolder(<?= $folder['id'] ?>, '<?= addslashes($folder['name']) ?>')">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#C05621" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          </button>
        </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── File table ── -->
    <?php if (!empty($files)): ?>
    <div class="dk-table-wrap">
      <table class="dk-table">
        <thead>
          <tr>
            <th style="width:40%">NAMA</th>
            <th>TIPE</th>
            <th>DIUBAH</th>
            <th>PEMILIK</th>
            <th>AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($files as $f): ?>
          <tr data-file-id="<?= $f['id'] ?>">
            <td>
              <div style="display:flex;align-items:center;gap:.65rem">
                <span style="flex-shrink:0"><?= $f['icon_svg'] ?></span>
                <div>
                  <div class="dk-file-name"><?= htmlspecialchars($f['original_name']) ?></div>
                  <div class="dk-file-size"><?= htmlspecialchars($f['size_fmt']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="dk-type-badge"><?= htmlspecialchars($f['type_label']) ?></span></td>
            <td style="color:#6B6055;font-size:12.5px"><?= htmlspecialchars($f['date_fmt']) ?></td>
            <td style="font-size:12.5px;color:#4A3F35"><?= htmlspecialchars($f['uploader_name'] ?? '-') ?></td>
            <td>
              <div class="dk-row-actions">
                <!-- Download -->
                <a href="<?= $baseUrl ?>/dokumen/<?= $f['id'] ?>/download"
                   class="dk-btn dk-btn-ghost" style="height:30px;padding:0 .6rem" title="Download">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                </a>
                <?php if ($curSection !== 'trash'): ?>
                <?php if ($isAdmin || Auth::hasRole('sekretaris') || $f['uploaded_by'] == Auth::id()): ?>
                <!-- Hapus (soft) -->
                <button class="dk-btn dk-btn-ghost" style="height:30px;padding:0 .6rem" title="Hapus"
                        onclick="dkDeleteFile(<?= $f['id'] ?>, '<?= addslashes($f['original_name']) ?>')">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#C05621" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/></svg>
                </button>
                <?php endif; ?>
                <?php else: ?>
                <!-- Restore -->
                <button class="dk-btn dk-btn-ghost" style="height:30px;padding:0 .6rem" title="Pulihkan"
                        onclick="dkRestoreFile(<?= $f['id'] ?>)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#27A155" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
                </button>
                <!-- Hard delete -->
                <button class="dk-btn dk-btn-ghost" style="height:30px;padding:0 .6rem" title="Hapus Permanen"
                        onclick="dkForceDelete(<?= $f['id'] ?>, '<?= addslashes($f['original_name']) ?>')">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#C05621" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <!-- Empty state -->
    <div class="dk-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
      </svg>
      <?php if ($curSection === 'trash'): ?>
        <p>Sampah kosong</p>
        <small>File yang dihapus akan muncul di sini</small>
      <?php elseif ($curSection === 'shared'): ?>
        <p>Belum ada file yang dibagikan ke Anda</p>
      <?php elseif ($curSection === 'recent'): ?>
        <p>Belum ada aktivitas file terbaru</p>
      <?php else: ?>
        <p>Belum ada file di sini</p>
        <?php if ($isAdmin || Auth::hasRole('sekretaris')): ?>
        <small>Klik <strong>Upload File</strong> untuk menambahkan dokumen pertama</small>
        <?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  </main>
</div>

<!-- ═══════════════ MODAL: Upload File ═══════════════ -->
<div id="dk-modal-upload" style="display:none" class="dk-overlay" onclick="if(event.target===this)this.style.display='none'">
  <div class="dk-modal">
    <div class="dk-modal-header">
      <span class="dk-modal-title">Upload Dokumen</span>
      <button class="dk-modal-close" onclick="document.getElementById('dk-modal-upload').style.display='none'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="dk-modal-body">
      <div class="dk-dropzone" id="dk-dropzone" onclick="document.getElementById('dk-file-input').click()">
        <input type="file" id="dk-file-input" multiple style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#DDD5C4" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p>Klik atau <strong>drag &amp; drop</strong> file ke sini</p>
        <small>PDF, Word, Excel, PPT, Video, Gambar, ZIP — Maks 50 MB per file</small>
      </div>
      <div id="dk-upload-queue" style="margin-top:.85rem"></div>
      <div id="dk-upload-msg" class="dk-msg"></div>
    </div>
    <div class="dk-modal-footer">
      <button class="dk-btn dk-btn-outline" onclick="document.getElementById('dk-modal-upload').style.display='none'">Tutup</button>
      <button class="dk-btn dk-btn-primary" id="dk-btn-do-upload" disabled>Upload Semua</button>
    </div>
  </div>
</div>

<!-- ═══════════════ MODAL: Folder Baru ═══════════════ -->
<div id="dk-modal-folder" style="display:none" class="dk-overlay" onclick="if(event.target===this)this.style.display='none'">
  <div class="dk-modal">
    <div class="dk-modal-header">
      <span class="dk-modal-title" id="dk-folder-modal-title">Buat Folder Baru</span>
      <button class="dk-modal-close" onclick="document.getElementById('dk-modal-folder').style.display='none'">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="dk-modal-body">
      <label class="dk-label">Nama Folder</label>
      <input type="text" class="dk-ctrl" id="dk-folder-name" placeholder="Contoh: Dokumen 2025">
      <input type="hidden" id="dk-folder-id-edit" value="">
      <div id="dk-folder-msg" class="dk-msg"></div>
    </div>
    <div class="dk-modal-footer">
      <button class="dk-btn dk-btn-outline" onclick="document.getElementById('dk-modal-folder').style.display='none'">Batal</button>
      <button class="dk-btn dk-btn-primary" id="dk-btn-save-folder">Simpan</button>
    </div>
  </div>
</div>

<script>
(function(){
  const BASE     = '<?= $baseUrl ?>';
  const FOLDER_ID = <?= $folderId ? $folderId : 'null' ?>;
  const SECTION   = '<?= $curSection ?>';

  /* ── helpers ── */
  function setMsg(elId, html, ok) {
    const el = document.getElementById(elId);
    if (!el) return;
    const icon = ok
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
    el.innerHTML = `<span class="${ok?'dk-msg-ok':'dk-msg-err'}">${icon} ${html}</span>`;
  }
  function reload() { setTimeout(()=>location.reload(), 1200); }

  /* ── Open modals ── */
  document.getElementById('dk-open-upload')?.addEventListener('click',()=>{
    document.getElementById('dk-modal-upload').style.display='flex';
  });
  document.getElementById('dk-open-folder')?.addEventListener('click',()=>{
    document.getElementById('dk-folder-modal-title').textContent='Buat Folder Baru';
    document.getElementById('dk-folder-name').value='';
    document.getElementById('dk-folder-id-edit').value='';
    document.getElementById('dk-folder-msg').innerHTML='';
    document.getElementById('dk-modal-folder').style.display='flex';
  });

  /* ── Upload: file input + drag-drop ── */
  const fileInput  = document.getElementById('dk-file-input');
  const dropzone   = document.getElementById('dk-dropzone');
  const uploadQueue= document.getElementById('dk-upload-queue');
  const uploadBtn  = document.getElementById('dk-btn-do-upload');
  let pendingFiles = [];

  function renderQueue() {
    uploadQueue.innerHTML = pendingFiles.map((f,i)=>`
      <div style="display:flex;align-items:center;gap:.65rem;background:#F5F0E8;border:1px solid #E8E2D9;border-radius:8px;padding:.55rem .85rem;margin-bottom:.4rem" id="qitem-${i}">
        <span style="flex:1;font-size:13px;font-weight:600;color:#1C1714;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${f.name}</span>
        <span style="font-size:11.5px;color:#6B6055;flex-shrink:0">${fmtSz(f.size)}</span>
        <div class="dk-progress" style="width:80px"><div class="dk-progress-bar" id="qprog-${i}" style="width:0"></div></div>
        <span id="qstat-${i}" style="font-size:11px;color:#A89E90;flex-shrink:0">Menunggu</span>
      </div>
    `).join('');
    uploadBtn.disabled = pendingFiles.length === 0;
  }
  function fmtSz(b){
    if(b<1024) return b+' B';
    if(b<1048576) return (b/1024).toFixed(1)+' KB';
    return (b/1048576).toFixed(2)+' MB';
  }
  function addFiles(files) {
    Array.from(files).forEach(f=>{ if(!pendingFiles.find(p=>p.name===f.name&&p.size===f.size)) pendingFiles.push(f); });
    renderQueue();
  }

  fileInput.addEventListener('change', ()=>addFiles(fileInput.files));
  ['dragenter','dragover'].forEach(e=>dropzone.addEventListener(e,ev=>{ev.preventDefault();dropzone.classList.add('dragover');}));
  ['dragleave','drop'].forEach(e=>dropzone.addEventListener(e,ev=>{ev.preventDefault();dropzone.classList.remove('dragover');}));
  dropzone.addEventListener('drop',ev=>addFiles(ev.dataTransfer.files));

  uploadBtn.addEventListener('click', async ()=>{
    uploadBtn.disabled = true;
    let allOk = true;
    for (let i = 0; i < pendingFiles.length; i++) {
      const f  = pendingFiles[i];
      const fd = new FormData();
      fd.append('file', f);
      if (FOLDER_ID) fd.append('folder_id', FOLDER_ID);
      document.getElementById('qstat-'+i).textContent = 'Uploading...';
      try {
        const xhr = new XMLHttpRequest();
        await new Promise((res,rej)=>{
          xhr.upload.onprogress = e=>{ if(e.lengthComputable){ const p=Math.round(e.loaded/e.total*100); document.getElementById('qprog-'+i).style.width=p+'%'; } };
          xhr.onload = ()=>res(xhr);
          xhr.onerror = ()=>rej();
          xhr.open('POST', BASE+'/api/dokumen/upload');
          xhr.send(fd);
        });
        const data = JSON.parse(xhr.responseText);
        if (data.success) {
          document.getElementById('qstat-'+i).textContent = '✓ Selesai';
          document.getElementById('qstat-'+i).style.color = '#27A155';
          document.getElementById('qprog-'+i).style.width = '100%';
        } else {
          document.getElementById('qstat-'+i).textContent = '✗ ' + (data.message||'Gagal');
          document.getElementById('qstat-'+i).style.color = '#C05621';
          allOk = false;
        }
      } catch(e) {
        document.getElementById('qstat-'+i).textContent = '✗ Error';
        allOk = false;
      }
    }
    setMsg('dk-upload-msg', allOk ? 'Semua file berhasil diupload!' : 'Beberapa file gagal.', allOk);
    if (allOk) reload();
    else uploadBtn.disabled = false;
  });

  /* ── Folder: save (create/rename) ── */
  document.getElementById('dk-btn-save-folder')?.addEventListener('click', async ()=>{
    const name    = document.getElementById('dk-folder-name').value.trim();
    const editId  = document.getElementById('dk-folder-id-edit').value;
    if (!name) { setMsg('dk-folder-msg','Nama folder tidak boleh kosong.',false); return; }

    const fd = new FormData();
    fd.append('name', name);
    if (FOLDER_ID && !editId) fd.append('parent_id', FOLDER_ID);

    const url = editId
      ? BASE+'/api/dokumen/folder/'+editId+'/rename'
      : BASE+'/api/dokumen/folder/create';

    try {
      const data = await (await fetch(url,{method:'POST',body:fd})).json();
      setMsg('dk-folder-msg', data.message, data.success);
      if (data.success) reload();
    } catch(e) { setMsg('dk-folder-msg','Gagal terhubung ke server.',false); }
  });

  /* ── Folder: rename (global fn) ── */
  window.dkRenameFolder = function(id, currentName){
    document.getElementById('dk-folder-modal-title').textContent='Rename Folder';
    document.getElementById('dk-folder-name').value=currentName;
    document.getElementById('dk-folder-id-edit').value=id;
    document.getElementById('dk-folder-msg').innerHTML='';
    document.getElementById('dk-modal-folder').style.display='flex';
  };

  /* ── Folder: delete ── */
  window.dkDeleteFolder = async function(id, name){
    if (!confirm('Hapus folder "'+name+'"?\nFolder harus kosong sebelum dapat dihapus.')) return;
    try {
      const data = await (await fetch(BASE+'/api/dokumen/folder/'+id+'/delete',{method:'POST'})).json();
      if (data.success) { alert(data.message); location.reload(); }
      else alert(data.message);
    } catch(e){ alert('Gagal terhubung ke server.'); }
  };

  /* ── File: delete (soft) ── */
  window.dkDeleteFile = async function(id, name){
    if (!confirm('Pindahkan "'+name+'" ke Sampah?')) return;
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+id+'/delete',{method:'POST'})).json();
      if (data.success) {
        const row = document.querySelector('tr[data-file-id="'+id+'"]');
        if (row) row.remove();
      } else alert(data.message);
    } catch(e){ alert('Gagal terhubung ke server.'); }
  };

  /* ── File: restore ── */
  window.dkRestoreFile = async function(id){
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+id+'/restore',{method:'POST'})).json();
      if (data.success) location.reload();
      else alert(data.message);
    } catch(e){ alert('Gagal terhubung ke server.'); }
  };

  /* ── File: force delete ── */
  window.dkForceDelete = async function(id, name){
    if (!confirm('Hapus permanen "'+name+'"?\nFile tidak dapat dipulihkan kembali.')) return;
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+id+'/force-delete',{method:'POST'})).json();
      if (data.success) {
        const row = document.querySelector('tr[data-file-id="'+id+'"]');
        if (row) row.remove();
      } else alert(data.message);
    } catch(e){ alert('Gagal terhubung ke server.'); }
  };

})();
</script>
