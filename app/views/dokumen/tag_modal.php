<?php
/**
 * Partial view: Tag & Kategori Modal (Fase 5)
 * Di-include dari dokumen/index.php
 */
?>
<style>
/* ===== TAG MANAGER MODAL ===== */
.dm-tm-overlay {
  position:fixed; inset:0; background:rgba(0,0,0,.5);
  z-index:1300; display:flex; align-items:center; justify-content:center;
  opacity:0; pointer-events:none; transition:opacity 200ms;
}
.dm-tm-overlay.open { opacity:1; pointer-events:auto; }
.dm-tm-modal {
  background:#fff; border-radius:14px; width:100%; max-width:640px;
  max-height:90vh; display:flex; flex-direction:column;
  box-shadow:0 24px 64px rgba(0,0,0,.22);
  transform:translateY(16px) scale(.97); transition:transform 200ms;
}
.dm-tm-overlay.open .dm-tm-modal { transform:none; }
.dm-tm-header {
  padding:1.1rem 1.25rem .9rem; border-bottom:1px solid #F0EBE2;
  display:flex; align-items:flex-start; justify-content:space-between; flex-shrink:0;
}
.dm-tm-title { font-size:15px; font-weight:800; color:#1C1714; }
.dm-tm-sub   { font-size:12px; color:#A89E90; margin-top:.2rem; }
.dm-tm-close {
  background:none; border:none; cursor:pointer; color:#A89E90;
  width:30px; height:30px; border-radius:7px;
  display:flex; align-items:center; justify-content:center; font-size:18px;
}
.dm-tm-close:hover { background:#F5F0E8; color:#1C1714; }

/* Tabs */
.dm-tm-tabs {
  display:flex; border-bottom:1px solid #F0EBE2; flex-shrink:0;
  padding:0 1.25rem;
}
.dm-tm-tab {
  padding:.6rem 1rem; font-size:13px; font-weight:700; color:#A89E90;
  border-bottom:2.5px solid transparent; cursor:pointer;
  background:none; border-top:none; border-left:none; border-right:none;
  transition:all 150ms;
}
.dm-tm-tab.active { color:#7B1C1C; border-bottom-color:#7B1C1C; }

.dm-tm-body { overflow-y:auto; flex:1; padding:1rem 1.25rem; }

/* Panel tag pada file */
.dm-file-tag-panel { padding:.75rem 1.25rem; background:#F9F7F4; border-bottom:1px solid #F0EBE2; flex-shrink:0; }
.dm-file-tag-panel-title { font-size:11px; font-weight:800; color:#A89E90; text-transform:uppercase; letter-spacing:.06em; margin-bottom:.5rem; }
.dm-tag-chips { display:flex; flex-wrap:wrap; gap:.35rem; min-height:28px; }
.dm-tag-chip {
  display:inline-flex; align-items:center; gap:.3rem;
  padding:.2em .6em; border-radius:99px;
  font-size:11.5px; font-weight:700; color:#fff;
  cursor:default;
}
.dm-tag-chip-rm {
  cursor:pointer; opacity:.7; line-height:1;
  background:none; border:none; color:inherit; font-size:12px; padding:0;
}
.dm-tag-chip-rm:hover { opacity:1; }
.dm-tag-checkrow {
  display:flex; align-items:center; gap:.65rem;
  padding:.45rem .5rem; border-radius:8px; cursor:pointer;
  transition:background 120ms;
}
.dm-tag-checkrow:hover { background:#F5F0E8; }
.dm-tag-checkrow input[type=checkbox] { width:15px; height:15px; accent-color:#7B1C1C; cursor:pointer; }
.dm-tag-dot { width:12px; height:12px; border-radius:50%; flex-shrink:0; }
.dm-tag-check-name { font-size:13px; color:#1C1714; flex:1; }
.dm-tag-check-kat { font-size:11px; color:#A89E90; }

/* Form buat tag/kategori */
.dm-tm-form { display:flex; flex-direction:column; gap:.7rem; margin-bottom:1.25rem; }
.dm-tm-form-row { display:flex; gap:.6rem; align-items:flex-end; }
.dm-tm-form label { font-size:11.5px; font-weight:700; color:#6B6055; margin-bottom:.25rem; display:block; }
.dm-tm-form input[type=text],
.dm-tm-form select {
  width:100%; border:1.5px solid #DDD5C4; border-radius:8px;
  padding:.42rem .7rem; font-size:13px; color:#1C1714; background:#fff;
  outline:none; transition:border-color 150ms;
}
.dm-tm-form input[type=text]:focus,
.dm-tm-form select:focus { border-color:#7B1C1C; }
.dm-tm-form input[type=color] {
  width:38px; height:36px; border:1.5px solid #DDD5C4; border-radius:8px;
  padding:.1rem .2rem; cursor:pointer; background:#fff;
}
.dm-tm-save-btn {
  height:36px; padding:0 1.1rem; border-radius:8px;
  background:#7B1C1C; color:#fff; border:none;
  font-size:13px; font-weight:700; cursor:pointer;
  white-space:nowrap; transition:background 160ms;
}
.dm-tm-save-btn:hover { background:#5A1212; }
.dm-tm-msg { font-size:12px; margin:.3rem 0; }
.dm-tm-msg-ok  { color:#27A155; }
.dm-tm-msg-err { color:#C05621; }

/* Daftar tag/kategori yang sudah ada */
.dm-tm-list-item {
  display:flex; align-items:center; gap:.6rem;
  padding:.55rem .6rem; border-radius:8px; border:1px solid #E8E2D9;
  margin-bottom:.4rem; background:#fff;
}
.dm-tm-list-name { flex:1; font-size:13px; font-weight:700; color:#1C1714; }
.dm-tm-list-meta { font-size:11.5px; color:#A89E90; }
.dm-tm-list-edit {
  height:28px; padding:0 .65rem; border-radius:6px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:11.5px; font-weight:700; color:#4A5568;
  cursor:pointer; transition:all 150ms;
}
.dm-tm-list-edit:hover { border-color:#7B1C1C; color:#7B1C1C; }
.dm-tm-list-del {
  height:28px; padding:0 .65rem; border-radius:6px;
  border:1.5px solid #DDD5C4; background:#fff;
  font-size:11.5px; font-weight:700; color:#C05621;
  cursor:pointer; transition:all 150ms;
}
.dm-tm-list-del:hover { background:#C05621; color:#fff; border-color:#C05621; }
.dm-tm-empty { text-align:center; padding:1.5rem; color:#A89E90; font-size:13px; }
</style>

<!-- ======================== MODAL: TAG MANAGER ======================== -->
<div class="dm-tm-overlay" id="modal-tag-manager">
  <div class="dm-tm-modal">
    <div class="dm-tm-header">
      <div>
        <div class="dm-tm-title" id="tm-title">Kelola Tag</div>
        <div class="dm-tm-sub"  id="tm-sub"></div>
      </div>
      <button class="dm-tm-close" onclick="closeTagManager()">&times;</button>
    </div>

    <!-- Panel tag file (tampil saat mode=file) -->
    <div class="dm-file-tag-panel" id="tm-file-panel" style="display:none">
      <div class="dm-file-tag-panel-title">Tag aktif pada file</div>
      <div class="dm-tag-chips" id="tm-file-chips"></div>
    </div>

    <!-- Tabs -->
    <div class="dm-tm-tabs" id="tm-tabs">
      <button class="dm-tm-tab active" data-tab="tags"     onclick="tmSwitchTab('tags')">Tag</button>
      <button class="dm-tm-tab"        data-tab="kategoris" onclick="tmSwitchTab('kategoris')">Kategori</button>
    </div>

    <div class="dm-tm-body">

      <!-- ===== TAB: TAGS ===== -->
      <div id="tm-pane-tags">
        <div id="tm-file-tag-select" style="display:none;margin-bottom:.9rem">
          <div class="dm-file-tag-panel-title" style="margin-bottom:.5rem">Centang tag untuk file ini</div>
          <div id="tm-tag-checkboxes"></div>
          <button class="dm-tm-save-btn" style="margin-top:.6rem;width:100%" onclick="syncFileTags()">Simpan Tag</button>
          <div class="dm-tm-msg" id="tm-sync-msg"></div>
        </div>

        <!-- Form buat tag baru -->
        <div id="tm-admin-tag-form" style="display:none">
          <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">Buat Tag Baru</div>
          <div class="dm-tm-form">
            <div class="dm-tm-form-row">
              <div style="flex:1">
                <label>Nama tag</label>
                <input type="text" id="tm-tag-name" placeholder="Mis: Kontrak, Laporan...">
              </div>
              <div>
                <label>Warna</label>
                <input type="color" id="tm-tag-color" value="#2B6CB0">
              </div>
            </div>
            <div>
              <label>Kategori (opsional)</label>
              <select id="tm-tag-kat">
                <option value="">Tanpa kategori</option>
              </select>
            </div>
            <button class="dm-tm-save-btn" onclick="createTag()">Buat Tag</button>
            <div class="dm-tm-msg" id="tm-tag-msg"></div>
          </div>
          <hr style="border:none;border-top:1px solid #F0EBE2;margin:.5rem 0 .85rem">
        </div>

        <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">Semua Tag</div>
        <div id="tm-tag-list"><div class="dm-tm-empty">Memuat...</div></div>
      </div>

      <!-- ===== TAB: KATEGORIS ===== -->
      <div id="tm-pane-kategoris" style="display:none">
        <div id="tm-admin-kat-form" style="display:none">
          <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">Buat Kategori Baru</div>
          <div class="dm-tm-form">
            <div class="dm-tm-form-row">
              <div style="flex:1">
                <label>Nama kategori</label>
                <input type="text" id="tm-kat-name" placeholder="Mis: Keuangan, SDM...">
              </div>
              <div>
                <label>Warna</label>
                <input type="color" id="tm-kat-color" value="#7B1C1C">
              </div>
            </div>
            <button class="dm-tm-save-btn" onclick="createKategori()">Buat Kategori</button>
            <div class="dm-tm-msg" id="tm-kat-msg"></div>
          </div>
          <hr style="border:none;border-top:1px solid #F0EBE2;margin:.5rem 0 .85rem">
        </div>
        <div style="font-size:12px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.06em;margin-bottom:.65rem">Semua Kategori</div>
        <div id="tm-kat-list"><div class="dm-tm-empty">Memuat...</div></div>
      </div>

    </div>
  </div>
</div>

<script>
(function(){
  const BASE = '<?= rtrim(BASE_URL, '/') ?>';
  let tmMode    = 'admin'; // 'admin' | 'file'
  let tmFileId  = null;
  let tmCanEdit = false;
  let tmState   = { tags:[], kategoris:[], fileTags:[] };
  let tmActiveTab = 'tags';
  let isAdmin   = <?= Auth::hasRole('admin') ? 'true' : 'false' ?>;

  /* ======================== BUKA / TUTUP ======================== */
  window.openTagManager = function(fileId, fileName, canEdit) {
    tmMode    = fileId ? 'file' : 'admin';
    tmFileId  = fileId || null;
    tmCanEdit = !!canEdit;
    document.getElementById('tm-title').textContent = fileId ? 'Tag File' : 'Kelola Tag & Kategori';
    document.getElementById('tm-sub').textContent   = fileId ? fileName : 'Buat, edit, dan atur tag untuk semua dokumen';
    document.getElementById('tm-file-panel').style.display   = fileId ? '' : 'none';
    document.getElementById('tm-file-tag-select').style.display = (fileId && canEdit) ? '' : 'none';
    document.getElementById('tm-admin-tag-form').style.display  = isAdmin ? '' : 'none';
    document.getElementById('tm-admin-kat-form').style.display  = isAdmin ? '' : 'none';
    tmSwitchTab('tags');
    document.getElementById('modal-tag-manager').classList.add('open');
    document.body.style.overflow = 'hidden';
    loadAll(fileId);
  };

  window.closeTagManager = function() {
    document.getElementById('modal-tag-manager').classList.remove('open');
    document.body.style.overflow = '';
    tmFileId = null;
  };

  function tmSwitchTab(tab) {
    tmActiveTab = tab;
    document.querySelectorAll('.dm-tm-tab').forEach(t => t.classList.toggle('active', t.dataset.tab === tab));
    document.getElementById('tm-pane-tags').style.display      = tab==='tags'      ? '' : 'none';
    document.getElementById('tm-pane-kategoris').style.display = tab==='kategoris' ? '' : 'none';
  }
  window.tmSwitchTab = tmSwitchTab;

  /* ======================== LOAD DATA ======================== */
  async function loadAll(fileId) {
    try {
      const base = await (await fetch(BASE+'/api/dokumen/tags')).json();
      tmState.tags     = base.tags     || [];
      tmState.kategoris= base.kategoris|| [];
      if (fileId) {
        const ft = await (await fetch(BASE+'/api/dokumen/'+fileId+'/tags')).json();
        tmState.fileTags = ft.tags || [];
      } else {
        tmState.fileTags = [];
      }
      render();
    } catch(e) {
      document.getElementById('tm-tag-list').innerHTML = '<div class="dm-tm-empty">Gagal memuat data.</div>';
    }
  }

  function render() {
    renderKatSelect();
    renderTagList();
    renderKatList();
    if (tmFileId) {
      renderFileChips();
      renderTagCheckboxes();
    }
  }

  /* ======================== RENDER ======================== */
  function renderKatSelect() {
    const sel = document.getElementById('tm-tag-kat');
    sel.innerHTML = '<option value="">Tanpa kategori</option>'
      + tmState.kategoris.map(k => '<option value="'+k.id+'">'+esc(k.name)+'</option>').join('');
  }

  function renderTagList() {
    const wrap = document.getElementById('tm-tag-list');
    if (!tmState.tags.length) { wrap.innerHTML = '<div class="dm-tm-empty">Belum ada tag.</div>'; return; }
    wrap.innerHTML = tmState.tags.map(t =>
      '<div class="dm-tm-list-item">'
      +'<div class="dm-tag-dot" style="background:'+esc(t.color)+'"></div>'
      +'<div>'
      +'<div class="dm-tm-list-name">'+esc(t.name)+'</div>'
      +(t.kategori_name ? '<div class="dm-tm-list-meta">'+esc(t.kategori_name)+' &middot; </div>' : '')
      +'</div>'
      +'<span class="dm-tm-list-meta" style="margin-left:auto">'+t.file_count+' file</span>'
      +(isAdmin
        ? '<button class="dm-tm-list-edit" onclick="editTag('+t.id+')">Edit</button>'
          +'<button class="dm-tm-list-del"  onclick="deleteTag('+t.id+',\''+esc(t.name)+'\')" >Hapus</button>'
        : '')
      +'</div>'
    ).join('');
  }

  function renderKatList() {
    const wrap = document.getElementById('tm-kat-list');
    if (!tmState.kategoris.length) { wrap.innerHTML = '<div class="dm-tm-empty">Belum ada kategori.</div>'; return; }
    wrap.innerHTML = tmState.kategoris.map(k =>
      '<div class="dm-tm-list-item">'
      +'<div class="dm-tag-dot" style="background:'+esc(k.color)+'"></div>'
      +'<div class="dm-tm-list-name">'+esc(k.name)+'</div>'
      +(isAdmin
        ? '<button class="dm-tm-list-edit" onclick="editKat('+k.id+')">Edit</button>'
          +'<button class="dm-tm-list-del"  onclick="deleteKat('+k.id+',\''+esc(k.name)+'\')" >Hapus</button>'
        : '')
      +'</div>'
    ).join('');
  }

  function renderFileChips() {
    const wrap = document.getElementById('tm-file-chips');
    if (!tmState.fileTags.length) { wrap.innerHTML = '<span style="font-size:12px;color:#A89E90">Belum ada tag.</span>'; return; }
    wrap.innerHTML = tmState.fileTags.map(t =>
      '<span class="dm-tag-chip" style="background:'+esc(t.color)+'">'+esc(t.name)
      +(tmCanEdit ? '<button class="dm-tag-chip-rm" onclick="removeTagFromFile('+t.id+')" title="Hapus tag">&times;</button>' : '')
      +'</span>'
    ).join('');
  }

  function renderTagCheckboxes() {
    const wrap = document.getElementById('tm-tag-checkboxes');
    const selectedIds = new Set(tmState.fileTags.map(t=>t.id));
    if (!tmState.tags.length) { wrap.innerHTML = '<div class="dm-tm-empty">Belum ada tag. Buat tag terlebih dahulu.</div>'; return; }
    // Kelompokkan per kategori
    const groups = {};
    tmState.tags.forEach(t => {
      const g = t.kategori_name || 'Tanpa Kategori';
      if (!groups[g]) groups[g] = [];
      groups[g].push(t);
    });
    let html = '';
    Object.keys(groups).sort().forEach(g => {
      html += '<div style="font-size:10.5px;font-weight:800;color:#A89E90;text-transform:uppercase;letter-spacing:.05em;margin:.6rem 0 .3rem">'+esc(g)+'</div>';
      html += groups[g].map(t =>
        '<label class="dm-tag-checkrow">'
        +'<input type="checkbox" name="file-tag" value="'+t.id+'" '+(selectedIds.has(t.id)?'checked':'')+'>'
        +'<div class="dm-tag-dot" style="background:'+esc(t.color)+'"></div>'
        +'<span class="dm-tag-check-name">'+esc(t.name)+'</span>'
        +(t.kategori_name ? '<span class="dm-tag-check-kat">'+esc(t.kategori_name)+'</span>' : '')
        +'</label>'
      ).join('');
    });
    wrap.innerHTML = html;
  }

  /* ======================== AKSI FILE TAG ======================== */
  window.syncFileTags = async function() {
    const ids = [...document.querySelectorAll('input[name=file-tag]:checked')].map(i=>i.value);
    const fd  = new FormData();
    ids.forEach(id => fd.append('tag_ids[]', id));
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+tmFileId+'/tags/sync',{method:'POST',body:fd})).json();
      setMsg('tm-sync-msg', data.message, data.success);
      if (data.success) {
        tmState.fileTags = data.tags || [];
        renderFileChips();
        refreshFileRowTags(tmFileId, tmState.fileTags);
      }
    } catch(e) { setMsg('tm-sync-msg','Gagal koneksi.',false); }
  };

  window.removeTagFromFile = async function(tagId) {
    const fd = new FormData();
    const newIds = tmState.fileTags.filter(t=>t.id!==tagId).map(t=>String(t.id));
    newIds.forEach(id => fd.append('tag_ids[]', id));
    try {
      const data = await (await fetch(BASE+'/api/dokumen/'+tmFileId+'/tags/sync',{method:'POST',body:fd})).json();
      if (data.success) {
        tmState.fileTags = data.tags || [];
        renderFileChips();
        renderTagCheckboxes();
        refreshFileRowTags(tmFileId, tmState.fileTags);
      }
    } catch(e) {}
  };

  function refreshFileRowTags(fileId, tags) {
    const row = document.getElementById('file-row-tags-'+fileId);
    if (!row) return;
    row.innerHTML = tags.map(t=>
      '<span class="dm-tag-chip" style="background:'+esc(t.color)+';font-size:10px;padding:.1em .45em">'+esc(t.name)+'</span>'
    ).join('');
  }

  /* ======================== CRUD TAG (Admin) ======================== */
  window.createTag = async function() {
    const name  = document.getElementById('tm-tag-name').value.trim();
    const color = document.getElementById('tm-tag-color').value;
    const katId = document.getElementById('tm-tag-kat').value;
    if (!name) { setMsg('tm-tag-msg','Nama wajib diisi.',false); return; }
    const fd = new FormData();
    fd.append('name',name); fd.append('color',color); if(katId) fd.append('kategori_id',katId);
    const data = await (await fetch(BASE+'/api/dokumen/tags',{method:'POST',body:fd})).json();
    setMsg('tm-tag-msg', data.message, data.success);
    if (data.success) {
      document.getElementById('tm-tag-name').value = '';
      tmState.tags = data.tags; tmState.kategoris = data.kategoris;
      render();
    }
  };

  window.editTag = function(id) {
    const t = tmState.tags.find(x=>x.id===id); if(!t) return;
    const newName  = prompt('Nama tag baru:', t.name); if(!newName) return;
    const newColor = prompt('Warna hex (mis #2B6CB0):', t.color) || t.color;
    const fd = new FormData();
    fd.append('name',newName.trim()); fd.append('color',newColor);
    if (t.kategori_id) fd.append('kategori_id', t.kategori_id);
    fetch(BASE+'/api/dokumen/tags/'+id+'/update',{method:'POST',body:fd})
      .then(r=>r.json()).then(data=>{ if(data.success){tmState.tags=data.tags;tmState.kategoris=data.kategoris;render();}else alert(data.message); });
  };

  window.deleteTag = function(id, name) {
    if (!confirm('Hapus tag "'+name+'"? Tag akan dihapus dari semua file.')) return;
    const fd = new FormData();
    fetch(BASE+'/api/dokumen/tags/'+id+'/delete',{method:'POST',body:fd})
      .then(r=>r.json()).then(data=>{ if(data.success){tmState.tags=data.tags;tmState.kategoris=data.kategoris;render();}else alert(data.message); });
  };

  /* ======================== CRUD KATEGORI (Admin) ======================== */
  window.createKategori = async function() {
    const name  = document.getElementById('tm-kat-name').value.trim();
    const color = document.getElementById('tm-kat-color').value;
    if (!name) { setMsg('tm-kat-msg','Nama wajib diisi.',false); return; }
    const fd = new FormData(); fd.append('name',name); fd.append('color',color);
    const data = await (await fetch(BASE+'/api/dokumen/kategoris',{method:'POST',body:fd})).json();
    setMsg('tm-kat-msg', data.message, data.success);
    if (data.success) {
      document.getElementById('tm-kat-name').value = '';
      tmState.kategoris = data.kategoris;
      render();
    }
  };

  window.editKat = function(id) {
    const k = tmState.kategoris.find(x=>x.id===id); if(!k) return;
    const newName  = prompt('Nama kategori baru:', k.name); if(!newName) return;
    const newColor = prompt('Warna hex:', k.color) || k.color;
    const fd = new FormData(); fd.append('name',newName.trim()); fd.append('color',newColor);
    fetch(BASE+'/api/dokumen/kategoris/'+id+'/update',{method:'POST',body:fd})
      .then(r=>r.json()).then(data=>{ if(data.success){tmState.kategoris=data.kategoris;render();}else alert(data.message); });
  };

  window.deleteKat = function(id, name) {
    if (!confirm('Hapus kategori "'+name+'"?')) return;
    const fd = new FormData();
    fetch(BASE+'/api/dokumen/kategoris/'+id+'/delete',{method:'POST',body:fd})
      .then(r=>r.json()).then(data=>{ if(data.success){tmState.kategoris=data.kategoris;tmState.tags=data.tags||tmState.tags;render();}else alert(data.message); });
  };

  /* ======================== HELPERS ======================== */
  function setMsg(elId, msg, ok) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.className = 'dm-tm-msg '+(ok?'dm-tm-msg-ok':'dm-tm-msg-err');
    el.textContent = msg;
  }
  function esc(s) { const d=document.createElement('div'); d.textContent=String(s||''); return d.innerHTML; }

  /* ESC + klik luar */
  document.getElementById('modal-tag-manager').addEventListener('click', e => {
    if (e.target===document.getElementById('modal-tag-manager')) closeTagManager();
  });
  document.addEventListener('keydown', e => {
    if (e.key==='Escape' && document.getElementById('modal-tag-manager').classList.contains('open')) closeTagManager();
  });
})();
</script>
