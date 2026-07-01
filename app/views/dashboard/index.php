<?php
$baseUrl = rtrim(BASE_URL, '/');
$isAdmin = ($user['role'] === 'admin');
$hour    = (int)date('H');
$greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$nama     = htmlspecialchars($user['name'] ?? 'Pengguna');

$statCards = [
    ['key'=>'total_meetings', 'label'=>'Total Kegiatan',    'color'=>'#7B1C1C', 'bg'=>'#fdf2f2',
     'icon'=>'<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>'],
    ['key'=>'meeting_today',  'label'=>'Hari Ini',          'color'=>'#b45309', 'bg'=>'#fffbeb',
     'icon'=>'<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m9 16 2 2 4-4"/>'],
    ['key'=>'meeting_month',  'label'=>'Bulan '.date('M'),  'color'=>'#0369a1', 'bg'=>'#eff6ff',
     'icon'=>'<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/>'],
    ['key'=>'tl_pending',     'label'=>'Tugas Pending',     'color'=>'#c57a00', 'bg'=>'#fefce8',
     'icon'=>'<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
    ['key'=>'tl_overdue',     'label'=>'Terlambat',         'color'=>'#dc2626', 'bg'=>'#fef2f2',
     'icon'=>'<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><path d="M12 9v4M12 17h.01"/>'],
    ['key'=>'tl_done',        'label'=>'Tugas Selesai',     'color'=>'#059669', 'bg'=>'#f0fdf4',
     'icon'=>'<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>'],
];
if ($isAdmin) {
    $statCards[] = ['key'=>'total_users','label'=>'User Aktif','color'=>'#7c3aed','bg'=>'#f5f3ff',
        'icon'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'];
}
?>
<style>
/* ─── Reset & Base ─── */
.db { font-family: inherit; }

/* ─── Top Bar ─── */
.db-topbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1.75rem; gap: 1rem; flex-wrap: wrap;
}
.db-greeting-line { font-size: 1.25rem; font-weight: 800; color: #1a1a1a; line-height: 1.2; }
.db-greeting-sub  { font-size: 12.5px; color: #6b7280; margin-top: .2rem; }
.db-new-btn {
  display: inline-flex; align-items: center; gap: .45rem;
  background: #7B1C1C; color: #fff;
  font-size: 13px; font-weight: 700;
  padding: .55rem 1.1rem; border-radius: 8px;
  text-decoration: none; border: none; cursor: pointer;
  white-space: nowrap;
  transition: background .15s;
}
.db-new-btn:hover { background: #5a1212; color: #fff; }

/* ─── Stat Grid ─── */
.db-stats {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: .85rem;
  margin-bottom: 1.75rem;
}
.db-stat {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.1rem 1rem 1rem;
  position: relative;
  overflow: hidden;
  transition: box-shadow .15s, transform .15s;
}
.db-stat:hover { box-shadow: 0 6px 20px rgba(0,0,0,.09); transform: translateY(-2px); }
.db-stat-icon {
  width: 36px; height: 36px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: .75rem;
}
.db-stat-num  { font-size: 1.85rem; font-weight: 800; line-height: 1; color: #111827; letter-spacing: -.03em; }
.db-stat-lbl  { font-size: 11.5px; color: #6b7280; margin-top: .3rem; font-weight: 500; }
.db-stat-line {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px; border-radius: 0 0 12px 12px;
}

/* ─── 2-col grid ─── */
.db-grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.db-grid3 { display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 1rem; }
@media(max-width:900px) {
  .db-grid2, .db-grid3 { grid-template-columns: 1fr; }
}
@media(max-width:560px) {
  .db-stats { grid-template-columns: repeat(2, 1fr); }
  .db-stat-num { font-size: 1.5rem; }
}

/* ─── Panel / Card ─── */
.db-panel {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  overflow: hidden;
  display: flex; flex-direction: column;
}
.db-panel-head {
  display: flex; align-items: center; justify-content: space-between;
  padding: .85rem 1.1rem;
  border-bottom: 1px solid #f3f4f6;
  gap: .5rem;
}
.db-panel-title {
  display: flex; align-items: center; gap: .5rem;
  font-size: 13px; font-weight: 700; color: #111827;
}
.db-panel-title svg { color: #7B1C1C; flex-shrink: 0; }
.db-panel-link {
  font-size: 11.5px; color: #7B1C1C; font-weight: 600;
  text-decoration: none; white-space: nowrap;
  padding: .25rem .6rem; border-radius: 6px;
  border: 1px solid #f5d0d0;
  transition: background .13s;
}
.db-panel-link:hover { background: #fdf2f2; }
.db-panel-body { flex: 1; }

/* ─── List rows ─── */
.db-row {
  display: flex; align-items: flex-start; gap: .75rem;
  padding: .8rem 1.1rem;
  border-bottom: 1px solid #f9fafb;
  text-decoration: none; color: inherit;
  transition: background .12s;
}
.db-row:last-child { border-bottom: none; }
.db-row:hover { background: #fafafa; }
a.db-row:hover { background: #fdf9f9; }

/* Date badge */
.db-datebadge {
  min-width: 40px; text-align: center;
  background: #fdf2f2; border-radius: 8px;
  padding: .4rem .3rem; flex-shrink: 0;
}
.db-datebadge .d { font-size: 17px; font-weight: 800; color: #7B1C1C; line-height: 1; }
.db-datebadge .m { font-size: 9px; text-transform: uppercase; letter-spacing: .05em; color: #b91c1c; margin-top: 1px; }

/* Dot status */
.db-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }

/* Tags */
.db-tag {
  display: inline-flex; align-items: center;
  font-size: 10px; font-weight: 700; letter-spacing: .02em;
  padding: 2px 7px; border-radius: 99px;
}
.db-tag-today  { background: #fdf2f2; color: #7B1C1C; }
.db-tag-high   { background: #fee2e2; color: #dc2626; }
.db-tag-medium { background: #fef3c7; color: #b45309; }
.db-tag-low    { background: #d1fae5; color: #059669; }
.db-tag-overdue{ background: #fee2e2; color: #dc2626; }

/* Empty */
.db-empty {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  padding: 2.5rem 1rem; gap: .6rem;
  color: #9ca3af; font-size: 12.5px; text-align: center;
}
.db-empty svg { color: #e5e7eb; }

/* ─── Chart wrappers ─── */
.db-chart-wrap { padding: 1rem 1.1rem 1rem; }
.db-chart-year {
  font-size: 11.5px; color: #374151;
  border: 1px solid #e5e7eb; border-radius: 6px;
  padding: .25rem .5rem; background: #fff; cursor: pointer;
}
.db-chart-year:focus { outline: none; border-color: #7B1C1C; }

/* ─── Donut legend ─── */
.db-donut-legend {
  display: flex; flex-wrap: wrap; gap: .4rem .85rem;
  padding: .75rem 1.1rem;
  border-top: 1px solid #f3f4f6;
  font-size: 11.5px;
}
.db-donut-legend-item { display: flex; align-items: center; gap: .35rem; color: #6b7280; }
.db-donut-legend-item strong { color: #111827; }
.db-donut-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
</style>

<div class="db">

<!-- ─── Top Bar ─── -->
<div class="db-topbar">
  <div>
    <div class="db-greeting-line"><?= $greeting ?>, <?= $nama ?> 👋</div>
    <div class="db-greeting-sub"><?= date('l, d F Y') ?> &mdash; Ringkasan aktivitas Anda</div>
  </div>
  <a href="<?= $baseUrl ?>/meetings/create" class="db-new-btn d-none d-sm-inline-flex">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Buat Kegiatan
  </a>
</div>

<!-- ─── Stat Cards ─── -->
<div class="db-stats">
  <?php foreach ($statCards as $sc): ?>
  <div class="db-stat">
    <div class="db-stat-icon" style="background:<?= $sc['bg'] ?>;color:<?= $sc['color'] ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $sc['icon'] ?></svg>
    </div>
    <div class="db-stat-num"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
    <div class="db-stat-lbl"><?= $sc['label'] ?></div>
    <div class="db-stat-line" style="background:<?= $sc['color'] ?>"></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- ─── Row 1: Kegiatan Mendatang + Tindak Lanjut ─── -->
<div class="db-grid2">

  <!-- Kegiatan Mendatang -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
        Kegiatan Mendatang
        <span style="font-size:11px;font-weight:500;color:#9ca3af;">7 hari ke depan</span>
      </div>
      <a href="<?= $baseUrl ?>/meetings" class="db-panel-link">Semua →</a>
    </div>
    <div class="db-panel-body">
      <?php if (empty($upcoming)): ?>
      <div class="db-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
        Tidak ada kegiatan mendatang
      </div>
      <?php endif; ?>
      <?php foreach ($upcoming as $m):
        $dt      = new DateTime($m['start_datetime']);
        $isToday = $dt->format('Y-m-d') === date('Y-m-d');
      ?>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$m['id'] ?>" class="db-row">
        <div class="db-datebadge">
          <div class="d"><?= $dt->format('d') ?></div>
          <div class="m"><?= $dt->format('M') ?></div>
        </div>
        <div style="flex:1;overflow:hidden;">
          <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
            <span style="font-size:13px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px;">
              <?= htmlspecialchars($m['title']) ?>
            </span>
            <?php if ($isToday): ?>
            <span class="db-tag db-tag-today">Hari ini</span>
            <?php endif; ?>
          </div>
          <div style="font-size:11.5px;color:#6b7280;margin-top:3px;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
            <span><?= $dt->format('H:i') ?> WIB</span>
            <span style="color:#d1d5db">·</span>
            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;"><?= htmlspecialchars($m['location'] ?: '—') ?></span>
            <span style="color:#d1d5db">·</span>
            <span><?= (int)$m['total_peserta'] ?> peserta</span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tindak Lanjut Terdekat -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tindak Lanjut Terdekat
      </div>
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="db-panel-link">Semua →</a>
    </div>
    <div class="db-panel-body">
      <?php if (empty($tlDeadline)): ?>
      <div class="db-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
        Tidak ada tindak lanjut aktif
      </div>
      <?php endif; ?>
      <?php foreach ($tlDeadline as $tl):
        $isOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d');
        $prio      = $tl['priority'] ?? 'low';
        $prioLabel = ['high'=>'Tinggi','medium'=>'Sedang','low'=>'Rendah'][$prio] ?? ucfirst($prio);
        $dotColor  = ['high'=>'#dc2626','medium'=>'#d97706','low'=>'#10b981'][$prio] ?? '#9ca3af';
      ?>
      <div class="db-row" style="<?= $isOverdue ? 'background:#fff5f5;' : '' ?>">
        <div class="db-dot" style="background:<?= $dotColor ?>"></div>
        <div style="flex:1;overflow:hidden;">
          <div style="font-size:13px;font-weight:600;color:<?= $isOverdue ? '#dc2626' : '#111827' ?>;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
            <?= htmlspecialchars($tl['description'] ?? '') ?>
          </div>
          <div style="font-size:11.5px;color:#6b7280;margin-top:2px;">
            <?= htmlspecialchars($tl['meeting_title'] ?? '') ?> · <?= htmlspecialchars($tl['assigned_name'] ?? '—') ?>
          </div>
        </div>
        <div style="flex-shrink:0;text-align:right;">
          <?php if (!empty($tl['due_date'])): ?>
          <div style="font-size:11.5px;font-weight:600;color:<?= $isOverdue ? '#dc2626' : '#6b7280' ?>;white-space:nowrap;">
            <?= date('d M', strtotime($tl['due_date'])) ?><?= $isOverdue ? ' ⚠' : '' ?>
          </div>
          <?php endif; ?>
          <span class="db-tag db-tag-<?= $prio ?>" style="margin-top:3px;"><?= $prioLabel ?></span>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ─── Row 2: Bar Chart + Donut ─── -->
<div class="db-grid3">

  <!-- Bar: Kegiatan Per Bulan -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        Kegiatan Per Bulan
      </div>
      <select id="selYearBar" class="db-chart-year">
        <?php foreach ($availableYears as $yr): ?>
        <option value="<?= $yr ?>" <?= $yr==date('Y')?'selected':'' ?>><?= $yr ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="db-chart-wrap"><canvas id="cvBar" height="130"></canvas></div>
  </div>

  <!-- Donut: Status TL -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        Status Tindak Lanjut
      </div>
    </div>
    <div class="db-chart-wrap" style="display:flex;justify-content:center;padding-bottom:.5rem;">
      <div style="max-width:180px;width:100%;"><canvas id="cvDonut"></canvas></div>
    </div>
    <div class="db-donut-legend" id="donutLegend"></div>
  </div>

</div>

<!-- ─── Row 3: Line Chart + Top Dept ─── -->
<div class="db-grid3">

  <!-- Line: Tren TL -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Tren Tindak Lanjut
      </div>
      <select id="selYearLine" class="db-chart-year">
        <?php foreach ($availableYears as $yr): ?>
        <option value="<?= $yr ?>" <?= $yr==date('Y')?'selected':'' ?>><?= $yr ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="db-chart-wrap"><canvas id="cvLine" height="130"></canvas></div>
  </div>

  <!-- Horizontal Bar: Top Unit Kerja (admin only) -->
  <?php if ($isAdmin): ?>
  <div class="db-panel" id="panelTopDept">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        Top Unit Kerja
      </div>
    </div>
    <div class="db-chart-wrap"><canvas id="cvDept" height="200"></canvas></div>
    <div id="noDeptMsg" class="db-empty" style="display:none;">
      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect width="20" height="14" x="2" y="7" rx="2"/></svg>
      Data unit kerja belum tersedia
    </div>
  </div>
  <?php endif; ?>

</div>

</div><!-- /db -->

<script>
(function(){
  'use strict';
  const BASE   = <?= json_encode($baseUrl) ?>;
  const MONTHS = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  /* Palet */
  const C = {
    red:     '#7B1C1C',
    redA:    'rgba(123,28,28,.12)',
    amber:   '#d97706',
    green:   '#059669',
    greenA:  'rgba(5,150,105,.12)',
    danger:  '#dc2626',
    dangerA: 'rgba(220,38,38,.10)',
    blue:    '#2563eb',
    teal:    '#0d9488',
    purple:  '#7c3aed',
    gray:    '#9ca3af',
    grid:    'rgba(0,0,0,.05)',
  };

  const baseOpts = {
    responsive: true,
    maintainAspectRatio: true,
    plugins: { legend: { display: false } },
    scales: {
      x: { grid: { display: false }, ticks: { color:'#6b7280', font:{ size:11 } } },
      y: { beginAtZero: true, ticks: { precision:0, stepSize:1, color:'#6b7280', font:{ size:11 } }, grid: { color: C.grid } }
    }
  };

  /* ── Bar: Kegiatan Per Bulan ── */
  let cvBar, chartBar;
  async function loadBar(year) {
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-monthly?year='+year)).json();
      cvBar = document.getElementById('cvBar');
      if (chartBar) chartBar.destroy();
      const curMonth = new Date().getMonth();
      chartBar = new Chart(cvBar.getContext('2d'), {
        type: 'bar',
        data: {
          labels: MONTHS,
          datasets: [{
            data: d.data,
            backgroundColor: d.data.map((_,i) => i===curMonth && d.year==new Date().getFullYear() ? C.red : 'rgba(123,28,28,.18)'),
            borderRadius: 5,
            borderSkipped: false,
          }]
        },
        options: {
          ...baseOpts,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c => ' '+c.parsed.y+' kegiatan' } }
          }
        }
      });
    } catch(e){}
  }
  const selBar = document.getElementById('selYearBar');
  loadBar(selBar.value);
  selBar.addEventListener('change', ()=>loadBar(selBar.value));

  /* ── Donut: Status TL ── */
  async function loadDonut() {
    try {
      const d      = await (await fetch(BASE+'/api/dashboard/chart-tl-status')).json();
      const keys   = ['pending','in_progress','done','cancelled','overdue'];
      const labels = ['Pending','Dikerjakan','Selesai','Dibatalkan','Terlambat'];
      const colors = [C.amber, C.teal, C.green, C.gray, C.danger];
      const vals   = keys.map(k => d.data[k]||0);

      new Chart(document.getElementById('cvDonut').getContext('2d'), {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{ data: vals, backgroundColor: colors, borderWidth: 2, borderColor:'#fff', hoverOffset:5 }]
        },
        options: {
          responsive: true,
          cutout: '70%',
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c=>' '+c.label+': '+c.parsed } }
          }
        }
      });

      document.getElementById('donutLegend').innerHTML = keys.map((k,i)=>
        `<div class="db-donut-legend-item">
          <div class="db-donut-dot" style="background:${colors[i]}"></div>
          <span>${labels[i]}</span>
          <strong>${vals[i]}</strong>
        </div>`
      ).join('');
    } catch(e){}
  }
  loadDonut();

  /* ── Line: Tren TL ── */
  let chartLine;
  async function loadLine(year) {
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-tl-trend?year='+year)).json();
      if (chartLine) chartLine.destroy();
      chartLine = new Chart(document.getElementById('cvLine').getContext('2d'), {
        type: 'line',
        data: {
          labels: MONTHS,
          datasets: [
            {
              label: 'Selesai',
              data: d.done,
              borderColor: C.green, backgroundColor: C.greenA,
              tension: .4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2,
            },
            {
              label: 'Terlambat',
              data: d.overdue,
              borderColor: C.danger, backgroundColor: C.dangerA,
              tension: .4, fill: true, pointRadius: 3, pointHoverRadius: 6, borderWidth: 2,
            }
          ]
        },
        options: {
          ...baseOpts,
          plugins: {
            legend: {
              display: true, position: 'top',
              labels: { boxWidth: 9, font:{ size:11 }, color:'#374151', padding: 12 }
            },
            tooltip: { mode:'index', intersect: false }
          },
          interaction: { mode:'nearest', axis:'x', intersect: false }
        }
      });
    } catch(e){}
  }
  const selLine = document.getElementById('selYearLine');
  loadLine(selLine.value);
  selLine.addEventListener('change', ()=>loadLine(selLine.value));

  /* ── Hbar: Top Unit Kerja ── */
  async function loadDept() {
    const wrap = document.getElementById('panelTopDept');
    if (!wrap) return;
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-top-dept')).json();
      if (!d.labels?.length) {
        document.getElementById('cvDept').style.display='none';
        document.getElementById('noDeptMsg').style.display='flex';
        return;
      }
      const palette = [C.red,'#b45309',C.blue,C.teal,C.purple,C.green,C.danger,C.amber];
      new Chart(document.getElementById('cvDept').getContext('2d'), {
        type: 'bar',
        data: {
          labels: d.labels,
          datasets: [{
            data: d.data,
            backgroundColor: d.labels.map((_,i)=>palette[i%palette.length]+'BB'),
            borderColor:     d.labels.map((_,i)=>palette[i%palette.length]),
            borderWidth: 1, borderRadius: 4, borderSkipped: false,
          }]
        },
        options: {
          indexAxis: 'y',
          responsive: true,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: c=>' '+c.parsed.x+' meeting' } }
          },
          scales: {
            x: { beginAtZero: true, ticks: { precision:0, stepSize:1, color:'#6b7280', font:{size:11} }, grid:{color:C.grid} },
            y: { grid: { display:false }, ticks: { color:'#6b7280', font:{size:11} } }
          }
        }
      });
    } catch(e){}
  }
  loadDept();
})();
</script>
