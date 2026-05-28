/**
 * notulen-comments.js
 */
(function () {
  'use strict';

  let replyToId       = null;
  let showResolved    = false;
  let mentionUsers    = [];
  let pendingMentions = [];

  const commentList    = document.getElementById('comment-list');
  const commentInput   = document.getElementById('comment-input');
  const submitBtn      = document.getElementById('btn-submit-comment');
  const replyIndicator = document.getElementById('reply-indicator');
  const countBadge     = document.getElementById('comment-count');
  const toggleBtn      = document.getElementById('btn-toggle-resolved');

  if (!commentList) return;

  if (typeof ALL_USERS !== 'undefined') mentionUsers = ALL_USERS;

  const baseUrl   = (typeof BASE_URL   !== 'undefined') ? BASE_URL.replace(/\/$/, '') : '';
  const meetingId = (typeof MEETING_ID !== 'undefined') ? MEETING_ID : null;

  // ── DEBUG BANNER — hapus setelah masalah ditemukan ──
  commentList.innerHTML = `
    <div class="alert alert-info m-2 py-2" style="font-size:11px;">
      <strong>DEBUG:</strong><br>
      MEETING_ID = <code>${meetingId}</code><br>
      BASE_URL = <code>${escHtml(baseUrl)}</code><br>
      Fetch URL = <code>${escHtml(baseUrl)}/api/notulen/${meetingId}/comments</code><br>
      <span id="debug-response">Menunggu response...</span>
    </div>`;

  if (!meetingId) {
    commentList.innerHTML = '<p class="text-danger text-center py-3 small">Error: MEETING_ID tidak terdefinisi.</p>';
    return;
  }

  /* ── Helpers ─────────────────────────────────────────── */
  function escHtml(s) {
    return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function formatMention(s) {
    return s.replace(/@([\w][\w\s]*)/g, '<strong class="text-orange">@$1</strong>');
  }
  function formatTime(ts) {
    if (!ts) return '';
    const d = new Date(ts);
    return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short'}) + ' ' +
           d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  }

  async function apiFetch(url, options = {}) {
    const res = await fetch(url, { credentials: 'same-origin', ...options });
    const ct  = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const txt = await res.text();
      throw new Error(`HTTP ${res.status} bukan JSON:\n` + txt.replace(/<[^>]+>/g,' ').trim().substring(0,300));
    }
    return res.json();
  }

  /* ── Load & Render ─────────────────────────────────────── */
  async function loadComments() {
    try {
      const url  = `${baseUrl}/api/notulen/${meetingId}/comments`;
      const data = await apiFetch(url);

      // Update debug banner
      const dbg = document.getElementById('debug-response');
      if (dbg) dbg.innerHTML = `Response: <code>${escHtml(JSON.stringify(data).substring(0,200))}</code>`;

      if (data.success) {
        renderComments(data.comments);
      } else {
        commentList.innerHTML = `<p class="text-danger text-center py-3 small">Gagal: ${escHtml(data.message)}</p>`;
      }
    } catch (e) {
      commentList.innerHTML = `<div class="alert alert-danger m-2 py-2 small"><strong>Error:</strong><br><pre style="font-size:10px;white-space:pre-wrap;">${escHtml(e.message)}</pre></div>`;
    }
  }

  function renderComments(comments) {
    const filtered = showResolved ? comments : comments.filter(c => !c.is_resolved);
    const active   = comments.filter(c => !c.is_resolved).length;
    if (countBadge) countBadge.textContent = active;

    if (!filtered.length) {
      commentList.innerHTML = '<p class="text-muted text-center py-3 small">Belum ada komentar.</p>';
      return;
    }
    commentList.innerHTML = filtered.map(c => renderThread(c)).join('');
    bindCommentActions();
  }

  function renderThread(c) {
    const avatar  = c.user_name ? c.user_name.charAt(0).toUpperCase() : '?';
    const isAdmin = typeof IS_EDITOR !== 'undefined' && IS_EDITOR;
    const resolved = c.is_resolved
      ? '<span class="badge bg-green-lt ms-1" style="font-size:10px;">✓ Selesai</span>' : '';

    const repliesHtml = (c.replies || []).map(r => `
      <div class="d-flex gap-2 mt-2 ms-4">
        <span class="avatar avatar-xs" style="background:#e5e7eb;color:#374151;font-size:10px;font-weight:700;flex-shrink:0;">
          ${r.user_name ? r.user_name.charAt(0).toUpperCase() : '?'}
        </span>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2">
            <strong style="font-size:12px;">${escHtml(r.user_name)}</strong>
            <small class="text-muted">${formatTime(r.created_at)}</small>
          </div>
          <p class="mb-0" style="font-size:13px;">${formatMention(escHtml(r.content))}</p>
        </div>
        ${r.user_id == CURRENT_USER_ID
          ? `<button class="btn btn-sm btn-ghost-danger p-0 px-1 btn-del-comment" data-id="${r.id}">✕</button>`
          : ''}
      </div>
    `).join('');

    return `
    <div class="border rounded p-2 mb-2 ${c.is_resolved ? 'opacity-75 bg-light' : ''}" data-comment-id="${c.id}">
      <div class="d-flex gap-2">
        <span class="avatar avatar-sm" style="background:var(--brand,#7B1C1C);color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">${avatar}</span>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <strong style="font-size:13px;">${escHtml(c.user_name)}</strong>
            <small class="text-muted">${formatTime(c.created_at)}</small>
            ${resolved}
          </div>
          <p class="mb-1" style="font-size:13px;">${formatMention(escHtml(c.content))}</p>
          <div class="d-flex gap-2">
            <button class="btn btn-xs btn-ghost-secondary btn-reply"
              data-id="${c.id}" data-name="${escHtml(c.user_name)}"
              style="font-size:11px;padding:2px 8px;">Balas</button>
            ${isAdmin ? `<button class="btn btn-xs btn-ghost-secondary btn-resolve"
              data-id="${c.id}" style="font-size:11px;padding:2px 8px;">
              ${c.is_resolved ? 'Buka' : 'Selesaikan'}</button>` : ''}
            ${c.user_id == CURRENT_USER_ID
              ? `<button class="btn btn-xs btn-ghost-danger btn-del-comment"
                  data-id="${c.id}" style="font-size:11px;padding:2px 8px;">✕ Hapus</button>`
              : ''}
          </div>
        </div>
      </div>
      ${repliesHtml}
    </div>`;
  }

  function bindCommentActions() {
    document.querySelectorAll('.btn-reply').forEach(btn => {
      btn.addEventListener('click', () => {
        replyToId = parseInt(btn.dataset.id);
        if (replyIndicator) {
          replyIndicator.innerHTML = `💬 Membalas <strong>${escHtml(btn.dataset.name)}</strong>
            <a href="#" class="ms-1 text-danger" id="cancel-reply">× Batal</a>`;
          document.getElementById('cancel-reply')?.addEventListener('click', e => {
            e.preventDefault(); replyToId = null; replyIndicator.innerHTML = '';
          });
        }
        commentInput?.focus();
      });
    });
    document.querySelectorAll('.btn-resolve').forEach(btn => {
      btn.addEventListener('click', async () => {
        try {
          const data = await apiFetch(`${baseUrl}/api/comments/${btn.dataset.id}/resolve`, { method: 'POST' });
          if (data.success) loadComments();
        } catch (e) { alert('Resolve error: ' + e.message); }
      });
    });
    document.querySelectorAll('.btn-del-comment').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (!confirm('Hapus komentar ini?')) return;
        try {
          const data = await apiFetch(`${baseUrl}/api/comments/${btn.dataset.id}/delete`, { method: 'POST' });
          if (data.success) loadComments();
        } catch (e) { alert('Delete error: ' + e.message); }
      });
    });
  }

  /* ── Submit ──────────────────────────────────────────────── */
  if (submitBtn) {
    submitBtn.addEventListener('click', async () => {
      const content = commentInput ? commentInput.value.trim() : '';
      if (!content) { commentInput?.focus(); return; }

      submitBtn.disabled    = true;
      submitBtn.textContent = 'Mengirim...';

      try {
        const data = await apiFetch(`${baseUrl}/api/notulen/${meetingId}/comments`, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({ content, parent_id: replyToId, mentions: pendingMentions })
        });

        if (data.success) {
          if (commentInput) commentInput.value = '';
          replyToId = null; pendingMentions = [];
          if (replyIndicator) replyIndicator.innerHTML = '';
          loadComments();
        } else {
          commentList.insertAdjacentHTML('afterbegin',
            `<div class="alert alert-danger m-2 py-2 small">Gagal: ${escHtml(data.message)}</div>`);
        }
      } catch (e) {
        commentList.insertAdjacentHTML('afterbegin',
          `<div class="alert alert-danger m-2 py-2 small"><strong>Error:</strong><br><pre style="font-size:10px;white-space:pre-wrap;max-height:120px;overflow:auto;">${escHtml(e.message)}</pre></div>`);
      } finally {
        submitBtn.disabled    = false;
        submitBtn.textContent = 'Kirim';
      }
    });

    commentInput?.addEventListener('keydown', e => {
      if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submitBtn.click(); }
    });
  }

  /* ── Mention ─────────────────────────────────────────────── */
  const dropdown = document.getElementById('mention-dropdown');
  if (dropdown && commentInput) {
    commentInput.addEventListener('input', () => {
      const val   = commentInput.value;
      const atIdx = val.lastIndexOf('@');
      if (atIdx === -1) { dropdown.style.display = 'none'; return; }
      const q = val.slice(atIdx + 1).toLowerCase();
      if (!q || q.includes(' ')) { dropdown.style.display = 'none'; return; }
      const matches = mentionUsers.filter(u => u.name.toLowerCase().includes(q));
      if (!matches.length) { dropdown.style.display = 'none'; return; }
      dropdown.innerHTML = matches.slice(0, 5).map(u =>
        `<a href="#" class="dropdown-item py-1" data-id="${u.id}" data-name="${escHtml(u.name)}">@${escHtml(u.name)}</a>`
      ).join('');
      dropdown.style.cssText = 'display:block!important;position:absolute;z-index:999;bottom:100%;left:0;';
      dropdown.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', e => {
          e.preventDefault();
          commentInput.value = val.slice(0, atIdx) + '@' + item.dataset.name + ' ';
          pendingMentions.push(parseInt(item.dataset.id));
          dropdown.style.display = 'none';
        });
      });
    });
  }

  /* ── Toggle Resolved ───────────────────────────────────── */
  toggleBtn?.addEventListener('click', () => {
    showResolved = !showResolved;
    toggleBtn.textContent = showResolved ? 'Sembunyikan Selesai' : 'Tampilkan Selesai';
    loadComments();
  });

  loadComments();
  setInterval(loadComments, 15000);
})();
