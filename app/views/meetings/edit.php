<?php
$baseUrl = rtrim(BASE_URL, '/');

// ── Status labels ──────────────────────────────────────────────
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

// ── Group departments by parent_id ────────────────────────────
$deptByParent = [];
foreach (($departments ?? []) as $d) {
  $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
}

// ── Color presets (palet Kemenbud) ────────────────────────────
$colorPresets = ['#7B1C1C','#5E1212','#C9A84C','#A8882E','#2F6BC4','#1a7340','#7d3cb5','#6b6b6b'];
$currentColor = strtolower(trim($meeting['color'] ?? '#7b1c1c'));

// ── Participants ───────────────────────────────────────────────
$participantIds = array_map('intval', $participantIds ?? []);
$allUsers       = $allUsers ?? [];
$avPalette      = ['#7B1C1C','#2F6BC4','#1a7340','#7d3cb5','#C9A84C','#0d7a8a'];
?>

<?php if (!empty($_SESSION['flash_error'])): ?>
<div class="ed-alert" id="edAlertErr" role="alert" aria-live="polite">
  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <?= htmlspecialchars($_SESSION['flash_error']) ?>
  <button type="button" class="ed-alert-close" onclick="document.getElementById('edAlertErr').remove()" aria-label="Tutup">&times;</button>
</div>
<?php unset($_SESSION['flash_error']); endif; ?>

<?php /* ================================================================
   HERO
================================================================ */ ?>
<div class="ed-hero mb-4" style="--mc:<?= htmlspecialchars($currentColor) ?>">
  <div class="ed-hero-inner">
    <nav class="ed-bc" aria-label="Breadcrumb">
      <a href="<?= $baseUrl ?>/meetings">Kegiatan</a>
      <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>"><?= htmlspecialchars($meeting['title']) ?></a>
      <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Edit</span>
    </nav>
    <div class="ed-hero-row">
      <h1 class="ed-hero-title">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Edit Kegiatan
      </h1>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-back-btn">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke Detail
      </a>
    </div>
  </div>
</div>

<?php /* ================================================================
   FORM
================================================================ */ ?>
<div class="ed-card">
  <form method="POST"
        action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/update"
        id="editMeetingForm"
        novalidate>
    <?= Auth::csrfField() ?>

    <?php /* ── Section 1: Informasi Dasar ── */ ?>
    <div class="ed-section">
      <p class="ed-section-title">Informasi Dasar</p>
      <div class="row g-3">

        <div class="col-12">
          <label class="form-label required" for="fTitle">Judul Kegiatan</label>
          <input type="text" id="fTitle" name="title" class="form-control"
                 maxlength="255" autocomplete="off" required
                 value="<?= htmlspecialchars($meeting['title']) ?>">
          <div class="invalid-feedback">Judul kegiatan wajib diisi.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label required" for="fStart">Tanggal &amp; Jam Mulai</label>
          <input type="datetime-local" id="fStart" name="start_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
          <div class="invalid-feedback">Waktu mulai wajib diisi.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label required" for="fEnd">Tanggal &amp; Jam Selesai</label>
          <input type="datetime-local" id="fEnd" name="end_datetime" class="form-control" required
                 value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
          <div class="invalid-feedback">Waktu selesai wajib diisi &amp; harus setelah waktu mulai.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label required" for="fStatus">Status</label>
          <select id="fStatus" name="status" class="form-select" required>
            <?php foreach ($statusLabel as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= ($meeting['status'] ?? 'scheduled') === $val ? 'selected' : '' ?>>
              <?= htmlspecialchars($lbl) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <div class="invalid-feedback">Status wajib dipilih.</div>
        </div>

        <div class="col-md-6">
          <label class="form-label" for="fLocation">Lokasi / Link Video</label>
          <input type="text" id="fLocation" name="location" class="form-control"
                 placeholder="Ruang Rapat A, atau https://meet.google.com/&hellip;"
                 value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
          <div class="ed-hint">Jika berupa URL, akan ditampilkan sebagai tautan di halaman detail.</div>
        </div>

        <div class="col-12">
          <label class="form-label" for="fDesc">Deskripsi / Agenda</label>
          <textarea id="fDesc" name="description" class="form-control" rows="4"
                    placeholder="Tulis agenda kegiatan&hellip;"><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>

      </div>
    </div>

    <div class="ed-divider"></div>

    <?php /* ── Section 2: Unit Kerja ── */ ?>
    <div class="ed-section">
      <p class="ed-section-title">Unit Kerja</p>
      <div class="row g-2">
        <div class="col-md-4">
          <label class="form-label" for="fU1">Unit Kerja</label>
          <select id="fU1" name="_u1" class="form-select" onchange="edCascade(1)">
            <option value="">&mdash; Semua Unit Kerja &mdash;</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[1] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="fU2">Bidang / Bagian</label>
          <select id="fU2" name="_u2" class="form-select" onchange="edCascade(2)"
                  <?= $sel[1] ? '' : 'disabled' ?>>
            <option value="">&mdash; Semua Bidang &mdash;</option>
            <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[2] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label" for="fU3">Sub Bidang <span class="ed-optional">(opsional)</span></label>
          <select id="fU3" name="_u3" class="form-select" onchange="edCascade(3)"
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

    <?php /* ── Section 3: Warna & Peserta ── */ ?>
    <div class="ed-section">
      <div class="row g-4">

        <!-- Color picker -->
        <div class="col-md-4">
          <p class="ed-section-title" style="margin-bottom:.7rem">Warna Kalender</p>
          <div class="ed-color-row">
            <?php foreach ($colorPresets as $hex):
              $isActive = strtolower($hex) === $currentColor;
            ?>
            <button type="button"
                    class="ed-swatch<?= $isActive ? ' active' : '' ?>"
                    style="--sw:<?= $hex ?>;background:<?= $hex ?>"
                    data-color="<?= $hex ?>"
                    onclick="edPickColor('<?= $hex ?>')"
                    aria-label="Pilih warna <?= $hex ?>"
                    title="<?= $hex ?>"></button>
            <?php endforeach; ?>
            <label class="ed-swatch ed-swatch-custom" title="Warna kustom">
              <input type="color" id="fColorPicker"
                     value="<?= htmlspecialchars($currentColor) ?>"
                     onchange="edPickColor(this.value)"
                     aria-label="Pilih warna kustom">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r=".5" fill="currentColor"/><circle cx="17.5" cy="10.5" r=".5" fill="currentColor"/><circle cx="8.5" cy="7.5" r=".5" fill="currentColor"/><circle cx="6.5" cy="12.5" r=".5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.47-1.125"/><path d="M20 12c0-4.5-4-8-8-8"/></svg>
            </label>
          </div>
          <input type="hidden" id="fColor" name="color" value="<?= htmlspecialchars($currentColor) ?>">
          <div class="ed-color-preview">
            <span class="ed-color-dot" id="edColorDot" style="background:<?= htmlspecialchars($currentColor) ?>"></span>
            <span class="ed-color-hex" id="edColorHex"><?= htmlspecialchars($currentColor) ?></span>
          </div>
        </div>

        <!-- Participants -->
        <div class="col-md-8">
          <p class="ed-section-title" style="margin-bottom:.7rem">Peserta</p>
          <div class="ed-p-wrap">
            <div class="ed-p-search">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" id="fPSearch" placeholder="Cari nama peserta…" autocomplete="off" aria-label="Cari peserta">
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
            <div class="ed-p-count" aria-live="polite">
              <span id="fPCountNum"><?= count($participantIds) ?></span> peserta dipilih
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Footer -->
    <div class="ed-footer">
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="ed-btn-cancel">Batal</a>
      <button type="submit" class="ed-btn-submit" id="edSubmitBtn">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Perubahan
      </button>
    </div>

  </form>
</div>

<?php /* ================================================================
   JAVASCRIPT
================================================================ */ ?>
<?php $childrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
(function () {
  'use strict';

  var CHILD_URL = <?= json_encode($childrenUrl) ?>;

  /* ── Cascade dept dropdowns ── */
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
      o.value = d.id;
      o.textContent = d.name;
      sel.appendChild(o);
    });
  }

  window.edCascade = function (level) {
    var s1 = document.getElementById('fU1');
    var s2 = document.getElementById('fU2');
    var s3 = document.getElementById('fU3');

    if (level === 1) {
      buildOptions(s2, [], '\u2014 Semua Bidang \u2014');
      buildOptions(s3, [], '\u2014 Opsional \u2014');
      s2.disabled = true;
      s3.disabled = true;
      syncDept();
      if (!s1.value) return;
      fetchKids(s1.value).then(function (kids) {
        if (kids.length) { buildOptions(s2, kids, '\u2014 Semua Bidang \u2014'); s2.disabled = false; }
        syncDept();
      });
    } else if (level === 2) {
      buildOptions(s3, [], '\u2014 Opsional \u2014');
      s3.disabled = true;
      syncDept();
      if (!s2.value) return;
      fetchKids(s2.value).then(function (kids) {
        if (kids.length) { buildOptions(s3, kids, '\u2014 Opsional \u2014'); s3.disabled = false; }
        syncDept();
      });
    } else {
      syncDept();
    }
  };

  /* ── Color picker ── */
  window.edPickColor = function (hex) {
    document.getElementById('fColor').value       = hex;
    document.getElementById('fColorPicker').value = hex;
    document.getElementById('edColorDot').style.background = hex;
    document.getElementById('edColorHex').textContent      = hex;
    document.querySelectorAll('.ed-swatch[data-color]').forEach(function (s) {
      s.classList.toggle('active', s.dataset.color.toLowerCase() === hex.toLowerCase());
    });
    var hero = document.querySelector('.ed-hero');
    if (hero) hero.style.setProperty('--mc', hex);
  };

  /* ── Participant search ── */
  var pSearch = document.getElementById('fPSearch');
  var pList   = document.getElementById('fPList');
  var pCount  = document.getElementById('fPCountNum');

  function updateCount() {
    if (pCount) pCount.textContent = pList.querySelectorAll('input[type=checkbox]:checked').length;
  }

  if (pSearch && pList) {
    pSearch.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      pList.querySelectorAll('.ed-p-item').forEach(function (item) {
        var nm = item.querySelector('.ed-p-name');
        item.style.display = (!q || (nm && nm.textContent.toLowerCase().includes(q))) ? '' : 'none';
      });
    });
    pList.addEventListener('change', function (e) {
      if (e.target.type === 'checkbox') updateCount();
    });
  }

  /* ── Form validation ── */
  var form = document.getElementById('editMeetingForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      var startEl = document.getElementById('fStart');
      var endEl   = document.getElementById('fEnd');
      var valid   = true;

      form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

      form.querySelectorAll('[required]').forEach(function (el) {
        if (!el.value.trim()) { el.classList.add('is-invalid'); valid = false; }
      });

      if (startEl.value && endEl.value && endEl.value <= startEl.value) {
        endEl.classList.add('is-invalid'); valid = false;
      }

      if (!valid) {
        e.preventDefault();
        var first = form.querySelector('.is-invalid');
        if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
      }

      var btn = document.getElementById('edSubmitBtn');
      if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';
      }
    });
  }

  /* ── Auto-suggest end = start + 1h ── */
  var startEl2 = document.getElementById('fStart');
  var endEl2   = document.getElementById('fEnd');
  if (startEl2 && endEl2) {
    startEl2.addEventListener('change', function () {
      if (!endEl2.value || endEl2.value <= startEl2.value) {
        var d = new Date(startEl2.value);
        if (!isNaN(d.getTime())) {
          d.setHours(d.getHours() + 1);
          var pad = function (n) { return String(n).padStart(2, '0'); };
          endEl2.value = d.getFullYear() + '-'
            + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
          endEl2.classList.remove('is-invalid');
        }
      }
    });
  }

}());
</script>

<style>
/* =============================================================
   EDIT.PHP — scoped styles
   Token dari custom.css:
     --brand  --brand-dark  --brand-light  --brand-xlight
     --gold   --gold-dark
     --bg-page  --bg-card  --text-main  --text-muted
     --border   --border-light
============================================================= */

/* Alert */
.ed-alert {
  display:flex; align-items:center; gap:.5rem;
  background:rgba(123,28,28,.07); color:var(--brand);
  border:1px solid rgba(123,28,28,.22); border-radius:8px;
  padding:.65rem 1rem; margin-bottom:1rem;
  font-size:13px; font-weight:500;
}
.ed-alert-close { margin-left:auto; background:none; border:none; font-size:18px; cursor:pointer; color:inherit; opacity:.7; line-height:1; }
.ed-alert-close:hover { opacity:1; }

/* Hero */
.ed-hero {
  --mc: var(--brand);
  background: linear-gradient(135deg, var(--mc) 0%, var(--mc) 55%, #3d0a0a 100%);
  border-radius:14px;
  box-shadow:0 4px 24px rgba(0,0,0,.16);
}
.ed-hero-inner { padding:1.2rem 1.6rem; }
.ed-bc         { display:flex; align-items:center; gap:.3rem; font-size:12px; color:rgba(255,255,255,.58); margin-bottom:.5rem; }
.ed-bc a       { color:rgba(255,255,255,.80); text-decoration:none; }
.ed-bc a:hover { color:#fff; text-decoration:underline; }
.ed-hero-row   { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.5rem; }
.ed-hero-title { display:flex; align-items:center; gap:.5rem; font-size:clamp(14px,2.2vw,19px); font-weight:800; color:#fff; margin:0; }
.ed-back-btn   { display:inline-flex; align-items:center; gap:.35rem; font-size:13px; font-weight:600; background:rgba(255,255,255,.14); border:1.5px solid rgba(255,255,255,.28); color:#fff; padding:.38rem .85rem; border-radius:8px; text-decoration:none; transition:background .18s; white-space:nowrap; }
.ed-back-btn:hover { background:rgba(255,255,255,.24); color:#fff; }

/* Card */
.ed-card { background:var(--bg-card); border:1px solid var(--border-light); border-radius:12px; overflow:hidden; box-shadow:0 1px 6px rgba(0,0,0,.05); }

/* Sections */
.ed-section       { padding:1.4rem 1.6rem; }
.ed-section-title { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; color:var(--text-muted); margin-bottom:1rem; }
.ed-divider       { height:1px; background:var(--border-light); }
.ed-hint          { font-size:12px; color:var(--text-muted); margin-top:.25rem; }
.ed-optional      { font-weight:400; color:var(--text-muted); font-size:11px; }

/* Color picker */
.ed-color-row { display:flex; flex-wrap:wrap; align-items:center; gap:.4rem; margin-bottom:.5rem; }
.ed-swatch {
  width:26px; height:26px; border-radius:50%;
  border:2.5px solid transparent;
  outline:none; cursor:pointer;
  transition:transform .15s,box-shadow .15s;
  flex-shrink:0; padding:0;
}
.ed-swatch:hover { transform:scale(1.15); }
.ed-swatch.active {
  box-shadow:0 0 0 2px #fff, 0 0 0 4.5px var(--sw, var(--brand));
  transform:scale(1.1);
}
.ed-swatch-custom {
  display:flex; align-items:center; justify-content:center;
  background:var(--bg-page); border:1.5px dashed var(--border); color:var(--text-muted);
  cursor:pointer; overflow:hidden; position:relative;
  transition:border-color .15s,color .15s;
}
.ed-swatch-custom:hover { border-color:var(--text-muted); color:var(--text-main); }
.ed-swatch-custom input[type=color] { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
.ed-color-preview { display:flex; align-items:center; gap:.4rem; margin-top:.3rem; }
.ed-color-dot     { display:inline-block; width:14px; height:14px; border-radius:50%; border:1px solid var(--border); flex-shrink:0; }
.ed-color-hex     { font-size:12px; color:var(--text-muted); font-family:monospace; }

/* Participant box */
.ed-p-wrap    { border:1px solid var(--border); border-radius:10px; overflow:hidden; }
.ed-p-search  { display:flex; align-items:center; gap:.4rem; padding:.5rem .8rem; border-bottom:1px solid var(--border-light); background:var(--bg-page); }
.ed-p-search svg   { flex-shrink:0; color:var(--text-muted); }
.ed-p-search input { border:none; background:none; outline:none; font-size:13px; width:100%; color:var(--text-main); }
.ed-p-list    { max-height:230px; overflow-y:auto; padding:.3rem 0; }
.ed-p-item    { display:flex; align-items:center; gap:.5rem; padding:.4rem .8rem; cursor:pointer; transition:background .12s; }
.ed-p-item:hover { background:var(--brand-xlight); }
.ed-p-item input[type=checkbox] { width:15px; height:15px; flex-shrink:0; cursor:pointer; accent-color:var(--brand); }
.ed-pav       { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; font-size:11px; font-weight:800; color:#fff; flex-shrink:0; }
.ed-p-name    { font-size:13px; font-weight:500; flex:1; min-width:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.ed-p-dept    { font-size:11px; color:var(--text-muted); white-space:nowrap; }
.ed-p-count   { padding:.35rem .8rem; font-size:12px; font-weight:600; color:var(--text-muted); background:var(--bg-page); border-top:1px solid var(--border-light); }

/* Footer */
.ed-footer { display:flex; justify-content:flex-end; align-items:center; gap:.75rem; padding:1rem 1.6rem; border-top:1px solid var(--border-light); background:var(--bg-page); }
.ed-btn-cancel { font-size:13px; font-weight:600; color:var(--text-muted); text-decoration:none; padding:.45rem .9rem; border-radius:8px; transition:color .15s,background .15s; }
.ed-btn-cancel:hover { color:var(--text-main); background:var(--border-light); }
.ed-btn-submit { display:inline-flex; align-items:center; gap:.4rem; font-size:13px; font-weight:700; background:var(--brand); color:#fff; padding:.5rem 1.2rem; border:none; border-radius:8px; cursor:pointer; transition:background .18s; }
.ed-btn-submit:hover:not(:disabled) { background:var(--brand-dark); }
.ed-btn-submit:disabled { opacity:.6; cursor:not-allowed; }

/* Responsive */
@media (max-width:575px) {
  .ed-hero-inner { padding:1rem; }
  .ed-section    { padding:1rem; }
  .ed-footer     { padding:.75rem 1rem; flex-wrap:wrap; }
  .ed-p-list     { max-height:170px; }
}
</style>
