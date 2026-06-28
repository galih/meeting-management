<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$statusBadge = [
  'scheduled' => 'blue',
  'ongoing'   => 'amber',
  'done'      => 'green',
  'cancelled' => 'red',
];
$statusIcon = [
  'scheduled' => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
  'ongoing'   => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="8 5 19 12 8 19 8 5"/></svg>',
  'done'      => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6 9 17l-5-5"/></svg>',
  'cancelled' => '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6 6 18M6 6l12 12"/></svg>',
];
$pStatusBadge = ['accepted'=>'green','invited'=>'blue','declined'=>'red','attended'=>'teal','pending'=>'secondary'];
$pStatusLabel = ['accepted'=>'Diterima','invited'=>'Diundang','declined'=>'Ditolak','attended'=>'Hadir','pending'=>'Menunggu'];
$prioBadge    = ['high'=>'red','medium'=>'amber','low'=>'green'];
$prioLabel    = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'];
$tlsBadge     = ['pending'=>'secondary','in_progress'=>'blue','done'=>'green','cancelled'=>'red'];
$tlsLabel     = ['pending'=>'Menunggu','in_progress'=>'Berlangsung','done'=>'Selesai','cancelled'=>'Dibatalkan'];

$canEdit          = $canEdit ?? false;
$participants     = $participants ?? [];
$tindakLanjutList = $tindakLanjutList ?? [];

$mStatus     = $meeting['status'] ?? 'scheduled';
$mLabel      = $statusLabel[$mStatus] ?? ucfirst($mStatus);
$mBadge      = $statusBadge[$mStatus] ?? 'secondary';
$mIcon       = $statusIcon[$mStatus] ?? '';
$loc         = trim($meeting['location'] ?? '');
$isLink      = $loc && (str_starts_with($loc, 'http://') || str_starts_with($loc, 'https://'));
$brand       = htmlspecialchars($meeting['color'] ?? '#C9A227');

$totalPeserta = count($participants);
$totalTL      = count($tindakLanjutList);
$doneTL       = count(array_filter($tindakLanjutList, fn($t) => ($t['status'] ?? '') === 'done'));
$today        = date('Y-m-d');
$overdueTL    = count(array_filter($tindakLanjutList, fn($t) => !empty($t['due_date']) && $t['due_date'] < $today && !in_array($t['status'] ?? '', ['done','cancelled'])));
$progressPct  = $totalTL > 0 ? round(($doneTL / $totalTL) * 100) : 0;
$csrfToken    = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$avPalette    = ['#6B3D1E','#8C5A2B','#B07A36','#C9A227','#9F1D20','#205375','#3A5A40'];
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="km-toast" id="kmToast" role="alert" aria-live="polite">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 6 9 17l-5-5"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="km-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="km-toast km-toast--error" id="kmToastErr" role="alert" aria-live="polite">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="km-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="km-show" style="--meeting-accent: <?= $brand ?>;">
  <section class="km-hero mb-4">
    <div class="km-hero__pattern"></div>
    <div class="km-hero__body">
      <nav class="km-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
        <span>/</span>
        <span>Detail Kegiatan</span>
      </nav>

      <div class="km-hero__top">
        <div class="km-hero__content">
          <div class="km-eyebrow">Portal Kegiatan</div>
          <h1 class="km-title"><?= htmlspecialchars($meeting['title']) ?></h1>
          <div class="km-badges">
            <span class="km-badge km-badge--<?= $mBadge ?>"><?= $mIcon ?><?= $mLabel ?></span>
            <?php if (!empty($meeting['dept_name'])): ?>
              <span class="km-badge km-badge--soft"><?= htmlspecialchars($meeting['dept_name']) ?></span>
            <?php endif; ?>
            <span class="km-badge km-badge--soft">PIC: <?= htmlspecialchars($meeting['creator_name'] ?? '-') ?></span>
          </div>
        </div>

        <div class="km-actions">
          <?php if ($canEdit): ?>
            <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/edit" class="btn km-btn-primary">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"/></svg>
              Edit Kegiatan
            </a>
          <?php endif; ?>
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
            <button type="button" class="btn km-btn-secondary" data-bs-toggle="modal" data-bs-target="#modalStatus">Ubah Status</button>
          <?php endif; ?>
          <?php if (Auth::hasRole('admin')): ?>
            <button type="button" class="btn km-btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus">Hapus</button>
          <?php endif; ?>
        </div>
      </div>

      <div class="km-meta-grid">
        <div class="km-meta-card">
          <span class="km-meta-label">Waktu Mulai</span>
          <strong><?= date('d M Y, H:i', strtotime($meeting['start_datetime'])) ?></strong>
        </div>
        <div class="km-meta-card">
          <span class="km-meta-label">Waktu Selesai</span>
          <strong><?= date('d M Y, H:i', strtotime($meeting['end_datetime'])) ?></strong>
        </div>
        <div class="km-meta-card km-meta-card--wide">
          <span class="km-meta-label">Lokasi</span>
          <?php if ($loc): ?>
            <?php if ($isLink): ?>
              <a href="<?= htmlspecialchars($loc) ?>" target="_blank" rel="noopener noreferrer" class="km-location-link">Buka tautan rapat</a>
            <?php else: ?>
              <strong><?= htmlspecialchars($loc) ?></strong>
            <?php endif; ?>
          <?php else: ?>
            <strong>Belum ditentukan</strong>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <div class="row g-4">
    <div class="col-xl-4 col-lg-5">
      <div class="km-side-stack">
        <section class="km-panel km-panel--stats">
          <div class="km-panel__head">
            <h2>Ringkasan</h2>
            <span>Statistik utama</span>
          </div>
          <div class="km-stats-grid">
            <article class="km-stat-card">
              <span class="km-stat-icon km-stat-icon--brown">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
              </span>
              <div>
                <strong><?= $totalPeserta ?></strong>
                <span>Peserta</span>
              </div>
            </article>
            <article class="km-stat-card">
              <span class="km-stat-icon km-stat-icon--gold">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              </span>
              <div>
                <strong><?= $totalTL ?></strong>
                <span>Tindak lanjut</span>
              </div>
            </article>
            <article class="km-stat-card">
              <span class="km-stat-icon km-stat-icon--green">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
              </span>
              <div>
                <strong><?= $doneTL ?></strong>
                <span>Selesai</span>
              </div>
            </article>
            <article class="km-stat-card">
              <span class="km-stat-icon km-stat-icon--red">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
              </span>
              <div>
                <strong><?= $overdueTL ?></strong>
                <span>Terlambat</span>
              </div>
            </article>
          </div>
        </section>

        <?php if ($totalTL > 0): ?>
        <section class="km-panel">
          <div class="km-panel__head">
            <h2>Progres Pekerjaan</h2>
            <span><?= $doneTL ?> dari <?= $totalTL ?> selesai</span>
          </div>
          <div class="km-progress-wrap">
            <div class="km-progress-top">
              <strong><?= $progressPct ?>%</strong>
              <span>Progress keseluruhan</span>
            </div>
            <div class="km-progress-bar" role="progressbar" aria-valuenow="<?= $progressPct ?>" aria-valuemin="0" aria-valuemax="100">
              <div class="km-progress-fill" style="width: <?= $progressPct ?>%"></div>
            </div>
          </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($meeting['description'])): ?>
        <section class="km-panel">
          <div class="km-panel__head">
            <h2>Agenda Kegiatan</h2>
            <span>Deskripsi singkat</span>
          </div>
          <div class="km-agenda"><?= nl2br(htmlspecialchars($meeting['description'])) ?></div>
        </section>
        <?php endif; ?>

        <section class="km-panel">
          <div class="km-panel__head">
            <h2>Dokumen</h2>
            <span>Aksi cepat</span>
          </div>
          <div class="km-docs">
            <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>" class="km-doc-btn km-doc-btn--primary">Buka Notulen</a>
            <a href="<?= $baseUrl ?>/notulen/<?= (int)$meeting['id'] ?>/export-docx" class="km-doc-btn">Export DOCX</a>
            <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
              <button type="button" class="km-doc-btn" id="btnKirimUndangan">Kirim Undangan</button>
              <?php if (($meeting['status'] ?? '') === 'done'): ?>
                <button type="button" class="km-doc-btn" id="btnKirimRingkasan">Kirim Ringkasan</button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </div>

    <div class="col-xl-8 col-lg-7">
      <section class="km-panel km-panel--main">
        <div class="km-tabs" role="tablist">
          <button type="button" class="km-tab active" data-tab="tl" role="tab" aria-selected="true" aria-controls="km-panel-tl" id="km-tab-tl">Tindak Lanjut <span><?= $totalTL ?></span></button>
          <button type="button" class="km-tab" data-tab="peserta" role="tab" aria-selected="false" aria-controls="km-panel-peserta" id="km-tab-peserta">Peserta <span><?= $totalPeserta ?></span></button>
        </div>

        <div id="km-panel-tl" class="km-tab-panel active" role="tabpanel" aria-labelledby="km-tab-tl">
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
          <div class="km-panel-toolbar">
            <button type="button" class="km-toolbar-btn" data-bs-toggle="modal" data-bs-target="#modalTambahTL">Tambah Tindak Lanjut</button>
          </div>
          <?php endif; ?>

          <?php if (empty($tindakLanjutList)): ?>
            <div class="km-empty-state">
              <div class="km-empty-icon">✓</div>
              <p>Belum ada tindak lanjut untuk kegiatan ini.</p>
            </div>
          <?php else: ?>
            <div class="table-responsive">
              <table class="km-table" aria-label="Daftar tindak lanjut">
                <thead>
                  <tr>
                    <th>Tugas</th>
                    <th>PIC</th>
                    <th>Deadline</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($tindakLanjutList as $tl):
                    $isOver = !empty($tl['due_date']) && $tl['due_date'] < $today && !in_array($tl['status'] ?? '', ['done','cancelled']);
                    $pBadge = $prioBadge[$tl['priority'] ?? ''] ?? 'secondary';
                    $pLbl   = $prioLabel[$tl['priority'] ?? ''] ?? ucfirst($tl['priority'] ?? '-');
                    $sBadge = $tlsBadge[$tl['status'] ?? ''] ?? 'secondary';
                    $sLbl   = $tlsLabel[$tl['status'] ?? ''] ?? ucfirst($tl['status'] ?? '-');
                    $picName = $tl['assigned_name'] ?? '';
                    $avBg = $picName ? $avPalette[abs(crc32($picName)) % count($avPalette)] : '#8C8C8C';
                  ?>
                  <tr class="<?= $isOver ? 'is-overdue' : '' ?>">
                    <td>
                      <div class="km-task-title"><?= htmlspecialchars($tl['description']) ?></div>
                      <?php if ($isOver): ?><span class="km-inline-badge">Terlambat</span><?php endif; ?>
                    </td>
                    <td>
                      <?php if ($picName): ?>
                        <div class="km-pic">
                          <span class="km-avatar" style="background: <?= $avBg ?>;">
                            <?= strtoupper(mb_substr($picName, 0, 1)) ?>
                          </span>
                          <span><?= htmlspecialchars($picName) ?></span>
                        </div>
                      <?php else: ?>
                        <span class="km-muted">—</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <span class="<?= $isOver ? 'km-overdue-text' : 'km-muted' ?>">
                        <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
                      </span>
                    </td>
                    <td><span class="km-status km-status--<?= $pBadge ?>"><?= $pLbl ?></span></td>
                    <td><span class="km-status km-status--<?= $sBadge ?>"><?= $sLbl ?></span></td>
                    <td><a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="km-detail-link">Detail</a></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <div id="km-panel-peserta" class="km-tab-panel" role="tabpanel" aria-labelledby="km-tab-peserta">
          <?php if (empty($participants)): ?>
            <div class="km-empty-state">
              <div class="km-empty-icon">👥</div>
              <p>Belum ada peserta terdaftar.</p>
            </div>
          <?php else: ?>
            <div class="km-participants">
              <?php foreach ($participants as $p):
                $psBadge = $pStatusBadge[$p['status'] ?? ''] ?? 'secondary';
                $psLbl   = $pStatusLabel[$p['status'] ?? ''] ?? ucfirst($p['status'] ?? '-');
                $avBg    = $avPalette[abs(crc32($p['name'])) % count($avPalette)];
              ?>
              <article class="km-person-card">
                <span class="km-avatar km-avatar--lg" style="background: <?= $avBg ?>;">
                  <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
                </span>
                <div class="km-person-body">
                  <div class="km-person-name"><?= htmlspecialchars($p['name']) ?></div>
                  <?php if (!empty($p['position'])): ?>
                    <div class="km-person-position"><?= htmlspecialchars($p['position']) ?></div>
                  <?php endif; ?>
                </div>
                <span class="km-status km-status--<?= $psBadge ?>"><?= $psLbl ?></span>
              </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<?php if (Auth::hasRole('admin', 'sekretaris')): ?>
<div class="modal modal-blur fade" id="modalStatus" tabindex="-1" aria-labelledby="lblModalStatus" aria-hidden="true">
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
              <option value="<?= $val ?>" <?= ($meeting['status'] ?? '') === $val ? 'selected' : '' ?>><?= htmlspecialchars($lbl) ?></option>
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

<div class="modal modal-blur fade" id="modalTambahTL" tabindex="-1" aria-labelledby="lblModalTL" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="lblModalTL">Tambah Tindak Lanjut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required" for="tlDesc">Deskripsi Tugas</label>
          <textarea id="tlDesc" class="form-control" rows="3" placeholder="Contoh: Susun laporan evaluasi semester&hellip;"></textarea>
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
              <input type="radio" name="tlPriority" class="form-check-input" value="<?= $v ?>" <?= $v === 'medium' ? 'checked' : '' ?>>
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
<?php endif; ?>

<?php if (Auth::hasRole('admin')): ?>
<div class="modal modal-blur fade" id="modalHapus" tabindex="-1" aria-labelledby="lblModalHapus" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center py-4">
        <div class="km-delete-icon mb-3">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="m19 6-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/></svg>
        </div>
        <h5 class="mb-2" id="lblModalHapus">Hapus Kegiatan?</h5>
        <p class="text-muted mb-0" style="font-size:13px;">Kegiatan <strong style="color:#6B3D1E;"><?= htmlspecialchars($meeting['title']) ?></strong> akan dihapus permanen beserta notulen, peserta, dan tindak lanjut.</p>
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

<script>
(function () {
  'use strict';

  var BASE   = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
  var MTG_ID = <?= (int)$meeting['id'] ?>;
  var CSRF   = <?= json_encode($csrfToken) ?>;

  document.querySelectorAll('.km-tab').forEach(function (btn) {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.km-tab').forEach(function (b) {
        b.classList.remove('active');
        b.setAttribute('aria-selected', 'false');
      });
      document.querySelectorAll('.km-tab-panel').forEach(function (p) {
        p.classList.remove('active');
      });
      btn.classList.add('active');
      btn.setAttribute('aria-selected', 'true');
      var panel = document.getElementById('km-panel-' + btn.dataset.tab);
      if (panel) panel.classList.add('active');
    });
  });

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
        else {
          kmToast(d.message || 'Gagal menyimpan.', 'error');
          btnSaveTL.disabled = false;
          btnSaveTL.innerHTML = orig;
        }
      })
      .catch(function (err) {
        kmToast('Terjadi kesalahan: ' + err.message, 'error');
        btnSaveTL.disabled = false;
        btnSaveTL.innerHTML = orig;
      });
    });
  }

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
      .then(function (d) { kmToast(d.message || 'Undangan terkirim.', 'success'); })
      .catch(function () { kmToast('Gagal mengirim undangan.', 'error'); })
      .finally(function () {
        btnInv.disabled = false;
        btnInv.innerHTML = orig;
      });
    });
  }

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
      .then(function (d) { kmToast(d.message || 'Ringkasan terkirim.', 'success'); })
      .catch(function () { kmToast('Gagal mengirim ringkasan.', 'error'); })
      .finally(function () {
        btnSum.disabled = false;
        btnSum.innerHTML = orig;
      });
    });
  }

  function kmToast(msg, type) {
    var t = document.createElement('div');
    t.className = 'km-toast' + (type === 'error' ? ' km-toast--error' : '');
    t.setAttribute('role', 'alert');
    t.setAttribute('aria-live', 'polite');
    t.innerHTML = msg + '<button class="km-toast-close" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>';
    document.body.appendChild(t);
    setTimeout(function () {
      t.style.opacity = '0';
      setTimeout(function () { if (t.parentElement) t.remove(); }, 350);
    }, 4200);
  }

  ['kmToast', 'kmToastErr'].forEach(function (id) {
    var el = document.getElementById(id);
    if (!el) return;
    setTimeout(function () {
      el.style.opacity = '0';
      setTimeout(function () { if (el.parentElement) el.remove(); }, 350);
    }, 4200);
  });
}());
</script>

<style>
:root {
  --km-gold: #C9A227;
  --km-gold-soft: #E5C96D;
  --km-brown: #6B3D1E;
  --km-brown-deep: #4A2912;
  --km-cream: #FDF8F0;
  --km-cream-strong: #F4E9D8;
  --km-red: #A63A2B;
  --km-green: #2E6B4A;
  --km-blue: #315C8D;
  --km-text: #3C2B1F;
  --km-muted: #7A685A;
  --km-border: #E4D6C2;
}

.km-show {
  --bg-card: #ffffff;
  --border-light: var(--km-border);
  --text-main: var(--km-text);
  --text-muted: var(--km-muted);
}

.km-toast {
  position: fixed;
  top: 1rem;
  right: 1rem;
  z-index: 1090;
  display: flex;
  align-items: center;
  gap: .5rem;
  max-width: 340px;
  padding: .7rem 1rem;
  border-radius: 12px;
  background: var(--km-brown);
  color: #fff;
  box-shadow: 0 14px 30px rgba(74, 41, 18, .22);
  transition: opacity .35s ease;
}
.km-toast--error { background: var(--km-red); }
.km-toast-close {
  margin-left: auto;
  border: 0;
  background: transparent;
  color: inherit;
  font-size: 1.1rem;
  cursor: pointer;
}

.km-hero {
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(201,162,39,.25);
  border-radius: 24px;
  background: linear-gradient(135deg, var(--km-brown-deep) 0%, var(--km-brown) 52%, #8B542B 100%);
  box-shadow: 0 24px 48px rgba(74, 41, 18, .18);
}
.km-hero__pattern {
  position: absolute;
  inset: 0;
  background:
    radial-gradient(circle at 100% 0%, rgba(229,201,109,.25), transparent 34%),
    linear-gradient(135deg, transparent 0 24%, rgba(255,255,255,.05) 24% 26%, transparent 26% 50%, rgba(255,255,255,.05) 50% 52%, transparent 52%);
  opacity: .7;
  pointer-events: none;
}
.km-hero__body {
  position: relative;
  padding: 1.5rem;
}
.km-breadcrumb {
  display: flex;
  align-items: center;
  gap: .45rem;
  margin-bottom: .8rem;
  color: rgba(255,255,255,.7);
  font-size: 12px;
}
.km-breadcrumb a {
  color: rgba(255,255,255,.82);
  text-decoration: none;
}
.km-breadcrumb a:hover { color: var(--km-gold-soft); }
.km-eyebrow {
  display: inline-flex;
  align-items: center;
  padding: .35rem .75rem;
  border: 1px solid rgba(229,201,109,.28);
  border-radius: 999px;
  background: rgba(255,255,255,.08);
  color: var(--km-gold-soft);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .12em;
  font-weight: 700;
  margin-bottom: .9rem;
}
.km-hero__top {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.km-title {
  margin: 0;
  color: #fff;
  font-size: clamp(1.5rem, 2.5vw, 2.2rem);
  line-height: 1.15;
  font-weight: 800;
}
.km-badges {
  display: flex;
  flex-wrap: wrap;
  gap: .5rem;
  margin-top: .85rem;
}
.km-badge {
  display: inline-flex;
  align-items: center;
  gap: .35rem;
  padding: .42rem .75rem;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}
.km-badge--soft {
  color: #fff;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.12);
}
.km-badge--amber { background: rgba(229,201,109,.18); color: #FFF1BE; }
.km-badge--blue { background: rgba(96,146,215,.18); color: #D8E7FF; }
.km-badge--green { background: rgba(84,175,120,.18); color: #DDF6E5; }
.km-badge--red { background: rgba(237,120,102,.16); color: #FFD6D0; }
.km-badge--secondary { background: rgba(255,255,255,.12); color: #fff; }
.km-actions {
  display: flex;
  gap: .65rem;
  flex-wrap: wrap;
  align-items: flex-start;
}
.km-btn-primary,
.km-btn-secondary,
.km-btn-danger {
  min-height: 42px;
  border-radius: 12px;
  padding: .72rem 1rem;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: .45rem;
  text-decoration: none;
  border: 0;
}
.km-btn-primary {
  background: var(--km-gold);
  color: var(--km-brown-deep);
}
.km-btn-primary:hover { background: var(--km-gold-soft); color: var(--km-brown-deep); }
.km-btn-secondary {
  background: rgba(255,255,255,.12);
  color: #fff;
  border: 1px solid rgba(255,255,255,.18);
}
.km-btn-secondary:hover { background: rgba(255,255,255,.18); color: #fff; }
.km-btn-danger {
  background: rgba(166,58,43,.18);
  color: #FFD4CC;
  border: 1px solid rgba(255,212,204,.14);
}
.km-btn-danger:hover { background: rgba(166,58,43,.3); color: #fff; }
.km-meta-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .9rem;
  margin-top: 1.2rem;
}
.km-meta-card {
  padding: 1rem 1rem 1.05rem;
  border-radius: 18px;
  background: rgba(255,255,255,.08);
  border: 1px solid rgba(255,255,255,.12);
  color: #fff;
  backdrop-filter: blur(8px);
}
.km-meta-card--wide { grid-column: span 1; }
.km-meta-label {
  display: block;
  margin-bottom: .45rem;
  color: rgba(255,255,255,.72);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .12em;
  font-weight: 700;
}
.km-location-link {
  color: var(--km-gold-soft);
  text-decoration: underline;
  font-weight: 700;
}
.km-location-link:hover { color: #fff; }

.km-side-stack {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}
.km-panel {
  background: var(--bg-card);
  border: 1px solid var(--border-light);
  border-radius: 22px;
  box-shadow: 0 10px 30px rgba(74, 41, 18, .06);
  overflow: hidden;
}
.km-panel--main { min-height: 100%; }
.km-panel__head {
  padding: 1rem 1.15rem;
  border-bottom: 1px solid var(--border-light);
  background: linear-gradient(180deg, #FFFDFC 0%, var(--km-cream) 100%);
}
.km-panel__head h2 {
  margin: 0;
  color: var(--km-brown);
  font-size: 15px;
  font-weight: 800;
}
.km-panel__head span {
  display: inline-block;
  margin-top: .2rem;
  color: var(--text-muted);
  font-size: 12px;
}
.km-stats-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: .8rem;
  padding: 1rem 1.05rem 1.1rem;
}
.km-stat-card {
  display: flex;
  align-items: center;
  gap: .75rem;
  padding: .9rem;
  border-radius: 18px;
  background: var(--km-cream);
  border: 1px solid rgba(201,162,39,.12);
}
.km-stat-card strong {
  display: block;
  color: var(--text-main);
  font-size: 22px;
  line-height: 1;
}
.km-stat-card span:last-child {
  color: var(--text-muted);
  font-size: 12px;
}
.km-stat-icon {
  width: 42px;
  height: 42px;
  border-radius: 14px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.km-stat-icon--brown { background: rgba(107,61,30,.1); color: var(--km-brown); }
.km-stat-icon--gold { background: rgba(201,162,39,.14); color: #9A7312; }
.km-stat-icon--green { background: rgba(46,107,74,.12); color: var(--km-green); }
.km-stat-icon--red { background: rgba(166,58,43,.1); color: var(--km-red); }
.km-progress-wrap,
.km-agenda,
.km-docs { padding: 1rem 1.15rem 1.15rem; }
.km-progress-top {
  display: flex;
  justify-content: space-between;
  align-items: baseline;
  gap: .75rem;
  margin-bottom: .65rem;
}
.km-progress-top strong {
  color: var(--km-brown);
  font-size: 24px;
}
.km-progress-top span { color: var(--text-muted); font-size: 12px; }
.km-progress-bar {
  height: 10px;
  background: var(--km-cream-strong);
  border-radius: 999px;
  overflow: hidden;
}
.km-progress-fill {
  height: 100%;
  border-radius: 999px;
  background: linear-gradient(90deg, var(--km-brown) 0%, var(--km-gold) 100%);
}
.km-agenda {
  color: var(--text-main);
  font-size: 13.5px;
  line-height: 1.7;
  white-space: pre-wrap;
}
.km-docs {
  display: flex;
  flex-direction: column;
  gap: .65rem;
}
.km-doc-btn {
  min-height: 44px;
  border-radius: 14px;
  border: 1px solid var(--border-light);
  background: #fff;
  color: var(--text-main);
  font-weight: 700;
  font-size: 13px;
  text-align: center;
  padding: .75rem 1rem;
  text-decoration: none;
}
.km-doc-btn:hover {
  color: var(--km-brown);
  background: var(--km-cream);
  border-color: rgba(201,162,39,.42);
}
.km-doc-btn--primary {
  background: var(--km-brown);
  color: #fff;
  border-color: var(--km-brown);
}
.km-doc-btn--primary:hover { background: var(--km-brown-deep); color: #fff; }

.km-tabs {
  display: flex;
  gap: .35rem;
  padding: .9rem .95rem 0;
  background: linear-gradient(180deg, #FFFDFC 0%, var(--km-cream) 100%);
  border-bottom: 1px solid var(--border-light);
}
.km-tab {
  border: 0;
  background: transparent;
  color: var(--text-muted);
  padding: .8rem 1rem;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  font-size: 13px;
  font-weight: 700;
}
.km-tab span {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 22px;
  margin-left: .35rem;
  padding: .1rem .4rem;
  border-radius: 999px;
  background: rgba(107,61,30,.08);
  font-size: 11px;
}
.km-tab.active {
  background: #fff;
  color: var(--km-brown);
  box-shadow: 0 -1px 0 #fff, 0 0 0 1px var(--border-light);
}
.km-tab-panel { display: none; }
.km-tab-panel.active { display: block; }
.km-panel-toolbar {
  padding: 1rem 1.1rem;
  border-bottom: 1px solid var(--border-light);
}
.km-toolbar-btn {
  min-height: 42px;
  border: 0;
  border-radius: 12px;
  background: var(--km-brown);
  color: #fff;
  padding: .72rem 1rem;
  font-size: 13px;
  font-weight: 700;
}
.km-toolbar-btn:hover { background: var(--km-brown-deep); }
.km-empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: .75rem;
  padding: 3.25rem 1.5rem;
  color: var(--text-muted);
  text-align: center;
}
.km-empty-icon {
  width: 62px;
  height: 62px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: var(--km-cream);
  color: var(--km-brown);
  font-size: 24px;
}
.km-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}
.km-table thead th {
  padding: .85rem 1rem;
  background: var(--km-cream);
  color: var(--km-brown);
  text-align: left;
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .08em;
  border-bottom: 1px solid var(--border-light);
}
.km-table tbody td {
  padding: .95rem 1rem;
  border-bottom: 1px solid #F1E7D8;
  vertical-align: middle;
}
.km-table tbody tr:hover { background: #FFFCF8; }
.km-table tbody tr.is-overdue { background: rgba(166,58,43,.03); }
.km-task-title {
  color: var(--text-main);
  font-weight: 600;
  line-height: 1.5;
}
.km-inline-badge {
  display: inline-flex;
  margin-top: .4rem;
  padding: .2rem .5rem;
  border-radius: 999px;
  background: rgba(166,58,43,.1);
  color: var(--km-red);
  font-size: 11px;
  font-weight: 700;
}
.km-pic {
  display: flex;
  align-items: center;
  gap: .5rem;
}
.km-avatar {
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-size: 12px;
  font-weight: 800;
}
.km-avatar--lg {
  width: 44px;
  height: 44px;
  font-size: 16px;
}
.km-muted { color: var(--text-muted); }
.km-overdue-text {
  color: var(--km-red);
  font-weight: 700;
}
.km-status {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: .35rem .62rem;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 700;
}
.km-status--secondary { background: #F1ECE4; color: #77695D; }
.km-status--red { background: rgba(166,58,43,.1); color: var(--km-red); }
.km-status--amber { background: rgba(201,162,39,.16); color: #9A7312; }
.km-status--green { background: rgba(46,107,74,.12); color: var(--km-green); }
.km-status--blue { background: rgba(49,92,141,.1); color: var(--km-blue); }
.km-status--teal { background: rgba(32,83,117,.1); color: #205375; }
.km-detail-link {
  color: var(--km-brown);
  text-decoration: none;
  font-weight: 700;
  font-size: 12px;
}
.km-detail-link:hover { color: var(--km-gold); }
.km-participants {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: .95rem;
  padding: 1rem;
}
.km-person-card {
  display: flex;
  align-items: center;
  gap: .8rem;
  padding: .95rem;
  border-radius: 18px;
  background: var(--km-cream);
  border: 1px solid rgba(201,162,39,.12);
}
.km-person-body {
  flex: 1;
  min-width: 0;
}
.km-person-name {
  color: var(--text-main);
  font-size: 13px;
  font-weight: 700;
}
.km-person-position {
  color: var(--text-muted);
  font-size: 11.5px;
  margin-top: .15rem;
}
.km-delete-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto;
  border-radius: 50%;
  background: rgba(166,58,43,.08);
  color: var(--km-red);
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 991.98px) {
  .km-meta-grid { grid-template-columns: 1fr; }
  .km-participants { grid-template-columns: 1fr; }
}

@media (max-width: 767.98px) {
  .km-hero__body { padding: 1.1rem; }
  .km-title { font-size: 1.35rem; }
  .km-stats-grid { grid-template-columns: 1fr; }
  .km-tabs { overflow-x: auto; }
  .km-tab { white-space: nowrap; }
  .km-table thead { display: none; }
  .km-table,
  .km-table tbody,
  .km-table tr,
  .km-table td { display: block; width: 100%; }
  .km-table tr { padding: .65rem 0; }
  .km-table td {
    border-bottom: 0;
    padding: .35rem 1rem;
  }
  .km-table td:last-child { padding-bottom: .85rem; }
}
</style>
