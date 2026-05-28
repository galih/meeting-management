<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusColor = match($meeting['status']) {
    'scheduled' => 'blue',
    'ongoing'   => 'orange',
    'done'      => 'green',
    'cancelled' => 'red',
    default     => 'secondary',
};
?>

<!-- Flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- Page Header -->
<div class="page-header d-print-none mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <a href="<?= $baseUrl ?>/meetings" class="btn btn-sm btn-outline-secondary">&larr; Kembali</a>
    <h2 class="page-title mb-0 me-2"><?= htmlspecialchars($meeting['title']) ?></h2>
    <span class="badge bg-<?= $statusColor ?>"><?= ucfirst($meeting['status']) ?></span>
  </div>
</div>

<div class="row row-cards">

  <!-- Kolom Kiri: Info + Peserta -->
  <div class="col-lg-4">

    <!-- Info Kegiatan -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Detail Kegiatan</h3>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <div class="card-options">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalEditStatus">Ubah Status</button>
        </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <dl class="row mb-0 small">
          <dt class="col-5 text-muted">Departemen</dt>
          <dd class="col-7"><?= htmlspecialchars($meeting['dept_name'] ?? '-') ?></dd>
          <dt class="col-5 text-muted">Lokasi</dt>
          <dd class="col-7">
            <?php if (!empty($meeting['location']) && str_starts_with($meeting['location'], 'http')): ?>
              <a href="<?= htmlspecialchars($meeting['location']) ?>" target="_blank" rel="noopener">🔗 Link Kegiatan</a>
            <?php else: ?>
              <?= htmlspecialchars($meeting['location'] ?? '-') ?>
            <?php endif; ?>
          </dd>
          <dt class="col-5 text-muted">Mulai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['start_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Selesai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['end_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Dibuat oleh</dt>
          <dd class="col-7"><?= htmlspecialchars($meeting['creator_name'] ?? '-') ?></dd>
        </dl>
        <?php if (!empty($meeting['description'])): ?>
        <hr class="my-2">
        <div class="text-muted small fw-semibold mb-1">Agenda</div>
        <p class="mb-0 small"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
        <?php endif; ?>
      </div>
      <!-- Action Buttons -->
      <div class="card-footer">
        <div class="btn-list">
          <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>" class="btn btn-primary w-100">
            📝 Buka Notulen
          </a>
          <a href="<?= $baseUrl ?>/notulen/<?= $meeting['id'] ?>/export-pdf"
             target="_blank" class="btn btn-outline-danger w-100">
            🖨️ Export PDF
          </a>
          <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
          <button class="btn btn-outline-primary w-100" id="btn-send-invitation">
            📧 Kirim Undangan
          </button>
          <?php if ($meeting['status'] === 'done'): ?>
          <button class="btn btn-outline-success w-100" id="btn-send-summary">
            📋 Kirim Ringkasan
          </button>
          <?php endif; ?>
          <?php endif; ?>

          <?php if (Auth::hasRole('admin')): ?>
          <div class="w-100"><hr class="my-2"></div>
          <form method="POST" class="w-100"
                action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/delete"
                onsubmit="return confirm('Yakin ingin menghapus kegiatan &quot;<?= htmlspecialchars(addslashes($meeting['title'])) ?>&quot;?\n\nSemua notulen, peserta, dan tindak lanjut terkait akan ikut terhapus.')">
            <?= Auth::csrfField() ?>
            <button type="submit" class="btn btn-danger w-100">🗑️ Hapus Kegiatan</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Peserta -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Peserta <span class="badge bg-blue-lt text-blue ms-1"><?= count($participants) ?></span></h3>
      </div>
      <div class="list-group list-group-flush" style="max-height:360px;overflow-y:auto;">
        <?php if (empty($participants)): ?>
        <div class="list-group-item text-muted text-center py-3 small">Belum ada peserta</div>
        <?php endif; ?>
        <?php foreach ($participants as $p): ?>
        <div class="list-group-item px-3 py-2">
          <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm" style="background:var(--tblr-primary);color:#fff;font-size:.7rem;font-weight:700;flex-shrink:0;">
              <?= strtoupper(mb_substr($p['name'], 0, 1)) ?>
            </span>
            <div class="flex-fill" style="min-width:0;">
              <div class="fw-semibold text-truncate" style="font-size:13px;"><?= htmlspecialchars($p['name']) ?></div>
              <?php if (!empty($p['email'])): ?>
              <div class="text-muted text-truncate" style="font-size:11px;"><?= htmlspecialchars($p['email']) ?></div>
              <?php endif; ?>
            </div>
            <span class="badge bg-<?= match($p['status']) {
              'accepted' => 'green',
              'invited'  => 'blue',
              'declined' => 'red',
              'attended' => 'teal',
              'pending'  => 'secondary',
              default    => 'secondary',
            } ?>-lt flex-shrink-0" style="font-size:10px;"><?= ucfirst($p['status']) ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <!-- Kolom Kanan: Tindak Lanjut -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Tindak Lanjut</h3>
        <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
        <div class="card-options">
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                  data-bs-target="#modalAddTL">+ Tambah</button>
        </div>
        <?php endif; ?>
      </div>
      <?php if (empty($tindakLanjutList)): ?>
      <div class="card-body text-center text-muted py-5">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg text-muted mb-2" width="40" height="40"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <polyline points="9 11 12 14 22 4"/>
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        </svg>
        <p class="mb-0 text-muted">Belum ada tindak lanjut untuk kegiatan ini</p>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-vcenter table-hover card-table">
          <thead>
            <tr>
              <th>Deskripsi</th>
              <th class="text-nowrap">PIC</th>
              <th class="text-nowrap">Deadline</th>
              <th class="text-nowrap">Prioritas</th>
              <th class="text-nowrap">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $overdue = !empty($tl['due_date'])
                && $tl['due_date'] < date('Y-m-d')
                && !in_array($tl['status'], ['done', 'cancelled']);
              $priorityColor = match($tl['priority']) {
                'high'   => 'red',
                'medium' => 'yellow',
                'low'    => 'green',
                default  => 'secondary',
              };
              $statusColor = match($tl['status']) {
                'pending'     => 'secondary',
                'in_progress' => 'blue',
                'done'        => 'green',
                'cancelled'   => 'red',
                default       => 'secondary',
              };
            ?>
            <tr<?= $overdue ? ' class="table-danger"' : '' ?>>
              <td style="max-width:260px;">
                <div class="text-truncate" style="max-width:240px;" title="<?= htmlspecialchars($tl['description']) ?>">
                  <?= htmlspecialchars($tl['description']) ?>
                </div>
                <?php if ($overdue): ?>
                <span class="badge bg-red-lt text-red" style="font-size:9px;">Terlambat</span>
                <?php endif; ?>
              </td>
              <td class="text-nowrap text-muted"><?= htmlspecialchars($tl['assigned_name'] ?? '-') ?></td>
              <td class="text-nowrap text-muted">
                <?php if (!empty($tl['due_date'])): ?>
                  <span class="<?= $overdue ? 'text-danger fw-semibold' : '' ?>">
                    <?= date('d M Y', strtotime($tl['due_date'])) ?>
                  </span>
                <?php else: ?>
                  -
                <?php endif; ?>
              </td>
              <td class="text-nowrap">
                <span class="badge bg-<?= $priorityColor ?>-lt">
                  <?= ucfirst($tl['priority']) ?>
                </span>
              </td>
              <td class="text-nowrap">
                <span class="badge bg-<?= $statusColor ?>">
                  <?= ucfirst(str_replace('_', ' ', $tl['status'])) ?>
                </span>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Modal Ubah Status -->
<?php if (Auth::hasRole('admin', 'sekretaris')): ?>
<div class="modal modal-blur fade" id="modalEditStatus" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings/<?= $meeting['id'] ?>/status">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Kegiatan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <select name="status" class="form-select">
            <?php foreach (['scheduled' => 'Scheduled', 'ongoing' => 'Ongoing', 'done' => 'Done', 'cancelled' => 'Cancelled'] as $val => $label): ?>
            <option value="<?= $val ?>" <?= $meeting['status'] === $val ? 'selected' : '' ?>>
              <?= $label ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah Tindak Lanjut -->
<div class="modal modal-blur fade" id="modalAddTL" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Tindak Lanjut</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label required">Deskripsi Tugas</label>
          <textarea id="tl-deskripsi" class="form-control" rows="3" required></textarea>
        </div>
        <div class="row g-2">
          <div class="col-6">
            <label class="form-label">Ditugaskan ke</label>
            <select id="tl-assigned" class="form-select">
              <option value="">-- Pilih User --</option>
              <?php foreach ($participants as $p): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <label class="form-label">Deadline</label>
            <input type="date" id="tl-deadline" class="form-control" min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Prioritas</label>
          <div class="d-flex gap-3">
            <?php foreach (['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi'] as $v => $l): ?>
            <label class="form-check">
              <input type="radio" name="tl-priority" class="form-check-input"
                     value="<?= $v ?>" <?= $v === 'medium' ? 'checked' : '' ?>>
              <span class="form-check-label"><?= $l ?></span>
            </label>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-save-tl">Simpan</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php
$tlUrl      = json_encode($baseUrl . '/tindak-lanjut');
$inviteUrl  = json_encode($baseUrl . '/meetings/' . $meeting['id'] . '/send-invitations');
$summaryUrl = json_encode($baseUrl . '/meetings/' . $meeting['id'] . '/send-summary');
$meetingId  = (int)$meeting['id'];
?>
<script>
const MID = <?= $meetingId ?>;

document.getElementById('btn-save-tl')?.addEventListener('click', async () => {
  const deskripsi = document.getElementById('tl-deskripsi').value.trim();
  if (!deskripsi) { alert('Deskripsi wajib diisi!'); return; }
  const priority = document.querySelector('input[name="tl-priority"]:checked')?.value || 'medium';
  const res = await fetch(<?= $tlUrl ?>, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      meeting_id:  MID,
      description: deskripsi,
      assigned_to: document.getElementById('tl-assigned').value,
      due_date:    document.getElementById('tl-deadline').value,
      priority
    })
  });
  const d = await res.json();
  if (d.success) {
    bootstrap.Modal.getInstance(document.getElementById('modalAddTL')).hide();
    location.reload();
  } else {
    alert(d.message || 'Gagal menyimpan');
  }
});

document.getElementById('btn-send-invitation')?.addEventListener('click', async () => {
  if (!confirm('Kirim undangan email ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-invitation');
  btn.disabled = true; btn.textContent = '⏳ Mengirim...';
  const res  = await fetch(<?= $inviteUrl ?>, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false; btn.innerHTML = '📧 Kirim Undangan';
  alert(data.message || (data.success ? 'Undangan terkirim!' : 'Gagal mengirim.'));
});

document.getElementById('btn-send-summary')?.addEventListener('click', async () => {
  if (!confirm('Kirim ringkasan notulen ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-summary');
  btn.disabled = true; btn.textContent = '⏳ Mengirim...';
  const res  = await fetch(<?= $summaryUrl ?>, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false; btn.innerHTML = '📋 Kirim Ringkasan';
  alert(data.message || (data.success ? 'Ringkasan terkirim!' : 'Gagal mengirim.'));
});
</script>
