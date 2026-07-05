<?php
$baseUrl  = rtrim(BASE_URL, '/');
$isAdmin  = ($user['role'] === 'admin');
$hour     = (int)date('H');
$greeting = $hour < 12 ? 'Selamat Pagi' : ($hour < 15 ? 'Selamat Siang' : ($hour < 18 ? 'Selamat Sore' : 'Selamat Malam'));
$nama     = htmlspecialchars($user['name'] ?? 'Pengguna');
$roleLabel = ['admin' => 'Administrator', 'sekretaris' => 'Sekretaris', 'peserta' => 'Peserta'][$user['role'] ?? ''] ?? ucfirst($user['role'] ?? '');

/* ── Stat card definitions ── */
$statCards = [
    [
        'key'   => 'total_meetings',
        'label' => 'Total Kegiatan',
        'color' => '#7B1C1C',
        'light' => '#fdf2f2',
        'icon'  => '<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/>',
        'href'  => '/meetings',
    ],
    [
        'key'   => 'meeting_today',
        'label' => 'Kegiatan Hari Ini',
        'color' => '#b45309',
        'light' => '#fffbeb',
        'icon'  => '<path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/><path d="m9 16 2 2 4-4"/>',
        'href'  => '/meetings',
    ],
    [
        'key'   => 'meeting_month',
        'label' => 'Bulan '.date('M'),
        'color' => '#1d4ed8',
        'light' => '#eff6ff',
        'icon'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="M8 14h.01M12 14h.01M16 14h.01"/>',
        'href'  => '/meetings',
    ],
    [
        'key'   => 'tl_pending',
        'label' => 'Tugas Pending',
        'color' => '#b45309',
        'light' => '#fefce8',
        'icon'  => '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
        'href'  => '/tindak-lanjut',
    ],
    [
        'key'   => 'tl_overdue',
        'label' => 'Terlambat',
        'color' => '#dc2626',
        'light' => '#fef2f2',
        'icon'  => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3z"/><path d="M12 9v4M12 17h.01"/>',
        'href'  => '/tindak-lanjut',
    ],
    [
        'key'   => 'tl_done',
        'label' => 'Tugas Selesai',
        'color' => '#059669',
        'light' => '#f0fdf4',
        'icon'  => '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/>',
        'href'  => '/tindak-lanjut',
    ],
];
if ($isAdmin) {
    $statCards[] = [
        'key'   => 'total_users',
        'label' => 'User Aktif',
        'color' => '#7c3aed',
        'light' => '#f5f3ff',
        'icon'  => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'href'  => '/users',
    ];
}
?>
<style>
/* ══ Base ═══════════════════════════════════════════════════════════ */
.db { --red:#7B1C1C; --red-l:#fdf2f2; --border:#e5e7eb; --radius:12px;
      --shadow:0 1px 3px rgba(0,0,0,.06),0 1px 2px rgba(0,0,0,.04);
      --shadow-md:0 4px 16px rgba(0,0,0,.08);
      font-family:inherit; }

/* ══ Page header ═════════════════════════════════════════════════ */
.db-header {
  display:flex; align-items:center; justify-content:space-between;
  flex-wrap:wrap; gap:1rem;
  padding:1.5rem 0 1.25rem;
  border-bottom:1px solid var(--border);
  margin-bottom:1.5rem;
}
.db-header-left {}
.db-welcome   { font-size:1.3rem; font-weight:800; color:#111827; line-height:1.2; }
.db-welcome span.wave { display:inline-block; animation:wave 2s ease-in-out infinite; }
@keyframes wave { 0%,100%{transform:rotate(0deg)} 25%{transform:rotate(18deg)} 75%{transform:rotate(-10deg)} }
.db-meta      { font-size:12px; color:#6b7280; margin-top:.25rem; display:flex; align-items:center; gap:.5rem; flex-wrap:wrap; }
.db-meta-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  background:#f3f4f6; border-radius:99px;
  padding:2px 8px; font-size:11px; font-weight:600; color:#374151;
}
.db-header-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
.db-btn {
  display:inline-flex; align-items:center; gap:.4rem;
  font-size:12.5px; font-weight:700; padding:.5rem 1rem;
  border-radius:8px; border:1px solid transparent;
  text-decoration:none; cursor:pointer; white-space:nowrap;
  transition:all .15s;
}
.db-btn-primary { background:var(--red); color:#fff; border-color:var(--red); }
.db-btn-primary:hover { background:#5a1212; color:#fff; }
.db-btn-ghost   { background:#fff; color:#374151; border-color:var(--border); }
.db-btn-ghost:hover { background:#f9fafb; }

/* ══ Stats row ══════════════════════════════════════════════════ */
.db-stats {
  display:grid;
  grid-template-columns:repeat(auto-fill, minmax(148px, 1fr));
  gap:.85rem;
  margin-bottom:1.5rem;
}
.db-stat {
  background:#fff; border:1px solid var(--border); border-radius:var(--radius);
  padding:1.1rem 1rem .95rem; position:relative; overflow:hidden;
  text-decoration:none; color:inherit; display:block;
  transition:box-shadow .15s, transform .15s;
}
.db-stat:hover { box-shadow:var(--shadow-md); transform:translateY(-2px); }
.db-stat-top   { display:flex; align-items:center; justify-content:space-between; margin-bottom:.65rem; }
.db-stat-icon  { width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; }
.db-stat-trend { font-size:10px; font-weight:700; padding:2px 6px; border-radius:99px; }
.db-stat-num   { font-size:1.9rem; font-weight:800; line-height:1; color:#111827; letter-spacing:-.04em; }
.db-stat-lbl   { font-size:11.5px; color:#6b7280; margin-top:.3rem; font-weight:500; }
.db-stat-bar   { position:absolute; bottom:0; left:0; right:0; height:3px; border-radius:0 0 var(--radius) var(--radius); }

/* ══ Layout grids ════════════════════════════════════════════════ */
.db-row1 { display:grid; grid-template-columns:1.1fr .9fr; gap:1rem; margin-bottom:1rem; }
.db-row2 { display:grid; grid-template-columns:2fr 1fr;   gap:1rem; margin-bottom:1rem; }
.db-row3 { display:grid; grid-template-columns:1fr 1fr;   gap:1rem; margin-bottom:1rem; }
@media(max-width:900px){
  .db-row1,.db-row2,.db-row3 { grid-template-columns:1fr; }
}
@media(max-width:560px){
  .db-stats { grid-template-columns:repeat(2,1fr); }
  .db-stat-num { font-size:1.55rem; }
}

/* ══ Panel ═══════════════════════════════════════════════════════ */
.db-panel {
  background:#fff; border:1px solid var(--border); border-radius:var(--radius);
  overflow:hidden; display:flex; flex-direction:column;
  box-shadow:var(--shadow);
}
.db-panel-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:.8rem 1.1rem; border-bottom:1px solid #f3f4f6; gap:.5rem;
  flex-shrink:0;
}
.db-panel-title {
  display:flex; align-items:center; gap:.45rem;
  font-size:13px; font-weight:700; color:#111827;
}
.db-panel-title svg { color:var(--red); flex-shrink:0; }
.db-panel-badge {
  font-size:10.5px; color:#6b7280; font-weight:500;
  background:#f3f4f6; border-radius:99px; padding:2px 7px;
}
.db-panel-link {
  font-size:11.5px; color:var(--red); font-weight:600;
  text-decoration:none; white-space:nowrap;
  padding:.2rem .6rem; border-radius:6px; border:1px solid #f5d0d0;
  transition:background .12s;
}
.db-panel-link:hover { background:var(--red-l); }
.db-panel-body { flex:1; overflow:hidden; }

/* ══ List rows ═══════════════════════════════════════════════════ */
.db-item {
  display:flex; align-items:flex-start; gap:.75rem;
  padding:.8rem 1.1rem; border-bottom:1px solid #f9fafb;
  text-decoration:none; color:inherit; transition:background .12s;
}
.db-item:last-child { border-bottom:none; }
.db-item:hover      { background:#fafafa; }
a.db-item:hover     { background:#fdf9f9; }

/* Date badge */
.db-datebadge {
  min-width:42px; text-align:center; flex-shrink:0;
  background:var(--red-l); border-radius:8px; padding:.4rem .3rem;
}
.db-datebadge .dd { font-size:17px; font-weight:800; color:var(--red); line-height:1; }
.db-datebadge .dm { font-size:9px; text-transform:uppercase; letter-spacing:.06em; color:#b91c1c; margin-top:1px; }

/* Avatar placeholder */
.db-avatar {
  width:32px; height:32px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
  font-size:12px; font-weight:700; color:#fff;
}

/* Dot */
.db-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; margin-top:5px; }

/* Tags */
.db-tag {
  display:inline-flex; align-items:center;
  font-size:10px; font-weight:700; letter-spacing:.02em;
  padding:2px 7px; border-radius:99px; white-space:nowrap;
}
.db-tag-today   { background:#fdf2f2; color:var(--red); }
.db-tag-high    { background:#fee2e2; color:#dc2626; }
.db-tag-medium  { background:#fef3c7; color:#b45309; }
.db-tag-low     { background:#d1fae5; color:#059669; }
.db-tag-overdue { background:#fee2e2; color:#dc2626; }
.db-tag-done    { background:#d1fae5; color:#059669; }
.db-tag-pending { background:#fef3c7; color:#b45309; }
.db-tag-info    { background:#dbeafe; color:#1d4ed8; }

/* Empty */
.db-empty {
  display:flex; flex-direction:column; align-items:center; justify-content:center;
  padding:2.5rem 1rem; gap:.5rem; color:#9ca3af; font-size:12.5px; text-align:center;
}
.db-empty svg { color:#e5e7eb; margin-bottom:.25rem; }

/* Chart */
.db-chart-wrap { padding:1rem 1.1rem; }
.db-year-sel {
  font-size:11.5px; color:#374151;
  border:1px solid var(--border); border-radius:6px;
  padding:.25rem .5rem; background:#fff; cursor:pointer;
}
.db-year-sel:focus { outline:none; border-color:var(--red); }

/* Donut legend */
.db-legend {
  display:flex; flex-wrap:wrap; gap:.35rem .8rem;
  padding:.75rem 1.1rem; border-top:1px solid #f3f4f6;
  font-size:11.5px;
}
.db-legend-item { display:flex; align-items:center; gap:.3rem; color:#6b7280; }
.db-legend-item strong { color:#111827; }
.db-legend-dot  { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* Activity feed */
.db-act-icon {
  width:30px; height:30px; border-radius:50%; flex-shrink:0;
  display:flex; align-items:center; justify-content:center;
}
.db-act-time { font-size:10.5px; color:#9ca3af; white-space:nowrap; }

/* Skeleton loading */
.db-skeleton {
  background:linear-gradient(90deg,#f3f4f6 25%,#e9eaec 50%,#f3f4f6 75%);
  background-size:200% 100%; animation:shimmer 1.4s infinite;
  border-radius:4px; height:14px;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>

<div class="db">

<!-- ══ Page Header ════════════════════════════════════════════════ -->
<div class="db-header">
  <div class="db-header-left">
    <div class="db-welcome">
      <?= $greeting ?>, <?= $nama ?> <span class="wave">👋</span>
    </div>
    <div class="db-meta">
      <span><?= date('l, d F Y') ?></span>
      <span style="color:#d1d5db">·</span>
      <span class="db-meta-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
        <?= date('H:i') ?> WIB
      </span>
      <span class="db-meta-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?= $roleLabel ?>
      </span>
    </div>
  </div>
  <div class="db-header-actions">
    <?php if ($isAdmin || ($user['role'] ?? '') === 'sekretaris'): ?>
    <a href="<?= $baseUrl ?>/meetings/create" class="db-btn db-btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Buat Kegiatan
    </a>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/meetings" class="db-btn db-btn-ghost">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
      Semua Kegiatan
    </a>
  </div>
</div>

<!-- ══ Stat Cards ═════════════════════════════════════════════════ -->
<div class="db-stats">
  <?php foreach ($statCards as $sc): ?>
  <a href="<?= $baseUrl . $sc['href'] ?>" class="db-stat">
    <div class="db-stat-top">
      <div class="db-stat-icon" style="background:<?= $sc['light'] ?>;color:<?= $sc['color'] ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $sc['icon'] ?></svg>
      </div>
    </div>
    <div class="db-stat-num"><?= number_format((int)($stats[$sc['key']] ?? 0)) ?></div>
    <div class="db-stat-lbl"><?= $sc['label'] ?></div>
    <div class="db-stat-bar" style="background:<?= $sc['color'] ?>"></div>
  </a>
  <?php endforeach; ?>
</div>

<!-- ══ Row 1: Kegiatan Mendatang + Tindak Lanjut ════════════════ -->
<div class="db-row1">

  <!-- Kegiatan Mendatang -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
        Kegiatan Mendatang
        <span class="db-panel-badge">7 hari ke depan</span>
      </div>
      <a href="<?= $baseUrl ?>/meetings" class="db-panel-link">Lihat semua →</a>
    </div>
    <div class="db-panel-body">
      <?php if (empty($upcoming)): ?>
      <div class="db-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg>
        <div>Tidak ada kegiatan mendatang</div>
        <div style="font-size:11px;color:#d1d5db;">dalam 7 hari ke depan</div>
      </div>
      <?php endif; ?>
      <?php foreach ($upcoming as $m):
        $dt      = new DateTime($m['start_datetime']);
        $isToday = $dt->format('Y-m-d') === date('Y-m-d');
        $isTomorrow = $dt->format('Y-m-d') === date('Y-m-d', strtotime('+1 day'));
      ?>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$m['id'] ?>" class="db-item">
        <div class="db-datebadge">
          <div class="dd"><?= $dt->format('d') ?></div>
          <div class="dm"><?= $dt->format('M') ?></div>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="display:flex;align-items:center;gap:.4rem;flex-wrap:wrap;">
            <span style="font-size:13px;font-weight:600;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:220px;">
              <?= htmlspecialchars($m['title']) ?>
            </span>
            <?php if ($isToday): ?>
              <span class="db-tag db-tag-today">Hari ini</span>
            <?php elseif ($isTomorrow): ?>
              <span class="db-tag db-tag-info">Besok</span>
            <?php endif; ?>
          </div>
          <div style="font-size:11.5px;color:#6b7280;margin-top:3px;display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            <span><?= $dt->format('H:i') ?> WIB</span>
            <?php if (!empty($m['location'])): ?>
            <span style="color:#d1d5db">·</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:110px;"><?= htmlspecialchars($m['location']) ?></span>
            <?php endif; ?>
            <span style="color:#d1d5db">·</span>
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            <span><?= (int)$m['total_peserta'] ?> peserta</span>
          </div>
        </div>
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="2" style="flex-shrink:0;margin-top:2px;"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Tindak Lanjut Terdekat -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tindak Lanjut
        <span class="db-panel-badge">Aktif</span>
      </div>
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="db-panel-link">Lihat semua →</a>
    </div>
    <div class="db-panel-body">
      <?php if (empty($tlDeadline)): ?>
      <div class="db-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
        <div>Tidak ada tindak lanjut aktif</div>
      </div>
      <?php endif; ?>
      <?php foreach ($tlDeadline as $tl):
        $isOverdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d');
        $prio      = $tl['priority'] ?? 'low';
        $prioMap   = ['high'=>['Tinggi','#dc2626','#fee2e2'],'medium'=>['Sedang','#d97706','#fef3c7'],'low'=>['Rendah','#059669','#d1fae5']];
        [$prioLabel, $prioColor, $prioBg] = $prioMap[$prio] ?? ['Rendah','#059669','#d1fae5'];
        $dotColors = ['high'=>'#dc2626','medium'=>'#d97706','low'=>'#10b981'];
        $dotColor  = $dotColors[$prio] ?? '#9ca3af';
      ?>
      <div class="db-item" style="<?= $isOverdue ? 'background:linear-gradient(to right,#fff5f5,#fff);' : '' ?>">
        <div class="db-dot" style="background:<?= $isOverdue ? '#dc2626' : $dotColor ?>; margin-top:4px;"></div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13px;font-weight:600;color:<?= $isOverdue ? '#dc2626' : '#111827' ?>;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?= htmlspecialchars($tl['description'] ?? '') ?>
          </div>
          <div style="font-size:11px;color:#9ca3af;margin-top:2px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
            <?= htmlspecialchars($tl['meeting_title'] ?? '') ?>
          </div>
          <div style="display:flex;align-items:center;gap:.4rem;margin-top:.3rem;flex-wrap:wrap;">
            <span class="db-tag" style="background:<?= $prioBg ?>;color:<?= $prioColor ?>;"><?= $prioLabel ?></span>
            <?php if ($isOverdue): ?><span class="db-tag db-tag-overdue">⚠ Terlambat</span><?php endif; ?>
          </div>
        </div>
        <div style="flex-shrink:0;text-align:right;">
          <?php if (!empty($tl['due_date'])): ?>
          <div style="font-size:11.5px;font-weight:600;color:<?= $isOverdue ? '#dc2626' : '#6b7280' ?>;white-space:nowrap;">
            <?= date('d M', strtotime($tl['due_date'])) ?>
          </div>
          <?php endif; ?>
          <div style="font-size:11px;color:#9ca3af;margin-top:2px;white-space:nowrap;">
            <?= htmlspecialchars($tl['assigned_name'] ?? '—') ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<!-- ══ Row 2: Bar Chart + Donut ══════════════════════════════════ -->
<div class="db-row2">

  <!-- Bar: Kegiatan Per Bulan -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        Kegiatan Per Bulan
      </div>
      <select id="selYearBar" class="db-year-sel">
        <?php foreach ($availableYears as $yr): ?>
        <option value="<?= $yr ?>" <?= $yr==date('Y')?'selected':'' ?>><?= $yr ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="db-chart-wrap"><canvas id="cvBar" height="120"></canvas></div>
  </div>

  <!-- Donut: Status Tindak Lanjut -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
        Status Tindak Lanjut
      </div>
    </div>
    <div class="db-chart-wrap" style="display:flex;justify-content:center;padding-bottom:.25rem;">
      <div style="max-width:175px;width:100%;"><canvas id="cvDonut"></canvas></div>
    </div>
    <div class="db-legend" id="donutLegend"></div>
  </div>

</div>

<!-- ══ Row 3: Line Trend + Top Dept (admin) ══════════════════════ -->
<div class="db-row3">

  <!-- Line: Tren Tindak Lanjut -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Tren Tindak Lanjut
      </div>
      <select id="selYearLine" class="db-year-sel">
        <?php foreach ($availableYears as $yr): ?>
        <option value="<?= $yr ?>" <?= $yr==date('Y')?'selected':'' ?>><?= $yr ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="db-chart-wrap"><canvas id="cvLine" height="130"></canvas></div>
  </div>

  <!-- Top Unit Kerja (admin only) -->
  <?php if ($isAdmin): ?>
  <div class="db-panel" id="panelTopDept">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="7" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        Top Unit Kerja
        <span class="db-panel-badge">5 tertinggi</span>
      </div>
    </div>
    <div class="db-chart-wrap"><canvas id="cvDept" height="200"></canvas></div>
    <div id="noDeptMsg" class="db-empty" style="display:none;">
      <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><rect width="20" height="14" x="2" y="7" rx="2"/></svg>
      <div>Data unit kerja belum tersedia</div>
    </div>
  </div>
  <?php else: ?>
  <!-- Non-admin: Recent Activity feed -->
  <div class="db-panel">
    <div class="db-panel-head">
      <div class="db-panel-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Aktivitas Terakhir
      </div>
      <a href="<?= $baseUrl ?>/notifications" class="db-panel-link">Semua →</a>
    </div>
    <div class="db-panel-body">
      <?php if (empty($recentActivity)): ?>
      <div class="db-empty">
        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/></svg>
        <div>Belum ada aktivitas</div>
      </div>
      <?php endif; ?>
      <?php
      $actColors = ['#7B1C1C','#1d4ed8','#059669','#b45309','#7c3aed'];
      foreach ($recentActivity as $i => $act):
        $aColor = $actColors[$i % count($actColors)];
        $aTime  = (new DateTime($act['created_at']))->format('d M, H:i');
        $isRead = !empty($act['is_read']);
      ?>
      <div class="db-item" style="<?= !$isRead ? 'background:linear-gradient(to right,#fdf9f9,#fff);' : '' ?>">
        <div class="db-act-icon" style="background:<?= $aColor ?>22;color:<?= $aColor ?>;">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:12.5px;color:#374151;line-height:1.4;<?= !$isRead ? 'font-weight:600;' : '' ?>">
            <?= htmlspecialchars($act['message'] ?? '') ?>
          </div>
          <div class="db-act-time" style="margin-top:2px;"><?= $aTime ?></div>
        </div>
        <?php if (!$isRead): ?>
        <div style="width:6px;height:6px;border-radius:50%;background:var(--red);flex-shrink:0;margin-top:4px;"></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
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

  const C = {
    red:     '#7B1C1C',
    redL:    'rgba(123,28,28,.13)',
    amber:   '#d97706',
    amberL:  'rgba(217,119,6,.12)',
    green:   '#059669',
    greenL:  'rgba(5,150,105,.12)',
    danger:  '#dc2626',
    dangerL: 'rgba(220,38,38,.10)',
    blue:    '#2563eb',
    teal:    '#0d9488',
    purple:  '#7c3aed',
    gray:    '#9ca3af',
    grid:    'rgba(0,0,0,.05)',
  };

  const tickStyle = { color:'#9ca3af', font:{ size:11, family:"inherit" } };
  const baseOpts  = {
    responsive:true, maintainAspectRatio:true,
    plugins:{ legend:{ display:false } },
    scales:{
      x:{ grid:{ display:false }, ticks:tickStyle },
      y:{ beginAtZero:true, ticks:{ precision:0, stepSize:1, ...tickStyle }, grid:{ color:C.grid } }
    }
  };

  /* ── helpers ── */
  function hexA(hex, a) {
    const r=parseInt(hex.slice(1,3),16), g=parseInt(hex.slice(3,5),16), b=parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${a})`;
  }

  /* ══ Bar: Kegiatan Per Bulan ══ */
  let chartBar;
  async function loadBar(year) {
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-monthly?year='+year)).json();
      const ctx = document.getElementById('cvBar').getContext('2d');
      if (chartBar) chartBar.destroy();
      const curM = new Date().getMonth();
      const isCurrentYear = d.year == new Date().getFullYear();
      const bg = d.data.map((_,i) =>
        isCurrentYear && i === curM ? C.red : hexA(C.red, .18)
      );
      chartBar = new Chart(ctx, {
        type:'bar',
        data:{
          labels: MONTHS,
          datasets:[{
            data: d.data,
            backgroundColor: bg,
            hoverBackgroundColor: d.data.map((_,i) => isCurrentYear && i===curM ? '#5a1212' : hexA(C.red,.35)),
            borderRadius:5, borderSkipped:false,
          }]
        },
        options:{
          ...baseOpts,
          plugins:{
            legend:{ display:false },
            tooltip:{ callbacks:{ label: c=>' '+c.parsed.y+' kegiatan' } }
          }
        }
      });
    } catch(e){ console.warn('Bar chart error', e); }
  }
  const selBar = document.getElementById('selYearBar');
  loadBar(selBar.value);
  selBar.addEventListener('change', ()=>loadBar(selBar.value));

  /* ══ Donut: Status TL ══ */
  async function loadDonut() {
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-tl-status')).json();
      const keys   = ['pending','in_progress','done','cancelled','overdue'];
      const labels = ['Pending','Dikerjakan','Selesai','Dibatalkan','Terlambat'];
      const colors = [C.amber, C.teal, C.green, C.gray, C.danger];
      const vals   = keys.map(k=>d.data[k]||0);
      const total  = vals.reduce((a,b)=>a+b,0);

      new Chart(document.getElementById('cvDonut').getContext('2d'), {
        type:'doughnut',
        data:{
          labels,
          datasets:[{ data:vals, backgroundColor:colors, borderWidth:2, borderColor:'#fff', hoverOffset:6 }]
        },
        options:{
          responsive:true, cutout:'68%',
          plugins:{
            legend:{ display:false },
            tooltip:{ callbacks:{ label: c=>' '+c.label+': '+c.parsed+(total?' ('+Math.round(c.parsed/total*100)+'%)':'') } }
          }
        }
      });

      document.getElementById('donutLegend').innerHTML = keys.map((k,i)=>
        `<div class="db-legend-item">
          <div class="db-legend-dot" style="background:${colors[i]}"></div>
          <span>${labels[i]}</span>
          <strong>${vals[i]}</strong>
        </div>`
      ).join('');
    } catch(e){ console.warn('Donut error', e); }
  }
  loadDonut();

  /* ══ Line: Tren Tindak Lanjut ══ */
  let chartLine;
  async function loadLine(year) {
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-tl-trend?year='+year)).json();
      if (chartLine) chartLine.destroy();
      chartLine = new Chart(document.getElementById('cvLine').getContext('2d'), {
        type:'line',
        data:{
          labels: MONTHS,
          datasets:[
            {
              label:'Selesai',
              data: d.done,
              borderColor:C.green, backgroundColor:C.greenL,
              tension:.4, fill:true, pointRadius:3, pointHoverRadius:6, borderWidth:2,
            },
            {
              label:'Terlambat',
              data: d.overdue,
              borderColor:C.danger, backgroundColor:C.dangerL,
              tension:.4, fill:true, pointRadius:3, pointHoverRadius:6, borderWidth:2,
            }
          ]
        },
        options:{
          ...baseOpts,
          plugins:{
            legend:{
              display:true, position:'top',
              labels:{ boxWidth:9, font:{ size:11, family:"inherit" }, color:'#374151', padding:12 }
            },
            tooltip:{ mode:'index', intersect:false }
          },
          interaction:{ mode:'nearest', axis:'x', intersect:false }
        }
      });
    } catch(e){ console.warn('Line chart error', e); }
  }
  const selLine = document.getElementById('selYearLine');
  loadLine(selLine.value);
  selLine.addEventListener('change', ()=>loadLine(selLine.value));

  /* ══ Hbar: Top Unit Kerja (admin) ══ */
  async function loadDept() {
    const wrap = document.getElementById('panelTopDept');
    if (!wrap) return;
    try {
      const d = await (await fetch(BASE+'/api/dashboard/chart-top-dept')).json();
      if (!d.labels?.length) {
        document.getElementById('cvDept').style.display = 'none';
        document.getElementById('noDeptMsg').style.display = 'flex';
        return;
      }
      const palette = [C.red, '#b45309', C.blue, C.teal, C.purple, C.green, C.danger, C.amber];
      new Chart(document.getElementById('cvDept').getContext('2d'), {
        type:'bar',
        data:{
          labels: d.labels,
          datasets:[{
            data: d.data,
            backgroundColor: d.labels.map((_,i)=>hexA(palette[i%palette.length],.65)),
            borderColor:     d.labels.map((_,i)=>palette[i%palette.length]),
            borderWidth:1, borderRadius:4, borderSkipped:false,
          }]
        },
        options:{
          indexAxis:'y',
          responsive:true,
          plugins:{
            legend:{ display:false },
            tooltip:{ callbacks:{ label: c=>' '+c.parsed.x+' kegiatan' } }
          },
          scales:{
            x:{ beginAtZero:true, ticks:{ precision:0, stepSize:1, ...tickStyle }, grid:{ color:C.grid } },
            y:{ grid:{ display:false }, ticks:tickStyle }
          }
        }
      });
    } catch(e){ console.warn('Dept chart error', e); }
  }
  loadDept();
})();
</script>
