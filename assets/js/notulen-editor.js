/* globals Quill, MEETING_ID, CURRENT_USER_ID, IS_EDITOR,
   INITIAL_CONTENT, SAVE_URL, SYNC_URL, BASE_URL */

// ─────────────────────────────────────────────────────────────────────
// STATE
// ─────────────────────────────────────────────────────────────────────
window.quill   = null;
let currentVersion = 0;
let isSyncing      = false;
let saveTimer      = null;
let replyToId      = null;
let showResolved   = false;
let allUsers       = [];

// ─────────────────────────────────────────────────────────────────────
// TOAST
// ─────────────────────────────────────────────────────────────────────
function showToast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
  el.style.cssText = 'z-index:9999;max-width:360px;font-size:13.5px;';
  el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4500);
}

// ─────────────────────────────────────────────────────────────────────
// SAVE STATUS
// ─────────────────────────────────────────────────────────────────────
function setSaveStatus(msg, color) {
  const el = document.getElementById('save-status');
  if (!el) return;
  el.textContent = msg;
  el.style.color = color || 'rgba(255,255,255,.65)';
}

// ─────────────────────────────────────────────────────────────────────
// AUTO-SAVE
// ─────────────────────────────────────────────────────────────────────
function debouncedSave() {
  clearTimeout(saveTimer);
  saveTimer = setTimeout(doSave, 1500);
}

async function doSave() {
  if (!window.quill) return;
  setSaveStatus('Menyimpan...');
  try {
    const isEmpty = window.quill.getText().trim().length === 0;
    const html    = isEmpty ? '' : window.quill.root.innerHTML;
    let res;
    try {
      res = await fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, content: html })
      });
    } catch {
      setSaveStatus('✗ Tidak ada koneksi', 'rgba(255,100,100,.9)');
      return;
    }
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch {
      console.error('Response bukan JSON:', text.substring(0, 500));
      setSaveStatus('✗ Server error', 'rgba(255,100,100,.9)');
      showToast('⚠️ Gagal menyimpan notulen. Periksa log server.', 'danger');
      return;
    }
    if (data.success) {
      if (data.version) currentVersion = data.version;
      setSaveStatus('✓ Tersimpan ' + new Date().toLocaleTimeString('id-ID'));
    } else {
      setSaveStatus('✗ Gagal: ' + (data.message || 'error'), 'rgba(255,100,100,.9)');
      if (res.status === 403) {
        showToast('⚠️ Sesi kadaluarsa. <a href="/logout" class="alert-link">Login ulang</a>', 'warning');
      } else {
        showToast('✗ Gagal simpan: ' + (data.message || 'error'), 'danger');
      }
    }
  } catch (e) {
    console.error('Save error:', e);
    setSaveStatus('✗ Gagal simpan', 'rgba(255,100,100,.9)');
  }
}

// ─────────────────────────────────────────────────────────────────────
// LIVE SYNC (polling)
// ─────────────────────────────────────────────────────────────────────
async function pollNotulen() {
  if (isSyncing || !window.quill) return;
  isSyncing = true;
  try {
    const res  = await fetch(`${SYNC_URL}?meeting_id=${MEETING_ID}&version=${currentVersion}`);
    if (!res.ok) throw new Error('HTTP ' + res.status);
    const data = await res.json();
    if (
      data.success &&
      data.version &&
      parseInt(data.version) > currentVersion &&
      parseInt(data.editor_id) !== CURRENT_USER_ID
    ) {
      currentVersion = parseInt(data.version);
      if (data.content) {
        window.quill.clipboard.dangerouslyPasteHTML(data.content);
        showToast(`✏️ <strong>${data.editor_name || 'Pengguna lain'}</strong> memperbarui notulen`);
      }
    }
    const syncEl = document.getElementById('sync-status');
    if (syncEl) syncEl.innerHTML = '<span class="ned-live-dot"></span>Live';
  } catch {
    const syncEl = document.getElementById('sync-status');
    if (syncEl) syncEl.innerHTML = '<span style="width:7px;height:7px;border-radius:50%;background:#f87171;display:inline-block;"></span> Offline';
  } finally {
    isSyncing = false;
    setTimeout(pollNotulen, 5000);
  }
}

// ─────────────────────────────────────────────────────────────────────
// ATTACHMENT
// URL sesuai route: GET|POST /api/meetings/{id}/attachments
//                  GET  /attachments/{id}/download
//                  POST /api/attachments/{id}/delete
// ─────────────────────────────────────────────────────────────────────
function initAttachment() {
  const panel = document.getElementById('attachment-panel');
  if (!panel) return;
  const meetingId = panel.dataset.meetingId;
  const listEl    = document.getElementById('attachment-list');
  const countEl   = document.getElementById('attach-count');
  const apiBase   = `${BASE_URL}/api/meetings/${meetingId}/attachments`;

  document.getElementById('btn-show-upload-form')?.addEventListener('click', () => {
    const w = document.getElementById('upload-form-wrapper');
    if (w) w.style.display = w.style.display === 'none' ? '' : 'none';
  });
  document.getElementById('btn-cancel-upload')?.addEventListener('click', () => {
    const w = document.getElementById('upload-form-wrapper');
    if (w) w.style.display = 'none';
    document.getElementById('form-upload-attachment')?.reset();
  });

  function loadAttachments() {
    if (!listEl) return;
    listEl.innerHTML = '<div class="ned-attach-loading"><span class="spinner-border spinner-border-sm"></span> Memuat…</div>';
    fetch(apiBase)
      .then(r => r.json())
      .then(data => {
        const items = data.attachments || [];
        if (countEl) countEl.textContent = items.length;
        if (!items.length) {
          listEl.innerHTML = '<div class="ned-tl-empty">Belum ada lampiran</div>';
          return;
        }
        listEl.innerHTML = items.map(a => renderAttachItem(a)).join('');
        listEl.querySelectorAll('.btn-del-attach').forEach(btn => {
          btn.addEventListener('click', function () {
            if (!confirm('Hapus lampiran ini?')) return;
            fetch(`${BASE_URL}/api/attachments/${this.dataset.id}/delete`, { method: 'POST' })
              .then(r => r.json())
              .then(d => { if (d.success) loadAttachments(); else alert(d.message || 'Gagal menghapus'); })
              .catch(() => alert('Terjadi kesalahan.'));
          });
        });
      })
      .catch(() => { if (listEl) listEl.innerHTML = '<div class="ned-tl-empty">Gagal memuat lampiran</div>'; });
  }

  function renderAttachItem(a) {
    const icons = { pdf:'📄', doc:'📝', docx:'📝', xls:'📊', xlsx:'📊',
                    ppt:'📹', pptx:'📹', jpg:'🖼️', jpeg:'🖼️', png:'🖼️', zip:'🗂️', rar:'🗂️' };
    const ext  = (a.filename || '').split('.').pop().toLowerCase();
    const icon = icons[ext] || '📁';
    const size = a.file_size ? formatBytes(a.file_size) : '';
    return `
      <div class="ned-tl-item" style="padding:.5rem .9rem;">
        <div class="d-flex align-items-center gap-2">
          <span style="font-size:18px;flex-shrink:0;">${icon}</span>
          <div style="flex:1;min-width:0;">
            <div style="font-size:12.5px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
              <a href="${BASE_URL}/attachments/${a.id}/download" target="_blank" style="color:var(--kb-primary);text-decoration:none;">${escHtml(a.original_name || a.filename)}</a>
            </div>
            <div style="font-size:11px;color:var(--kb-text-muted);">${escHtml(a.category || '')}${size ? ' &middot; ' + size : ''}</div>
          </div>
          ${IS_EDITOR ? `<button class="ned-tl-del btn-del-attach" data-id="${a.id}" title="Hapus">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>` : ''}
        </div>
      </div>`;
  }

  document.getElementById('form-upload-attachment')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    const fileInput = document.getElementById('attach-file');
    const category  = document.getElementById('attach-category')?.value || 'dokumen';
    const alertEl   = document.getElementById('upload-alert');
    const spinner   = document.getElementById('upload-spinner');
    const btnUpload = document.getElementById('btn-do-upload');
    if (!fileInput?.files?.length) return;
    if (fileInput.files[0].size > 10 * 1024 * 1024) {
      showAlert(alertEl, 'danger', 'File terlalu besar (maks. 10 MB)'); return;
    }
    const fd = new FormData();
    fd.append('file', fileInput.files[0]);
    fd.append('category', category);
    fd.append('meeting_id', meetingId);
    if (spinner)   spinner.classList.remove('d-none');
    if (btnUpload) btnUpload.disabled = true;
    if (alertEl)   alertEl.classList.add('d-none');
    try {
      const res  = await fetch(apiBase, { method: 'POST', body: fd });
      const data = await res.json();
      if (data.success) {
        this.reset();
        document.getElementById('upload-form-wrapper').style.display = 'none';
        loadAttachments();
        showToast('✓ Lampiran berhasil diupload', 'success');
      } else {
        showAlert(alertEl, 'danger', data.message || 'Gagal upload');
      }
    } catch { showAlert(alertEl, 'danger', 'Terjadi kesalahan jaringan.'); }
    finally {
      if (spinner)   spinner.classList.add('d-none');
      if (btnUpload) btnUpload.disabled = false;
    }
  });

  loadAttachments();
}

// ─────────────────────────────────────────────────────────────────────
// KOMENTAR / DISKUSI
// URL sesuai route: GET|POST /api/notulen/{id}/comments
//                  POST /api/comments/{id}/resolve
//                  POST /api/comments/{id}/delete
// ─────────────────────────────────────────────────────────────────────
function initComments() {
  const commentList  = document.getElementById('comment-list');
  const commentInput = document.getElementById('comment-input');
  const btnSubmit    = document.getElementById('btn-submit-comment');
  const replyIndic   = document.getElementById('reply-indicator');
  const countEl      = document.getElementById('comment-count');
  const mentionDD    = document.getElementById('mention-dropdown');
  if (!commentList) return;

  const commentsUrl = `${BASE_URL}/api/notulen/${MEETING_ID}/comments`;

  // Load users untuk mention autocomplete
  fetch(`${BASE_URL}/api/users`)
    .then(r => r.json())
    .then(d => { allUsers = d.users || []; })
    .catch(() => {});

  function loadComments() {
    fetch(`${commentsUrl}?show_resolved=${showResolved ? 1 : 0}`)
      .then(r => r.json())
      .then(data => {
        const comments = data.comments || [];
        if (countEl) countEl.textContent = comments.length;
        if (!comments.length) {
          commentList.innerHTML = '<div style="padding:.9rem;text-align:center;font-size:12.5px;color:var(--kb-text-faint);">Belum ada komentar</div>';
          return;
        }
        commentList.innerHTML = comments.map(c => renderComment(c)).join('');
        bindCommentActions();
      })
      .catch(() => {
        commentList.innerHTML = '<div style="padding:.9rem;text-align:center;font-size:12.5px;color:var(--kb-text-faint);">Gagal memuat komentar</div>';
      });
  }

  function renderComment(c) {
    const initials = (c.user_name || 'U').charAt(0).toUpperCase();
    const time     = c.created_at ? new Date(c.created_at).toLocaleString('id-ID', { day:'2-digit', month:'short', hour:'2-digit', minute:'2-digit' }) : '';
    const resolved = c.is_resolved == 1;
    return `
      <div class="ned-comment-item" id="comment-${c.id}" style="padding:.65rem .85rem;border-bottom:1px solid var(--kb-border-light);${resolved ? 'opacity:.55;' : ''}">
        <div class="d-flex gap-2">
          <span class="ned-user-avatar" style="width:28px;height:28px;font-size:11px;flex-shrink:0;">${escHtml(initials)}</span>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.2rem;">
              <span style="font-size:12.5px;font-weight:700;color:var(--kb-text);">${escHtml(c.user_name || 'Anonim')}</span>
              <span style="font-size:11px;color:var(--kb-text-faint);">${time}</span>
            </div>
            ${c.parent_id ? `<div style="font-size:11px;color:var(--kb-text-muted);margin-bottom:.15rem;">↳ Membalas komentar</div>` : ''}
            <div style="font-size:13px;color:var(--kb-text);line-height:1.5;word-break:break-word;">${escHtml(c.content)}</div>
            <div class="d-flex gap-2 mt-1 flex-wrap">
              <button class="btn-comment-reply" data-id="${c.id}" data-name="${escHtml(c.user_name || '')}" style="background:none;border:none;font-size:11px;color:var(--kb-text-muted);cursor:pointer;padding:0;">Balas</button>
              ${IS_EDITOR || c.user_id == CURRENT_USER_ID ? `
              <button class="btn-comment-resolve" data-id="${c.id}" data-resolved="${resolved ? 1 : 0}" style="background:none;border:none;font-size:11px;color:var(--kb-text-muted);cursor:pointer;padding:0;">${resolved ? 'Buka kembali' : 'Selesai'}</button>
              <button class="btn-comment-del" data-id="${c.id}" style="background:none;border:none;font-size:11px;color:var(--kb-red,#dc3545);cursor:pointer;padding:0;">Hapus</button>
              ` : ''}
            </div>
          </div>
        </div>
      </div>`;
  }

  function bindCommentActions() {
    commentList.querySelectorAll('.btn-comment-reply').forEach(btn => {
      btn.addEventListener('click', function () {
        replyToId = this.dataset.id;
        if (replyIndic) replyIndic.textContent = `↳ Membalas ${this.dataset.name}`;
        commentInput?.focus();
      });
    });
    commentList.querySelectorAll('.btn-comment-resolve').forEach(btn => {
      btn.addEventListener('click', async function () {
        const id  = this.dataset.id;
        const cur = parseInt(this.dataset.resolved);
        try {
          const res  = await fetch(`${BASE_URL}/api/comments/${id}/resolve`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ resolved: cur ? 0 : 1 })
          });
          const data = await res.json();
          if (data.success) loadComments();
          else alert(data.message || 'Gagal');
        } catch { alert('Terjadi kesalahan.'); }
      });
    });
    commentList.querySelectorAll('.btn-comment-del').forEach(btn => {
      btn.addEventListener('click', async function () {
        if (!confirm('Hapus komentar ini?')) return;
        try {
          const res  = await fetch(`${BASE_URL}/api/comments/${this.dataset.id}/delete`, { method: 'POST' });
          const data = await res.json();
          if (data.success) loadComments();
          else alert(data.message || 'Gagal menghapus');
        } catch { alert('Terjadi kesalahan.'); }
      });
    });
  }

  commentInput?.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submitComment(); }
  });
  btnSubmit?.addEventListener('click', submitComment);

  async function submitComment() {
    const text = commentInput?.value.trim();
    if (!text) return;
    try {
      const res  = await fetch(commentsUrl, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ content: text, parent_id: replyToId || null })
      });
      const data = await res.json();
      if (data.success) {
        if (commentInput) commentInput.value = '';
        if (replyIndic)   replyIndic.textContent = '';
        replyToId = null;
        loadComments();
      } else {
        alert(data.message || 'Gagal kirim komentar');
      }
    } catch { alert('Terjadi kesalahan.'); }
  }

  document.getElementById('btn-toggle-resolved')?.addEventListener('click', function () {
    showResolved = !showResolved;
    this.textContent = showResolved ? 'Sembunyikan Selesai' : 'Tampilkan Selesai';
    loadComments();
  });

  // Mention autocomplete @
  commentInput?.addEventListener('input', function () {
    const val    = this.value;
    const cursor = this.selectionStart;
    const before = val.substring(0, cursor);
    const match  = before.match(/@([\w.]*)$/);
    if (match && mentionDD) {
      const q        = match[1].toLowerCase();
      const filtered = allUsers.filter(u => u.name.toLowerCase().includes(q)).slice(0, 6);
      if (filtered.length) {
        mentionDD.innerHTML = filtered.map(u =>
          `<li><a class="dropdown-item mention-item" href="#" data-name="${escHtml(u.name)}" style="font-size:13px;">${escHtml(u.name)}</a></li>`
        ).join('');
        mentionDD.classList.add('show');
        mentionDD.querySelectorAll('.mention-item').forEach(a => {
          a.addEventListener('click', function (e) {
            e.preventDefault();
            const name   = this.dataset.name;
            const newVal = val.substring(0, before.lastIndexOf('@')) + '@' + name + ' ' + val.substring(cursor);
            commentInput.value = newVal;
            mentionDD.classList.remove('show');
            mentionDD.innerHTML = '';
            commentInput.focus();
          });
        });
      } else {
        mentionDD.classList.remove('show');
        mentionDD.innerHTML = '';
      }
    } else if (mentionDD) {
      mentionDD.classList.remove('show');
      mentionDD.innerHTML = '';
    }
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('#mention-dropdown') && !e.target.closest('#comment-input') && mentionDD) {
      mentionDD.classList.remove('show');
      mentionDD.innerHTML = '';
    }
  });

  loadComments();
}

// ─────────────────────────────────────────────────────────────────────
// TINDAK LANJUT
// ─────────────────────────────────────────────────────────────────────
function initTindakLanjut() {
  document.querySelectorAll('.btn-tl-del').forEach(btn => {
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      try {
        const res  = await fetch(this.dataset.url, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
          document.getElementById('tl-item-' + this.dataset.id)?.remove();
          if (!document.querySelectorAll('[id^="tl-item-"]').length) {
            const empty = document.getElementById('tl-empty');
            if (empty) empty.style.display = '';
          }
        } else { alert(data.message || 'Gagal menghapus'); }
      } catch { alert('Terjadi kesalahan.'); }
    });
  });

  document.getElementById('btn-tl2-save')?.addEventListener('click', async function () {
    const desc     = document.getElementById('tl2-desk')?.value.trim();
    const assignTo = document.getElementById('tl2-assign')?.value;
    const deadline = document.getElementById('tl2-deadline')?.value;
    const priority = document.getElementById('tl2-priority')?.value;
    if (!desc) { alert('Deskripsi wajib diisi'); return; }
    this.disabled = true;
    try {
      const res  = await fetch(`${BASE_URL}/tindak-lanjut`, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, description: desc,
                                  assigned_to: assignTo || null, due_date: deadline || null, priority })
      });
      const data = await res.json();
      if (data.success) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalTL')).hide();
        location.reload();
      } else { alert(data.message || 'Gagal menyimpan tindak lanjut'); }
    } catch { alert('Terjadi kesalahan. Coba lagi.'); }
    finally { this.disabled = false; }
  });
}

// ─────────────────────────────────────────────────────────────────────
// INIT QUILL
// ─────────────────────────────────────────────────────────────────────
function initQuill() {
  const container = document.getElementById('quill-editor');
  if (!container) return;

  if (typeof Quill === 'undefined') {
    container.innerHTML = '<div class="alert alert-warning m-3">Editor gagal dimuat. Silakan refresh halaman.</div>';
    return;
  }

  const toolbarOptions = IS_EDITOR ? [
    [{ header: [2, 3, 4, false] }],
    ['bold', 'italic', 'underline', 'strike'],
    [{ color: [] }, { background: [] }],
    [{ list: 'ordered' }, { list: 'bullet' }, { list: 'check' }],
    [{ indent: '-1' }, { indent: '+1' }],
    ['blockquote', 'code-block'],
    ['link'],
    ['clean']
  ] : false;

  window.quill = new Quill(container, {
    theme:       'snow',
    readOnly:    !IS_EDITOR,
    placeholder: IS_EDITOR ? 'Mulai tulis notulen di sini...' : 'Belum ada isi notulen.',
    modules:     { toolbar: toolbarOptions }
  });

  if (INITIAL_CONTENT && INITIAL_CONTENT.trim() !== '') {
    window.quill.clipboard.dangerouslyPasteHTML(INITIAL_CONTENT);
    window.quill.history.clear();
  }

  if (IS_EDITOR) {
    window.quill.on('text-change', debouncedSave);
  }

  document.getElementById('btn-save-manual')?.addEventListener('click', async function () {
    this.disabled = true;
    await doSave();
    this.disabled = false;
  });

  initTindakLanjut();
  initAttachment();
  initComments();
  pollNotulen();
}

// ─────────────────────────────────────────────────────────────────────
// HELPERS
// ─────────────────────────────────────────────────────────────────────
function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function formatBytes(bytes) {
  if (bytes < 1024)    return bytes + ' B';
  if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
  return (bytes / 1048576).toFixed(1) + ' MB';
}

function showAlert(el, type, msg) {
  if (!el) return;
  el.className = `alert alert-${type} py-2 small`;
  el.textContent = msg;
  el.classList.remove('d-none');
}

// ─────────────────────────────────────────────────────────────────────
// BOOT
// ─────────────────────────────────────────────────────────────────────
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initQuill);
} else {
  initQuill();
}
