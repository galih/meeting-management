<?php
/** @var array $roles @var array $modules @var array $rolePerms */
?>
<style>
/* ── Role Manager ─────────────────────────────────────── */
.role-card          { border-radius: 10px; border: 1px solid #e9ecef; }
.role-badge         { display:inline-block; padding:3px 10px; border-radius:20px; font-size:.78rem; font-weight:600; color:#fff; }
.perm-matrix        { width:100%; border-collapse:collapse; font-size:.82rem; }
.perm-matrix th,
.perm-matrix td     { padding:7px 10px; border:1px solid #dee2e6; vertical-align:middle; }
.perm-matrix thead th { background:#f8f9fa; font-weight:600; }
.perm-matrix .module-header { background:#e9ecef; font-weight:700; font-size:.78rem; text-transform:uppercase; letter-spacing:.05em; }
.perm-matrix .col-perm { min-width:160px; }
.perm-matrix .col-role { text-align:center; min-width:100px; }
.perm-matrix input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
.perm-matrix input[type=checkbox]:disabled { cursor:not-allowed; opacity:.5; }
.role-sys-tag       { font-size:.7rem; color:#868e96; margin-left:4px; }
.color-dot          { display:inline-block; width:12px; height:12px; border-radius:50%; margin-right:6px; vertical-align:middle; }
.btn-save-perms     { min-width:120px; }
.saving-spinner     { display:none; }
</style>

<div class="container-fluid py-4">

  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_success']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error']) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0 fw-bold">Manajemen Role &amp; Permission</h4>
      <small class="text-muted">Buat role baru dan atur hak akses per-role secara visual</small>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRole">
      <i class="bi bi-plus-circle me-1"></i> Tambah Role
    </button>
  </div>

  <!-- Role Cards -->
  <div class="row g-3 mb-4">
    <?php foreach ($roles as $r): ?>
    <div class="col-sm-6 col-md-4 col-xl-3">
      <div class="role-card p-3 h-100">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <span class="role-badge" style="background:<?= htmlspecialchars($r['color']) ?>">
              <?= htmlspecialchars($r['label']) ?>
            </span>
            <?php if ($r['is_system']): ?>
              <span class="role-sys-tag">(system)</span>
            <?php endif; ?>
            <div class="mt-1 text-muted" style="font-size:.8rem">
              <code><?= htmlspecialchars($r['name']) ?></code>
            </div>
          </div>
          <?php if (!$r['is_system']): ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="#" onclick="openEditRole(<?= $r['id'] ?>, '<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>', '<?= htmlspecialchars($r['color'], ENT_QUOTES) ?>')"><i class="bi bi-pencil me-1"></i>Edit</a></li>
              <li><a class="dropdown-item text-danger" href="#" onclick="deleteRole(<?= $r['id'] ?>, '<?= htmlspecialchars($r['label'], ENT_QUOTES) ?>')"><i class="bi bi-trash me-1"></i>Hapus</a></li>
            </ul>
          </div>
          <?php endif; ?>
        </div>
        <div class="mt-2 d-flex gap-3">
          <span class="text-muted" style="font-size:.8rem"><i class="bi bi-people"></i> <?= (int)$r['user_count'] ?> pengguna</span>
          <span class="text-muted" style="font-size:.8rem"><i class="bi bi-shield-check"></i> <?= (int)$r['perm_count'] ?> permission</span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Permission Matrix -->
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Matrix Permission</h5>
      <button class="btn btn-success btn-save-perms" id="btnSaveAllPerms" onclick="saveAllPermissions()">
        <span class="saving-spinner spinner-border spinner-border-sm me-1" id="savingSpinner"></span>
        <i class="bi bi-save me-1" id="saveIcon"></i> Simpan Perubahan
      </button>
    </div>
    <div class="card-body p-0" style="overflow-x:auto">
      <table class="perm-matrix">
        <thead>
          <tr>
            <th class="col-perm">Permission</th>
            <?php foreach ($roles as $r): ?>
            <th class="col-role">
              <span class="color-dot" style="background:<?= htmlspecialchars($r['color']) ?>"></span>
              <?= htmlspecialchars($r['label']) ?>
            </th>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php
          $moduleLabels = [
            'meeting'      => '📅 Meeting',
            'notulen'      => '📝 Notulen',
            'tindaklanjut' => '✅ Tindak Lanjut',
            'dokumen'      => '📁 Dokumen',
            'user'         => '👥 User & Role',
            'settings'     => '⚙️ Settings',
          ];
          foreach ($modules as $moduleName => $perms): ?>
          <tr>
            <td class="module-header" colspan="<?= count($roles) + 1 ?>">
              <?= $moduleLabels[$moduleName] ?? strtoupper($moduleName) ?>
            </td>
          </tr>
          <?php foreach ($perms as $perm): ?>
          <tr>
            <td class="col-perm">
              <?= htmlspecialchars($perm['label']) ?>
              <div class="text-muted" style="font-size:.72rem"><code><?= htmlspecialchars($perm['name']) ?></code></div>
            </td>
            <?php foreach ($roles as $r):
                $checked  = in_array($perm['name'], $rolePerms[$r['id']] ?? []);
                $disabled = ($r['is_system'] && $r['name'] === 'admin'); // admin selalu semua
            ?>
            <td class="col-role">
              <input type="checkbox"
                class="perm-checkbox"
                data-role-id="<?= $r['id'] ?>"
                data-perm="<?= htmlspecialchars($perm['name']) ?>"
                <?= $checked  ? 'checked'  : '' ?>
                <?= $disabled ? 'disabled' : '' ?>
              >
            </td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Tambah Role -->
<div class="modal fade" id="modalAddRole" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="<?= BASE_URL ?>/roles">
      <?= Auth::csrfField() ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Role Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Nama (slug) <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" placeholder="contoh: supervisor" required pattern="[a-zA-Z0-9_]+" title="Huruf, angka, dan underscore saja">
            <div class="form-text">Huruf kecil, angka, underscore. Tidak bisa diubah setelah dibuat.</div>
          </div>
          <div class="mb-3">
            <label class="form-label">Label Tampil <span class="text-danger">*</span></label>
            <input type="text" name="label" class="form-control" placeholder="contoh: Supervisor" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Warna Badge</label>
            <div class="d-flex align-items-center gap-2">
              <input type="color" name="color" class="form-control form-control-color" value="#6c757d" style="width:50px">
              <span class="text-muted" style="font-size:.85rem">Warna badge di daftar pengguna</span>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Role</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Edit Role -->
<div class="modal fade" id="modalEditRole" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" id="formEditRole" action="">
      <?= Auth::csrfField() ?>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Role</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Label Tampil <span class="text-danger">*</span></label>
            <input type="text" name="label" id="editRoleLabel" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Warna Badge</label>
            <input type="color" name="color" id="editRoleColor" class="form-control form-control-color" style="width:50px">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF     = document.querySelector('meta[name=csrf-token]')?.content ?? '';

function openEditRole(id, label, color) {
  document.getElementById('editRoleLabel').value = label;
  document.getElementById('editRoleColor').value = color;
  document.getElementById('formEditRole').action  = BASE_URL + '/roles/' + id + '/update';
  new bootstrap.Modal(document.getElementById('modalEditRole')).show();
}

function deleteRole(id, label) {
  if (!confirm(`Hapus role "${label}"? Aksi ini tidak bisa dibatalkan.`)) return;
  fetch(BASE_URL + '/roles/' + id + '/delete', {
    method: 'POST',
    headers: { 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' }
  })
  .then(r => r.json())
  .then(d => {
    if (d.success) { location.reload(); }
    else { alert(d.message); }
  });
}

function saveAllPermissions() {
  const spinner  = document.getElementById('savingSpinner');
  const icon     = document.getElementById('saveIcon');
  const btn      = document.getElementById('btnSaveAllPerms');
  btn.disabled   = true;
  spinner.style.display = 'inline-block';
  icon.style.display    = 'none';

  // Kumpulkan semua checkbox per role
  const byRole = {};
  document.querySelectorAll('.perm-checkbox:not([disabled])').forEach(cb => {
    const rid = cb.dataset.roleId;
    if (!byRole[rid]) byRole[rid] = [];
    if (cb.checked) byRole[rid].push(cb.dataset.perm);
  });

  // Kirim request per-role secara paralel
  const requests = Object.entries(byRole).map(([roleId, perms]) =>
    fetch(BASE_URL + '/api/roles/' + roleId + '/permissions', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF, 'X-Requested-With': 'XMLHttpRequest' },
      body:    JSON.stringify({ permissions: perms })
    }).then(r => r.json())
  );

  Promise.all(requests).then(results => {
    btn.disabled = false;
    spinner.style.display = 'none';
    icon.style.display    = '';
    const failed = results.filter(r => !r.success);
    if (failed.length) {
      alert('Gagal menyimpan beberapa role: ' + failed.map(r=>r.message).join(', '));
    } else {
      // Tampilkan toast sukses
      const toast = document.createElement('div');
      toast.className = 'alert alert-success position-fixed bottom-0 end-0 m-3 shadow';
      toast.style.zIndex = '9999';
      toast.innerHTML = '<i class="bi bi-check-circle me-1"></i>Permission berhasil disimpan!';
      document.body.appendChild(toast);
      setTimeout(() => { toast.remove(); location.reload(); }, 1500);
    }
  }).catch(e => {
    btn.disabled = false;
    spinner.style.display = 'none';
    icon.style.display    = '';
    alert('Terjadi kesalahan: ' + e.message);
  });
}
</script>
