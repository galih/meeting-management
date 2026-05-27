<div class="card">
  <div class="card-header">
    <h3 class="card-title">Riwayat Notulen</h3>
    <div class="card-options">
      <a href="/notulen/<?= $meetingId ?>" class="btn btn-sm btn-outline-secondary">
        &larr; Kembali ke Editor
      </a>
    </div>
  </div>

  <?php if (empty($histories)): ?>
  <div class="card-body text-center text-muted py-5">Belum ada riwayat perubahan</div>
  <?php else: ?>
  <div class="list-group list-group-flush">
    <?php foreach ($histories as $i => $h): ?>
    <div class="list-group-item">
      <div class="d-flex justify-content-between align-items-start">
        <div>
          <span class="fw-semibold">Versi #<?= count($histories) - $i ?></span>
          <span class="text-muted small ms-2">
            diedit oleh <strong><?= htmlspecialchars($h['editor_name']) ?></strong>
          </span>
        </div>
        <small class="text-muted">
          <?= date('d M Y H:i:s', strtotime($h['edited_at'])) ?>
        </small>
      </div>
      <details class="mt-2">
        <summary class="btn btn-sm btn-outline-secondary">Lihat konten</summary>
        <div class="mt-2 p-2 bg-light rounded small">
          <pre style="white-space:pre-wrap;word-break:break-word;font-size:11px;"><?= htmlspecialchars(json_encode(json_decode($h['content']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
      </details>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
