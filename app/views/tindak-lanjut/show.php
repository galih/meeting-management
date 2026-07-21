<?php
$baseUrl     = rtrim(BASE_URL, '/');
$csrfToken   = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES);
$statusMap   = [
    'pending'     => ['label' => 'Menunggu',    'color' => 'secondary', 'bg' => '#f0f0f0',  'text' => '#555'],
    'in_progress' => ['label' => 'Berlangsung', 'color' => 'blue',      'bg' => '#e0f4ff',  'text' => '#0284c7'],
    'done'        => ['label' => 'Selesai',     'color' => 'green',     'bg' => '#e6faf0',  'text' => '#16a34a'],
    'cancelled'   => ['label' => 'Dibatalkan',  'color' => 'red',       'bg' => '#fff0f0',  'text' => '#dc2626'],
];
$priorityMap = [
    'low'    => ['label' => 'Rendah', 'color' => 'green',  'bg' => '#e6faf0', 'text' => '#16a34a'],
    'medium' => ['label' => 'Sedang', 'color' => 'orange', 'bg' => '#fffbea', 'text' => '#b45309'],
    'high'   => ['label' => 'Tinggi', 'color' => 'red',    'bg' => '#fff0f0', 'text' => '#dc2626'],
];
$st          = $statusMap[$tl['status']]     ?? ['label' => $tl['status'],   'color' => 'secondary', 'bg' => '#f0f0f0', 'text' => '#555'];
$pr          = $priorityMap[$tl['priority']] ?? ['label' => $tl['priority'], 'color' => 'secondary', 'bg' => '#f0f0f0', 'text' => '#555'];
$isAdminLike = Auth::hasRole('admin', 'sekretaris');
$isOverdue   = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'], ['done', 'cancelled']);
?>

<style>
/* -- Hero -------------------------------------------------------- */
.tl-hero           { background:linear-gradient(135deg,#7B1C1C 0%,#9B2020 60%,#A83218 100%);border-radius:14px;box-shadow:0 4px 20px rgba(123,28,28,.22);overflow:hidden;position:relative; }
.tl-hero-inner     { padding:1.4rem 1.6rem 1rem; }
.tl-breadcrumb     { display:flex;align-items:center;gap:.5rem;font-size:12px;color:rgba(255,255,255,.65);margin-bottom:.5rem; }
.tl-breadcrumb a   { color:rgba(255,255,255,.8);text-decoration:none;transition:color .15s; }
.tl-breadcrumb a:hover { color:#fff; }
.tl-breadcrumb-sep { color:rgba(255,255,255,.4); }
.tl-hero-title     { font-size:clamp(15px,2.5vw,22px);font-weight:800;color:#fff;margin:0;line-height:1.3; }

/* -- Badges ------------------------------------------------------ */
.tl-badge          { display:inline-block;padding:.22em .65em;border-radius:6px;font-size:12px;font-weight:700; }
.tl-badge-secondary { background:#f0f0f0;color:#555; }
.tl-badge-blue     { background:#e0f4ff;color:#0284c7; }
.tl-badge-green    { background:#e6faf0;color:#16a34a; }
.tl-badge-red      { background:#fff0f0;color:#dc2626; }
.tl-badge-orange   { background:#fffbea;color:#b45309; }
.tl-badge-outline  { background:transparent;border:1.5px solid currentColor; }

/* -- Buttons ----------------------------------------------------- */
.tl-btn-ghost   { background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;font-size:13px;font-weight:600;padding:.45rem .9rem;transition:background .15s;text-decoration:none;display:inline-flex;align-items:center;gap:6px; }
.tl-btn-ghost:hover { background:rgba(255,255,255,.25);color:#fff; }
.tl-btn-danger  { background:#a82515;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;padding:.45rem .9rem;cursor:pointer;transition:opacity .15s;display:inline-flex;align-items:center;gap:6px; }
.tl-btn-danger:hover { opacity:.85; }
.tl-btn-danger:disabled { opacity:.5;cursor:not-allowed; }
.tl-btn-send    { background:#7B1C1C;color:#fff;border:none;border-radius:8px;padding:.5rem 1rem;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;display:inline-flex;align-items:center;gap:5px; }
.tl-btn-send:hover { opacity:.85; }
.tl-btn-send:disabled { opacity:.5;cursor:not-allowed; }

/* -- Cards ------------------------------------------------------- */
.tl-card            { border:1px solid #eee;border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.05); }
.tl-card-header     { padding:.65rem 1rem;background:#faf4eb;font-weight:700;font-size:13px;color:#7B1C1C;display:flex;align-items:center;gap:8px; }
.tl-card-body       { padding:.9rem 1rem; }
.tl-count-badge     { display:inline-flex;align-items:center;justify-content:center;min-width:20px;height:20px;background:#7B1C1C;color:#fff;border-radius:10px;font-size:11px;font-weight:700;padding:0 5px; }

/* -- Info List --------------------------------------------------- */
.tl-info-list  { }
.tl-info-row   { padding:.55rem 1rem;border-bottom:1px solid #f0f0f0;display:flex;align-items:center;gap:.75rem; }
.tl-info-row:last-child { border-bottom:none; }
.tl-info-label { min-width:90px;font-size:11px;font-weight:700;text-transform:uppercase;color:#999;letter-spacing:.04em;flex-shrink:0; }
.tl-info-val   { font-size:13px;color:#333; }
.tl-info-link  { font-size:13px;color:#7B1C1C;text-decoration:none;font-weight:600; }
.tl-info-link:hover { text-decoration:underline; }
.tl-text-danger { color:#dc2626;font-weight:700; }

/* -- Notes ------------------------------------------------------- */
.tl-notes-list      { max-height:480px;overflow-y:auto; }
.tl-note-item       { display:flex;gap:.75rem;padding:.9rem 1rem;border-bottom:1px solid #f5f5f5;position:relative; }
.tl-note-item:last-child { border-bottom:none; }
.tl-note-avatar     { width:34px;height:34px;border-radius:50%;background:#7B1C1C;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;flex-shrink:0; }
.tl-note-body       { flex:1;min-width:0; }
.tl-note-meta       { display:flex;align-items:center;gap:.5rem;margin-bottom:.25rem;flex-wrap:wrap; }
.tl-note-author     { font-size:13px;font-weight:700;color:#333; }
.tl-note-time       { font-size:11px;color:#aaa; }
.tl-note-text       { font-size:13px;color:#444;white-space:pre-wrap;line-height:1.6; }
.tl-note-delete     { position:absolute;top:.7rem;right:.7rem;display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:#fff0f0;color:#dc2626;border:1px solid #ffd0d0;border-radius:6px;cursor:pointer;opacity:0;transition:opacity .15s; }
.tl-note-item:hover .tl-note-delete { opacity:1; }
.tl-note-input-area { padding:.75rem 1rem;border-top:1px solid #eee;background:#fafafa; }
.tl-note-input-wrap { display:flex;gap:8px;align-items:flex-start; }
.tl-note-textarea   { flex:1;border:1px solid #ddd;border-radius:8px;padding:.5rem .75rem;font-size:13px;resize:none;transition:border .15s; }
.tl-note-textarea:focus { outline:none;border-color:#7B1C1C; }
.tl-empty           { padding:2.5rem;text-align:center;color:#bbb; }

/* -- Status Buttons ---------------------------------------------- */
.tl-status-btn          { width:100%;padding:.5rem .75rem;border-radius:8px;border:1.5px solid #e0e0e0;background:#fff;font-size:13px;font-weight:600;color:#555;cursor:pointer;text-align:left;display:flex;align-items:center;gap:8px;transition:all .15s; }
.tl-status-btn:hover    { border-color:#7B1C1C;color:#7B1C1C;background:#fdf7f0; }
.tl-status-btn-active   { border-color:currentColor !important;background:var(--tl-st-bg) !important;color:var(--tl-st-text) !important; }
.tl-status-dot          { width:8px;height:8px;border-radius:50%;background:currentColor;flex-shrink:0; }

/* -- Nav Links --------------------------------------------------- */
.tl-nav-links  { padding:.5rem 0; }
.tl-nav-link   { display:flex;align-items:center;gap:8px;padding:.55rem 1rem;font-size:13px;color:#555;text-decoration:none;transition:background .15s; }
.tl-nav-link:hover { background:#fdf7f0;color:#7B1C1C; }

/* -- Lampiran ---------------------------------------------------- */
.tl-attach-head      { padding:.65rem 1rem;background:#faf4eb;font-weight:700;font-size:13px;color:#7B1C1C;display:flex;align-items:center;gap:8px; }
.tl-attach-btn-add   { margin-left:auto;background:#7B1C1C;color:#fff;border:none;border-radius:6px;font-size:11px;font-weight:700;padding:.22rem .55rem;display:inline-flex;align-items:center;gap:4px;cursor:pointer;transition:opacity .15s; }
.tl-attach-btn-add:hover { opacity:.85; }
.tl-attach-upload    { padding:.75rem 1rem;border-bottom:1px solid #f0f0f0;background:#fffdf8; }
.tl-attach-form-label{ font-size:12px;font-weight:700;color:#333;display:block;margin-bottom:.2rem; }
.tl-attach-form-hint { font-size:11px;color:#aaa;margin-top:.2rem; }
.tl-attach-btn-upload{ background:linear-gradient(135deg,#7B1C1C,#9B2020);border:none;color:#fff;font-size:12px;font-weight:700;border-radius:7px;padding:.38rem .75rem;display:inline-flex;align-items:center;gap:.28rem;cursor:pointer;transition:filter .15s; }
.tl-attach-btn-upload:hover { filter:brightness(1.1); }
.tl-attach-btn-cancel{ background:transparent;border:1.5px solid #ddd;color:#888;font-size:12px;border-radius:7px;padding:.38rem .75rem;cursor:pointer;transition:all .15s; }
.tl-attach-btn-cancel:hover { border-color:#888; }
.tl-attach-list      { max-height:260px;overflow-y:auto; }
.tl-attach-item      { display:flex;align-items:center;gap:.6rem;padding:.55rem 1rem;border-bottom:1px solid #f5f5f5;font-size:12.5px; }
.tl-attach-item:last-child { border-bottom:none; }
.tl-attach-icon      { width:28px;height:28px;border-radius:6px;background:#f0e8e8;color:#7B1C1C;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.tl-attach-name      { flex:1;min-width:0;font-weight:600;color:#333;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
.tl-attach-meta      { font-size:11px;color:#aaa; }
.tl-attach-del       { background:transparent;border:none;color:#dc2626;width:22px;height:22px;border-radius:5px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;padding:0;flex-shrink:0;opacity:0; }
.tl-attach-item:hover .tl-attach-del { opacity:1; }
.tl-attach-del:hover { background:#fff0f0; }
.tl-attach-empty     { padding:1.5rem;text-align:center;font-size:12.5px;color:#bbb; }
.tl-attach-loading   { padding:.9rem;text-align:center;font-size:12.5px;color:#888; }
</style>

<!-- BUG FIX #5: CSRF disimpan sebagai variabel JS inline (bukan <meta> di body) -->
<script>var _CSRF_TOKEN_SHOW = '<?= $csrfToken ?>';</script>

<!-- Hero -->
<div class="tl-hero mb-4">
  <div class="tl-hero-inner">
    <nav class="tl-breadcrumb">
      <a href="<?= $baseUrl ?>/tindak-lanjut">Tindak Lanjut</a>
      <span class="tl-breadcrumb-sep">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </span>
      <span>Detail</span>
    </nav>
    <div class="d-flex flex-wrap align-items-flex-start justify-content-between gap-3 mt-2">
      <div style="max-width:600px">
        <h1 class="tl-hero-title"><?= htmlspecialchars($tl['description']) ?></h1>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
          <span class="tl-badge tl-badge-<?= $st['color'] ?>"><?= $st['label'] ?></span>
          <span class="tl-badge tl-badge-<?= $pr['color'] ?> tl-badge-outline"><?= $pr['label'] ?></span>
          <?php if ($isOverdue): ?>
          <span class="tl-badge tl-badge-red">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Terlambat
          </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tl-btn-ghost">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Lihat Meeting
        </a>
        <?php if ($isAdminLike): ?>
        <button class="tl-btn-danger" id="btn-delete-tl">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Kolom kiri: Notes -->
  <div class="col-lg-8">
    <div class="tl-card">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Progress Notes
        <span class="tl-count-badge" id="note-count-badge"><?= count($notes) ?></span>
      </div>
      <div id="notes-list" class="tl-notes-list">
        <?php if (empty($notes)): ?>
        <div class="tl-empty" id="notes-empty"><p>Belum ada catatan progress</p></div>
        <?php else: ?>
        <?php foreach ($notes as $note): ?>
        <div class="tl-note-item" data-note-id="<?= (int)$note['id'] ?>">
          <div class="tl-note-avatar"><?= strtoupper(mb_substr($note['author_name'], 0, 1)) ?></div>
          <div class="tl-note-body">
            <div class="tl-note-meta">
              <span class="tl-note-author"><?= htmlspecialchars($note['author_name']) ?></span>
              <span class="tl-note-time"><?= htmlspecialchars($note['created_at_human'] ?? date('d M Y · H:i', strtotime($note['created_at']))) ?></span>
            </div>
            <div class="tl-note-text"><?= nl2br(htmlspecialchars($note['note'])) ?></div>
          </div>
          <?php if ($note['can_delete']): ?>
          <button class="tl-note-delete btn-delete-note" data-id="<?= (int)$note['id'] ?>" title="Hapus catatan">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <?php if ($canEdit): ?>
      <div class="tl-note-input-area">
        <div class="tl-note-input-wrap">
          <textarea id="note-input" class="tl-note-textarea" rows="2"
            placeholder="Tulis catatan progress… (Ctrl+Enter untuk kirim)"></textarea>
          <button id="note-submit" class="tl-btn-send">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim
          </button>
        </div>
        <p style="font-size:11px;color:#aaa;margin:.4rem 0 0">Gunakan @nama untuk mention pengguna</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Kolom kanan: Info + Lampiran + Status + Nav -->
  <div class="col-lg-4">
    <!-- Info -->
    <div class="tl-card mb-3">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        Info Tindak Lanjut
      </div>
      <div class="tl-info-list">
        <div class="tl-info-row">
          <span class="tl-info-label">Meeting</span>
          <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tl-info-link"><?= htmlspecialchars($tl['meeting_title']) ?></a>
        </div>
        <div class="tl-info-row">
          <span class="tl-info-label">PIC</span>
          <?php if (!empty($tl['assignee_name'])): ?>
          <div class="d-flex align-items-center gap-2">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#7B1C1C;color:#fff;font-size:10px;font-weight:700;">
              <?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?>
            </span>
            <span class="tl-info-val"><?= htmlspecialchars($tl['assignee_name']) ?></span>
          </div>
          <?php else: ?>
          <span class="tl-info-val" style="color:#bbb">—</span>
          <?php endif; ?>
        </div>
        <div class="tl-info-row">
          <span class="tl-info-label">Deadline</span>
          <span class="tl-info-val <?= $isOverdue ? 'tl-text-danger' : '' ?>">
            <?php if (!empty($tl['due_date'])): ?>
            <?php if ($isOverdue): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?php endif; ?>
            <?= date('d M Y', strtotime($tl['due_date'])) ?>
            <?php else: ?>—<?php endif; ?>
          </span>
        </div>
        <div class="tl-info-row">
          <span class="tl-info-label">Status</span>
          <span class="tl-badge tl-badge-<?= $st['color'] ?>" id="show-status-badge"><?= $st['label'] ?></span>
        </div>
        <div class="tl-info-row">
          <span class="tl-info-label">Prioritas</span>
          <span class="tl-badge tl-badge-<?= $pr['color'] ?>"><?= $pr['label'] ?></span>
        </div>
        <?php if (!empty($tl['creator_name'])): ?>
        <div class="tl-info-row">
          <span class="tl-info-label">Dibuat oleh</span>
          <span class="tl-info-val"><?= htmlspecialchars($tl['creator_name']) ?></span>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ============================================================
         LAMPIRAN PROGRESS
    ============================================================ -->
    <div class="tl-card mb-3" id="tl-attachment-panel" data-tl-id="<?= (int)$tl['id'] ?>">
      <div class="tl-attach-head">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        Lampiran Progress
        <span class="tl-count-badge" id="tl-attach-count">0</span>
        <?php if ($canEdit): ?>
        <button class="tl-attach-btn-add" id="btn-tl-show-upload">
          <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload
        </button>
        <?php endif; ?>
      </div>

      <?php if ($canEdit): ?>
      <div id="tl-upload-form-wrapper" style="display:none;" class="tl-attach-upload">
        <form id="form-tl-upload" enctype="multipart/form-data">
          <div class="mb-2">
            <label class="tl-attach-form-label">Pilih File <span style="color:#dc2626;">*</span></label>
            <input type="file" id="tl-attach-file" class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
            <div class="tl-attach-form-hint">PDF, Office, Gambar, ZIP &middot; maks. 10 MB</div>
          </div>
          <div class="mb-2">
            <label class="tl-attach-form-label">Kategori</label>
            <select id="tl-attach-category" class="form-select form-select-sm">
              <option value="dokumen">Dokumen</option>
              <option value="presentasi">Presentasi</option>
              <option value="gambar">Gambar</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="tl-attach-btn-upload flex-fill" id="btn-tl-do-upload">
              <span id="tl-upload-spinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
              Upload
            </button>
            <button type="button" class="tl-attach-btn-cancel" id="btn-tl-cancel-upload">Batal</button>
          </div>
          <div id="tl-upload-alert" class="d-none mt-2"></div>
        </form>
      </div>
      <?php endif; ?>

      <div id="tl-attachment-list" class="tl-attach-list">
        <div class="tl-attach-loading">
          <span class="spinner-border spinner-border-sm"></span> Memuat…
        </div>
      </div>
    </div>
    <!-- END LAMPIRAN -->

    <!-- Ubah Status -->
    <?php if ($canEdit): ?>
    <div class="tl-card mb-3">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/></svg>
        Ubah Status
      </div>
      <div class="tl-card-body">
        <div class="d-grid gap-2">
          <?php foreach ($statusMap as $val => $info): ?>
          <button
            class="tl-status-btn <?= $tl['status'] === $val ? 'tl-status-btn-active' : '' ?>"
            data-status="<?= $val ?>"
            style="--tl-st-bg:<?= $info['bg'] ?>;--tl-st-text:<?= $info['text'] ?>;<?= $tl['status'] === $val ? 'color:'.$info['text'].';background:'.$info['bg'].';border-color:'.$info['text'] : '' ?>">
            <span class="tl-status-dot" style="background:<?= $info['text'] ?>"></span>
            <?= $info['label'] ?>
            <?php if ($tl['status'] === $val): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ms-auto"><polyline points="20 6 9 17 4 12"/></svg>
            <?php endif; ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Navigasi -->
    <div class="tl-card">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        Navigasi
      </div>
      <div class="tl-nav-links">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tl-nav-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Kembali ke Meeting
        </a>
        <a href="<?= $baseUrl ?>/tindak-lanjut" class="tl-nav-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Semua Tindak Lanjut
        </a>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var TL_ID      = <?= (int)$tl['id'] ?>;
  var BASE_URL   = <?= json_encode($baseUrl) ?>;
  var CSRF_TOKEN = (typeof _CSRF_TOKEN_SHOW !== 'undefined') ? _CSRF_TOKEN_SHOW : '';
  var CAN_EDIT   = <?= $canEdit ? 'true' : 'false' ?>;

  // FIX: URL API lampiran wajib pakai prefix /api/
  var attachApiBase = BASE_URL + '/api/tindak-lanjut/' + TL_ID + '/attachments';

  function jsonHeaders(){
    return {'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN};
  }
  function setNoteCount(n){
    var badge = document.getElementById('note-count-badge');
    if (badge) badge.textContent = n;
  }
  function setAttachCount(n){
    var el = document.getElementById('tl-attach-count');
    if (el) el.textContent = n;
  }
  function escHtml(s){
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  // ── Status buttons ───────────────────────────────────────────────
  document.querySelectorAll('.tl-status-btn').forEach(function(btn){
    btn.addEventListener('click', async function(){
      var status = btn.dataset.status;
      var res = await fetch(BASE_URL + '/tindak-lanjut/' + TL_ID + '/status', {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({_csrf: CSRF_TOKEN, status: status})
      });
      var d = await res.json();
      if (d.success) location.reload();
      else alert(d.message || 'Gagal menyimpan status');
    });
  });

  // ── Note input ───────────────────────────────────────────────────
  var noteInput  = document.getElementById('note-input');
  var noteSubmit = document.getElementById('note-submit');

  async function submitNote(){
    if (!noteInput || !noteSubmit) return;
    var note = noteInput.value.trim();
    if (!note) return;
    noteSubmit.disabled = true;
    var res = await fetch(BASE_URL + '/tindak-lanjut/' + TL_ID + '/notes', {
      method: 'POST',
      headers: jsonHeaders(),
      body: JSON.stringify({_csrf: CSRF_TOKEN, note: note})
    });
    var d = await res.json();
    if (d.success) {
      noteInput.value = '';
      var emptyEl = document.getElementById('notes-empty');
      if (emptyEl) emptyEl.remove();
      var n   = d.note;
      var div = document.createElement('div');
      div.className      = 'tl-note-item';
      div.dataset.noteId = n.id;
      div.innerHTML =
        '<div class="tl-note-avatar">' + escHtml(String(n.author_name || '?').charAt(0).toUpperCase()) + '</div>' +
        '<div class="tl-note-body">' +
          '<div class="tl-note-meta">' +
            '<span class="tl-note-author">' + escHtml(n.author_name) + '</span>' +
            '<span class="tl-note-time">'  + escHtml(n.created_at_human || n.created_at) + '</span>' +
          '</div>' +
          '<div class="tl-note-text">' + escHtml(n.note).replace(/\n/g, '<br>') + '</div>' +
        '</div>' +
        (n.can_delete
          ? '<button class="tl-note-delete btn-delete-note" data-id="' + n.id + '" title="Hapus catatan">' +
              '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
            '</button>'
          : '');
      document.getElementById('notes-list').appendChild(div);
      var delBtn = div.querySelector('.btn-delete-note');
      if (delBtn) bindDeleteNote(delBtn);
      setNoteCount(d.note_count);
    } else {
      alert(d.message || 'Gagal mengirim note');
    }
    noteSubmit.disabled = false;
  }

  if (noteSubmit) noteSubmit.addEventListener('click', submitNote);
  if (noteInput)  noteInput.addEventListener('keydown', function(e){ if (e.key === 'Enter' && e.ctrlKey) submitNote(); });

  function bindDeleteNote(btn){
    btn.addEventListener('click', async function(){
      if (!confirm('Hapus catatan ini?')) return;
      var noteId = btn.dataset.id;
      var res = await fetch(BASE_URL + '/tindak-lanjut/' + TL_ID + '/notes/' + noteId + '/delete', {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({_csrf: CSRF_TOKEN})
      });
      var d = await res.json();
      if (d.success) {
        btn.closest('.tl-note-item').remove();
        setNoteCount(d.note_count);
        if (!document.querySelector('.tl-note-item'))
          document.getElementById('notes-list').innerHTML =
            '<div class="tl-empty" id="notes-empty"><p>Belum ada catatan progress</p></div>';
      } else {
        alert(d.message || 'Gagal menghapus catatan');
      }
    });
  }
  document.querySelectorAll('.btn-delete-note').forEach(bindDeleteNote);

  // ── Delete TL ────────────────────────────────────────────────────
  var btnDeleteTL = document.getElementById('btn-delete-tl');
  if (btnDeleteTL) {
    btnDeleteTL.addEventListener('click', async function(){
      if (!confirm('Hapus tindak lanjut ini secara permanen?')) return;
      btnDeleteTL.disabled = true;
      var res = await fetch(BASE_URL + '/tindak-lanjut/' + TL_ID + '/delete', {
        method: 'POST',
        headers: jsonHeaders(),
        body: JSON.stringify({_csrf: CSRF_TOKEN})
      });
      var d = await res.json();
      if (d.success) location.href = BASE_URL + '/tindak-lanjut';
      else { alert(d.message || 'Gagal menghapus'); btnDeleteTL.disabled = false; }
    });
  }

  // ════════════════════════════════════════════════════════════════
  // LAMPIRAN PROGRESS
  // ════════════════════════════════════════════════════════════════

  function fileIcon(ext){
    var e = (ext || '').toLowerCase();
    if (['jpg','jpeg','png','gif','webp'].indexOf(e) >= 0) return '🖼';
    if (['pdf'].indexOf(e) >= 0) return '📄';
    if (['doc','docx'].indexOf(e) >= 0) return '📝';
    if (['xls','xlsx'].indexOf(e) >= 0) return '📊';
    if (['ppt','pptx'].indexOf(e) >= 0) return '📑';
    if (['zip','rar'].indexOf(e) >= 0) return '🗃';
    return '📎';
  }

  function formatBytes(b){
    if (!b) return '';
    if (b < 1024) return b + ' B';
    if (b < 1048576) return (b/1024).toFixed(1) + ' KB';
    return (b/1048576).toFixed(1) + ' MB';
  }

  function renderAttachItem(a){
    var ext = (a.original_name || '').split('.').pop();
    var div = document.createElement('div');
    div.className   = 'tl-attach-item';
    div.dataset.id  = a.id;
    div.innerHTML =
      '<div class="tl-attach-icon" style="font-size:14px;">' + fileIcon(ext) + '</div>' +
      '<div style="flex:1;min-width:0;">' +
        '<div class="tl-attach-name">' +
          '<a href="' + escHtml(a.url || '#') + '" target="_blank" ' +
             'style="color:#7B1C1C;text-decoration:none;font-weight:600;" ' +
             'title="' + escHtml(a.original_name) + '">' +
            escHtml(a.original_name) +
          '</a>' +
        '</div>' +
        '<div class="tl-attach-meta">' +
          (a.size_fmt || formatBytes(a.file_size)) +
          (a.uploaded_by_name ? ' · ' + escHtml(a.uploaded_by_name) : '') +
        '</div>' +
      '</div>' +
      (a.can_delete
        ? '<button class="tl-attach-del btn-tl-del-attach" data-id="' + a.id + '" title="Hapus lampiran">' +
            '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
          '</button>'
        : '');
    if (a.can_delete) {
      var delBtn = div.querySelector('.btn-tl-del-attach');
      if (delBtn) bindDeleteAttach(delBtn);
    }
    return div;
  }

  function bindDeleteAttach(btn){
    btn.addEventListener('click', async function(){
      if (!confirm('Hapus lampiran ini?')) return;
      var aid = btn.dataset.id;
      // FIX: URL delete juga wajib pakai /api/ prefix
      var res = await fetch(BASE_URL + '/api/tindak-lanjut/' + TL_ID + '/attachments/' + aid + '/delete', {
        method: 'POST',
        headers: {'X-CSRF-Token': CSRF_TOKEN},
        body: ''
      });
      var d = await res.json();
      if (d.success) {
        var item = btn.closest('.tl-attach-item');
        if (item) item.remove();
        setAttachCount(d.attach_count);
        var list = document.getElementById('tl-attachment-list');
        if (list && !list.querySelector('.tl-attach-item'))
          list.innerHTML = '<div class="tl-attach-empty">Belum ada lampiran</div>';
      } else {
        alert(d.message || 'Gagal menghapus lampiran');
      }
    });
  }

  // FIX: Load lampiran — baca d.data bukan d.attachments
  async function loadAttachments(){
    var list = document.getElementById('tl-attachment-list');
    if (!list) return;
    try {
      var res = await fetch(attachApiBase, { headers: {'X-CSRF-Token': CSRF_TOKEN} });
      if (!res.ok) throw new Error('HTTP ' + res.status);
      var d = await res.json();
      list.innerHTML = '';
      if (!d.success || !d.data || !d.data.length) {
        list.innerHTML = '<div class="tl-attach-empty">Belum ada lampiran</div>';
        setAttachCount(0);
        return;
      }
      d.data.forEach(function(a){ list.appendChild(renderAttachItem(a)); });
      setAttachCount(d.attach_count || d.data.length);
    } catch(e) {
      list.innerHTML = '<div class="tl-attach-empty" style="color:#dc2626;">Gagal memuat lampiran</div>';
    }
  }
  loadAttachments();

  // Toggle upload form
  var btnShowUpload   = document.getElementById('btn-tl-show-upload');
  var uploadWrapper   = document.getElementById('tl-upload-form-wrapper');
  var btnCancelUpload = document.getElementById('btn-tl-cancel-upload');

  if (btnShowUpload) {
    btnShowUpload.addEventListener('click', function(){
      if (uploadWrapper) uploadWrapper.style.display = uploadWrapper.style.display === 'none' ? '' : 'none';
    });
  }
  if (btnCancelUpload) {
    btnCancelUpload.addEventListener('click', function(){
      if (uploadWrapper) uploadWrapper.style.display = 'none';
      var form = document.getElementById('form-tl-upload');
      if (form) form.reset();
      var alertEl = document.getElementById('tl-upload-alert');
      if (alertEl) { alertEl.className = 'd-none'; alertEl.textContent = ''; }
    });
  }

  // Handle upload form submit
  var formUpload = document.getElementById('form-tl-upload');
  if (formUpload) {
    formUpload.addEventListener('submit', async function(e){
      e.preventDefault();
      var fileInput = document.getElementById('tl-attach-file');
      var category  = document.getElementById('tl-attach-category');
      var alertEl   = document.getElementById('tl-upload-alert');
      var spinner   = document.getElementById('tl-upload-spinner');
      var btnUpload = document.getElementById('btn-tl-do-upload');
      if (!fileInput || !fileInput.files.length) return;

      var file = fileInput.files[0];
      if (file.size > 10 * 1024 * 1024) {
        alertEl.className = 'alert alert-danger py-1 px-2 mt-2';
        alertEl.textContent = 'Ukuran file melebihi 10 MB.';
        return;
      }

      var fd = new FormData();
      fd.append('file', file);
      fd.append('category', category ? category.value : 'lainnya');

      btnUpload.disabled = true;
      if (spinner) spinner.classList.remove('d-none');
      alertEl.className = 'd-none';

      try {
        var res = await fetch(attachApiBase + '/upload', {
          method: 'POST',
          headers: {'X-CSRF-Token': CSRF_TOKEN},
          body: fd
        });
        var d = await res.json();
        if (d.success) {
          formUpload.reset();
          if (uploadWrapper) uploadWrapper.style.display = 'none';
          var list = document.getElementById('tl-attachment-list');
          var emptyEl = list ? list.querySelector('.tl-attach-empty') : null;
          if (emptyEl) emptyEl.remove();
          if (list && d.attachment) list.appendChild(renderAttachItem(d.attachment));
          setAttachCount(d.attach_count || 0);
        } else {
          alertEl.className = 'alert alert-danger py-1 px-2 mt-2';
          alertEl.textContent = d.message || 'Gagal mengunggah file';
        }
      } catch(err) {
        alertEl.className = 'alert alert-danger py-1 px-2 mt-2';
        alertEl.textContent = 'Terjadi kesalahan jaringan.';
      }
      btnUpload.disabled = false;
      if (spinner) spinner.classList.add('d-none');
    });
  }

})();
</script>
