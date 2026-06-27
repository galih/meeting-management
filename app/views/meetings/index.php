<?php
$baseUrl  = rtrim(BASE_URL, '/');
$isAdmin  = Auth::hasRole('admin');
$allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
$meetingStatusColor = [
  'scheduled' => 'blue',
  'ongoing'   => 'orange',
  'done'      => 'green',
  'cancelled' => 'red',
];
$meetingStatusIcon = [
  'scheduled' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
  'ongoing'   => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="5 3 19 12 5 21 5 3"/></svg>',
  'done'      => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>',
  'cancelled' => '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
];

$statusCount = ['scheduled'=>0,'ongoing'=>0,'done'=>0,'cancelled'=>0];
foreach ($meetings as $m) {
  $s = $m['status'] ?? 'scheduled';
  if (isset($statusCount[$s])) $statusCount[$s]++;
}
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible mb-3">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- PAGE HEADER -->
<div class="meetings-page-header mb-4">
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
      <h1 class="meetings-page-title">
        <span class="meetings-page-title-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </span>
        Manajemen Kegiatan
      </h1>
      <p class="meetings-page-subtitle">Kelola jadwal, peserta, dan agenda kegiatan instansi</p>
    </div>
    <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
    <button class="btn btn-create-meeting" data-bs-toggle="modal" data-bs-target="#modalMeeting">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Buat Kegiatan Baru
    </button>
    <?php endif; ?>
  </div>

  <!-- Stat Strip -->
  <div class="meetings-stat-strip mt-3">
    <div class="meetings-stat-item">
      <span class="meetings-stat-value"><?= count($meetings) ?></span>
      <span class="meetings-stat-label">Total</span>
    </div>
    <div class="meetings-stat-sep"></div>
    <div class="meetings-stat-item meetings-stat-blue">
      <span class="meetings-stat-value"><?= $statusCount['scheduled'] ?></span>
      <span class="meetings-stat-label">Terjadwal</span>
    </div>
    <div class="meetings-stat-sep"></div>
    <div class="meetings-stat-item meetings-stat-orange">
      <span class="meetings-stat-value"><?= $statusCount['ongoing'] ?></span>
      <span class="meetings-stat-label">Berlangsung</span>
    </div>
    <div class="meetings-stat-sep"></div>
    <div class="meetings-stat-item meetings-stat-green">
      <span class="meetings-stat-value"><?= $statusCount['done'] ?></span>
      <span class="meetings-stat-label">Selesai</span>
    </div>
    <div class="meetings-stat-sep"></div>
    <div class="meetings-stat-item meetings-stat-red">
      <span class="meetings-stat-value"><?= $statusCount['cancelled'] ?></span>
      <span class="meetings-stat-label">Dibatalkan</span>
    </div>
  </div>
</div>

<!-- MAIN CARD -->
<div class="card meetings-main-card">
  <div class="card-header meetings-card-header">
    <div class="d-flex align-items-center gap-2 flex-wrap w-100">
      <!-- View Tabs -->
      <div class="meetings-view-tabs">
        <button class="meetings-tab-btn active" data-view="calendar">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Kalender
        </button>
        <button class="meetings-tab-btn" data-view="list">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1"/><circle cx="3" cy="12" r="1"/><circle cx="3" cy="18" r="1"/></svg>
          Daftar
        </button>
      </div>
      <!-- Search -->
      <div class="meetings-search-wrap ms-auto" id="list-search-wrap" style="display:none">
        <svg xmlns="http://www.w3.org/2000/svg" class="meetings-search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="meetings-search" class="meetings-search-input" placeholder="Cari kegiatan...">
      </div>
      <!-- Filter -->
      <div id="list-filter-wrap" style="display:none">
        <select id="meetings-status-filter" class="meetings-filter-select">
          <option value="">Semua Status</option>
          <option value="scheduled">Terjadwal</option>
          <option value="ongoing">Berlangsung</option>
          <option value="done">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </div>
    </div>
  </div>

  <div class="card-body p-0">
    <!-- Calendar -->
    <div id="view-calendar" class="p-3">
      <div id="calendar" style="min-height:620px;"></div>
    </div>

    <!-- List -->
    <div id="view-list" style="display:none;">
      <?php if (empty($meetings)): ?>
      <div class="meetings-empty">
        <div class="meetings-empty-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="8" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="13" y2="18"/></svg>
        </div>
        <h3 class="meetings-empty-title">Belum ada kegiatan</h3>
        <p class="meetings-empty-desc">Buat kegiatan pertama untuk memulai penjadwalan</p>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMeeting">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Buat Kegiatan
        </button>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table meetings-table" id="meetings-table">
          <thead>
            <tr>
              <th>Kegiatan</th>
              <th>Waktu</th>
              <th>Lokasi</th>
              <th>Peserta</th>
              <th>Status</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($meetings as $m):
              $sc  = $meetingStatusColor[$m['status']] ?? 'secondary';
              $ico = $meetingStatusIcon[$m['status']] ?? '';
              $lbl = $statusLabel[$m['status']] ?? ucfirst($m['status']);
              $startDate = date('d M Y', strtotime($m['start_datetime']));
              $startTime = date('H:i', strtotime($m['start_datetime']));
              $endDate   = date('d M Y', strtotime($m['end_datetime']));
              $endTime   = date('H:i', strtotime($m['end_datetime']));
              $sameDay   = $startDate === $endDate;
            ?>
            <tr class="meetings-row" data-status="<?= $m['status'] ?>" data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>">
              <td class="meetings-col-title">
                <div class="meetings-title-block">
                  <span class="meetings-color-dot" style="background:<?= htmlspecialchars($m['color'] ?? '#7B1C1C') ?>"></span>
                  <div>
                    <div class="fw-semibold meetings-title-text"><?= htmlspecialchars($m['title']) ?></div>
                    <div class="meetings-creator">oleh <?= htmlspecialchars($m['creator_name'] ?? '-') ?></div>
                  </div>
                </div>
              </td>
              <td class="meetings-col-time">
                <div class="meetings-time-block">
                  <span class="meetings-date"><?= $startDate ?></span>
                  <span class="meetings-time"><?= $startTime ?>
                    <?php if ($sameDay): ?>– <?= $endTime ?>
                    <?php else: ?>– <?= $endDate ?> <?= $endTime ?><?php endif; ?>
                  </span>
                </div>
              </td>
              <td class="meetings-col-loc">
                <?php if (!empty($m['location'])): ?>
                <div class="meetings-location">
                  <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                  <span><?= htmlspecialchars($m['location']) ?></span>
                </div>
                <?php else: ?><span class="text-muted">—</span><?php endif; ?>
              </td>
              <td class="meetings-col-peserta">
                <span class="meetings-badge-peserta">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  <?= (int)$m['total_peserta'] ?> orang
                </span>
              </td>
              <td class="meetings-col-status">
                <span class="meetings-status-badge meetings-status-<?= $sc ?>">
                  <?= $ico ?> <?= $lbl ?>
                </span>
              </td>
              <td class="meetings-col-action text-end">
                <div class="d-flex gap-1 justify-content-end">
                  <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>" class="btn btn-sm meetings-btn-detail">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Detail
                  </a>
                  <?php if ($isAdmin): ?>
                  <button type="button" class="btn btn-sm meetings-btn-delete"
                          title="Hapus Kegiatan"
                          onclick="confirmDeleteMeeting(<?= $m['id'] ?>, <?= htmlspecialchars(json_encode($m['title'])) ?>)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="meetings-table-footer">
        <span id="meetings-count-label">Menampilkan <?= count($meetings) ?> kegiatan</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal Hapus -->
<div class="modal modal-blur fade" id="modalDeleteMeeting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content meetings-modal-delete">
      <div class="modal-body text-center py-4">
        <div class="meetings-delete-icon-wrap mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="modal-title mb-1" style="color:var(--text-main)">Hapus Kegiatan?</h5>
        <p class="text-muted small mb-1">Kegiatan <strong id="deleteMeetingTitle" class="text-danger"></strong> akan dihapus permanen.</p>
        <p class="text-muted" style="font-size:11.5px">Semua peserta, tindak lanjut, dan notulen terkait ikut terhapus.</p>
      </div>
      <div class="modal-footer justify-content-center gap-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
        <form id="formDeleteMeeting" method="POST" action="" class="d-inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-sm btn-danger">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Buat Kegiatan -->
<div class="modal modal-blur fade" id="modalMeeting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="d-flex align-items-center gap-2">
            <span class="meetings-modal-header-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </span>
            <h5 class="modal-title">Buat Kegiatan Baru</h5>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Judul Kegiatan</label>
              <input type="text" name="title" class="form-control" required
                     placeholder="Contoh: Rapat Evaluasi Bulanan Q2">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Tanggal &amp; Jam Mulai</label>
              <input type="datetime-local" name="start_datetime" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Tanggal &amp; Jam Selesai</label>
              <input type="datetime-local" name="end_datetime" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Lokasi / Link Video</label>
              <input type="text" name="location" class="form-control"
                     placeholder="Ruang Rapat A / https://meet.google.com/...">
            </div>
            <!-- Unit Kerja Cascade -->
            <div class="col-12">
              <label class="form-label">Unit Kerja</label>
              <div class="row g-2">
                <div class="col-md-4">
                  <select id="mtg-u1" class="form-select" onchange="cascadeMtg(1)">
                    <option value="">-- Semua Unit Kerja --</option>
                    <?php foreach ($departments as $d): if ((int)($d['level'] ?? 1) !== 1) continue; ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Unit Kerja</div>
                </div>
                <div class="col-md-4">
                  <select id="mtg-u2" class="form-select" disabled onchange="cascadeMtg(2)">
                    <option value="">-- Pilih unit dulu --</option>
                  </select>
                  <div class="form-text">Bidang / Bagian</div>
                </div>
                <div class="col-md-4">
                  <select id="mtg-u3" class="form-select" disabled onchange="cascadeMtg(3)">
                    <option value="">-- Opsional --</option>
                  </select>
                  <div class="form-text">Sub Bidang / Sub Bagian</div>
                </div>
              </div>
              <input type="hidden" id="mtg-dept-id" name="department_id" value="">
            </div>
            <div class="col-md-6">
              <label class="form-label">Warna Kalender</label>
              <div class="d-flex align-items-center gap-2">
                <input type="color" name="color" id="mtg-color" class="form-control form-control-color" value="#7B1C1C">
                <span class="text-muted small">Warna marker di kalender</span>
              </div>
            </div>
            <div class="col-md-6">
              <label class="form-label">Peserta</label>
              <select name="participants[]" class="form-select" multiple size="4">
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Tahan Ctrl / Cmd untuk pilih lebih dari satu</div>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi / Agenda</label>
              <textarea name="description" class="form-control" rows="3"
                        placeholder="Tulis agenda kegiatan..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary ms-auto">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Kegiatan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<style>
/* ── Meetings Page Styles ──────────────────────────────────────────── */

.meetings-page-header {
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 60%, #A83218 100%);
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
  position: relative; overflow: hidden;
}
.meetings-page-header::after {
  content: ''; position: absolute;
  top: -30px; right: -30px;
  width: 160px; height: 160px; border-radius: 50%;
  background: rgba(201,168,76,.10); pointer-events: none;
}
.meetings-page-title {
  font-size: 20px; font-weight: 800; color: #fff; margin: 0;
  display: flex; align-items: center; gap: .5rem; letter-spacing: -.02em;
}
.meetings-page-title-icon {
  width: 36px; height: 36px; background: rgba(255,255,255,.15);
  border-radius: 9px; display: inline-flex; align-items: center;
  justify-content: center; flex-shrink: 0;
}
.meetings-page-subtitle { color: rgba(255,255,255,.70); font-size: 13px; margin: .25rem 0 0 3rem; }

.btn-create-meeting {
  background: var(--gold) !important;
  border: 1px solid var(--gold-dark) !important;
  color: #3D0A0A !important; font-weight: 700; font-size: 13.5px;
  padding: .55rem 1.25rem; border-radius: 9px;
  box-shadow: 0 3px 12px rgba(201,168,76,.30);
  display: inline-flex; align-items: center; gap: .4rem;
  transition: all .18s; cursor: pointer;
}
.btn-create-meeting:hover {
  background: var(--gold-dark) !important; color: #fff !important;
  transform: translateY(-1px); box-shadow: 0 6px 18px rgba(201,168,76,.38);
}

/* Stat Strip */
.meetings-stat-strip {
  display: flex; align-items: center;
  background: rgba(0,0,0,.18); border-radius: 10px;
  padding: .5rem 1.25rem; width: fit-content; backdrop-filter: blur(6px);
}
.meetings-stat-item { display: flex; align-items: center; gap: .4rem; padding: 0 .75rem; }
.meetings-stat-value { font-size: 18px; font-weight: 800; color: #fff; line-height: 1; }
.meetings-stat-label { font-size: 11px; color: rgba(255,255,255,.65); font-weight: 500; }
.meetings-stat-sep   { width: 1px; height: 28px; background: rgba(255,255,255,.18); }
.meetings-stat-blue   .meetings-stat-value { color: #93c5fd; }
.meetings-stat-orange .meetings-stat-value { color: #fcd34d; }
.meetings-stat-green  .meetings-stat-value { color: #86efac; }
.meetings-stat-red    .meetings-stat-value { color: #fca5a5; }

/* Main Card */
.meetings-main-card {
  border: 1px solid var(--border-light); border-radius: 14px;
  overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.06);
}
.meetings-card-header {
  background: #fff; border-bottom: 1px solid var(--border-light);
  padding: .75rem 1.25rem !important; min-height: 56px;
}
.meetings-card-header::before { display: none !important; }

/* Pill Tabs */
.meetings-view-tabs {
  display: flex; background: var(--bg-page);
  border-radius: 9px; padding: 3px; gap: 2px;
  border: 1px solid var(--border-light);
}
.meetings-tab-btn {
  background: transparent; border: none; border-radius: 7px;
  padding: .35rem .85rem; font-size: 13px; font-weight: 600;
  color: var(--text-muted); display: flex; align-items: center; gap: .35rem;
  cursor: pointer; transition: all .16s; white-space: nowrap;
}
.meetings-tab-btn:hover { color: var(--brand); background: rgba(123,28,28,.06); }
.meetings-tab-btn.active { background: var(--brand); color: #fff; box-shadow: 0 2px 8px rgba(123,28,28,.22); }
.meetings-tab-btn.active svg { stroke: #fff; }

/* Search & Filter */
.meetings-search-wrap { position: relative; width: 240px; }
.meetings-search-icon {
  position: absolute; left: .6rem; top: 50%;
  transform: translateY(-50%); color: var(--text-muted); pointer-events: none;
}
.meetings-search-input {
  border: 1.5px solid var(--border); border-radius: 8px;
  padding: .38rem .75rem .38rem 2rem;
  font-size: 13px; width: 100%; outline: none;
  transition: border-color .16s, box-shadow .16s;
  background: #fff; color: var(--text-main);
}
.meetings-search-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
.meetings-filter-select {
  border: 1.5px solid var(--border); border-radius: 8px;
  padding: .38rem .75rem; font-size: 13px; outline: none;
  background: #fff; color: var(--text-main); cursor: pointer;
  transition: border-color .16s;
}
.meetings-filter-select:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,.15); }

/* Table */
.meetings-table { margin: 0; font-size: 13.5px; }
.meetings-table thead th {
  background: #faf6ef; border-bottom: 2px solid var(--border);
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-muted);
  padding: .65rem 1.1rem; white-space: nowrap;
}
.meetings-table tbody td {
  padding: .8rem 1.1rem; vertical-align: middle;
  border-bottom: 1px solid var(--border-light);
}
.meetings-table tbody tr:last-child td { border-bottom: none; }
.meetings-table tbody tr:hover { background: #faf4eb; }
.meetings-table tbody tr.d-none { display: none !important; }

.meetings-title-block { display: flex; align-items: flex-start; gap: .6rem; }
.meetings-color-dot {
  width: 10px; height: 10px; border-radius: 50%;
  flex-shrink: 0; margin-top: 4px; box-shadow: 0 0 0 2px rgba(0,0,0,.08);
}
.meetings-title-text { font-size: 13.5px; font-weight: 600; color: var(--text-main); line-height: 1.35; }
.meetings-creator    { font-size: 11.5px; color: var(--text-muted); margin-top: 1px; }

.meetings-time-block { line-height: 1.35; }
.meetings-date { font-size: 13px; font-weight: 600; color: var(--text-main); display: block; }
.meetings-time { font-size: 11.5px; color: var(--text-muted); }

.meetings-location {
  display: flex; align-items: flex-start; gap: .3rem;
  font-size: 13px; color: var(--text-muted); max-width: 180px;
}
.meetings-location span {
  display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;
}

.meetings-badge-peserta {
  display: inline-flex; align-items: center; gap: .3rem;
  background: rgba(32,107,196,.08); color: #1557a0;
  font-size: 12px; font-weight: 600; padding: .25em .65em; border-radius: 20px;
}

.meetings-status-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 11.5px; font-weight: 700; padding: .3em .7em;
  border-radius: 20px; white-space: nowrap; letter-spacing: .02em;
}
.meetings-status-blue   { background: rgba(32,107,196,.10);  color: #1557a0; }
.meetings-status-orange { background: rgba(201,168,76,.14);  color: #7a5f00; }
.meetings-status-green  { background: rgba(47,179,68,.10);   color: #1e7a2e; }
.meetings-status-red    { background: rgba(192,57,43,.10);   color: #a82515; }

.meetings-btn-detail {
  background: transparent; border: 1.5px solid var(--brand); color: var(--brand);
  font-size: 12px; font-weight: 600; padding: .28rem .65rem; border-radius: 7px;
  display: inline-flex; align-items: center; gap: .3rem; transition: all .15s;
}
.meetings-btn-detail:hover { background: var(--brand); color: #fff; box-shadow: 0 2px 8px rgba(123,28,28,.20); }
.meetings-btn-delete {
  background: transparent; border: 1.5px solid rgba(192,57,43,.35); color: #a82515;
  padding: .28rem .5rem; border-radius: 7px;
  display: inline-flex; align-items: center; transition: all .15s;
}
.meetings-btn-delete:hover { background: rgba(192,57,43,.08); border-color: #a82515; }

.meetings-table-footer {
  padding: .65rem 1.1rem; background: #faf6ef;
  border-top: 1px solid var(--border-light);
  font-size: 12px; color: var(--text-muted);
}

/* Empty State */
.meetings-empty {
  display: flex; flex-direction: column; align-items: center;
  text-align: center; padding: 4rem 2rem; color: var(--text-muted);
}
.meetings-empty-icon {
  width: 72px; height: 72px; background: var(--brand-light); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.25rem; color: var(--brand);
}
.meetings-empty-title { font-size: 17px; font-weight: 700; color: var(--text-main); margin-bottom: .4rem; }
.meetings-empty-desc  { font-size: 13px; color: var(--text-muted); margin-bottom: 1.25rem; max-width: 30ch; }

/* Delete modal */
.meetings-modal-delete .modal-header { display: none !important; }
.meetings-delete-icon-wrap {
  width: 60px; height: 60px; background: rgba(192,57,43,.10); border-radius: 50%;
  display: flex; align-items: center; justify-content: center; margin: 0 auto; color: #a82515;
}

/* Modal header icon */
.meetings-modal-header-icon {
  width: 32px; height: 32px; background: rgba(255,255,255,.18); border-radius: 7px;
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* Responsive */
@media (max-width: 767.98px) {
  .meetings-page-header   { padding: 1rem; }
  .meetings-page-title    { font-size: 16px; }
  .meetings-page-subtitle { margin-left: 2.5rem; }
  .meetings-stat-strip    { flex-wrap: wrap; row-gap: .4rem; }
  .meetings-search-wrap   { width: 100%; }
  .meetings-col-loc       { display: none; }
}
</style>

<?php
$calendarApiUrl  = $baseUrl . '/api/meetings/calendar';
$meetingBaseUrl  = $baseUrl . '/meetings/';
$deptChildrenUrl = $baseUrl . '/api/departments/children';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const tabs       = document.querySelectorAll('.meetings-tab-btn');
  const viewCal    = document.getElementById('view-calendar');
  const viewList   = document.getElementById('view-list');
  const searchWrap = document.getElementById('list-search-wrap');
  const filterWrap = document.getElementById('list-filter-wrap');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const v = tab.dataset.view;
      viewCal.style.display    = v === 'calendar' ? '' : 'none';
      viewList.style.display   = v === 'list'     ? '' : 'none';
      searchWrap.style.display = v === 'list'     ? '' : 'none';
      filterWrap.style.display = v === 'list'     ? '' : 'none';
      if (v === 'calendar') calendar.render();
    });
  });

  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView:  'dayGridMonth',
    locale:       'id',
    height:       650,
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'dayGridMonth,timeGridWeek,listWeek'
    },
    buttonText: { today:'Hari ini', month:'Bulan', week:'Minggu', list:'Agenda' },
    events: {
      url:     <?= json_encode($calendarApiUrl) ?>,
      failure: () => { console.error('Gagal memuat events kalender'); }
    },
    eventClick: info => {
      window.location.href = <?= json_encode($meetingBaseUrl) ?> + info.event.id;
    },
    eventDidMount: info => {
      const loc = info.event.extendedProps.location || 'Lokasi belum diset';
      info.el.setAttribute('title', info.event.title + '\n\uD83D\uDCCD ' + loc);
    }
  });
  calendar.render();

  // Live search & filter
  const searchInput  = document.getElementById('meetings-search');
  const filterSelect = document.getElementById('meetings-status-filter');
  const tableBody    = document.querySelector('#meetings-table tbody');
  const countLabel   = document.getElementById('meetings-count-label');

  function filterTable() {
    if (!tableBody) return;
    const q  = searchInput  ? searchInput.value.toLowerCase()  : '';
    const sf = filterSelect ? filterSelect.value : '';
    let visible = 0;
    tableBody.querySelectorAll('tr.meetings-row').forEach(row => {
      const show = (!q || (row.dataset.title||'').includes(q)) &&
                   (!sf || row.dataset.status === sf);
      row.classList.toggle('d-none', !show);
      if (show) visible++;
    });
    if (countLabel) countLabel.textContent = 'Menampilkan ' + visible + ' kegiatan';
  }

  if (searchInput)  searchInput.addEventListener('input', filterTable);
  if (filterSelect) filterSelect.addEventListener('change', filterTable);
});

const _deptChildrenUrl = <?= json_encode($deptChildrenUrl) ?>;

async function fetchDeptChildren(parentId) {
  try {
    const res = await fetch(_deptChildrenUrl + '?parent_id=' + parentId);
    return await res.json();
  } catch(e) { return []; }
}

function syncMtgHidden() {
  const v3 = document.getElementById('mtg-u3').value;
  const v2 = document.getElementById('mtg-u2').value;
  const v1 = document.getElementById('mtg-u1').value;
  document.getElementById('mtg-dept-id').value = v3 || v2 || v1 || '';
}

async function cascadeMtg(level) {
  const s1 = document.getElementById('mtg-u1');
  const s2 = document.getElementById('mtg-u2');
  const s3 = document.getElementById('mtg-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">-- Pilih unit dulu --</option>';
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s2.disabled = s3.disabled = true;
    syncMtgHidden();
    if (!s1.value) return;
    const kids = await fetchDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">-- Semua Bidang --</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncMtgHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s3.disabled = true;
    syncMtgHidden();
    if (!s2.value) return;
    const kids = await fetchDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">-- Semua Sub Bidang --</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncMtgHidden();
  } else {
    syncMtgHidden();
  }
}

function confirmDeleteMeeting(id, title) {
  document.getElementById('deleteMeetingTitle').textContent = title;
  document.getElementById('formDeleteMeeting').action =
    <?= json_encode($baseUrl) ?> + '/meetings/' + id + '/delete';
  const modal = new bootstrap.Modal(document.getElementById('modalDeleteMeeting'));
  modal.show();
}
</script>
