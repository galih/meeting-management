<?php
$baseUrl     = rtrim(BASE_URL, '/');
$user        = Auth::user();
$isAdminLike = Auth::hasRole('admin', 'sekretaris');

$priorityColor  = ['high' => 'red', 'medium' => 'orange', 'low' => 'green'];
$priorityLabel  = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$statusColorMap = ['pending' => 'secondary', 'in_progress' => 'blue', 'done' => 'green', 'cancelled' => 'red'];
$statusLabel    = ['pending' => 'Menunggu', 'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

$qp    = array_filter(['q' => $search ?? '', 'status' => $status ?? '', 'priority' => $priority ?? '', 'user_id' => $user_id ?? 0]);
$qpStr = $qp ? '&' . http_build_query($qp) : '';

$kanbanCols = [
    'pending'     => ['label' => 'Menunggu',    'dot' => 'secondary', 'items' => []],
    'in_progress' => ['label' => 'Berlangsung', 'dot' => 'blue',      'items' => []],
    'done'        => ['label' => 'Selesai',     'dot' => 'green',     'items' => []],
    'cancelled'   => ['label' => 'Dibatalkan',  'dot' => 'red',       'items' => []],
];
foreach ($tindakLanjutList as $tl) {
    $s = $tl['status'] ?? 'pending';
    if (isset($kanbanCols[$s])) $kanbanCols[$s]['items'][] = $tl;
}

$allUsersJson = json_encode(array_values(array_map(fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']], $allUsers ?? [])));
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="tli-flash tli-flash-success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="tli-flash-close" onclick="this.parentElement.remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="tli-flash tli-flash-danger">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="tli-flash-close" onclick="this.parentElement.remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- ==============================  PAGE HEADER  ============================== -->
<div class="tli-page-header mb-4">
  <div class="tli-page-header-inner">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h1 class="tli-page-title">Tindak Lanjut</h1>
        <p class="tli-page-sub">Kelola dan pantau semua tindak lanjut kegiatan</p>
      </div>
      <?php if ($isAdminLike): ?>
      <button class="btn tli-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Tindak Lanjut
      </button>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ==============================  STAT CARDS  ============================== -->
<div class="tli-stat-row mb-4">
  <?php
  $stats = [
    ['key'=>'total',       'label'=>'Total Tugas',  'dot'=>'brand',     'svg'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
    ['key'=>'pending',     'label'=>'Menunggu',     'dot'=>'secondary', 'svg'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ['key'=>'in_progress', 'label'=>'Berlangsung',  'dot'=>'blue',      'svg'=>'<polygon points="5 3 19 12 5 21 5 3"/>'],
    ['key'=>'done',        'label'=>'Selesai',      'dot'=>'green',     'svg'=>'<polyline points="20 6 9 17 4 12"/>'],
    ['key'=>'overdue',     'label'=>'Terlambat',    'dot'=>'red',       'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
  ];
  foreach ($stats as $sc): ?>
  <div class="tli-stat-card">
    <div class="tli-stat-icon tli-stat-icon-<?= $sc['dot'] ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $sc['svg'] ?></svg>
    </div>
    <div class="tli-stat-info">
      <div class="tli-stat-val" id="stat-<?= $sc['key'] ?>"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
      <div class="tli-stat-lbl"><?= $sc['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ==============================  TOOLBAR  ============================== -->
<div class="tli-toolbar mb-3">
  <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="tli-filter-form">

    <!-- Search -->
    <div class="tli-search-wrap">
      <svg class="tli-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
             class="tli-search-input" placeholder="Cari deskripsi...">
    </div>

    <!-- Selects -->
    <select name="status" class="tli-select" id="filter-status">
      <option value="">Semua Status</option>
      <?php foreach ($statusLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($status ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <select name="priority" class="tli-select">
      <option value="">Semua Prioritas</option>
      <?php foreach ($priorityLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($priority ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <?php if (Auth::hasRole('admin') && !empty($users)): ?>
    <select name="user_id" class="tli-select">
      <option value="">Semua User</option>
      <?php foreach ($users as $u): ?>
      <option value="<?= $u['id'] ?>" <?= ($user_id ?? 0) == $u['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($u['name']) ?>
      </option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <button type="submit" class="tli-btn-filter">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filter
    </button>

    <?php if (($status ?? '') || ($priority ?? '') || ($search ?? '') || ($user_id ?? 0)): ?>
    <a href="<?= $baseUrl ?>/tindak-lanjut" class="tli-btn-reset">Reset</a>
    <?php endif; ?>
  </form>

  <!-- View toggle -->
  <div class="tli-view-toggle">
    <button id="btn-view-table" class="tli-view-btn" title="Tampilan Tabel">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
      Tabel
    </button>
    <button id="btn-view-kanban" class="tli-view-btn" title="Tampilan Kanban">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="13" rx="1"/><rect x="17" y="3" width="5" height="9" rx="1"/></svg>
      Kanban
    </button>
  </div>
</div>

<!-- ==============================  VIEW: TABEL  ============================== -->
<div id="view-table">
  <div class="tli-card">
    <div class="table-responsive">
      <table class="tli-table">
        <thead>
          <tr>
            <th style="width:32%">Tugas</th>
            <th>Meeting</th>
            <th>PIC</th>
            <th>Deadline</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th style="width:80px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tindakLanjutList)): ?>
          <tr>
            <td colspan="7">
              <div class="tli-empty">
                <div class="tli-empty-icon">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <p>Belum ada tindak lanjut</p>
              </div>
            </td>
          </tr>
          <?php endif; ?>
          <?php foreach ($tindakLanjutList as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d')
                       && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
            $pc      = $priorityColor[$tl['priority']] ?? 'secondary';
            $sc      = $statusColorMap[$tl['status']] ?? 'secondary';
          ?>
          <tr class="<?= $overdue ? 'tli-row-overdue' : '' ?>" id="trow-<?= $tl['id'] ?>">
            <td>
              <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-task-link">
                <?= htmlspecialchars($tl['description']) ?>
              </a>
              <?php if ($overdue): ?>
              <span class="tli-badge tli-badge-red" style="margin-top:.25rem;display:inline-flex;">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Terlambat
              </span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-meeting-link">
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 38, '…')) ?>
              </a>
            </td>
            <td>
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="d-flex align-items-center gap-2">
                <span class="tli-avatar"><?= strtoupper(mb_substr($tl['assignee_name'],0,1)) ?></span>
                <span class="tli-assignee-name"><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php else: ?><span class="tli-text-muted">&mdash;</span><?php endif; ?>
            </td>
            <td class="tli-deadline <?= $overdue ? 'tli-deadline-over' : '' ?>">
              <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '&mdash;' ?>
            </td>
            <td>
              <span class="tli-badge tli-badge-<?= $pc ?>"><?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?></span>
            </td>
            <td>
              <?php if ($canEdit): ?>
              <select class="tli-status-select status-select" data-id="<?= $tl['id'] ?>"
                      data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status">
                <?php foreach ($statusLabel as $v => $l): ?>
                <option value="<?= $v ?>" <?= $tl['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <?php else: ?>
              <span class="tli-badge tli-badge-<?= $sc ?>"><?= $statusLabel[$tl['status']] ?? ucfirst($tl['status']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <div class="d-flex gap-1 justify-content-end align-items-center">
                <!-- Notes -->
                <button class="tli-icon-btn btn-notes"
                        data-id="<?= $tl['id'] ?>"
                        data-status="<?= $tl['status'] ?>"
                        data-can-done="<?= ($canEdit && $tl['status'] !== 'done') ? '1' : '0' ?>"
                        data-desc="<?= htmlspecialchars($tl['description']) ?>"
                        data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                        data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        title="Progress Note" data-bs-toggle="modal" data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <?php if ($nc > 0): ?><span class="tli-note-count" id="nbadge-<?= $tl['id'] ?>"><?= $nc ?></span><?php else: ?><span class="tli-note-count" id="nbadge-<?= $tl['id'] ?>" style="display:none"><?= $nc ?></span><?php endif; ?>
                </button>
                <!-- Detail -->
                <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-icon-btn tli-icon-btn-detail" title="Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if ($isAdminLike): ?>
                <button class="tli-icon-btn tli-icon-btn-del btn-del"
                        data-id="<?= $tl['id'] ?>"
                        data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                        title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (($totalPages ?? 1) > 1): ?>
    <div class="tli-pagination">
      <span class="tli-pag-info">
        Menampilkan <?= (($page - 1) * $perPage) + 1 ?>&ndash;<?= min($page * $perPage, $totalRows) ?>
        dari <strong><?= $totalRows ?></strong>
      </span>
      <div class="tli-pag-links">
        <a class="tli-pag-btn <?= $page <= 1 ? 'tli-pag-disabled' : '' ?>"
           href="?page=<?= $page - 1 ?><?= $qpStr ?>">&lsaquo;</a>
        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
        <a class="tli-pag-btn <?= $i === $page ? 'tli-pag-active' : '' ?>"
           href="?page=<?= $i ?><?= $qpStr ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="tli-pag-btn <?= $page >= $totalPages ? 'tli-pag-disabled' : '' ?>"
           href="?page=<?= $page + 1 ?><?= $qpStr ?>">&rsaquo;</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ==============================  VIEW: KANBAN  ============================== -->
<div id="view-kanban" style="display:none">
  <div class="row g-3" id="kanban-board">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="tli-kanban-col">
        <!-- Col header -->
        <div class="tli-kanban-header">
          <span class="tli-status-dot tli-dot-<?= $col['dot'] ?>"></span>
          <span class="tli-kanban-title"><?= $col['label'] ?></span>
          <span class="tli-kanban-count" id="kanban-count-<?= $colStatus ?>"><?= count($col['items']) ?></span>
        </div>
        <!-- Cards -->
        <div class="tli-kanban-body kanban-col"
             id="kanban-col-<?= $colStatus ?>"
             data-status="<?= $colStatus ?>">
          <?php if (empty($col['items'])): ?>
          <div class="tli-kanban-empty kanban-empty">Belum ada tugas</div>
          <?php endif; ?>

          <?php foreach ($col['items'] as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d')
                       && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $pc      = $priorityColor[$tl['priority']] ?? 'secondary';
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <div class="tli-kcard <?= $overdue ? 'tli-kcard-overdue' : '' ?> <?= in_array($tl['status'],['done','cancelled']) ? 'tli-kcard-faded' : '' ?>"
               id="kcard-<?= $tl['id'] ?>"
               data-id="<?= $tl['id'] ?>"
               data-status="<?= $tl['status'] ?>"
               data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
               style="cursor:<?= ($canEdit && $isAdminLike) ? 'grab' : 'default' ?>">

            <!-- Priority chip -->
            <div class="tli-kcard-top">
              <span class="tli-badge tli-badge-<?= $pc ?> tli-badge-sm"><?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?></span>
              <?php if ($overdue): ?>
              <span class="tli-badge tli-badge-red tli-badge-sm">Terlambat</span>
              <?php endif; ?>
            </div>

            <!-- Description -->
            <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-kcard-desc">
              <?= htmlspecialchars($tl['description']) ?>
            </a>

            <!-- Meta -->
            <div class="tli-kcard-meta">
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="tli-kcard-meta-item">
                <span class="tli-kcard-avatar"><?= strtoupper(mb_substr($tl['assignee_name'],0,1)) ?></span>
                <span><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php endif; ?>
              <?php if (!empty($tl['due_date'])): ?>
              <div class="tli-kcard-meta-item <?= $overdue ? 'tli-text-danger' : '' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span><?= date('d M Y', strtotime($tl['due_date'])) ?></span>
              </div>
              <?php endif; ?>
            </div>

            <!-- Meeting link + actions -->
            <div class="tli-kcard-footer">
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-kcard-meeting">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 28, '…')) ?>
              </a>
              <div class="d-flex gap-1 align-items-center">
                <button class="tli-kcard-btn btn-notes"
                        data-id="<?= $tl['id'] ?>"
                        data-status="<?= $tl['status'] ?>"
                        data-can-done="<?= ($canEdit && $tl['status'] !== 'done') ? '1' : '0' ?>"
                        data-desc="<?= htmlspecialchars($tl['description']) ?>"
                        data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                        data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        title="Notes" data-bs-toggle="modal" data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <?php if ($nc > 0): ?><span class="tli-note-count" id="nbadge-<?= $tl['id'] ?>"><?= $nc ?></span><?php else: ?><span class="tli-note-count" id="nbadge-<?= $tl['id'] ?>" style="display:none"><?= $nc ?></span><?php endif; ?>
                </button>
                <?php if ($isAdminLike): ?>
                <button class="tli-kcard-btn tli-kcard-btn-del btn-del"
                        data-id="<?= $tl['id'] ?>"
                        data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                        title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- ==============================  MODAL: TAMBAH TL  ============================== -->
<?php if ($isAdminLike): ?>
<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="formTambahTL">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="tli-modal-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </span>
            <h5 class="modal-title">Tambah Tindak Lanjut</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div id="tl-form-alert" class="alert alert-danger d-none"></div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Meeting / Kegiatan</label>
              <select name="meeting_id" class="form-select" required>
                <option value="">-- Pilih Meeting --</option>
                <?php
                $meetings = Database::query("SELECT id, title FROM meetings ORDER BY start_datetime DESC LIMIT 200");
                foreach ($meetings as $m):
                ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label required">Deskripsi Tugas</label>
              <textarea name="description" class="form-control" rows="3"
                        required placeholder="Tulis deskripsi tindak lanjut..."></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ditugaskan ke</label>
              <select name="assigned_to" class="form-select">
                <option value="">-- Belum ditugaskan --</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Deadline</label>
              <input type="date" name="due_date" class="form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label">Prioritas</label>
              <select name="priority" class="form-select">
                <option value="medium" selected>Sedang</option>
                <option value="high">Tinggi</option>
                <option value="low">Rendah</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn tli-btn-add" id="btn-save-tl">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ==============================  MODAL: PROGRESS NOTES  ============================== -->
<div class="modal modal-blur fade" id="modalNotes" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title">Progress Notes</h5>
          <div class="tli-text-muted" id="notes-desc" style="font-size:13px;max-width:360px;margin-top:.15rem;"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="notes-thread"
             style="max-height:320px;overflow-y:auto;padding:1rem;
                    display:flex;flex-direction:column;gap:.75rem;">
          <div class="tli-notes-loading">Memuat…</div>
        </div>
        <div id="done-bar" class="tli-done-bar" style="display:none">
          <button id="btn-mark-done" class="tli-btn-done">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Tandai Selesai
          </button>
        </div>
        <div class="tli-note-input-area">
          <div class="tli-note-input-wrap">
            <div style="flex:1;position:relative;">
              <textarea id="note-input" class="tli-note-textarea" rows="2"
                        placeholder="Tulis progress note… (@ mention, Ctrl+Enter kirim)"></textarea>
              <div id="mention-dropdown" class="tli-mention-drop" style="display:none"></div>
            </div>
            <button id="btn-send-note" class="tli-btn-send">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim
            </button>
          </div>
          <p class="tli-note-hint">Ketik <code>@nama</code> untuk mention &middot; <kbd>Ctrl+Enter</kbd> kirim</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ==============================  STYLES  ============================== -->
<style>
/* ── Flash ───────────────────────────────────────────────────────────── */
.tli-flash {
  display:flex;align-items:center;gap:.5rem;
  padding:.7rem 1rem;border-radius:10px;margin-bottom:1rem;
  font-size:13.5px;font-weight:500;
}
.tli-flash-success{background:rgba(47,107,64,.10);color:#1e7a2e;border:1px solid rgba(47,107,64,.2);}
.tli-flash-danger {background:rgba(168,37,21,.09);color:#a82515;border:1px solid rgba(168,37,21,.2);}
.tli-flash-close  {margin-left:auto;background:none;border:none;font-size:18px;cursor:pointer;line-height:1;opacity:.6;}
.tli-flash-close:hover{opacity:1;}

/* ── Page header ─────────────────────────────────────────────────────── */
.tli-page-header {
  background:linear-gradient(135deg,var(--brand) 0%,#9B2020 60%,#A83218 100%);
  border-radius:14px;overflow:hidden;position:relative;
  box-shadow:0 4px 20px rgba(123,28,28,.22);
}
.tli-page-header::after {
  content:'';position:absolute;top:-40px;right:-40px;
  width:180px;height:180px;border-radius:50%;
  background:rgba(201,168,76,.09);pointer-events:none;
}
.tli-page-header-inner{padding:1.3rem 1.6rem;}
.tli-page-title{font-size:clamp(18px,3vw,26px);font-weight:800;color:#fff;margin:0;letter-spacing:-.02em;}
.tli-page-sub  {font-size:13px;color:rgba(255,255,255,.7);margin:.25rem 0 0;}

/* ── Add button ──────────────────────────────────────────────────────── */
.tli-btn-add {
  background:var(--gold);border:none;color:#3D0A0A;
  font-size:13px;font-weight:700;border-radius:8px;
  padding:.5rem 1.1rem;display:inline-flex;align-items:center;gap:.4rem;
  cursor:pointer;transition:all .14s;
}
.tli-btn-add:hover{background:var(--gold-dark);color:#fff;box-shadow:0 3px 10px rgba(201,168,76,.3);}

/* ── Stat cards ──────────────────────────────────────────────────────── */
.tli-stat-row {
  display:grid;
  grid-template-columns:repeat(5,1fr);
  gap:.75rem;
}
@media(max-width:1023px){.tli-stat-row{grid-template-columns:repeat(3,1fr);}}
@media(max-width:575px) {.tli-stat-row{grid-template-columns:repeat(2,1fr);}}

.tli-stat-card {
  background:#fff;border:1px solid var(--border-light);
  border-radius:12px;padding:.85rem 1rem;
  display:flex;align-items:center;gap:.75rem;
  box-shadow:0 1px 4px rgba(0,0,0,.05);transition:box-shadow .14s;
}
.tli-stat-card:hover{box-shadow:0 4px 14px rgba(0,0,0,.09);}

.tli-stat-icon {
  width:40px;height:40px;border-radius:10px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;
}
.tli-stat-icon-brand     {background:rgba(123,28,28,.10);color:var(--brand);}
.tli-stat-icon-secondary {background:rgba(100,100,100,.10);color:#64748b;}
.tli-stat-icon-blue      {background:rgba(32,107,196,.10);color:#1557a0;}
.tli-stat-icon-green     {background:rgba(47,107,64,.10);color:#1e7a2e;}
.tli-stat-icon-red       {background:rgba(168,37,21,.10);color:#a82515;}

.tli-stat-val{font-size:22px;font-weight:800;color:var(--brand);line-height:1;}
.tli-stat-lbl{font-size:11.5px;color:var(--text-muted);font-weight:500;margin-top:.1rem;}

/* ── Toolbar ─────────────────────────────────────────────────────────── */
.tli-toolbar {
  display:flex;flex-wrap:wrap;align-items:center;
  justify-content:space-between;gap:.6rem;
  background:#fff;border:1px solid var(--border-light);
  border-radius:12px;padding:.7rem 1rem;
  box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.tli-filter-form{display:flex;flex-wrap:wrap;align-items:center;gap:.5rem;flex:1;}

.tli-search-wrap{position:relative;}
.tli-search-icon{position:absolute;left:.65rem;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;}
.tli-search-input{
  padding:.38rem .75rem .38rem 2rem;
  border:1.5px solid var(--border);border-radius:8px;
  font-size:13px;width:200px;transition:border-color .14s;
}
.tli-search-input:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(123,28,28,.08);}

.tli-select{
  padding:.38rem .7rem;border:1.5px solid var(--border);
  border-radius:8px;font-size:13px;background:#fff;
  cursor:pointer;transition:border-color .14s;
}
.tli-select:focus{outline:none;border-color:var(--brand);}

.tli-btn-filter{
  background:var(--brand);border:none;color:#fff;
  font-size:13px;font-weight:600;border-radius:8px;
  padding:.4rem .9rem;display:inline-flex;align-items:center;gap:.35rem;
  cursor:pointer;transition:all .14s;
}
.tli-btn-filter:hover{background:var(--brand-dark);}

.tli-btn-reset{
  font-size:13px;font-weight:600;color:#a82515;
  text-decoration:none;padding:.4rem .6rem;
  border-radius:8px;transition:background .13s;
}
.tli-btn-reset:hover{background:rgba(168,37,21,.08);}

/* View toggle */
.tli-view-toggle{display:flex;border:1.5px solid var(--border);border-radius:8px;overflow:hidden;}
.tli-view-btn{
  background:none;border:none;color:var(--text-muted);
  font-size:13px;font-weight:600;padding:.38rem .85rem;
  display:inline-flex;align-items:center;gap:.35rem;
  cursor:pointer;transition:all .13s;
}
.tli-view-btn:not(:last-child){border-right:1.5px solid var(--border);}
.tli-view-btn.active,.tli-view-btn:hover{background:var(--brand);color:#fff;}

/* ── Card shell ──────────────────────────────────────────────────────── */
.tli-card {
  border:1px solid var(--border-light);border-radius:14px;
  overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06);
  background:#fff;
}

/* ── Table ───────────────────────────────────────────────────────────── */
.tli-table{width:100%;border-collapse:collapse;font-size:13.5px;}
.tli-table thead th{
  background:#faf6ef;border-bottom:2px solid var(--border);
  padding:.6rem 1rem;font-size:10.5px;font-weight:700;
  letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);
  white-space:nowrap;
}
.tli-table tbody td{
  padding:.75rem 1rem;border-bottom:1px solid var(--border-light);
  vertical-align:middle;
}
.tli-table tbody tr:last-child td{border-bottom:none;}
.tli-table tbody tr:hover{background:#faf6ef;}
.tli-row-overdue td:first-child{border-left:3px solid #a82515;}
.tli-row-overdue{background:#fff7f7 !important;}
.tli-row-overdue:hover{background:#fee9e9 !important;}

.tli-task-link{
  font-size:13px;font-weight:600;color:var(--text-main);
  text-decoration:none;display:-webkit-box;
  -webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.tli-task-link:hover{color:var(--brand);}
.tli-meeting-link{font-size:12px;color:var(--brand);text-decoration:none;font-weight:500;}
.tli-meeting-link:hover{text-decoration:underline;}
.tli-assignee-name{font-size:13px;color:var(--text-main);}
.tli-deadline    {font-size:13px;color:var(--text-muted);white-space:nowrap;}
.tli-deadline-over{color:#a82515;font-weight:700;}
.tli-text-muted  {color:var(--text-muted);}
.tli-text-danger {color:#a82515;}

/* Avatar */
.tli-avatar{
  width:26px;height:26px;border-radius:50%;
  background:var(--brand);color:#fff;
  font-size:11px;font-weight:700;
  display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;
}

/* Icon buttons */
.tli-icon-btn{
  background:none;border:none;cursor:pointer;
  width:30px;height:30px;border-radius:7px;
  display:inline-flex;align-items:center;justify-content:center;
  color:var(--text-muted);transition:all .13s;position:relative;
}
.tli-icon-btn:hover{background:rgba(123,28,28,.08);color:var(--brand);}
.tli-icon-btn-detail{border:1.5px solid var(--brand);color:var(--brand);}
.tli-icon-btn-detail:hover{background:var(--brand);color:#fff;}
.tli-icon-btn-del:hover{background:rgba(168,37,21,.1);color:#a82515;}

/* Note count bubble */
.tli-note-count{
  position:absolute;top:2px;right:2px;
  background:var(--brand);color:#fff;
  font-size:9px;font-weight:700;
  min-width:14px;height:14px;border-radius:7px;
  display:flex;align-items:center;justify-content:center;padding:0 2px;
}

/* Status select */
.tli-status-select{
  font-size:12.5px;font-weight:600;border:1.5px solid var(--border);
  border-radius:7px;padding:.28rem .55rem;
  background:#fff;cursor:pointer;transition:border-color .13s;
}
.tli-status-select:focus{outline:none;border-color:var(--brand);}

/* Badge */
.tli-badge{
  display:inline-flex;align-items:center;gap:.22rem;
  font-size:11.5px;font-weight:700;padding:.28em .7em;
  border-radius:20px;white-space:nowrap;
}
.tli-badge-sm{font-size:10px;padding:.22em .55em;}
.tli-badge-red      {background:rgba(168,37,21,.10);color:#a82515;}
.tli-badge-orange   {background:rgba(201,168,76,.15);color:#7a5800;}
.tli-badge-green    {background:rgba(47,107,64,.10);color:#1e7a2e;}
.tli-badge-blue     {background:rgba(32,107,196,.10);color:#1557a0;}
.tli-badge-secondary{background:rgba(100,100,100,.10);color:#64748b;}

/* Pagination */
.tli-pagination{
  display:flex;align-items:center;justify-content:space-between;
  padding:.65rem 1rem;border-top:1px solid var(--border-light);
  background:#faf6ef;
}
.tli-pag-info{font-size:13px;color:var(--text-muted);}
.tli-pag-links{display:flex;gap:.25rem;}
.tli-pag-btn{
  display:inline-flex;align-items:center;justify-content:center;
  width:32px;height:32px;border-radius:7px;
  font-size:13px;font-weight:600;text-decoration:none;
  color:var(--text-main);border:1.5px solid var(--border);
  background:#fff;transition:all .13s;
}
.tli-pag-btn:hover{border-color:var(--brand);color:var(--brand);}
.tli-pag-active{background:var(--brand);color:#fff !important;border-color:var(--brand-dark);}
.tli-pag-disabled{opacity:.4;pointer-events:none;}

/* ── Kanban ──────────────────────────────────────────────────────────── */
.tli-kanban-col{
  background:#faf6ef;border:1px solid var(--border-light);
  border-radius:14px;overflow:hidden;
  box-shadow:0 1px 4px rgba(0,0,0,.05);
}
.tli-kanban-header{
  display:flex;align-items:center;gap:.5rem;
  padding:.65rem .9rem;border-bottom:2px solid var(--border-light);
  background:#fff;
}
.tli-kanban-title{font-size:13px;font-weight:700;color:var(--text-main);flex:1;}
.tli-kanban-count{
  background:var(--brand);color:#fff;
  font-size:10.5px;font-weight:700;
  padding:.1em .55em;border-radius:20px;
}
.tli-kanban-body{
  padding:.6rem;min-height:180px;
  overflow-y:auto;max-height:calc(100vh - 340px);
}
.tli-kanban-empty{
  text-align:center;color:var(--text-muted);
  font-size:13px;padding:2rem 1rem;
  border:2px dashed var(--border);border-radius:10px;
  margin:.25rem;
}

/* Kanban cards */
.tli-kcard{
  background:#fff;border:1px solid var(--border-light);
  border-radius:10px;padding:.7rem .8rem;margin-bottom:.5rem;
  transition:box-shadow .13s,transform .13s;
}
.tli-kcard:hover{box-shadow:0 4px 14px rgba(0,0,0,.1);transform:translateY(-1px);}
.tli-kcard-overdue{border-left:3px solid #a82515;}
.tli-kcard-faded{opacity:.7;}

.tli-kcard-top{display:flex;gap:.3rem;flex-wrap:wrap;margin-bottom:.4rem;}

.tli-kcard-desc{
  display:block;font-size:12.5px;font-weight:600;
  color:var(--text-main);text-decoration:none;line-height:1.4;
  margin-bottom:.45rem;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.tli-kcard-desc:hover{color:var(--brand);}

.tli-kcard-meta{
  display:flex;flex-direction:column;gap:.2rem;
  margin-bottom:.45rem;
}
.tli-kcard-meta-item{
  display:flex;align-items:center;gap:.35rem;
  font-size:11.5px;color:var(--text-muted);
}
.tli-kcard-avatar{
  width:18px;height:18px;border-radius:50%;
  background:var(--brand);color:#fff;
  font-size:9px;font-weight:700;
  display:inline-flex;align-items:center;justify-content:center;
}

.tli-kcard-footer{
  display:flex;align-items:center;justify-content:space-between;
  padding-top:.4rem;border-top:1px solid var(--border-light);
}
.tli-kcard-meeting{
  font-size:10.5px;color:var(--brand);text-decoration:none;
  display:flex;align-items:center;gap:.3rem;font-weight:500;
  white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;
}
.tli-kcard-meeting:hover{text-decoration:underline;}

.tli-kcard-btn{
  background:none;border:none;cursor:pointer;
  width:26px;height:26px;border-radius:6px;
  display:inline-flex;align-items:center;justify-content:center;
  color:var(--text-muted);transition:all .12s;position:relative;
}
.tli-kcard-btn:hover{background:rgba(123,28,28,.1);color:var(--brand);}
.tli-kcard-btn-del:hover{background:rgba(168,37,21,.1);color:#a82515;}

/* Status dot */
.tli-status-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.tli-dot-secondary{background:#94a3b8;}
.tli-dot-blue     {background:#2d6ec4;}
.tli-dot-green    {background:#1e7a2e;}
.tli-dot-red      {background:#a82515;}

/* Sortable drag */
.sortable-ghost{opacity:.3;background:rgba(123,28,28,.06);border:2px dashed var(--brand);}
.sortable-drag {box-shadow:0 8px 24px rgba(0,0,0,.18) !important;transform:rotate(1.5deg);}

/* ── Modal icon ──────────────────────────────────────────────────────── */
.tli-modal-icon{
  width:30px;height:30px;background:var(--brand-light);border-radius:7px;
  display:inline-flex;align-items:center;justify-content:center;
  color:var(--brand);flex-shrink:0;
}

/* ── Notes modal ─────────────────────────────────────────────────────── */
.tli-notes-loading{text-align:center;color:var(--text-muted);font-size:13px;padding:2rem;}

.tli-done-bar{
  border-top:1px solid var(--border-light);
  padding:.6rem 1rem;background:#f0fdf4;
}
.tli-btn-done{
  width:100%;background:#1e7a2e;border:none;color:#fff;
  font-size:13px;font-weight:700;border-radius:8px;
  padding:.5rem;display:flex;align-items:center;justify-content:center;gap:.4rem;
  cursor:pointer;transition:all .14s;
}
.tli-btn-done:hover{background:#16602a;}

.tli-note-input-area{
  padding:.85rem;border-top:1px solid var(--border-light);
  background:#faf6ef;
}
.tli-note-input-wrap{display:flex;gap:.6rem;align-items:flex-end;}
.tli-note-textarea{
  width:100%;resize:vertical;min-height:56px;
  border:1.5px solid var(--border);border-radius:8px;
  font-size:13.5px;padding:.5rem .75rem;transition:border-color .14s;
}
.tli-note-textarea:focus{outline:none;border-color:var(--brand);box-shadow:0 0 0 3px rgba(123,28,28,.1);}
.tli-btn-send{
  background:var(--brand);border:none;color:#fff;
  font-size:13px;font-weight:700;border-radius:8px;
  padding:.5rem 1rem;display:inline-flex;align-items:center;gap:.35rem;
  cursor:pointer;white-space:nowrap;flex-shrink:0;transition:all .14s;
}
.tli-btn-send:hover{background:var(--brand-dark);}
.tli-btn-send:disabled{opacity:.6;cursor:default;}
.tli-note-hint{font-size:11px;color:var(--text-muted);margin:.4rem 0 0;}
.tli-note-hint code{background:rgba(123,28,28,.08);color:var(--brand);border-radius:3px;padding:0 3px;}
.tli-note-hint kbd{background:#eee;border-radius:3px;padding:1px 4px;font-size:10px;}

/* Note thread items */
.tli-note-thread-item{
  display:flex;gap:.6rem;
}
.tli-nt-avatar{
  width:30px;height:30px;border-radius:50%;
  background:var(--brand);color:#fff;font-size:12px;font-weight:700;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.tli-nt-body{flex:1;min-width:0;}
.tli-nt-meta{
  display:flex;justify-content:space-between;align-items:baseline;
  gap:.5rem;margin-bottom:.2rem;
}
.tli-nt-author{font-size:13px;font-weight:700;color:var(--text-main);}
.tli-nt-time  {font-size:11px;color:var(--text-muted);}
.tli-nt-text  {font-size:13.5px;color:var(--text-main);line-height:1.55;white-space:pre-wrap;}
.tli-nt-del{
  background:none;border:none;cursor:pointer;
  color:var(--text-muted);font-size:14px;padding:.1rem .3rem;
  border-radius:4px;transition:all .12s;
}
.tli-nt-del:hover{color:#a82515;background:rgba(168,37,21,.08);}

/* @mention dropdown */
.tli-mention-drop{
  position:absolute;bottom:calc(100% + 4px);left:0;
  background:#fff;border:1px solid var(--border);border-radius:8px;
  box-shadow:0 4px 16px rgba(0,0,0,.12);
  min-width:180px;max-height:180px;overflow-y:auto;z-index:9999;
}
.tli-mention-item{
  padding:.5rem .85rem;font-size:13px;cursor:pointer;
  transition:background .11s;
}
.tli-mention-item:hover,.tli-mention-item.focused{background:var(--brand-light);color:var(--brand);}
</style>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function(){
'use strict';
var BASE          = <?= json_encode($baseUrl) ?>;
var ALL_USERS     = <?= $allUsersJson ?>;
var IS_ADMIN_LIKE = <?= $isAdminLike ? 'true' : 'false' ?>;
var ACTIVE_UID    = <?= (int)($user_id ?? 0) ?>;

function escHtml(s){
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function updateStatCards(s){
  if(!s) return;
  ['total','pending','in_progress','done','overdue'].forEach(function(k){
    var el=document.getElementById('stat-'+k);
    if(el && s[k]!==undefined) el.textContent=s[k];
  });
}
function updateNoteBadge(id,count){
  document.querySelectorAll('#nbadge-'+id).forEach(function(el){
    if(count>0){el.style.display='flex';el.textContent=count;}
    else{el.style.display='none';}
  });
}
async function postStatus(url,status){
  var r=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({status:status,user_id:ACTIVE_UID})});
  return r.json();
}

/* ── View toggle ────────────────────────────────────── */
var btnTable  = document.getElementById('btn-view-table');
var btnKanban = document.getElementById('btn-view-kanban');
var divTable  = document.getElementById('view-table');
var divKanban = document.getElementById('view-kanban');

function setView(v){
  var k=v==='kanban';
  divTable.style.display=k?'none':'';
  divKanban.style.display=k?'':'';
  btnTable.classList.toggle('active',!k);
  btnKanban.classList.toggle('active',k);
  divKanban.style.display=k?'':'none';
  localStorage.setItem('tl_view',v);
  var fs=document.getElementById('filter-status');
  if(fs) fs.disabled=k;
}
btnTable.addEventListener('click',function(){setView('table');});
btnKanban.addEventListener('click',function(){setView('kanban');});
setView(localStorage.getItem('tl_view')||'kanban');

/* ── Status select (table) ───────────────────────── */
document.querySelectorAll('.status-select').forEach(function(sel){
  sel.addEventListener('change',async function(){
    var d=await postStatus(this.dataset.url,this.value);
    if(!d.success){alert(d.message||'Gagal update status');return;}
    updateStatCards(d.summary);
    var tr=this.closest('tr');
    if(tr) tr.style.opacity=['done','cancelled'].indexOf(this.value)>=0?'0.5':'';
  });
});

/* ── Delete ───────────────────────────────────────── */
function bindDel(){
  document.querySelectorAll('.btn-del').forEach(function(btn){
    if(btn._bound) return; btn._bound=true;
    btn.addEventListener('click',async function(){
      if(!confirm('Hapus tindak lanjut ini?')) return;
      var r=await fetch(this.dataset.url,{method:'POST'});
      var d=await r.json();
      if(!d.success){alert(d.message||'Gagal hapus');return;}
      var trow=document.getElementById('trow-'+this.dataset.id);
      var kcard=document.getElementById('kcard-'+this.dataset.id);
      if(trow) trow.remove();
      if(kcard){var col=kcard.closest('.kanban-col');kcard.remove();updateColCount(col);}
    });
  });
}
bindDel();

/* ── Kanban col count ────────────────────────────── */
function updateColCount(colEl){
  if(!colEl) return;
  var count=colEl.querySelectorAll('.tli-kcard').length;
  var badge=document.getElementById('kanban-count-'+colEl.dataset.status);
  if(badge) badge.textContent=count;
  var empty=colEl.querySelector('.kanban-empty');
  if(count===0&&!empty){
    var e=document.createElement('div');
    e.className='tli-kanban-empty kanban-empty';
    e.textContent='Belum ada tugas';
    colEl.appendChild(e);
  } else if(count>0&&empty) empty.remove();
}

/* ── SortableJS ──────────────────────────────────── */
document.querySelectorAll('.kanban-col').forEach(function(col){
  Sortable.create(col,{
    group:'kanban',animation:150,
    ghostClass:'sortable-ghost',dragClass:'sortable-drag',
    disabled:!IS_ADMIN_LIKE,
    filter:'.btn-notes,.btn-del,.tli-kcard-btn',
    onEnd:async function(evt){
      var card=evt.item,newCol=evt.to,newStatus=newCol.dataset.status,oldStatus=evt.from.dataset.status;
      if(newStatus===oldStatus) return;
      card.style.opacity='0.5';
      var d=await postStatus(card.dataset.url,newStatus);
      if(d.success){
        card.dataset.status=newStatus;
        updateStatCards(d.summary);
        updateColCount(evt.from);updateColCount(newCol);
        card.style.opacity=['done','cancelled'].indexOf(newStatus)>=0?'0.7':'';
        card.classList.toggle('tli-kcard-faded',['done','cancelled'].indexOf(newStatus)>=0);
        var nb=card.querySelector('.btn-notes');
        if(nb){nb.dataset.status=newStatus;nb.dataset.canDone=newStatus!=='done'?'1':'0';}
      } else {
        alert(d.message||'Gagal update');
        evt.from.insertBefore(card,evt.from.children[evt.oldIndex]||null);
        card.style.opacity='';
        updateColCount(evt.from);updateColCount(newCol);
      }
    }
  });
});

/* ── Modal Tambah TL ──────────────────────────────── */
var formTambah=document.getElementById('formTambahTL');
if(formTambah){
  formTambah.addEventListener('submit',async function(e){
    e.preventDefault();
    var alertEl=document.getElementById('tl-form-alert');
    alertEl.classList.add('d-none');
    var btn=document.getElementById('btn-save-tl');
    btn.disabled=true;
    var fd=new FormData(formTambah),obj={};
    fd.forEach(function(v,k){obj[k]=v;});
    var r=await fetch(BASE+'/tindak-lanjut',{
      method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(obj)
    });
    var d=await r.json();
    btn.disabled=false;
    if(d.success){
      bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTambahTL')).hide();
      formTambah.reset();
      window.location.reload();
    } else {
      alertEl.textContent=d.message||'Gagal menyimpan.';
      alertEl.classList.remove('d-none');
    }
  });
}

/* ── Progress Notes modal ────────────────────────── */
var _tlId=0,_noteUrl='',_statusUrl='',_delBase='';

function renderNote(n){
  var d=new Date(n.created_at);
  var ts=d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})
       +' '+d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  var text=escHtml(n.note).replace(/@([\w ]+)/g,'<span style="color:var(--brand);font-weight:600;">@$1</span>');
  var wrap=document.createElement('div');
  wrap.className='tli-note-thread-item';
  wrap.dataset.noteid=n.id;
  wrap.innerHTML=
    '<div class="tli-nt-avatar">'+escHtml(n.author_name.charAt(0).toUpperCase())+'</div>'
    +'<div class="tli-nt-body">'
    +'<div class="tli-nt-meta">'
    +'<span class="tli-nt-author">'+escHtml(n.author_name)+'</span>'
    +'<span class="tli-nt-time">'+ts+(n.can_delete?'<button class="tli-nt-del btn-del-note" data-note-id="'+n.id+'" style="margin-left:.4rem">&#215;</button>':'')+'</span>'
    +'</div>'
    +'<div class="tli-nt-text">'+text+'</div>'
    +'</div>';
  if(n.can_delete){
    wrap.querySelector('.btn-del-note').addEventListener('click',deleteNote);
  }
  return wrap;
}

async function loadNotes(url){
  var thread=document.getElementById('notes-thread');
  thread.innerHTML='<div class="tli-notes-loading">Memuat…</div>';
  var notes=await(await fetch(url)).json();
  thread.innerHTML='';
  if(!notes.length){
    thread.innerHTML='<div class="tli-notes-loading">Belum ada progress note.</div>';
    return;
  }
  notes.forEach(function(n){thread.appendChild(renderNote(n));});
  thread.scrollTop=thread.scrollHeight;
}

async function deleteNote(e){
  if(!confirm('Hapus note ini?')) return;
  var noteId=e.currentTarget.dataset.noteId;
  var d=await(await fetch(_delBase+'/'+noteId+'/delete',{method:'POST'})).json();
  if(d.success){
    e.currentTarget.closest('[data-noteid]').remove();
    if(d.note_count!==undefined) updateNoteBadge(_tlId,d.note_count);
    if(!document.querySelector('.tli-note-thread-item')){
      document.getElementById('notes-thread').innerHTML='<div class="tli-notes-loading">Belum ada progress note.</div>';
    }
  } else alert(d.message||'Gagal hapus');
}

document.querySelectorAll('.btn-notes').forEach(function(btn){
  btn.addEventListener('click',function(){
    _tlId=parseInt(this.dataset.id);
    _noteUrl=this.dataset.urlPost;
    _statusUrl=this.dataset.urlStatus;
    _delBase=this.dataset.deleteBase;
    document.getElementById('notes-desc').textContent=this.dataset.desc;
    document.getElementById('note-input').value='';
    document.getElementById('done-bar').style.display=this.dataset.canDone==='1'?'':'none';
    loadNotes(this.dataset.urlGet);
  });
});

var btnMarkDone=document.getElementById('btn-mark-done');
if(btnMarkDone){
  btnMarkDone.addEventListener('click',async function(){
    if(!confirm('Tandai tugas ini sebagai Selesai?')) return;
    this.disabled=true;
    var d=await postStatus(_statusUrl,'done');
    this.disabled=false;
    if(!d.success){alert(d.message||'Gagal');return;}
    updateStatCards(d.summary);
    var kcard=document.getElementById('kcard-'+_tlId);
    if(kcard){
      var oldCol=kcard.closest('.kanban-col');
      var newCol=document.getElementById('kanban-col-done');
      if(oldCol&&newCol&&oldCol!==newCol){newCol.appendChild(kcard);updateColCount(oldCol);updateColCount(newCol);}
      kcard.style.opacity='0.7';kcard.dataset.status='done';
      kcard.classList.add('tli-kcard-faded');
    }
    var trow=document.getElementById('trow-'+_tlId);
    if(trow){var sel=trow.querySelector('.status-select');if(sel)sel.value='done';trow.style.opacity='0.5';}
    document.getElementById('done-bar').style.display='none';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalNotes')).hide();
  });
}

var btnSend=document.getElementById('btn-send-note');
async function sendNote(){
  var note=document.getElementById('note-input').value.trim();
  if(!note) return;
  btnSend.disabled=true;
  var d=await(await fetch(_noteUrl,{
    method:'POST',headers:{'Content-Type':'application/json'},
    body:JSON.stringify({note:note})
  })).json();
  btnSend.disabled=false;
  if(!d.success){alert(d.message||'Gagal kirim');return;}
  document.getElementById('note-input').value='';
  hideMention();
  var thread=document.getElementById('notes-thread');
  var empty=thread.querySelector('.tli-notes-loading');
  if(empty) empty.remove();
  thread.appendChild(renderNote(d.note));
  thread.scrollTop=thread.scrollHeight;
  if(d.note_count!==undefined) updateNoteBadge(_tlId,d.note_count);
}
if(btnSend) btnSend.addEventListener('click',sendNote);

var noteInput=document.getElementById('note-input');
if(noteInput){
  noteInput.addEventListener('keydown',function(e){
    if(e.key==='Escape'){hideMention();return;}
    if(e.key==='ArrowDown'){moveFocus(1);e.preventDefault();return;}
    if(e.key==='ArrowUp'){moveFocus(-1);e.preventDefault();return;}
    if(e.key==='Enter'&&isMentionOpen()){selectFocused();e.preventDefault();return;}
    if(e.ctrlKey&&e.key==='Enter'){hideMention();sendNote();}
  });
}

/* ── @mention ────────────────────────────────────────── */
var _mStart=-1,mDrop=document.getElementById('mention-dropdown');
function isMentionOpen(){return mDrop&&mDrop.style.display!=='none';}
function hideMention(){if(!mDrop)return;mDrop.style.display='none';mDrop.innerHTML='';_mStart=-1;}
function showMention(items){
  if(!mDrop) return;
  mDrop.innerHTML='';
  if(!items.length){hideMention();return;}
  items.forEach(function(u,i){
    var el=document.createElement('div');
    el.className='tli-mention-item';
    el.textContent=u.name;
    el.dataset.name=u.name;
    el.addEventListener('mousedown',function(e){e.preventDefault();insertMention(u.name);});
    el.addEventListener('mouseenter',function(){setFocus(i);});
    mDrop.appendChild(el);
  });
  mDrop.style.display='block';
}
function setFocus(idx){
  mDrop.querySelectorAll('.tli-mention-item').forEach(function(el,i){
    el.classList.toggle('focused',i===idx);
  });
}
function moveFocus(dir){
  var items=Array.from(mDrop.querySelectorAll('.tli-mention-item'));
  var cur=items.findIndex(function(el){return el.classList.contains('focused');});
  setFocus(Math.max(0,Math.min(items.length-1,cur+dir)));
}
function selectFocused(){
  var f=mDrop.querySelector('.focused')||mDrop.querySelector('.tli-mention-item');
  if(f) insertMention(f.dataset.name);
}
function insertMention(name){
  var ta=document.getElementById('note-input'),val=ta.value;
  var before=val.substring(0,_mStart),after=val.substring(ta.selectionStart);
  ta.value=before+'@'+name+' '+after;
  var p=before.length+name.length+2;
  ta.setSelectionRange(p,p);
  hideMention();ta.focus();
}
if(noteInput){
  noteInput.addEventListener('input',function(){
    var val=this.value,cur=this.selectionStart;
    var m=val.substring(0,cur).match(/@([\w ]*)$/);
    if(!m){hideMention();return;}
    _mStart=val.substring(0,cur).lastIndexOf('@');
    var q=m[1].toLowerCase();
    var res=ALL_USERS.filter(function(u){return u.name.toLowerCase().indexOf(q)>=0;}).slice(0,6);
    showMention(res);
  });
}

})();
</script>
