<?php
$baseUrl   = rtrim(BASE_URL, '/');
$statCards = [
  ['key'=>'total',       'label'=>'Total Tugas',    'color'=>'blue'],
  ['key'=>'pending',     'label'=>'Pending',         'color'=>'yellow'],
  ['key'=>'in_progress', 'label'=>'Sedang Berjalan', 'color'=>'orange'],
  ['key'=>'done',        'label'=>'Selesai',         'color'=>'green'],
  ['key'=>'overdue',     'label'=>'Terlambat',       'color'=>'red'],
];
$user = Auth::user();
// Query string tanpa 'page' untuk link pagination
$qp = array_filter([
  'q'        => $search   ?? '',
  'status'   => $status   ?? '',
  'priority' => $priority ?? '',
  'user_id'  => $user_id  ?? '',
]);
$qpStr = $qp ? '&' . http_build_query($qp) : '';
?>

<!-- Stat Cards -->
<div class="row row-deck row-cards mb-4" id="stat-cards">
  <?php foreach ($statCards as $sc): ?>
  <div class="col-6 col-lg">
    <div class="card">
      <div class="card-body">
        <div class="subheader text-muted mb-1"><?= $sc['label'] ?></div>
        <div class="h1 mb-0" id="stat-<?= $sc['key'] ?>"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
        <div class="mt-1">
          <span class="status-dot bg-<?= $sc['color'] ?> d-inline-block me-1"></span>
          <span class="text-muted small"><?= $sc['label'] ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Flash -->
<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mb-3">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<!-- Filter + Tabel -->
<div class="card">
  <div class="card-header flex-column flex-md-row gap-2">
    <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="d-flex flex-wrap gap-2 flex-fill">
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
             class="form-control form-control-sm" style="min-width:180px;"
             placeholder="Cari deskripsi...">
      <select name="status" class="form-select form-select-sm" style="width:140px;">
        <option value="">Semua Status</option>
        <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','cancelled'=>'Cancelled'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($status??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <select name="priority" class="form-select form-select-sm" style="width:130px;">
        <option value="">Semua Prioritas</option>
        <?php foreach (['high'=>'High','medium'=>'Medium','low'=>'Low'] as $v=>$l): ?>
        <option value="<?= $v ?>" <?= ($priority??'')===$v?'selected':'' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
      <?php if (($status??'')||($priority??'')||($search??'')): ?>
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-sm btn-ghost-danger">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-vcenter card-table table-hover">
      <thead>
        <tr>
          <th>Deskripsi</th>
          <th>Meeting</th>
          <th>Ditugaskan ke</th>
          <th>Deadline</th>
          <th>Prioritas</th>
          <th>Status</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($tindakLanjutList)): ?>
        <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada data tindak lanjut</td></tr>
        <?php endif; ?>
        <?php foreach ($tindakLanjutList as $tl):
          $overdue = !empty($tl['due_date'])
                     && $tl['due_date'] < date('Y-m-d')
                     && !in_array($tl['status'], ['done','cancelled']);
          $canEdit = Auth::hasRole('admin','sekretaris')
                     || ($user['role']==='peserta' && $tl['assigned_to']==$user['id']);
        ?>
        <tr class="<?= $overdue ? 'table-danger' : '' ?>">
          <td>
            <div class="fw-semibold"><?= htmlspecialchars($tl['description']) ?></div>
            <?php if ($overdue): ?>
            <span class="badge bg-red-lt text-red small">⚠ Terlambat</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="<?= $baseUrl ?>/meetings/<?= $tl['meeting_id'] ?>" class="text-orange small">
              <?= htmlspecialchars($tl['meeting_title']) ?>
            </a>
          </td>
          <td class="text-muted"><?= htmlspecialchars($tl['assignee_name'] ?? '-') ?></td>
          <td class="text-muted small">
            <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '-' ?>
          </td>
          <td>
            <span class="badge bg-<?= match($tl['priority']) {
              'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'
            } ?>-lt"><?= ucfirst($tl['priority']) ?></span>
          </td>
          <td>
            <?php if ($canEdit): ?>
            <select class="form-select form-select-sm status-select"
                    data-id="<?= $tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
                    style="width:130px;">
              <?php foreach (['pending'=>'Pending','in_progress'=>'In Progress','done'=>'Done','cancelled'=>'Cancelled'] as $v=>$l): ?>
              <option value="<?= $v ?>" <?= $tl['status']===$v?'selected':'' ?>><?= $l ?></option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
            <span class="badge bg-<?= match($tl['status']) {
              'pending'=>'secondary','in_progress'=>'blue',
              'done'=>'green','cancelled'=>'red',default=>'secondary'
            } ?>"><?= ucfirst(str_replace('_',' ',$tl['status'])) ?></span>
            <?php endif; ?>
          </td>
          <td class="text-end">
            <button class="btn btn-sm btn-ghost-secondary btn-notes"
                    data-id="<?= $tl['id'] ?>"
                    data-desc="<?= htmlspecialchars($tl['description']) ?>"
                    data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                    data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                    title="Progress Note"
                    data-bs-toggle="modal" data-bs-target="#modalNotes">
              💬
            </button>
            <?php if (Auth::hasRole('admin','sekretaris')): ?>
            <button class="btn btn-sm btn-ghost-danger btn-del"
                    data-id="<?= $tl['id'] ?>"
                    data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                    title="Hapus">✕</button>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($totalPages > 1): ?>
  <div class="card-footer d-flex align-items-center justify-content-between">
    <p class="m-0 text-muted small">
      Menampilkan <?= (($page-1)*$perPage)+1 ?>–<?= min($page*$perPage, $totalRows) ?>
      dari <?= $totalRows ?> data
    </p>
    <ul class="pagination m-0">
      <li class="page-item <?= $page<=1 ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page-1 ?><?= $qpStr ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </a>
      </li>
      <?php
        $start = max(1, $page - 2);
        $end   = min($totalPages, $page + 2);
        for ($i = $start; $i <= $end; $i++):
      ?>
      <li class="page-item <?= $i===$page ? 'active' : '' ?>">
        <a class="page-link" href="?page=<?= $i ?><?= $qpStr ?>"><?= $i ?></a>
      </li>
      <?php endfor; ?>
      <li class="page-item <?= $page>=$totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="?page=<?= $page+1 ?><?= $qpStr ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
               fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </a>
      </li>
    </ul>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Progress Notes -->
<div class="modal modal-blur fade" id="modalNotes" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-0">Progress Note</h5>
          <div class="text-muted small" id="notes-desc"></div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="notes-thread"
             style="max-height:340px;overflow-y:auto;padding:1rem;"
             class="d-flex flex-column gap-2">
          <div class="text-center text-muted py-3 small" id="notes-loading">Memuat...</div>
        </div>
        <div class="border-top p-3 d-flex gap-2 align-items-end">
          <textarea id="note-input" class="form-control form-control-sm"
                    rows="2" placeholder="Tulis progress note... (Ctrl+Enter kirim)" style="resize:none;"></textarea>
          <button id="btn-send-note" class="btn btn-primary btn-sm">Kirim</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// ── Helpers ──────────────────────────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ── Stat cards live update ────────────────────────────────────────────────────
function updateStatCards(summary) {
  if (!summary) return;
  ['total','pending','in_progress','done','overdue'].forEach(key => {
    const el = document.getElementById('stat-' + key);
    if (el && summary[key] !== undefined) el.textContent = summary[key];
  });
}

// ── Status select ─────────────────────────────────────────────────────────────
document.querySelectorAll('.status-select').forEach(sel => {
  sel.addEventListener('change', async function () {
    const res = await fetch(this.dataset.url, {
      method:  'POST',
      headers: {'Content-Type':'application/json'},
      body:    JSON.stringify({ status: this.value })
    });
    const d = await res.json();
    if (!d.success) {
      alert(d.message || 'Gagal update status');
    } else {
      // Update stat cards tanpa reload
      updateStatCards(d.summary);
      if (this.value === 'done' || this.value === 'cancelled') {
        this.closest('tr').style.opacity = '0.5';
      } else {
        this.closest('tr').style.opacity = '';
      }
    }
  });
});

// ── Hapus TL ──────────────────────────────────────────────────────────────────
document.querySelectorAll('.btn-del').forEach(btn => {
  btn.addEventListener('click', async function () {
    if (!confirm('Hapus tindak lanjut ini?')) return;
    const res = await fetch(this.dataset.url, { method: 'POST' });
    const d   = await res.json();
    if (d.success) this.closest('tr').remove();
    else alert(d.message || 'Gagal hapus');
  });
});

// ── Progress Notes ────────────────────────────────────────────────────────────
let _currentNoteUrl      = '';
let _currentDeleteBase   = '<?= rtrim(BASE_URL, '/') ?>/tindak-lanjut/notes';

function renderNote(n) {
  const d  = new Date(n.created_at);
  const ts = d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})
           + ' ' + d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const div = document.createElement('div');
  div.className = 'card card-sm mb-1';
  div.dataset.noteid = n.id;

  const delBtn = n.can_delete
    ? `<button class="btn btn-sm btn-ghost-danger btn-del-note ms-1 py-0 px-1"
              data-note-id="${n.id}" title="Hapus">✕</button>`
    : '';

  div.innerHTML = `
    <div class="card-body py-2 px-3">
      <div class="d-flex justify-content-between align-items-center mb-1">
        <span class="fw-semibold small">${escHtml(n.author_name)}</span>
        <span class="text-muted" style="font-size:11px;">${ts}${delBtn}</span>
      </div>
      <div class="text-muted small" style="white-space:pre-wrap;">${escHtml(n.note)}</div>
    </div>`;

  if (n.can_delete) {
    div.querySelector('.btn-del-note').addEventListener('click', deleteNote);
  }
  return div;
}

async function loadNotes(url) {
  const thread = document.getElementById('notes-thread');
  thread.innerHTML = '<div class="text-center text-muted py-3 small">Memuat...</div>';
  const res   = await fetch(url);
  const notes = await res.json();
  thread.innerHTML = '';
  if (!notes.length) {
    thread.innerHTML = '<div class="text-center text-muted py-3 small">Belum ada progress note.</div>';
    return;
  }
  notes.forEach(n => thread.appendChild(renderNote(n)));
  thread.scrollTop = thread.scrollHeight;
}

async function deleteNote(e) {
  if (!confirm('Hapus note ini?')) return;
  const noteId = e.currentTarget.dataset.noteId;
  const res = await fetch(`${_currentDeleteBase}/${noteId}/delete`, { method: 'POST' });
  const d   = await res.json();
  if (d.success) e.currentTarget.closest('[data-noteid]').remove();
  else alert(d.message || 'Gagal hapus');
}

document.querySelectorAll('.btn-notes').forEach(btn => {
  btn.addEventListener('click', function () {
    _currentNoteUrl = this.dataset.urlPost;
    document.getElementById('notes-desc').textContent = this.dataset.desc;
    document.getElementById('note-input').value = '';
    loadNotes(this.dataset.urlGet);
  });
});

document.getElementById('btn-send-note')?.addEventListener('click', async function () {
  const note = document.getElementById('note-input').value.trim();
  if (!note) return;
  this.disabled = true;
  const res = await fetch(_currentNoteUrl, {
    method:  'POST',
    headers: {'Content-Type':'application/json'},
    body:    JSON.stringify({ note })
  });
  const d = await res.json();
  this.disabled = false;
  if (!d.success) { alert(d.message || 'Gagal kirim'); return; }
  document.getElementById('note-input').value = '';
  const thread = document.getElementById('notes-thread');
  const empty  = thread.querySelector('.text-center');
  if (empty) empty.remove();
  thread.appendChild(renderNote(d.note));
  thread.scrollTop = thread.scrollHeight;
});

document.getElementById('note-input')?.addEventListener('keydown', function(e) {
  if (e.ctrlKey && e.key === 'Enter') document.getElementById('btn-send-note').click();
});
</script>
