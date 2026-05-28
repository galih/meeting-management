<?php
$logo   = $settings['app_logo']   ?? '';
$bg     = $settings['login_bg']   ?? '';
$apiBase = rtrim(BASE_URL, '/');
?>

<div class="row g-3">

  <!-- Logo Aplikasi -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20" height="20"
               viewBox="0 0 24 24" fill="none" stroke="#f76707" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          Logo Aplikasi
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Logo ditampilkan di sidebar dan halaman login. Format: JPG, PNG, WEBP, SVG. Maks 2 MB.</p>

        <!-- Preview -->
        <div class="mb-3 text-center">
          <div id="logo-preview-wrap" class="border rounded p-3 d-inline-block" style="min-width:160px;min-height:80px;background:#f8f9fa;">
            <?php if ($logo): ?>
            <img id="logo-preview" src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-height:80px;max-width:200px;object-fit:contain;">
            <?php else: ?>
            <span id="logo-placeholder" class="text-muted small">Belum ada logo<br><small>(menggunakan teks <?= APP_NAME ?>)</small></span>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-flex gap-2 align-items-center">
          <label class="btn btn-outline-primary mb-0" for="input-logo">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Pilih File
          </label>
          <input type="file" id="input-logo" accept="image/*" class="d-none">
          <button id="btn-upload-logo" class="btn btn-primary" disabled>Upload</button>
          <?php if ($logo): ?>
          <button id="btn-remove-logo" class="btn btn-outline-danger">Hapus</button>
          <?php endif; ?>
        </div>
        <div id="logo-msg" class="mt-2"></div>
      </div>
    </div>
  </div>

  <!-- Background Login -->
  <div class="col-md-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20" height="20"
               viewBox="0 0 24 24" fill="none" stroke="#f76707" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2"/>
            <circle cx="8.5" cy="8.5" r="1.5"/>
            <polyline points="21 15 16 10 5 21"/>
          </svg>
          Background Halaman Login
        </h3>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-3">Gambar latar halaman login. Ukuran disarankan 1920&times;1080. Format: JPG, PNG, WEBP. Maks 2 MB.</p>

        <!-- Preview -->
        <div class="mb-3 text-center">
          <div id="bg-preview-wrap" class="border rounded overflow-hidden d-inline-block" style="width:100%;height:120px;background:#f8f9fa;">
            <?php if ($bg): ?>
            <img id="bg-preview" src="<?= htmlspecialchars($bg) ?>" alt="BG" style="width:100%;height:120px;object-fit:cover;">
            <?php else: ?>
            <div class="d-flex align-items-center justify-content-center h-100">
              <span class="text-muted small">Belum ada background<br><small>(menggunakan warna default)</small></span>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="d-flex gap-2 align-items-center">
          <label class="btn btn-outline-primary mb-0" for="input-bg">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Pilih File
          </label>
          <input type="file" id="input-bg" accept="image/*" class="d-none">
          <button id="btn-upload-bg" class="btn btn-primary" disabled>Upload</button>
          <?php if ($bg): ?>
          <button id="btn-remove-bg" class="btn btn-outline-danger">Hapus</button>
          <?php endif; ?>
        </div>
        <div id="bg-msg" class="mt-2"></div>
      </div>
    </div>
  </div>

</div>

<script>
(function () {
  const BASE = '<?= $apiBase ?>';

  function handleFileInput(inputId, previewId, wrapId, placeholderId, uploadBtnId, uploadUrl, msgId) {
    const input     = document.getElementById(inputId);
    const uploadBtn = document.getElementById(uploadBtnId);
    const msg       = document.getElementById(msgId);
    if (!input) return;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      // Local preview
      const reader = new FileReader();
      reader.onload = e => {
        const wrap = document.getElementById(wrapId);
        wrap.innerHTML = `<img id="${previewId}" src="${e.target.result}" style="max-height:120px;max-width:100%;object-fit:contain;">`;
      };
      reader.readAsDataURL(file);
      uploadBtn.disabled = false;
    });

    uploadBtn.addEventListener('click', async () => {
      const file = input.files[0];
      if (!file) return;
      uploadBtn.disabled = true;
      uploadBtn.textContent = 'Mengupload...';
      const fd = new FormData();
      fd.append(inputId === 'input-logo' ? 'logo' : 'login_bg', file);
      try {
        const res  = await fetch(uploadUrl, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          msg.innerHTML = '<span class="text-success">&#10003; Berhasil diupload. Refresh halaman untuk melihat perubahan.</span>';
          setTimeout(() => location.reload(), 1500);
        } else {
          msg.innerHTML = `<span class="text-danger">&#10005; ${data.message}</span>`;
          uploadBtn.disabled = false;
          uploadBtn.textContent = 'Upload';
        }
      } catch (e) {
        msg.innerHTML = '<span class="text-danger">Terjadi kesalahan jaringan.</span>';
        uploadBtn.disabled = false;
        uploadBtn.textContent = 'Upload';
      }
    });
  }

  handleFileInput('input-logo', 'logo-preview', 'logo-preview-wrap', 'logo-placeholder',
    'btn-upload-logo', `${BASE}/api/settings/upload-logo`, 'logo-msg');

  handleFileInput('input-bg', 'bg-preview', 'bg-preview-wrap', null,
    'btn-upload-bg', `${BASE}/api/settings/upload-login-bg`, 'bg-msg');

  // Hapus logo
  document.getElementById('btn-remove-logo')?.addEventListener('click', async () => {
    if (!confirm('Hapus logo? Aplikasi akan kembali menampilkan teks nama.')) return;
    const res  = await fetch(`${BASE}/api/settings/remove-logo`, { method: 'POST' });
    const data = await res.json();
    if (data.success) location.reload();
  });

  // Hapus background
  document.getElementById('btn-remove-bg')?.addEventListener('click', async () => {
    if (!confirm('Hapus background login?')) return;
    const res  = await fetch(`${BASE}/api/settings/remove-login-bg`, { method: 'POST' });
    const data = await res.json();
    if (data.success) location.reload();
  });
})();
</script>
