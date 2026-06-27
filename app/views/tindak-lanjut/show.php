<?php
$baseUrl    = BASE_URL;
$statusMap  = [
    'pending'     => ['label' => 'Pending',      'color' => 'secondary'],
    'in_progress' => ['label' => 'Berlangsung',  'color' => 'warning'],
    'done'        => ['label' => 'Selesai',       'color' => 'success'],
    'cancelled'   => ['label' => 'Dibatalkan',   'color' => 'danger'],
];
$priorityMap = [
    'low'    => ['label' => 'Rendah',  'color' => 'secondary'],
    'medium' => ['label' => 'Sedang',  'color' => 'warning'],
    'high'   => ['label' => 'Tinggi',  'color' => 'danger'],
];
$st = $statusMap[$tl['status']]   ?? ['label' => $tl['status'],   'color' => 'secondary'];
$pr = $priorityMap[$tl['priority']] ?? ['label' => $tl['priority'], 'color' => 'secondary'];
$isAdminLike = Auth::hasRole('admin', 'sekretaris');
$myId        = Auth::id();
?>

<!-- Breadcrumb -->
<div class="page-header d-print-none mt-3 mb-3">
  <div class="row align-items-center">
    <div class="col">
      <div class="page-pretitle">
        <a href="<?= $baseUrl ?>/tindak-lanjut">Tindak Lanjut</a> &rsaquo;
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>"><?= htmlspecialchars($tl['meeting_title']) ?></a>
      </div>
      <h2 class="page-title"><?= htmlspecialchars($tl['description']) ?></h2>
    </div>
    <div class="col-auto ms-auto">
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-outline-secondary btn-sm">
        &larr; Kembali
      </a>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Kolom kiri: info + notes -->
  <div class="col-lg-8">

    <!-- Info card -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title">Info Tindak Lanjut</h3>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Deskripsi</dt>
          <dd class="col-sm-8"><?= nl2br(htmlspecialchars($tl['description'])) ?></dd>

          <dt class="col-sm-4">Meeting</dt>
          <dd class="col-sm-8">
            <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>">
              <?= htmlspecialchars($tl['meeting_title']) ?>
            </a>
          </dd>

          <dt class="col-sm-4">Ditugaskan ke</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($tl['assignee_name'] ?? '-') ?></dd>

          <dt class="col-sm-4">Dibuat oleh</dt>
          <dd class="col-sm-8"><?= htmlspecialchars($tl['creator_name'] ?? '-') ?></dd>

          <dt class="col-sm-4">Deadline</dt>
          <dd class="col-sm-8">
            <?php if ($tl['due_date']): ?>
              <?= date('d M Y', strtotime($tl['due_date'])) ?>
              <?php if ($tl['status'] !== 'done' && $tl['due_date'] < date('Y-m-d')): ?>
                <span class="badge bg-danger ms-1">Terlambat</span>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </dd>

          <dt class="col-sm-4">Status</dt>
          <dd class="col-sm-8">
            <span class="badge bg-<?= $st['color'] ?>"><?= $st['label'] ?></span>
          </dd>

          <dt class="col-sm-4">Prioritas</dt>
          <dd class="col-sm-8">
            <span class="badge bg-<?= $pr['color'] ?>"><?= $pr['label'] ?></span>
          </dd>

          <?php if ($tl['completed_at']): ?>
          <dt class="col-sm-4">Selesai pada</dt>
          <dd class="col-sm-8"><?= date('d M Y H:i', strtotime($tl['completed_at'])) ?></dd>
          <?php endif; ?>
        </dl>
      </div>

      <?php if ($canEdit): ?>
      <div class="card-footer d-flex gap-2">
        <select id="tl-status-select" class="form-select form-select-sm w-auto">
          <?php foreach ($statusMap as $val => $info): ?>
            <option value="<?= $val ?>" <?= $tl['status'] === $val ? 'selected' : '' ?>>
              <?= $info['label'] ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button id="tl-status-save" class="btn btn-sm btn-primary">Simpan Status</button>
      </div>
      <?php endif; ?>
    </div>

    <!-- Progress Notes -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">Progress Notes</h3>
      </div>
      <div class="card-body" id="notes-list">
        <?php if (empty($notes)): ?>
          <p class="text-muted text-center py-3">Belum ada catatan progress.</p>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
          <div class="note-item d-flex gap-2 mb-3" data-note-id="<?= (int)$note['id'] ?>">
            <div class="flex-fill">
              <div class="d-flex justify-content-between align-items-start">
                <strong><?= htmlspecialchars($note['author_name']) ?></strong>
                <small class="text-muted"><?= date('d M Y H:i', strtotime($note['created_at'])) ?></small>
              </div>
              <div class="mt-1"><?= nl2br(htmlspecialchars($note['note'])) ?></div>
            </div>
            <?php if ($note['can_delete']): ?>
            <button class="btn btn-sm btn-ghost-danger btn-delete-note" data-id="<?= (int)$note['id'] ?>" title="Hapus">
              &times;
            </button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <?php if ($canEdit): ?>
      <div class="card-footer">
        <div class="d-flex gap-2">
          <textarea id="note-input" class="form-control form-control-sm" rows="2"
                    placeholder="Tulis catatan progress..."></textarea>
          <button id="note-submit" class="btn btn-sm btn-primary align-self-end">Kirim</button>
        </div>
      </div>
      <?php endif; ?>
    </div>

  </div>

  <!-- Kolom kanan: quick actions -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Aksi</h3></div>
      <div class="list-group list-group-flush">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="list-group-item list-group-item-action">
          &#128197; Lihat Meeting Terkait
        </a>
        <a href="<?= $baseUrl ?>/tindak-lanjut" class="list-group-item list-group-item-action">
          &#128203; Semua Tindak Lanjut
        </a>
        <?php if ($isAdminLike): ?>
        <button class="list-group-item list-group-item-action text-danger btn-delete-tl"
                data-id="<?= (int)$tl['id'] ?>">
          &#128465; Hapus Tindak Lanjut
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
const TL_ID   = <?= (int)$tl['id'] ?>;
const BASE_URL = '<?= $baseUrl ?>';

// ── Update Status ────────────────────────────────────────────────────
const statusSave = document.getElementById('tl-status-save');
if (statusSave) {
  statusSave.addEventListener('click', async () => {
    const status = document.getElementById('tl-status-select').value;
    statusSave.disabled = true;
    try {
      const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      });
      const d = await res.json();
      if (d.success) {
        location.reload();
      } else {
        alert(d.message || 'Gagal menyimpan status');
      }
    } finally {
      statusSave.disabled = false;
    }
  });
}

// ── Kirim Note ───────────────────────────────────────────────────────
const noteSubmit = document.getElementById('note-submit');
if (noteSubmit) {
  noteSubmit.addEventListener('click', async () => {
    const input = document.getElementById('note-input');
    const note  = input.value.trim();
    if (!note) return;
    noteSubmit.disabled = true;
    try {
      const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/notes`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ note })
      });
      const d = await res.json();
      if (d.success) {
        input.value = '';
        // Tambah note ke DOM tanpa reload
        const list = document.getElementById('notes-list');
        const empty = list.querySelector('.text-center');
        if (empty) empty.remove();
        const n = d.note;
        const div = document.createElement('div');
        div.className = 'note-item d-flex gap-2 mb-3';
        div.dataset.noteId = n.id;
        div.innerHTML = `
          <div class="flex-fill">
            <div class="d-flex justify-content-between align-items-start">
              <strong>${n.author_name}</strong>
              <small class="text-muted">${n.created_at}</small>
            </div>
            <div class="mt-1">${n.note.replace(/\n/g,'<br>')}</div>
          </div>
          ${n.can_delete ? `<button class="btn btn-sm btn-ghost-danger btn-delete-note" data-id="${n.id}">&times;</button>` : ''}
        `;
        list.appendChild(div);
        bindDeleteNote(div.querySelector('.btn-delete-note'));
      } else {
        alert(d.message || 'Gagal mengirim note');
      }
    } finally {
      noteSubmit.disabled = false;
    }
  });
}

// ── Hapus Note ───────────────────────────────────────────────────────
function bindDeleteNote(btn) {
  if (!btn) return;
  btn.addEventListener('click', async () => {
    if (!confirm('Hapus catatan ini?')) return;
    const noteId = btn.dataset.id;
    const res  = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/notes/${noteId}/delete`, { method: 'POST' });
    const d    = await res.json();
    if (d.success) {
      btn.closest('.note-item').remove();
      if (!document.querySelector('.note-item')) {
        document.getElementById('notes-list').innerHTML =
          '<p class="text-muted text-center py-3">Belum ada catatan progress.</p>';
      }
    }
  });
}
document.querySelectorAll('.btn-delete-note').forEach(bindDeleteNote);

// ── Hapus TL ─────────────────────────────────────────────────────────
const delTl = document.querySelector('.btn-delete-tl');
if (delTl) {
  delTl.addEventListener('click', async () => {
    if (!confirm('Hapus tindak lanjut ini secara permanen?')) return;
    const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/delete`, { method: 'POST' });
    const d   = await res.json();
    if (d.success) {
      location.href = `${BASE_URL}/tindak-lanjut`;
    }
  });
}
</script>
