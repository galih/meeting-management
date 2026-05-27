<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<div class="row row-cards">
  <?php foreach ($departments as $dept): ?>
  <div class="col-md-6 col-lg-4">
    <div class="card">
      <div class="card-body">
        <div class="d-flex align-items-center gap-3 mb-3">
          <span class="avatar avatar-md rounded" style="background:#f76707;color:#fff;font-size:16px;font-weight:800;">
            <?= htmlspecialchars($dept['code'] ?? mb_substr($dept['name'],0,2)) ?>
          </span>
          <div>
            <div class="fw-bold"><?= htmlspecialchars($dept['name']) ?></div>
            <div class="text-muted small"><?= htmlspecialchars($dept['description'] ?? '-') ?></div>
          </div>
        </div>
        <div class="d-flex justify-content-between text-muted small">
          <span>👤 Kepala: <strong><?= htmlspecialchars($dept['head_name'] ?? '-') ?></strong></span>
          <span class="badge bg-blue-lt"><?= $dept['total_users'] ?> anggota</span>
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary flex-fill"
                onclick='openEditDept(<?= htmlspecialchars(json_encode($dept), ENT_QUOTES) ?>)'>
          Edit
        </button>
        <button class="btn btn-sm btn-outline-danger" data-id="<?= $dept['id'] ?>" onclick="deleteDept(this)">
          Hapus
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- Card Tambah -->
  <div class="col-md-6 col-lg-4">
    <div class="card card-dashed h-100 d-flex align-items-center justify-content-center" style="cursor:pointer;"
         data-bs-toggle="modal" data-bs-target="#modalAddDept">
      <div class="card-body text-center text-muted py-4">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24"
             fill="none" stroke="#f76707" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        <div class="mt-2 fw-semibold">Tambah Departemen</div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal modal-blur fade" id="modalAddDept" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/departments">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Departemen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-8">
              <label class="form-label required">Nama Departemen</label>
              <input type="text" name="name" class="form-control" required
                     placeholder="Contoh: Teknologi Informasi">
            </div>
            <div class="col-4">
              <label class="form-label">Kode</label>
              <input type="text" name="code" class="form-control" maxlength="10"
                     placeholder="IT">
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Kepala Divisi</label>
              <select name="head_id" class="form-select">
                <option value="">-- Pilih User --</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
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

<!-- Modal Edit -->
<div class="modal modal-blur fade" id="modalEditDept" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditDept">
        <div class="modal-header">
          <h5 class="modal-title">Edit Departemen</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-8">
              <label class="form-label required">Nama</label>
              <input type="text" name="name" id="edit-dept-name" class="form-control" required>
            </div>
            <div class="col-4">
              <label class="form-label">Kode</label>
              <input type="text" name="code" id="edit-dept-code" class="form-control" maxlength="10">
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" id="edit-dept-desc" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Kepala Divisi</label>
              <select name="head_id" id="edit-dept-head" class="form-select">
                <option value="">-- Pilih User --</option>
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
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
function openEditDept(d) {
  document.getElementById('edit-dept-name').value = d.name;
  document.getElementById('edit-dept-code').value = d.code || '';
  document.getElementById('edit-dept-desc').value = d.description || '';
  document.getElementById('edit-dept-head').value = d.head_id || '';
  document.getElementById('formEditDept').action   = '/departments/' + d.id + '/update';
  new bootstrap.Modal(document.getElementById('modalEditDept')).show();
}
async function deleteDept(btn) {
  if (!confirm('Hapus departemen ini?')) return;
  const res = await fetch('/departments/' + btn.dataset.id + '/delete', { method: 'POST' });
  const d   = await res.json();
  if (d.success) btn.closest('.col-md-6').remove();
}
</script>
