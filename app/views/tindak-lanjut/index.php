<?php
$baseUrl     = rtrim(BASE_URL, '/');
$user        = Auth::user();
$isAdminLike = Auth::hasRole('admin', 'sekretaris');

$priorityColor = ['high' => 'danger', 'medium' => 'warning', 'low' => 'success'];
$priorityLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$statusColor   = ['pending' => 'secondary', 'in_progress' => 'info', 'done' => 'success', 'cancelled' => 'danger'];
$statusLabel   = ['pending' => 'Menunggu', 'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

$qp    = array_filter(['q' => $search ?? '', 'status' => $status ?? '', 'priority' => $priority ?? '', 'user_id' => $user_id ?? 0]);
$qpStr = $qp ? '&' . http_build_query($qp) : '';

$kanbanCols = [
    'pending'     => ['label' => 'Menunggu',    'color' => 'secondary', 'items' => []],
    'in_progress' => ['label' => 'Berlangsung', 'color' => 'info',      'items' => []],
    'done'        => ['label' => 'Selesai',     'color' => 'success',   'items' => []],
    'cancelled'   => ['label' => 'Dibatalkan',  'color' => 'danger',    'items' => []],
];
foreach ($tindakLanjutList as $tl) {
    $s = $tl['status'] ?? 'pending';
    if (isset($kanbanCols[$s])) $kanbanCols[$s]['items'][] = $tl;
}

$allUsersJson = json_encode(array_values(array_map(
    fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']],
    $allUsers ?? []
)));

// BUG FIX #2: meetingOptions harus disediakan oleh controller, fallback ke query jika belum ada
$meetingOptions = $meetingOptions ?? Database::query("SELECT id, title FROM meetings ORDER BY start_datetime DESC LIMIT 200");

$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');
?>

<style>
/* ── Page Header ─────────────────────────────────────────────── */
.tli-page-header { }
.tli-header-icon { display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:rgba(123,28,28,.1);border-radius:8px;color:#7B1C1C; }
.tli-page-title  { font-size:clamp(16px,2.5vw,22px);font-weight:800;color:#1a1a1a;margin:0; }
.tli-page-sub    { font-size:13px;color:#888;margin:0; }

/* ── Buttons ─────────────────────────────────────────────────── */
.tli-btn-gold        { display:inline-flex;align-items:center;gap:6px;padding:.45rem .9rem;background:linear-gradient(135deg,#7B1C1C,#a83218);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s; }
.tli-btn-gold:hover  { opacity:.88; }
.tli-btn-gold:disabled { opacity:.55;cursor:not-allowed; }
.tli-btn-brand-sm    { display:inline-flex;align-items:center;gap:5px;padding:.38rem .75rem;background:#7B1C1C;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:opacity .15s; }
.tli-btn-brand-sm:hover { opacity:.85; }
.tli-btn-reset       { display:inline-flex;align-items:center;padding:.38rem .7rem;background:#f0f0f0;color:#555;border:none;border-radius:7px;font-size:12px;text-decoration:none;transition:background .15s; }
.tli-btn-reset:hover { background:#e0e0e0; }

/* ── Stat Cards ──────────────────────────────────────────────── */
.tli-stat-row        { display:flex;flex-wrap:wrap;gap:12px; }
.tli-stat-card       { display:flex;align-items:center;gap:10px;background:#fff;border:1px solid #eee;border-radius:12px;padding:.65rem 1rem;flex:1;min-width:120px;box-shadow:0 1px 4px rgba(0,0,0,.05); }
.tli-stat-icon       { display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:9px;flex-shrink:0; }
.tli-stat-icon-brand { background:rgba(123,28,28,.1);color:#7B1C1C; }
.tli-stat-icon-muted { background:#f0f0f0;color:#888; }
.tli-stat-icon-info  { background:#e0f4ff;color:#0284c7; }
.tli-stat-icon-success { background:#e6faf0;color:#16a34a; }
.tli-stat-icon-danger  { background:#fff0f0;color:#dc2626; }
.tli-stat-val        { font-size:20px;font-weight:800;color:#1a1a1a;line-height:1; }
.tli-stat-lbl        { font-size:11px;color:#888;margin-top:2px; }

/* ── Toolbar ─────────────────────────────────────────────────── */
.tli-toolbar         { display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px; }
.tli-filter-form     { display:flex;flex-wrap:wrap;align-items:center;gap:8px;flex:1; }
.tli-search-wrap     { position:relative;display:flex;align-items:center; }
.tli-search-ico      { position:absolute;left:9px;color:#aaa;pointer-events:none; }
.tli-input           { border:1px solid #ddd;border-radius:7px;padding:.38rem .6rem;font-size:13px;color:#333;background:#fff;transition:border .15s; }
.tli-input:focus     { outline:none;border-color:#7B1C1C; }
.tli-search-input    { padding-left:28px;min-width:180px; }
.tli-select          { min-width:130px; }
.tli-view-toggle     { display:flex;gap:4px; }
.tli-vbtn            { padding:.38rem .8rem;border:1px solid #ddd;background:#fff;color:#555;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s; }
.tli-vbtn.active,.tli-vbtn:hover { background:#7B1C1C;color:#fff;border-color:#7B1C1C; }

/* ── Table Card ──────────────────────────────────────────────── */
.tli-card            { background:#fff;border:1px solid #eee;border-radius:12px;overflow:hidden;box-shadow:0 1px 4px rgba(0,0,0,.05); }
.tli-table thead tr  { background:#faf4eb; }
.tli-table th        { font-size:11px;font-weight:700;color:#7B1C1C;text-transform:uppercase;letter-spacing:.04em;padding:.6rem .8rem;border-bottom:1px solid #eee; }
.tli-table td        { font-size:13px;padding:.6rem .8rem;vertical-align:middle;border-bottom:1px solid #f5f5f5; }
.tli-table tbody tr:last-child td { border-bottom:none; }
.tli-table tbody tr:hover  { background:#fdf7f0; }
.tli-row-overdue     { background:#fff8f8 !important; }

/* ── Table Content ───────────────────────────────────────────── */
.tli-task-link       { font-weight:600;color:#1a1a1a;text-decoration:none; }
.tli-task-link:hover { color:#7B1C1C;text-decoration:underline; }
.tli-meeting-link    { font-size:12px;color:#7B1C1C;text-decoration:none; }
.tli-meeting-link:hover { text-decoration:underline; }
.tli-avatar          { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:50%;background:#7B1C1C;color:#fff;font-size:10px;font-weight:700;flex-shrink:0; }
.tli-assignee        { font-size:13px; }
.tli-text-muted      { color:#aaa; }
.tli-overdue-text    { color:#dc2626;font-weight:600; }

/* ── Badges ──────────────────────────────────────────────────── */
.tli-badge           { display:inline-block;padding:.2em .55em;border-radius:5px;font-size:11px;font-weight:700;letter-spacing:.03em; }
.tli-badge-danger    { background:#fff0f0;color:#dc2626; }
.tli-badge-warning   { background:#fffbea;color:#b45309; }
.tli-badge-success   { background:#e6faf0;color:#16a34a; }
.tli-badge-info      { background:#e0f4ff;color:#0284c7; }
.tli-badge-secondary { background:#f0f0f0;color:#666; }
.tli-badge-brand     { background:rgba(123,28,28,.1);color:#7B1C1C; }
.tli-badge-muted     { background:#f5f5f5;color:#aaa; }
.tli-badge-xs        { font-size:10px;padding:.15em .45em; }

/* ── Status Select ───────────────────────────────────────────── */
.tli-status-sel      { border:1px solid #ddd;border-radius:6px;padding:.25rem .45rem;font-size:12px;color:#333;cursor:pointer;transition:opacity .15s; }
.tli-status-sel.loading { opacity:.5;pointer-events:none; }

/* ── Action Buttons ──────────────────────────────────────────── */
.tli-ico-btn         { display:inline-flex;align-items:center;justify-content:center;gap:4px;padding:.3rem .55rem;border:1px solid #e0e0e0;border-radius:7px;background:#fff;font-size:12px;color:#555;cursor:pointer;text-decoration:none;transition:all .15s;white-space:nowrap; }
.tli-ico-btn:hover   { background:#f5f5f5;border-color:#ccc; }
.tli-ico-detail      { color:#0284c7;border-color:#bde0f5; }
.tli-ico-detail:hover { background:#e0f4ff; }
.tli-ico-del         { color:#dc2626;border-color:#ffd0d0; }
.tli-ico-del:hover   { background:#fff0f0; }
.tli-note-bubble     { display:inline-flex;align-items:center;justify-content:center;min-width:16px;height:16px;background:#7B1C1C;color:#fff;border-radius:8px;font-size:10px;font-weight:700;padding:0 4px; }

/* ── Empty State ─────────────────────────────────────────────── */
.tli-empty-state     { padding:2.5rem;text-align:center;color:#aaa; }

/* ── Kanban ──────────────────────────────────────────────────── */
.tli-kb-col          { background:#f9f9f9;border:1px solid #eee;border-radius:12px;overflow:hidden;height:100%; }
.tli-kb-header       { display:flex;align-items:center;gap:8px;padding:.6rem .9rem;background:#faf4eb;border-bottom:1px solid #eee; }
.tli-kb-title        { font-size:12px;font-weight:700;color:#7B1C1C;text-transform:uppercase;letter-spacing:.04em; }
/* UX FIX: kanban column body sekarang punya max-height + scroll */
.tli-kb-body         { padding:.6rem;min-height:120px;max-height:520px;overflow-y:auto;display:flex;flex-direction:column;gap:8px; }
.tli-kb-empty        { text-align:center;font-size:12px;color:#ccc;padding:1.5rem 0; }
.tli-kcard           { background:#fff;border:1px solid #eee;border-radius:9px;padding:.65rem .8rem;box-shadow:0 1px 3px rgba(0,0,0,.04);cursor:grab;transition:box-shadow .15s; }
.tli-kcard:hover     { box-shadow:0 3px 10px rgba(0,0,0,.09); }
.tli-kcard-desc      { font-size:13px;font-weight:600;color:#1a1a1a;text-decoration:none;display:block;margin-bottom:6px; }
.tli-kcard-desc:hover { color:#7B1C1C; }
.tli-kcard-meta      { display:flex;flex-wrap:wrap;align-items:center;gap:5px;margin-bottom:5px; }
.tli-kcard-due       { font-size:11px;color:#888; }
.tli-kcard-footer    { border-top:1px solid #f0f0f0;padding-top:5px;margin-top:2px; }
.tli-kcard-meeting   { font-size:11px;color:#7B1C1C;text-decoration:none; }
.tli-kcard-meeting:hover { text-decoration:underline; }

/* ── Modal Notes ─────────────────────────────────────────────── */
.tli-notes-list      { max-height:360px;overflow-y:auto; }
.tli-note-item       { display:flex;gap:.65rem;padding:.75rem 1rem;border-bottom:1px solid #f5f5f5;position:relative; }
.tli-note-item:last-child { border-bottom:none; }
.tli-note-avatar     { width:30px;height:30px;border-radius:50%;background:#7B1C1C;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;flex-shrink:0; }
.tli-note-body       { flex:1;min-width:0; }
.tli-note-meta       { display:flex;align-items:center;gap:.4rem;margin-bottom:.2rem;flex-wrap:wrap; }
.tli-note-author     { font-size:13px;font-weight:700;color:#333; }
.tli-note-time       { font-size:11px;color:#aaa; }
.tli-note-text       { font-size:13px;color:#444;white-space:pre-wrap;line-height:1.55; }
.tli-note-del-btn    { position:absolute;top:.6rem;right:.6rem;display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#fff0f0;color:#dc2626;border:1px solid #ffd0d0;border-radius:5px;cursor:pointer;opacity:0;transition:opacity .15s; }
.tli-note-item:hover .tli-note-del-btn { opacity:1; }
.tli-note-empty      { padding:2rem;text-align:center;color:#bbb;font-size:13px; }
.tli-note-input-row  { display:flex;gap:8px;align-items:flex-end; }
.tli-note-ta         { flex:1;border:1px solid #ddd;border-radius:8px;padding:.45rem .7rem;font-size:13px;resize:none;transition:border .15s; }
.tli-note-ta:focus   { outline:none;border-color:#7B1C1C; }

/* ── Modal Enhancements ──────────────────────────────────────── */
.modal-content       { border-radius:14px;border:none;box-shadow:0 8px 32px rgba(0,0,0,.15); }
.modal-header-tl     { background:linear-gradient(135deg,#7B1C1C,#a83218);border-radius:14px 14px 0 0;padding:1rem 1.25rem; }
.modal-header-tl .modal-title { color:#fff;font-weight:700;font-size:15px; }
.modal-header-tl .btn-close   { filter:invert(1) brightness(2); }
.tli-form-label      { font-size:12px;font-weight:700;color:#555;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px; }
.tli-required        { color:#dc2626; }

/* ── Spinner ─────────────────────────────────────────────────── */
.tli-spinner { display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:tli-spin .6s linear infinite; }
@keyframes tli-spin { to { transform:rotate(360deg); } }
</style>

<div class="tli-page-header mb-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="tli-header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </span>
        <h1 class="tli-page-title">Tindak Lanjut</h1>
      </div>
      <p class="tli-page-sub">Kelola dan pantau semua tindak lanjut kegiatan rapat</p>
    </div>
    <?php if ($isAdminLike): ?>
    <button class="tli-btn-gold" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Tindak Lanjut
    </button>
    <?php endif; ?>
  </div>
</div>

<div class="tli-stat-row mb-4">
<?php
$statDefs = [
  ['key'=>'total',       'label'=>'Total Tugas',  'icon'=>'brand',
   'svg'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
  ['key'=>'pending',     'label'=>'Menunggu',     'icon'=>'muted',
   'svg'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
  ['key'=>'in_progress', 'label'=>'Berlangsung',  'icon'=>'info',
   'svg'=>'<polygon points="5 3 19 12 5 21 5 3"/>'],
  ['key'=>'done',        'label'=>'Selesai',      'icon'=>'success',
   'svg'=>'<polyline points="20 6 9 17 4 12"/>'],
  ['key'=>'overdue',     'label'=>'Terlambat',    'icon'=>'danger',
   'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
];
foreach ($statDefs as $sc): ?>
  <div class="tli-stat-card">
    <span class="tli-stat-icon tli-stat-icon-<?= $sc['icon'] ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $sc['svg'] ?></svg>
    </span>
    <div>
      <div class="tli-stat-val" id="stat-<?= $sc['key'] ?>"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
      <div class="tli-stat-lbl"><?= $sc['label'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="tli-toolbar mb-3">
  <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="tli-filter-form">
    <div class="tli-search-wrap">
      <svg class="tli-search-ico" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="tli-input tli-search-input" placeholder="Cari deskripsi…">
    </div>

    <select name="status" class="tli-input tli-select" id="filter-status">
      <option value="">Semua Status</option>
      <?php foreach ($statusLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($status ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <select name="priority" class="tli-input tli-select">
      <option value="">Semua Prioritas</option>
      <?php foreach ($priorityLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($priority ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <?php if (Auth::hasRole('admin', 'sekretaris') && !empty($allUsers)): ?>
    <select name="user_id" class="tli-input tli-select">
      <option value="">Semua User</option>
      <?php foreach ($allUsers as $u): ?>
      <option value="<?= $u['id'] ?>" <?= ($user_id ?? 0) == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <button type="submit" class="tli-btn-brand-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filter
    </button>
    <?php
    // BUG FIX #3: cek user_id > 0 agar tombol Reset muncul saat filter user aktif
    $hasFilter = ($status ?? '') || ($priority ?? '') || ($search ?? '') || (($user_id ?? 0) > 0);
    ?>
    <?php if ($hasFilter): ?>
    <a href="<?= $baseUrl ?>/tindak-lanjut" class="tli-btn-reset">&#x2715; Reset</a>
    <?php endif; ?>
  </form>

  <div class="tli-view-toggle">
    <button id="btn-view-table" class="tli-vbtn" title="Tampilan Tabel">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
      Tabel
    </button>
    <button id="btn-view-kanban" class="tli-vbtn" title="Tampilan Kanban">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="12" rx="1"/><rect x="17" y="3" width="5" height="15" rx="1"/></svg>
      Kanban
    </button>
  </div>
</div>

<div id="view-table">
  <div class="tli-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 tli-table">
        <thead>
          <tr>
            <th style="width:32%">Tugas</th>
            <th>Meeting</th>
            <th>PIC</th>
            <th>Deadline</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th style="width:120px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tindakLanjutList)): ?>
          <tr><td colspan="7"><div class="tli-empty-state"><p>Belum ada tindak lanjut yang ditemukan</p></div></td></tr>
          <?php endif; ?>
          <?php foreach ($tindakLanjutList as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <tr class="<?= $overdue ? 'tli-row-overdue' : '' ?>" id="trow-<?= $tl['id'] ?>">
            <td>
              <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-task-link"><?= htmlspecialchars($tl['description']) ?></a>
            </td>
            <td>
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-meeting-link"><?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 40, '…')) ?></a>
            </td>
            <td>
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="d-flex align-items-center gap-2">
                <span class="tli-avatar"><?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?></span>
                <span class="tli-assignee"><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php else: ?>
              <span class="tli-text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="text-nowrap <?= $overdue ? 'tli-overdue-text' : 'tli-text-muted' ?>">
              <?php if (!empty($tl['due_date'])): ?>
              <div class="d-flex align-items-center gap-1">
                <?php if ($overdue): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php endif; ?>
                <?= date('d M Y', strtotime($tl['due_date'])) ?>
              </div>
              <?php else: ?>—<?php endif; ?>
            </td>
            <td>
              <span class="tli-badge tli-badge-<?= $priorityColor[$tl['priority']] ?? 'muted' ?>">
                <?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?>
              </span>
            </td>
            <td>
              <?php if ($canEdit): ?>
              <select class="tli-status-sel status-select" data-id="<?= $tl['id'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status">
                <?php foreach ($statusLabel as $v => $l): ?>
                <option value="<?= $v ?>" <?= $tl['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <?php else: ?>
              <span class="tli-badge tli-badge-<?= $statusColor[$tl['status']] ?? 'muted' ?>">
                <?= $statusLabel[$tl['status']] ?? ucfirst($tl['status']) ?>
              </span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-end align-items-center">
                <button class="tli-ico-btn btn-notes"
                  data-id="<?= $tl['id'] ?>"
                  data-status="<?= $tl['status'] ?>"
                  data-can-done="<?= ($canEdit && $tl['status'] !== 'done') ? '1' : '0' ?>"
                  data-desc="<?= htmlspecialchars($tl['description']) ?>"
                  data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                  data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                  data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                  data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                  data-can-edit="<?= $canEdit ? '1' : '0' ?>"
                  title="Progress Notes"
                  data-bs-toggle="modal"
                  data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span id="nbadge-<?= $tl['id'] ?>" class="tli-note-bubble" <?= $nc < 1 ? 'style="display:none"' : '' ?>><?= $nc ?></span>
                </button>
                <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-ico-btn tli-ico-detail" title="Lihat Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if ($isAdminLike): ?>
                <button class="tli-ico-btn tli-ico-del btn-del"
                  data-id="<?= $tl['id'] ?>"
                  data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                  title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="view-kanban" style="display:none">
  <div class="row g-3">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="tli-kb-col">
        <div class="tli-kb-header">
          <span class="tli-kb-title"><?= $col['label'] ?></span>
          <span class="tli-badge tli-badge-brand ms-auto" id="kanban-count-<?= $colStatus ?>"><?= count($col['items']) ?></span>
        </div>
        <div class="tli-kb-body kanban-col" id="kanban-col-<?= $colStatus ?>" data-status="<?= $colStatus ?>">
          <?php if (empty($col['items'])): ?>
          <div class="tli-kb-empty kanban-empty">Belum ada tugas</div>
          <?php endif; ?>
          <?php foreach ($col['items'] as $tl):
            $klOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'], ['done','cancelled']);
          ?>
          <div class="tli-kcard" id="kcard-<?= $tl['id'] ?>"
            data-id="<?= $tl['id'] ?>"
            data-status="<?= $tl['status'] ?>"
            data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status">
            <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-kcard-desc">
              <?= htmlspecialchars($tl['description']) ?>
            </a>
            <div class="tli-kcard-meta">
              <span class="tli-badge tli-badge-<?= $priorityColor[$tl['priority']] ?? 'muted' ?> tli-badge-xs">
                <?= $priorityLabel[$tl['priority']] ?? '' ?>
              </span>
              <?php if (!empty($tl['due_date'])): ?>
              <span class="tli-kcard-due <?= $klOverdue ? 'tli-overdue-text' : '' ?>">
                <?php if ($klOverdue): ?>
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <?php endif; ?>
                <?= date('d M', strtotime($tl['due_date'])) ?>
              </span>
              <?php endif; ?>
              <?php if (!empty($tl['assignee_name'])): ?>
              <span class="tli-avatar" style="width:18px;height:18px;font-size:9px" title="<?= htmlspecialchars($tl['assignee_name']) ?>">
                <?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?>
              </span>
              <?php endif; ?>
            </div>
            <div class="tli-kcard-footer">
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-kcard-meeting">
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'],0,28,'…')) ?>
              </a>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($isAdminLike): ?>
<div class="modal fade" id="modalTambahTL" tabindex="-1" aria-hidden="true" aria-labelledby="modalTambahTLLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="formTambahTL">
        <input type="hidden" name="_csrf" value="<?= $csrfToken ?>">
        <div class="modal-header modal-header-tl">
          <h5 class="modal-title" id="modalTambahTLLabel">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="me-2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="tli-form-label">Meeting <span class="tli-required">*</span></label>
            <select name="meeting_id" class="form-select" required>
              <option value="">— Pilih Meeting —</option>
              <?php foreach ($meetingOptions as $m): ?>
              <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="tli-form-label">Deskripsi Tugas <span class="tli-required">*</span></label>
            <textarea name="description" class="form-control" rows="3"
              placeholder="Tuliskan deskripsi tindak lanjut secara jelas…" required></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="tli-form-label">PIC / Penanggung Jawab</label>
              <select name="assigned_to" class="form-select">
                <option value="">— Pilih PIC —</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="tli-form-label">Prioritas</label>
              <select name="priority" class="form-select">
                <option value="low">Rendah</option>
                <option value="medium" selected>Sedang</option>
                <option value="high">Tinggi</option>
              </select>
            </div>
            <div class="col-md-4">
              <label class="tli-form-label">Deadline</label>
              <input type="date" name="due_date" class="form-control"
                min="<?= date('Y-m-d') ?>">
            </div>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
          <!-- UX FIX: tombol Simpan punya id untuk disable saat loading -->
          <button type="submit" class="tli-btn-gold" id="btnSimpanTL">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            <span id="btnSimpanTLText">Simpan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Progress Notes -->
<div class="modal fade" id="modalNotes" tabindex="-1" aria-hidden="true" aria-labelledby="modalNotesLabel">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header modal-header-tl">
        <h5 class="modal-title" id="modalNotesLabel">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span id="modalNotesTitle">Progress Notes</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <div id="modalNotesList" class="tli-notes-list">
          <div class="tli-note-empty" id="modalNotesEmpty">Memuat...</div>
        </div>
        <div id="modalNotesInputArea" class="p-3 border-top" style="display:none">
          <div class="tli-note-input-row">
            <textarea id="modalNoteTextarea" class="tli-note-ta" rows="2"
              placeholder="Tulis catatan progress… (Ctrl+Enter untuk kirim)"></textarea>
            <button id="modalNoteSubmit" class="tli-btn-gold" style="white-space:nowrap">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim
            </button>
          </div>
          <p style="font-size:11px;color:#aaa;margin:.3rem 0 0">Gunakan @nama untuk mention pengguna</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function(){'use strict';
var BASE=<?= json_encode($baseUrl) ?>;
var ALL_USERS=<?= $allUsersJson ?>;
var IS_ADMIN_LIKE=<?= $isAdminLike ? 'true' : 'false' ?>;
var CSRF_TOKEN=<?= json_encode($_SESSION['csrf_token'] ?? '') ?>;

function postJSON(url,body){
  return fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify(Object.assign({_csrf:CSRF_TOKEN},body))}).then(r=>r.json());
}
function updateStatCards(s){
  if(!s)return;
  ['total','pending','in_progress','done','overdue'].forEach(function(k){
    var el=document.getElementById('stat-'+k);
    if(el&&s[k]!==undefined)el.textContent=s[k];
  });
}
function updateNoteBubble(id,count){
  document.querySelectorAll('#nbadge-'+id).forEach(function(el){
    el.textContent=count;
    el.style.display=count>0?'flex':'none';
  });
}
function escHtml(s){
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

// BUG FIX #1: helper update kanban column count badge
function updateKanbanCount(status){
  var col=document.getElementById('kanban-col-'+status);
  var badge=document.getElementById('kanban-count-'+status);
  if(!col||!badge)return;
  var cards=col.querySelectorAll('.tli-kcard');
  badge.textContent=cards.length;
  var empty=col.querySelector('.kanban-empty');
  if(cards.length===0){
    if(!empty){
      var d=document.createElement('div');
      d.className='tli-kb-empty kanban-empty';
      d.textContent='Belum ada tugas';
      col.appendChild(d);
    }
  } else {
    if(empty)empty.remove();
  }
}

// ── View toggle ───────────────────────────────────────────────
var btnTable=document.getElementById('btn-view-table'),
    btnKanban=document.getElementById('btn-view-kanban'),
    divTable=document.getElementById('view-table'),
    divKanban=document.getElementById('view-kanban');
function setView(v){
  var isK=v==='kanban';
  divTable.style.display=isK?'none':'';
  divKanban.style.display=isK?'':'none';
  btnTable.classList.toggle('active',!isK);
  btnKanban.classList.toggle('active',isK);
}
btnTable.addEventListener('click',function(){setView('table');});
btnKanban.addEventListener('click',function(){setView('kanban');});
setView('table');

// ── Status select (UX FIX: loading class saat update) ─────────
document.querySelectorAll('.status-select').forEach(function(sel){
  sel.dataset.prev=sel.value;
  sel.addEventListener('change',async function(){
    this.classList.add('loading');
    var d=await postJSON(this.dataset.url,{status:this.value});
    this.classList.remove('loading');
    if(!d.success){alert(d.message||'Gagal update status');this.value=this.dataset.prev;return;}
    this.dataset.prev=this.value;
    updateStatCards(d.summary);
  });
});

// ── Delete ────────────────────────────────────────────────────
function bindDel(){
  document.querySelectorAll('.btn-del').forEach(function(btn){
    if(btn._bound)return;btn._bound=true;
    btn.addEventListener('click',async function(){
      if(!confirm('Hapus tindak lanjut ini?'))return;
      var r=await fetch(this.dataset.url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({_csrf:CSRF_TOKEN})});
      var d=await r.json();
      if(!d.success){alert(d.message||'Gagal hapus');return;}
      var id=this.dataset.id;
      var trow=document.getElementById('trow-'+id);
      var kcard=document.getElementById('kcard-'+id);
      if(trow)trow.remove();
      if(kcard){
        var oldStatus=kcard.dataset.status;
        kcard.remove();
        updateKanbanCount(oldStatus); // BUG FIX #1
      }
      updateStatCards(d.summary);
    });
  });
}
bindDel();

// ── Kanban drag (BUG FIX #1: update count kedua kolom) ────────
if(IS_ADMIN_LIKE){
  document.querySelectorAll('.kanban-col').forEach(function(col){
    Sortable.create(col,{group:'kanban',animation:150,onEnd:async function(evt){
      var card=evt.item,newCol=evt.to,oldCol=evt.from,newStatus=newCol.dataset.status,oldStatus=oldCol.dataset.status;
      if(newStatus===card.dataset.status)return;
      card.dataset.status=newStatus;
      var d=await postJSON(card.dataset.url,{status:newStatus});
      if(!d.success){
        alert(d.message||'Gagal update status');
        oldCol.appendChild(card);
        card.dataset.status=oldStatus;
        updateKanbanCount(newStatus);
        updateKanbanCount(oldStatus);
        return;
      }
      updateKanbanCount(newStatus); // BUG FIX #1
      updateKanbanCount(oldStatus); // BUG FIX #1
      updateStatCards(d.summary);
    }});
  });
}

// ── Modal Notes handler ───────────────────────────────────────
var _notesCurrentId=0;
var _notesCanEdit=false;

var modalNotesEl=document.getElementById('modalNotes');
if(modalNotesEl){
  modalNotesEl.addEventListener('show.bs.modal',function(e){
    var btn=e.relatedTarget;
    if(!btn)return;
    _notesCurrentId=parseInt(btn.dataset.id)||0;
    _notesCanEdit=btn.dataset.canEdit==='1';
    document.getElementById('modalNotesTitle').textContent=
      'Progress Notes \u2014 '+escHtml(btn.dataset.desc||'');
    var inputArea=document.getElementById('modalNotesInputArea');
    if(inputArea)inputArea.style.display=_notesCanEdit?'':'none';
    loadNotes(_notesCurrentId);
  });
  modalNotesEl.addEventListener('hidden.bs.modal',function(){
    document.getElementById('modalNotesList').innerHTML=
      '<div class="tli-note-empty" id="modalNotesEmpty">Memuat...</div>';
    _notesCurrentId=0;
  });
}

function loadNotes(id){
  var list=document.getElementById('modalNotesList');
  list.innerHTML='<div class="tli-note-empty">Memuat...</div>';
  fetch(BASE+'/tindak-lanjut/'+id+'/notes',{
    headers:{'X-CSRF-Token':CSRF_TOKEN}
  }).then(function(r){return r.json();}).then(function(notes){
    renderNotes(notes);
  }).catch(function(){
    list.innerHTML='<div class="tli-note-empty">Gagal memuat catatan</div>';
  });
}

function renderNotes(notes){
  var list=document.getElementById('modalNotesList');
  if(!notes||notes.length===0){
    list.innerHTML='<div class="tli-note-empty">Belum ada catatan progress</div>';
    return;
  }
  list.innerHTML='';
  notes.forEach(function(n){list.appendChild(buildNoteEl(n));});
}

function buildNoteEl(n){
  var div=document.createElement('div');
  div.className='tli-note-item';
  div.dataset.noteId=n.id;
  div.innerHTML=
    '<div class="tli-note-avatar">'+escHtml(String(n.author_name||'?').charAt(0).toUpperCase())+'</div>'+
    '<div class="tli-note-body">'+
      '<div class="tli-note-meta">'+
        '<span class="tli-note-author">'+escHtml(n.author_name)+'</span>'+
        '<span class="tli-note-time">'+escHtml(n.created_at_human||n.created_at||'')+'</span>'+
      '</div>'+
      '<div class="tli-note-text">'+escHtml(n.note).replace(/\n/g,'<br>')+'</div>'+
    '</div>'+
    (n.can_delete
      ? '<button class="tli-note-del-btn btn-modal-del-note" data-id="'+n.id+'" title="Hapus">'+
          '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'+
        '</button>'
      : '');
  var delBtn=div.querySelector('.btn-modal-del-note');
  if(delBtn)bindModalNoteDelete(delBtn);
  return div;
}

function bindModalNoteDelete(btn){
  btn.addEventListener('click',async function(){
    if(!confirm('Hapus catatan ini?'))return;
    var noteId=btn.dataset.id;
    var r=await fetch(BASE+'/tindak-lanjut/'+_notesCurrentId+'/notes/'+noteId+'/delete',{
      method:'POST',
      headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
      body:JSON.stringify({_csrf:CSRF_TOKEN})
    });
    var d=await r.json();
    if(d.success){
      btn.closest('.tli-note-item').remove();
      if(!document.querySelector('#modalNotesList .tli-note-item'))
        document.getElementById('modalNotesList').innerHTML='<div class="tli-note-empty">Belum ada catatan progress</div>';
      updateNoteBubble(_notesCurrentId,d.note_count);
    } else {
      alert(d.message||'Gagal menghapus catatan');
    }
  });
}

var modalNoteSubmit=document.getElementById('modalNoteSubmit');
var modalNoteTA=document.getElementById('modalNoteTextarea');
async function submitModalNote(){
  if(!modalNoteTA||!_notesCurrentId)return;
  var note=modalNoteTA.value.trim();
  if(!note)return;
  if(modalNoteSubmit)modalNoteSubmit.disabled=true;
  var r=await fetch(BASE+'/tindak-lanjut/'+_notesCurrentId+'/notes',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},
    body:JSON.stringify({_csrf:CSRF_TOKEN,note:note})
  });
  var d=await r.json();
  if(d.success){
    modalNoteTA.value='';
    var list=document.getElementById('modalNotesList');
    // BUG FIX #4: hapus pesan kosong jika ada
    var empty=list.querySelector('.tli-note-empty');
    if(empty)empty.remove();
    list.appendChild(buildNoteEl(d.note));
    list.scrollTop=list.scrollHeight;
    updateNoteBubble(_notesCurrentId,d.note_count);
  } else {
    alert(d.message||'Gagal mengirim note');
  }
  if(modalNoteSubmit)modalNoteSubmit.disabled=false;
}
if(modalNoteSubmit)modalNoteSubmit.addEventListener('click',submitModalNote);
if(modalNoteTA)modalNoteTA.addEventListener('keydown',function(e){if(e.key==='Enter'&&e.ctrlKey)submitModalNote();});

// UX FIX: loading state saat submit form Tambah Tindak Lanjut
var formTambahTL=document.getElementById('formTambahTL');
var btnSimpanTL=document.getElementById('btnSimpanTL');
var btnSimpanTLText=document.getElementById('btnSimpanTLText');
if(formTambahTL&&btnSimpanTL){
  formTambahTL.addEventListener('submit',async function(e){
    e.preventDefault();
    btnSimpanTL.disabled=true;
    btnSimpanTLText.innerHTML='<span class="tli-spinner"></span> Menyimpan...';
    var fd=new FormData(formTambahTL);
    var body={};
    fd.forEach(function(v,k){body[k]=v;});
    try{
      var r=await fetch(BASE+'/tindak-lanjut',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify(Object.assign({_csrf:CSRF_TOKEN},body))});
      var d=await r.json();
      if(d.success){
        location.reload();
      } else {
        alert(d.message||'Gagal menyimpan');
        btnSimpanTL.disabled=false;
        btnSimpanTLText.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
      }
    } catch(err){
      alert('Terjadi kesalahan jaringan');
      btnSimpanTL.disabled=false;
      btnSimpanTLText.innerHTML='<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
    }
  });
}

})();
</script>
