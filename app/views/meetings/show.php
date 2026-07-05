<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusColor = [
  'scheduled' => 'mi-blue',
  'ongoing'   => 'mi-amber',
  'done'      => 'mi-green',
  'cancelled' => 'mi-red',
];
$statusIcon = [
  'scheduled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];
$pStatusColor = ['accepted'=>'mi-green','invited'=>'mi-blue','declined'=>'mi-red','attended'=>'mi-green','pending'=>'mi-amber'];
$pStatusLabel = ['accepted'=>'Diterima','invited'=>'Diundang','declined'=>'Ditolak','attended'=>'Hadir','pending'=>'Menunggu'];
$prioBadge    = ['high'=>'mi-red','medium'=>'mi-amber','low'=>'mi-green'];
$prioLabel    = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$tlsBadge     = ['pending'=>'mi-gray','in_progress'=>'mi-blue','done'=>'mi-green','cancelled'=>'mi-red'];
$tlsLabel     = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];

$canEdit          = $canEdit ?? false;
$participants     = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];
// BUG #4: $users bisa tidak di-pass dari controller lama; fallback ke $participants
$allUsersForTL    = $users ?? $participants;

$mStatus  = $meeting['status'] ?? 'scheduled';
$mLabel   = $statusLabel[$mStatus]  ?? ucfirst($mStatus);
$mColor   = $statusColor[$mStatus]  ?? 'mi-blue';
$mIcon    = $statusIcon[$mStatus]   ?? '';
$loc      = trim($meeting['location'] ?? '');
$isLink   = $loc && (str_starts_with($loc,'http://') || str_starts_with($loc,'https://'));

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => ($t['status']??'') === 'done'));
$inProgressTL = count(array_filter($tindakLanjutList, fn($t) => ($t['status']??'') === 'in_progress'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList, fn($t) =>
  !empty($t['due_date']) && $t['due_date'] < $today && !in_array($t['status']??'', ['done','cancelled'])
));
$progressPct  = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;

// BUG #8: Selalu ambil CSRF token fresh dari session
$csrfToken    = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$avPalette    = ['#7B1C1C','#9B2020','#8b5e00','#205375','#2d7a2d','#6b2fa0','#a05c00'];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// UX: Hitung durasi meeting
$durMins  = (int)round((strtotime($meeting['end_datetime']) - strtotime($meeting['start_datetime'])) / 60);
$durHours = intdiv($durMins, 60);
$durMin2  = $durMins % 60;
$durStr   = $durHours > 0 ? "{$durHours}j" . ($durMin2 > 0 ? " {$durMin2}m" : '') : "{$durMin2}m";

// UX: Peserta per status
$pByStatus = [];
foreach ($participants as $p) {
  $s = $p['status'] ?? 'invited';
  $pByStatus[$s] = ($pByStatus[$s] ?? 0) + 1;
}

// Progress bar colour: merah < 30%, amber 30-69%, hijau >= 70%
$progressColorClass = $progressPct >= 70 ? 'ms-prog-green' : ($progressPct >= 30 ? 'ms-prog-amber' : 'ms-prog-red');
?>

<!-- ══ FLASH TOAST ═══════════════════════════════════════════════════ -->
<?php if ($flashSuccess): ?>
<div class="mi-toast mi-toast-ok" id="miFlashToast" role="alert" aria-live="polite">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <span><?= htmlspecialchars($flashSuccess) ?></span>
  <button class="mi-toast-close" onclick="this.closest('.mi-toast').remove()" aria-label="Tutup">&times;</button>
</div>
<?php elseif ($flashError): ?>
<div class="mi-toast mi-toast-err" id="miFlashToast" role="alert" aria-live="assertive">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button class="mi-toast-close" onclick="this.closest('.mi-toast').remove()" aria-label="Tutup">&times;</button>
</div>
<?php endif; ?>

<!-- ══ HERO ══════════════════════════════════════════════════════════ -->
<div class="mi-hero mi-hero-detail">
  <div class="mi-hero-left">
    <a href="<?= $baseUrl ?>/meetings" class="mi-hero-back" title="Kembali ke Daftar Kegiatan" aria-label="Kembali">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="mi-hero-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <div class="mi-hero-eyebrow">Detail Kegiatan</div>
      <h1 class="mi-hero-title"><?= htmlspecialchars($meeting['title']) ?></h1>
      <div class="mi-hero-meta">
        <span class="mi-status <?= $mColor ?>"><?= $mIcon ?> <?= $mLabel ?></span>
        <?php if (!empty($meeting['dept_name'])): ?>
          <span class="mi-hero-chip">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($meeting['dept_name']) ?>
          </span>
        <?php endif; ?>
        <span class="mi-hero-chip">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <?= htmlspecialchars($meeting['creator_name'] ?? '—') ?>
        </span>
      </div>
    </div>
  </div>
  <!-- UX: Grouping tombol dengan divider -->
  <div class="mi-hero-actions">
    <?php if ($canEdit): ?>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="mi-btn-edit">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4z"/></svg>
        Edit
      </a>
    <?php endif; ?>
    <?php if (Auth::hasRole('admin','sekretaris')): ?>
      <button type="button" class="mi-btn-action" data-bs-toggle="modal" data-bs-target="#modalStatus">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Ubah Status
      </button>
    <?php endif; ?>
    <?php if (Auth::hasRole('admin')): ?>
      <span class="mi-hero-action-sep"></span>
      <button type="button" class="mi-btn-del-hero" data-bs-toggle="modal" data-bs-target="#modalHapus" aria-label="Hapus Kegiatan">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
        Hapus
      </button>
    <?php endif; ?>
  </div>
</div>

<!-- ══ INFO STRIP ════════════════════════════════════════════════════ -->
<div class="mi-info-strip">
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <span><strong>Mulai:</strong> <?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?> WIB</span>
  </div>
  <div class="mi-info-sep"></div>
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span><strong>Selesai:</strong> <?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?> WIB</span>
  </div>
  <!-- UX: Durasi kegiatan -->
  <div class="mi-info-sep"></div>
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2z"/><path d="M12 6v6l4 2"/></svg>
    <span><strong>Durasi:</strong> <?= $durStr ?></span>
  </div>
  <div class="mi-info-sep"></div>
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
    <?php if ($loc): ?>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="mi-loc-link">
          <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          Buka Tautan Rapat
        </a>
      <?php else: ?>
        <span><?= htmlspecialchars($loc) ?></span>
      <?php endif; ?>
    <?php else: ?>
      <span class="mi-null">Lokasi belum ditentukan</span>
    <?php endif; ?>
  </div>
</div>

<!-- ══ STAT CARDS ════════════════════════════════════════════════════ -->
<div class="mi-stats mi-stats-detail">
  <div class="mi-stat-card mi-stat-all">
    <div class="mi-stat-icon-wrap">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
      <div class="mi-stat-val"><?= $totalPeserta ?></div>
      <div class="mi-stat-lbl">Peserta</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-sched" style="cursor:default;">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $totalTL ?></div>
      <div class="mi-stat-lbl">Tindak Lanjut</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-done" style="cursor:default;">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $doneTL ?></div>
      <div class="mi-stat-lbl">Selesai</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-cancel" style="cursor:default;">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $overdueTL ?></div>
      <div class="mi-stat-lbl">Terlambat</div>
    </div>
  </div>
</div>

<!-- ══ MAIN GRID ═════════════════════════════════════════════════════ -->
<div class="ms-grid">

  <!-- ── SIDEBAR ───────────────────────────────────────────────────── -->
  <aside class="ms-sidebar">

    <!-- Progress TL -->
    <?php if ($totalTL > 0): ?>
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head">
        <span>Progres Tindak Lanjut</span>
        <strong><?= $progressPct ?>%</strong>
      </div>
      <div class="ms-progress-wrap">
        <div class="ms-progress-bar" role="progressbar"
             aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100"
             aria-label="Progres tindak lanjut <?= $progressPct ?>%">
          <!-- UX: warna bar berubah sesuai persentase -->
          <div class="ms-progress-fill <?= $progressColorClass ?>" style="width:<?= $progressPct ?>%"></div>
        </div>
        <div class="ms-progress-sub">
          <?= $doneTL ?> dari <?= $totalTL ?> selesai
          <?php if ($inProgressTL > 0): ?>&nbsp;&bull;&nbsp;<span class="ms-prog-ongoing"><?= $inProgressTL ?> sedang berjalan</span><?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Agenda -->
    <?php if (!empty($meeting['description'])): ?>
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head"><span>Agenda Kegiatan</span></div>
      <div class="ms-agenda"><?= nl2br(htmlspecialchars($meeting['description'])) ?></div>
    </div>
    <?php endif; ?>

    <!-- Dokumen -->
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head"><span>Dokumen</span></div>
      <div class="ms-doc-list">
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="ms-doc-btn ms-doc-btn--primary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>/export-docx" class="ms-doc-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export DOCX
        </a>
      </div>
    </div>

    <!-- UX: Pisahkan section Kirim ke bagian tersendiri -->
    <?php if (Auth::hasRole('admin','sekretaris')): ?>
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head"><span>Kirim Notifikasi</span></div>
      <div class="ms-doc-list">
        <!-- BUG #6: Tampilkan tombol undangan hanya jika ada peserta -->
        <?php if ($totalPeserta > 0): ?>
        <button type="button" class="ms-doc-btn" id="btnKirimUndangan">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          Kirim Undangan
          <span class="ms-doc-badge"><?= $totalPeserta ?></span>
        </button>
        <?php endif; ?>
        <?php if (($meeting['status']??'') === 'done'): ?>
        <button type="button" class="ms-doc-btn" id="btnKirimRingkasan">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php if ($totalPeserta === 0 && ($meeting['status']??'') !== 'done'): ?>
        <p class="ms-doc-empty-hint">Belum ada peserta untuk dikirim undangan.</p>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

  </aside>

  <!-- ── MAIN CONTENT ──────────────────────────────────────────────── -->
  <main class="ms-main">
    <div class="mi-panel">

      <!-- Tabs -->
      <!-- BUG #7: Tambahkan id untuk URL hash persistence -->
      <div class="ms-tabs" role="tablist" id="msTabs">
        <button class="ms-tab active" data-tab="tl" role="tab" aria-selected="true" aria-controls="ms-panel-tl" id="ms-tab-tl">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Tindak Lanjut <span class="ms-tab-count"><?= $totalTL ?></span>
          <?php if ($overdueTL > 0): ?><span class="ms-tab-alert"><?= $overdueTL ?></span><?php endif; ?>
        </button>
        <button class="ms-tab" data-tab="peserta" role="tab" aria-selected="false" aria-controls="ms-panel-peserta" id="ms-tab-peserta">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta <span class="ms-tab-count"><?= $totalPeserta ?></span>
        </button>
      </div>

      <!-- ── Tab: Tindak Lanjut ──────────────────────────────────── -->
      <div id="ms-panel-tl" class="ms-tab-panel active" role="tabpanel" aria-labelledby="ms-tab-tl">
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <div class="ms-tab-toolbar">
          <button type="button" class="mi-btn-create ms-btn-add-tl" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
          <?php if ($totalTL > 0): ?>
          <!-- UX: Quick filter bar -->
          <div class="ms-tl-filter" id="msTlFilter">
            <button class="ms-tl-ftab active" data-filter="all">Semua <span class="ms-tl-fcount"><?= $totalTL ?></span></button>
            <?php if ($totalTL - $doneTL - count(array_filter($tindakLanjutList, fn($t) => ($t['status']??'') === 'cancelled')) > 0): ?>
            <button class="ms-tl-ftab" data-filter="active">Aktif</button>
            <?php endif; ?>
            <?php if ($overdueTL > 0): ?>
            <button class="ms-tl-ftab ms-tl-ftab--alert" data-filter="overdue">Terlambat <span class="ms-tl-fcount"><?= $overdueTL ?></span></button>
            <?php endif; ?>
            <?php if ($doneTL > 0): ?>
            <button class="ms-tl-ftab" data-filter="done">Selesai <span class="ms-tl-fcount"><?= $doneTL ?></span></button>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="mi-empty">
          <div class="mi-empty-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <h3 class="mi-empty-title">Belum ada tindak lanjut</h3>
          <p class="mi-empty-desc">Tambahkan tindak lanjut untuk memantau progres kegiatan ini.</p>
          <?php if (Auth::hasRole('admin','sekretaris')): ?>
          <button type="button" class="mi-btn-create ms-btn-add-tl" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="mi-table-wrap">
          <table class="mi-table" aria-label="Daftar tindak lanjut" id="msTlTable">
            <thead>
              <tr>
                <th>Tugas</th>
                <th>PIC</th>
                <th>Deadline</th>
                <th class="ms-col-hide-sm">Prioritas</th>
                <th>Status</th>
                <th style="text-align:right">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($tindakLanjutList as $tl):
                $isOver = !empty($tl['due_date']) && $tl['due_date'] < $today
                          && !in_array($tl['status']??'',['done','cancelled']);
                $pBadge = $prioBadge[$tl['priority']??''] ?? 'mi-gray';
                $pLbl   = $prioLabel[$tl['priority']??'']  ?? ucfirst($tl['priority']??'—');
                $sBadge = $tlsBadge[$tl['status']??'']    ?? 'mi-gray';
                $sLbl   = $tlsLabel[$tl['status']??'']    ?? ucfirst($tl['status']??'—');
                $picName = $tl['assigned_name'] ?? '';
                $avBg    = $picName ? $avPalette[abs(crc32($picName)) % count($avPalette)] : '#8C8C8C';
                // Data attr untuk quick filter JS
                $tlStatus = $tl['status'] ?? 'pending';
              ?>
              <tr class="<?= $isOver ? 'ms-overdue-row' : '' ?> ms-tl-row"
                  data-tl-status="<?= htmlspecialchars($tlStatus) ?>"
                  data-tl-overdue="<?= $isOver ? '1' : '0' ?>">
                <td>
                  <div class="mi-title-name"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($isOver): ?>
                  <span class="ms-overdue-badge">
                    <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Terlambat
                  </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($picName): ?>
                  <div class="ms-pic">
                    <span class="ms-avatar" style="background:<?= $avBg ?>"><?= strtoupper(mb_substr($picName,0,1)) ?></span>
                    <span><?= htmlspecialchars($picName) ?></span>
                  </div>
                  <?php else: ?><span class="mi-null">—</span><?php endif; ?>
                </td>
                <td class="<?= $isOver ? 'ms-overdue-text' : '' ?>">
                  <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
                </td>
                <td class="ms-col-hide-sm"><span class="mi-status <?= $pBadge ?>"><?= $pLbl ?></span></td>
                <td><span class="mi-status <?= $sBadge ?>"><?= $sLbl ?></span></td>
                <td style="text-align:right">
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="mi-btn-detail">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Detail
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mi-tfoot">
          <span id="msTlCountLabel"><?= $totalTL ?> tindak lanjut &bull; <?= $doneTL ?> selesai
          <?php if ($overdueTL > 0): ?> &bull; <span class="ms-tfoot-overdue"><?= $overdueTL ?> terlambat</span><?php endif; ?>
          </span>
        </div>
        <?php endif; ?>
      </div>

      <!-- ── Tab: Peserta ───────────────────────────────────────── -->
      <div id="ms-panel-peserta" class="ms-tab-panel" role="tabpanel" aria-labelledby="ms-tab-peserta">
        <?php if (empty($participants)): ?>
        <div class="mi-empty">
          <div class="mi-empty-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <h3 class="mi-empty-title">Belum ada peserta</h3>
          <p class="mi-empty-desc">Peserta belum ditambahkan ke kegiatan ini.</p>
        </div>
        <?php else: ?>
        <div class="ms-peserta-grid">
          <?php foreach ($participants as $p):
            $psBadge = $pStatusColor[$p['status']??''] ?? 'mi-gray';
            $psLbl   = $pStatusLabel[$p['status']??''] ?? ucfirst($p['status']??'—');
            $avBg    = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
          ?>
          <div class="ms-peserta-card">
            <span class="ms-avatar ms-avatar-lg" style="background:<?= $avBg ?>">
              <?= strtoupper(mb_substr($p['name'],0,1)) ?>
            </span>
            <div class="ms-peserta-info">
              <div class="mi-title-name ms-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <div class="ms-peserta-pos"><?= !empty($p['position']) ? htmlspecialchars($p['position']) : '<span class="mi-null">Jabatan belum diset</span>' ?></div>
            </div>
            <span class="mi-status <?= $psBadge ?> ms-peserta-status"><?= $psLbl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- UX: Footer dengan breakdown status peserta -->
        <div class="mi-tfoot ms-peserta-tfoot">
          <span><?= $totalPeserta ?> peserta terdaftar</span>
          <div class="ms-peserta-stat-row">
            <?php foreach ($pByStatus as $st => $cnt): ?>
              <span class="mi-status <?= $pStatusColor[$st] ?? 'mi-gray' ?>" style="font-size:11px;">
                <?= $pStatusLabel[$st] ?? $st ?>: <?= $cnt ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- ══ MODAL: UBAH STATUS ════════════════════════════════════════════ -->
<?php if (Auth::hasRole('admin','sekretaris')): ?>
<div class="modal fade" id="modalStatus" tabindex="-1" aria-labelledby="modalStatusLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content mi-modal-del">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="mi-mc-header">
          <div class="mi-mc-header-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <h5 class="mi-mc-title" id="modalStatusLabel">Ubah Status Kegiatan</h5>
          <button type="button" class="mi-mc-close" data-bs-dismiss="modal" aria-label="Tutup">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div class="mi-mc-body" style="max-height:none;">
          <div class="mi-mc-field">
            <label class="mi-mc-lbl" for="selStatus">Status Baru</label>
            <select id="selStatus" name="status" class="mi-mc-select" style="width:100%;">
              <?php foreach ($statusLabel as $val => $lbl): ?>
                <option value="<?= $val ?>" <?= ($meeting['status']??'') === $val ? 'selected' : '' ?>>
                  <?= htmlspecialchars($lbl) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="mi-modal-del-foot">
          <button type="button" class="mi-btn-cancel" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="mi-mc-btn-submit" style="padding:.45rem 1.1rem;">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ MODAL: TAMBAH TINDAK LANJUT ══════════════════════════════════ -->
<div class="modal fade" id="modalTambahTL" tabindex="-1" aria-labelledby="modalTLLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content mi-modal-create">
      <div class="mi-mc-header">
        <div class="mi-mc-header-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <h5 class="mi-mc-title" id="modalTLLabel">Tambah Tindak Lanjut</h5>
        <button type="button" class="mi-mc-close" data-bs-dismiss="modal" aria-label="Tutup">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="mi-mc-body">
        <div class="mi-mc-section">
          <div class="mi-mc-section-label">Deskripsi Tugas <span class="mi-req-star">*</span></div>
          <div class="mi-mc-field">
            <!-- UX: character counter -->
            <textarea id="tlDesc" class="mi-mc-textarea" rows="3" maxlength="500"
                      placeholder="Contoh: Susun laporan evaluasi semester…"></textarea>
            <div class="ms-char-counter"><span id="tlDescCount">0</span>/500</div>
            <div class="invalid-feedback" id="tlDescErr">Deskripsi wajib diisi.</div>
          </div>
        </div>
        <div class="mi-mc-section">
          <div class="mi-mc-section-label">Penugasan &amp; Tenggat</div>
          <div class="mi-mc-grid">
            <div class="mi-mc-field">
              <label class="mi-mc-lbl" for="tlAssigned">Ditugaskan ke</label>
              <!-- BUG #3: Gunakan $allUsersForTL (semua user aktif), bukan hanya peserta -->
              <select id="tlAssigned" class="mi-mc-select">
                <option value="">— Pilih penanggungjawab —</option>
                <?php foreach ($allUsersForTL as $u): ?>
                  <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mi-mc-field">
              <label class="mi-mc-lbl" for="tlDeadline">Deadline</label>
              <!-- BUG #5: min tidak boleh hardcode $today karena timezone server bisa berbeda -->
              <input type="date" id="tlDeadline" class="mi-mc-input">
            </div>
          </div>
        </div>
        <div class="mi-mc-section">
          <div class="mi-mc-section-label">Prioritas</div>
          <div style="display:flex;gap:1rem;flex-wrap:wrap;">
            <?php foreach (['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'] as $v => $l): ?>
            <label class="mi-mc-pcheck">
              <input type="radio" name="tlPriority" value="<?= $v ?>" <?= $v==='medium' ? 'checked' : '' ?>>
              <span class="mi-mc-pname"><?= $l ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="mi-mc-footer">
        <button type="button" class="mi-mc-btn-cancel" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="mi-mc-btn-submit" id="btnSaveTL">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Simpan
        </button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MODAL: HAPUS ══════════════════════════════════════════════════ -->
<?php if (Auth::hasRole('admin')): ?>
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content mi-modal-del">
      <div class="mi-modal-del-body">
        <div class="mi-del-icon-wrap">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
        </div>
        <h5 class="mi-del-title" id="modalHapusLabel">Hapus Kegiatan?</h5>
        <p class="mi-del-desc">
          Kegiatan <strong class="mi-del-name"><?= htmlspecialchars($meeting['title']) ?></strong>
          akan dihapus permanen beserta seluruh peserta, notulen, dan tindak lanjut terkait.
        </p>
      </div>
      <div class="mi-modal-del-foot">
        <button type="button" class="mi-btn-cancel" data-bs-dismiss="modal">Batal</button>
        <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/delete" style="display:inline;">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="mi-btn-confirm-del">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ SCRIPT ════════════════════════════════════════════════════════ -->
<script>
(function () {
  'use strict';
  var BASE   = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  var MTG_ID = <?= (int)$meeting['id'] ?>;
  // BUG #8: CSRF token selalu diambil fresh dari PHP
  var CSRF   = <?= json_encode($csrfToken) ?>;

  /* ── Auto-dismiss toast ── */
  var toast = document.getElementById('miFlashToast');
  if (toast) {
    setTimeout(function () { toast.style.opacity = '0'; }, 4000);
    setTimeout(function () { if (toast.parentElement) toast.remove(); }, 4500);
  }

  /* ── Tabs + URL hash persistence (BUG #7) ── */
  function activateTab(tabName) {
    document.querySelectorAll('.ms-tab').forEach(function (b) {
      var active = b.dataset.tab === tabName;
      b.classList.toggle('active', active);
      b.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    document.querySelectorAll('.ms-tab-panel').forEach(function (p) {
      p.classList.remove('active');
    });
    var panel = document.getElementById('ms-panel-' + tabName);
    if (panel) panel.classList.add('active');
  }

  document.querySelectorAll('.ms-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var tab = btn.dataset.tab;
      activateTab(tab);
      history.replaceState(null, '', '#tab-' + tab);
    });
  });

  // Restore tab dari URL hash
  var hash = window.location.hash;
  if (hash === '#tab-peserta') activateTab('peserta');

  /* ── Quick filter tindak lanjut ── */
  var tlFilter = document.getElementById('msTlFilter');
  if (tlFilter) {
    tlFilter.addEventListener('click', function (e) {
      var btn = e.target.closest('.ms-tl-ftab');
      if (!btn) return;
      tlFilter.querySelectorAll('.ms-tl-ftab').forEach(function (b) { b.classList.remove('active'); });
      btn.classList.add('active');
      var f = btn.dataset.filter;
      var rows = document.querySelectorAll('.ms-tl-row');
      var visible = 0;
      rows.forEach(function (row) {
        var show = true;
        if (f === 'active')  show = row.dataset.tlStatus !== 'done' && row.dataset.tlStatus !== 'cancelled';
        if (f === 'overdue') show = row.dataset.tlOverdue === '1';
        if (f === 'done')    show = row.dataset.tlStatus === 'done';
        row.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      var lbl = document.getElementById('msTlCountLabel');
      if (lbl) lbl.textContent = visible + ' tindak lanjut ditampilkan';
    });
  }

  /* ── Character counter textarea deskripsi (BUG modal UX) ── */
  var tlDesc = document.getElementById('tlDesc');
  var tlDescCount = document.getElementById('tlDescCount');
  if (tlDesc && tlDescCount) {
    tlDesc.addEventListener('input', function () {
      tlDescCount.textContent = tlDesc.value.length;
    });
  }

  /* ── BUG #5: Set min deadline dari tanggal sekarang di browser ── */
  var tlDeadline = document.getElementById('tlDeadline');
  if (tlDeadline) {
    var now = new Date();
    var yyyy = now.getFullYear();
    var mm   = String(now.getMonth() + 1).padStart(2, '0');
    var dd   = String(now.getDate()).padStart(2, '0');
    tlDeadline.min = yyyy + '-' + mm + '-' + dd;
  }

  /* ── Save tindak lanjut ── */
  var btnSaveTL = document.getElementById('btnSaveTL');
  var modalTLEl = document.getElementById('modalTambahTL');
  if (btnSaveTL) {
    btnSaveTL.addEventListener('click', function () {
      var desc     = document.getElementById('tlDesc');
      var assigned = document.getElementById('tlAssigned');
      var deadline = document.getElementById('tlDeadline');
      var prio     = document.querySelector('input[name="tlPriority"]:checked');
      var errEl    = document.getElementById('tlDescErr');

      desc.classList.remove('is-invalid');
      if (errEl) errEl.style.display = 'none';
      if (!desc.value.trim()) {
        desc.classList.add('is-invalid');
        if (errEl) errEl.style.display = 'block';
        desc.focus();
        return;
      }
      var orig = btnSaveTL.innerHTML;
      btnSaveTL.disabled = true;
      btnSaveTL.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';
      fetch(BASE + '/tindak-lanjut', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
          meeting_id: MTG_ID,
          description: desc.value.trim(),
          assigned_to: assigned ? (assigned.value || null) : null,
          due_date: deadline ? (deadline.value || null) : null,
          priority: prio ? prio.value : 'medium',
          _csrf: CSRF
        })
      })
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function (d) {
        if (d.success) {
          // BUG #2: Reset modal sebelum reload
          if (tlDesc) { tlDesc.value = ''; if (tlDescCount) tlDescCount.textContent = '0'; }
          if (assigned) assigned.value = '';
          if (deadline) deadline.value = '';
          var defPrio = document.querySelector('input[name="tlPriority"][value="medium"]');
          if (defPrio) defPrio.checked = true;
          window.location.reload();
        } else {
          miToast(d.message || 'Gagal menyimpan.', 'err');
          btnSaveTL.disabled = false;
          btnSaveTL.innerHTML = orig;
        }
      })
      .catch(function (e) {
        miToast('Kesalahan: ' + e.message, 'err');
        btnSaveTL.disabled = false;
        btnSaveTL.innerHTML = orig;
      });
    });

    // BUG #2: Reset form saat modal ditutup
    if (modalTLEl) {
      modalTLEl.addEventListener('hidden.bs.modal', function () {
        var desc = document.getElementById('tlDesc');
        var assigned = document.getElementById('tlAssigned');
        var deadline = document.getElementById('tlDeadline');
        if (desc) { desc.value = ''; desc.classList.remove('is-invalid'); if (tlDescCount) tlDescCount.textContent = '0'; }
        if (assigned) assigned.value = '';
        if (deadline) deadline.value = '';
        var defPrio = document.querySelector('input[name="tlPriority"][value="medium"]');
        if (defPrio) defPrio.checked = true;
        btnSaveTL.disabled = false;
      });
    }
  }

  /* ── Kirim Undangan ── */
  var btnInv = document.getElementById('btnKirimUndangan');
  if (btnInv) {
    btnInv.addEventListener('click', function () {
      if (!confirm('Kirim undangan email ke semua peserta?')) return;
      var orig = btnInv.innerHTML;
      btnInv.disabled = true;
      btnInv.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-invitations', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { miToast(d.message || 'Undangan terkirim.', 'ok'); })
      .catch(function () { miToast('Gagal mengirim undangan.', 'err'); })
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
      btnSum.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      fetch(BASE + '/meetings/' + MTG_ID + '/send-summary', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ _csrf: CSRF })
      })
      .then(function (r) { return r.json(); })
      .then(function (d) { miToast(d.message || 'Ringkasan terkirim.', 'ok'); })
      .catch(function () { miToast('Gagal mengirim ringkasan.', 'err'); })
      .finally(function () { btnSum.disabled = false; btnSum.innerHTML = orig; });
    });
  }

  /* ── BUG #1: miToast dengan ikon SVG ── */
  function miToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'mi-toast mi-toast-' + (type === 'ok' ? 'ok' : 'err');
    t.setAttribute('role', 'alert');
    t.setAttribute('aria-live', type === 'ok' ? 'polite' : 'assertive');
    var icon = type === 'ok'
      ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
    t.innerHTML = icon + '<span>' + msg + '</span><button class="mi-toast-close" onclick="this.closest(\'.mi-toast\').remove()" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    setTimeout(function () { t.style.opacity = '0'; }, 4000);
    setTimeout(function () { if (t.parentElement) t.remove(); }, 4500);
  }
}());
</script>

<!-- ══ STYLES ════════════════════════════════════════════════════════ -->
<style>
/* ── Variabel Palet ── */
:root {
  --mi-primary:      #7B1C1C;
  --mi-primary-dark: #5e1616;
  --mi-primary-deep: #9B2020;
  --mi-primary-grad: linear-gradient(135deg,#7B1C1C 0%,#9B2020 55%,#A83218 100%);
  --mi-gold:         #C9A84C;
  --mi-gold-dark:    #b8922a;
  --border-light:    #ede8e0;
  --border:          #ddd;
  --text-main:       #2c1a1a;
  --text-muted:      #8c7a6b;
  --bg-warm:         #faf6ef;
  --bg-warm-hover:   #faf4eb;
}

/* ── Toast ── */
.mi-toast {
  position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
  display: flex; align-items: center; gap: .6rem;
  padding: .7rem 1rem; border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.14);
  animation: miSlideIn .25s ease; max-width: 360px;
  transition: opacity .4s;
}
@keyframes miSlideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
.mi-toast-ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.mi-toast-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.mi-toast-close { background: none; border: none; font-size: 16px; cursor: pointer; margin-left: .25rem; opacity: .6; line-height: 1; padding: 0; }
.mi-toast-close:hover { opacity: 1; }

/* ── Hero ── */
.mi-hero {
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
  background: var(--mi-primary-grad);
  padding: 1.25rem 1.5rem; border-radius: 14px; margin-bottom: 1rem;
  box-shadow: 0 4px 20px rgba(123,28,28,.22); position: relative; overflow: hidden;
}
.mi-hero::after {
  content: ''; position: absolute; top: -40px; right: -40px;
  width: 180px; height: 180px; border-radius: 50%;
  background: rgba(201,168,76,.08); pointer-events: none;
}
.mi-hero-left { display: flex; align-items: center; gap: .75rem; }
.mi-hero-back {
  display: flex; align-items: center; justify-content: center;
  width: 34px; height: 34px; border-radius: 9px;
  background: rgba(255,255,255,.15); color: #fff;
  text-decoration: none; flex-shrink: 0; transition: background .15s;
}
.mi-hero-back:hover { background: rgba(255,255,255,.25); color: #fff; }
.mi-hero-icon {
  width: 38px; height: 38px; border-radius: 10px;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #fff;
}
.mi-hero-eyebrow {
  font-size: 10.5px; font-weight: 700; text-transform: uppercase;
  letter-spacing: .12em; color: rgba(255,255,255,.65); margin-bottom: .25rem;
}
.mi-hero-title {
  font-size: clamp(1.05rem, 2.2vw, 1.45rem); font-weight: 800;
  color: #fff; margin: 0; line-height: 1.25;
}
.mi-hero-meta { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: .5rem; }
.mi-hero-chip {
  display: inline-flex; align-items: center; gap: .3rem;
  padding: .3rem .65rem; border-radius: 999px;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.12);
  color: rgba(255,255,255,.88); font-size: 11.5px; font-weight: 600;
}
.mi-hero-actions { display: flex; gap: .6rem; flex-wrap: wrap; align-items: flex-start; }
.mi-hero-action-sep {
  width: 1px; height: 28px; background: rgba(255,255,255,.2);
  align-self: center; flex-shrink: 0;
}
.mi-btn-edit {
  display: inline-flex; align-items: center; gap: .4rem;
  background: var(--mi-gold); border: 1px solid rgba(0,0,0,.1);
  color: #3d0a0a; font-size: 13px; font-weight: 700;
  padding: .5rem 1rem; border-radius: 9px;
  cursor: pointer; text-decoration: none; transition: all .18s; white-space: nowrap;
}
.mi-btn-edit:hover { background: var(--mi-gold-dark); color: #fff; }
.mi-btn-action {
  display: inline-flex; align-items: center; gap: .4rem;
  background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
  color: #fff; font-size: 13px; font-weight: 600;
  padding: .5rem 1rem; border-radius: 9px; cursor: pointer; transition: all .18s;
}
.mi-btn-action:hover { background: rgba(255,255,255,.22); }
.mi-btn-del-hero {
  display: inline-flex; align-items: center; gap: .4rem;
  background: rgba(192,57,43,.18); border: 1px solid rgba(255,180,170,.2);
  color: #FFD4CC; font-size: 13px; font-weight: 600;
  padding: .5rem 1rem; border-radius: 9px; cursor: pointer; transition: all .18s;
}
.mi-btn-del-hero:hover { background: rgba(192,57,43,.3); color: #fff; }

/* ── Info strip ── */
.mi-info-strip {
  display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
  background: #fff; border: 1px solid var(--border-light); border-radius: 12px;
  padding: .7rem 1.1rem; margin-bottom: 1rem; font-size: 13px;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.mi-info-item { display: flex; align-items: center; gap: .4rem; color: var(--text-main); }
.mi-info-item svg { color: var(--text-muted); flex-shrink: 0; }
.mi-info-sep { width: 1px; height: 16px; background: var(--border-light); flex-shrink: 0; }
.mi-loc-link {
  display: inline-flex; align-items: center; gap: .3rem;
  color: var(--mi-primary); font-weight: 600; text-decoration: underline;
  text-underline-offset: 2px;
}
.mi-loc-link:hover { color: var(--mi-primary-deep); }

/* ── Stat cards ── */
.mi-stats {
  display: grid;
  grid-template-columns: 1.4fr repeat(4, 1fr);
  gap: .75rem; margin-bottom: 1.25rem;
}
.mi-stats-detail { grid-template-columns: 1.4fr repeat(3, 1fr); }
.mi-stat-card {
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 12px; padding: .9rem 1.1rem;
  display: flex; align-items: center; gap: .7rem;
  cursor: default; transition: all .18s;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.mi-stat-all  { background: var(--mi-primary); border-color: var(--mi-primary); }
.mi-stat-all .mi-stat-val { color: #fff; font-size: 24px; }
.mi-stat-all .mi-stat-lbl { color: rgba(255,255,255,.75); }
.mi-stat-icon-wrap {
  width: 36px; height: 36px; border-radius: 9px;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center; color: #fff;
}
.mi-stat-val { font-size: 22px; font-weight: 800; color: var(--text-main); line-height: 1; }
.mi-stat-lbl { font-size: 11.5px; font-weight: 500; color: var(--text-muted); margin-top: 2px; }
.mi-stat-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 1px; }
.mi-stat-sched  .mi-stat-dot { background: #3b82f6; }
.mi-stat-done   .mi-stat-dot { background: #22c55e; }
.mi-stat-cancel .mi-stat-dot { background: #ef4444; }

/* ── Panel ── */
.mi-panel {
  background: #fff; border: 1px solid var(--border-light);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,.05);
}

/* ── Layout grid ── */
.ms-grid { display: grid; grid-template-columns: 280px 1fr; gap: 1.1rem; align-items: start; }

/* ── Sidebar ── */
.ms-sidebar { display: flex; flex-direction: column; gap: 1rem; }
.ms-panel { overflow: hidden; }
.ms-panel-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: .85rem 1.1rem; background: var(--bg-warm);
  border-bottom: 1px solid var(--border-light);
  font-size: 11px; font-weight: 700; color: var(--text-muted);
  text-transform: uppercase; letter-spacing: .07em;
}
.ms-panel-head strong {
  font-size: 15px; text-transform: none; letter-spacing: 0; color: var(--mi-primary);
}
/* ── Progress ── */
.ms-progress-wrap { padding: .75rem 1.1rem .9rem; }
.ms-progress-bar {
  height: 10px; background: #f0ece5; border-radius: 999px; overflow: hidden;
}
.ms-progress-fill {
  height: 100%; border-radius: 999px; transition: width .6s ease;
}
/* UX: Progress warna dinamis */
.ms-prog-green { background: linear-gradient(90deg,#16a34a,#22c55e); }
.ms-prog-amber { background: linear-gradient(90deg,#d97706,#f59e0b); }
.ms-prog-red   { background: linear-gradient(90deg,var(--mi-primary),#e05c5c); }
.ms-progress-sub { font-size: 11.5px; color: var(--text-muted); margin-top: .45rem; }
.ms-prog-ongoing { color: #1d4ed8; font-weight: 600; }
/* ── Agenda ── */
.ms-agenda {
  padding: .75rem 1.1rem 1rem; font-size: 13.5px;
  line-height: 1.7; color: var(--text-main);
}
/* ── Doc list ── */
.ms-doc-list { display: flex; flex-direction: column; gap: .5rem; padding: .75rem 1rem 1rem; }
.ms-doc-btn {
  min-height: 42px; border-radius: 10px;
  border: 1.5px solid var(--border-light); background: #fff;
  color: var(--text-main); font-weight: 600; font-size: 13px;
  text-align: center; padding: .65rem 1rem;
  text-decoration: none; cursor: pointer; position: relative;
  display: inline-flex; align-items: center; justify-content: center; gap: .4rem;
  transition: all .15s;
}
.ms-doc-btn:hover { background: var(--bg-warm-hover); border-color: rgba(123,28,28,.25); color: var(--mi-primary); }
.ms-doc-btn--primary { background: var(--mi-primary); color: #fff; border-color: var(--mi-primary); }
.ms-doc-btn--primary:hover { background: var(--mi-primary-dark); color: #fff; }
.ms-doc-badge {
  position: absolute; top: -6px; right: -6px;
  background: var(--mi-gold); color: #3d0a0a;
  font-size: 10px; font-weight: 800; min-width: 18px; height: 18px;
  border-radius: 999px; display: flex; align-items: center; justify-content: center;
  border: 2px solid #fff;
}
.ms-doc-empty-hint {
  font-size: 12px; color: var(--text-muted);
  text-align: center; margin: .5rem 0 0; padding: 0 .5rem;
  line-height: 1.5;
}

/* ── Tabs ── */
.ms-tabs {
  display: flex; gap: .25rem; padding: .85rem .95rem 0;
  background: var(--bg-warm); border-bottom: 1px solid var(--border-light);
}
.ms-tab {
  background: transparent; border: 0;
  border-top-left-radius: 10px; border-top-right-radius: 10px;
  padding: .75rem 1rem; font-size: 13px; font-weight: 700;
  color: var(--text-muted); cursor: pointer; transition: all .15s;
  display: inline-flex; align-items: center; gap: .35rem;
  position: relative;
}
.ms-tab:hover { color: var(--mi-primary); background: rgba(123,28,28,.04); }
.ms-tab.active {
  background: #fff; color: var(--mi-primary);
  box-shadow: 0 -1px 0 #fff, 0 0 0 1px var(--border-light);
}
.ms-tab-count {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 20px; padding: .1rem .4rem; border-radius: 999px;
  background: rgba(123,28,28,.08); font-size: 11px; color: var(--mi-primary);
}
/* UX: Alert badge di tab TL jika ada yang overdue */
.ms-tab-alert {
  position: absolute; top: 6px; right: 6px;
  background: #ef4444; color: #fff;
  font-size: 9px; font-weight: 800; min-width: 16px; height: 16px;
  border-radius: 999px; display: flex; align-items: center; justify-content: center;
  border: 2px solid var(--bg-warm);
}
.ms-tab-panel { display: none; }
.ms-tab-panel.active { display: block; }
.ms-tab-toolbar {
  display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
  padding: .85rem 1.1rem;
  border-bottom: 1px solid var(--border-light);
}
/* ── Quick Filter TL ── */
.ms-tl-filter { display: flex; gap: .35rem; flex-wrap: wrap; }
.ms-tl-ftab {
  border: 1.5px solid var(--border-light); background: #fff;
  color: var(--text-muted); font-size: 12px; font-weight: 600;
  padding: .28rem .7rem; border-radius: 999px;
  cursor: pointer; transition: all .15s; display: inline-flex; align-items: center; gap: .3rem;
}
.ms-tl-ftab:hover { border-color: var(--mi-primary); color: var(--mi-primary); }
.ms-tl-ftab.active { background: var(--mi-primary); color: #fff; border-color: var(--mi-primary); }
.ms-tl-ftab--alert { border-color: rgba(239,68,68,.4); color: #b91c1c; }
.ms-tl-ftab--alert.active { background: #ef4444; border-color: #ef4444; }
.ms-tl-fcount {
  background: rgba(255,255,255,.25); color: inherit;
  font-size: 10px; min-width: 16px; height: 16px; border-radius: 999px;
  display: inline-flex; align-items: center; justify-content: center; padding: 0 3px;
}
.ms-tl-ftab:not(.active) .ms-tl-fcount { background: rgba(123,28,28,.08); }
/* ── Tombol tambah TL ── */
.ms-btn-add-tl {
  background: var(--mi-primary) !important;
  color: #fff !important;
  box-shadow: 0 3px 12px rgba(123,28,28,.25) !important;
}
.ms-btn-add-tl:hover {
  background: var(--mi-primary-dark) !important;
  transform: translateY(-1px);
  box-shadow: 0 5px 16px rgba(123,28,28,.32) !important;
}
/* ── Table ── */
.mi-table-wrap { overflow-x: auto; }
.mi-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.mi-table thead th {
  background: var(--bg-warm); border-bottom: 2px solid var(--border);
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-muted);
  padding: .65rem 1.1rem; white-space: nowrap;
}
.mi-table tbody td {
  padding: .78rem 1.1rem; vertical-align: middle;
  border-bottom: 1px solid var(--border-light);
}
.mi-table tbody tr:last-child td { border-bottom: none; }
.mi-table tbody tr:hover { background: var(--bg-warm-hover); }
.mi-title-name { font-size: 13.5px; font-weight: 600; color: var(--text-main); line-height: 1.35; }
.mi-null { color: var(--text-muted); font-size: 13px; }
/* Status badges */
.mi-status {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 11.5px; font-weight: 700; padding: .28em .7em;
  border-radius: 20px; white-space: nowrap; letter-spacing: .02em;
}
.mi-blue   { background: rgba(59,130,246,.10);  color: #1d4ed8; }
.mi-amber  { background: rgba(245,158,11,.12);  color: #92400e; }
.mi-green  { background: rgba(34,197,94,.10);   color: #15803d; }
.mi-red    { background: rgba(239,68,68,.10);   color: #b91c1c; }
.mi-gray   { background: rgba(0,0,0,.06);       color: #5a5a5a; }
/* Action button */
.mi-btn-detail {
  display: inline-flex; align-items: center; gap: .3rem;
  border: 1.5px solid var(--mi-primary); color: var(--mi-primary);
  background: transparent; font-size: 12px; font-weight: 600;
  padding: .28rem .65rem; border-radius: 7px;
  text-decoration: none; transition: all .15s;
}
.mi-btn-detail:hover { background: var(--mi-primary); color: #fff; box-shadow: 0 2px 8px rgba(123,28,28,.20); }
/* Table footer */
.mi-tfoot {
  display: flex; align-items: center; justify-content: space-between;
  padding: .6rem 1.1rem; background: var(--bg-warm);
  border-top: 1px solid var(--border-light);
  font-size: 12px; color: var(--text-muted);
}
.ms-tfoot-overdue { color: #a82515; font-weight: 700; }
/* Overdue */
.ms-overdue-row td { background: rgba(192,57,43,.025); }
.ms-overdue-badge {
  display: inline-flex; align-items: center; gap: .25rem;
  margin-top: .3rem; padding: .18rem .5rem;
  border-radius: 999px; background: rgba(192,57,43,.10);
  color: #a82515; font-size: 11px; font-weight: 700;
}
.ms-overdue-text { color: #a82515; font-weight: 700; font-size: 13px; }
/* Avatar */
.ms-avatar {
  width: 30px; height: 30px; border-radius: 50%;
  display: inline-flex; align-items: center; justify-content: center;
  color: #fff; font-size: 12px; font-weight: 800; flex-shrink: 0;
}
.ms-avatar-lg { width: 40px; height: 40px; font-size: 15px; }
.ms-pic { display: flex; align-items: center; gap: .55rem; }
/* ── Peserta grid ── */
.ms-peserta-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(min(260px, 100%), 1fr));
  gap: .65rem; padding: 1rem;
}
.ms-peserta-card {
  display: flex; align-items: center; gap: .65rem;
  padding: .75rem 1rem; border-radius: 10px;
  border: 1px solid var(--border-light); background: #fff;
  transition: all .15s;
}
.ms-peserta-card:hover { background: var(--bg-warm); border-color: rgba(123,28,28,.18); }
.ms-peserta-info { flex: 1; min-width: 0; }
.ms-peserta-name { font-size: 13px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ms-peserta-pos  { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.ms-peserta-status { flex-shrink: 0; }
/* UX: footer peserta dengan breakdown status */
.ms-peserta-tfoot { flex-wrap: wrap; gap: .5rem; }
.ms-peserta-stat-row { display: flex; flex-wrap: wrap; gap: .35rem; }
/* ── Empty state ── */
.mi-empty {
  display: flex; flex-direction: column; align-items: center;
  text-align: center; padding: 3.5rem 2rem;
}
.mi-empty-icon {
  width: 72px; height: 72px; border-radius: 50%;
  background: rgba(123,28,28,.07);
  display: flex; align-items: center; justify-content: center;
  color: var(--mi-primary); margin-bottom: 1.25rem;
}
.mi-empty-title { font-size: 17px; font-weight: 700; color: var(--text-main); margin-bottom: .4rem; }
.mi-empty-desc  { font-size: 13px; color: var(--text-muted); max-width: 30ch; margin-bottom: 1.25rem; }
/* ── Modal hapus ── */
.mi-modal-del { border-radius: 14px; border: none; overflow: hidden; }
.mi-modal-del-body { padding: 2rem 1.5rem 1rem; text-align: center; }
.mi-del-icon-wrap {
  width: 58px; height: 58px; border-radius: 50%;
  background: rgba(192,57,43,.10);
  display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1rem; color: #a82515;
}
.mi-del-title { font-size: 16px; font-weight: 700; color: var(--text-main); margin-bottom: .5rem; }
.mi-del-desc  { font-size: 13px; color: var(--text-muted); line-height: 1.55; }
.mi-del-name  { color: #a82515; }
.mi-modal-del-foot {
  display: flex; justify-content: center; gap: .75rem;
  padding: 1rem 1.5rem 1.5rem;
}
.mi-btn-cancel {
  padding: .45rem 1.1rem; border-radius: 8px; font-size: 13.5px; font-weight: 600;
  border: 1.5px solid var(--border); background: #fff; color: var(--text-main);
  cursor: pointer; transition: all .15s;
}
.mi-btn-cancel:hover { background: var(--bg-warm); }
.mi-btn-confirm-del {
  padding: .45rem 1.1rem; border-radius: 8px; font-size: 13.5px; font-weight: 700;
  border: none; background: #a82515; color: #fff; cursor: pointer; transition: all .15s;
}
.mi-btn-confirm-del:hover { background: #8b1e11; }
/* ── Modal form ── */
.mi-modal-create { border-radius: 14px; border: none; overflow: hidden; }
.mi-mc-header {
  display: flex; align-items: center; gap: .75rem;
  padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-light);
  background: var(--mi-primary);
}
.mi-mc-header-icon {
  width: 34px; height: 34px; border-radius: 8px;
  background: rgba(255,255,255,.18);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff;
}
.mi-mc-title { font-size: 15px; font-weight: 700; color: #fff; margin: 0; flex: 1; }
.mi-mc-close {
  background: rgba(255,255,255,.18); border: none; border-radius: 7px;
  width: 30px; height: 30px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #fff; transition: background .15s;
}
.mi-mc-close:hover { background: rgba(255,255,255,.28); }
.mi-mc-body {
  padding: 1.25rem 1.5rem;
  display: flex; flex-direction: column; gap: 1.1rem;
  overflow-y: auto; max-height: 70vh;
}
.mi-mc-section-label {
  font-size: 10.5px; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--text-muted);
  margin-bottom: .65rem; padding-bottom: .45rem;
  border-bottom: 1px solid var(--border-light);
}
.mi-req-star { color: #a82515; }
.mi-mc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.mi-mc-field { display: flex; flex-direction: column; gap: .3rem; }
.mi-mc-lbl { font-size: 12.5px; font-weight: 600; color: var(--text-main); }
.mi-mc-input, .mi-mc-select, .mi-mc-textarea {
  border: 1.5px solid var(--border); border-radius: 8px;
  padding: .42rem .75rem; font-size: 13.5px;
  background: #fff; color: var(--text-main); outline: none;
  transition: border-color .15s, box-shadow .15s; font-family: inherit;
}
.mi-mc-input:focus, .mi-mc-select:focus, .mi-mc-textarea:focus {
  border-color: var(--mi-primary);
  box-shadow: 0 0 0 3px rgba(123,28,28,.10);
}
.mi-mc-textarea { resize: vertical; }
.mi-mc-textarea.is-invalid { border-color: #ef4444; }
.mi-mc-select { cursor: pointer; }
/* UX: char counter */
.ms-char-counter {
  font-size: 11px; color: var(--text-muted);
  text-align: right; margin-top: .15rem;
}
.invalid-feedback { display: none; color: #a82515; font-size: 12px; margin-top: .25rem; }
.mi-mc-pcheck {
  display: inline-flex; align-items: center; gap: .3rem;
  cursor: pointer; font-size: 12.5px; padding: .2rem .5rem;
  border-radius: 20px; border: 1px solid var(--border-light);
  transition: all .12s; user-select: none;
}
.mi-mc-pcheck:hover { background: var(--bg-warm); border-color: var(--mi-gold); }
.mi-mc-pcheck input[type=radio] { accent-color: var(--mi-primary); width: 13px; height: 13px; }
.mi-mc-pcheck:has(input:checked) { background: rgba(123,28,28,.07); border-color: rgba(123,28,28,.25); }
.mi-mc-pname { font-weight: 600; }
.mi-mc-footer {
  display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
  padding: 1rem 1.5rem; border-top: 1px solid var(--border-light);
  background: var(--bg-warm);
}
.mi-mc-btn-cancel {
  padding: .48rem 1.1rem; border-radius: 8px; font-size: 13.5px; font-weight: 600;
  border: 1.5px solid var(--border); background: #fff; color: var(--text-main);
  cursor: pointer; transition: all .15s;
}
.mi-mc-btn-cancel:hover { background: #f0ece5; }
.mi-mc-btn-submit {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .48rem 1.25rem; border-radius: 8px; font-size: 13.5px; font-weight: 700;
  border: none; background: var(--mi-primary); color: #fff; cursor: pointer;
  box-shadow: 0 3px 12px rgba(123,28,28,.25); transition: all .15s;
}
.mi-mc-btn-submit:hover { background: var(--mi-primary-dark); transform: translateY(-1px); box-shadow: 0 5px 16px rgba(123,28,28,.32); }
/* ── Btn create ── */
.mi-btn-create {
  display: inline-flex; align-items: center; gap: .4rem;
  background: var(--mi-gold); border: 1px solid rgba(0,0,0,.1);
  color: #3d0a0a; font-size: 13.5px; font-weight: 700;
  padding: .55rem 1.2rem; border-radius: 9px; cursor: pointer;
  box-shadow: 0 3px 12px rgba(201,168,76,.30);
  transition: all .18s; white-space: nowrap;
}
.mi-btn-create:hover {
  background: var(--mi-gold-dark); color: #fff;
  transform: translateY(-1px); box-shadow: 0 6px 18px rgba(201,168,76,.38);
}
/* ── Responsive ── */
@media (max-width: 1199px) { .ms-grid { grid-template-columns: 240px 1fr; } }
@media (max-width: 991px) {
  .ms-grid { grid-template-columns: 1fr; }
  .ms-sidebar { order: 2; }
  .ms-main { order: 1; }
  .mi-stats-detail { grid-template-columns: 1fr 1fr; }
  .ms-peserta-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767px) {
  .mi-hero { padding: 1rem; }
  .mi-hero-title { font-size: 1.05rem; }
  .mi-hero-actions { width: 100%; }
  .mi-hero-action-sep { display: none; }
  .mi-info-sep { display: none; }
  .mi-info-strip { flex-direction: column; align-items: flex-start; gap: .4rem; }
  .mi-stats-detail { grid-template-columns: 1fr 1fr; }
  .ms-peserta-grid { grid-template-columns: 1fr; }
  /* UX: Sembunyikan kolom Prioritas di mobile agar tabel tidak overflow */
  .ms-col-hide-sm { display: none; }
  .mi-mc-grid { grid-template-columns: 1fr; }
  .ms-tab-toolbar { flex-direction: column; align-items: flex-start; }
  .ms-tl-filter { width: 100%; overflow-x: auto; flex-wrap: nowrap; padding-bottom: .25rem; }
}
</style>
