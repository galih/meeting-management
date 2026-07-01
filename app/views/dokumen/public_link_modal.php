<?php
/**
 * Partial view: Public Share Link Modal (Fase 6)
 */
?>
<style>
.dm-pl-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.5);
  z-index:1400; display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none; transition:opacity 200ms;
}
.dm-pl-overlay.open { opacity:1; pointer-events:auto; }
.dm-pl-modal {
  background:#fff; border-radius:14px; width:100%; max-width:520px;
  max-height:88vh; display:flex; flex-direction:column;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
  transform:translateY(16px) scale(.97); transition:transform 200ms;
}
.dm-pl-overlay.open .dm-pl-modal { transform:none; }
.dm-pl-header {
  padding:1.1rem 1.25rem .9rem; border-bottom:1px solid #F0EBE2;
  display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0;
}
.dm-pl-title { font-size:15px; font-weight:800; color:#1C1714; }
.dm-pl-sub   { font-size:12px; color:#A89E90; margin-top:.2rem; }
.dm-pl-close {
  background:none; border:none; cursor:pointer; color:#A89E90;
  width:30px; height:30px; border-radius:7px;
  display:flex; align-items:center; justify-content:center; font-size:18px;
}
.dm-pl-close:hover { background:#F5F0E8; color:#1C1714; }
.dm-pl-body { overflow-y:auto; flex:1; padding:1rem 1.25rem; }

/* Form */
.dm-pl-form { display:flex; flex-direction:column; gap:.75rem; margin-bottom:1.25rem; }
.dm-pl-label { font-size:11.5px; font-weight:700; color:#6B6055; margin-bottom:.25rem; display:block; }
.dm-pl-select, .dm-pl-input {
  width:100%; border:1.5px solid #DDD5C4; border-radius:9px;
  padding:.45rem .75rem; font-size:13px; color:#1C1714; background:#fff;
  outline:none; transition:border-color 150ms;
}
.dm-pl-select:focus, .dm-pl-input:focus { border-color:#7B1C1C; }
.dm-pl-row { display:grid; grid-template-columns:1fr 1fr; gap:.65rem; }
.dm-pl-btn {
  height:38px; padding:0 1.1rem; border-radius:9px;
  background:#7B1C1C; color:#fff; border:none;
  font-size:13px; font-weight:700; cursor:pointer;
  transition:background 160ms; width:100%;
}
.dm-pl-btn:hover { background:#5A1212; }
.dm-pl-msg { font-size:12.5px; margin:.25rem 0; }
.dm-pl-msg-ok  { color:#27A155; }
.dm-pl-msg-err { color:#C05621; }

/* Daftar link */
.dm-pl-link-item {
  border:1px solid #E8E2D9; border-radius:10px;
  padding:.75rem .9rem; margin-bottom:.55rem; background:#fff;
}
.dm-pl-link-item.expired { opacity:.55; }
.dm-pl-link-url {
  display:flex; align-items:center; gap:.5rem;
  background:#F9F7F4; border-radius:7px; padding:.4rem .65rem;
  margin-bottom:.55rem;
}
.dm-pl-link-url span {
  flex:1; font-size:12px; color:#4A5568; font-family:monospace;
  overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.dm-pl-copy-btn {
  height:28px; padding:0 .7rem; border-radius:6px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:12px; font-weight:700; color:#4A5568;
  cursor:pointer; transition:all 150ms; flex-shrink:0;
}
.dm-pl-copy-btn:hover { border-color:#7B1C1C; color:#7B1C1C; }
.dm-pl-link-meta {
  display:flex; flex-wrap:wrap; gap:.4rem; margin-bottom:.5rem;
}
.dm-pl-badge {
  font-size:10.5px; font-weight:800; border-radius:5px;
  padding:.15em .55em;
}
.dm-pl-badge-view     { background:#EBF8FF; color:#2B6CB0; }
.dm-pl-badge-download { background:#F0FFF4; color:#276749; }
.dm-pl-badge-pass     { background:#FAF5FF; color:#6B46C1; }
.dm-pl-badge-exp      { background:#FFF5F5; color:#C05621; }
.dm-pl-badge-ok       { background:#F0FFF4; color:#276749; }
.dm-pl-badge-count    { background:#F9F7F4; color:#6B6055; }
.dm-pl-del-btn {
  height:28px; padding:0 .7rem; border-radius:6px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:12px; font-weight:700; color:#C05621;
  cursor:pointer; transition:all 150ms;
}
.dm-pl-del-btn:hover { background:#C05621; color:#fff; border-color:#C05621; }
.dm-pl-empty { text-align:center; padding:1.5rem; color:#A89E90; font-size:13px; }
</style>

<div class="dm-pl-overlay" id="modal-public-link">
  <div class="dm-pl-modal">
    <div class="dm-pl-header">
      <div>
        <div class="dm-pl-title">Link Publik</div>
        <div class="dm-pl-sub" id="pl-filename"></div>
      </div>
      <button class="dm-pl-close" onclick="closePublicLinkModal()">&times;</button>
    </div>
    <div class="dm-pl-body">

      <!-- Form buat link baru -->
      <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.75rem">Buat Link Baru</div>
      <div class="dm-pl-form">
        <div>
          <label class="dm-pl-label">Izin Akses</label>
          <select id="pl-permission" class="dm-pl-select">
            <option value="view">Hanya Lihat (View only)</option>
            <option value="download">Lihat + Download</option>
          </select>
        </div>
        <div class="dm-pl-row">
          <div>
            <label class="dm-pl-label">Password (opsional)</label>
            <input type="password" id="pl-password" class="dm-pl-input" placeholder="Kosongkan = tanpa password">
          </div>
          <div>
            <label class="dm-pl-label">Kadaluarsa (opsional)</label>
            <input type="datetime-local" id="pl-expires" class="dm-pl-input">
          </div>
        </div>
        <div>
          <label class="dm-pl-label">Maks. Download (opsional, 0 = tak terbatas)</label>
          <input type="number" id="pl-maxdl" class="dm-pl-input" placeholder="Kosongkan = tidak dibatasi" min="0">
        </div>
        <button class="dm-pl-btn" onclick="createPublicLink()">Buat Link Publik</button>
        <div class="dm-pl-msg" id="pl-msg"></div>
      </div>

      <hr style="border:none;border-top:1px solid #F0EBE2;margin:.25rem 0 1rem">

      <!-- Daftar link aktif -->
      <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">Link Aktif</div>
      <div id="pl-list"><div class="dm-pl-empty">Memuat...</div></div>
    </div>
  </div>
</div>

<script>
(function(){
  const BASE = '<?= rtrim(BASE_URL, '/') ?>';
  let plFileId   = null;
  let plFileName = '';

  window.openPublicLinkModal = function(fileId, fileName) {
    plFileId   = fileId;
    plFileName = fileName;
    document.getElementById('pl-filename').textContent = fileName;
    document.getElementById('pl-msg').textContent = '';
    document.getElementById('pl-password').value = '';
    document.getElementById('pl-expires').value  = '';
    document.getElementById('pl-maxdl').value    = '';
    document.getElementById('modal-public-link').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadLinks(fileId);
  };

  window.closePublicLinkModal = function() {
    document.getElementById('modal-public-link').classList.remove('open');
    document.body.style.overflow = '';
    plFileId = null;
  };

  async function loadLinks(fileId) {
    document.getElementById('pl-list').innerHTML = '<div class="dm-pl-empty">Memuat...</div>';
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+fileId+'/public-links')).json();
      if (data.success) renderLinks(data.links);
      else document.getElementById('pl-list').innerHTML = '<div class="dm-pl-empty">Gagal memuat.</div>';
    } catch(e) {
      document.getElementById('pl-list').innerHTML = '<div class="dm-pl-empty">Gagal koneksi.</div>';
    }
  }

  function renderLinks(links) {
    const wrap = document.getElementById('pl-list');
    if (!links || !links.length) { wrap.innerHTML = '<div class="dm-pl-empty">Belum ada link publik.</div>'; return; }
    wrap.innerHTML = links.map(l => {
      const expired = !l.is_valid;
      const dlCount = l.max_downloads ? l.download_count+'/'+l.max_downloads+' DL' : l.download_count+' DL';
      const expLabel= l.expires_at ? new Date(l.expires_at).toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric',hour:'2-digit',minute:'2-digit'}) : 'Tidak kadaluarsa';
      return '<div class="dm-pl-link-item'+(expired?' expired':'')+'">'
        +'<div class="dm-pl-link-url">'
        +'<span id="url-'+l.id+'">'+esc(l.url)+'</span>'
        +'<button class="dm-pl-copy-btn" onclick="copyUrl(\''+l.id+'\',\''+esc(l.url)+'\')" >Salin</button>'
        +'</div>'
        +'<div class="dm-pl-link-meta">'
        +(l.permission==='download'
          ? '<span class="dm-pl-badge dm-pl-badge-download">&#8659; Download</span>'
          : '<span class="dm-pl-badge dm-pl-badge-view">&#128065; View only</span>')
        +(l.has_password ? '<span class="dm-pl-badge dm-pl-badge-pass">&#128274; Password</span>' : '')
        +'<span class="dm-pl-badge '+(expired?'dm-pl-badge-exp':'dm-pl-badge-ok')+'">'+(expired?'Kadaluarsa':expLabel)+'</span>'
        +'<span class="dm-pl-badge dm-pl-badge-count">'+dlCount+'</span>'
        +'</div>'
        +'<button class="dm-pl-del-btn" onclick="deleteLink('+l.id+')">Hapus Link</button>'
        +'</div>';
    }).join('');
  }

  window.createPublicLink = async function() {
    const perm   = document.getElementById('pl-permission').value;
    const pass   = document.getElementById('pl-password').value.trim();
    const exp    = document.getElementById('pl-expires').value;
    const maxdl  = document.getElementById('pl-maxdl').value;
    const fd = new FormData();
    fd.append('permission', perm);
    if (pass)  fd.append('password',      pass);
    if (exp)   fd.append('expires_at',    exp);
    if (maxdl) fd.append('max_downloads', maxdl);
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+plFileId+'/public-links',{method:'POST',body:fd})).json();
      setMsg(data.message, data.success);
      if (data.success) {
        document.getElementById('pl-password').value = '';
        document.getElementById('pl-expires').value  = '';
        document.getElementById('pl-maxdl').value    = '';
        loadLinks(plFileId);
      }
    } catch(e) { setMsg('Gagal koneksi.', false); }
  };

  window.deleteLink = async function(linkId) {
    if (!confirm('Hapus link ini? Siapapun yang punya link tidak bisa lagi mengaksesnya.')) return;
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+plFileId+'/public-links/'+linkId+'/delete',{method:'POST'})).json();
      setMsg(data.message, data.success);
      if (data.success) renderLinks(data.links);
    } catch(e) { setMsg('Gagal koneksi.', false); }
  };

  window.copyUrl = function(id, url) {
    navigator.clipboard.writeText(url).then(() => {
      const btn = document.querySelector('button[onclick*="copyUrl(\''+id);
      if (!btn) return;
      const orig = btn.textContent;
      btn.textContent = '\u2714 Disalin!';
      btn.style.color = '#27A155';
      setTimeout(()=>{ btn.textContent=orig; btn.style.color=''; }, 1800);
    });
  };

  function setMsg(msg, ok) {
    const el = document.getElementById('pl-msg');
    el.className = 'dm-pl-msg '+(ok?'dm-pl-msg-ok':'dm-pl-msg-err');
    el.textContent = msg;
  }
  function esc(s) { const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }

  document.getElementById('modal-public-link').addEventListener('click', e => {
    if (e.target===document.getElementById('modal-public-link')) closePublicLinkModal();
  });
  document.addEventListener('keydown', e => {
    if (e.key==='Escape' && document.getElementById('modal-public-link').classList.contains('open')) closePublicLinkModal();
  });
})();
</script>
