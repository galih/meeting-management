<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="card">
  <div class="card-header">
    <h3 class="card-title">✏️ Edit Kegiatan</h3>
    <div class="card-options">
      <a href="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>" class="btn btn-sm btn-outline-secondary">
        &larr; Kembali ke Detail
      </a>
    </div>
  </div>

  <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/update">
    <?= Auth::csrfField() ?>

    <div class="card-body">
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label required">Judul Kegiatan</label>
          <input type="text" name="title" class="form-control" required
                 value="<?= htmlspecialchars($meeting['title']) ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label required">Tanggal & Jam Mulai</label>
          <input type="datetime-local" name="start_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label required">Tanggal & Jam Selesai</label>
          <input type="datetime-local" name="end_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
        </div>

        <div class="col-12">
          <label class="form-label">Lokasi / Link Video</label>
          <input type="text" name="location" class="form-control"
                 placeholder="Ruang Rapat A / https://meet.google.com/..."
                 value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
        </div>

        <!-- Unit Kerja Cascade -->
        <div class="col-12">
          <label class="form-label">Unit Kerja</label>
          <div class="row g-2">
            <?php
              // Tentukan level dari department_id yang tersimpan
              $selDeptId = (int)($meeting['department_id'] ?? 0);
              $selDept   = $selDeptId ? Database::queryOne(
                "SELECT id, name, level, parent_id FROM departments WHERE id=?", [$selDeptId]
              ) : null;
              // Bangun chain: level1, level2, level3
              $sel = [1 => 0, 2 => 0, 3 => 0];
              if ($selDept) {
                $sel[$selDept['level']] = $selDept['id'];
                if ($selDept['level'] > 1) {
                  $par = Database::queryOne("SELECT id,level,parent_id FROM departments WHERE id=?", [$selDept['parent_id']]);
                  if ($par) {
                    $sel[$par['level']] = $par['id'];
                    if ($par['level'] > 1) {
                      $par2 = Database::queryOne("SELECT id,level FROM departments WHERE id=?", [$par['parent_id']]);
                      if ($par2) $sel[$par2['level']] = $par2['id'];
                    }
                  }
                }
              }
              $deptByParent = [];
              foreach ($departments as $d) {
                $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
              }
            ?>
            <div class="col-md-4">
              <select id="edit-u1" class="form-select" onchange="cascadeEdit(1)">
                <option value="">-- Semua Unit Kerja --</option>
                <?php foreach ($deptByParent[0] ?? [] as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $sel[1] == $d['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Unit Kerja</div>
            </div>
            <div class="col-md-4">
              <select id="edit-u2" class="form-select" onchange="cascadeEdit(2)"
                      <?= $sel[1] ? '' : 'disabled' ?>>
                <option value="">-- Semua Bidang --</option>
                <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $sel[2] == $d['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Bidang / Bagian</div>
            </div>
            <div class="col-md-4">
              <select id="edit-u3" class="form-select" onchange="cascadeEdit(3)"
                      <?= $sel[2] ? '' : 'disabled' ?>>
                <option value="">-- Opsional --</option>
                <?php foreach ($deptByParent[$sel[2]] ?? [] as $d): ?>
                <option value="<?= $d['id'] ?>" <?= $sel[3] == $d['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($d['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text">Sub Bidang / Sub Bagian</div>
            </div>
          </div>
          <input type="hidden" id="edit-dept-id" name="department_id"
                 value="<?= $selDeptId ?: '' ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Warna Kalender</label>
          <input type="color" name="color" class="form-control form-control-color"
                 value="<?= htmlspecialchars($meeting['color'] ?? '#206bc4') ?>">
        </div>

        <div class="col-12">
          <label class="form-label">Peserta</label>
          <select name="participants[]" class="form-select" multiple size="6">
            <?php foreach ($allUsers as $u): ?>
            <option value="<?= $u['id'] ?>"
                    <?= in_array($u['id'], $participantIds) ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <small class="text-muted">Tahan Ctrl / Cmd untuk pilih lebih dari satu</small>
        </div>

        <div class="col-12">
          <label class="form-label">Deskripsi / Agenda</label>
          <textarea name="description" class="form-control" rows="4"
                    placeholder="Tulis agenda kegiatan..."><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <div class="card-footer d-flex justify-content-end gap-2">
      <a href="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>" class="btn btn-link text-muted">Batal</a>
      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </div>
  </form>
</div>

<?php $deptChildrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
const _editDeptChildUrl = <?= json_encode($deptChildrenUrl) ?>;

async function fetchEditDeptChildren(parentId) {
  try {
    const res = await fetch(_editDeptChildUrl + '?parent_id=' + parentId);
    return await res.json();
  } catch(e) { return []; }
}

function syncEditHidden() {
  const v3 = document.getElementById('edit-u3').value;
  const v2 = document.getElementById('edit-u2').value;
  const v1 = document.getElementById('edit-u1').value;
  document.getElementById('edit-dept-id').value = v3 || v2 || v1 || '';
}

async function cascadeEdit(level) {
  const s1 = document.getElementById('edit-u1');
  const s2 = document.getElementById('edit-u2');
  const s3 = document.getElementById('edit-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">-- Semua Bidang --</option>';
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s2.disabled = s3.disabled = true;
    syncEditHidden();
    if (!s1.value) return;
    const kids = await fetchEditDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML += kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncEditHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s3.disabled = true;
    syncEditHidden();
    if (!s2.value) return;
    const kids = await fetchEditDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML += kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncEditHidden();
  } else {
    syncEditHidden();
  }
}
</script>
