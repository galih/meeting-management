<?php
$baseUrl = rtrim(BASE_URL, '/');
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']);
$today = date('Y-m-d');
?>

<!-- ══ FLASH TOAST ═══════════════════════════════════════════════════ -->
<?php if ($flashError): ?>
<!-- BUG #1: Tambah aria-live dan transisi opacity -->
<div class="mc-toast mc-toast-err" id="mcToast" role="alert" aria-live="assertive">
  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
  <span><?= htmlspecialchars($flashError) ?></span>
  <button class="mc-toast-close" onclick="this.closest('.mc-toast').remove()" aria-label="Tutup">&times;</button>
</div>
<?php endif; ?>

<!-- ══ PAGE HEADER ═══════════════════════════════════════════════════ -->
<div class="mc-hero">
  <div class="mc-hero-left">
    <a href="<?= $baseUrl ?>/meetings" class="mc-back" title="Kembali ke Daftar Kegiatan" aria-label="Kembali">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="mc-hero-icon">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
    </div>
    <div>
      <div class="mc-hero-eyebrow">Kegiatan Baru</div>
      <h1 class="mc-hero-title">Buat Kegiatan</h1>
      <p class="mc-hero-sub">Isi formulir untuk menambahkan kegiatan ke kalender instansi</p>
    </div>
  </div>
  <!-- UX: Step indicator -->
  <div class="mc-steps" aria-label="Langkah formulir">
    <div class="mc-step mc-step--done" id="mcStep1">
      <span class="mc-step-dot">1</span>
      <span class="mc-step-lbl">Informasi</span>
    </div>
    <div class="mc-step-line"></div>
    <div class="mc-step" id="mcStep2">
      <span class="mc-step-dot">2</span>
      <span class="mc-step-lbl">Peserta</span>
    </div>
    <div class="mc-step-line"></div>
    <div class="mc-step" id="mcStep3">
      <span class="mc-step-dot">3</span>
      <span class="mc-step-lbl">Simpan</span>
    </div>
  </div>
</div>

<!-- ══ FORM CARD ═══════════════════════════════════════════════════ -->
<div class="mc-card">
  <form method="POST" action="<?= $baseUrl ?>/meetings" id="mcForm" novalidate>
    <?= Auth::csrfField() ?>

    <!-- ─ Informasi Dasar ─ -->
    <div class="mc-section" id="mcSecInfo">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Informasi Dasar
        <span class="mc-section-req-hint">* wajib diisi</span>
      </div>
      <div class="mc-grid">
        <!-- Judul -->
        <div class="mc-field mc-full">
          <label class="mc-lbl mc-req" for="mcTitle">Judul Kegiatan</label>
          <input type="text" name="title" id="mcTitle" class="mc-input" autocomplete="off"
                 placeholder="Contoh: Rapat Koordinasi Semester I"
                 value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                 maxlength="200" required
                 aria-describedby="mcTitleErr">
          <div class="mc-field-err" id="mcTitleErr" aria-live="polite"></div>
        </div>
        <!-- Mulai -->
        <div class="mc-field">
          <label class="mc-lbl mc-req" for="mcStart">Mulai</label>
          <input type="datetime-local" name="start_datetime" id="mcStart" class="mc-input" required
                 value="<?= htmlspecialchars($_POST['start_datetime'] ?? '') ?>"
                 aria-describedby="mcStartErr">
          <div class="mc-field-err" id="mcStartErr" aria-live="polite"></div>
        </div>
        <!-- Selesai -->
        <div class="mc-field">
          <label class="mc-lbl mc-req" for="mcEnd">Selesai</label>
          <!-- BUG #2: Validasi end > start ditambahkan di JS -->
          <input type="datetime-local" name="end_datetime" id="mcEnd" class="mc-input" required
                 value="<?= htmlspecialchars($_POST['end_datetime'] ?? '') ?>"
                 aria-describedby="mcEndErr">
          <div class="mc-field-err" id="mcEndErr" aria-live="polite"></div>
        </div>
        <!-- Durasi preview -->
        <div class="mc-field mc-full" id="mcDurPreviewWrap" style="display:none;">
          <div class="mc-dur-preview" id="mcDurPreview"></div>
        </div>
        <!-- Lokasi -->
        <div class="mc-field mc-full">
          <label class="mc-lbl" for="mcLocation">Lokasi / Tautan Video</label>
          <div class="mc-input-icon-wrap">
            <svg class="mc-input-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            <input type="text" name="location" id="mcLocation" class="mc-input mc-input--icon" autocomplete="off"
                   placeholder="Ruang Rapat A  atau  https://meet.google.com/..."
                   value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
          </div>
          <!-- UX: Panduan lokasi dinamis -->
          <div class="mc-loc-hint" id="mcLocHint"></div>
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
          <label class="mc-lbl" for="create-u1">Unit Kerja</label>
          <select id="create-u1" class="mc-select" onchange="cascadeCreate(1)">
            <option value="">— Semua —</option>
            <?php foreach ($deptByParent[0] ?? [] as $d): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mc-field">
          <label class="mc-lbl" for="create-u2">Bidang / Bagian</label>
          <select id="create-u2" class="mc-select" disabled onchange="cascadeCreate(2)">
            <option value="">— Pilih unit dulu —</option>
          </select>
        </div>
        <div class="mc-field">
          <label class="mc-lbl" for="create-u3">Sub Bidang</label>
          <select id="create-u3" class="mc-select" disabled onchange="cascadeCreate(3)">
            <option value="">— Opsional —</option>
          </select>
        </div>
      </div>
      <input type="hidden" id="create-dept-id" name="department_id" value="">
    </div>

    <!-- ─ Peserta & Tampilan ─ -->
    <div class="mc-section" id="mcSecPeserta">
      <div class="mc-section-title">
        <span class="mc-section-bar"></span>Peserta &amp; Tampilan
      </div>
      <div class="mc-grid">
        <div class="mc-field mc-full">
          <label class="mc-lbl">Pilih Peserta</label>
          <!-- BUG #7 / UX: Search + Pilih Semua / Batalkan Semua -->
          <div class="mc-p-toolbar">
            <div class="mc-p-search-wrap">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
              <input type="text" id="mcPSearch" class="mc-p-search" placeholder="Cari nama peserta…" autocomplete="off">
            </div>
            <div class="mc-p-actions">
              <button type="button" class="mc-p-action-btn" id="btnSelectAll">Pilih Semua</button>
              <button type="button" class="mc-p-action-btn" id="btnClearAll">Batalkan</button>
            </div>
          </div>
          <div class="mc-participants" id="mcParticipants">
            <?php foreach ($allUsers as $u): ?>
            <label class="mc-pcheck" data-name="<?= htmlspecialchars(strtolower($u['name'])) ?>">
              <input type="checkbox" name="participants[]" value="<?= (int)$u['id'] ?>"
                     <?= in_array($u['id'], $_POST['participants'] ?? []) ? 'checked' : '' ?>>
              <span class="mc-pname"><?= htmlspecialchars($u['name']) ?></span>
            </label>
            <?php endforeach; ?>
          </div>
          <!-- UX: Chip preview peserta terpilih -->
          <div class="mc-selected-chips" id="mcSelectedChips"></div>
          <div class="mc-pcount" id="mcPCount">0 peserta dipilih</div>
        </div>
        <div class="mc-field">
          <label class="mc-lbl" for="mcColor">Warna Kalender</label>
          <div class="mc-color-wrap">
            <input type="color" name="color" id="mcColor" class="mc-color-input"
                   value="<?= htmlspecialchars($_POST['color'] ?? '#7B1C1C') ?>">
            <div class="mc-color-presets">
              <?php foreach (['#7B1C1C','#1a6e9b','#2d7a2d','#8b5e00','#6b2fa0','#c0392b','#2c7a6e'] as $c): ?>
              <button type="button" class="mc-color-dot" style="background:<?= $c ?>" data-color="<?= $c ?>" title="<?= $c ?>"></button>
              <?php endforeach; ?>
            </div>
          </div>
          <!-- UX: Preview warna terpilih -->
          <div class="mc-color-preview" id="mcColorPreview">
            <span class="mc-color-swatch" id="mcColorSwatch" style="background:<?= htmlspecialchars($_POST['color'] ?? '#7B1C1C') ?>"></span>
            <span id="mcColorLabel"><?= htmlspecialchars($_POST['color'] ?? '#7B1C1C') ?></span>
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
      <textarea name="description" id="mcDesc" class="mc-textarea" rows="4" maxlength="2000"
                placeholder="Tulis poin-poin agenda kegiatan…"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
      <!-- UX: Karakter counter -->
      <div class="mc-char-counter"><span id="mcDescCount"><?= strlen($_POST['description'] ?? '') ?></span>/2000</div>
    </div>

    <!-- ─ Footer ─ -->
    <div class="mc-footer">
      <a href="<?= $baseUrl ?>/meetings" class="mc-btn-cancel">Batal</a>
      <!-- BUG #6: Tombol submit di-disable saat loading -->
      <button type="submit" class="mc-btn-submit" id="mcBtnSubmit">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Buat Kegiatan
      </button>
    </div>

  </form>
</div>

<!-- ══ STYLES ═══════════════════════════════════════════════════ -->
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

/* ── Toast ── */
.mc-toast {
  position:fixed; top:1.25rem; right:1.25rem; z-index:9999;
  display:flex; align-items:center; gap:.6rem;
  padding:.65rem 1rem; border-radius:10px;
  font-size:13.5px; font-weight:500;
  box-shadow:0 4px 20px rgba(0,0,0,.14);
  animation:mcSlide .25s ease; max-width:360px;
  transition:opacity .4s;
}
@keyframes mcSlide { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:none} }
.mc-toast-err  { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
.mc-toast-ok   { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.mc-toast-close { background:none; border:none; font-size:16px; cursor:pointer; opacity:.6; line-height:1; padding:0; margin-left:.25rem; }
.mc-toast-close:hover { opacity:1; }

/* ── Hero ── */
.mc-hero {
  display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:.75rem;
  background:linear-gradient(135deg,#7B1C1C 0%,#9B2020 55%,#A83218 100%);
  padding:1.25rem 1.5rem; border-radius:14px; margin-bottom:1.25rem;
  box-shadow:0 4px 20px rgba(123,28,28,.22); position:relative; overflow:hidden;
}
.mc-hero::after {
  content:''; position:absolute; top:-40px; right:-40px;
  width:180px; height:180px; border-radius:50%;
  background:rgba(201,168,76,.08); pointer-events:none;
}
.mc-hero-left { display:flex; align-items:center; gap:.75rem; }
.mc-back {
  width:34px; height:34px; border-radius:8px;
  background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center;
  color:#fff; text-decoration:none; flex-shrink:0; transition:background .15s;
}
.mc-back:hover { background:rgba(255,255,255,.28); color:#fff; }
.mc-hero-icon {
  width:38px; height:38px; border-radius:10px;
  background:rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center; flex-shrink:0; color:#fff;
}
.mc-hero-eyebrow {
  font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.12em;
  color:rgba(255,255,255,.6); margin-bottom:.2rem;
}
.mc-hero-title { font-size:18px; font-weight:800; color:#fff; margin:0; letter-spacing:-.02em; }
.mc-hero-sub   { font-size:12px; color:rgba(255,255,255,.68); margin:.15rem 0 0; }

/* ── Step indicator ── */
.mc-steps {
  display:flex; align-items:center; gap:0;
  background:rgba(255,255,255,.1); border-radius:99px;
  padding:.35rem .9rem;
}
.mc-step {
  display:flex; align-items:center; gap:.35rem;
  font-size:11.5px; font-weight:600; color:rgba(255,255,255,.5);
  transition:color .2s;
}
.mc-step--done { color:rgba(255,255,255,.95); }
.mc-step--active { color:#fff; }
.mc-step-dot {
  width:22px; height:22px; border-radius:50%;
  background:rgba(255,255,255,.15);
  display:flex; align-items:center; justify-content:center;
  font-size:11px; font-weight:800; flex-shrink:0;
  transition:background .2s, color .2s;
}
.mc-step--done .mc-step-dot  { background:var(--gold); color:#3d0a0a; }
.mc-step--active .mc-step-dot { background:#fff; color:var(--maroon); }
.mc-step-line { width:24px; height:2px; background:rgba(255,255,255,.18); margin:0 .3rem; }

/* ── Card ── */
.mc-card {
  background:#fff; border:1px solid var(--cream-border);
  border-radius:14px; overflow:hidden;
  box-shadow:0 2px 14px rgba(0,0,0,.05);
}

/* ── Section ── */
.mc-section { padding:1.25rem 1.5rem; border-bottom:1px solid var(--cream-border); }
.mc-section:last-of-type { border-bottom:none; }
.mc-section-title {
  display:flex; align-items:center; gap:.5rem; flex-wrap:wrap;
  font-size:10.5px; font-weight:700; letter-spacing:.08em;
  text-transform:uppercase; color:var(--text-muted);
  margin-bottom:.9rem;
}
.mc-section-bar { display:block; width:3px; height:14px; border-radius:2px; background:var(--maroon); flex-shrink:0; }
.mc-opt { font-weight:500; text-transform:none; letter-spacing:0; font-size:10.5px; }
.mc-section-req-hint { margin-left:auto; font-size:10.5px; color:var(--maroon); font-weight:600; text-transform:none; letter-spacing:0; }

/* ── Grid ── */
.mc-grid { display:grid; grid-template-columns:1fr 1fr; gap:.75rem; }
.mc-grid-3 { grid-template-columns:1fr 1fr 1fr; }
.mc-full { grid-column:span 2; }
.mc-field { display:flex; flex-direction:column; gap:.3rem; }
.mc-lbl { font-size:12.5px; font-weight:600; color:var(--text-main); }
.mc-lbl.mc-req::after { content:' *'; color:var(--maroon); }

/* ── Field error ── */
.mc-field-err {
  font-size:12px; color:#a82515; min-height:1.2em;
  display:flex; align-items:center; gap:.3rem;
}
.mc-field-err:empty { display:none; }

/* ── Inputs ── */
.mc-input, .mc-select, .mc-textarea {
  border:1.5px solid var(--cream-border); border-radius:8px;
  padding:.48rem .75rem; font-size:13.5px; font-family:inherit;
  background:var(--cream); color:var(--text-main); outline:none;
  transition:border-color .15s, box-shadow .15s;
}
.mc-input:focus, .mc-select:focus, .mc-textarea:focus {
  border-color:var(--maroon); background:#fff;
  box-shadow:0 0 0 3px var(--maroon-light);
}
.mc-input.mc-err, .mc-select.mc-err, .mc-textarea.mc-err {
  border-color:#e05c5c;
  box-shadow:0 0 0 3px rgba(224,92,92,.12);
}
.mc-input.mc-ok { border-color:#22c55e; }
.mc-select { cursor:pointer; }
.mc-textarea { resize:vertical; }

/* Icon input -->
.mc-input-icon-wrap { position:relative; }
.mc-input-icon {
  position:absolute; left:.7rem; top:50%; transform:translateY(-50%);
  color:var(--text-muted); pointer-events:none;
}
.mc-input--icon { padding-left:2.2rem; }

/* ── Lokasi hint ── */
.mc-loc-hint {
  font-size:11.5px; margin-top:.25rem;
  display:flex; align-items:center; gap:.3rem;
}
.mc-loc-hint:empty { display:none; }
.mc-loc-hint--link { color:#1d4ed8; }
.mc-loc-hint--text { color:var(--text-muted); }

/* ── Durasi preview ── */
.mc-dur-preview {
  display:inline-flex; align-items:center; gap:.4rem;
  padding:.35rem .85rem; border-radius:999px;
  background:rgba(34,197,94,.1); border:1px solid rgba(34,197,94,.25);
  font-size:12px; font-weight:600; color:#15803d;
}

/* ── Participants ── */
.mc-p-toolbar {
  display:flex; align-items:center; gap:.6rem; flex-wrap:wrap;
  margin-bottom:.4rem;
}
.mc-p-search-wrap {
  position:relative; flex:1; min-width:160px;
}
.mc-p-search-wrap svg { position:absolute; left:.55rem; top:50%; transform:translateY(-50%); color:var(--text-muted); pointer-events:none; }
.mc-p-search {
  width:100%; padding:.38rem .65rem .38rem 2rem;
  border:1.5px solid var(--cream-border); border-radius:8px;
  font-size:13px; font-family:inherit; background:var(--cream); outline:none;
  transition:border-color .15s, box-shadow .15s;
}
.mc-p-search:focus { border-color:var(--maroon); background:#fff; box-shadow:0 0 0 3px var(--maroon-light); }
.mc-p-actions { display:flex; gap:.35rem; }
.mc-p-action-btn {
  padding:.3rem .7rem; border-radius:6px; font-size:11.5px; font-weight:600;
  border:1.5px solid var(--cream-border); background:#fff; color:var(--text-muted);
  cursor:pointer; transition:all .12s; white-space:nowrap;
}
.mc-p-action-btn:hover { border-color:var(--maroon); color:var(--maroon); background:var(--maroon-light); }
.mc-participants {
  display:flex; flex-wrap:wrap; gap:.4rem;
  max-height:150px; overflow-y:auto;
  border:1.5px solid var(--cream-border); border-radius:8px;
  padding:.5rem .65rem; background:#fff;
  scroll-behavior:smooth;
}
.mc-participants:focus-within { border-color:var(--maroon); box-shadow:0 0 0 3px var(--maroon-light); }
.mc-pcheck {
  display:inline-flex; align-items:center; gap:.3rem;
  cursor:pointer; font-size:12.5px; padding:.2rem .5rem;
  border-radius:20px; border:1px solid var(--cream-border);
  transition:all .12s; user-select:none;
}
.mc-pcheck:hover { background:#fdf5e6; border-color:var(--gold); }
.mc-pcheck.mc-p-hidden { display:none; }
.mc-pcheck input[type=checkbox] { accent-color:var(--maroon); width:13px; height:13px; }
.mc-pcheck input:checked~.mc-pname { color:var(--maroon); font-weight:600; }
.mc-pcheck:has(input:checked) { background:var(--maroon-light); border-color:rgba(123,28,28,.25); }
/* UX: Chip peserta terpilih -->
.mc-selected-chips {
  display:flex; flex-wrap:wrap; gap:.35rem;
  margin-top:.45rem; min-height:0;
}
.mc-sel-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.22rem .6rem .22rem .7rem; border-radius:999px;
  background:var(--maroon); color:#fff;
  font-size:11.5px; font-weight:600;
}
.mc-sel-chip-rm {
  background:none; border:none; color:rgba(255,255,255,.7);
  font-size:14px; line-height:1; cursor:pointer; padding:0; margin-left:.1rem;
  transition:color .1s;
}
.mc-sel-chip-rm:hover { color:#fff; }
.mc-pcount { font-size:11.5px; color:var(--text-muted); margin-top:.2rem; }

/* ── Color ── */
.mc-color-wrap { display:flex; align-items:center; gap:.6rem; flex-wrap:wrap; }
.mc-color-input {
  width:38px; height:38px; border:2px solid var(--cream-border);
  border-radius:8px; padding:2px; cursor:pointer; background:none;
}
.mc-color-presets { display:flex; gap:.4rem; flex-wrap:wrap; }
.mc-color-dot {
  width:24px; height:24px; border-radius:50%;
  border:2px solid transparent; cursor:pointer;
  transition:transform .12s, border-color .12s, box-shadow .12s;
  padding:0;
}
.mc-color-dot:hover { transform:scale(1.2); }
.mc-color-dot.mc-active { border-color:#fff; box-shadow:0 0 0 2.5px var(--text-main); transform:scale(1.12); }
/* UX: Preview warna -->
.mc-color-preview {
  display:flex; align-items:center; gap:.5rem;
  margin-top:.5rem; font-size:12px; color:var(--text-muted); font-weight:600;
}
.mc-color-swatch {
  width:20px; height:20px; border-radius:5px;
  border:1.5px solid rgba(0,0,0,.1); display:inline-block; flex-shrink:0;
  transition:background .15s;
}

/* ── Char counter ── */
.mc-char-counter { font-size:11px; color:var(--text-muted); text-align:right; margin-top:.25rem; }

/* ── Footer ── */
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
  padding:.48rem 1.35rem; border-radius:8px; font-size:13.5px; font-weight:700;
  border:none; background:var(--maroon); color:#fff; cursor:pointer;
  box-shadow:0 3px 12px rgba(123,28,28,.25); transition:all .15s;
}
.mc-btn-submit:hover:not(:disabled) { background:var(--maroon-dark); transform:translateY(-1px); box-shadow:0 5px 16px rgba(123,28,28,.32); }
.mc-btn-submit:disabled { opacity:.7; cursor:not-allowed; transform:none !important; }

/* ── Responsive ── */
@media(max-width:767px) {
  .mc-grid, .mc-grid-3 { grid-template-columns:1fr; }
  .mc-full { grid-column:span 1; }
  .mc-hero { padding:1rem; flex-direction:column; align-items:flex-start; }
  .mc-steps { display:none; }
  .mc-p-toolbar { flex-direction:column; align-items:stretch; }
  .mc-p-actions { justify-content:flex-end; }
}
</style>

<?php $deptChildrenUrl = $baseUrl . '/api/departments/children'; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── BUG #1: Toast fade-out ── */
  var toast = document.getElementById('mcToast');
  if (toast) {
    setTimeout(function () { toast.style.opacity = '0'; }, 4000);
    setTimeout(function () { if (toast.parentElement) toast.remove(); }, 4500);
  }

  /* ── Inputs ref ── */
  var titleEl    = document.getElementById('mcTitle');
  var startEl    = document.getElementById('mcStart');
  var endEl      = document.getElementById('mcEnd');
  var locationEl = document.getElementById('mcLocation');
  var colorInput = document.getElementById('mcColor');
  var descEl     = document.getElementById('mcDesc');
  var descCount  = document.getElementById('mcDescCount');
  var submitBtn  = document.getElementById('mcBtnSubmit');
  var form       = document.getElementById('mcForm');

  /* ── Helper: set/clear error ── */
  function setErr(inputEl, errId, msg) {
    inputEl.classList.toggle('mc-err', !!msg);
    inputEl.classList.toggle('mc-ok', !msg && inputEl.value.trim() !== '');
    var el = document.getElementById(errId);
    if (el) el.textContent = msg || '';
  }

  /* ── Validasi judul ── */
  if (titleEl) {
    titleEl.addEventListener('input', function () {
      setErr(titleEl, 'mcTitleErr', titleEl.value.trim() === '' ? 'Judul wajib diisi.' : '');
    });
    titleEl.addEventListener('blur', function () {
      setErr(titleEl, 'mcTitleErr', titleEl.value.trim() === '' ? 'Judul wajib diisi.' : '');
    });
  }

  /* ── BUG #2: Validasi end > start + durasi preview ── */
  function checkDatetime() {
    var sv = startEl ? startEl.value : '';
    var ev = endEl   ? endEl.value   : '';
    setErr(startEl, 'mcStartErr', sv === '' ? 'Waktu mulai wajib diisi.' : '');
    if (ev === '') {
      setErr(endEl, 'mcEndErr', 'Waktu selesai wajib diisi.');
      hideDurPreview();
      return false;
    }
    if (sv && ev && ev <= sv) {
      setErr(endEl, 'mcEndErr', 'Waktu selesai harus setelah waktu mulai.');
      hideDurPreview();
      return false;
    }
    setErr(endEl, 'mcEndErr', '');
    if (sv && ev && ev > sv) showDurPreview(sv, ev);
    return true;
  }

  function showDurPreview(sv, ev) {
    var diff = (new Date(ev) - new Date(sv)) / 60000;
    var h = Math.floor(diff / 60), m = diff % 60;
    var txt = (h > 0 ? h + ' jam ' : '') + (m > 0 ? m + ' menit' : '');
    var wrap = document.getElementById('mcDurPreviewWrap');
    var dp   = document.getElementById('mcDurPreview');
    if (wrap && dp) {
      dp.innerHTML = '⏱️ Durasi: <strong>' + txt + '</strong>';
      wrap.style.display = '';
    }
  }
  function hideDurPreview() {
    var wrap = document.getElementById('mcDurPreviewWrap');
    if (wrap) wrap.style.display = 'none';
  }

  if (startEl) {
    startEl.addEventListener('change', function () {
      // Auto-isi end +1 jam jika end kosong atau <= start
      if (endEl && (!endEl.value || endEl.value <= startEl.value)) {
        var d = new Date(startEl.value); d.setHours(d.getHours() + 1);
        endEl.value = d.toISOString().slice(0, 16);
      }
      checkDatetime();
    });
  }
  if (endEl) endEl.addEventListener('change', checkDatetime);

  /* ── Lokasi hint dinamis ── */
  if (locationEl) {
    locationEl.addEventListener('input', function () {
      var v = locationEl.value.trim();
      var hint = document.getElementById('mcLocHint');
      if (!hint) return;
      if (!v) { hint.textContent = ''; hint.className = 'mc-loc-hint'; return; }
      var isLink = /^https?:\/\//i.test(v);
      if (isLink) {
        hint.className = 'mc-loc-hint mc-loc-hint--link';
        hint.innerHTML = '🔗 Akan ditampilkan sebagai tautan klik untuk peserta';
      } else {
        hint.className = 'mc-loc-hint mc-loc-hint--text';
        hint.innerHTML = '📍 Akan ditampilkan sebagai alamat fisik';
      }
    });
  }

  /* ── Karakter counter deskripsi ── */
  if (descEl && descCount) {
    descEl.addEventListener('input', function () {
      descCount.textContent = descEl.value.length;
    });
  }

  /* ── BUG #3: Sync active state color preset dari value awal (termasuk dari $_POST) ── */
  function syncColorActive(val) {
    document.querySelectorAll('.mc-color-dot').forEach(function (b) {
      b.classList.toggle('mc-active', b.dataset.color.toLowerCase() === val.toLowerCase());
    });
    var swatch = document.getElementById('mcColorSwatch');
    var label  = document.getElementById('mcColorLabel');
    if (swatch) swatch.style.background = val;
    if (label)  label.textContent = val;
  }
  if (colorInput) {
    syncColorActive(colorInput.value); // BUG #3 fix: sync saat load
    document.querySelectorAll('.mc-color-dot').forEach(function (btn) {
      btn.addEventListener('click', function () {
        colorInput.value = btn.dataset.color;
        syncColorActive(btn.dataset.color);
      });
    });
    colorInput.addEventListener('input', function () {
      syncColorActive(colorInput.value);
    });
  }

  /* ── BUG #7 / UX: Peserta search, pilih semua, batalkan, chips ── */
  function refreshChips() {
    var checked = document.querySelectorAll('#mcParticipants input:checked');
    var n = checked.length;
    var pCount = document.getElementById('mcPCount');
    var chipsEl = document.getElementById('mcSelectedChips');
    if (pCount) pCount.textContent = n + ' peserta dipilih';
    if (!chipsEl) return;
    chipsEl.innerHTML = '';
    checked.forEach(function (cb) {
      var name = cb.closest('.mc-pcheck') ? cb.closest('.mc-pcheck').querySelector('.mc-pname').textContent : '';
      var chip = document.createElement('span');
      chip.className = 'mc-sel-chip';
      chip.innerHTML = name + '<button type="button" class="mc-sel-chip-rm" aria-label="Hapus ' + name + '">&times;</button>';
      chip.querySelector('.mc-sel-chip-rm').addEventListener('click', function () {
        cb.checked = false;
        cb.closest('.mc-pcheck').classList.remove('mc-active');
        refreshChips();
      });
      chipsEl.appendChild(chip);
    });
    // Update step indicator
    updateSteps();
  }

  document.querySelectorAll('#mcParticipants input').forEach(function (cb) {
    cb.addEventListener('change', refreshChips);
  });

  // Search filter
  var pSearch = document.getElementById('mcPSearch');
  if (pSearch) {
    pSearch.addEventListener('input', function () {
      var q = pSearch.value.toLowerCase().trim();
      document.querySelectorAll('.mc-pcheck').forEach(function (lbl) {
        var nm = lbl.getAttribute('data-name') || '';
        lbl.classList.toggle('mc-p-hidden', q !== '' && !nm.includes(q));
      });
    });
  }

  // Pilih Semua
  var btnAll = document.getElementById('btnSelectAll');
  if (btnAll) {
    btnAll.addEventListener('click', function () {
      document.querySelectorAll('.mc-pcheck:not(.mc-p-hidden) input').forEach(function (cb) { cb.checked = true; });
      refreshChips();
    });
  }

  // Batalkan Semua
  var btnClear = document.getElementById('btnClearAll');
  if (btnClear) {
    btnClear.addEventListener('click', function () {
      document.querySelectorAll('#mcParticipants input').forEach(function (cb) { cb.checked = false; });
      refreshChips();
    });
  }

  refreshChips(); // Init chips & counter dari $_POST restore

  /* ── Step indicator ── */
  function updateSteps() {
    var hasInfo     = titleEl && titleEl.value.trim() !== '' && startEl && startEl.value && endEl && endEl.value;
    var hasPeserta  = document.querySelectorAll('#mcParticipants input:checked').length > 0;
    var s1 = document.getElementById('mcStep1');
    var s2 = document.getElementById('mcStep2');
    var s3 = document.getElementById('mcStep3');
    if (s1) s1.className = 'mc-step' + (hasInfo ? ' mc-step--done' : ' mc-step--active');
    if (s2) s2.className = 'mc-step' + (hasInfo && hasPeserta ? ' mc-step--done' : hasInfo ? ' mc-step--active' : '');
    if (s3) s3.className = 'mc-step' + (hasInfo && hasPeserta ? ' mc-step--active' : '');
  }
  if (titleEl) titleEl.addEventListener('input', updateSteps);
  if (startEl) startEl.addEventListener('change', updateSteps);
  if (endEl)   endEl.addEventListener('change', updateSteps);
  updateSteps();

  /* ── BUG #6: Disable submit button saat loading (anti double-submit) ── */
  if (form && submitBtn) {
    form.addEventListener('submit', function (e) {
      // Jalankan semua validasi dulu
      var valid = true;
      if (titleEl && titleEl.value.trim() === '') {
        setErr(titleEl, 'mcTitleErr', 'Judul wajib diisi.');
        titleEl.focus();
        valid = false;
      }
      if (!checkDatetime()) valid = false;
      if (!valid) { e.preventDefault(); return; }

      // BUG #6: Disable dan tampilkan spinner
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" aria-hidden="true"></span>Menyimpan…';
    });
  }

});

/* ── BUG #4: Cascade dept dengan AbortController (anti race condition) ── */
var _cascadeController = null;
var _createDeptChildUrl = <?= json_encode($deptChildrenUrl) ?>;

/* BUG #5: Handle non-array / error response */
async function fetchCreateDeptChildren(pid) {
  if (_cascadeController) _cascadeController.abort();
  _cascadeController = new AbortController();
  try {
    var resp = await fetch(_createDeptChildUrl + '?parent_id=' + encodeURIComponent(pid), {
      signal: _cascadeController.signal
    });
    if (!resp.ok) throw new Error('HTTP ' + resp.status);
    var data = await resp.json();
    return Array.isArray(data) ? data : (Array.isArray(data.data) ? data.data : []);
  } catch(e) {
    if (e.name !== 'AbortError') console.warn('fetchDeptChildren error:', e);
    return [];
  }
}

function syncCreateHidden() {
  var v = document.getElementById('create-u3').value ||
          document.getElementById('create-u2').value ||
          document.getElementById('create-u1').value || '';
  document.getElementById('create-dept-id').value = v;
}

async function cascadeCreate(level) {
  var s1 = document.getElementById('create-u1');
  var s2 = document.getElementById('create-u2');
  var s3 = document.getElementById('create-u3');
  if (level === 1) {
    s2.innerHTML = '<option value="">— Pilih unit dulu —</option>';
    s3.innerHTML = '<option value="">— Opsional —</option>';
    s2.disabled = s3.disabled = true;
    syncCreateHidden();
    if (!s1.value) return;
    var kids = await fetchCreateDeptChildren(s1.value);
    if (kids.length) {
      s2.innerHTML = '<option value="">— Semua Bidang —</option>' +
        kids.map(function (d) { return '<option value="' + d.id + '">' + d.name + '</option>'; }).join('');
      s2.disabled = false;
    }
    syncCreateHidden();
  } else if (level === 2) {
    s3.innerHTML = '<option value="">— Opsional —</option>';
    s3.disabled = true;
    syncCreateHidden();
    if (!s2.value) return;
    var kids = await fetchCreateDeptChildren(s2.value);
    if (kids.length) {
      s3.innerHTML = '<option value="">— Semua Sub Bidang —</option>' +
        kids.map(function (d) { return '<option value="' + d.id + '">' + d.name + '</option>'; }).join('');
      s3.disabled = false;
    }
    syncCreateHidden();
  } else {
    syncCreateHidden();
  }
}
</script>
