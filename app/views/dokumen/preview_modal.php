<?php
/**
 * Partial view: Preview Modal (Fase 3)
 * Di-include dari dokumen/index.php
 * Tidak memerlukan variabel khusus — semua dari JS.
 */
?>
<!-- ======================== MODAL: PREVIEW FILE ======================== -->
<style>
.dm-preview-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.72);
  z-index:1100; display:flex; align-items:stretch;
  opacity:0; pointer-events:none; transition:opacity 200ms;
}
.dm-preview-overlay.open { opacity:1; pointer-events:auto; }

/* Kiri: konten preview */
.dm-preview-main {
  flex:1; display:flex; flex-direction:column;
  align-items:center; justify-content:center;
  overflow:hidden; position:relative;
  padding:1rem;
}
.dm-preview-close-btn {
  position:absolute; top:1rem; left:1rem;
  background:rgba(255,255,255,.12); border:none; color:#fff;
  width:36px; height:36px; border-radius:50%; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  transition:background 150ms; z-index:10;
  font-size:18px; line-height:1;
}
.dm-preview-close-btn:hover { background:rgba(255,255,255,.25); }

.dm-preview-content { max-width:100%; max-height:calc(100vh - 80px); display:flex; align-items:center; justify-content:center; }
.dm-preview-content img   { max-width:100%; max-height:calc(100vh - 80px); object-fit:contain; border-radius:6px; }
.dm-preview-content video,
.dm-preview-content audio { max-width:100%; border-radius:6px; outline:none; }
.dm-preview-content iframe { width:calc(100vw - 360px); height:calc(100vh - 50px); border:none; border-radius:6px; background:#fff; }
.dm-preview-content pre   {
  background:#1e1e1e; color:#d4d4d4;
  padding:1.5rem; border-radius:8px; overflow:auto;
  max-height:calc(100vh - 80px); font-size:13px;
  max-width:calc(100vw - 360px); white-space:pre-wrap; word-break:break-all;
}
.dm-preview-nopreview {
  display:flex; flex-direction:column; align-items:center; gap:1rem;
  color:#fff; text-align:center;
}
.dm-preview-nopreview svg  { opacity:.4; }
.dm-preview-nopreview p    { margin:0; font-size:14px; opacity:.7; }
.dm-preview-nopreview span { font-size:20px; font-weight:800; }

/* Kanan: panel info */
.dm-preview-panel {
  width:300px; flex-shrink:0;
  background:#fff; overflow-y:auto;
  display:flex; flex-direction:column;
  box-shadow:-4px 0 20px rgba(0,0,0,.2);
}
.dm-preview-panel-header {
  padding:1.1rem 1.1rem .75rem;
  border-bottom:1px solid #F0EBE2;
  display:flex; align-items:flex-start; gap:.6rem;
}
.dm-panel-file-icon {
  width:40px; height:40px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  font-size:11px; font-weight:800; color:#fff; flex-shrink:0;
}
.dm-panel-title {
  flex:1; min-width:0;
}
.dm-panel-filename {
  font-size:13.5px; font-weight:800; color:#1C1714;
  word-break:break-all; line-height:1.3;
  cursor:pointer; border-radius:5px; padding:.1rem .2rem;
  transition:background 150ms;
}
.dm-panel-filename:hover { background:#F5F0E8; }
.dm-panel-filename-input {
  width:100%; border:1.5px solid #7B1C1C; border-radius:5px;
  padding:.2rem .4rem; font-size:13px; font-weight:700;
  color:#1C1714; background:#fff; outline:none; display:none;
}
.dm-panel-meta { font-size:11.5px; color:#A89E90; margin-top:.2rem; }

.dm-panel-section {
  padding:.85rem 1.1rem;
  border-bottom:1px solid #F5F0E8;
}
.dm-panel-section-title {
  font-size:10.5px; font-weight:800; color:#A89E90;
  text-transform:uppercase; letter-spacing:.06em; margin-bottom:.6rem;
}
.dm-panel-row {
  display:flex; justify-content:space-between; align-items:center;
  font-size:12.5px; color:#4A5568; margin-bottom:.35rem;
}
.dm-panel-row:last-child { margin-bottom:0; }
.dm-panel-row strong { color:#1C1714; font-weight:700; }

.dm-panel-actions {
  padding:1rem 1.1rem;
  display:flex; flex-direction:column; gap:.45rem;
  margin-top:auto;
}
.dm-panel-btn {
  display:flex; align-items:center; justify-content:center; gap:.5rem;
  height:38px; border-radius:9px; font-size:13px; font-weight:700;
  border:1.5px solid transparent; cursor:pointer; width:100%;
  transition:all 180ms;
}
.dm-panel-btn-primary  { background:#7B1C1C; color:#fff; border-color:#5A1212; }
.dm-panel-btn-primary:hover  { background:#5A1212; }
.dm-panel-btn-outline  { background:#fff; color:#4A5568; border-color:#DDD5C4; }
.dm-panel-btn-outline:hover  { border-color:#7B1C1C; color:#7B1C1C; }
.dm-panel-btn-share    { background:#fff; color:#2B6CB0; border-color:#DDD5C4; }
.dm-panel-btn-share:hover    { background:#2B6CB0; color:#fff; }
.dm-panel-btn-danger   { background:#fff; color:#C05621; border-color:#DDD5C4; }
.dm-panel-btn-danger:hover   { background:#C05621; color:#fff; }

/* Share mini-list di panel */
.dm-panel-share-item {
  display:flex; align-items:center; gap:.5rem;
  font-size:12px; color:#4A5568; padding:.3rem 0;
  border-bottom:1px solid #F5F0E8;
}
.dm-panel-share-item:last-child { border-bottom:none; }
.dm-panel-share-avatar {
  width:24px; height:24px; border-radius:50%;
  background:#7B1C1C; color:#fff;
  display:flex; align-items:center; justify-content:center;
  font-size:10px; font-weight:800; flex-shrink:0;
}
.dm-preview-spinner {
  display:flex; align-items:center; justify-content:center;
  height:100%; color:#fff; font-size:14px;
}

@media(max-width:640px) {
  .dm-preview-panel { display:none; }
  .dm-preview-content iframe { width:100vw; }
}
</style>

<div class="dm-preview-overlay" id="modal-preview">
  <!-- Area kiri: konten -->
  <div class="dm-preview-main" id="preview-main">
    <button class="dm-preview-close-btn" onclick="closePreview()" title="Tutup">&times;</button>
    <div class="dm-preview-content" id="preview-content">
      <div class="dm-preview-spinner">Memuat pratinjau...</div>
    </div>
  </div>

  <!-- Area kanan: panel info -->
  <aside class="dm-preview-panel" id="preview-panel">
    <div class="dm-preview-panel-header">
      <div class="dm-panel-file-icon" id="panel-icon">?</div>
      <div class="dm-panel-title">
        <div class="dm-panel-filename" id="panel-filename" title="Klik untuk rename">-</div>
        <input type="text" class="dm-panel-filename-input" id="panel-filename-input">
        <div class="dm-panel-meta" id="panel-meta">-</div>
      </div>
    </div>

    <div class="dm-panel-section" id="panel-details">
      <div class="dm-panel-section-title">Detail</div>
      <div class="dm-panel-row"><span>Tipe</span>      <strong id="pd-type">-</strong></div>
      <div class="dm-panel-row"><span>Ukuran</span>   <strong id="pd-size">-</strong></div>
      <div class="dm-panel-row"><span>Diupload</span> <strong id="pd-uploader">-</strong></div>
      <div class="dm-panel-row"><span>Tanggal</span>  <strong id="pd-date">-</strong></div>
    </div>

    <div class="dm-panel-section" id="panel-shares-section">
      <div class="dm-panel-section-title">Dibagikan ke</div>
      <div id="panel-shares-list"><span style="font-size:12px;color:#A89E90">Belum dibagikan.</span></div>
    </div>

    <div class="dm-panel-actions" id="panel-actions">
      <!-- diisi JS -->
    </div>
  </aside>
</div>

<script>
(function(){
  const BASE = '<?= rtrim(BASE_URL, '/') ?>';
  let currentFileId = null;
  let currentFile   = null;

  /* ── Buka preview ── */
  window.openPreview = function(fileId) {
    currentFileId = fileId;
    document.getElementById('preview-content').innerHTML =
      '<div class="dm-preview-spinner">Memuat pratinjau...</div>';
    document.getElementById('preview-panel').innerHTML =
      '<div style="padding:1.5rem;color:#A89E90;font-size:13px">Memuat info...</div>';
    document.getElementById('modal-preview').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadPreviewContent(fileId);
    loadFileInfo(fileId);
  };

  window.closePreview = function() {
    document.getElementById('modal-preview').classList.remove('open');
    document.body.style.overflow = '';
    // stop video/audio
    document.querySelectorAll('#preview-content video, #preview-content audio').forEach(m => m.pause());
    currentFileId = null; currentFile = null;
  };

  /* ── Load konten ── */
  async function loadPreviewContent(fileId) {
    const box = document.getElementById('preview-content');
    try {
      // Cek apakah bisa preview dulu via info
      const info = await (await fetch(BASE+'/api/dokumen/'+fileId+'/info')).json();
      if (!info.success) { box.innerHTML = errBox('Tidak dapat memuat file.'); return; }
      const f = info.file;
      const url = BASE+'/api/dokumen/'+fileId+'/preview';

      if (!f.previewable) {
        box.innerHTML = '<div class="dm-preview-nopreview">'
          +'<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>'
          +'<span>'+escHtml(f.original_name)+'</span>'
          +'<p>Tipe ini tidak dapat dipratinjau di browser.</p>'
          +(f.can_download ? '<a href="'+BASE+'/dokumen/'+fileId+'/download" class="dm-panel-btn dm-panel-btn-primary" style="text-decoration:none;margin-top:.5rem;width:auto;padding:0 1.5rem">Download File</a>' : '')
          +'</div>';
        return;
      }

      if (f.mime_type.startsWith('image/')) {
        box.innerHTML = '<img src="'+url+'" alt="'+escHtml(f.original_name)+'" loading="lazy">';
      } else if (f.mime_type.startsWith('video/')) {
        box.innerHTML = '<video controls autoplay><source src="'+url+'" type="'+escHtml(f.mime_type)+'">Browser tidak mendukung video.</video>';
      } else if (f.mime_type.startsWith('audio/')) {
        box.innerHTML = '<audio controls autoplay style="width:360px"><source src="'+url+'" type="'+escHtml(f.mime_type)+'">Browser tidak mendukung audio.</audio>';
      } else if (f.mime_type === 'application/pdf') {
        box.innerHTML = '<iframe src="'+url+'#toolbar=1&navpanes=0" title="'+escHtml(f.original_name)+'"></iframe>';
      } else if (f.mime_type === 'text/plain' || f.mime_type === 'text/csv') {
        const resp = await fetch(url);
        const text = await resp.text();
        box.innerHTML = '<pre>'+escHtml(text.slice(0,50000))+'</pre>';
      } else {
        box.innerHTML = errBox('Tipe file tidak dikenali.');
      }
    } catch(e) {
      box.innerHTML = errBox('Gagal memuat pratinjau.');
    }
  }

  function errBox(msg) {
    return '<div class="dm-preview-nopreview"><p style="color:#fca5a5">'+escHtml(msg)+'</p></div>';
  }

  /* ── Load info panel ── */
  async function loadFileInfo(fileId) {
    const panel = document.getElementById('preview-panel');
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/info')).json();
      if (!data.success) { panel.innerHTML = '<div style="padding:1rem;color:#C05621">Gagal memuat info.</div>'; return; }
      currentFile = data.file;
      renderPanel(data.file, data.shares);
    } catch(e) {
      panel.innerHTML = '<div style="padding:1rem;color:#C05621">Gagal koneksi.</div>';
    }
  }

  function renderPanel(f, shares) {
    // icon & header
    document.getElementById('preview-panel').innerHTML = '';
    document.getElementById('preview-panel').appendChild(buildPanelDOM(f, shares));

    // rename click
    const nameEl  = document.getElementById('panel-filename');
    const inputEl = document.getElementById('panel-filename-input');
    if (f.can_share || f.can_delete) { // hanya owner/admin boleh rename
      nameEl.addEventListener('click', () => {
        nameEl.style.display  = 'none';
        inputEl.style.display = 'block';
        inputEl.value = nameEl.textContent;
        inputEl.focus(); inputEl.select();
      });
      inputEl.addEventListener('blur',    () => commitRename(f.id, nameEl, inputEl));
      inputEl.addEventListener('keydown', e => {
        if (e.key==='Enter')  { e.preventDefault(); inputEl.blur(); }
        if (e.key==='Escape') { inputEl.style.display='none'; nameEl.style.display=''; }
      });
    }
  }

  function buildPanelDOM(f, shares) {
    const frag = document.createDocumentFragment();

    // header
    const hdr = document.createElement('div');
    hdr.className = 'dm-preview-panel-header';
    hdr.innerHTML =
      '<div class="dm-panel-file-icon" style="background:'+escHtml(f.mime_color)+'">'+escHtml(f.mime_label)+'</div>'
      +'<div class="dm-panel-title">'
      +'<div class="dm-panel-filename" id="panel-filename" title="'+(f.can_share||f.can_delete?'Klik untuk rename':'')+'">'+escHtml(f.original_name)+'</div>'
      +'<input type="text" class="dm-panel-filename-input" id="panel-filename-input">'
      +'<div class="dm-panel-meta" id="panel-meta">'+escHtml(f.size_fmt)+'</div>'
      +'</div>';
    frag.appendChild(hdr);

    // detail
    const det = document.createElement('div');
    det.className = 'dm-panel-section';
    const tgl = f.created_at ? new Date(f.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'}) : '-';
    det.innerHTML =
      '<div class="dm-panel-section-title">Detail</div>'
      +'<div class="dm-panel-row"><span>Tipe</span>      <strong>'+escHtml(f.mime_label)+'</strong></div>'
      +'<div class="dm-panel-row"><span>Ukuran</span>   <strong>'+escHtml(f.size_fmt)+'</strong></div>'
      +'<div class="dm-panel-row"><span>Diupload</span> <strong>'+escHtml(f.uploader_name||'-')+'</strong></div>'
      +'<div class="dm-panel-row"><span>Tanggal</span>  <strong>'+tgl+'</strong></div>';
    frag.appendChild(det);

    // share list
    const sh = document.createElement('div');
    sh.className = 'dm-panel-section';
    let shareHtml = '<div class="dm-panel-section-title">Dibagikan ke</div>';
    if (!shares || !shares.length) {
      shareHtml += '<span style="font-size:12px;color:#A89E90">Belum dibagikan.</span>';
    } else {
      shareHtml += shares.map(s =>
        '<div class="dm-panel-share-item">'
        + '<div class="dm-panel-share-avatar">'+escHtml(s.user_name.charAt(0).toUpperCase())+'</div>'
        + '<span style="flex:1">'+escHtml(s.user_name)+'</span>'
        + '<span style="font-size:11px;color:#A89E90;background:#F5F0E8;border-radius:4px;padding:.1em .45em">'
        + (s.permission==='download'?'DL':'View')+'</span>'
        +'</div>'
      ).join('');
    }
    sh.innerHTML = shareHtml;
    frag.appendChild(sh);

    // actions
    const act = document.createElement('div');
    act.className = 'dm-panel-actions';
    if (f.can_download) {
      act.innerHTML += '<a href="'+BASE+'/dokumen/'+f.id+'/download" class="dm-panel-btn dm-panel-btn-primary" style="text-decoration:none">'
        +'<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download'
        +'</a>';
    }
    if (f.can_share) {
      act.innerHTML += '<button class="dm-panel-btn dm-panel-btn-share" onclick="closePreview();openShareModal('+f.id+',\''+f.original_name.replace(/'/g,"\\'")+'\')" >'
        +'<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg> Bagikan'
        +'</button>';
    }
    if (f.can_delete) {
      act.innerHTML += '<button class="dm-panel-btn dm-panel-btn-danger" onclick="closePreview();deleteFile('+f.id+',\''+f.original_name.replace(/'/g,"\\'")+'\')" >'
        +'<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg> Hapus'
        +'</button>';
    }
    frag.appendChild(act);
    return frag;
  }

  /* ── Rename inline dari panel ── */
  async function commitRename(fileId, nameEl, inputEl) {
    const newName = inputEl.value.trim();
    inputEl.style.display = 'none';
    nameEl.style.display  = '';
    if (!newName || newName === nameEl.textContent) return;
    const fd = new FormData(); fd.append('name', newName);
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/rename',{method:'POST',body:fd})).json();
      if (data.success) {
        nameEl.textContent = newName;
        // update tabel utama jika baris ada
        const rowName = document.querySelector('#file-row-'+fileId+' .dm-file-name');
        if (rowName) rowName.textContent = newName;
      } else {
        alert(data.message);
      }
    } catch(e) { alert('Gagal koneksi.'); }
  }

  /* ── Keyboard ESC ── */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closePreview();
  });
  /* ── Click overlay utk tutup ── */
  document.getElementById('modal-preview').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-preview') ||
        e.target === document.getElementById('preview-main')) closePreview();
  });

  function escHtml(s) { const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }
})();
</script>
