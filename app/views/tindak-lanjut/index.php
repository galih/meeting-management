<?php
$baseUrl     = rtrim(BASE_URL, '/');
$user        = Auth::user();
$isAdminLike = Auth::hasRole('admin', 'sekretaris');

$priorityColor = ['high' => 'danger', 'medium' => 'warning', 'low'  => 'success'];
$priorityLabel = ['high' => 'Tinggi', 'medium' => 'Sedang',  'low'  => 'Rendah'];
$statusColor   = ['pending' => 'secondary', 'in_progress' => 'info', 'done' => 'success', 'cancelled' => 'danger'];
$statusLabel   = ['pending' => 'Menunggu',  'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

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
?>

<!-- ═══════════════════════════  PAGE HEADER  ═══════════════════════════ -->
<div class="tli-page-header mb-4">
  <div class="tli-page-header-inner d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <h1 class="tli-page-title">Tindak Lanjut</h1>
      <p class="tli-page-sub">Kelola dan pantau semua tindak lanjut kegiatan</p>
    </div>
    <?php if ($isAdminLike): ?>
    <button class="btn tli-btn-gold" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Tindak Lanjut
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════════════════  STAT CARDS  ═══════════════════════════ -->
<div class="tli-stat-row mb-4">
  <?php
  $statDefs = [
    ['key'=>'total',       'label'=>'Total Tugas',  'icon_color'=>'brand',
     'svg'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
    ['key'=>'pending',     'label'=>'Menunggu',     'icon_color'=>'secondary',
     'svg'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
    ['key'=>'in_progress', 'label'=>'Berlangsung',  'icon_color'=>'info',
     'svg'=>'<polygon points="5 3 19 12 5 21 5 3"/>'],
    ['key'=>'done',        'label'=>'Selesai',      'icon_color'=>'success',
     'svg'=>'<polyline points="20 6 9 17 4 12"/>'],
    ['key'=>'overdue',     'label'=>'Terlambat',    'icon_color'=>'danger',
     'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
  ];
  foreach ($statDefs as $sc): ?>
  <div class="tli-stat-card">
    <span class="tli-stat-icon tli-stat-icon-<?= $sc['icon_color'] ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $sc['svg'] ?></svg>
    </span>
    <div>
      <div class="tli-stat-val" id="stat-<?= $sc['key'] ?>"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
      <div class="tli-stat-lbl"><?= $sc['label'] ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ═══════════════════════════  TOOLBAR  ═══════════════════════════════ -->
<div class="tli-toolbar mb-3">
  <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="tli-filter-form">
    <div class="tli-search-wrap">
      <svg class="tli-search-ico" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
             class="tli-input tli-search-input" placeholder="Cari deskripsi…">
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

    <?php if (Auth::hasRole('admin') && !empty($users)): ?>
    <select name="user_id" class="tli-input tli-select">
      <option value="">Semua User</option>
      <?php foreach ($users as $u): ?>
      <option value="<?= $u['id'] ?>" <?= ($user_id ?? 0) == $u['id'] ? 'selected' : '' ?>>
        <?= htmlspecialchars($u['name']) ?>
      </option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <button type="submit" class="btn tli-btn-brand-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filter
    </button>

    <?php if (($status ?? '') || ($priority ?? '') || ($search ?? '') || ($user_id ?? 0)): ?>
    <a href="<?= $baseUrl ?>/tindak-lanjut" class="tli-btn-reset">Reset</a>
    <?php endif; ?>
  </form>

  <!-- View toggle -->
  <div class="tli-view-toggle" role="group" aria-label="Ganti tampilan">
    <button id="btn-view-table" class="tli-vbtn" title="Tampilan Tabel">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/><line x1="9" y1="3" x2="9" y2="21"/></svg>
      Tabel
    </button>
    <button id="btn-view-kanban" class="tli-vbtn" title="Tampilan Kanban">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="5" height="18" rx="1"/><rect x="10" y="3" width="5" height="13" rx="1"/><rect x="17" y="3" width="5" height="9" rx="1"/></svg>
      Kanban
    </button>
  </div>
</div>

<!-- ═══════════════════════════  VIEW: TABEL  ═══════════════════════════ -->
<div id="view-table">
  <div class="card card-body p-0" style="border-radius:14px;overflow:hidden;border:1px solid var(--border-light);box-shadow:0 2px 10px rgba(0,0,0,.06);">
    <div class="table-responsive">
      <table class="table table-vcenter table-hover card-table mb-0 tli-table">
        <thead>
          <tr>
            <th style="width:32%">Tugas</th>
            <th>Meeting</th>
            <th>PIC</th>
            <th>Deadline</th>
            <th>Prioritas</th>
            <th>Status</th>
            <th style="width:90px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tindakLanjutList)): ?>
          <tr><td colspan="7">
            <div class="tli-empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              <p>Belum ada tindak lanjut</p>
            </div>
          </td></tr>
          <?php endif; ?>
          <?php foreach ($tindakLanjutList as $tl):
            $overdue = !empty($tl['due_date'])
                    && $tl['due_date'] < date('Y-m-d')
                    && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <tr class="<?= $overdue ? 'tli-row-overdue' : '' ?>" id="trow-<?= $tl['id'] ?>">
            <td>
              <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-task-link">
                <?= htmlspecialchars($tl['description']) ?>
              </a>
              <?php if ($overdue): ?>
              <span class="badge bg-danger-lt tli-badge-sm mt-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Terlambat
              </span>
              <?php endif; ?>
            </td>
            <td>
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-meeting-link">
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 40, '…')) ?>
              </a>
            </td>
            <td>
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="d-flex align-items-center gap-2">
                <span class="tli-avatar"><?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?></span>
                <span class="tli-assignee"><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </td>
            <td class="text-nowrap <?= $overdue ? 'tli-overdue-text' : 'text-muted' ?>">
              <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
            </td>
            <td>
              <span class="badge bg-<?= $priorityColor[$tl['priority']] ?? 'secondary' ?>-lt tli-badge-sm">
                <?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?>
              </span>
            </td>
            <td>
              <?php if ($canEdit): ?>
              <select class="tli-status-sel status-select" data-id="<?= $tl['id'] ?>"
                      data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status">
                <?php foreach ($statusLabel as $v => $l): ?>
                <option value="<?= $v ?>" <?= $tl['status'] === $v ? 'selected' : '' ?>><?= $l ?></option>
                <?php endforeach; ?>
              </select>
              <?php else: ?>
              <span class="badge bg-<?= $statusColor[$tl['status']] ?? 'secondary' ?>-lt tli-badge-sm">
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
                        title="Progress Notes"
                        data-bs-toggle="modal" data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span class="tli-note-bubble" id="nbadge-<?= $tl['id'] ?>" <?= $nc < 1 ? 'style="display:none"' : '' ?>><?= $nc ?></span>
                </button>
                <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-ico-btn tli-ico-detail" title="Detail">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>
                <?php if ($isAdminLike): ?>
                <button class="tli-ico-btn tli-ico-del btn-del"
                        data-id="<?= $tl['id'] ?>"
                        data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                        title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
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
      <span class="text-muted" style="font-size:13px;">
        Menampilkan <?= (($page - 1) * $perPage) + 1 ?>–<?= min($page * $perPage, $totalRows) ?>
        dari <strong><?= $totalRows ?></strong>
      </span>
      <div class="d-flex gap-1">
        <a class="tli-pag-btn <?= $page <= 1 ? 'disabled' : '' ?>" href="?page=<?= $page-1 ?><?= $qpStr ?>">&lsaquo;</a>
        <?php for ($i = max(1,$page-2); $i <= min($totalPages,$page+2); $i++): ?>
        <a class="tli-pag-btn <?= $i===$page ? 'tli-pag-active' : '' ?>" href="?page=<?= $i ?><?= $qpStr ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="tli-pag-btn <?= $page >= $totalPages ? 'disabled' : '' ?>" href="?page=<?= $page+1 ?><?= $qpStr ?>">&rsaquo;</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════════════════  VIEW: KANBAN  ══════════════════════════ -->
<div id="view-kanban" style="display:none">
  <div class="row g-3" id="kanban-board">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="tli-kb-col">
        <div class="tli-kb-header">
          <span class="tli-kb-dot bg-<?= $col['color'] ?>"></span>
          <span class="tli-kb-title"><?= $col['label'] ?></span>
          <span class="badge bg-brand-lt tli-badge-sm ms-auto" id="kanban-count-<?= $colStatus ?>"><?= count($col['items']) ?></span>
        </div>
        <div class="tli-kb-body kanban-col" id="kanban-col-<?= $colStatus ?>" data-status="<?= $colStatus ?>">
          <?php if (empty($col['items'])): ?>
          <div class="tli-kb-empty kanban-empty">Belum ada tugas</div>
          <?php endif; ?>
          <?php foreach ($col['items'] as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d')
                    && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <div class="tli-kcard <?= $overdue ? 'tli-kcard-overdue' : '' ?> <?= in_array($tl['status'],['done','cancelled']) ? 'tli-kcard-faded':'' ?>"
               id="kcard-<?= $tl['id'] ?>"
               data-id="<?= $tl['id'] ?>"
               data-status="<?= $tl['status'] ?>"
               data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
               style="cursor:<?= ($canEdit && $isAdminLike) ? 'grab' : 'default' ?>">

            <div class="d-flex gap-1 flex-wrap mb-2">
              <span class="badge bg-<?= $priorityColor[$tl['priority']] ?? 'secondary' ?>-lt tli-badge-sm">
                <?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?>
              </span>
              <?php if ($overdue): ?>
              <span class="badge bg-danger-lt tli-badge-sm">Terlambat</span>
              <?php endif; ?>
            </div>

            <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-kcard-desc">
              <?= htmlspecialchars($tl['description']) ?>
            </a>

            <div class="tli-kcard-meta">
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="tli-kcard-meta-row">
                <span class="tli-avatar tli-avatar-xs"><?= strtoupper(mb_substr($tl['assignee_name'],0,1)) ?></span>
                <span><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php endif; ?>
              <?php if (!empty($tl['due_date'])): ?>
              <div class="tli-kcard-meta-row <?= $overdue ? 'tli-overdue-text':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= date('d M Y', strtotime($tl['due_date'])) ?>
              </div>
              <?php endif; ?>
            </div>

            <div class="tli-kcard-footer">
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-kcard-meeting">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 28, '…')) ?>
              </a>
              <div class="d-flex gap-1">
                <button class="tli-ico-btn btn-notes"
                        data-id="<?= $tl['id'] ?>"
                        data-status="<?= $tl['status'] ?>"
                        data-can-done="<?= ($canEdit && $tl['status'] !== 'done') ? '1' : '0' ?>"
                        data-desc="<?= htmlspecialchars($tl['description']) ?>"
                        data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                        data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        title="Notes"
                        data-bs-toggle="modal" data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span class="tli-note-bubble" id="nbadge-<?= $tl['id'] ?>" <?= $nc < 1 ? 'style="display:none"' : '' ?>><?= $nc ?></span>
                </button>
                <?php if ($isAdminLike): ?>
                <button class="tli-ico-btn tli-ico-del btn-del"
                        data-id="<?= $tl['id'] ?>"
                        data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                        title="Hapus">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
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

<!-- ═══════════════════════  MODAL: TAMBAH TINDAK LANJUT  ═══════════════ -->
<?php if ($isAdminLike): ?>
<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <form id="formTambahTL">
        <div class="modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="tli-modal-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </span>
            <h5 class="modal-title">Tambah Tindak Lanjut</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="tl-form-alert" class="alert alert-danger d-none" role="alert"></div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Meeting / Kegiatan</label>
              <select name="meeting_id" class="form-select" required>
                <option value="">— Pilih Meeting —</option>
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
                        required placeholder="Tulis deskripsi tindak lanjut…"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label">Ditugaskan kepada</label>
              <select name="assigned_to" class="form-select">
                <option value="">— Belum ditugaskan —</option>
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
          <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn tli-btn-brand" id="btn-save-tl">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════════════  MODAL: PROGRESS NOTES  ═════════════════════ -->
<div class="modal modal-blur fade" id="modalNotes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-bottom">
        <div>
          <h5 class="modal-title">Progress Notes</h5>
          <div id="notes-desc" class="text-muted mt-1" style="font-size:13px;max-width:360px;"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Thread -->
        <div id="notes-thread" style="max-height:320px;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.75rem;">
          <div class="text-center text-muted py-4" style="font-size:13px;">Memuat…</div>
        </div>

        <!-- Tandai selesai -->
        <div id="done-bar" class="px-3 py-2 border-top" style="background:#f0fdf4;display:none;">
          <button id="btn-mark-done" class="btn btn-success w-100 btn-sm fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Tandai Selesai
          </button>
        </div>

        <!-- Input -->
        <div class="tli-note-input-area">
          <div class="d-flex gap-2 align-items-end">
            <div style="flex:1;position:relative;">
              <textarea id="note-input" class="tli-note-textarea form-control" rows="2"
                        placeholder="Tulis progress note… (@ mention, Ctrl+Enter kirim)"></textarea>
              <div id="mention-dropdown" class="tli-mention-drop" style="display:none"></div>
            </div>
            <button id="btn-send-note" class="btn tli-btn-brand text-nowrap flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim
            </button>
          </div>
          <p class="tli-note-hint mt-2 mb-0">Ketik <code>@nama</code> untuk mention · <kbd>Ctrl+Enter</kbd> kirim</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════  CSS  ═══════════════════════════════ -->
<style>
/* ── Page header ─────────────────────────────────────── */
.tli-page-header {
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 60%, #A83218 100%);
  border-radius: 14px;
  padding: 1.3rem 1.6rem;
  position: relative;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
}
.tli-page-header::after {
  content: '';
  position: absolute; top: -40px; right: -40px;
  width: 180px; height: 180px; border-radius: 50%;
  background: rgba(var(--gold-rgb, 201,168,76), .09);
  pointer-events: none;
}
.tli-page-title { font-size: clamp(18px,3vw,26px); font-weight: 800; color: #fff; margin: 0; letter-spacing: -.02em; }
.tli-page-sub   { font-size: 13px; color: rgba(255,255,255,.72); margin: .25rem 0 0; }

/* ── Buttons ─────────────────────────────────────────── */
.tli-btn-gold {
  background: var(--gold); border: none; color: #3D0A0A;
  font-size: 13px; font-weight: 700; border-radius: 8px;
  padding: .48rem 1.1rem; display: inline-flex; align-items: center; gap: .4rem;
  transition: all .14s;
}
.tli-btn-gold:hover  { background: var(--gold-dark); color: #fff; box-shadow: 0 3px 10px rgba(201,168,76,.3); }
.tli-btn-brand {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 700; border-radius: 8px;
  padding: .48rem 1.1rem; display: inline-flex; align-items: center; gap: .4rem;
  transition: all .14s;
}
.tli-btn-brand:hover    { background: var(--brand-dark); color: #fff; }
.tli-btn-brand:disabled { opacity: .6; cursor: default; }
.tli-btn-brand-sm {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 600; border-radius: 8px;
  padding: .38rem .9rem; display: inline-flex; align-items: center; gap: .35rem;
  transition: background .14s;
}
.tli-btn-brand-sm:hover { background: var(--brand-dark); }

/* ── Stat cards ──────────────────────────────────────── */
.tli-stat-row {
  display: grid;
  grid-template-columns: repeat(5,1fr);
  gap: .75rem;
}
@media(max-width:1023px) { .tli-stat-row { grid-template-columns: repeat(3,1fr); } }
@media(max-width:575px)  { .tli-stat-row { grid-template-columns: repeat(2,1fr); } }

.tli-stat-card {
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 12px; padding: .85rem 1rem;
  display: flex; align-items: center; gap: .75rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.05); transition: box-shadow .14s;
}
.tli-stat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.09); }

.tli-stat-icon {
  width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.tli-stat-icon-brand     { background: rgba(123,28,28,.1);   color: var(--brand); }
.tli-stat-icon-secondary { background: rgba(100,100,100,.1); color: #64748b; }
.tli-stat-icon-info      { background: rgba(32,107,196,.1);  color: #1557a0; }
.tli-stat-icon-success   { background: rgba(47,107,64,.1);   color: #1e7a2e; }
.tli-stat-icon-danger    { background: rgba(168,37,21,.1);   color: #a82515; }

.tli-stat-val { font-size: 22px; font-weight: 800; color: var(--brand); line-height: 1; }
.tli-stat-lbl { font-size: 11.5px; color: var(--text-muted); font-weight: 500; margin-top: .1rem; }

/* ── Toolbar ─────────────────────────────────────────── */
.tli-toolbar {
  display: flex; flex-wrap: wrap; align-items: center;
  justify-content: space-between; gap: .6rem;
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 12px; padding: .7rem 1rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.tli-filter-form { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; flex: 1; }

.tli-input {
  padding: .38rem .75rem;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13px; background: #fff; transition: border-color .14s;
}
.tli-input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.08); }

.tli-search-wrap  { position: relative; }
.tli-search-ico   { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
.tli-search-input { padding-left: 2rem; width: 200px; }

.tli-btn-reset {
  font-size: 13px; font-weight: 600; color: #a82515;
  text-decoration: none; padding: .38rem .65rem;
  border-radius: 8px; transition: background .13s;
}
.tli-btn-reset:hover { background: rgba(168,37,21,.08); }

/* View toggle */
.tli-view-toggle { display: flex; border: 1.5px solid var(--border); border-radius: 8px; overflow: hidden; }
.tli-vbtn {
  background: none; border: none; color: var(--text-muted);
  font-size: 13px; font-weight: 600;
  padding: .38rem .85rem; display: inline-flex; align-items: center; gap: .35rem;
  cursor: pointer; transition: all .13s;
}
.tli-vbtn + .tli-vbtn { border-left: 1.5px solid var(--border); }
.tli-vbtn.active,
.tli-vbtn:hover { background: var(--brand); color: #fff; }

/* ── Table ───────────────────────────────────────────── */
.tli-table thead th {
  background: #faf6ef;
  font-size: 10.5px; font-weight: 700;
  letter-spacing: .07em; text-transform: uppercase;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border);
}
.tli-table tbody td { vertical-align: middle; }
.tli-row-overdue td:first-child { border-left: 3px solid #a82515; }
.tli-row-overdue  { background: #fff7f7 !important; }

.tli-task-link {
  font-size: 13px; font-weight: 600; color: var(--text-main);
  text-decoration: none;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.tli-task-link:hover  { color: var(--brand); }
.tli-meeting-link     { font-size: 12px; color: var(--brand); text-decoration: none; font-weight: 500; }
.tli-meeting-link:hover { text-decoration: underline; }
.tli-assignee         { font-size: 13px; color: var(--text-main); }
.tli-overdue-text     { color: #a82515; font-weight: 700; }

/* ── Badges (tiny) ───────────────────────────────────── */
.tli-badge-sm { font-size: 10.5px; padding: .22em .6em; display: inline-flex; align-items: center; gap: .2rem; }

/* ── Avatar ──────────────────────────────────────────── */
.tli-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 11px; font-weight: 700;
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tli-avatar-xs { width: 18px; height: 18px; font-size: 9px; }

/* ── Icon buttons ────────────────────────────────────── */
.tli-ico-btn {
  background: none; border: none; cursor: pointer;
  width: 30px; height: 30px; border-radius: 7px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--text-muted); transition: all .13s; position: relative;
}
.tli-ico-btn:hover    { background: rgba(123,28,28,.08); color: var(--brand); }
.tli-ico-detail       { border: 1.5px solid var(--brand); color: var(--brand); }
.tli-ico-detail:hover { background: var(--brand); color: #fff; }
.tli-ico-del:hover    { background: rgba(168,37,21,.1); color: #a82515; }

/* Note bubble */
.tli-note-bubble {
  position: absolute; top: 2px; right: 2px;
  background: var(--brand); color: #fff;
  font-size: 9px; font-weight: 700;
  min-width: 14px; height: 14px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center; padding: 0 2px;
}

/* Status select */
.tli-status-sel {
  font-size: 12.5px; font-weight: 600;
  border: 1.5px solid var(--border); border-radius: 7px;
  padding: .28rem .55rem; background: #fff;
  cursor: pointer; transition: border-color .13s;
}
.tli-status-sel:focus { outline: none; border-color: var(--brand); }

/* ── Pagination ──────────────────────────────────────── */
.tli-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: .65rem 1rem; border-top: 1px solid var(--border-light);
  background: #faf6ef;
}
.tli-pag-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 7px;
  font-size: 13px; font-weight: 600; text-decoration: none;
  color: var(--text-main); border: 1.5px solid var(--border);
  background: #fff; transition: all .13s;
}
.tli-pag-btn:hover  { border-color: var(--brand); color: var(--brand); }
.tli-pag-active     { background: var(--brand); color: #fff !important; border-color: var(--brand); }
.tli-pag-btn.disabled { opacity: .4; pointer-events: none; }

/* ── Empty state ─────────────────────────────────────── */
.tli-empty-state {
  display: flex; flex-direction: column; align-items: center;
  padding: 3rem 1rem; color: var(--text-muted); gap: .75rem;
}
.tli-empty-state p { margin: 0; font-size: 14px; }

/* ── Kanban ──────────────────────────────────────────── */
.tli-kb-col {
  background: #faf6ef; border: 1px solid var(--border-light);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.tli-kb-header {
  display: flex; align-items: center; gap: .5rem;
  padding: .65rem .9rem; border-bottom: 1px solid var(--border-light);
  background: #fff;
}
.tli-kb-dot   { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; }
.tli-kb-title { font-size: 13px; font-weight: 700; color: var(--text-main); }
.tli-kb-body  { padding: .6rem; min-height: 180px; overflow-y: auto; max-height: calc(100vh - 340px); }
.tli-kb-empty {
  text-align: center; color: var(--text-muted); font-size: 13px;
  padding: 2rem 1rem; border: 2px dashed var(--border); border-radius: 10px; margin: .25rem;
}

/* Kanban card */
.tli-kcard {
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 10px; padding: .7rem .8rem; margin-bottom: .5rem;
  transition: box-shadow .13s, transform .13s;
}
.tli-kcard:hover        { box-shadow: 0 4px 14px rgba(0,0,0,.1); transform: translateY(-1px); }
.tli-kcard-overdue      { border-left: 3px solid #a82515; }
.tli-kcard-faded        { opacity: .7; }
.tli-kcard-desc {
  display: block; font-size: 12.5px; font-weight: 600;
  color: var(--text-main); text-decoration: none; line-height: 1.4;
  margin-bottom: .45rem;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.tli-kcard-desc:hover { color: var(--brand); }
.tli-kcard-meta { display: flex; flex-direction: column; gap: .25rem; margin-bottom: .45rem; }
.tli-kcard-meta-row { display: flex; align-items: center; gap: .35rem; font-size: 11.5px; color: var(--text-muted); }
.tli-kcard-footer {
  display: flex; align-items: center; justify-content: space-between;
  padding-top: .4rem; border-top: 1px solid var(--border-light);
}
.tli-kcard-meeting {
  font-size: 10.5px; color: var(--brand); text-decoration: none; font-weight: 500;
  display: flex; align-items: center; gap: .3rem;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 70%;
}
.tli-kcard-meeting:hover { text-decoration: underline; }

/* Sortable */
.sortable-ghost { opacity: .3; background: rgba(123,28,28,.06); border: 2px dashed var(--brand); }
.sortable-drag  { box-shadow: 0 8px 24px rgba(0,0,0,.18) !important; transform: rotate(1.5deg); }

/* ── Modal icon ──────────────────────────────────────── */
.tli-modal-icon {
  width: 32px; height: 32px; background: var(--brand-light);
  border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
  color: var(--brand); flex-shrink: 0;
}

/* ── Notes modal input area ──────────────────────────── */
.tli-note-input-area {
  padding: .85rem; border-top: 1px solid var(--border-light); background: #faf6ef;
}
.tli-note-textarea {
  resize: vertical; min-height: 56px;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13.5px; transition: border-color .14s;
}
.tli-note-textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.1); }
.tli-note-hint { font-size: 11px; color: var(--text-muted); }
.tli-note-hint code { background: rgba(123,28,28,.08); color: var(--brand); border-radius: 3px; padding: 0 3px; }
.tli-note-hint kbd  { background: #eee; border-radius: 3px; padding: 1px 4px; font-size: 10px; }

/* Note thread */
.tli-nt-item  { display: flex; gap: .6rem; }
.tli-nt-body  { flex: 1; min-width: 0; }
.tli-nt-meta  { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; margin-bottom: .15rem; }
.tli-nt-name  { font-size: 13px; font-weight: 700; color: var(--text-main); }
.tli-nt-time  { font-size: 11px; color: var(--text-muted); }
.tli-nt-text  { font-size: 13.5px; color: var(--text-main); line-height: 1.55; white-space: pre-wrap; }
.tli-nt-del   {
  background: none; border: none; cursor: pointer;
  color: var(--text-muted); font-size: 14px; padding: .1rem .3rem;
  border-radius: 4px; transition: all .12s;
}
.tli-nt-del:hover { color: #a82515; background: rgba(168,37,21,.08); }

/* @mention dropdown */
.tli-mention-drop {
  position: absolute; bottom: calc(100% + 4px); left: 0;
  background: #fff; border: 1px solid var(--border); border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.12);
  min-width: 180px; max-height: 180px; overflow-y: auto; z-index: 9999;
}
.tli-mention-item { padding: .5rem .85rem; font-size: 13px; cursor: pointer; transition: background .11s; }
.tli-mention-item:hover,
.tli-mention-item.focused { background: var(--brand-light); color: var(--brand); }

/* Tabler bg-brand-lt shim (jika belum ada di custom.css) */
.bg-brand-lt { background-color: rgba(123,28,28,.1) !important; color: var(--brand) !important; }
</style>

<!-- ═══════════════════════════════  JS  ════════════════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function () {
'use strict';

var BASE          = <?= json_encode($baseUrl) ?>;
var ALL_USERS     = <?= $allUsersJson ?>;
var IS_ADMIN_LIKE = <?= $isAdminLike ? 'true' : 'false' ?>;
var ACTIVE_UID    = <?= (int)($user_id ?? 0) ?>;

/* ── helpers ──────────────────────────────────────────── */
function esc(s) {
  return String(s)
    .replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function updateStatCards(s) {
  if (!s) return;
  ['total','pending','in_progress','done','overdue'].forEach(function(k) {
    var el = document.getElementById('stat-' + k);
    if (el && s[k] !== undefined) el.textContent = s[k];
  });
}
function updateNoteBubble(id, count) {
  document.querySelectorAll('#nbadge-' + id).forEach(function(el) {
    el.textContent = count;
    el.style.display = count > 0 ? 'flex' : 'none';
  });
}
async function postJSON(url, body) {
  var r = await fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(body)
  });
  return r.json();
}

/* ── View toggle ──────────────────────────────────────── */
var btnTable  = document.getElementById('btn-view-table');
var btnKanban = document.getElementById('btn-view-kanban');
var divTable  = document.getElementById('view-table');
var divKanban = document.getElementById('view-kanban');

function setView(v) {
  var isKanban = v === 'kanban';
  divTable.style.display  = isKanban ? 'none' : '';
  divKanban.style.display = isKanban ? '' : 'none';
  btnTable.classList.toggle('active', !isKanban);
  btnKanban.classList.toggle('active', isKanban);
  var fs = document.getElementById('filter-status');
  if (fs) fs.disabled = isKanban;
  try { localStorage.setItem('tl_view', v); } catch(e) {}
}

btnTable.addEventListener('click', function() { setView('table'); });
btnKanban.addEventListener('click', function() { setView('kanban'); });
(function() {
  var saved = 'kanban';
  try { saved = localStorage.getItem('tl_view') || 'kanban'; } catch(e) {}
  setView(saved);
})();

/* ── Status select (table view) ───────────────────────── */
document.querySelectorAll('.status-select').forEach(function(sel) {
  sel.addEventListener('change', async function() {
    var d = await postJSON(this.dataset.url, { status: this.value });
    if (!d.success) { alert(d.message || 'Gagal update status'); return; }
    updateStatCards(d.summary);
    var tr = this.closest('tr');
    if (tr) tr.style.opacity = ['done','cancelled'].includes(this.value) ? '.55' : '';
  });
});

/* ── Delete ───────────────────────────────────────────── */
function bindDel() {
  document.querySelectorAll('.btn-del').forEach(function(btn) {
    if (btn._bound) return;
    btn._bound = true;
    btn.addEventListener('click', async function() {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      var r = await fetch(this.dataset.url, { method: 'POST' });
      var d = await r.json();
      if (!d.success) { alert(d.message || 'Gagal hapus'); return; }
      var id    = this.dataset.id;
      var trow  = document.getElementById('trow-' + id);
      var kcard = document.getElementById('kcard-' + id);
      if (trow)  trow.remove();
      if (kcard) { var col = kcard.closest('.kanban-col'); kcard.remove(); updateColCount(col); }
      updateStatCards(d.summary);
    });
  });
}
bindDel();

/* ── Kanban col count ─────────────────────────────────── */
function updateColCount(cry);
    });
  });
}
bindDel();

/* ── Kanban col count ─────────────────────────────────── */
function updateColCount(c
