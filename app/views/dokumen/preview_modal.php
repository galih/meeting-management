<?php
/**
 * Partial view: Preview Modal (Fase 3) + Version History (Fase 4) + Public Link (Fase 6)
 * Rebuild UI: panel kanan lebih modern, aksi lebih rapi, rename inline tetap berjalan.
 */
?>
<style>
/* ── Preview overlay ── */
.dm-pv-overlay {
  position:fixed; inset:0;
  background:rgba(10,6,3,.82);
  backdrop-filter:blur(4px);
  z-index:1100;
  display:flex; align-items:stretch;
  opacity:0; pointer-events:none;
  transition:opacity .22s ease;
}
.dm-pv-overlay.open { opacity:1; pointer-events:auto; }

/* ── Preview area (kiri) ── */
.dm-pv-stage {
  flex:1; min-width:0;
  display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  position:relative; overflow:hidden; padding:1.5rem;
}
.dm-pv-topbar {
  position:absolute; top:0; left:0; right:0;
  display:flex; align-items:center; justify-content:space-between;
  padding:.85rem 1.1rem;
  background:linear-gradient(180deg,rgba(10,6,3,.55) 0%,transparent 100%);
  z-index:10;
}
.dm-pv-topbar-title {
  font-size:13px; font-weight:800; color:rgba(255,255,255,.85);
  white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:calc(100% - 120px);
}
.dm-pv-topbar-actions { display:flex; gap:.5rem; align-items:center; flex-shrink:0; }
.dm-pv-icon-btn {
  width:38px; height:38px; border:none; border-radius:12px; cursor:pointer;
  background:rgba(255,255,255,.12); color:#fff;
  display:flex; align-items:center; justify-content:center;
  transition:background .16s ease;
}
.dm-pv-icon-btn:hover { background:rgba(255,255,255,.22); }
.dm-pv-content {
  max-width:100%; max-height:calc(100vh - 96px);
  display:flex; align-items:center; justify-content:center;
}
.dm-pv-content img    { max-width:100%; max-height:calc(100vh - 96px); object-fit:contain; border-radius:10px; }
.dm-pv-content video,
.dm-pv-content audio  { max-width:100%; border-radius:10px; outline:none; }
.dm-pv-content iframe {
  width:calc(100vw - 380px); height:calc(100vh - 64px);
  border:none; border-radius:10px; background:#fff;
}
.dm-pv-content pre {
  background:#18130F; color:#E2D6CA;
  padding:1.5rem; border-radius:12px;
  overflow:auto; max-height:calc(100vh - 96px);
  font-size:13px; font-family:ui-monospace, SFMono-Regular, Menlo, monospace;
  max-width:calc(100vw - 380px); white-space:pre-wrap; word-break:break-all;
  border:1px solid rgba(255,255,255,.07);
}
.dm-pv-empty {
  display:flex; flex-direction:column; align-items:center; gap:1.1rem;
  color:#fff; text-align:center;
}
.dm-pv-empty-icon {
  width:88px; height:88px; border-radius:26px;
  background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12);
  display:flex; align-items:center; justify-content:center; color:rgba(255,255,255,.4);
}
.dm-pv-empty h3   { margin:0; font-size:18px; font-weight:900; color:rgba(255,255,255,.85); }
.dm-pv-empty p    { margin:0; font-size:13.5px; color:rgba(255,255,255,.5); max-width:400px; line-height:1.65; }
.dm-pv-spinner    { display:flex; align-items:center; justify-content:center; gap:.75rem; color:rgba(255,255,255,.7); font-size:14px; }

/* ── Side panel (kanan) ── */
.dm-pv-panel {
  width:320px; flex-shrink:0;
  background:#FDFCFA;
  border-left:1px solid #EEE4D9;
  display:flex; flex-direction:column;
  overflow:hidden;
  box-shadow:-8px 0 28px rgba(15,8,4,.18);
}
.dm-pv-panel-header {
  padding:1.15rem 1.1rem .9rem;
  border-bottom:1px solid #EDE4D9;
  background:linear-gradient(180deg,#FFFFFF 0%,#FAF7F3 100%);
}
.dm-pv-file-row {
  display:flex; gap:.75rem; align-items:flex-start;
}
.dm-pv-file-badge {
  width:44px; height:44px; flex-shrink:0; border-radius:14px;
  display:flex; align-items:center; justify-content:center;
  font-size:11px; font-weight:900; color:#fff;
  box-shadow:inset 0 -6px 14px rgba(0,0,0,.1);
}
.dm-pv-panel-name {
  flex:1; min-width:0;
}
.dm-pv-filename {
  font-size:14px; font-weight:900; color:#1F1A17;
  word-break:break-word; line-height:1.35;
  cursor:pointer; border-radius:8px; padding:.2rem .3rem; margin:-.2rem -.3rem;
  transition:background .15s ease;
  display:block;
}
.dm-pv-filename:hover { background:#F5EEE6; }
.dm-pv-filename-input {
  width:100%; border:1.5px solid #7B1C1C; border-radius:8px;
  padding:.35rem .55rem; font-size:13px; font-weight:800;
  color:#1F1A17; background:#fff; outline:none; display:none;
  box-shadow:0 0 0 3px rgba(123,28,28,.07);
}
.dm-pv-file-size { display:block; margin-top:.3rem; font-size:12px; color:#9A8D7F; }
.dm-pv-body {
  flex:1; overflow-y:auto; display:flex; flex-direction:column; gap:0;
}
.dm-pv-section {
  padding:.95rem 1.1rem;
  border-bottom:1px solid #F2EBE2;
}
.dm-pv-section:last-child { border-bottom:none; }
.dm-pv-section-label {
  font-size:10px; font-weight:900; color:#A09080;
  text-transform:uppercase; letter-spacing:.08em; margin-bottom:.7rem;
}
.dm-pv-row {
  display:flex; justify-content:space-between; align-items:center;
  font-size:12.5px; color:#5A5047; margin-bottom:.4rem;
}
.dm-pv-row:last-child { margin-bottom:0; }
.dm-pv-row-val { font-weight:800; color:#1F1A17; max-width:55%; text-align:right; }
.dm-pv-share-chip {
  display:inline-flex; align-items:center; gap:.35rem;
  background:#F6F1EA; border:1px solid #E8DDCC; border-radius:999px;
  padding:.22rem .6rem; font-size:11.5px; font-weight:800; color:#5A4F44;
  margin:.2rem .2rem 0 0;
}
.dm-pv-share-chip-dot {
  width:8px; height:8px; border-radius:50%; flex-shrink:0;
}
.dm-pv-actions {
  padding:1rem 1.1rem;
  border-top:1px solid #EDE4D9;
  display:flex; flex-direction:column; gap:.5rem;
  background:#FDFCFA;
}
.dm-pv-btn {
  display:flex; align-items:center; gap:.55rem; justify-content:center;
  width:100%; min-height:44px; border-radius:14px;
  font-size:13px; font-weight:800; cursor:pointer;
  border:1.5px solid transparent; text-decoration:none;
  transition:all .18s ease;
}
.dm-pv-btn svg { flex-shrink:0; }
.dm-pv-btn-dl      { background:#7B1C1C; color:#fff; border-color:#5A1212; box-shadow:0 10px 20px rgba(123,28,28,.14); }
.dm-pv-btn-dl:hover { background:#5A1212; }
.dm-pv-btn-history { background:#fff; color:#6B46C1; border-color:#DDD5F5; }
.dm-pv-btn-history:hover { background:#6B46C1; color:#fff; border-color:#5A36AA; }
.dm-pv-btn-share   { background:#fff; color:#2B6CB0; border-color:#CCE0F5; }
.dm-pv-btn-share:hover  { background:#2B6CB0; color:#fff; }
.dm-pv-btn-public  { background:#fff; color:#276749; border-color:#C7E8D7; }
.dm-pv-btn-public:hover { background:#276749; color:#fff; }
.dm-pv-btn-outline { background:#fff; color:#5A5047; border-color:#DDD5C4; }
.dm-pv-btn-outline:hover { color:#1F1A17; border-color:#C4B8A8; }
.dm-pv-btn-danger  { background:#fff; color:#C05621; border-color:#F0D3C4; }
.dm-pv-btn-danger:hover  { background:#C05621; color:#fff; }
.dm-pv-actions-pair { display:grid; grid-template-columns:1fr 1fr; gap:.5rem; }

@media(max-width:760px) {
  .dm-pv-panel { display:none; }
  .dm-pv-content iframe { width:100vw; }
}
</style>

<div class="dm-pv-overlay" id="modal-preview">

  <!-- Stage: area preview isi file -->
  <div class="dm-pv-stage" id="preview-main">
    <div class="dm-pv-topbar">
      <div class="dm-pv-topbar-title" id="pv-topbar-title">Memuat...</div>
      <div class="dm-pv-topbar-actions">
        <a id="pv-dl-shortcut" href="#" class="dm-pv-icon-btn" title="Download" style="display:none">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        </a>
        <button class="dm-pv-icon-btn" onclick="closePreview()" title="Tutup (Esc)">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
      </div>
    </div>
    <div class="dm-pv-content" id="preview-content">
      <div class="dm-pv-spinner">
        <div class="spinner-border"></div>
        Memuat pratinjau...
      </div>
    </div>
  </div>

  <!-- Panel: info & aksi file -->
  <aside class="dm-pv-panel" id="preview-panel">
    <div class="dm-pv-panel-header">
      <div class="dm-pv-file-row">
        <div class="dm-pv-file-badge" id="pv-badge">—</div>
        <div class="dm-pv-panel-name">
          <span class="dm-pv-filename" id="panel-filename">Memuat...</span>
          <input type="text" class="dm-pv-filename-input" id="panel-filename-input">
          <small class="dm-pv-file-size" id="pv-size">—</small>
        </div>
      </div>
    </div>

    <div class="dm-pv-body" id="pv-body">
      <div class="dm-pv-section">
        <div style="text-align:center;color:#A89E90;font-size:13px;padding:.5rem 0">
          <div class="spinner-border spinner-border-sm"></div>
        </div>
      </div>
    </div>

    <div class="dm-pv-actions" id="pv-actions" style="display:none"></div>
  </aside>

</div>

<?php include __DIR__ . '/version_modal.php'; ?>
<?php include __DIR__ . '/public_link_modal.php'; ?>

<script>
(function(){
  const BASE = '<?= rtrim(BASE_URL, '/') ?>';
  let currentFileId = null;

  window.openPreview = function(fileId) {
    currentFileId = fileId;
    document.getElementById('pv-topbar-title').textContent = 'Memuat...';
    document.getElementById('pv-dl-shortcut').style.display = 'none';
    document.getElementById('preview-content').innerHTML =
      '<div class="dm-pv-spinner"><div class="spinner-border"></div>Memuat pratinjau...</div>';
    document.getElementById('pv-badge').textContent = '—';
    document.getElementById('pv-badge').style.background = '#C4B8A8';
    document.getElementById('panel-filename').textContent = 'Memuat...';
    document.getElementById('pv-size').textContent = '';
    document.getElementById('pv-body').innerHTML =
      '<div class="dm-pv-section"><div style="text-align:center;color:#A89E90;font-size:13px;padding:.5rem 0"><div class="spinner-border spinner-border-sm"></div></div></div>';
    document.getElementById('pv-actions').style.display = 'none';
    document.getElementById('modal-preview').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadPreview(fileId);
  };

  window.closePreview = function() {
    document.getElementById('modal-preview').classList.remove('open');
    document.body.style.overflow = '';
    document.querySelectorAll('#preview-content video, #preview-content audio').forEach(m => m.pause());
    currentFileId = null;
  };

  async function loadPreview(fileId) {
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/info')).json();
      if (!data.success) { showStageError('Tidak dapat memuat file.'); renderPanelError(); return; }
      renderStage(data.file);
      renderPanel(data.file, data.shares || []);
    } catch(e) {
      showStageError('Gagal koneksi ke server.');
      renderPanelError();
    }
  }

  function renderStage(f) {
    const box = document.getElementById('preview-content');
    const url = BASE + '/api/dokumen/' + f.id + '/preview';
    document.getElementById('pv-topbar-title').textContent = f.original_name;
    if (f.can_download) {
      const dl = document.getElementById('pv-dl-shortcut');
      dl.href = BASE + '/dokumen/' + f.id + '/download';
      dl.style.display = 'flex';
    }
    if (!f.previewable) {
      box.innerHTML =
        '<div class="dm-pv-empty">'
        + '<div class="dm-pv-empty-icon">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>'
        + '</div>'
        + '<h3>' + escHtml(f.original_name) + '</h3>'
        + '<p>Tipe <strong>' + escHtml(f.mime_label) + '</strong> tidak dapat dipratinjau secara langsung di browser.</p>'
        + (f.can_download ? '<a href="' + BASE + '/dokumen/' + f.id + '/download" class="dm-pv-btn dm-pv-btn-dl" style="max-width:220px">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download File</a>' : '')
        + '</div>';
      return;
    }
    const mt = f.mime_type || '';
    if (mt.startsWith('image/'))
      box.innerHTML = '<img src="' + url + '" alt="' + escHtml(f.original_name) + '" loading="lazy">';
    else if (mt.startsWith('video/'))
      box.innerHTML = '<video controls autoplay><source src="' + url + '" type="' + escHtml(mt) + '">Browser tidak mendukung video.</video>';
    else if (mt.startsWith('audio/'))
      box.innerHTML = '<audio controls autoplay style="width:360px"><source src="' + url + '" type="' + escHtml(mt) + '">Browser tidak mendukung audio.</audio>';
    else if (mt === 'application/pdf')
      box.innerHTML = '<iframe src="' + url + '#toolbar=1&navpanes=0" title="' + escHtml(f.original_name) + '"></iframe>';
    else if (mt === 'text/plain' || mt === 'text/csv') {
      fetch(url).then(r => r.text()).then(text => {
        box.innerHTML = '<pre>' + escHtml(text.slice(0, 60000)) + '</pre>';
      }).catch(() => showStageError('Gagal memuat teks.'));
    } else {
      showStageError('Tipe file tidak dapat dipratinjau.');
    }
  }

  function showStageError(msg) {
    document.getElementById('preview-content').innerHTML =
      '<div class="dm-pv-empty"><div class="dm-pv-empty-icon">'
      + '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
      + '</div><p style="color:rgba(255,200,180,.7)">' + escHtml(msg) + '</p></div>';
  }

  function renderPanelError() {
    document.getElementById('pv-body').innerHTML =
      '<div class="dm-pv-section"><p style="color:#C05621;font-size:13px">Gagal memuat info file.</p></div>';
  }

  function renderPanel(f, shares) {
    // Badge & header
    const badge = document.getElementById('pv-badge');
    badge.textContent = f.mime_label || '?';
    badge.style.background = f.mime_color || '#A89E90';
    const nameEl  = document.getElementById('panel-filename');
    const inputEl = document.getElementById('panel-filename-input');
    nameEl.textContent = f.original_name;
    document.getElementById('pv-size').textContent = f.size_fmt || '';

    // Rename inline
    if (f.can_share || f.can_delete) {
      nameEl.title = 'Klik untuk ubah nama';
      nameEl.onclick = () => {
        nameEl.style.display = 'none';
        inputEl.style.display = 'block';
        inputEl.value = nameEl.textContent;
        inputEl.focus(); inputEl.select();
      };
      inputEl.onblur = () => commitRename(f.id, nameEl, inputEl);
      inputEl.onkeydown = e => {
        if (e.key === 'Enter')  { e.preventDefault(); inputEl.blur(); }
        if (e.key === 'Escape') { inputEl.style.display = 'none'; nameEl.style.display = ''; }
      };
    } else {
      nameEl.style.cursor = 'default';
      nameEl.title = '';
    }

    // Body sections
    const body = document.getElementById('pv-body');
    const tgl = f.created_at
      ? new Date(f.updated_at || f.created_at).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' })
      : '—';

    let html = '';

    // Detail section
    html +=
      '<div class="dm-pv-section">'
      + '<div class="dm-pv-section-label">Informasi File</div>'
      + row('Tipe', f.mime_label)
      + row('Ukuran', f.size_fmt)
      + row('Diupload oleh', f.uploader_name || '—')
      + row('Terakhir diperbarui', tgl)
      + '</div>';

    // Share section
    html += '<div class="dm-pv-section"><div class="dm-pv-section-label">Dibagikan ke</div>';
    if (!shares || !shares.length) {
      html += '<p style="font-size:12.5px;color:#A89E90;margin:0">Belum dibagikan ke siapa pun.</p>';
    } else {
      const permColor = { download:'#276749', view:'#2B6CB0' };
      html += '<div style="display:flex;flex-wrap:wrap;gap:.25rem">';
      html += shares.map(s =>
        '<span class="dm-pv-share-chip">'
        + '<span class="dm-pv-share-chip-dot" style="background:' + (permColor[s.permission] || '#A89E90') + '"></span>'
        + escHtml(s.user_name)
        + '</span>'
      ).join('');
      html += '</div>';
    }
    html += '</div>';

    body.innerHTML = html;

    // Actions
    const acts = document.getElementById('pv-actions');
    acts.style.display = '';
    let aHtml = '';

    if (f.can_download)
      aHtml +=
        '<a href="' + BASE + '/dokumen/' + f.id + '/download" class="dm-pv-btn dm-pv-btn-dl">'
        + icon('dl') + ' Download</a>';

    aHtml += '<div class="dm-pv-actions-pair">';

    aHtml +=
      '<button class="dm-pv-btn dm-pv-btn-history" onclick="openVersionModal(' + f.id + ',' + sq(f.original_name) + ',' + (f.can_share || f.can_delete ? 'true' : 'false') + ')">'
      + icon('history') + ' Riwayat</button>';

    if (f.can_share)
      aHtml +=
        '<button class="dm-pv-btn dm-pv-btn-public" onclick="closePreview();openPublicLinkModal(' + f.id + ',' + sq(f.original_name) + ')">'
        + icon('public') + ' Publik</button>';
    else
      aHtml += '<span></span>';

    aHtml += '</div>';

    if (f.can_share)
      aHtml +=
        '<button class="dm-pv-btn dm-pv-btn-share" onclick="closePreview();openShareModal(' + f.id + ',' + sq(f.original_name) + ')">'
        + icon('share') + ' Bagikan ke User</button>';

    if (f.can_delete)
      aHtml +=
        '<button class="dm-pv-btn dm-pv-btn-danger" onclick="closePreview();deleteFile(' + f.id + ',' + sq(f.original_name) + ')">'
        + icon('trash') + ' Hapus File</button>';

    acts.innerHTML = aHtml;
  }

  function row(label, val) {
    return '<div class="dm-pv-row"><span>' + label + '</span><span class="dm-pv-row-val">' + escHtml(String(val||'—')) + '</span></div>';
  }

  function sq(s) { return "'" + String(s||'').replace(/'/g, "\\'" ) + "'"; }

  const ICONS = {
    dl:      '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
    history: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
    public:  '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    share:   '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
    trash:   '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>',
  };
  function icon(k) { return ICONS[k] || ''; }

  async function commitRename(fileId, nameEl, inputEl) {
    const newName = (inputEl.value || '').trim();
    inputEl.style.display = 'none';
    nameEl.style.display  = '';
    if (!newName || newName === nameEl.textContent) return;
    const fd = new FormData();
    fd.append('name', newName);
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/rename', { method:'POST', body:fd })).json();
      if (data.success) {
        nameEl.textContent = newName;
        document.getElementById('pv-topbar-title').textContent = newName;
        const rowName = document.querySelector('#file-row-' + fileId + ' .dm-file-name');
        if (rowName) rowName.textContent = newName;
      } else {
        alert(data.message || 'Gagal mengubah nama.');
      }
    } catch(e) {
      alert('Gagal koneksi.');
    }
  }

  document.addEventListener('keydown', e => { if (e.key === 'Escape') closePreview(); });
  document.getElementById('modal-preview').addEventListener('click', e => {
    if (e.target === document.getElementById('preview-main')) closePreview();
  });

  function escHtml(s) { const d = document.createElement('div'); d.textContent = String(s || ''); return d.innerHTML; }
})();
</script>
