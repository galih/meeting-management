/**
 * notulen-comments.js
 * Sistem komentar & diskusi notulen dengan fitur:
 * - Load & tampilkan komentar bersarang (thread)
 * - Reply ke komentar
 * - Mention @nama dengan dropdown
 * - Resolve thread
 * - Auto-refresh tiap 15 detik
 */

(function () {
  'use strict';

  let replyToId    = null;
  let showResolved = false;
  let mentionUsers = [];
  let pendingMentions = [];

  const commentList    = document.getElementById('comment-list');
  const commentInput   = document.getElementById('comment-input');
  const submitBtn      = document.getElementById('btn-submit-comment');
  const replyIndicator = document.getElementById('reply-indicator');
  const countBadge     = document.getElementById('comment-count');
  const toggleBtn      = document.getElementById('btn-toggle-resolved');

  if (!commentList) return;

  // Inisialisasi daftar user untuk mention
  if (typeof ALL_USERS !== 'undefined') mentionUsers = ALL_USERS;

  // ── Load Komentar ──────────────────────────────────────────
  async function loadComments() {
    const res  = await fetch(`/api/notulen/${MEETING_ID}/comments`);
    const data = await res.json();
    if (!data.success) return;
    renderComments(data.comments);
  }

  function renderComments(comments) {
    const filtered = showResolved ? comments : comments.filter(c => !c.is_resolved);
    const active   = comments.filter(c => !c.is_resolved).length;
    countBadge.textContent = active;

    if (!filtered.length) {
      commentList.innerHTML = '<p class="text-muted text-center py-3 small">Belum ada komentar.</p>';
      return;
    }
    commentList.innerHTML = filtered.map(c => renderThread(c)).join('');
    bindCommentActions();
  }

  function renderThread(c) {
    const avatar   = c.user_name.charAt(0).toUpperCase();
    const time     = formatTime(c.created_at);
    const resolved = c.is_resolved
      ? '<span class="badge bg-green-lt text-green ms-1" style="font-size:10px;">✓ Selesai</span>' : '';
    const isAdmin  = typeof IS_EDITOR !== 'undefined' && IS_EDITOR;

    const repliesHtml = (c.replies || []).map(r => `
      <div class="d-flex gap-2 mt-2 ms-4">
        <span class="avatar avatar-xs" style="background:#e5e7eb;color:#374151;font-size:10px;font-weight:700;flex-shrink:0;">
          ${r.user_name.charAt(0).toUpperCase()}
        </span>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2">
            <strong style="font-size:12px;">${escHtml(r.user_name)}</strong>
            <small class="text-muted">${formatTime(r.created_at)}</small>
          </div>
          <p class="mb-0" style="font-size:13px;">${escHtml(r.content)}</p>
        </div>
        ${r.user_id == CURRENT_USER_ID ? `<button class="btn btn-sm btn-ghost-danger p-0 px-1 btn-del-comment" data-id="${r.id}" style="font-size:11px;">✕</button>` : ''}
      </div>
    `).join('');

    return `
    <div class="border rounded p-2 mb-2 ${c.is_resolved ? 'opacity-75 bg-light' : ''}" data-comment-id="${c.id}">
      <div class="d-flex gap-2">
        <span class="avatar avatar-sm" style="background:#f76707;color:#fff;font-size:12px;font-weight:700;flex-shrink:0;">
          ${avatar}
        </span>
        <div class="flex-fill">
          <div class="d-flex align-items-center gap-2 flex-wrap">
            <strong style="font-size:13px;">${escHtml(c.user_name)}</strong>
            <small class="text-muted">${time}</small>
            ${resolved}
          </div>
          <p class="mb-1" style="font-size:13px;">${formatMention(escHtml(c.content))}</p>
          <div class="d-flex gap-2">
            <button class="btn btn-xs btn-ghost-secondary btn-reply" data-id="${c.id}" data-name="${escHtml(c.user_name)}" style="font-size:11px;padding:2px 8px;">Balas</button>
            ${isAdmin ? `<button class="btn btn-xs btn-ghost-secondary btn-resolve" data-id="${c.id}" style="font-size:11px;padding:2px 8px;">${c.is_resolved ? 'Buka' : 'Selesaikan'}</button>` : ''}
            ${c.user_id == CURRENT_USER_ID ? `<button class="btn btn-xs btn-ghost-danger btn-del-comment" data-id="${c.id}" style="font-size:11px;padding:2px 8px;">✕ Hapus</button>` : ''}
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
        replyIndicator.innerHTML = `💬 Membalas <strong>${escHtml(btn.dataset.name)}</strong>
          <a href="#" class="ms-1 text-danger" id="cancel-reply">× Batal</a>`;
        commentInput.focus();
        document.getElementById('cancel-reply')?.addEventListener('click', e => {
          e.preventDefault(); replyToId = null; replyIndicator.innerHTML = '';
        });
      });
    });

    document.querySelectorAll('.btn-resolve').forEach(btn => {
      btn.addEventListener('click', async () => {
        const res  = await fetch(`/api/comments/${btn.dataset.id}/resolve`, { method: 'POST' });
        const data = await res.json();
        if (data.success) loadComments();
      });
    });

    document.querySelectorAll('.btn-del-comment').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (!confirm('Hapus komentar ini?')) return;
        const res  = await fetch(`/api/comments/${btn.dataset.id}/delete`, { method: 'POST' });
        const data = await res.json();
        if (data.success) loadComments();
      });
    });
  }

  // ── Submit Komentar ───────────────────────────────────────
  submitBtn?.addEventListener('click', async () => {
    const content = commentInput.value.trim();
    if (!content) return;
    submitBtn.disabled = true;
    const res = await fetch(`/api/notulen/${MEETING_ID}/comments`, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        content,
        parent_id: replyToId,
        mentions:  pendingMentions
      })
    });
    const data = await res.json();
    submitBtn.disabled = false;
    if (data.success) {
      commentInput.value = '';
      replyToId = null;
      pendingMentions = [];
      replyIndicator.innerHTML = '';
      loadComments();
    }
  });

  // ── Mention @nama ─────────────────────────────────────────
  const dropdown = document.getElementById('mention-dropdown');
  commentInput?.addEventListener('input', () => {
    const val     = commentInput.value;
    const atIdx   = val.lastIndexOf('@');
    if (atIdx === -1 || val[atIdx - 1] === ' ' || val.slice(atIdx + 1).includes(' ')) {
      dropdown.style.display = 'none'; return;
    }
    const q = val.slice(atIdx + 1).toLowerCase();
    const matches = mentionUsers.filter(u => u.name.toLowerCase().includes(q));
    if (!matches.length) { dropdown.style.display = 'none'; return; }
    dropdown.innerHTML = matches.slice(0, 5).map(u =>
      `<a href="#" class="dropdown-item py-1" data-id="${u.id}" data-name="${escHtml(u.name)}">@${escHtml(u.name)}</a>`
    ).join('');
    dropdown.style.cssText = 'display:block!important;position:absolute;z-index:999;bottom:100%;left:0;';
    dropdown.querySelectorAll('.dropdown-item').forEach(item => {
      item.addEventListener('click', e => {
        e.preventDefault();
        const before = val.slice(0, atIdx);
        commentInput.value = before + '@' + item.dataset.name + ' ';
        pendingMentions.push(parseInt(item.dataset.id));
        dropdown.style.display = 'none';
      });
    });
  });

  // ── Toggle Resolved ───────────────────────────────────────
  toggleBtn?.addEventListener('click', () => {
    showResolved = !showResolved;
    toggleBtn.textContent = showResolved ? 'Sembunyikan Selesai' : 'Tampilkan Selesai';
    loadComments();
  });

  // ── Helper ────────────────────────────────────────────────
  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
  function formatMention(s) {
    return s.replace(/@(\w[\w\s]*)/g, '<strong class="text-orange">@$1</strong>');
  }
  function formatTime(ts) {
    const d = new Date(ts);
    return d.toLocaleDateString('id-ID',{day:'2-digit',month:'short'}) + ' ' +
           d.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
  }

  // ── Init ──────────────────────────────────────────────────
  loadComments();
  setInterval(loadComments, 15000); // refresh tiap 15 detik

})();
