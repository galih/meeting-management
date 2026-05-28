<?php $u = $profileUser; ?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Profil Saya</h3>
      </div>
      <div class="card-body">
        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['flash_error']) ?><?php unset($_SESSION['flash_error']); ?></div>
        <?php endif; ?>
        <form method="POST" action="<?= BASE_URL ?>/profile">
          <div class="mb-3">
            <label class="form-label required">Nama</label>
            <input type="text" name="name" class="form-control" required
                   value="<?= htmlspecialchars($u['name'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label required">Email</label>
            <input type="email" name="email" class="form-control" required
                   value="<?= htmlspecialchars($u['email'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Password Baru <span class="text-muted small">(kosongkan jika tidak ingin mengubah)</span></label>
            <input type="password" name="password" class="form-control" autocomplete="new-password" minlength="6">
          </div>
          <div class="mb-3">
            <label class="form-label">Departemen</label>
            <input type="text" class="form-control" disabled
                   value="<?= htmlspecialchars($u['dept_name'] ?? '-') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label">Role</label>
            <input type="text" class="form-control" disabled
                   value="<?= ucfirst($u['role'] ?? '') ?>">
          </div>
          <button type="submit" class="btn btn-primary w-100">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>
</div>
