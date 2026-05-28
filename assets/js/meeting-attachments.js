/**
 * meeting-attachments.js
 * Digunakan di: halaman detail meeting (show.php) & halaman notulen (editor.php)
 */
(function () {
  'use strict';

  function init() {
    const panel    = document.getElementById('attachment-panel');
    const list     = document.getElementById('attachment-list');
    const countEl  = document.getElementById('attach-count');
    const form     = document.getElementById('form-upload-attachment');
    const input    = document.getElementById('attach-file');

    if (!panel || !list) return;

    // Baca meeting ID: dari window.MEETING_ID atau data-meeting-id pada panel
    const meetingId = (typeof window.MEETING_ID !== 'undefined' && window.MEETING_ID)
                    ? parseInt(window.MEETING_ID)
                    : parseInt(panel.dataset.meetingId || '0');

    if (!meetingId || isNaN(meetingId)) {
      list.innerHTML = '<div class="list-group-item text-center text-danger py-3 small">Konfigurasi error: meeting ID tidak ditemukan.</div>';
      return;
    }

    // Pastikan BASE_URL tersedia
    const baseUrl = (typeof BASE_URL !== 'undefined') ? BASE_URL.replace(/\/$/, '') : '';

    // ── Toggle form upload ────────────────────────────────────────────
    const btnShow    = document.getElementById('btn-show-upload-form');
    const btnCancel  = document.getElementById('btn-cancel-upload');
    const wrapper    = document.getElementById('upload-form-wrapper');

    btnShow?.addEventListener('click', () => {
      if (wrapper) wrapper.style.display = '';
      if (btnShow) btnShow.style.display = 'none';
    });

    btnCancel?.addEventListener('click', () => {
      resetForm();
    });

    function resetForm() {
      if (wrapper) wrapper.style.display = 'none';
      if (btnShow) btnShow.style.display = '';
      form?.reset();
      const alertEl = document.getElementById('upload-alert');
      if (alertEl) { alertEl.className = 'd-none'; alertEl.innerHTML = ''; }
    }

    // ── Load daftar lampiran ──────────────────────────────────────────
    async function loadAttachments() {
      list.innerHTML = '<div class="list-group-item text-center text-muted py-3 small"><span class="spinner-border spinner-border-sm"></span> Memuat...</div>';
      try {
        const res = await fetch(`${baseUrl}/api/meetings/${meetingId}/attachments`, {
          credentials: 'same-origin'
        });

        // Cek apakah response JSON
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
          const txt = await res.text();
          list.innerHTML = `<div class="list-group-item text-danger small px-3 py-2">Server error (${res.status}): ${escHtml(txt.substring(0, 200))}</div>`;
          return;
        }

        const data = await res.json();
        if (!data.success) {
          list.innerHTML = `<div class="list-group-item text-danger small px-3 py-2">${escHtml(data.message || 'Gagal memuat lampiran')}</div>`;
          return;
        }

        const items = data.attachments || [];
        if (countEl) countEl.textContent = items.length;

        if (!items.length) {
          list.innerHTML = '<div class="list-group-item text-muted text-center py-3 small">Belum ada lampiran.</div>';
          return;
        }

        list.innerHTML = items.map(a => `
          <div class="list-group-item px-3 py-2 d-flex align-items-center gap-2" data-attach-id="${a.id}">
            <span style="font-size:20px;line-height:1;">${a.icon ?? '📎'}</span>
            <div class="flex-fill overflow-hidden">
              <a href="${baseUrl}/attachments/${a.id}/download"
                 class="fw-semibold d-block text-truncate" style="font-size:12.5px;"
                 title="${escHtml(a.filename)}">${escHtml(a.filename)}</a>
              <small class="text-muted" style="font-size:11px;">
                ${escHtml(a.size_human)} &bull; ${escHtml(a.uploader_name)}
                &bull; <span class="badge bg-orange-lt" style="font-size:9px;">${escHtml(a.category)}</span>
              </small>
            </div>
            ${a.can_delete
              ? `<button class="btn btn-sm btn-ghost-danger btn-del-attach p-0 px-1"
                        data-id="${a.id}" title="Hapus file">&times;</button>`
              : ''}
          </div>
        `).join('');

        list.querySelectorAll('.btn-del-attach').forEach(btn => {
          btn.addEventListener('click', async () => {
            if (!confirm('Hapus file ini?')) return;
            try {
              const r = await fetch(`${baseUrl}/api/attachments/${btn.dataset.id}/delete`, {
                method: 'POST', credentials: 'same-origin'
              });
              const d = await r.json();
              if (d.success) loadAttachments();
              else alert(d.message || 'Gagal menghapus.');
            } catch (e) {
              alert('Koneksi gagal: ' + e.message);
            }
          });
        });

      } catch (err) {
        list.innerHTML = `<div class="list-group-item text-danger small px-3 py-2">Gagal menghubungi server: ${escHtml(err.message)}</div>`;
      }
    }

    // ── Upload ────────────────────────────────────────────────────────
    form?.addEventListener('submit', async (e) => {
      e.preventDefault();
      if (!input?.files[0]) return;

      const alertEl   = document.getElementById('upload-alert');
      const spinner   = document.getElementById('upload-spinner');
      const btnUpload = document.getElementById('btn-do-upload');

      if (spinner)   spinner.classList.remove('d-none');
      if (btnUpload) btnUpload.disabled = true;
      if (alertEl)   { alertEl.className = 'd-none'; alertEl.innerHTML = ''; }

      const fd = new FormData();
      fd.append('file', input.files[0]);
      fd.append('category', document.getElementById('attach-category')?.value || 'lainnya');

      try {
        const res = await fetch(`${baseUrl}/api/meetings/${meetingId}/attachments`, {
          method: 'POST', body: fd, credentials: 'same-origin'
        });

        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
          const txt = await res.text();
          throw new Error(`Server (${res.status}): ${txt.substring(0, 200)}`);
        }

        const d = await res.json();
        if (d.success) {
          resetForm();
          loadAttachments();
        } else {
          throw new Error(d.message || 'Upload gagal.');
        }
      } catch (err) {
        if (alertEl) {
          alertEl.className = 'alert alert-danger py-2 mt-1 mb-0';
          alertEl.textContent = err.message;
        } else {
          alert(err.message);
        }
      } finally {
        if (spinner)   spinner.classList.add('d-none');
        if (btnUpload) btnUpload.disabled = false;
      }
    });

    function escHtml(s) {
      return String(s ?? '')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    loadAttachments();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
