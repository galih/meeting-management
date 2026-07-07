<?php
$baseUrl     = rtrim(BASE_URL, '/');
$user        = Auth::user();
$isAdminLike = Auth::hasRole('admin', 'sekretaris');

$priorityColor = ['high' => 'danger', 'medium' => 'warning', 'low' => 'success'];
$priorityLabel = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
$statusColor   = ['pending' => 'secondary', 'in_progress' => 'info', 'done' => 'success', 'cancelled' => 'danger'];
$statusLabel   = ['pending' => 'Menunggu', 'in_progress' => 'Berlangsung', 'done' => 'Selesai', 'cancelled' => 'Dibatalkan'];

$qp    = array_filter(['q' => $search ?? '', 'status' => $status ?? '', 'priority' => $priority ?? '', 'user_id' => $user_id ?? 0]);
$qpStr = $qp ? '&' . http_build_query($qp) : '';

$kanbanCols = [
    'pending'     => ['label' => 'Menunggu',    'color' => 'secondary', 'items' => []],
    'in_progress' => ['label' => 'Berlangsung', 'color' => 'info',      'items' => []],
    'done'        => ['label' => 'Selesai',     'color' => 'success',   'items' => []],
    'cancelled'   => ['label' => 'Dibatalkan',  'color' => 'danger',    'items' => []],
];
foreach ($tindakLanjutList as $tl) {
    $s = $tl['status'] ?? 'pending';
    if (isset($kanbanCols[$s])) $kanbanCols[$s]['items'][] = $tl;
}

$allUsersJson = json_encode(array_values(array_map(
    fn($u) => ['id' => (int)$u['id'], 'name' => $u['name']],
    $allUsers ?? []
)));

$meetingOptions = Database::query("SELECT id, title FROM meetings ORDER BY start_datetime DESC LIMIT 200");

$csrfToken = htmlspecialchars($_SESSION['csrf_token'] ?? '');
?>

<div class="tli-page-header mb-4">
  <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
      <div class="d-flex align-items-center gap-2 mb-1">
        <span class="tli-header-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </span>
        <h1 class="tli-page-title">Tindak Lanjut</h1>
      </div>
      <p class="tli-page-sub">Kelola dan pantau semua tindak lanjut kegiatan rapat</p>
    </div>
    <?php if ($isAdminLike): ?>
    <button class="tli-btn-gold" data-bs-toggle="modal" data-bs-target="#modalTambahTL">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Tambah Tindak Lanjut
    </button>
    <?php endif; ?>
  </div>
</div>

<div class="tli-stat-row mb-4">
<?php
$statDefs = [
  ['key'=>'total',       'label'=>'Total Tugas',  'icon'=>'brand',
   'svg'=>'<polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>'],
  ['key'=>'pending',     'label'=>'Menunggu',     'icon'=>'muted',
   'svg'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'],
  ['key'=>'in_progress', 'label'=>'Berlangsung',  'icon'=>'info',
   'svg'=>'<polygon points="5 3 19 12 5 21 5 3"/>'],
  ['key'=>'done',        'label'=>'Selesai',      'icon'=>'success',
   'svg'=>'<polyline points="20 6 9 17 4 12"/>'],
  ['key'=>'overdue',     'label'=>'Terlambat',    'icon'=>'danger',
   'svg'=>'<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'],
];
foreach ($statDefs as $sc): ?>
  <div class="tli-stat-card">
    <span class="tli-stat-icon tli-stat-icon-<?= $sc['icon'] ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $sc['svg'] ?></svg>
    </span>
    <div>
      <div class="tli-stat-val" id="stat-<?= $sc['key'] ?>"><?= (int)($summary[$sc['key']] ?? 0) ?></div>
      <div class="tli-stat-lbl"><?= $sc['label'] ?></div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<div class="tli-toolbar mb-3">
  <form method="GET" action="<?= $baseUrl ?>/tindak-lanjut" class="tli-filter-form">
    <div class="tli-search-wrap">
      <svg class="tli-search-ico" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" name="q" value="<?= htmlspecialchars($search ?? '') ?>" class="tli-input tli-search-input" placeholder="Cari deskripsi…">
    </div>

    <select name="status" class="tli-input tli-select" id="filter-status">
      <option value="">Semua Status</option>
      <?php foreach ($statusLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($status ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <select name="priority" class="tli-input tli-select">
      <option value="">Semua Prioritas</option>
      <?php foreach ($priorityLabel as $v => $l): ?>
      <option value="<?= $v ?>" <?= ($priority ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
      <?php endforeach; ?>
    </select>

    <?php if (Auth::hasRole('admin') && !empty($allUsers)): ?>
    <select name="user_id" class="tli-input tli-select">
      <option value="">Semua User</option>
      <?php foreach ($allUsers as $u): ?>
      <option value="<?= $u['id'] ?>" <?= ($user_id ?? 0) == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <?php endif; ?>

    <button type="submit" class="tli-btn-brand-sm">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
      Filter
    </button>
    <?php if (($status ?? '') || ($priority ?? '') || ($search ?? '') || ($user_id ?? 0)): ?>
    <a href="<?= $baseUrl ?>/tindak-lanjut" class="tli-btn-reset">&#x2715; Reset</a>
    <?php endif; ?>
  </form>

  <div class="tli-view-toggle">
    <button id="btn-view-table" class="tli-vbtn" title="Tampilan Tabel">Tabel</button>
    <button id="btn-view-kanban" class="tli-vbtn" title="Tampilan Kanban">Kanban</button>
  </div>
</div>

<div id="view-table">
  <div class="tli-card">
    <div class="table-responsive">
      <table class="table table-hover mb-0 tli-table">
        <thead><tr><th style="width:32%">Tugas</th><th>Meeting</th><th>PIC</th><th>Deadline</th><th>Prioritas</th><th>Status</th><th style="width:96px"></th></tr></thead>
        <tbody>
          <?php if (empty($tindakLanjutList)): ?>
          <tr><td colspan="7"><div class="tli-empty-state"><p>Belum ada tindak lanjut yang ditemukan</p></div></td></tr>
          <?php endif; ?>
          <?php foreach ($tindakLanjutList as $tl):
            $overdue = !empty($tl['due_date']) && $tl['due_date'] < date('Y-m-d') && !in_array($tl['status'], ['done','cancelled']);
            $canEdit = $isAdminLike || ($tl['assigned_to'] == $user['id']);
            $nc      = (int)($tl['note_count'] ?? 0);
          ?>
          <tr class="<?= $overdue ? 'tli-row-overdue' : '' ?>" id="trow-<?= $tl['id'] ?>">
            <td><a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-task-link"><?= htmlspecialchars($tl['description']) ?></a></td>
            <td><a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-meeting-link"><?= htmlspecialchars(mb_strimwidth($tl['meeting_title'], 0, 40, '…')) ?></a></td>
            <td><?php if (!empty($tl['assignee_name'])): ?><div class="d-flex align-items-center gap-2"><span class="tli-avatar"><?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?></span><span class="tli-assignee"><?= htmlspecialchars($tl['assignee_name']) ?></span></div><?php else: ?><span class="tli-text-muted">—</span><?php endif; ?></td>
            <td class="text-nowrap <?= $overdue ? 'tli-overdue-text' : 'tli-text-muted' ?>"><?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?></td>
            <td><span class="tli-badge tli-badge-<?= ['high'=>'danger','medium'=>'warning','low'=>'success'][$tl['priority']] ?? 'muted' ?>"><?= $priorityLabel[$tl['priority']] ?? ucfirst($tl['priority']) ?></span></td>
            <td><?php if ($canEdit): ?><select class="tli-status-sel status-select" data-id="<?= $tl['id'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"><?php foreach ($statusLabel as $v => $l): ?><option value="<?= $v ?>" <?= $tl['status'] === $v ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select><?php else: ?><span class="tli-badge tli-badge-<?= ['pending'=>'muted','in_progress'=>'info','done'=>'success','cancelled'=>'danger'][$tl['status']] ?? 'muted' ?>"><?= $statusLabel[$tl['status']] ?? ucfirst($tl['status']) ?></span><?php endif; ?></td>
            <td><div class="d-flex gap-1 justify-content-end align-items-center"><button class="tli-ico-btn btn-notes" data-id="<?= $tl['id'] ?>" data-status="<?= $tl['status'] ?>" data-can-done="<?= ($canEdit && $tl['status'] !== 'done') ? '1' : '0' ?>" data-desc="<?= htmlspecialchars($tl['description']) ?>" data-url-get="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes" data-url-post="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes" data-url-status="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status" data-delete-base="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/notes" title="Progress Notes" data-bs-toggle="modal" data-bs-target="#modalNotes">Notes <span class="tli-note-bubble" id="nbadge-<?= $tl['id'] ?>" <?= $nc < 1 ? 'style="display:none"' : '' ?>><?= $nc ?></span></button><a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-ico-btn tli-ico-detail" title="Detail">D</a><?php if ($isAdminLike): ?><button class="tli-ico-btn tli-ico-del btn-del" data-id="<?= $tl['id'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/delete" title="Hapus">X</button><?php endif; ?></div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div id="view-kanban" style="display:none">
  <div class="row g-3">
    <?php foreach ($kanbanCols as $colStatus => $col): ?>
    <div class="col-12 col-md-6 col-xl-3"><div class="tli-kb-col"><div class="tli-kb-header"><span class="tli-kb-title"><?= $col['label'] ?></span><span class="tli-badge tli-badge-brand ms-auto" id="kanban-count-<?= $colStatus ?>"><?= count($col['items']) ?></span></div><div class="tli-kb-body kanban-col" id="kanban-col-<?= $colStatus ?>" data-status="<?= $colStatus ?>"><?php if (empty($col['items'])): ?><div class="tli-kb-empty kanban-empty">Belum ada tugas</div><?php endif; ?><?php foreach ($col['items'] as $tl): ?><div class="tli-kcard" id="kcard-<?= $tl['id'] ?>" data-id="<?= $tl['id'] ?>" data-status="<?= $tl['status'] ?>" data-url="<?= $baseUrl ?>/tindak-lanjut/<?= $tl['id'] ?>/status"><a href="<?= $baseUrl ?>/tindak-lanjut/<?= (int)$tl['id'] ?>" class="tli-kcard-desc"><?= htmlspecialchars($tl['description']) ?></a><div class="tli-kcard-footer"><a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tli-kcard-meeting"><?= htmlspecialchars(mb_strimwidth($tl['meeting_title'],0,28,'…')) ?></a></div></div><?php endforeach; ?></div></div></div>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($isAdminLike): ?><div class="modal fade" id="modalTambahTL" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><form id="formTambahTL"><input type="hidden" name="_csrf" value="<?= $csrfToken ?>"><div class="modal-body"><select name="meeting_id" class="form-select" required><?php foreach ($meetingOptions as $m): ?><option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['title']) ?></option><?php endforeach; ?></select><textarea name="description" class="form-control" rows="3" required></textarea></div><div class="modal-footer"><button type="submit">Simpan</button></div></form></div></div></div><?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
<script>
(function(){'use strict';var BASE=<?= json_encode($baseUrl) ?>;var ALL_USERS=<?= $allUsersJson ?>;var IS_ADMIN_LIKE=<?= $isAdminLike ? 'true' : 'false' ?>;var CSRF_TOKEN=<?= json_encode($_SESSION['csrf_token'] ?? '') ?>;
function postJSON(url,body){return fetch(url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify(Object.assign({_csrf:CSRF_TOKEN},body))}).then(r=>r.json())}
function updateStatCards(s){if(!s)return;['total','pending','in_progress','done','overdue'].forEach(function(k){var el=document.getElementById('stat-'+k);if(el&&s[k]!==undefined)el.textContent=s[k];});}
function updateNoteBubble(id,count){document.querySelectorAll('#nbadge-'+id).forEach(function(el){el.textContent=count;el.style.display=count>0?'flex':'none';});}
var btnTable=document.getElementById('btn-view-table'),btnKanban=document.getElementById('btn-view-kanban'),divTable=document.getElementById('view-table'),divKanban=document.getElementById('view-kanban');
function setView(v){var isK=v==='kanban';divTable.style.display=isK?'none':'';divKanban.style.display=isK?'':'none';btnTable.classList.toggle('active',!isK);btnKanban.classList.toggle('active',isK);}btnTable.addEventListener('click',function(){setView('table');});btnKanban.addEventListener('click',function(){setView('kanban');});setView('table');
document.querySelectorAll('.status-select').forEach(function(sel){sel.dataset.prev=sel.value;sel.addEventListener('change',async function(){var d=await postJSON(this.dataset.url,{status:this.value});if(!d.success){alert(d.message||'Gagal update status');this.value=this.dataset.prev;return;}this.dataset.prev=this.value;updateStatCards(d.summary);});});
function bindDel(){document.querySelectorAll('.btn-del').forEach(function(btn){if(btn._bound)return;btn._bound=true;btn.addEventListener('click',async function(){if(!confirm('Hapus tindak lanjut ini?'))return;var r=await fetch(this.dataset.url,{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body:JSON.stringify({_csrf:CSRF_TOKEN})});var d=await r.json();if(!d.success){alert(d.message||'Gagal hapus');return;}var id=this.dataset.id;var trow=document.getElementById('trow-'+id);var kcard=document.getElementById('kcard-'+id);if(trow)trow.remove();if(kcard){var kcol=kcard.closest('.kanban-col');kcard.remove();}updateStatCards(d.summary);});});}bindDel();
if(IS_ADMIN_LIKE){document.querySelectorAll('.kanban-col').forEach(function(col){Sortable.create(col,{group:'kanban',animation:150,onEnd:async function(evt){var card=evt.item,newCol=evt.to,oldCol=evt.from,newStatus=newCol.dataset.status;if(newStatus===card.dataset.status)return;card.dataset.status=newStatus;var d=await postJSON(card.dataset.url,{status:newStatus});if(!d.success){alert(d.message||'Gagal update status');oldCol.appendChild(card);card.dataset.status=oldCol.dataset.status;return;}updateStatCards(d.summary);}});});}
})();
</script>
