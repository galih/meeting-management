<?php
$baseUrl   = rtrim(BASE_URL, '/');
$statCards = [
  ['key'=>'total',       'label'=>'Total Tugas',    'color'=>'blue'],
  ['key'=>'pending',     'label'=>'Pending',         'color'=>'yellow'],
  ['key'=>'in_progress', 'label'=>'Sedang Berjalan', 'color'=>'orange'],
  ['key'=>'done',        'label'=>'Selesai',         'color'=>'green'],
  ['key'=>'overdue',     'label'=>'Terlambat',       'color'=>'red'],
];
$user = Auth::user();
?>

<!-- Stat Cards -->
<div class="row row-deck row-cards mb-4">
  <?php foreach ($statCards as $sc): ?>
  <div class="col-6 col-lg">
    <div class="card">
      <div class="card-body">
        <div class="subheader text-muted mb-1"><?= $sc['label'] ?></div>
        <div class="h1 mb-0"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
        <div class="mt-1">
          <span class="status-dot bg-<?= $sc['color'] ?> d-inline-block me-1"></span>
          <span class="text-muted small"><?= $sc['label'] ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- Filter + Tabel -->
<div class="card">
  <div class="card-header flex-column flex-md-row gap-2">
    <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="d-flex flex-wrap gap-2 flex-fill">
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
             class="form-control form-control-sm" style="min-width:180px;"
             placeholder="Cari deskripsi...">
      <select name="status" class="form-select form-select-sm" style="width:140px;">
        <option value="">Semua Status</option>
        <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','cancelled'=>'Cancelled'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($status??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <select name="priority" class="form-select form-select-sm" style="width:130px;">
        <option value="">Semua Prioritas</option>
        <?php foreach (['high'=>'High','medium'=>'Medium','low'=>'Low'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($priority??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
      <?php if (($status??'')||($priority??'')||($search??'')): ?>
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-sm btn-ghost-danger">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover">
      <thead>
        <tr>
          <th>Deskripsi</th>
          <th>Meeting</th>
          <th>Ditugaskan ke</th>
          <th>Deadline</th>
          <th>Prioritas</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tindakLanjutList)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data tindak lanjut</td></tr>
        <?php endif; ?>
        <?php foreach ($tindakLanjutList as $tl):
          $overdue = !empty($tl['due_date'])
                     && $tl['due_date'] < date('Y-m-d')
                     && !in_array($tl['status'], ['done','cancelled']);
          $canEdit = Auth::hasRole('admin','sekretaris')
                     || ($user['role']==='peserta' && $tl['assigned_to']==$user['id']);
        ?>
        <tr class="<?= $overdue ? 'table-danger' : '' ?>">
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($tl['description']) ?></div>
            <?php if ($overdue): ?>
            <span class="badge bg-red-lt text-red small">⚠ Terlambat</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= $baseUrl ?>/meetings/<?= $tl['meeting_id'] ?>" class="text-orange small">
              <?= htmlspecialchars($tl['meeting_title']) ?>
            </a>
          </td>
          <td class="text-muted"><?= htmlspecialchars($tl['assignee_name'] ?? '-') ?></td>
          <td class="text-muted small">
            <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-' ?>
          </td>
          <td>
            <span class="badge bg-<?= match($tl['priority']) {
              'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
            } ?>-lt"><?= ucfirst($tl['priority']) ?></span>
          </td>
          <td>
            <?php if ($canEdit): ?>
            <select class="form-select form-select-sm status-select"
                    data-id="<?= $tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                    style="width:130px;">
              <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','cancelled'=>'Cancelled'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $tl['status']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
            <span class="badge bg-<?= match($tl['status']) {
              'pending'=>'secondary','in_progress'=>'blue',
              'done'=>'green','cancelled'=>'red',default=>'secondary'
            } ?>"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <?php if (Auth::hasRole('admin','sekretaris')): ?>
            <button class="btn btn-sm btn-ghost-danger btn-del"
                    data-id="<?= $tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                    title="Hapus">✕</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.querySelectorAll('.status-select').forEach(sel => {
  sel.addEventListener('change', async function () {
    const res = await fetch(this.dataset.url, {
      method:  'POST',
      headers: {'Content-Type':'application/json'},
      body:    JSON.stringify({ status: this.value })
    });
    const d = await res.json();
    if (!d.success) {
      alert(d.message || 'Gagal update status');
    } else if (this.value === 'done') {
      this.closest('tr').style.opacity = '0.5';
    }
  });
});

document.querySelectorAll('.btn-del').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Hapus tindak lanjut ini?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) this.closest('tr').remove();
    else alert(d.message || 'Gagal hapus');
  });
});
</script>
