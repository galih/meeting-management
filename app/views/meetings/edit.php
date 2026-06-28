<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];

// ── Resolve departemen chain ───────────────────────────────────
$selDeptId = (int)($meeting['department_id'] ?? 0);
$selDept   = $selDeptId
  ? Database::queryOne('SELECT id, name, level, parent_id FROM departments WHERE id = ?', [$selDeptId])
  : null;

$sel = [1 => 0, 2 => 0, 3 => 0];
if ($selDept) {
  $sel[$selDept['level']] = (int)$selDept['id'];
  if ($selDept['level'] > 1) {
    $par = Database::queryOne('SELECT id, level, parent_id FROM departments WHERE id = ?', [$selDept['parent_id']]);
    if ($par) {
      $sel[$par['level']] = (int)$par['id'];
      if ($par['level'] > 1) {
        $par2 = Database::queryOne('SELECT id, level FROM departments WHERE id = ?', [$par['parent_id']]);
        if ($par2) $sel[$par2['level']] = (int)$par2['id'];
      }
    }
  }
}

$deptByParent = [];
foreach (($departments ?? []) as $d) {
  $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
}

$colorPresets = ['#7B1C1C','#5E1212','#C9A84C','#A8882E','#2F6BC4','#1a7340','#7d3cb5','#6b6b6b'];
$currentColor = strtolower(trim($meeting['color'] ?? '#7b1c1c'));

$participantIds = array_map('intval', $participantIds ?? []);
$allUsers       = $allUsers ?? [];
$avPalette      = ['#7B1C1C','#2F6BC4','#1a7340','#7d3cb5','#C9A84C','#0d7a8a'];

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<!-- ══ FLASH TOAST ══════════════════════════════════════════════════ -->
<?php if ($flashError): ?>
<div class="mi-toast mi-toast-err" id="edFlashToast">
  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button class="mi-toast-close" onclick="this.closest('.mi-toast').remove()">×</button>
</div>
<?php endif; ?>

<!-- ══ HERO ══════════════════════════════════════════════════════════ -->
<div class="mi-hero ed-hero" id="edHero" style="--mc:<?= htmlspecialchars($currentColor) ?>">
  <div class="mi-hero-left">
    <div class="mi-hero-icon">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </div>
    <div>
      <nav class="ed-breadcrumb" aria-label="Breadcrumb">
        <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>"><?= htmlspecialchars($meeting['title']) ?></a>
        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        <span>Edit</span>
      </nav>
      <h1 class="mi-hero-title">Edit Kegiatan</h1>
      <p class="mi-hero-sub">Perbarui informasi, peserta, dan tampilan kegiatan</p>
    </div>
  </div>
  <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-back-btn">
    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Detail
  </a>
</div>

<!-- ══ FORM CARD ═════════════════════════════════════════════════════ -->
<div class="mi-panel ed-card">
  <form method="POST"
        action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/update"
        id="editMeetingForm"
        novalidate>
    <?= Auth::csrfField() ?>

    <!-- ── Section 1: Informasi Dasar ──────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-section-header">
        <div class="ed-section-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="ed-section-label">Informasi Dasar</span>
      </div>

      <div class="ed-grid">
        <!-- Judul -->
        <div class="ed-field ed-field-full">
          <label class="ed-lbl ed-req" for="fTitle">Judul Kegiatan</label>
          <input type="text" id="fTitle" name="title" class="ed-input" maxlength="255"
                 autocomplete="off" required
                 value="<?= htmlspecialchars($meeting['title']) ?>"
                 placeholder="Contoh: Rapat Evaluasi Bulanan Q2">
          <div class="ed-invalid">Judul kegiatan wajib diisi.</div>
        </div>

        <!-- Mulai -->
        <div class="ed-field">
          <label class="ed-lbl ed-req" for="fStart">Tanggal &amp; Jam Mulai</label>
          <div class="ed-input-icon-wrap">
            <svg class="ed-input-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fStart" name="start_datetime" class="ed-input ed-input-with-ico" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
          </div>
          <div class="ed-invalid">Waktu mulai wajib diisi.</div>
        </div>

        <!-- Selesai -->
        <div class="ed-field">
          <label class="ed-lbl ed-req" for="fEnd">Tanggal &amp; Jam Selesai</label>
          <div class="ed-input-icon-wrap">
            <svg class="ed-input-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fEnd" name="end_datetime" class="ed-input ed-input-with-ico" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
          </div>
          <div class="ed-invalid">Waktu selesai wajib diisi &amp; harus setelah waktu mulai.</div>
        </div>

        <!-- Status -->
        <div class="ed-field">
          <label class="ed-lbl ed-req" for="fStatus">Status Kegiatan</label>
          <div class="ed-input-icon-wrap">
            <svg class="ed-input-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <select id="fStatus" name="status" class="ed-input ed-input-with-ico ed-select" required>
              <?php foreach ($statusLabel as $val => $lbl): ?>
              <option value="<?= $val ?>" <?= ($meeting['status'] ?? 'scheduled') === $val ? 'selected' : '' ?>>
                <?= htmlspecialchars($lbl) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="ed-invalid">Status wajib dipilih.</div>
        </div>

        <!-- Lokasi -->
        <div class="ed-field">
          <label class="ed-lbl" for="fLocation">Lokasi / Link Video</label>
          <div class="ed-input-icon-wrap">
            <svg class="ed-input-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <input type="text" id="fLocation" name="location" class="ed-input ed-input-with-ico"
                   placeholder="Ruang Rapat A  atau  https://meet.google.com/…"
                   value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
          </div>
          <div class="ed-hint">Jika berupa URL, akan ditampilkan sebagai tautan di halaman detail.</div>
        </div>

        <!-- Deskripsi -->
        <div class="ed-field ed-field-full">
          <label class="ed-lbl" for="fDesc">Deskripsi / Agenda</label>
          <textarea id="fDesc" name="description" class="ed-input ed-textarea" rows="4"
                    placeholder="Tulis poin-poin agenda kegiatan…"><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="ed-divider"></div>

    <!-- ── Section 2: Unit Kerja ────────────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-section-header">
        <div class="ed-section-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <span class="ed-section-label">Unit Kerja <span class="ed-opt">(opsional)</span></span>
      </div>

      <div class="ed-grid ed-grid-3">
        <div class="ed-field">
          <label class="ed-lbl" for="fU1">Unit Kerja</label>
          <select id="fU1" name="_u1" class="ed-input ed-select" onchange="edCascade(1)">
            <option value="">&mdash; Semua Unit Kerja &mdash;</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[1] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ed-field">
          <label class="ed-lbl" for="fU2">Bidang / Bagian</label>
          <select id="fU2" name="_u2" class="ed-input ed-select" onchange="edCascade(2)"
                  <?= $sel[1] ? '' : 'disabled' ?>>
            <option value="">&mdash; Semua Bidang &mdash;</option>
            <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[2] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="ed-field">
          <label class="ed-lbl" for="fU3">Sub Bidang <span class="ed-opt">(opsional)</span></label>
          <select id="fU3" name="_u3" class="ed-input ed-select" onchange="edCascade(3)"
                  <?= $sel[2] ? '' : 'disabled' ?>>
            <option value="">&mdash; Opsional &mdash;</option>
            <?php foreach ($deptByParent[$sel[2]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[3] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <input type="hidden" id="fDeptId" name="department_id" value="<?= $selDeptId ?: '' ?>">
    </div>

    <div class="ed-divider"></div>

    <!-- ── Section 3: Warna Kalender ───────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-section-header">
        <div class="ed-section-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.47-1.125"/><path d="M20 12c0-4.5-4-8-8-8"/></svg>
        </div>
        <span class="ed-section-label">Warna Kalender</span>
      </div>

      <div class="ed-color-row">
        <?php foreach ($colorPresets as $hex):
          $isActive = strtolower($hex) === $currentColor;
        ?>
        <button type="button"
                class="mi-color-preset ed-swatch<?= $isActive ? ' mi-active' : '' ?>"
                style="background:<?= $hex ?>"
                data-color="<?= $hex ?>"
                onclick="edPickColor('<?= $hex ?>')"
                aria-label="Pilih warna <?= $hex ?>"
                title="<?= $hex ?>"></button>
        <?php endforeach; ?>
        <label class="mi-color-preset ed-swatch-custom" title="Warna kustom">
          <input type="color" id="fColorPicker"
                 value="<?= htmlspecialchars($currentColor) ?>"
                 onchange="edPickColor(this.value)"
                 aria-label="Pilih warna kustom">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </label>
      </div>
      <input type="hidden" id="fColor" name="color" value="<?= htmlspecialchars($currentColor) ?>">
      <div class="ed-color-preview">
        <span class="ed-color-dot" id="edColorDot" style="background:<?= htmlspecialchars($currentColor) ?>"></span>
        <span class="ed-color-hex" id="edColorHex"><?= htmlspecialchars($currentColor) ?></span>
        <span class="ed-color-note">· Warna yang dipilih tampil pada kalender kegiatan</span>
      </div>
    </div>

    <div class="ed-divider"></div>

    <!-- ── Section 4: Peserta ───────────────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-section-header">
        <div class="ed-section-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <span class="ed-section-label">Pilih Peserta</span>
        <span class="mi-badge-peserta" id="fPCountBadge" style="margin-left:.5rem">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          <span id="fPCountNum"><?= count($participantIds) ?></span> dipilih
        </span>
      </div>

      <div class="ed-p-wrap">
        <div class="ed-p-search">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" id="fPSearch" placeholder="Cari nama peserta…" autocomplete="off" aria-label="Cari peserta">
          <button type="button" class="ed-p-search-clear" id="fPSearchClear" title="Hapus pencarian">×</button>
        </div>
        <div class="ed-p-list" id="fPList" role="group" aria-label="Daftar peserta">
          <?php foreach ($allUsers as $u):
            $avBg = $avPalette[abs(crc32($u['name'])) % count($avPalette)];
          ?>
          <label class="ed-p-item">
            <input type="checkbox" name="participants[]" value="<?= (int)$u['id'] ?>"
                   <?= in_array((int)$u['id'], $participantIds) ? 'checked' : '' ?>>
            <span class="ed-pav" style="background:<?= $avBg ?>">
              <?= strtoupper(mb_substr($u['name'], 0, 1)) ?>
            </span>
            <span class="ed-p-name"><?= htmlspecialchars($u['name']) ?></span>
            <?php if (!empty($u['dept_name'])): ?>
            <span class="ed-p-dept"><?= htmlspecialchars($u['dept_name']) ?></span>
            <?php endif; ?>
          </label>
          <?php endforeach; ?>
        </div>
        <div class="ed-p-foot" aria-live="polite">
          <span id="fPCountLabel"><?= count($participantIds) ?> peserta dipilih</span>
          <button type="button" class="ed-p-deselect" id="fPDeselect">Hapus semua</button>
        </div>
      </div>
    </div>

    <!-- ── Form Footer ──────────────────────────────────────────────── -->
    <div class="mi-mc-footer ed-footer">
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="mi-mc-btn-cancel">Batal</a>
      <button type="submit" class="mi-mc-btn-submit" id="edSubmitBtn">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Perubahan
      </button>
    </div>

  </form>
</div>

<?php $childrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
(function () {
  'use strict';

  /* ── Auto-dismiss toast ── */
  var toast = document.getElementById('edFlashToast');
  if (toast) {
    setTimeout(function () { toast.style.opacity = '0'; toast.style.transition = 'opacity .4s'; }, 4000);
    setTimeout(function () { if (toast.parentNode) toast.remove(); }, 4500);
  }

  var CHILD_URL = <?= json_encode($childrenUrl) ?>;

  /* ── Cascade dept ── */
  function fetchKids(parentId) {
    return fetch(CHILD_URL + '?parent_id=' + encodeURIComponent(parentId))
      .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .catch(function () { return []; });
  }
  function syncDept() {
    var v3 = document.getElementById('fU3').value;
    var v2 = document.getElementById('fU2').value;
    var v1 = document.getElementById('fU1').value;
    document.getElementById('fDeptId').value = v3 || v2 || v1 || '';
  }
  function buildOptions(sel, items, placeholder) {
    sel.innerHTML = '<option value="">' + placeholder + '</option>';
    items.forEach(function (d) {
      var o = document.createElement('option');
      o.value = d.id; o.textContent = d.name; sel.appendChild(o);
    });
  }
  window.edCascade = function (level) {
    var s1 = document.getElementById('fU1');
    var s2 = document.getElementById('fU2');
    var s3 = document.getElementById('fU3');
    if (level === 1) {
      buildOptions(s2, [], '\u2014 Semua Bidang \u2014');
      buildOptions(s3, [], '\u2014 Opsional \u2014');
      s2.disabled = s3.disabled = true; syncDept();
      if (!s1.value) return;
      fetchKids(s1.value).then(function (kids) {
        if (kids.length) { buildOptions(s2, kids, '\u2014 Semua Bidang \u2014'); s2.disabled = false; }
        syncDept();
      });
    } else if (level === 2) {
      buildOptions(s3, [], '\u2014 Opsional \u2014');
      s3.disabled = true; syncDept();
      if (!s2.value) return;
      fetchKids(s2.value).then(function (kids) {
        if (kids.length) { buildOptions(s3, kids, '\u2014 Opsional \u2014'); s3.disabled = false; }
        syncDept();
      });
    } else { syncDept(); }
  };

  /* ── Color picker ── */
  window.edPickColor = function (hex) {
    document.getElementById('fColor').value       = hex;
    document.getElementById('fColorPicker').value = hex;
    document.getElementById('edColorDot').style.background = hex;
    document.getElementById('edColorHex').textContent      = hex;
    document.querySelectorAll('.ed-swatch[data-color]').forEach(function (s) {
      s.classList.toggle('mi-active', s.dataset.color.toLowerCase() === hex.toLowerCase());
    });
    document.getElementById('edHero').style.setProperty('--mc', hex);
  };

  /* ── Participant search & count ── */
  var pSearch  = document.getElementById('fPSearch');
  var pClear   = document.getElementById('fPSearchClear');
  var pList    = document.getElementById('fPList');
  var pCountN  = document.getElementById('fPCountNum');
  var pCountL  = document.getElementById('fPCountLabel');
  var pDesel   = document.getElementById('fPDeselect');

  function updateCount() {
    var n = pList ? pList.querySelectorAll('input[type=checkbox]:checked').length : 0;
    if (pCountN) pCountN.textContent = n;
    if (pCountL) pCountL.textContent = n + ' peserta dipilih';
  }

  if (pSearch && pList) {
    pSearch.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      if (pClear) pClear.style.display = q ? 'block' : 'none';
      pList.querySelectorAll('.ed-p-item').forEach(function (item) {
        var nm = item.querySelector('.ed-p-name');
        item.style.display = (!q || (nm && nm.textContent.toLowerCase().includes(q))) ? '' : 'none';
      });
    });
    pList.addEventListener('change', function (e) {
      if (e.target.type === 'checkbox') updateCount();
    });
  }
  if (pClear) {
    pClear.addEventListener('click', function () {
      pSearch.value = ''; pClear.style.display = 'none';
      pList.querySelectorAll('.ed-p-item').forEach(function (i) { i.style.display = ''; });
    });
  }
  if (pDesel) {
    pDesel.addEventListener('click', function () {
      pList.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
      updateCount();
    });
  }

  /* ── Form validation ── */
  var form = document.getElementById('editMeetingForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      var startEl = document.getElementById('fStart');
      var endEl   = document.getElementById('fEnd');
      var valid   = true;
      form.querySelectorAll('.ed-input-invalid').forEach(function (el) { el.classList.remove('ed-input-invalid'); });
      form.querySelectorAll('[required]').forEach(function (el) {
        if (!el.value.trim()) { el.classList.add('ed-input-invalid'); valid = false; }
      });
      if (startEl && endEl && startEl.value && endEl.value && endEl.value <= startEl.value) {
        endEl.classList.add('ed-input-invalid'); valid = false;
      }
      if (!valid) {
        e.preventDefault();
        var first = form.querySelector('.ed-input-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }
      var btn = document.getElementById('edSubmitBtn');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="ed-spinner"></span>Menyimpan\u2026';
      }
    });
    form.querySelectorAll('.ed-input').forEach(function (el) {
      el.addEventListener('input', function () { el.classList.remove('ed-input-invalid'); });
      el.addEventListener('change', function () { el.classList.remove('ed-input-invalid'); });
    });
  }

  /* ── Auto-suggest end = start + 1h ── */
  var s = document.getElementById('fStart');
  var e = document.getElementById('fEnd');
  if (s && e) {
    s.addEventListener('change', function () {
      if (!e.value || e.value <= s.value) {
        var d = new Date(s.value);
        if (!isNaN(d.getTime())) {
          d.setHours(d.getHours() + 1);
          var pad = function (n) { return String(n).padStart(2, '0'); };
          e.value = d.getFullYear() + '-'
            + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
          e.classList.remove('ed-input-invalid');
        }
      }
    });
  }

}());
</script>

<style>
/* ================================================================
   EDIT.PHP — palet selaras index.php
   Primary : #7B1C1C  (maroon)
   Gold    : #C9A84C
   Bg warm : #faf6ef / #faf4eb / #f5f0ea
   Border  : #ede8e0 / #e0d8cc
================================================================ */

/* ── Toast (sama persis index.php) ── */
.mi-toast {
  position: fixed; top: 1.25rem; right: 1.25rem; z-index: 9999;
  display: flex; align-items: center; gap: .6rem;
  padding: .7rem 1rem; border-radius: 10px;
  font-size: 13.5px; font-weight: 500;
  box-shadow: 0 4px 20px rgba(0,0,0,.14);
  animation: miSlideIn .25s ease;
  max-width: 360px;
}
@keyframes miSlideIn { from { opacity:0; transform: translateY(-8px) } to { opacity:1; transform:none } }
.mi-toast-err { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.mi-toast-close { background: none; border: none; font-size: 16px; cursor: pointer; margin-left: .25rem; opacity:.6; line-height:1; padding:0; }
.mi-toast-close:hover { opacity:1; }

/* ── Hero (extends .mi-hero from index.php) ── */
.ed-hero {
  background: linear-gradient(135deg, var(--mc, #7B1C1C) 0%, #9B2020 55%, #A83218 100%);
  margin-bottom: 1.25rem;
}
.ed-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .25rem;
}
.ed-breadcrumb a { color: rgba(255,255,255,.80); text-decoration: none; }
.ed-breadcrumb a:hover { color: #fff; text-decoration: underline; }
.ed-back-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600;
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3);
  color: #fff; padding: .45rem .95rem; border-radius: 9px;
  text-decoration: none; transition: background .18s; white-space: nowrap;
  flex-shrink: 0;
}
.ed-back-btn:hover { background: rgba(255,255,255,.26); color: #fff; }

/* ── Form card (extends .mi-panel) ── */
.ed-card { border-radius: 14px; }

/* ── Section ── */
.ed-section { padding: 1.4rem 1.5rem; }
.ed-section-header {
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: .9rem; padding-bottom: .55rem;
  border-bottom: 1px solid #ede8e0;
}
.ed-section-icon {
  width: 26px; height: 26px; border-radius: 7px;
  background: rgba(123,28,28,.09);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #7B1C1C;
}
.ed-section-label {
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: #7B1C1C;
}
.ed-opt { font-weight: 500; text-transform: none; letter-spacing: 0; color: #8c7a6b; font-size: 11px; }
.ed-divider { height: 1px; background: #ede8e0; }

/* ── Grid ── */
.ed-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .85rem; }
.ed-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
.ed-field-full { grid-column: span 2; }
.ed-field { display: flex; flex-direction: column; gap: .3rem; }

/* ── Labels ── */
.ed-lbl { font-size: 12.5px; font-weight: 600; color: #2c1a1a; }
.ed-req::after { content: ' *'; color: #a82515; }
.ed-hint { font-size: 12px; color: #8c7a6b; margin-top: .2rem; }

/* ── Inputs ── */
.ed-input {
  border: 1.5px solid #ddd; border-radius: 8px;
  padding: .42rem .75rem; font-size: 13.5px;
  background: #fff; color: #2c1a1a; outline: none;
  transition: border-color .15s, box-shadow .15s;
  font-family: inherit; width: 100%;
}
.ed-input:focus {
  border-color: #7B1C1C;
  box-shadow: 0 0 0 3px rgba(123,28,28,.10);
}
.ed-input.ed-input-invalid {
  border-color: #a82515;
  box-shadow: 0 0 0 3px rgba(168,37,21,.10);
}
.ed-input:disabled { background: #f5f0ea; color: #8c7a6b; cursor: not-allowed; }
.ed-textarea { resize: vertical; min-height: 90px; }
.ed-select { cursor: pointer; }

.ed-input-icon-wrap { position: relative; }
.ed-input-ico {
  position: absolute; left: .65rem; top: 50%; transform: translateY(-50%);
  color: #8c7a6b; pointer-events: none;
}
.ed-input-with-ico { padding-left: 2rem; }

.ed-invalid { font-size: 12px; color: #a82515; display: none; margin-top: .15rem; }
.ed-input-invalid ~ .ed-invalid { display: block; }

/* ── Color swatches ── */
.ed-color-row { display: flex; flex-wrap: wrap; align-items: center; gap: .45rem; margin-bottom: .5rem; }
.ed-swatch {
  width: 30px; height: 30px; border-radius: 50%;
  border: 2.5px solid transparent; outline: none; cursor: pointer;
  transition: transform .15s, box-shadow .15s;
  flex-shrink: 0; padding: 0;
}
.ed-swatch:hover { transform: scale(1.15); }
.mi-active.ed-swatch {
  box-shadow: 0 0 0 2px #fff, 0 0 0 4.5px currentColor;
  transform: scale(1.1);
}
.ed-swatch-custom {
  display: flex; align-items: center; justify-content: center;
  background: #faf4eb; border: 1.5px dashed #ddd; color: #8c7a6b;
  cursor: pointer; overflow: hidden; position: relative;
  width: 30px; height: 30px; border-radius: 50%;
  transition: border-color .15s, color .15s;
}
.ed-swatch-custom:hover { border-color: #7B1C1C; color: #7B1C1C; }
.ed-swatch-custom input[type=color] { position: absolute; width: 1px; height: 1px; opacity: 0; pointer-events: none; }
.ed-color-preview { display: flex; align-items: center; gap: .45rem; margin-top: .35rem; }
.ed-color-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; border: 1px solid #ddd; flex-shrink: 0; }
.ed-color-hex { font-size: 12px; color: #8c7a6b; font-family: monospace; }
.ed-color-note { font-size: 11.5px; color: #8c7a6b; }

/* ── Participant box ── */
.ed-p-wrap { border: 1.5px solid #ddd; border-radius: 10px; overflow: hidden; background: #fff; }
.ed-p-search {
  display: flex; align-items: center; gap: .45rem;
  padding: .5rem .85rem; border-bottom: 1px solid #ede8e0; background: #faf4eb;
  position: relative;
}
.ed-p-search svg { flex-shrink: 0; color: #8c7a6b; }
.ed-p-search input {
  border: none; background: none; outline: none;
  font-size: 13px; width: 100%; color: #2c1a1a; font-family: inherit;
}
.ed-p-search-clear {
  background: none; border: none; font-size: 15px; cursor: pointer;
  color: #8c7a6b; opacity: .6; line-height: 1; padding: 0; display: none;
}
.ed-p-search-clear:hover { opacity: 1; }

.ed-p-list { max-height: 230px; overflow-y: auto; padding: .3rem 0; }
.ed-p-item {
  display: flex; align-items: center; gap: .5rem;
  padding: .4rem .85rem; cursor: pointer; transition: background .12s;
  font-size: 13px;
}
.ed-p-item:hover { background: #faf6ef; }
.ed-p-item input[type=checkbox] {
  width: 15px; height: 15px; flex-shrink: 0;
  cursor: pointer; accent-color: #7B1C1C;
}
.ed-p-item:has(input:checked) { background: rgba(123,28,28,.05); }
.ed-p-item:has(input:checked) .ed-p-name { color: #7B1C1C; font-weight: 600; }
.ed-pav {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px; border-radius: 50%;
  font-size: 11px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.ed-p-name { font-size: 13px; font-weight: 500; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ed-p-dept { font-size: 11px; color: #8c7a6b; white-space: nowrap; }
.ed-p-foot {
  display: flex; justify-content: space-between; align-items: center;
  padding: .38rem .85rem; background: #faf4eb;
  border-top: 1px solid #ede8e0; font-size: 12px; font-weight: 600; color: #8c7a6b;
}
.ed-p-deselect {
  background: none; border: none; font-size: 12px; color: #a82515;
  cursor: pointer; padding: 0; font-family: inherit; font-weight: 600;
}
.ed-p-deselect:hover { color: #7B1C1C; text-decoration: underline; }

/* ── Form footer ── */
.ed-footer {
  background: #faf6ef !important;
}

/* ── Submit spinner ── */
.ed-spinner {
  display: inline-block; width: 12px; height: 12px;
  border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
  border-radius: 50%; animation: edSpin .6s linear infinite;
  margin-right: .35rem;
}
@keyframes edSpin { to { transform: rotate(360deg); } }

/* ── Responsive ── */
@media (max-width: 991.98px) {
  .ed-grid-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767.98px) {
  .mi-hero.ed-hero { flex-wrap: wrap; }
  .ed-back-btn { width: 100%; justify-content: center; }
  .ed-section { padding: 1rem; }
  .ed-grid { grid-template-columns: 1fr; }
  .ed-field-full { grid-column: span 1; }
  .ed-grid-3 { grid-template-columns: 1fr; }
  .ed-p-list { max-height: 170px; }
}
</style>
