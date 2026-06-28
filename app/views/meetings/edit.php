<?php
$baseUrl = rtrim(BASE_URL, '/');

$statusLabel = [
  'scheduled' => 'Terjadwal',
  'ongoing'   => 'Berlangsung',
  'done'      => 'Selesai',
  'cancelled' => 'Dibatalkan',
];

// ── Resolve departemen chain ──────────────────────────────────────
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

$colorPresets = ['#7B1C1C','#1a6e9b','#2d7a2d','#8b5e00','#6b2fa0','#c0392b','#2c7a6e','#6b6b6b'];
$currentColor = strtolower(trim($meeting['color'] ?? '#7b1c1c'));

$participantIds = array_map('intval', $participantIds ?? []);
$allUsers       = $allUsers ?? [];
$avPalette      = ['#7B1C1C','#1a6e9b','#2d7a2d','#6b2fa0','#8b5e00','#2c7a6e'];

$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);

$childrenUrl = $baseUrl . '/api/departments/children';
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
<div class="mi-hero" id="edHero" style="background:linear-gradient(135deg,<?= htmlspecialchars($currentColor) ?> 0%,#9B2020 55%,#A83218 100%)">
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

<!-- ══ FORM PANEL ════════════════════════════════════════════════════ -->
<div class="mi-panel">
  <form method="POST"
        action="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>/update"
        id="editMeetingForm"
        novalidate>
    <?= Auth::csrfField() ?>

    <!-- ── Informasi Dasar ───────────────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <span class="ed-sec-label">Informasi Dasar</span>
      </div>

      <div class="mi-mc-grid">
        <div class="mi-mc-field mi-mc-full">
          <label class="mi-mc-lbl mi-req" for="fTitle">Judul Kegiatan</label>
          <input type="text" id="fTitle" name="title" class="mi-mc-input ed-input" maxlength="255"
                 autocomplete="off" required
                 value="<?= htmlspecialchars($meeting['title']) ?>"
                 placeholder="Contoh: Rapat Evaluasi Bulanan Q2">
          <span class="ed-invalid-msg">Judul kegiatan wajib diisi.</span>
        </div>

        <div class="mi-mc-field">
          <label class="mi-mc-lbl mi-req" for="fStart">Tanggal &amp; Jam Mulai</label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fStart" name="start_datetime"
                   class="mi-mc-input ed-input ed-input-pl" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['start_datetime'])) ?>">
          </div>
          <span class="ed-invalid-msg">Waktu mulai wajib diisi.</span>
        </div>

        <div class="mi-mc-field">
          <label class="mi-mc-lbl mi-req" for="fEnd">Tanggal &amp; Jam Selesai</label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <input type="datetime-local" id="fEnd" name="end_datetime"
                   class="mi-mc-input ed-input ed-input-pl" required
                   value="<?= date('Y-m-d\TH:i', strtotime($meeting['end_datetime'])) ?>">
          </div>
          <span class="ed-invalid-msg">Waktu selesai wajib diisi &amp; harus setelah waktu mulai.</span>
        </div>

        <div class="mi-mc-field">
          <label class="mi-mc-lbl mi-req" for="fStatus">Status Kegiatan</label>
          <select id="fStatus" name="status" class="mi-mc-select ed-input" required>
            <?php foreach ($statusLabel as $val => $lbl): ?>
            <option value="<?= $val ?>" <?= ($meeting['status'] ?? 'scheduled') === $val ? 'selected' : '' ?>>
              <?= htmlspecialchars($lbl) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <span class="ed-invalid-msg">Status wajib dipilih.</span>
        </div>

        <div class="mi-mc-field">
          <label class="mi-mc-lbl" for="fLocation">Lokasi / Tautan Video</label>
          <div class="ed-ico-wrap">
            <svg class="ed-ico" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <input type="text" id="fLocation" name="location"
                   class="mi-mc-input ed-input ed-input-pl"
                   placeholder="Ruang Rapat A  atau  https://meet.google.com/…"
                   value="<?= htmlspecialchars($meeting['location'] ?? '') ?>">
          </div>
          <span class="ed-hint">Jika berupa URL, akan ditampilkan sebagai tautan di halaman detail.</span>
        </div>

        <div class="mi-mc-field mi-mc-full">
          <label class="mi-mc-lbl" for="fDesc">Deskripsi / Agenda <span class="mi-mc-opt">(opsional)</span></label>
          <textarea id="fDesc" name="description" class="mi-mc-textarea ed-input" rows="4"
                    placeholder="Tulis poin-poin agenda kegiatan…"><?= htmlspecialchars($meeting['description'] ?? '') ?></textarea>
        </div>
      </div>
    </div>

    <div class="ed-divider"></div>

    <!-- ── Unit Kerja ────────────────────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <span class="ed-sec-label">Unit Kerja <span class="mi-mc-opt">(opsional)</span></span>
      </div>

      <div class="mi-mc-grid mi-mc-grid-3">
        <div class="mi-mc-field">
          <label class="mi-mc-lbl" for="fU1">Unit Kerja</label>
          <select id="fU1" name="_u1" class="mi-mc-select" onchange="edCascade(1)">
            <option value="">— Semua Unit Kerja —</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[1] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mi-mc-field">
          <label class="mi-mc-lbl" for="fU2">Bidang / Bagian</label>
          <select id="fU2" name="_u2" class="mi-mc-select" onchange="edCascade(2)"
                  <?= $sel[1] ? '' : 'disabled' ?>>
            <option value="">— Semua Bidang —</option>
            <?php foreach ($deptByParent[$sel[1]] ?? [] as $d): ?>
            <option value="<?= (int)$d['id'] ?>" <?= $sel[2] === (int)$d['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($d['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mi-mc-field">
          <label class="mi-mc-lbl" for="fU3">Sub Bidang <span class="mi-mc-opt">(opsional)</span></label>
          <select id="fU3" name="_u3" class="mi-mc-select" onchange="edCascade(3)"
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
      <input type="hidden" id="fDeptId" name="department_id" value="<?= $selDeptId ?: '' ?>">
    </div>

    <div class="ed-divider"></div>

    <!-- ── Peserta & Warna ───────────────────────────────────────── -->
    <div class="ed-section">
      <div class="ed-sec-head">
        <div class="ed-sec-icon">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <span class="ed-sec-label">Peserta &amp; Tampilan</span>
      </div>

      <div class="mi-mc-grid">
        <!-- Peserta -->
        <div class="mi-mc-field mi-mc-full">
          <label class="mi-mc-lbl">
            Pilih Peserta
            <span class="mi-badge-peserta" id="edPBadge" style="margin-left:.4rem">
              <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <span id="edPNum"><?= count($participantIds) ?></span> dipilih
            </span>
          </label>
          <!-- Search bar -->
          <div class="ed-p-searchbar">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="edPSearch" placeholder="Cari nama peserta…" autocomplete="off" aria-label="Cari peserta">
            <button type="button" id="edPSearchClear" class="ed-p-clear" title="Hapus">×</button>
          </div>
          <!-- List -->
          <div class="mi-mc-participants ed-p-list" id="edPList">
            <?php foreach ($allUsers as $u):
              $avBg = $avPalette[abs(crc32($u['name'])) % count($avPalette)];
            ?>
            <label class="mi-mc-pcheck ed-pcheck">
              <input type="checkbox" name="participants[]" value="<?= (int)$u['id'] ?>"
                     <?= in_array((int)$u['id'], $participantIds) ? 'checked' : '' ?>>
              <span class="ed-pav" style="background:<?= $avBg ?>"><?= strtoupper(mb_substr($u['name'],0,1)) ?></span>
              <span class="mi-mc-pname"><?= htmlspecialchars($u['name']) ?></span>
              <?php if (!empty($u['dept_name'])): ?>
              <span class="ed-pdept"><?= htmlspecialchars($u['dept_name']) ?></span>
              <?php endif; ?>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="ed-p-foot" aria-live="polite">
            <span class="mi-mc-pcount" id="edPCount"><?= count($participantIds) ?> peserta dipilih</span>
            <button type="button" class="ed-p-desel" id="edPDesel">Hapus semua</button>
          </div>
        </div>

        <!-- Warna -->
        <div class="mi-mc-field">
          <label class="mi-mc-lbl">Warna Kalender</label>
          <div class="mi-color-picker-wrap">
            <input type="color" id="fColorPicker" class="mi-color-input"
                   value="<?= htmlspecialchars($currentColor) ?>"
                   onchange="edPickColor(this.value)">
            <div class="mi-color-presets">
              <?php foreach ($colorPresets as $hex):
                $active = strtolower($hex) === $currentColor;
              ?>
              <button type="button"
                      class="mi-color-preset<?= $active ? ' mi-active' : '' ?>"
                      style="background:<?= $hex ?>"
                      data-color="<?= $hex ?>"
                      onclick="edPickColor('<?= $hex ?>')"
                      title="<?= $hex ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
          <input type="hidden" id="fColor" name="color" value="<?= htmlspecialchars($currentColor) ?>">
          <div class="ed-color-preview">
            <span class="ed-color-dot" id="edColorDot" style="background:<?= htmlspecialchars($currentColor) ?>"></span>
            <span class="ed-color-hex" id="edColorHex"><?= htmlspecialchars($currentColor) ?></span>
            <span style="font-size:11.5px;color:var(--text-muted,#8c7a6b)">· tampil di kalender</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Footer ───────────────────────────────────────────────── -->
    <div class="mi-mc-footer">
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$meeting['id'] ?>" class="mi-mc-btn-cancel">Batal</a>
      <button type="submit" class="mi-mc-btn-submit" id="edSubmitBtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v14a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
        Simpan Perubahan
      </button>
    </div>

  </form>
</div>

<script>
(function () {
  'use strict';

  /* toast auto-dismiss */
  var t = document.getElementById('edFlashToast');
  if (t) { setTimeout(function(){t.style.opacity='0';t.style.transition='opacity .4s';},4000); setTimeout(function(){if(t.parentNode)t.remove();},4500); }

  /* ── cascade dept ── */
  var CURL = <?= json_encode($childrenUrl) ?>;
  function fetchKids(pid) {
    return fetch(CURL + '?parent_id=' + encodeURIComponent(pid))
      .then(function(r){ return r.ok ? r.json() : []; }).catch(function(){ return []; });
  }
  function syncDept() {
    var v3=document.getElementById('fU3').value,
        v2=document.getElementById('fU2').value,
        v1=document.getElementById('fU1').value;
    document.getElementById('fDeptId').value = v3||v2||v1||'';
  }
  function buildOpts(sel, items, ph) {
    sel.innerHTML = '<option value="">'+ph+'</option>';
    items.forEach(function(d){ var o=document.createElement('option'); o.value=d.id; o.textContent=d.name; sel.appendChild(o); });
  }
  window.edCascade = function(level) {
    var s1=document.getElementById('fU1'),s2=document.getElementById('fU2'),s3=document.getElementById('fU3');
    if (level===1) {
      buildOpts(s2,[],'— Semua Bidang —'); buildOpts(s3,[],'— Opsional —');
      s2.disabled=s3.disabled=true; syncDept();
      if (!s1.value) return;
      fetchKids(s1.value).then(function(k){ if(k.length){buildOpts(s2,k,'— Semua Bidang —');s2.disabled=false;} syncDept(); });
    } else if (level===2) {
      buildOpts(s3,[],'— Opsional —'); s3.disabled=true; syncDept();
      if (!s2.value) return;
      fetchKids(s2.value).then(function(k){ if(k.length){buildOpts(s3,k,'— Opsional —');s3.disabled=false;} syncDept(); });
    } else { syncDept(); }
  };

  /* ── color ── */
  window.edPickColor = function(hex) {
    document.getElementById('fColor').value = hex;
    document.getElementById('fColorPicker').value = hex;
    document.getElementById('edColorDot').style.background = hex;
    document.getElementById('edColorHex').textContent = hex;
    document.querySelectorAll('.mi-color-preset[data-color]').forEach(function(b){
      b.classList.toggle('mi-active', b.dataset.color.toLowerCase()===hex.toLowerCase());
    });
    /* update hero gradient */
    document.getElementById('edHero').style.background =
      'linear-gradient(135deg,'+hex+' 0%,#9B2020 55%,#A83218 100%)';
  };

  /* ── participant search & count ── */
  var pSearch = document.getElementById('edPSearch'),
      pClear  = document.getElementById('edPSearchClear'),
      pList   = document.getElementById('edPList'),
      pNum    = document.getElementById('edPNum'),
      pCount  = document.getElementById('edPCount'),
      pDesel  = document.getElementById('edPDesel');

  function updatePCount() {
    var n = pList ? pList.querySelectorAll('input[type=checkbox]:checked').length : 0;
    if (pNum)   pNum.textContent   = n;
    if (pCount) pCount.textContent = n + ' peserta dipilih';
  }
  if (pSearch && pList) {
    pSearch.addEventListener('input', function() {
      var q = this.value.trim().toLowerCase();
      if (pClear) pClear.style.display = q ? 'block' : 'none';
      pList.querySelectorAll('.ed-pcheck').forEach(function(item) {
        var nm = item.querySelector('.mi-mc-pname');
        item.style.display = (!q || (nm && nm.textContent.toLowerCase().includes(q))) ? '' : 'none';
      });
    });
    pList.addEventListener('change', function(e) { if(e.target.type==='checkbox') updatePCount(); });
  }
  if (pClear) pClear.addEventListener('click', function() {
    pSearch.value=''; pClear.style.display='none';
    pList.querySelectorAll('.ed-pcheck').forEach(function(i){i.style.display='';});
  });
  if (pDesel) pDesel.addEventListener('click', function() {
    pList.querySelectorAll('input[type=checkbox]').forEach(function(c){c.checked=false;});
    updatePCount();
  });

  /* ── form validation ── */
  var form = document.getElementById('editMeetingForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      var sEl=document.getElementById('fStart'), eEl=document.getElementById('fEnd'), ok=true;
      form.querySelectorAll('.ed-invalid').forEach(function(el){el.classList.remove('ed-invalid');});
      form.querySelectorAll('[required]').forEach(function(el){
        if (!el.value.trim()){el.classList.add('ed-invalid');ok=false;}
      });
      if (sEl&&eEl&&sEl.value&&eEl.value&&eEl.value<=sEl.value){eEl.classList.add('ed-invalid');ok=false;}
      if (!ok) {
        e.preventDefault();
        var first=form.querySelector('.ed-invalid');
        if(first) first.scrollIntoView({behavior:'smooth',block:'center'});
        return;
      }
      var btn=document.getElementById('edSubmitBtn');
      if (btn){btn.disabled=true;btn.innerHTML='<span class="ed-spinner"></span>Menyimpan\u2026';}
    });
    form.querySelectorAll('.mi-mc-input,.mi-mc-select,.mi-mc-textarea').forEach(function(el){
      el.addEventListener('input',  function(){el.classList.remove('ed-invalid');});
      el.addEventListener('change', function(){el.classList.remove('ed-invalid');});
    });
  }

  /* ── auto-fill end = start + 1h ── */
  var sEl=document.getElementById('fStart'), eEl=document.getElementById('fEnd');
  if (sEl&&eEl) {
    sEl.addEventListener('change', function() {
      if (!eEl.value||eEl.value<=sEl.value) {
        var d=new Date(sEl.value);
        if (!isNaN(d.getTime())) {
          d.setHours(d.getHours()+1);
          var p=function(n){return String(n).padStart(2,'0');};
          eEl.value=d.getFullYear()+'-'+p(d.getMonth()+1)+'-'+p(d.getDate())+'T'+p(d.getHours())+':'+p(d.getMinutes());
          eEl.classList.remove('ed-invalid');
        }
      }
    });
  }

}());
</script>

<style>
/* ── breadcrumb ── */
.ed-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .25rem;
}
.ed-breadcrumb a { color: rgba(255,255,255,.80); text-decoration: none; }
.ed-breadcrumb a:hover { color: #fff; text-decoration: underline; }

/* ── back button ── */
.ed-back-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  font-size: 13px; font-weight: 600;
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3);
  color: #fff; padding: .45rem .95rem; border-radius: 9px;
  text-decoration: none; transition: background .18s; white-space: nowrap; flex-shrink: 0;
}
.ed-back-btn:hover { background: rgba(255,255,255,.26); color: #fff; }

/* ── section ── */
.ed-section { padding: 1.4rem 1.5rem; }
.ed-sec-head {
  display: flex; align-items: center; gap: .5rem;
  margin-bottom: .9rem; padding-bottom: .55rem;
  border-bottom: 1px solid var(--border-light, #ede8e0);
}
.ed-sec-icon {
  width: 26px; height: 26px; border-radius: 7px;
  background: rgba(123,28,28,.09);
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; color: #7B1C1C;
}
.ed-sec-label {
  font-size: 10.5px; font-weight: 700; letter-spacing: .07em;
  text-transform: uppercase; color: #7B1C1C;
}
.ed-divider { height: 1px; background: var(--border-light, #ede8e0); }

/* ── input override (invalid state) ── */
.mi-mc-input.ed-invalid,
.mi-mc-select.ed-invalid,
.mi-mc-textarea.ed-invalid {
  border-color: #a82515;
  box-shadow: 0 0 0 3px rgba(168,37,21,.10);
}
.ed-invalid-msg { font-size: 12px; color: #a82515; display: none; margin-top: .1rem; }
.ed-invalid ~ .ed-invalid-msg { display: block; }
.ed-hint { font-size: 12px; color: var(--text-muted, #8c7a6b); margin-top: .15rem; }

/* ── icon-inside input ── */
.ed-ico-wrap { position: relative; }
.ed-ico {
  position: absolute; left: .65rem; top: 50%; transform: translateY(-50%);
  color: var(--text-muted, #8c7a6b); pointer-events: none;
}
.ed-input-pl { padding-left: 2rem; }

/* ── participant search ── */
.ed-p-searchbar {
  display: flex; align-items: center; gap: .4rem;
  border: 1.5px solid var(--border, #ddd); border-radius: 8px 8px 0 0;
  padding: .45rem .75rem; background: #faf4eb;
}
.ed-p-searchbar svg { flex-shrink: 0; color: var(--text-muted, #8c7a6b); }
.ed-p-searchbar input {
  border: none; background: none; outline: none;
  font-size: 13px; width: 100%; color: var(--text-main, #2c1a1a); font-family: inherit;
}
.ed-p-clear {
  background: none; border: none; font-size: 15px; cursor: pointer;
  color: var(--text-muted); opacity: .6; line-height: 1; padding: 0; display: none;
}
.ed-p-clear:hover { opacity: 1; }

/* list reuses .mi-mc-participants */
.ed-p-list {
  border-radius: 0 !important;
  border-top: none !important;
  max-height: 220px;
}

/* individual check item extras */
.ed-pcheck { flex-wrap: nowrap !important; }
.ed-pav {
  display: inline-flex; align-items: center; justify-content: center;
  width: 24px; height: 24px; border-radius: 50%;
  font-size: 11px; font-weight: 800; color: #fff; flex-shrink: 0;
}
.ed-pdept { font-size: 11px; color: var(--text-muted, #8c7a6b); white-space: nowrap; margin-left: auto; }
.mi-mc-pcheck:has(input:checked) .mi-mc-pname { color: #7B1C1C; }

/* footer row */
.ed-p-foot {
  display: flex; justify-content: space-between; align-items: center;
  padding: .38rem .75rem;
  background: #faf4eb; border: 1.5px solid var(--border, #ddd);
  border-top: 1px solid var(--border-light, #ede8e0);
  border-radius: 0 0 8px 8px;
}
.ed-p-desel {
  background: none; border: none; font-size: 12px; color: #a82515;
  cursor: pointer; padding: 0; font-family: inherit; font-weight: 600;
}
.ed-p-desel:hover { color: #7B1C1C; text-decoration: underline; }

/* color preview */
.ed-color-preview { display: flex; align-items: center; gap: .4rem; margin-top: .4rem; }
.ed-color-dot { display: inline-block; width: 14px; height: 14px; border-radius: 50%; border: 1px solid #ddd; flex-shrink: 0; }
.ed-color-hex { font-size: 12px; color: var(--text-muted, #8c7a6b); font-family: monospace; }

/* spinner */
.ed-spinner {
  display: inline-block; width: 12px; height: 12px;
  border: 2px solid rgba(255,255,255,.4); border-top-color: #fff;
  border-radius: 50%; animation: edSpin .6s linear infinite; margin-right: .35rem;
}
@keyframes edSpin { to { transform: rotate(360deg); } }

/* responsive */
@media (max-width: 991px) {
  .ed-section { padding: 1.1rem 1.25rem; }
  .mi-mc-grid-3 { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 767px) {
  .mi-hero { flex-wrap: wrap; padding: 1rem; }
  .ed-back-btn { width: 100%; justify-content: center; }
  .ed-section { padding: 1rem; }
  .mi-mc-grid { grid-template-columns: 1fr !important; }
  .mi-mc-full { grid-column: span 1; }
  .mi-mc-grid-3 { grid-template-columns: 1fr; }
  .ed-p-list { max-height: 160px; }
}
</style>
