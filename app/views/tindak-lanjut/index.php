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
$isAdminLike = Auth::hasRole('admin','sekretaris');

$qp = array_filter([
  'q'        => $search   ?? '',
  'priority' => $priority ?? '',
  'user_id'  => $user_id  ?? '',
]);
$qpStr = $qp ? '&' . http_build_query($qp) : '';

// Kelompokkan data per status untuk kanban
$kanbanCols = [
  'pending'     => ['label'=>'Pending',         'color'=>'yellow', 'icon'=>'⏳', 'items'=>[]],
  'in_progress' => ['label'=>'Sedang Berjalan', 'color'=>'blue',   'icon'=>'🔄', 'items'=>[]],
  'done'        => ['label'=>'Selesai',         'color'=>'green',  'icon'=>'✅', 'items'=>[]],
  'cancelled'   => ['label'=>'Dibatalkan',      'color'=>'red',    'icon'=>'🚫', 'items'=>[]],
];
// Untuk kanban kita pakai semua data (tanpa filter status)
foreach ($tindakLanjutList as $tl) {
  $s = $tl['status'] ?? 'pending';
  if (isset($kanbanCols[$s])) $kanbanCols[$s]['items'][] = $tl;
}
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

<!-- Toolbar: Filter + Toggle View -->
<div class="card mb-3">
  <div class="card-header flex-column flex-md-row gap-2">
    <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="d-flex flex-wrap gap-2 flex-fill">
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>"
             class="form-control form-control-sm" style="min-width:180px;"
             placeholder="Cari deskripsi...">
      <select name="status" class="form-select form-select-sm" style="width:140px;" id="filter-status">
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
      <?php if (Auth::hasRole('admin') && !empty($users)): ?>
      <select name="user_id" class="form-select form-select-sm" style="width:150px;">
        <option value="">Semua User</option>
        <?php foreach ($users as $u): ?>
        <option value="<?= $u['id'] ?>" <?= ($user_id??0)==$u['id']?'selected':'' ?>>
          <?= htmlspecialchars($u['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?php endif; ?>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
      <?php if (($status??'')||($priority??'')||($search??'')||($user_id??0)): ?>
      <a href="<?= $baseUrl ?>/tindak-lanjut" class="btn btn-sm btn-ghost-danger">Reset</a>
      <?php endif; ?>
    </form>
    <!-- Toggle Table / Kanban -->
    <div class="btn-group ms-auto flex-shrink-0" role="group">
      <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-view-table" title="Tampilan Tabel">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2"/>
          <line x1="3" y1="9" x2="21" y2="9"/>
          <line x1="3" y1="15" x2="21" y2="15"/>
          <line x1="9" y1="3" x2="9" y2="21"/>
        </svg>
        Tabel
      </button>
      <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-view-kanban" title="Tampilan Kanban">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="5" height="18" rx="1"/>
          <rect x="10" y="3" width="5" height="13" rx="1"/>
          <rect x="17" y="3" width="5" height="9" rx="1"/>
        </svg>
        Kanban
      </button>
    </div>
  </div>
</div>

<!-- ═══════════════ VIEW: TABEL ═══════════════ -->
<div id="view-table">
  <div class="card">
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
                      data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                      title="Progress Note"
                      data-bs-toggle="modal" data-bs-target="#modalNotes">💬</button>
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
        <li class="page-item <?= $page<=1?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page-1 ?><?= $qpStr ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          </a>
        </li>
        <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
        <li class="page-item <?= $i===$page?'active':'' ?>">
          <a class="page-link" href="?page=<?= $i ?><?= $qpStr ?>"><?= $i ?></a>
        </li>
        <?php endfor; ?>
        <li class="page-item <?= $page>=$totalPages?'disabled':'' ?>">
          <a class="page-link" href="?page=<?= $page+1 ?><?= $qpStr ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </li>
      </ul>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ═══════════════ VIEW: KANBAN ═══════════════ -->
<div id="view-kanban" style="display:none;">
  <?php if (!empty($status)): ?>
  <div class="alert alert-info py-2 small mb-3">
    ℹ️ Filter status aktif (<strong><?= htmlspecialchars($status) ?></strong>) — kanban menampilkan semua kolom,
    card yang tampil mengikuti filter prioritas & pencarian.
  </div>
  <?php endif; ?>
  <div class="row g-3" id="kanban-board">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3">
      <div class="card h-100">
        <div class="card-header py-2 bg-<?= $col['color'] ?>-lt">
          <h5 class="card-title mb-0">
            <?= $col['icon'] ?> <?= $col['label'] ?>
            <span class="badge bg-<?= $col['color'] ?> ms-1" id="kanban-count-<?= $colStatus ?>">
              <?= count($col['items']) ?>
            </span>
          </h5>
        </div>
        <div class="card-body p-2 kanban-col"
             id="kanban-col-<?= $colStatus ?>"
             data-status="<?= $colStatus ?>"
             style="min-height:200px;overflow-y:auto;max-height:calc(100vh - 320px);">
          <?php if (empty($col['items'])): ?>
          <div class="kanban-empty text-center text-muted py-4 small"
               style="border:2px dashed #dee2e6;border-radius:8px;">
            Tidak ada tugas
          </div>
          <?php endif; ?>
          <?php foreach ($col['items'] as $tl):
            $overdue = !empty($tl['due_date'])
                       && $tl['due_date'] < date('Y-m-d')
                       && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = Auth::hasRole('admin','sekretaris')
                       || ($user['role']==='peserta' && $tl['assigned_to']==$user['id']);
            $pc = match($tl['priority']) {'high'=>'red','medium'=>'orange','low'=>'green',default=>'secondary'};
          ?>
          <div class="kanban-card card card-sm mb-2 <?= $overdue?'border-danger':'' ?>"
               id="kcard-<?= $tl['id'] ?>"
               data-id="<?= $tl['id'] ?>"
               data-status="<?= htmlspecialchars($tl['status']) ?>"
               data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"
               style="cursor:<?= ($canEdit && $isAdminLike) ?'grab':'default' ?>;<?= $tl['status']==='done'||$tl['status']==='cancelled'?'opacity:.7':'' ?>">
            <div class="card-body p-2">
              <div class="d-flex justify-content-between align-items-start gap-1 mb-1">
                <span class="small fw-semibold flex-fill" style="line-height:1.3;">
                  <?= htmlspecialchars($tl['description']) ?>
                </span>
                <span class="badge bg-<?= $pc ?>-lt text-<?= $pc ?> flex-shrink-0"
                      style="font-size:9px;"><?= ucfirst($tl['priority']) ?></span>
              </div>
              <div class="text-muted" style="font-size:11px;">
                👤 <?= htmlspecialchars($tl['assignee_name'] ?? '-') ?>
              </div>
              <?php if (!empty($tl['due_date'])): ?>
              <div class="<?= $overdue?'text-danger':'text-muted' ?>" style="font-size:11px;">
                📅 <?= date('d M Y', strtotime($tl['due_date'])) ?>
                <?php if ($overdue): ?><span class="badge bg-red-lt text-red" style="font-size:9px;">Terlambat</span><?php endif; ?>
              </div>
              <?php endif; ?>
              <div class="d-flex justify-content-between align-items-center mt-1">
                <a href="<?= $baseUrl ?>/meetings/<?= $tl['meeting_id'] ?>" class="text-orange" style="font-size:10px;">
                  📋 <?= htmlspecialchars(mb_strimwidth($tl['meeting_title'],0,28,'…')) ?>
                </a>
                <div class="d-flex gap-1">
                  <button class="btn btn-sm btn-ghost-secondary btn-notes p-0"
                          style="width:22px;height:22px;font-size:12px;"
                          data-id="<?= $tl['id'] ?>"
                          data-desc="<?= htmlspecialchars($tl['description']) ?>"
                          data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                          data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                          data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes"
                          title="Progress Note"
                          data-bs-toggle="modal" data-bs-target="#modalNotes">💬</button>
                  <?php if (Auth::hasRole('admin','sekretaris')): ?>
                  <button class="btn btn-sm btn-ghost-danger btn-del p-0"
                          style="width:22px;height:22px;font-size:12px;"
                          data-id="<?= $tl['id'] ?>"
                          data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete"
                          title="Hapus">✕</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
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
        <div id="notes-thread" style="max-height:340px;overflow-y:auto;padding:1rem;"
             class="d-flex flex-column gap-2">
          <div class="text-center text-muted py-3 small">Memuat...</div>
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

<!-- SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>

<script>
const _activeUserId = <?= (int)($user_id ?? 0) ?>;
const _isAdminLike  = <?= $isAdminLike ? 'true' : 'false' ?>;

// ── Helpers ────────────────────────────────────────────────────────────
function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

function updateStatCards(summary) {
  if (!summary) return;
  ['total','pending','in_progress','done','overdue'].forEach(k => {
    const el = document.getElementById('stat-' + k);
    if (el && summary[k] !== undefined) el.textContent = summary[k];
  });
}

async function postStatus(url, status) {
  const res = await fetch(url, {
    method:  'POST',
    headers: {'Content-Type':'application/json'},
    body:    JSON.stringify({ status, user_id: _activeUserId })
  });
  return res.json();
}

// ── Toggle View ────────────────────────────────────────────────────────
const LS_VIEW = 'tl_view_pref';
const btnTable  = document.getElementById('btn-view-table');
const btnKanban = document.getElementById('btn-view-kanban');
const divTable  = document.getElementById('view-table');
const divKanban = document.getElementById('view-kanban');

function setView(v) {
  const isKanban = v === 'kanban';
  divTable.style.display  = isKanban ? 'none' : '';
  divKanban.style.display = isKanban ? '' : 'none';
  btnTable.classList.toggle('active', !isKanban);
  btnKanban.classList.toggle('active', isKanban);
  localStorage.setItem(LS_VIEW, v);
  // Filter status tidak relevan di kanban — sembunyikan
  const filterStatus = document.getElementById('filter-status');
  if (filterStatus) filterStatus.closest('select').disabled = isKanban;
}

btnTable.addEventListener('click',  () => setView('table'));
btnKanban.addEventListener('click', () => setView('kanban'));
setView(localStorage.getItem(LS_VIEW) || 'table');

// ── Status select (tabel) ──────────────────────────────────────────────
document.querySelectorAll('#view-table .status-select').forEach(sel => {
  sel.addEventListener('change', async function () {
    const d = await postStatus(this.dataset.url, this.value);
    if (!d.success) { alert(d.message || 'Gagal update status'); return; }
    updateStatCards(d.summary);
    this.closest('tr').style.opacity = ['done','cancelled'].includes(this.value) ? '0.5' : '';
  });
});

// ── Hapus TL (tabel + kanban) ──────────────────────────────────────────
function bindDelButtons(scope) {
  (scope || document).querySelectorAll('.btn-del').forEach(btn => {
    if (btn.dataset.bound) return;
    btn.dataset.bound = '1';
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      const res = await fetch(this.dataset.url, { method: 'POST' });
      const d   = await res.json();
      if (!d.success) { alert(d.message || 'Gagal hapus'); return; }
      // Hapus dari tabel
      document.querySelectorAll(`tr:has([data-url$="/${this.dataset.id}/delete"])`)
              .forEach(r => r.remove());
      // Hapus dari kanban
      const kcard = document.getElementById('kcard-' + this.dataset.id);
      if (kcard) {
        const col = kcard.closest('.kanban-col');
        kcard.remove();
        updateColCount(col);
      }
    });
  });
}
bindDelButtons();

// ── Kanban: update badge count per kolom ─────────────────────────────
function updateColCount(colEl) {
  if (!colEl) return;
  const status = colEl.dataset.status;
  const count  = colEl.querySelectorAll('.kanban-card').length;
  const badge  = document.getElementById('kanban-count-' + status);
  if (badge) badge.textContent = count;
  // Tampilkan/sembunyikan placeholder
  let empty = colEl.querySelector('.kanban-empty');
  if (count === 0 && !empty) {
    empty = document.createElement('div');
    empty.className = 'kanban-empty text-center text-muted py-4 small';
    empty.style.cssText = 'border:2px dashed #dee2e6;border-radius:8px;';
    empty.textContent = 'Tidak ada tugas';
    colEl.appendChild(empty);
  } else if (count > 0 && empty) {
    empty.remove();
  }
}

// ── Kanban: SortableJS drag & drop ────────────────────────────────────
document.querySelectorAll('.kanban-col').forEach(col => {
  Sortable.create(col, {
    group:     'kanban',           // allow cross-column drag
    animation: 150,
    ghostClass: 'sortable-ghost',
    dragClass:  'sortable-drag',
    disabled:  !_isAdminLike,     // peserta tidak bisa drag
    filter:    '.btn-notes,.btn-del', // klik tombol tidak trigger drag
    onEnd: async function (evt) {
      const card      = evt.item;
      const newColEl  = evt.to;
      const newStatus = newColEl.dataset.status;
      const oldStatus = evt.from.dataset.status;

      if (newStatus === oldStatus) return; // sama kolom, tidak perlu update

      const url = card.dataset.url;
      card.style.opacity = '0.5';

      const d = await postStatus(url, newStatus);
      if (d.success) {
        card.dataset.status = newStatus;
        updateStatCards(d.summary);
        updateColCount(evt.from);
        updateColCount(newColEl);
        // Fade done/cancelled
        card.style.opacity = ['done','cancelled'].includes(newStatus) ? '0.7' : '';
      } else {
        // Rollback: kembalikan card ke kolom semula
        alert(d.message || 'Gagal update status');
        evt.from.insertBefore(card, evt.from.children[evt.oldIndex] || null);
        card.style.opacity = '';
        updateColCount(evt.from);
        updateColCount(newColEl);
      }
    }
  });
});

// ── Progress Notes ─────────────────────────────────────────────────────
let _currentNoteUrl    = '';
let _currentDeleteBase = '';

function renderNote(n) {
  const d  = new Date(n.created_at);
  const ts = d.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})
           + ' ' + d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  const div = document.createElement('div');
  div.className      = 'card card-sm mb-1';
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
  if (n.can_delete) div.querySelector('.btn-del-note').addEventListener('click', deleteNote);
  return div;
}

async function loadNotes(url) {
  const thread = document.getElementById('notes-thread');
  thread.innerHTML = '<div class="text-center text-muted py-3 small">Memuat...</div>';
  const notes = await (await fetch(url)).json();
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
  const d = await (await fetch(`${_currentDeleteBase}/${noteId}/delete`, { method:'POST' })).json();
  if (d.success) e.currentTarget.closest('[data-noteid]').remove();
  else alert(d.message || 'Gagal hapus');
}

document.querySelectorAll('.btn-notes').forEach(btn => {
  btn.addEventListener('click', function () {
    _currentNoteUrl    = this.dataset.urlPost;
    _currentDeleteBase = this.dataset.deleteBase;
    document.getElementById('notes-desc').textContent = this.dataset.desc;
    document.getElementById('note-input').value = '';
    loadNotes(this.dataset.urlGet);
  });
});

document.getElementById('btn-send-note')?.addEventListener('click', async function () {
  const note = document.getElementById('note-input').value.trim();
  if (!note) return;
  this.disabled = true;
  const d = await (await fetch(_currentNoteUrl, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify({ note })
  })).json();
  this.disabled = false;
  if (!d.success) { alert(d.message || 'Gagal kirim'); return; }
  document.getElementById('note-input').value = '';
  const thread = document.getElementById('notes-thread');
  const empty  = thread.querySelector('.text-center');
  if (empty) empty.remove();
  thread.appendChild(renderNote(d.note));
  thread.scrollTop = thread.scrollHeight;
});

document.getElementById('note-input')?.addEventListener('keydown', e => {
  if (e.ctrlKey && e.key === 'Enter') document.getElementById('btn-send-note').click();
});
</script>

<style>
.sortable-ghost  { opacity: .35; background: #e7f1ff; border: 2px dashed #4e9af1; }
.sortable-drag   { box-shadow: 0 8px 24px rgba(0,0,0,.18) !important; transform: rotate(1.5deg); }
.kanban-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,.1); }
#kanban-board .kanban-col { transition: background .15s; }
#kanban-board .kanban-col.sortable-over { background: #f0f6ff; }
</style>
