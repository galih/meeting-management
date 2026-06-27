<?php
$logo    = $settings['app_logo']   ?? '';
$bg      = $settings['login_bg']   ?? '';
$apiBase = rtrim(BASE_URL, '/');
?>
<style>
/* ===== ST: Settings Namespace ===== */
.st-hero {
  background: linear-gradient(135deg, #7B1C1C 0%, #9B2020 60%, #5A1212 100%);
  border-radius: 14px;
  padding: 1.5rem 2rem;
  margin-bottom: 1.5rem;
  color: #fff;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.st-hero::before {
  content: '';
  position: absolute; inset: 0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events: none;
}
.st-hero-icon {
  width: 52px; height: 52px; border-radius: 12px;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; position: relative;
}
.st-hero-title  { font-size: 1.3rem; font-weight: 800; line-height: 1.2; }
.st-hero-sub    { font-size: 13px; color: rgba(255,255,255,.70); margin-top: .2rem; }
.st-gold-bar {
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #C9A84C, #A8872F, #C9A84C);
  border-radius: 0 0 14px 14px;
}

/* Tab nav */
.st-tabs {
  display: flex; gap: .35rem;
  border-bottom: 2px solid #E8E2D9;
  margin-bottom: 1.5rem;
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}
.st-tab {
  display: inline-flex; align-items: center; gap: .45rem;
  padding: .55rem 1.1rem;
  font-size: 13.5px; font-weight: 700;
  color: #6B6055; text-decoration: none;
  border-bottom: 2.5px solid transparent;
  margin-bottom: -2px;
  cursor: pointer; background: none; border-top: none; border-left: none; border-right: none;
  white-space: nowrap;
  transition: color 140ms, border-color 140ms;
}
.st-tab:hover { color: #7B1C1C; }
.st-tab.active { color: #7B1C1C; border-bottom-color: #7B1C1C; }
.st-tab svg { opacity: .6; }
.st-tab.active svg { opacity: 1; }

/* Tab panels */
.st-panel { display: none; }
.st-panel.active { display: block; }

/* Card */
.st-card {
  background: #fff;
  border: 1px solid #E8E2D9;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(28,23,20,.06);
  margin-bottom: 1.25rem;
}
.st-card-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #F0EBE2;
  display: flex; align-items: center; justify-content: space-between; gap: .5rem;
}
.st-card-title {
  display: flex; align-items: center; gap: .55rem;
  font-size: 14.5px; font-weight: 800; color: #1C1714;
}
.st-card-title svg { color: #7B1C1C; }
.st-card-body { padding: 1.25rem; }

/* Form controls */
.st-label {
  display: block;
  font-size: 11.5px; font-weight: 700;
  color: #6B6055; letter-spacing: .03em; text-transform: uppercase;
  margin-bottom: .3rem;
}
.st-label .req { color: #C05621; margin-left: .15rem; }
.st-ctrl {
  width: 100%;
  height: 40px;
  border: 1.5px solid #DDD5C4;
  border-radius: 8px;
  padding: 0 .85rem;
  font-size: 13.5px; color: #1C1714;
  background: #FDFCFA;
  transition: border-color 180ms;
  outline: none;
  appearance: none; -webkit-appearance: none;
}
.st-ctrl:focus { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.08); }
select.st-ctrl {
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236B6055' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right .7rem center;
  padding-right: 2rem;
}
.st-hint { font-size: 11.5px; color: #A89E90; margin-top: .3rem; line-height: 1.4; }
.st-group { margin-bottom: 1rem; }
.st-row { display: flex; gap: .85rem; flex-wrap: wrap; }
.st-row > .st-group { flex: 1; min-width: 140px; }

/* Input group (password toggle, test email) */
.st-input-group {
  display: flex;
  border: 1.5px solid #DDD5C4;
  border-radius: 8px;
  overflow: hidden;
  transition: border-color 180ms;
}
.st-input-group:focus-within { border-color: #7B1C1C; box-shadow: 0 0 0 3px rgba(123,28,28,.08); }
.st-input-group .st-ctrl {
  border: none; border-radius: 0;
  box-shadow: none !important;
  flex: 1;
}
.st-input-group-addon {
  display: flex; align-items: center; justify-content: center;
  padding: 0 .75rem;
  background: #F5F0E8;
  border-left: 1.5px solid #DDD5C4;
  color: #6B6055; font-size: 13px; font-weight: 600;
  cursor: pointer; white-space: nowrap;
  transition: background 140ms, color 140ms;
}
.st-input-group-addon:hover { background: #EDE6DC; color: #1C1714; }
.st-input-group-addon button {
  background: none; border: none; cursor: pointer;
  display: flex; align-items: center; color: inherit;
}

/* Buttons */
.st-btn {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: 13.5px; font-weight: 700;
  height: 40px; padding: 0 1.1rem;
  border-radius: 8px; cursor: pointer;
  border: 1.5px solid transparent;
  transition: all 180ms cubic-bezier(.16,1,.3,1);
  white-space: nowrap; text-decoration: none;
}
.st-btn-primary { background: #7B1C1C; color: #fff; border-color: #5A1212; }
.st-btn-primary:hover { background: #5A1212; color: #fff; }
.st-btn-outline { background: #fff; color: #6B6055; border-color: #DDD5C4; }
.st-btn-outline:hover { border-color: #7B1C1C; color: #7B1C1C; }
.st-btn-danger { background: #fff; color: #C05621; border-color: #DDD5C4; }
.st-btn-danger:hover { background: #C05621; color: #fff; border-color: #A8421A; }
.st-btn:disabled { opacity: .5; cursor: not-allowed; }

/* Upload preview */
.st-preview-box {
  border: 2px dashed #DDD5C4;
  border-radius: 10px;
  background: #FDFCFA;
  display: flex; align-items: center; justify-content: center;
  overflow: hidden;
  transition: border-color 180ms;
}
.st-preview-box:hover { border-color: #7B1C1C; }
.st-preview-empty {
  text-align: center; padding: 1.5rem;
  color: #A89E90; font-size: 12.5px; line-height: 1.5;
}
.st-preview-empty svg { margin: 0 auto .5rem; display: block; color: #DDD5C4; }

/* Flash / Message */
.st-msg { margin-top: .75rem; font-size: 13px; }
.st-msg-ok  { color: #27A155; display: flex; align-items: center; gap: .4rem; }
.st-msg-err { color: #C05621; display: flex; align-items: center; gap: .4rem; }

/* Info box (SMTP guide) */
.st-infobox {
  background: #F5F0E8;
  border: 1px solid #E8E2D9;
  border-radius: 10px;
  padding: 1rem 1.25rem;
  margin-top: 1.25rem;
}
.st-infobox-title { font-size: 12.5px; font-weight: 800; color: #1C1714; margin-bottom: .75rem; display: flex; align-items: center; gap: .4rem; }
.st-guide-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(180px,1fr)); gap: .85rem; }
.st-guide-item {
  background: #fff; border: 1px solid #E8E2D9;
  border-radius: 8px; padding: .75rem;
  font-size: 12px; line-height: 1.6; color: #6B6055;
}
.st-guide-item strong { display: block; font-size: 12.5px; color: #1C1714; margin-bottom: .25rem; }
.st-guide-item code { background: #F0EBE2; padding: .05em .35em; border-radius: 4px; font-size: 11px; color: #7B1C1C; }

/* Admin badge */
.st-admin-badge {
  font-size: 11px; font-weight: 700;
  background: rgba(0,100,148,.1); color: #006494;
  border: 1px solid rgba(0,100,148,.2);
  border-radius: 20px; padding: .15em .7em;
}

/* Divider */
.st-divider { border: none; border-top: 1px solid #F0EBE2; margin: 1.25rem 0; }

@media (max-width: 640px) {
  .st-hero { padding: 1.1rem; }
  .st-card-body { padding: 1rem; }
  .st-row > .st-group { min-width: 100%; }
}
</style>

<!-- Hero -->
<div class="st-hero">
  <div class="st-hero-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8">
      <circle cx="12" cy="12" r="3"/>
      <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
    </svg>
  </div>
  <div>
    <div class="st-hero-title">Pengaturan Sistem</div>
    <div class="st-hero-sub">Kelola logo, tampilan, dan konfigurasi email aplikasi</div>
  </div>
  <div class="st-gold-bar"></div>
</div>

<!-- Tabs -->
<div class="st-tabs" role="tablist">
  <button class="st-tab active" data-tab="branding" role="tab">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    Branding
  </button>
  <button class="st-tab" data-tab="smtp" role="tab">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
    Email / SMTP
  </button>
</div>

<!-- Panel: Branding -->
<div class="st-panel active" id="panel-branding">
  <div class="st-row">

    <!-- Logo -->
    <div class="st-group" style="flex:1;min-width:260px">
      <div class="st-card">
        <div class="st-card-header">
          <div class="st-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            Logo Aplikasi
          </div>
        </div>
        <div class="st-card-body">
          <p class="st-hint" style="margin-bottom:.85rem">Ditampilkan di sidebar dan halaman login. Format: JPG, PNG, WEBP, SVG. Maks 2 MB.</p>

          <div class="st-preview-box" id="logo-preview-wrap" style="height:100px;margin-bottom:.85rem">
            <?php if ($logo): ?>
            <img id="logo-preview" src="<?= htmlspecialchars($logo) ?>" alt="Logo" style="max-height:90px;max-width:100%;object-fit:contain;padding:.5rem">
            <?php else: ?>
            <div class="st-preview-empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Belum ada logo<br><small>Menggunakan teks <?= APP_NAME ?></small>
            </div>
            <?php endif; ?>
          </div>

          <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <label class="st-btn st-btn-outline" for="input-logo" style="cursor:pointer;margin:0">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Pilih File
            </label>
            <input type="file" id="input-logo" accept="image/*" style="display:none">
            <button id="btn-upload-logo" class="st-btn st-btn-primary" disabled>Upload</button>
            <?php if ($logo): ?>
            <button id="btn-remove-logo" class="st-btn st-btn-danger">Hapus</button>
            <?php endif; ?>
          </div>
          <div id="logo-msg" class="st-msg"></div>
        </div>
      </div>
    </div>

    <!-- Background Login -->
    <div class="st-group" style="flex:1;min-width:260px">
      <div class="st-card">
        <div class="st-card-header">
          <div class="st-card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            Background Halaman Login
          </div>
        </div>
        <div class="st-card-body">
          <p class="st-hint" style="margin-bottom:.85rem">Ukuran disarankan 1920&times;1080. Format: JPG, PNG, WEBP. Maks 2 MB.</p>

          <div class="st-preview-box" id="bg-preview-wrap" style="height:100px;margin-bottom:.85rem">
            <?php if ($bg): ?>
            <img id="bg-preview" src="<?= htmlspecialchars($bg) ?>" alt="Background" style="width:100%;height:100px;object-fit:cover">
            <?php else: ?>
            <div class="st-preview-empty">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              Belum ada background<br><small>Menggunakan warna default</small>
            </div>
            <?php endif; ?>
          </div>

          <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <label class="st-btn st-btn-outline" for="input-bg" style="cursor:pointer;margin:0">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
              Pilih File
            </label>
            <input type="file" id="input-bg" accept="image/*" style="display:none">
            <button id="btn-upload-bg" class="st-btn st-btn-primary" disabled>Upload</button>
            <?php if ($bg): ?>
            <button id="btn-remove-bg" class="st-btn st-btn-danger">Hapus</button>
            <?php endif; ?>
          </div>
          <div id="bg-msg" class="st-msg"></div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Panel: SMTP -->
<div class="st-panel" id="panel-smtp">
  <div class="st-card">
    <div class="st-card-header">
      <div class="st-card-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
        Pengaturan SMTP
      </div>
      <span class="st-admin-badge">Hanya Admin</span>
    </div>
    <div class="st-card-body">
      <p class="st-hint" style="margin-bottom:1.25rem">
        Konfigurasi server SMTP untuk pengiriman email undangan, ringkasan kegiatan, dan reminder tindak lanjut.
        Password disimpan terenkripsi di database.
      </p>

      <!-- Row 1: Host, Port, Enkripsi -->
      <div class="st-row">
        <div class="st-group" style="flex:3">
          <label class="st-label">SMTP Host <span class="req">*</span></label>
          <input type="text" class="st-ctrl" id="smtp_host"
                 value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"
                 placeholder="smtp.gmail.com">
          <div class="st-hint">Contoh: smtp.gmail.com &bull; smtp.mail.yahoo.com &bull; smtp.office365.com</div>
        </div>
        <div class="st-group" style="flex:1;min-width:90px">
          <label class="st-label">Port <span class="req">*</span></label>
          <input type="number" class="st-ctrl" id="smtp_port"
                 value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>"
                 placeholder="587">
        </div>
        <div class="st-group" style="flex:1;min-width:100px">
          <label class="st-label">Enkripsi <span class="req">*</span></label>
          <select class="st-ctrl" id="smtp_encryption">
            <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
            <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
            <option value=""   <?= ($settings['smtp_encryption'] ?? '') === ''    ? 'selected' : '' ?>>None</option>
          </select>
        </div>
      </div>

      <hr class="st-divider">

      <!-- Row 2: Username, Password -->
      <div class="st-row">
        <div class="st-group">
          <label class="st-label">Username / Email SMTP <span class="req">*</span></label>
          <input type="email" class="st-ctrl" id="smtp_username"
                 value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"
                 placeholder="akunmu@gmail.com" autocomplete="off">
        </div>
        <div class="st-group">
          <label class="st-label">Password SMTP</label>
          <div class="st-input-group">
            <input type="password" class="st-ctrl" id="smtp_password"
                   placeholder="Kosongkan jika tidak ingin mengubah" autocomplete="new-password">
            <div class="st-input-group-addon">
              <button type="button" id="btn-toggle-pass" title="Tampilkan/sembunyikan" aria-label="Toggle password">
                <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
          </div>
          <div class="st-hint">Untuk Gmail: gunakan <strong>App Password</strong>, bukan password akun biasa.</div>
        </div>
      </div>

      <!-- Row 3: From Email, From Name -->
      <div class="st-row">
        <div class="st-group">
          <label class="st-label">From Email <span class="req">*</span></label>
          <input type="email" class="st-ctrl" id="smtp_from_email"
                 value="<?= htmlspecialchars($settings['smtp_from_email'] ?? '') ?>"
                 placeholder="noreply@domain.com">
        </div>
        <div class="st-group">
          <label class="st-label">From Name <span class="req">*</span></label>
          <input type="text" class="st-ctrl" id="smtp_from_name"
                 value="<?= htmlspecialchars($settings['smtp_from_name'] ?? APP_NAME) ?>"
                 placeholder="<?= APP_NAME ?>">
        </div>
      </div>

      <hr class="st-divider">

      <!-- Actions -->
      <div style="display:flex;gap:.65rem;align-items:flex-end;flex-wrap:wrap">
        <button class="st-btn st-btn-primary" id="btn-save-smtp">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Simpan SMTP
        </button>
        <div class="st-input-group" style="max-width:360px">
          <input type="email" class="st-ctrl" id="smtp_test_email" placeholder="Email tujuan test" style="min-width:180px">
          <div class="st-input-group-addon" style="cursor:default">
            <button type="button" class="st-btn st-btn-outline" id="btn-test-smtp"
                    style="border:none;border-radius:0;height:40px;padding:0 .85rem;background:transparent">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg>
              Kirim Test
            </button>
          </div>
        </div>
      </div>
      <div id="smtp-msg" class="st-msg" style="margin-top:.65rem"></div>

      <!-- Quick Guide -->
      <div class="st-infobox">
        <div class="st-infobox-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          Panduan Konfigurasi Cepat
        </div>
        <div class="st-guide-grid">
          <div class="st-guide-item">
            <strong>Gmail</strong>
            Host: <code>smtp.gmail.com</code><br>
            Port: <code>587</code> &bull; Enkripsi: <code>TLS</code><br>
            Aktifkan 2FA lalu buat App Password
          </div>
          <div class="st-guide-item">
            <strong>Yahoo Mail</strong>
            Host: <code>smtp.mail.yahoo.com</code><br>
            Port: <code>465</code> &bull; Enkripsi: <code>SSL</code><br>
            Aktifkan App Password di keamanan akun
          </div>
          <div class="st-guide-item">
            <strong>Office 365</strong>
            Host: <code>smtp.office365.com</code><br>
            Port: <code>587</code> &bull; Enkripsi: <code>TLS</code><br>
            Gunakan email &amp; password akun O365
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const BASE = '<?= $apiBase ?>';

  /* ── Tab switching ── */
  document.querySelectorAll('.st-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.st-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.st-panel').forEach(p => p.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('panel-' + btn.dataset.tab).classList.add('active');
    });
  });

  /* ── Upload helper ── */
  function handleUpload(inputId, previewWrapId, uploadBtnId, uploadUrl, msgId, previewStyle) {
    const input     = document.getElementById(inputId);
    const uploadBtn = document.getElementById(uploadBtnId);
    const msg       = document.getElementById(msgId);
    if (!input) return;

    input.addEventListener('change', () => {
      const file = input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = e => {
        const wrap = document.getElementById(previewWrapId);
        wrap.innerHTML = `<img src="${e.target.result}" style="${previewStyle}">`;
      };
      reader.readAsDataURL(file);
      uploadBtn.disabled = false;
    });

    uploadBtn.addEventListener('click', async () => {
      const file = input.files[0];
      if (!file) return;
      uploadBtn.disabled = true;
      uploadBtn.textContent = 'Mengupload…';
      const fd = new FormData();
      fd.append(inputId === 'input-logo' ? 'logo' : 'login_bg', file);
      try {
        const res  = await fetch(uploadUrl, { method: 'POST', body: fd });
        const data = await res.json();
        if (data.success) {
          msg.innerHTML = '<span class="st-msg-ok"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Berhasil diupload. Halaman akan dimuat ulang…</span>';
          setTimeout(() => location.reload(), 1500);
        } else {
          msg.innerHTML = `<span class="st-msg-err">✕ ${data.message}</span>`;
          uploadBtn.disabled = false; uploadBtn.textContent = 'Upload';
        }
      } catch (e) {
        msg.innerHTML = '<span class="st-msg-err">✕ Terjadi kesalahan jaringan.</span>';
        uploadBtn.disabled = false; uploadBtn.textContent = 'Upload';
      }
    });
  }

  handleUpload('input-logo', 'logo-preview-wrap', 'btn-upload-logo',
    `${BASE}/api/settings/upload-logo`, 'logo-msg',
    'max-height:90px;max-width:100%;object-fit:contain;padding:.5rem');

  handleUpload('input-bg', 'bg-preview-wrap', 'btn-upload-bg',
    `${BASE}/api/settings/upload-login-bg`, 'bg-msg',
    'width:100%;height:100px;object-fit:cover');

  document.getElementById('btn-remove-logo')?.addEventListener('click', async () => {
    if (!confirm('Hapus logo?')) return;
    const d = await (await fetch(`${BASE}/api/settings/remove-logo`, { method: 'POST' })).json();
    if (d.success) location.reload();
  });
  document.getElementById('btn-remove-bg')?.addEventListener('click', async () => {
    if (!confirm('Hapus background login?')) return;
    const d = await (await fetch(`${BASE}/api/settings/remove-login-bg`, { method: 'POST' })).json();
    if (d.success) location.reload();
  });

  /* ── Toggle password ── */
  document.getElementById('btn-toggle-pass')?.addEventListener('click', () => {
    const inp = document.getElementById('smtp_password');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    document.getElementById('eye-icon').innerHTML = inp.type === 'text'
      ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>'
      : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
  });

  /* ── Save SMTP ── */
  const smtpMsg = document.getElementById('smtp-msg');
  function setMsg(html, ok) {
    const icon = ok
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>';
    smtpMsg.innerHTML = `<span class="${ok ? 'st-msg-ok' : 'st-msg-err'}">${icon} ${html}</span>`;
  }

  document.getElementById('btn-save-smtp').addEventListener('click', async function() {
    this.disabled = true; this.textContent = 'Menyimpan…';
    const fd = new FormData();
    ['smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_password','smtp_from_email','smtp_from_name']
      .forEach(id => fd.append(id, document.getElementById(id).value));
    try {
      const data = await (await fetch(`${BASE}/api/settings/save-smtp`, { method:'POST', body:fd })).json();
      setMsg(data.message, data.success);
    } catch(e) { setMsg('Gagal terhubung ke server.', false); }
    finally {
      this.disabled = false;
      this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg> Simpan SMTP`;
    }
  });

  /* ── Test SMTP ── */
  document.getElementById('btn-test-smtp').addEventListener('click', async function() {
    const to = document.getElementById('smtp_test_email').value.trim();
    if (!to) { setMsg('Masukkan email tujuan test.', false); return; }
    this.disabled = true; this.textContent = 'Mengirim…';
    const fd = new FormData(); fd.append('test_email', to);
    try {
      const data = await (await fetch(`${BASE}/api/settings/test-smtp`, { method:'POST', body:fd })).json();
      setMsg(data.message, data.success);
    } catch(e) { setMsg('Gagal terhubung ke server.', false); }
    finally {
      this.disabled = false;
      this.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 2 15 22 11 13 2 9 22 2"/></svg> Kirim Test`;
    }
  });
})();
</script>
