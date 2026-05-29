<?php
$baseUrl = rtrim(BASE_URL, '/');
$actionGroups = [
    ''        => 'Semua Aksi',
    'auth'    => 'Autentikasi',
    'meeting' => 'Kegiatan',
    'user'    => 'User',
];
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20" height="20"
           viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
      </svg>
      Log Aktivitas
    </h3>
    <div class="card-options">
      <span class="text-muted small me-3">Total: <strong><?= number_format($total) ?></strong> entri</span>
      <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#modalPurge">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2">
          <polyline points="3 6 5 6 21 6"/>
          <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
          <path d="M10 11v6"/><path d="M14 11v6"/>
          <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
        </svg>
        Bersihkan Log
      </button>
    </div>
  </div>

  <!-- Filter -->
  <div class="card-body border-bottom py-3">
    <form method="GET" action="<?= $baseUrl ?>/admin/activity-log" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label form-label-sm">User</label>
        <select name="user_id" class="form-select form-select-sm">
          <option value="">Semua User</option>
          <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] == $u['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label form-label-sm">Modul</label>
        <select name="subject_type" class="form-select form-select-sm">
          <?php foreach ($actionGroups as $val => $label): ?>
          <option value="<?= $val ?>" <?= ($filters['subject_type'] === $val) ? 'selected' : '' ?>>
            <?= $label ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label form-label-sm">Dari Tanggal</label>
        <input type="date" name="date_from" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filters['date_from']) ?>">
      </div>
      <div class="col-md-2">
        <label class="form-label form-label-sm">Sampai Tanggal</label>
        <input type="date" name="date_to" class="form-control form-control-sm"
               value="<?= htmlspecialchars($filters['date_to']) ?>">
      </div>
      <div class="col-md-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary btn-sm">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg> Filter
        </button>
        <a href="<?= $baseUrl ?>/admin/activity-log" class="btn btn-outline-secondary btn-sm">Reset</a>
      </div>
    </form>
  </div>

  <!-- Tabel -->
  <div class="table-responsive">
    <table class="table table-vcenter table-sm card-table">
      <thead>
        <tr>
          <th style="width:160px">Waktu</th>
          <th>User</th>
          <th style="width:110px">Aksi</th>
          <th>Keterangan</th>
          <th style="width:100px">Modul</th>
          <th>IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr><td colspan="6" class="text-center text-muted py-5">Belum ada log aktivitas</td></tr>
        <?php endif; ?>
        <?php foreach ($logs as $log):
          [$label, $bgClass, $textClass] = ActivityLog::badge($log['action']);
        ?>
        <tr>
          <td class="text-muted small">
            <?= date('d M Y', strtotime($log['created_at'])) ?><br>
            <span class="text-muted"><?= date('H:i:s', strtotime($log['created_at'])) ?></span>
          </td>
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($log['user_name'] ?? '—') ?></div>
            <div class="text-muted small"><?= htmlspecialchars($log['user_role'] ?? '') ?></div>
          </td>
          <td>
            <span class="badge <?= $bgClass ?> <?= $textClass ?>"><?= $label ?></span>
          </td>
          <td><?= htmlspecialchars($log['description'] ?? '') ?></td>
          <td>
            <?php if ($log['subject_type']): ?>
            <span class="badge bg-secondary-lt text-secondary">
              <?= htmlspecialchars($log['subject_type']) ?>
              <?= $log['subject_id'] ? '#' . $log['subject_id'] : '' ?>
            </span>
            <?php else: ?>—<?php endif; ?>
          </td>
          <td class="text-muted small"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-muted">
      Halaman <?= $page ?> dari <?= $totalPages ?>
      (<?= number_format($total) ?> entri)
    </p>
    <ul class="pagination m-0 ms-auto">
      <?php if ($page > 1): ?>
      <li class="page-item">
        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
          &laquo; Sebelumnya
        </a>
      </li>
      <?php endif; ?>
      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
          <?= $i ?>
        </a>
      </li>
      <?php endfor; ?>
      <?php if ($page < $totalPages): ?>
      <li class="page-item">
        <a class="page-link" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
          Berikutnya &raquo;
        </a>
      </li>
      <?php endif; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Purge -->
<div class="modal modal-blur fade" id="modalPurge" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Bersihkan Log Lama</div>
        <p class="text-muted mt-2">Hapus semua log yang lebih dari:</p>
        <form id="formPurge" method="POST" action="<?= $baseUrl ?>/admin/activity-log/purge">
          <?= Auth::csrfField() ?>
          <div class="input-group">
            <input type="number" name="days" value="90" min="1" class="form-control">
            <span class="input-group-text">hari</span>
          </div>
          <div class="mt-3 d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-danger">Hapus Log</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
