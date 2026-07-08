<?php
$baseUrl   = rtrim(BASE_URL, '/');
$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES);
$levelLabel = [1 => 'Unit Kerja', 2 => 'Bidang / Bagian', 3 => 'Sub Bidang'];
$levelBadge = [1 => 'dept-badge-red', 2 => 'dept-badge-blue', 3 => 'dept-badge-green'];
$deptLevel  = $dept['level'] ?? 1;

// PHP <7.4 compatible stats
$countUnassigned = count(array_filter($others, function($u){ return $u['dept_name'] === '—'; }));
$countOtherDept  = count(array_filter($others, function($u){ return $u['dept_name'] !== '—'; }));
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
  --dp-radius-xs:     6px;
  --dp-shadow-sm:     0 1px 4px rgba(28,23,20,.07);
  --dp-shadow-md:     0 3px 12px rgba(28,23,20,.09);
  --dp-transition:    180ms cubic-bezier(.16,1,.3,1);
}
.dm-wrap * { box-sizing: border-box; }

/* Hero */
.dm-hero {
  background: linear-gradient(135deg,#7B1C1C 0%,#9B2020 50%,#6A1515 100%);
  border-radius: var(--dp-radius); box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative; overflow: hidden; margin-bottom: 1.25rem;
}
.dm-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.dm-hero-inner {
  padding: 1.25rem 1.5rem 1.1rem; position: relative; z-index: 1;
  display: flex; align-items: center; justify-content: space-between;
  gap: 1rem; flex-wrap: wrap;
}
.dm-hero-left   { display:flex; align-items:center; gap:.85rem; }
.dm-hero-icon   {
  width:44px; height:44px; border-radius:var(--dp-radius-sm);
  background:rgba(255,255,255,.15); border:1.5px solid rgba(255,255,255,.22);
  display:flex; align-items:center; justify-content:center;
  color:rgba(255,255,255,.9); flex-shrink:0;
}
.dm-hero-title  { font-size:clamp(16px,2vw,20px); font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.dm-hero-sub    { font-size:12px; color:rgba(255,255,255,.65); margin-top:.15rem; }
.dm-hero-bar    { height:3px; background:linear-gradient(90deg,var(--dp-gold) 0%,#A8872F 60%,transparent 100%); }

/* Buttons */
.dm-btn {
  display:inline-flex; align-items:center; gap:.35rem;
  font-size:12.5px; font-weight:700; border-radius:var(--dp-radius-sm);
  padding:.38rem .9rem; cursor:pointer; transition:all var(--dp-transition);
  border:1.5px solid transparent; text-decoration:none; background:none;
}
.dm-btn-ghost { background:rgba(255,255,255,.12); color:#fff; border-color:rgba(255,255,255,.25); }
.dm-btn-ghost:hover { background:rgba(255,255,255,.22); color:#fff; }
.dm-btn-primary {
  background:linear-gradient(135deg,var(--dp-primary),#9B2020);
  color:#fff; border-color:var(--dp-primary-dark);
  box-shadow:0 2px 8px rgba(123,28,28,.25);
}
.dm-btn-primary:hover { background:linear-gradient(135deg,#9B2020,var(--dp-primary-dark)); color:#fff; }
.dm-btn-primary:disabled { opacity:.55; cursor:not-allowed; }
.dm-btn-danger { background:var(--dp-red-bg); color:var(--dp-red); border-color:rgba(123,28,28,.25); }
.dm-btn-danger:hover { background:var(--dp-red); color:#fff; }
.dm-btn-danger:disabled { opacity:.55; cursor:not-allowed; }
.dm-btn-sm { padding:.26rem .65rem; font-size:11.5px; }

/* Alert */
.dm-alert {
  display:flex; align-items:center; gap:.6rem;
  padding:.65rem 1rem; border-radius:var(--dp-radius-sm);
  font-size:13px; margin-bottom:.85rem; border:1px solid;
}
.dm-alert-success { background:var(--dp-green-bg); color:var(--dp-green); border-color:rgba(42,107,58,.2); }
.dm-alert-danger  { background:var(--dp-red-bg);   color:var(--dp-red);   border-color:rgba(123,28,28,.2); }
.dm-alert-close   { margin-left:auto; cursor:pointer; background:none; border:none; color:inherit; opacity:.6; font-size:16px; padding:0; }
.dm-alert-close:hover { opacity:1; }

/* Stats */
.dm-stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(130px,1fr)); gap:.65rem; margin-bottom:1.1rem; }
.dm-stat-card {
  background:#fff; border:1.5px solid var(--dp-border-light);
  border-radius:var(--dp-radius-sm); padding:.75rem 1rem;
  box-shadow:var(--dp-shadow-sm);
}
.dm-stat-label { font-size:11px; font-weight:700; color:var(--dp-text-faint); text-transform:uppercase; letter-spacing:.06em; }
.dm-stat-value { font-size:22px; font-weight:800; color:var(--dp-text); line-height:1.15; margin-top:.15rem; }
.dm-stat-sub   { font-size:11px; color:var(--dp-text-muted); margin-top:.1rem; }

/* Panel */
.dm-panel {
  background:#fff; border:1.5px solid var(--dp-border-light);
  border-radius:var(--dp-radius); box-shadow:var(--dp-shadow-sm);
  overflow:hidden; margin-bottom:1rem;
}
.dm-panel-header {
  display:flex; align-items:center; justify-content:space-between;
  gap:.75rem; padding:.85rem 1.25rem;
  background:var(--dp-surface); border-bottom:1px solid var(--dp-border-light);
  flex-wrap:wrap;
}
.dm-panel-title {
  font-size:13.5px; font-weight:800; color:var(--dp-primary);
  display:flex; align-items:center; gap:.45rem;
}
.dm-panel-body  { padding:0; }

/* Table */
.dm-table { width:100%; border-collapse:collapse; font-size:13px; }
.dm-table thead tr { background:var(--dp-surface); }
.dm-table th {
  padding:.6rem 1rem; text-align:left;
  font-size:11px; font-weight:700; color:var(--dp-text-faint);
  text-transform:uppercase; letter-spacing:.05em;
  border-bottom:1px solid var(--dp-border-light);
}
.dm-table td {
  padding:.65rem 1rem; border-bottom:1px solid var(--dp-border-light);
  color:var(--dp-text); vertical-align:middle;
}
.dm-table tr:last-child td { border-bottom:none; }
.dm-table tbody tr:hover td { background:var(--dp-primary-light); }

/* Avatar */
.dm-avatar {
  width:32px; height:32px; border-radius:50%;
  background:var(--dp-primary); color:#fff;
  font-size:12px; font-weight:800;
  display:inline-flex; align-items:center; justify-content:center;
  flex-shrink:0;
}
.dm-user-cell { display:flex; align-items:center; gap:.65rem; }
.dm-user-name { font-weight:700; color:var(--dp-text); }
.dm-user-email { font-size:11.5px; color:var(--dp-text-muted); margin-top:.05rem; }

/* Badge */
.dm-badge {
  display:inline-flex; align-items:center; gap:.3rem;
  font-size:10.5px; font-weight:700; padding:.2em .65em;
  border-radius:20px; white-space:nowrap;
}
.dept-badge-red   { background:var(--dp-red-bg);   color:var(--dp-red); }
.dept-badge-blue  { background:var(--dp-blue-bg);  color:var(--dp-blue); }
.dept-badge-green { background:var(--dp-green-bg); color:var(--dp-green); }
.dm-badge-gray    { background:rgba(107,96,85,.1); color:var(--dp-text-muted); }

/* Empty */
.dm-empty { padding:3rem 1rem; text-align:center; color:var(--dp-text-faint); }
.dm-empty h4 { font-size:14px; color:var(--dp-text-muted); margin:.5rem 0 .25rem; font-weight:700; }
.dm-empty p  { font-size:12.5px; margin:0; }

/* Search filter with clear button */
.dm-search-wrap { position:relative; display:inline-flex; align-items:center; }
.dm-search-ico  { position:absolute; left:9px; top:50%; transform:translateY(-50%); color:var(--dp-text-faint); pointer-events:none; }
.dm-search-input {
  border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  padding:.38rem 1.8rem .38rem 2rem; font-size:13px; color:var(--dp-text);
  background:#fff; outline:none; width:200px;
  transition:border-color var(--dp-transition);
}
.dm-search-input:focus { border-color:var(--dp-primary); box-shadow:0 0 0 3px var(--dp-primary-ring); }
.dm-search-clear {
  position:absolute; right:7px; top:50%; transform:translateY(-50%);
  background:none; border:none; cursor:pointer; color:var(--dp-text-faint);
  font-size:14px; line-height:1; padding:0; display:none;
}
.dm-search-clear:hover { color:var(--dp-text); }

/* Modal */
.dm-modal .modal-content {
  border:1px solid var(--dp-border); border-radius:var(--dp-radius);
  box-shadow:var(--dp-shadow-md); overflow:hidden;
}
.dm-modal .modal-header {
  background:var(--dp-surface); border-bottom:1px solid var(--dp-border-light); padding:.9rem 1.25rem;
}
.dm-modal .modal-title { font-size:15px; font-weight:800; color:var(--dp-primary); display:flex; align-items:center; gap:.5rem; }
.dm-modal .modal-body   { padding:1.25rem; }
.dm-modal .modal-footer { background:var(--dp-surface); border-top:1px solid var(--dp-border-light); padding:.75rem 1.25rem; gap:.5rem; }
.dm-form-label { font-size:12px; font-weight:700; color:var(--dp-text); display:block; margin-bottom:.3rem; }
.dm-form-control {
  width:100%; border:1.5px solid var(--dp-border); border-radius:var(--dp-radius-sm);
  padding:.42rem .8rem; font-size:13.5px; color:var(--dp-text);
  background:#fff; outline:none;
  transition:border-color var(--dp-transition), box-shadow var(--dp-transition);
}
.dm-form-control:focus { border-color:var(--dp-primary); box-shadow:0 0 0 3px var(--dp-primary-ring); }
select.dm-form-control { appearance:auto; }
.dm-form-hint { font-size:11px; color:var(--dp-text-faint); margin-top:.2rem; }
.dm-modal-danger .modal-header { background:var(--dp-red-bg); border-color:rgba(123,28,28,.18); }
.dm-modal-danger .modal-title  { color:var(--dp-red); }

/* fix: scope mobile hide to tbl-others only so tbl-members Aksi column stays */
@media(max-width:600px){
  .dm-hero-inner { flex-direction:column; align-items:flex-start; }
  .dm-search-input { width:100%; }
  .dm-panel-header { flex-direction:column; align-items:flex-start; }
  #tbl-others th:nth-child(3), #tbl-others td:nth-child(3) { display:none; }
}
</style>

<div class="dm-wrap">

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="dm-alert dm-alert-success">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button class="dm-alert-close" onclick="this.closest('.dm-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="dm-alert dm-alert-danger">
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button class="dm-alert-close" onclick="this.closest('.dm-alert').remove()">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- Hero -->
<div class="dm-hero">
  <div class="dm-hero-inner">
    <div class="dm-hero-left">
      <div class="dm-hero-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </div>
      <div>
        <div class="dm-hero-title">
          Anggota &mdash; <?= htmlspecialchars($dept['name']) ?>
          <span class="dm-badge <?= $levelBadge[$deptLevel] ?? 'dept-badge-red' ?>" style="font-size:10px;">
            <?= $levelLabel[$deptLevel] ?? 'Unit' ?>
          </span>
        </div>
        <div class="dm-hero-sub">
          <?php if ($dept['head_name']): ?>
            Kepala: <?= htmlspecialchars($dept['head_name']) ?> &nbsp;&bull;&nbsp;
          <?php endif; ?>
          <?= count($members) ?> anggota aktif
        </div>
      </div>
    </div>
    <a href="<?= $baseUrl ?>/departments" class="dm-btn dm-btn-ghost">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
      Kembali
    </a>
  </div>
  <div class="dm-hero-bar"></div>
</div>

<!-- Stats -->
<div class="dm-stats">
  <div class="dm-stat-card">
    <div class="dm-stat-label">Total Anggota</div>
    <div class="dm-stat-value" style="color:var(--dp-primary)"><?= count($members) ?></div>
    <div class="dm-stat-sub">aktif saat ini</div>
  </div>
  <div class="dm-stat-card">
    <div class="dm-stat-label">Belum Ditugaskan</div>
    <div class="dm-stat-value" style="color:var(--dp-text-muted)"><?= $countUnassigned ?></div>
    <div class="dm-stat-sub">user tanpa unit</div>
  </div>
  <div class="dm-stat-card">
    <div class="dm-stat-label">Dari Unit Lain</div>
    <div class="dm-stat-value" style="color:var(--dp-blue)"><?= $countOtherDept ?></div>
    <div class="dm-stat-sub">dapat dipindah</div>
  </div>
</div>

<!-- ── Panel: Anggota saat ini ── -->
<div class="dm-panel">
  <div class="dm-panel-header">
    <div class="dm-panel-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Anggota Saat Ini
    </div>
    <div class="dm-search-wrap">
      <svg class="dm-search-ico" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="search-members" class="dm-search-input" placeholder="Cari anggota…"
             oninput="filterTable('tbl-members', this.value); toggleClear('clear-members', this.value)">
      <button type="button" id="clear-members" class="dm-search-clear" title="Hapus pencarian"
              onclick="clearSearch('search-members','tbl-members','clear-members')">&times;</button>
    </div>
  </div>
  <div class="dm-panel-body">
    <?php if (empty($members)): ?>
    <div class="dm-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;margin:0 auto;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      <h4>Belum ada anggota</h4>
      <p>Tambahkan anggota dari panel di bawah.</p>
    </div>
    <?php else: ?>
    <table class="dm-table" id="tbl-members">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($members as $m): ?>
        <tr>
          <td>
            <div class="dm-user-cell">
              <div class="dm-avatar"><?= mb_strtoupper(mb_substr($m['name'], 0, 1)) ?></div>
              <div>
                <div class="dm-user-name"><?= htmlspecialchars($m['name']) ?></div>
              </div>
            </div>
          </td>
          <td style="color:var(--dp-text-muted);font-size:12.5px;"><?= htmlspecialchars($m['email'] ?? '—') ?></td>
          <td>
            <!-- fix: use ENT_QUOTES on data-name to prevent attribute-context XSS -->
            <button class="dm-btn dm-btn-danger dm-btn-sm btn-remove-member"
                    data-id="<?= (int)$m['id'] ?>"
                    data-name="<?= htmlspecialchars($m['name'], ENT_QUOTES, 'UTF-8') ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              Lepas
            </button>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<!-- ── Panel: Tambah anggota ── -->
<div class="dm-panel">
  <div class="dm-panel-header">
    <div class="dm-panel-title">
      <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Anggota
    </div>
    <div class="dm-search-wrap">
      <svg class="dm-search-ico" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="search-others" class="dm-search-input" placeholder="Cari pengguna…"
             oninput="filterTable('tbl-others', this.value); toggleClear('clear-others', this.value)">
      <button type="button" id="clear-others" class="dm-search-clear" title="Hapus pencarian"
              onclick="clearSearch('search-others','tbl-others','clear-others')">&times;</button>
    </div>
  </div>
  <div class="dm-panel-body">
    <?php if (empty($others)): ?>
    <div class="dm-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:.35;margin:0 auto;"><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      <h4>Semua pengguna sudah menjadi anggota</h4>
      <p>Tidak ada pengguna lain yang tersedia.</p>
    </div>
    <?php else: ?>
    <table class="dm-table" id="tbl-others">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Unit Saat Ini</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($others as $o): ?>
        <tr>
          <td>
            <div class="dm-user-cell">
              <div class="dm-avatar" style="background:var(--dp-blue);"><?= mb_strtoupper(mb_substr($o['name'], 0, 1)) ?></div>
              <div>
                <div class="dm-user-name"><?= htmlspecialchars($o['name']) ?></div>
              </div>
            </div>
          </td>
          <td style="color:var(--dp-text-muted);font-size:12.5px;"><?= htmlspecialchars($o['email'] ?? '—') ?></td>
          <td>
            <?php if ($o['dept_name'] === '—'): ?>
              <span class="dm-badge dm-badge-gray">Belum ditugaskan</span>
            <?php else: ?>
              <span class="dm-badge dm-badge-gray"><?= htmlspecialchars($o['dept_name']) ?></span>
            <?php endif; ?>
          </td>
          <td>
            <!--
              fix: replace inline onsubmit JS string injection with data-attributes.
              confirmAssign is now bound via event listener in JS block below.
            -->
            <form method="POST"
                  action="<?= $baseUrl ?>/departments/<?= (int)$dept['id'] ?>/assign-member"
                  class="form-assign"
                  data-uid="<?= (int)$o['id'] ?>"
                  data-uname="<?= htmlspecialchars($o['name'], ENT_QUOTES, 'UTF-8') ?>"
                  data-dept="<?= htmlspecialchars($o['dept_name'], ENT_QUOTES, 'UTF-8') ?>">
              <?= Auth::csrfField() ?>
              <input type="hidden" name="user_id" value="<?= (int)$o['id'] ?>">
              <button type="submit" class="dm-btn dm-btn-primary dm-btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

</div><!-- /.dm-wrap -->

<!-- Modal konfirmasi lepas anggota -->
<div class="modal modal-blur fade dm-modal dm-modal-danger" id="modalRemoveMember" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Lepas Anggota
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="font-size:13.5px;color:var(--dp-text);">
        Yakin ingin melepas <strong id="remove-member-name"></strong> dari unit ini?
        <div style="margin-top:.4rem;font-size:12px;color:var(--dp-text-muted);">User tidak akan dihapus, hanya dilepas dari unit kerja ini.</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="dm-btn dm-btn-ghost" data-bs-dismiss="modal" style="color:var(--dp-text);border-color:var(--dp-border);">Batal</button>
        <form id="form-remove-member" method="POST">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="user_id" id="remove-member-uid">
          <button type="submit" class="dm-btn dm-btn-danger">Ya, Lepas</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var BASE    = <?= json_encode($baseUrl) ?>;
  var DEPT_ID = <?= (int)$dept['id'] ?>;

  /* ── Search/filter tabel ── */
  window.filterTable = function(tableId, query) {
    var q    = query.toLowerCase().trim();
    var rows = document.querySelectorAll('#' + tableId + ' tbody tr');
    rows.forEach(function(row) {
      row.style.display = (!q || row.textContent.toLowerCase().indexOf(q) > -1) ? '' : 'none';
    });
  };

  /* ── Toggle clear (×) button visibility ── */
  window.toggleClear = function(clearId, value) {
    var btn = document.getElementById(clearId);
    if (btn) btn.style.display = value ? 'block' : 'none';
  };

  /* ── Clear search and reset table ── */
  window.clearSearch = function(inputId, tableId, clearId) {
    var inp = document.getElementById(inputId);
    if (inp) { inp.value = ''; inp.focus(); }
    filterTable(tableId, '');
    toggleClear(clearId, '');
  };

  /* ── fix: bind confirmAssign via event listener, not inline onsubmit ──
     Reads name and current dept from data-attributes to avoid JS string injection. */
  document.querySelectorAll('.form-assign').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      var name = this.dataset.uname || '';
      var dept = this.dataset.dept  || '';
      if (dept && dept !== '—') {
        if (!confirm('"' + name + '" saat ini berada di unit "' + dept + '". Pindahkan ke unit ini?')) {
          e.preventDefault();
          return;
        }
      }
      /* fix: prevent double-submit */
      var btn = this.querySelector('button[type=submit]');
      if (btn) { btn.disabled = true; btn.textContent = '…'; }
    });
  });

  /* ── fix: prevent double-submit on remove form ── */
  document.getElementById('form-remove-member').addEventListener('submit', function() {
    var btn = this.querySelector('button[type=submit]');
    if (btn) btn.disabled = true;
  });

  /* ── Bootstrap modal helper with vanilla fallback ── */
  function bsModal(el) {
    var BS = window.bootstrap;
    if (BS && BS.Modal) return new BS.Modal(el);
    return {
      show: function() {
        el.classList.add('show'); el.style.display = 'block';
        document.body.classList.add('modal-open');
      },
      hide: function() {
        el.classList.remove('show'); el.style.display = '';
        document.body.classList.remove('modal-open');
      }
    };
  }

  /* ── Modal lepas anggota ── */
  document.querySelectorAll('.btn-remove-member').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var uid  = this.dataset.id;
      var name = this.dataset.name;
      document.getElementById('remove-member-name').textContent = name;
      document.getElementById('remove-member-uid').value = uid;
      document.getElementById('form-remove-member').action =
        BASE + '/departments/' + DEPT_ID + '/remove-member';
      /* reset disabled state in case modal was opened before */
      var submitBtn = document.querySelector('#form-remove-member button[type=submit]');
      if (submitBtn) submitBtn.disabled = false;
      bsModal(document.getElementById('modalRemoveMember')).show();
    });
  });
})();
</script>
