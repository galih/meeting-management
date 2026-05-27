<!-- Detail Meeting -->
<div class="row row-cards">

  <!-- Kolom Kiri: Info + Peserta -->
  <div class="col-lg-4">

    <!-- Info Meeting -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Detail Meeting</h3>
        <?php if (Auth::can('admin','sekretaris')): ?>
        <div class="card-options gap-1">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                  data-bs-target="#modalEditStatus">Ubah Status</button>
        </div>
        <?php endif; ?>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-5 text-muted">Status</dt>
          <dd class="col-7">
            <span class="badge bg-<?= match($meeting['status']) {
              'scheduled'=>'blue','ongoing'=>'orange',
              'completed'=>'green','cancelled'=>'red',default=>'secondary'
            } ?>"><?= ucfirst($meeting['status']) ?></span>
          </dd>
          <dt class="col-5 text-muted">Lokasi</dt>
          <dd class="col-7"><?= htmlspecialchars($meeting['location'] ?? '-') ?></dd>
          <dt class="col-5 text-muted">Mulai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['start_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Selesai</dt>
          <dd class="col-7"><?= date('d M Y H:i', strtotime($meeting['end_datetime'])) ?></dd>
          <dt class="col-5 text-muted">Dibuat oleh</dt>
          <dd class="col-7"><?= htmlspecialchars($meeting['creator_name']) ?></dd>
        </dl>
        <?php if ($meeting['description']): ?>
        <hr>
        <div class="text-muted small fw-semibold mb-1">Agenda</div>
        <p class="mb-0 small"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
        <?php endif; ?>
      </div>
      <!-- Action Buttons -->
      <div class="card-footer d-grid gap-2">
        <a href="/notulen/<?= $meeting['id'] ?>" class="btn btn-primary">
          📝 Buka Notulen
        </a>
        <a href="/notulen/<?= $meeting['id'] ?>/export-pdf"
           target="_blank" class="btn btn-outline-danger">
          🖨️ Export PDF
        </a>
        <?php if (Auth::can('admin','sekretaris')): ?>
        <button class="btn btn-outline-primary" id="btn-send-invitation">
          📧 Kirim Undangan
        </button>
        <?php if ($meeting['status'] === 'completed'): ?>
        <button class="btn btn-outline-success" id="btn-send-summary">
          📋 Kirim Ringkasan
        </button>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Peserta -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Peserta (<?= count($participants) ?>)</h3>
      </div>
      <div class="list-group list-group-flush">
        <?php if (empty($participants)): ?>
        <div class="list-group-item text-muted text-center py-3">Belum ada peserta</div>
        <?php endif; ?>
        <?php foreach ($participants as $p): ?>
        <div class="list-group-item">
          <div class="d-flex align-items-center gap-2">
            <span class="avatar avatar-sm"
                  style="background:#f76707;color:white;font-size:12px;font-weight:700;">
              <?= strtoupper(mb_substr($p['name'],0,1)) ?>
            </span>
            <div class="flex-fill">
              <div class="fw-semibold" style="font-size:13px;"><?= htmlspecialchars($p['name']) ?></div>
            </div>
            <span class="badge bg-<?= match($p['status']) {
              'accepted'=>'green','invited'=>'blue','declined'=>'red','attended'=>'teal',default=>'secondary'
            } ?>-lt" style="font-size:10px;"><?= ucfirst($p['status']) ?></span>
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
        <?php if (Auth::can('admin','sekretaris')): ?>
        <div class="card-options">
          <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                  data-bs-target="#modalAddTL">+ Tambah</button>
        </div>
        <?php endif; ?>
      </div>
      <?php if (empty($tindakLanjutList)): ?>
      <div class="card-body text-center text-muted py-5">
        <p class="mb-0">Belum ada tindak lanjut untuk meeting ini</p>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr><th>Deskripsi</th><th>PIC</th><th>Deadline</th><th>Prioritas</th><th>Status</th></tr>
          </thead>
          <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $overdue = $tl['deadline'] && $tl['deadline'] < date('Y-m-d')
                         && !in_array($tl['status'],['done','cancelled']);
            ?>
            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
              <td><?= htmlspecialchars($tl['deskripsi']) ?>
                <?php if ($overdue): ?><span class="badge bg-red ms-1 small">Terlambat</span><?php endif; ?>
              </td>
              <td><?= htmlspecialchars($tl['assigned_name'] ?? '-') ?></td>
              <td class="text-muted"><?= $tl['deadline'] ? date('d M Y', strtotime($tl['deadline'])) : '-' ?></td>
              <td><span class="badge bg-<?= match($tl['priority']) {
                'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
              } ?>-lt"><?= ucfirst($tl['priority']) ?></span></td>
              <td><span class="badge bg-<?= match($tl['status']) {
                'pending'=>'secondary','in_progress'=>'blue',
                'done'=>'green','cancelled'=>'red',default=>'secondary'
              } ?>"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></span></td>
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
<?php if (Auth::can('admin','sekretaris')): ?>
<div class="modal modal-blur fade" id="modalEditStatus" tabindex="-1">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="/meetings/<?= $meeting['id'] ?>/status">
        <div class="modal-header">
          <h5 class="modal-title">Ubah Status Meeting</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <select name="status" class="form-select">
            <?php foreach (['scheduled','ongoing','completed','cancelled'] as $s): ?>
            <option value="<?= $s ?>" <?= $meeting['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
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
            <?php foreach (['low'=>'Rendah','medium'=>'Sedang','high'=>'Tinggi'] as $v=>$l): ?>
            <label class="form-check">
              <input type="radio" name="tl-priority" class="form-check-input"
                     value="<?= $v ?>" <?= $v==='medium'?'checked':'' ?>>
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

<?php $scripts = <<<JS
<script>
const MID = {$meeting['id']};

// Tambah Tindak Lanjut
document.getElementById('btn-save-tl')?.addEventListener('click', async () => {
  const deskripsi = document.getElementById('tl-deskripsi').value.trim();
  if (!deskripsi) { alert('Deskripsi wajib diisi!'); return; }
  const priority  = document.querySelector('input[name="tl-priority"]:checked')?.value || 'medium';
  const res = await fetch('/tindak-lanjut', {
    method: 'POST', headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      meeting_id: MID, deskripsi,
      assigned_to: document.getElementById('tl-assigned').value,
      deadline: document.getElementById('tl-deadline').value,
      priority
    })
  });
  const d = await res.json();
  if (d.success) { bootstrap.Modal.getInstance(document.getElementById('modalAddTL')).hide(); location.reload(); }
  else alert(d.message || 'Gagal menyimpan');
});

// Kirim Undangan
document.getElementById('btn-send-invitation')?.addEventListener('click', async () => {
  if (!confirm('Kirim undangan email ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-invitation');
  btn.disabled = true; btn.textContent = '⏳ Mengirim...';
  const res  = await fetch(`/meetings/{$meeting['id']}/send-invitations`, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false; btn.textContent = '📧 Kirim Undangan';
  alert(data.message || (data.success ? 'Terkirim!' : 'Gagal.'));
});

// Kirim Ringkasan
document.getElementById('btn-send-summary')?.addEventListener('click', async () => {
  if (!confirm('Kirim ringkasan notulen ke semua peserta?')) return;
  const btn = document.getElementById('btn-send-summary');
  btn.disabled = true; btn.textContent = '⏳ Mengirim...';
  const res  = await fetch(`/meetings/{$meeting['id']}/send-summary`, { method: 'POST' });
  const data = await res.json();
  btn.disabled = false; btn.textContent = '📋 Kirim Ringkasan';
  alert(data.message || (data.success ? 'Terkirim!' : 'Gagal.'));
});
</script>
JS; ?>
