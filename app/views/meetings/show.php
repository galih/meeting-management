<?php
$baseUrl = rtrim(BASE_URL, '/');

// ── Status maps ──────────────────────────────────────────────
$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusCls = [
  'scheduled' => 'sh-status-blue',
  'ongoing'   => 'sh-status-gold',
  'done'      => 'sh-status-green',
  'cancelled' => 'sh-status-red',
];
$statusIcon = [
  'scheduled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];
$pStatusCls = [
  'accepted' => 'bdg-green',
  'invited'  => 'bdg-blue',
  'declined' => 'bdg-red',
  'attended' => 'bdg-teal',
  'pending'  => 'bdg-muted',
];
$pStatusLabel = [
  'accepted' => 'Diterima',
  'invited'  => 'Diundang',
  'declined' => 'Ditolak',
  'attended' => 'Hadir',
  'pending'  => 'Menunggu',
];
$prioClsMap = ['high' => 'bdg-red', 'medium' => 'bdg-gold', 'low' => 'bdg-green'];
$prioLblMap = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$tlSClsMap  = ['pending' => 'bdg-muted', 'in_progress' => 'bdg-blue', 'done' => 'bdg-green', 'cancelled' => 'bdg-red'];
$tlSLblMap  = ['pending' => 'Menunggu', 'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

// ── Computed vars ─────────────────────────────────────────────
$canEdit          = $canEdit ?? false;
$participants     = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];

$mStatus  = $meeting['status'] ?? 'scheduled';
$mCls     = $statusCls[$mStatus]   ?? 'sh-status-muted';
$mLabel   = $statusLabel[$mStatus] ?? ucfirst($mStatus);
$mIcon    = $statusIcon[$mStatus]  ?? '';
$loc      = trim($meeting['location'] ?? '');
$isLink   = $loc && (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://'));
$brand    = htmlspecialchars($meeting['color'] ?? '#7B1C1C');

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => $t['status'] === 'done'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList,
  fn($t) => !empty($t['due_date']) && $t['due_date'] < $today
         && !in_array($t['status'], ['done', 'cancelled'])
));
$progressPct = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;
$csrfToken   = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$avPalette   = ['#7B1C1C', '#2F6BC4', '#1a7340', '#7d3cb5', '#C9A84C', '#0d7a8a', '#b5530a'];
?>

<?php /* ── Flash toasts ── */ ?>
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="sh-toast" id="shToast" role="alert" aria-live="polite">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>
<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="sh-toast sh-toast--error" id="shToastErr" role="alert" aria-live="polite">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php /* ================================================================
   HERO
================================================================ */ ?>
<div class="sh-hero" style="--mc:<?= $brand ?>">
  <div class="sh-hero-body">
    <div class="sh-hero-row">

      <div class="sh-hero-left">
        <nav class="sh-bc" aria-label="Breadcrumb">
          <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
          <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          <span>Detail</span>
        </nav>
        <h1 class="sh-hero-title"><?= htmlspecialchars($meeting['title']) ?></h1>
        <div class="sh-hero-meta">
          <span class="sh-status <?= $mCls ?>"><?= $mIcon ?>&nbsp;<?= $mLabel ?></span>
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
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="sh-btn sh-btn--gold">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Edit
        </a>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button type="button" class="sh-btn sh-btn--ghost" data-bs-toggle="modal" data-bs-target="#modalStatus">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          Ubah Status
        </button>
        <?php endif; ?>
        <?php if (Auth::hasRole('admin')): ?>
        <button type="button" class="sh-btn sh-btn--danger" data-bs-toggle="modal" data-bs-target="#modalHapus">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>

    </div>
  </div>

  <div class="sh-hero-strip">
    <span class="sh-strip-item">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <strong>Mulai:</strong>&nbsp;<?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?>
    </span>
    <span class="sh-strip-sep">&rarr;</span>
    <span class="sh-strip-item">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <strong>Selesai:</strong>&nbsp;<?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?>
    </span>
    <?php if ($loc): ?>
    <span class="sh-strip-sep">&middot;</span>
    <span class="sh-strip-item">
      <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="sh-strip-link">
          Buka Link Kegiatan
          <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
      <?php else: ?>
        <?= htmlspecialchars($loc) ?>
      <?php endif; ?>
    </span>
    <?php endif; ?>
  </div>
</div>

<?php /* ================================================================
   BODY
================================================================ */ ?>
<div class="row g-3 mt-1">

  <?php /* ── SIDEBAR ── */ ?>
  <div class="col-xl-3 col-lg-4">

    <!-- Stat cards -->
    <div class="sh-stats mb-3">
      <div class="sh-stat">
        <div class="sh-stat-ico sico-blue">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalPeserta ?></div>
          <div class="sh-stat-lbl">Peserta</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico sico-brand">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div>
          <div class="sh-stat-val"><?= $totalTL ?></div>
          <div class="sh-stat-lbl">Tindak Lanjut</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico sico-green">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
          <div class="sh-stat-val val-green"><?= $doneTL ?></div>
          <div class="sh-stat-lbl">Selesai</div>
        </div>
      </div>
      <div class="sh-stat">
        <div class="sh-stat-ico <?= $overdueTL > 0 ? 'sico-red' : 'sico-muted' ?>">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div>
          <div class="sh-stat-val <?= $overdueTL > 0 ? 'val-red' : '' ?>"><?= $overdueTL ?></div>
          <div class="sh-stat-lbl">Terlambat</div>
        </div>
      </div>
    </div>

    <!-- Progress TL -->
    <?php if ($totalTL > 0): ?>
    <div class="sh-card mb-3">
      <div class="sh-card-hd">Progress Tindak Lanjut</div>
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
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
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
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
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
        <button type="button" class="sh-doc-btn sh-doc-outline-teal" id="btnKirimRingkasan">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 11 13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </div><!-- /sidebar -->

  <?php /* ── MAIN PANEL ── */ ?>
  <div class="col-xl-9 col-lg-8">
    <div class="sh-main-card">

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
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Peserta
          <span class="sh-pill"><?= $totalPeserta ?></span>
        </button>
      </div>

      <?php /* Tab: Tindak Lanjut */ ?>
      <div id="sh-panel-tl" class="sh-panel active" role="tabpanel" aria-labelledby="sh-tab-tl">

        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <div class="sh-toolbar">
          <button type="button" class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          </div>
          <p>Belum ada tindak lanjut untuk kegiatan ini.</p>
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
          <button type="button" class="sh-btn-add" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
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
                && !in_array($tl['status'], ['done', 'cancelled']);
              $pCls = $prioClsMap[$tl['priority'] ?? ''] ?? 'bdg-muted';
              $pLbl = $prioLblMap[$tl['priority'] ?? ''] ?? ucfirst($tl['priority'] ?? '-');
              $sCls = $tlSClsMap[$tl['status'] ?? '']   ?? 'bdg-muted';
              $sLbl = $tlSLblMap[$tl['status'] ?? '']   ?? ucfirst($tl['status'] ?? '-');
            ?>
              <tr class="<?= $isOver ? 'row-overdue' : '' ?>">
                <td>
                  <div class="sh-tl-desc"><?= htmlspecialchars($tl['description']) ?></div>
                  <?php if ($isOver): ?>
                  <span class="sh-bdg bdg-red" style="font-size:10px;margin-top:.2rem;display:inline-flex">Terlambat</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($tl['assigned_name'])): ?>
                  <div class="sh-pic">
                    <span class="sh-av sh-av-sm"><?= strtoupper(mb_substr($tl['assigned_name'], 0, 1)) ?></span>
                    <span><?= htmlspecialchars($tl['assigned_name']) ?></span>
                  </div>
                  <?php else: ?><span class="text-muted">&mdash;</span><?php endif; ?>
                </td>
                <td>
                  <span class="<?= $isOver ? 'dl-over' : 'dl-normal' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '&mdash;' ?>
                  </span>
                </td>
                <td><span class="sh-bdg <?= $pCls ?>"><?= $pLbl ?></span></td>
                <td><span class="sh-bdg <?= $sCls ?>"><?= $sLbl ?></span></td>
                <td>
                  <a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="sh-link-detail">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
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

      <?php /* Tab: Peserta */ ?>
      <div id="sh-panel-peserta" class="sh-panel" role="tabpanel" aria-labelledby="sh-tab-peserta">
        <?php if (empty($participants)): ?>
        <div class="sh-empty">
          <div class="sh-empty-ico">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          </div>
          <p>Belum ada peserta terdaftar.</p>
        </div>
        <?php else: ?>
        <div class="sh-peserta-grid">
          <?php foreach ($participants as $p):
            $psCls = $pStatusCls[$p['status'] ?? '']   ?? 'bdg-muted';
            $psLbl = $pStatusLabel[$p['status'] ?? ''] ?? ucfirst($p['status'] ?? '-');
            $bg    = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
          ?>
          <div class="sh-peserta-card">
            <span class="sh-av" style="background:<?= $bg ?>">
              <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
            </span>
            <div class="sh-peserta-info">
              <div class="sh-peserta-name"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['position'])): ?>
              <div class="sh-peserta-pos"><?= htmlspecialchars($p['position']) ?></div>
              <?php endif; ?>
            </div>
            <span class="sh-bdg <?= $psCls ?>"><?= $psLbl ?></span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div><!-- /sh-panel-peserta -->

    </div><!-- /sh-main-card -->
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
            <?php foreach (['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'] as $v => $l): ?>
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
          <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mb-2" id="lblModalHapus">Hapus Kegiatan?</h5>
        <p class="text-muted small mb-0">
          Kegiatan <strong class="text-danger"><?= htmlspecialchars($meeting['title']) ?></strong>
          akan dihapus permanen beserta semua notulen, peserta, dan tindak lanjut.
        </p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary"
                data-bs-dismiss="modal">Batal</button>
        <form method="POST"
              action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/delete">
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
      document.querySelectorAll('.sh-panel').forEach(function (p) {
        p.classList.remove('active');
      });
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
      if (!desc.value.trim()) {
        desc.classList.add('is-invalid');
        desc.focus();
        return;
      }

      var origHtml = btnSaveTL.innerHTML;
      btnSaveTL.disabled = true;
      btnSaveTL.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';

      fetch(BASE + '/tindak-lanjut', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({
          meeting_id : MTG_ID,
          description: desc.value.trim(),
          assigned_to: assigned ? (assigned.value || null) : null,
          due_date   : deadline ? (deadline.value  || null) : null,
          priority   : prio ? prio.value : 'medium',
          _csrf      : CSRF
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
          shToast(data.message || 'Gagal menyimpan tindak lanjut.', 'error');
          btnSaveTL.disabled = false;
          btnSaveTL.innerHTML = origHtml;
        }
      })
      .catch(function (err) {
        shToast('Terjadi kesalahan: ' + err.message, 'error');
        btnSaveTL.disabled = false;
        btnSaveTL.innerHTML = origHtml;
      });
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
        body: JSON.stringify({ _csrf: CSRF })
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
        body: JSON.stringify({ _csrf: CSRF })
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
    t.className = 'sh-toast' + (type === 'error' ? ' sh-toast--error' : '');
    t.setAttribute('role', 'alert');
    t.setAttribute('aria-live', 'polite');
    t.innerHTML = msg +
      '<button class="sh-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    setTimeout(function () {
      t.style.opacity = '0';
      setTimeout(function () { if (t.parentElement) t.remove(); }, 400);
    }, 4500);
  }

  /* ── Auto-dismiss flash toasts ── */
  ['shToast', 'shToastErr'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) {
      setTimeout(function () {
        el.style.opacity = '0';
        setTimeout(function () { if (el.parentElement) el.remove(); }, 400);
      }, 4500);
    }
  });

}());
</script>

<style>
/* =============================================================
   SHOW.PHP — scoped styles
   Semua warna dari token custom.css:
     --brand, --brand-dark, --brand-light, --brand-xlight
     --gold,  --gold-dark,  --gold-light
     --bg-page, --bg-card, --text-main, --text-muted
     --border, --border-light
============================================================= */

/* ── Toast ─────────────────────────────────────────────────── */
.sh-toast {
  position: fixed; top: 1.1rem; right: 1.1rem; z-index: 1090;
  display: flex; align-items: center; gap: .45rem;
  background: #1a7340; color: #fff;
  padding: .6rem 1rem; border-radius: 9px;
  font-size: 13px; font-weight: 600;
  box-shadow: 0 4px 18px rgba(0,0,0,.18);
  transition: opacity .4s ease;
  max-width: 340px;
}
.sh-toast--error { background: var(--brand); }
.sh-toast-close {
  background: none; border: none; color: inherit;
  cursor: pointer; font-size: 17px; line-height: 1;
  padding: 0 0 0 .4rem; opacity: .8; margin-left: auto;
}
.sh-toast-close:hover { opacity: 1; }

/* ── Hero ──────────────────────────────────────────────────── */
.sh-hero {
  --mc: var(--brand);
  background: linear-gradient(135deg, var(--mc) 0%, var(--mc) 60%, #3d0a0a 100%);
  border-radius: 14px;
  box-shadow: 0 4px 24px rgba(0,0,0,.18);
  overflow: hidden;
}
.sh-hero-body   { padding: 1.35rem 1.6rem 1rem; }
.sh-hero-row    { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem; }
.sh-bc          { display: flex; align-items: center; gap: .3rem; font-size: 11.5px; color: rgba(255,255,255,.55); margin-bottom: .4rem; }
.sh-bc a        { color: rgba(255,255,255,.78); text-decoration: none; }
.sh-bc a:hover  { color: #fff; text-decoration: underline; }
.sh-hero-title  { font-size: clamp(15px,2.3vw,21px); font-weight: 800; color: #fff; margin: 0; letter-spacing: -.02em; line-height: 1.25; }
.sh-hero-meta   { display: flex; flex-wrap: wrap; align-items: center; gap: .35rem; margin-top: .5rem; }

/* Status pill (in hero) */
.sh-status      { display: inline-flex; align-items: center; gap: .3rem; font-size: 11px; font-weight: 700; padding: .25em .6em; border-radius: 20px; white-space: nowrap; }
.sh-status-blue   { background: rgba(47,107,196,.22);  color: #a8caff; }
.sh-status-gold   { background: rgba(201,168,76,.28);  color: #ffe9a0; }
.sh-status-green  { background: rgba(26,115,64,.28);   color: #a1f0c0; }
.sh-status-red    { background: rgba(192,57,43,.28);   color: #ffc1b8; }
.sh-status-muted  { background: rgba(255,255,255,.12); color: rgba(255,255,255,.7); }

/* Chip */
.sh-chip { display: inline-flex; align-items: center; gap: .25rem; font-size: 11px; color: rgba(255,255,255,.72); background: rgba(255,255,255,.10); padding: .2em .55em; border-radius: 20px; }

/* Hero action buttons */
.sh-hero-actions { display: flex; flex-wrap: wrap; gap: .4rem; align-items: flex-start; padding-top: 1.2rem; }
.sh-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 12.5px; font-weight: 700;
  padding: .38rem .9rem; border-radius: 8px;
  cursor: pointer; border: none; text-decoration: none;
  transition: background .18s, box-shadow .18s;
  white-space: nowrap;
}
.sh-btn--gold   { background: var(--gold); color: #fff; }
.sh-btn--gold:hover { background: var(--gold-dark); color: #fff; }
.sh-btn--ghost  { background: rgba(255,255,255,.14); border: 1.5px solid rgba(255,255,255,.3); color: #fff; }
.sh-btn--ghost:hover { background: rgba(255,255,255,.24); }
.sh-btn--danger { background: rgba(192,57,43,.22); border: 1.5px solid rgba(255,255,255,.25); color: #ffd5cf; }
.sh-btn--danger:hover { background: rgba(192,57,43,.38); color: #fff; }

/* Hero strip */
.sh-hero-strip  { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; background: rgba(0,0,0,.20); padding: .55rem 1.6rem; font-size: 12px; color: rgba(255,255,255,.82); }
.sh-strip-item  { display: flex; align-items: center; gap: .3rem; }
.sh-strip-sep   { opacity: .45; }
.sh-strip-link  { color: rgba(255,255,255,.9); text-decoration: underline dotted; display: inline-flex; align-items: center; gap: .2rem; }
.sh-strip-link:hover { color: #fff; }

/* ── Stat grid ─────────────────────────────────────────────── */
.sh-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.sh-stat  { display: flex; align-items: center; gap: .6rem; background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 10px; padding: .7rem .85rem; }
.sh-stat-ico { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.sico-blue  { background: rgba(47,107,196,.10);  color: #2F6BC4; }
.sico-brand { background: var(--brand-light);    color: var(--brand); }
.sico-green { background: rgba(26,115,64,.10);   color: #1a7340; }
.sico-red   { background: rgba(192,57,43,.10);   color: #a82515; }
.sico-muted { background: rgba(122,106,90,.08);  color: var(--text-muted); }
.sh-stat-val { font-size: 18px; font-weight: 800; color: var(--text-main); line-height: 1.1; }
.sh-stat-lbl { font-size: 10.5px; color: var(--text-muted); font-weight: 600; margin-top: 1px; }
.val-green   { color: #1a7340; }
.val-red     { color: #a82515; }

/* ── Sidebar cards ─────────────────────────────────────────── */
.sh-card    { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 10px; overflow: hidden; }
.sh-card-hd { display: flex; align-items: center; gap: .35rem; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .07em; color: var(--text-muted); padding: .65rem .9rem; border-bottom: 1px solid var(--border-light); background: #faf6ef; }
.sh-card-bd { padding: .85rem .9rem; }

.sh-prog-row  { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: .4rem; }
.sh-prog-pct  { font-size: 14px; font-weight: 800; color: var(--brand); }
.sh-prog-meta { font-size: 11.5px; color: var(--text-muted); }
.sh-prog-bar  { height: 7px; background: var(--border-light); border-radius: 999px; overflow: hidden; }
.sh-prog-fill { height: 100%; background: linear-gradient(90deg, var(--brand), var(--gold)); border-radius: 999px; transition: width .5s ease; }

.sh-agenda { font-size: 13px; color: var(--text-main); line-height: 1.65; margin: 0; white-space: pre-wrap; word-break: break-word; }

.sh-doc-list { display: flex; flex-direction: column; gap: .45rem; }
.sh-doc-btn  {
  display: inline-flex; align-items: center; justify-content: center;
  gap: .4rem; font-size: 13px; font-weight: 600;
  padding: .45rem .9rem; border-radius: 8px;
  cursor: pointer; text-decoration: none; border: none;
  transition: background .16s, color .16s; width: 100%;
}
.sh-doc-primary         { background: var(--brand); color: #fff; }
.sh-doc-primary:hover   { background: var(--brand-dark); color: #fff; }
.sh-doc-outline         { background: transparent; color: var(--text-main); border: 1.5px solid var(--border); }
.sh-doc-outline:hover   { background: var(--brand-xlight); border-color: var(--brand); color: var(--brand); }
.sh-doc-outline-teal    { background: transparent; color: #0d7a8a; border: 1.5px solid rgba(13,122,138,.30); }
.sh-doc-outline-teal:hover { background: rgba(13,122,138,.06); border-color: #0d7a8a; }

/* ── Main card & tabs ──────────────────────────────────────── */
.sh-main-card { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 12px; overflow: hidden; }
.sh-tabs      { display: flex; border-bottom: 1px solid var(--border-light); background: #faf6ef; padding: 0 1rem; }
.sh-tab {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600; color: var(--text-muted);
  padding: .7rem .9rem; background: none; border: none;
  border-bottom: 2.5px solid transparent;
  cursor: pointer; transition: color .15s, border-color .15s; white-space: nowrap;
}
.sh-tab:hover  { color: var(--brand); }
.sh-tab.active { color: var(--brand); border-bottom-color: var(--brand); font-weight: 700; }
.sh-pill {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 18px; height: 18px; padding: 0 5px;
  font-size: 10px; font-weight: 700;
  background: var(--border-light); color: var(--text-muted); border-radius: 9px;
}
.sh-tab.active .sh-pill { background: var(--brand-light); color: var(--brand); }

.sh-panel        { display: none; }
.sh-panel.active { display: block; }

.sh-toolbar { padding: .75rem 1rem; border-bottom: 1px solid var(--border-light); background: #fdfaf5; }

.sh-empty      { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 2.5rem 1rem; color: var(--text-muted); }
.sh-empty-ico  { width: 54px; height: 54px; border-radius: 14px; background: var(--brand-xlight); display: flex; align-items: center; justify-content: center; color: var(--brand); margin-bottom: .9rem; }
.sh-empty p    { font-size: 13.5px; margin-bottom: 1rem; }

.sh-btn-add {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 12.5px; font-weight: 700;
  background: var(--brand); color: #fff;
  padding: .4rem 1rem; border-radius: 8px; border: none; cursor: pointer;
  transition: background .18s;
}
.sh-btn-add:hover { background: var(--brand-dark); }

/* ── Table ─────────────────────────────────────────────────── */
.sh-table { width: 100%; border-collapse: collapse; font-size: 13px; color: var(--text-main); }
.sh-table thead th {
  font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
  color: var(--text-muted); background: #faf6ef;
  border-bottom: 2px solid var(--border); padding: .6rem 1rem; white-space: nowrap;
}
.sh-table tbody td { padding: .65rem 1rem; border-bottom: 1px solid var(--border-light); vertical-align: middle; }
.sh-table tbody tr:last-child td { border-bottom: none; }
.sh-table tbody tr:hover { background: var(--brand-xlight); }
.row-overdue       { background: rgba(192,57,43,.03) !important; }
.row-overdue:hover { background: rgba(192,57,43,.06) !important; }

/* ── Badges ────────────────────────────────────────────────── */
.sh-bdg       { display: inline-flex; align-items: center; font-size: 10.5px; font-weight: 700; padding: .25em .6em; border-radius: 5px; white-space: nowrap; }
.bdg-green    { background: rgba(26,115,64,.10);  color: #1a7340; }
.bdg-blue     { background: rgba(47,107,196,.10); color: #1557a0; }
.bdg-red      { background: rgba(192,57,43,.10);  color: #a82515; }
.bdg-gold     { background: var(--gold-light);    color: #7a5000; }
.bdg-teal     { background: rgba(13,122,138,.10); color: #0a7a58; }
.bdg-muted    { background: rgba(122,106,90,.09); color: var(--text-muted); }

/* ── Misc ──────────────────────────────────────────────────── */
.sh-tl-desc    { font-size: 13px; font-weight: 500; line-height: 1.4; }
.dl-normal     { font-size: 12.5px; color: var(--text-muted); }
.dl-over       { font-size: 12.5px; color: #a82515; font-weight: 700; }
.sh-pic        { display: flex; align-items: center; gap: .4rem; font-size: 13px; }
.sh-av         { display: inline-flex; align-items: center; justify-content: center; width: 30px; height: 30px; border-radius: 50%; font-size: 12px; font-weight: 800; color: #fff; background: var(--brand); flex-shrink: 0; }
.sh-av-sm      { width: 22px; height: 22px; font-size: 10px; }
.sh-link-detail { display: inline-flex; align-items: center; gap: .25rem; font-size: 12px; color: var(--brand); font-weight: 600; text-decoration: none; }
.sh-link-detail:hover { color: var(--brand-dark); text-decoration: underline; }

/* ── Peserta grid ──────────────────────────────────────────── */
.sh-peserta-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: .5rem; padding: .85rem 1rem; }
.sh-peserta-card { display: flex; align-items: center; gap: .6rem; background: var(--bg-page); border: 1px solid var(--border-light); border-radius: 10px; padding: .6rem .85rem; }
.sh-peserta-info { flex: 1; min-width: 0; }
.sh-peserta-name { font-size: 13px; font-weight: 600; color: var(--text-main); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sh-peserta-pos  { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ── Delete icon container ─────────────────────────────────── */
.sh-del-ico { width: 60px; height: 60px; border-radius: 50%; background: rgba(192,57,43,.10); color: #a82515; display: flex; align-items: center; justify-content: center; margin: 0 auto; }

/* ── Responsive ────────────────────────────────────────────── */
@media (max-width: 575px) {
  .sh-hero-body { padding: 1rem; }
  .sh-hero-strip { padding: .5rem 1rem; }
  .sh-hero-actions { padding-top: 0; }
  .sh-stats { grid-template-columns: 1fr 1fr; }
  .sh-peserta-grid { grid-template-columns: 1fr; }
}
</style>
