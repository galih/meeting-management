<?php
$baseUrl = rtrim(BASE_URL, '/');
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

<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0 small">Kelola template isi notulen. Setiap template dapat memiliki kop surat sendiri.</p>
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddTemplate">
    + Template Baru
  </button>
</div>

<?php if (empty($templates)): ?>
<div class="card">
  <div class="card-body text-center text-muted py-5">
    Belum ada template. Klik <strong>+ Template Baru</strong> untuk membuat.
  </div>
</div>
<?php else: ?>
<div class="row row-cards">
  <?php foreach ($templates as $tpl): ?>
  <div class="col-md-6 col-lg-4" id="tpl-col-<?= $tpl['id'] ?>">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <h4 class="card-title mb-0"><?= htmlspecialchars($tpl['name']) ?></h4>
          <div class="d-flex gap-1">
            <?php if ($tpl['is_default']): ?>
            <span class="badge bg-green-lt text-green">Default</span>
            <?php endif; ?>
            <?php if (!empty($tpl['letterhead_html'])): ?>
            <span class="badge bg-blue-lt text-blue" title="Template ini memiliki kop surat">Kop Surat ✓</span>
            <?php endif; ?>
          </div>
        </div>
        <p class="text-muted small mb-2"><?= htmlspecialchars($tpl['description'] ?? '-') ?></p>
        <div class="border rounded p-2 bg-light small" style="max-height:120px;overflow:hidden;font-size:11px;">
          <?= strip_tags($tpl['content']) ?>
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary flex-fill btn-edit-tpl"
                data-id="<?= $tpl['id'] ?>"
                data-name="<?= htmlspecialchars($tpl['name'], ENT_QUOTES) ?>"
                data-desc="<?= htmlspecialchars($tpl['description'] ?? '', ENT_QUOTES) ?>"
                data-content="<?= htmlspecialchars($tpl['content'], ENT_QUOTES) ?>"
                data-letterhead="<?= htmlspecialchars($tpl['letterhead_html'] ?? '', ENT_QUOTES) ?>"
                data-default="<?= $tpl['is_default'] ?>">
          Edit
        </button>
        <button class="btn btn-sm btn-outline-danger btn-del-tpl"
                data-id="<?= $tpl['id'] ?>"
                data-url="<?= $baseUrl ?>/notulen-templates/<?= $tpl['id'] ?>/delete">
          Hapus
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ===================== Modal Tambah ===================== -->
<div class="modal modal-blur fade" id="modalAddTemplate" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/notulen-templates">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Buat Template Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">Nama Template</label>
              <input type="text" name="name" class="form-control" required
                     placeholder="Contoh: Template Rapat Bulanan">
            </div>
            <div class="col-md-6">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" class="form-control"
                     placeholder="Keterangan singkat template ini">
            </div>

            <!-- Kop Surat -->
            <div class="col-12">
              <label class="form-label d-flex align-items-center gap-2">
                Kop Surat
                <span class="badge bg-blue-lt text-blue">Opsional</span>
              </label>
              <div class="form-hint mb-1">
                HTML kop surat yang tampil di bagian atas PDF. Kosongkan jika tidak perlu kop surat.
                Gunakan tag <code>&lt;table&gt;</code>, <code>&lt;img&gt;</code>, dsb.
              </div>
              <textarea name="letterhead_html" class="form-control font-monospace" rows="6"
                        placeholder="<table style=&quot;width:100%;border-bottom:3px double #222;&quot;>&#10;  <tr>&#10;    <td style=&quot;text-align:center;&quot;>&#10;      <strong>NAMA INSTANSI</strong><br>Alamat Instansi&#10;    </td>&#10;  </tr>&#10;</table>"
                        style="font-size:12px;"></textarea>
            </div>

            <!-- Konten Template -->
            <div class="col-12">
              <label class="form-label required">Konten Template</label>
              <div id="add-tpl-editor" style="min-height:320px;" class="border rounded"></div>
              <input type="hidden" name="content" id="add-tpl-content">
              <div class="form-hint mt-1">
                Placeholder tersedia: <code>{{MEETING_TITLE}}</code> <code>{{MEETING_DAY}}</code>
                <code>{{MEETING_DATE}}</code> <code>{{MEETING_START}}</code> <code>{{MEETING_END}}</code>
                <code>{{MEETING_LOCATION}}</code> <code>{{NOTULIS_NAME}}</code> <code>{{PARTICIPANTS}}</code>
              </div>
            </div>

            <div class="col-12">
              <label class="form-check">
                <input type="checkbox" name="is_default" class="form-check-input" value="1">
                <span class="form-check-label">Jadikan template default untuk notulen baru</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btn-submit-add-tpl">Simpan Template</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ===================== Modal Edit ===================== -->
<div class="modal modal-blur fade" id="modalEditTemplate" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <form method="POST" id="form-edit-tpl">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Edit Template</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label required">Nama Template</label>
              <input type="text" name="name" id="edit-tpl-name" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Deskripsi</label>
              <input type="text" name="description" id="edit-tpl-desc" class="form-control">
            </div>

            <!-- Kop Surat -->
            <div class="col-12">
              <label class="form-label d-flex align-items-center gap-2">
                Kop Surat
                <span class="badge bg-blue-lt text-blue">Opsional</span>
              </label>
              <div class="form-hint mb-1">HTML kop surat yang tampil di bagian atas PDF. Kosongkan jika tidak perlu.</div>
              <textarea name="letterhead_html" id="edit-tpl-letterhead" class="form-control font-monospace"
                        rows="6" style="font-size:12px;"></textarea>
            </div>

            <!-- Konten Template -->
            <div class="col-12">
              <label class="form-label required">Konten Template</label>
              <div id="edit-tpl-editor" style="min-height:320px;" class="border rounded"></div>
              <input type="hidden" name="content" id="edit-tpl-content">
              <div class="form-hint mt-1">
                Placeholder: <code>{{MEETING_TITLE}}</code> <code>{{MEETING_DAY}}</code>
                <code>{{MEETING_DATE}}</code> <code>{{MEETING_START}}</code> <code>{{MEETING_END}}</code>
                <code>{{MEETING_LOCATION}}</code> <code>{{NOTULIS_NAME}}</code> <code>{{PARTICIPANTS}}</code>
              </div>
            </div>

            <div class="col-12">
              <label class="form-check">
                <input type="checkbox" name="is_default" id="edit-tpl-default" class="form-check-input" value="1">
                <span class="form-check-label">Jadikan template default untuk notulen baru</span>
              </label>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Update Template</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Quill -->
<link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
<script>
const tplBaseUrl = <?= json_encode(rtrim(BASE_URL, '/')) ?>;
const toolbarOpts = [
  [{ header: [1,2,3,false] }],
  ['bold','italic','underline'],
  [{ list: 'ordered' }, { list: 'bullet' }],
  ['link', 'blockquote'],
  [{ align: [] }],
  ['clean']
];

const addQuill = new Quill('#add-tpl-editor', { theme:'snow', modules:{ toolbar: toolbarOpts } });
const editQuill = new Quill('#edit-tpl-editor', { theme:'snow', modules:{ toolbar: toolbarOpts } });

document.getElementById('modalAddTemplate').querySelector('form')
  .addEventListener('submit', () => {
    document.getElementById('add-tpl-content').value = addQuill.root.innerHTML;
  });

document.getElementById('form-edit-tpl').addEventListener('submit', () => {
  document.getElementById('edit-tpl-content').value = editQuill.root.innerHTML;
});

function htmlDecode(str) {
  return str.replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&')
            .replace(/&quot;/g,'"').replace(/&#039;/g,"'");
}

document.querySelectorAll('.btn-edit-tpl').forEach(btn => {
  btn.addEventListener('click', function () {
    document.getElementById('edit-tpl-name').value        = this.dataset.name;
    document.getElementById('edit-tpl-desc').value        = this.dataset.desc;
    document.getElementById('edit-tpl-default').checked   = this.dataset.default === '1';
    document.getElementById('edit-tpl-letterhead').value  = htmlDecode(this.dataset.letterhead || '');
    editQuill.root.innerHTML = htmlDecode(this.dataset.content);
    document.getElementById('form-edit-tpl').action =
      tplBaseUrl + '/notulen-templates/' + this.dataset.id + '/update';
    new bootstrap.Modal(document.getElementById('modalEditTemplate')).show();
  });
});

document.querySelectorAll('.btn-del-tpl').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Hapus template ini?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      document.getElementById('tpl-col-' + this.dataset.id)?.remove();
    } else {
      alert(d.message || 'Gagal menghapus.');
    }
  });
});
</script>
