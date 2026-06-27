<?php
$baseUrl  = rtrim(BASE_URL, '/');
$list     = $histories ?? [];
$editorUrl = $baseUrl . '/notulen/' . (int)$meeting['id'];
$backUrl   = $baseUrl . '/meetings/' . (int)$meeting['id'];
$total     = count($list);
?>

<!-- ============================  HERO HEADER  ============================ -->
<div class="nhist-hero mb-4">
  <div class="nhist-hero-inner">
    <nav class="nhist-breadcrumb">
      <a href="<?= $backUrl ?>">Detail Kegiatan</a>
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="<?= $editorUrl ?>">Notulen</a>
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Riwayat</span>
    </nav>
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h1 class="nhist-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
          Riwayat Notulen
        </h1>
        <p class="nhist-subtitle"><?= htmlspecialchars($meeting['title']) ?></p>
      </div>
      <div class="d-flex gap-2">
        <a href="<?= $editorUrl ?>" class="btn nhist-btn-back">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          Buka Editor
        </a>
      </div>
    </div>
    <!-- Stat strip -->
    <div class="nhist-stat-strip">
      <div class="nhist-stat-item">
        <span class="nhist-stat-val"><?= $total ?></span>
        <span class="nhist-stat-lbl">Total Revisi</span>
      </div>
      <?php if ($total > 0): ?>
      <div class="nhist-stat-sep"></div>
      <div class="nhist-stat-item">
        <span class="nhist-stat-val nhist-stat-accent"><?= htmlspecialchars($list[0]['editor_name'] ?? '—') ?></span>
        <span class="nhist-stat-lbl">Edit Terakhir oleh</span>
      </div>
      <div class="nhist-stat-sep"></div>
      <div class="nhist-stat-item">
        <span class="nhist-stat-val nhist-stat-accent"><?= date('d M Y · H:i', strtotime($list[0]['created_at'])) ?></span>
        <span class="nhist-stat-lbl">Waktu Terakhir</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============================  TIMELINE  ============================ -->
<div class="nhist-main">
  <?php if (empty($list)): ?>
  <div class="nhist-empty">
    <div class="nhist-empty-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
    </div>
    <h3 class="nhist-empty-title">Belum ada riwayat</h3>
    <p class="nhist-empty-desc">Riwayat perubahan akan muncul setelah notulen pertama kali disimpan</p>
    <a href="<?= $editorUrl ?>" class="btn nhist-btn-back">Buka Editor Notulen</a>
  </div>
  <?php else: ?>
  <div class="nhist-timeline">
    <?php foreach ($list as $i => $h):
      $vNum   = $h['version'] ?? ($total - $i);
      $isLatest = ($i === 0);
      $raw    = $h['content'] ?? '';
      $decoded = json_decode($raw, true);
      $uniqId = 'hist-detail-' . $i;
    ?>
    <div class="nhist-entry <?= $isLatest ? 'nhist-entry-latest' : '' ?>">
      <div class="nhist-timeline-dot <?= $isLatest ? 'nhist-dot-latest' : '' ?>"></div>
      <div class="nhist-entry-card">
        <div class="nhist-entry-header">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="nhist-version-badge <?= $isLatest ? 'nhist-version-latest' : '' ?>">
              v<?= $vNum ?>
            </span>
            <?php if ($isLatest): ?>
            <span class="nhist-latest-chip">Versi Terbaru</span>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2 ms-1">
              <span class="nhist-editor-avatar"><?= strtoupper(mb_substr($h['editor_name'] ?? 'S', 0, 1)) ?></span>
              <div>
                <div class="nhist-editor-name"><?= htmlspecialchars($h['editor_name'] ?? '—') ?></div>
                <div class="nhist-editor-time"><?= date('d M Y, H:i', strtotime($h['created_at'])) ?></div>
              </div>
            </div>
          </div>
          <button class="nhist-toggle-btn" onclick="toggleHistDetail('<?= $uniqId ?>', this)" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat Konten
          </button>
        </div>
        <div class="nhist-entry-content" id="<?= $uniqId ?>" style="display:none;">
          <div class="nhist-content-box">
            <?php
              if ($decoded && isset($decoded['ops'])) {
                $texts = array_map(fn($op) => $op['insert'] ?? '', $decoded['ops']);
                echo '<div class="nhist-content-text">' . nl2br(htmlspecialchars(implode('', $texts))) . '</div>';
              } elseif ($decoded !== null) {
                echo '<pre class="nhist-content-json">' . htmlspecialchars(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) . '</pre>';
              } else {
                echo '<div class="nhist-content-html">' . $raw . '</div>';
              }
            ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

<!-- ============================  STYLES  ============================ -->
<style>
/* ─ Hero ────────────────────────────────────────────────── */
.nhist-hero {
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 55%, #A83218 100%);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
  position: relative;
}
.nhist-hero::after {
  content:''; position:absolute; top:-40px; right:-40px;
  width:180px; height:180px; border-radius:50%;
  background:rgba(201,168,76,.09); pointer-events:none;
}
.nhist-hero-inner { padding:1.25rem 1.5rem 0; }

.nhist-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:12px; color:rgba(255,255,255,.65); margin-bottom:.5rem;
}
.nhist-breadcrumb a { color:rgba(255,255,255,.75); text-decoration:none; }
.nhist-breadcrumb a:hover { color:#fff; }

.nhist-title {
  font-size:clamp(16px,2.2vw,22px); font-weight:800; color:#fff;
  margin:0; display:flex; align-items:center; gap:.5rem; letter-spacing:-.02em;
}
.nhist-subtitle {
  font-size:13px; color:rgba(255,255,255,.72); margin:.2rem 0 0;
  max-width:60ch; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

.nhist-btn-back {
  background:var(--gold); border:1px solid var(--gold-dark); color:#3D0A0A;
  font-size:13px; font-weight:700; border-radius:8px;
  padding:.42rem 1rem; display:inline-flex; align-items:center; gap:.4rem;
  transition:all .15s;
}
.nhist-btn-back:hover { background:var(--gold-dark); color:#fff; }

/* Stat strip */
.nhist-stat-strip {
  display:flex; align-items:center; flex-wrap:wrap;
  background:rgba(0,0,0,.18); backdrop-filter:blur(4px);
  margin:0 -1.5rem; padding:.55rem 1.5rem;
  gap:.5rem;
}
.nhist-stat-item { display:flex; flex-direction:column; padding:0 .75rem; }
.nhist-stat-val  { font-size:14px; font-weight:800; color:#fff; line-height:1.2; }
.nhist-stat-lbl  { font-size:11px; color:rgba(255,255,255,.6); }
.nhist-stat-accent { color:var(--gold); }
.nhist-stat-sep  { width:1px; height:30px; background:rgba(255,255,255,.18); }

/* ─ Main ────────────────────────────────────────────────── */
.nhist-main { max-width:860px; margin:0 auto; }

/* Empty */
.nhist-empty {
  display:flex; flex-direction:column; align-items:center;
  text-align:center; padding:4rem 2rem;
}
.nhist-empty-icon {
  width:72px; height:72px; background:var(--brand-light); border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  margin-bottom:1rem; color:var(--brand);
}
.nhist-empty-title { font-size:17px; font-weight:700; color:var(--text-main); margin-bottom:.4rem; }
.nhist-empty-desc  { font-size:13px; color:var(--text-muted); margin-bottom:1.25rem; max-width:32ch; }

/* Timeline */
.nhist-timeline { position:relative; padding:.5rem 0; }
.nhist-timeline::before {
  content:''; position:absolute; left:18px; top:0; bottom:0;
  width:2px; background:var(--border);
}

.nhist-entry {
  position:relative; padding:0 0 1.25rem 52px;
}
.nhist-entry:last-child { padding-bottom:0; }

.nhist-timeline-dot {
  position:absolute; left:10px; top:18px;
  width:18px; height:18px; border-radius:50%;
  background:#fff; border:2.5px solid var(--border);
  box-shadow:0 1px 4px rgba(0,0,0,.08);
}
.nhist-dot-latest { border-color:var(--brand); background:var(--brand); }

.nhist-entry-card {
  background:#fff; border:1px solid var(--border-light);
  border-radius:12px; overflow:hidden;
  box-shadow:0 2px 8px rgba(0,0,0,.05);
  transition:box-shadow .15s;
}
.nhist-entry-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.09); }
.nhist-entry-latest .nhist-entry-card {
  border-color:rgba(123,28,28,.25);
  box-shadow:0 3px 14px rgba(123,28,28,.10);
}

.nhist-entry-header {
  display:flex; align-items:center; justify-content:space-between;
  padding:.7rem 1rem; flex-wrap:wrap; gap:.5rem;
  border-bottom:1px solid var(--border-light); background:#faf9f7;
}

.nhist-version-badge {
  display:inline-flex; align-items:center;
  font-size:11px; font-weight:800; padding:.2em .65em;
  border-radius:20px; letter-spacing:.04em; white-space:nowrap;
  background:rgba(100,100,100,.10); color:#64748b;
}
.nhist-version-latest { background:rgba(123,28,28,.12); color:var(--brand); }

.nhist-latest-chip {
  display:inline-flex; align-items:center;
  background:var(--brand); color:#fff;
  font-size:10px; font-weight:700; padding:.2em .6em;
  border-radius:20px; letter-spacing:.04em;
}

.nhist-editor-avatar {
  width:28px; height:28px; border-radius:50%;
  background:var(--brand); color:#fff;
  font-size:12px; font-weight:800;
  display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
}
.nhist-editor-name { font-size:13px; font-weight:600; color:var(--text-main); line-height:1.2; }
.nhist-editor-time { font-size:11px; color:var(--text-muted); }

.nhist-toggle-btn {
  background:transparent; border:1.5px solid var(--border);
  color:var(--text-muted); border-radius:7px;
  font-size:12px; font-weight:600; padding:.3rem .75rem;
  display:inline-flex; align-items:center; gap:.35rem;
  cursor:pointer; transition:all .14s; white-space:nowrap;
}
.nhist-toggle-btn:hover,
.nhist-toggle-btn[aria-expanded="true"] { border-color:var(--brand); color:var(--brand); }

.nhist-entry-content { }
.nhist-content-box {
  padding:.75rem 1rem;
  max-height:320px; overflow-y:auto;
  background:#fafafa;
}
.nhist-content-text {
  font-size:13px; color:var(--text-main); line-height:1.7;
  white-space:pre-wrap; word-break:break-word;
}
.nhist-content-json {
  font-size:11.5px; color:var(--text-muted); line-height:1.5;
  white-space:pre-wrap; word-break:break-word; margin:0;
}
.nhist-content-html {
  font-size:13.5px; color:var(--text-main); line-height:1.7;
}

/* Scrollbar */
.nhist-content-box::-webkit-scrollbar { width:5px; }
.nhist-content-box::-webkit-scrollbar-thumb { background:var(--border); border-radius:3px; }

@media(max-width:767.98px) {
  .nhist-hero-inner { padding:1rem 1rem 0; }
  .nhist-title { font-size:16px; }
  .nhist-stat-strip { flex-wrap:wrap; padding:.5rem 1rem; margin:0 -1rem; }
  .nhist-timeline::before { left:14px; }
  .nhist-entry { padding-left:40px; }
  .nhist-timeline-dot { left:6px; }
}
</style>

<script>
function toggleHistDetail(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const open = el.style.display === 'none' || el.style.display === '';
  el.style.display = open ? 'block' : 'none';
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  btn.innerHTML = open
    ? `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg> Sembunyikan`
    : `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> Lihat Konten`;
}
</script>
