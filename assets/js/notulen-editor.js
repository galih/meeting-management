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

function setSaveStatus(msg) {
  const el = document.getElementById('save-status');
  if (el) el.textContent = msg;
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
    const html    = isEmpty ? '' : window.quill.root.innerHTML;

    let res;
    try {
      res = await fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, content: html })
      });
    } catch (networkErr) {
      setSaveStatus('✗ Tidak ada koneksi');
      return;
    }

    const text = await res.text();
    let data;
    try {
      data = JSON.parse(text);
    } catch (e) {
      console.error('Response bukan JSON:', text.substring(0, 500));
      setSaveStatus('✗ Server error (response tidak valid)');
      showToast('⚠️ Gagal menyimpan notulen. Periksa log server.', 'danger');
      return;
    }

    if (data.success) {
      if (data.version) currentVersion = data.version;
      setSaveStatus('✓ Tersimpan ' + new Date().toLocaleTimeString('id-ID'));
    } else {
      setSaveStatus('✗ Gagal: ' + (data.message || 'error tidak diketahui'));
      if (res.status === 403) {
        showToast('⚠️ Sesi kadaluarsa. <a href="/logout" class="alert-link">Login ulang</a>', 'warning');
      } else {
        showToast('✗ Gagal simpan: ' + (data.message || 'error'), 'danger');
      }
    }
  } catch (e) {
    console.error('Save error:', e);
    setSaveStatus('✗ Gagal simpan');
  }
}

async function pollNotulen() {
  if (isSyncing || !window.quill) return;
  isSyncing = true;
  try {
    const res  = await fetch(`${SYNC_URL}?meeting_id=${MEETING_ID}&version=${currentVersion}`);
    const data = await res.json();
    if (data.status === 'updated') {
      currentVersion = data.data.version;
      if (parseInt(data.data.last_edited_by_id) !== CURRENT_USER_ID) {
        window.quill.root.innerHTML = data.data.content || '';
        showToast(`✏️ <strong>${data.data.editor_name}</strong> memperbarui notulen`);
      }
    }
  } catch (e) {
    const syncEl = document.getElementById('sync-status');
    if (syncEl) syncEl.innerHTML = '<span class="status-dot bg-red d-inline-block me-1"></span>Offline';
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

  if (INITIAL_CONTENT && INITIAL_CONTENT.trim() !== '') {
    window.quill.root.innerHTML = INITIAL_CONTENT;
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
          const modal = bootstrap.Modal.getInstance(document.getElementById('modalTL'));
          if (modal) modal.hide();
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

  pollNotulen();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initQuill);
} else {
  initQuill();
}
