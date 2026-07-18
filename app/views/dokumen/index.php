<?php
/**
 * Rebuild UI/UX Dokumen v2 - Simplified & More Usable
 * Variabel dari DokumenController::index():
 *   $folders, $files, $stats, $breadcrumb
 *   $section (my-files|shared|recent)
 *   $folderId, $filterType, $search
 *
 * CHANGELOG v2 (rebuild UX):
 *   - Overflow menu (...) pada file card, hanya Preview + Download tampil permanen
 *   - Hapus filter tipe file duplikat di sidebar (cukup di search bar)
 *   - Modal konfirmasi hapus custom (bukan confirm() native)
 *   - Live search dengan debounce (auto-submit setelah user berhenti mengetik)
 *   - Stat card "Diupload Bulan Ini" menggantikan "Lokasi" yang kurang berguna
 */
$base      = rtrim(BASE_URL, '/');
$isAdmin   = Auth::hasRole('admin', 'sekretaris');
$canUpload = Auth::hasRole('admin', 'sekretaris');
$myId      = (int)(Auth::user()['id'] ?? 0);
$sectionTitles = [
  'my-files' => 'Semua Dokumen',
  'shared'   => 'Dibagikan ke Saya',
  'recent'   => 'Akses Terbaru',
];
$sectionDescriptions = [
  'my-files' => 'Kelola file, folder, dan distribusi dokumen dalam satu workspace yang lebih rapi.',
  'shared'   => 'Lihat dokumen yang dibagikan ke Anda beserta level aksesnya.',
  'recent'   => 'Akses cepat ke file yang terakhir dibuka atau diperbarui.',
];
$currentTitle = $sectionTitles[$section] ?? 'Dokumen';
$currentDesc  = $sectionDescriptions[$section] ?? 'Kelola dokumen Anda.';

// Hitung file yang diupload bulan ini (fallback jika belum disediakan controller)
$uploadedThisMonth = $stats['uploaded_this_month'] ?? null;
if ($uploadedThisMonth === null && !empty($files)) {
    $uploadedThisMonth = 0;
    $curMonth = date('Y-m');
    foreach ($files as $ff) {
        if (isset($ff['created_at']) && date('Y-m', strtotime($ff['created_at'])) === $curMonth) {
            $uploadedThisMonth++;
        }
    }
}
?>
<style>
:root {
  --dm-bg:#F6F3EE;
  --dm-panel:#FFFFFF;
  --dm-panel-soft:#FBF8F4;
  --dm-line:#E7DED2;
  --dm-line-strong:#D8CCBC;
  --dm-text:#1F1A17;
  --dm-muted:#7A6F63;
  --dm-maroon:#7B1C1C;
  --dm-maroon-dark:#5B1212;
  --dm-blue:#2B6CB0;
  --dm-green:#276749;
  --dm-orange:#C05621;
  --dm-shadow:0 18px 40px rgba(46,33,20,.08);
}
* { box-sizing:border-box; }
.dm-page {
  padding:1.5rem;
  background:linear-gradient(180deg,#F8F5F0 0%, #F4EFE7 100%);
  min-height:calc(100vh - 64px);
}
.dm-layout {
  display:grid;
  grid-template-columns:240px minmax(0,1fr);
  gap:1.25rem;
  align-items:start;
}
.dm-panel {
  background:var(--dm-panel);
  border:1px solid var(--dm-line);
  border-radius:22px;
  box-shadow:var(--dm-shadow);
}
.dm-sidebar { padding:1rem; position:sticky; top:1rem; }
.dm-brand { padding:.55rem .55rem .95rem; border-bottom:1px solid #EFE7DD; margin-bottom:.9rem; }
.dm-brand-badge {
  display:inline-flex; align-items:center; gap:.45rem; font-size:11px; font-weight:800;
  color:var(--dm-maroon); background:rgba(123,28,28,.08); padding:.3rem .65rem; border-radius:999px; margin-bottom:.65rem;
}
.dm-brand-title { font-size:18px; font-weight:900; color:var(--dm-text); line-height:1.15; }
.dm-brand-sub { margin-top:.35rem; font-size:12.5px; color:var(--dm-muted); line-height:1.5; }
.dm-upload-btn {
  width:100%; height:46px; border:none; border-radius:14px;
  background:linear-gradient(135deg,var(--dm-maroon) 0%, #922727 100%);
  color:#fff; font-size:13.5px; font-weight:800; cursor:pointer;
  display:flex; align-items:center; justify-content:center; gap:.55rem;
  box-shadow:0 12px 28px rgba(123,28,28,.22);
  transition:transform .18s ease, box-shadow .18s ease; margin-bottom:1rem;
}
.dm-upload-btn:hover { transform:translateY(-1px); box-shadow:0 16px 30px rgba(123,28,28,.28); }
.dm-nav-group { margin-bottom:.95rem; }
.dm-nav-label {
  padding:.35rem .65rem; margin-bottom:.4rem; font-size:10.5px; letter-spacing:.08em;
  text-transform:uppercase; font-weight:800; color:#9B8F80;
}
.dm-nav-link {
  display:flex; align-items:center; gap:.7rem; width:100%; text-decoration:none; border:none; background:none;
  border-radius:14px; padding:.78rem .8rem; margin-bottom:.25rem; color:#4E463E; font-size:13.5px; font-weight:700;
  cursor:pointer; transition:all .16s ease;
}
.dm-nav-link svg { opacity:.75; flex-shrink:0; }
.dm-nav-link:hover { background:#F6F0E8; color:var(--dm-text); }
.dm-nav-link.active {
  background:linear-gradient(135deg,var(--dm-maroon) 0%, #8B2323 100%); color:#fff; box-shadow:0 12px 24px rgba(123,28,28,.18);
}
.dm-nav-link.active svg { opacity:1; }
.dm-nav-badge {
  margin-left:auto; min-width:24px; height:24px; border-radius:999px; padding:0 .45rem;
  display:inline-flex; align-items:center; justify-content:center; font-size:11px; font-weight:800;
  background:rgba(123,28,28,.1); color:var(--dm-maroon);
}
.dm-nav-link.active .dm-nav-badge { background:rgba(255,255,255,.2); color:#fff; }
.dm-side-note {
  margin-top:.75rem; padding:.85rem .9rem; background:linear-gradient(180deg,#FBF8F4 0%, #F7F2EB 100%);
  border:1px solid #EEE3D7; border-radius:16px;
}
.dm-side-note-title { font-size:12.5px; font-weight:800; color:var(--dm-text); margin-bottom:.25rem; }
.dm-side-note-text { font-size:12px; color:var(--dm-muted); line-height:1.55; }
.dm-main { display:flex; flex-direction:column; gap:1rem; min-width:0; }
.dm-hero {
  padding:1.2rem 1.25rem;
  background:
    radial-gradient(circle at top right, rgba(123,28,28,.13) 0, rgba(123,28,28,0) 38%),
    linear-gradient(135deg,#FFFFFF 0%, #FBF6F0 100%);
}
.dm-hero-top { display:flex; gap:1rem; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; }
.dm-kicker {
  display:inline-flex; align-items:center; gap:.45rem; background:#F6EEE5; color:var(--dm-maroon); border:1px solid #E8D9C8;
  font-size:11px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; padding:.35rem .65rem; border-radius:999px; margin-bottom:.75rem;
}
.dm-title { font-size:30px; line-height:1.08; letter-spacing:-.02em; color:var(--dm-text); font-weight:900; margin:0; }
.dm-subtitle { margin:.55rem 0 0; max-width:760px; color:var(--dm-muted); font-size:14px; line-height:1.6; }
.dm-hero-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
.dm-btn {
  display:inline-flex; align-items:center; justify-content:center; gap:.45rem; min-height:40px; padding:0 .95rem;
  border-radius:12px; cursor:pointer; border:1px solid transparent; text-decoration:none; white-space:nowrap;
  font-size:13px; font-weight:800; transition:all .18s ease;
}
.dm-btn svg { flex-shrink:0; }
.dm-btn-primary { background:var(--dm-maroon); color:#fff; border-color:var(--dm-maroon-dark); box-shadow:0 10px 20px rgba(123,28,28,.16); }
.dm-btn-primary:hover { background:var(--dm-maroon-dark); }
.dm-btn-outline { background:#fff; color:#5A5047; border-color:var(--dm-line-strong); }
.dm-btn-outline:hover { color:var(--dm-maroon); border-color:var(--dm-maroon); }
.dm-btn-share { background:#fff; color:var(--dm-blue); border-color:#D7E3F5; }
.dm-btn-share:hover { background:var(--dm-blue); color:#fff; border-color:var(--dm-blue); }
.dm-btn-public { background:#fff; color:var(--dm-green); border-color:#D9E9DF; }
.dm-btn-public:hover { background:var(--dm-green); color:#fff; border-color:var(--dm-green); }
.dm-btn-danger { background:#fff; color:var(--dm-orange); border-color:#F0D6C9; }
.dm-btn-danger:hover { background:var(--dm-orange); color:#fff; border-color:var(--dm-orange); }
.dm-btn-ghost { background:#F9F4EE; color:#665A4E; border-color:#EFE4D8; }
.dm-btn-ghost:hover { color:var(--dm-text); border-color:#D7C7B5; }
.dm-btn-sm { min-height:34px; padding:0 .75rem; font-size:12px; border-radius:10px; }
.dm-stats-grid { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:.8rem; margin-top:1rem; }
.dm-stat-card {
  background:linear-gradient(180deg,#fff 0%, #FBF7F2 100%); border:1px solid #EEE3D7; border-radius:18px; padding:1rem; min-width:0;
}
.dm-stat-card.clickable { cursor:pointer; transition:transform .16s ease, border-color .16s ease; }
.dm-stat-card.clickable:hover { transform:translateY(-2px); border-color:#D9C4AF; }
.dm-stat-label { font-size:11px; font-weight:800; color:#9D8E7E; letter-spacing:.06em; text-transform:uppercase; margin-bottom:.55rem; }
.dm-stat-value { font-size:24px; font-weight:900; color:var(--dm-text); line-height:1; }
.dm-stat-meta { margin-top:.35rem; font-size:12px; color:var(--dm-muted); line-height:1.45; }
.dm-controls { padding:1rem 1.1rem; display:flex; gap:.9rem; flex-wrap:wrap; align-items:center; justify-content:space-between; }
.dm-search-form { display:flex; gap:.75rem; flex:1; min-width:260px; flex-wrap:wrap; }
.dm-search {
  flex:1; min-width:220px; display:flex; align-items:center; gap:.55rem; background:#FCFAF7; border:1.5px solid var(--dm-line);
  border-radius:14px; min-height:46px; padding:0 .85rem; transition:border-color .16s ease, box-shadow .16s ease; position:relative;
}
.dm-search:focus-within { border-color:var(--dm-maroon); box-shadow:0 0 0 4px rgba(123,28,28,.07); }
.dm-search svg { color:#A49382; flex-shrink:0; }
.dm-search input { flex:1; min-width:0; border:none; background:transparent; outline:none; height:44px; font-size:13.5px; color:var(--dm-text); }
.dm-search-spinner { display:none; }
.dm-search-spinner.show { display:inline-block; }
.dm-filter-select {
  min-height:46px; border:1.5px solid var(--dm-line); border-radius:14px; background:#FCFAF7; font-size:13px; color:var(--dm-text);
  outline:none; padding:0 2.25rem 0 .95rem; appearance:none; -webkit-appearance:none; cursor:pointer;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237A6F63' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat:no-repeat; background-position:right .8rem center;
}
.dm-filter-select:focus { border-color:var(--dm-maroon); }
.dm-breadcrumb-wrap { display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:.8rem; padding:0 .2rem; }
.dm-breadcrumb { display:flex; align-items:center; gap:.45rem; flex-wrap:wrap; }
.dm-breadcrumb a, .dm-breadcrumb span { font-size:12.5px; }
.dm-breadcrumb a { color:var(--dm-maroon); text-decoration:none; font-weight:700; }
.dm-breadcrumb a:hover { text-decoration:underline; }
.dm-breadcrumb-sep { color:#AB9D8D; }
.dm-breadcrumb-current { color:var(--dm-text); font-weight:800; }
.dm-toolbar-info { font-size:12.5px; color:var(--dm-muted); }
.dm-section-card { padding:1.1rem; }
.dm-section-head { display:flex; align-items:center; justify-content:space-between; gap:.75rem; margin-bottom:.9rem; flex-wrap:wrap; }
.dm-section-title { display:flex; align-items:center; gap:.6rem; font-size:14px; font-weight:900; color:var(--dm-text); letter-spacing:.01em; }
.dm-section-title svg { color:var(--dm-maroon); }
.dm-section-hint { font-size:12px; color:var(--dm-muted); }
.dm-folder-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:.9rem; }
.dm-folder-card {
  position:relative; display:block; text-decoration:none; background:linear-gradient(180deg,#FFFDF9 0%, #FBF6EF 100%);
  border:1px solid #EEE1D3; border-radius:20px; padding:1rem; min-height:152px; overflow:hidden;
  transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.dm-folder-card:hover { transform:translateY(-2px); border-color:#D9C4AF; box-shadow:0 18px 30px rgba(102,78,52,.1); }
.dm-folder-card::after { content:''; position:absolute; inset:auto -20px -38px auto; width:110px; height:110px; border-radius:50%; background:rgba(217,119,6,.08); }
.dm-folder-top { display:flex; align-items:flex-start; justify-content:space-between; gap:.75rem; position:relative; z-index:1; }
.dm-folder-icon {
  width:48px; height:48px; border-radius:16px; background:linear-gradient(180deg,#FFF2CC 0%, #FFE49C 100%);
  display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:inset 0 0 0 1px rgba(217,119,6,.08);
}
.dm-folder-menu {
  width:34px; height:34px; border:none; border-radius:10px; background:#fff; color:#8D7E6C; cursor:pointer;
  display:flex; align-items:center; justify-content:center; border:1px solid #E8DCCA; opacity:.55; transition:all .18s ease;
}
.dm-folder-card:hover .dm-folder-menu, .dm-folder-menu:focus { opacity:1; }
.dm-folder-menu:hover { color:var(--dm-text); border-color:#DCC7AF; }
.dm-folder-name { margin-top:.85rem; font-size:15px; font-weight:900; color:var(--dm-text); line-height:1.35; word-break:break-word; position:relative; z-index:1; }
.dm-folder-meta { margin-top:.45rem; font-size:12.5px; color:var(--dm-muted); line-height:1.5; position:relative; z-index:1; }
.dm-folder-footer { margin-top:1rem; display:flex; align-items:center; justify-content:space-between; position:relative; z-index:1; }
.dm-folder-badge {
  display:inline-flex; align-items:center; gap:.35rem; border-radius:999px; background:#fff; border:1px solid #E9DCCB;
  color:#6A5F54; padding:.28rem .65rem; font-size:11px; font-weight:800;
}
.dm-content-card { padding:1rem 1.1rem 1.1rem; }
.dm-content-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; margin-bottom:1rem; }
.dm-content-title { font-size:16px; font-weight:900; color:var(--dm-text); margin:0; }
.dm-content-sub { font-size:12.5px; color:var(--dm-muted); margin-top:.3rem; }
.dm-view-switch { display:flex; align-items:center; gap:.35rem; padding:.3rem; background:#F7F1E8; border:1px solid #E9DDD0; border-radius:14px; }
.dm-view-switch button {
  height:34px; min-width:34px; padding:0 .8rem; border:none; border-radius:10px; cursor:pointer; background:transparent;
  color:#7B6E5F; font-size:12px; font-weight:800; display:inline-flex; align-items:center; justify-content:center; gap:.4rem;
}
.dm-view-switch button.active { background:#fff; color:var(--dm-text); box-shadow:0 4px 12px rgba(72,57,40,.08); }
.dm-file-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:.9rem; }
.dm-file-card {
  display:flex; gap:.9rem; align-items:flex-start; background:linear-gradient(180deg,#FFFFFF 0%, #FCF8F4 100%);
  border:1px solid #EEE3D7; border-radius:20px; padding:1rem; transition:transform .18s ease, border-color .18s ease, box-shadow .18s ease;
  position:relative;
}
.dm-file-card:hover { transform:translateY(-2px); border-color:#DACBB9; box-shadow:0 16px 28px rgba(74,53,31,.08); }
.dm-file-icon {
  width:48px; height:48px; border-radius:16px; display:flex; align-items:center; justify-content:center; color:#fff;
  font-size:11px; font-weight:900; flex-shrink:0; box-shadow:inset 0 -8px 18px rgba(0,0,0,.08);
}
.dm-file-body { flex:1; min-width:0; }
.dm-file-top { display:flex; align-items:flex-start; gap:.65rem; justify-content:space-between; }
.dm-file-name { font-size:14px; font-weight:900; color:var(--dm-text); line-height:1.45; word-break:break-word; cursor:pointer; }
.dm-file-name:hover { color:var(--dm-maroon); }
.dm-file-tags { display:flex; gap:.45rem; flex-wrap:wrap; margin-top:.55rem; }
.dm-chip { display:inline-flex; align-items:center; gap:.3rem; min-height:26px; padding:0 .65rem; border-radius:999px; font-size:11px; font-weight:800; border:1px solid transparent; }
.dm-chip-soft { background:#F5EEE6; color:#6A5E52; border-color:#E8D9C8; }
.dm-chip-view { background:#EAF4FF; color:#2B6CB0; border-color:#D8E9FA; }
.dm-chip-download { background:#E8F7EE; color:#276749; border-color:#D4EBDA; }
.dm-chip-owner { background:#FFF2E7; color:#B45309; border-color:#F3DEC7; }
.dm-file-meta { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.5rem .8rem; margin-top:.85rem; }
.dm-meta-item { min-width:0; }
.dm-meta-label { font-size:10.5px; font-weight:800; color:#A08F7D; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.18rem; }
.dm-meta-value { font-size:12.5px; color:#4D433B; line-height:1.45; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.dm-file-actions { display:flex; gap:.45rem; flex-wrap:wrap; margin-top:.95rem; align-items:center; }

/* Overflow menu (kebab) */
.dm-menu-wrap { position:relative; margin-left:auto; }
.dm-menu-btn {
  width:34px; height:34px; border-radius:10px; border:1px solid var(--dm-line-strong); background:#fff; color:#5A5047;
  cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all .16s ease;
}
.dm-menu-btn:hover { color:var(--dm-maroon); border-color:var(--dm-maroon); }
.dm-menu-dropdown {
  position:absolute; right:0; top:calc(100% + 6px); min-width:190px; background:#fff; border:1px solid #E8DDD1;
  border-radius:14px; box-shadow:0 18px 36px rgba(45,30,17,.16); z-index:60; display:none; overflow:hidden; padding:.35rem;
}
.dm-menu-dropdown.open { display:block; }
.dm-menu-item {
  display:flex; align-items:center; gap:.6rem; width:100%; text-align:left; border:none; background:none; cursor:pointer;
  padding:.65rem .7rem; border-radius:10px; font-size:12.5px; font-weight:700; color:var(--dm-text); transition:background .14s ease;
}
.dm-menu-item:hover { background:#F7F1E8; }
.dm-menu-item.danger { color:var(--dm-orange); }
.dm-menu-item.danger:hover { background:#FDE9E2; }
.dm-menu-item svg { flex-shrink:0; opacity:.85; }

.dm-file-grid.dm-view-list { grid-template-columns:1fr !important; gap:.8rem; }
.dm-file-grid.dm-view-list .dm-file-card { align-items:center; }
.dm-file-grid.dm-view-list .dm-file-icon { width:54px; height:54px; border-radius:18px; }
.dm-file-grid.dm-view-list .dm-file-body { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(220px,.95fr) auto; gap:1rem; align-items:center; }
.dm-file-grid.dm-view-list .dm-file-top, .dm-file-grid.dm-view-list .dm-file-meta, .dm-file-grid.dm-view-list .dm-file-actions { margin-top:0; }
.dm-file-grid.dm-view-list .dm-file-top { display:block; }
.dm-file-grid.dm-view-list .dm-file-tags { margin-top:.4rem; }
.dm-file-grid.dm-view-list .dm-file-meta { grid-template-columns:repeat(2,minmax(0,1fr)); gap:.4rem .9rem; }
.dm-file-grid.dm-view-list .dm-file-actions { justify-content:flex-end; }
.dm-empty { padding:3rem 1.25rem; text-align:center; background:linear-gradient(180deg,#FFFDFB 0%, #FAF5EF 100%); border:1px dashed #E7D8C8; border-radius:22px; }
.dm-empty-icon { width:72px; height:72px; margin:0 auto 1rem; border-radius:22px; background:#F4EBE1; color:#B39B85; display:flex; align-items:center; justify-content:center; }
.dm-empty h3 { margin:0; font-size:18px; font-weight:900; color:var(--dm-text); }
.dm-empty p { margin:.55rem auto 0; max-width:520px; font-size:13.5px; color:var(--dm-muted); line-height:1.65; }
.dm-empty .dm-empty-actions { margin-top:1rem; display:flex; gap:.6rem; justify-content:center; flex-wrap:wrap; }
.dm-msg { margin-top:.75rem; font-size:12.5px; }
.dm-msg-ok, .dm-msg-err { display:inline-flex; align-items:center; gap:.4rem; font-weight:700; }
.dm-msg-ok { color:#1E8B4D; }
.dm-msg-err { color:#C05621; }
.dm-modal-overlay {
  position:fixed; inset:0; background:rgba(17,12,8,.5); backdrop-filter:blur(3px); z-index:1050; display:flex;
  align-items:center; justify-content:center; opacity:0; pointer-events:none; transition:opacity .2s ease;
}
.dm-modal-overlay.open { opacity:1; pointer-events:auto; }
.dm-modal {
  width:100%; max-width:470px; max-height:90vh; overflow:auto; background:#fff; border-radius:22px; border:1px solid #EEE4D9;
  box-shadow:0 28px 70px rgba(15,10,7,.24); transform:translateY(18px) scale(.98); transition:transform .2s ease;
}
.dm-modal-overlay.open .dm-modal { transform:none; }
.dm-modal-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; padding:1.15rem 1.2rem .9rem; border-bottom:1px solid #F1E8DD; }
.dm-modal-title { font-size:16px; font-weight:900; color:var(--dm-text); }
.dm-modal-close { width:34px; height:34px; border:none; border-radius:12px; cursor:pointer; background:#F8F3EC; color:#847563; display:flex; align-items:center; justify-content:center; }
.dm-modal-close:hover { color:var(--dm-text); background:#F0E6DB; }
.dm-modal-body { padding:1.2rem; }
.dm-modal-footer { padding:0 1.2rem 1.2rem; display:flex; gap:.6rem; justify-content:flex-end; flex-wrap:wrap; }
.dm-label { display:block; margin-bottom:.38rem; font-size:11px; font-weight:800; color:#8E7F6D; text-transform:uppercase; letter-spacing:.07em; }
.dm-ctrl {
  width:100%; min-height:44px; border:1.5px solid var(--dm-line); border-radius:14px; background:#FCFAF7; outline:none;
  padding:0 .95rem; color:var(--dm-text); font-size:13.5px; transition:border-color .16s ease, box-shadow .16s ease;
}
.dm-ctrl:focus { border-color:var(--dm-maroon); box-shadow:0 0 0 4px rgba(123,28,28,.07); }
.dm-dropzone { border:2px dashed #DECDBC; border-radius:18px; background:#FCFAF7; padding:2rem 1.25rem; text-align:center; cursor:pointer; transition:all .18s ease; }
.dm-dropzone:hover, .dm-dropzone.dragover { border-color:var(--dm-maroon); background:#FBF4F1; }
.dm-dropzone svg { display:block; margin:0 auto .8rem; color:#C8B5A0; }
.dm-dropzone p { margin:0; font-size:14px; color:#5E5348; }
.dm-dropzone small { display:block; margin-top:.35rem; font-size:12px; color:#9C8F80; }
.dm-progress { background:#E9DED1; border-radius:999px; height:8px; margin-top:1rem; overflow:hidden; display:none; }
.dm-progress-bar { height:100%; width:0; background:linear-gradient(90deg,var(--dm-maroon) 0%, #B43737 100%); border-radius:999px; }
.dm-progress-label { font-size:11.5px; color:#8C7E70; margin-top:.4rem; }
.dm-user-search-wrap { position:relative; }
.dm-user-dropdown {
  position:absolute; top:calc(100% + 6px); left:0; right:0; z-index:50; background:#fff; border:1px solid #E8DDD1;
  border-radius:14px; box-shadow:0 18px 30px rgba(45,30,17,.12); max-height:220px; overflow:auto; display:none;
}
.dm-user-dropdown.open { display:block; }
.dm-user-option { padding:.7rem .85rem; font-size:13px; color:var(--dm-text); cursor:pointer; display:flex; align-items:center; gap:.45rem; transition:background .16s ease; }
.dm-user-option:hover { background:#F8F2EB; }
.dm-user-option small { color:#9B8E80; }
.dm-share-list, .dm-pub-link-list { display:flex; flex-direction:column; gap:.55rem; margin-top:.95rem; }
.dm-share-item, .dm-pub-link-item { background:#FBF8F4; border:1px solid #EEE2D5; border-radius:16px; padding:.75rem .85rem; }
.dm-share-item { display:flex; align-items:center; gap:.7rem; }
.dm-share-avatar { width:34px; height:34px; border-radius:50%; background:var(--dm-maroon); color:#fff; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:900; flex-shrink:0; }
.dm-share-info { flex:1; min-width:0; }
.dm-share-name { font-size:13px; font-weight:800; color:var(--dm-text); }
.dm-share-role { font-size:11.5px; color:#9A8D7F; }
.dm-share-perm-select { min-height:34px; border-radius:10px; border:1px solid #DDCFBE; background:#fff; padding:0 .65rem; font-size:12px; font-weight:800; color:var(--dm-text); outline:none; }
.dm-share-revoke { width:30px; height:30px; border:none; border-radius:10px; cursor:pointer; color:#9E9183; background:transparent; display:flex; align-items:center; justify-content:center; }
.dm-share-revoke:hover { background:#FDE9E2; color:#C05621; }
.dm-pub-form { background:#FCFAF7; border:1px solid #EEE2D5; border-radius:18px; padding:1rem; display:flex; flex-direction:column; gap:.8rem; }
.dm-pub-form-row { display:flex; gap:.7rem; flex-wrap:wrap; }
.dm-pub-form-col { flex:1; min-width:140px; }
.dm-pub-link-url { display:flex; gap:.55rem; align-items:center; }
.dm-pub-link-url input { flex:1; min-width:0; min-height:38px; border:1px solid #DECFBD; border-radius:12px; background:#fff; color:#403831; padding:0 .8rem; font-size:12px; font-family:ui-monospace, SFMono-Regular, Menlo, monospace; }
.dm-pub-link-copy { min-height:38px; border:none; border-radius:12px; background:var(--dm-green); color:#fff; padding:0 .8rem; font-size:12px; font-weight:800; cursor:pointer; }
.dm-pub-link-copy:hover { background:#1F5339; }
.dm-pub-link-copy.copied { background:#1E8B4D; }
.dm-pub-link-meta { margin-top:.55rem; display:flex; gap:.45rem; align-items:center; flex-wrap:wrap; font-size:11px; color:#7B6F63; }
.dm-pub-link-badge { display:inline-flex; align-items:center; gap:.25rem; border-radius:999px; padding:.2rem .55rem; font-size:10.5px; font-weight:800; background:#E8F7EE; color:var(--dm-green); }
.dm-pub-link-badge.locked { background:#EAF4FF; color:#2B6CB0; }
.dm-pub-link-badge.expired { background:#FDE9E2; color:#C05621; }
.dm-pub-link-del { margin-left:auto; border:none; background:transparent; color:#948678; font-size:12px; font-weight:800; cursor:pointer; padding:.2rem .45rem; border-radius:8px; }
.dm-pub-link-del:hover { background:#FDE9E2; color:#C05621; }
.spinner-border { width:18px; height:18px; border:2px solid #E0D4C8; border-top-color:var(--dm-maroon); border-radius:50%; display:inline-block; animation:dmspin .8s linear infinite; }
.spinner-border-sm { width:16px; height:16px; }
@keyframes dmspin { to { transform:rotate(360deg); } }
@media(max-width:1180px) {
  .dm-layout { grid-template-columns:1fr; }
  .dm-sidebar { position:static; }
  .dm-stats-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media(max-width:900px) {
  .dm-file-grid.dm-view-list .dm-file-body { grid-template-columns:1fr; gap:.85rem; align-items:flex-start; }
  .dm-file-grid.dm-view-list .dm-file-actions { justify-content:flex-start; }
}
@media(max-width:768px) {
  .dm-page { padding:1rem; }
  .dm-title { font-size:24px; }
  .dm-controls { padding:.9rem; }
  .dm-search-form { min-width:0; }
  .dm-folder-grid, .dm-file-grid { grid-template-columns:1fr; }
  .dm-stats-grid { grid-template-columns:1fr; }
  .dm-file-meta { grid-template-columns:1fr; }
  .dm-file-card { padding:.9rem; }
  .dm-sidebar { padding:.9rem; }
}
</style>

<div class="dm-page">
  <div class="dm-layout">
    <aside class="dm-sidebar dm-panel">
      <div class="dm-brand">
        <div class="dm-brand-badge">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
          Workspace Dokumen
        </div>
        <div class="dm-brand-title">Arsip lebih tertata, akses lebih cepat.</div>
        <div class="dm-brand-sub">Navigasi ringkas untuk file pribadi, dokumen yang dibagikan, dan akses terbaru.</div>
      </div>

      <?php if ($canUpload): ?>
      <button class="dm-upload-btn" id="btn-open-upload">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Dokumen
      </button>
      <?php endif; ?>

      <div class="dm-nav-group">
        <div class="dm-nav-label">Navigasi</div>
        <a href="<?= $base ?>/dokumen" class="dm-nav-link <?= $section==='my-files' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Semua File
          <span class="dm-nav-badge"><?= (int)$stats['total_files'] ?></span>
        </a>
        <a href="<?= $base ?>/dokumen?section=shared" class="dm-nav-link <?= $section==='shared' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
          Dibagikan ke Saya
          <?php if (!empty($stats['shared_count'])): ?>
          <span class="dm-nav-badge"><?= (int)$stats['shared_count'] ?></span>
          <?php endif; ?>
        </a>
        <a href="<?= $base ?>/dokumen?section=recent" class="dm-nav-link <?= $section==='recent' ? 'active' : '' ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Terbaru
        </a>
      </div>

      <div class="dm-side-note">
        <div class="dm-side-note-title">Tips cepat</div>
        <div class="dm-side-note-text">Klik nama file untuk preview. Gunakan menu (⋮) pada file untuk Bagikan, Link Publik, atau Hapus.</div>
      </div>
    </aside>

    <main class="dm-main">
      <section class="dm-hero dm-panel">
        <div class="dm-hero-top">
          <div>
            <div class="dm-kicker">
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              Area Dokumen
            </div>
            <h1 class="dm-title"><?= htmlspecialchars($currentTitle) ?></h1>
            <p class="dm-subtitle"><?= htmlspecialchars($currentDesc) ?></p>
          </div>
          <div class="dm-hero-actions">
            <?php if ($canUpload): ?>
            <button type="button" class="dm-btn dm-btn-primary" onclick="openUploadModal()">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Upload
            </button>
            <button type="button" class="dm-btn dm-btn-outline" id="btn-open-folder">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              Folder Baru
            </button>
            <?php endif; ?>
          </div>
        </div>

        <div class="dm-stats-grid">
          <div class="dm-stat-card">
            <div class="dm-stat-label">Total File</div>
            <div class="dm-stat-value"><?= (int)$stats['total_files'] ?></div>
            <div class="dm-stat-meta">Jumlah dokumen yang tersedia pada workspace saat ini.</div>
          </div>
          <div class="dm-stat-card">
            <div class="dm-stat-label">Total Penyimpanan</div>
            <div class="dm-stat-value" style="font-size:22px"><?= htmlspecialchars($stats['total_size_fmt']) ?></div>
            <div class="dm-stat-meta">Akumulasi kapasitas file yang sudah diunggah.</div>
          </div>
          <div class="dm-stat-card">
            <div class="dm-stat-label">Shared</div>
            <div class="dm-stat-value"><?= (int)($stats['shared_count'] ?? 0) ?></div>
            <div class="dm-stat-meta">Dokumen yang dibagikan ke akun Anda oleh pengguna lain.</div>
          </div>
          <div class="dm-stat-card clickable" id="stat-month-card" title="Lihat file yang diupload bulan ini">
            <div class="dm-stat-label">Diupload Bulan Ini</div>
            <div class="dm-stat-value"><?= (int)($uploadedThisMonth ?? 0) ?></div>
            <div class="dm-stat-meta">File baru yang masuk pada <?= date('F Y') ?>.</div>
          </div>
        </div>
      </section>

      <section class="dm-panel dm-controls">
        <form method="GET" action="<?= $base ?>/dokumen" class="dm-search-form" id="dm-search-form">
          <input type="hidden" name="section" value="<?= htmlspecialchars($section) ?>">
          <?php if ($folderId): ?><input type="hidden" name="folder" value="<?= (int)$folderId ?>"><?php endif; ?>
          <div class="dm-search">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" name="q" id="dm-search-input" value="<?= htmlspecialchars($search) ?>" placeholder="Cari nama file, tipe dokumen, atau folder...">
            <span class="dm-search-spinner spinner-border spinner-border-sm" id="dm-search-spinner"></span>
          </div>
          <select name="type" id="dm-type-select" class="dm-filter-select">
            <option value="">Semua Tipe</option>
            <option value="pdf" <?= $filterType==='pdf'?'selected':'' ?>>PDF</option>
            <option value="word" <?= $filterType==='word'?'selected':'' ?>>Word</option>
            <option value="sheet" <?= $filterType==='sheet'?'selected':'' ?>>Excel</option>
            <option value="image" <?= $filterType==='image'?'selected':'' ?>>Gambar</option>
            <option value="video" <?= $filterType==='video'?'selected':'' ?>>Video</option>
            <option value="zip" <?= $filterType==='zip'?'selected':'' ?>>ZIP</option>
          </select>
          <noscript><button class="dm-btn dm-btn-ghost" type="submit">Terapkan</button></noscript>
        </form>
        <div class="dm-toolbar-info"><?= count($files) ?> file ditampilkan<?= $filterType ? ' · filter ' . htmlspecialchars(strtoupper($filterType)) : '' ?></div>
      </section>

      <?php if (!empty($breadcrumb)): ?>
      <section class="dm-breadcrumb-wrap">
        <div class="dm-breadcrumb">
          <a href="<?= $base ?>/dokumen">Root</a>
          <?php foreach ($breadcrumb as $crumb): ?>
            <span class="dm-breadcrumb-sep">›</span>
            <?php if ((int)$crumb['id'] === (int)$folderId): ?>
              <span class="dm-breadcrumb-current"><?= htmlspecialchars($crumb['name']) ?></span>
            <?php else: ?>
              <a href="<?= $base ?>/dokumen?folder=<?= (int)$crumb['id'] ?>"><?= htmlspecialchars($crumb['name']) ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <?php if ($section === 'my-files' && !empty($folders)): ?>
      <section class="dm-panel dm-section-card">
        <div class="dm-section-head">
          <div>
            <div class="dm-section-title">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              Folder
            </div>
          </div>
          <div class="dm-toolbar-info"><?= count($folders) ?> folder</div>
        </div>
        <div class="dm-folder-grid" id="folder-grid">
          <?php foreach ($folders as $folder): ?>
          <a href="<?= $base ?>/dokumen?folder=<?= (int)$folder['id'] ?>" class="dm-folder-card" data-folder-id="<?= (int)$folder['id'] ?>">
            <div class="dm-folder-top">
              <div class="dm-folder-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="#D97706" stroke="#D97706" stroke-width="0"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              </div>
              <?php if ($canUpload): ?>
              <button class="dm-folder-menu" data-folder-id="<?= (int)$folder['id'] ?>" data-folder-name="<?= htmlspecialchars($folder['name']) ?>" onclick="event.preventDefault();openFolderMenu(this)" title="Ubah nama folder">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
              </button>
              <?php endif; ?>
            </div>
            <div class="dm-folder-name"><?= htmlspecialchars($folder['name']) ?></div>
            <div class="dm-folder-meta"><?= (int)$folder['file_count'] ?> file · <?= DokumenModel::formatSize((int)$folder['total_size']) ?></div>
            <div class="dm-folder-footer">
              <span class="dm-folder-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                Buka folder
              </span>
            </div>
            <?php if ($canUpload): ?>
            <div class="dm-folder-footer" style="margin-top:.65rem;gap:.45rem;flex-wrap:wrap">
              <button type="button" class="dm-btn dm-btn-share dm-btn-sm btn-share-folder" data-folder-id="<?= (int)$folder['id'] ?>" data-folder-name="<?= htmlspecialchars($folder['name']) ?>">Bagikan</button>
              <button type="button" class="dm-btn dm-btn-danger dm-btn-sm btn-delete-folder" data-folder-id="<?= (int)$folder['id'] ?>" data-folder-name="<?= htmlspecialchars($folder['name']) ?>">Hapus</button>
            </div>
            <?php endif; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php endif; ?>

      <section class="dm-panel dm-content-card">
        <div class="dm-content-head">
          <div>
            <h2 class="dm-content-title"><?= $section==='shared' ? 'Daftar Dokumen yang Dibagikan' : ($section==='recent' ? 'Dokumen Terakhir Diakses' : 'Daftar File') ?></h2>
            <div class="dm-content-sub">Preview dan Download tersedia langsung. Aksi lain ada di menu (⋮).</div>
          </div>
          <div class="dm-view-switch" role="group" aria-label="Mode tampilan file">
            <button type="button" data-view-btn="grid" class="active" aria-pressed="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect></svg>
              Grid
            </button>
            <button type="button" data-view-btn="list" aria-pressed="false">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
              List
            </button>
          </div>
        </div>

        <?php if (empty($files)): ?>
        <div class="dm-empty">
          <div class="dm-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
          </div>
          <h3><?= $search ? 'Tidak ada hasil yang cocok' : 'Belum ada dokumen di area ini' ?></h3>
          <p><?= $search ? 'Coba ubah kata kunci pencarian atau hapus filter tipe file agar hasil yang relevan muncul kembali.' : 'Mulai dengan mengunggah file pertama atau buat folder untuk menata arsip berdasarkan kategori, periode, atau unit kerja.' ?></p>
          <?php if ($canUpload && !$search && $section==='my-files'): ?>
          <div class="dm-empty-actions">
            <button type="button" class="dm-btn dm-btn-primary" onclick="openUploadModal()">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Upload File Pertama
            </button>
            <button type="button" class="dm-btn dm-btn-outline" id="btn-open-folder-empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
              Buat Folder
            </button>
          </div>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="dm-file-grid dm-view-grid" id="file-tbody">
          <?php foreach ($files as $f):
            $isOwner    = (int)$f['uploaded_by'] === $myId;
            $canShare   = $isOwner || Auth::hasRole('admin');
            $canDl      = $isOwner || Auth::hasRole('admin') || ($f['share_permission'] ?? '') === 'download';
            $canDelete  = $f['can_delete'];
            $perm       = $f['share_permission'] ?? 'view';
          ?>
          <article class="dm-file-card" id="file-row-<?= (int)$f['id'] ?>">
            <div class="dm-file-icon" style="background:<?= htmlspecialchars($f['mime_color']) ?>">
              <?= htmlspecialchars($f['mime_label']) ?>
            </div>
            <div class="dm-file-body">
              <div class="dm-file-top">
                <div style="min-width:0;flex:1">
                  <div class="dm-file-name" onclick="openPreview(<?= (int)$f['id'] ?>)"><?= htmlspecialchars($f['original_name']) ?></div>
                  <div class="dm-file-tags">
                    <span class="dm-chip dm-chip-soft"><?= htmlspecialchars($f['mime_label']) ?></span>
                    <?php if ($section==='shared'): ?>
                      <span class="dm-chip <?= $perm==='download' ? 'dm-chip-download' : 'dm-chip-view' ?>"><?= $perm==='download' ? 'Download' : 'View Only' ?></span>
                    <?php elseif ($isOwner): ?>
                      <span class="dm-chip dm-chip-owner">Milik Saya</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div class="dm-file-meta">
                <div class="dm-meta-item">
                  <div class="dm-meta-label">Ukuran</div>
                  <div class="dm-meta-value"><?= htmlspecialchars($f['size_fmt']) ?></div>
                </div>
                <div class="dm-meta-item">
                  <div class="dm-meta-label">Tanggal</div>
                  <div class="dm-meta-value"><?= date('d M Y', strtotime($f['created_at'])) ?></div>
                </div>
                <div class="dm-meta-item">
                  <div class="dm-meta-label">Uploader</div>
                  <div class="dm-meta-value"><?= htmlspecialchars($f['uploader_name'] ?? '-') ?></div>
                </div>
                <div class="dm-meta-item">
                  <div class="dm-meta-label">Akses</div>
                  <div class="dm-meta-value"><?= $section==='shared' ? ($perm==='download' ? 'View + Download' : 'View Only') : ($canShare ? 'Kelola & Bagikan' : 'Akses terbatas') ?></div>
                </div>
              </div>

              <div class="dm-file-actions">
                <button class="dm-btn dm-btn-outline dm-btn-sm" type="button" onclick="openPreview(<?= (int)$f['id'] ?>)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8S1 12 1 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                  Preview
                </button>

                <?php if ($canDl): ?>
                <a href="<?= $base ?>/dokumen/<?= (int)$f['id'] ?>/download" class="dm-btn dm-btn-ghost dm-btn-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                  Download
                </a>
                <?php endif; ?>

                <?php if (($canShare && $section !== 'shared') || $canDelete): ?>
                <div class="dm-menu-wrap">
                  <button type="button" class="dm-menu-btn" onclick="toggleFileMenu(event, <?= (int)$f['id'] ?>)" title="Aksi lainnya" aria-label="Aksi lainnya">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg>
                  </button>
                  <div class="dm-menu-dropdown" id="file-menu-<?= (int)$f['id'] ?>">
                    <?php if ($canShare && $section !== 'shared'): ?>
                    <button type="button" class="dm-menu-item" onclick="closeAllMenus();openPublicLinkModal(<?= (int)$f['id'] ?>, '<?= addslashes($f['original_name']) ?>')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                      Link Publik
                    </button>
                    <button type="button" class="dm-menu-item" onclick="closeAllMenus();openShareModal(<?= (int)$f['id'] ?>, '<?= addslashes($f['original_name']) ?>')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                      Bagikan
                    </button>
                    <?php endif; ?>
                    <?php if ($canDelete): ?>
                    <button type="button" class="dm-menu-item danger" onclick="closeAllMenus();confirmDeleteFile(<?= (int)$f['id'] ?>, '<?= addslashes($f['original_name']) ?>')">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                      Hapus
                    </button>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>
              </div>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div id="page-msg" class="dm-msg"></div>
      </section>
    </main>
  </div>
</div>

<div class="dm-modal-overlay" id="modal-upload">
  <div class="dm-modal">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title">Upload Dokumen</div>
        <div style="font-size:12px;color:#9A8D7F;margin-top:.2rem">Tambahkan satu atau beberapa file ke workspace aktif.</div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-upload')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div class="dm-dropzone" id="upload-dropzone">
        <input type="file" id="input-file-upload" multiple style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <p>Klik atau <strong>seret file</strong> ke area ini</p>
        <small>Maksimal 50 MB per file · PDF, Word, Excel, PPT, gambar, video, ZIP</small>
      </div>
      <div class="dm-progress" id="upload-progress"><div class="dm-progress-bar" id="upload-progress-bar"></div></div>
      <div class="dm-progress-label" id="upload-progress-label" style="display:none"></div>
      <div id="upload-file-list" style="margin-top:.85rem"></div>
      <div id="upload-msg" class="dm-msg"></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-upload')">Tutup</button>
      <button class="dm-btn dm-btn-primary" id="btn-do-upload" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload
      </button>
    </div>
  </div>
</div>

<div class="dm-modal-overlay" id="modal-folder">
  <div class="dm-modal">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title" id="folder-modal-title">Buat Folder Baru</div>
        <div style="font-size:12px;color:#9A8D7F;margin-top:.2rem">Gunakan folder untuk merapikan arsip berdasarkan kategori atau periode.</div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-folder')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div style="margin-bottom:1rem">
        <label class="dm-label">Nama Folder <span style="color:#C05621">*</span></label>
        <input type="text" class="dm-ctrl" id="input-folder-name" placeholder="Contoh: Laporan 2026" maxlength="100">
      </div>
      <input type="hidden" id="folder-action-id" value="">
      <div id="folder-msg" class="dm-msg"></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-folder')">Batal</button>
      <button class="dm-btn dm-btn-primary" id="btn-do-folder" onclick="submitFolder()">Simpan</button>
    </div>
  </div>
</div>

<div class="dm-modal-overlay" id="modal-share">
  <div class="dm-modal" style="max-width:560px">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title">Bagikan Dokumen</div>
        <div id="share-modal-filename" style="font-size:12px;color:#9A8D7F;margin-top:.2rem"></div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-share')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div style="display:flex;gap:.65rem;align-items:flex-end;flex-wrap:wrap;margin-bottom:.75rem">
        <div style="flex:1;min-width:180px">
          <label class="dm-label">Cari User</label>
          <div class="dm-user-search-wrap">
            <input type="text" class="dm-ctrl" id="share-user-search" placeholder="Ketik nama atau username..." autocomplete="off">
            <div class="dm-user-dropdown" id="share-user-dropdown"></div>
            <input type="hidden" id="share-selected-user-id">
            <div id="share-selected-user-name" style="font-size:12px;color:var(--dm-maroon);font-weight:800;margin-top:.3rem;min-height:16px"></div>
          </div>
        </div>
        <div>
          <label class="dm-label">Akses</label>
          <select class="dm-ctrl" id="share-permission" style="width:150px">
            <option value="view">View Only</option>
            <option value="download">View + Download</option>
          </select>
        </div>
        <button class="dm-btn dm-btn-share" id="btn-do-share" onclick="submitShare()">Bagikan</button>
      </div>
      <div id="share-msg" class="dm-msg"></div>
      <div style="font-size:11px;font-weight:800;color:#9A8D7F;text-transform:uppercase;letter-spacing:.07em;margin:1rem 0 .4rem">Sudah Dibagikan ke</div>
      <div class="dm-share-list" id="share-list"><div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0"><div class="spinner-border spinner-border-sm"></div></div></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-share')">Tutup</button>
    </div>
  </div>
</div>

<div class="dm-modal-overlay" id="modal-public-link">
  <div class="dm-modal" style="max-width:600px">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title">Link Publik</div>
        <div id="pub-modal-filename" style="font-size:12px;color:#9A8D7F;margin-top:.2rem"></div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-public-link')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div style="font-size:11px;font-weight:800;color:#8E7F6D;text-transform:uppercase;letter-spacing:.07em;margin-bottom:.55rem">Buat Link Baru</div>
      <div class="dm-pub-form">
        <div class="dm-pub-form-row">
          <div class="dm-pub-form-col">
            <label class="dm-label">Izin Akses</label>
            <select class="dm-ctrl" id="pub-permission">
              <option value="view">View Only</option>
              <option value="download">View + Download</option>
            </select>
          </div>
          <div class="dm-pub-form-col">
            <label class="dm-label">Kadaluarsa</label>
            <input type="datetime-local" class="dm-ctrl" id="pub-expires-at">
          </div>
        </div>
        <div class="dm-pub-form-row">
          <div class="dm-pub-form-col">
            <label class="dm-label">Password</label>
            <input type="text" class="dm-ctrl" id="pub-password" placeholder="Opsional">
          </div>
          <div class="dm-pub-form-col">
            <label class="dm-label">Maks. Download</label>
            <input type="number" class="dm-ctrl" id="pub-max-dl" min="1" placeholder="Tidak terbatas">
          </div>
        </div>
        <div style="text-align:right">
          <button class="dm-btn dm-btn-primary" id="btn-create-pub-link" onclick="createPublicLink()">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Buat Link
          </button>
        </div>
      </div>
      <div id="pub-link-msg" class="dm-msg"></div>
      <div style="font-size:11px;font-weight:800;color:#9A8D7F;text-transform:uppercase;letter-spacing:.07em;margin:1.1rem 0 .45rem">Link Aktif</div>
      <div class="dm-pub-link-list" id="pub-link-list"><div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0"><div class="spinner-border spinner-border-sm"></div></div></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-public-link')">Tutup</button>
    </div>
  </div>
</div>

<!-- Modal konfirmasi hapus file (custom, bukan confirm() native) -->
<div class="dm-modal-overlay" id="modal-confirm-delete-file">
  <div class="dm-modal" style="max-width:440px">
    <div class="dm-modal-header">
      <div>
        <div class="dm-modal-title">Hapus File</div>
        <div id="confirm-delete-filename" style="font-size:12px;color:#9A8D7F;margin-top:.2rem"></div>
      </div>
      <button class="dm-modal-close" onclick="closeModal('modal-confirm-delete-file')">&times;</button>
    </div>
    <div class="dm-modal-body">
      <p style="margin:0;color:#5A5047;font-size:13px;line-height:1.6">File yang dihapus tidak dapat dikembalikan. Pastikan Anda benar-benar ingin menghapusnya.</p>
      <div id="confirm-delete-msg" class="dm-msg"></div>
    </div>
    <div class="dm-modal-footer">
      <button class="dm-btn dm-btn-outline" onclick="closeModal('modal-confirm-delete-file')">Batal</button>
      <button class="dm-btn dm-btn-danger" id="btn-confirm-delete-file">Ya, Hapus File</button>
    </div>
  </div>
</div>

<?php include __DIR__ . '/preview_modal.php'; ?>

<script>
(function(){
  const BASE = '<?= $base ?>';
  const FOLDER_ID = <?= $folderId ?? 'null' ?>;

  function openModal(id){ document.getElementById(id)?.classList.add('open'); }
  function closeModal(id){ document.getElementById(id)?.classList.remove('open'); }
  window.closeModal = closeModal;

  function setMsg(elId, html, ok) {
    const el = document.getElementById(elId); if (!el) return;
    const icon = ok
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
    el.innerHTML = '<span class="dm-msg-' + (ok ? 'ok' : 'err') + '">' + icon + ' ' + html + '</span>';
  }
  function escHtml(s){ const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
  function fmtSize(b){ if(b<1024) return b+'B'; if(b<1048576) return (b/1024).toFixed(1)+'KB'; return (b/1048576).toFixed(2)+'MB'; }

  /* ===== Overflow menu (kebab) untuk file card ===== */
  window.toggleFileMenu = function(evt, fileId) {
    evt.stopPropagation();
    const dd = document.getElementById('file-menu-' + fileId);
    const isOpen = dd.classList.contains('open');
    closeAllMenus();
    if (!isOpen) dd.classList.add('open');
  };
  window.closeAllMenus = function() {
    document.querySelectorAll('.dm-menu-dropdown.open').forEach(m => m.classList.remove('open'));
  };
  document.addEventListener('click', () => closeAllMenus());

  /* ===== Live search dengan debounce ===== */
  const searchForm = document.getElementById('dm-search-form');
  const searchInput = document.getElementById('dm-search-input');
  const searchSpinner = document.getElementById('dm-search-spinner');
  const typeSelect = document.getElementById('dm-type-select');
  let searchDebounceTimer = null;

  searchInput?.addEventListener('input', function() {
    clearTimeout(searchDebounceTimer);
    searchSpinner.classList.add('show');
    searchDebounceTimer = setTimeout(() => { searchForm.submit(); }, 400);
  });
  typeSelect?.addEventListener('change', function() { searchForm.submit(); });

  /* ===== Upload ===== */
  const dropzone = document.getElementById('upload-dropzone');
  const fileInput = document.getElementById('input-file-upload');
  const uploadBtn = document.getElementById('btn-do-upload');
  const fileList = document.getElementById('upload-file-list');
  const progressWrap = document.getElementById('upload-progress');
  const progressBar = document.getElementById('upload-progress-bar');
  const progressLabel = document.getElementById('upload-progress-label');
  let pendingFiles = [];

  function openUploadModal() {
    pendingFiles = [];
    fileList.innerHTML = '';
    document.getElementById('upload-msg').innerHTML = '';
    fileInput.value = '';
    uploadBtn.disabled = true;
    progressWrap.style.display = 'none';
    progressLabel.style.display = 'none';
    progressBar.style.width = '0%';
    openModal('modal-upload');
  }
  window.openUploadModal = openUploadModal;
  document.getElementById('btn-open-upload')?.addEventListener('click', openUploadModal);

  dropzone?.addEventListener('click', () => fileInput.click());
  ['dragenter','dragover'].forEach(ev => dropzone?.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.add('dragover'); }));
  ['dragleave','drop'].forEach(ev => dropzone?.addEventListener(ev, e => { e.preventDefault(); dropzone.classList.remove('dragover'); }));
  dropzone?.addEventListener('drop', e => addFiles(e.dataTransfer.files));
  fileInput?.addEventListener('change', () => addFiles(fileInput.files));

  function addFiles(list) {
    Array.from(list).forEach(f => pendingFiles.push(f));
    renderFileList();
    uploadBtn.disabled = pendingFiles.length === 0;
  }

  function renderFileList() {
    fileList.innerHTML = pendingFiles.map((f, i) =>
      '<div style="display:flex;align-items:center;gap:.6rem;padding:.7rem .8rem;background:#FBF8F4;border:1px solid #EEE2D5;border-radius:14px;margin-bottom:.45rem;font-size:12.5px">'
      + '<span style="flex:1;color:#1F1A17;font-weight:800;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + escHtml(f.name) + '</span>'
      + '<span style="color:#8C7E70;flex-shrink:0">' + fmtSize(f.size) + '</span>'
      + '<button onclick="removePending(' + i + ')" style="border:none;background:#F3E7DB;color:#8A7867;width:28px;height:28px;border-radius:9px;cursor:pointer">×</button>'
      + '</div>'
    ).join('');
  }
  window.removePending = function(i){ pendingFiles.splice(i,1); renderFileList(); uploadBtn.disabled = pendingFiles.length === 0; };

  uploadBtn?.addEventListener('click', async () => {
    if (!pendingFiles.length) return;
    uploadBtn.disabled = true;
    progressWrap.style.display = 'block';
    progressLabel.style.display = 'block';
    let done = 0;
    const total = pendingFiles.length;
    for (const file of pendingFiles) {
      progressLabel.textContent = 'Mengupload ' + file.name + ' (' + (done+1) + '/' + total + ')...';
      const fd = new FormData();
      fd.append('file', file);
      if (FOLDER_ID) fd.append('folder_id', FOLDER_ID);
      try {
        const data = await (await fetch(BASE + '/api/dokumen/upload', { method:'POST', body:fd })).json();
        if (data.success && data.file) appendFileRow(data.file);
      } catch(e) {}
      done++;
      progressBar.style.width = Math.round(done / total * 100) + '%';
    }
    progressLabel.textContent = 'Upload selesai.';
    setMsg('upload-msg', 'Upload selesai. ' + total + ' file ditambahkan.', true);
    setTimeout(() => { closeModal('modal-upload'); }, 1000);
  });

  function appendFileRow(f) {
    const tbody = document.getElementById('file-tbody');
    if (!tbody) { location.reload(); return; }
    const card = document.createElement('article');
    card.className = 'dm-file-card';
    card.id = 'file-row-' + f.id;
    const name = f.original_name.replace(/'/g, "\\'");
    card.innerHTML =
      '<div class="dm-file-icon" style="background:'+escHtml(f.mime_color)+'">'+escHtml(f.mime_label)+'</div>'
      + '<div class="dm-file-body">'
      + '  <div class="dm-file-top"><div style="min-width:0;flex:1"><div class="dm-file-name" onclick="openPreview('+f.id+')">'+escHtml(f.original_name)+'</div><div class="dm-file-tags"><span class="dm-chip dm-chip-soft">'+escHtml(f.mime_label)+'</span><span class="dm-chip dm-chip-owner">Baru diupload</span></div></div></div>'
      + '  <div class="dm-file-meta">'
      + '    <div class="dm-meta-item"><div class="dm-meta-label">Ukuran</div><div class="dm-meta-value">'+escHtml(f.size_fmt)+'</div></div>'
      + '    <div class="dm-meta-item"><div class="dm-meta-label">Tanggal</div><div class="dm-meta-value">'+new Date().toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})+'</div></div>'
      + '    <div class="dm-meta-item"><div class="dm-meta-label">Uploader</div><div class="dm-meta-value">-</div></div>'
      + '    <div class="dm-meta-item"><div class="dm-meta-label">Akses</div><div class="dm-meta-value">Kelola & Bagikan</div></div>'
      + '  </div>'
      + '  <div class="dm-file-actions">'
      + '    <button class="dm-btn dm-btn-outline dm-btn-sm" type="button" onclick="openPreview('+f.id+')">Preview</button>'
      + '    <a href="'+BASE+'/dokumen/'+f.id+'/download" class="dm-btn dm-btn-ghost dm-btn-sm">Download</a>'
      + '    <div class="dm-menu-wrap">'
      + '      <button type="button" class="dm-menu-btn" onclick="toggleFileMenu(event,'+f.id+')" title="Aksi lainnya"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="1"></circle><circle cx="12" cy="12" r="1"></circle><circle cx="12" cy="19" r="1"></circle></svg></button>'
      + '      <div class="dm-menu-dropdown" id="file-menu-'+f.id+'">'
      + '        <button type="button" class="dm-menu-item" onclick="closeAllMenus();openPublicLinkModal('+f.id+',\''+name+'\')">Link Publik</button>'
      + '        <button type="button" class="dm-menu-item" onclick="closeAllMenus();openShareModal('+f.id+',\''+name+'\')">Bagikan</button>'
      + '        <button type="button" class="dm-menu-item danger" onclick="closeAllMenus();confirmDeleteFile('+f.id+',\''+name+'\')">Hapus</button>'
      + '      </div>'
      + '    </div>'
      + '  </div>'
      + '</div>';
    tbody.prepend(card);
  }

  /* ===== Folder ===== */
  let folderMode = 'create';
  document.querySelectorAll('#btn-open-folder, #btn-open-folder-empty').forEach(btn => btn.addEventListener('click', () => {
    folderMode = 'create';
    document.getElementById('folder-modal-title').textContent = 'Buat Folder Baru';
    document.getElementById('input-folder-name').value = '';
    document.getElementById('folder-action-id').value = '';
    document.getElementById('folder-msg').innerHTML = '';
    openModal('modal-folder');
  }));

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
    if (!name) { setMsg('folder-msg', 'Nama tidak boleh kosong.', false); return; }
    const btn = document.getElementById('btn-do-folder');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    const fd = new FormData();
    fd.append('name', name);
    let url;
    if (folderMode === 'create') {
      url = BASE + '/api/dokumen/folder/create';
      if (FOLDER_ID) fd.append('parent_id', FOLDER_ID);
    } else {
      url = BASE + '/api/dokumen/folder/' + document.getElementById('folder-action-id').value + '/rename';
    }
    try {
      const data = await (await fetch(url, { method:'POST', body:fd })).json();
      if (data.success) { setMsg('folder-msg', data.message, true); setTimeout(() => location.reload(), 900); }
      else { setMsg('folder-msg', data.message, false); btn.disabled = false; btn.textContent = 'Simpan'; }
    } catch(e) { setMsg('folder-msg', 'Gagal koneksi.', false); btn.disabled = false; btn.textContent = 'Simpan'; }
  };

  /* ===== Modal konfirmasi hapus file (custom) ===== */
  let deleteFileId = null;
  window.confirmDeleteFile = function(id, name) {
    deleteFileId = id;
    document.getElementById('confirm-delete-filename').textContent = name;
    document.getElementById('confirm-delete-msg').innerHTML = '';
    openModal('modal-confirm-delete-file');
  };
  document.getElementById('btn-confirm-delete-file')?.addEventListener('click', function() {
    if (!deleteFileId) return;
    const btn = this;
    btn.disabled = true; btn.textContent = 'Menghapus...';
    fetch(BASE + '/api/dokumen/' + deleteFileId + '/delete', { method:'POST' })
      .then(r => r.json())
      .then(data => {
        btn.disabled = false; btn.textContent = 'Ya, Hapus File';
        if (data.success) {
          document.getElementById('file-row-' + deleteFileId)?.remove();
          closeModal('modal-confirm-delete-file');
          setMsg('page-msg', 'File berhasil dihapus.', true);
        } else {
          setMsg('confirm-delete-msg', data.message || 'Gagal menghapus file.', false);
        }
      })
      .catch(() => {
        btn.disabled = false; btn.textContent = 'Ya, Hapus File';
        setMsg('confirm-delete-msg', 'Gagal koneksi.', false);
      });
  });

  /* ===== Share ===== */
  let shareFileId = null;
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

  document.getElementById('share-user-search')?.addEventListener('input', function() {
    clearTimeout(shareDebounce);
    const q = this.value.trim();
    if (q.length < 1) { document.getElementById('share-user-dropdown').classList.remove('open'); return; }
    shareDebounce = setTimeout(() => searchUsers(q), 280);
  });

  async function searchUsers(q) {
    try {
      const data = await (await fetch(BASE + '/api/users?q=' + encodeURIComponent(q))).json();
      const dd = document.getElementById('share-user-dropdown');
      if (!data.users || !data.users.length) {
        dd.innerHTML = '<div class="dm-user-option" style="color:#9A8D7F;cursor:default">Tidak ada user ditemukan</div>';
        dd.classList.add('open');
        return;
      }
      dd.innerHTML = data.users.map(u =>
        '<div class="dm-user-option" data-id="'+u.id+'" data-name="'+escHtml(u.name)+'" onclick="selectUser(this)">'
        + '<strong>'+escHtml(u.name)+'</strong>'
        + '<small>@'+escHtml(u.username)+' · '+escHtml(u.role)+'</small>'
        + '</div>'
      ).join('');
      dd.classList.add('open');
    } catch(e) {}
  }

  window.selectUser = function(el) {
    document.getElementById('share-selected-user-id').value = el.dataset.id;
    document.getElementById('share-selected-user-name').textContent = '✓ ' + el.dataset.name;
    document.getElementById('share-user-search').value = el.dataset.name;
    document.getElementById('share-user-dropdown').classList.remove('open');
  };

  window.submitShare = async function() {
    const userId = document.getElementById('share-selected-user-id').value;
    const perm = document.getElementById('share-permission').value;
    if (!userId) { setMsg('share-msg', 'Pilih user terlebih dahulu.', false); return; }
    const btn = document.getElementById('btn-do-share');
    btn.disabled = true; btn.textContent = 'Membagikan...';
    const fd = new FormData();
    fd.append('user_id', userId);
    fd.append('permission', perm);
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + shareFileId + '/shares', { method:'POST', body:fd })).json();
      if (data.success) {
        setMsg('share-msg', data.message, true);
        renderShareList(data.shares || []);
        document.getElementById('share-user-search').value = '';
        document.getElementById('share-selected-user-id').value = '';
        document.getElementById('share-selected-user-name').textContent = '';
      } else {
        setMsg('share-msg', data.message, false);
      }
    } catch(e) { setMsg('share-msg', 'Gagal koneksi.', false); }
    btn.disabled = false; btn.textContent = 'Bagikan';
  };

  async function loadShareList(fileId) {
    document.getElementById('share-list').innerHTML = '<div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0"><div class="spinner-border spinner-border-sm"></div></div>';
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/shares')).json();
      renderShareList(data.shares || []);
    } catch(e) {
      document.getElementById('share-list').innerHTML = '<div style="color:#C05621;font-size:13px">Gagal memuat data.</div>';
    }
  }

  function renderShareList(shares) {
    const el = document.getElementById('share-list');
    if (!shares.length) {
      el.innerHTML = '<div style="text-align:center;color:#9A8D7F;font-size:13px;padding:.6rem">Belum dibagikan ke siapa pun.</div>';
      return;
    }
    el.innerHTML = shares.map(s =>
      '<div class="dm-share-item" id="share-item-'+s.shared_to+'">'
      + '<div class="dm-share-avatar">'+escHtml((s.user_name || '?').charAt(0).toUpperCase())+'</div>'
      + '<div class="dm-share-info">'
      + '  <div class="dm-share-name">'+escHtml(s.user_name)+'</div>'
      + '  <div class="dm-share-role">@'+escHtml(s.username)+' · '+escHtml(s.role)+'</div>'
      + '</div>'
      + '<select class="dm-share-perm-select" onchange="updatePerm('+shareFileId+','+s.shared_to+',this.value)">'
      + '  <option value="view"'+(s.permission==='view'?' selected':'')+'>View Only</option>'
      + '  <option value="download"'+(s.permission==='download'?' selected':'')+'>Download</option>'
      + '</select>'
      + '<button class="dm-share-revoke" title="Cabut akses" onclick="revokeShare('+shareFileId+','+s.shared_to+')">'
      + '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
      + '</button>'
      + '</div>'
    ).join('');
  }

  window.updatePerm = async function(fileId, userId, perm) {
    const fd = new FormData(); fd.append('permission', perm);
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/shares/' + userId + '/permission', { method:'POST', body:fd })).json();
      if (!data.success) alert(data.message || 'Gagal memperbarui izin.');
      else renderShareList(data.shares || []);
    } catch(e) { alert('Gagal koneksi.'); }
  };

  window.revokeShare = async function(fileId, userId) {
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/shares/' + userId + '/delete', { method:'POST' })).json();
      if (data.success) renderShareList(data.shares || []);
      else alert(data.message || 'Gagal mencabut akses.');
    } catch(e) { alert('Gagal koneksi.'); }
  };

  /* ===== Public Link ===== */
  let pubFileId = null;
  window.openPublicLinkModal = function(fileId, fileName) {
    pubFileId = fileId;
    document.getElementById('pub-modal-filename').textContent = fileName;
    document.getElementById('pub-link-msg').innerHTML = '';
    document.getElementById('pub-permission').value = 'view';
    document.getElementById('pub-expires-at').value = '';
    document.getElementById('pub-password').value = '';
    document.getElementById('pub-max-dl').value = '';
    loadPublicLinks(fileId);
    openModal('modal-public-link');
  };

  async function loadPublicLinks(fileId) {
    const list = document.getElementById('pub-link-list');
    list.innerHTML = '<div style="text-align:center;color:#A89E90;font-size:13px;padding:.75rem 0"><div class="spinner-border spinner-border-sm"></div></div>';
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/public-links')).json();
      renderPublicLinks(data.links || []);
    } catch(e) {
      list.innerHTML = '<div style="color:#C05621;font-size:13px">Gagal memuat link.</div>';
    }
  }

  function renderPublicLinks(links) {
    const list = document.getElementById('pub-link-list');
    if (!links.length) {
      list.innerHTML = '<div style="text-align:center;color:#9A8D7F;font-size:13px;padding:.6rem">Belum ada link publik.</div>';
      return;
    }
    list.innerHTML = links.map(lk => {
      const isExpired = !lk.is_valid;
      const expLabel = lk.expires_at ? (isExpired ? 'Kadaluarsa' : 'Exp: ' + new Date(lk.expires_at).toLocaleDateString('id-ID')) : 'Tanpa batas';
      const dlInfo = lk.max_downloads ? (lk.download_count + '/' + lk.max_downloads + ' download') : (lk.download_count + ' download');
      return '<div class="dm-pub-link-item">'
        + '<div class="dm-pub-link-url">'
        + '<input type="text" readonly value="' + escHtml(lk.url) + '" id="pub-url-' + lk.id + '">'
        + '<button class="dm-pub-link-copy" id="pub-copy-' + lk.id + '" onclick="copyPubLink(' + lk.id + ')">Salin</button>'
        + '</div>'
        + '<div class="dm-pub-link-meta">'
        + '<span class="dm-pub-link-badge">' + (lk.permission === 'download' ? 'Download' : 'View') + '</span>'
        + (lk.has_password ? '<span class="dm-pub-link-badge locked">Password</span>' : '')
        + '<span class="dm-pub-link-badge' + (isExpired ? ' expired' : '') + '">' + escHtml(expLabel) + '</span>'
        + '<span style="color:#8D8072">' + escHtml(dlInfo) + '</span>'
        + '<button class="dm-pub-link-del" onclick="deletePublicLink(' + lk.id + ')">Hapus</button>'
        + '</div></div>';
    }).join('');
  }

  window.copyPubLink = function(linkId) {
    const input = document.getElementById('pub-url-' + linkId);
    const btn = document.getElementById('pub-copy-' + linkId);
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(() => {
      btn.textContent = 'Tersalin';
      btn.classList.add('copied');
      setTimeout(() => { btn.textContent = 'Salin'; btn.classList.remove('copied'); }, 1800);
    }).catch(() => {
      input.select();
      document.execCommand('copy');
      btn.textContent = 'Tersalin';
      setTimeout(() => { btn.textContent = 'Salin'; }, 1800);
    });
  };

  window.createPublicLink = async function() {
    const btn = document.getElementById('btn-create-pub-link');
    btn.disabled = true; btn.textContent = 'Membuat...';
    const fd = new FormData();
    fd.append('permission', document.getElementById('pub-permission').value);
    fd.append('expires_at', document.getElementById('pub-expires-at').value);
    fd.append('password', document.getElementById('pub-password').value);
    fd.append('max_downloads', document.getElementById('pub-max-dl').value);
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + pubFileId + '/public-links', { method:'POST', body:fd })).json();
      if (data.success) {
        setMsg('pub-link-msg', data.message, true);
        document.getElementById('pub-expires-at').value = '';
        document.getElementById('pub-password').value = '';
        document.getElementById('pub-max-dl').value = '';
        loadPublicLinks(pubFileId);
      } else {
        setMsg('pub-link-msg', data.message, false);
      }
    } catch(e) { setMsg('pub-link-msg', 'Gagal koneksi.', false); }
    btn.disabled = false;
    btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>Buat Link';
  };

  window.deletePublicLink = async function(linkId) {
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + pubFileId + '/public-links/' + linkId + '/delete', { method:'POST' })).json();
      if (data.success) {
        setMsg('pub-link-msg', 'Link dihapus.', true);
        renderPublicLinks(data.links || []);
      } else {
        setMsg('pub-link-msg', data.message, false);
      }
    } catch(e) { setMsg('pub-link-msg', 'Gagal koneksi.', false); }
  };

  document.querySelectorAll('.dm-modal-overlay').forEach(ov => {
    ov.addEventListener('click', e => { if (e.target === ov) closeModal(ov.id); });
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.dm-modal-overlay.open').forEach(m => m.classList.remove('open'));
  });
  document.addEventListener('click', e => {
    const dd = document.getElementById('share-user-dropdown');
    if (dd && !dd.contains(e.target) && e.target.id !== 'share-user-search') dd.classList.remove('open');
  });
})();
</script>
<script src="<?= $base ?>/app/views/dokumen/_grid_list_toggle.js"></script>

<div class="dm-modal-overlay" id="modal-folder-share">
  <div class="dm-modal" style="max-width:560px">
    <div class="dm-modal-header">
      <div><div class="dm-modal-title">Bagikan Folder</div><div id="shareFolderName" style="font-size:12px;color:#9A8D7F;margin-top:.2rem"></div></div>
      <button class="dm-modal-close" onclick="closeFolderShareModal()">&times;</button>
    </div>
    <div class="dm-modal-body">
      <div class="dm-user-search-wrap"><input type="text" class="dm-ctrl" id="shareFolderUserSearch" placeholder="Cari user..."><div class="dm-user-dropdown" id="shareFolderUserSuggestions"></div></div>
      <div style="display:flex;gap:.6rem;margin-top:.8rem;align-items:center"><select class="dm-ctrl" id="shareFolderPermission" style="width:160px"><option value="view">View Only</option><option value="edit">Edit</option></select><button class="dm-btn dm-btn-share" id="btnAddFolderShare" type="button">Bagikan</button></div>
      <div id="shareFolderMsg" class="dm-msg"></div>
      <div style="margin-top:1rem;font-weight:800;font-size:11px;color:#9A8D7F;text-transform:uppercase">Daftar Akses</div><div id="shareFolderList" class="dm-share-list"></div>
    </div>
    <div class="dm-modal-footer"><button class="dm-btn dm-btn-outline" onclick="closeFolderShareModal()">Tutup</button></div>
  </div>
</div>
<div class="dm-modal-overlay" id="modal-folder-delete">
  <div class="dm-modal" style="max-width:460px">
    <div class="dm-modal-header">
      <div><div class="dm-modal-title">Hapus Folder</div><div id="deleteFolderName" style="font-size:12px;color:#9A8D7F;margin-top:.2rem"></div></div>
      <button class="dm-modal-close" onclick="closeFolderDeleteModal()">&times;</button>
    </div>
    <div class="dm-modal-body"><p style="margin:0;color:#5A5047;font-size:13px;line-height:1.6">Tindakan ini akan menghapus folder beserta isinya. Aksi ini tidak dapat dibatalkan.</p><div id="deleteFolderMsg" class="dm-msg"></div></div>
    <div class="dm-modal-footer"><button class="dm-btn dm-btn-outline" onclick="closeFolderDeleteModal()">Batal</button><button class="dm-btn dm-btn-danger" id="btnConfirmDeleteFolder" type="button">Ya, Hapus</button></div>
  </div>
</div>
<script>window._BASE_URL = '<?= $base ?>';</script>
<script src="<?= $base ?>/app/views/dokumen/_folder_share.js"></script>
