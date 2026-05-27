(function () {
  const badge   = document.getElementById('notif-badge');
  const list    = document.getElementById('notif-list');
  const markAll = document.getElementById('mark-all-read');

  const iconMap = {
    meeting_invite:    '📅',
    notulen_update:    '📝',
    tindak_lanjut_due: '⚠️',
    default:           '🔔'
  };

  function escHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }

  function timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)    return diff + 'd lalu';
    if (diff < 3600)  return Math.floor(diff / 60) + 'm lalu';
    if (diff < 86400) return Math.floor(diff / 3600) + 'j lalu';
    return Math.floor(diff / 86400) + ' hari lalu';
  }

  async function loadNotifications() {
    try {
      const res    = await fetch(BASE_URL + '/api/notifications');
      if (!res.ok) return;
      const notifs = await res.json();

      if (badge) {
        if (notifs.length > 0) {
          badge.textContent   = notifs.length > 9 ? '9+' : notifs.length;
          badge.style.display = '';
        } else {
          badge.style.display = 'none';
        }
      }

      if (!list) return;

      if (notifs.length === 0) {
        list.innerHTML = '<div class="list-group-item text-center text-muted py-4">Tidak ada notifikasi baru</div>';
        return;
      }

      list.innerHTML = notifs.map(n => {
        const icon = iconMap[n.type] || iconMap.default;
        const ago  = timeAgo(n.created_at);
        const data = JSON.parse(n.data || '{}');
        const link = data.meeting_id ? BASE_URL + '/meetings/' + data.meeting_id : BASE_URL + '/notifications';
        return `
          <a href="${link}" class="list-group-item list-group-item-action"
             onclick="window._markRead(${n.id}, event)">
            <div class="d-flex align-items-start gap-2">
              <span style="font-size:20px;">${icon}</span>
              <div class="flex-fill">
                <div class="d-flex justify-content-between">
                  <strong class="small">${escHtml(n.title)}</strong>
                  <small class="text-muted text-nowrap ms-2">${ago}</small>
                </div>
                <p class="mb-0 small text-muted">${escHtml(n.message)}</p>
              </div>
              <span class="status-dot status-dot-animated bg-orange flex-shrink-0"></span>
            </div>
          </a>`;
      }).join('');
    } catch (e) {
      console.warn('Notifikasi gagal:', e);
    }
  }

  window._markRead = async function (id, e) {
    e.preventDefault();
    const href = e.currentTarget.getAttribute('href');
    await fetch(BASE_URL + '/api/notifications/read', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    if (href && href !== '#') window.location.href = href;
    else loadNotifications();
  };

  if (markAll) {
    markAll.addEventListener('click', async e => {
      e.preventDefault();
      await fetch(BASE_URL + '/api/notifications/read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ all: true })
      });
      loadNotifications();
    });
  }

  loadNotifications();
  setInterval(loadNotifications, 20000);
})();
