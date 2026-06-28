<?php
$baseUrl = rtrim(BASE_URL, '/');

// ── Status maps ──────────────────────────────────────────────
$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusBadge = [
  'scheduled' => 'blue',
  'ongoing'   => 'orange',
  'done'      => 'green',
  'cancelled' => 'red',
];
$statusIcon = [
  'scheduled' => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];
$pStatusBadge = ['accepted'=>'green','invited'=>'blue','declined'=>'red','attended'=>'teal','pending'=>'secondary'];
$pStatusLabel = ['accepted'=>'Diterima','invited'=>'Diundang','declined'=>'Ditolak','attended'=>'Hadir','pending'=>'Menunggu'];
$prioBadge    = ['high'=>'red','medium'=>'orange','low'=>'green'];
$prioLabel    = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$tlsBadge     = ['pending'=>'secondary','in_progress'=>'blue','done'=>'green','cancelled'=>'red'];
$tlsLabel     = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];

// ── Computed vars ─────────────────────────────────────────────
$canEdit          = $canEdit ?? false;
$participants     = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];

$mStatus     = $meeting['status'] ?? 'scheduled';
$mLabel      = $statusLabel[$mStatus]  ?? ucfirst($mStatus);
$mBadge      = $statusBadge[$mStatus]  ?? 'secondary';
$mIcon       = $statusIcon[$mStatus]   ?? '';
$loc         = trim($meeting['location'] ?? '');
$isLink      = $loc && (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://'));
$brand       = htmlspecialchars($meeting['color'] ?? '#7B1C1C');

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => ($t['status'] ?? '') === 'done'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList,
  fn($t) => !empty($t['due_date']) && $t['due_date'] < $today
         && !in_array($t['status'] ?? '', ['done','cancelled'])
));
$progressPct  = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;
$csrfToken    = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$avPalette    = ['#7B1C1C','#2F6BC4','#1a7340','#7d3cb5','#C9A84C','#0d7a8a','#b5530a'];
?>

<?php /* ── Flash toasts ── */ ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="sh-toast" id="shToast" role="alert" aria-live="polite">
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="sh-toast sh-toast--err" id="shToastErr" role="alert" aria-live="polite">
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php /* ================================================================
   HERO
================================================================ */ ?>
<div class="sh-hero mb-4" style="--mc:<?= $brand ?>">

  <div class="sh-hero-inner">
    <!-- Breadcrumb -->
    <nav class="sh-breadcrumb" aria-label="Breadcrumb">
      <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Detail</span>
    </nav>

    <!-- Title row -->
    <div class="sh-hero-row">
      <div>
        <h1 class="sh-hero-title"><?= htmlspecialchars($meeting['title']) ?></h1>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
          <span class="sh-badge sh-badge-<?= $mBadge ?>"><?= $mIcon ?>&nbsp;<?= $mLabel ?></span>
          <?php if (!empty($meeting['dept_name'])): ?>
          <span class="sh-badge sh-badge-secondary">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($meeting['dept_name']) ?>
          </span>
          <?php endif; ?>
          <span class="sh-badge sh-badge-secondary">
            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <?= htmlspecialchars($meeting['creator_name'] ?? '-') ?>
          </span>
        </div>
      </div>

      <div class="sh-hero-actions">
        <?php if ($canEdit): ?>
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="btn sh-btn-gold">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button type="button" class="btn sh-btn-ghost" data-bs-toggle="modal" data-bs-target="#modalStatus">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Ubah Status
        </button>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?>
        <button type="button" class="btn sh-btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Meta strip -->
  <div class="sh-meta-strip">
    <div class="sh-meta-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span>Mulai: <strong><?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?></strong></span>
    </div>
    <div class="sh-meta-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span>Selesai: <strong><?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?></strong></span>
    </div>
    <?php if ($loc): ?>
    <div class="sh-meta-item">
      <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="sh-meta-link">
          Buka Link
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      <?php else: ?>
        <span><?= htmlspecialchars($loc) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

</div><!-- /sh-hero -->

<?php /* ================================================================
   BODY
================================================================ */ ?>
<div class="row g-3">

  <!-- ── SIDEBAR ────────────────────────────────────────── -->
  <div class="col-xl-3 col-lg-4">

    <!-- Stat cards -->
    <div class="sh-stats mb-3">
      <div class="sh-stat">
        <div class="sh-stat-ico" style="background:rgba(32,107,196,.10);color:#1557a0">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalPeserta ?></div>
          <div class="sh-stat-lbl">Peserta</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico" style="background:rgba(123,28,28,.10);color:var(--brand)">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalTL ?></div>
          <div class="sh-stat-lbl">Tindak Lanjut</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico" style="background:rgba(47,107,64,.10);color:#1e7a2e">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <div class="sh-stat-val" style="color:#1e7a2e"><?= $doneTL ?></div>
          <div class="sh-stat-lbl">Selesai</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico" style="background:<?= $overdueTL > 0 ? 'rgba(168,37,21,.10);color:#a82515' : 'rgba(100,100,100,.10);color:#64748b' ?>">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <div class="sh-stat-val" style="<?= $overdueTL > 0 ? 'color:#a82515' : '' ?>"><?= $overdueTL ?></div>
          <div class="sh-stat-lbl">Terlambat</div>
        </div>
      </div>
    </div>

    <!-- Progress TL -->
    <?php if ($totalTL > 0): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Progress Tindak Lanjut
      </div>
      <div class="sh-card-bd">
        <div class="sh-prog-row">
          <span class="sh-prog-pct"><?= $progressPct ?>%</span>
          <span class="sh-prog-meta"><?= $doneTL ?> / <?= $totalTL ?> selesai</span>
        </div>
        <div class="sh-prog-bar" role="progressbar"
             aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100">
          <div class="sh-prog-fill" style="width:<?= $progressPct ?>%"></div>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Agenda -->
    <?php if (!empty($meeting['description'])): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Agenda
      </div>
      <div class="sh-card-bd">
        <p class="sh-agenda"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
      </div>
    </div>
    <?php endif; ?>

    <!-- Dokumen -->
    <div class="sh-card">
      <div class="sh-card-hd">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Dokumen
      </div>
      <div class="sh-card-bd sh-doc-list">
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="sh-doc-btn sh-doc-primary">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>/export-docx" class="sh-doc-btn sh-doc-outline">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export DOCX
        </a>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button type="button" class="sh-doc-btn sh-doc-outline" id="btnKirimUndangan">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Kirim Undangan
        </button>
        <?php if (($meeting['status'] ?? '') === 'done'): ?>
        <button type="button" class="sh-doc-btn sh-doc-teal" id="btnKirimRingkasan">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /sidebar -->

  <!-- ── MAIN PANEL ──────────────────────────────────────── -->
  <div class="col-xl-9 col-lg-8">
    <div class="sh-main">

      <!-- Tab nav -->
      <div class="sh-tabs" role="tablist">
        <button type="button" class="sh-tab active" data-tab="tl"
                role="tab" aria-selected="true" aria-controls="sh-panel-tl" id="sh-tab-tl">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tindak Lanjut
          <span class="sh-pill"><?= $totalTL ?></span>
        </button>
        <button type="button" class="sh-tab" data-tab="peserta"
                role="tab" aria-selected="false" aria-controls="sh-panel-peserta" id="sh-tab-peserta">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta
          <span class="sh-pill"><?= $totalPeserta ?></span>
        </button>
      </div>

      <!-- Panel: Tindak Lanjut -->
      <div id="sh-panel-tl" class="sh-panel active" role="tabpanel" aria-labelledby="sh-tab-tl">
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <div class="sh-toolbar">
          <button type="button" class="sh-add-btn" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <p>Belum ada tindak lanjut.</p>
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
          <button type="button" class="sh-add-btn" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
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
                <th style="width:60px"></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $isOver = !empty($tl['due_date'])
                && $tl['due_date'] < $today
                && !in_array($tl['status'] ?? '', ['done','cancelled']);
              $pBadge = $prioBadge[$tl['priority'] ?? ''] ?? 'secondary';
              $pLbl   = $prioLabel[$tl['priority'] ?? ''] ?? ucfirst($tl['priority'] ?? '-');
              $sBadge = $tlsBadge[$tl['status'] ?? '']   ?? 'secondary';
              $sLbl   = $tlsLabel[$tl['status'] ?? '']   ?? ucfirst($tl['status'] ?? '-');
            ?>
              <tr class="<?= $isOver ? 'row-overdue' : '' ?>">
                <td>
                  <div class="sh-tl-desc"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($isOver): ?>
                  <span class="sh-badge sh-badge-red" style="font-size:10px;margin-top:.2rem;display:inline-flex">Terlambat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tl['assigned_name'])): ?>
                  <div class="sh-pic-row">
                    <?php $avBg = $avPalette[abs(crc32($tl['assigned_name'])) % count($avPalette)]; ?>
                    <span class="sh-av sh-av-sm" style="background:<?= $avBg ?>"><?= strtoupper(mb_substr($tl['assigned_name'], 0, 1)) ?></span>
                    <span><?= htmlspecialchars($tl['assigned_name']) ?></span>
                  </div>
                  <?php else: ?><span class="sh-text-muted">&mdash;</span><?php endif; ?>
                </td>
                <td>
                  <span style="font-size:12.5px;<?= $isOver ? 'color:#a82515;font-weight:700' : 'color:var(--text-muted)' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '&mdash;' ?>
                  </span>
                </td>
                <td><span class="sh-badge sh-badge-<?= $pBadge ?>"><?= $pLbl ?></span></td>
                <td><span class="sh-badge sh-badge-<?= $sBadge ?>"><?= $sLbl ?></span></td>
                <td>
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="sh-link">
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
      </div><!-- /sh-panel-tl -->

      <!-- Panel: Peserta -->
      <div id="sh-panel-peserta" class="sh-panel" role="tabpanel" aria-labelledby="sh-tab-peserta">
        <?php if (empty($participants)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <p>Belum ada peserta terdaftar.</p>
        </div>
        <?php else: ?>
        <div class="sh-peserta-grid">
          <?php foreach ($participants as $p):
            $psBadge = $pStatusBadge[$p['status'] ?? ''] ?? 'secondary';
            $psLbl   = $pStatusLabel[$p['status'] ?? ''] ?? ucfirst($p['status'] ?? '-');
            $avBg    = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
          ?>
          <div class="sh-peserta-card">
            <span class="sh-av" style="background:<?= $avBg ?>">
              <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
            </span>
            <div class="sh-peserta-info">
              <div class="sh-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['position'])): ?>
              <div class="sh-peserta-pos"><?= htmlspecialchars($p['position']) ?></div>
              <?php endif; ?>
            </div>
            <span class="sh-badge sh-badge-<?= $psBadge ?>"><?= $psLbl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div><!-- /sh-panel-peserta -->

    </div><!-- /sh-main -->
  </div><!-- /col -->

</div><!-- /row -->

<?php /* ================================================================
   MODALS
================================================================ */ ?>

<?php if (Auth::hasRole('admin', 'sekretaris')): ?>

<!-- Modal: Ubah Status -->
<div class="modal modal-blur fade" id="modalStatus" tabindex="-1"
     aria-labelledby="lblModalStatus" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title" id="lblModalStatus">Ubah Status Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <label class="form-label" for="selStatus">Status Baru</label>
          <select id="selStatus" name="status" class="form-select">
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

<!-- Modal: Tambah Tindak Lanjut -->
<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1"
     aria-labelledby="lblModalTL" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lblModalTL">Tambah Tindak Lanjut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required" for="tlDesc">Deskripsi Tugas</label>
          <textarea id="tlDesc" class="form-control" rows="3"
                    placeholder="Contoh: Buat laporan evaluasi Q2&hellip;"></textarea>
          <div class="invalid-feedback">Deskripsi wajib diisi.</div>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label" for="tlAssigned">Ditugaskan ke</label>
            <select id="tlAssigned" class="form-select">
              <option value="">— Pilih peserta —</option>
              <?php foreach ($participants as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label" for="tlDeadline">Deadline</label>
            <input type="date" id="tlDeadline" class="form-control" min="<?= $today ?>">
          </div>
        </div>
        <div>
          <label class="form-label">Prioritas</label>
          <div class="d-flex gap-3 flex-wrap">
            <?php foreach (['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'] as $v => $l): ?>
            <label class="form-check">
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
        <button type="button" class="btn btn-primary" id="btnSaveTL">Simpan</button>
      </div>
    </div>
  </div>
</div>

<?php endif; /* admin/sekretaris */ ?>

<?php if (Auth::hasRole('admin')): ?>
<!-- Modal: Hapus -->
<div class="modal modal-blur fade" id="modalHapus" tabindex="-1"
     aria-labelledby="lblModalHapus" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="sh-del-ico mb-3">
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mb-2" id="lblModalHapus">Hapus Kegiatan?</h5>
        <p class="text-muted mb-0" style="font-size:13px">
          Kegiatan <strong style="color:var(--brand)"><?= htmlspecialchars($meeting['title']) ?></strong>
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

  var BASE   = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  var MTG_ID = <?= (int)$meeting['id'] ?>;
  var CSRF   = <?= json_encode($csrfToken) ?>;

  /* ── Tab switch ── */
  document.querySelectorAll('.sh-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.sh-tab').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.sh-panel').forEach(function (p) { p.classList.remove('active'); });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      var panel = document.getElementById('sh-panel-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });

  /* ── Tambah TL ── */
  var btnSaveTL = document.getElementById('btnSaveTL');
  if (btnSaveTL) {
    btnSaveTL.addEventListener('click', function () {
      var desc     = document.getElementById('tlDesc');
      var assigned = document.getElementById('tlAssigned');
      var deadline = document.getElementById('tlDeadline');
      var prio     = document.querySelector('input[name="tlPriority"]:checked');
      desc.classList.remove('is-invalid');
      if (!desc.value.trim()) { desc.classList.add('is-invalid'); desc.focus(); return; }
      var orig = btnSaveTL.innerHTML;
      btnSaveTL.disabled = true;
      btnSaveTL.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';
      fetch(BASE + '/tindak-lanjut', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body   : JSON.stringify({
          meeting_id : MTG_ID,
          description: desc.value.trim(),
          assigned_to: assigned ? (assigned.value || null) : null,
          due_date   : deadline ? (deadline.value  || null) : null,
          priority   : prio ? prio.value : 'medium',
          _csrf      : CSRF
        })
      })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (d) {
        if (d.success) { window.location.reload(); }
        else { shToast(d.message || 'Gagal menyimpan.', 'error'); btnSaveTL.disabled = false; btnSaveTL.innerHTML = orig; }
      })
      .catch(function (err) { shToast('Terjadi kesalahan: ' + err.message, 'error'); btnSaveTL.disabled = false; btnSaveTL.innerHTML = orig; });
    });
  }

  /* ── Kirim Undangan ── */
  var btnInv = document.getElementById('btnKirimUndangan');
  if (btnInv) {
    btnInv.addEventListener('click', function () {
      if (!confirm('Kirim undangan email ke semua peserta?')) return;
      var orig = btnInv.innerHTML;
      btnInv.disabled = true;
      btnInv.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-invitations', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body   : JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { shToast(d.message || 'Undangan terkirim.', 'success'); })
      .catch(function ()  { shToast('Gagal mengirim undangan.', 'error'); })
      .finally(function () { btnInv.disabled = false; btnInv.innerHTML = orig; });
    });
  }

  /* ── Kirim Ringkasan ── */
  var btnSum = document.getElementById('btnKirimRingkasan');
  if (btnSum) {
    btnSum.addEventListener('click', function () {
      if (!confirm('Kirim ringkasan kegiatan ke semua peserta?')) return;
      var orig = btnSum.innerHTML;
      btnSum.disabled = true;
      btnSum.innerHTML = '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-summary', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body   : JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { shToast(d.message || 'Ringkasan terkirim.', 'success'); })
      .catch(function ()  { shToast('Gagal mengirim ringkasan.', 'error'); })
      .finally(function () { btnSum.disabled = false; btnSum.innerHTML = orig; });
    });
  }

  /* ── Toast helper ── */
  function shToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'sh-toast' + (type === 'error' ? ' sh-toast--err' : '');
    t.setAttribute('role', 'alert');
    t.setAttribute('aria-live', 'polite');
    t.innerHTML = msg + '<button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    setTimeout(function () {
      t.style.opacity = '0';
      setTimeout(function () { if (t.parentElement) t.remove(); }, 350);
    }, 4500);
  }

  /* ── Auto-dismiss flash toasts ── */
  ['shToast', 'shToastErr'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    setTimeout(function () { el.style.opacity = '0'; setTimeout(function () { if (el.parentElement) el.remove(); }, 350); }, 4500);
  });

}());
</script>

<style>
/* ================================================================
   SHOW.PHP — palet warna selaras tindak-lanjut/show.php
================================================================ */

/* ── Toast ───────────────────────────────────────────────── */
.sh-toast {
  position:fixed;top:1rem;right:1rem;z-index:1090;
  display:flex;align-items:center;gap:.45rem;
  background:#1e7a2e;color:#fff;
  padding:.55rem 1rem;border-radius:9px;
  font-size:13px;font-weight:600;
  box-shadow:0 4px 18px rgba(0,0,0,.18);
  transition:opacity .35s ease;max-width:340px;
}
.sh-toast--err { background:#a82515; }
.sh-toast-close { background:none;border:none;color:inherit;cursor:pointer;font-size:17px;line-height:1;padding:0 0 0 .4rem;opacity:.8;margin-left:auto; }
.sh-toast-close:hover { opacity:1; }

/* ── Hero ────────────────────────────────────────────────── */
.sh-hero {
  --mc: var(--brand);
  background: linear-gradient(135deg, var(--mc) 0%, #9B2020 60%, #A83218 100%);
  border-radius:14px;
  box-shadow:0 4px 20px rgba(123,28,28,.22);
  overflow:hidden;position:relative;
}
.sh-hero::after {
  content:'';position:absolute;top:-40px;right:-40px;
  width:180px;height:180px;border-radius:50%;
  background:rgba(201,168,76,.09);pointer-events:none;
}
.sh-hero-inner { padding:1.4rem 1.6rem 1rem; }

.sh-breadcrumb {
  display:flex;align-items:center;gap:.3rem;
  font-size:12px;color:rgba(255,255,255,.65);margin-bottom:.4rem;
}
.sh-breadcrumb a { color:rgba(255,255,255,.75);text-decoration:none; }
.sh-breadcrumb a:hover { color:#fff; }

.sh-hero-title {
  font-size:clamp(15px,2.5vw,22px);
  font-weight:800;color:#fff;margin:0;
  letter-spacing:-.02em;line-height:1.3;
}

.sh-hero-actions { display:flex;flex-wrap:wrap;gap:.4rem;align-items:flex-start;padding-top:1.1rem; }

.sh-btn-gold {
  display:inline-flex;align-items:center;gap:.35rem;
  font-size:13px;font-weight:700;
  background:rgba(201,168,76,.85);color:#fff;
  padding:.4rem .95rem;border-radius:8px;
  text-decoration:none;border:none;cursor:pointer;transition:background .18s;
}
.sh-btn-gold:hover { background:rgba(201,168,76,1);color:#fff; }
.sh-btn-ghost {
  background:rgba(255,255,255,.15);border:1.5px solid rgba(255,255,255,.3);
  color:#fff;font-size:13px;font-weight:600;border-radius:8px;
  display:inline-flex;align-items:center;gap:.35rem;padding:.4rem 1rem;
}
.sh-btn-ghost:hover { background:rgba(255,255,255,.25);color:#fff; }
.sh-btn-danger {
  background:rgba(192,57,43,.25);border:1.5px solid rgba(192,57,43,.5);
  color:#fca5a5;font-size:13px;font-weight:600;border-radius:8px;
  display:inline-flex;align-items:center;gap:.35rem;padding:.4rem 1rem;cursor:pointer;
}
.sh-btn-danger:hover { background:rgba(192,57,43,.45);color:#fff; }

/* Meta strip */
.sh-meta-strip {
  display:flex;align-items:center;flex-wrap:wrap;gap:.5rem;
  background:rgba(0,0,0,.18);padding:.55rem 1.6rem;
  font-size:13px;color:rgba(255,255,255,.82);backdrop-filter:blur(4px);
}
.sh-meta-item { display:flex;align-items:center;gap:.35rem; }
.sh-meta-link { color:rgba(255,255,255,.9);text-decoration:underline dotted;display:inline-flex;align-items:center;gap:.2rem; }
.sh-meta-link:hover { color:#fff; }

/* ── Badge ───────────────────────────────────────────────── */
.sh-badge {
  display:inline-flex;align-items:center;gap:.25rem;
  font-size:11.5px;font-weight:700;padding:.28em .7em;
  border-radius:20px;white-space:nowrap;
}
.sh-badge-red       { background:rgba(168,37,21,.10);  color:#a82515; }
.sh-badge-orange    { background:rgba(201,168,76,.15);  color:#7a5800; }
.sh-badge-green     { background:rgba(47,107,64,.10);   color:#1e7a2e; }
.sh-badge-blue      { background:rgba(32,107,196,.10);  color:#1557a0; }
.sh-badge-teal      { background:rgba(13,122,138,.10);  color:#0a6a78; }
.sh-badge-secondary { background:rgba(100,100,100,.10); color:#64748b; }
/* on dark hero */
.sh-hero .sh-badge-red       { background:rgba(252,165,165,.18);color:#fecaca; }
.sh-hero .sh-badge-orange    { background:rgba(253,211,77,.18); color:#fde68a; }
.sh-hero .sh-badge-green     { background:rgba(134,239,172,.18);color:#bbf7d0; }
.sh-hero .sh-badge-blue      { background:rgba(147,197,253,.18);color:#bfdbfe; }
.sh-hero .sh-badge-secondary { background:rgba(255,255,255,.15);color:rgba(255,255,255,.85); }

/* ── Stat grid ───────────────────────────────────────────── */
.sh-stats { display:grid;grid-template-columns:1fr 1fr;gap:.5rem; }
.sh-stat  { display:flex;align-items:center;gap:.6rem;background:var(--bg-card);border:1px solid var(--border-light);border-radius:10px;padding:.7rem .85rem; }
.sh-stat-ico { width:34px;height:34px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.sh-stat-val { font-size:18px;font-weight:800;color:var(--text-main);line-height:1.1; }
.sh-stat-lbl { font-size:10.5px;color:var(--text-muted);font-weight:600;margin-top:1px; }

/* ── Sidebar cards ───────────────────────────────────────── */
.sh-card    { background:var(--bg-card);border:1px solid var(--border-light);border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06); }
.sh-card-hd {
  display:flex;align-items:center;gap:.4rem;
  font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
  color:var(--brand);background:#faf4eb;
  padding:.6rem .9rem;border-bottom:1px solid var(--border-light);
}
.sh-card-bd { padding:.85rem .9rem; }

.sh-prog-row  { display:flex;justify-content:space-between;align-items:baseline;margin-bottom:.4rem; }
.sh-prog-pct  { font-size:14px;font-weight:800;color:var(--brand); }
.sh-prog-meta { font-size:11.5px;color:var(--text-muted); }
.sh-prog-bar  { height:7px;background:var(--border-light);border-radius:999px;overflow:hidden; }
.sh-prog-fill { height:100%;background:linear-gradient(90deg,var(--brand),#C9A84C);border-radius:999px;transition:width .5s ease; }

.sh-agenda { font-size:13px;color:var(--text-main);line-height:1.65;margin:0;white-space:pre-wrap;word-break:break-word; }

.sh-doc-list { display:flex;flex-direction:column;gap:.4rem; }
.sh-doc-btn  { display:inline-flex;align-items:center;justify-content:center;gap:.4rem;font-size:13px;font-weight:600;padding:.45rem .9rem;border-radius:8px;cursor:pointer;text-decoration:none;border:none;transition:background .16s,color .16s;width:100%; }
.sh-doc-primary       { background:var(--brand);color:#fff; }
.sh-doc-primary:hover { background:var(--brand-dark);color:#fff; }
.sh-doc-outline       { background:transparent;color:var(--text-main);border:1.5px solid var(--border); }
.sh-doc-outline:hover { background:#faf4eb;border-color:var(--brand);color:var(--brand); }
.sh-doc-teal          { background:transparent;color:#0d7a8a;border:1.5px solid rgba(13,122,138,.28); }
.sh-doc-teal:hover    { background:rgba(13,122,138,.06);border-color:#0d7a8a; }

/* ── Main card & tabs ────────────────────────────────────── */
.sh-main  { background:var(--bg-card);border:1px solid var(--border-light);border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.06); }
.sh-tabs  { display:flex;border-bottom:1px solid var(--border-light);background:#faf4eb; }
.sh-tab   {
  display:inline-flex;align-items:center;gap:.4rem;
  padding:.7rem 1.1rem;font-size:13px;font-weight:600;
  color:var(--text-muted);background:none;border:none;
  border-bottom:2.5px solid transparent;
  cursor:pointer;transition:color .14s,border-color .14s;
  white-space:nowrap;
}
.sh-tab:hover  { color:var(--brand); }
.sh-tab.active { color:var(--brand);border-bottom-color:var(--brand);background:#fff; }
.sh-pill {
  display:inline-flex;align-items:center;justify-content:center;
  background:var(--brand);color:#fff;
  font-size:10.5px;font-weight:700;padding:.1em .55em;
  border-radius:20px;min-width:18px;
}
.sh-tab:not(.active) .sh-pill { background:rgba(100,100,100,.15);color:var(--text-muted); }

.sh-panel { display:none; }
.sh-panel.active { display:block; }

.sh-toolbar { padding:.75rem 1rem;border-bottom:1px solid var(--border-light);background:#faf6ef; }
.sh-add-btn {
  display:inline-flex;align-items:center;gap:.35rem;
  font-size:13px;font-weight:700;
  background:var(--brand);color:#fff;
  padding:.42rem .95rem;border-radius:8px;
  border:none;cursor:pointer;transition:background .14s;
}
.sh-add-btn:hover { background:var(--brand-dark); }

/* Empty state */
.sh-empty {
  display:flex;flex-direction:column;align-items:center;
  padding:3rem 2rem;text-align:center;color:var(--text-muted);
}
.sh-empty-ico {
  width:60px;height:60px;background:#faf4eb;border-radius:50%;
  display:flex;align-items:center;justify-content:center;
  margin-bottom:.85rem;color:var(--brand);
}
.sh-empty p { font-size:13.5px;margin:0 0 1rem; }

/* TL table */
.sh-table { width:100%;border-collapse:collapse;font-size:13px; }
.sh-table thead th {
  padding:.6rem .9rem;text-align:left;
  font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
  color:var(--brand);background:#faf4eb;
  border-bottom:1px solid var(--border-light);
}
.sh-table tbody tr { border-bottom:1px solid var(--border-light);transition:background .12s; }
.sh-table tbody tr:last-child { border-bottom:none; }
.sh-table tbody tr:hover { background:#faf6ef; }
.sh-table tbody tr.row-overdue { background:rgba(168,37,21,.03); }
.sh-table tbody tr.row-overdue:hover { background:rgba(168,37,21,.06); }
.sh-table td { padding:.65rem .9rem;vertical-align:middle; }
.sh-tl-desc { font-size:13px;font-weight:500;color:var(--text-main); }
.sh-pic-row { display:flex;align-items:center;gap:.35rem; }
.sh-text-muted { color:var(--text-muted); }
.sh-link { display:inline-flex;align-items:center;gap:.25rem;font-size:12.5px;font-weight:600;color:var(--brand);text-decoration:none; }
.sh-link:hover { text-decoration:underline; }

/* Avatar */
.sh-av {
  display:inline-flex;align-items:center;justify-content:center;
  width:30px;height:30px;border-radius:50%;
  font-size:12px;font-weight:700;color:#fff;flex-shrink:0;
}
.sh-av-sm { width:24px;height:24px;font-size:10px; }

/* Peserta grid */
.sh-peserta-grid { display:flex;flex-direction:column; }
.sh-peserta-card {
  display:flex;align-items:center;gap:.6rem;
  padding:.7rem .9rem;border-bottom:1px solid var(--border-light);
  transition:background .12s;
}
.sh-peserta-card:last-child { border-bottom:none; }
.sh-peserta-card:hover { background:#faf6ef; }
.sh-peserta-info { flex:1;min-width:0; }
.sh-peserta-name { font-size:13px;font-weight:600;color:var(--text-main); }
.sh-peserta-pos  { font-size:11.5px;color:var(--text-muted);margin-top:1px; }

/* Delete modal icon */
.sh-del-ico {
  width:60px;height:60px;border-radius:50%;
  background:rgba(168,37,21,.08);color:#a82515;
  display:flex;align-items:center;justify-content:center;margin:0 auto;
}

/* Responsive */
@media (max-width:991.98px) {
  .sh-stats { grid-template-columns:repeat(4,1fr); }
}
@media (max-width:767.98px) {
  .sh-hero-inner { padding:1rem; }
  .sh-hero-title { font-size:15px; }
  .sh-meta-strip { padding:.5rem 1rem;font-size:12px; }
  .sh-stats { grid-template-columns:1fr 1fr; }
}
</style>
