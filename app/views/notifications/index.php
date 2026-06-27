<?php
$baseUrl   = rtrim(BASE_URL, '/');
$typeIcons = [
    'meeting_invite'      => ['icon' => '📅', 'color' => 'blue'],
    'meeting_invitation'  => ['icon' => '📅', 'color' => 'blue'],
    'tindak_lanjut'       => ['icon' => '✅', 'color' => 'orange'],
    'tindak_lanjut_due'   => ['icon' => '⚠️', 'color' => 'red'],
    'notulen_update'      => ['icon' => '📝', 'color' => 'orange'],
    'notulen_comment'     => ['icon' => '💬', 'color' => 'blue'],
    'comment_mention'     => ['icon' => '@',  'color' => 'purple'],
];

/**
 * Bangun URL yang benar:
 * - Jika $raw sudah full URL (http/https) → pakai langsung
 * - Jika path relatif (misal /notulen/5) → prepend $baseUrl
 * - Kosong → '#'
 * PHP 7.4 compat: ganti str_starts_with dengan strncmp
 */
function buildLink(string $baseUrl, string $raw): string {
    if (empty($raw)) return '#';
    if (strncmp($raw, 'http://',  7) === 0) return $raw;
    if (strncmp($raw, 'https://', 8) === 0) return $raw;
    return $baseUrl . '/' . ltrim($raw, '/');
}
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">Semua Notifikasi</h3>
    <div class="card-options">
      <span class="text-muted small">Total: <?= $total ?? 0 ?> notifikasi</span>
      <?php if (($total ?? 0) > 0): ?>
      <button class="btn btn-sm btn-ghost-secondary ms-2" id="btnMarkAll">Tandai semua dibaca</button>
      <?php endif; ?>
    </div>
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
      $cfg  = $typeIcons[$n['type']] ?? ['icon' => '🔔', 'color' => 'secondary'];
      // Kolom di tabel adalah 'url', bukan 'link'
      $raw  = $n['url'] ?? $n['link'] ?? '';
      $link = buildLink($baseUrl, $raw);
    ?>
    <div class="list-group-item <?= $n['is_read'] ? '' : 'bg-orange-lt' ?>">
      <div class="d-flex align-items-start gap-3">
        <span style="font-size:24px;flex-shrink:0;"><?= $cfg['icon'] ?></span>
        <div class="flex-fill">
          <div class="d-flex justify-content-between">
            <a href="<?= htmlspecialchars($link) ?>"
               class="fw-semibold <?= $n['is_read'] ? 'text-muted' : 'text-dark' ?> text-decoration-none">
              <?= htmlspecialchars($n['message']) ?>
            </a>
            <small class="text-muted text-nowrap ms-3">
              <?= date('d M Y H:i', strtotime($n['created_at'])) ?>
            </small>
          </div>
        </div>
        <?php if (!$n['is_read']): ?>
        <span class="status-dot status-dot-animated bg-orange flex-shrink-0 mt-1"></span>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (($totalPage ?? 1) > 1): ?>
  <div class="card-footer d-flex justify-content-center">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPage; $i++): ?>
      <li class="page-item <?= $i === ($page ?? 1) ? 'active' : '' ?>">
        <a class="page-link" href="<?= $baseUrl ?>/notifications?page=<?= $i ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<script>
const notifBaseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
document.getElementById('btnMarkAll')?.addEventListener('click', async function () {
  await fetch(notifBaseUrl + '/api/notifications/read', {
    method:  'POST',
    headers: {'Content-Type':'application/json'},
    body:    JSON.stringify({ all: true })
  });
  document.querySelectorAll('.bg-orange-lt').forEach(el => el.classList.remove('bg-orange-lt'));
  document.querySelectorAll('.status-dot-animated').forEach(el => el.remove());
  this.remove();
});
</script>
