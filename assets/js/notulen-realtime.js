/* global EditorJS, Header, List, Checklist, Table,
   MEETING_ID, CURRENT_USER_ID, IS_EDITOR, INITIAL_CONTENT,
   SAVE_URL, SYNC_URL */

let currentVersion = 0;
let isSyncing      = false;

function debounce(fn, delay) {
  let t;
  return (...args) => { clearTimeout(t); t = setTimeout(() => fn(...args), delay); };
}

function showToast(msg, type = 'info') {
  const el = document.createElement('div');
  el.className = `alert alert-${type} alert-dismissible position-fixed bottom-0 end-0 m-3 shadow`;
  el.style.zIndex = '9999';
  el.innerHTML = msg + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
  document.body.appendChild(el);
  setTimeout(() => el.remove(), 4000);
}

// Parse INITIAL_CONTENT dengan aman — bisa berupa string JSON atau object
function parseInitialContent() {
  try {
    if (!INITIAL_CONTENT || INITIAL_CONTENT === '{}' || INITIAL_CONTENT === '') return {};
    const parsed = (typeof INITIAL_CONTENT === 'string') ? JSON.parse(INITIAL_CONTENT) : INITIAL_CONTENT;
    return (parsed && typeof parsed === 'object') ? parsed : {};
  } catch (e) {
    return {};
  }
}

const editor = new EditorJS({
  holder:      'editorjs',
  readOnly:    !IS_EDITOR,
  placeholder: IS_EDITOR ? 'Mulai tulis notulen di sini...' : '',
  tools: {
    header:    { class: Header,    inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } },
    list:      { class: List,      inlineToolbar: true },
    checklist: { class: Checklist, inlineToolbar: true },
    table:     { class: Table,     config: { rows: 2, cols: 3 } }
  },
  data: parseInitialContent(),
  onChange: IS_EDITOR ? debounce(async () => {
    const saveStatus = document.getElementById('save-status');
    if (saveStatus) saveStatus.textContent = 'Menyimpan...';
    try {
      const content = await editor.save();
      const res = await fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, content })
      });
      const data = await res.json();
      if (data.success) {
        if (data.version) currentVersion = data.version;
        if (saveStatus) saveStatus.textContent = '\u2713 Tersimpan ' + new Date().toLocaleTimeString('id-ID');
      } else {
        if (saveStatus) saveStatus.textContent = '\u2717 Gagal simpan';
      }
    } catch (e) {
      console.error('Save error:', e);
      if (saveStatus) saveStatus.textContent = '\u2717 Gagal simpan';
    }
  }, 1500) : undefined
});

async function pollNotulen() {
  if (isSyncing) return;
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

document.addEventListener('DOMContentLoaded', () => pollNotulen());
