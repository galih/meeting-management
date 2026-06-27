<?php
$baseUrl = rtrim(BASE_URL, '/');
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">➕ Buat Kegiatan Baru</h3>
    <div class="card-options">
      <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">
        &larr; Kembali ke Daftar
      </a>
    </div>
  </div>

  <form method="POST" action="<?= $baseUrl ?>/meetings">

    <div class="card-body">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label required">Judul Kegiatan</label>
          <input type="text" name="title" class="form-control" required
                 placeholder="Contoh: Rapat Koordinasi Semester I"
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label required">Tanggal & Jam Mulai</label>
          <input type="datetime-local" name="start_datetime" class="form-control" required
                 value="<?= htmlspecialchars($_POST['start_datetime'] ?? '') ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label required">Tanggal & Jam Selesai</label>
          <input type="datetime-local" name="end_datetime" class="form-control" required
                 value="<?= htmlspecialchars($_POST['end_datetime'] ?? '') ?>">
        </div>

        <div class="col-12">
          <label class="form-label">Lokasi / Link Video</label>
          <input type="text" name="location" class="form-control"
                 placeholder="Ruang Rapat A / https://meet.google.com/..."
                 value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
        </div>

        <!-- Unit Kerja Cascade -->
        <div class="col-12">
          <label class="form-label">Unit Kerja</label>
          <div class="row g-2">
            <?php
              $deptByParent = [];
              foreach ($departments as $d) {
                $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
              }
            ?>
            <div class="col-md-4">
              <select id="create-u1" class="form-select" onchange="cascadeCreate(1)">
                <option value="">-- Semua Unit Kerja --</option>
                <?php foreach ($deptByParent[0] ?? [] as $d): ?>
                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Unit Kerja</div>
            </div>
            <div class="col-md-4">
              <select id="create-u2" class="form-select" onchange="cascadeCreate(2)" disabled>
                <option value="">-- Semua Bidang --</option>
              </select>
              <div class="form-text">Bidang / Bagian</div>
            </div>
            <div class="col-md-4">
              <select id="create-u3" class="form-select" onchange="cascadeCreate(3)" disabled>
                <option value="">-- Opsional --</option>
              </select>
              <div class="form-text">Sub Bidang / Sub Bagian</div>
            </div>
          </div>
          <input type="hidden" id="create-dept-id" name="department_id" value="">
        </div>

        <div class="col-md-6">
          <label class="form-label">Warna Kalender</label>
          <input type="color" name="color" class="form-control form-control-color"
                 value="<?= htmlspecialchars($_POST['color'] ?? '#f76707') ?>">
        </div>

        <div class="col-12">
          <label class="form-label">Peserta</label>
          <select name="participants[]" class="form-select" multiple size="6">
            <?php foreach ($allUsers as $u): ?>
            <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Tahan Ctrl / Cmd untuk pilih lebih dari satu</small>
        </div>

        <div class="col-12">
          <label class="form-label">Deskripsi / Agenda</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Tulis agenda kegiatan..."><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <div class="card-footer d-flex justify-content-end gap-2">
      <a href="<?= $baseUrl ?>/meetings" class="btn btn-link text-muted">Batal</a>
      <button type="submit" class="btn btn-primary">Buat Kegiatan</button>
    </div>
  </form>
</div>

<?php $deptChildrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
const _createDeptChildUrl = <?= json_encode($deptChildrenUrl) ?>;

async function fetchCreateDeptChildren(parentId) {
  try {
    const res = await fetch(_createDeptChildUrl + '?parent_id=' + parentId);
    return await res.json();
  } catch(e) { return []; }
}

function syncCreateHidden() {
  const v3 = document.getElementById('create-u3').value;
  const v2 = document.getElementById('create-u2').value;
  const v1 = document.getElementById('create-u1').value;
  document.getElementById('create-dept-id').value = v3 || v2 || v1 || '';
}

async function cascadeCreate(level) {
  const s1 = document.getElementById('create-u1');
  const s2 = document.getElementById('create-u2');
  const s3 = document.getElementById('create-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">-- Semua Bidang --</option>';
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s2.disabled = s3.disabled = true;
    syncCreateHidden();
    if (!s1.value) return;
    const kids = await fetchCreateDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML += kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncCreateHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s3.disabled = true;
    syncCreateHidden();
    if (!s2.value) return;
    const kids = await fetchCreateDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML += kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncCreateHidden();
  } else {
    syncCreateHidden();
  }
}
</script>
