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

$meetingOptions = Database::query("SELECT id, title FROM meetings ORDER BY start_datetime DESC LIMIT 200");

$csrfToken = CSRF::token();
?>

<!-- ═══════════════  PAGE HEADER  ═══════════════ -->
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

<!-- ═══════════════  STAT CARDS  ═══════════════ -->
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

<!-- ═══════════════  TOOLBAR  ═══════════════ -->
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

    <?php if (Auth::hasRole('admin') && !empty($allUsers)): ?>
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
    <?php if (($status ?? '') || ($priority ?? '') || ($search ?? '') || ($user_id ?? 0)): ?>
    <a href="<?= $baseUrl ?>/tindak-lanjut" class="tli-btn-reset">&#x2715; Reset</a>
    <?php endif; ?>
  </form>

  <div class="tli-view-toggle">
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

<!-- ═══════════════  VIEW: TABEL  ═══════════════ -->
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
            <th style="width:96px"></th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($tindakLanjutList)): ?>
          <tr><td colspan="7">
            <div class="tli-empty-state">
              <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              <p>Belum ada tindak lanjut yang ditemukan</p>
            </div>
          </td></tr>
          <?php endif; ?>
          <?php foreach ($tindakLanjutList as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <tr class="<?= $overdue ? 'tli-row-overdue' : '' ?>" id="trow-<?= $tl['id'] ?>">
            <td>
              <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-task-link">
                <?= htmlspecialchars($tl['description']) ?>
              </a>
              <?php if ($overdue): ?>
              <span class="tli-badge tli-badge-danger mt-1">
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
              <?php else: ?><span class="tli-text-muted">—</span><?php endif; ?>
            </td>
            <td class="text-nowrap <?= $overdue ? 'tli-overdue-text' : 'tli-text-muted' ?>">
              <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
            </td>
            <td>
              <?php $pc = ['high'=>'danger','medium'=>'warning','low'=>'success'][$tl['priority']] ?? 'muted'; ?>
              <span class="tli-badge tli-badge-<?= $pc ?>">
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
              <?php $sc2 = ['pending'=>'muted','in_progress'=>'info','done'=>'success','cancelled'=>'danger'][$tl['status']] ?? 'muted'; ?>
              <span class="tli-badge tli-badge-<?= $sc2 ?>">
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
                <button class="tli-ico-btn tli-ico-del btn-del" data-id="<?= $tl['id'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete" title="Hapus">
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
      <span class="tli-pag-info">Menampilkan <?= (($page-1)*$perPage)+1 ?>–<?= min($page*$perPage,$totalRows) ?> dari <strong><?= $totalRows ?></strong></span>
      <div class="d-flex gap-1">
        <a class="tli-pag-btn <?= $page<=1?'disabled':'' ?>" href="?page=<?= $page-1 ?><?= $qpStr ?>">&lsaquo;</a>
        <?php for ($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
        <a class="tli-pag-btn <?= $i===$page?'tli-pag-active':'' ?>" href="?page=<?= $i ?><?= $qpStr ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a class="tli-pag-btn <?= $page>=$totalPages?'disabled':'' ?>" href="?page=<?= $page+1 ?><?= $qpStr ?>">&rsaquo;</a>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════  VIEW: KANBAN  ═══════════════ -->
<div id="view-kanban" style="display:none">
  <div class="row g-3">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="tli-kb-col">
        <div class="tli-kb-header">
          <?php $dotColors=['pending'=>'#94a3b8','in_progress'=>'#1557a0','done'=>'#1e7a2e','cancelled'=>'#a82515']; ?>
          <span class="tli-kb-dot" style="background:<?= $dotColors[$colStatus]??'#aaa' ?>"></span>
          <span class="tli-kb-title"><?= $col['label'] ?></span>
          <span class="tli-badge tli-badge-brand ms-auto" id="kanban-count-<?= $colStatus ?>"><?= count($col['items']) ?></span>
        </div>
        <div class="tli-kb-body kanban-col" id="kanban-col-<?= $colStatus ?>" data-status="<?= $colStatus ?>">
          <?php if (empty($col['items'])): ?>
          <div class="tli-kb-empty kanban-empty">Belum ada tugas</div>
          <?php endif; ?>
          <?php foreach ($col['items'] as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'],['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <div class="tli-kcard <?= $overdue?'tli-kcard-overdue':'' ?> <?= in_array($tl['status'],['done','cancelled'])?'tli-kcard-faded':'' ?>"
               id="kcard-<?= $tl['id'] ?>" data-id="<?= $tl['id'] ?>" data-status="<?= $tl['status'] ?>"
               data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
               style="cursor:<?= ($canEdit&&$isAdminLike)?'grab':'default' ?>">
            <div class="d-flex gap-1 flex-wrap mb-2">
              <?php $pc2=['high'=>'danger','medium'=>'warning','low'=>'success'][$tl['priority']]??'muted'; ?>
              <span class="tli-badge tli-badge-<?= ['high'=>'danger','medium'=>'warning','low'=>'success'][$tl['priority']]??'muted' ?>">
                <?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?>
              </span>
              <?php if ($overdue): ?><span class="tli-badge tli-badge-danger">Terlambat</span><?php endif; ?>
            </div>
            <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-kcard-desc"><?= htmlspecialchars($tl['description']) ?></a>
            <div class="tli-kcard-meta">
              <?php if (!empty($tl['assignee_name'])): ?>
              <div class="tli-kcard-meta-row">
                <span class="tli-avatar tli-avatar-xs"><?= strtoupper(mb_substr($tl['assignee_name'],0,1)) ?></span>
                <span><?= htmlspecialchars($tl['assignee_name']) ?></span>
              </div>
              <?php endif; ?>
              <?php if (!empty($tl['due_date'])): ?>
              <div class="tli-kcard-meta-row <?= $overdue?'tli-overdue-text':'' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= date('d M Y', strtotime($tl['due_date'])) ?>
              </div>
              <?php endif; ?>
            </div>
            <div class="tli-kcard-footer">
              <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-kcard-meeting">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'],0,28,'…')) ?>
              </a>
              <div class="d-flex gap-1">
                <button class="tli-ico-btn btn-notes"
                        data-id="<?= $tl['id'] ?>" data-status="<?= $tl['status'] ?>"
                        data-can-done="<?= ($canEdit&&$tl['status']!=='done')?'1':'0' ?>"
                        data-desc="<?= htmlspecialchars($tl['description']) ?>"
                        data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                        data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                        title="Notes" data-bs-toggle="modal" data-bs-target="#modalNotes">
                  <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span class="tli-note-bubble" id="nbadge-<?= $tl['id'] ?>" <?= $nc<1?'style="display:none"':'' ?>><?= $nc ?></span>
                </button>
                <?php if ($isAdminLike): ?>
                <button class="tli-ico-btn tli-ico-del btn-del" data-id="<?= $tl['id'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete" title="Hapus">
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

<!-- ═══════════════  MODAL: TAMBAH  ═══════════════ -->
<?php if ($isAdminLike): ?>
<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content tli-modal-content">
      <form id="formTambahTL">
        <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
        <div class="modal-header tli-modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="tli-modal-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </span>
            <h5 class="modal-title fw-bold">Tambah Tindak Lanjut</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div id="tl-form-alert" class="alert alert-danger d-none"></div>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label fw-semibold">Meeting / Kegiatan <span class="text-danger">*</span></label>
              <select name="meeting_id" class="form-select tli-form-control" required>
                <option value="">— Pilih Meeting —</option>
                <?php foreach ($meetingOptions as $m): ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Deskripsi Tugas <span class="text-danger">*</span></label>
              <textarea name="description" class="form-control tli-form-control" rows="3" required placeholder="Tulis deskripsi tindak lanjut…"></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Ditugaskan kepada</label>
              <select name="assigned_to" class="form-select tli-form-control">
                <option value="">— Belum ditugaskan —</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Deadline</label>
              <input type="date" name="due_date" class="form-control tli-form-control" min="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Prioritas</label>
              <select name="priority" class="form-select tli-form-control">
                <option value="medium" selected>Sedang</option>
                <option value="high">Tinggi</option>
                <option value="low">Rendah</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="tli-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="tli-btn-brand" id="btn-save-tl">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ═══════════════  MODAL: PROGRESS NOTES  ═══════════════ -->
<div class="modal modal-blur fade" id="modalNotes" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content tli-modal-content">
      <div class="modal-header tli-modal-header">
        <div>
          <h5 class="modal-title fw-bold">Progress Notes</h5>
          <div id="notes-desc" class="tli-text-muted mt-1" style="font-size:13px;max-width:360px;"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body p-0">
        <div id="notes-thread" style="max-height:320px;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.75rem;">
          <div class="text-center tli-text-muted py-4" style="font-size:13px;">Memuat…</div>
        </div>
        <div id="done-bar" class="px-3 py-2 border-top" style="background:#f0fdf4;display:none;">
          <button id="btn-mark-done" class="btn btn-success w-100 btn-sm fw-bold">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Tandai Selesai
          </button>
        </div>
        <div class="tli-note-input-area">
          <div class="d-flex gap-2 align-items-end">
            <div style="flex:1;position:relative;">
              <textarea id="note-input" class="tli-note-textarea form-control" rows="2" placeholder="Tulis progress note… (@ mention, Ctrl+Enter kirim)"></textarea>
              <div id="mention-dropdown" class="tli-mention-drop" style="display:none"></div>
            </div>
            <button id="btn-send-note" class="tli-btn-brand text-nowrap flex-shrink-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim
            </button>
          </div>
          <p class="tli-note-hint mt-2 mb-0">Ketik <code>@nama</code> untuk mention &middot; <kbd>Ctrl+Enter</kbd> kirim</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ═══════════════════════  CSS  ═══════════════════════ -->
<style>
/*
  Kemenbud Color Palette
  --brand      : #7B1C1C  (Merah Marun)
  --brand-dark : #5C1212
  --brand-light: rgba(123,28,28,.09)
  --gold       : #C9A84C  (Emas)
  --gold-dark  : #A8882E
  --cream      : #FAF6EF  (Krem)
  --border     : #E8DDD0
  --border-light: #F0EAE1
*/
:root {
  --brand       : #7B1C1C;
  --brand-dark  : #5C1212;
  --brand-light : rgba(123,28,28,.09);
  --gold        : #C9A84C;
  --gold-dark   : #A8882E;
  --gold-light  : rgba(201,168,76,.12);
  --cream       : #FAF6EF;
  --border      : #E8DDD0;
  --border-light: #F0EAE1;
  --text-main   : #1A2530;
  --text-muted  : #6B7280;
  --success     : #1e7a2e;
  --danger      : #a82515;
  --info        : #1557a0;
}

/* Page header */
.tli-page-header {
  background: linear-gradient(135deg, #7B1C1C 0%, #9B1F1F 55%, #6B3A1A 100%);
  border-radius: 16px;
  padding: 1.4rem 1.75rem;
  box-shadow: 0 6px 24px rgba(123,28,28,.22);
  position: relative;
  overflow: hidden;
}
.tli-page-header::before {
  content: '';
  position: absolute; top: -60px; right: -60px;
  width: 220px; height: 220px; border-radius: 50%;
  background: var(--gold-light);
  pointer-events: none;
}
.tli-page-header::after {
  content: '';
  position: absolute; bottom: -40px; left: 30%;
  width: 140px; height: 140px; border-radius: 50%;
  background: rgba(255,255,255,.04);
  pointer-events: none;
}
.tli-header-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: var(--gold-light);
  border: 1px solid rgba(201,168,76,.3);
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--gold); flex-shrink: 0;
}
.tli-page-title { font-size: clamp(17px,3vw,24px); font-weight: 800; color: #fff; margin: 0; letter-spacing: -.02em; }
.tli-page-sub   { font-size: 13px; color: rgba(255,255,255,.68); margin: .2rem 0 0; }

/* Buttons */
.tli-btn-gold {
  background: var(--gold); border: none; color: #3D0A0A;
  font-size: 13px; font-weight: 700; border-radius: 9px;
  padding: .5rem 1.2rem; display: inline-flex; align-items: center; gap: .45rem;
  transition: all .15s; cursor: pointer; line-height: 1;
}
.tli-btn-gold:hover { background: var(--gold-dark); color: #fff; box-shadow: 0 4px 12px rgba(201,168,76,.3); }

.tli-btn-brand {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 700; border-radius: 9px;
  padding: .5rem 1.2rem; display: inline-flex; align-items: center; gap: .4rem;
  transition: all .15s; cursor: pointer; line-height: 1;
}
.tli-btn-brand:hover    { background: var(--brand-dark); box-shadow: 0 4px 12px rgba(123,28,28,.25); }
.tli-btn-brand:disabled { opacity: .55; cursor: default; }

.tli-btn-brand-sm {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 600; border-radius: 8px;
  padding: .4rem 1rem; display: inline-flex; align-items: center; gap: .35rem;
  transition: background .14s; cursor: pointer; line-height: 1;
}
.tli-btn-brand-sm:hover { background: var(--brand-dark); }

.tli-btn-ghost {
  background: none; border: 1.5px solid var(--border); color: var(--text-muted);
  font-size: 13px; font-weight: 600; border-radius: 8px;
  padding: .45rem 1rem; cursor: pointer; transition: all .13s;
}
.tli-btn-ghost:hover { border-color: var(--brand); color: var(--brand); }

.tli-btn-reset {
  font-size: 13px; font-weight: 600; color: var(--danger);
  text-decoration: none; padding: .38rem .65rem;
  border-radius: 8px; transition: background .13s;
}
.tli-btn-reset:hover { background: rgba(168,37,21,.08); }

/* Stat cards */
.tli-stat-row {
  display: grid;
  grid-template-columns: repeat(5,1fr);
  gap: .75rem;
}
@media(max-width:1023px) { .tli-stat-row { grid-template-columns: repeat(3,1fr); } }
@media(max-width:575px)  { .tli-stat-row { grid-template-columns: repeat(2,1fr); } }
.tli-stat-card {
  background: #fff; border: 1.5px solid var(--border-light);
  border-radius: 13px; padding: .9rem 1rem;
  display: flex; align-items: center; gap: .75rem;
  box-shadow: 0 1px 5px rgba(123,28,28,.06);
  transition: box-shadow .14s, transform .14s;
}
.tli-stat-card:hover { box-shadow: 0 5px 18px rgba(123,28,28,.1); transform: translateY(-1px); }
.tli-stat-icon {
  width: 42px; height: 42px; border-radius: 11px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
}
.tli-stat-icon-brand   { background: var(--brand-light); color: var(--brand); }
.tli-stat-icon-muted   { background: rgba(107,114,128,.1); color: #6B7280; }
.tli-stat-icon-info    { background: rgba(21,87,160,.1);  color: var(--info); }
.tli-stat-icon-success { background: rgba(30,122,46,.1);  color: var(--success); }
.tli-stat-icon-danger  { background: rgba(168,37,21,.1);  color: var(--danger); }
.tli-stat-val { font-size: 23px; font-weight: 800; color: var(--brand); line-height: 1; }
.tli-stat-lbl { font-size: 11.5px; color: var(--text-muted); font-weight: 500; margin-top: .12rem; }

/* Toolbar */
.tli-toolbar {
  display: flex; flex-wrap: wrap; align-items: center;
  justify-content: space-between; gap: .6rem;
  background: #fff; border: 1.5px solid var(--border-light);
  border-radius: 13px; padding: .75rem 1rem;
  box-shadow: 0 1px 5px rgba(0,0,0,.04);
}
.tli-filter-form { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; flex: 1; }
.tli-input {
  padding: .4rem .8rem;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13px; background: #fff; color: var(--text-main);
  transition: border-color .14s;
}
.tli-input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.08); }
.tli-select { padding-right: 1.8rem; cursor: pointer; }
.tli-search-wrap  { position: relative; }
.tli-search-ico   { position: absolute; left: .65rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none; }
.tli-search-input { padding-left: 2.1rem; width: 200px; }

/* View toggle */
.tli-view-toggle { display: flex; border: 1.5px solid var(--border); border-radius: 9px; overflow: hidden; }
.tli-vbtn {
  background: none; border: none; color: var(--text-muted);
  font-size: 13px; font-weight: 600;
  padding: .4rem .9rem; display: inline-flex; align-items: center; gap: .35rem;
  cursor: pointer; transition: all .13s;
}
.tli-vbtn + .tli-vbtn { border-left: 1.5px solid var(--border); }
.tli-vbtn.active, .tli-vbtn:hover { background: var(--brand); color: #fff; }

/* Card container */
.tli-card {
  background: #fff; border: 1.5px solid var(--border-light);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 2px 10px rgba(123,28,28,.06);
}

/* Table */
.tli-table thead th {
  background: var(--cream);
  font-size: 10.5px; font-weight: 700;
  letter-spacing: .08em; text-transform: uppercase;
  color: var(--text-muted);
  border-bottom: 2px solid var(--border);
  padding: .75rem 1rem;
}
.tli-table tbody td { vertical-align: middle; padding: .7rem 1rem; border-color: var(--border-light); }
.tli-table tbody tr:hover { background: rgba(250,246,239,.6); }
.tli-row-overdue { background: #fff8f8 !important; }
.tli-row-overdue td:first-child { border-left: 3px solid var(--danger); }

.tli-task-link {
  font-size: 13px; font-weight: 600; color: var(--text-main); text-decoration: none;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.tli-task-link:hover { color: var(--brand); }
.tli-meeting-link { font-size: 12px; color: var(--brand); text-decoration: none; font-weight: 500; }
.tli-meeting-link:hover { text-decoration: underline; }
.tli-assignee     { font-size: 13px; color: var(--text-main); }
.tli-overdue-text { color: var(--danger); font-weight: 700; }
.tli-text-muted   { color: var(--text-muted); }

/* Badges */
.tli-badge {
  display: inline-flex; align-items: center; gap: .22rem;
  font-size: 10.5px; font-weight: 700; padding: .22em .65em;
  border-radius: 5px; line-height: 1.4;
}
.tli-badge-brand   { background: var(--brand-light); color: var(--brand); }
.tli-badge-danger  { background: rgba(168,37,21,.1);  color: var(--danger); }
.tli-badge-warning { background: rgba(201,168,76,.15); color: #7a5c00; }
.tli-badge-success { background: rgba(30,122,46,.1);  color: var(--success); }
.tli-badge-info    { background: rgba(21,87,160,.1);  color: var(--info); }
.tli-badge-muted   { background: rgba(107,114,128,.1); color: var(--text-muted); }

/* Avatar */
.tli-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 11px; font-weight: 700;
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tli-avatar-xs { width: 20px; height: 20px; font-size: 9px; }

/* Icon buttons */
.tli-ico-btn {
  background: none; border: none; cursor: pointer;
  width: 30px; height: 30px; border-radius: 7px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--text-muted); transition: all .13s; position: relative;
}
.tli-ico-btn:hover    { background: var(--brand-light); color: var(--brand); }
.tli-ico-detail       { border: 1.5px solid var(--brand); color: var(--brand); }
.tli-ico-detail:hover { background: var(--brand); color: #fff; }
.tli-ico-del:hover    { background: rgba(168,37,21,.1); color: var(--danger); }

.tli-note-bubble {
  position: absolute; top: 2px; right: 2px;
  background: var(--gold); color: #3D0A0A;
  font-size: 9px; font-weight: 700;
  min-width: 14px; height: 14px; border-radius: 7px;
  display: flex; align-items: center; justify-content: center; padding: 0 2px;
}

/* Status select */
.tli-status-sel {
  font-size: 12.5px; font-weight: 600;
  border: 1.5px solid var(--border); border-radius: 7px;
  padding: .28rem .55rem; background: #fff; color: var(--text-main);
  cursor: pointer; transition: border-color .13s;
}
.tli-status-sel:focus { outline: none; border-color: var(--brand); }

/* Pagination */
.tli-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: .7rem 1rem; border-top: 1.5px solid var(--border-light);
  background: var(--cream);
}
.tli-pag-info { font-size: 13px; color: var(--text-muted); }
.tli-pag-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 32px; height: 32px; border-radius: 7px;
  font-size: 13px; font-weight: 600; text-decoration: none;
  color: var(--text-main); border: 1.5px solid var(--border);
  background: #fff; transition: all .13s;
}
.tli-pag-btn:hover   { border-color: var(--brand); color: var(--brand); }
.tli-pag-active      { background: var(--brand) !important; color: #fff !important; border-color: var(--brand) !important; }
.tli-pag-btn.disabled { opacity: .38; pointer-events: none; }

/* Empty state */
.tli-empty-state {
  display: flex; flex-direction: column; align-items: center;
  padding: 3.5rem 1rem; color: var(--text-muted); gap: .75rem;
}
.tli-empty-state p { margin: 0; font-size: 14px; }

/* Kanban */
.tli-kb-col {
  background: var(--cream); border: 1.5px solid var(--border-light);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 1px 5px rgba(123,28,28,.05);
}
.tli-kb-header {
  display: flex; align-items: center; gap: .5rem;
  padding: .7rem 1rem; border-bottom: 1.5px solid var(--border-light);
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
  background: #fff; border: 1.5px solid var(--border-light);
  border-radius: 10px; padding: .75rem .85rem; margin-bottom: .5rem;
  transition: box-shadow .13s, transform .13s;
}
.tli-kcard:hover        { box-shadow: 0 5px 16px rgba(123,28,28,.1); transform: translateY(-1px); }
.tli-kcard-overdue      { border-left: 3px solid var(--danger); }
.tli-kcard-faded        { opacity: .65; }
.tli-kcard-desc {
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
  font-size: 12.5px; font-weight: 600; color: var(--text-main);
  text-decoration: none; line-height: 1.4; margin-bottom: .45rem;
}
.tli-kcard-desc:hover { color: var(--brand); }
.tli-kcard-meta        { display: flex; flex-direction: column; gap: .25rem; margin-bottom: .45rem; }
.tli-kcard-meta-row    { display: flex; align-items: center; gap: .35rem; font-size: 11.5px; color: var(--text-muted); }
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

/* SortableJS */
.sortable-ghost { opacity: .25; background: rgba(123,28,28,.06); border: 2px dashed var(--brand); }
.sortable-drag  { box-shadow: 0 10px 30px rgba(0,0,0,.18) !important; transform: rotate(1.5deg); }

/* Modal */
.tli-modal-content { border: none; border-radius: 16px; overflow: hidden; }
.tli-modal-header  {
  background: var(--cream); border-bottom: 1.5px solid var(--border-light);
  padding: 1rem 1.25rem;
}
.tli-modal-icon {
  width: 34px; height: 34px; background: var(--brand-light);
  border-radius: 8px; display: inline-flex; align-items: center; justify-content: center;
  color: var(--brand); flex-shrink: 0;
}

/* Form controls in modal */
.tli-form-control {
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13.5px; transition: border-color .14s;
}
.tli-form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.08); }

/* Note input */
.tli-note-input-area {
  padding: .9rem; border-top: 1.5px solid var(--border-light); background: var(--cream);
}
.tli-note-textarea {
  resize: vertical; min-height: 56px;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13.5px; transition: border-color .14s;
}
.tli-note-textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.1); }
.tli-note-hint      { font-size: 11px; color: var(--text-muted); }
.tli-note-hint code { background: rgba(123,28,28,.08); color: var(--brand); border-radius: 3px; padding: 0 3px; }
.tli-note-hint kbd  { background: #eee; border-radius: 3px; padding: 1px 4px; font-size: 10px; }

/* Note thread */
.tli-nt-item { display: flex; gap: .6rem; }
.tli-nt-body { flex: 1; min-width: 0; }
.tli-nt-meta { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; margin-bottom: .15rem; }
.tli-nt-name { font-size: 13px; font-weight: 700; color: var(--text-main); }
.tli-nt-time { font-size: 11px; color: var(--text-muted); }
.tli-nt-text { font-size: 13.5px; color: var(--text-main); line-height: 1.55; white-space: pre-wrap; }
.tli-nt-del  {
  background: none; border: none; cursor: pointer;
  color: var(--text-muted); font-size: 14px; padding: .1rem .3rem;
  border-radius: 4px; transition: all .12s;
}
.tli-nt-del:hover { color: var(--danger); background: rgba(168,37,21,.08); }

/* Mention dropdown */
.tli-mention-drop {
  position: absolute; bottom: calc(100% + 4px); left: 0;
  background: #fff; border: 1.5px solid var(--border); border-radius: 9px;
  box-shadow: 0 4px 18px rgba(0,0,0,.12);
  min-width: 190px; max-height: 180px; overflow-y: auto; z-index: 9999;
}
.tli-mention-item { padding: .5rem .9rem; font-size: 13px; cursor: pointer; transition: background .11s; }
.tli-mention-item:hover,
.tli-mention-item.focused { background: var(--brand-light); color: var(--brand); }
</style>

<!-- ═══════════════════════  JS  ═══════════════════════ -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function () {
'use strict';

var BASE          = <?= json_encode($baseUrl) ?>;
var ALL_USERS     = <?= $allUsersJson ?>;
var IS_ADMIN_LIKE = <?= $isAdminLike ? 'true' : 'false' ?>;
var CSRF_TOKEN    = <?= json_encode($csrfToken) ?>;

var _currentView    = 'table';
var _mentionFocusIdx = -1;

function esc(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
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
    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
    body: JSON.stringify(Object.assign({ _csrf: CSRF_TOKEN }, body))
  });
  return r.json();
}

/* ── View toggle ── */
var btnTable  = document.getElementById('btn-view-table');
var btnKanban = document.getElementById('btn-view-kanban');
var divTable  = document.getElementById('view-table');
var divKanban = document.getElementById('view-kanban');

function setView(v) {
  _currentView = v;
  var isKanban = v === 'kanban';
  divTable.style.display  = isKanban ? 'none' : '';
  divKanban.style.display = isKanban ? '' : 'none';
  btnTable.classList.toggle('active', !isKanban);
  btnKanban.classList.toggle('active', isKanban);
  var fs = document.getElementById('filter-status');
  if (fs) { fs.disabled = isKanban; fs.style.opacity = isKanban ? '.45' : ''; }
}
btnTable.addEventListener('click', function() { setView('table'); });
btnKanban.addEventListener('click', function() { setView('kanban'); });
setView('table');

/* ── Status select (table) ── */
document.querySelectorAll('.status-select').forEach(function(sel) {
  sel.addEventListener('change', async function() {
    var d = await postJSON(this.dataset.url, { status: this.value });
    if (!d.success) { alert(d.message || 'Gagal update status'); this.value = this.dataset.prev || this.value; return; }
    this.dataset.prev = this.value;
    updateStatCards(d.summary);
    var tr = this.closest('tr');
    if (tr) tr.style.opacity = ['done','cancelled'].indexOf(this.value) >= 0 ? '.55' : '';
  });
  sel.dataset.prev = sel.value;
});

/* ── Kanban col count ── */
function updateColCount(col) {
  if (!col) return;
  var count = col.querySelectorAll('.tli-kcard').length;
  var badge = document.getElementById('kanban-count-' + col.dataset.status);
  if (badge) badge.textContent = count;
  var empty = col.querySelector('.kanban-empty');
  if (empty) empty.style.display = count > 0 ? 'none' : '';
}

/* ── Delete ── */
function bindDel() {
  document.querySelectorAll('.btn-del').forEach(function(btn) {
    if (btn._bound) return;
    btn._bound = true;
    btn.addEventListener('click', async function() {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      var r = await fetch(this.dataset.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ _csrf: CSRF_TOKEN })
      });
      var d = await r.json();
      if (!d.success) { alert(d.message || 'Gagal hapus'); return; }
      var id = this.dataset.id;
      var trow  = document.getElementById('trow-' + id);
      var kcard = document.getElementById('kcard-' + id);
      if (trow)  trow.remove();
      if (kcard) { var kcol = kcard.closest('.kanban-col'); kcard.remove(); updateColCount(kcol); }
      updateStatCards(d.summary);
    });
  });
}
bindDel();

/* ── Kanban drag-drop ── */
if (IS_ADMIN_LIKE) {
  document.querySelectorAll('.kanban-col').forEach(function(col) {
    Sortable.create(col, {
      group: 'kanban', animation: 150,
      ghostClass: 'sortable-ghost', dragClass: 'sortable-drag',
      onEnd: async function(evt) {
        var card = evt.item, newCol = evt.to, oldCol = evt.from;
        var newStatus = newCol.dataset.status, id = card.dataset.id;
        if (newStatus === card.dataset.status) { updateColCount(newCol); return; }
        card.dataset.status = newStatus;
        var d = await postJSON(card.dataset.url, { status: newStatus });
        if (!d.success) {
          alert(d.message || 'Gagal update status');
          oldCol.appendChild(card); card.dataset.status = oldCol.dataset.status;
          updateColCount(oldCol); updateColCount(newCol); return;
        }
        updateColCount(oldCol); updateColCount(newCol); updateStatCards(d.summary);
      }
    });
  });
}

/* ── Progress Notes Modal ── */
var _notesCtx = {};

var modalNotesEl = document.getElementById('modalNotes');
modalNotesEl.addEventListener('show.bs.modal', function(e) {
  var btn = e.relatedTarget;
  if (!btn) return;
  _notesCtx = {
    id: btn.dataset.id, status: btn.dataset.status,
    canDone: btn.dataset.canDone === '1',
    urlGet: btn.dataset.urlGet, urlPost: btn.dataset.urlPost,
    urlStatus: btn.dataset.urlStatus, delBase: btn.dataset.deleteBase,
  };
  document.getElementById('notes-desc').textContent = btn.dataset.desc || '';
  document.getElementById('note-input').value = '';
  document.getElementById('done-bar').style.display = _notesCtx.canDone ? '' : 'none';
  loadNotes();
});

async function loadNotes() {
  var thread = document.getElementById('notes-thread');
  thread.innerHTML = '<div class="text-center tli-text-muted py-4" style="font-size:13px;">Memuat…</div>';
  try {
    var r = await fetch(_notesCtx.urlGet);
    var notes = await r.json();
    renderNotes(Array.isArray(notes) ? notes : (notes.notes || []));
    updateNoteBubble(_notesCtx.id, Array.isArray(notes) ? notes.length : (notes.notes || []).length);
  } catch(e) {
    thread.innerHTML = '<div class="text-center tli-text-muted py-3">Gagal memuat notes.</div>';
  }
}

function renderNotes(notes) {
  var thread = document.getElementById('notes-thread');
  if (!notes.length) {
    thread.innerHTML = '<div class="text-center tli-text-muted py-4" style="font-size:13px;">Belum ada progress note.</div>';
    return;
  }
  thread.innerHTML = notes.map(function(n) {
    var initials = (n.author_name || '?').charAt(0).toUpperCase();
    var canDel   = n.can_delete ? '<button class="tli-nt-del" data-note-id="' + n.id + '" title="Hapus">&times;</button>' : '';
    var txt      = esc(n.note || '').replace(/@([\w\-]+)/g, '<strong style="color:var(--brand)">@$1</strong>');
    return '<div class="tli-nt-item" id="note-item-' + n.id + '">' +
      '<span class="tli-avatar tli-avatar-xs flex-shrink-0">' + initials + '</span>' +
      '<div class="tli-nt-body">' +
        '<div class="tli-nt-meta">' +
          '<span class="tli-nt-name">' + esc(n.author_name || '') + '</span>' +
          '<div class="d-flex align-items-center gap-2">' +
            '<span class="tli-nt-time">' + esc(n.created_at_human || n.created_at || '') + '</span>' +
            canDel +
          '</div>' +
        '</div>' +
        '<div class="tli-nt-text">' + txt + '</div>' +
      '</div>' +
    '</div>';
  }).join('');
  thread.querySelectorAll('.tli-nt-del').forEach(function(btn) {
    btn.addEventListener('click', async function() {
      if (!confirm('Hapus note ini?')) return;
      var nid = this.dataset.noteId;
      var r   = await fetch(_notesCtx.delBase + '/' + nid + '/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
        body: JSON.stringify({ _csrf: CSRF_TOKEN })
      });
      var d = await r.json();
      if (!d.success) { alert(d.message || 'Gagal hapus'); return; }
      var el = document.getElementById('note-item-' + nid);
      if (el) el.remove();
      updateNoteBubble(_notesCtx.id, d.note_count != null ? d.note_count : 0);
    });
  });
  thread.scrollTop = thread.scrollHeight;
}

async function sendNote() {
  var inp  = document.getElementById('note-input');
  var text = inp.value.trim();
  if (!text) return;
  var btn = document.getElementById('btn-send-note');
  btn.disabled = true;
  var d = await postJSON(_notesCtx.urlPost, { note: text });
  btn.disabled = false;
  if (!d.success) { alert(d.message || 'Gagal kirim'); return; }
  inp.value = '';
  hideMentionDrop();
  loadNotes();
}
document.getElementById('btn-send-note').addEventListener('click', sendNote);
document.getElementById('note-input').addEventListener('keydown', function(e) {
  if (e.key === 'Enter' && e.ctrlKey) { e.preventDefault(); sendNote(); }
});

/* Tandai selesai */
document.getElementById('btn-mark-done').addEventListener('click', async function() {
  var d = await postJSON(_notesCtx.urlStatus, { status: 'done' });
  if (!d.success) { alert(d.message || 'Gagal'); return; }
  document.getElementById('done-bar').style.display = 'none';
  _notesCtx.canDone = false;
  var kcard = document.getElementById('kcard-' + _notesCtx.id);
  if (kcard) {
    var oldCol = kcard.closest('.kanban-col');
    var newCol = document.getElementById('kanban-col-done');
    if (newCol && oldCol !== newCol) { newCol.appendChild(kcard); kcard.dataset.status = 'done'; updateColCount(oldCol); updateColCount(newCol); }
    kcard.classList.add('tli-kcard-faded');
  }
  var trow = document.getElementById('trow-' + _notesCtx.id);
  if (trow) trow.style.opacity = '.55';
  updateStatCards(d.summary);
});

/* ── @mention autocomplete ── */
function hideMentionDrop() {
  document.getElementById('mention-dropdown').style.display = 'none';
  _mentionFocusIdx = -1;
}
document.getElementById('note-input').addEventListener('input', function() {
  var val = this.value, pos = this.selectionStart;
  var chunk = val.slice(0, pos);
  var m = chunk.match(/@([\w\-]*)$/);
  var drop = document.getElementById('mention-dropdown');
  if (!m) { hideMentionDrop(); return; }
  var q = m[1].toLowerCase();
  var matches = ALL_USERS.filter(function(u) { return u.name.toLowerCase().indexOf(q) >= 0; }).slice(0, 6);
  if (!matches.length) { hideMentionDrop(); return; }
  drop.innerHTML = matches.map(function(u, i) {
    return '<div class="tli-mention-item" data-name="' + esc(u.name) + '" data-idx="' + i + '">' + esc(u.name) + '</div>';
  }).join('');
  drop.style.display = '';
  _mentionFocusIdx = -1;
  drop.querySelectorAll('.tli-mention-item').forEach(function(item) {
    item.addEventListener('mousedown', function(e) { e.preventDefault(); insertMention(this.dataset.name); });
  });
});
document.getElementById('note-input').addEventListener('keydown', function(e) {
  var drop = document.getElementById('mention-dropdown');
  var items = drop.querySelectorAll('.tli-mention-item');
  if (!items.length || drop.style.display === 'none') return;
  if (e.key === 'ArrowDown') { e.preventDefault(); _mentionFocusIdx = Math.min(_mentionFocusIdx+1,items.length-1); items.forEach(function(el,i){el.classList.toggle('focused',i===_mentionFocusIdx);}); }
  else if (e.key === 'ArrowUp') { e.preventDefault(); _mentionFocusIdx = Math.max(_mentionFocusIdx-1,0); items.forEach(function(el,i){el.classList.toggle('focused',i===_mentionFocusIdx);}); }
  else if (e.key === 'Enter' && _mentionFocusIdx >= 0) { e.preventDefault(); insertMention(items[_mentionFocusIdx].dataset.name); }
  else if (e.key === 'Escape') { hideMentionDrop(); }
});
function insertMention(name) {
  var inp = document.getElementById('note-input');
  var pos = inp.selectionStart, val = inp.value;
  var chunk = val.slice(0, pos);
  var before = chunk.replace(/@([\w\-]*)$/, '@' + name + ' ');
  inp.value = before + val.slice(pos);
  inp.selectionStart = inp.selectionEnd = before.length;
  inp.focus(); hideMentionDrop();
}
document.addEventListener('click', function(e) {
  if (!e.target.closest('#mention-dropdown') && !e.target.closest('#note-input')) hideMentionDrop();
});

/* ── Modal: Tambah TL ── */
var formTambah = document.getElementById('formTambahTL');
if (formTambah) {
  formTambah.addEventListener('submit', async function(e) {
    e.preventDefault();
    var alertEl = document.getElementById('tl-form-alert');
    var saveBtn = document.getElementById('btn-save-tl');
    alertEl.classList.add('d-none');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan…';
    var data = Object.fromEntries(new FormData(this));
    var d    = await postJSON(BASE + '/tindak-lanjut/store', data);
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
    if (!d.success) { alertEl.textContent = d.message || 'Gagal menyimpan.'; alertEl.classList.remove('d-none'); return; }
    bootstrap.Modal.getInstance(document.getElementById('modalTambahTL')).hide();
    formTambah.reset();
    window.location.reload();
  });
}

})();
</script>
