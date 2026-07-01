<?php
/**
 * Partial view: Version History Modal (Fase 4)
 * Di-include dari dokumen/index.php
 */
?>
<!-- ======================== MODAL: VERSION HISTORY ======================== -->
<style>
.dm-vh-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.5);
  z-index:1200; display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none; transition:opacity 200ms;
}
.dm-vh-overlay.open { opacity:1; pointer-events:auto; }
.dm-vh-modal {
  background:#fff; border-radius:14px;
  width:100%; max-width:600px; max-height:88vh;
  display:flex; flex-direction:column;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
  transform:translateY(20px) scale(.97); transition:transform 200ms;
}
.dm-vh-overlay.open .dm-vh-modal { transform:none; }
.dm-vh-header {
  padding:1.1rem 1.25rem .9rem;
  border-bottom:1px solid #F0EBE2;
  display:flex; align-items:flex-start; justify-content:space-between;
  flex-shrink:0;
}
.dm-vh-title { font-size:15px; font-weight:800; color:#1C1714; }
.dm-vh-sub   { font-size:12px; color:#A89E90; margin-top:.2rem; }
.dm-vh-close {
  background:none; border:none; cursor:pointer; color:#A89E90;
  width:30px; height:30px; border-radius:7px;
  display:flex; align-items:center; justify-content:center; font-size:18px;
}
.dm-vh-close:hover { background:#F5F0E8; color:#1C1714; }

/* Upload revisi bar */
.dm-vh-upload-bar {
  padding:.85rem 1.25rem;
  border-bottom:1px solid #F0EBE2;
  display:flex; align-items:center; gap:.65rem; flex-shrink:0;
  background:#F9F7F4;
}
.dm-vh-upload-label {
  flex:1; display:flex; align-items:center; gap:.5rem;
  border:1.5px dashed #DDD5C4; border-radius:8px;
  padding:.5rem .9rem; cursor:pointer; background:#fff;
  transition:border-color 180ms;
  font-size:13px; color:#6B6055;
}
.dm-vh-upload-label:hover { border-color:#7B1C1C; color:#7B1C1C; }
.dm-vh-upload-label svg { flex-shrink:0; }
.dm-vh-upload-label span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.dm-vh-upload-btn {
  height:38px; padding:0 1.1rem; border-radius:8px;
  background:#7B1C1C; color:#fff; border:none;
  font-size:13px; font-weight:700; cursor:pointer;
  display:flex; align-items:center; gap:.4rem;
  transition:background 180ms; white-space:nowrap; flex-shrink:0;
}
.dm-vh-upload-btn:hover    { background:#5A1212; }
.dm-vh-upload-btn:disabled { background:#DDD5C4; cursor:not-allowed; }
.dm-vh-upload-progress { height:3px; background:#E8E2D9; border-radius:99px; margin:.45rem 1.25rem 0; display:none; }
.dm-vh-upload-progress-bar { height:100%; background:#7B1C1C; border-radius:99px; width:0%; transition:width 120ms; }

/* Version list */
.dm-vh-list-wrap { overflow-y:auto; flex:1; padding:.75rem 1.25rem; }
.dm-vh-current-label {
  font-size:10.5px; font-weight:800; color:#A89E90;
  text-transform:uppercase; letter-spacing:.06em;
  margin-bottom:.5rem; margin-top:.25rem;
}
.dm-vh-item {
  display:flex; align-items:center; gap:.75rem;
  padding:.75rem .9rem;
  border:1px solid #E8E2D9; border-radius:10px;
  margin-bottom:.55rem; background:#fff;
  transition:border-color 180ms;
}
.dm-vh-item:hover { border-color:#DDD5C4; }
.dm-vh-item.is-current { border-color:#7B1C1C; background:#FDF9F6; }
.dm-vh-icon {
  width:38px; height:38px; border-radius:8px;
  display:flex; align-items:center; justify-content:center;
  font-size:10px; font-weight:800; color:#fff; flex-shrink:0;
}
.dm-vh-info { flex:1; min-width:0; }
.dm-vh-name {
  font-size:13px; font-weight:700; color:#1C1714;
  overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.dm-vh-meta { font-size:11.5px; color:#A89E90; margin-top:.15rem; }
.dm-vh-badge {
  font-size:10px; font-weight:800; border-radius:5px;
  padding:.15em .55em; background:#7B1C1C; color:#fff;
  flex-shrink:0;
}
.dm-vh-badge-old { background:#E8E2D9; color:#6B6055; }
.dm-vh-dl-btn {
  height:30px; padding:0 .75rem; border-radius:7px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:12px; font-weight:700; color:#6B6055;
  cursor:pointer; display:flex; align-items:center; gap:.35rem;
  transition:all 160ms; flex-shrink:0;
}
.dm-vh-dl-btn:hover { border-color:#7B1C1C; color:#7B1C1C; }
.dm-vh-empty {
  text-align:center; padding:2.5rem 1rem;
  color:#A89E90; font-size:13.5px;
}
.dm-vh-msg { font-size:12.5px; margin:.5rem 1.25rem; }
.dm-vh-msg-ok  { color:#27A155; }
.dm-vh-msg-err { color:#C05621; }

/* Restore button */
.dm-vh-restore-btn {
  height:30px; padding:0 .75rem; border-radius:7px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:12px; font-weight:700; color:#2B6CB0;
  cursor:pointer; display:flex; align-items:center; gap:.35rem;
  transition:all 160ms; flex-shrink:0; margin-right:.3rem;
}
.dm-vh-restore-btn:hover { border-color:#2B6CB0; background:#EBF8FF; }
</style>

<div class="dm-vh-overlay" id="modal-versions">
  <div class="dm-vh-modal">
    <div class="dm-vh-header">
      <div>
        <div class="dm-vh-title">Riwayat Versi</div>
        <div class="dm-vh-sub" id="vh-filename"></div>
      </div>
      <button class="dm-vh-close" onclick="closeVersionModal()">&times;</button>
    </div>

    <!-- Upload revisi (hanya pemilik / admin) -->
    <div class="dm-vh-upload-bar" id="vh-upload-bar" style="display:none">
      <label class="dm-vh-upload-label" for="vh-file-input">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        <span id="vh-file-label">Upload versi baru&hellip;</span>
      </label>
      <input type="file" id="vh-file-input" style="display:none">
      <button class="dm-vh-upload-btn" id="vh-upload-btn" disabled>
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        Upload Revisi
      </button>
    </div>
    <div class="dm-vh-upload-progress" id="vh-progress">
      <div class="dm-vh-upload-progress-bar" id="vh-progress-bar"></div>
    </div>
    <div class="dm-vh-msg" id="vh-msg"></div>

    <!-- Daftar versi -->
    <div class="dm-vh-list-wrap" id="vh-list">
      <div class="dm-vh-empty">Memuat riwayat versi&hellip;</div>
    </div>
  </div>
</div>

<script>
(function(){
  const BASE = '<?= rtrim(BASE_URL, '/') ?>';
  let vhFileId   = null;
  let vhCanWrite = false;
  let vhFileName = '';

  /* ── Buka modal ── */
  window.openVersionModal = function(fileId, fileName, canWrite) {
    vhFileId   = fileId;
    vhFileName = fileName;
    vhCanWrite = !!canWrite;
    document.getElementById('vh-filename').textContent = fileName;
    document.getElementById('vh-msg').textContent = '';
    document.getElementById('vh-upload-bar').style.display = canWrite ? 'flex' : 'none';
    document.getElementById('vh-file-input').value = '';
    document.getElementById('vh-file-label').textContent = 'Upload versi baru\u2026';
    document.getElementById('vh-upload-btn').disabled = true;
    document.getElementById('vh-progress').style.display = 'none';
    document.getElementById('modal-versions').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadVersions(fileId);
  };

  window.closeVersionModal = function() {
    document.getElementById('modal-versions').classList.remove('open');
    document.body.style.overflow = '';
    vhFileId = null;
  };

  /* ── Pilih file untuk upload revisi ── */
  document.getElementById('vh-file-input').addEventListener('change', function() {
    const f = this.files[0];
    if (f) {
      document.getElementById('vh-file-label').textContent = f.name;
      document.getElementById('vh-upload-btn').disabled = false;
    }
  });

  /* ── Upload revisi ── */
  document.getElementById('vh-upload-btn').addEventListener('click', async function() {
    const input = document.getElementById('vh-file-input');
    if (!input.files[0] || !vhFileId) return;
    const btn = this;
    btn.disabled = true;
    const progWrap = document.getElementById('vh-progress');
    const progBar  = document.getElementById('vh-progress-bar');
    progWrap.style.display = 'block';
    progBar.style.width = '20%';
    const fd = new FormData();
    fd.append('file', input.files[0]);
    try {
      const r = await fetch(BASE + '/api/dokumen/' + vhFileId + '/versions/upload', { method:'POST', body:fd });
      progBar.style.width = '90%';
      const data = await r.json();
      progBar.style.width = '100%';
      setTimeout(() => { progWrap.style.display='none'; progBar.style.width='0%'; }, 400);
      if (data.success) {
        setVhMsg(data.message, true);
        input.value = '';
        document.getElementById('vh-file-label').textContent = 'Upload versi baru\u2026';
        renderVersions(data.versions, data.file);
        // Refresh nama di tabel utama jika berbeda
        if (data.file) {
          const rowName = document.querySelector('#file-row-' + vhFileId + ' .dm-file-name');
          if (rowName) rowName.textContent = data.file.original_name;
        }
      } else {
        setVhMsg(data.message, false);
        btn.disabled = false;
      }
    } catch(e) {
      setVhMsg('Gagal koneksi.', false);
      btn.disabled = false;
    }
  });

  /* ── Load daftar versi ── */
  async function loadVersions(fileId) {
    document.getElementById('vh-list').innerHTML = '<div class="dm-vh-empty">Memuat&hellip;</div>';
    try {
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/versions')).json();
      if (!data.success) { document.getElementById('vh-list').innerHTML = '<div class="dm-vh-empty">Gagal memuat riwayat.</div>'; return; }
      // Ambil info file current untuk tampilkan sebagai versi aktif
      const info = await (await fetch(BASE + '/api/dokumen/' + fileId + '/info')).json();
      renderVersions(data.versions, info.success ? info.file : null);
    } catch(e) {
      document.getElementById('vh-list').innerHTML = '<div class="dm-vh-empty">Gagal koneksi.</div>';
    }
  }

  function renderVersions(versions, currentFile) {
    const wrap = document.getElementById('vh-list');
    let html = '';

    // Versi aktif (file saat ini)
    if (currentFile) {
      const tgl = currentFile.created_at
        ? new Date(currentFile.updated_at || currentFile.created_at)
            .toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})
        : '-';
      const sz = currentFile.size_fmt || '-';
      html += '<div class="dm-vh-current-label">Versi Aktif</div>'
        + '<div class="dm-vh-item is-current">'
        + '<div class="dm-vh-icon" style="background:' + esc(currentFile.mime_color||'#A89E90') + '">' + esc(currentFile.mime_label||'FILE') + '</div>'
        + '<div class="dm-vh-info">'
        + '<div class="dm-vh-name">' + esc(currentFile.original_name) + '</div>'
        + '<div class="dm-vh-meta">' + sz + ' &middot; ' + tgl + '</div>'
        + '</div>'
        + '<span class="dm-vh-badge">Aktif</span>'
        + '<a href="' + BASE + '/dokumen/' + currentFile.id + '/download" class="dm-vh-dl-btn" title="Download versi aktif">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>'
        + 'Download</a>'
        + '</div>';
    }

    // Versi lama dari tabel dokumen_versions
    if (!versions || !versions.length) {
      if (!currentFile) html += '<div class="dm-vh-empty">Belum ada riwayat versi.</div>';
      else html += '<div style="font-size:12px;color:#A89E90;text-align:center;padding:.75rem 0">Belum ada versi sebelumnya.</div>';
    } else {
      html += '<div class="dm-vh-current-label" style="margin-top:1rem">Versi Sebelumnya (' + versions.length + ')</div>';
      versions.forEach(v => {
        const tgl = new Date(v.created_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'});
        html += '<div class="dm-vh-item">'
          + '<div class="dm-vh-icon" style="background:' + esc(v.mime_color||'#A89E90') + '">' + esc(v.mime_label||'FILE') + '</div>'
          + '<div class="dm-vh-info">'
          + '<div class="dm-vh-name">' + esc(v.original_name) + '</div>'
          + '<div class="dm-vh-meta">v' + v.version_no + ' &middot; ' + esc(v.size_fmt) + ' &middot; ' + tgl + ' &middot; ' + esc(v.uploader_name||'-') + '</div>'
          + '</div>'
          + '<span class="dm-vh-badge dm-vh-badge-old">v' + v.version_no + '</span>'
          + (vhCanWrite
              ? '<button class="dm-vh-restore-btn" onclick="restoreVersion(' + vhFileId + ',' + v.id + ')" title="Jadikan versi ini sebagai aktif">'
                + '<svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 .49-3.36"/></svg>'
                + 'Restore</button>'
              : '')
          + '<a href="' + BASE + '/api/dokumen/versions/' + v.id + '/download" class="dm-vh-dl-btn">'
          + '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>'
          + 'Download</a>'
          + '</div>';
      });
    }

    wrap.innerHTML = html;
  }

  /* ── Restore versi lama jadi aktif ── */
  window.restoreVersion = async function(fileId, versionId) {
    if (!confirm('Jadikan versi ini sebagai versi aktif? File aktif saat ini akan disimpan sebagai riwayat.')) return;
    try {
      const fd = new FormData(); fd.append('version_id', versionId);
      const data = await (await fetch(BASE + '/api/dokumen/' + fileId + '/versions/restore', {method:'POST', body:fd})).json();
      if (data.success) {
        setVhMsg(data.message, true);
        renderVersions(data.versions, data.file);
        const rowName = document.querySelector('#file-row-' + fileId + ' .dm-file-name');
        if (rowName && data.file) rowName.textContent = data.file.original_name;
      } else {
        setVhMsg(data.message, false);
      }
    } catch(e) { setVhMsg('Gagal koneksi.', false); }
  };

  function setVhMsg(msg, ok) {
    const el = document.getElementById('vh-msg');
    el.className = 'dm-vh-msg ' + (ok ? 'dm-vh-msg-ok' : 'dm-vh-msg-err');
    el.textContent = msg;
  }
  function esc(s) { const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }

  /* Tutup klik luar / ESC */
  document.getElementById('modal-versions').addEventListener('click', e => {
    if (e.target === document.getElementById('modal-versions')) closeVersionModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && document.getElementById('modal-versions').classList.contains('open')) closeVersionModal();
  });
})();
</script>
