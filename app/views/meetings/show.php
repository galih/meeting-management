<!-- Detail Meeting -->
<div class="row row-cards">

  <!-- Kolom Kiri: Info + Peserta -->
  <div class="col-lg-4">

    <!-- Info Meeting -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Detail Meeting</h3>
        <?php if (Auth::can('admin','sekretaris')): ?>
        <div class="card-options">
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

          <dt class="col-5 text-muted">Dibuat</dt>
          <dd class="col-7"><?= date('d M Y', strtotime($meeting['created_at'])) ?></dd>
        </dl>

        <?php if ($meeting['description']): ?>
        <hr>
        <div class="text-muted small fw-semibold mb-1">Agenda / Deskripsi</div>
        <p class="mb-0 small"><?= nl2br(htmlspecialchars($meeting['description'])) ?></p>
        <?php endif; ?>
      </div>
      <div class="card-footer">
        <a href="/notulen/<?= $meeting['id'] ?>" class="btn btn-primary w-100">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          Buka Notulen
        </a>
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
            <span class="avatar avatar-sm" style="background:#f76707;color:white;font-size:12px;font-weight:700;">
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
                  data-bs-target="#modalAddTL">
            + Tambah
          </button>
        </div>
        <?php endif; ?>
      </div>

      <?php if (empty($tindakLanjutList)): ?>
      <div class="card-body text-center text-muted py-5">
        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="1" class="mb-3">
          <polyline points="9 11 12 14 22 4"/>
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        </svg>
        <p class="mb-0">Belum ada tindak lanjut untuk meeting ini</p>
      </div>
      <?php else: ?>
      <div class="table-responsive">
        <table class="table table-vcenter card-table">
          <thead>
            <tr>
              <th>Deskripsi</th><th>Ditugaskan ke</th>
              <th>Deadline</th><th>Prioritas</th><th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tindakLanjutList as $tl):
              $overdue = $tl['deadline'] && $tl['deadline'] < date('Y-m-d')
                         && !in_array($tl['status'],['done','cancelled']);
            ?>
            <tr class="<?= $overdue ? 'table-danger' : '' ?>">
              <td>
                <?= htmlspecialchars($tl['deskripsi']) ?>
                <?php if ($overdue): ?>
                <span class="badge bg-red ms-1 small">Terlambat</span>
                <?php endif; ?>
              </td>
              <td><?= htmlspecialchars($tl['assigned_name'] ?? '-') ?></td>
              <td class="text-muted">
                <?= $tl['deadline'] ? date('d M Y', strtotime($tl['deadline'])) : '-' ?>
              </td>
              <td>
                <span class="badge bg-<?= match($tl['priority']) {
                  'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
                } ?>-lt"><?= ucfirst($tl['priority']) ?></span>
              </td>
              <td>
                <span class="badge bg-<?= match($tl['status']) {
                  'pending'=>'secondary','in_progress'=>'blue',
                  'done'=>'green','cancelled'=>'red',default=>'secondary'
                } ?>"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></span>
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
            <option value="<?= $s ?>" <?= $meeting['status']===$s?'selected':'' ?>>
              <?= ucfirst($s) ?>
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
          <textarea id="tl-deskripsi" class="form-control" rows="3" required
                    placeholder="Tulis deskripsi tindak lanjut..."></textarea>
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
            <input type="date" id="tl-deadline" class="form-control"
                   min="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="mt-2">
          <label class="form-label">Prioritas</label>
          <div class="d-flex gap-2">
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
const MEETING_ID_SHOW = {$meeting['id']};

document.getElementById('btn-save-tl')?.addEventListener('click', async () => {
  const deskripsi = document.getElementById('tl-deskripsi').value.trim();
  if (!deskripsi) { alert('Deskripsi wajib diisi!'); return; }

  const priority = document.querySelector('input[name="tl-priority"]:checked')?.value || 'medium';
  const res = await fetch('/tindak-lanjut', {
    method:  'POST',
    headers: { 'Content-Type': 'application/json' },
    body:    JSON.stringify({
      meeting_id:  MEETING_ID_SHOW,
      deskripsi,
      assigned_to: document.getElementById('tl-assigned').value,
      deadline:    document.getElementById('tl-deadline').value,
      priority
    })
  });
  const data = await res.json();
  if (data.success) {
    bootstrap.Modal.getInstance(document.getElementById('modalAddTL')).hide();
    location.reload();
  } else {
    alert(data.message || 'Gagal menyimpan');
  }
});
</script>
JS; ?>
