<?php
$baseUrl    = rtrim(BASE_URL, '/');
$statConfig = [
    ['key'=>'total_meetings',  'label'=>'Total Meeting',      'color'=>'blue',   'icon'=>'📅'],
    ['key'=>'meeting_today',   'label'=>'Meeting Hari Ini',   'color'=>'orange', 'icon'=>'🗓️'],
    ['key'=>'tl_pending',      'label'=>'Tugas Pending',      'color'=>'yellow', 'icon'=>'⏳'],
    ['key'=>'tl_overdue',      'label'=>'Tugas Terlambat',    'color'=>'red',    'icon'=>'⚠️'],
    ['key'=>'tl_done',         'label'=>'Tugas Selesai',      'color'=>'green',  'icon'=>'✅'],
    ['key'=>'notif_unread',    'label'=>'Notif Belum Dibaca', 'color'=>'purple', 'icon'=>'🔔'],
];
if ($user['role'] === 'admin') {
    array_splice($statConfig, 1, 0, [
        ['key'=>'total_users','label'=>'User Aktif','color'=>'teal','icon'=>'👥'],
    ]);
}
?>

<!-- Stat Cards -->
<div class="row row-deck row-cards g-3 mb-4">
  <?php foreach ($statConfig as $sc): ?>
  <div class="col-6 col-lg-3">
    <div class="card stat-card position-relative">
      <div class="card-body">
        <div class="subheader"><?= htmlspecialchars($sc['label']) ?></div>
        <div class="h1"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
        <div class="stat-footer">
          <span class="status-dot status-dot-animated bg-<?= $sc['color'] ?>"></span>
          <span class="text-muted"><?= htmlspecialchars($sc['label']) ?></span>
        </div>
        <div class="stat-icon"><?= $sc['icon'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row row-cards g-3">

  <!-- Meeting Mendatang -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Meeting Mendatang <span class="text-muted fw-normal ms-1" style="font-size:12px;">7 hari ke depan</span>
        </h3>
        <div class="card-options">
          <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($upcoming)): ?>
        <div class="list-group-item text-center text-muted py-5">
          <div style="font-size:32px;margin-bottom:.4rem;">📭</div>
          Tidak ada meeting mendatang
        </div>
        <?php endif; ?>
        <?php foreach ($upcoming as $m):
          $start   = new DateTime($m['start_datetime']);
          $isToday = $start->format('Y-m-d') === date('Y-m-d');
        ?>
        <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>" class="list-group-item list-group-item-action">
          <div class="row align-items-center g-2">
            <div class="col-auto">
              <div class="text-center" style="width:44px;background:var(--brand-light);border-radius:8px;padding:6px 4px;">
                <div class="fw-bold lh-1" style="font-size:20px;color:var(--brand);"><?= $start->format('d') ?></div>
                <div class="text-muted" style="font-size:9px;text-transform:uppercase;letter-spacing:.05em;"><?= $start->format('M') ?></div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <span class="fw-semibold" style="font-size:13.5px;"><?= htmlspecialchars($m['title']) ?></span>
                <?php if ($isToday): ?>
                <span class="badge bg-orange-lt text-orange" style="white-space:nowrap;">Hari ini</span>
                <?php endif; ?>
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:2px;">
                🕐 <?= $start->format('H:i') ?>
                &nbsp;·&nbsp;
                📍 <?= htmlspecialchars($m['location'] ?: 'Lokasi belum diset') ?>
                &nbsp;·&nbsp;
                👥 <?= (int)$m['total_peserta'] ?> peserta
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
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 11 12 14 22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          Tindak Lanjut Terdekat
        </h3>
        <div class="card-options">
          <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($tlDeadline)): ?>
        <div class="list-group-item text-center text-muted py-5">
          <div style="font-size:32px;margin-bottom:.4rem;">🎉</div>
          Tidak ada tindak lanjut aktif
        </div>
        <?php endif; ?>
        <?php foreach ($tlDeadline as $tl):
          $isOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d');
        ?>
        <div class="list-group-item <?= $isOverdue ? 'bg-red-lt' : '' ?>">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="flex-fill overflow-hidden">
              <div class="fw-semibold text-truncate" style="font-size:13.5px;">
                <?= htmlspecialchars($tl['description'] ?? '') ?>
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:2px;">
                📋 <?= htmlspecialchars($tl['meeting_title']) ?>
                &nbsp;·&nbsp;
                👤 <?= htmlspecialchars($tl['assigned_name'] ?? 'Belum ditugaskan') ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <?php if (!empty($tl['due_date'])): ?>
              <div class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>" style="font-size:12px;">
                <?= date('d M', strtotime($tl['due_date'])) ?><?= $isOverdue ? ' ⚠️' : '' ?>
              </div>
              <?php endif; ?>
              <span class="badge bg-<?= match($tl['priority'] ?? '') {
                'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
              } ?>-lt"><?= ucfirst($tl['priority'] ?? '-') ?></span>
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
          <a href="<?= $baseUrl ?>/notifications" class="btn btn-sm btn-outline-secondary">Semua Notifikasi</a>
        </div>
      </div>
      <?php if (empty($recentActivity)): ?>
      <div class="card-body text-center text-muted py-5">
        <div style="font-size:36px;margin-bottom:.5rem;">🔔</div>
        Belum ada aktivitas
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr>
              <th>Tipe</th>
              <th>Pesan</th>
              <th style="white-space:nowrap;">Waktu</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $icons = [
                'meeting_invite'      => '📅',
                'meeting_invitation'  => '📅',
                'notulen_update'      => '📝',
                'notulen_comment'     => '💬',
                'tindak_lanjut_due'   => '⚠️',
                'tindak_lanjut'       => '✅',
                'comment_mention'     => '@',
              ];
              foreach ($recentActivity as $act):
                $icon = $icons[$act['type']] ?? '🔔';
                $diff = time() - strtotime($act['created_at']);
                if ($diff < 60)        $timeAgo = $diff . 'd lalu';
                elseif ($diff < 3600)  $timeAgo = floor($diff/60) . 'm lalu';
                elseif ($diff < 86400) $timeAgo = floor($diff/3600) . 'j lalu';
                else                   $timeAgo = date('d M Y', strtotime($act['created_at']));
            ?>
            <tr>
              <td style="white-space:nowrap;">
                <span class="badge bg-blue-lt">
                  <?= $icon ?> <?= htmlspecialchars(str_replace('_',' ',$act['type'])) ?>
                </span>
              </td>
              <td class="text-muted"><?= htmlspecialchars($act['message']) ?></td>
              <td class="text-muted" style="white-space:nowrap;font-size:12px;"><?= $timeAgo ?></td>
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
      <?php endif; ?>
    </div>
  </div>

</div>
