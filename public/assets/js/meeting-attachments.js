/**
 * meeting-attachments.js
 * Upload, tampil, dan hapus lampiran file di halaman detail meeting
 */
(function () {
  'use strict';

  const panel    = document.getElementById('attachment-panel');
  const list     = document.getElementById('attachment-list');
  const form     = document.getElementById('form-upload-attachment');
  const input    = document.getElementById('attach-file');
  const progress = document.getElementById('upload-progress');
  const countEl  = document.getElementById('attach-count');

  if (!panel || typeof MEETING_ID === 'undefined') return;

  async function loadAttachments() {
    const res  = await fetch(`/api/meetings/${MEETING_ID}/attachments`);
    const data = await res.json();
    if (!data.success) return;
    const items = data.attachments;
    countEl && (countEl.textContent = items.length);
    if (!items.length) {
      list.innerHTML = '<p class="text-muted text-center small py-2">Belum ada lampiran.</p>';
      return;
    }
    list.innerHTML = items.map(a => `
      <div class="d-flex align-items-center gap-2 py-1 border-bottom" data-attach-id="${a.id}">
        <span style="font-size:20px;">${a.icon}</span>
        <div class="flex-fill overflow-hidden">
          <a href="/attachments/${a.id}/download"
             class="fw-semibold text-truncate d-block" style="font-size:13px;"
             title="${escHtml(a.filename)}">${escHtml(a.filename)}</a>
          <small class="text-muted">${a.size_human} &bull; ${escHtml(a.uploader_name)} &bull; ${escHtml(a.category)}</small>
        </div>
        ${a.can_delete ? `<button class="btn btn-sm btn-ghost-danger btn-del-attach p-0 px-1" data-id="${a.id}">&times;</button>` : ''}
      </div>
    `).join('');
    document.querySelectorAll('.btn-del-attach').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (!confirm('Hapus file ini?')) return;
        const r = await fetch(`/api/attachments/${btn.dataset.id}/delete`, { method: 'POST' });
        const d = await r.json();
        if (d.success) loadAttachments();
      });
    });
  }

  // Upload
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!input.files[0]) return;
    const fd = new FormData();
    fd.append('file', input.files[0]);
    fd.append('category', document.getElementById('attach-category')?.value || 'lainnya');
    progress && (progress.style.display = '');
    const xhr = new XMLHttpRequest();
    xhr.open('POST', `/api/meetings/${MEETING_ID}/attachments`);
    xhr.upload.onprogress = e => {
      if (progress && e.lengthComputable) {
        const pct = Math.round(e.loaded / e.total * 100);
        progress.querySelector('.progress-bar').style.width = pct + '%';
      }
    };
    xhr.onload = () => {
      progress && (progress.style.display = 'none');
      const d = JSON.parse(xhr.responseText);
      if (d.success) { form.reset(); loadAttachments(); }
      else alert(d.message || 'Upload gagal');
    };
    xhr.send(fd);
  });

  function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }

  loadAttachments();
})();
