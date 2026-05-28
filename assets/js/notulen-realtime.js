/* globals EditorJS, Header, NestedList, Checklist, Table, Underline,
   MEETING_ID, CURRENT_USER_ID, IS_EDITOR,
   INITIAL_CONTENT, SAVE_URL, SYNC_URL */

let editor         = null;
let currentVersion = 0;
let isSyncing      = false;
let saveTimer      = null;

function debounce(fn, delay) {
  return function () {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(fn, delay);
  };
}

function showToast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
  el.style.zIndex = '9999';
  el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

function parseInitialContent() {
  try {
    if (!INITIAL_CONTENT || INITIAL_CONTENT === '{}' || INITIAL_CONTENT === '') return { blocks: [] };
    const parsed = (typeof INITIAL_CONTENT === 'string') ? JSON.parse(INITIAL_CONTENT) : INITIAL_CONTENT;
    return (parsed && Array.isArray(parsed.blocks) && parsed.blocks.length > 0)
      ? parsed
      : { blocks: [] };
  } catch (e) {
    return { blocks: [] };
  }
}

async function doSave() {
  if (!editor) return;
  const saveStatus = document.getElementById('save-status');
  if (saveStatus) saveStatus.textContent = 'Menyimpan...';
  try {
    const content = await editor.save();
    const res  = await fetch(SAVE_URL, {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({ meeting_id: MEETING_ID, content })
    });
    const text = await res.text();
    let data;
    try   { data = JSON.parse(text); }
    catch (e) {
      console.error('Response bukan JSON:', text);
      if (saveStatus) saveStatus.textContent = '\u2717 Server error';
      return;
    }
    if (data.success) {
      if (data.version) currentVersion = data.version;
      if (saveStatus) saveStatus.textContent = '\u2713 Tersimpan ' + new Date().toLocaleTimeString('id-ID');
    } else {
      if (saveStatus) saveStatus.textContent = '\u2717 Gagal: ' + (data.message || 'error');
      if (res.status === 403) showToast('\u26a0\ufe0f Sesi kadaluarsa. Silakan <a href="/logout" class="alert-link">login ulang</a>.', 'warning');
    }
  } catch (e) {
    console.error('Save error:', e);
    if (saveStatus) saveStatus.textContent = '\u2717 Gagal simpan';
  }
}

async function pollNotulen() {
  if (isSyncing || !editor) return;
  isSyncing = true;
  try {
    const res  = await fetch(`${SYNC_URL}?meeting_id=${MEETING_ID}&version=${currentVersion}`);
    const data = await res.json();
    if (data.status === 'updated') {
      currentVersion = data.data.version;
      if (data.data.last_edited_by_id != CURRENT_USER_ID) {
        const parsed = typeof data.data.content === 'string'
          ? JSON.parse(data.data.content)
          : data.data.content;
        await editor.render(parsed);
        showToast(`\u270f\ufe0f <strong>${data.data.editor_name}</strong> memperbarui notulen`);
      }
    }
  } catch (e) {
    const syncEl = document.getElementById('sync-status');
    if (syncEl) syncEl.innerHTML = '<span class="status-dot bg-red d-inline-block me-1"></span>Offline';
  } finally {
    isSyncing = false;
    setTimeout(pollNotulen, 3000);
  }
}

function initEditor() {
  if (typeof EditorJS === 'undefined') {
    const holder = document.getElementById('editorjs');
    if (holder) holder.innerHTML = '<div class="alert alert-warning m-3">Editor gagal di-load. Coba refresh halaman.</div>';
    return;
  }

  const debouncedSave = IS_EDITOR ? debounce(doSave, 1500) : null;

  editor = new EditorJS({
    holder:      'editorjs',
    readOnly:    !IS_EDITOR,
    placeholder: IS_EDITOR ? 'Mulai tulis notulen di sini...' : '',
    tools: {
      header:    { class: Header,     inlineToolbar: true, config: { levels: [2,3,4], defaultLevel: 2 } },
      list:      { class: NestedList, inlineToolbar: true, config: { defaultStyle: 'unordered' } },
      checklist: { class: Checklist,  inlineToolbar: true },
      table:     { class: Table,      config: { rows: 2, cols: 3, withHeadings: true } },
      underline: { class: Underline }
    },
    data: parseInitialContent(),
    onReady() {
      pollNotulen();
      const btn = document.getElementById('btn-save-manual');
      if (btn) {
        btn.addEventListener('click', async () => {
          btn.disabled = true;
          await doSave();
          btn.disabled = false;
        });
      }
    },
    onChange: IS_EDITOR ? (_api, _event) => { debouncedSave(); } : undefined
  });
}

// Gunakan DOMContentLoaded; karena EditorJS CDN ada di <head> ia sudah ter-parse
// sebelum body scripts berjalan — initEditor() aman dipanggil di sini.
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initEditor);
} else {
  // DOM sudah ready (script di-load async/defer atau setelah DOMContentLoaded)
  initEditor();
}
