<?php
$baseUrl = rtrim(BASE_URL, '/');
$storeUrl = $baseUrl . '/recurring';
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <p class="text-muted mb-0 small">Meeting terjadwal otomatis berulang — generate 4 minggu ke depan</p>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-secondary btn-sm" id="btn-generate-all">
      ⚡ Generate Semua
    </button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRecurring">
      + Recurring Baru
    </button>
  </div>
</div>

<?php if (empty($list)): ?>
<div class="card">
  <div class="card-body text-center text-muted py-5">
    <p class="mb-2">Belum ada recurring meeting.</p>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddRecurring">Buat Pertama</button>
  </div>
</div>
<?php else: ?>
<div class="row row-cards">
  <?php foreach ($list as $r):
    $freqLabel = ['daily'=>'Harian','weekly'=>'Mingguan','biweekly'=>'2 Mingguan','monthly'=>'Bulanan'];
    $days      = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    $dayLabel  = $r['day_of_week'] !== null
      ? $days[$r['day_of_week']]
      : ($r['day_of_month'] ? 'Tgl ' . $r['day_of_month'] : '-');
  ?>
  <div class="col-md-6 col-lg-4">
    <div class="card">
      <div class="card-status-top" style="background:<?= htmlspecialchars($r['color']) ?>;"></div>
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <h4 class="card-title mb-0"><?= htmlspecialchars($r['title']) ?></h4>
          <span class="badge bg-orange-lt text-orange"><?= $freqLabel[$r['frequency']] ?? $r['frequency'] ?></span>
        </div>
        <div class="text-muted small mb-2">
          📍 <?= htmlspecialchars($r['location'] ?? '-') ?><br>
          🕐 <?= substr($r['start_time'], 0, 5) ?> – <?= substr($r['end_time'], 0, 5) ?>
          &nbsp;|&nbsp; 📅 <?= $dayLabel ?><br>
          👤 <?= htmlspecialchars($r['creator_name']) ?>
          <?php if (!empty($r['dept_name'])): ?>&nbsp;|&nbsp; 🏢 <?= htmlspecialchars($r['dept_name']) ?><?php endif; ?>
        </div>
        <div class="d-flex justify-content-between text-muted" style="font-size:11px;">
          <span>Generated: <strong><?= (int)$r['total_generated'] ?> meeting</strong></span>
          <?php if (!empty($r['end_date'])): ?>
          <span>Berakhir: <?= date('d M Y', strtotime($r['end_date'])) ?></span>
          <?php else: ?>
          <span class="text-green">♾️ Tanpa batas</span>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-footer d-flex gap-2">
        <button class="btn btn-sm btn-outline-primary flex-fill btn-generate"
                data-id="<?= $r['id'] ?>"
                data-url="<?= $baseUrl ?>/recurring/<?= $r['id'] ?>/generate">
          ⚡ Generate
        </button>
        <button class="btn btn-sm btn-outline-danger btn-del-recurring"
                data-id="<?= $r['id'] ?>"
                data-url="<?= $baseUrl ?>/recurring/<?= $r['id'] ?>/delete">
          Hapus
        </button>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal Tambah -->
<div class="modal modal-blur fade" id="modalAddRecurring" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $storeUrl ?>">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Buat Recurring Meeting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Judul Meeting</label>
              <input type="text" name="title" class="form-control" required placeholder="Contoh: Standup Harian IT">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Frekuensi</label>
              <select name="frequency" class="form-select" id="rec-frequency" required>
                <option value="daily">Harian</option>
                <option value="weekly" selected>Mingguan</option>
                <option value="biweekly">2 Mingguan</option>
                <option value="monthly">Bulanan</option>
              </select>
            </div>
            <div class="col-md-6" id="row-day-of-week">
              <label class="form-label">Hari</label>
              <select name="day_of_week" class="form-select">
                <option value="1">Senin</option><option value="2">Selasa</option>
                <option value="3">Rabu</option><option value="4">Kamis</option>
                <option value="5">Jumat</option><option value="6">Sabtu</option>
                <option value="0">Minggu</option>
              </select>
            </div>
            <div class="col-md-6" id="row-day-of-month" style="display:none;">
              <label class="form-label">Tanggal (1-28)</label>
              <input type="number" name="day_of_month" class="form-control" min="1" max="28" value="1">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Jam Mulai</label>
              <input type="time" name="start_time" class="form-control" required value="09:00">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Jam Selesai</label>
              <input type="time" name="end_time" class="form-control" required value="10:00">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Tanggal Mulai</label>
              <input type="date" name="start_date" class="form-control" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Tanggal Berakhir <small class="text-muted">(kosongkan = tanpa batas)</small></label>
              <input type="date" name="end_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Lokasi</label>
              <input type="text" name="location" class="form-control" placeholder="Ruang Rapat / Online">
            </div>
            <div class="col-md-3">
              <label class="form-label">Warna</label>
              <input type="color" name="color" class="form-control form-control-color" value="#f76707">
            </div>
            <div class="col-md-3">
              <label class="form-label">Departemen</label>
              <select name="department_id" class="form-select">
                <option value="">-- Semua --</option>
                <?php foreach ($departments as $dept): ?>
                <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">Peserta Default</label>
              <div class="row g-1" style="max-height:150px;overflow-y:auto;">
                <?php foreach ($users as $u): ?>
                <div class="col-md-4">
                  <label class="form-check">
                    <input type="checkbox" name="participants[]" class="form-check-input" value="<?= $u['id'] ?>">
                    <span class="form-check-label small"><?= htmlspecialchars($u['name']) ?></span>
                  </label>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <div class="col-12">
              <label class="form-label">Agenda / Deskripsi</label>
              <textarea name="description" class="form-control" rows="2"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Buat &amp; Generate Meeting Pertama</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('rec-frequency')?.addEventListener('change', function() {
  const isMonthly = this.value === 'monthly';
  const isDaily   = this.value === 'daily';
  document.getElementById('row-day-of-week').style.display  = (!isMonthly && !isDaily) ? '' : 'none';
  document.getElementById('row-day-of-month').style.display = isMonthly ? '' : 'none';
});

document.querySelectorAll('.btn-generate').forEach(btn => {
  btn.addEventListener('click', async () => {
    btn.disabled = true; btn.textContent = '⏳ Proses...';
    const res  = await fetch(btn.dataset.url, { method: 'POST' });
    const data = await res.json();
    btn.disabled = false; btn.textContent = '⚡ Generate';
    alert(data.message);
    if (data.count > 0) location.reload();
  });
});

document.getElementById('btn-generate-all')?.addEventListener('click', async () => {
  if (!confirm('Generate semua recurring meeting 4 minggu ke depan?')) return;
  const btn = document.getElementById('btn-generate-all');
  btn.disabled = true; btn.textContent = '⏳ Proses...';
  const res  = await fetch('<?= $baseUrl ?>/api/recurring/generate-all', { method: 'POST' });
  const data = await res.json();
  btn.disabled = false; btn.textContent = '⚡ Generate Semua';
  alert(`Total ${data.total_generated ?? 0} meeting berhasil digenerate.`);
  if ((data.total_generated ?? 0) > 0) location.reload();
});

document.querySelectorAll('.btn-del-recurring').forEach(btn => {
  btn.addEventListener('click', async () => {
    if (!confirm('Hapus recurring meeting ini? Meeting yang sudah digenerate tidak ikut terhapus.')) return;
    const res = await fetch(btn.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) btn.closest('.col-md-6, .col-lg-4').remove();
    else alert(d.message || 'Gagal hapus');
  });
});
</script>
