<?php
$statConfig = [
    ['key'=>'total_meetings',  'label'=>'Total Meeting',     'color'=>'blue',   'suffix'=>''],
    ['key'=>'meeting_today',   'label'=>'Meeting Hari Ini',  'color'=>'orange', 'suffix'=>''],
    ['key'=>'tl_pending',      'label'=>'Tugas Pending',     'color'=>'yellow', 'suffix'=>''],
    ['key'=>'tl_overdue',      'label'=>'Tugas Terlambat',   'color'=>'red',    'suffix'=>''],
    ['key'=>'tl_done',         'label'=>'Tugas Selesai',     'color'=>'green',  'suffix'=>''],
    ['key'=>'notif_unread',    'label'=>'Notif Belum Dibaca','color'=>'purple', 'suffix'=>''],
];
if ($user['role'] === 'admin') {
    array_splice($statConfig, 1, 0, [['key'=>'total_users','label'=>'Total User Aktif','color'=>'teal','suffix'=>'']]);
}
?>

<!-- Stat Cards -->
<div class="row row-deck row-cards mb-4">
  <?php foreach ($statConfig as $sc): ?>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center">
          <div class="subheader mb-2 text-muted"><?= $sc['label'] ?></div>
        </div>
        <div class="h1 mb-0"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?><?= $sc['suffix'] ?></div>
        <div class="d-flex mt-2">
          <span class="text-<?= $sc['color'] ?> d-flex align-items-center">
            <span class="status-dot status-dot-animated bg-<?= $sc['color'] ?> me-2"></span>
            <?= $sc['label'] ?>
          </span>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row row-cards">

  <!-- Meeting Mendatang -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>Meeting Mendatang (7 Hari)
        </h3>
        <div class="card-options">
          <a href="/meetings" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($upcoming)): ?>
        <div class="list-group-item text-center text-muted py-4">Tidak ada meeting mendatang</div>
        <?php endif; ?>
        <?php foreach ($upcoming as $m):
          $start     = new DateTime($m['start_datetime']);
          $isToday   = $start->format('Y-m-d') === date('Y-m-d');
        ?>
        <a href="/meetings/<?= $m['id'] ?>" class="list-group-item list-group-item-action">
          <div class="row align-items-center">
            <div class="col-auto">
              <div class="text-center" style="width:40px;">
                <div class="fw-bold" style="font-size:18px;color:#f76707;"><?= $start->format('d') ?></div>
                <div class="text-muted" style="font-size:10px;text-transform:uppercase;"><?= $start->format('M') ?></div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex justify-content-between">
                <span class="fw-semibold"><?= htmlspecialchars($m['title']) ?></span>
                <?php if ($isToday): ?>
                <span class="badge bg-orange-lt text-orange">Hari ini</span>
                <?php endif; ?>
              </div>
              <div class="text-muted small">
                🕐 <?= $start->format('H:i') ?> &nbsp;|&nbsp;
                📍 <?= htmlspecialchars($m['location'] ?? 'Lokasi belum diset') ?> &nbsp;|&nbsp;
                👥 <?= $m['total_peserta'] ?> peserta
              </div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tindak Lanjut Deadline Terdekat -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="24" height="24" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <polyline points="9 11 12 14 22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>Tindak Lanjut Terdekat
        </h3>
        <div class="card-options">
          <a href="/tindak-lanjut" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($tlDeadline)): ?>
        <div class="list-group-item text-center text-muted py-4">Tidak ada tindak lanjut aktif</div>
        <?php endif; ?>
        <?php foreach ($tlDeadline as $tl):
          $isOverdue = $tl['deadline'] && $tl['deadline'] < date('Y-m-d');
        ?>
        <div class="list-group-item <?= $isOverdue ? 'bg-red-lt' : '' ?>">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <div class="fw-semibold"><?= htmlspecialchars($tl['deskripsi']) ?></div>
              <div class="text-muted small">
                📋 <?= htmlspecialchars($tl['meeting_title']) ?> &nbsp;|&nbsp;
                👤 <?= htmlspecialchars($tl['assigned_name'] ?? 'Belum ditugaskan') ?>
              </div>
            </div>
            <div class="text-end ms-2">
              <?php if ($tl['deadline']): ?>
              <div class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?> small">
                <?= date('d M', strtotime($tl['deadline'])) ?>
                <?= $isOverdue ? ' ⚠️' : '' ?>
              </div>
              <?php endif; ?>
              <span class="badge bg-<?= match($tl['priority']) {
                'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
              } ?>-lt"><?= ucfirst($tl['priority']) ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Aktivitas Terbaru -->
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Aktivitas Terbaru</h3>
        <div class="card-options">
          <a href="/notifications" class="btn btn-sm btn-outline-secondary">Semua Notifikasi</a>
        </div>
      </div>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead><tr><th>Tipe</th><th>Pesan</th><th>Waktu</th><th>Status</th></tr></thead>
          <tbody>
            <?php if (empty($recentActivity)): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada aktivitas</td></tr>
            <?php endif; ?>
            <?php foreach ($recentActivity as $act):
              $icons = ['meeting_invite'=>'📅','notulen_update'=>'📝','tindak_lanjut_due'=>'⚠️'];
              $icon  = $icons[$act['type']] ?? '🔔';
              $data  = json_decode($act['data'] ?? '{}', true);
            ?>
            <tr>
              <td><span class="badge bg-blue-lt"><?= $icon ?> <?= htmlspecialchars($act['type']) ?></span></td>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($act['title']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($act['message']) ?></div>
              </td>
              <td class="text-muted small">
                <?php
                  $diff = time() - strtotime($act['created_at']);
                  if ($diff < 60)      echo $diff . 'd lalu';
                  elseif ($diff < 3600) echo floor($diff/60) . 'm lalu';
                  elseif ($diff < 86400) echo floor($diff/3600) . 'j lalu';
                  else echo date('d M Y', strtotime($act['created_at']));
                ?>
              </td>
              <td>
                <?= $act['is_read'] 
                  ? '<span class="badge bg-green-lt">Dibaca</span>'
                  : '<span class="badge bg-orange-lt">Baru</span>' ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
