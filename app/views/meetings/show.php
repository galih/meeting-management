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
$priorityColor = [
  'high'   => 'red',
  'medium' => 'orange',
  'low'    => 'green',
];
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
$isLink       = !empty($loc) && (strncmp($loc,'http://',7)===0 || strncmp($loc,'https://',8)===0);
$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => $t['status'] === 'done'));
$overdueTL    = count(array_filter($tindakLanjutList,
  fn($t) => !empty($t['due_date']) && $t['due_date'] < date('Y-m-d') && !in_array($t['status'],['done','cancelled'])
));
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- ============================  HERO HEADER  ============================ -->
<div class="show-hero mb-4">
  <div class="show-hero-inner">
    <div class="d-flex flex-wrap align-items-flex-start justify-content-between gap-3">
      <div class="show-hero-left">
        <!-- Breadcrumb -->
        <nav class="show-breadcrumb">
          <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Detail</span>
        </nav>
        <!-- Title & Status -->
        <h1 class="show-hero-title"><?= htmlspecialchars($meeting['title']) ?></h1>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
          <span class="show-status-badge show-status-<?= $mStatusColor ?>">
            <?= $mStatusIco ?> <?= $mStatusLbl ?>
          </span>
          <?php if (!empty($meeting['dept_name'])): ?>
          <span class="show-dept-chip">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($meeting['dept_name']) ?>
          </span>
          <?php endif; ?>
          <span class="show-creator-chip">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($meeting['creator_name'] ?? '-') ?>
          </span>
        </div>
      </div>
      <div class="show-hero-actions d-flex flex-wrap gap-2">
        <?php if ($canEdit): ?>
        <a href="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/edit" class="btn show-btn-edit">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <button class="btn show-btn-status" data-bs-toggle="modal" data-bs-target="#modalEditStatus">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Ubah Status
        </button>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?>
        <button class="btn show-btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteMeeting">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Waktu strip -->
  <div class="show-time-strip">
    <div class="show-time-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span><strong>Mulai:</strong> <?= date('d M Y · H:i', strtotime($meeting['start_datetime'])) ?></span>
    </div>
    <span class="show-time-div">&rarr;</span>
    <div class="show-time-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
      <span><strong>Selesai:</strong> <?= date('d M Y · H:i', strtotime($meeting['end_datetime'])) ?></span>
    </div>
    <?php if (!empty($loc)): ?>
    <span class="show-time-div">·</span>
    <div class="show-time-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?php if ($isLink): ?>
      <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener" class="show-loc-link">Buka Link Kegiatan</a>
      <?php else: ?>
      <span><?= htmlspecialchars($loc) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ============================  BODY  ============================ -->
<div class="row g-3">

  <!-- ===  KOLOM KIRI (sidebar)  === -->
  <div class="col-lg-3 col-md-4">

    <!-- Stat cards -->
    <div class="show-stat-grid mb-3">
      <div class="show-stat-card">
        <div class="show-stat-val"><?= $totalPeserta ?></div>
        <div class="show-stat-lbl">Peserta</div>
      </div>
      <div class="show-stat-card">
        <div class="show-stat-val"><?= $totalTL ?></div>
        <div class="show-stat-lbl">Tindak Lanjut</div>
      </div>
      <div class="show-stat-card show-stat-green">
        <div class="show-stat-val"><?= $doneTL ?></div>
        <div class="show-stat-lbl">Selesai</div>
      </div>
      <div class="show-stat-card <?= $overdueTL > 0 ? 'show-stat-red' : '' ?>">
        <div class="show-stat-val"><?= $overdueTL ?></div>
        <div class="show-stat-lbl">Terlambat</div>
      </div>
    </div>

    <!-- Agenda -->
    <?php if (!empty($meeting['description'])): ?>
    <div class="card show-sidebar-card mb-3">
      <div class="card-header show-sidebar-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Agenda
      </div>
      <div class="card-body show-sidebar-body">
        <p class="mb-0" style="font-size:13px;line-height:1.6;white-space:pre-wrap;"><?= htmlspecialchars($meeting['description']) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Aksi Dokumen -->
    <div class="card show-sidebar-card">
      <div class="card-header show-sidebar-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Dokumen
      </div>
      <div class="card-body show-sidebar-body d-grid gap-2">
        <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>" class="btn show-doc-btn show-doc-btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>/export-docx" target="_blank"
           class="btn show-doc-btn show-doc-btn-outline">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13l2 2 4-4"/></svg>
          Export DOCX
        </a>
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <button class="btn show-doc-btn show-doc-btn-outline" id="btn-send-invitation">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Kirim Undangan
        </button>
        <?php if ($meeting['status'] === 'done'): ?>
        <button class="btn show-doc-btn show-doc-btn-outline-green" id="btn-send-summary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ===  KOLOM KANAN (tab panel)  === -->
  <div class="col-lg-9 col-md-8">
    <div class="card show-main-card">
      <!-- konten sisanya tetap sama -->
    </div>
  </div>
</div>
