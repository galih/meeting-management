<?php
$baseUrl = rtrim(BASE_URL, '/');
$roles   = ['admin' => 'Admin', 'sekretaris' => 'Sekretaris', 'peserta' => 'Peserta'];
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
          <th>Nama</th><th>Username</th><th>Email</th><th>Departemen</th><th>Role</th><th>Status</th>
          <th>Dibuat</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
        <tr><td colspan="9" class="text-center text-muted py-5">Tidak ada pengguna ditemukan</td></tr>
        <?php endif; ?>
        <?php foreach ($users as $i => $u): ?>
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
            <span class="badge bg-<?= match($u['role']) {
              'admin'=>'red','sekretaris'=>'orange','peserta'=>'blue',default=>'secondary'
            } ?>-lt"><?= ucfirst($u['role']) ?></span>
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
              <button class="btn btn-sm btn-outline-primary btn-edit"
                      data-user="<?= $uJson ?>">
                Edit
              </button>
              <?php if ($u['is_active'] && $u['id'] != Auth::id()): ?>
              <button class="btn btn-sm btn-outline-warning btn-nonaktif"
                      data-id="<?= $u['id'] ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/delete">
                Nonaktifkan
              </button>
              <?php endif; ?>
              <?php if ($u['id'] != Auth::id()): ?>
              <button class="btn btn-sm btn-danger btn-hapus"
                      data-id="<?= $u['id'] ?>"
                      data-name="<?= htmlspecialchars($u['name']) ?>"
                      data-url="<?= $baseUrl ?>/users/<?= $u['id'] ?>/destroy">
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
        <button type="button" class="btn btn-link link-secondary me-auto"
                data-bs-dismiss="modal">Batal</button>
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
            <label class="form-label required">Username
              <small class="text-muted">(untuk login, tanpa spasi)</small>
            </label>
            <input type="text" name="username" class="form-control" required
                   placeholder="contoh: john.doe" pattern="[a-zA-Z0-9._-]+"
                   title="Hanya huruf, angka, titik, underscore, atau strip">
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control" required placeholder="email@domain.com">
          </div>
          <div class="mb-3">
            <label class="form-label required">Password</label>
            <input type="password" name="password" class="form-control" required
                   minlength="8" placeholder="Minimal 8 karakter">
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
            <label class="form-label">Departemen</label>
            <select name="department_id" class="form-select">
              <option value="">-- Tidak ada --</option>
              <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
              <?php endforeach; ?>
            </select>
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
            <label class="form-label required">Username
              <small class="text-muted">(untuk login)</small>
            </label>
            <input type="text" name="username" id="edit-username" class="form-control" required
                   pattern="[a-zA-Z0-9._-]+"
                   title="Hanya huruf, angka, titik, underscore, atau strip">
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" id="edit-email" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru
              <small class="text-muted">(kosongkan jika tidak berubah)</small>
            </label>
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
            <label class="form-label">Departemen</label>
            <select name="department_id" id="edit-dept" class="form-select">
              <option value="">-- Tidak ada --</option>
              <?php foreach ($departments as $dept): ?>
              <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
              <?php endforeach; ?>
            </select>
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
const baseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;

// Edit
document.querySelectorAll('.btn-edit').forEach(btn => {
  btn.addEventListener('click', function () {
    const raw = this.getAttribute('data-user');
    let u;
    try { u = JSON.parse(raw); } catch (e) {
      alert('Gagal membuka form edit. Silakan refresh halaman.'); return;
    }
    document.getElementById('edit-name').value     = u.name     ?? '';
    document.getElementById('edit-username').value = u.username ?? '';
    document.getElementById('edit-email').value    = u.email    ?? '';
    document.getElementById('edit-role').value     = u.role     ?? 'peserta';
    document.getElementById('edit-dept').value     = u.department_id ?? '';
    document.getElementById('edit-active').checked = u.is_active == 1;
    document.getElementById('formEdit').action     = baseUrl + '/users/' + u.id + '/update';
    new bootstrap.Modal(document.getElementById('modalEditUser')).show();
  });
});

// Nonaktifkan
document.querySelectorAll('.btn-nonaktif').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Nonaktifkan user ini?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      const row = document.getElementById('row-' + this.dataset.id);
      if (row) {
        const badge = row.querySelector('.badge');
        if (badge) badge.outerHTML = '<span class="badge bg-secondary-lt">Nonaktif</span>';
      }
      this.remove();
    } else {
      alert(d.message || 'Gagal menonaktifkan user');
    }
  });
});

// Hapus permanen
let hapusUrl = '';
let hapusId  = '';

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
  btn.disabled = true;
  btn.textContent = 'Menghapus...';
  const res = await fetch(hapusUrl, { method: 'POST' });
  const d   = await res.json();
  bootstrap.Modal.getInstance(document.getElementById('modalHapusUser')).hide();
  btn.disabled = false;
  btn.textContent = 'Ya, Hapus';
  if (d.success) {
    const row = document.getElementById('row-' + hapusId);
    if (row) row.remove();
  } else {
    alert(d.message || 'Gagal menghapus user');
  }
});
</script>
