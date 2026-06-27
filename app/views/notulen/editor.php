<?php
$baseUrl  = rtrim(BASE_URL, '/');
$pdfUrl   = $baseUrl . '/notulen/' . $meeting['id'] . '/export-pdf';
$docxUrl  = $baseUrl . '/notulen/' . $meeting['id'] . '/export-docx';
$histUrl  = $baseUrl . '/notulen/' . $meeting['id'] . '/history';
$backUrl  = $baseUrl . '/meetings/' . $meeting['id'];
$canEdit  = Auth::hasRole('admin', 'sekretaris');

$statusBadge   = ['pending'=>'secondary','in_progress'=>'blue','done'=>'green','cancelled'=>'red'];
$statusLabel   = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];
$priorityBadge = ['high'=>'red','medium'=>'orange','low'=>'green'];
$priorityLabel = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$meetingBadge  = ['scheduled'=>'blue','ongoing'=>'orange','done'=>'green','cancelled'=>'red'];
$meetingStatusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$loc    = $meeting['location'] ?? '';
$isLink = !empty($loc) && (strncmp($loc,'http://',7)===0 || strncmp($loc,'https://',8)===0);
?>

<!-- ============================  HERO HEADER  ============================ -->
<div class="ned-hero mb-4">
  <div class="ned-hero-inner">
    <div class="d-flex flex-wrap align-items-flex-start justify-content-between gap-3">
      <div>
        <nav class="ned-breadcrumb">
          <a href="<?= $backUrl ?>">Detail Kegiatan</a>
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Notulen</span>
        </nav>
        <h1 class="ned-hero-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <?= htmlspecialchars($meeting['title']) ?>
        </h1>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
          <span id="sync-status" class="ned-live-badge">
            <span class="ned-live-dot"></span>Live
          </span>
          <span id="save-status" class="ned-save-status">Tersimpan</span>
          <span class="ned-info-chip"><?= date('d M Y · H:i', strtotime($meeting['start_datetime'])) ?></span>
          <?php if (!empty($loc)): ?>
          <span class="ned-info-chip">
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener" class="ned-loc-link">🔗 Link Kegiatan</a>
            <?php else: ?>
            <?= htmlspecialchars($loc) ?>
            <?php endif; ?>
          </span>
          <?php endif; ?>
        </div>
      </div>
      <div class="ned-hero-actions d-flex flex-wrap gap-2">
        <?php if ($canEdit): ?>
        <button type="button" class="btn ned-btn-tpl" id="btn-pick-template">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Template
        </button>
        <button id="btn-save-manual" class="btn ned-btn-save">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan
        </button>
        <?php endif; ?>
        <div class="dropdown">
          <button class="btn ned-btn-export dropdown-toggle" data-bs-toggle="dropdown">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Export
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="<?= $pdfUrl ?>" target="_blank">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Export PDF
            </a></li>
            <li><a class="dropdown-item" href="<?= $docxUrl ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Export Word (.docx)
            </a></li>
          </ul>
        </div>
        <?php if ($canEdit): ?>
        <a href="<?= $histUrl ?>" class="btn ned-btn-hist">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
          Riwayat
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php if (!$canEdit): ?>
<div class="alert ned-readonly-alert mb-3">
  <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  Anda hanya bisa membaca notulen ini. Edit tidak tersedia.
  <button type="button" class="btn-close btn-close-sm ms-auto" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ============================  BODY  ============================ -->
<div class="row g-3">

  <!-- ===  EDITOR + KOMENTAR  === -->
  <div class="col-lg-8">

    <!-- Editor Card -->
    <div class="card ned-editor-card">
      <div class="card-body p-0">
        <div id="quill-editor" class="ned-quill-area"></div>
      </div>
    </div>

    <!-- Diskusi -->
    <div class="card ned-comment-card mt-3" id="comment-panel">
      <div class="ned-comment-header">
        <div class="d-flex align-items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span class="ned-comment-title">Diskusi</span>
          <span class="ned-count-badge" id="comment-count">0</span>
        </div>
        <button class="btn ned-btn-sm-outline" id="btn-toggle-resolved">Tampilkan Selesai</button>
      </div>
      <div id="comment-list" class="ned-comment-list"></div>
      <div class="ned-comment-footer">
        <div class="d-flex gap-2 align-items-start">
          <span class="ned-user-avatar"><?= strtoupper(mb_substr($user['name'], 0, 1)) ?></span>
          <div class="flex-fill">
            <div class="position-relative">
              <div id="mention-dropdown" class="dropdown-menu"></div>
              <textarea id="comment-input" class="ned-comment-input"
                        rows="2" placeholder="Tulis komentar... (@ untuk mention, Enter untuk kirim)"></textarea>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
              <small class="text-muted" id="reply-indicator"></small>
              <button class="btn ned-btn-send" id="btn-submit-comment">Kirim</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ===  SIDEBAR KANAN  === -->
  <div class="col-lg-4">

    <!-- Info Meeting -->
    <div class="card ned-sidebar-card mb-3">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Info Kegiatan
      </div>
      <div class="ned-sidebar-body">
        <dl class="ned-dl">
          <dt>Lokasi</dt>
          <dd>
            <?php if ($isLink): ?>
            <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener" class="ned-link">Buka Link</a>
            <?php else: ?>
            <?= htmlspecialchars($loc ?: '—') ?>
            <?php endif; ?>
          </dd>
          <dt>Mulai</dt>
          <dd><?= date('d M Y H:i', strtotime($meeting['start_datetime'])) ?></dd>
          <dt>Selesai</dt>
          <dd><?= date('d M Y H:i', strtotime($meeting['end_datetime'])) ?></dd>
          <dt>Status</dt>
          <dd>
            <?php $ms = $meetingBadge[$meeting['status']] ?? 'secondary'; ?>
            <span class="ned-badge ned-badge-<?= $ms ?>">
              <?= $meetingStatusLabel[$meeting['status']] ?? ucfirst($meeting['status']) ?>
            </span>
          </dd>
        </dl>
      </div>
      <div class="ned-sidebar-footer">
        <a href="<?= $backUrl ?>" class="btn ned-btn-back w-100">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Kembali ke Detail Kegiatan
        </a>
      </div>
    </div>

    <!-- Lampiran -->
    <div class="card ned-sidebar-card mb-3" id="attachment-panel" data-meeting-id="<?= (int)$meeting['id'] ?>">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        Lampiran
        <span class="ned-count-badge ms-1" id="attach-count">0</span>
        <?php if ($canEdit): ?>
        <button class="btn ned-btn-add-sm ms-auto" id="btn-show-upload-form">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Upload
        </button>
        <?php endif; ?>
      </div>

      <?php if ($canEdit): ?>
      <div id="upload-form-wrapper" style="display:none;" class="ned-upload-form">
        <form id="form-upload-attachment" enctype="multipart/form-data">
          <div class="mb-2">
            <label class="ned-form-label">Pilih File <span class="text-danger">*</span></label>
            <input type="file" id="attach-file" class="form-control form-control-sm"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip,.rar" required>
            <div class="ned-form-hint">PDF, Office, Gambar, ZIP · maks. 10 MB</div>
          </div>
          <div class="mb-2">
            <label class="ned-form-label">Kategori</label>
            <select id="attach-category" class="form-select form-select-sm">
              <option value="dokumen">Dokumen</option>
              <option value="presentasi">Presentasi</option>
              <option value="gambar">Gambar</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn ned-btn-upload flex-fill" id="btn-do-upload">
              <span id="upload-spinner" class="spinner-border spinner-border-sm me-1 d-none"></span>
              Upload
            </button>
            <button type="button" class="btn ned-btn-cancel" id="btn-cancel-upload">Batal</button>
          </div>
          <div id="upload-alert" class="d-none mt-2"></div>
        </form>
      </div>
      <?php endif; ?>

      <div id="attachment-list" class="ned-attachment-list">
        <div class="ned-attach-loading">
          <span class="spinner-border spinner-border-sm"></span> Memuat...
        </div>
      </div>
    </div>

    <!-- Tindak Lanjut -->
    <div class="card ned-sidebar-card">
      <div class="ned-sidebar-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tindak Lanjut
        <?php if ($canEdit): ?>
        <button class="btn ned-btn-add-sm ms-auto" data-bs-toggle="modal" data-bs-target="#modalTL">
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Tambah
        </button>
        <?php endif; ?>
      </div>
      <div class="ned-tl-list" id="tl-list">
        <?php if (empty($tindakLanjutList)): ?>
        <div class="ned-tl-empty" id="tl-empty">Belum ada tindak lanjut</div>
        <?php endif; ?>
        <?php foreach ($tindakLanjutList as $tl):
          $pc   = $priorityBadge[$tl['priority']] ?? 'secondary';
          $plbl = $priorityLabel[$tl['priority']]  ?? ucfirst($tl['priority']);
          $sc   = $statusBadge[$tl['status']]       ?? 'secondary';
          $slbl = $statusLabel[$tl['status']]        ?? ucfirst(str_replace('_',' ',$tl['status']));
        ?>
        <div class="ned-tl-item" id="tl-item-<?= (int)$tl['id'] ?>">
          <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
            <span class="ned-tl-desc"><?= htmlspecialchars($tl['description']) ?></span>
            <?php if ($canEdit): ?>
            <button class="ned-tl-del btn-tl-del"
                    data-id="<?= (int)$tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>/delete"
                    title="Hapus">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>
          </div>
          <div class="ned-tl-meta">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($tl['assigned_name'] ?? '—') ?>
            <?php if (!empty($tl['due_date'])): ?>
            · <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <?= date('d M Y', strtotime($tl['due_date'])) ?>
            <?php endif; ?>
          </div>
          <div class="d-flex gap-1 mt-1">
            <span class="ned-badge ned-badge-<?= $pc ?>"><?= $plbl ?></span>
            <span class="ned-badge ned-badge-<?= $sc ?>"><?= $slbl ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- ============================  MODALS  ============================ -->

<?php if ($canEdit): ?>
<!-- Modal Template -->
<div class="modal modal-blur fade" id="modalPickTemplate" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          </span>
          <h5 class="modal-title">Pilih Template Notulen</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning py-2 small mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          Memilih template akan <strong>mengganti</strong> seluruh isi notulen. Simpan dulu jika ada perubahan penting.
        </div>
        <div id="tpl-list-loading" class="text-center py-4">
          <span class="spinner-border spinner-border-sm"></span> Memuat template...
        </div>
        <div id="tpl-list-container" class="row g-3" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah TL -->
<div class="modal modal-blur fade" id="modalTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <span class="ned-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </span>
          <h5 class="modal-title">Tambah Tindak Lanjut</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Deskripsi Tugas</label>
          <textarea id="tl2-desk" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan hasil evaluasi Q2..." required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Ditugaskan ke</label>
            <select id="tl2-assign" class="form-select">
              <option value="">-- Pilih --</option>
              <?php foreach ($users as $u): ?>
              <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Deadline</label>
            <input type="date" id="tl2-deadline" class="form-control" min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Prioritas</label>
          <select id="tl2-priority" class="form-select">
            <option value="low">Rendah</option>
            <option value="medium" selected>Sedang</option>
            <option value="high">Tinggi</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-tl2-save" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============================  STYLES  ============================ -->
<style>
/* ─ Hero ─────────────────────────────────────────────────── */
.ned-hero {
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 55%, #A83218 100%);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
  position: relative;
}
.ned-hero::after {
  content:''; position:absolute; top:-40px; right:-40px;
  width:180px; height:180px; border-radius:50%;
  background:rgba(201,168,76,.09); pointer-events:none;
}
.ned-hero-inner { padding: 1.25rem 1.5rem 1rem; }

.ned-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:12px; color:rgba(255,255,255,.65); margin-bottom:.4rem;
}
.ned-breadcrumb a { color:rgba(255,255,255,.75); text-decoration:none; }
.ned-breadcrumb a:hover { color:#fff; }

.ned-hero-title {
  font-size: clamp(16px,2.2vw,22px); font-weight:800; color:#fff;
  margin:0; display:flex; align-items:center; gap:.5rem;
  letter-spacing:-.02em; line-height:1.25;
}

.ned-live-badge {
  display:inline-flex; align-items:center; gap:.35rem;
  background:rgba(47,179,68,.2); color:#86efac;
  font-size:11.5px; font-weight:700; padding:.25em .75em; border-radius:20px;
}
.ned-live-dot {
  width:7px; height:7px; border-radius:50%;
  background:#4ade80;
  animation: ned-pulse 1.5s infinite;
}
@keyframes ned-pulse { 0%,100%{opacity:1} 50%{opacity:.4} }

.ned-save-status { font-size:12px; color:rgba(255,255,255,.7); }

.ned-info-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  background:rgba(255,255,255,.12); color:rgba(255,255,255,.8);
  font-size:12px; padding:.25em .65em; border-radius:20px;
}
.ned-loc-link { color:var(--gold); text-decoration:none; }
.ned-loc-link:hover { text-decoration:underline; }

/* Hero action buttons */
.ned-hero-actions .btn { font-size:13px; font-weight:600; border-radius:8px; display:inline-flex; align-items:center; gap:.35rem; padding:.42rem 1rem; }
.ned-btn-save   { background:var(--gold); border:1px solid var(--gold-dark); color:#3D0A0A; }
.ned-btn-save:hover { background:var(--gold-dark); color:#fff; }
.ned-btn-tpl    { background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.3); color:#fff; }
.ned-btn-tpl:hover { background:rgba(255,255,255,.25); color:#fff; }
.ned-btn-export { background:rgba(255,255,255,.12); border:1.5px solid rgba(255,255,255,.25); color:#fff; }
.ned-btn-export:hover,.ned-btn-export:focus { background:rgba(255,255,255,.22); color:#fff; }
.ned-btn-hist   { background:transparent; border:1.5px solid rgba(255,255,255,.3); color:rgba(255,255,255,.8); }
.ned-btn-hist:hover { border-color:#fff; color:#fff; }

/* Readonly alert */
.ned-readonly-alert {
  display:flex; align-items:center; gap:.5rem;
  background:#faf4eb; border:1px solid var(--border); border-left:3px solid var(--gold);
  color:var(--text-main); border-radius:8px; padding:.6rem 1rem; font-size:13px;
}

/* ─ Editor card ─────────────────────────────────────── */
.ned-editor-card {
  border:1px solid var(--border-light); border-radius:14px;
  overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.06);
}
.ned-quill-area { min-height:480px; font-size:15px; border:none; padding:.25rem; }

/* ─ Comment card ────────────────────────────────────── */
.ned-comment-card { border:1px solid var(--border-light); border-radius:14px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.06); }
.ned-comment-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:.7rem 1rem; border-bottom:1px solid var(--border-light);
  background:#faf6ef;
}
.ned-comment-title { font-size:14px; font-weight:700; color:var(--brand); }
.ned-count-badge {
  background:rgba(123,28,28,.1); color:var(--brand);
  font-size:11px; font-weight:700; padding:.1em .5em; border-radius:20px;
}
.ned-comment-list { padding:.5rem .75rem; min-height:60px; }
.ned-comment-footer { padding:.75rem 1rem; border-top:1px solid var(--border-light); background:#fff; }
.ned-user-avatar {
  width:32px; height:32px; border-radius:50%;
  background:var(--brand); color:#fff;
  font-size:13px; font-weight:700;
  display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.ned-comment-input {
  border:1.5px solid var(--border); border-radius:8px;
  padding:.45rem .75rem; font-size:13px; width:100%;
  outline:none; transition:border-color .15s, box-shadow .15s; resize:none;
}
.ned-comment-input:focus { border-color:var(--brand); box-shadow:0 0 0 3px rgba(123,28,28,.12); }
.ned-btn-send {
  background:var(--brand); border:none; color:#fff;
  font-size:12.5px; font-weight:600; border-radius:7px;
  padding:.35rem .9rem; display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; transition:background .15s;
}
.ned-btn-send:hover { background:var(--brand-dark); color:#fff; }

.ned-btn-sm-outline {
  background:transparent; border:1.5px solid var(--border); color:var(--text-muted);
  font-size:12px; font-weight:600; border-radius:7px; padding:.3rem .75rem;
  cursor:pointer; transition:all .14s;
}
.ned-btn-sm-outline:hover { border-color:var(--brand); color:var(--brand); }

/* ─ Sidebar ────────────────────────────────────────────── */
.ned-sidebar-card {
  border:1px solid var(--border-light); border-radius:12px;
  overflow:hidden; box-shadow:0 1px 6px rgba(0,0,0,.05);
}
.ned-sidebar-header {
  display:flex; align-items:center; gap:.4rem;
  font-size:11.5px; font-weight:700; letter-spacing:.07em; text-transform:uppercase;
  color:var(--brand); background:#faf4eb;
  padding:.55rem .85rem; border-bottom:1px solid var(--border-light);
}
.ned-sidebar-body   { padding:.7rem .85rem; background:#fff; }
.ned-sidebar-footer { padding:.55rem .85rem; background:#faf9f7; border-top:1px solid var(--border-light); }

.ned-btn-add-sm {
  background:var(--brand); border:none; color:#fff;
  font-size:11.5px; font-weight:700; border-radius:6px;
  padding:.25rem .6rem; display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; transition:background .14s;
}
.ned-btn-add-sm:hover { background:var(--brand-dark); color:#fff; }

.ned-btn-back {
  background:transparent; border:1.5px solid var(--border);
  color:var(--text-main); font-size:12.5px; font-weight:600;
  border-radius:8px; padding:.38rem .75rem;
  display:inline-flex; align-items:center; gap:.35rem; transition:all .14s;
}
.ned-btn-back:hover { border-color:var(--brand); color:var(--brand); }

/* DL info */
.ned-dl { display:grid; grid-template-columns:auto 1fr; gap:.2rem .75rem; margin:0; font-size:13px; }
.ned-dl dt { color:var(--text-muted); font-weight:600; white-space:nowrap; }
.ned-dl dd { color:var(--text-main); margin:0; }
.ned-link { color:var(--brand); text-decoration:none; }
.ned-link:hover { text-decoration:underline; }

/* Upload form */
.ned-upload-form { padding:.75rem .85rem; border-bottom:1px solid var(--border-light); background:#fff; }
.ned-form-label { font-size:12px; font-weight:600; color:var(--text-main); display:block; margin-bottom:.25rem; }
.ned-form-hint  { font-size:11px; color:var(--text-muted); margin-top:.2rem; }
.ned-btn-upload {
  background:var(--brand); border:none; color:#fff;
  font-size:13px; font-weight:600; border-radius:8px; padding:.38rem .75rem;
  display:inline-flex; align-items:center; gap:.3rem; cursor:pointer; transition:background .14s;
}
.ned-btn-upload:hover { background:var(--brand-dark); color:#fff; }
.ned-btn-cancel {
  background:transparent; border:1.5px solid var(--border); color:var(--text-muted);
  font-size:13px; border-radius:8px; padding:.38rem .75rem; cursor:pointer; transition:all .14s;
}
.ned-btn-cancel:hover { border-color:var(--text-muted); }

/* Attachment list */
.ned-attachment-list { max-height:280px; overflow-y:auto; }
.ned-attach-loading { padding:.85rem; text-align:center; font-size:12.5px; color:var(--text-muted); }

/* TL list */
.ned-tl-list { }
.ned-tl-item {
  padding:.65rem .85rem; border-bottom:1px solid var(--border-light);
  background:#fff; transition:background .12s;
}
.ned-tl-item:last-child { border-bottom:none; }
.ned-tl-item:hover { background:#faf4eb; }
.ned-tl-desc {
  font-size:13px; font-weight:600; color:var(--text-main); line-height:1.35;
  display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
}
.ned-tl-meta { font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:.25rem; margin:.2rem 0; }
.ned-tl-del {
  background:transparent; border:none; color:#c0392b; cursor:pointer;
  width:20px; height:20px; border-radius:4px; flex-shrink:0;
  display:inline-flex; align-items:center; justify-content:center;
  transition:background .13s; padding:0;
}
.ned-tl-del:hover { background:rgba(192,57,43,.12); }
.ned-tl-empty { padding:.85rem; text-align:center; font-size:12.5px; color:var(--text-muted); }

/* Badges */
.ned-badge {
  display:inline-flex; align-items:center;
  font-size:10.5px; font-weight:700; padding:.2em .6em; border-radius:20px; white-space:nowrap;
}
.ned-badge-red       { background:rgba(168,37,21,.10);  color:#a82515; }
.ned-badge-orange    { background:rgba(201,168,76,.15);  color:#7a5f00; }
.ned-badge-green     { background:rgba(47,107,64,.10);   color:#1e7a2e; }
.ned-badge-blue      { background:rgba(32,107,196,.10);  color:#1557a0; }
.ned-badge-secondary { background:rgba(100,100,100,.10); color:#64748b; }

/* Modal icon */
.ned-modal-icon {
  width:30px; height:30px; background:var(--brand-light); border-radius:7px;
  display:inline-flex; align-items:center; justify-content:center; color:var(--brand);
}

/* Responsive */
@media(max-width:767.98px) {
  .ned-hero-inner { padding:1rem; }
  .ned-hero-title { font-size:15px; }
  .ned-hero-actions { width:100%; }
  .ned-hero-actions .btn { font-size:12px; padding:.38rem .75rem; }
}
</style>

<?php if ($canEdit): ?>
<script>
const TPL_API_URL = <?= json_encode(rtrim(BASE_URL, '/') . '/api/notulen-templates') ?>;
const TPL_MANAGE_URL = <?= json_encode(rtrim(BASE_URL, '/') . '/notulen-templates') ?>;
let tplListLoaded = false;

document.getElementById('btn-pick-template')?.addEventListener('click', () => {
  const modal = new bootstrap.Modal(document.getElementById('modalPickTemplate'));
  modal.show();
  if (tplListLoaded) return;
  fetch(TPL_API_URL)
    .then(r => r.json())
    .then(data => {
      tplListLoaded = true;
      const loading   = document.getElementById('tpl-list-loading');
      const container = document.getElementById('tpl-list-container');
      loading.style.display = 'none';
      container.style.display = '';
      if (!data.templates || !data.templates.length) {
        container.innerHTML = `<div class="col-12 text-muted text-center py-3">Belum ada template. <a href="${TPL_MANAGE_URL}" target="_blank">Buat template</a></div>`;
        return;
      }
      data.templates.forEach(tpl => {
        const col = document.createElement('div');
        col.className = 'col-md-6';
        col.innerHTML = `
          <div class="card h-100 border">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-start mb-1">
                <h5 class="card-title mb-0">${tpl.name}</h5>
                ${(tpl.is_default == 1) ? '<span class="ned-badge ned-badge-green">Default</span>' : ''}
              </div>
              <p class="text-muted small mb-0">${tpl.description || '—'}</p>
            </div>
            <div class="card-footer py-2">
              <button class="btn btn-sm btn-primary w-100 btn-apply-tpl" data-tpl-id="${tpl.id}">Gunakan Template Ini</button>
            </div>
          </div>`;
        container.appendChild(col);
      });
      container.querySelectorAll('.btn-apply-tpl').forEach(btn => {
        btn.addEventListener('click', async function () {
          const res = await fetch(TPL_API_URL + '/' + this.dataset.tplId);
          const d   = await res.json();
          if (!d.success) { alert(d.message || 'Gagal memuat template.'); return; }
          if (!window.quill) { alert('Editor belum siap.'); return; }
          window.quill.root.innerHTML = d.template.content;
          bootstrap.Modal.getInstance(document.getElementById('modalPickTemplate')).hide();
          const ss = document.getElementById('save-status');
          if (ss) { ss.textContent = '● Belum disimpan'; ss.className = 'ned-save-status text-warning'; }
        });
      });
    })
    .catch(() => {
      document.getElementById('tpl-list-loading').innerHTML = '<div class="text-danger">Gagal memuat daftar template.</div>';
    });
});

function bindTlDelButtons() {
  document.querySelectorAll('.btn-tl-del').forEach(btn => {
    if (btn.dataset.bound) return;
    btn.dataset.bound = '1';
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      this.disabled = true;
      const res = await fetch(this.dataset.url, { method: 'POST' });
      const d   = await res.json();
      if (d.success) {
        document.getElementById('tl-item-' + this.dataset.id)?.remove();
        const list = document.getElementById('tl-list');
        if (list && !list.querySelector('.ned-tl-item')) {
          if (!document.getElementById('tl-empty')) {
            const e = document.createElement('div');
            e.id = 'tl-empty'; e.className = 'ned-tl-empty';
            e.textContent = 'Belum ada tindak lanjut';
            list.appendChild(e);
          }
        }
      } else { alert(d.message || 'Gagal hapus'); this.disabled = false; }
    });
  });
}
bindTlDelButtons();
</script>
<?php endif; ?>
