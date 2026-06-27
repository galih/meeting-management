<?php
$baseUrl = rtrim(BASE_URL, '/');
$actionGroups = [
    ''        => 'Semua Modul',
    'auth'    => 'Autentikasi',
    'meeting' => 'Kegiatan',
    'user'    => 'User',
];

// Helper: icon per subject_type
function alIcon(string $action): string {
    $a = strtolower($action);
    if (str_contains($a,'login') || str_contains($a,'logout') || str_contains($a,'auth'))
        return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>';
    if (str_contains($a,'create') || str_contains($a,'add') || str_contains($a,'store'))
        return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
    if (str_contains($a,'delete') || str_contains($a,'destroy') || str_contains($a,'purge'))
        return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
    if (str_contains($a,'update') || str_contains($a,'edit'))
        return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';
    return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
}
?>
<style>
/* ===== AL: Activity Log Namespace ===== */
.al-hero {
  background: linear-gradient(135deg, #7B1C1C 0%, #9B2020 60%, #5A1212 100%);
  border-radius: 14px;
  padding: 1.5rem 2rem;
  margin-bottom: 1.5rem;
  color: #fff;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
}
.al-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events: none;
}
.al-hero-left { position: relative; display: flex; align-items: center; gap: 1rem; }
.al-hero-icon {
  width: 52px; height: 52px; border-radius: 12px;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.al-hero-title { font-size: 1.3rem; font-weight: 800; line-height: 1.2; }
.al-hero-sub { font-size: 13px; color: rgba(255,255,255,.70); margin-top: .2rem; }
.al-hero-right { position: relative; display: flex; align-items: center; gap: .6rem; flex-wrap: wrap; }
.al-stat {
  background: rgba(255,255,255,.13);
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 10px;
  padding: .45rem .9rem;
  text-align: center;
  min-width: 70px;
}
.al-stat-num { font-size: 1.25rem; font-weight: 800; line-height: 1; }
.al-stat-lbl { font-size: 10.5px; color: rgba(255,255,255,.65); margin-top: .15rem; }
.al-gold-bar {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #C9A84C, #A8872F, #C9A84C);
  border-radius: 0 0 14px 14px;
}

/* Filter bar */
.al-filter-card {
  background: #fff;
  border: 1px solid #E8E2D9;
  border-radius: 12px;
  padding: 1rem 1.25rem;
  margin-bottom: 1.25rem;
  box-shadow: 0 2px 8px rgba(28,23,20,.05);
}
.al-filter-row { display: flex; gap: .65rem; align-items: flex-end; flex-wrap: wrap; }
.al-filter-group { display: flex; flex-direction: column; gap: .3rem; flex: 1; min-width: 130px; }
.al-filter-group label { font-size: 11.5px; font-weight: 600; color: #6B6055; letter-spacing: .02em; text-transform: uppercase; }
.al-filter-group .al-ctrl {
  height: 36px;
  border: 1.5px solid #DDD5C4;
  border-radius: 8px;
  padding: 0 .75rem;
  font-size: 13.5px;
  color: #1C1714;
  background: #FDFCFA;
  width: 100%;
  transition: border-color 180ms;
  outline: none;
  appearance: none;
  -webkit-appearance: none;
}
.al-filter-group .al-ctrl:focus { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.08); }
select.al-ctrl { background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B6055' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right .6rem center; padding-right: 2rem; }
.al-filter-actions { display: flex; gap: .5rem; align-items: flex-end; flex-shrink: 0; }
.al-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 700;
  height: 36px; padding: 0 1rem;
  border-radius: 8px; cursor: pointer;
  text-decoration: none; border: 1.5px solid transparent;
  transition: all 180ms cubic-bezier(.16,1,.3,1);
  white-space: nowrap;
}
.al-btn-primary { background: #7B1C1C; color: #fff; border-color: #5A1212; }
.al-btn-primary:hover { background: #5A1212; color: #fff; }
.al-btn-ghost { background: #fff; color: #6B6055; border-color: #DDD5C4; }
.al-btn-ghost:hover { border-color: #7B1C1C; color: #7B1C1C; }
.al-btn-danger { background: #fff; color: #C05621; border-color: #DDD5C4; }
.al-btn-danger:hover { background: #C05621; color: #fff; border-color: #A8421A; }

/* Main card */
.al-card {
  background: #fff;
  border: 1px solid #E8E2D9;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 12px rgba(28,23,20,.06);
}
.al-card-header {
  padding: .75rem 1.25rem;
  border-bottom: 1px solid #F0EBE2;
  display: flex; align-items: center; justify-content: space-between;
  gap: .5rem;
}
.al-card-header-title { font-size: 13px; font-weight: 700; color: #6B6055; letter-spacing: .03em; text-transform: uppercase; }
.al-entry-count { font-size: 12.5px; color: #A89E90; }

/* Timeline table */
.al-table { width: 100%; border-collapse: collapse; }
.al-table th {
  padding: .55rem 1rem;
  font-size: 11px; font-weight: 700;
  color: #A89E90; text-transform: uppercase; letter-spacing: .05em;
  border-bottom: 1px solid #F0EBE2;
  white-space: nowrap;
  background: #FDFCFA;
}
.al-table td { padding: .75rem 1rem; border-bottom: 1px solid #F5F0E8; vertical-align: middle; }
.al-table tbody tr:last-child td { border-bottom: none; }
.al-table tbody tr:hover td { background: #FDFAF7; }

/* Time cell */
.al-time-date { font-size: 12.5px; font-weight: 600; color: #1C1714; white-space: nowrap; }
.al-time-clock { font-size: 11.5px; color: #A89E90; font-variant-numeric: tabular-nums; margin-top: .1rem; }

/* Action badge */
.al-action-wrap { display: flex; align-items: center; gap: .45rem; }
.al-action-dot {
  width: 28px; height: 28px; border-radius: 8px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.al-badge {
  display: inline-block;
  font-size: 11px; font-weight: 700;
  padding: .2em .65em; border-radius: 20px;
  letter-spacing: .03em; white-space: nowrap;
}

/* User cell */
.al-user-name { font-size: 13.5px; font-weight: 600; color: #1C1714; }
.al-user-role { font-size: 11.5px; color: #A89E90; margin-top: .1rem; }
.al-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: linear-gradient(135deg, #7B1C1C, #9B2020);
  display: flex; align-items: center; justify-content: center;
  font-size: 12px; font-weight: 800; color: #fff;
  flex-shrink: 0;
}
.al-user-cell { display: flex; align-items: center; gap: .6rem; }

/* Description */
.al-desc { font-size: 13px; color: #1C1714; line-height: 1.5; }
.al-module-badge {
  display: inline-block;
  font-size: 11px; font-weight: 600;
  padding: .2em .6em; border-radius: 6px;
  background: #F0EBE2; color: #6B6055;
  margin-top: .3rem;
  white-space: nowrap;
}

/* IP */
.al-ip { font-size: 11.5px; color: #A89E90; font-variant-numeric: tabular-nums; white-space: nowrap; }

/* Empty */
.al-empty {
  text-align: center;
  padding: 4rem 2rem;
  color: #A89E90;
}
.al-empty-icon {
  width: 56px; height: 56px; border-radius: 50%;
  background: #F5F0E8; display: flex; align-items: center; justify-content: center;
  margin: 0 auto 1rem;
}
.al-empty-title { font-size: 15px; font-weight: 700; color: #6B6055; margin-bottom: .35rem; }
.al-empty-sub { font-size: 13px; }

/* Pagination */
.al-pager {
  display: flex; align-items: center; justify-content: space-between;
  gap: .75rem; flex-wrap: wrap;
  padding: .85rem 1.25rem;
  border-top: 1px solid #F0EBE2;
  background: #FDFCFA;
}
.al-pager-info { font-size: 12.5px; color: #A89E90; }
.al-pager-pages { display: flex; gap: .3rem; flex-wrap: wrap; }
.al-pager-btn {
  display: inline-flex; align-items: center; justify-content: center;
  min-width: 32px; height: 32px;
  padding: 0 .6rem;
  border-radius: 8px; border: 1.5px solid #E8E2D9;
  font-size: 12.5px; font-weight: 600;
  color: #6B6055; text-decoration: none;
  background: #fff;
  transition: all 140ms;
}
.al-pager-btn:hover { border-color: #7B1C1C; color: #7B1C1C; }
.al-pager-btn.active { background: #7B1C1C; border-color: #7B1C1C; color: #fff; }
.al-pager-btn.disabled { opacity: .4; pointer-events: none; }

/* Flash */
.al-flash {
  display: flex; align-items: center; gap: .65rem;
  background: #EDFAF0; border: 1px solid #B4DFC0;
  border-radius: 10px; padding: .7rem 1rem;
  margin-bottom: 1.25rem; font-size: 13.5px; color: #1A5C33;
  font-weight: 600;
}
.al-flash svg { flex-shrink: 0; color: #27A155; }
.al-flash-close {
  margin-left: auto; background: none; border: none;
  cursor: pointer; color: #6B9E80; font-size: 16px; line-height: 1;
  padding: 0 .2rem;
}

/* Modal purge */
.al-modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(28,23,20,.45); z-index: 1050;
  align-items: center; justify-content: center; padding: 1rem;
}
.al-modal-overlay.show { display: flex; }
.al-modal {
  background: #fff; border-radius: 14px;
  width: 100%; max-width: 380px;
  box-shadow: 0 16px 48px rgba(28,23,20,.18);
  overflow: hidden;
  position: relative;
}
.al-modal::before {
  content: '';
  display: block; height: 3px;
  background: linear-gradient(90deg, #C05621, #C9A84C);
}
.al-modal-body { padding: 1.5rem; }
.al-modal-icon {
  width: 52px; height: 52px; border-radius: 50%;
  background: rgba(192,86,33,.1);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1rem; color: #C05621;
}
.al-modal-title { font-size: 16px; font-weight: 800; color: #1C1714; margin-bottom: .35rem; }
.al-modal-desc { font-size: 13.5px; color: #6B6055; margin-bottom: 1.25rem; line-height: 1.55; }
.al-days-input-wrap {
  display: flex; align-items: center; gap: 0;
  border: 1.5px solid #DDD5C4; border-radius: 8px; overflow: hidden;
  margin-bottom: 1.25rem;
}
.al-days-input {
  flex: 1; height: 40px; border: none; padding: 0 .75rem;
  font-size: 15px; font-weight: 700; color: #1C1714;
  outline: none; background: #FDFCFA;
  -moz-appearance: textfield;
}
.al-days-input::-webkit-inner-spin-button { -webkit-appearance: none; }
.al-days-suffix {
  padding: 0 .85rem; height: 40px;
  display: flex; align-items: center;
  font-size: 13px; color: #A89E90; font-weight: 600;
  background: #F5F0E8; border-left: 1.5px solid #DDD5C4;
}
.al-modal-footer { display: flex; gap: .5rem; justify-content: flex-end; }
.al-btn-modal-cancel { background: #fff; color: #6B6055; border-color: #DDD5C4; }
.al-btn-modal-cancel:hover { border-color: #7B1C1C; color: #7B1C1C; }
.al-btn-modal-del { background: #C05621; color: #fff; border-color: #A8421A; }
.al-btn-modal-del:hover { background: #A8421A; }

@media (max-width: 768px) {
  .al-hero { padding: 1.1rem 1.1rem; }
  .al-filter-row { flex-direction: column; }
  .al-filter-group { min-width: 100%; }
  .al-filter-actions { width: 100%; }
  .al-btn { flex: 1; justify-content: center; }
  th.al-hide-sm, td.al-hide-sm { display: none; }
}
</style>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="al-flash" id="alFlash">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="al-flash-close" onclick="document.getElementById('alFlash').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- Hero -->
<div class="al-hero">
  <div class="al-hero-left">
    <div class="al-hero-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
        <polyline points="14 2 14 8 20 8"/>
        <line x1="16" y1="13" x2="8" y2="13"/>
        <line x1="16" y1="17" x2="8" y2="17"/>
        <polyline points="10 9 9 9 8 9"/>
      </svg>
    </div>
    <div>
      <div class="al-hero-title">Log Aktivitas</div>
      <div class="al-hero-sub">Rekam jejak seluruh tindakan pengguna dalam sistem</div>
    </div>
  </div>
  <div class="al-hero-right">
    <div class="al-stat">
      <div class="al-stat-num"><?= number_format($total) ?></div>
      <div class="al-stat-lbl">Total Entri</div>
    </div>
    <?php
      $todayCount = 0;
      foreach ($logs as $l) {
          if (date('Y-m-d', strtotime($l['created_at'])) === date('Y-m-d')) $todayCount++;
      }
    ?>
    <div class="al-stat">
      <div class="al-stat-num"><?= $todayCount ?></div>
      <div class="al-stat-lbl">Hari Ini</div>
    </div>
    <div class="al-stat">
      <div class="al-stat-num"><?= $totalPages ?></div>
      <div class="al-stat-lbl">Halaman</div>
    </div>
    <button class="al-btn al-btn-danger" onclick="document.getElementById('alPurgeModal').classList.add('show')">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
      Bersihkan Log
    </button>
  </div>
  <div class="al-gold-bar"></div>
</div>

<!-- Filter -->
<div class="al-filter-card">
  <form method="GET" action="<?= $baseUrl ?>/admin/activity-log">
    <div class="al-filter-row">
      <div class="al-filter-group" style="min-width:160px;flex:2">
        <label>User</label>
        <select name="user_id" class="al-ctrl">
          <option value="">Semua User</option>
          <?php foreach ($users as $u): ?>
          <option value="<?= $u['id'] ?>" <?= ($filters['user_id'] == $u['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="al-filter-group">
        <label>Modul</label>
        <select name="subject_type" class="al-ctrl">
          <?php foreach ($actionGroups as $val => $label): ?>
          <option value="<?= $val ?>" <?= ($filters['subject_type'] === $val) ? 'selected' : '' ?>>
            <?= $label ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="al-filter-group">
        <label>Dari Tanggal</label>
        <input type="date" name="date_from" class="al-ctrl" value="<?= htmlspecialchars($filters['date_from']) ?>">
      </div>
      <div class="al-filter-group">
        <label>Sampai Tanggal</label>
        <input type="date" name="date_to" class="al-ctrl" value="<?= htmlspecialchars($filters['date_to']) ?>">
      </div>
      <div class="al-filter-actions">
        <button type="submit" class="al-btn al-btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Filter
        </button>
        <a href="<?= $baseUrl ?>/admin/activity-log" class="al-btn al-btn-ghost">Reset</a>
      </div>
    </div>
  </form>
</div>

<!-- Log Table -->
<div class="al-card">
  <div class="al-card-header">
    <span class="al-card-header-title">Riwayat Aktivitas</span>
    <?php if (!empty($filters['user_id']) || !empty($filters['subject_type']) || !empty($filters['date_from']) || !empty($filters['date_to'])): ?>
    <span style="font-size:12px;background:#FFF5EB;border:1px solid #F5C98A;color:#8B5E0A;border-radius:20px;padding:.15em .7em;font-weight:600">
      Filter aktif
    </span>
    <?php endif; ?>
    <span class="al-entry-count"><?= number_format(count($logs)) ?> dari <?= number_format($total) ?> entri</span>
  </div>

  <div style="overflow-x:auto">
    <table class="al-table">
      <thead>
        <tr>
          <th style="width:130px">Waktu</th>
          <th style="width:170px">Pengguna</th>
          <th style="width:130px">Aksi</th>
          <th>Keterangan</th>
          <th class="al-hide-sm" style="width:90px">IP</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr>
          <td colspan="5">
            <div class="al-empty">
              <div class="al-empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#A89E90" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
              </div>
              <div class="al-empty-title">Belum ada log aktivitas</div>
              <div class="al-empty-sub">Log akan muncul setelah ada aktivitas dalam sistem</div>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        <?php foreach ($logs as $log):
          [$label, $bgClass, $textClass] = ActivityLog::badge($log['action']);
          // Convert Tabler CSS classes → inline colour
          $dotBg = '#F5F0E8'; $dotColor = '#6B6055';
          if (str_contains($bgClass,'success')) { $dotBg='rgba(67,122,34,.12)'; $dotColor='#437A22'; }
          elseif (str_contains($bgClass,'danger'))  { $dotBg='rgba(161,44,123,.12)'; $dotColor='#A12C7B'; }
          elseif (str_contains($bgClass,'warning')) { $dotBg='rgba(150,66,25,.12)'; $dotColor='#964219'; }
          elseif (str_contains($bgClass,'primary') || str_contains($bgClass,'blue')) { $dotBg='rgba(0,100,148,.12)'; $dotColor='#006494'; }
          elseif (str_contains($bgClass,'info'))    { $dotBg='rgba(0,100,148,.10)'; $dotColor='#006494'; }

          $badgeStyle = "background:{$dotBg};color:{$dotColor}";
          $dotStyle   = "background:{$dotBg};color:{$dotColor}";
          $initials   = strtoupper(substr($log['user_name'] ?? '?', 0, 1));
          if (str_word_count($log['user_name'] ?? '') > 1) {
              $parts = explode(' ', trim($log['user_name']));
              $initials = strtoupper(substr($parts[0],0,1) . substr(end($parts),0,1));
          }
        ?>
        <tr>
          <td>
            <div class="al-time-date"><?= date('d M Y', strtotime($log['created_at'])) ?></div>
            <div class="al-time-clock"><?= date('H:i:s', strtotime($log['created_at'])) ?></div>
          </td>
          <td>
            <div class="al-user-cell">
              <div class="al-avatar"><?= htmlspecialchars($initials) ?></div>
              <div>
                <div class="al-user-name"><?= htmlspecialchars($log['user_name'] ?? '—') ?></div>
                <div class="al-user-role"><?= htmlspecialchars($log['user_role'] ?? '') ?></div>
              </div>
            </div>
          </td>
          <td>
            <div class="al-action-wrap">
              <div class="al-action-dot" style="<?= $dotStyle ?>"><?= alIcon($log['action']) ?></div>
              <span class="al-badge" style="<?= $badgeStyle ?>"><?= $label ?></span>
            </div>
          </td>
          <td>
            <div class="al-desc"><?= htmlspecialchars($log['description'] ?? '—') ?></div>
            <?php if ($log['subject_type']): ?>
            <span class="al-module-badge">
              <?= htmlspecialchars(ucfirst($log['subject_type'])) ?>
              <?= $log['subject_id'] ? ' #' . $log['subject_id'] : '' ?>
            </span>
            <?php endif; ?>
          </td>
          <td class="al-hide-sm">
            <span class="al-ip"><?= htmlspecialchars($log['ip_address'] ?? '—') ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="al-pager">
    <span class="al-pager-info">
      Halaman <?= $page ?> dari <?= $totalPages ?> &nbsp;&middot;&nbsp; <?= number_format($total) ?> entri
    </span>
    <div class="al-pager-pages">
      <?php if ($page > 1): ?>
      <a class="al-pager-btn" href="?<?= http_build_query(array_merge($filters, ['page' => 1])) ?>" title="Pertama">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
      </a>
      <a class="al-pager-btn" href="?<?= http_build_query(array_merge($filters, ['page' => $page - 1])) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
      </a>
      <?php endif; ?>

      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
      <a class="al-pager-btn <?= $i === $page ? 'active' : '' ?>"
         href="?<?= http_build_query(array_merge($filters, ['page' => $i])) ?>">
        <?= $i ?>
      </a>
      <?php endfor; ?>

      <?php if ($page < $totalPages): ?>
      <a class="al-pager-btn" href="?<?= http_build_query(array_merge($filters, ['page' => $page + 1])) ?>">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
      <a class="al-pager-btn" href="?<?= http_build_query(array_merge($filters, ['page' => $totalPages])) ?>" title="Terakhir">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
      </a>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<!-- Purge Modal -->
<div class="al-modal-overlay" id="alPurgeModal" onclick="if(event.target===this)this.classList.remove('show')">
  <div class="al-modal">
    <div class="al-modal-body">
      <div class="al-modal-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
      </div>
      <div class="al-modal-title">Bersihkan Log Lama</div>
      <p class="al-modal-desc">Hapus semua entri log yang lebih tua dari jumlah hari yang ditentukan. Tindakan ini tidak dapat dibatalkan.</p>
      <form id="alFormPurge" method="POST" action="<?= $baseUrl ?>/admin/activity-log/purge">
        <?= Auth::csrfField() ?>
        <div class="al-days-input-wrap">
          <input type="number" name="days" value="90" min="1" class="al-days-input" placeholder="90">
          <span class="al-days-suffix">hari yang lalu</span>
        </div>
        <div class="al-modal-footer">
          <button type="button" class="al-btn al-btn-modal-cancel" onclick="document.getElementById('alPurgeModal').classList.remove('show')">Batal</button>
          <button type="submit" class="al-btn al-btn-modal-del">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
            Hapus Log
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
