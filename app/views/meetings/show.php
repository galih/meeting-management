<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$canEdit = $canEdit ?? false;

$meetingStatusColor = [
  'scheduled' => 'blue',
  'ongoing'   => 'orange',
  'done'      => 'green',
  'cancelled' => 'red',
];
$statusIconSvg = [
  'scheduled' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];
$participantStatusColor = [
  'accepted' => 'green',
  'invited'  => 'blue',
  'declined' => 'red',
  'attended' => 'teal',
  'pending'  => 'secondary',
];
$participantStatusLabel = [
  'accepted' => 'Diterima',
  'invited'  => 'Diundang',
  'declined' => 'Ditolak',
  'attended' => 'Hadir',
  'pending'  => 'Menunggu',
];
$priorityColor = ['high' => 'red', 'medium' => 'orange', 'low' => 'green'];
$priorityLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$tlStatusColor = [
  'pending'     => 'secondary',
  'in_progress' => 'blue',
  'done'        => 'green',
  'cancelled'   => 'red',
];
$tlStatusLabel = [
  'pending'     => 'Menunggu',
  'in_progress' => 'Berlangsung',
  'done'        => 'Selesai',
  'cancelled'   => 'Dibatalkan',
];

$mStatusKey   = $meeting['status'] ?? 'scheduled';
$mStatusColor = $meetingStatusColor[$mStatusKey] ?? 'secondary';
$mStatusLbl   = $statusLabel[$mStatusKey] ?? ucfirst($mStatusKey);
$mStatusIco   = $statusIconSvg[$mStatusKey] ?? '';
$loc          = $meeting['location'] ?? '';
$isLink       = !empty($loc) && (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://'));
$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => $t['status'] === 'done'));
$overdueTL    = count(array_filter($tindakLanjutList,
  fn($t) => !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && !in_array($t['status'], ['done','cancelled'])
));
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="sh-flash-toast" id="shFlashToast">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button onclick="document.getElementById('shFlashToast').remove()" class="sh-flash-close">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- ================================================================
     HERO HEADER
================================================================ -->
<div class="sh-hero mb-4">
  <div class="sh-hero-body">
    <div class="sh-hero-top">
      <div class="sh-hero-left">
        <nav class="sh-breadcrumb" aria-label="breadcrumb">
          <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Detail</span>
        </nav>
        <h1 class="sh-title"><?= htmlspecialchars($meeting['title']) ?></h1>
        <div class="sh-meta">
          <span class="sh-status sh-status-<?= $mStatusColor ?>"><?= $mStatusIco ?>&nbsp;<?= $mStatusLbl ?></span>
          <?php if (!empty($meeting['dept_name'])): ?>
          <span class="sh-chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($meeting['dept_name']) ?>
          </span>
          <?php endif; ?>
          <span class="sh-chip">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($meeting['creator_name'] ?? '-') ?>
          </span>
        </div>
      </div>
      <div class="sh-hero-actions">
        <?php if ($canEdit): ?>
        <a href="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/edit" class="sh-btn sh-btn-gold">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button class="sh-btn sh-btn-ghost" data-bs-toggle="modal" data-bs-target="#modalEditStatus">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Ubah Status
        </button>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?>
        <button class="sh-btn sh-btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteMeeting">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="sh-hero-strip">
    <span class="sh-strip-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <strong>Mulai:</strong>&nbsp;<?= date('d M Y · H:i', strtotime($meeting['start_datetime'])) ?>
    </span>
    <span class="sh-strip-div">&rarr;</span>
    <span class="sh-strip-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <strong>Selesai:</strong>&nbsp;<?= date('d M Y · H:i', strtotime($meeting['end_datetime'])) ?>
    </span>
    <?php if (!empty($loc)): ?>
    <span class="sh-strip-div">&middot;</span>
    <span class="sh-strip-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener" class="sh-loc-link">Buka Link Kegiatan</a>
      <?php else: ?>
        <?= htmlspecialchars($loc) ?>
      <?php endif; ?>
    </span>
    <?php endif; ?>
  </div>
</div>

<!-- ================================================================
     BODY: SIDEBAR + MAIN
================================================================ -->
<div class="row g-3">

  <!-- SIDEBAR -->
  <div class="col-xl-3 col-lg-4">

    <!-- Stat mini cards -->
    <div class="sh-stat-grid mb-3">
      <div class="sh-stat">
        <div class="sh-stat-val"><?= $totalPeserta ?></div>
        <div class="sh-stat-lbl">Peserta</div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-val"><?= $totalTL ?></div>
        <div class="sh-stat-lbl">Tindak Lanjut</div>
      </div>
      <div class="sh-stat sh-stat-green">
        <div class="sh-stat-val"><?= $doneTL ?></div>
        <div class="sh-stat-lbl">Selesai</div>
      </div>
      <div class="sh-stat <?= $overdueTL > 0 ? 'sh-stat-red' : '' ?>">
        <div class="sh-stat-val"><?= $overdueTL ?></div>
        <div class="sh-stat-lbl">Terlambat</div>
      </div>
    </div>

    <!-- Agenda -->
    <?php if (!empty($meeting['description'])): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Agenda
      </div>
      <div class="sh-card-body">
        <p class="sh-agenda-text"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Dokumen sidebar -->
    <div class="sh-card">
      <div class="sh-card-hd">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Dokumen
      </div>
      <div class="sh-card-body sh-doc-grid">
        <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>" class="sh-doc-btn sh-doc-primary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>/export-docx"
           class="sh-doc-btn sh-doc-outline">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export DOCX
        </a>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button class="sh-doc-btn sh-doc-outline" id="btnSendInvitation">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Kirim Undangan
        </button>
        <?php if ($meeting['status'] === 'done'): ?>
        <button class="sh-doc-btn sh-doc-outline-green" id="btnSendSummary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MAIN PANEL -->
  <div class="col-xl-9 col-lg-8">
    <div class="sh-main-card">

      <!-- Tab Nav -->
      <div class="sh-tabs" role="tablist">
        <button class="sh-tab active" data-tab="tl" role="tab" aria-selected="true">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tindak Lanjut
          <span class="sh-tab-badge"><?= $totalTL ?></span>
        </button>
        <button class="sh-tab" data-tab="peserta" role="tab" aria-selected="false">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta
          <span class="sh-tab-badge"><?= $totalPeserta ?></span>
        </button>
      </div>

      <!-- Tab: Tindak Lanjut -->
      <div id="sh-tab-tl" class="sh-tab-panel active">
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <div class="sh-tab-toolbar">
          <button class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalAddTL">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="sh-empty">
          <div class="sh-empty-icon">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <p class="sh-empty-text">Belum ada tindak lanjut untuk kegiatan ini</p>
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
          <button class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalAddTL">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Sekarang
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="sh-table">
            <thead>
              <tr>
                <th style="width:38%">Tugas</th>
                <th>PIC</th>
                <th>Deadline</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $isOverdue = !empty($tl['due_date'])
                && $tl['due_date'] < date('Y-m-d')
                && !in_array($tl['status'], ['done','cancelled']);
              $pc   = $priorityColor[$tl['priority']] ?? 'secondary';
              $plbl = $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']);
              $tc   = $tlStatusColor[$tl['status']]   ?? 'secondary';
              $tlbl = $tlStatusLabel[$tl['status']]   ?? ucfirst($tl['status']);
            ?>
              <tr class="<?= $isOverdue ? 'sh-row-overdue' : '' ?>">
                <td>
                  <div class="sh-tl-desc"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($isOverdue): ?>
                  <span class="sh-badge sh-badge-red" style="margin-top:.25rem">Terlambat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tl['assigned_name'])): ?>
                  <div class="sh-pic">
                    <span class="sh-avatar"><?= strtoupper(mb_substr($tl['assigned_name'], 0, 1)) ?></span>
                    <span class="sh-pic-name"><?= htmlspecialchars($tl['assigned_name']) ?></span>
                  </div>
                  <?php else: ?><span class="sh-muted">&mdash;</span><?php endif; ?>
                </td>
                <td>
                  <span class="sh-deadline <?= $isOverdue ? 'sh-deadline-over' : '' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
                  </span>
                </td>
                <td><span class="sh-badge sh-badge-<?= $pc ?>"><?= $plbl ?></span></td>
                <td><span class="sh-badge sh-badge-<?= $tc ?>"><?= $tlbl ?></span></td>
                <td>
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>" class="sh-link-detail">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Detail
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Tab: Peserta -->
      <div id="sh-tab-peserta" class="sh-tab-panel">
        <?php if (empty($participants)): ?>
        <div class="sh-empty">
          <div class="sh-empty-icon">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <p class="sh-empty-text">Belum ada peserta terdaftar</p>
        </div>
        <?php else: ?>
        <div class="sh-peserta-grid">
          <?php
          $avatarPalette = ['#7B1C1C','#A83218','#C9A84C','#2F6BC4','#1a7340','#7d3cb5','#0d7a8a','#b5530a'];
          foreach ($participants as $p):
            $psc  = $participantStatusColor[$p['status']] ?? 'secondary';
            $plbl = $participantStatusLabel[$p['status']] ?? ucfirst($p['status']);
            $bg   = $avatarPalette[abs(crc32($p['name'])) % count($avatarPalette)];
          ?>
          <div class="sh-peserta-card">
            <span class="sh-peserta-av" style="background:<?= $bg ?>">
              <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
            </span>
            <div class="sh-peserta-info">
              <div class="sh-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['position'])): ?>
              <div class="sh-peserta-pos"><?= htmlspecialchars($p['position']) ?></div>
              <?php endif; ?>
            </div>
            <span class="sh-badge sh-badge-<?= $psc ?>"><?= $plbl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- /sh-main-card -->
  </div>
</div>

<!-- ================================================================
     MODALS
================================================================ -->

<!-- Modal Ubah Status -->
<?php if (Auth::hasRole('admin', 'sekretaris')): ?>
<div class="modal modal-blur fade" id="modalEditStatus" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Status baru</label>
          <select name="status" class="form-select">
            <?php foreach ($statusLabel as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= ($meeting['status'] ?? '') === $val ? 'selected' : '' ?>>
              <?= htmlspecialchars($lbl) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Tindak Lanjut -->
<div class="modal modal-blur fade" id="modalAddTL" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tambah Tindak Lanjut
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Deskripsi Tugas</label>
          <textarea id="tlDesc" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan hasil evaluasi Q2…" required></textarea>
          <div class="invalid-feedback" id="tlDescErr">Deskripsi wajib diisi.</div>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Ditugaskan ke</label>
            <select id="tlAssigned" class="form-select">
              <option value="">— Pilih peserta —</option>
              <?php foreach ($participants as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Deadline</label>
            <input type="date" id="tlDeadline" class="form-control"
                   min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Prioritas</label>
          <div class="d-flex gap-3">
            <?php foreach (['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'] as $v => $l): ?>
            <label class="form-check form-check-inline">
              <input type="radio" name="tlPriority" class="form-check-input"
                     value="<?= $v ?>" <?= $v === 'medium' ? 'checked' : '' ?>>
              <span class="form-check-label"><?= $l ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveTL">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Hapus Kegiatan -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal modal-blur fade" id="modalDeleteMeeting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="sh-del-icon mb-3">
          <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mb-1">Hapus Kegiatan?</h5>
        <p class="text-muted small mb-0">
          Kegiatan <strong class="text-danger"><?= htmlspecialchars($meeting['title']) ?></strong> akan dihapus permanen.
          Semua peserta, notulen, dan tindak lanjut ikut terhapus.
        </p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/delete" style="display:inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<script>
(function () {
  // ── Tab switching ──────────────────────────────────────────────
  document.querySelectorAll('.sh-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.sh-tab').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.sh-tab-panel').forEach(function (p) {
        p.classList.remove('active');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      var panel = document.getElementById('sh-tab-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });

  // ── Tambah Tindak Lanjut ───────────────────────────────────────
  var btnSaveTL = document.getElementById('btnSaveTL');
  if (btnSaveTL) {
    btnSaveTL.addEventListener('click', function () {
      var desc     = document.getElementById('tlDesc');
      var assigned = document.getElementById('tlAssigned');
      var deadline = document.getElementById('tlDeadline');
      var priority = document.querySelector('input[name="tlPriority"]:checked');

      desc.classList.remove('is-invalid');
      if (!desc.value.trim()) {
        desc.classList.add('is-invalid');
        desc.focus();
        return;
      }

      btnSaveTL.disabled = true;
      btnSaveTL.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';

      fetch('<?= $baseUrl ?>/tindak-lanjut', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
          meeting_id:  <?= (int)$meeting['id'] ?>,
          description: desc.value.trim(),
          assigned_to: assigned ? (assigned.value || null) : null,
          due_date:    deadline ? (deadline.value || null) : null,
          priority:    priority ? priority.value : 'medium',
          _csrf:       '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>'
        })
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          window.location.reload();
        } else {
          alert(data.message || 'Gagal menyimpan tindak lanjut.');
          btnSaveTL.disabled = false;
          btnSaveTL.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
        }
      })
      .catch(function () {
        alert('Terjadi kesalahan jaringan.');
        btnSaveTL.disabled = false;
        btnSaveTL.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Simpan';
      });
    });
  }

  // ── Kirim Undangan ─────────────────────────────────────────────
  var btnInv = document.getElementById('btnSendInvitation');
  if (btnInv) {
    btnInv.addEventListener('click', function () {
      if (!confirm('Kirim undangan email ke semua peserta?')) return;
      btnInv.disabled = true;
      fetch('<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/send-invitations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>' })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { alert(d.message || 'Undangan terkirim.'); btnInv.disabled = false; })
      .catch(function () { alert('Gagal mengirim undangan.'); btnInv.disabled = false; });
    });
  }

  // ── Kirim Ringkasan ────────────────────────────────────────────
  var btnSum = document.getElementById('btnSendSummary');
  if (btnSum) {
    btnSum.addEventListener('click', function () {
      if (!confirm('Kirim ringkasan kegiatan ke semua peserta?')) return;
      btnSum.disabled = true;
      fetch('<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/send-summary', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>' })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { alert(d.message || 'Ringkasan terkirim.'); btnSum.disabled = false; })
      .catch(function () { alert('Gagal mengirim ringkasan.'); btnSum.disabled = false; });
    });
  }

  // ── Auto-dismiss flash toast ────────────────────────────────────
  var toast = document.getElementById('shFlashToast');
  if (toast) setTimeout(function () { toast.style.opacity = '0'; setTimeout(function () { toast.remove(); }, 400); }, 4000);
}());
</script>

<!-- ================================================================
     STYLES
================================================================ -->
<style>
/* ── Flash Toast ── */
.sh-flash-toast {
  position: fixed; top: 1.2rem; right: 1.2rem; z-index: 1100;
  display: flex; align-items: center; gap: .5rem;
  background: #1e7a2e; color: #fff;
  padding: .65rem 1rem; border-radius: 10px;
  font-size: 13px; font-weight: 600;
  box-shadow: 0 4px 16px rgba(0,0,0,.2);
  transition: opacity .4s ease;
}
.sh-flash-close {
  background: none; border: none; color: inherit;
  font-size: 18px; line-height: 1; cursor: pointer;
  padding: 0 0 0 .5rem; opacity: .8;
}
.sh-flash-close:hover { opacity: 1; }

/* ── Hero ── */
.sh-hero {
  background: linear-gradient(135deg, var(--brand, #7B1C1C) 0%, #9B2020 55%, #A83218 100%);
  border-radius: 14px;
  box-shadow: 0 4px 24px rgba(123,28,28,.22);
  overflow: hidden;
}
.sh-hero-body { padding: 1.4rem 1.6rem 1rem; }
.sh-hero-top { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem; }
.sh-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .5rem;
}
.sh-breadcrumb a { color: rgba(255,255,255,.8); text-decoration: none; }
.sh-breadcrumb a:hover { color: #fff; text-decoration: underline; }
.sh-title {
  font-size: clamp(16px,2.5vw,23px); font-weight: 800;
  color: #fff; margin: 0; letter-spacing: -.02em; line-height: 1.25;
}
.sh-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .5rem; }
.sh-status {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 12px; font-weight: 700; padding: .28em .75em;
  border-radius: 20px;
}
.sh-status-blue     { background: rgba(147,197,253,.2);  color: #bfdbfe; }
.sh-status-orange   { background: rgba(253,186,116,.2);  color: #fed7aa; }
.sh-status-green    { background: rgba(134,239,172,.2);  color: #bbf7d0; }
.sh-status-red      { background: rgba(252,165,165,.2);  color: #fecaca; }
.sh-status-secondary{ background: rgba(255,255,255,.15); color: rgba(255,255,255,.8); }
.sh-chip {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.75);
  background: rgba(255,255,255,.12); padding: .25em .65em; border-radius: 20px;
}
.sh-hero-actions {
  display: flex; flex-wrap: wrap; gap: .4rem; align-items: flex-start; padding-top: .1rem;
}
.sh-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600; padding: .42rem .9rem;
  border-radius: 8px; border: none; cursor: pointer; text-decoration: none;
  transition: background .18s, color .18s;
}
.sh-btn-gold   { background: var(--gold, #C9A84C); color: #3D0A0A; }
.sh-btn-gold:hover { background: #b08e3a; color: #fff; }
.sh-btn-ghost  { background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3); color: #fff; }
.sh-btn-ghost:hover { background: rgba(255,255,255,.25); }
.sh-btn-danger { background: rgba(192,57,43,.25); border: 1.5px solid rgba(192,57,43,.5); color: #fca5a5; }
.sh-btn-danger:hover { background: rgba(192,57,43,.45); color: #fff; }
/* Time strip */
.sh-hero-strip {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
  background: rgba(0,0,0,.18); padding: .55rem 1.6rem;
  font-size: 13px; color: rgba(255,255,255,.82);
}
.sh-strip-item { display: flex; align-items: center; gap: .3rem; }
.sh-strip-div  { color: rgba(255,255,255,.4); }
.sh-loc-link   { color: var(--gold, #C9A84C); text-decoration: none; }
.sh-loc-link:hover { text-decoration: underline; }

/* ── Stat Grid ── */
.sh-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.sh-stat {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 10px; padding: .7rem .8rem;
  text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sh-stat-val { font-size: 22px; font-weight: 800; color: var(--brand,#7B1C1C); line-height: 1; }
.sh-stat-lbl { font-size: 11px; color: #888; margin-top: .15rem; font-weight: 500; }
.sh-stat-green .sh-stat-val { color: #1e7a2e; }
.sh-stat-red   .sh-stat-val { color: #b91c1c; }

/* ── Sidebar card ── */
.sh-card {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 10px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sh-card-hd {
  display: flex; align-items: center; gap: .4rem;
  font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
  color: #555; padding: .6rem 1rem;
  border-bottom: 1px solid #e8e3db; background: #fafaf8;
}
.sh-card-body { padding: .8rem 1rem; }
.sh-agenda-text { font-size: 13px; line-height: 1.65; color: #444; margin: 0; }
.sh-doc-grid { display: flex; flex-direction: column; gap: .5rem; }
.sh-doc-btn {
  display: flex; align-items: center; justify-content: center; gap: .4rem;
  font-size: 13px; font-weight: 600; padding: .5rem .8rem;
  border-radius: 8px; border: none; cursor: pointer;
  text-decoration: none; transition: background .18s, border-color .18s;
}
.sh-doc-primary { background: var(--brand, #7B1C1C); color: #fff; }
.sh-doc-primary:hover { background: #9B2020; color: #fff; }
.sh-doc-outline {
  background: #fff; color: #333;
  border: 1.5px solid #d4cfc8;
}
.sh-doc-outline:hover { background: #f5f0ea; border-color: #b5a89a; }
.sh-doc-outline-green { background: #fff; color: #1e7a2e; border: 1.5px solid #a3d9a5; }
.sh-doc-outline-green:hover { background: #f0faf0; }

/* ── Main card ── */
.sh-main-card {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 6px rgba(0,0,0,.05);
}

/* ── Tabs ── */
.sh-tabs {
  display: flex; gap: 0;
  border-bottom: 1px solid #e8e3db;
  background: #fafaf8;
  padding: 0 1rem;
}
.sh-tab {
  display: flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600; color: #777;
  padding: .75rem 1rem; border: none; background: none;
  border-bottom: 2px solid transparent; cursor: pointer;
  transition: color .18s, border-color .18s;
}
.sh-tab:hover { color: var(--brand, #7B1C1C); }
.sh-tab.active {
  color: var(--brand, #7B1C1C);
  border-bottom-color: var(--brand, #7B1C1C);
}
.sh-tab-badge {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 18px; padding: 0 5px;
  font-size: 11px; font-weight: 700;
  background: #eee; color: #555; border-radius: 20px;
}
.sh-tab.active .sh-tab-badge { background: var(--brand, #7B1C1C); color: #fff; }

/* ── Tab panels ── */
.sh-tab-panel { display: none; }
.sh-tab-panel.active { display: block; }
.sh-tab-toolbar {
  display: flex; justify-content: flex-end;
  padding: .75rem 1rem;
  border-bottom: 1px solid #f0ece6;
}

/* ── Add button ── */
.sh-btn-add {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600;
  background: var(--brand, #7B1C1C); color: #fff;
  padding: .42rem .9rem; border-radius: 8px;
  border: none; cursor: pointer; transition: background .18s;
}
.sh-btn-add:hover { background: #9B2020; }

/* ── Empty state ── */
.sh-empty {
  display: flex; flex-direction: column; align-items: center;
  text-align: center; padding: 2.5rem 1rem;
  color: #999;
}
.sh-empty-icon { color: #ccc; margin-bottom: .75rem; }
.sh-empty-text { font-size: 13px; margin: 0 0 1rem; }

/* ── Table ── */
.sh-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sh-table thead tr { background: #faf8f5; }
.sh-table th {
  padding: .6rem 1rem; text-align: left;
  font-size: 11px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .05em; color: #777;
  border-bottom: 1px solid #e8e3db;
}
.sh-table td { padding: .7rem 1rem; border-bottom: 1px solid #f0ece6; vertical-align: middle; }
.sh-table tbody tr:last-child td { border-bottom: none; }
.sh-table tbody tr:hover td { background: #faf8f5; }
.sh-row-overdue td { background: #fff8f5 !important; }
.sh-tl-desc { font-weight: 500; color: #222; }
.sh-muted    { color: #aaa; }
.sh-pic { display: flex; align-items: center; gap: .45rem; }
.sh-avatar {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px; border-radius: 50%;
  font-size: 11px; font-weight: 700; color: #fff;
  background: var(--brand, #7B1C1C); flex-shrink: 0;
}
.sh-pic-name { font-size: 13px; }
.sh-deadline { font-size: 12px; color: #555; }
.sh-deadline-over { color: #b91c1c; font-weight: 700; }
.sh-link-detail {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 12px; font-weight: 600; color: var(--brand, #7B1C1C);
  text-decoration: none;
}
.sh-link-detail:hover { text-decoration: underline; }

/* ── Badges ── */
.sh-badge {
  display: inline-flex; align-items: center;
  font-size: 11px; font-weight: 700;
  padding: .2em .6em; border-radius: 20px;
}
.sh-badge-red       { background: #fee2e2; color: #b91c1c; }
.sh-badge-orange    { background: #ffedd5; color: #c2410c; }
.sh-badge-green     { background: #dcfce7; color: #15803d; }
.sh-badge-blue      { background: #dbeafe; color: #1d4ed8; }
.sh-badge-teal      { background: #ccfbf1; color: #0f766e; }
.sh-badge-secondary { background: #f1f0ee; color: #666; }

/* ── Peserta grid ── */
.sh-peserta-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .6rem; padding: .9rem 1rem; }
.sh-peserta-card {
  display: flex; align-items: center; gap: .6rem;
  background: #faf8f5; border: 1px solid #e8e3db;
  border-radius: 10px; padding: .6rem .8rem;
}
.sh-peserta-av {
  display: inline-flex; align-items: center; justify-content: center;
  width: 34px; height: 34px; border-radius: 50%;
  font-size: 13px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.sh-peserta-info { flex: 1; min-width: 0; }
.sh-peserta-name { font-size: 13px; font-weight: 600; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sh-peserta-pos  { font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Delete icon ── */
.sh-del-icon {
  display: flex; align-items: center; justify-content: center;
  width: 60px; height: 60px; border-radius: 50%;
  background: #fee2e2; color: #b91c1c; margin: 0 auto;
}

/* ── Responsive ── */
@media (max-width: 575px) {
  .sh-hero-body  { padding: 1rem; }
  .sh-hero-strip { padding: .5rem 1rem; font-size: 12px; }
  .sh-stat-grid  { grid-template-columns: repeat(4,1fr); }
  .sh-peserta-grid { grid-template-columns: 1fr; }
  .sh-tabs { overflow-x: auto; }
  .sh-table th, .sh-table td { padding: .5rem .65rem; }
}
</style>
