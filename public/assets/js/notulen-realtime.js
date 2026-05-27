/* global EditorJS, Header, List, Checklist, Table, MEETING_ID, CURRENT_USER_ID, IS_EDITOR, INITIAL_CONTENT */

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

const editor = new EditorJS({
  holder:   'editorjs',
  readOnly: !IS_EDITOR,
  placeholder: IS_EDITOR ? 'Mulai tulis notulen di sini...' : '',
  tools: {
    header:    { class: Header,    inlineToolbar: true, config: { levels: [2, 3, 4], defaultLevel: 2 } },
    list:      { class: List,      inlineToolbar: true },
    checklist: { class: Checklist, inlineToolbar: true },
    table:     { class: Table,     config: { rows: 2, cols: 3 } }
  },
  data: (typeof INITIAL_CONTENT === 'string' && INITIAL_CONTENT !== '{}')
        ? JSON.parse(INITIAL_CONTENT)
        : {},
  onChange: IS_EDITOR ? debounce(async () => {
    const saveStatus = document.getElementById('save-status');
    if (saveStatus) saveStatus.textContent = 'Menyimpan...';
    try {
      const content = await editor.save();
      const res = await fetch('/api/notulen/save', {
        method:  'POST',
        headers: { 'Content-Type': 'application/json' },
        body:    JSON.stringify({ meeting_id: MEETING_ID, content })
      });
      const data = await res.json();
      if (data.success) {
        currentVersion = data.version;
        if (saveStatus) saveStatus.textContent = '✓ Tersimpan ' + new Date().toLocaleTimeString('id-ID');
      }
    } catch (e) {
      if (saveStatus) saveStatus.textContent = '✗ Gagal simpan';
    }
  }, 1500) : undefined
});

async function pollNotulen() {
  if (isSyncing) return;
  isSyncing = true;
  try {
    const res  = await fetch(`/api/notulen/sync?meeting_id=${MEETING_ID}&version=${currentVersion}`);
    const data = await res.json();
    if (data.status === 'updated') {
      currentVersion = data.data.version;
      if (data.data.last_edited_by_id != CURRENT_USER_ID) {
        await editor.render(JSON.parse(data.data.content));
        showToast(`✏️ <strong>${data.data.editor_name}</strong> memperbarui notulen`);
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
