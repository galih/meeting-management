<?php
$baseUrl  = rtrim(BASE_URL, '/');
$isAdmin  = Auth::hasRole('admin');
$allUsers = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];
?>

<?php if (!empty($_SESSION['flash_success'])): ?>
<div class="alert alert-success alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_success']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_success']); endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="alert alert-danger alert-dismissible mt-2">
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<div class="card">
  <div class="card-header">
    <ul class="nav nav-tabs card-header-tabs" id="meetingTabs">
      <li class="nav-item">
        <a class="nav-link active" href="#" data-view="calendar">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
          </svg>Kalender
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#" data-view="list">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon me-1" width="24" height="24"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/>
            <line x1="8" y1="18" x2="21" y2="18"/>
            <circle cx="3" cy="6" r="1"/><circle cx="3" cy="12" r="1"/><circle cx="3" cy="18" r="1"/>
          </svg>Daftar
        </a>
      </li>
    </ul>
    <?php if (Auth::hasRole('admin', 'sekretaris')): ?>
    <div class="card-options">
      <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalMeeting">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24"
             viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Buat Kegiatan
      </button>
    </div>
    <?php endif; ?>
  </div>

  <div class="card-body">
    <div id="view-calendar">
      <div id="calendar" style="min-height:600px;"></div>
    </div>
    <div id="view-list" style="display:none;">
      <div class="table-responsive">
        <table class="table table-vcenter card-table table-hover">
          <thead>
            <tr>
              <th>Judul Kegiatan</th><th>Lokasi</th><th>Mulai</th>
              <th>Selesai</th><th>Peserta</th><th>Status</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($meetings)): ?>
            <tr><td colspan="7" class="text-center text-muted py-5">Belum ada kegiatan</td></tr>
            <?php endif; ?>
            <?php foreach ($meetings as $m): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= htmlspecialchars($m['title']) ?></div>
                <div class="text-muted small">oleh <?= htmlspecialchars($m['creator_name'] ?? '-') ?></div>
              </td>
              <td class="text-muted"><?= htmlspecialchars($m['location'] ?? '-') ?></td>
              <td><?= date('d M Y', strtotime($m['start_datetime'])) ?><br>
                  <small class="text-muted"><?= date('H:i', strtotime($m['start_datetime'])) ?></small></td>
              <td><?= date('d M Y', strtotime($m['end_datetime'])) ?><br>
                  <small class="text-muted"><?= date('H:i', strtotime($m['end_datetime'])) ?></small></td>
              <td><span class="badge bg-blue-lt"><?= $m['total_peserta'] ?> orang</span></td>
              <td>
                <span class="badge bg-<?= match($m['status']) {
                  'scheduled' => 'blue',
                  'ongoing'   => 'orange',
                  'done'      => 'green',
                  'cancelled' => 'red',
                  default     => 'secondary'
                } ?>"><?= $statusLabel[$m['status']] ?? ucfirst($m['status']) ?></span>
              </td>
              <td>
                <div class="d-flex gap-1 align-items-center">
                  <a href="<?= $baseUrl ?>/meetings/<?= $m['id'] ?>"
                     class="btn btn-sm btn-outline-primary">Detail</a>
                  <?php if ($isAdmin): ?>
                  <button type="button"
                          class="btn btn-sm btn-outline-danger"
                          title="Hapus Kegiatan"
                          onclick="confirmDeleteMeeting(<?= $m['id'] ?>, <?= htmlspecialchars(json_encode($m['title'])) ?>)">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                         viewBox="0 0 24 24" fill="none" stroke="currentColor"
                         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <polyline points="3 6 5 6 21 6"/>
                      <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                      <path d="M10 11v6"/><path d="M14 11v6"/>
                      <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                    </svg>
                  </button>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php if ($isAdmin): ?>
<!-- Modal Konfirmasi Hapus Kegiatan -->
<div class="modal modal-blur fade" id="modalDeleteMeeting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body">
        <div class="modal-title">Hapus Kegiatan</div>
        <div class="mt-2">Yakin ingin menghapus kegiatan:<br>
          <strong id="deleteMeetingTitle" class="text-danger"></strong>?
        </div>
        <div class="text-muted small mt-1">
          Semua data peserta, tindak lanjut, dan notulen terkait akan ikut terhapus.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-link link-secondary me-auto"
                data-bs-dismiss="modal">Batal</button>
        <form id="formDeleteMeeting" method="POST" action="">
          <?= Auth::csrfField() ?>
          <input type="hidden" name="_method" value="DELETE">
          <button type="submit" class="btn btn-danger">Ya, Hapus</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Modal Buat Kegiatan -->
<div class="modal modal-blur fade" id="modalMeeting" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="POST" action="<?= $baseUrl ?>/meetings">
        <?= Auth::csrfField() ?>
        <div class="modal-header">
          <h5 class="modal-title">Buat Kegiatan Baru</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label required">Judul Kegiatan</label>
              <input type="text" name="title" class="form-control" required
                     placeholder="Contoh: Rapat Evaluasi Bulanan Q2">
            </div>
            <div class="col-md-6">
              <label class="form-label required">Tanggal & Jam Mulai</label>
              <input type="datetime-local" name="start_datetime" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label required">Tanggal & Jam Selesai</label>
              <input type="datetime-local" name="end_datetime" class="form-control" required>
            </div>
            <div class="col-12">
              <label class="form-label">Lokasi / Link Video</label>
              <input type="text" name="location" class="form-control"
                     placeholder="Ruang Rapat A / https://meet.google.com/...">
            </div>

            <!-- Unit Kerja Cascade -->
            <div class="col-12">
              <label class="form-label">Unit Kerja</label>
              <div class="row g-2">
                <div class="col-md-4">
                  <select id="mtg-u1" class="form-select" onchange="cascadeMtg(1)">
                    <option value="">-- Semua Unit Kerja --</option>
                    <?php foreach ($departments as $d): if ((int)($d['level'] ?? 1) !== 1) continue; ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Unit Kerja</div>
                </div>
                <div class="col-md-4">
                  <select id="mtg-u2" class="form-select" disabled onchange="cascadeMtg(2)">
                    <option value="">-- Pilih unit dulu --</option>
                  </select>
                  <div class="form-text">Bidang / Bagian</div>
                </div>
                <div class="col-md-4">
                  <select id="mtg-u3" class="form-select" disabled onchange="cascadeMtg(3)">
                    <option value="">-- Opsional --</option>
                  </select>
                  <div class="form-text">Sub Bidang / Sub Bagian</div>
                </div>
              </div>
              <input type="hidden" id="mtg-dept-id" name="department_id" value="">
            </div>

            <div class="col-md-6">
              <label class="form-label">Warna Kalender</label>
              <input type="color" name="color" class="form-control form-control-color" value="#206bc4">
            </div>
            <div class="col-12">
              <label class="form-label">Peserta</label>
              <select name="participants[]" class="form-select" multiple size="5">
                <?php foreach ($allUsers as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="text-muted">Tahan Ctrl / Cmd untuk pilih lebih dari satu</small>
            </div>
            <div class="col-12">
              <label class="form-label">Deskripsi / Agenda</label>
              <textarea name="description" class="form-control" rows="3"
                        placeholder="Tulis agenda kegiatan..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-link link-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary ms-auto">Buat Kegiatan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php
$calendarApiUrl  = $baseUrl . '/api/meetings/calendar';
$meetingBaseUrl  = $baseUrl . '/meetings/';
$deptChildrenUrl = $baseUrl . '/api/departments/children';
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-view]').forEach(tab => {
    tab.addEventListener('click', e => {
      e.preventDefault();
      document.querySelectorAll('[data-view]').forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      const v = tab.dataset.view;
      document.getElementById('view-calendar').style.display = v === 'calendar' ? '' : 'none';
      document.getElementById('view-list').style.display     = v === 'list'     ? '' : 'none';
      if (v === 'calendar') calendar.render();
    });
  });

  const calendarEl = document.getElementById('calendar');
  const calendar   = new FullCalendar.Calendar(calendarEl, {
    initialView:  'dayGridMonth',
    locale:       'id',
    height:       650,
    headerToolbar: {
      left:   'prev,next today',
      center: 'title',
      right:  'dayGridMonth,timeGridWeek,listWeek'
    },
    buttonText: { today:'Hari ini', month:'Bulan', week:'Minggu', list:'Agenda' },
    events: {
      url:     <?= json_encode($calendarApiUrl) ?>,
      failure: () => { console.error('Gagal memuat events kalender'); }
    },
    eventClick: info => {
      window.location.href = <?= json_encode($meetingBaseUrl) ?> + info.event.id;
    },
    eventDidMount: info => {
      const loc = info.event.extendedProps.location || 'Lokasi belum diset';
      info.el.setAttribute('title', info.event.title + '\n\uD83D\uDCCD ' + loc);
    }
  });
  calendar.render();
});

const _deptChildrenUrl = <?= json_encode($deptChildrenUrl) ?>;

async function fetchDeptChildren(parentId) {
  try {
    const res = await fetch(_deptChildrenUrl + '?parent_id=' + parentId);
    return await res.json();
  } catch(e) { return []; }
}

function syncMtgHidden() {
  const v3 = document.getElementById('mtg-u3').value;
  const v2 = document.getElementById('mtg-u2').value;
  const v1 = document.getElementById('mtg-u1').value;
  document.getElementById('mtg-dept-id').value = v3 || v2 || v1 || '';
}

async function cascadeMtg(level) {
  const s1 = document.getElementById('mtg-u1');
  const s2 = document.getElementById('mtg-u2');
  const s3 = document.getElementById('mtg-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">-- Pilih unit dulu --</option>';
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s2.disabled = s3.disabled = true;
    syncMtgHidden();
    if (!s1.value) return;
    const kids = await fetchDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">-- Semua Bidang --</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s2.disabled = false;
    }
    syncMtgHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">-- Opsional --</option>';
    s3.disabled = true;
    syncMtgHidden();
    if (!s2.value) return;
    const kids = await fetchDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">-- Semua Sub Bidang --</option>' +
        kids.map(d => `<option value="${d.id}">${d.name}</option>`).join('');
      s3.disabled = false;
    }
    syncMtgHidden();
  } else {
    syncMtgHidden();
  }
}

function confirmDeleteMeeting(id, title) {
  document.getElementById('deleteMeetingTitle').textContent = title;
  document.getElementById('formDeleteMeeting').action =
    <?= json_encode($baseUrl) ?> + '/meetings/' + id + '/delete';
  const modal = new bootstrap.Modal(document.getElementById('modalDeleteMeeting'));
  modal.show();
}
</script>
