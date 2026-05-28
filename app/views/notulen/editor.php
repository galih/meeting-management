<?php
$baseUrl = rtrim(BASE_URL, '/');
$pdfUrl  = $baseUrl . '/notulen/' . $meeting['id'] . '/export-pdf';
$histUrl = $baseUrl . '/notulen/' . $meeting['id'] . '/history';
$backUrl = $baseUrl . '/meetings/' . $meeting['id'];
$canEdit = Auth::hasRole('admin', 'sekretaris');

$statusBadge   = ['pending'=>'secondary','in_progress'=>'blue','done'=>'green','cancelled'=>'red'];
$priorityBadge = ['high'=>'red','medium'=>'yellow','low'=>'green'];
$meetingBadge  = ['scheduled'=>'blue','ongoing'=>'orange','done'=>'green','cancelled'=>'red'];
?>

<div class="row g-3">

  <!-- Editor Utama -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2 text-brand" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Notulen: <?= htmlspecialchars($meeting['title']) ?>
        </h3>
        <div class="card-options d-flex align-items-center gap-2">
          <span id="sync-status" class="badge bg-green-lt text-green">
            <span class="status-dot status-dot-animated bg-green d-inline-block me-1"></span>Live
          </span>
          <span id="save-status" class="text-muted small">Tersimpan</span>
          <?php if ($canEdit): ?>
          <button id="btn-save-manual" class="btn btn-sm btn-primary ms-1">💾 Simpan</button>
          <?php endif; ?>
          <a href="<?= $pdfUrl ?>" target="_blank" class="btn btn-sm btn-outline-danger ms-1">🖨️ PDF</a>
          <?php if ($canEdit): ?>
          <a href="<?= $histUrl ?>" class="btn btn-sm btn-outline-secondary ms-1">Riwayat</a>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!$canEdit): ?>
      <div class="alert alert-info alert-dismissible m-3 mb-0 py-2">
        🔒 Anda hanya bisa membaca notulen ini.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="card-body p-0">
        <div id="quill-editor"
             style="min-height:480px; font-size:15px; border:none;"
             class="p-1"></div>
      </div>
    </div>

    <!-- Panel Komentar -->
    <div class="card mt-3" id="comment-panel">
      <div class="card-header">
        <h4 class="card-title">
          💬 Diskusi
          <span class="badge bg-blue-lt text-blue ms-1" id="comment-count">0</span>
        </h4>
        <div class="card-options">
          <button class="btn btn-sm btn-outline-secondary" id="btn-toggle-resolved">
            Tampilkan Selesai
          </button>
        </div>
      </div>
      <div class="card-body p-0">
        <div id="comment-list" class="px-3 py-2"></div>
      </div>
      <div class="card-footer">
        <div class="d-flex gap-2 align-items-start">
          <span class="avatar avatar-sm"
                style="background:var(--brand);color:#fff;font-weight:700;flex-shrink:0;">
            <?= strtoupper(mb_substr($user['name'], 0, 1)) ?>
          </span>
          <div class="flex-fill">
            <div class="position-relative">
              <div id="mention-dropdown" class="dropdown-menu"></div>
              <textarea id="comment-input" class="form-control" rows="2"
                        placeholder="Tulis komentar... (ketik @ untuk mention, Enter untuk kirim)"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <small class="text-muted" id="reply-indicator"></small>
              <button class="btn btn-sm btn-primary" id="btn-submit-comment">Kirim</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar Kanan -->
  <div class="col-lg-4">

    <!-- Info Meeting -->
    <div class="card mb-3">
      <div class="card-header"><h4 class="card-title">Info Meeting</h4></div>
      <div class="card-body py-2">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted">Lokasi</dt>
          <dd class="col-7"><?= htmlspecialchars($meeting['location'] ?? '-') ?></dd>
          <dt class="col-5 text-muted">Mulai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['start_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Selesai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['end_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Status</dt>
          <dd class="col-7">
            <span class="badge bg-<?= $meetingBadge[$meeting['status']] ?? 'secondary' ?>-lt">
              <?= ucfirst($meeting['status']) ?>
            </span>
          </dd>
        </dl>
      </div>
      <div class="card-footer py-2">
        <a href="<?= $backUrl ?>" class="btn btn-sm btn-outline-secondary w-100">
          &larr; Kembali ke Detail Meeting
        </a>
      </div>
    </div>

    <!-- ======================================================
         PANEL LAMPIRAN (Attachments)
         Menggunakan API & JS yang sama dengan show.php
         ====================================================== -->
    <div class="card mb-3" id="attachment-panel">
      <div class="card-header">
        <h4 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1 text-brand" width="18" height="18"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
          </svg>
          Lampiran
          <span class="badge bg-orange-lt text-orange ms-1" id="attach-count">0</span>
        </h4>
        <?php if ($canEdit): ?>
        <div class="card-options">
          <button class="btn btn-sm btn-primary" id="btn-show-upload-form">
            + Upload
          </button>
        </div>
        <?php endif; ?>
      </div>

      <!-- Form Upload (tersembunyi, muncul saat klik Upload) -->
      <?php if ($canEdit): ?>
      <div id="upload-form-wrapper" style="display:none;" class="px-3 pt-3 pb-1 border-bottom">
        <form id="form-upload-attachment" enctype="multipart/form-data">
          <div class="mb-2">
            <label class="form-label required mb-1" style="font-size:12px;">Pilih File</label>
            <input type="file" id="attach-file" class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
            <div class="form-text" style="font-size:11px;">PDF, Office, Gambar, ZIP — maks. 10 MB</div>
          </div>
          <div class="mb-2">
            <label class="form-label mb-1" style="font-size:12px;">Kategori</label>
            <select id="attach-category" class="form-select form-select-sm">
              <option value="dokumen">📄 Dokumen</option>
              <option value="presentasi">📊 Presentasi</option>
              <option value="gambar">🖼️ Gambar</option>
              <option value="lainnya">📎 Lainnya</option>
            </select>
          </div>
          <div class="d-flex gap-2 mb-2">
            <button type="submit" class="btn btn-sm btn-primary flex-fill" id="btn-do-upload">
              <span id="upload-spinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
              Upload
            </button>
            <button type="button" class="btn btn-sm btn-link text-muted" id="btn-cancel-upload">Batal</button>
          </div>
          <div id="upload-alert" class="d-none"></div>
        </form>
      </div>
      <?php endif; ?>

      <!-- Daftar File -->
      <div id="attachment-list" class="list-group list-group-flush" style="max-height:320px;overflow-y:auto;">
        <div class="list-group-item text-center text-muted py-3 small">
          <span class="spinner-border spinner-border-sm"></span>
        </div>
      </div>
    </div>
    <!-- /END PANEL LAMPIRAN -->

    <!-- Tindak Lanjut -->
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Tindak Lanjut</h4>
        <?php if ($canEdit): ?>
        <div class="card-options">
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                  data-bs-target="#modalTL">+ Tambah</button>
        </div>
        <?php endif; ?>
      </div>
      <div class="list-group list-group-flush" id="tl-list">
        <?php if (empty($tindakLanjutList)): ?>
        <div class="list-group-item text-muted text-center py-3 small">Belum ada tindak lanjut</div>
        <?php endif; ?>
        <?php foreach ($tindakLanjutList as $tl): ?>
        <div class="list-group-item px-3 py-2">
          <div class="d-flex justify-content-between align-items-start">
            <span class="small fw-semibold"><?= htmlspecialchars($tl['description']) ?></span>
            <span class="badge bg-<?= $priorityBadge[$tl['priority']] ?? 'secondary' ?>-lt ms-1"
                  style="font-size:9px;"><?= ucfirst($tl['priority']) ?></span>
          </div>
          <div class="text-muted" style="font-size:11px;">
            👤 <?= htmlspecialchars($tl['assigned_name'] ?? '-') ?>
            <?php if (!empty($tl['due_date'])): ?>
              | 📅 <?= date('d M Y', strtotime($tl['due_date'])) ?>
            <?php endif; ?>
          </div>
          <span class="badge bg-<?= $statusBadge[$tl['status']] ?? 'secondary' ?>"
                style="font-size:9px;"><?= ucfirst(str_replace('_', ' ', $tl['status'])) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div><!-- /col-lg-4 -->
</div><!-- /row -->

<!-- Modal Tindak Lanjut -->
<?php if ($canEdit): ?>
<div class="modal modal-blur fade" id="modalTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Tindak Lanjut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Deskripsi</label>
          <textarea id="tl2-desk" class="form-control" rows="3" required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Ditugaskan ke</label>
            <select id="tl2-assign" class="form-select">
              <option value="">-- Pilih --</option>
              <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Deadline</label>
            <input type="date" id="tl2-deadline" class="form-control">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Prioritas</label>
          <select id="tl2-priority" class="form-select">
            <option value="low">Rendah</option>
            <option value="medium" selected>Sedang</option>
            <option value="high">Tinggi</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-tl2-save" class="btn btn-primary">Simpan</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
// Inject data meeting_id untuk JS attachments
$scripts = ($scripts ?? '') . '
<script>
// Pastikan MEETING_ID tersedia untuk meeting-attachments.js
if (typeof MEETING_ID === "undefined") {
  window.MEETING_ID = ' . (int)$meeting['id'] . ';
}
</script>
<script src="' . $baseUrl . '/assets/js/meeting-attachments.js"></script>
';
?>