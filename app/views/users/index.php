<?php
$baseUrl = rtrim(BASE_URL, '/');
$roles   = ['admin' => 'Admin', 'sekretaris' => 'Sekretaris', 'peserta' => 'Peserta'];
$deptL1  = array_values(array_filter($departments, fn($d) => (int)($d['level'] ?? 1) === 1));
// PHP 7.4 compat: array lookup menggantikan match()
$roleColor = ['admin'=>'red','sekretaris'=>'orange','peserta'=>'blue'];
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="card">
  <div class="card-header">
    <div class="col">
      <form method="GET" action="<?= $baseUrl ?>/users" class="d-flex gap-2">
        <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
               class="form-control form-control-sm" style="width:220px;"
               placeholder="Cari nama, username, atau email...">
        <button class="btn btn-sm btn-outline-secondary">Cari</button>
        <?php if (!empty($search)): ?>
        <a href="<?= $baseUrl ?>/users" class="btn btn-sm btn-ghost-secondary">Reset</a>
        <?php endif; ?>
      </form>
    </div>
    <div class="card-options">
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAddUser">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg> Tambah User
      </button>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover">
      <thead>
        <tr>
          <th style="width:40px;">#</th>
          <th>Nama</th><th>Username</th><th>Email</th><th>Unit Kerja</th><th>Role</th><th>Status</th>
          <th>Dibuat</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr><td colspan="9" class="text-center text-muted py-5">Tidak ada pengguna ditemukan</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $i => $u):
          $rc = $roleColor[$u['role']] ?? 'secondary';
        ?>
        <tr id="row-<?= $u['id'] ?>">
          <td class="text-muted"><?= ($page - 1) * 10 + $i + 1 ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <span class="avatar avatar-sm"
                    style="background:#f76707;color:#fff;font-weight:700;flex-shrink:0;">
                <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
              </span>
              <div class="fw-semibold"><?= htmlspecialchars($u['name']) ?></div>
            </div>
          </td>
          <td class="text-muted"><code><?= htmlspecialchars($u['username']) ?></code></td>
          <td class="text-muted"><?= htmlspecialchars($u['email']) ?></td>
          <td class="text-muted"><?= htmlspecialchars($u['dept_name'] ?? '-') ?></td>
          <td>
            <span class="badge bg-<?= $rc ?>-lt"><?= ucfirst($u['role']) ?></span>
          </td>
          <td>
            <?php if ($u['is_active']): ?>
            <span class="badge bg-green-lt">
              <span class="status-dot bg-green d-inline-block me-1"></span>Aktif
            </span>
            <?php else: ?>
            <span class="badge bg-secondary-lt">Nonaktif</span>
            <?php endif; ?>
          </td>
          <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
          <td>
            <?php
              $uJson = htmlspecialchars(json_encode($u, JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_TAG | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="d-flex gap-1 flex-wrap">
              <button class="btn btn-sm btn-outline-primary btn-edit" data-user="<?= $uJson ?>">Edit</button>
              <?php if ($u['is_active'] && $u['id'] != Auth::id()): ?>
              <button class="btn btn-sm btn-outline-warning btn-nonaktif"
                      data-id="<?= $u['id'] ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/delete">Nonaktifkan</button>
              <?php endif; ?>
              <?php if ($u['id'] != Auth::id()): ?>
              <button class="btn btn-sm btn-danger btn-hapus"
                      data-id="<?= $u['id'] ?>"
                      data-name="<?= htmlspecialchars($u['name']) ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/destroy">Hapus</button>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (($totalPage ?? 1) > 1): ?>
  <div class="card-footer d-flex align-items-center">
    <p class="m-0 text-muted small">
      Menampilkan <strong><?= count($users) ?></strong> dari <strong><?= $total ?></strong> pengguna
    </p>
    <ul class="pagination m-0 ms-auto">
      <?php for ($p = 1; $p <= $totalPage; $p++): ?>
      <li class="page-item <?= $p == $page ? 'active' : '' ?>">
        <a class="page-link" href="<?= $baseUrl ?>/users?page=<?= $p ?>&q=<?= urlencode($search ?? '') ?>">
          <?= $p ?>
        </a>
      </li>
      <?php endfor; ?>
    </ul>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal modal-blur fade" id="modalHapusUser" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Hapus Pengguna</div>
        <div class="text-secondary mt-2">
          Yakin ingin menghapus <strong id="hapus-nama"></strong> secara permanen?
          <br><small class="text-danger">Tindakan ini tidak dapat dibatalkan.</small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Batal</button>
        <button type="button" id="btn-konfirmasi-hapus" class="btn btn-danger">Ya, Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah User -->
<div class="modal modal-blur fade" id="modalAddUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/users">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Tambah Pengguna Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" required placeholder="Nama lengkap">
          </div>
          <div class="mb-3">
            <label class="form-label required">Username <small class="text-muted">(tanpa spasi)</small></label>
            <input type="text" name="username" class="form-control" required
                   placeholder="contoh: john.doe" pattern="[a-zA-Z0-9._-]+">
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control" required placeholder="email@domain.com">
          </div>
          <div class="mb-3">
            <label class="form-label required">Password</label>
            <input type="password" name="password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
          </div>
          <div class="mb-3">
            <label class="form-label required">Role</label>
            <select name="role" class="form-select" required>
              <?php foreach ($roles as $val => $label): ?>
              <option value="<?= $val ?>"><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-0">
            <label class="form-label">Unit Kerja</label>
            <div class="row g-2">
              <div class="col-12">
                <select id="add-u1" class="form-select form-select-sm" onchange="cascadeUser('add',1)">
                  <option value="">-- Pilih Unit Kerja --</option>
                  <?php foreach ($deptL1 as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text">Unit Kerja (Level 1)</div>
              </div>
              <div class="col-12">
                <select id="add-u2" class="form-select form-select-sm" disabled onchange="cascadeUser('add',2)">
                  <option value="">-- Pilih Unit Kerja dulu --</option>
                </select>
                <div class="form-text">Bidang / Bagian (Level 2)</div>
              </div>
              <div class="col-12">
                <select id="add-u3" class="form-select form-select-sm" disabled onchange="cascadeUser('add',3)">
                  <option value="">-- Pilih Bidang dulu --</option>
                </select>
                <div class="form-text">Sub Bidang / Sub Bagian (Level 3)</div>
              </div>
            </div>
            <input type="hidden" id="add-dept-id" name="department_id" value="">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit User -->
<div class="modal modal-blur fade" id="modalEditUser" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEdit">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Edit Pengguna</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="name" id="edit-name" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label required">Username <small class="text-muted">(untuk login)</small></label>
            <input type="text" name="username" id="edit-username" class="form-control" required pattern="[a-zA-Z0-9._-]+">
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" id="edit-email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru <small class="text-muted">(kosongkan jika tidak berubah)</small></label>
            <input type="password" name="password" class="form-control" minlength="8">
          </div>
          <div class="mb-3">
            <label class="form-label required">Role</label>
            <select name="role" id="edit-role" class="form-select">
              <?php foreach ($roles as $val => $label): ?>
              <option value="<?= $val ?>"><?= $label ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Unit Kerja</label>
            <div class="row g-2">
              <div class="col-12">
                <select id="edit-u1" class="form-select form-select-sm" onchange="cascadeUser('edit',1)">
                  <option value="">-- Pilih Unit Kerja --</option>
                  <?php foreach ($deptL1 as $d): ?>
                  <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="form-text">Unit Kerja (Level 1)</div>
              </div>
              <div class="col-12">
                <select id="edit-u2" class="form-select form-select-sm" disabled onchange="cascadeUser('edit',2)">
                  <option value="">-- Pilih Unit Kerja dulu --</option>
                </select>
                <div class="form-text">Bidang / Bagian (Level 2)</div>
              </div>
              <div class="col-12">
                <select id="edit-u3" class="form-select form-select-sm" disabled onchange="cascadeUser('edit',3)">
                  <option value="">-- Pilih Bidang dulu --</option>
                </select>
                <div class="form-text">Sub Bidang / Sub Bagian (Level 3)</div>
              </div>
            </div>
            <input type="hidden" id="edit-dept-id" name="department_id" value="">
          </div>
          <div class="mb-0">
            <label class="form-check">
              <input type="checkbox" name="is_active" id="edit-active" class="form-check-input" value="1">
              <span class="form-check-label">Pengguna Aktif</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update</button>
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
    s2.innerHTML = '<option value="">-- Pilih Unit Kerja dulu --</option>';
    s3.innerHTML = '<option value="">-- Pilih Bidang dulu --</option>';
    s2.disabled = s3.disabled = true;
    syncHidden(prefix);
    if (!s1.value) return;
    const kids = await fetchDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">-- Semua Bidang --</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncHidden(prefix);
  } else if (level === 2) {
    s3.innerHTML = '<option value="">-- Pilih Bidang dulu --</option>';
    s3.disabled = true;
    syncHidden(prefix);
    if (!s2.value) return;
    const kids = await fetchDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">-- Semua Sub Bidang --</option>' +
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
  s1.value = ''; s2.innerHTML = '<option value="">-- Pilih Unit Kerja dulu --</option>';
  s3.innerHTML = '<option value="">-- Pilih Bidang dulu --</option>';
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
    s2.innerHTML = '<option value="">-- Semua Bidang --</option>' +
      c2.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
    s2.disabled = false; s2.value = node.id;
  } else if (node.level == 3) {
    const p2 = allDepts.find(d => d.id == node.parent_id);
    if (p2) {
      s1.value = p2.parent_id;
      const c2 = await fetchDeptChildren(p2.parent_id);
      s2.innerHTML = '<option value="">-- Semua Bidang --</option>' +
        c2.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false; s2.value = p2.id;
      const c3 = await fetchDeptChildren(p2.id);
      s3.innerHTML = '<option value="">-- Semua Sub Bidang --</option>' +
        c3.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false; s3.value = node.id;
    }
  }
  hid.value = deptId;
}

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
    if (!confirm('Nonaktifkan user ini?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      const row = document.getElementById('row-' + this.dataset.id);
      if (row) row.querySelector('.badge').outerHTML = '<span class="badge bg-secondary-lt">Nonaktif</span>';
      this.remove();
    } else alert(d.message || 'Gagal menonaktifkan user');
  });
});

let hapusUrl = '', hapusId = '';
document.querySelectorAll('.btn-hapus').forEach(btn => {
  btn.addEventListener('click', function () {
    hapusUrl = this.dataset.url; hapusId = this.dataset.id;
    document.getElementById('hapus-nama').textContent = this.dataset.name;
    new bootstrap.Modal(document.getElementById('modalHapusUser')).show();
  });
});
document.getElementById('btn-konfirmasi-hapus').addEventListener('click', async () => {
  const btn = document.getElementById('btn-konfirmasi-hapus');
  btn.disabled = true; btn.textContent = 'Menghapus...';
  const res = await fetch(hapusUrl, { method: 'POST' });
  const d   = await res.json();
  bootstrap.Modal.getInstance(document.getElementById('modalHapusUser')).hide();
  btn.disabled = false; btn.textContent = 'Ya, Hapus';
  if (d.success) { document.getElementById('row-' + hapusId)?.remove(); }
  else alert(d.message || 'Gagal menghapus user');
});
</script>
