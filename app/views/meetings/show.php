<?php
$baseUrl = rtrim(BASE_URL, '/');

// ── Status maps ────────────────────────────────────────────────────────────
$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusColor = [
  'scheduled' => 'blue',
  'ongoing'   => 'orange',
  'done'      => 'green',
  'cancelled' => 'red',
];
$statusIcon = [
  'scheduled' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];
$pStatusColor = [
  'accepted' => 'green',
  'invited'  => 'blue',
  'declined' => 'red',
  'attended' => 'teal',
  'pending'  => 'secondary',
];
$pStatusLabel = [
  'accepted' => 'Diterima',
  'invited'  => 'Diundang',
  'declined' => 'Ditolak',
  'attended' => 'Hadir',
  'pending'  => 'Menunggu',
];
$prioColor = ['high' => 'red', 'medium' => 'orange', 'low' => 'green'];
$prioLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$tlStatusColor = ['pending' => 'secondary', 'in_progress' => 'blue', 'done' => 'green', 'cancelled' => 'red'];
$tlStatusLabel = ['pending' => 'Menunggu', 'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

// ── Computed vars ──────────────────────────────────────────────────────────
$canEdit      = $canEdit ?? false;
$participants = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];

$mStatus    = $meeting['status'] ?? 'scheduled';
$mColor     = $statusColor[$mStatus]  ?? 'secondary';
$mLabel     = $statusLabel[$mStatus]  ?? ucfirst($mStatus);
$mIcon      = $statusIcon[$mStatus]   ?? '';
$loc        = trim($meeting['location'] ?? '');
$isLink     = $loc && (str_starts_with($loc,'http://') || str_starts_with($loc,'https://'));
$brand      = htmlspecialchars($meeting['color'] ?? '#7B1C1C');

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => $t['status'] === 'done'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList,
  fn($t) => !empty($t['due_date']) && $t['due_date'] < $today
         && !in_array($t['status'], ['done','cancelled'])
));
$progressPct  = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;

$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');
?>

<?php /* ── Flash toast ── */ ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="sh-toast" id="shToast" role="alert" aria-live="polite">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="sh-toast-close" onclick="document.getElementById('shToast').remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="sh-toast sh-toast-error" id="shToastErr" role="alert" aria-live="polite">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="sh-toast-close" onclick="document.getElementById('shToastErr').remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php /* ================================================================
   HERO
================================================================ */ ?>
<div class="sh-hero mb-4" style="--brand:<?= $brand ?>">
  <div class="sh-hero-body">
    <div class="sh-hero-top">

      <div class="sh-hero-left">
        <nav class="sh-breadcrumb" aria-label="Breadcrumb">
          <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Detail</span>
        </nav>
        <h1 class="sh-title"><?= htmlspecialchars($meeting['title']) ?></h1>
        <div class="sh-meta">
          <span class="sh-status sh-status-<?= $mColor ?>"><?= $mIcon ?>&nbsp;<?= $mLabel ?></span>
          <?php if (!empty($meeting['dept_name'])): ?>
          <span class="sh-chip">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($meeting['dept_name']) ?>
          </span>
          <?php endif; ?>
          <span class="sh-chip">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($meeting['creator_name'] ?? '-') ?>
          </span>
        </div>
      </div>

      <div class="sh-hero-actions">
        <?php if ($canEdit): ?>
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="sh-btn sh-btn-gold">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <button type="button" class="sh-btn sh-btn-ghost" data-bs-toggle="modal" data-bs-target="#modalStatus">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Ubah Status
        </button>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?>
        <button type="button" class="sh-btn sh-btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="sh-hero-strip">
    <span class="sh-strip-item">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <strong>Mulai:</strong>&nbsp;<?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?>
    </span>
    <span class="sh-strip-sep">&rarr;</span>
    <span class="sh-strip-item">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <strong>Selesai:</strong>&nbsp;<?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?>
    </span>
    <?php if ($loc): ?>
    <span class="sh-strip-sep">&middot;</span>
    <span class="sh-strip-item">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="sh-strip-link">Buka Link Kegiatan <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>
      <?php else: ?>
        <?= htmlspecialchars($loc) ?>
      <?php endif; ?>
    </span>
    <?php endif; ?>
  </div>
</div>

<?php /* ================================================================
   BODY LAYOUT
================================================================ */ ?>
<div class="row g-3">

  <?php /* ── SIDEBAR ── */ ?>
  <div class="col-xl-3 col-lg-4">

    <?php /* Stat cards */ ?>
    <div class="sh-stat-grid mb-3">
      <div class="sh-stat">
        <div class="sh-stat-ico sh-stat-ico-blue">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalPeserta ?></div>
          <div class="sh-stat-lbl">Peserta</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico sh-stat-ico-purple">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalTL ?></div>
          <div class="sh-stat-lbl">Tindak Lanjut</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico sh-stat-ico-green">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <div class="sh-stat-val sh-val-green"><?= $doneTL ?></div>
          <div class="sh-stat-lbl">Selesai</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico <?= $overdueTL > 0 ? 'sh-stat-ico-red' : 'sh-stat-ico-muted' ?>">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <div class="sh-stat-val <?= $overdueTL > 0 ? 'sh-val-red' : '' ?>"><?= $overdueTL ?></div>
          <div class="sh-stat-lbl">Terlambat</div>
        </div>
      </div>
    </div>

    <?php /* Progress TL */ ?>
    <?php if ($totalTL > 0): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">Progress Tindak Lanjut</div>
      <div class="sh-card-body">
        <div class="sh-prog-row">
          <span class="sh-prog-pct"><?= $progressPct ?>%</span>
          <span class="sh-prog-meta"><?= $doneTL ?> / <?= $totalTL ?></span>
        </div>
        <div class="sh-prog-bar">
          <div class="sh-prog-fill" style="width:<?= $progressPct ?>%"></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Agenda */ ?>
    <?php if (!empty($meeting['description'])): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Agenda
      </div>
      <div class="sh-card-body">
        <p class="sh-agenda-text"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <?php /* Dokumen */ ?>
    <div class="sh-card">
      <div class="sh-card-hd">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Dokumen
      </div>
      <div class="sh-card-body sh-doc-list">
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="sh-doc-btn sh-doc-primary">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>/export-docx" class="sh-doc-btn sh-doc-outline">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export DOCX
        </a>
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <button type="button" class="sh-doc-btn sh-doc-outline" id="btnKirimUndangan">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Kirim Undangan
        </button>
        <?php if (($meeting['status'] ?? '') === 'done'): ?>
        <button type="button" class="sh-doc-btn sh-doc-outline-green" id="btnKirimRingkasan">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <?php /* ── MAIN PANEL ── */ ?>
  <div class="col-xl-9 col-lg-8">
    <div class="sh-main-card">

      <?php /* Tab nav */ ?>
      <div class="sh-tabs" role="tablist">
        <button type="button" class="sh-tab active" data-tab="tl" role="tab" aria-selected="true" aria-controls="sh-tab-tl">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tindak Lanjut
          <span class="sh-badge-pill"><?= $totalTL ?></span>
        </button>
        <button type="button" class="sh-tab" data-tab="peserta" role="tab" aria-selected="false" aria-controls="sh-tab-peserta">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta
          <span class="sh-badge-pill"><?= $totalPeserta ?></span>
        </button>
      </div>

      <?php /* ── Tab: Tindak Lanjut ── */ ?>
      <div id="sh-tab-tl" class="sh-tab-panel active" role="tabpanel">

        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <div class="sh-panel-toolbar">
          <button type="button" class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <p>Belum ada tindak lanjut</p>
          <?php if (Auth::hasRole('admin','sekretaris')): ?>
          <button type="button" class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Sekarang
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
          <table class="sh-table" aria-label="Daftar Tindak Lanjut">
            <thead>
              <tr>
                <th style="width:38%">Tugas</th>
                <th>PIC</th>
                <th>Deadline</th>
                <th>Prioritas</th>
                <th>Status</th>
                <th style="width:56px"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $isOver = !empty($tl['due_date'])
                && $tl['due_date'] < $today
                && !in_array($tl['status'], ['done','cancelled']);
              $pc  = $prioColor[$tl['priority']] ?? 'secondary';
              $pl  = $prioLabel[$tl['priority']] ?? ucfirst($tl['priority'] ?? '');
              $tc  = $tlStatusColor[$tl['status']] ?? 'secondary';
              $tl_ = $tlStatusLabel[$tl['status']] ?? ucfirst($tl['status'] ?? '');
            ?>
              <tr class="<?= $isOver ? 'sh-tr-over' : '' ?>">
                <td>
                  <div class="sh-tl-desc"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($isOver): ?>
                  <span class="sh-badge sh-badge-red sh-badge-sm" style="margin-top:.2rem">Terlambat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tl['assigned_name'])): ?>
                  <div class="sh-pic">
                    <span class="sh-av sh-av-sm"><?= strtoupper(mb_substr($tl['assigned_name'],0,1)) ?></span>
                    <span><?= htmlspecialchars($tl['assigned_name']) ?></span>
                  </div>
                  <?php else: ?><span class="sh-muted">&mdash;</span><?php endif; ?>
                </td>
                <td>
                  <span class="<?= $isOver ? 'sh-deadline-over' : 'sh-deadline' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '&mdash;' ?>
                  </span>
                </td>
                <td><span class="sh-badge sh-badge-<?= $pc ?>"><?= $pl ?></span></td>
                <td><span class="sh-badge sh-badge-<?= $tc ?>"><?= $tl_ ?></span></td>
                <td>
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="sh-btn-detail">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
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

      <?php /* ── Tab: Peserta ── */ ?>
      <div id="sh-tab-peserta" class="sh-tab-panel" role="tabpanel">
        <?php
        $avPalette = ['#7B1C1C','#A83218','#C9A84C','#2F6BC4','#1a7340','#7d3cb5','#0d7a8a','#b5530a'];
        ?>
        <?php if (empty($participants)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <p>Belum ada peserta terdaftar</p>
        </div>
        <?php else: ?>
        <div class="sh-peserta-grid">
          <?php foreach ($participants as $p):
            $psc = $pStatusColor[$p['status']] ?? 'secondary';
            $psl = $pStatusLabel[$p['status']] ?? ucfirst($p['status'] ?? '');
            $bg  = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
          ?>
          <div class="sh-peserta-card">
            <span class="sh-av" style="background:<?= $bg ?>">
              <?= strtoupper(mb_substr($p['name'],0,1)) ?>
            </span>
            <div class="sh-peserta-info">
              <div class="sh-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['position'])): ?>
              <div class="sh-peserta-pos"><?= htmlspecialchars($p['position']) ?></div>
              <?php endif; ?>
            </div>
            <span class="sh-badge sh-badge-<?= $psc ?>"><?= $psl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div><!-- /sh-main-card -->
  </div>
</div>

<?php /* ================================================================
   MODALS
================================================================ */ ?>

<?php if (Auth::hasRole('admin','sekretaris')): ?>

<?php /* Modal Ubah Status */ ?>
<div class="modal modal-blur fade" id="modalStatus" tabindex="-1" aria-labelledby="modalStatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="modalStatusLabel">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Ubah Status
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <label class="form-label fw-semibold" for="modalStatusSelect">Status baru</label>
          <select id="modalStatusSelect" name="status" class="form-select">
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

<?php /* Modal Tambah Tindak Lanjut */ ?>
<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1" aria-labelledby="modalTLLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTLLabel">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-1"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tambah Tindak Lanjut
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label class="form-label fw-semibold" for="tlDesc">Deskripsi Tugas <span class="text-danger">*</span></label>
          <textarea id="tlDesc" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan evaluasi Q2…"></textarea>
          <div class="invalid-feedback">Deskripsi wajib diisi.</div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="tlAssigned">Ditugaskan ke</label>
            <select id="tlAssigned" class="form-select">
              <option value="">— Pilih peserta —</option>
              <?php foreach ($participants as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold" for="tlDeadline">Deadline</label>
            <input type="date" id="tlDeadline" class="form-control" min="<?= $today ?>">
          </div>
        </div>

        <div>
          <label class="form-label fw-semibold">Prioritas</label>
          <div class="d-flex gap-3 flex-wrap">
            <?php foreach (['low' => 'Rendah','medium' => 'Sedang','high' => 'Tinggi'] as $v => $l): ?>
            <label class="form-check">
              <input type="radio" name="tlPriority" class="form-check-input" value="<?= $v ?>" <?= $v === 'medium' ? 'checked' : '' ?>>
              <span class="form-check-label"><?= $l ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btnSaveTL">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>

<?php endif; /* admin/sekretaris */ ?>

<?php if (Auth::hasRole('admin')): ?>
<?php /* Modal Hapus */ ?>
<div class="modal modal-blur fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="sh-del-ico mb-3">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mb-1" id="modalHapusLabel">Hapus Kegiatan?</h5>
        <p class="text-muted small mb-0">
          Kegiatan <strong class="text-danger"><?= htmlspecialchars($meeting['title']) ?></strong>
          akan dihapus permanen beserta semua notulen, peserta, dan tindak lanjut.
        </p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/delete">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php /* ================================================================
   JAVASCRIPT
================================================================ */ ?>
<script>
(function () {
  'use strict';

  var BASE    = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  var MTG_ID  = <?= (int)$meeting['id'] ?>;
  var CSRF    = <?= json_encode($csrfToken) ?>;

  /* ── Tab switch ──────────────────────────────────────────────── */
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

  /* ── Tambah TL ───────────────────────────────────────────────── */
  var btnSaveTL = document.getElementById('btnSaveTL');
  if (btnSaveTL) {
    btnSaveTL.addEventListener('click', function () {
      var desc     = document.getElementById('tlDesc');
      var assigned = document.getElementById('tlAssigned');
      var deadline = document.getElementById('tlDeadline');
      var prio     = document.querySelector('input[name="tlPriority"]:checked');

      desc.classList.remove('is-invalid');
      if (!desc.value.trim()) {
        desc.classList.add('is-invalid');
        desc.focus();
        return;
      }

      var origHtml = btnSaveTL.innerHTML;
      btnSaveTL.disabled = true;
      btnSaveTL.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';

      fetch(BASE + '/tindak-lanjut', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
          meeting_id:  MTG_ID,
          description: desc.value.trim(),
          assigned_to: assigned ? (assigned.value || null) : null,
          due_date:    deadline ? (deadline.value || null) : null,
          priority:    prio ? prio.value : 'medium',
          _csrf:       CSRF
        })
      })
      .then(function (r) {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
      })
      .then(function (data) {
        if (data.success) {
          window.location.reload();
        } else {
          shAlert(data.message || 'Gagal menyimpan tindak lanjut.');
          btnSaveTL.disabled = false;
          btnSaveTL.innerHTML = origHtml;
        }
      })
      .catch(function (err) {
        shAlert('Terjadi kesalahan: ' + err.message);
        btnSaveTL.disabled = false;
        btnSaveTL.innerHTML = origHtml;
      });
    });
  }

  /* ── Kirim Undangan ──────────────────────────────────────────── */
  var btnInv = document.getElementById('btnKirimUndangan');
  if (btnInv) {
    btnInv.addEventListener('click', function () {
      if (!confirm('Kirim undangan email ke semua peserta?')) return;
      var orig = btnInv.innerHTML;
      btnInv.disabled = true;
      btnInv.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-invitations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { shAlert(d.message || 'Undangan terkirim.', 'success'); })
      .catch(function () { shAlert('Gagal mengirim undangan.'); })
      .finally(function () { btnInv.disabled = false; btnInv.innerHTML = orig; });
    });
  }

  /* ── Kirim Ringkasan ─────────────────────────────────────────── */
  var btnSum = document.getElementById('btnKirimRingkasan');
  if (btnSum) {
    btnSum.addEventListener('click', function () {
      if (!confirm('Kirim ringkasan kegiatan ke semua peserta?')) return;
      var orig = btnSum.innerHTML;
      btnSum.disabled = true;
      btnSum.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-summary', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { shAlert(d.message || 'Ringkasan terkirim.', 'success'); })
      .catch(function () { shAlert('Gagal mengirim ringkasan.'); })
      .finally(function () { btnSum.disabled = false; btnSum.innerHTML = orig; });
    });
  }

  /* ── Toast helper ────────────────────────────────────────────── */
  function shAlert(msg, type) {
    var t = document.createElement('div');
    t.className = 'sh-toast' + (type === 'success' ? '' : ' sh-toast-error');
    t.setAttribute('role', 'alert');
    t.innerHTML = msg + '<button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    setTimeout(function () { t.style.opacity = '0'; setTimeout(function () { t.remove(); }, 400); }, 4000);
  }

  /* ── Auto-dismiss flash toasts ───────────────────────────────── */
  ['shToast','shToastErr'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) setTimeout(function () { el.style.opacity = '0'; setTimeout(function () { el.remove(); }, 400); }, 4500);
  });

}());
</script>

<?php /* ================================================================
   STYLES
================================================================ */ ?>
<style>
/* ── CSS custom prop fallback ── */
.sh-hero { --brand: #7B1C1C; }

/* ── Toast ── */
.sh-toast {
  position: fixed; top: 1.2rem; right: 1.2rem; z-index: 1090;
  display: flex; align-items: center; gap: .5rem;
  background: #15803d; color: #fff;
  padding: .65rem 1rem; border-radius: 10px;
  font-size: 13px; font-weight: 600;
  box-shadow: 0 4px 18px rgba(0,0,0,.18);
  transition: opacity .4s ease;
  max-width: 340px;
}
.sh-toast-error { background: #b91c1c; }
.sh-toast-close {
  background: none; border: none; color: inherit; cursor: pointer;
  font-size: 18px; line-height: 1; padding: 0 0 0 .4rem; opacity: .8;
}
.sh-toast-close:hover { opacity: 1; }

/* ── Hero ── */
.sh-hero {
  background: linear-gradient(135deg, var(--brand) 0%, color-mix(in srgb, var(--brand) 75%, #fff 25%) 100%);
  border-radius: 14px;
  box-shadow: 0 4px 24px rgba(0,0,0,.16);
  overflow: hidden;
}
.sh-hero-body   { padding: 1.4rem 1.6rem 1rem; }
.sh-hero-top    { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem; }
.sh-breadcrumb  { display: flex; align-items: center; gap: .3rem; font-size: 12px; color: rgba(255,255,255,.6); margin-bottom: .45rem; }
.sh-breadcrumb a { color: rgba(255,255,255,.8); text-decoration: none; }
.sh-breadcrumb a:hover { color: #fff; text-decoration: underline; }
.sh-title {
  font-size: clamp(15px, 2.4vw, 22px); font-weight: 800;
  color: #fff; margin: 0; letter-spacing: -.02em; line-height: 1.25;
}
.sh-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-top: .5rem; }
.sh-status {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 11.5px; font-weight: 700; padding: .25em .72em; border-radius: 20px;
}
.sh-status-blue     { background: rgba(147,197,253,.2);  color: #bfdbfe; }
.sh-status-orange   { background: rgba(253,186,116,.2);  color: #fed7aa; }
.sh-status-green    { background: rgba(134,239,172,.2);  color: #bbf7d0; }
.sh-status-red      { background: rgba(252,165,165,.2);  color: #fecaca; }
.sh-status-secondary{ background: rgba(255,255,255,.15); color: rgba(255,255,255,.8); }
.sh-chip {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.75);
  background: rgba(255,255,255,.12); padding: .22em .65em; border-radius: 20px;
}
.sh-hero-actions  { display: flex; flex-wrap: wrap; gap: .4rem; }

/* ── Hero action buttons ── */
.sh-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600; padding: .42rem .9rem;
  border-radius: 8px; border: none; cursor: pointer; text-decoration: none;
  transition: background .18s, color .18s;
  white-space: nowrap;
}
.sh-btn-gold   { background: #C9A84C; color: #3D0A0A; }
.sh-btn-gold:hover { background: #b08e3a; color: #fff; }
.sh-btn-ghost  { background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3); color: #fff; }
.sh-btn-ghost:hover { background: rgba(255,255,255,.25); }
.sh-btn-danger { background: rgba(185,28,28,.25); border: 1.5px solid rgba(185,28,28,.45); color: #fca5a5; }
.sh-btn-danger:hover { background: rgba(185,28,28,.45); color: #fff; }

/* ── Strip ── */
.sh-hero-strip {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
  background: rgba(0,0,0,.18); padding: .5rem 1.6rem;
  font-size: 12.5px; color: rgba(255,255,255,.82);
}
.sh-strip-item { display: flex; align-items: center; gap: .3rem; }
.sh-strip-sep  { color: rgba(255,255,255,.35); }
.sh-strip-link { color: #C9A84C; text-decoration: none; display: inline-flex; align-items: center; gap: .25rem; }
.sh-strip-link:hover { text-decoration: underline; }

/* ── Stat cards ── */
.sh-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.sh-stat {
  display: flex; align-items: center; gap: .7rem;
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 10px; padding: .75rem .85rem;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sh-stat-ico {
  display: flex; align-items: center; justify-content: center;
  width: 34px; height: 34px; border-radius: 8px; flex-shrink: 0;
}
.sh-stat-ico-blue   { background: #dbeafe; color: #1d4ed8; }
.sh-stat-ico-purple { background: #ede9fe; color: #6d28d9; }
.sh-stat-ico-green  { background: #dcfce7; color: #15803d; }
.sh-stat-ico-red    { background: #fee2e2; color: #b91c1c; }
.sh-stat-ico-muted  { background: #f1f0ee; color: #888; }
.sh-stat-val { font-size: 20px; font-weight: 800; color: #222; line-height: 1; }
.sh-stat-lbl { font-size: 11px; color: #888; font-weight: 500; margin-top: .1rem; }
.sh-val-green { color: #15803d; }
.sh-val-red   { color: #b91c1c; }

/* ── Progress ── */
.sh-prog-row   { display: flex; justify-content: space-between; align-items: center; margin-bottom: .35rem; }
.sh-prog-pct   { font-size: 13px; font-weight: 700; color: #333; }
.sh-prog-meta  { font-size: 12px; color: #888; }
.sh-prog-bar   { height: 7px; background: #e8e3db; border-radius: 99px; overflow: hidden; }
.sh-prog-fill  { height: 100%; background: var(--brand, #7B1C1C); border-radius: 99px; transition: width .4s ease; }

/* ── Sidebar card ── */
.sh-card {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 10px; overflow: hidden;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.sh-card-hd {
  display: flex; align-items: center; gap: .4rem;
  font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #666;
  padding: .55rem 1rem; border-bottom: 1px solid #e8e3db; background: #fafaf8;
}
.sh-card-body    { padding: .75rem 1rem; }
.sh-agenda-text  { font-size: 13px; line-height: 1.65; color: #444; margin: 0; white-space: pre-wrap; }
.sh-doc-list     { display: flex; flex-direction: column; gap: .45rem; }
.sh-doc-btn {
  display: flex; align-items: center; justify-content: center; gap: .4rem;
  font-size: 13px; font-weight: 600; padding: .5rem .8rem;
  border-radius: 8px; border: none; cursor: pointer; text-decoration: none;
  transition: background .18s, border-color .18s; white-space: nowrap;
}
.sh-doc-primary { background: var(--brand, #7B1C1C); color: #fff; }
.sh-doc-primary:hover { filter: brightness(1.1); color: #fff; }
.sh-doc-outline { background: #fff; color: #333; border: 1.5px solid #d4cfc8; }
.sh-doc-outline:hover { background: #f5f0ea; border-color: #b5a89a; }
.sh-doc-outline-green { background: #fff; color: #15803d; border: 1.5px solid #a3d9a5; }
.sh-doc-outline-green:hover { background: #f0faf0; }

/* ── Main card ── */
.sh-main-card {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 6px rgba(0,0,0,.05);
}

/* ── Tabs ── */
.sh-tabs {
  display: flex; border-bottom: 1px solid #e8e3db;
  background: #fafaf8; padding: 0 1rem; overflow-x: auto;
}
.sh-tab {
  display: flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600; color: #777;
  padding: .75rem 1rem; border: none; background: none;
  border-bottom: 2px solid transparent; cursor: pointer;
  transition: color .18s, border-color .18s; white-space: nowrap;
}
.sh-tab:hover { color: var(--brand, #7B1C1C); }
.sh-tab.active { color: var(--brand, #7B1C1C); border-bottom-color: var(--brand, #7B1C1C); }
.sh-badge-pill {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; height: 18px; padding: 0 5px;
  font-size: 11px; font-weight: 700;
  background: #eee; color: #555; border-radius: 20px;
}
.sh-tab.active .sh-badge-pill { background: var(--brand, #7B1C1C); color: #fff; }

/* ── Tab panel ── */
.sh-tab-panel { display: none; }
.sh-tab-panel.active { display: block; }
.sh-panel-toolbar {
  display: flex; justify-content: flex-end; align-items: center;
  padding: .75rem 1rem; border-bottom: 1px solid #f0ece6;
}

/* ── Add button ── */
.sh-btn-add {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600;
  background: var(--brand, #7B1C1C); color: #fff;
  padding: .42rem .9rem; border-radius: 8px; border: none; cursor: pointer;
  transition: filter .18s;
}
.sh-btn-add:hover { filter: brightness(1.12); }

/* ── Empty state ── */
.sh-empty {
  display: flex; flex-direction: column; align-items: center;
  text-align: center; padding: 2.5rem 1rem; color: #aaa;
}
.sh-empty-ico  { color: #ccc; margin-bottom: .65rem; }
.sh-empty p    { font-size: 13px; margin: 0 0 1rem; }

/* ── Table ── */
.sh-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.sh-table thead tr  { background: #faf8f5; }
.sh-table th {
  padding: .55rem 1rem; text-align: left;
  font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
  color: #777; border-bottom: 1px solid #e8e3db;
}
.sh-table td        { padding: .7rem 1rem; border-bottom: 1px solid #f0ece6; vertical-align: middle; }
.sh-table tbody tr:last-child td { border-bottom: none; }
.sh-table tbody tr:hover td { background: #faf8f5; }
.sh-tr-over td      { background: #fff8f5 !important; }
.sh-tl-desc         { font-weight: 500; color: #222; }
.sh-muted           { color: #bbb; }
.sh-pic             { display: flex; align-items: center; gap: .45rem; }
.sh-av {
  display: inline-flex; align-items: center; justify-content: center;
  width: 30px; height: 30px; border-radius: 50%;
  font-size: 12px; font-weight: 700; color: #fff;
  background: var(--brand, #7B1C1C); flex-shrink: 0;
}
.sh-av-sm { width: 26px; height: 26px; font-size: 11px; }
.sh-deadline      { font-size: 12px; color: #555; }
.sh-deadline-over { font-size: 12px; color: #b91c1c; font-weight: 700; }
.sh-btn-detail {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 12px; font-weight: 600;
  color: var(--brand, #7B1C1C); text-decoration: none;
}
.sh-btn-detail:hover { text-decoration: underline; }

/* ── Badges ── */
.sh-badge {
  display: inline-flex; align-items: center;
  font-size: 11px; font-weight: 700;
  padding: .22em .65em; border-radius: 20px; line-height: 1.4;
}
.sh-badge-sm     { font-size: 10.5px; padding: .18em .55em; }
.sh-badge-red    { background: #fee2e2; color: #b91c1c; }
.sh-badge-orange { background: #ffedd5; color: #c2410c; }
.sh-badge-green  { background: #dcfce7; color: #15803d; }
.sh-badge-blue   { background: #dbeafe; color: #1d4ed8; }
.sh-badge-teal   { background: #ccfbf1; color: #0f766e; }
.sh-badge-secondary { background: #f1f0ee; color: #666; }

/* ── Peserta grid ── */
.sh-peserta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: .55rem; padding: .9rem 1rem;
}
.sh-peserta-card {
  display: flex; align-items: center; gap: .6rem;
  background: #faf8f5; border: 1px solid #e8e3db;
  border-radius: 10px; padding: .6rem .8rem;
}
.sh-peserta-info  { flex: 1; min-width: 0; }
.sh-peserta-name  { font-size: 13px; font-weight: 600; color: #222; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sh-peserta-pos   { font-size: 11px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Delete icon ── */
.sh-del-ico {
  display: flex; align-items: center; justify-content: center;
  width: 58px; height: 58px; border-radius: 50%;
  background: #fee2e2; color: #b91c1c; margin: 0 auto;
}

/* ── Responsive ── */
@media (max-width: 575px) {
  .sh-hero-body  { padding: 1rem; }
  .sh-hero-strip { padding: .45rem 1rem; font-size: 12px; }
  .sh-stat-grid  { grid-template-columns: repeat(2, 1fr); }
  .sh-peserta-grid { grid-template-columns: 1fr; }
  .sh-table th, .sh-table td { padding: .5rem .7rem; }
}
</style>
