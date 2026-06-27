<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Sedang Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];

// Hitung chain departemen yang dipilih
$selDeptId = (int)($meeting['department_id'] ?? 0);
$selDept   = $selDeptId ? Database::queryOne(
  'SELECT id, name, level, parent_id FROM departments WHERE id=?', [$selDeptId]
) : null;
$sel = [1 => 0, 2 => 0, 3 => 0];
if ($selDept) {
  $sel[$selDept['level']] = $selDept['id'];
  if ($selDept['level'] > 1) {
    $par = Database::queryOne('SELECT id,level,parent_id FROM departments WHERE id=?', [$selDept['parent_id']]);
    if ($par) {
      $sel[$par['level']] = $par['id'];
      if ($par['level'] > 1) {
        $par2 = Database::queryOne('SELECT id,level FROM departments WHERE id=?', [$par['parent_id']]);
        if ($par2) $sel[$par2['level']] = $par2['id'];
      }
    }
  }
}
$deptByParent = [];
foreach ($departments as $d) {
  $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
}

// Color presets
$colorPresets = ['#7B1C1C','#2F6BC4','#1a7340','#7d3cb5','#C9A84C','#0d7a8a','#b5530a','#6b6b6b'];
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="ed-alert-err">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button onclick="this.parentElement.remove()" class="ed-alert-close">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<!-- ================================================================
     HERO HEADER
================================================================ -->
<div class="ed-hero mb-4">
  <div class="ed-hero-body">
    <nav class="ed-breadcrumb">
      <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>"><?= htmlspecialchars($meeting['title']) ?></a>
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Edit</span>
    </nav>
    <div class="ed-hero-row">
      <h1 class="ed-hero-title">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Kegiatan
      </h1>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-back-btn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke Detail
      </a>
    </div>
  </div>
</div>

<!-- ================================================================
     FORM CARD
================================================================ -->
<div class="ed-card">
  <form method="POST" action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/update"
        id="editMeetingForm" novalidate>
    <?= Auth::csrfField() ?>

    <!-- ── Section: Informasi Dasar ── -->
    <div class="ed-section">
      <div class="ed-section-title">Informasi Dasar</div>
      <div class="row g-3">

        <div class="col-12">
          <label class="ed-label required" for="edit-title">Judul Kegiatan</label>
          <input type="text" id="edit-title" name="title" class="form-control" required
                 maxlength="255" autocomplete="off"
                 value="<?= htmlspecialchars($meeting['title']) ?>">
          <div class="invalid-feedback">Judul kegiatan wajib diisi.</div>
        </div>

        <div class="col-md-6">
          <label class="ed-label required" for="edit-start">Tanggal & Jam Mulai</label>
          <input type="datetime-local" id="edit-start" name="start_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
          <div class="invalid-feedback">Waktu mulai wajib diisi.</div>
        </div>

        <div class="col-md-6">
          <label class="ed-label required" for="edit-end">Tanggal & Jam Selesai</label>
          <input type="datetime-local" id="edit-end" name="end_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
          <div class="invalid-feedback">Waktu selesai wajib diisi & harus setelah waktu mulai.</div>
        </div>

        <div class="col-12">
          <label class="ed-label" for="edit-location">Lokasi / Link Video</label>
          <input type="text" id="edit-location" name="location" class="form-control"
                 placeholder="Ruang Rapat A, atau https://meet.google.com/..."
                 value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
        </div>

        <div class="col-12">
          <label class="ed-label" for="edit-desc">Deskripsi / Agenda</label>
          <textarea id="edit-desc" name="description" class="form-control" rows="4"
                    placeholder="Tulis agenda kegiatan…"><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <div class="ed-divider"></div>

    <!-- ── Section: Unit Kerja ── -->
    <div class="ed-section">
      <div class="ed-section-title">Unit Kerja</div>
      <div class="row g-2">
        <div class="col-md-4">
          <label class="ed-label-sm" for="edit-u1">Unit Kerja</label>
          <select id="edit-u1" name="_u1" class="form-select" onchange="edCascade(1)">
            <option value="">— Semua Unit Kerja —</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[1] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="ed-label-sm" for="edit-u2">Bidang / Bagian</label>
          <select id="edit-u2" name="_u2" class="form-select" onchange="edCascade(2)"
                  <?= $sel[1] ? '' : 'disabled' ?>>
            <option value="">— Semua Bidang —</option>
            <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[2] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="ed-label-sm" for="edit-u3">Sub Bidang <span class="ed-optional">(opsional)</span></label>
          <select id="edit-u3" name="_u3" class="form-select" onchange="edCascade(3)"
                  <?= $sel[2] ? '' : 'disabled' ?>>
            <option value="">— Opsional —</option>
            <?php foreach ($deptByParent[$sel[2]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[3] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <input type="hidden" id="edit-dept-id" name="department_id"
             value="<?= $selDeptId ?: '' ?>">
    </div>

    <div class="ed-divider"></div>

    <!-- ── Section: Warna & Peserta ── -->
    <div class="ed-section">
      <div class="row g-3">

        <div class="col-md-4">
          <label class="ed-label">Warna Kalender</label>
          <div class="ed-color-row">
            <?php
            $currentColor = strtolower($meeting['color'] ?? '#7B1C1C');
            foreach ($colorPresets as $hex):
              $active = strtolower($hex) === $currentColor ? 'active' : '';
            ?>
            <button type="button" class="ed-color-swatch <?= $active ?>"
                    style="background:<?= $hex ?>"
                    data-color="<?= $hex ?>"
                    title="<?= $hex ?>"
                    onclick="edPickColor('<?= $hex ?>')"
                    aria-label="Pilih warna <?= $hex ?>"></button>
            <?php endforeach; ?>
            <label class="ed-color-custom" title="Pilih warna kustom">
              <input type="color" id="edit-color-picker" value="<?= htmlspecialchars($currentColor) ?>"
                     onchange="edPickColor(this.value)" aria-label="Pilih warna kustom">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93A10 10 0 0 0 4.93 19.07"/><path d="M12 2v2M12 20v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42"/></svg>
            </label>
          </div>
          <input type="hidden" id="edit-color" name="color" value="<?= htmlspecialchars($currentColor) ?>">
          <div class="ed-color-preview">
            <span class="ed-color-dot" id="edColorDot" style="background:<?= htmlspecialchars($currentColor) ?>"></span>
            <span id="edColorHex" style="font-size:12px;color:#666"><?= htmlspecialchars($currentColor) ?></span>
          </div>
        </div>

        <div class="col-md-8">
          <label class="ed-label">Peserta</label>
          <div class="ed-participant-wrap">
            <div class="ed-participant-search">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" id="edParticipantSearch" placeholder="Cari peserta…" autocomplete="off">
            </div>
            <div class="ed-participant-list" id="edParticipantList">
              <?php foreach ($allUsers as $u): ?>
              <label class="ed-participant-item">
                <input type="checkbox" name="participants[]" value="<?= (int)$u['id'] ?>"
                       <?= in_array($u['id'], $participantIds) ? 'checked' : '' ?>>
                <span class="ed-pav" style="background:<?= ['#7B1C1C','#2F6BC4','#1a7340','#7d3cb5','#C9A84C','#0d7a8a'][abs(crc32($u['name'])) % 6] ?>">
                  <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
                </span>
                <span class="ed-p-name"><?= htmlspecialchars($u['name']) ?></span>
                <?php if (!empty($u['dept_name'])): ?>
                <span class="ed-p-dept"><?= htmlspecialchars($u['dept_name']) ?></span>
                <?php endif; ?>
              </label>
              <?php endforeach; ?>
            </div>
            <div class="ed-participant-count" id="edPCount">
              <?= count($participantIds) ?> peserta dipilih
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- ── Footer ── -->
    <div class="ed-footer">
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-btn-cancel">Batal</a>
      <button type="submit" class="ed-btn-submit" id="edSubmitBtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Perubahan
      </button>
    </div>

  </form>
</div>

<!-- ================================================================
     JAVASCRIPT
================================================================ -->
<?php $deptChildrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
(function () {
  var _childUrl = <?= json_encode($deptChildrenUrl) ?>;

  // ── Cascade department dropdowns ────────────────────────────────
  async function fetchChildren(parentId) {
    try {
      var r = await fetch(_childUrl + '?parent_id=' + encodeURIComponent(parentId));
      if (!r.ok) return [];
      return await r.json();
    } catch (e) { return []; }
  }

  function syncDeptHidden() {
    var v3 = document.getElementById('edit-u3').value;
    var v2 = document.getElementById('edit-u2').value;
    var v1 = document.getElementById('edit-u1').value;
    document.getElementById('edit-dept-id').value = v3 || v2 || v1 || '';
  }

  window.edCascade = async function (level) {
    var s1 = document.getElementById('edit-u1');
    var s2 = document.getElementById('edit-u2');
    var s3 = document.getElementById('edit-u3');

    if (level === 1) {
      s2.innerHTML = '<option value="">— Semua Bidang —</option>';
      s3.innerHTML = '<option value="">— Opsional —</option>';
      s2.disabled = true;
      s3.disabled = true;
      syncDeptHidden();
      if (!s1.value) return;
      var kids = await fetchChildren(s1.value);
      if (kids.length) {
        kids.forEach(function (d) {
          var o = document.createElement('option');
          o.value = d.id; o.textContent = d.name;
          s2.appendChild(o);
        });
        s2.disabled = false;
      }
      syncDeptHidden();
    } else if (level === 2) {
      s3.innerHTML = '<option value="">— Opsional —</option>';
      s3.disabled = true;
      syncDeptHidden();
      if (!s2.value) return;
      var kids = await fetchChildren(s2.value);
      if (kids.length) {
        kids.forEach(function (d) {
          var o = document.createElement('option');
          o.value = d.id; o.textContent = d.name;
          s3.appendChild(o);
        });
        s3.disabled = false;
      }
      syncDeptHidden();
    } else {
      syncDeptHidden();
    }
  };

  // ── Color picker ────────────────────────────────────────────────
  window.edPickColor = function (hex) {
    document.getElementById('edit-color').value = hex;
    document.getElementById('edit-color-picker').value = hex;
    document.getElementById('edColorDot').style.background = hex;
    document.getElementById('edColorHex').textContent = hex;
    document.querySelectorAll('.ed-color-swatch').forEach(function (s) {
      s.classList.toggle('active', s.dataset.color.toLowerCase() === hex.toLowerCase());
    });
  };

  // ── Participant search ──────────────────────────────────────────
  var searchInput = document.getElementById('edParticipantSearch');
  var pList       = document.getElementById('edParticipantList');
  var pCount      = document.getElementById('edPCount');

  function updateCount() {
    var checked = pList.querySelectorAll('input[type=checkbox]:checked').length;
    pCount.textContent = checked + ' peserta dipilih';
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      pList.querySelectorAll('.ed-participant-item').forEach(function (item) {
        var name = item.querySelector('.ed-p-name');
        if (name) {
          item.style.display = (!q || name.textContent.toLowerCase().includes(q)) ? '' : 'none';
        }
      });
    });
  }

  if (pList) {
    pList.addEventListener('change', function (e) {
      if (e.target.type === 'checkbox') updateCount();
    });
  }

  // ── Form validation ─────────────────────────────────────────────
  var form = document.getElementById('editMeetingForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      var start = document.getElementById('edit-start');
      var end   = document.getElementById('edit-end');

      // Reset
      form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

      var valid = true;

      if (!form.checkValidity()) {
        form.querySelectorAll('[required]').forEach(function (el) {
          if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
        });
      }

      if (start.value && end.value && end.value <= start.value) {
        end.classList.add('is-invalid');
        valid = false;
      }

      if (!valid) {
        e.preventDefault();
        var firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      var btn = document.getElementById('edSubmitBtn');
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan…';
    });
  }

  // ── Auto-sync start → end (+1 jam) ─────────────────────────────
  var startInput = document.getElementById('edit-start');
  var endInput   = document.getElementById('edit-end');
  if (startInput && endInput) {
    startInput.addEventListener('change', function () {
      if (!endInput.value || endInput.value <= startInput.value) {
        var d = new Date(startInput.value);
        if (!isNaN(d)) {
          d.setHours(d.getHours() + 1);
          var pad = function (n) { return String(n).padStart(2, '0'); };
          endInput.value = d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate())
                         + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
        }
      }
    });
  }
}());
</script>

<!-- ================================================================
     STYLES
================================================================ -->
<style>
/* ── Alert error ── */
.ed-alert-err {
  display: flex; align-items: center; gap: .5rem;
  background: #fee2e2; color: #b91c1c;
  border: 1px solid #fca5a5; border-radius: 8px;
  padding: .65rem 1rem; margin-bottom: 1rem;
  font-size: 13px; font-weight: 500;
}
.ed-alert-close {
  margin-left: auto; background: none; border: none;
  font-size: 18px; cursor: pointer; color: inherit; opacity: .7;
}
.ed-alert-close:hover { opacity: 1; }

/* ── Hero ── */
.ed-hero {
  background: linear-gradient(135deg, var(--brand,#7B1C1C) 0%, #9B2020 55%, #A83218 100%);
  border-radius: 14px; padding: 1.2rem 1.6rem;
  box-shadow: 0 4px 24px rgba(123,28,28,.22);
}
.ed-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .6rem;
}
.ed-breadcrumb a { color: rgba(255,255,255,.8); text-decoration: none; }
.ed-breadcrumb a:hover { color: #fff; text-decoration: underline; }
.ed-hero-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem; }
.ed-hero-title {
  display: flex; align-items: center; gap: .5rem;
  font-size: clamp(15px,2.2vw,21px); font-weight: 800;
  color: #fff; margin: 0;
}
.ed-back-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600;
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3);
  color: #fff; padding: .4rem .85rem; border-radius: 8px;
  text-decoration: none; transition: background .18s;
}
.ed-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

/* ── Form card ── */
.ed-card {
  background: #fff; border: 1px solid #e8e3db;
  border-radius: 12px; overflow: hidden;
  box-shadow: 0 1px 6px rgba(0,0,0,.05);
}

/* ── Sections ── */
.ed-section { padding: 1.4rem 1.6rem; }
.ed-section-title {
  font-size: 11px; font-weight: 800;
  text-transform: uppercase; letter-spacing: .08em;
  color: #888; margin-bottom: 1rem;
}
.ed-divider { height: 1px; background: #f0ece6; margin: 0; }

/* ── Labels ── */
.ed-label {
  display: block; font-size: 13px; font-weight: 600; color: #333; margin-bottom: .35rem;
}
.ed-label.required::after {
  content: ' *'; color: #b91c1c;
}
.ed-label-sm {
  display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: .3rem;
}
.ed-optional { font-weight: 400; color: #aaa; font-size: 11px; }

/* ── Color picker ── */
.ed-color-row { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem; margin-bottom: .5rem; }
.ed-color-swatch {
  width: 26px; height: 26px; border-radius: 50%;
  border: 2px solid transparent; cursor: pointer;
  transition: transform .15s, box-shadow .15s;
  flex-shrink: 0;
}
.ed-color-swatch:hover { transform: scale(1.15); }
.ed-color-swatch.active {
  box-shadow: 0 0 0 3px #fff, 0 0 0 5px currentColor;
  transform: scale(1.1);
}
.ed-color-custom {
  width: 26px; height: 26px; border-radius: 50%;
  border: 1.5px dashed #ccc; display: flex; align-items: center; justify-content: center;
  cursor: pointer; color: #888; overflow: hidden;
  transition: border-color .15s;
}
.ed-color-custom:hover { border-color: #888; color: #333; }
.ed-color-custom input[type=color] {
  position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none;
}
.ed-color-preview { display: flex; align-items: center; gap: .4rem; margin-top: .3rem; }
.ed-color-dot {
  display: inline-block; width: 14px; height: 14px;
  border-radius: 50%; border: 1px solid rgba(0,0,0,.12);
}

/* ── Participant box ── */
.ed-participant-wrap {
  border: 1px solid #d4cfc8; border-radius: 10px; overflow: hidden;
}
.ed-participant-search {
  display: flex; align-items: center; gap: .4rem;
  padding: .5rem .75rem;
  border-bottom: 1px solid #e8e3db;
  background: #fafaf8;
}
.ed-participant-search input {
  border: none; background: none; outline: none;
  font-size: 13px; width: 100%; color: #333;
}
.ed-participant-list {
  max-height: 220px; overflow-y: auto;
  padding: .4rem 0;
}
.ed-participant-item {
  display: flex; align-items: center; gap: .5rem;
  padding: .4rem .75rem; cursor: pointer;
  transition: background .12s;
}
.ed-participant-item:hover { background: #faf5ee; }
.ed-participant-item input[type=checkbox] { width: 15px; height: 15px; flex-shrink: 0; cursor: pointer; }
.ed-pav {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px; border-radius: 50%;
  font-size: 11px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.ed-p-name { font-size: 13px; font-weight: 500; flex: 1; }
.ed-p-dept { font-size: 11px; color: #999; white-space: nowrap; }
.ed-participant-count {
  padding: .35rem .75rem;
  font-size: 12px; font-weight: 600; color: #777;
  background: #fafaf8; border-top: 1px solid #e8e3db;
}

/* ── Footer ── */
.ed-footer {
  display: flex; justify-content: flex-end; align-items: center; gap: .75rem;
  padding: 1rem 1.6rem;
  border-top: 1px solid #f0ece6;
  background: #fafaf8;
}
.ed-btn-cancel {
  font-size: 13px; font-weight: 600; color: #777;
  text-decoration: none; padding: .45rem .9rem;
  border-radius: 8px; transition: color .15s, background .15s;
}
.ed-btn-cancel:hover { color: #333; background: #f0ece6; }
.ed-btn-submit {
  display: inline-flex; align-items: center; gap: .4rem;
  font-size: 13px; font-weight: 700;
  background: var(--brand,#7B1C1C); color: #fff;
  padding: .5rem 1.1rem; border: none; border-radius: 8px;
  cursor: pointer; transition: background .18s;
}
.ed-btn-submit:hover { background: #9B2020; }
.ed-btn-submit:disabled { opacity: .65; cursor: not-allowed; }

/* ── Responsive ── */
@media (max-width: 575px) {
  .ed-hero { padding: 1rem; }
  .ed-section { padding: 1rem; }
  .ed-footer { padding: .75rem 1rem; }
  .ed-participant-list { max-height: 160px; }
}
</style>
