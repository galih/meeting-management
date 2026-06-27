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
        <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>/export-pdf" target="_blank"
           class="btn show-doc-btn show-doc-btn-outline-red">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
          Export PDF
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

      <!-- Tab nav -->
      <div class="show-tab-nav">
        <button class="show-tab-btn active" data-tab="tl">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tindak Lanjut
          <span class="show-tab-count"><?= $totalTL ?></span>
        </button>
        <button class="show-tab-btn" data-tab="peserta">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta
          <span class="show-tab-count"><?= $totalPeserta ?></span>
        </button>
      </div>

      <!-- Tab: Tindak Lanjut -->
      <div id="tab-tl" class="show-tab-panel active">
        <div class="show-tab-toolbar">
          <?php if (Auth::hasRole('admin','sekretaris')): ?>
          <button class="btn show-btn-add" data-bs-toggle="modal" data-bs-target="#modalAddTL">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
          <?php endif; ?>
        </div>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="show-empty">
          <div class="show-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <p class="show-empty-text">Belum ada tindak lanjut untuk kegiatan ini</p>
          <?php if (Auth::hasRole('admin','sekretaris')): ?>
          <button class="btn show-btn-add" data-bs-toggle="modal" data-bs-target="#modalAddTL">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Sekarang
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="show-tl-table">
            <thead>
              <tr>
                <th style="width:40%">Tugas</th>
                <th>PIC</th>
                <th>Deadline</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tindakLanjutList as $tl):
                $overdue = !empty($tl['due_date'])
                  && $tl['due_date'] < date('Y-m-d')
                  && !in_array($tl['status'], ['done','cancelled']);
                $pc  = $priorityColor[$tl['priority']] ?? 'secondary';
                $plbl = $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']);
                $tlc = $tlStatusColor[$tl['status']]   ?? 'secondary';
                $tllbl = $tlStatusLabel[$tl['status']]  ?? ucfirst($tl['status']);
              ?>
              <tr class="<?= $overdue ? 'show-tl-overdue' : '' ?>">
                <td>
                  <div class="show-tl-desc"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($overdue): ?>
                  <span class="show-badge show-badge-red" style="margin-top:.2rem;display:inline-flex;">Terlambat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tl['assigned_name'])): ?>
                  <div class="d-flex align-items-center gap-2">
                    <span class="show-avatar"><?= strtoupper(mb_substr($tl['assigned_name'],0,1)) ?></span>
                    <span style="font-size:13px;"><?= htmlspecialchars($tl['assigned_name']) ?></span>
                  </div>
                  <?php else: ?><span class="text-muted">&mdash;</span><?php endif; ?>
                </td>
                <td>
                  <span class="show-deadline <?= $overdue ? 'show-deadline-over' : '' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
                  </span>
                </td>
                <td>
                  <span class="show-badge show-badge-<?= $pc ?>"><?= $plbl ?></span>
                </td>
                <td>
                  <span class="show-badge show-badge-<?= $tlc ?>"><?= $tllbl ?></span>
                </td>
                <td>
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>" class="show-link-detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
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
      <div id="tab-peserta" class="show-tab-panel">
        <?php if (empty($participants)): ?>
        <div class="show-empty">
          <div class="show-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <p class="show-empty-text">Belum ada peserta terdaftar</p>
        </div>
        <?php else: ?>
        <div class="show-peserta-grid">
          <?php foreach ($participants as $p):
            $psc  = $participantStatusColor[$p['status']] ?? 'secondary';
            $plbl = $participantStatusLabel[$p['status']] ?? ucfirst($p['status']);
            $initials = strtoupper(mb_substr($p['name'],0,1));
            $avatarColors = ['#7B1C1C','#A83218','#C9A84C','#2F6BC4','#1a7340','#7d3cb5'];
            $avatarBg = $avatarColors[crc32($p['name']) % count($avatarColors)];
          ?>
          <div class="show-peserta-card">
            <div class="show-peserta-avatar" style="background:<?= $avatarBg ?>">
              <?= $initials ?>
            </div>
            <div class="show-peserta-info">
              <div class="show-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['position'])): ?>
              <div class="show-peserta-pos"><?= htmlspecialchars($p['position']) ?></div>
              <?php endif; ?>
            </div>
            <span class="show-badge show-badge-<?= $psc ?>"><?= $plbl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- ============================  MODALS  ============================ -->

<!-- Modal Ubah Status -->
<?php if (Auth::hasRole('admin','sekretaris')): ?>
<div class="modal modal-blur fade" id="modalEditStatus" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <select name="status" class="form-select">
            <?php foreach ($statusLabel as $val => $label): ?>
            <option value="<?= $val ?>" <?= $meeting['status'] === $val ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Tindak Lanjut -->
<div class="modal modal-blur fade" id="modalAddTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="d-flex align-items-center gap-2">
          <span class="show-modal-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </span>
          <h5 class="modal-title">Tambah Tindak Lanjut</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Deskripsi Tugas</label>
          <textarea id="tl-deskripsi" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan hasil evaluasi Q2..." required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-md-6">
            <label class="form-label">Ditugaskan ke</label>
            <select id="tl-assigned" class="form-select">
              <option value="">-- Pilih peserta --</option>
              <?php foreach ($participants as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Deadline</label>
            <input type="date" id="tl-deadline" class="form-control" min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Prioritas</label>
          <div class="d-flex gap-3">
            <?php foreach (['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'] as $v => $l): ?>
            <label class="form-check">
              <input type="radio" name="tl-priority" class="form-check-input"
                     value="<?= $v ?>" <?= $v === 'medium' ? 'checked' : '' ?>>
              <span class="form-check-label"><?= $l ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-save-tl">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Hapus -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal modal-blur fade" id="modalDeleteMeeting" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="show-delete-icon mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mb-1">Hapus Kegiatan?</h5>
        <p class="text-muted small mb-0">
          Kegiatan <strong class="text-danger"><?= htmlspecialchars($meeting['title']) ?></strong> akan dihapus permanen.
          Semua peserta, notulen, dan tindak lanjut ikut terhapus.
        </p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/delete" class="d-inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ============================  STYLES  ============================ -->
<style>
/* ─ Hero ────────────────────────────────────────────────────────── */
.show-hero {
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 60%, #A83218 100%);
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
  overflow: hidden; position: relative;
}
.show-hero::after {
  content: ''; position: absolute; top: -40px; right: -40px;
  width: 200px; height: 200px; border-radius: 50%;
  background: rgba(201,168,76,.09); pointer-events: none;
}
.show-hero-inner { padding: 1.4rem 1.6rem 1rem; }

.show-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .5rem;
}
.show-breadcrumb a { color: rgba(255,255,255,.75); text-decoration: none; }
.show-breadcrumb a:hover { color: #fff; }

.show-hero-title {
  font-size: clamp(17px, 2.5vw, 24px);
  font-weight: 800; color: #fff; margin: 0;
  letter-spacing: -.02em; line-height: 1.25;
}

.show-status-badge {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 12px; font-weight: 700; padding: .3em .8em;
  border-radius: 20px; white-space: nowrap;
}
.show-status-blue     { background: rgba(147,197,253,.18); color: #bfdbfe; }
.show-status-orange   { background: rgba(253,211,77,.18);  color: #fde68a; }
.show-status-green    { background: rgba(134,239,172,.18); color: #bbf7d0; }
.show-status-red      { background: rgba(252,165,165,.18); color: #fecaca; }
.show-status-secondary{ background: rgba(255,255,255,.15); color: rgba(255,255,255,.8); }

.show-dept-chip, .show-creator-chip {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.75);
  background: rgba(255,255,255,.12); padding: .25em .65em; border-radius: 20px;
}

.show-hero-actions .btn { font-size: 13px; font-weight: 600; border-radius: 8px; display: inline-flex; align-items: center; gap: .35rem; padding: .45rem 1rem; }
.show-btn-edit   { background: var(--gold); border-color: var(--gold-dark); color: #3D0A0A; }
.show-btn-edit:hover { background: var(--gold-dark); color: #fff; }
.show-btn-status { background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3); color: #fff; }
.show-btn-status:hover { background: rgba(255,255,255,.25); color: #fff; }
.show-btn-danger { background: rgba(192,57,43,.25); border: 1.5px solid rgba(192,57,43,.5); color: #fca5a5; }
.show-btn-danger:hover { background: rgba(192,57,43,.4); color: #fff; }

/* Time strip */
.show-time-strip {
  display: flex; align-items: center; flex-wrap: wrap; gap: .5rem;
  background: rgba(0,0,0,.18); padding: .6rem 1.6rem;
  font-size: 13px; color: rgba(255,255,255,.82); backdrop-filter: blur(4px);
}
.show-time-item { display: flex; align-items: center; gap: .35rem; }
.show-time-div  { color: rgba(255,255,255,.4); font-size: 16px; }
.show-loc-link  { color: var(--gold); text-decoration: none; }
.show-loc-link:hover { text-decoration: underline; }

/* ─ Stat cards ────────────────────────────────────────────── */
.show-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.show-stat-card {
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 10px; padding: .7rem .8rem;
  text-align: center; box-shadow: 0 1px 4px rgba(0,0,0,.05);
}
.show-stat-val  { font-size: 22px; font-weight: 800; color: var(--brand); line-height: 1; }
.show-stat-lbl  { font-size: 11px; color: var(--text-muted); margin-top: .15rem; font-weight: 500; }
.show-stat-green .show-stat-val { color: #1e7a2e; }
.show-stat-red   .show-stat-val { color: #a82515; }

/* ─ Sidebar card ─────────────────────────────────────────── */
.show-sidebar-card { border: 1px solid var(--border-light); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,.05); }
.show-sidebar-card-header {
  display: flex; align-items: center; gap: .4rem;
  font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
  color: var(--brand); background: #faf4eb;
  padding: .55rem .85rem; border-bottom: 1px solid var(--border-light);
}
.show-sidebar-body { padding: .75rem .85rem; background: #fff; }

/* Doc buttons */
.show-doc-btn {
  font-size: 13px; font-weight: 600; border-radius: 8px; padding: .45rem .85rem;
  display: inline-flex; align-items: center; gap: .4rem; width: 100%;
  justify-content: center; transition: all .15s;
}
.show-doc-btn-primary       { background: var(--brand); border: none; color: #fff; }
.show-doc-btn-primary:hover { background: var(--brand-dark); color: #fff; }
.show-doc-btn-outline-red   { background: transparent; border: 1.5px solid rgba(168,37,21,.35); color: #a82515; }
.show-doc-btn-outline-red:hover { background: rgba(168,37,21,.06); }
.show-doc-btn-outline       { background: transparent; border: 1.5px solid var(--border); color: var(--text-main); }
.show-doc-btn-outline:hover { border-color: var(--brand); color: var(--brand); }
.show-doc-btn-outline-green { background: transparent; border: 1.5px solid rgba(30,122,46,.35); color: #1e7a2e; }
.show-doc-btn-outline-green:hover { background: rgba(30,122,46,.06); }

/* ─ Main card / tab ──────────────────────────────────────── */
.show-main-card {
  border: 1px solid var(--border-light); border-radius: 14px;
  overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.show-tab-nav {
  display: flex; border-bottom: 2px solid var(--border-light);
  background: #faf6ef; padding: 0 .75rem;
}
.show-tab-btn {
  background: none; border: none; font-size: 13.5px; font-weight: 600;
  color: var(--text-muted); padding: .75rem 1.1rem; cursor: pointer;
  display: inline-flex; align-items: center; gap: .4rem;
  border-bottom: 2.5px solid transparent; margin-bottom: -2px;
  transition: color .15s, border-color .15s;
}
.show-tab-btn:hover { color: var(--brand); }
.show-tab-btn.active { color: var(--brand); border-bottom-color: var(--brand); }
.show-tab-count {
  background: rgba(123,28,28,.12); color: var(--brand);
  font-size: 11px; font-weight: 700; padding: .1em .5em; border-radius: 20px;
  min-width: 20px; text-align: center;
}
.show-tab-btn.active .show-tab-count { background: var(--brand); color: #fff; }

.show-tab-panel { display: none; }
.show-tab-panel.active { display: block; }

.show-tab-toolbar {
  padding: .7rem 1rem; border-bottom: 1px solid var(--border-light);
  background: #fff; display: flex; align-items: center; justify-content: flex-end;
}
.show-btn-add {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 600; border-radius: 8px;
  padding: .42rem 1rem; display: inline-flex; align-items: center; gap: .35rem;
  transition: all .15s; cursor: pointer;
}
.show-btn-add:hover { background: var(--brand-dark); box-shadow: 0 3px 10px rgba(123,28,28,.22); }

/* Empty */
.show-empty {
  display: flex; flex-direction: column; align-items: center;
  padding: 3.5rem 2rem; text-align: center; color: var(--text-muted);
}
.show-empty-icon {
  width: 64px; height: 64px; background: var(--brand-light); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1rem; color: var(--brand);
}
.show-empty-text { font-size: 13.5px; color: var(--text-muted); margin-bottom: 1rem; }

/* TL table */
.show-tl-table {
  width: 100%; border-collapse: collapse; font-size: 13.5px;
}
.show-tl-table thead th {
  background: #faf6ef; border-bottom: 2px solid var(--border);
  padding: .6rem 1rem; font-size: 10.5px; font-weight: 700;
  letter-spacing: .07em; text-transform: uppercase; color: var(--text-muted);
  white-space: nowrap;
}
.show-tl-table tbody td {
  padding: .75rem 1rem; border-bottom: 1px solid var(--border-light);
  vertical-align: middle;
}
.show-tl-table tbody tr:last-child td { border-bottom: none; }
.show-tl-table tbody tr:hover { background: #faf4eb; }
.show-tl-overdue { background: #fff5f5 !important; }
.show-tl-overdue:hover { background: #fee9e9 !important; }

.show-tl-desc {
  font-size: 13px; color: var(--text-main); line-height: 1.4;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.show-deadline { font-size: 13px; color: var(--text-main); white-space: nowrap; }
.show-deadline-over { color: #a82515; font-weight: 700; }

.show-link-detail {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 12px; font-weight: 600; color: var(--brand);
  text-decoration: none; border: 1.5px solid var(--brand);
  border-radius: 6px; padding: .2rem .55rem; white-space: nowrap;
  transition: all .14s;
}
.show-link-detail:hover { background: var(--brand); color: #fff; }

/* Badge */
.show-badge {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 11.5px; font-weight: 700; padding: .25em .65em; border-radius: 20px; white-space: nowrap;
}
.show-badge-red       { background: rgba(168,37,21,.10);  color: #a82515; }
.show-badge-orange    { background: rgba(201,168,76,.14);  color: #7a5f00; }
.show-badge-green     { background: rgba(47,107,64,.10);   color: #1e7a2e; }
.show-badge-blue      { background: rgba(32,107,196,.10);  color: #1557a0; }
.show-badge-teal      { background: rgba(14,116,144,.10);  color: #0e5f74; }
.show-badge-secondary { background: rgba(100,100,100,.10); color: #64748b; }

/* Avatar */
.show-avatar {
  width: 28px; height: 28px; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 12px; font-weight: 700;
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Peserta grid */
.show-peserta-grid { padding: .75rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: .5rem; }
.show-peserta-card {
  display: flex; align-items: center; gap: .65rem;
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 10px; padding: .65rem .85rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.04); transition: box-shadow .15s;
}
.show-peserta-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.10); }
.show-peserta-avatar {
  width: 36px; height: 36px; border-radius: 50%;
  color: #fff; font-size: 14px; font-weight: 800;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.show-peserta-info { flex: 1; min-width: 0; }
.show-peserta-name { font-size: 13.5px; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.show-peserta-pos  { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Modal icon */
.show-modal-icon {
  width: 30px; height: 30px; background: var(--brand-light); border-radius: 7px;
  display: inline-flex; align-items: center; justify-content: center;
  color: var(--brand); flex-shrink: 0;
}
.show-delete-icon {
  width: 60px; height: 60px; background: rgba(168,37,21,.10); border-radius: 50%;
  display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #a82515;
}

/* Responsive */
@media (max-width: 767.98px) {
  .show-hero-inner   { padding: 1rem; }
  .show-hero-title   { font-size: 16px; }
  .show-time-strip   { padding: .5rem 1rem; font-size: 12px; }
  .show-stat-grid    { grid-template-columns: repeat(4,1fr); }
  .show-peserta-grid { grid-template-columns: 1fr; }
}
</style>

<?php
$tlUrl      = json_encode($baseUrl . '/tindak-lanjut');
$inviteUrl  = json_encode($baseUrl . '/meetings/' . $meeting['id'] . '/send-invitations');
$summaryUrl = json_encode($baseUrl . '/meetings/' . $meeting['id'] . '/send-summary');
?>
<script>
// Tab switching
document.querySelectorAll('.show-tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.show-tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.show-tab-panel').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
  });
});

// Tambah TL
document.getElementById('btn-save-tl')?.addEventListener('click', async () => {
  const deskripsi = document.getElementById('tl-deskripsi').value.trim();
  if (!deskripsi) { alert('Deskripsi wajib diisi!'); return; }
  const priority = document.querySelector('input[name="tl-priority"]:checked')?.value || 'medium';
  const btn = document.getElementById('btn-save-tl');
  btn.disabled = true; btn.textContent = 'Menyimpan...';
  try {
    const res = await fetch(<?= $tlUrl ?>, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        meeting_id:  <?= (int)$meeting['id'] ?>,
        description: deskripsi,
        assigned_to: document.getElementById('tl-assigned').value,
        due_date:    document.getElementById('tl-deadline').value,
        priority
      })
    });
    const d = await res.json();
    if (d.success) {
      bootstrap.Modal.getInstance(document.getElementById('modalAddTL')).hide();
      location.reload();
    } else { alert(d.message || 'Gagal menyimpan'); }
  } catch(e) { alert('Terjadi kesalahan jaringan'); }
  btn.disabled = false;
});

// Kirim Undangan
document.getElementById('btn-send-invitation')?.addEventListener('click', async () => {
  if (!confirm('Kirim undangan email ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-invitation');
  btn.disabled = true;
  const res  = await fetch(<?= $inviteUrl ?>, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false;
  alert(data.message || (data.success ? 'Undangan terkirim!' : 'Gagal mengirim.'));
});

// Kirim Ringkasan
document.getElementById('btn-send-summary')?.addEventListener('click', async () => {
  if (!confirm('Kirim ringkasan notulen ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-summary');
  btn.disabled = true;
  const res  = await fetch(<?= $summaryUrl ?>, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false;
  alert(data.message || (data.success ? 'Ringkasan terkirim!' : 'Gagal mengirim.'));
});
</script>
