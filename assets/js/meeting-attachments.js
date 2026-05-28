/**
 * meeting-attachments.js
 * Upload, tampil, dan hapus lampiran file
 * Digunakan di: halaman detail meeting (show.php) & halaman notulen (editor.php)
 */
(function () {
  'use strict';

  function init() {
    const panel    = document.getElementById('attachment-panel');
    const list     = document.getElementById('attachment-list');
    const form     = document.getElementById('form-upload-attachment');
    const input    = document.getElementById('attach-file');
    const countEl  = document.getElementById('attach-count');

    // Cari MEETING_ID dari: window global, atau data-attribute pada panel
    const meetingId = (typeof MEETING_ID !== 'undefined' ? MEETING_ID : null)
                   || (panel ? parseInt(panel.dataset.meetingId) : null);

    if (!panel || !meetingId) return;

    // ── Toggle form upload ──────────────────────────────────────
    const btnShow   = document.getElementById('btn-show-upload-form');
    const btnCancel = document.getElementById('btn-cancel-upload');
    const wrapper   = document.getElementById('upload-form-wrapper');

    btnShow?.addEventListener('click', () => {
      if (wrapper) wrapper.style.display = '';
      btnShow.style.display = 'none';
    });
    btnCancel?.addEventListener('click', () => {
      if (wrapper) wrapper.style.display = 'none';
      if (btnShow) btnShow.style.display = '';
      form?.reset();
      const alertEl = document.getElementById('upload-alert');
      if (alertEl) { alertEl.className = 'd-none'; alertEl.innerHTML = ''; }
    });

    // ── Load daftar lampiran ────────────────────────────────────
    async function loadAttachments() {
      try {
        const res  = await fetch(`${BASE_URL}/api/meetings/${meetingId}/attachments`);
        const data = await res.json();
        if (!data.success) {
          list.innerHTML = '<p class="text-muted text-center small py-3">Gagal memuat lampiran.</p>';
          return;
        }
        const items = data.attachments;
        if (countEl) countEl.textContent = items.length;
        if (!items.length) {
          list.innerHTML = '<p class="text-muted text-center small py-3">Belum ada lampiran.</p>';
          return;
        }
        list.innerHTML = items.map(a => `
          <div class="list-group-item px-3 py-2 d-flex align-items-center gap-2" data-attach-id="${a.id}">
            <span style="font-size:20px;line-height:1;">${escHtml(a.icon ?? '📎')}</span>
            <div class="flex-fill overflow-hidden">
              <a href="${BASE_URL}/attachments/${a.id}/download"
                 class="fw-semibold d-block text-truncate" style="font-size:12.5px;"
                 title="${escHtml(a.filename)}">${escHtml(a.filename)}</a>
              <small class="text-muted" style="font-size:11px;">
                ${escHtml(a.size_human)} &bull; ${escHtml(a.uploader_name)} &bull;
                <span class="badge bg-orange-lt" style="font-size:9px;">${escHtml(a.category)}</span>
              </small>
            </div>
            ${a.can_delete
              ? `<button class="btn btn-sm btn-ghost-danger btn-del-attach p-0 px-1" data-id="${a.id}" title="Hapus">&times;</button>`
              : ''}
          </div>
        `).join('');

        document.querySelectorAll('.btn-del-attach').forEach(btn => {
          btn.addEventListener('click', async () => {
            if (!confirm('Hapus file ini?')) return;
            const r = await fetch(`${BASE_URL}/api/attachments/${btn.dataset.id}/delete`, { method: 'POST' });
            const d = await r.json();
            if (d.success) loadAttachments();
            else alert(d.message || 'Gagal menghapus.');
          });
        });
      } catch (err) {
        list.innerHTML = '<p class="text-danger text-center small py-3">Error: ' + escHtml(err.message) + '</p>';
      }
    }

    // ── Upload ──────────────────────────────────────────────────
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!input?.files[0]) return;

      const alertEl  = document.getElementById('upload-alert');
      const spinner  = document.getElementById('upload-spinner');
      const btnUpload = document.getElementById('btn-do-upload');

      if (spinner)  spinner.classList.remove('d-none');
      if (btnUpload) btnUpload.disabled = true;
      if (alertEl)  { alertEl.className = 'd-none'; alertEl.innerHTML = ''; }

      const fd = new FormData();
      fd.append('file', input.files[0]);
      fd.append('category', document.getElementById('attach-category')?.value || 'lainnya');

      try {
        const res = await fetch(`${BASE_URL}/api/meetings/${meetingId}/attachments`, {
          method: 'POST', body: fd
        });
        const d = await res.json();
        if (d.success) {
          form.reset();
          if (wrapper) wrapper.style.display = 'none';
          if (btnShow) btnShow.style.display = '';
          loadAttachments();
        } else {
          if (alertEl) {
            alertEl.className = 'alert alert-danger py-2 mt-1';
            alertEl.innerHTML = d.message || 'Upload gagal.';
          } else {
            alert(d.message || 'Upload gagal.');
          }
        }
      } catch (err) {
        if (alertEl) {
          alertEl.className = 'alert alert-danger py-2 mt-1';
          alertEl.innerHTML = 'Koneksi gagal: ' + escHtml(err.message);
        }
      } finally {
        if (spinner)  spinner.classList.add('d-none');
        if (btnUpload) btnUpload.disabled = false;
      }
    });

    function escHtml(s) {
      return String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    loadAttachments();
  }

  // Tunggu DOM siap — handle kasus script di-load sebelum DOM selesai
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
