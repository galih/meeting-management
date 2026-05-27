<?php $baseUrl = rtrim(BASE_URL, '/'); ?>

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

  <?php if (empty($history)): ?>
  <div class="card-body text-center text-muted py-5">
    <p class="mb-0">Belum ada riwayat perubahan untuk notulen ini.</p>
  </div>
  <?php else: ?>
  <div class="list-group list-group-flush">
    <?php foreach ($history as $i => $h): ?>
    <div class="list-group-item">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="fw-semibold">Versi #<?= count($history) - $i ?></span>
          <span class="text-muted small ms-2">
            diedit oleh <strong><?= htmlspecialchars($h['editor_name'] ?? '-') ?></strong>
          </span>
        </div>
        <small class="text-muted text-nowrap ms-3">
          <?= date('d M Y H:i:s', strtotime($h['created_at'])) ?>
        </small>
      </div>
      <details class="mt-2">
        <summary class="btn btn-sm btn-outline-secondary">Lihat konten</summary>
        <div class="mt-2 p-2 bg-light rounded small">
          <pre style="white-space:pre-wrap;word-break:break-word;font-size:11px;max-height:300px;overflow-y:auto;"><?= htmlspecialchars(json_encode(json_decode($h['content']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
      </details>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
