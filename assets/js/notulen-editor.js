/* globals Quill, MEETING_ID, CURRENT_USER_ID, IS_EDITOR,
   INITIAL_CONTENT, SAVE_URL, SYNC_URL, BASE_URL */

// Expose ke window agar script eksternal (template picker, dll) bisa akses
window.quill    = null;
let currentVersion = 0;
let isSyncing      = false;
let saveTimer      = null;

function showToast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
  el.style.zIndex = '9999';
  el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

function setSaveStatus(msg, color) {
  const el = document.getElementById('save-status');
  if (!el) return;
  el.textContent = msg;
  if (color) el.style.color = color;
  else el.style.color = '';
}

function debouncedSave() {
  clearTimeout(saveTimer);
  saveTimer = setTimeout(doSave, 1500);
}

async function doSave() {
  if (!window.quill) return;
  setSaveStatus('Menyimpan...');
  try {
    const isEmpty = window.quill.getText().trim().length === 0;
    // Kirim HTML dari Quill sebagai key 'content' sesuai yang dibaca controller
    const html    = isEmpty ? '' : window.quill.root.innerHTML;

    let res;
    try {
      res = await fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, content: html })
      });
    } catch (networkErr) {
      setSaveStatus('✗ Tidak ada koneksi', 'rgba(255,100,100,.9)');
      return;
    }

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('Response bukan JSON:', text.substring(0, 500));
      setSaveStatus('✗ Server error (response tidak valid)', 'rgba(255,100,100,.9)');
      showToast('⚠️ Gagal menyimpan notulen. Periksa log server.', 'danger');
      return;
    }

    if (data.success) {
      if (data.version) currentVersion = data.version;
      setSaveStatus('✓ Tersimpan ' + new Date().toLocaleTimeString('id-ID'), 'rgba(255,255,255,.65)');
    } else {
      setSaveStatus('✗ Gagal: ' + (data.message || 'error tidak diketahui'), 'rgba(255,100,100,.9)');
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

async function pollNotulen() {
  if (isSyncing || !window.quill) return;
  isSyncing = true;
  try {
    const res  = await fetch(`${SYNC_URL}?meeting_id=${MEETING_ID}&version=${currentVersion}`);
    if (!res.ok) { throw new Error('HTTP ' + res.status); }
    const data = await res.json();

    // Format response dari sync(): { success, updated_at, version, content, editor_name, editor_id }
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
    if (syncEl) {
      syncEl.innerHTML = '<span class="ned-live-dot"></span>Live';
    }
  } catch (e) {
    const syncEl = document.getElementById('sync-status');
    if (syncEl) syncEl.innerHTML = '<span style="width:7px;height:7px;border-radius:50%;background:#f87171;display:inline-block;"></span> Offline';
  } finally {
    isSyncing = false;
    setTimeout(pollNotulen, 5000);
  }
}

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
    modules: { toolbar: toolbarOptions }
  });

  // Load konten awal (HTML dari DB) ke Quill
  if (INITIAL_CONTENT && INITIAL_CONTENT.trim() !== '') {
    window.quill.clipboard.dangerouslyPasteHTML(INITIAL_CONTENT);
    window.quill.history.clear();
  }

  if (IS_EDITOR) {
    window.quill.on('text-change', debouncedSave);
  }

  const btnManual = document.getElementById('btn-save-manual');
  if (btnManual) {
    btnManual.addEventListener('click', async () => {
      btnManual.disabled = true;
      await doSave();
      btnManual.disabled = false;
    });
  }

  // Tindak Lanjut via AJAX
  const btnTl = document.getElementById('btn-tl2-save');
  if (btnTl) {
    btnTl.addEventListener('click', async () => {
      const desc     = document.getElementById('tl2-desk').value.trim();
      const assignTo = document.getElementById('tl2-assign').value;
      const deadline = document.getElementById('tl2-deadline').value;
      const priority = document.getElementById('tl2-priority').value;
      if (!desc) { alert('Deskripsi wajib diisi'); return; }
      btnTl.disabled = true;
      try {
        const res  = await fetch(`${BASE_URL}/tindak-lanjut`, {
          method:  'POST',
          headers: { 'Content-Type': 'application/json' },
          body:    JSON.stringify({
            meeting_id:  MEETING_ID,
            description: desc,
            assigned_to: assignTo || null,
            due_date:    deadline || null,
            priority
          })
        });
        const data = await res.json();
        if (data.success) {
          const modalEl = document.getElementById('modalTL');
          if (modalEl) bootstrap.Modal.getOrCreateInstance(modalEl).hide();
          location.reload();
        } else {
          alert(data.message || 'Gagal menyimpan tindak lanjut');
        }
      } catch (e) {
        alert('Terjadi kesalahan. Coba lagi.');
      } finally {
        btnTl.disabled = false;
      }
    });
  }

  // Hapus tindak lanjut
  document.querySelectorAll('.btn-tl-del').forEach(btn => {
    btn.addEventListener('click', async function () {
      if (!confirm('Hapus tindak lanjut ini?')) return;
      const url = this.dataset.url;
      const id  = this.dataset.id;
      try {
        const res  = await fetch(url, { method: 'POST' });
        const data = await res.json();
        if (data.success) {
          const item = document.getElementById('tl-item-' + id);
          if (item) item.remove();
          const remaining = document.querySelectorAll('[id^="tl-item-"]');
          if (!remaining.length) {
            const empty = document.getElementById('tl-empty');
            if (empty) empty.style.display = '';
          }
        } else {
          alert(data.message || 'Gagal menghapus');
        }
      } catch (e) {
        alert('Terjadi kesalahan.');
      }
    });
  });

  pollNotulen();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initQuill);
} else {
  initQuill();
}
