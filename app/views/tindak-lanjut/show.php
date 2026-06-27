<?php
$baseUrl     = rtrim(BASE_URL, '/');
$statusMap   = [
    'pending'     => ['label' => 'Menunggu',    'color' => 'secondary'],
    'in_progress' => ['label' => 'Berlangsung', 'color' => 'blue'],
    'done'        => ['label' => 'Selesai',     'color' => 'green'],
    'cancelled'   => ['label' => 'Dibatalkan',  'color' => 'red'],
];
$priorityMap = [
    'low'    => ['label' => 'Rendah', 'color' => 'green'],
    'medium' => ['label' => 'Sedang', 'color' => 'orange'],
    'high'   => ['label' => 'Tinggi', 'color' => 'red'],
];
$st          = $statusMap[$tl['status']]     ?? ['label' => $tl['status'],     'color' => 'secondary'];
$pr          = $priorityMap[$tl['priority']] ?? ['label' => $tl['priority'],   'color' => 'secondary'];
$isAdminLike = Auth::hasRole('admin', 'sekretaris');
$isOverdue   = !empty($tl['due_date'])
    && $tl['due_date'] < date('Y-m-d')
    && !in_array($tl['status'], ['done', 'cancelled']);
?>

<!-- ==============================  HERO  ============================== -->
<div class="tl-hero mb-4">
  <div class="tl-hero-inner">

    <!-- Breadcrumb -->
    <nav class="tl-breadcrumb">
      <a href="<?= $baseUrl ?>/tindak-lanjut">Tindak Lanjut</a>
      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>"><?= htmlspecialchars($tl['meeting_title']) ?></a>
      <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
      <span>Detail</span>
    </nav>

    <!-- Title row -->
    <div class="d-flex flex-wrap align-items-flex-start justify-content-between gap-3 mt-2">
      <div>
        <h1 class="tl-hero-title"><?= htmlspecialchars($tl['description']) ?></h1>
        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
          <!-- Status badge -->
          <span class="tl-badge tl-badge-<?= $st['color'] ?>"><?= $st['label'] ?></span>
          <!-- Priority badge -->
          <span class="tl-badge tl-badge-<?= $pr['color'] ?> tl-badge-outline">
            <?php
            $prioIcons = ['high'=>'▲','medium'=>'■','low'=>'▼'];
            echo ($prioIcons[$tl['priority']] ?? '') . ' ' . $pr['label'];
            ?>
          </span>
          <?php if ($isOverdue): ?>
          <span class="tl-badge tl-badge-red">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Terlambat
          </span>
          <?php endif; ?>
        </div>
      </div>

      <!-- Actions -->
      <div class="d-flex flex-wrap gap-2">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="btn tl-btn-ghost">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Lihat Meeting
        </a>
        <?php if ($isAdminLike): ?>
        <button class="btn tl-btn-danger" id="btn-delete-tl">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
          Hapus
        </button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Meta strip -->
  <div class="tl-meta-strip">
    <?php if (!empty($tl['assignee_name'])): ?>
    <div class="tl-meta-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <span>PIC: <strong><?= htmlspecialchars($tl['assignee_name']) ?></strong></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($tl['due_date'])): ?>
    <div class="tl-meta-item <?= $isOverdue ? 'tl-meta-danger' : '' ?>">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <span>Deadline: <strong><?= date('d M Y', strtotime($tl['due_date'])) ?></strong></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($tl['creator_name'])): ?>
    <div class="tl-meta-item">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <span>Dibuat oleh: <strong><?= htmlspecialchars($tl['creator_name']) ?></strong></span>
    </div>
    <?php endif; ?>
    <?php if (!empty($tl['completed_at'])): ?>
    <div class="tl-meta-item tl-meta-success">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
      <span>Selesai: <strong><?= date('d M Y H:i', strtotime($tl['completed_at'])) ?></strong></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- ==============================  BODY  ============================== -->
<div class="row g-3">

  <!-- ── Kolom kiri: progress notes ── -->
  <div class="col-lg-8">
    <div class="tl-card">

      <!-- Card header -->
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Progress Notes
        <?php if (!empty($notes)): ?>
        <span class="tl-count-badge"><?= count($notes) ?></span>
        <?php endif; ?>
      </div>

      <!-- Notes list -->
      <div id="notes-list" class="tl-notes-list">
        <?php if (empty($notes)): ?>
        <div class="tl-empty" id="notes-empty">
          <div class="tl-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          </div>
          <p>Belum ada catatan progress</p>
        </div>
        <?php else: ?>
          <?php foreach ($notes as $note): ?>
          <div class="tl-note-item" data-note-id="<?= (int)$note['id'] ?>">
            <div class="tl-note-avatar"><?= strtoupper(mb_substr($note['author_name'], 0, 1)) ?></div>
            <div class="tl-note-body">
              <div class="tl-note-meta">
                <span class="tl-note-author"><?= htmlspecialchars($note['author_name']) ?></span>
                <span class="tl-note-time"><?= date('d M Y · H:i', strtotime($note['created_at'])) ?></span>
              </div>
              <div class="tl-note-text"><?= nl2br(htmlspecialchars($note['note'])) ?></div>
            </div>
            <?php if ($note['can_delete']): ?>
            <button class="tl-note-delete btn-delete-note" data-id="<?= (int)$note['id'] ?>" title="Hapus catatan">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- Note input -->
      <?php if ($canEdit): ?>
      <div class="tl-note-input-area">
        <div class="tl-note-input-wrap">
          <textarea id="note-input" class="tl-note-textarea" rows="2"
                    placeholder="Tulis catatan progress... (Ctrl+Enter untuk kirim)"></textarea>
          <button id="note-submit" class="tl-btn-send">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            Kirim
          </button>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Kolom kanan: info + ubah status ── -->
  <div class="col-lg-4">

    <!-- Info card -->
    <div class="tl-card mb-3">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Info Tindak Lanjut
      </div>
      <div class="tl-info-list">

        <div class="tl-info-row">
          <span class="tl-info-label">Meeting</span>
          <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tl-info-link">
            <?= htmlspecialchars($tl['meeting_title']) ?>
          </a>
        </div>

        <div class="tl-info-row">
          <span class="tl-info-label">PIC</span>
          <?php if (!empty($tl['assignee_name'])): ?>
          <div class="d-flex align-items-center gap-2">
            <span class="tl-avatar"><?= strtoupper(mb_substr($tl['assignee_name'], 0, 1)) ?></span>
            <span class="tl-info-val"><?= htmlspecialchars($tl['assignee_name']) ?></span>
          </div>
          <?php else: ?><span class="tl-info-val text-muted">—</span><?php endif; ?>
        </div>

        <div class="tl-info-row">
          <span class="tl-info-label">Dibuat oleh</span>
          <span class="tl-info-val"><?= htmlspecialchars($tl['creator_name'] ?? '—') ?></span>
        </div>

        <div class="tl-info-row">
          <span class="tl-info-label">Deadline</span>
          <span class="tl-info-val <?= $isOverdue ? 'tl-text-danger' : '' ?>">
            <?= !empty($tl['due_date']) ? date('d M Y', strtotime($tl['due_date'])) : '—' ?>
            <?php if ($isOverdue): ?><span class="tl-badge tl-badge-red ms-1">Terlambat</span><?php endif; ?>
          </span>
        </div>

        <div class="tl-info-row">
          <span class="tl-info-label">Status</span>
          <span class="tl-badge tl-badge-<?= $st['color'] ?>"><?= $st['label'] ?></span>
        </div>

        <div class="tl-info-row">
          <span class="tl-info-label">Prioritas</span>
          <span class="tl-badge tl-badge-<?= $pr['color'] ?>"><?= $pr['label'] ?></span>
        </div>

        <?php if (!empty($tl['completed_at'])): ?>
        <div class="tl-info-row">
          <span class="tl-info-label">Selesai pada</span>
          <span class="tl-info-val tl-text-success"><?= date('d M Y H:i', strtotime($tl['completed_at'])) ?></span>
        </div>
        <?php endif; ?>

      </div>
    </div>

    <!-- Ubah status card -->
    <?php if ($canEdit): ?>
    <div class="tl-card mb-3">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 1 1-.49-8.85"/></svg>
        Ubah Status
      </div>
      <div class="tl-card-body">
        <div class="d-grid gap-2">
          <?php foreach ($statusMap as $val => $info): ?>
          <button class="tl-status-btn <?= $tl['status'] === $val ? 'tl-status-btn-active' : '' ?>"
                  data-status="<?= $val ?>">
            <span class="tl-status-dot tl-dot-<?= $info['color'] ?>"></span>
            <?= $info['label'] ?>
            <?php if ($tl['status'] === $val): ?>
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="ms-auto"><polyline points="20 6 9 17 4 12"/></svg>
            <?php endif; ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Nav card -->
    <div class="tl-card">
      <div class="tl-card-header">
        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
        Navigasi
      </div>
      <div class="tl-nav-links">
        <a href="<?= $baseUrl ?>/meetings/<?= (int)$tl['meeting_id'] ?>" class="tl-nav-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Kembali ke Meeting
        </a>
        <a href="<?= $baseUrl ?>/tindak-lanjut" class="tl-nav-link">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
          Semua Tindak Lanjut
        </a>
      </div>
    </div>

  </div>
</div>

<!-- ==============================  STYLES  ============================== -->
<style>
/* ── Hero ──────────────────────────────────────────────── */
.tl-hero {
  background: linear-gradient(135deg, var(--brand) 0%, #9B2020 60%, #A83218 100%);
  border-radius: 14px;
  box-shadow: 0 4px 20px rgba(123,28,28,.22);
  overflow: hidden; position: relative;
}
.tl-hero::after {
  content: ''; position: absolute; top: -40px; right: -40px;
  width: 180px; height: 180px; border-radius: 50%;
  background: rgba(201,168,76,.09); pointer-events: none;
}
.tl-hero-inner { padding: 1.4rem 1.6rem 1rem; }

.tl-breadcrumb {
  display: flex; align-items: center; gap: .3rem;
  font-size: 12px; color: rgba(255,255,255,.65); margin-bottom: .4rem;
}
.tl-breadcrumb a { color: rgba(255,255,255,.75); text-decoration: none; }
.tl-breadcrumb a:hover { color: #fff; }

.tl-hero-title {
  font-size: clamp(15px, 2.5vw, 22px);
  font-weight: 800; color: #fff; margin: 0;
  letter-spacing: -.02em; line-height: 1.3;
}

/* Meta strip */
.tl-meta-strip {
  display: flex; align-items: center; flex-wrap: wrap; gap: .5rem;
  background: rgba(0,0,0,.18); padding: .55rem 1.6rem;
  font-size: 13px; color: rgba(255,255,255,.82); backdrop-filter: blur(4px);
}
.tl-meta-item { display: flex; align-items: center; gap: .35rem; }
.tl-meta-danger strong { color: #fca5a5; }
.tl-meta-success strong { color: #bbf7d0; }

/* Hero buttons */
.tl-btn-ghost {
  background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.3);
  color: #fff; font-size: 13px; font-weight: 600; border-radius: 8px;
  display: inline-flex; align-items: center; gap: .35rem; padding: .42rem 1rem;
}
.tl-btn-ghost:hover { background: rgba(255,255,255,.25); color: #fff; }
.tl-btn-danger {
  background: rgba(192,57,43,.25); border: 1.5px solid rgba(192,57,43,.5);
  color: #fca5a5; font-size: 13px; font-weight: 600; border-radius: 8px;
  display: inline-flex; align-items: center; gap: .35rem; padding: .42rem 1rem;
  cursor: pointer;
}
.tl-btn-danger:hover { background: rgba(192,57,43,.45); color: #fff; }

/* ── Badge ──────────────────────────────────────────────── */
.tl-badge {
  display: inline-flex; align-items: center; gap: .25rem;
  font-size: 11.5px; font-weight: 700; padding: .28em .7em;
  border-radius: 20px; white-space: nowrap;
}
.tl-badge-red       { background: rgba(168,37,21,.10);  color: #a82515; }
.tl-badge-orange    { background: rgba(201,168,76,.15);  color: #7a5800; }
.tl-badge-green     { background: rgba(47,107,64,.10);   color: #1e7a2e; }
.tl-badge-blue      { background: rgba(32,107,196,.10);  color: #1557a0; }
.tl-badge-secondary { background: rgba(100,100,100,.10); color: #64748b; }
/* outline variant (used in hero for priority) */
.tl-badge-outline.tl-badge-red       { background: rgba(168,37,21,.06);  border: 1px solid rgba(168,37,21,.3); }
.tl-badge-outline.tl-badge-orange    { background: rgba(201,168,76,.06);  border: 1px solid rgba(201,168,76,.4); }
.tl-badge-outline.tl-badge-green     { background: rgba(47,107,64,.06);   border: 1px solid rgba(47,107,64,.3); }
/* badge on dark hero bg */
.tl-hero .tl-badge-red       { background: rgba(252,165,165,.18); color: #fecaca; border-color: rgba(252,165,165,.3); }
.tl-hero .tl-badge-orange    { background: rgba(253,211,77,.18);  color: #fde68a; border-color: rgba(253,211,77,.3); }
.tl-hero .tl-badge-green     { background: rgba(134,239,172,.18); color: #bbf7d0; border-color: rgba(134,239,172,.3); }
.tl-hero .tl-badge-blue      { background: rgba(147,197,253,.18); color: #bfdbfe; border-color: rgba(147,197,253,.3); }
.tl-hero .tl-badge-secondary { background: rgba(255,255,255,.15); color: rgba(255,255,255,.85); }

/* ── Card ───────────────────────────────────────────────── */
.tl-card {
  border: 1px solid var(--border-light);
  border-radius: 14px; overflow: hidden;
  box-shadow: 0 2px 10px rgba(0,0,0,.06);
  background: #fff;
}
.tl-card-header {
  display: flex; align-items: center; gap: .4rem;
  font-size: 12px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
  color: var(--brand); background: #faf4eb;
  padding: .6rem .9rem; border-bottom: 1px solid var(--border-light);
}
.tl-count-badge {
  background: var(--brand); color: #fff;
  font-size: 10.5px; font-weight: 700; padding: .1em .55em;
  border-radius: 20px; margin-left: .2rem;
}
.tl-card-body { padding: .85rem; }

/* ── Info list ──────────────────────────────────────────── */
.tl-info-list { padding: .2rem 0; }
.tl-info-row {
  display: flex; align-items: flex-start; gap: .5rem;
  padding: .55rem .9rem;
  border-bottom: 1px solid var(--border-light);
  font-size: 13px;
}
.tl-info-row:last-child { border-bottom: none; }
.tl-info-label {
  min-width: 90px; font-size: 11.5px; font-weight: 700;
  text-transform: uppercase; letter-spacing: .04em;
  color: var(--text-muted); padding-top: .1rem;
}
.tl-info-val    { color: var(--text-main); }
.tl-info-link   { color: var(--brand); text-decoration: none; font-weight: 500; }
.tl-info-link:hover { text-decoration: underline; }
.tl-text-danger { color: #a82515; font-weight: 700; }
.tl-text-success{ color: #1e7a2e; font-weight: 700; }

/* ── Avatar ─────────────────────────────────────────────── */
.tl-avatar {
  width: 26px; height: 26px; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 11px; font-weight: 700;
  display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Status buttons ─────────────────────────────────────── */
.tl-status-btn {
  display: flex; align-items: center; gap: .5rem;
  background: #faf4eb; border: 1.5px solid var(--border-light);
  border-radius: 8px; padding: .55rem .85rem;
  font-size: 13px; font-weight: 600; color: var(--text-main);
  cursor: pointer; text-align: left; transition: all .14s;
}
.tl-status-btn:hover { border-color: var(--brand); color: var(--brand); background: #fff; }
.tl-status-btn-active {
  background: var(--brand); color: #fff; border-color: var(--brand-dark);
}
.tl-status-btn-active:hover { background: var(--brand-dark); color: #fff; border-color: var(--brand-dark); }
.tl-status-dot {
  width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}
.tl-dot-secondary { background: #94a3b8; }
.tl-dot-blue      { background: #2d6ec4; }
.tl-dot-green     { background: #1e7a2e; }
.tl-dot-red       { background: #a82515; }

/* ── Nav links ──────────────────────────────────────────── */
.tl-nav-links { display: flex; flex-direction: column; }
.tl-nav-link {
  display: flex; align-items: center; gap: .5rem;
  padding: .65rem .9rem; font-size: 13px; font-weight: 500;
  color: var(--text-main); text-decoration: none;
  border-bottom: 1px solid var(--border-light);
  transition: background .13s, color .13s;
}
.tl-nav-link:last-child { border-bottom: none; }
.tl-nav-link:hover { background: #faf4eb; color: var(--brand); }
.tl-nav-link svg { color: var(--brand); }

/* ── Notes ──────────────────────────────────────────────── */
.tl-notes-list { min-height: 120px; }

.tl-empty {
  display: flex; flex-direction: column; align-items: center;
  padding: 3rem 2rem; text-align: center; color: var(--text-muted);
}
.tl-empty-icon {
  width: 60px; height: 60px; background: var(--brand-light); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  margin-bottom: .85rem; color: var(--brand);
}
.tl-empty p { font-size: 13.5px; margin: 0; }

.tl-note-item {
  display: flex; align-items: flex-start; gap: .75rem;
  padding: .9rem 1rem; border-bottom: 1px solid var(--border-light);
  transition: background .12s;
}
.tl-note-item:last-child { border-bottom: none; }
.tl-note-item:hover { background: #faf6ef; }

.tl-note-avatar {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--brand); color: #fff;
  font-size: 12px; font-weight: 700;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.tl-note-body    { flex: 1; min-width: 0; }
.tl-note-meta    { display: flex; justify-content: space-between; align-items: baseline; gap: .5rem; margin-bottom: .3rem; }
.tl-note-author  { font-size: 13px; font-weight: 700; color: var(--text-main); }
.tl-note-time    { font-size: 11.5px; color: var(--text-muted); white-space: nowrap; }
.tl-note-text    { font-size: 13.5px; color: var(--text-main); line-height: 1.55; }

.tl-note-delete {
  background: none; border: none; padding: .25rem;
  color: var(--text-muted); cursor: pointer; border-radius: 6px;
  display: flex; align-items: center; flex-shrink: 0;
  transition: color .12s, background .12s;
}
.tl-note-delete:hover { color: #a82515; background: rgba(168,37,21,.08); }

/* Note input */
.tl-note-input-area {
  padding: .85rem; border-top: 1px solid var(--border-light); background: #faf6ef;
}
.tl-note-input-wrap { display: flex; gap: .6rem; align-items: flex-end; }
.tl-note-textarea {
  flex: 1; resize: vertical; min-height: 60px;
  border: 1.5px solid var(--border); border-radius: 8px;
  font-size: 13.5px; padding: .5rem .75rem;
  transition: border-color .14s;
}
.tl-note-textarea:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 3px rgba(123,28,28,.1); }
.tl-btn-send {
  background: var(--brand); border: none; color: #fff;
  font-size: 13px; font-weight: 700; border-radius: 8px;
  padding: .5rem 1rem; display: inline-flex; align-items: center; gap: .35rem;
  cursor: pointer; white-space: nowrap; transition: all .14s; flex-shrink: 0;
}
.tl-btn-send:hover { background: var(--brand-dark); box-shadow: 0 3px 10px rgba(123,28,28,.22); }
.tl-btn-send:disabled { opacity: .6; cursor: default; }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width: 767.98px) {
  .tl-hero-inner { padding: 1rem; }
  .tl-hero-title { font-size: 15px; }
  .tl-meta-strip { padding: .5rem 1rem; font-size: 12px; }
}
</style>

<script>
const TL_ID   = <?= (int)$tl['id'] ?>;
const BASE_URL = '<?= $baseUrl ?>';

/* ── Status buttons ─────────────────────────────────────── */
document.querySelectorAll('.tl-status-btn').forEach(btn => {
  btn.addEventListener('click', async () => {
    const status = btn.dataset.status;
    if (btn.classList.contains('tl-status-btn-active')) return;
    btn.disabled = true;
    try {
      const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status })
      });
      const d = await res.json();
      if (d.success) { location.reload(); }
      else { alert(d.message || 'Gagal menyimpan status'); }
    } finally { btn.disabled = false; }
  });
});

/* ── Kirim Note ─────────────────────────────────────────── */
const noteInput  = document.getElementById('note-input');
const noteSubmit = document.getElementById('note-submit');

async function submitNote() {
  if (!noteSubmit || !noteInput) return;
  const note = noteInput.value.trim();
  if (!note) return;
  noteSubmit.disabled = true;
  try {
    const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/notes`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ note })
    });
    const d = await res.json();
    if (d.success) {
      noteInput.value = '';
      const empty = document.getElementById('notes-empty');
      if (empty) empty.remove();
      const n   = d.note;
      const div = document.createElement('div');
      div.className = 'tl-note-item';
      div.dataset.noteId = n.id;
      div.innerHTML = `
        <div class="tl-note-avatar">${n.author_name.charAt(0).toUpperCase()}</div>
        <div class="tl-note-body">
          <div class="tl-note-meta">
            <span class="tl-note-author">${n.author_name}</span>
            <span class="tl-note-time">${n.created_at}</span>
          </div>
          <div class="tl-note-text">${n.note.replace(/\n/g,'<br>')}</div>
        </div>
        ${n.can_delete ? `<button class="tl-note-delete btn-delete-note" data-id="${n.id}" title="Hapus catatan"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>` : ''}
      `;
      document.getElementById('notes-list').appendChild(div);
      const delBtn = div.querySelector('.btn-delete-note');
      if (delBtn) bindDeleteNote(delBtn);
    } else { alert(d.message || 'Gagal mengirim note'); }
  } finally { noteSubmit.disabled = false; }
}

noteSubmit?.addEventListener('click', submitNote);
noteInput?.addEventListener('keydown', e => {
  if (e.key === 'Enter' && e.ctrlKey) submitNote();
});

/* ── Hapus Note ─────────────────────────────────────────── */
function bindDeleteNote(btn) {
  if (!btn) return;
  btn.addEventListener('click', async () => {
    if (!confirm('Hapus catatan ini?')) return;
    const noteId = btn.dataset.id;
    const res    = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/notes/${noteId}/delete`, { method: 'POST' });
    const d      = await res.json();
    if (d.success) {
      btn.closest('.tl-note-item').remove();
      if (!document.querySelector('.tl-note-item')) {
        document.getElementById('notes-list').innerHTML =
          `<div class="tl-empty" id="notes-empty">
            <div class="tl-empty-icon"><svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></div>
            <p>Belum ada catatan progress</p>
          </div>`;
      }
    }
  });
}
document.querySelectorAll('.btn-delete-note').forEach(bindDeleteNote);

/* ── Hapus TL ───────────────────────────────────────────── */
document.getElementById('btn-delete-tl')?.addEventListener('click', async () => {
  if (!confirm('Hapus tindak lanjut ini secara permanen?')) return;
  const btn = document.getElementById('btn-delete-tl');
  btn.disabled = true;
  const res = await fetch(`${BASE_URL}/tindak-lanjut/${TL_ID}/delete`, { method: 'POST' });
  const d   = await res.json();
  if (d.success) { location.href = `${BASE_URL}/tindak-lanjut`; }
  else { btn.disabled = false; alert(d.message || 'Gagal menghapus'); }
});
</script>
