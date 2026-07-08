<?php
$baseUrl    = rtrim(BASE_URL, '/');
$levelLabel = [1 => 'Unit Kerja', 2 => 'Bidang / Bagian', 3 => 'Sub Bidang'];
$levelBadge = [1 => 'dept-badge-red', 2 => 'dept-badge-blue', 3 => 'dept-badge-green'];
$deptLevel  = $dept['level'] ?? 1;
?>

<style>
:root {
  --dp-primary:       #7B1C1C;
  --dp-primary-dark:  #5A1212;
  --dp-primary-light: rgba(123,28,28,.08);
  --dp-primary-ring:  rgba(123,28,28,.18);
  --dp-gold:          #C9A84C;
  --dp-surface:       #FBF8F3;
  --dp-border:        #DDD5C4;
  --dp-border-light:  #EDE8DE;
  --dp-text:          #1C1714;
  --dp-text-muted:    #6B6055;
  --dp-text-faint:    #A89E90;
  --dp-red:           #7B1C1C;
  --dp-red-bg:        rgba(123,28,28,.08);
  --dp-blue:          #1B4F82;
  --dp-blue-bg:       rgba(27,79,130,.08);
  --dp-green:         #2A6B3A;
  --dp-green-bg:      rgba(42,107,58,.08);
  --dp-radius:        12px;
  --dp-radius-sm:     8px;
  --dp-shadow-sm:     0 1px 4px rgba(28,23,20,.07);
  --dp-shadow-md:     0 3px 12px rgba(28,23,20,.09);
  --dp-transition:    180ms cubic-bezier(.16,1,.3,1);
}
.dh-wrap * { box-sizing: border-box; }

/* Hero */
.dh-hero {
  background: linear-gradient(135deg,#7B1C1C 0%,#9B2020 50%,#6A1515 100%);
  border-radius: var(--dp-radius); box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative; overflow: hidden; margin-bottom: 1.25rem;
}
.dh-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.dh-hero-inner {
  padding: 1.25rem 1.5rem 1.1rem; position: relative; z-index:1;
  display:flex; align-items:center; justify-content:space-between;
  gap:1rem; flex-wrap:wrap;
}
.dh-hero-left   { display:flex; align-items:center; gap:.85rem; }
.dh-hero-icon   {
  width:44px; height:44px; border-radius:var(--dp-radius-sm);
  background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.22);
  display:flex; align-items:center; justify-content:center;
  color:rgba(255,255,255,.9); flex-shrink:0;
}
.dh-hero-title  { font-size:clamp(16px,2vw,20px); font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.dh-hero-sub    { font-size:12px; color:rgba(255,255,255,.65); margin-top:.15rem; }
.dh-hero-bar    { height:3px; background:linear-gradient(90deg,var(--dp-gold) 0%,#A8872F 60%,transparent 100%); }

/* Buttons */
.dh-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:700; border-radius:var(--dp-radius-sm);
  padding:.38rem .9rem; cursor:pointer; transition:all var(--dp-transition);
  border:1.5px solid transparent; text-decoration:none; background:none;
}
.dh-btn-ghost { background:rgba(255,255,255,.12); color:#fff; border-color:rgba(255,255,255,.25); }
.dh-btn-ghost:hover { background:rgba(255,255,255,.22); color:#fff; }
.dh-btn-outline {
  background:#fff; color:var(--dp-primary);
  border-color:rgba(123,28,28,.3);
}
.dh-btn-outline:hover { background:var(--dp-primary-light); }

/* Stats */
.dh-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.65rem; margin-bottom:1.1rem; }
.dh-stat-card {
  background:#fff; border:1.5px solid var(--dp-border-light);
  border-radius:var(--dp-radius-sm); padding:.75rem 1rem;
  box-shadow:var(--dp-shadow-sm);
}
.dh-stat-label { font-size:11px; font-weight:700; color:var(--dp-text-faint); text-transform:uppercase; letter-spacing:.06em; }
.dh-stat-value { font-size:22px; font-weight:800; color:var(--dp-text); line-height:1.15; margin-top:.15rem; }
.dh-stat-sub   { font-size:11px; color:var(--dp-text-muted); margin-top:.1rem; }

/* Panel */
.dh-panel {
  background:#fff; border:1.5px solid var(--dp-border-light);
  border-radius:var(--dp-radius); box-shadow:var(--dp-shadow-sm);
  overflow:hidden; margin-bottom:1rem;
}
.dh-panel-header {
  display:flex; align-items:center; justify-content:space-between;
  gap:.75rem; padding:.85rem 1.25rem;
  background:var(--dp-surface); border-bottom:1px solid var(--dp-border-light);
  flex-wrap:wrap;
}
.dh-panel-title {
  font-size:13.5px; font-weight:800; color:var(--dp-primary);
  display:flex; align-items:center; gap:.45rem;
}

/* Table */
.dh-table { width:100%; border-collapse:collapse; font-size:13px; }
.dh-table thead tr { background:var(--dp-surface); }
.dh-table th {
  padding:.6rem 1rem; text-align:left;
  font-size:11px; font-weight:700; color:var(--dp-text-faint);
  text-transform:uppercase; letter-spacing:.05em;
  border-bottom:1px solid var(--dp-border-light);
}
.dh-table td {
  padding:.65rem 1rem; border-bottom:1px solid var(--dp-border-light);
  color:var(--dp-text); vertical-align:middle;
}
.dh-table tr:last-child td { border-bottom:none; }
.dh-table tbody tr:hover td { background:var(--dp-primary-light); }

/* Avatar */
.dh-avatar {
  width:30px; height:30px; border-radius:50%;
  background:var(--dp-primary); color:#fff;
  font-size:11px; font-weight:800;
  display:inline-flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.dh-user-cell { display:flex; align-items:center; gap:.6rem; }
.dh-user-name { font-weight:700; color:var(--dp-text); }
.dh-user-email { font-size:11px; color:var(--dp-text-muted); margin-top:.05rem; }

/* Badge */
.dh-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:10.5px; font-weight:700; padding:.2em .65em;
  border-radius:20px; white-space:nowrap;
}
.dept-badge-red   { background:var(--dp-red-bg);   color:var(--dp-red); }
.dept-badge-blue  { background:var(--dp-blue-bg);  color:var(--dp-blue); }
.dept-badge-green { background:var(--dp-green-bg); color:var(--dp-green); }
.dh-badge-assign  { background:var(--dp-green-bg); color:var(--dp-green); }
.dh-badge-remove  { background:var(--dp-red-bg);   color:var(--dp-red); }
.dh-badge-gray    { background:rgba(107,96,85,.1); color:var(--dp-text-muted); }

/* Empty */
.dh-empty { padding:3rem 1rem; text-align:center; color:var(--dp-text-faint); }
.dh-empty h4 { font-size:14px; color:var(--dp-text-muted); margin:.5rem 0 .25rem; font-weight:700; }
.dh-empty p  { font-size:12.5px; margin:0; }

/* Search */
.dh-search-wrap { position:relative; display:inline-flex; align-items:center; }
.dh-search-ico  { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:var(--dp-text-faint); pointer-events:none; }
.dh-search-input {
  border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  padding:.38rem 1.8rem .38rem 2rem; font-size:13px; color:var(--dp-text);
  background:#fff; outline:none; width:220px;
  transition:border-color var(--dp-transition);
}
.dh-search-input:focus { border-color:var(--dp-primary); box-shadow:0 0 0 3px var(--dp-primary-ring); }
.dh-search-clear {
  position:absolute; right:7px; top:50%; transform:translateY(-50%);
  background:none; border:none; cursor:pointer; color:var(--dp-text-faint);
  font-size:14px; line-height:1; padding:0; display:none;
}
.dh-search-clear:hover { color:var(--dp-text); }

@media(max-width:600px){
  .dh-hero-inner { flex-direction:column; align-items:flex-start; }
  .dh-search-input { width:100%; }
  .dh-panel-header { flex-direction:column; align-items:flex-start; }
  #tbl-log th:nth-child(4), #tbl-log td:nth-child(4),
  #tbl-log th:nth-child(5), #tbl-log td:nth-child(5) { display:none; }
}
</style>

<div class="dh-wrap">

<!-- Hero -->
<div class="dh-hero">
  <div class="dh-hero-inner">
    <div class="dh-hero-left">
      <div class="dh-hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      </div>
      <div>
        <div class="dh-hero-title">
          Riwayat Anggota &mdash; <?= htmlspecialchars($dept['name']) ?>
          <span class="dh-badge <?= $levelBadge[$deptLevel] ?? 'dept-badge-red' ?>" style="font-size:10px;">
            <?= $levelLabel[$deptLevel] ?? 'Unit' ?>
          </span>
        </div>
        <div class="dh-hero-sub">
          <?php if ($dept['head_name']): ?>
            Kepala: <?= htmlspecialchars($dept['head_name']) ?> &nbsp;&bull;&nbsp;
          <?php endif; ?>
          <?= count($logs) ?> entri riwayat
        </div>
      </div>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
      <a href="<?= $baseUrl ?>/departments/<?= (int)$dept['id'] ?>/members" class="dh-btn dh-btn-ghost">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        Kelola Anggota
      </a>
      <a href="<?= $baseUrl ?>/departments" class="dh-btn dh-btn-ghost">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
      </a>
    </div>
  </div>
  <div class="dh-hero-bar"></div>
</div>

<!-- Stats: Rapat Unit Kerja -->
<?php if ($meetingStats): ?>
<div class="dh-stats">
  <div class="dh-stat-card">
    <div class="dh-stat-label">Total Rapat</div>
    <div class="dh-stat-value" style="color:var(--dp-primary)"><?= (int)($meetingStats['total_meetings'] ?? 0) ?></div>
    <div class="dh-stat-sub">semua status</div>
  </div>
  <div class="dh-stat-card">
    <div class="dh-stat-label">Selesai</div>
    <div class="dh-stat-value" style="color:var(--dp-green)"><?= (int)($meetingStats['done_meetings'] ?? 0) ?></div>
    <div class="dh-stat-sub">rapat done</div>
  </div>
  <div class="dh-stat-card">
    <div class="dh-stat-label">Mendatang</div>
    <div class="dh-stat-value" style="color:var(--dp-blue)"><?= (int)($meetingStats['upcoming_meetings'] ?? 0) ?></div>
    <div class="dh-stat-sub">scheduled / ongoing</div>
  </div>
  <div class="dh-stat-card">
    <div class="dh-stat-label">Entri Riwayat</div>
    <div class="dh-stat-value" style="color:var(--dp-text-muted)"><?= count($logs) ?></div>
    <div class="dh-stat-sub">assign &amp; remove</div>
  </div>
</div>
<?php endif; ?>

<!-- Panel: Tabel Riwayat -->
<div class="dh-panel">
  <div class="dh-panel-header">
    <div class="dh-panel-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      Log Perpindahan Anggota
    </div>
    <div class="dh-search-wrap">
      <svg class="dh-search-ico" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="search-log" class="dh-search-input" placeholder="Cari nama, aksi, unit…"
             oninput="filterLog(this.value); toggleClear('clear-log', this.value)">
      <button type="button" id="clear-log" class="dh-search-clear" title="Hapus pencarian"
              onclick="clearLog()">&times;</button>
    </div>
  </div>

  <?php if (empty($logs)): ?>
  <div class="dh-empty">
    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;margin:0 auto;"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
    <h4>Belum ada riwayat</h4>
    <p>Log akan muncul setelah ada aksi assign atau remove anggota.</p>
  </div>
  <?php else: ?>
  <div style="overflow-x:auto;">
    <table class="dh-table" id="tbl-log">
      <thead>
        <tr>
          <th>Anggota</th>
          <th>Aksi</th>
          <th>Unit Asal</th>
          <th>Oleh</th>
          <th>Waktu</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
          <td>
            <div class="dh-user-cell">
              <div class="dh-avatar"><?= mb_strtoupper(mb_substr($log['user_name'] ?? '?', 0, 1)) ?></div>
              <div>
                <div class="dh-user-name"><?= htmlspecialchars($log['user_name'] ?? '(dihapus)') ?></div>
                <div class="dh-user-email"><?= htmlspecialchars($log['user_email'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td>
            <?php if ($log['action'] === 'assign'): ?>
              <span class="dh-badge dh-badge-assign">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Ditambahkan
              </span>
            <?php else: ?>
              <span class="dh-badge dh-badge-remove">
                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Dilepas
              </span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--dp-text-muted);">
            <?php if (!empty($log['from_dept_name'])): ?>
              <span class="dh-badge dh-badge-gray"><?= htmlspecialchars($log['from_dept_name']) ?></span>
            <?php else: ?>
              <span style="color:var(--dp-text-faint)">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:12px;color:var(--dp-text-muted);">
            <?= htmlspecialchars($log['actor_name'] ?? '—') ?>
          </td>
          <td style="font-size:12px;color:var(--dp-text-muted);white-space:nowrap;">
            <?php
              $ts = strtotime($log['created_at']);
              echo date('d M Y, H:i', $ts);
            ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

</div><!-- /.dh-wrap -->

<script>
(function(){
  function filterLog(query) {
    var q    = query.toLowerCase().trim();
    var rows = document.querySelectorAll('#tbl-log tbody tr');
    rows.forEach(function(row) {
      row.style.display = (!q || row.textContent.toLowerCase().indexOf(q) > -1) ? '' : 'none';
    });
  }
  function toggleClear(id, val) {
    var btn = document.getElementById(id);
    if (btn) btn.style.display = val ? 'block' : 'none';
  }
  function clearLog() {
    var inp = document.getElementById('search-log');
    if (inp) { inp.value = ''; inp.focus(); }
    filterLog('');
    toggleClear('clear-log', '');
  }
  window.filterLog  = filterLog;
  window.toggleClear = toggleClear;
  window.clearLog   = clearLog;
})();
</script>
