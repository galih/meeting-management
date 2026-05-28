<?php
$logo    = $settings['app_logo']   ?? '';
$bg      = $settings['login_bg']   ?? '';
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

<!-- ── SMTP Settings ── -->
<div class="row g-3 mt-1">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-2" width="20" height="20"
               viewBox="0 0 24 24" fill="none" stroke="#f76707" stroke-width="2">
            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
            <polyline points="22,6 12,13 2,6"/>
          </svg>
          Pengaturan SMTP (Email)
        </h3>
        <div class="card-options">
          <span class="badge bg-blue-lt text-blue" style="font-size:11px;">Hanya Admin</span>
        </div>
      </div>
      <div class="card-body">
        <p class="text-muted small mb-4">
          Konfigurasi server SMTP untuk pengiriman email undangan, ringkasan kegiatan, dan reminder tindak lanjut.
          Password disimpan terenkripsi di database.
        </p>

        <div class="row g-3">

          <!-- SMTP Host -->
          <div class="col-md-8">
            <label class="form-label required">SMTP Host</label>
            <input type="text" class="form-control" id="smtp_host"
                   value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"
                   placeholder="smtp.gmail.com">
            <div class="form-text">Contoh: smtp.gmail.com &bull; smtp.mail.yahoo.com &bull; smtp.office365.com</div>
          </div>

          <!-- SMTP Port -->
          <div class="col-md-2">
            <label class="form-label required">Port</label>
            <input type="number" class="form-control" id="smtp_port"
                   value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>"
                   placeholder="587">
          </div>

          <!-- Enkripsi -->
          <div class="col-md-2">
            <label class="form-label required">Enkripsi</label>
            <select class="form-select" id="smtp_encryption">
              <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls'  ? 'selected' : '' ?>>TLS</option>
              <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl'  ? 'selected' : '' ?>>SSL</option>
              <option value=""    <?= ($settings['smtp_encryption'] ?? '') === ''     ? 'selected' : '' ?>>None</option>
            </select>
          </div>

          <!-- Username -->
          <div class="col-md-6">
            <label class="form-label required">Username / Email SMTP</label>
            <input type="email" class="form-control" id="smtp_username"
                   value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"
                   placeholder="akunmu@gmail.com" autocomplete="off">
          </div>

          <!-- Password -->
          <div class="col-md-6">
            <label class="form-label">Password SMTP</label>
            <div class="input-group">
              <input type="password" class="form-control" id="smtp_password"
                     placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
              <button class="btn btn-outline-secondary" type="button" id="btn-toggle-pass"
                      title="Tampilkan/sembunyikan password">
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            <div class="form-text">Untuk Gmail: gunakan <strong>App Password</strong>, bukan password akun biasa.</div>
          </div>

          <!-- From Email -->
          <div class="col-md-6">
            <label class="form-label required">From Email</label>
            <input type="email" class="form-control" id="smtp_from_email"
                   value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>"
                   placeholder="noreply@domain.com">
          </div>

          <!-- From Name -->
          <div class="col-md-6">
            <label class="form-label required">From Name</label>
            <input type="text" class="form-control" id="smtp_from_name"
                   value="<?= htmlspecialchars($settings['smtp_from_name'] ?? APP_NAME) ?>"
                   placeholder="<?= APP_NAME ?>">
          </div>

        </div>

        <!-- Tombol Simpan + Test -->
        <div class="row g-2 mt-3 align-items-end">
          <div class="col-auto">
            <button class="btn btn-primary" id="btn-save-smtp">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              Simpan SMTP
            </button>
          </div>
          <div class="col-auto">
            <div class="input-group">
              <input type="email" class="form-control" id="smtp_test_email"
                     placeholder="Email tujuan test" style="width:220px;">
              <button class="btn btn-outline-secondary" id="btn-test-smtp">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                     viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                Kirim Test
              </button>
            </div>
          </div>
          <div class="col-12">
            <div id="smtp-msg" class="mt-1"></div>
          </div>
        </div>

        <!-- Panduan cepat -->
        <div class="mt-4 p-3 bg-blue-lt rounded">
          <div class="fw-semibold mb-2" style="font-size:13px;">📌 Panduan Cepat</div>
          <div class="row g-2" style="font-size:12px;">
            <div class="col-md-4">
              <strong>Gmail</strong><br>
              Host: <code>smtp.gmail.com</code><br>
              Port: <code>587</code> &bull; TLS<br>
              Aktifkan 2FA &rarr; buat App Password
            </div>
            <div class="col-md-4">
              <strong>Yahoo Mail</strong><br>
              Host: <code>smtp.mail.yahoo.com</code><br>
              Port: <code>465</code> &bull; SSL<br>
              Aktifkan App Password di keamanan akun
            </div>
            <div class="col-md-4">
              <strong>Office 365</strong><br>
              Host: <code>smtp.office365.com</code><br>
              Port: <code>587</code> &bull; TLS<br>
              Gunakan email &amp; password akun O365
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const BASE = '<?= $apiBase ?>';

  // ── Upload handlers (logo & bg) ──
  function handleFileInput(inputId, previewId, wrapId, placeholderId, uploadBtnId, uploadUrl, msgId) {
    const input     = document.getElementById(inputId);
    const uploadBtn = document.getElementById(uploadBtnId);
    const msg       = document.getElementById(msgId);
    if (!input) return;
    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
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

  document.getElementById('btn-remove-logo')?.addEventListener('click', async () => {
    if (!confirm('Hapus logo?')) return;
    const res = await fetch(`${BASE}/api/settings/remove-logo`, { method: 'POST' });
    const d   = await res.json();
    if (d.success) location.reload();
  });
  document.getElementById('btn-remove-bg')?.addEventListener('click', async () => {
    if (!confirm('Hapus background login?')) return;
    const res = await fetch(`${BASE}/api/settings/remove-login-bg`, { method: 'POST' });
    const d   = await res.json();
    if (d.success) location.reload();
  });

  // ── Toggle password visibility ──
  document.getElementById('btn-toggle-pass')?.addEventListener('click', () => {
    const inp = document.getElementById('smtp_password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
  });

  // ── Simpan SMTP ──
  const smtpMsg  = document.getElementById('smtp-msg');
  const saveBtn  = document.getElementById('btn-save-smtp');
  const testBtn  = document.getElementById('btn-test-smtp');

  function setMsg(html, type = 'success') {
    smtpMsg.innerHTML = `<div class="alert alert-${type} py-2 mb-0">${html}</div>`;
  }

  saveBtn.addEventListener('click', async () => {
    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';
    const fd = new FormData();
    fd.append('smtp_host',       document.getElementById('smtp_host').value);
    fd.append('smtp_port',       document.getElementById('smtp_port').value);
    fd.append('smtp_encryption', document.getElementById('smtp_encryption').value);
    fd.append('smtp_username',   document.getElementById('smtp_username').value);
    fd.append('smtp_password',   document.getElementById('smtp_password').value);
    fd.append('smtp_from_email', document.getElementById('smtp_from_email').value);
    fd.append('smtp_from_name',  document.getElementById('smtp_from_name').value);
    try {
      const res  = await fetch(`${BASE}/api/settings/save-smtp`, { method: 'POST', body: fd });
      const data = await res.json();
      setMsg((data.success ? '&#10003; ' : '&#10005; ') + data.message, data.success ? 'success' : 'danger');
    } catch (e) {
      setMsg('&#10005; Gagal terhubung ke server.', 'danger');
    } finally {
      saveBtn.disabled = false;
      saveBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan SMTP`;
    }
  });

  // ── Test SMTP ──
  testBtn.addEventListener('click', async () => {
    const to = document.getElementById('smtp_test_email').value.trim();
    if (!to) { setMsg('&#10005; Masukkan email tujuan test.', 'warning'); return; }
    testBtn.disabled = true;
    testBtn.textContent = 'Mengirim...';
    const fd = new FormData();
    fd.append('test_email', to);
    try {
      const res  = await fetch(`${BASE}/api/settings/test-smtp`, { method: 'POST', body: fd });
      const data = await res.json();
      setMsg((data.success ? '&#10003; ' : '&#10005; ') + data.message, data.success ? 'success' : 'danger');
    } catch (e) {
      setMsg('&#10005; Gagal terhubung ke server.', 'danger');
    } finally {
      testBtn.disabled = false;
      testBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg> Kirim Test`;
    }
  });
})();
</script>
