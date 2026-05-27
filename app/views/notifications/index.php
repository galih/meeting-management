<?php
$typeIcons = [
    'meeting_invite'    => ['icon'=>'📅','color'=>'blue'],
    'notulen_update'    => ['icon'=>'📝','color'=>'orange'],
    'tindak_lanjut_due' => ['icon'=>'⚠️','color'=>'red'],
];
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Semua Notifikasi</h3>
    <div class="card-options text-muted small">Total: <?= $total ?> notifikasi</div>
  </div>

  <div class="list-group list-group-flush">
    <?php if (empty($notifs)): ?>
    <div class="list-group-item text-center text-muted py-5">
      <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
           fill="none" stroke="currentColor" stroke-width="1" class="mb-3 text-muted">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
      </svg>
      <p class="mb-0">Tidak ada notifikasi</p>
    </div>
    <?php endif; ?>

    <?php foreach ($notifs as $n):
      $cfg  = $typeIcons[$n['type']] ?? ['icon'=>'🔔','color'=>'secondary'];
      $data = json_decode($n['data'] ?? '{}', true);
      $link = isset($data['meeting_id']) ? '/meetings/'.$data['meeting_id'] : '#';
    ?>
    <a href="<?= $link ?>" class="list-group-item list-group-item-action <?= $n['is_read'] ? '' : 'bg-orange-lt' ?>">
      <div class="d-flex align-items-start gap-3">
        <span style="font-size:24px;flex-shrink:0;"><?= $cfg['icon'] ?></span>
        <div class="flex-fill">
          <div class="d-flex justify-content-between">
            <span class="fw-semibold <?= $n['is_read'] ? 'text-muted' : '' ?>">
              <?= htmlspecialchars($n['title']) ?>
            </span>
            <small class="text-muted text-nowrap ms-3">
              <?= date('d M Y H:i', strtotime($n['created_at'])) ?>
            </small>
          </div>
          <p class="mb-0 text-muted small"><?= htmlspecialchars($n['message']) ?></p>
        </div>
        <?php if (!$n['is_read']): ?>
        <span class="status-dot status-dot-animated bg-orange flex-shrink-0 mt-1"></span>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Pagination -->
  <?php if ($totalPage > 1): ?>
  <div class="card-footer d-flex justify-content-center">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPage; $i++): ?>
      <li class="page-item <?= $i === $page ? 'active' : '' ?>">
        <a class="page-link" href="/notifications?page=<?= $i ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>
