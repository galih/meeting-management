<?php
$baseUrl = rtrim(BASE_URL, '/');
$roles   = ['admin' => 'Admin', 'sekretaris' => 'Sekretaris', 'peserta' => 'Peserta'];
$deptL1  = array_values(array_filter($departments, fn($d) => (int)($d['level'] ?? 1) === 1));
?>

<!-- ============================================================
     KEMENBUD USERS PAGE
     Primary  : #7B1C1C  (Merah Marun)
     Accent   : #C9A84C  (Emas Kemenbud)
     Surface  : #FBF8F3  (Krem Hangat)
============================================================ -->
<style>
:root {
  --kb-primary:        #7B1C1C;
  --kb-primary-dark:   #5A1212;
  --kb-primary-light:  rgba(123,28,28,.08);
  --kb-primary-ring:   rgba(123,28,28,.18);
  --kb-gold:           #C9A84C;
  --kb-gold-dark:      #A8872F;
  --kb-gold-light:     rgba(201,168,76,.14);
  --kb-surface:        #FBF8F3;
  --kb-surface-2:      #F5F0E8;
  --kb-surface-3:      #EDE6D6;
  --kb-border:         #DDD5C4;
  --kb-border-light:   #EDE8DE;
  --kb-text:           #1C1714;
  --kb-text-muted:     #6B6055;
  --kb-text-faint:     #A89E90;
  --kb-green:          #2A6B3A;
  --kb-green-bg:       rgba(42,107,58,.10);
  --kb-blue:           #1B4F82;
  --kb-blue-bg:        rgba(27,79,130,.10);
  --kb-red:            #A8251A;
  --kb-red-bg:         rgba(168,37,26,.10);
  --kb-orange:         #C05621;
  --kb-orange-bg:      rgba(192,86,33,.10);
  --kb-gray-bg:        rgba(100,100,100,.10);
  --kb-radius:         12px;
  --kb-radius-sm:      8px;
  --kb-radius-xs:      6px;
  --kb-shadow-sm:      0 1px 4px rgba(28,23,20,.07);
  --kb-shadow-md:      0 3px 12px rgba(28,23,20,.09);
  --kb-transition:     180ms cubic-bezier(.16,1,.3,1);
}

.usr-wrap * { box-sizing: border-box; }

/* ── Hero ─────────────────────────────────────────────────────── */
.usr-hero {
  background: linear-gradient(135deg,#7B1C1C 0%,#9B2020 50%,#6A1515 100%);
  border-radius: var(--kb-radius);
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative; overflow: hidden;
  margin-bottom: 1.25rem;
}
.usr-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.usr-hero::after {
  content:''; position:absolute; inset:0;
  background:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.usr-hero-inner {
  padding: 1.25rem 1.5rem 1.1rem;
  position: relative; z-index: 1;
  display: flex; align-items: center;
  justify-content: space-between; gap: 1rem;
  flex-wrap: wrap;
}
.usr-hero-left { display:flex; align-items:center; gap:.85rem; }
.usr-hero-icon {
  width: 44px; height: 44px; border-radius: var(--kb-radius-sm);
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.22);
  display: flex; align-items: center; justify-content: center;
  color: rgba(255,255,255,.9); flex-shrink: 0;
}
.usr-hero-title {
  font-size: clamp(16px,2vw,22px); font-weight: 800;
  color: #fff; margin: 0; letter-spacing: -.02em;
}
.usr-hero-sub {
  font-size: 12.5px; color: rgba(255,255,255,.65); margin-top: .15rem;
}
.usr-hero-bar {
  height: 3px;
  background: linear-gradient(90deg,var(--kb-gold) 0%,var(--kb-gold-dark) 60%,transparent 100%);
}

/* ── Toolbar ──────────────────────────────────────────────────── */
.usr-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  gap: .75rem; flex-wrap: wrap; margin-bottom: 1rem;
}
.usr-search-form { display:flex; gap:.4rem; align-items:center; }
.usr-search-input {
  border: 1.5px solid var(--kb-border); border-radius: var(--kb-radius-sm);
  padding: .38rem .8rem; font-size: 13px; width: 240px;
  outline: none; color: var(--kb-text); background: #fff;
  transition: border-color var(--kb-transition), box-shadow var(--kb-transition);
}
.usr-search-input:focus {
  border-color: var(--kb-primary); box-shadow: 0 0 0 3px var(--kb-primary-ring);
}
.usr-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 12.5px; font-weight: 700; border-radius: var(--kb-radius-sm);
  padding: .38rem .9rem; cursor: pointer; transition: all var(--kb-transition);
  border: 1.5px solid transparent;
}
.usr-btn-primary {
  background: linear-gradient(135deg,var(--kb-primary),#9B2020);
  color: #fff; border-color: var(--kb-primary-dark);
  box-shadow: 0 2px 8px rgba(123,28,28,.25);
}
.usr-btn-primary:hover { background: linear-gradient(135deg,#9B2020,var(--kb-primary-dark)); color:#fff; }
.usr-btn-outline {
  background: #fff; color: var(--kb-text-muted);
  border-color: var(--kb-border);
}
.usr-btn-outline:hover { border-color: var(--kb-primary); color: var(--kb-primary); }
.usr-btn-ghost {
  background: transparent; color: var(--kb-text-muted); border-color: transparent;
}
.usr-btn-ghost:hover { background: var(--kb-primary-light); color: var(--kb-primary); }

/* ── Stats Row ───────────────────────────────────────────────── */
.usr-stats {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(140px,1fr));
  gap: .65rem; margin-bottom: 1rem;
}
.usr-stat-card {
  background: #fff; border: 1px solid var(--kb-border-light);
  border-radius: var(--kb-radius-sm); padding: .75rem 1rem;
  box-shadow: var(--kb-shadow-sm);
}
.usr-stat-label { font-size: 11px; font-weight: 700; color: var(--kb-text-faint); text-transform: uppercase; letter-spacing: .06em; }
.usr-stat-value { font-size: 22px; font-weight: 800; color: var(--kb-text); line-height: 1.15; margin-top: .15rem; }
.usr-stat-sub   { font-size: 11px; color: var(--kb-text-muted); margin-top: .1rem; }

/* ── Table Card ──────────────────────────────────────────────── */
.usr-card {
  background: #fff; border: 1px solid var(--kb-border-light);
  border-radius: var(--kb-radius); overflow: hidden;
  box-shadow: var(--kb-shadow-md);
}
.usr-table { width: 100%; border-collapse: collapse; }
.usr-table thead th {
  font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase;
  color: var(--kb-text-muted); padding: .65rem 1rem;
  background: var(--kb-surface); border-bottom: 1px solid var(--kb-border-light);
  white-space: nowrap;
}
.usr-table tbody tr {
  border-bottom: 1px solid var(--kb-border-light);
  transition: background var(--kb-transition);
}
.usr-table tbody tr:last-child { border-bottom: none; }
.usr-table tbody tr:hover { background: var(--kb-surface); }
.usr-table td { padding: .75rem 1rem; font-size: 13.5px; color: var(--kb-text); vertical-align: middle; }
.usr-table td.muted { color: var(--kb-text-muted); }

/* Avatar */
.usr-avatar {
  width: 34px; height: 34px; border-radius: 50%; flex-shrink: 0;
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 13px; font-weight: 800; color: #fff;
  box-shadow: 0 2px 6px rgba(123,28,20,.2);
}

/* Badges */
.usr-badge {
  display: inline-flex; align-items: center; gap: .3rem;
  font-size: 11px; font-weight: 700; padding: .2em .65em;
  border-radius: 20px; white-space: nowrap;
}
.usr-badge-red    { background: var(--kb-red-bg);    color: var(--kb-red); }
.usr-badge-orange { background: var(--kb-orange-bg); color: var(--kb-orange); }
.usr-badge-blue   { background: var(--kb-blue-bg);   color: var(--kb-blue); }
.usr-badge-green  { background: var(--kb-green-bg);  color: var(--kb-green); }
.usr-badge-gray   { background: var(--kb-gray-bg);   color: var(--kb-text-muted); }
.usr-badge-gold   { background: var(--kb-gold-light); color: #7A5C00; }

/* Status dot */
.usr-dot { width:7px; height:7px; border-radius:50%; display:inline-block; flex-shrink:0; }
.usr-dot-green  { background: #2A6B3A; }
.usr-dot-gray   { background: var(--kb-text-faint); }

/* Action buttons */
.usr-actions { display:flex; gap:.35rem; flex-wrap:wrap; }
.usr-action-btn {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 11.5px; font-weight: 700; padding: .27rem .65rem;
  border-radius: var(--kb-radius-xs); cursor: pointer;
  transition: all var(--kb-transition); border: 1.5px solid transparent;
  white-space: nowrap;
}
.ua-edit   { background: var(--kb-blue-bg); color: var(--kb-blue); border-color: rgba(27,79,130,.18); }
.ua-edit:hover { background: var(--kb-blue); color: #fff; }
.ua-off    { background: var(--kb-orange-bg); color: var(--kb-orange); border-color: rgba(192,86,33,.18); }
.ua-off:hover { background: var(--kb-orange); color: #fff; }
.ua-del    { background: var(--kb-red-bg); color: var(--kb-red); border-color: rgba(168,37,26,.18); }
.ua-del:hover { background: var(--kb-red); color: #fff; }

/* Empty state */
.usr-empty {
  padding: 3.5rem 1rem; text-align: center;
  color: var(--kb-text-faint);
}
.usr-empty-icon { margin: 0 auto .75rem; width:48px; height:48px; opacity:.45; }
.usr-empty h4 { font-size: 15px; color: var(--kb-text-muted); margin: 0 0 .3rem; font-weight: 700; }
.usr-empty p  { font-size: 13px; margin: 0; }

/* Pagination */
.usr-pagination {
  display: flex; align-items: center; justify-content: space-between;
  padding: .75rem 1rem; border-top: 1px solid var(--kb-border-light);
  background: var(--kb-surface); gap: .5rem; flex-wrap: wrap;
}
.usr-page-info { font-size: 12.5px; color: var(--kb-text-muted); }
.usr-page-list { display:flex; gap:.3rem; list-style:none; margin:0; padding:0; }
.usr-page-list a, .usr-page-list span {
  display: inline-flex; align-items: center; justify-content: center;
  width: 30px; height: 30px; border-radius: var(--kb-radius-xs);
  font-size: 12.5px; font-weight: 600; text-decoration: none;
  color: var(--kb-text-muted); border: 1.5px solid var(--kb-border);
  transition: all var(--kb-transition);
}
.usr-page-list a:hover { border-color: var(--kb-primary); color: var(--kb-primary); }
.usr-page-list .active span {
  background: var(--kb-primary); color: #fff; border-color: var(--kb-primary);
}

/* ── Modals ──────────────────────────────────────────────────── */
.usr-modal .modal-content {
  border: 1px solid var(--kb-border); border-radius: var(--kb-radius);
  box-shadow: var(--kb-shadow-md); overflow: hidden;
}
.usr-modal .modal-header {
  background: var(--kb-surface); border-bottom: 1px solid var(--kb-border-light);
  padding: .9rem 1.25rem;
}
.usr-modal .modal-title {
  font-size: 15px; font-weight: 800; color: var(--kb-primary);
  display: flex; align-items: center; gap: .5rem;
}
.usr-modal .modal-body    { padding: 1.25rem; }
.usr-modal .modal-footer  {
  background: var(--kb-surface); border-top: 1px solid var(--kb-border-light);
  padding: .75rem 1.25rem; gap: .5rem;
}
.usr-form-label {
  font-size: 12px; font-weight: 700; color: var(--kb-text);
  display: block; margin-bottom: .3rem;
}
.usr-form-label .req { color: var(--kb-red); margin-left: .15rem; }
.usr-form-control {
  width: 100%; border: 1.5px solid var(--kb-border); border-radius: var(--kb-radius-sm);
  padding: .42rem .8rem; font-size: 13.5px; color: var(--kb-text);
  background: #fff; outline: none;
  transition: border-color var(--kb-transition), box-shadow var(--kb-transition);
}
.usr-form-control:focus {
  border-color: var(--kb-primary); box-shadow: 0 0 0 3px var(--kb-primary-ring);
}
select.usr-form-control { appearance: auto; }
.usr-form-hint { font-size: 11px; color: var(--kb-text-faint); margin-top: .2rem; }
.usr-form-check { display:flex; align-items:center; gap:.5rem; font-size:13.5px; cursor:pointer; }

/* Danger confirm modal */
.usr-modal-danger .modal-header { background: var(--kb-red-bg); border-color: rgba(168,37,26,.18); }
.usr-modal-danger .modal-title  { color: var(--kb-red); }

/* Flash alerts */
.usr-alert {
  display:flex; align-items:center; gap:.6rem;
  padding: .65rem 1rem; border-radius: var(--kb-radius-sm);
  font-size: 13px; margin-bottom: .85rem;
  border: 1px solid;
}
.usr-alert-success { background: var(--kb-green-bg); color: var(--kb-green); border-color: rgba(42,107,58,.2); }
.usr-alert-danger  { background: var(--kb-red-bg);   color: var(--kb-red);   border-color: rgba(168,37,26,.2); }
.usr-alert-close {
  margin-left: auto; cursor:pointer; background:none; border:none;
  color: inherit; opacity:.6; font-size: 16px; line-height:1; padding:0;
}
.usr-alert-close:hover { opacity:1; }

/* Responsive */
@media(max-width:767.98px){
  .usr-hero-inner { flex-direction:column; align-items:flex-start; }
  .usr-stats { grid-template-columns: repeat(2,1fr); }
  .usr-search-input { width: 160px; }
  .usr-table thead { display:none; }
  .usr-table tbody tr { display:block; padding:.5rem; }
  .usr-table td {
    display:flex; justify-content:space-between; align-items:center;
    padding:.35rem .5rem; border:none; font-size:13px;
  }
  .usr-table td::before { content:attr(data-label); color:var(--kb-text-faint); font-size:11px; font-weight:700; }
}
</style>

<div class="usr-wrap">

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="usr-alert usr-alert-success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="usr-alert-close" onclick="this.closest('.usr-alert').remove()">×</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="usr-alert usr-alert-danger">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="usr-alert-close" onclick="this.closest('.usr-alert').remove()">×</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- ── Hero ── -->
<div class="usr-hero">
  <div class="usr-hero-inner">
    <div class="usr-hero-left">
      <div class="usr-hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="usr-hero-title">Manajemen Pengguna</div>
        <div class="usr-hero-sub">Kelola akun, role, dan unit kerja seluruh pengguna sistem</div>
      </div>
    </div>
    <button class="usr-btn usr-btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUser">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Pengguna
    </button>
  </div>
  <div class="usr-hero-bar"></div>
</div>

<!-- ── Stats ── -->
<?php
$totalUsers  = $total ?? count($users);
$activeCount = count(array_filter($users, fn($u) => $u['is_active']));
$adminCount  = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$sekrCount   = count(array_filter($users, fn($u) => $u['role'] === 'sekretaris'));
?>
<div class="usr-stats">
  <div class="usr-stat-card">
    <div class="usr-stat-label">Total Pengguna</div>
    <div class="usr-stat-value"><?= $totalUsers ?></div>
    <div class="usr-stat-sub">terdaftar di sistem</div>
  </div>
  <div class="usr-stat-card">
    <div class="usr-stat-label">Aktif</div>
    <div class="usr-stat-value" style="color:var(--kb-green)"><?= $activeCount ?></div>
    <div class="usr-stat-sub">akun aktif</div>
  </div>
  <div class="usr-stat-card">
    <div class="usr-stat-label">Admin</div>
    <div class="usr-stat-value" style="color:var(--kb-red)"><?= $adminCount ?></div>
    <div class="usr-stat-sub">hak akses penuh</div>
  </div>
  <div class="usr-stat-card">
    <div class="usr-stat-label">Sekretaris</div>
    <div class="usr-stat-value" style="color:var(--kb-orange)"><?= $sekrCount ?></div>
    <div class="usr-stat-sub">pengelola kegiatan</div>
  </div>
</div>

<!-- ── Toolbar ── -->
<div class="usr-toolbar">
  <form method="GET" action="<?= $baseUrl ?>/users" class="usr-search-form">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--kb-text-faint)" stroke-width="2" style="position:absolute;margin-left:.6rem;pointer-events:none">
      <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
           class="usr-search-input" style="padding-left:2rem;"
           placeholder="Cari nama, username, email…">
    <button type="submit" class="usr-btn usr-btn-outline">Cari</button>
    <?php if (!empty($search)): ?>
    <a href="<?= $baseUrl ?>/users" class="usr-btn usr-btn-ghost">Reset</a>
    <?php endif; ?>
  </form>
  <div style="font-size:12.5px;color:var(--kb-text-muted);">
    Menampilkan <strong><?= count($users) ?></strong><?php if (!empty($search)): ?> hasil untuk "<em><?= htmlspecialchars($search) ?></em>"<?php endif; ?>
  </div>
</div>

<!-- ── Table ── -->
<div class="usr-card">
  <div style="overflow-x:auto;">
    <table class="usr-table">
      <thead>
        <tr>
          <th style="width:42px;">#</th>
          <th>Pengguna</th>
          <th>Username</th>
          <th>Email</th>
          <th>Unit Kerja</th>
          <th>Role</th>
          <th>Status</th>
          <th>Bergabung</th>
          <th style="width:160px;">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr>
          <td colspan="9">
            <div class="usr-empty">
              <div class="usr-empty-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
              </div>
              <h4>Tidak ada pengguna ditemukan</h4>
              <p><?= !empty($search) ? 'Coba ubah kata kunci pencarian.' : 'Mulai dengan menambahkan pengguna baru.' ?></p>
            </div>
          </td>
        </tr>
        <?php endif; ?>
        <?php
        $avatarColors = ['#7B1C1C','#1B4F82','#2A6B3A','#C05621','#6B3A8A','#A8252A','#1A5E7A'];
        foreach ($users as $i => $u):
          $idx     = $i % count($avatarColors);
          $bgColor = $avatarColors[$idx];
          $roleBadge = match($u['role']) {
            'admin'      => ['class'=>'usr-badge-red',    'label'=>'Admin'],
            'sekretaris' => ['class'=>'usr-badge-orange',  'label'=>'Sekretaris'],
            default      => ['class'=>'usr-badge-blue',   'label'=>'Peserta'],
          };
          $uJson = htmlspecialchars(json_encode($u, JSON_HEX_QUOT|JSON_HEX_APOS|JSON_HEX_TAG|JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
        ?>
        <tr id="row-<?= $u['id'] ?>">
          <td class="muted" data-label="#"><?= ($page - 1) * 10 + $i + 1 ?></td>
          <td data-label="Pengguna">
            <div style="display:flex;align-items:center;gap:.6rem;">
              <span class="usr-avatar" style="background:<?= $bgColor ?>;">
                <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
              </span>
              <span style="font-weight:700;"><?= htmlspecialchars($u['name']) ?></span>
            </div>
          </td>
          <td class="muted" data-label="Username">
            <code style="font-size:12.5px;background:var(--kb-surface-2);padding:.15em .45em;border-radius:4px;">
              <?= htmlspecialchars($u['username']) ?>
            </code>
          </td>
          <td class="muted" data-label="Email"><?= htmlspecialchars($u['email']) ?></td>
          <td class="muted" data-label="Unit Kerja" style="max-width:160px;">
            <span style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
              <?= htmlspecialchars($u['dept_name'] ?? '—') ?>
            </span>
          </td>
          <td data-label="Role">
            <span class="usr-badge <?= $roleBadge['class'] ?>"><?= $roleBadge['label'] ?></span>
          </td>
          <td data-label="Status">
            <?php if ($u['is_active']): ?>
            <span class="usr-badge usr-badge-green">
              <span class="usr-dot usr-dot-green"></span>Aktif
            </span>
            <?php else: ?>
            <span class="usr-badge usr-badge-gray">
              <span class="usr-dot usr-dot-gray"></span>Nonaktif
            </span>
            <?php endif; ?>
          </td>
          <td class="muted" data-label="Bergabung" style="font-size:12.5px;">
            <?= date('d M Y', strtotime($u['created_at'])) ?>
          </td>
          <td data-label="Aksi">
            <div class="usr-actions">
              <button class="usr-action-btn ua-edit btn-edit" data-user="<?= $uJson ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
              </button>
              <?php if ($u['is_active'] && $u['id'] != Auth::id()): ?>
              <button class="usr-action-btn ua-off btn-nonaktif"
                      data-id="<?= $u['id'] ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/delete">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                Nonaktif
              </button>
              <?php endif; ?>
              <?php if ($u['id'] != Auth::id()): ?>
              <button class="usr-action-btn ua-del btn-hapus"
                      data-id="<?= $u['id'] ?>"
                      data-name="<?= htmlspecialchars($u['name']) ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/destroy">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                Hapus
              </button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (($totalPage ?? 1) > 1): ?>
  <div class="usr-pagination">
    <span class="usr-page-info">
      Halaman <strong><?= $page ?></strong> dari <strong><?= $totalPage ?></strong>
      &nbsp;·&nbsp; <?= $total ?> pengguna
    </span>
    <ul class="usr-page-list">
      <?php for ($p = 1; $p <= $totalPage; $p++): ?>
      <li class="<?= $p == $page ? 'active' : '' ?>">
        <?php if ($p == $page): ?>
        <span><?= $p ?></span>
        <?php else: ?>
        <a href="<?= $baseUrl ?>/users?page=<?= $p ?>&q=<?= urlencode($search ?? '') ?>"><?= $p ?></a>
        <?php endif; ?>
      </li>
      <?php endfor; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

</div><!-- /.usr-wrap -->

<!-- ================================================================
     MODAL: Konfirmasi Hapus
================================================================ -->
<div class="modal modal-blur fade usr-modal usr-modal-danger" id="modalHapusUser" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Hapus Pengguna
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:13.5px;color:var(--kb-text);padding:1.25rem;">
        Yakin ingin menghapus <strong id="hapus-nama"></strong> secara permanen?
        <div style="margin-top:.5rem;font-size:12px;color:var(--kb-red);">⚠ Tindakan ini tidak dapat dibatalkan.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="usr-btn usr-btn-ghost" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-konfirmasi-hapus" class="usr-btn usr-btn-primary"
                style="background:linear-gradient(135deg,var(--kb-red),#8B1A14);">
          Ya, Hapus
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Tambah User
================================================================ -->
<div class="modal modal-blur fade usr-modal" id="modalAddUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/users">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
            Tambah Pengguna Baru
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1rem;">
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Nama Lengkap <span class="req">*</span></label>
              <input type="text" name="name" class="usr-form-control" required placeholder="Nama lengkap">
            </div>
            <div>
              <label class="usr-form-label">Username <span class="req">*</span></label>
              <input type="text" name="username" class="usr-form-control" required
                     placeholder="contoh: john.doe" pattern="[a-zA-Z0-9._-]+">
              <div class="usr-form-hint">Tanpa spasi, hanya huruf/angka/titik/strip</div>
            </div>
            <div>
              <label class="usr-form-label">Role <span class="req">*</span></label>
              <select name="role" class="usr-form-control" required>
                <?php foreach ($roles as $val => $label): ?>
                <option value="<?= $val ?>"><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Email <span class="req">*</span></label>
              <input type="email" name="email" class="usr-form-control" required placeholder="email@domain.com">
            </div>
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Password <span class="req">*</span></label>
              <input type="password" name="password" class="usr-form-control" required minlength="8" placeholder="Minimal 8 karakter">
            </div>
          </div>

          <div style="margin-top:.9rem;">
            <label class="usr-form-label">Unit Kerja</label>
            <div style="display:flex;flex-direction:column;gap:.45rem;">
              <div>
                <select id="add-u1" class="usr-form-control" onchange="cascadeUser('add',1)">
                  <option value="">— Pilih Unit Kerja (Level 1) —</option>
                  <?php foreach ($deptL1 as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <select id="add-u2" class="usr-form-control" disabled onchange="cascadeUser('add',2)">
                  <option value="">— Pilih Bidang / Bagian (Level 2) —</option>
                </select>
              </div>
              <div>
                <select id="add-u3" class="usr-form-control" disabled onchange="cascadeUser('add',3)">
                  <option value="">— Pilih Sub Bidang (Level 3) —</option>
                </select>
              </div>
            </div>
            <input type="hidden" id="add-dept-id" name="department_id" value="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="usr-btn usr-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="usr-btn usr-btn-primary">Simpan Pengguna</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ================================================================
     MODAL: Edit User
================================================================ -->
<div class="modal modal-blur fade usr-modal" id="modalEditUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEdit">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Edit Pengguna
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem 1rem;">
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Nama Lengkap <span class="req">*</span></label>
              <input type="text" name="name" id="edit-name" class="usr-form-control" required>
            </div>
            <div>
              <label class="usr-form-label">Username <span class="req">*</span></label>
              <input type="text" name="username" id="edit-username" class="usr-form-control" required pattern="[a-zA-Z0-9._-]+">
            </div>
            <div>
              <label class="usr-form-label">Role <span class="req">*</span></label>
              <select name="role" id="edit-role" class="usr-form-control">
                <?php foreach ($roles as $val => $label): ?>
                <option value="<?= $val ?>"><?= $label ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Email <span class="req">*</span></label>
              <input type="email" name="email" id="edit-email" class="usr-form-control" required>
            </div>
            <div style="grid-column:1/-1;">
              <label class="usr-form-label">Password Baru <small style="font-weight:400;color:var(--kb-text-faint);">(kosongkan jika tidak berubah)</small></label>
              <input type="password" name="password" class="usr-form-control" minlength="8" placeholder="••••••••">
            </div>
          </div>

          <div style="margin-top:.9rem;">
            <label class="usr-form-label">Unit Kerja</label>
            <div style="display:flex;flex-direction:column;gap:.45rem;">
              <div>
                <select id="edit-u1" class="usr-form-control" onchange="cascadeUser('edit',1)">
                  <option value="">— Pilih Unit Kerja (Level 1) —</option>
                  <?php foreach ($deptL1 as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <select id="edit-u2" class="usr-form-control" disabled onchange="cascadeUser('edit',2)">
                  <option value="">— Pilih Bidang / Bagian (Level 2) —</option>
                </select>
              </div>
              <div>
                <select id="edit-u3" class="usr-form-control" disabled onchange="cascadeUser('edit',3)">
                  <option value="">— Pilih Sub Bidang (Level 3) —</option>
                </select>
              </div>
            </div>
            <input type="hidden" id="edit-dept-id" name="department_id" value="">
          </div>

          <div style="margin-top:.85rem;">
            <label class="usr-form-check">
              <input type="checkbox" name="is_active" id="edit-active" value="1"
                     style="accent-color:var(--kb-primary);width:15px;height:15px;">
              Pengguna Aktif
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="usr-btn usr-btn-ghost" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="usr-btn usr-btn-primary">Simpan Perubahan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const baseUrl         = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
const deptChildrenUrl = baseUrl + '/api/departments/children';
const allDepts        = <?= json_encode(array_values($departments)) ?>;

async function fetchDeptChildren(parentId) {
  try {
    const res = await fetch(deptChildrenUrl + '?parent_id=' + parentId);
    return await res.json();
  } catch(e) { return []; }
}

function syncHidden(prefix) {
  const v3 = document.getElementById(prefix + '-u3')?.value || '';
  const v2 = document.getElementById(prefix + '-u2')?.value || '';
  const v1 = document.getElementById(prefix + '-u1')?.value || '';
  document.getElementById(prefix + '-dept-id').value = v3 || v2 || v1 || '';
}

async function cascadeUser(prefix, level) {
  const s1 = document.getElementById(prefix + '-u1');
  const s2 = document.getElementById(prefix + '-u2');
  const s3 = document.getElementById(prefix + '-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">— Pilih Bidang / Bagian (Level 2) —</option>';
    s3.innerHTML = '<option value="">— Pilih Sub Bidang (Level 3) —</option>';
    s2.disabled = s3.disabled = true;
    syncHidden(prefix);
    if (!s1.value) return;
    const kids = await fetchDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">— Semua Bidang —</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncHidden(prefix);
  } else if (level === 2) {
    s3.innerHTML = '<option value="">— Pilih Sub Bidang (Level 3) —</option>';
    s3.disabled = true;
    syncHidden(prefix);
    if (!s2.value) return;
    const kids = await fetchDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">— Semua Sub Bidang —</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncHidden(prefix);
  } else {
    syncHidden(prefix);
  }
}

async function setEditCascade(deptId) {
  const s1 = document.getElementById('edit-u1');
  const s2 = document.getElementById('edit-u2');
  const s3 = document.getElementById('edit-u3');
  const hid = document.getElementById('edit-dept-id');
  s1.value = '';
  s2.innerHTML = '<option value="">— Pilih Bidang / Bagian (Level 2) —</option>';
  s3.innerHTML = '<option value="">— Pilih Sub Bidang (Level 3) —</option>';
  s2.disabled = s3.disabled = true;
  hid.value = deptId || '';
  if (!deptId) return;
  const node = allDepts.find(d => d.id == deptId);
  if (!node) return;
  if (node.level == 1) {
    s1.value = node.id;
  } else if (node.level == 2) {
    s1.value = node.parent_id;
    const c2 = await fetchDeptChildren(node.parent_id);
    s2.innerHTML = '<option value="">— Semua Bidang —</option>' +
      c2.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    s2.disabled = false; s2.value = node.id;
  } else if (node.level == 3) {
    const p2 = allDepts.find(d => d.id == node.parent_id);
    if (p2) {
      s1.value = p2.parent_id;
      const c2 = await fetchDeptChildren(p2.parent_id);
      s2.innerHTML = '<option value="">— Semua Bidang —</option>' +
        c2.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false; s2.value = p2.id;
      const c3 = await fetchDeptChildren(p2.id);
      s3.innerHTML = '<option value="">— Semua Sub Bidang —</option>' +
        c3.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false; s3.value = node.id;
    }
  }
  hid.value = deptId;
}

/* ── Event Listeners ── */
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', function () {
    let u;
    try { u = JSON.parse(this.getAttribute('data-user')); }
    catch(e) { alert('Gagal membuka form edit.'); return; }
    document.getElementById('edit-name').value     = u.name     ?? '';
    document.getElementById('edit-username').value = u.username ?? '';
    document.getElementById('edit-email').value    = u.email    ?? '';
    document.getElementById('edit-role').value     = u.role     ?? 'peserta';
    document.getElementById('edit-active').checked = u.is_active == 1;
    document.getElementById('formEdit').action     = baseUrl + '/users/' + u.id + '/update';
    setEditCascade(u.department_id);
    new bootstrap.Modal(document.getElementById('modalEditUser')).show();
  });
});

document.querySelectorAll('.btn-nonaktif').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Nonaktifkan pengguna ini?')) return;
    this.disabled = true;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      const row = document.getElementById('row-' + this.dataset.id);
      if (row) {
        const statusCell = row.querySelector('[data-label="Status"]');
        if (statusCell) statusCell.innerHTML = '<span class="usr-badge usr-badge-gray"><span class="usr-dot usr-dot-gray"></span>Nonaktif</span>';
        this.remove();
      }
    } else {
      alert(d.message || 'Gagal menonaktifkan pengguna');
      this.disabled = false;
    }
  });
});

let hapusUrl = '', hapusId = '';
document.querySelectorAll('.btn-hapus').forEach(btn => {
  btn.addEventListener('click', function () {
    hapusUrl = this.dataset.url;
    hapusId  = this.dataset.id;
    document.getElementById('hapus-nama').textContent = this.dataset.name;
    new bootstrap.Modal(document.getElementById('modalHapusUser')).show();
  });
});
document.getElementById('btn-konfirmasi-hapus').addEventListener('click', async () => {
  const btn = document.getElementById('btn-konfirmasi-hapus');
  btn.disabled = true; btn.textContent = 'Menghapus…';
  const res = await fetch(hapusUrl, { method: 'POST' });
  const d   = await res.json();
  bootstrap.Modal.getInstance(document.getElementById('modalHapusUser')).hide();
  btn.disabled = false; btn.textContent = 'Ya, Hapus';
  if (d.success) { document.getElementById('row-' + hapusId)?.remove(); }
  else alert(d.message || 'Gagal menghapus pengguna');
});
</script>
