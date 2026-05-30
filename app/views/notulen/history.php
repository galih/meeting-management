<?php
$baseUrl = rtrim(BASE_URL, '/');
// controller mengirim $histories (plural)
$list = $histories ?? [];
?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">
      Riwayat Notulen &mdash; <?= htmlspecialchars($meeting['title']) ?>
    </h3>
    <div class="card-options">
      <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="btn btn-sm btn-outline-secondary">
        &larr; Kembali ke Editor
      </a>
    </div>
  </div>

  <?php if (empty($list)): ?>
  <div class="card-body text-center text-muted py-5">
    <p class="mb-0">Belum ada riwayat perubahan untuk notulen ini.</p>
  </div>
  <?php else: ?>
  <div class="list-group list-group-flush">
    <?php $total = count($list); foreach ($list as $i => $h): ?>
    <div class="list-group-item">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-blue-lt fw-semibold">v<?= $h['version'] ?? ($total - $i) ?></span>
          <span class="text-muted small">
            diedit oleh
            <strong><?= htmlspecialchars($h['editor_name'] ?? '-') ?></strong>
          </span>
        </div>
        <small class="text-muted text-nowrap ms-3">
          <?= date('d M Y, H:i', strtotime($h['created_at'])) ?>
        </small>
      </div>
      <details>
        <summary class="btn btn-sm btn-outline-secondary mt-1">Lihat konten</summary>
        <div class="mt-2 p-3 bg-light rounded">
          <?php
            $raw = $h['content'] ?? '';
            $decoded = json_decode($raw, true);
            if ($decoded && isset($decoded['ops'])) {
              // Quill Delta — tampilkan teks mentah
              $texts = array_map(fn($op) => $op['insert'] ?? '', $decoded['ops']);
              echo '<div class="small" style="white-space:pre-wrap;word-break:break-word;max-height:300px;overflow-y:auto;">' . htmlspecialchars(implode('', $texts)) . '</div>';
            } elseif ($decoded !== null) {
              // JSON lain (EditorJS dsb)
              echo '<pre class="small mb-0" style="white-space:pre-wrap;word-break:break-word;max-height:300px;overflow-y:auto;">' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
            } else {
              // HTML biasa
              echo '<div class="small" style="max-height:300px;overflow-y:auto;">' . $raw . '</div>';
            }
          ?>
        </div>
      </details>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
