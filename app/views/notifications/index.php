<?php
$baseUrl     = rtrim(BASE_URL, '/');
$currentPage = (int)($page ?? 1);
$currentFilter = $filter ?? 'all';
$unreadTotal = (int)($unreadTotal ?? 0);

$typeConfig = [
    'meeting_invite'     => ['label' => 'Undangan Kegiatan', 'color' => '#2563EB', 'bg' => '#EFF6FF'],
    'meeting_invitation' => ['label' => 'Undangan Kegiatan', 'color' => '#2563EB', 'bg' => '#EFF6FF'],
    'tindak_lanjut'      => ['label' => 'Tindak Lanjut',    'color' => '#D97706', 'bg' => '#FFFBEB'],
    'tindak_lanjut_due'  => ['label' => 'Jatuh Tempo',      'color' => '#DC2626', 'bg' => '#FEF2F2'],
    'notulen_update'     => ['label' => 'Notulen Diperbarui','color' => '#7C3AED', 'bg' => '#F5F3FF'],
    'notulen_comment'    => ['label' => 'Komentar Notulen', 'color' => '#0891B2', 'bg' => '#ECFEFF'],
    'comment_mention'    => ['label' => 'Mention',          'color' => '#059669', 'bg' => '#ECFDF5'],
];

function buildNotifLink(string $baseUrl, string $raw): string {
    if (empty($raw)) return '#';
    if (strncmp($raw, 'http://',  7) === 0) return $raw;
    if (strncmp($raw, 'https://', 8) === 0) return $raw;
    return $baseUrl . '/' . ltrim($raw, '/');
}

// Fix: cast is_read correctly (PDO may return string "0"/"1")
function isRead($val): bool {
    return (int)$val === 1;
}

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)     return 'Baru saja';
    if ($diff < 3600)   return floor($diff/60) . ' menit lalu';
    if ($diff < 86400)  return floor($diff/3600) . ' jam lalu';
    if ($diff < 604800) return floor($diff/86400) . ' hari lalu';
    return date('d M Y', strtotime($datetime));
}
?>

<style>
.notif-page-hero {
  background: linear-gradient(135deg, #6A1010 0%, #4E0C0C 100%);
  border-radius: 16px;
  padding: 1.75rem 2rem 1.5rem;
  margin-bottom: 1.5rem;
  position: relative;
  overflow: hidden;
}
.notif-page-hero::before {
  content: '';
  position: absolute; inset: 0;
  background-image: radial-gradient(circle at 20% 50%, rgba(201,168,76,.12) 0%, transparent 60%),
                    radial-gradient(circle at 80% 20%, rgba(255,255,255,.05) 0%, transparent 50%);
  pointer-events: none;
}
.notif-page-hero::after {
  content: '';
  position: absolute; bottom: 0; left: 0; right: 0;
  height: 3px;
  background: linear-gradient(90deg, #C9A84C, rgba(201,168,76,.3));
}
.notif-hero-title {
  font-size: 22px; font-weight: 900; color: #fff;
  letter-spacing: -.02em; margin: 0;
  display: flex; align-items: center; gap: .6rem;
}
.notif-hero-sub {
  font-size: 13px; color: rgba(255,255,255,.6); margin-top: .35rem;
}
.notif-hero-badge {
  display: inline-flex; align-items: center;
  background: rgba(201,168,76,.2); border: 1px solid rgba(201,168,76,.4);
  color: #C9A84C; font-size: 11px; font-weight: 800;
  padding: .2em .6em; border-radius: 20px; letter-spacing: .04em;
}

/* Filter tabs */
.notif-tabs {
  display: flex; gap: .5rem;
  margin-bottom: 1rem;
  border-bottom: 2px solid #EDE8DE;
  padding-bottom: 0;
}
.notif-tab {
  display: inline-flex; align-items: center; gap: .4rem;
  padding: .5rem 1rem;
  font-size: 13px; font-weight: 700; color: #A89E90;
  text-decoration: none; border: none; background: none;
  cursor: pointer; border-bottom: 2.5px solid transparent;
  margin-bottom: -2px; transition: all 200ms;
}
.notif-tab:hover { color: #6A1010; }
.notif-tab.active { color: #6A1010; border-bottom-color: #6A1010; }
.notif-tab-count {
  font-size: 10px; font-weight: 900;
  background: #6A1010; color: #fff;
  padding: .1em .45em; border-radius: 10px;
}
.notif-tab.active .notif-tab-count { background: #C9A84C; }

/* Toolbar */
.notif-toolbar {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 1rem; gap: 1rem; flex-wrap: wrap;
}
.notif-toolbar-left { font-size: 12.5px; color: #A89E90; }
.notif-toolbar-right { display: flex; gap: .5rem; }
.notif-btn {
  display: inline-flex; align-items: center; gap: .35rem;
  padding: .38rem .9rem; border-radius: 8px;
  font-size: 12.5px; font-weight: 700;
  cursor: pointer; border: 1.5px solid;
  transition: all 150ms; text-decoration: none;
  background: none;
}
.notif-btn-primary {
  background: #6A1010; border-color: #6A1010; color: #fff;
}
.notif-btn-primary:hover { background: #4E0C0C; border-color: #4E0C0C; color: #fff; }
.notif-btn-ghost {
  background: #fff; border-color: #EDE8DE; color: #6B6055;
}
.notif-btn-ghost:hover { background: #F5F0E8; border-color: #DDD5C4; color: #1C1714; }
.notif-btn:disabled { opacity: .45; cursor: not-allowed; }

/* Notif card */
.notif-list {
  display: flex; flex-direction: column; gap: .5rem;
}
.notif-card {
  display: flex; align-items: flex-start; gap: 1rem;
  background: #fff;
  border: 1.5px solid #EDE8DE;
  border-radius: 12px;
  padding: 1rem 1.1rem;
  transition: all 200ms;
  position: relative;
  text-decoration: none;
  color: inherit;
  cursor: pointer;
}
.notif-card:hover {
  border-color: #C9A84C;
  box-shadow: 0 4px 16px rgba(106,16,16,.07);
  transform: translateY(-1px);
}
.notif-card.unread {
  background: linear-gradient(135deg, #FFF8F8 0%, #FFFCF8 100%);
  border-color: #F5C6C6;
}
.notif-card.unread::before {
  content: '';
  position: absolute; left: 0; top: 15%; bottom: 15%;
  width: 3px; background: #6A1010;
  border-radius: 0 3px 3px 0;
}
.notif-icon-wrap {
  width: 40px; height: 40px; border-radius: 10px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0; font-size: 18px;
}
.notif-body { flex: 1; min-width: 0; }
.notif-message {
  font-size: 13.5px; font-weight: 600; color: #1C1714;
  line-height: 1.45; margin-bottom: .25rem;
  display: -webkit-box; -webkit-line-clamp: 2;
  -webkit-box-orient: vertical; overflow: hidden;
}
.notif-card.unread .notif-message { font-weight: 800; }
.notif-meta {
  display: flex; align-items: center; gap: .75rem; flex-wrap: wrap;
}
.notif-time { font-size: 11.5px; color: #A89E90; }
.notif-type-badge {
  font-size: 10.5px; font-weight: 700;
  padding: .15em .55em; border-radius: 6px;
}
.notif-unread-dot {
  width: 8px; height: 8px; border-radius: 50%;
  background: #6A1010; flex-shrink: 0; margin-top: 4px;
  animation: pulse-dot 2s ease-in-out infinite;
}
@keyframes pulse-dot {
  0%, 100% { opacity: 1; transform: scale(1); }
  50% { opacity: .6; transform: scale(.8); }
}

/* Empty state */
.notif-empty {
  text-align: center; padding: 3.5rem 1rem;
  background: #fff; border: 1.5px solid #EDE8DE;
  border-radius: 12px; color: #A89E90;
}
.notif-empty-icon {
  width: 56px; height: 56px; border-radius: 16px;
  background: #F5F0E8; display: flex; align-items: center;
  justify-content: center; margin: 0 auto 1rem;
  color: #C9A84C;
}
.notif-empty-title { font-size: 15px; font-weight: 800; color: #6B6055; margin-bottom: .4rem; }
.notif-empty-sub   { font-size: 13px; color: #A89E90; }

/* Pagination */
.notif-pagination {
  display: flex; align-items: center; justify-content: space-between;
  margin-top: 1.25rem; flex-wrap: wrap; gap: .75rem;
}
.notif-pagination-info { font-size: 12.5px; color: #A89E90; }
.notif-pages { display: flex; gap: .3rem; }
.notif-page-btn {
  min-width: 34px; height: 34px; padding: 0 .5rem;
  border-radius: 8px; border: 1.5px solid #EDE8DE;
  background: #fff; font-size: 13px; font-weight: 700;
  color: #6B6055; cursor: pointer; transition: all 150ms;
  display: flex; align-items: center; justify-content: center;
  text-decoration: none;
}
.notif-page-btn:hover:not(.active):not(:disabled) { background: #F5F0E8; border-color: #DDD5C4; color: #1C1714; }
.notif-page-btn.active { background: #6A1010; border-color: #6A1010; color: #fff; }
.notif-page-btn:disabled { opacity: .35; cursor: not-allowed; pointer-events: none; }

/* Toast */
#notifToast {
  position: fixed; bottom: 24px; right: 24px; z-index: 9999;
  background: #1C1714; color: #fff;
  padding: .7rem 1.1rem; border-radius: 10px;
  font-size: 13px; font-weight: 700;
  box-shadow: 0 4px 20px rgba(0,0,0,.2);
  display: none; align-items: center; gap: .5rem;
  animation: slideInRight .25s ease;
}
#notifToast.show { display: flex; }
@keyframes slideInRight {
  from { transform: translateX(60px); opacity: 0; }
  to   { transform: translateX(0);    opacity: 1; }
}
</style>

<!-- Hero -->
<div class="notif-page-hero">
  <div class="d-flex align-items-center justify-content-between">
    <div>
      <h1 class="notif-hero-title">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Notifikasi
      </h1>
      <div class="notif-hero-sub">
        <?php if ($unreadTotal > 0): ?>
          <span class="notif-hero-badge"><?= $unreadTotal ?> belum dibaca</span>
        <?php else: ?>
          Semua notifikasi sudah dibaca
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Filter Tabs -->
<div class="notif-tabs">
  <a href="<?= $baseUrl ?>/notifications?filter=all"
     class="notif-tab <?= $currentFilter === 'all' ? 'active' : '' ?>">
    Semua
    <span class="notif-tab-count"><?= $total ?? 0 ?></span>
  </a>
  <a href="<?= $baseUrl ?>/notifications?filter=unread"
     class="notif-tab <?= $currentFilter === 'unread' ? 'active' : '' ?>">
    Belum Dibaca
    <?php if ($unreadTotal > 0): ?>
    <span class="notif-tab-count"><?= $unreadTotal ?></span>
    <?php endif; ?>
  </a>
</div>

<!-- Toolbar -->
<div class="notif-toolbar">
  <div class="notif-toolbar-left">
    <?php
      $from = ($total ?? 0) > 0 ? (($currentPage - 1) * 20 + 1) : 0;
      $to   = min($currentPage * 20, $total ?? 0);
    ?>
    <?= $from ?>–<?= $to ?> dari <?= $total ?? 0 ?> notifikasi
  </div>
  <div class="notif-toolbar-right">
    <?php if ($unreadTotal > 0): ?>
    <button class="notif-btn notif-btn-primary" id="btnMarkAllRead">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Tandai Semua Dibaca
    </button>
    <?php endif; ?>
    <button class="notif-btn notif-btn-ghost" id="btnDeleteAll">
      <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
      Hapus Semua
    </button>
  </div>
</div>

<!-- Notif List -->
<div class="notif-list" id="notifList">
  <?php if (empty($notifs)): ?>
  <div class="notif-empty">
    <div class="notif-empty-icon">
      <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    </div>
    <div class="notif-empty-title">
      <?= $currentFilter === 'unread' ? 'Tidak ada notifikasi yang belum dibaca' : 'Tidak ada notifikasi' ?>
    </div>
    <div class="notif-empty-sub">
      <?= $currentFilter === 'unread' ? 'Semua notifikasi sudah kamu baca.' : 'Notifikasi akan muncul di sini.' ?>
    </div>
  </div>
  <?php endif; ?>

  <?php foreach ($notifs as $n):
    $read = isRead($n['is_read']);
    $raw  = $n['url'] ?? $n['link'] ?? '';
    $link = buildNotifLink($baseUrl, $raw);
    $cfg  = $typeConfig[$n['type']] ?? ['label' => 'Notifikasi', 'color' => '#6B6055', 'bg' => '#F5F0E8'];
  ?>
  <div class="notif-card <?= $read ? '' : 'unread' ?>"
       data-id="<?= (int)$n['id'] ?>"
       data-link="<?= htmlspecialchars($link) ?>"
       data-read="<?= $read ? '1' : '0' ?>">
    <div class="notif-icon-wrap" style="background:<?= $cfg['bg'] ?>;">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="<?= $cfg['color'] ?>" stroke-width="2">
        <?php
        $type = $n['type'] ?? '';
        if (in_array($type, ['meeting_invite','meeting_invitation'])): ?>
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        <?php elseif ($type === 'tindak_lanjut_due'): ?>
          <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        <?php elseif ($type === 'tindak_lanjut'): ?>
          <polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
        <?php elseif (in_array($type, ['notulen_update','notulen_comment'])): ?>
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
        <?php elseif ($type === 'comment_mention'): ?>
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        <?php else: ?>
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
        <?php endif; ?>
      </svg>
    </div>
    <div class="notif-body">
      <div class="notif-message"><?= htmlspecialchars($n['message']) ?></div>
      <div class="notif-meta">
        <span class="notif-time"><?= timeAgo($n['created_at']) ?></span>
        <span class="notif-type-badge"
              style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
          <?= htmlspecialchars($cfg['label']) ?>
        </span>
      </div>
    </div>
    <?php if (!$read): ?>
    <div class="notif-unread-dot" title="Belum dibaca"></div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if (($totalPage ?? 1) > 1): ?>
<div class="notif-pagination">
  <div class="notif-pagination-info">
    Halaman <?= $currentPage ?> dari <?= $totalPage ?>
  </div>
  <div class="notif-pages">
    <?php
      $qs = $currentFilter !== 'all' ? '&filter=' . urlencode($currentFilter) : '';
    ?>
    <a href="<?= $baseUrl ?>/notifications?page=<?= max(1, $currentPage-1) . $qs ?>"
       class="notif-page-btn <?= $currentPage <= 1 ? 'disabled' : '' ?>"
       <?= $currentPage <= 1 ? 'aria-disabled="true"' : '' ?>>
      &lsaquo;
    </a>
    <?php
      $start = max(1, $currentPage - 2);
      $end   = min($totalPage, $currentPage + 2);
      if ($start > 1): ?>
        <a href="<?= $baseUrl ?>/notifications?page=1<?= $qs ?>" class="notif-page-btn">1</a>
        <?php if ($start > 2): ?><span class="notif-page-btn" style="border:none;cursor:default;">…</span><?php endif; ?>
      <?php endif; ?>
    <?php for ($i = $start; $i <= $end; $i++): ?>
      <a href="<?= $baseUrl ?>/notifications?page=<?= $i . $qs ?>"
         class="notif-page-btn <?= $i === $currentPage ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
    <?php if ($end < $totalPage): ?>
        <?php if ($end < $totalPage - 1): ?><span class="notif-page-btn" style="border:none;cursor:default;">…</span><?php endif; ?>
        <a href="<?= $baseUrl ?>/notifications?page=<?= $totalPage . $qs ?>" class="notif-page-btn"><?= $totalPage ?></a>
    <?php endif; ?>
    <a href="<?= $baseUrl ?>/notifications?page=<?= min($totalPage, $currentPage+1) . $qs ?>"
       class="notif-page-btn <?= $currentPage >= $totalPage ? 'disabled' : '' ?>"
       <?= $currentPage >= $totalPage ? 'aria-disabled="true"' : '' ?>>
      &rsaquo;
    </a>
  </div>
</div>
<?php endif; ?>

<!-- Toast -->
<div id="notifToast"></div>

<script>
(function () {
  'use strict';
  const BASE = <?= json_encode(rtrim(BASE_URL, '/')) ?>;

  function showToast(msg, ok = true) {
    const t = document.getElementById('notifToast');
    t.innerHTML = (ok
      ? '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4ade80" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>'
      : '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>')
      + ' ' + msg;
    t.classList.add('show');
    clearTimeout(t._timer);
    t._timer = setTimeout(() => t.classList.remove('show'), 3000);
  }

  /* ── Click notif card – mark as read then navigate ─────────────── */
  document.getElementById('notifList')?.addEventListener('click', async function (e) {
    const card = e.target.closest('.notif-card');
    if (!card) return;
    const id   = card.dataset.id;
    const link = card.dataset.link;
    const read = card.dataset.read === '1';

    if (!read && id) {
      try {
        await fetch(BASE + '/api/notifications/read', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: parseInt(id) })
        });
        card.classList.remove('unread');
        card.dataset.read = '1';
        card.querySelector('.notif-unread-dot')?.remove();
        // Update sidebar badge
        const dot = document.getElementById('notifDot');
        const remaining = document.querySelectorAll('.notif-card.unread').length;
        if (dot) dot.style.display = remaining > 0 ? '' : 'none';
      } catch(err) {}
    }

    if (link && link !== '#') {
      window.location.href = link;
    }
  });

  /* ── Mark all read ──────────────────────────────────────────── */
  document.getElementById('btnMarkAllRead')?.addEventListener('click', async function () {
    this.disabled = true;
    try {
      await fetch(BASE + '/api/notifications/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ all: true })
      });
      document.querySelectorAll('.notif-card.unread').forEach(c => {
        c.classList.remove('unread');
        c.querySelector('.notif-unread-dot')?.remove();
      });
      this.remove();
      const dot = document.getElementById('notifDot');
      if (dot) dot.style.display = 'none';
      showToast('Semua notifikasi ditandai dibaca');
    } catch(err) {
      showToast('Gagal menandai notifikasi', false);
      this.disabled = false;
    }
  });

  /* ── Delete all ─────────────────────────────────────────────── */
  document.getElementById('btnDeleteAll')?.addEventListener('click', async function () {
    if (!confirm('Hapus semua notifikasi? Tindakan ini tidak dapat dibatalkan.')) return;
    this.disabled = true;
    try {
      const res = await fetch(BASE + '/api/notifications/delete-all', { method: 'POST' });
      if (res.ok) {
        document.querySelectorAll('.notif-card').forEach(c => c.remove());
        showToast('Semua notifikasi dihapus');
        setTimeout(() => location.reload(), 1200);
      } else {
        throw new Error();
      }
    } catch(err) {
      showToast('Gagal menghapus notifikasi', false);
      this.disabled = false;
    }
  });

}());
</script>
