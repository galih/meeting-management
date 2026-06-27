<?php
$baseUrl   = rtrim(BASE_URL, '/');
$list      = $histories ?? [];
$editorUrl = $baseUrl . '/notulen/' . (int)$meeting['id'];
$backUrl   = $baseUrl . '/meetings/' . (int)$meeting['id'];
$total     = count($list);
?>

<!-- ============================================================
     KEMENBUD PALETTE (same tokens as editor.php)
============================================================ -->
<style>
:root {
  --kb-primary:       #7B1C1C;
  --kb-primary-dark:  #5A1212;
  --kb-primary-light: rgba(123,28,28,.08);
  --kb-primary-ring:  rgba(123,28,28,.18);
  --kb-gold:          #C9A84C;
  --kb-gold-dark:     #A8872F;
  --kb-gold-light:    rgba(201,168,76,.14);
  --kb-surface:       #FBF8F3;
  --kb-surface-2:     #F5F0E8;
  --kb-surface-3:     #EDE6D6;
  --kb-border:        #DDD5C4;
  --kb-border-light:  #EDE8DE;
  --kb-text:          #1C1714;
  --kb-text-muted:    #6B6055;
  --kb-text-faint:    #A89E90;
  --kb-radius:        12px;
  --kb-radius-sm:     8px;
  --kb-radius-xs:     6px;
  --kb-shadow-sm:     0 1px 4px rgba(28,23,20,.07);
  --kb-shadow-md:     0 3px 12px rgba(28,23,20,.09);
  --kb-shadow-lg:     0 6px 24px rgba(28,23,20,.12);
  --kb-transition:    180ms cubic-bezier(.16,1,.3,1);
}

/* ── Hero ─────────────────────────────────────────────────────── */
.nhist-hero {
  background: linear-gradient(135deg, #7B1C1C 0%, #9B2020 50%, #6A1515 100%);
  border-radius: var(--kb-radius); overflow: hidden;
  box-shadow: 0 4px 24px rgba(123,28,28,.28);
  position: relative;
}
.nhist-hero::before {
  content:''; position:absolute; bottom:-30px; right:-30px;
  width:160px; height:160px; border-radius:50%;
  background:rgba(201,168,76,.10); pointer-events:none;
}
.nhist-hero::after {
  content:''; position:absolute; top:0; left:0; right:0; bottom:0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E") repeat;
  pointer-events:none;
}
.nhist-hero-inner { padding:1.35rem 1.5rem 0; position:relative; z-index:1; }

.nhist-breadcrumb {
  display:flex; align-items:center; gap:.3rem;
  font-size:11.5px; color:rgba(255,255,255,.6); margin-bottom:.5rem;
}
.nhist-breadcrumb a { color:rgba(255,255,255,.78); text-decoration:none; transition:color var(--kb-transition); }
.nhist-breadcrumb a:hover { color:#fff; }

.nhist-title {
  font-size:clamp(15px,2vw,21px); font-weight:800; color:#fff;
  margin:0; display:flex; align-items:center; gap:.5rem; letter-spacing:-.02em;
}
.nhist-subtitle {
  font-size:13px; color:rgba(255,255,255,.7); margin:.25rem 0 0;
  max-width:60ch; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}

.nhist-btn-editor {
  background:var(--kb-gold); border:1.5px solid var(--kb-gold-dark);
  color:#2D1A00; font-size:12.5px; font-weight:700; border-radius:var(--kb-radius-sm);
  padding:.42rem 1rem; display:inline-flex; align-items:center; gap:.4rem;
  transition:all var(--kb-transition); box-shadow:0 2px 8px rgba(201,168,76,.3);
}
.nhist-btn-editor:hover { background:var(--kb-gold-dark); color:#fff; box-shadow:0 3px 12px rgba(168,135,47,.4); }

/* Stat strip */
.nhist-stat-strip {
  display:flex; align-items:center; flex-wrap:wrap;
  background:rgba(0,0,0,.20); backdrop-filter:blur(6px);
  margin:1rem -1.5rem 0; padding:.6rem 1.5rem;
  gap:.5rem;
}
.nhist-stat-item { display:flex; flex-direction:column; padding:0 .8rem; }
.nhist-stat-val  { font-size:13.5px; font-weight:800; color:#fff; line-height:1.2; }
.nhist-stat-lbl  { font-size:10.5px; color:rgba(255,255,255,.58); letter-spacing:.03em; }
.nhist-stat-gold { color:var(--kb-gold); }
.nhist-stat-sep  { width:1px; height:28px; background:rgba(255,255,255,.18); }

/* Gold underline bar */
.nhist-hero-bar {
  height:4px;
  background: linear-gradient(90deg, var(--kb-gold) 0%, var(--kb-gold-dark) 60%, transparent 100%);
}

/* ── Main / Timeline ─────────────────────────────────────────── */
.nhist-main { max-width:860px; margin:0 auto; }

/* Empty state */
.nhist-empty {
  display:flex; flex-direction:column; align-items:center;
  text-align:center; padding:4rem 2rem;
}
.nhist-empty-icon {
  width:72px; height:72px;
  background:var(--kb-primary-light); border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  margin-bottom:1.1rem; color:var(--kb-primary);
}
.nhist-empty-title { font-size:17px; font-weight:800; color:var(--kb-text); margin-bottom:.4rem; }
.nhist-empty-desc  { font-size:13px; color:var(--kb-text-muted); margin-bottom:1.4rem; max-width:32ch; }
.nhist-btn-empty {
  background:linear-gradient(135deg,var(--kb-primary),#9B2020);
  border:none; color:#fff; font-size:13px; font-weight:700;
  border-radius:var(--kb-radius-sm); padding:.5rem 1.25rem;
  display:inline-flex; align-items:center; gap:.4rem;
  box-shadow:0 3px 10px rgba(123,28,28,.25);
  transition:all var(--kb-transition);
}
.nhist-btn-empty:hover { box-shadow:0 4px 16px rgba(123,28,28,.35); color:#fff; }

/* Timeline container */
.nhist-timeline { position:relative; padding:.5rem 0; }
.nhist-timeline::before {
  content:''; position:absolute; left:18px; top:0; bottom:0;
  width:2px; background:var(--kb-border-light);
}

.nhist-entry {
  position:relative; padding:0 0 1.35rem 52px;
}
.nhist-entry:last-child { padding-bottom:0; }

/* Timeline dot */
.nhist-dot {
  position:absolute; left:10px; top:20px;
  width:18px; height:18px; border-radius:50%;
  background:#fff; border:2.5px solid var(--kb-border);
  box-shadow:var(--kb-shadow-sm);
}
.nhist-dot-latest {
  border-color:var(--kb-primary);
  background:linear-gradient(135deg,var(--kb-primary),#9B2020);
  box-shadow:0 2px 8px rgba(123,28,28,.3);
}

/* Entry card */
.nhist-card {
  background:#fff; border:1px solid var(--kb-border-light);
  border-radius:var(--kb-radius); overflow:hidden;
  box-shadow:var(--kb-shadow-sm);
  transition:box-shadow var(--kb-transition), border-color var(--kb-transition);
}
.nhist-card:hover { box-shadow:var(--kb-shadow-md); border-color:var(--kb-border); }
.nhist-entry-latest .nhist-card {
  border-color:rgba(123,28,28,.22);
  box-shadow:0 3px 16px rgba(123,28,28,.10);
}

/* Card header */
.nhist-card-head {
  display:flex; align-items:center; justify-content:space-between;
  padding:.7rem 1rem; flex-wrap:wrap; gap:.5rem;
  background:var(--kb-surface); border-bottom:1px solid var(--kb-border-light);
}

/* Version badge */
.nhist-ver {
  display:inline-flex; align-items:center;
  font-size:11px; font-weight:800; padding:.2em .7em;
  border-radius:20px; letter-spacing:.04em; white-space:nowrap;
  background:rgba(100,100,100,.1); color:var(--kb-text-muted);
}
.nhist-ver-latest {
  background:var(--kb-primary-light); color:var(--kb-primary);
}
.nhist-latest-chip {
  display:inline-flex; align-items:center;
  background:linear-gradient(135deg,var(--kb-primary),#9B2020); color:#fff;
  font-size:10px; font-weight:800; padding:.2em .65em;
  border-radius:20px; letter-spacing:.04em;
}

/* Editor avatar */
.nhist-avatar {
  width:28px; height:28px; border-radius:50%; flex-shrink:0;
  background:linear-gradient(135deg,var(--kb-primary),#9B2020); color:#fff;
  font-size:12px; font-weight:800;
  display:inline-flex; align-items:center; justify-content:center;
  box-shadow:0 1px 5px rgba(123,28,28,.22);
}
.nhist-editor-name { font-size:13px; font-weight:700; color:var(--kb-text); line-height:1.25; }
.nhist-editor-time { font-size:11px; color:var(--kb-text-muted); }

/* Toggle button */
.nhist-toggle {
  background:transparent; border:1.5px solid var(--kb-border);
  color:var(--kb-text-muted); border-radius:var(--kb-radius-xs);
  font-size:12px; font-weight:600; padding:.3rem .8rem;
  display:inline-flex; align-items:center; gap:.35rem;
  cursor:pointer; transition:all var(--kb-transition); white-space:nowrap;
}
.nhist-toggle:hover, .nhist-toggle[aria-expanded="true"] {
  border-color:var(--kb-primary); color:var(--kb-primary);
  background:var(--kb-primary-light);
}

/* Content box */
.nhist-content-box {
  padding:.85rem 1rem;
  max-height:340px; overflow-y:auto;
  background:var(--kb-surface);
  border-top:1px solid var(--kb-border-light);
}
.nhist-content-text {
  font-size:13px; color:var(--kb-text); line-height:1.75;
  white-space:pre-wrap; word-break:break-word;
}
.nhist-content-json {
  font-size:11px; color:var(--kb-text-muted); line-height:1.5;
  white-space:pre-wrap; word-break:break-word; margin:0;
  font-family: monospace;
}
.nhist-content-html {
  font-size:13.5px; color:var(--kb-text); line-height:1.75;
}
.nhist-content-box::-webkit-scrollbar { width:5px; }
.nhist-content-box::-webkit-scrollbar-track { background:transparent; }
.nhist-content-box::-webkit-scrollbar-thumb { background:var(--kb-border); border-radius:3px; }

/* ── Responsive ─────────────────────────────────────────────── */
@media(max-width:767.98px) {
  .nhist-hero-inner { padding:1rem 1rem 0; }
  .nhist-title { font-size:15px; }
  .nhist-stat-strip { flex-wrap:wrap; padding:.5rem 1rem; margin:1rem -1rem 0; }
  .nhist-timeline::before { left:14px; }
  .nhist-entry { padding-left:42px; }
  .nhist-dot { left:6px; }
}
</style>

<!-- ============================================================
     HERO HEADER
============================================================ -->
<div class="nhist-hero mb-4">
  <div class="nhist-hero-inner">
    <!-- Breadcrumb -->
    <nav class="nhist-breadcrumb">
      <a href="<?= $backUrl ?>">Detail Kegiatan</a>
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="<?= $editorUrl ?>">Notulen</a>
      <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Riwayat</span>
    </nav>

    <!-- Title row -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-1">
      <div style="flex:1; min-width:0;">
        <h1 class="nhist-title">
          <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.85;flex-shrink:0;"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
          Riwayat Notulen
        </h1>
        <p class="nhist-subtitle"><?= htmlspecialchars($meeting['title']) ?></p>
      </div>
      <a href="<?= $editorUrl ?>" class="btn nhist-btn-editor">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
        Buka Editor
      </a>
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
        <span class="nhist-stat-val nhist-stat-gold"><?= htmlspecialchars($list[0]['editor_name'] ?? '—') ?></span>
        <span class="nhist-stat-lbl">Edit Terakhir Oleh</span>
      </div>
      <div class="nhist-stat-sep"></div>
      <div class="nhist-stat-item">
        <span class="nhist-stat-val nhist-stat-gold"><?= date('d M Y · H:i', strtotime($list[0]['created_at'])) ?></span>
        <span class="nhist-stat-lbl">Waktu Terakhir</span>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="nhist-hero-bar"></div>
</div>

<!-- ============================================================
     TIMELINE
============================================================ -->
<div class="nhist-main">
  <?php if (empty($list)): ?>
  <!-- Empty state -->
  <div class="nhist-empty">
    <div class="nhist-empty-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-5.23"/></svg>
    </div>
    <h3 class="nhist-empty-title">Belum ada riwayat</h3>
    <p class="nhist-empty-desc">Riwayat perubahan akan muncul setelah notulen pertama kali disimpan</p>
    <a href="<?= $editorUrl ?>" class="nhist-btn-empty">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
      Buka Editor Notulen
    </a>
  </div>

  <?php else: ?>
  <!-- Timeline list -->
  <div class="nhist-timeline">
    <?php foreach ($list as $i => $h):
      $vNum     = $h['version'] ?? ($total - $i);
      $isLatest = ($i === 0);
      $raw      = $h['content'] ?? '';
      $decoded  = json_decode($raw, true);
      $uniqId   = 'nhist-body-' . $i;
    ?>
    <div class="nhist-entry <?= $isLatest ? 'nhist-entry-latest' : '' ?>">

      <!-- Dot -->
      <div class="nhist-dot <?= $isLatest ? 'nhist-dot-latest' : '' ?>"></div>

      <!-- Card -->
      <div class="nhist-card">

        <!-- Card header -->
        <div class="nhist-card-head">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Version badge -->
            <span class="nhist-ver <?= $isLatest ? 'nhist-ver-latest' : '' ?>">v<?= $vNum ?></span>
            <?php if ($isLatest): ?>
            <span class="nhist-latest-chip">Versi Terbaru</span>
            <?php endif; ?>
            <!-- Editor info -->
            <div class="d-flex align-items-center gap-2" style="margin-left:.2rem;">
              <span class="nhist-avatar"><?= strtoupper(mb_substr($h['editor_name'] ?? 'S', 0, 1)) ?></span>
              <div>
                <div class="nhist-editor-name"><?= htmlspecialchars($h['editor_name'] ?? '—') ?></div>
                <div class="nhist-editor-time"><?= date('d M Y, H:i', strtotime($h['created_at'])) ?></div>
              </div>
            </div>
          </div>
          <!-- Toggle button -->
          <button class="nhist-toggle" onclick="nhToggle('<?= $uniqId ?>', this)" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Lihat Konten
          </button>
        </div>

        <!-- Collapsible content -->
        <div id="<?= $uniqId ?>" style="display:none;">
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

<script>
function nhToggle(id, btn) {
  const el = document.getElementById(id);
  if (!el) return;
  const open = el.style.display === 'none' || !el.style.display;
  el.style.display = open ? 'block' : 'none';
  btn.setAttribute('aria-expanded', open ? 'true' : 'false');
  btn.innerHTML = open
    ? `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg> Sembunyikan`
    : `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg> Lihat Konten`;
}
</script>
