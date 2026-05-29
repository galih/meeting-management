<?php
$baseUrl    = rtrim(BASE_URL, '/');
$bulanIni   = date('F Y'); // contoh: May 2026
$statConfig = [
    ['key'=>'total_meetings',  'label'=>'Total Kegiatan',       'color'=>'blue',   'icon'=>'📅'],
    ['key'=>'meeting_today',   'label'=>'Kegiatan Hari Ini',    'color'=>'orange', 'icon'=>'🗓️'],
    ['key'=>'meeting_month',   'label'=>'Kegiatan Bulan Ini',   'color'=>'cyan',   'icon'=>'📆'],
    ['key'=>'tl_pending',      'label'=>'Tugas Pending',        'color'=>'yellow', 'icon'=>'⏳'],
    ['key'=>'tl_overdue',      'label'=>'Tugas Terlambat',      'color'=>'red',    'icon'=>'⚠️'],
    ['key'=>'tl_done',         'label'=>'Tugas Selesai',        'color'=>'green',  'icon'=>'✅'],
    ['key'=>'notif_unread',    'label'=>'Notif Belum Dibaca',   'color'=>'purple', 'icon'=>'🔔'],
];
if ($user['role'] === 'admin') {
    array_splice($statConfig, 3, 0, [
        ['key'=>'total_users','label'=>'User Aktif','color'=>'teal','icon'=>'👥'],
    ]);
}
?>

<!-- Stat Cards -->
<div class="row row-deck row-cards g-3 mb-4">
  <?php foreach ($statConfig as $sc): ?>
  <div class="col-6 col-lg-3">
    <div class="card stat-card position-relative">
      <div class="card-body">
        <div class="subheader"><?= htmlspecialchars($sc['label']) ?></div>
        <?php if ($sc['key'] === 'meeting_month'): ?>
        <div class="h1"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
        <div class="stat-footer">
          <span class="status-dot status-dot-animated bg-<?= $sc['color'] ?>"></span>
          <span class="text-muted"><?= date('M Y') ?></span>
        </div>
        <?php else: ?>
        <div class="h1"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
        <div class="stat-footer">
          <span class="status-dot status-dot-animated bg-<?= $sc['color'] ?>"></span>
          <span class="text-muted"><?= htmlspecialchars($sc['label']) ?></span>
        </div>
        <?php endif; ?>
        <div class="stat-icon"><?= $sc['icon'] ?></div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="row row-cards g-3">

  <!-- Kegiatan Mendatang -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
          Kegiatan Mendatang <span class="text-muted fw-normal ms-1" style="font-size:12px;">7 hari ke depan</span>
        </h3>
        <div class="card-options">
          <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($upcoming)): ?>
        <div class="list-group-item text-center text-muted py-5">
          <div style="font-size:32px;margin-bottom:.4rem;">📭</div>
          Tidak ada kegiatan mendatang
        </div>
        <?php endif; ?>
        <?php foreach ($upcoming as $m):
          $start   = new DateTime($m['start_datetime']);
          $isToday = $start->format('Y-m-d') === date('Y-m-d');
        ?>
        <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>" class="list-group-item list-group-item-action">
          <div class="row align-items-center g-2">
            <div class="col-auto">
              <div class="text-center" style="width:44px;background:var(--brand-light);border-radius:8px;padding:6px 4px;">
                <div class="fw-bold lh-1" style="font-size:20px;color:var(--brand);"><?= $start->format('d') ?></div>
                <div class="text-muted" style="font-size:9px;text-transform:uppercase;letter-spacing:.05em;"><?= $start->format('M') ?></div>
              </div>
            </div>
            <div class="col">
              <div class="d-flex justify-content-between align-items-start gap-2">
                <span class="fw-semibold" style="font-size:13.5px;"><?= htmlspecialchars($m['title']) ?></span>
                <?php if ($isToday): ?>
                <span class="badge bg-orange-lt text-orange" style="white-space:nowrap;">Hari ini</span>
                <?php endif; ?>
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:2px;">
                🕐 <?= $start->format('H:i') ?>
                &nbsp;·&nbsp;
                📍 <?= htmlspecialchars($m['location'] ?: 'Lokasi belum diset') ?>
                &nbsp;·&nbsp;
                👥 <?= (int)$m['total_peserta'] ?> peserta
              </div>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Tindak Lanjut Deadline Terdekat -->
  <div class="col-lg-6">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="9 11 12 14 22 4"/>
            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
          </svg>
          Tindak Lanjut Terdekat
        </h3>
        <div class="card-options">
          <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
        </div>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($tlDeadline)): ?>
        <div class="list-group-item text-center text-muted py-5">
          <div style="font-size:32px;margin-bottom:.4rem;">🎉</div>
          Tidak ada tindak lanjut aktif
        </div>
        <?php endif; ?>
        <?php foreach ($tlDeadline as $tl):
          $isOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d');
        ?>
        <div class="list-group-item <?= $isOverdue ? 'bg-red-lt' : '' ?>">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div class="flex-fill overflow-hidden">
              <div class="fw-semibold text-truncate" style="font-size:13.5px;">
                <?= htmlspecialchars($tl['description'] ?? '') ?>
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:2px;">
                📋 <?= htmlspecialchars($tl['meeting_title']) ?>
                &nbsp;·&nbsp;
                👤 <?= htmlspecialchars($tl['assigned_name'] ?? 'Belum ditugaskan') ?>
              </div>
            </div>
            <div class="text-end flex-shrink-0">
              <?php if (!empty($tl['due_date'])): ?>
              <div class="<?= $isOverdue ? 'text-danger fw-bold' : 'text-muted' ?>" style="font-size:12px;">
                <?= date('d M', strtotime($tl['due_date'])) ?><?= $isOverdue ? ' ⚠️' : '' ?>
              </div>
              <?php endif; ?>
              <span class="badge bg-<?= match($tl['priority'] ?? '') {
                'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
              } ?>-lt"><?= ucfirst($tl['priority'] ?? '-') ?></span>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- ===== ANALYTICS ROW 1: Meeting Per Bulan + Status TL ===== -->
<div class="row mt-4 g-3">

  <!-- Chart: Meeting Per Bulan -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <line x1="18" y1="20" x2="18" y2="10"/>
            <line x1="12" y1="20" x2="12" y2="4"/>
            <line x1="6" y1="20" x2="6" y2="14"/>
            <line x1="2" y1="20" x2="22" y2="20"/>
          </svg>
          Kegiatan Per Bulan
        </h3>
        <div class="card-options">
          <select id="chartYearSelect" class="form-select form-select-sm" style="width:auto;">
            <?php foreach ($availableYears as $yr): ?>
            <option value="<?= $yr ?>" <?= $yr == date('Y') ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="card-body">
        <canvas id="chartKegiatan" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart: Distribusi Status Tindak Lanjut (Donut) -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 2a10 10 0 0 1 10 10"/>
          </svg>
          Status Tindak Lanjut
        </h3>
      </div>
      <div class="card-body d-flex align-items-center justify-content-center">
        <div style="max-width:220px;width:100%;">
          <canvas id="chartTlStatus"></canvas>
        </div>
      </div>
      <div class="card-footer py-2" id="tl-status-legend" style="font-size:12px;"></div>
    </div>
  </div>

</div>

<!-- ===== ANALYTICS ROW 2: Tren TL + Top Unit Kerja ===== -->
<div class="row mt-3 g-3">

  <!-- Chart: Tren Selesai vs Terlambat -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          Tren Tindak Lanjut
        </h3>
        <div class="card-options">
          <select id="trendYearSelect" class="form-select form-select-sm" style="width:auto;">
            <?php foreach ($availableYears as $yr): ?>
            <option value="<?= $yr ?>" <?= $yr == date('Y') ? 'selected' : '' ?>><?= $yr ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="card-body">
        <canvas id="chartTlTrend" height="120"></canvas>
      </div>
    </div>
  </div>

  <!-- Chart: Top Unit Kerja (admin only) -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18" viewBox="0 0 24 24"
               fill="none" stroke="#f76707" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          Top Unit Kerja
        </h3>
      </div>
      <div class="card-body">
        <canvas id="chartTopDept" height="180"></canvas>
        <div id="no-dept-data" class="text-center text-muted py-4 d-none" style="font-size:13px;">
          📊 Data Unit Kerja belum tersedia
        </div>
      </div>
    </div>
  </div>

</div>

<script>
(function () {
  const BASE     = <?= json_encode($baseUrl) ?>;
  const months   = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
  const isAdmin  = <?= json_encode($user['role'] === 'admin') ?>;

  function defaultChartOptions(opts = {}) {
    return Object.assign({
      responsive: true,
      maintainAspectRatio: true,
      plugins: { legend: { display: false } },
    }, opts);
  }

  // 1. Chart Kegiatan Per Bulan (Bar)
  let chartKegiatan;
  async function loadChartKegiatan(year) {
    const res  = await fetch(BASE + '/api/dashboard/chart-monthly?year=' + year);
    const json = await res.json();
    const ctx  = document.getElementById('chartKegiatan').getContext('2d');
    if (chartKegiatan) chartKegiatan.destroy();
    chartKegiatan = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: months,
        datasets: [{
          label: 'Kegiatan ' + json.year,
          data: json.data,
          backgroundColor: 'rgba(123,28,28,0.75)',
          borderColor: '#7B1C1C',
          borderWidth: 1,
          borderRadius: 4,
        }]
      },
      options: defaultChartOptions({
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: c => ' ' + c.parsed.y + ' kegiatan' } }
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,.06)' } },
          x: { grid: { display: false } }
        }
      })
    });
  }
  const selYear = document.getElementById('chartYearSelect');
  loadChartKegiatan(selYear.value);
  selYear.addEventListener('change', () => loadChartKegiatan(selYear.value));

  // 2. Chart Status TL (Donut)
  async function loadChartTlStatus() {
    const res  = await fetch(BASE + '/api/dashboard/chart-tl-status');
    const json = await res.json();
    const d    = json.data;
    const labels  = ['Pending', 'Dikerjakan', 'Selesai', 'Dibatalkan', 'Terlambat'];
    const keys    = ['pending', 'in_progress', 'done', 'cancelled', 'overdue'];
    const colors  = ['#f59f00','#4263eb','#2fb344','#adb5bd','#d63939'];
    const values  = keys.map(k => d[k] || 0);
    const ctx     = document.getElementById('chartTlStatus').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{ data: values, backgroundColor: colors, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }]
      },
      options: {
        responsive: true,
        cutout: '65%',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: c => ' ' + c.label + ': ' + c.parsed } }
        }
      }
    });
    const lg = document.getElementById('tl-status-legend');
    lg.innerHTML = keys.map((k, i) =>
      `<span class="d-inline-flex align-items-center gap-1 me-2 mb-1">
        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${colors[i]};"></span>
        <span class="text-muted">${labels[i]}</span>
        <strong>${values[i]}</strong>
      </span>`
    ).join('');
  }
  loadChartTlStatus();

  // 3. Chart Tren TL (Line)
  let chartTrend;
  async function loadChartTrend(year) {
    const res  = await fetch(BASE + '/api/dashboard/chart-tl-trend?year=' + year);
    const json = await res.json();
    const ctx  = document.getElementById('chartTlTrend').getContext('2d');
    if (chartTrend) chartTrend.destroy();
    chartTrend = new Chart(ctx, {
      type: 'line',
      data: {
        labels: months,
        datasets: [
          {
            label: 'Selesai',
            data: json.done,
            borderColor: '#2fb344',
            backgroundColor: 'rgba(47,179,68,.12)',
            tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6,
          },
          {
            label: 'Terlambat',
            data: json.overdue,
            borderColor: '#d63939',
            backgroundColor: 'rgba(214,57,57,.10)',
            tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6,
          }
        ]
      },
      options: defaultChartOptions({
        plugins: {
          legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 12 } } },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,.06)' } },
          x: { grid: { display: false } }
        },
        interaction: { mode: 'nearest', axis: 'x', intersect: false }
      })
    });
  }
  const selTrend = document.getElementById('trendYearSelect');
  loadChartTrend(selTrend.value);
  selTrend.addEventListener('change', () => loadChartTrend(selTrend.value));

  // 4. Chart Top Unit Kerja (Horizontal Bar, admin only)
  async function loadChartTopDept() {
    if (!isAdmin) {
      document.getElementById('chartTopDept').closest('.card').style.display = 'none';
      return;
    }
    const res  = await fetch(BASE + '/api/dashboard/chart-top-dept');
    const json = await res.json();
    if (!json.labels.length) {
      document.getElementById('chartTopDept').style.display = 'none';
      document.getElementById('no-dept-data').classList.remove('d-none');
      return;
    }
    const ctx = document.getElementById('chartTopDept').getContext('2d');
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: json.labels,
        datasets: [{
          label: 'Jumlah Meeting',
          data: json.data,
          backgroundColor: [
            'rgba(66,99,235,.75)','rgba(47,179,68,.75)',
            'rgba(245,159,0,.75)','rgba(214,57,57,.75)','rgba(123,28,28,.75)'
          ],
          borderRadius: 4, borderWidth: 0,
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
          x: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,.06)' } },
          y: { grid: { display: false }, ticks: { font: { size: 12 } } }
        }
      }
    });
  }
  loadChartTopDept();

})();
</script>
