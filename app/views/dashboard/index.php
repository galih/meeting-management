<?php
/**
 * Dashboard — view utama
 * Palet: Kementerian Kebudayaan RI (emas #C8922A · cokelat #5C3D1E)
 * Layout: Tabler CSS + custom CSS vars override
 */
$baseUrl  = rtrim(BASE_URL, '/');
$isAdmin  = ($user['role'] === 'admin');

// Stat card config — icon pakai SVG Lucide inline (tidak bergantung emoji)
$statConfig = [
    ['key' => 'total_meetings', 'label' => 'Total Kegiatan',     'icon' => 'calendar',       'variant' => 'gold'],
    ['key' => 'meeting_today',  'label' => 'Kegiatan Hari Ini',  'icon' => 'calendar-check', 'variant' => 'amber'],
    ['key' => 'meeting_month',  'label' => 'Bulan ' . date('M Y'),'icon' => 'calendar-days', 'variant' => 'brown'],
    ['key' => 'tl_pending',     'label' => 'Tugas Pending',       'icon' => 'clock',          'variant' => 'warn'],
    ['key' => 'tl_overdue',     'label' => 'Tugas Terlambat',     'icon' => 'alert-triangle', 'variant' => 'danger'],
    ['key' => 'tl_done',        'label' => 'Tugas Selesai',       'icon' => 'check-circle',   'variant' => 'success'],
    ['key' => 'notif_unread',   'label' => 'Notif Belum Dibaca',  'icon' => 'bell',           'variant' => 'purple'],
];
if ($isAdmin) {
    array_splice($statConfig, 3, 0, [[
        'key' => 'total_users', 'label' => 'User Aktif', 'icon' => 'users', 'variant' => 'teal',
    ]]);
}

// Icon SVG path data (Lucide) — hanya path yang diperlukan
$icons = [
    'calendar'       => '<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>',
    'calendar-check' => '<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m9 16 2 2 4-4"/>',
    'calendar-days'  => '<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/>',
    'clock'          => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'alert-triangle' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><path d="M12 9v4M12 17h.01"/>',
    'check-circle'   => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
    'bell'           => '<path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9M13.73 21a2 2 0 0 1-3.46 0"/>',
    'users'          => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
    'bar-chart'      => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/>',
    'activity'       => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'pie-chart'      => '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
    'briefcase'      => '<rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    'task'           => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
];

function svgIcon(array $icons, string $name, int $size = 16, string $cls = ''): string {
    $path = $icons[$name] ?? '<circle cx="12" cy="12" r="10"/>';
    $c    = $cls ? ' class="' . $cls . '"' : '';
    return '<svg xmlns="http://www.w3.org/2000/svg"' . $c . ' width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $path . '</svg>';
}

$tlPriorityColor = ['high' => 'danger', 'medium' => 'warning', 'low' => 'success'];
?>

<style>
/* ── Palet Kementerian Kebudayaan RI ── */
:root {
  --kb-gold:        #C8922A;
  --kb-gold-dark:   #A07020;
  --kb-gold-light:  #F5E6C8;
  --kb-gold-xlight: #FDF7ED;
  --kb-brown:       #5C3D1E;
  --kb-brown-dark:  #3D270F;
  --kb-brown-light: #C8A882;
  --kb-amber:       #E0A020;
  --kb-cream:       #FBF5E8;

  /* Semantic aliases untuk komponen */
  --brand:          var(--kb-gold);
  --brand-dark:     var(--kb-gold-dark);
  --brand-light:    var(--kb-gold-light);
  --brand-xlight:   var(--kb-gold-xlight);
  --brand-accent:   var(--kb-brown);
}

/* ── Override Tabler brand color ── */
.btn-primary,
.bg-primary { background-color: var(--kb-gold) !important; border-color: var(--kb-gold-dark) !important; color: #fff !important; }
.btn-primary:hover { background-color: var(--kb-gold-dark) !important; }
.text-primary { color: var(--kb-gold) !important; }
.border-primary { border-color: var(--kb-gold) !important; }

/* ── Stat Cards ── */
.kb-stat {
  border-radius: 12px;
  border: 1px solid rgba(200,146,42,.18);
  background: #fff;
  padding: 20px 22px 16px;
  position: relative;
  overflow: hidden;
  transition: box-shadow .18s, transform .18s;
  box-shadow: 0 1px 4px rgba(92,61,30,.08);
}
.kb-stat:hover { box-shadow: 0 6px 22px rgba(92,61,30,.13); transform: translateY(-2px); }
.kb-stat-icon {
  width: 42px; height: 42px;
  border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 14px;
  flex-shrink: 0;
}
.kb-stat-value {
  font-size: 2rem;
  font-weight: 700;
  line-height: 1;
  color: var(--kb-brown);
  letter-spacing: -.02em;
}
.kb-stat-label {
  font-size: 12px;
  color: #6b7280;
  margin-top: 4px;
  font-weight: 500;
  letter-spacing: .01em;
}
.kb-stat-bar {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 3px;
  border-radius: 0 0 12px 12px;
}

/* Stat variant colours */
.kb-v-gold   .kb-stat-icon { background: var(--kb-gold-light);  color: var(--kb-gold-dark); }
.kb-v-gold   .kb-stat-bar  { background: var(--kb-gold); }
.kb-v-amber  .kb-stat-icon { background: #fef3c7; color: #d97706; }
.kb-v-amber  .kb-stat-bar  { background: #f59e0b; }
.kb-v-brown  .kb-stat-icon { background: #e8d5c0; color: var(--kb-brown); }
.kb-v-brown  .kb-stat-bar  { background: var(--kb-brown); }
.kb-v-warn   .kb-stat-icon { background: #fff3cd; color: #c57a00; }
.kb-v-warn   .kb-stat-bar  { background: #f59e0b; }
.kb-v-danger .kb-stat-icon { background: #fee2e2; color: #dc2626; }
.kb-v-danger .kb-stat-bar  { background: #ef4444; }
.kb-v-success .kb-stat-icon { background: #d1fae5; color: #059669; }
.kb-v-success .kb-stat-bar  { background: #10b981; }
.kb-v-purple .kb-stat-icon { background: #ede9fe; color: #7c3aed; }
.kb-v-purple .kb-stat-bar  { background: #8b5cf6; }
.kb-v-teal   .kb-stat-icon { background: #ccfbf1; color: #0d9488; }
.kb-v-teal   .kb-stat-bar  { background: #14b8a6; }

/* ── Section headers ── */
.kb-section-title {
  font-size: 13.5px;
  font-weight: 700;
  letter-spacing: .04em;
  text-transform: uppercase;
  color: var(--kb-brown);
  display: flex;
  align-items: center;
  gap: 6px;
  padding-bottom: 10px;
  border-bottom: 2px solid var(--kb-gold-light);
  margin-bottom: 0;
}
.kb-section-title .dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--kb-gold);
  flex-shrink: 0;
}

/* ── Card overrides ── */
.kb-card {
  border-radius: 12px;
  border: 1px solid rgba(200,146,42,.15);
  box-shadow: 0 1px 4px rgba(92,61,30,.06);
  overflow: hidden;
}
.kb-card .card-header {
  background: var(--kb-gold-xlight);
  border-bottom: 1px solid var(--kb-gold-light);
  padding: 14px 18px;
}
.kb-card .card-title {
  font-size: 13.5px;
  font-weight: 700;
  color: var(--kb-brown);
  display: flex;
  align-items: center;
  gap: 6px;
  margin: 0;
}
.kb-card .card-title svg { color: var(--kb-gold); flex-shrink: 0; }

/* ── List items ── */
.kb-list-item {
  display: block;
  padding: 12px 18px;
  border-bottom: 1px solid #f3f0ec;
  text-decoration: none;
  color: inherit;
  transition: background .14s;
}
.kb-list-item:last-child { border-bottom: none; }
.kb-list-item:hover { background: var(--kb-gold-xlight); }

.kb-date-badge {
  width: 46px;
  text-align: center;
  background: var(--kb-gold-light);
  border-radius: 8px;
  padding: 6px 4px;
  flex-shrink: 0;
}
.kb-date-badge .day {
  font-size: 20px;
  font-weight: 700;
  line-height: 1;
  color: var(--kb-brown);
}
.kb-date-badge .mon {
  font-size: 9px;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: var(--kb-gold-dark);
  margin-top: 1px;
}

/* ── Priority badge ── */
.kb-priority {
  display: inline-flex;
  align-items: center;
  gap: 3px;
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 99px;
}
.kb-priority-high   { background: #fee2e2; color: #dc2626; }
.kb-priority-medium { background: #fef3c7; color: #b45309; }
.kb-priority-low    { background: #d1fae5; color: #059669; }

/* ── Today badge ── */
.kb-today-badge {
  font-size: 10.5px;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 99px;
  background: var(--kb-gold-light);
  color: var(--kb-brown);
  white-space: nowrap;
}

/* ── Empty state ── */
.kb-empty {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  color: #9ca3af;
  font-size: 13px;
  text-align: center;
  gap: 8px;
}
.kb-empty svg { color: var(--kb-gold-light); }

/* ── Chart year select ── */
.kb-year-select {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 6px;
  border: 1px solid var(--kb-gold-light);
  background: #fff;
  color: var(--kb-brown);
  cursor: pointer;
}
.kb-year-select:focus { outline: none; border-color: var(--kb-gold); }

/* ── Overdue row ── */
.kb-overdue-row { background: #fff5f5; }

/* ── Responsive grid ── */
@media (max-width: 576px) {
  .kb-stat-value { font-size: 1.6rem; }
  .kb-stat { padding: 16px 16px 14px; }
}
</style>

<!-- ══════════════════════════════════════════
     GREETING + PAGE HEADER
══════════════════════════════════════════ -->
<?php
$hour     = (int)date('H');
$greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$nama     = htmlspecialchars($user['name'] ?? 'Pengguna');
?>
<div class="d-flex align-items-center justify-content-between mb-4 mt-2">
  <div>
    <h2 class="mb-0 fw-bold" style="color:var(--kb-brown);font-size:1.35rem;">
      <?= $greeting ?>, <?= $nama ?> 👋
    </h2>
    <p class="text-muted mb-0" style="font-size:13px;">
      <?= date('l, d F Y') ?> &mdash; Ringkasan aktivitas Anda hari ini
    </p>
  </div>
  <a href="<?= $baseUrl ?>/meetings/create"
     class="btn btn-primary d-none d-md-inline-flex align-items-center gap-2">
    <?= svgIcon($icons, 'calendar', 15) ?>
    Buat Kegiatan
  </a>
</div>

<!-- ══════════════════════════════════════════
     STAT CARDS
══════════════════════════════════════════ -->
<div class="row g-3 mb-4">
  <?php foreach ($statConfig as $sc): ?>
  <div class="col-6 col-md-4 col-lg-3">
    <div class="kb-stat kb-v-<?= $sc['variant'] ?>">
      <div class="kb-stat-icon">
        <?= svgIcon($icons, $sc['icon'], 20) ?>
      </div>
      <div class="kb-stat-value"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
      <div class="kb-stat-label"><?= htmlspecialchars($sc['label']) ?></div>
      <div class="kb-stat-bar"></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ══════════════════════════════════════════
     ROW 1: Kegiatan Mendatang + Tindak Lanjut
══════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <!-- Kegiatan Mendatang -->
  <div class="col-lg-6">
    <div class="card kb-card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <?= svgIcon($icons, 'calendar', 16) ?>
          Kegiatan Mendatang
          <span class="text-muted fw-normal ms-1" style="font-size:11px;text-transform:none;letter-spacing:0;">7 hari ke depan</span>
        </h3>
        <a href="<?= $baseUrl ?>/meetings"
           class="btn btn-sm" style="font-size:11.5px;color:var(--kb-gold-dark);padding:3px 10px;border:1px solid var(--kb-gold-light);border-radius:6px;text-decoration:none;">
          Lihat Semua
        </a>
      </div>

      <div class="list-group list-group-flush" style="min-height:120px;">
        <?php if (empty($upcoming)): ?>
        <div class="kb-empty">
          <?= svgIcon($icons, 'calendar', 40) ?>
          <span>Tidak ada kegiatan mendatang</span>
        </div>
        <?php endif; ?>

        <?php foreach ($upcoming as $m):
          $start   = new DateTime($m['start_datetime']);
          $isToday = $start->format('Y-m-d') === date('Y-m-d');
        ?>
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$m['id'] ?>" class="kb-list-item">
          <div class="d-flex align-items-center gap-3">
            <div class="kb-date-badge">
              <div class="day"><?= $start->format('d') ?></div>
              <div class="mon"><?= $start->format('M') ?></div>
            </div>
            <div class="flex-fill overflow-hidden">
              <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-semibold text-truncate" style="font-size:13.5px;color:#1f2937;">
                  <?= htmlspecialchars($m['title']) ?>
                </span>
                <?php if ($isToday): ?>
                <span class="kb-today-badge">Hari ini</span>
                <?php endif; ?>
              </div>
              <div class="text-muted d-flex align-items-center gap-2 flex-wrap" style="font-size:12px;margin-top:3px;">
                <span><?= $start->format('H:i') ?> WIB</span>
                <span style="color:#d1d5db;">·</span>
                <span class="text-truncate" style="max-width:150px;"><?= htmlspecialchars($m['location'] ?: 'Lokasi belum diset') ?></span>
                <span style="color:#d1d5db;">·</span>
                <span><?= (int)$m['total_peserta'] ?> peserta</span>
              </div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tindak Lanjut Terdekat -->
  <div class="col-lg-6">
    <div class="card kb-card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <?= svgIcon($icons, 'task', 16) ?>
          Tindak Lanjut Terdekat
        </h3>
        <a href="<?= $baseUrl ?>/tindak-lanjut"
           class="btn btn-sm" style="font-size:11.5px;color:var(--kb-gold-dark);padding:3px 10px;border:1px solid var(--kb-gold-light);border-radius:6px;text-decoration:none;">
          Lihat Semua
        </a>
      </div>

      <div class="list-group list-group-flush" style="min-height:120px;">
        <?php if (empty($tlDeadline)): ?>
        <div class="kb-empty">
          <?= svgIcon($icons, 'check-circle', 40) ?>
          <span>Tidak ada tindak lanjut aktif</span>
        </div>
        <?php endif; ?>

        <?php foreach ($tlDeadline as $tl):
          $isOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d');
          $prioKey   = $tl['priority'] ?? 'low';
          $prioCls   = 'kb-priority-' . $prioKey;
          $prioLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'][$prioKey] ?? ucfirst($prioKey);
        ?>
        <div class="kb-list-item <?= $isOverdue ? 'kb-overdue-row' : '' ?>">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-fill overflow-hidden">
              <div class="fw-semibold text-truncate" style="font-size:13.5px;color:<?= $isOverdue ? '#dc2626' : '#1f2937' ?>;">
                <?= htmlspecialchars($tl['description'] ?? '') ?>
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:3px;">
                <?= htmlspecialchars($tl['meeting_title'] ?? '') ?>
                &nbsp;·&nbsp;
                <?= htmlspecialchars($tl['assigned_name'] ?? 'Belum ditugaskan') ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <?php if (!empty($tl['due_date'])): ?>
              <div class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>" style="font-size:12px;white-space:nowrap;">
                <?= date('d M', strtotime($tl['due_date'])) ?>
                <?php if ($isOverdue): ?>
                <span style="font-size:10px;"> ⚠</span>
                <?php endif; ?>
              </div>
              <?php endif; ?>
              <span class="kb-priority <?= $prioCls ?> mt-1"><?= $prioLabel ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- ══════════════════════════════════════════
     ROW 2: Chart Kegiatan + Donut TL
══════════════════════════════════════════ -->
<div class="row g-3 mb-3">

  <!-- Bar Chart: Kegiatan Per Bulan -->
  <div class="col-lg-8">
    <div class="card kb-card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <?= svgIcon($icons, 'bar-chart', 16) ?>
          Kegiatan Per Bulan
        </h3>
        <select id="chartYearSelect" class="kb-year-select">
          <?php foreach ($availableYears as $yr): ?>
          <option value="<?= $yr ?>" <?= $yr == date('Y') ? 'selected' : '' ?>><?= $yr ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="card-body pb-3">
        <canvas id="chartKegiatan" height="110"></canvas>
      </div>
    </div>
  </div>

  <!-- Donut Chart: Status Tindak Lanjut -->
  <div class="col-lg-4">
    <div class="card kb-card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <?= svgIcon($icons, 'pie-chart', 16) ?>
          Status Tindak Lanjut
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center pb-0">
        <div style="max-width:200px;width:100%;">
          <canvas id="chartTlStatus"></canvas>
        </div>
      </div>
      <div class="card-footer py-3" id="tl-status-legend"
           style="font-size:11.5px;border-top:1px solid var(--kb-gold-light);background:var(--kb-gold-xlight);">
      </div>
    </div>
  </div>

</div>

<!-- ══════════════════════════════════════════
     ROW 3: Tren TL + Top Unit Kerja
══════════════════════════════════════════ -->
<div class="row g-3 mb-4">

  <!-- Line Chart: Tren Selesai vs Terlambat -->
  <div class="col-lg-8">
    <div class="card kb-card h-100">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h3 class="card-title">
          <?= svgIcon($icons, 'activity', 16) ?>
          Tren Tindak Lanjut
        </h3>
        <select id="trendYearSelect" class="kb-year-select">
          <?php foreach ($availableYears as $yr): ?>
          <option value="<?= $yr ?>" <?= $yr == date('Y') ? 'selected' : '' ?>><?= $yr ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="card-body pb-3">
        <canvas id="chartTlTrend" height="110"></canvas>
      </div>
    </div>
  </div>

  <!-- Horizontal Bar: Top Unit Kerja (admin only) -->
  <div class="col-lg-4" id="topDeptWrap">
    <div class="card kb-card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <?= svgIcon($icons, 'briefcase', 16) ?>
          Top Unit Kerja
        </h3>
      </div>
      <div class="card-body">
        <canvas id="chartTopDept" height="200"></canvas>
        <div id="no-dept-data" class="kb-empty d-none">
          <?= svgIcon($icons, 'briefcase', 36) ?>
          <span>Data Unit Kerja belum tersedia</span>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ══════════════════════════════════════════
     CHARTS JAVASCRIPT
══════════════════════════════════════════ -->
<script>
(function () {
  'use strict';

  const BASE    = <?= json_encode($baseUrl) ?>;
  const MONTHS  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  const IS_ADMIN = <?= json_encode($isAdmin) ?>;

  /* ── Palet Kemenbud untuk chart ── */
  const KB = {
    gold:        '#C8922A',
    goldDark:    '#A07020',
    goldLight:   'rgba(200,146,42,.15)',
    brown:       '#5C3D1E',
    brownLight:  'rgba(92,61,30,.12)',
    amber:       '#E0A020',
    success:     '#059669',
    successBg:   'rgba(5,150,105,.12)',
    danger:      '#dc2626',
    dangerBg:    'rgba(220,38,38,.10)',
    warn:        '#d97706',
    warnBg:      'rgba(217,119,6,.12)',
    purple:      '#7c3aed',
    teal:        '#0d9488',
    neutral:     '#9ca3af',
  };

  const GRID_COLOR = 'rgba(92,61,30,.07)';

  /* ── Chart: Kegiatan Per Bulan (Bar) ── */
  let chartKegiatan;
  async function loadChartKegiatan(year) {
    try {
      const res  = await fetch(BASE + '/api/dashboard/chart-monthly?year=' + year);
      const json = await res.json();
      const ctx  = document.getElementById('chartKegiatan').getContext('2d');
      if (chartKegiatan) chartKegiatan.destroy();
      chartKegiatan = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: MONTHS,
          datasets: [{
            label: 'Kegiatan ' + json.year,
            data: json.data,
            backgroundColor: MONTHS.map((_, i) =>
              i === (new Date().getMonth()) && json.year == new Date().getFullYear()
                ? KB.gold : KB.goldLight.replace('.15', '.55')),
            borderColor: KB.gold,
            borderWidth: 1,
            borderRadius: 5,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ' ' + c.parsed.y + ' kegiatan' } }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, precision: 0, color: KB.brown, font: { size: 11 } },
              grid: { color: GRID_COLOR }
            },
            x: {
              ticks: { color: KB.brown, font: { size: 11 } },
              grid: { display: false }
            }
          }
        }
      });
    } catch (e) { console.warn('chart-monthly error', e); }
  }

  const selYear = document.getElementById('chartYearSelect');
  loadChartKegiatan(selYear.value);
  selYear.addEventListener('change', () => loadChartKegiatan(selYear.value));

  /* ── Chart: Status TL (Donut) ── */
  async function loadChartTlStatus() {
    try {
      const res  = await fetch(BASE + '/api/dashboard/chart-tl-status');
      const json = await res.json();
      const d    = json.data;
      const labels = ['Pending', 'Dikerjakan', 'Selesai', 'Dibatalkan', 'Terlambat'];
      const keys   = ['pending', 'in_progress', 'done', 'cancelled', 'overdue'];
      const colors = [KB.warn, KB.teal, KB.success, KB.neutral, KB.danger];
      const values = keys.map(k => d[k] || 0);
      const ctx    = document.getElementById('chartTlStatus').getContext('2d');

      new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data: values,
            backgroundColor: colors,
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          cutout: '68%',
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.parsed } }
          }
        }
      });

      const lg = document.getElementById('tl-status-legend');
      lg.innerHTML = keys.map((k, i) =>
        '<span class="d-inline-flex align-items-center gap-1 me-2 mb-1">' +
          '<span style="display:inline-block;width:9px;height:9px;border-radius:50%;background:' + colors[i] + ';flex-shrink:0;"></span>' +
          '<span class="text-muted">' + labels[i] + '</span>' +
          '<strong style="color:var(--kb-brown);">' + values[i] + '</strong>' +
        '</span>'
      ).join('');
    } catch (e) { console.warn('chart-tl-status error', e); }
  }
  loadChartTlStatus();

  /* ── Chart: Tren TL (Line) ── */
  let chartTrend;
  async function loadChartTrend(year) {
    try {
      const res  = await fetch(BASE + '/api/dashboard/chart-tl-trend?year=' + year);
      const json = await res.json();
      const ctx  = document.getElementById('chartTlTrend').getContext('2d');
      if (chartTrend) chartTrend.destroy();
      chartTrend = new Chart(ctx, {
        type: 'line',
        data: {
          labels: MONTHS,
          datasets: [
            {
              label: 'Selesai',
              data: json.done,
              borderColor: KB.success,
              backgroundColor: KB.successBg,
              tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6,
              borderWidth: 2,
            },
            {
              label: 'Terlambat',
              data: json.overdue,
              borderColor: KB.danger,
              backgroundColor: KB.dangerBg,
              tension: 0.4, fill: true, pointRadius: 3, pointHoverRadius: 6,
              borderWidth: 2,
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: true,
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: { boxWidth: 10, font: { size: 11 }, color: KB.brown }
            },
            tooltip: { mode: 'index', intersect: false }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: { stepSize: 1, precision: 0, color: KB.brown, font: { size: 11 } },
              grid: { color: GRID_COLOR }
            },
            x: {
              ticks: { color: KB.brown, font: { size: 11 } },
              grid: { display: false }
            }
          },
          interaction: { mode: 'nearest', axis: 'x', intersect: false }
        }
      });
    } catch (e) { console.warn('chart-tl-trend error', e); }
  }

  const selTrend = document.getElementById('trendYearSelect');
  loadChartTrend(selTrend.value);
  selTrend.addEventListener('change', () => loadChartTrend(selTrend.value));

  /* ── Chart: Top Unit Kerja (Horizontal Bar, admin only) ── */
  async function loadChartTopDept() {
    if (!IS_ADMIN) {
      const wrap = document.getElementById('topDeptWrap');
      if (wrap) wrap.style.display = 'none';
      return;
    }
    try {
      const res  = await fetch(BASE + '/api/dashboard/chart-top-dept');
      const json = await res.json();

      if (!json.labels || !json.labels.length) {
        document.getElementById('chartTopDept').style.display = 'none';
        document.getElementById('no-dept-data').classList.remove('d-none');
        return;
      }

      const bgColors = [
        KB.gold, KB.brown, KB.amber, KB.teal, KB.purple,
        KB.success, KB.danger, KB.warn
      ].slice(0, json.labels.length);

      const ctx = document.getElementById('chartTopDept').getContext('2d');
      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: json.labels,
          datasets: [{
            label: 'Jumlah Meeting',
            data: json.data,
            backgroundColor: bgColors.map(c => c + 'CC'),
            borderColor: bgColors,
            borderWidth: 1,
            borderRadius: 4,
            borderSkipped: false,
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ' ' + c.parsed.x + ' meeting' } }
          },
          scales: {
            x: {
              beginAtZero: true,
              ticks: { stepSize: 1, precision: 0, color: KB.brown, font: { size: 11 } },
              grid: { color: GRID_COLOR }
            },
            y: {
              grid: { display: false },
              ticks: { color: KB.brown, font: { size: 11 } }
            }
          }
        }
      });
    } catch (e) { console.warn('chart-top-dept error', e); }
  }
  loadChartTopDept();

})();
</script>
