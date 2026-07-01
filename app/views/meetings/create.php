<?php
$baseUrl = rtrim(BASE_URL, '/');
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
?>

<!-- ══ FLASH ══ -->
<?php if ($flashError): ?>
<div class="mc-toast mc-toast-err" id="mcToast">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button onclick="this.closest('.mc-toast').remove()">×</button>
</div>
<?php endif; ?>

<!-- ══ PAGE HEADER ══ -->
<div class="mc-hero">
  <div class="mc-hero-left">
    <a href="<?= $baseUrl ?>/meetings" class="mc-back" title="Kembali">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="mc-hero-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <h1 class="mc-hero-title">Buat Kegiatan Baru</h1>
      <p class="mc-hero-sub">Isi formulir untuk menambahkan kegiatan ke kalender instansi</p>
    </div>
  </div>
</div>

<!-- ══ FORM CARD ══ -->
<div class="mc-card">
  <form method="POST" action="<?= $baseUrl ?>/meetings" id="mcForm">
    <?= Auth::csrfField() ?>

    <!-- ─ Informasi Dasar ─ -->
    <div class="mc-section">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Informasi Dasar
      </div>
      <div class="mc-grid">
        <div class="mc-field mc-full">
          <label class="mc-lbl mc-req">Judul Kegiatan</label>
          <input type="text" name="title" class="mc-input" required autocomplete="off"
                 placeholder="Contoh: Rapat Koordinasi Semester I"
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
        </div>
        <div class="mc-field">
          <label class="mc-lbl mc-req">Mulai</label>
          <input type="datetime-local" name="start_datetime" id="mcStart" class="mc-input" required
                 value="<?= htmlspecialchars($_POST['start_datetime'] ?? '') ?>">
        </div>
        <div class="mc-field">
          <label class="mc-lbl mc-req">Selesai</label>
          <input type="datetime-local" name="end_datetime" id="mcEnd" class="mc-input" required
                 value="<?= htmlspecialchars($_POST['end_datetime'] ?? '') ?>">
        </div>
        <div class="mc-field mc-full">
          <label class="mc-lbl">Lokasi / Tautan Video</label>
          <input type="text" name="location" class="mc-input"
                 placeholder="Ruang Rapat A  atau  https://meet.google.com/..."
                 value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
        </div>
      </div>
    </div>

    <!-- ─ Unit Kerja ─ -->
    <div class="mc-section">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Unit Kerja
        <span class="mc-opt">(opsional)</span>
      </div>
      <div class="mc-grid mc-grid-3">
        <?php
          $deptByParent = [];
          foreach ($departments as $d) $deptByParent[(int)($d['parent_id'] ?? 0)][] = $d;
        ?>
        <div class="mc-field">
          <label class="mc-lbl">Unit Kerja</label>
          <select id="create-u1" class="mc-select" onchange="cascadeCreate(1)">
            <option value="">— Semua —</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mc-field">
          <label class="mc-lbl">Bidang / Bagian</label>
          <select id="create-u2" class="mc-select" disabled onchange="cascadeCreate(2)">
            <option value="">— Pilih unit dulu —</option>
          </select>
        </div>
        <div class="mc-field">
          <label class="mc-lbl">Sub Bidang</label>
          <select id="create-u3" class="mc-select" disabled onchange="cascadeCreate(3)">
            <option value="">— Opsional —</option>
          </select>
        </div>
      </div>
      <input type="hidden" id="create-dept-id" name="department_id" value="">
    </div>

    <!-- ─ Peserta & Warna ─ -->
    <div class="mc-section">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Peserta &amp; Tampilan
      </div>
      <div class="mc-grid">
        <div class="mc-field mc-full">
          <label class="mc-lbl">Pilih Peserta</label>
          <div class="mc-participants" id="mcParticipants">
            <?php foreach ($allUsers as $u): ?>
            <label class="mc-pcheck">
              <input type="checkbox" name="participants[]" value="<?= $u['id'] ?>">
              <span class="mc-pname"><?= htmlspecialchars($u['name']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <div class="mc-pcount" id="mcPCount">0 peserta dipilih</div>
        </div>
        <div class="mc-field">
          <label class="mc-lbl">Warna Kalender</label>
          <div class="mc-color-wrap">
            <input type="color" name="color" id="mcColor" class="mc-color-input"
                   value="<?= htmlspecialchars($_POST['color'] ?? '#7B1C1C') ?>">
            <div class="mc-color-presets">
              <?php foreach (['#7B1C1C','#1a6e9b','#2d7a2d','#8b5e00','#6b2fa0','#c0392b','#2c7a6e'] as $c): ?>
              <button type="button" class="mc-color-dot" style="background:<?= $c ?>" data-color="<?= $c ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ─ Deskripsi ─ -->
    <div class="mc-section">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Deskripsi / Agenda
        <span class="mc-opt">(opsional)</span>
      </div>
      <textarea name="description" class="mc-textarea" rows="4"
                placeholder="Tulis poin-poin agenda kegiatan…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
    </div>

    <!-- ─ Footer ─ -->
    <div class="mc-footer">
      <a href="<?= $baseUrl ?>/meetings" class="mc-btn-cancel">Batal</a>
      <button type="submit" class="mc-btn-submit">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Kegiatan
      </button>
    </div>

  </form>
</div>

<!-- ══ STYLES ══ -->
<style>
:root {
  --maroon      : #7B1C1C;
  --maroon-dark : #5C1212;
  --maroon-deep : #3D0A0A;
  --maroon-light: rgba(123,28,28,.08);
  --gold        : #C9A84C;
  --gold-light  : rgba(201,168,76,.14);
  --cream       : #FAF6EF;
  --cream-border: #E8DDD0;
  --border      : #ddd;
  --text-main   : #1A1A1A;
  --text-muted  : #8B8B8B;
}

/* Toast */
.mc-toast {
  position:fixed; top:1.25rem; right:1.25rem; z-index:9999;
  display:flex; align-items:center; gap:.6rem;
  padding:.65rem 1rem; border-radius:10px;
  font-size:13.5px; font-weight:500;
  box-shadow:0 4px 20px rgba(0,0,0,.14);
  animation:mcSlide .25s ease; max-width:340px;
}
@keyframes mcSlide { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
.mc-toast-err { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.mc-toast button { background:none; border:none; font-size:16px; cursor:pointer; opacity:.6; line-height:1; }

/* Hero */
.mc-hero {
  display:flex; align-items:center; justify-content:space-between;
  background:linear-gradient(135deg,#7B1C1C 0%,#9B2020 55%,#A83218 100%);
  padding:1.25rem 1.5rem; border-radius:14px; margin-bottom:1.25rem;
  box-shadow:0 4px 20px rgba(123,28,28,.22);
}
.mc-hero-left { display:flex; align-items:center; gap:.75rem; }
.mc-back {
  width:34px; height:34px; border-radius:8px;
  background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
  color:#fff; text-decoration:none; flex-shrink:0; transition:background .15s;
}
.mc-back:hover { background:rgba(255,255,255,.28); }
.mc-hero-icon {
  width:38px; height:38px; border-radius:10px;
  background:rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#fff;
}
.mc-hero-title { font-size:18px; font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.mc-hero-sub   { font-size:12px; color:rgba(255,255,255,.68); margin:.15rem 0 0; }

/* Card */
.mc-card {
  background:#fff; border:1px solid var(--cream-border);
  border-radius:14px; overflow:hidden;
  box-shadow:0 2px 14px rgba(0,0,0,.05);
}

/* Section */
.mc-section { padding:1.25rem 1.5rem; border-bottom:1px solid var(--cream-border); }
.mc-section:last-child { border-bottom:none; }
.mc-section-title {
  display:flex; align-items:center; gap:.5rem;
  font-size:10.5px; font-weight:700; letter-spacing:.08em;
  text-transform:uppercase; color:var(--text-muted);
  margin-bottom:.9rem;
}
.mc-section-bar { display:block; width:3px; height:14px; border-radius:2px; background:var(--maroon); flex-shrink:0; }
.mc-opt { font-weight:500; text-transform:none; letter-spacing:0; font-size:10.5px; }

/* Grid */
.mc-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
.mc-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.mc-full { grid-column:span 2; }
.mc-field { display:flex; flex-direction:column; gap:.3rem; }
.mc-lbl { font-size:12.5px; font-weight:600; color:var(--text-main); }
.mc-lbl.mc-req::after { content:' *'; color:var(--maroon); }

/* Inputs */
.mc-input,.mc-select,.mc-textarea {
  border:1.5px solid var(--cream-border); border-radius:8px;
  padding:.42rem .75rem; font-size:13.5px; font-family:inherit;
  background:var(--cream); color:var(--text-main); outline:none;
  transition:border-color .15s,box-shadow .15s;
}
.mc-input:focus,.mc-select:focus,.mc-textarea:focus {
  border-color:var(--maroon); background:#fff;
  box-shadow:0 0 0 3px var(--maroon-light);
}
.mc-select { cursor:pointer; }
.mc-textarea { resize:vertical; }

/* Participants */
.mc-participants {
  display:flex; flex-wrap:wrap; gap:.4rem;
  max-height:130px; overflow-y:auto;
  border:1.5px solid var(--cream-border); border-radius:8px;
  padding:.5rem .65rem; background:#fff;
}
.mc-participants:focus-within { border-color:var(--maroon); box-shadow:0 0 0 3px var(--maroon-light); }
.mc-pcheck {
  display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; font-size:12.5px; padding:.2rem .5rem;
  border-radius:20px; border:1px solid var(--cream-border);
  transition:all .12s; user-select:none;
}
.mc-pcheck:hover { background:#fdf5e6; border-color:var(--gold); }
.mc-pcheck input[type=checkbox] { accent-color:var(--maroon); width:13px; height:13px; }
.mc-pcheck input:checked~.mc-pname { color:var(--maroon); font-weight:600; }
.mc-pcheck:has(input:checked) { background:var(--maroon-light); border-color:rgba(123,28,28,.25); }
.mc-pcount { font-size:11.5px; color:var(--text-muted); margin-top:.25rem; }

/* Color */
.mc-color-wrap { display:flex; align-items:center; gap:.6rem; }
.mc-color-input {
  width:38px; height:38px; border:2px solid var(--cream-border);
  border-radius:8px; padding:2px; cursor:pointer; background:none;
}
.mc-color-presets { display:flex; gap:.4rem; flex-wrap:wrap; }
.mc-color-dot {
  width:22px; height:22px; border-radius:50%;
  border:2px solid transparent; cursor:pointer;
  transition:transform .12s,border-color .12s;
}
.mc-color-dot:hover { transform:scale(1.18); }
.mc-color-dot.mc-active { border-color:#fff; box-shadow:0 0 0 2px var(--text-main); }

/* Footer */
.mc-footer {
  display:flex; align-items:center; justify-content:flex-end; gap:.75rem;
  padding:1rem 1.5rem; background:var(--cream);
  border-top:1px solid var(--cream-border);
}
.mc-btn-cancel {
  padding:.48rem 1.1rem; border-radius:8px; font-size:13.5px; font-weight:600;
  border:1.5px solid var(--cream-border); background:#fff; color:var(--text-main);
  cursor:pointer; text-decoration:none; transition:all .15s;
}
.mc-btn-cancel:hover { background:#f0ece5; color:var(--text-main); }
.mc-btn-submit {
  display:inline-flex; align-items:center; gap:.4rem;
  padding:.48rem 1.25rem; border-radius:8px; font-size:13.5px; font-weight:700;
  border:none; background:var(--maroon); color:#fff; cursor:pointer;
  box-shadow:0 3px 12px rgba(123,28,28,.25); transition:all .15s;
}
.mc-btn-submit:hover { background:var(--maroon-dark); transform:translateY(-1px); box-shadow:0 5px 16px rgba(123,28,28,.32); }

/* Responsive */
@media(max-width:767px) {
  .mc-grid,.mc-grid-3 { grid-template-columns:1fr; }
  .mc-full { grid-column:span 1; }
  .mc-hero { padding:1rem; }
}
</style>

<?php $deptChildrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toast = document.getElementById('mcToast');
  if (toast) setTimeout(() => toast.remove(), 4500);

  /* Start → End auto-fill */
  const s = document.getElementById('mcStart'), e = document.getElementById('mcEnd');
  if (s && e) s.addEventListener('change', () => {
    if (!e.value || e.value <= s.value) {
      const d = new Date(s.value); d.setHours(d.getHours() + 1);
      e.value = d.toISOString().slice(0,16);
    }
  });

  /* Participant counter */
  const pCount = document.getElementById('mcPCount');
  document.querySelectorAll('#mcParticipants input').forEach(c =>
    c.addEventListener('change', () => {
      const n = document.querySelectorAll('#mcParticipants input:checked').length;
      if (pCount) pCount.textContent = n + ' peserta dipilih';
    })
  );

  /* Color presets */
  const colorInput = document.getElementById('mcColor');
  document.querySelectorAll('.mc-color-dot').forEach(btn => {
    if (btn.dataset.color === colorInput?.value) btn.classList.add('mc-active');
    btn.addEventListener('click', () => {
      document.querySelectorAll('.mc-color-dot').forEach(b => b.classList.remove('mc-active'));
      btn.classList.add('mc-active');
      if (colorInput) colorInput.value = btn.dataset.color;
    });
  });
});

const _createDeptChildUrl = <?= json_encode($deptChildrenUrl) ?>;
async function fetchCreateDeptChildren(pid) {
  try { return await (await fetch(_createDeptChildUrl + '?parent_id=' + pid)).json(); }
  catch(e) { return []; }
}
function syncCreateHidden() {
  const v = document.getElementById('create-u3').value ||
            document.getElementById('create-u2').value ||
            document.getElementById('create-u1').value || '';
  document.getElementById('create-dept-id').value = v;
}
async function cascadeCreate(level) {
  const s1=document.getElementById('create-u1'),s2=document.getElementById('create-u2'),s3=document.getElementById('create-u3');
  if (level===1) {
    s2.innerHTML='<option value="">— Pilih unit dulu —</option>';
    s3.innerHTML='<option value="">— Opsional —</option>';
    s2.disabled=s3.disabled=true; syncCreateHidden(); if(!s1.value)return;
    const kids=await fetchCreateDeptChildren(s1.value);
    if(kids.length){s2.innerHTML='<option value="">— Semua Bidang —</option>'+kids.map(d=>`<option value="${d.id}">${d.name}</option>`).join('');s2.disabled=false;}
    syncCreateHidden();
  } else if(level===2){
    s3.innerHTML='<option value="">— Opsional —</option>';s3.disabled=true;syncCreateHidden();if(!s2.value)return;
    const kids=await fetchCreateDeptChildren(s2.value);
    if(kids.length){s3.innerHTML='<option value="">— Semua Sub Bidang —</option>'+kids.map(d=>`<option value="${d.id}">${d.name}</option>`).join('');s3.disabled=false;}
    syncCreateHidden();
  } else { syncCreateHidden(); }
}
</script>
