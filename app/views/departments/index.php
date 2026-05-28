<?php
$baseUrl   = rtrim(BASE_URL, '/');
$levelLabel = [1 => 'Unit Kerja', 2 => 'Bidang / Bagian', 3 => 'Sub Bidang / Sub Bagian'];
$levelColor = [1 => 'blue', 2 => 'green', 3 => 'orange'];

// Rekursi render card tree
function renderUnitTree(array $nodes, array $levelLabel, array $levelColor, string $baseUrl, array $allUsers, array $parents, int $depth = 0): void {
    foreach ($nodes as $unit):
        $lbl   = $levelLabel[$unit['level']] ?? 'Unit';
        $color = $levelColor[$unit['level']] ?? 'secondary';
        $indent = $depth > 0 ? 'ms-' . ($depth * 3) : '';
?>
<div class="col-12" id="dept-col-<?= $unit['id'] ?>">
  <div class="card mb-2 <?= $indent ?>" style="border-left: 3px solid var(--tblr-<?= $color ?>);">
    <div class="card-body py-2 px-3">
      <div class="d-flex align-items-center gap-3">
        <span class="avatar avatar-sm rounded bg-<?= $color ?>-lt text-<?= $color ?> fw-bold" style="font-size:11px;">
          <?= htmlspecialchars($unit['code'] ?? mb_substr($unit['name'], 0, 2)) ?>
        </span>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold"><?= htmlspecialchars($unit['name']) ?></span>
            <span class="badge bg-<?= $color ?>-lt text-<?= $color ?>" style="font-size:10px;"><?= $lbl ?></span>
            <?php if ($unit['parent_name']): ?>
            <span class="text-muted" style="font-size:11px;">↳ <?= htmlspecialchars($unit['parent_name']) ?></span>
            <?php endif; ?>
          </div>
          <div class="text-muted" style="font-size:11px;margin-top:2px;">
            <?php if ($unit['head_name']): ?>👤 <?= htmlspecialchars($unit['head_name']) ?> &nbsp;·&nbsp;<?php endif; ?>
            👥 <?= $unit['total_users'] ?> anggota
            <?php if ($unit['description']): ?>&nbsp;·&nbsp; <?= htmlspecialchars($unit['description']) ?><?php endif; ?>
          </div>
        </div>
        <div class="d-flex gap-1">
          <button class="btn btn-sm btn-outline-primary"
                  onclick='openEditUnit(<?= htmlspecialchars(json_encode($unit), ENT_QUOTES) ?>)'>
            Edit
          </button>
          <button class="btn btn-sm btn-outline-danger btn-del-dept"
                  data-id="<?= $unit['id'] ?>"
                  data-url="<?= $baseUrl ?>/departments/<?= $unit['id'] ?>/delete">
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
  <?php if (!empty($unit['children'])): ?>
  <div class="row g-1 ms-3">
    <?php renderUnitTree($unit['children'], $levelLabel, $levelColor, $baseUrl, $allUsers, $parents, $depth + 1); ?>
  </div>
  <?php endif; ?>
</div>
<?php
    endforeach;
}
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

<!-- Header + Tombol Tambah -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-0">Unit Kerja</h3>
    <small class="text-muted">Kelola Unit Kerja, Bidang/Bagian, dan Sub Bidang/Sub Bagian</small>
  </div>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddUnit">
    + Tambah Unit
  </button>
</div>

<!-- Legend Level -->
<div class="d-flex gap-2 mb-3 flex-wrap">
  <?php foreach ($levelLabel as $lv => $lbl): ?>
  <span class="badge bg-<?= $levelColor[$lv] ?>-lt text-<?= $levelColor[$lv] ?>"><?= $lbl ?></span>
  <?php endforeach; ?>
</div>

<!-- Tree List -->
<div class="row g-2">
  <?php if (empty($tree)): ?>
  <div class="col-12 text-center text-muted py-5">
    <div style="font-size:40px;">🏢</div>
    <div class="mt-2">Belum ada unit kerja. Klik <strong>+ Tambah Unit</strong> untuk memulai.</div>
  </div>
  <?php else: ?>
  <?php renderUnitTree($tree, $levelLabel, $levelColor, $baseUrl, $allUsers, $parents, 0); ?>
  <?php endif; ?>
</div>

<!-- ============ Modal Tambah ============ -->
<div class="modal modal-blur fade" id="modalAddUnit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/departments">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Tambah Unit Kerja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Induk (kosongkan jika Unit Kerja utama)</label>
              <select name="parent_id" id="add-parent" class="form-select">
                <option value="">-- Tidak ada (Level 1: Unit Kerja) --</option>
                <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>" data-level="<?= $p['level'] ?>">
                  <?= str_repeat('&nbsp;&nbsp;&nbsp;', $p['level'] - 1) ?>
                  [Lv.<?= $p['level'] ?>] <?= htmlspecialchars($p['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text" id="add-level-hint">Akan dibuat sebagai <strong>Level 1 — Unit Kerja</strong></div>
            </div>
            <div class="col-8">
              <label class="form-label required">Nama</label>
              <input type="text" name="name" class="form-control" required placeholder="Contoh: Bidang Perencanaan">
            </div>
            <div class="col-4">
              <label class="form-label">Kode</label>
              <input type="text" name="code" class="form-control" maxlength="10" placeholder="BP">
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Kepala / Penanggung Jawab</label>
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

<!-- ============ Modal Edit ============ -->
<div class="modal modal-blur fade" id="modalEditUnit" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" id="formEditUnit">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Edit Unit Kerja</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Induk</label>
              <select name="parent_id" id="edit-parent" class="form-select">
                <option value="">-- Tidak ada (Level 1: Unit Kerja) --</option>
                <?php foreach ($parents as $p): ?>
                <option value="<?= $p['id'] ?>" data-level="<?= $p['level'] ?>">
                  <?= str_repeat('&nbsp;&nbsp;&nbsp;', $p['level'] - 1) ?>
                  [Lv.<?= $p['level'] ?>] <?= htmlspecialchars($p['name']) ?>
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text" id="edit-level-hint"></div>
            </div>
            <div class="col-8">
              <label class="form-label required">Nama</label>
              <input type="text" name="name" id="edit-unit-name" class="form-control" required>
            </div>
            <div class="col-4">
              <label class="form-label">Kode</label>
              <input type="text" name="code" id="edit-unit-code" class="form-control" maxlength="10">
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" id="edit-unit-desc" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Kepala / Penanggung Jawab</label>
              <select name="head_id" id="edit-unit-head" class="form-select">
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
const unitBaseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
const levelNames  = <?= json_encode($levelLabel) ?>;

// --- Hint level di modal tambah ---
function updateLevelHint(selectEl, hintEl) {
  const opt = selectEl.options[selectEl.selectedIndex];
  const lv  = opt.value ? (parseInt(opt.dataset.level || 1) + 1) : 1;
  const lbl = levelNames[lv] || 'Unit';
  hintEl.innerHTML = 'Akan dibuat sebagai <strong>Level ' + lv + ' — ' + lbl + '</strong>';
}

document.getElementById('add-parent').addEventListener('change', function () {
  updateLevelHint(this, document.getElementById('add-level-hint'));
});
document.getElementById('edit-parent').addEventListener('change', function () {
  updateLevelHint(this, document.getElementById('edit-level-hint'));
});

// --- Buka modal edit ---
function openEditUnit(d) {
  document.getElementById('edit-unit-name').value = d.name;
  document.getElementById('edit-unit-code').value = d.code        || '';
  document.getElementById('edit-unit-desc').value = d.description || '';
  document.getElementById('edit-unit-head').value = d.head_id     || '';
  document.getElementById('edit-parent').value    = d.parent_id   || '';
  document.getElementById('formEditUnit').action  = unitBaseUrl + '/departments/' + d.id + '/update';
  updateLevelHint(
    document.getElementById('edit-parent'),
    document.getElementById('edit-level-hint')
  );
  new bootstrap.Modal(document.getElementById('modalEditUnit')).show();
}

// --- Hapus ---
document.querySelectorAll('.btn-del-dept').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Hapus unit ini beserta semua sub-unitnya?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      location.reload();
    } else {
      alert(d.message || 'Gagal menghapus unit');
    }
  });
});
</script>
