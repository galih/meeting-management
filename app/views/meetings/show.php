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
$pStatusColor = ['accepted'=>'mi-green','invited'=>'mi-blue','declined'=>'mi-red','attended'=>'mi-green','pending'=>'mi-gray'];
$pStatusLabel = ['accepted'=>'Diterima','invited'=>'Diundang','declined'=>'Ditolak','attended'=>'Hadir','pending'=>'Menunggu'];
$prioBadge    = ['high'=>'mi-red','medium'=>'mi-amber','low'=>'mi-green'];
$prioLabel    = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$tlsBadge     = ['pending'=>'mi-gray','in_progress'=>'mi-blue','done'=>'mi-green','cancelled'=>'mi-red'];
$tlsLabel     = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];

$canEdit          = $canEdit ?? false;
$participants     = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];

$mStatus  = $meeting['status'] ?? 'scheduled';
$mLabel   = $statusLabel[$mStatus]  ?? ucfirst($mStatus);
$mColor   = $statusColor[$mStatus]  ?? 'mi-blue';
$mIcon    = $statusIcon[$mStatus]   ?? '';
$loc      = trim($meeting['location'] ?? '');
$isLink   = $loc && (str_starts_with($loc,'http://') || str_starts_with($loc,'https://'));

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => ($t['status']??'') === 'done'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList, fn($t) =>
  !empty($t['due_date']) && $t['due_date'] < $today && !in_array($t['status']??'', ['done','cancelled'])
));
$progressPct  = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;
$csrfToken    = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$avPalette    = ['#7B1C1C','#9B2020','#8b5e00','#205375','#2d7a2d','#6b2fa0','#a05c00'];

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<?php if ($flashSuccess): ?>
<div class="mi-toast mi-toast-ok" id="miFlashToast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <span><?= htmlspecialchars($flashSuccess) ?></span>
  <button class="mi-toast-close" onclick="this.closest('.mi-toast').remove()">×</button>
</div>
<?php elseif ($flashError): ?>
<div class="mi-toast mi-toast-err" id="miFlashToast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button class="mi-toast-close" onclick="this.closest('.mi-toast').remove()">×</button>
</div>
<?php endif; ?>

<!-- ══ HERO ══════════════════════════════════════════════════════════ -->
<div class="mi-hero mi-hero-detail">
  <div class="mi-hero-left">
    <a href="<?= $baseUrl ?>/meetings" class="mi-hero-back" title="Kembali ke Daftar Kegiatan">
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
          <span class="mi-hero-chip"><?= htmlspecialchars($meeting['dept_name']) ?></span>
        <?php endif; ?>
        <span class="mi-hero-chip">PIC: <?= htmlspecialchars($meeting['creator_name'] ?? '—') ?></span>
      </div>
    </div>
  </div>
  <div class="mi-hero-actions">
    <?php if ($canEdit): ?>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="mi-btn-edit">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4z"/></svg>
        Edit
      </a>
    <?php endif; ?>
    <?php if (Auth::hasRole('admin','sekretaris')): ?>
      <button type="button" class="mi-btn-action" data-bs-toggle="modal" data-bs-target="#modalStatus">Ubah Status</button>
    <?php endif; ?>
    <?php if (Auth::hasRole('admin')): ?>
      <button type="button" class="mi-btn-del-hero" data-bs-toggle="modal" data-bs-target="#modalHapus">
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
    <span><strong>Mulai:</strong> <?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?></span>
  </div>
  <div class="mi-info-sep"></div>
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <span><strong>Selesai:</strong> <?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?></span>
  </div>
  <div class="mi-info-sep"></div>
  <div class="mi-info-item">
    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
    <?php if ($loc): ?>
      <?php if ($isLink): ?>
        <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="mi-loc-link">Buka tautan rapat</a>
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
    <div class="mi-stat-val"><?= $totalPeserta ?></div>
    <div class="mi-stat-lbl">Peserta</div>
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

<!-- ══ MAIN GRID ══════════════════════════════════════════════════════ -->
<div class="ms-grid">

  <!-- ── LEFT SIDEBAR ─────────────────────────────────────────────── -->
  <aside class="ms-sidebar">

    <?php if ($totalTL > 0): ?>
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head">
        <span>Progres Tindak Lanjut</span>
        <strong><?= $progressPct ?>%</strong>
      </div>
      <div class="ms-progress-bar" role="progressbar" aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100">
        <div class="ms-progress-fill" style="width:<?= $progressPct ?>%"></div>
      </div>
      <div class="ms-progress-sub"><?= $doneTL ?> dari <?= $totalTL ?> tugas selesai</div>
    </div>
    <?php endif; ?>

    <?php if (!empty($meeting['description'])): ?>
    <div class="mi-panel ms-panel">
      <div class="ms-panel-head"><span>Agenda Kegiatan</span></div>
      <div class="ms-agenda"><?= nl2br(htmlspecialchars($meeting['description'])) ?></div>
    </div>
    <?php endif; ?>

    <div class="mi-panel ms-panel">
      <div class="ms-panel-head"><span>Dokumen &amp; Aksi</span></div>
      <div class="ms-doc-list">
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="ms-doc-btn ms-doc-btn--primary">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
          Buka Notulen
        </a>
        <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>/export-docx" class="ms-doc-btn">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          Export DOCX
        </a>
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
          <button type="button" class="ms-doc-btn" id="btnKirimUndangan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            Kirim Undangan
          </button>
          <?php if (($meeting['status']??'') === 'done'): ?>
          <button type="button" class="ms-doc-btn" id="btnKirimRingkasan">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Kirim Ringkasan
          </button>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

  </aside>

  <!-- ── MAIN CONTENT ──────────────────────────────────────────────── -->
  <main class="ms-main">
    <div class="mi-panel">

      <!-- Tabs -->
      <div class="ms-tabs">
        <button class="ms-tab active" data-tab="tl" role="tab" aria-selected="true" aria-controls="ms-panel-tl">
          Tindak Lanjut <span class="ms-tab-count"><?= $totalTL ?></span>
        </button>
        <button class="ms-tab" data-tab="peserta" role="tab" aria-selected="false" aria-controls="ms-panel-peserta">
          Peserta <span class="ms-tab-count"><?= $totalPeserta ?></span>
        </button>
      </div>

      <!-- ── Tab: Tindak Lanjut ──────────────────────────────────── -->
      <div id="ms-panel-tl" class="ms-tab-panel active" role="tabpanel">
        <?php if (Auth::hasRole('admin','sekretaris')): ?>
        <div class="ms-tab-toolbar">
          <button type="button" class="mi-btn-create" style="background:#7B1C1C;color:#fff;box-shadow:0 3px 12px rgba(123,28,28,.25);" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Tindak Lanjut
          </button>
        </div>
        <?php endif; ?>

        <?php if (empty($tindakLanjutList)): ?>
          <div class="mi-empty">
            <div class="mi-empty-icon">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
            <h3 class="mi-empty-title">Belum ada tindak lanjut</h3>
            <p class="mi-empty-desc">Tambahkan tindak lanjut untuk memantau progres kegiatan ini.</p>
          </div>
        <?php else: ?>
          <div class="mi-table-wrap">
            <table class="mi-table" aria-label="Daftar tindak lanjut">
              <thead>
                <tr>
                  <th>Tugas</th>
                  <th>PIC</th>
                  <th>Deadline</th>
                  <th>Prioritas</th>
                  <th>Status</th>
                  <th style="text-align:right">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($tindakLanjutList as $tl):
                  $isOver  = !empty($tl['due_date']) && $tl['due_date'] < $today && !in_array($tl['status']??'',['done','cancelled']);
                  $pBadge  = $prioBadge[$tl['priority']??''] ?? 'mi-gray';
                  $pLbl    = $prioLabel[$tl['priority']??'']  ?? ucfirst($tl['priority']??'—');
                  $sBadge  = $tlsBadge[$tl['status']??'']    ?? 'mi-gray';
                  $sLbl    = $tlsLabel[$tl['status']??'']    ?? ucfirst($tl['status']??'—');
                  $picName = $tl['assigned_name'] ?? '';
                  $avBg    = $picName ? $avPalette[abs(crc32($picName)) % count($avPalette)] : '#8C8C8C';
                ?>
                <tr class="<?= $isOver ? 'ms-overdue-row' : '' ?>">
                  <td>
                    <div class="mi-title-name"><?= htmlspecialchars($tl['description']) ?></div>
                    <?php if ($isOver): ?><span class="ms-overdue-badge">Terlambat</span><?php endif; ?>
                  </td>
                  <td>
                    <?php if ($picName): ?>
                      <div class="ms-pic">
                        <span class="ms-avatar" style="background:<?= $avBg ?>;"><?= strtoupper(mb_substr($picName,0,1)) ?></span>
                        <span><?= htmlspecialchars($picName) ?></span>
                      </div>
                    <?php else: ?><span class="mi-null">—</span><?php endif; ?>
                  </td>
                  <td class="<?= $isOver ? 'ms-overdue-text' : '' ?>">
                    <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
                  </td>
                  <td><span class="mi-status <?= $pBadge ?>"><?= $pLbl ?></span></td>
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
            <span><?= $totalTL ?> tindak lanjut &bull; <?= $doneTL ?> selesai &bull; <?= $overdueTL ?> terlambat</span>
          </div>
        <?php endif; ?>
      </div>

      <!-- ── Tab: Peserta ───────────────────────────────────────── -->
      <div id="ms-panel-peserta" class="ms-tab-panel" role="tabpanel">
        <?php if (empty($participants)): ?>
          <div class="mi-empty">
            <div class="mi-empty-icon">
              <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <h3 class="mi-empty-title">Belum ada peserta</h3>
            <p class="mi-empty-desc">Peserta belum ditambahkan ke kegiatan ini.</p>
          </div>
        <?php else: ?>
          <div class="mi-table-wrap">
            <table class="mi-table" aria-label="Daftar peserta">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Jabatan</th>
                  <th>Status Kehadiran</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($participants as $p):
                  $psBadge = $pStatusColor[$p['status']??''] ?? 'mi-gray';
                  $psLbl   = $pStatusLabel[$p['status']??''] ?? ucfirst($p['status']??'—');
                  $avBg    = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
                ?>
                <tr>
                  <td>
                    <div class="ms-pic">
                      <span class="ms-avatar" style="background:<?= $avBg ?>;"><?= strtoupper(mb_substr($p['name'],0,1)) ?></span>
                      <span class="mi-title-name"><?= htmlspecialchars($p['name']) ?></span>
                    </div>
                  </td>
                  <td><?= !empty($p['position']) ? htmlspecialchars($p['position']) : '<span class="mi-null">—</span>' ?></td>
                  <td><span class="mi-status <?= $psBadge ?>"><?= $psLbl ?></span></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="mi-tfoot">
            <span><?= $totalPeserta ?> peserta terdaftar</span>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- ══ MODAL: UBAH STATUS ════════════════════════════════════════════ -->
<?php if (Auth::hasRole('admin','sekretaris')): ?>
<div class="modal fade" id="modalStatus" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content mi-modal-del">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="mi-mc-header">
          <div class="mi-mc-header-icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <h5 class="mi-mc-title">Ubah Status Kegiatan</h5>
          <button type="button" class="mi-mc-close" data-bs-dismiss="modal" aria-label="Tutup">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <div style="padding:1.25rem 1.5rem;">
          <label class="mi-mc-lbl" for="selStatus" style="margin-bottom:.35rem;display:block;">Status Baru</label>
          <select id="selStatus" name="status" class="mi-mc-select" style="width:100%;">
            <?php foreach ($statusLabel as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($meeting['status']??'') === $val ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
            <?php endforeach; ?>
          </select>
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
<div class="modal fade" id="modalTambahTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content mi-modal-create">
      <div class="mi-mc-header">
        <div class="mi-mc-header-icon">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <h5 class="mi-mc-title">Tambah Tindak Lanjut</h5>
        <button type="button" class="mi-mc-close" data-bs-dismiss="modal" aria-label="Tutup">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
      <div class="mi-mc-body">
        <div class="mi-mc-section">
          <div class="mi-mc-section-label">Deskripsi Tugas</div>
          <div class="mi-mc-field">
            <textarea id="tlDesc" class="mi-mc-textarea" rows="3" placeholder="Contoh: Susun laporan evaluasi semester…"></textarea>
            <div class="invalid-feedback">Deskripsi wajib diisi.</div>
          </div>
        </div>
        <div class="mi-mc-section">
          <div class="mi-mc-section-label">Penugasan &amp; Tenggat</div>
          <div class="mi-mc-grid">
            <div class="mi-mc-field">
              <label class="mi-mc-lbl" for="tlAssigned">Ditugaskan ke</label>
              <select id="tlAssigned" class="mi-mc-select">
                <option value="">— Pilih peserta —</option>
                <?php foreach ($participants as $p): ?>
                  <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="mi-mc-field">
              <label class="mi-mc-lbl" for="tlDeadline">Deadline</label>
              <input type="date" id="tlDeadline" class="mi-mc-input" min="<?= $today ?>">
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
<div class="modal fade" id="modalHapus" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content mi-modal-del">
      <div class="mi-modal-del-body">
        <div class="mi-del-icon-wrap">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6M9 6V4h6v2"/></svg>
        </div>
        <h5 class="mi-del-title">Hapus Kegiatan?</h5>
        <p class="mi-del-desc">
          Kegiatan <strong class="mi-del-name"><?= htmlspecialchars($meeting['title']) ?></strong> akan dihapus permanen beserta seluruh peserta, notulen, dan tindak lanjut terkait.
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
  var CSRF   = <?= json_encode($csrfToken) ?>;

  /* Auto-dismiss toast */
  var toast = document.getElementById('miFlashToast');
  if (toast) {
    setTimeout(function () { toast.style.opacity = '0'; }, 4000);
    setTimeout(function () { if (toast.parentElement) toast.remove(); }, 4500);
  }

  /* Tabs */
  document.querySelectorAll('.ms-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.ms-tab').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.ms-tab-panel').forEach(function (p) {
        p.classList.remove('active');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      var panel = document.getElementById('ms-panel-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });

  /* Save tindak lanjut */
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
        if (d.success) window.location.reload();
        else { miToast(d.message || 'Gagal menyimpan.', 'err'); btnSaveTL.disabled = false; btnSaveTL.innerHTML = orig; }
      })
      .catch(function (e) { miToast('Kesalahan: ' + e.message, 'err'); btnSaveTL.disabled = false; btnSaveTL.innerHTML = orig; });
    });
  }

  /* Kirim Undangan */
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

  /* Kirim Ringkasan */
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

  function miToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'mi-toast mi-toast-' + (type === 'ok' ? 'ok' : 'err');
    t.setAttribute('role', 'alert');
    t.innerHTML = msg + '<button class="mi-toast-close" onclick="this.closest(\'.mi-toast\').remove()">×</button>';
    document.body.appendChild(t);
    setTimeout(function () { t.style.opacity = '0'; }, 4000);
    setTimeout(function () { if (t.parentElement) t.remove(); }, 4500);
  }
}());
</script>

<!-- ══ STYLES ════════════════════════════════════════════════════════ -->
<style>
/* ── Toast (identical to index.php) ── */
.mi-toast {
  position:fixed; top:1.25rem; right:1.25rem; z-index:9999;
  display:flex; align-items:center; gap:.6rem;
  padding:.7rem 1rem; border-radius:10px;
  font-size:13.5px; font-weight:500;
  box-shadow:0 4px 20px rgba(0,0,0,.14);
  animation:miSlideIn .25s ease; max-width:360px;
  transition: opacity .4s;
}
@keyframes miSlideIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
.mi-toast-ok  { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.mi-toast-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.mi-toast-close { background:none; border:none; font-size:16px; cursor:pointer; margin-left:.25rem; opacity:.6; line-height:1; padding:0; }
.mi-toast-close:hover { opacity:1; }

/* ── Hero detail ── */
.mi-hero { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap; background:linear-gradient(135deg,#7B1C1C 0%,#9B2020 55%,#A83218 100%); padding:1.25rem 1.5rem; border-radius:14px; margin-bottom:1rem; box-shadow:0 4px 20px rgba(123,28,28,.22); position:relative; overflow:hidden; }
.mi-hero::after { content:''; position:absolute; top:-40px; right:-40px; width:180px; height:180px; border-radius:50%; background:rgba(201,168,76,.08); pointer-events:none; }
.mi-hero-left { display:flex; align-items:center; gap:.75rem; }
.mi-hero-back { display:flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:9px; background:rgba(255,255,255,.15); color:#fff; text-decoration:none; flex-shrink:0; transition:background .15s; }
.mi-hero-back:hover { background:rgba(255,255,255,.25); color:#fff; }
.mi-hero-icon { width:38px; height:38px; border-radius:10px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#fff; }
.mi-hero-eyebrow { font-size:10.5px; font-weight:700; text-transform:uppercase; letter-spacing:.12em; color:rgba(255,255,255,.65); margin-bottom:.25rem; }
.mi-hero-title { font-size:clamp(1.1rem,2.2vw,1.5rem); font-weight:800; color:#fff; margin:0; line-height:1.2; }
.mi-hero-meta { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; margin-top:.5rem; }
.mi-hero-chip { display:inline-flex; align-items:center; padding:.3rem .65rem; border-radius:999px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.12); color:rgba(255,255,255,.88); font-size:11.5px; font-weight:600; }
.mi-hero-actions { display:flex; gap:.6rem; flex-wrap:wrap; align-items:flex-start; }
.mi-btn-edit { display:inline-flex; align-items:center; gap:.4rem; background:var(--gold,#C9A84C); border:1px solid rgba(0,0,0,.1); color:#3d0a0a; font-size:13px; font-weight:700; padding:.5rem 1rem; border-radius:9px; cursor:pointer; text-decoration:none; transition:all .18s; white-space:nowrap; }
.mi-btn-edit:hover { background:#b8922a; color:#fff; }
.mi-btn-action { display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18); color:#fff; font-size:13px; font-weight:600; padding:.5rem 1rem; border-radius:9px; cursor:pointer; transition:all .18s; }
.mi-btn-action:hover { background:rgba(255,255,255,.22); }
.mi-btn-del-hero { display:inline-flex; align-items:center; gap:.4rem; background:rgba(192,57,43,.18); border:1px solid rgba(255,180,170,.2); color:#FFD4CC; font-size:13px; font-weight:600; padding:.5rem 1rem; border-radius:9px; cursor:pointer; transition:all .18s; }
.mi-btn-del-hero:hover { background:rgba(192,57,43,.3); color:#fff; }

/* ── Info strip ── */
.mi-info-strip { display:flex; flex-wrap:wrap; align-items:center; gap:.5rem; background:#fff; border:1px solid var(--border-light,#ede8e0); border-radius:12px; padding:.7rem 1.1rem; margin-bottom:1rem; font-size:13px; }
.mi-info-item { display:flex; align-items:center; gap:.4rem; color:var(--text-main,#2c1a1a); }
.mi-info-item svg { color:var(--text-muted,#8c7a6b); flex-shrink:0; }
.mi-info-sep { width:1px; height:16px; background:var(--border-light,#ede8e0); flex-shrink:0; }
.mi-loc-link { color:#7B1C1C; font-weight:600; text-decoration:underline; }
.mi-loc-link:hover { color:#9B2020; }

/* ── Stat cards (reuse index.php styles) ── */
.mi-stats-detail { grid-template-columns:1.4fr repeat(3,1fr); }

/* ── Layout grid ── */
.ms-grid { display:grid; grid-template-columns:280px 1fr; gap:1.1rem; align-items:start; }

/* ── Sidebar panels ── */
.ms-sidebar { display:flex; flex-direction:column; gap:1rem; }
.ms-panel { overflow:hidden; }
.ms-panel-head { display:flex; align-items:center; justify-content:space-between; padding:.85rem 1.1rem; background:#faf6ef; border-bottom:1px solid var(--border-light,#ede8e0); font-size:12px; font-weight:700; color:var(--text-muted,#8c7a6b); text-transform:uppercase; letter-spacing:.07em; }
.ms-panel-head strong { font-size:15px; text-transform:none; letter-spacing:0; color:#7B1C1C; }
.ms-progress-bar { height:10px; background:#f0ece5; border-radius:999px; overflow:hidden; margin:0 1.1rem; }
.ms-progress-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#7B1C1C 0%,#C9A84C 100%); transition:width .5s ease; }
.ms-progress-sub { padding:.5rem 1.1rem .9rem; font-size:11.5px; color:var(--text-muted); }
.ms-agenda { padding:.75rem 1.1rem 1rem; font-size:13.5px; line-height:1.7; color:var(--text-main); white-space:pre-wrap; }
.ms-doc-list { display:flex; flex-direction:column; gap:.5rem; padding:.75rem 1rem 1rem; }
.ms-doc-btn { min-height:42px; border-radius:10px; border:1.5px solid var(--border-light,#ede8e0); background:#fff; color:var(--text-main); font-weight:600; font-size:13px; text-align:center; padding:.65rem 1rem; text-decoration:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:.4rem; transition:all .15s; }
.ms-doc-btn:hover { background:#faf4eb; border-color:rgba(123,28,28,.25); color:#7B1C1C; }
.ms-doc-btn--primary { background:#7B1C1C; color:#fff; border-color:#7B1C1C; }
.ms-doc-btn--primary:hover { background:#5e1616; color:#fff; }

/* ── Main tabs ── */
.ms-tabs { display:flex; gap:.25rem; padding:.85rem .95rem 0; background:#faf6ef; border-bottom:1px solid var(--border-light,#ede8e0); }
.ms-tab { background:transparent; border:0; border-top-left-radius:10px; border-top-right-radius:10px; padding:.75rem 1rem; font-size:13px; font-weight:700; color:var(--text-muted,#8c7a6b); cursor:pointer; transition:all .15s; display:inline-flex; align-items:center; gap:.35rem; }
.ms-tab:hover { color:#7B1C1C; background:rgba(123,28,28,.04); }
.ms-tab.active { background:#fff; color:#7B1C1C; box-shadow:0 -1px 0 #fff, 0 0 0 1px var(--border-light,#ede8e0); }
.ms-tab-count { display:inline-flex; align-items:center; justify-content:center; min-width:20px; padding:.1rem .4rem; border-radius:999px; background:rgba(123,28,28,.08); font-size:11px; color:#7B1C1C; }
.ms-tab-panel { display:none; }
.ms-tab-panel.active { display:block; }
.ms-tab-toolbar { padding:.85rem 1.1rem; border-bottom:1px solid var(--border-light,#ede8e0); }

/* ── Table additions ── */
.ms-overdue-row td { background:rgba(192,57,43,.02); }
.ms-overdue-badge { display:inline-flex; margin-top:.3rem; padding:.18rem .5rem; border-radius:999px; background:rgba(192,57,43,.1); color:#a82515; font-size:11px; font-weight:700; }
.ms-overdue-text { color:#a82515; font-weight:700; font-size:13px; }
.ms-avatar { width:30px; height:30px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:800; flex-shrink:0; }
.ms-pic { display:flex; align-items:center; gap:.55rem; }

/* ── Status badge additions ── */
.mi-gray { background:rgba(0,0,0,.06); color:#5a5a5a; }

/* ── Responsive ── */
@media (max-width:1199px) { .ms-grid { grid-template-columns:240px 1fr; } }
@media (max-width:991px) {
  .ms-grid { grid-template-columns:1fr; }
  .ms-sidebar { order:2; }
  .ms-main { order:1; }
  .mi-stats-detail { grid-template-columns:1fr 1fr; }
}
@media (max-width:767px) {
  .mi-hero { padding:1rem; }
  .mi-hero-title { font-size:1.1rem; }
  .mi-hero-actions { width:100%; }
  .mi-info-sep { display:none; }
  .mi-info-strip { flex-direction:column; align-items:flex-start; }
  .mi-stats-detail { grid-template-columns:1fr 1fr; }
  .mi-table thead { display:none; }
  .mi-table, .mi-table tbody, .mi-table tr, .mi-table td { display:block; width:100%; }
  .mi-table tr { padding:.5rem 0; }
  .mi-table td { border-bottom:0; padding:.3rem 1rem; }
  .mi-table td:last-child { padding-bottom:.75rem; }
}
</style>
