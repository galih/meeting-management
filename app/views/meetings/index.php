<?php
$baseUrl  = rtrim(BASE_URL, '/');
$isAdmin  = Auth::hasRole('admin');
$allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

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

$cnt = ['scheduled'=>0,'ongoing'=>0,'done'=>0,'cancelled'=>0,'total'=>count($meetings)];
foreach ($meetings as $m) {
  $s = $m['status'] ?? 'scheduled';
  if (isset($cnt[$s])) $cnt[$s]++;
}

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<!-- ══ FLASH TOAST ══════════════════════════════════════════════════ -->
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

<!-- ══ HERO HEADER ══════════════════════════════════════════════════ -->
<div class="mi-hero">
  <div class="mi-hero-left">
    <div class="mi-hero-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <h1 class="mi-hero-title">Manajemen Kegiatan</h1>
      <p class="mi-hero-sub">Kelola jadwal, peserta, dan agenda kegiatan instansi</p>
    </div>
  </div>
  <?php if (Auth::hasRole('admin','sekretaris')): ?>
  <button class="mi-btn-create" data-bs-toggle="modal" data-bs-target="#modalMeeting">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Buat Kegiatan
  </button>
  <?php endif; ?>
</div>

<!-- ══ STAT CARDS ════════════════════════════════════════════════════ -->
<div class="mi-stats">
  <div class="mi-stat-card mi-stat-all">
    <div class="mi-stat-val"><?= $cnt['total'] ?></div>
    <div class="mi-stat-lbl">Total Kegiatan</div>
  </div>
  <div class="mi-stat-card mi-stat-sched" data-filter-stat="scheduled">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $cnt['scheduled'] ?></div>
      <div class="mi-stat-lbl">Terjadwal</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-ongoing" data-filter-stat="ongoing">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $cnt['ongoing'] ?></div>
      <div class="mi-stat-lbl">Berlangsung</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-done" data-filter-stat="done">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $cnt['done'] ?></div>
      <div class="mi-stat-lbl">Selesai</div>
    </div>
  </div>
  <div class="mi-stat-card mi-stat-cancel" data-filter-stat="cancelled">
    <div class="mi-stat-dot"></div>
    <div>
      <div class="mi-stat-val"><?= $cnt['cancelled'] ?></div>
      <div class="mi-stat-lbl">Dibatalkan</div>
    </div>
  </div>
</div>

<!-- ══ MAIN PANEL ════════════════════════════════════════════════════ -->
<div class="mi-panel">

  <!-- Toolbar -->
  <div class="mi-toolbar">
    <!-- View Toggle -->
    <div class="mi-view-toggle">
      <button class="mi-vtab active" data-view="calendar" title="Tampilan Kalender">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Kalender
      </button>
      <button class="mi-vtab" data-view="list" title="Tampilan Daftar">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><circle cx="3" cy="6" r="1.5" fill="currentColor"/><circle cx="3" cy="12" r="1.5" fill="currentColor"/><circle cx="3" cy="18" r="1.5" fill="currentColor"/></svg>
        Daftar
      </button>
    </div>

    <!-- List-only controls -->
    <div class="mi-list-controls" id="miListControls" style="display:none">
      <!-- Search -->
      <div class="mi-search-wrap">
        <svg class="mi-search-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="miSearch" class="mi-search-input" placeholder="Cari kegiatan…">
        <button class="mi-search-clear" id="miSearchClear" title="Hapus pencarian">×</button>
      </div>
      <!-- Status filter -->
      <div class="mi-filter-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-muted);flex-shrink:0"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
        <select id="miStatusFilter" class="mi-filter-sel">
          <option value="">Semua Status</option>
          <option value="scheduled">Terjadwal</option>
          <option value="ongoing">Berlangsung</option>
          <option value="done">Selesai</option>
          <option value="cancelled">Dibatalkan</option>
        </select>
      </div>
      <!-- Sort -->
      <div class="mi-filter-wrap">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--text-muted);flex-shrink:0"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="18" x2="11" y2="18"/></svg>
        <select id="miSortBy" class="mi-filter-sel">
          <option value="newest">Terbaru dulu</option>
          <option value="oldest">Terlama dulu</option>
          <option value="alpha">A → Z</option>
        </select>
      </div>
    </div>
  </div>

  <!-- ── CALENDAR VIEW ─────────────────────────────────────────────── -->
  <div id="miViewCalendar" class="mi-view-body">
    <div id="miCalendar"></div>
  </div>

  <!-- ── LIST VIEW ─────────────────────────────────────────────────── -->
  <div id="miViewList" class="mi-view-body" style="display:none">
    <?php if (empty($meetings)): ?>
    <div class="mi-empty">
      <div class="mi-empty-icon">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><line x1="9" y1="14" x2="15" y2="14" stroke-dasharray="2 1"/><line x1="9" y1="17" x2="13" y2="17" stroke-dasharray="2 1"/></svg>
      </div>
      <h3 class="mi-empty-title">Belum ada kegiatan</h3>
      <p class="mi-empty-desc">Buat kegiatan pertama untuk memulai penjadwalan instansi</p>
      <?php if (Auth::hasRole('admin','sekretaris')): ?>
      <button class="mi-btn-create" data-bs-toggle="modal" data-bs-target="#modalMeeting">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Kegiatan Baru
      </button>
      <?php endif; ?>
    </div>
    <?php else: ?>

    <!-- Table -->
    <div class="mi-table-wrap">
      <table class="mi-table" id="miTable">
        <thead>
          <tr>
            <th>Kegiatan</th>
            <th>Waktu</th>
            <th>Lokasi</th>
            <th>Peserta</th>
            <th>Status</th>
            <th style="text-align:right">Aksi</th>
          </tr>
        </thead>
        <tbody id="miTbody">
          <?php foreach ($meetings as $m):
            $sc  = $statusColor[$m['status']] ?? 'mi-blue';
            $ico = $statusIcon[$m['status']] ?? '';
            $lbl = $statusLabel[$m['status']] ?? ucfirst($m['status']);
            $sd  = date('d M Y', strtotime($m['start_datetime']));
            $st  = date('H:i', strtotime($m['start_datetime']));
            $ed  = date('d M Y', strtotime($m['end_datetime']));
            $et  = date('H:i', strtotime($m['end_datetime']));
            $sameDay = $sd === $ed;
            $ts  = strtotime($m['start_datetime']);
          ?>
          <tr class="mi-row"
              data-status="<?= $m['status'] ?>"
              data-title="<?= strtolower(htmlspecialchars($m['title'])) ?>"
              data-ts="<?= $ts ?>">
            <td class="mi-col-title">
              <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>" class="mi-title-link">
                <span class="mi-dot" style="background:<?= htmlspecialchars($m['color'] ?? '#7B1C1C') ?>"></span>
                <span class="mi-title-name"><?= htmlspecialchars($m['title']) ?></span>
              </a>
              <div class="mi-creator">oleh <?= htmlspecialchars($m['creator_name'] ?? '—') ?></div>
            </td>
            <td class="mi-col-time">
              <span class="mi-date"><?= $sd ?></span>
              <span class="mi-time">
                <?= $st ?>
                <?php if ($sameDay): ?>– <?= $et ?>
                <?php else: ?>– <?= $ed ?> <?= $et ?><?php endif; ?>
              </span>
            </td>
            <td class="mi-col-loc">
              <?php if (!empty($m['location'])): ?>
              <div class="mi-loc">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span><?= htmlspecialchars($m['location']) ?></span>
              </div>
              <?php else: ?><span class="mi-null">—</span><?php endif; ?>
            </td>
            <td class="mi-col-peserta">
              <span class="mi-badge-peserta">
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <?= (int)$m['total_peserta'] ?>
              </span>
            </td>
            <td class="mi-col-status">
              <span class="mi-status <?= $sc ?>"><?= $ico ?> <?= $lbl ?></span>
            </td>
            <td class="mi-col-act">
              <div class="mi-act-wrap">
                <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>" class="mi-btn-detail" title="Lihat Detail">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                  Detail
                </a>
                <?php if ($isAdmin): ?>
                <button class="mi-btn-del"
                        title="Hapus Kegiatan"
                        onclick="miConfirmDelete(<?= $m['id'] ?>,<?= htmlspecialchars(json_encode($m['title'])) ?>)">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                </button>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Table footer -->
    <div class="mi-tfoot">
      <span id="miCountLabel">Menampilkan <?= count($meetings) ?> kegiatan</span>
      <div class="mi-no-results" id="miNoResults" style="display:none">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        Tidak ada kegiatan yang cocok
      </div>
    </div>

    <?php endif; ?>
  </div>
</div>

<!-- ══ MODAL HAPUS ═══════════════════════════════════════════════════ -->
<?php if ($isAdmin): ?>
<div class="modal fade" id="miModalDel" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content mi-modal-del">
      <div class="mi-modal-del-body">
        <div class="mi-del-icon-wrap">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
        </div>
        <h5 class="mi-del-title">Hapus Kegiatan?</h5>
        <p class="mi-del-desc">
          Kegiatan <strong id="miDelTitle" class="mi-del-name"></strong> akan dihapus permanen
          beserta seluruh peserta, notulen, dan tindak lanjut terkait.
        </p>
      </div>
      <div class="mi-modal-del-foot">
        <button type="button" class="mi-btn-cancel" data-bs-dismiss="modal">Batal</button>
        <form id="miFormDel" method="POST" action="" style="display:inline">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="mi-btn-confirm-del">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MODAL BUAT KEGIATAN ═══════════════════════════════════════════ -->
<div class="modal fade" id="modalMeeting" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content mi-modal-create">
      <form method="POST" action="<?= $baseUrl ?>/meetings" id="miFormCreate">
        <?= Auth::csrfField() ?>

        <!-- Header -->
        <div class="mi-mc-header">
          <div class="mi-mc-header-icon">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <h5 class="mi-mc-title">Buat Kegiatan Baru</h5>
          <button type="button" class="mi-mc-close" data-bs-dismiss="modal" aria-label="Tutup">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <!-- Body -->
        <div class="mi-mc-body">

          <!-- Group: Informasi Dasar -->
          <div class="mi-mc-section">
            <div class="mi-mc-section-label">Informasi Dasar</div>
            <div class="mi-mc-grid">
              <div class="mi-mc-field mi-mc-full">
                <label class="mi-mc-lbl mi-req">Judul Kegiatan</label>
                <input type="text" name="title" class="mi-mc-input" required
                       placeholder="Contoh: Rapat Evaluasi Bulanan Q2" autocomplete="off">
              </div>
              <div class="mi-mc-field">
                <label class="mi-mc-lbl mi-req">Mulai</label>
                <input type="datetime-local" name="start_datetime" class="mi-mc-input" id="miStart" required>
              </div>
              <div class="mi-mc-field">
                <label class="mi-mc-lbl mi-req">Selesai</label>
                <input type="datetime-local" name="end_datetime" class="mi-mc-input" id="miEnd" required>
              </div>
              <div class="mi-mc-field mi-mc-full">
                <label class="mi-mc-lbl">Lokasi / Tautan Video</label>
                <input type="text" name="location" class="mi-mc-input"
                       placeholder="Ruang Rapat A  atau  https://meet.google.com/...">
              </div>
            </div>
          </div>

          <!-- Group: Unit Kerja -->
          <div class="mi-mc-section">
            <div class="mi-mc-section-label">Unit Kerja <span class="mi-mc-opt">(opsional)</span></div>
            <div class="mi-mc-grid mi-mc-grid-3">
              <div class="mi-mc-field">
                <label class="mi-mc-lbl">Unit Kerja</label>
                <select id="mtg-u1" class="mi-mc-select" onchange="cascadeMtg(1)">
                  <option value="">— Semua —</option>
                  <?php foreach ($departments as $d): if ((int)($d['level']??1)!==1) continue; ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mi-mc-field">
                <label class="mi-mc-lbl">Bidang / Bagian</label>
                <select id="mtg-u2" class="mi-mc-select" disabled onchange="cascadeMtg(2)">
                  <option value="">— Pilih unit dulu —</option>
                </select>
              </div>
              <div class="mi-mc-field">
                <label class="mi-mc-lbl">Sub Bidang</label>
                <select id="mtg-u3" class="mi-mc-select" disabled onchange="cascadeMtg(3)">
                  <option value="">— Opsional —</option>
                </select>
              </div>
            </div>
            <input type="hidden" id="mtg-dept-id" name="department_id" value="">
          </div>

          <!-- Group: Peserta & Warna -->
          <div class="mi-mc-section">
            <div class="mi-mc-section-label">Peserta &amp; Tampilan</div>
            <div class="mi-mc-grid">
              <div class="mi-mc-field mi-mc-full">
                <label class="mi-mc-lbl">Pilih Peserta</label>
                <div class="mi-mc-participants" id="miParticipants">
                  <?php foreach ($allUsers as $u): ?>
                  <label class="mi-mc-pcheck">
                    <input type="checkbox" name="participants[]" value="<?= $u['id'] ?>">
                    <span class="mi-mc-pname"><?= htmlspecialchars($u['name']) ?></span>
                  </label>
                  <?php endforeach; ?>
                </div>
                <div class="mi-mc-pcount" id="miPCount">0 peserta dipilih</div>
              </div>
              <div class="mi-mc-field">
                <label class="mi-mc-lbl">Warna Kalender</label>
                <div class="mi-color-picker-wrap">
                  <input type="color" name="color" id="mtgColor" class="mi-color-input" value="#7B1C1C">
                  <div class="mi-color-presets">
                    <?php foreach (['#7B1C1C','#1a6e9b','#2d7a2d','#8b5e00','#6b2fa0','#c0392b','#2c7a6e'] as $c): ?>
                    <button type="button" class="mi-color-preset" style="background:<?= $c ?>" data-color="<?= $c ?>" title="<?= $c ?>"></button>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Group: Deskripsi -->
          <div class="mi-mc-section">
            <div class="mi-mc-section-label">Deskripsi / Agenda <span class="mi-mc-opt">(opsional)</span></div>
            <textarea name="description" class="mi-mc-textarea" rows="3"
                      placeholder="Tulis poin-poin agenda kegiatan…"></textarea>
          </div>

        </div><!-- /mi-mc-body -->

        <!-- Footer -->
        <div class="mi-mc-footer">
          <button type="button" class="mi-mc-btn-cancel" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="mi-mc-btn-submit">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Buat Kegiatan
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ══ STYLES ════════════════════════════════════════════════════════ -->
<style>
/* ── Toast ── */
.mi-toast {
  position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
  display: flex; align-items: center; gap: .6rem;
  padding: .7rem 1rem; border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.14);
  animation: miSlideIn .25s ease;
  max-width: 360px;
}
@keyframes miSlideIn { from { opacity:0; transform: translateY(-8px) } to { opacity:1; transform:none } }
.mi-toast-ok  { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.mi-toast-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.mi-toast-close { background: none; border: none; font-size: 16px; cursor: pointer; margin-left: .25rem; opacity:.6; line-height:1; padding:0; }
.mi-toast-close:hover { opacity:1; }

/* ── Hero ── */
.mi-hero {
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
  background: linear-gradient(135deg, #7B1C1C 0%, #9B2020 55%, #A83218 100%);
  padding: 1.25rem 1.5rem; border-radius: 14px; margin-bottom: 1.25rem;
  box-shadow: 0 4px 20px rgba(123,28,28,.22); position: relative; overflow: hidden;
}
.mi-hero::after {
  content:''; position:absolute; top:-40px; right:-40px;
  width:180px; height:180px; border-radius:50%;
  background: rgba(201,168,76,.08); pointer-events:none;
}
.mi-hero-left { display:flex; align-items:center; gap:.75rem; }
.mi-hero-icon {
  width:38px; height:38px; border-radius:10px;
  background: rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; color:#fff;
}
.mi-hero-title { font-size:19px; font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.mi-hero-sub   { font-size:12.5px; color:rgba(255,255,255,.68); margin:.2rem 0 0; }

.mi-btn-create {
  display: inline-flex; align-items: center; gap:.4rem;
  background: var(--gold, #C9A84C); border: 1px solid rgba(0,0,0,.1);
  color: #3d0a0a; font-size: 13.5px; font-weight: 700;
  padding: .55rem 1.2rem; border-radius: 9px; cursor: pointer;
  box-shadow: 0 3px 12px rgba(201,168,76,.30);
  transition: all .18s; white-space: nowrap;
}
.mi-btn-create:hover {
  background: #b8922a; color: #fff;
  transform: translateY(-1px); box-shadow: 0 6px 18px rgba(201,168,76,.38);
}

/* ── Stat Cards ── */
.mi-stats {
  display: grid;
  grid-template-columns: 1.4fr repeat(4, 1fr);
  gap: .75rem; margin-bottom: 1.25rem;
}
.mi-stat-card {
  background: #fff; border: 1px solid var(--border-light, #ede8e0);
  border-radius: 12px; padding: .9rem 1.1rem;
  display: flex; align-items: center; gap: .7rem;
  cursor: pointer; transition: all .18s;
  box-shadow: 0 1px 4px rgba(0,0,0,.04);
}
.mi-stat-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,.08); transform: translateY(-1px); }
.mi-stat-card.mi-active { border-color: #7B1C1C; box-shadow: 0 0 0 2.5px rgba(123,28,28,.18); }
.mi-stat-all  { grid-column: span 1; background: #7B1C1C; border-color: #7B1C1C; }
.mi-stat-all .mi-stat-val { color: #fff; font-size: 24px; }
.mi-stat-all .mi-stat-lbl { color: rgba(255,255,255,.75); }
.mi-stat-val { font-size: 22px; font-weight: 800; color: var(--text-main, #2c1a1a); line-height: 1; }
.mi-stat-lbl { font-size: 11.5px; font-weight: 500; color: var(--text-muted, #8c7a6b); margin-top: 2px; }
.mi-stat-dot {
  width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 1px;
}
.mi-stat-sched  .mi-stat-dot { background: #3b82f6; }
.mi-stat-ongoing .mi-stat-dot { background: #f59e0b; }
.mi-stat-done   .mi-stat-dot { background: #22c55e; }
.mi-stat-cancel .mi-stat-dot { background: #ef4444; }

/* ── Panel ── */
.mi-panel {
  background: #fff; border: 1px solid var(--border-light, #ede8e0);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 2px 12px rgba(0,0,0,.05);
}

/* ── Toolbar ── */
.mi-toolbar {
  display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
  padding: .75rem 1.25rem; border-bottom: 1px solid var(--border-light, #ede8e0);
  background: #fff;
}
.mi-view-toggle {
  display: flex; background: #f5f0ea; border-radius: 9px;
  padding: 3px; gap: 2px; border: 1px solid var(--border-light, #ede8e0);
  flex-shrink: 0;
}
.mi-vtab {
  background: transparent; border: none; border-radius: 7px;
  padding: .32rem .8rem; font-size: 13px; font-weight: 600;
  color: var(--text-muted, #8c7a6b); display: flex; align-items: center; gap: .35rem;
  cursor: pointer; transition: all .15s; white-space: nowrap;
}
.mi-vtab:hover { color: #7B1C1C; background: rgba(123,28,28,.06); }
.mi-vtab.active { background: #7B1C1C; color: #fff; box-shadow: 0 2px 8px rgba(123,28,28,.22); }

.mi-list-controls { display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; flex: 1; }
.mi-search-wrap {
  position: relative; display: flex; align-items: center; min-width: 200px;
}
.mi-search-ico {
  position: absolute; left: .6rem; color: var(--text-muted, #8c7a6b); pointer-events: none;
}
.mi-search-input {
  border: 1.5px solid var(--border, #ddd); border-radius: 8px;
  padding: .38rem .75rem .38rem 1.95rem; font-size: 13px;
  width: 100%; outline: none; background: #fff; color: var(--text-main, #2c1a1a);
  transition: border-color .15s, box-shadow .15s;
}
.mi-search-input:focus { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.10); }
.mi-search-clear {
  position: absolute; right: .5rem; background: none; border: none;
  font-size: 16px; color: var(--text-muted); cursor: pointer;
  display: none; line-height: 1; padding: 0; opacity: .6;
}
.mi-search-clear:hover { opacity: 1; }
.mi-filter-wrap {
  display: flex; align-items: center; gap: .4rem;
  border: 1.5px solid var(--border, #ddd); border-radius: 8px;
  padding: .38rem .65rem; background: #fff;
  transition: border-color .15s;
}
.mi-filter-wrap:focus-within { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.10); }
.mi-filter-sel {
  border: none; outline: none; font-size: 13px;
  background: transparent; color: var(--text-main, #2c1a1a); cursor: pointer;
  padding: 0; min-width: 100px;
}

/* ── View Body ── */
.mi-view-body { padding: 0; }
#miCalendar { min-height: 640px; padding: 1rem 1.25rem 1.25rem; }

/* ── Table ── */
.mi-table-wrap { overflow-x: auto; }
.mi-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.mi-table thead th {
  background: #faf6ef; border-bottom: 2px solid var(--border, #e0d8cc);
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: var(--text-muted, #8c7a6b);
  padding: .65rem 1.1rem; white-space: nowrap;
}
.mi-table tbody td {
  padding: .78rem 1.1rem; vertical-align: middle;
  border-bottom: 1px solid var(--border-light, #f0ece5);
}
.mi-table tbody tr:last-child td { border-bottom: none; }
.mi-table tbody tr:hover { background: #faf4eb; }
.mi-row.mi-hidden { display: none !important; }

.mi-col-title { min-width: 220px; }
.mi-title-link {
  display: flex; align-items: flex-start; gap: .55rem;
  text-decoration: none; color: inherit;
}
.mi-title-link:hover .mi-title-name { color: #7B1C1C; text-decoration: underline; text-underline-offset: 2px; }
.mi-dot { width: 9px; height: 9px; border-radius: 50%; flex-shrink: 0; margin-top: 4px; }
.mi-title-name { font-size: 13.5px; font-weight: 600; color: var(--text-main, #2c1a1a); line-height: 1.35; }
.mi-creator { font-size: 11.5px; color: var(--text-muted, #8c7a6b); margin-top: 2px; padding-left: 1.4rem; }

.mi-col-time { white-space: nowrap; }
.mi-date { display: block; font-size: 13px; font-weight: 600; color: var(--text-main); }
.mi-time { font-size: 11.5px; color: var(--text-muted); }

.mi-col-loc { max-width: 190px; }
.mi-loc {
  display: flex; align-items: flex-start; gap: .3rem;
  font-size: 12.5px; color: var(--text-muted);
}
.mi-loc span { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
.mi-null { color: var(--text-muted); font-size: 13px; }

.mi-badge-peserta {
  display: inline-flex; align-items: center; gap: .3rem;
  background: rgba(32,107,196,.09); color: #1a5fa0;
  font-size: 12px; font-weight: 700; padding: .25em .65em; border-radius: 20px;
}

.mi-status {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 11.5px; font-weight: 700; padding: .28em .7em;
  border-radius: 20px; white-space: nowrap; letter-spacing: .02em;
}
.mi-blue   { background: rgba(59,130,246,.10);  color: #1d4ed8; }
.mi-amber  { background: rgba(245,158,11,.12);  color: #92400e; }
.mi-green  { background: rgba(34,197,94,.10);   color: #15803d; }
.mi-red    { background: rgba(239,68,68,.10);   color: #b91c1c; }

.mi-col-act { text-align: right; white-space: nowrap; }
.mi-act-wrap { display: flex; align-items: center; gap: .4rem; justify-content: flex-end; }
.mi-btn-detail {
  display: inline-flex; align-items: center; gap: .3rem;
  border: 1.5px solid #7B1C1C; color: #7B1C1C; background: transparent;
  font-size: 12px; font-weight: 600; padding: .28rem .65rem; border-radius: 7px;
  text-decoration: none; transition: all .15s;
}
.mi-btn-detail:hover { background: #7B1C1C; color: #fff; box-shadow: 0 2px 8px rgba(123,28,28,.20); }
.mi-btn-del {
  display: inline-flex; align-items: center; justify-content: center;
  width: 30px; height: 30px;
  border: 1.5px solid rgba(192,57,43,.30); color: #a82515;
  background: transparent; border-radius: 7px; cursor: pointer;
  transition: all .15s;
}
.mi-btn-del:hover { background: rgba(192,57,43,.08); border-color: #a82515; }

.mi-tfoot {
  display: flex; align-items: center; justify-content: space-between;
  padding: .6rem 1.1rem; background: #faf6ef;
  border-top: 1px solid var(--border-light, #f0ece5);
  font-size: 12px; color: var(--text-muted);
}
.mi-no-results { display: flex; align-items: center; gap: .35rem; }

/* ── Empty ── */
.mi-empty {
  display: flex; flex-direction: column; align-items: center;
  text-align: center; padding: 4rem 2rem;
}
.mi-empty-icon {
  width: 72px; height: 72px; border-radius: 50%;
  background: #fdf2f2; display: flex; align-items: center; justify-content: center;
  color: #7B1C1C; margin-bottom: 1.25rem;
}
.mi-empty-title { font-size: 17px; font-weight: 700; color: var(--text-main); margin-bottom: .4rem; }
.mi-empty-desc  { font-size: 13px; color: var(--text-muted); max-width: 30ch; margin-bottom: 1.25rem; }

/* ── Delete Modal ── */
.mi-modal-del { border-radius: 14px; border: none; overflow: hidden; }
.mi-modal-del-body { padding: 2rem 1.5rem 1rem; text-align: center; }
.mi-del-icon-wrap {
  width: 58px; height: 58px; border-radius: 50%;
  background: rgba(192,57,43,.10); display: flex; align-items: center;
  justify-content: center; margin: 0 auto 1rem; color: #a82515;
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
  border: 1.5px solid var(--border, #ddd); background: #fff; color: var(--text-main);
  cursor: pointer; transition: all .15s;
}
.mi-btn-cancel:hover { background: #f5f0ea; }
.mi-btn-confirm-del {
  padding: .45rem 1.1rem; border-radius: 8px; font-size: 13.5px; font-weight: 700;
  border: none; background: #a82515; color: #fff; cursor: pointer; transition: all .15s;
}
.mi-btn-confirm-del:hover { background: #8b1e11; }

/* ── Create Modal ── */
.mi-modal-create { border-radius: 14px; border: none; overflow: hidden; }
.mi-mc-header {
  display: flex; align-items: center; gap: .75rem;
  padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-light, #ede8e0);
  background: #7B1C1C;
}
.mi-mc-header-icon {
  width: 34px; height: 34px; border-radius: 8px;
  background: rgba(255,255,255,.18);
  display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: #fff;
}
.mi-mc-title { font-size: 15px; font-weight: 700; color: #fff; margin: 0; flex: 1; }
.mi-mc-close {
  background: rgba(255,255,255,.18); border: none; border-radius: 7px;
  width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #fff; transition: background .15s;
}
.mi-mc-close:hover { background: rgba(255,255,255,.28); }

.mi-mc-body { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1.1rem; overflow-y: auto; max-height: 70vh; }
.mi-mc-section {}
.mi-mc-section-label {
  font-size: 10.5px; font-weight: 700; letter-spacing: .08em;
  text-transform: uppercase; color: var(--text-muted, #8c7a6b);
  margin-bottom: .65rem; padding-bottom: .45rem;
  border-bottom: 1px solid var(--border-light, #f0ece5);
}
.mi-mc-opt { font-weight: 500; text-transform: none; letter-spacing: 0; }

.mi-mc-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .75rem; }
.mi-mc-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
.mi-mc-full { grid-column: span 2; }
.mi-mc-field { display: flex; flex-direction: column; gap: .3rem; }
.mi-mc-lbl { font-size: 12.5px; font-weight: 600; color: var(--text-main); }
.mi-mc-lbl.mi-req::after { content: ' *'; color: #a82515; }
.mi-mc-input, .mi-mc-select, .mi-mc-textarea {
  border: 1.5px solid var(--border, #ddd); border-radius: 8px;
  padding: .42rem .75rem; font-size: 13.5px;
  background: #fff; color: var(--text-main); outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit;
}
.mi-mc-input:focus, .mi-mc-select:focus, .mi-mc-textarea:focus {
  border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.10);
}
.mi-mc-textarea { resize: vertical; }
.mi-mc-select { cursor: pointer; }

/* Participant checkboxes */
.mi-mc-participants {
  display: flex; flex-wrap: wrap; gap: .4rem;
  max-height: 120px; overflow-y: auto;
  border: 1.5px solid var(--border, #ddd); border-radius: 8px;
  padding: .5rem .65rem; background: #fff;
}
.mi-mc-participants:focus-within { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.10); }
.mi-mc-pcheck {
  display: inline-flex; align-items: center; gap: .3rem;
  cursor: pointer; font-size: 12.5px; padding: .2rem .5rem;
  border-radius: 20px; border: 1px solid var(--border-light, #ede8e0);
  transition: all .12s; user-select: none;
}
.mi-mc-pcheck:hover { background: #fdf5e6; border-color: #C9A84C; }
.mi-mc-pcheck input[type=checkbox] { accent-color: #7B1C1C; width: 13px; height: 13px; }
.mi-mc-pcheck input:checked ~ .mi-mc-pname { color: #7B1C1C; font-weight: 600; }
.mi-mc-pcheck:has(input:checked) { background: rgba(123,28,28,.07); border-color: rgba(123,28,28,.25); }
.mi-mc-pcount { font-size: 11.5px; color: var(--text-muted); margin-top: .25rem; }

/* Color picker */
.mi-color-picker-wrap { display: flex; align-items: center; gap: .6rem; }
.mi-color-input {
  width: 38px; height: 38px; border: 2px solid var(--border, #ddd);
  border-radius: 8px; padding: 2px; cursor: pointer; background: none;
}
.mi-color-presets { display: flex; gap: .35rem; flex-wrap: wrap; }
.mi-color-preset {
  width: 22px; height: 22px; border-radius: 50%; border: 2px solid transparent;
  cursor: pointer; transition: transform .12s, border-color .12s;
}
.mi-color-preset:hover { transform: scale(1.15); }
.mi-color-preset.mi-active { border-color: #fff; box-shadow: 0 0 0 2px var(--text-main); }

/* Modal footer */
.mi-mc-footer {
  display: flex; align-items: center; justify-content: flex-end; gap: .75rem;
  padding: 1rem 1.5rem; border-top: 1px solid var(--border-light, #ede8e0);
  background: #faf6ef;
}
.mi-mc-btn-cancel {
  padding: .48rem 1.1rem; border-radius: 8px; font-size: 13.5px; font-weight: 600;
  border: 1.5px solid var(--border, #ddd); background: #fff; color: var(--text-main);
  cursor: pointer; transition: all .15s;
}
.mi-mc-btn-cancel:hover { background: #f0ece5; }
.mi-mc-btn-submit {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .48rem 1.25rem; border-radius: 8px; font-size: 13.5px; font-weight: 700;
  border: none; background: #7B1C1C; color: #fff; cursor: pointer;
  box-shadow: 0 3px 12px rgba(123,28,28,.25); transition: all .15s;
}
.mi-mc-btn-submit:hover { background: #5e1616; transform: translateY(-1px); box-shadow: 0 5px 16px rgba(123,28,28,.32); }

/* ── Responsive ── */
@media (max-width: 991px) {
  .mi-stats { grid-template-columns: 1fr 1fr; }
  .mi-stat-all { grid-column: span 2; }
  .mi-mc-grid-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767px) {
  .mi-hero { padding: 1rem; }
  .mi-hero-title { font-size: 16px; }
  .mi-stats { grid-template-columns: 1fr 1fr; }
  .mi-col-loc, .mi-col-peserta { display: none; }
  .mi-search-wrap { min-width: 140px; }
  .mi-mc-grid { grid-template-columns: 1fr; }
  .mi-mc-full { grid-column: span 1; }
  .mi-mc-grid-3 { grid-template-columns: 1fr; }
}
</style>

<?php
$calendarApiUrl  = $baseUrl . '/api/meetings/calendar';
$meetingBaseUrl  = $baseUrl . '/meetings/';
$deptChildrenUrl = $baseUrl . '/api/departments/children';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── Auto-dismiss toast ── */
  const toast = document.getElementById('miFlashToast');
  if (toast) setTimeout(() => toast.style.animation = 'none', 4000) || setTimeout(() => toast.remove(), 4400);

  /* ── View toggle ── */
  const vtabs    = document.querySelectorAll('.mi-vtab');
  const viewCal  = document.getElementById('miViewCalendar');
  const viewList = document.getElementById('miViewList');
  const listCtrl = document.getElementById('miListControls');

  vtabs.forEach(tab => {
    tab.addEventListener('click', () => {
      vtabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const v = tab.dataset.view;
      viewCal.style.display  = v === 'calendar' ? '' : 'none';
      viewList.style.display = v === 'list'     ? '' : 'none';
      listCtrl.style.display = v === 'list'     ? '' : 'none';
      if (v === 'calendar') cal.render();
    });
  });

  /* ── FullCalendar ── */
  const calEl = document.getElementById('miCalendar');
  const cal = new FullCalendar.Calendar(calEl, {
    initialView: 'dayGridMonth',
    locale: 'id', height: 650,
    headerToolbar: {
      left: 'prev,next today', center: 'title',
      right: 'dayGridMonth,timeGridWeek,listWeek'
    },
    buttonText: { today:'Hari ini', month:'Bulan', week:'Minggu', list:'Agenda' },
    events: { url: <?= json_encode($calendarApiUrl) ?>, failure: () => console.warn('Gagal load events kalender') },
    eventClick: info => window.location.href = <?= json_encode($meetingBaseUrl) ?> + info.event.id,
    eventDidMount: info => {
      const loc = info.event.extendedProps.location || 'Lokasi belum diset';
      info.el.title = info.event.title + '\n📍 ' + loc;
    }
  });
  cal.render();

  /* ── Stat card filter ── */
  document.querySelectorAll('[data-filter-stat]').forEach(card => {
    card.addEventListener('click', () => {
      const sf = document.getElementById('miStatusFilter');
      if (!sf) return;
      // Switch to list view
      vtabs.forEach(t => t.classList.remove('active'));
      document.querySelector('[data-view="list"]').classList.add('active');
      viewCal.style.display = 'none'; viewList.style.display = ''; listCtrl.style.display = '';
      sf.value = card.dataset.filterStat;
      filterTable();
    });
  });

  /* ── Search + Filter + Sort ── */
  const searchInput  = document.getElementById('miSearch');
  const filterSelect = document.getElementById('miStatusFilter');
  const sortSelect   = document.getElementById('miSortBy');
  const tbody        = document.getElementById('miTbody');
  const countLabel   = document.getElementById('miCountLabel');
  const noResults    = document.getElementById('miNoResults');
  const searchClear  = document.getElementById('miSearchClear');

  function filterTable() {
    if (!tbody) return;
    const q  = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const sf = filterSelect ? filterSelect.value : '';
    const sort = sortSelect ? sortSelect.value : 'newest';

    // Show/hide clear btn
    if (searchClear) searchClear.style.display = q ? 'block' : 'none';

    const rows = Array.from(tbody.querySelectorAll('tr.mi-row'));
    let visible = 0;

    rows.forEach(row => {
      const show = (!q || (row.dataset.title||'').includes(q)) &&
                   (!sf || row.dataset.status === sf);
      row.classList.toggle('mi-hidden', !show);
      if (show) visible++;
    });

    // Sort visible rows
    const visibleRows = rows.filter(r => !r.classList.contains('mi-hidden'));
    visibleRows.sort((a, b) => {
      if (sort === 'newest') return (b.dataset.ts || 0) - (a.dataset.ts || 0);
      if (sort === 'oldest') return (a.dataset.ts || 0) - (b.dataset.ts || 0);
      if (sort === 'alpha')  return (a.dataset.title||'').localeCompare(b.dataset.title||'');
      return 0;
    });
    visibleRows.forEach(r => tbody.appendChild(r));

    if (countLabel) countLabel.textContent = 'Menampilkan ' + visible + ' kegiatan';
    if (noResults)  noResults.style.display = visible === 0 ? 'flex' : 'none';
  }

  if (searchInput)  { searchInput.addEventListener('input', filterTable); }
  if (filterSelect) { filterSelect.addEventListener('change', filterTable); }
  if (sortSelect)   { sortSelect.addEventListener('change', filterTable); }
  if (searchClear)  { searchClear.addEventListener('click', () => { searchInput.value = ''; filterTable(); searchInput.focus(); }); }

  /* ── Participant counter ── */
  const pChecks = document.querySelectorAll('#miParticipants input[type=checkbox]');
  const pCount  = document.getElementById('miPCount');
  function updatePCount() {
    const n = document.querySelectorAll('#miParticipants input:checked').length;
    if (pCount) pCount.textContent = n + ' peserta dipilih';
  }
  pChecks.forEach(c => c.addEventListener('change', updatePCount));

  /* ── Color presets ── */
  document.querySelectorAll('.mi-color-preset').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('mtgColor').value = btn.dataset.color;
      document.querySelectorAll('.mi-color-preset').forEach(b => b.classList.remove('mi-active'));
      btn.classList.add('mi-active');
    });
  });
  // Init active
  const initColor = document.getElementById('mtgColor')?.value;
  document.querySelectorAll('.mi-color-preset').forEach(b => {
    if (b.dataset.color === initColor) b.classList.add('mi-active');
  });

  /* ── Start → End auto-fill ── */
  const miStart = document.getElementById('miStart');
  const miEnd   = document.getElementById('miEnd');
  if (miStart && miEnd) {
    miStart.addEventListener('change', () => {
      if (!miEnd.value || miEnd.value <= miStart.value) {
        const d = new Date(miStart.value);
        d.setHours(d.getHours() + 1);
        miEnd.value = d.toISOString().slice(0,16);
      }
    });
  }
});

/* ── Cascade department ── */
const _deptUrl = <?= json_encode($deptChildrenUrl) ?>;
async function fetchDeptChildren(pid) {
  try { return await (await fetch(_deptUrl + '?parent_id=' + pid)).json(); }
  catch(e) { return []; }
}
function syncMtgHidden() {
  const v = document.getElementById('mtg-u3').value ||
            document.getElementById('mtg-u2').value ||
            document.getElementById('mtg-u1').value || '';
  document.getElementById('mtg-dept-id').value = v;
}
async function cascadeMtg(level) {
  const s1 = document.getElementById('mtg-u1');
  const s2 = document.getElementById('mtg-u2');
  const s3 = document.getElementById('mtg-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">— Pilih unit dulu —</option>';
    s3.innerHTML = '<option value="">— Opsional —</option>';
    s2.disabled = s3.disabled = true; syncMtgHidden();
    if (!s1.value) return;
    const kids = await fetchDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">— Semua Bidang —</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncMtgHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">— Opsional —</option>'; s3.disabled = true; syncMtgHidden();
    if (!s2.value) return;
    const kids = await fetchDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">— Semua Sub Bidang —</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncMtgHidden();
  } else { syncMtgHidden(); }
}

/* ── Delete confirm ── */
function miConfirmDelete(id, title) {
  document.getElementById('miDelTitle').textContent = title;
  document.getElementById('miFormDel').action =
    <?= json_encode($baseUrl) ?> + '/meetings/' + id + '/delete';
  new bootstrap.Modal(document.getElementById('miModalDel')).show();
}
</script>
