<?php $baseUrl = rtrim(BASE_URL, '/'); ?>

<div class="page-header mb-4">
  <div class="container-xl">
    <div class="row align-items-center">
      <div class="col">
        <h2 class="page-title">Profil Saya</h2>
        <div class="text-muted"><?= ucfirst(htmlspecialchars($user['role'] ?? '')) ?></div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- Kartu Info Profil -->
  <div class="col-12 col-md-4">
    <div class="card text-center">
      <div class="card-body py-4">
        <span class="avatar avatar-xl mb-3"
              style="background:var(--brand,#7B1C1C);color:#fff;font-size:32px;font-weight:700;">
          <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
        </span>
        <h3 class="mb-1"><?= htmlspecialchars($user['name'] ?? '') ?></h3>
        <div class="text-muted small mb-1"><?= htmlspecialchars($user['email'] ?? '') ?></div>
        <span class="badge bg-blue-lt"><?= ucfirst(htmlspecialchars($user['role'] ?? '')) ?></span>
      </div>
    </div>
  </div>

  <div class="col-12 col-md-8">

    <!-- Form Edit Profil -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
          Edit Profil
        </h3>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= $baseUrl ?>/profile/update">
          <div class="mb-3">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="name" class="form-control"
                   value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
          </div>
          <div class="mb-0">
            <button type="submit" class="btn btn-primary">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                   viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
              </svg>
              Simpan Perubahan
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Form Ubah Password -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="18" height="18"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          Ubah Password
        </h3>
      </div>
      <div class="card-body">
        <form method="POST" action="<?= $baseUrl ?>/profile/change-password">
          <div class="mb-3">
            <label class="form-label required">Password Saat Ini</label>
            <div class="input-group">
              <input type="password" name="current_password" id="cur_pw"
                     class="form-control" placeholder="Masukkan password saat ini" required>
              <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="cur_pw">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label required">Password Baru</label>
            <div class="input-group">
              <input type="password" name="new_password" id="new_pw"
                     class="form-control" placeholder="Minimal 6 karakter" required minlength="6">
              <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="new_pw">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label required">Konfirmasi Password Baru</label>
            <div class="input-group">
              <input type="password" name="confirm_password" id="conf_pw"
                     class="form-control" placeholder="Ulangi password baru" required>
              <button type="button" class="btn btn-outline-secondary toggle-pw" data-target="conf_pw">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
            <div id="pw-match-msg" class="form-hint mt-1"></div>
          </div>
          <button type="submit" class="btn btn-warning">
            <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="16" height="16"
                 viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Ubah Password
          </button>
        </form>
      </div>
    </div>

  </div>
</div>

<script>
// Toggle show/hide password
document.querySelectorAll('.toggle-pw').forEach(btn => {
  btn.addEventListener('click', () => {
    const inp = document.getElementById(btn.dataset.target);
    inp.type = inp.type === 'password' ? 'text' : 'password';
  });
});

// Real-time cek konfirmasi password
const newPw  = document.getElementById('new_pw');
const confPw = document.getElementById('conf_pw');
const msg    = document.getElementById('pw-match-msg');
function checkMatch() {
  if (!confPw.value) { msg.textContent = ''; return; }
  if (newPw.value === confPw.value) {
    msg.textContent = '✓ Password cocok';
    msg.className   = 'form-hint text-success mt-1';
  } else {
    msg.textContent = '✗ Password tidak cocok';
    msg.className   = 'form-hint text-danger mt-1';
  }
}
newPw.addEventListener('input', checkMatch);
confPw.addEventListener('input', checkMatch);
</script>
