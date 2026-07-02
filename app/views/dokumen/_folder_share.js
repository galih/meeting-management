// _folder_share.js — Folder Share & Delete UI Handler
(function () {
  var BASE = (window._BASE_URL || '').replace(/\/$/, '');
  var currentFolderId = null;
  var selectedUserId = null;
  var selectedUserName = null;

  /* ── OPEN SHARE MODAL ──────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-share-folder');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    currentFolderId = btn.dataset.folderId;
    selectedUserId = null;
    selectedUserName = null;
    document.getElementById('shareFolderName').textContent = btn.dataset.folderName;
    document.getElementById('shareFolderUserSearch').value = '';
    document.getElementById('shareFolderUserSuggestions').innerHTML = '';
    document.getElementById('shareFolderMsg').innerHTML = '';
    loadFolderShares(currentFolderId);
    openDmModal('modal-folder-share');
  });

  /* ── OPEN DELETE MODAL ────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn-delete-folder');
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    currentFolderId = btn.dataset.folderId;
    document.getElementById('deleteFolderName').textContent = btn.dataset.folderName;
    document.getElementById('deleteFolderMsg').innerHTML = '';
    openDmModal('modal-folder-delete');
  });

  /* ── MODAL HELPERS ────────────────────────────────────────── */
  function openDmModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.add('open');
  }
  function closeDmModal(id) {
    var el = document.getElementById(id);
    if (el) el.classList.remove('open');
  }
  window.closeFolderShareModal = function () { closeDmModal('modal-folder-share'); };
  window.closeFolderDeleteModal = function () { closeDmModal('modal-folder-delete'); };

  /* ── LOAD SHARE LIST ─────────────────────────────────────── */
  function loadFolderShares(folderId) {
    var list = document.getElementById('shareFolderList');
    list.innerHTML = '<div style="text-align:center;padding:.75rem 0;color:#A89E90"><div class="spinner-border spinner-border-sm"></div></div>';
    fetch(BASE + '/api/dokumen/folder/' + folderId + '/shares')
      .then(function (r) { return r.json(); })
      .then(function (data) { renderFolderShares(data.shares || data || []); })
      .catch(function () { list.innerHTML = '<div style="color:#C05621;font-size:13px">Gagal memuat data.</div>'; });
  }

  function renderFolderShares(shares) {
    var list = document.getElementById('shareFolderList');
    if (!shares.length) {
      list.innerHTML = '<div style="text-align:center;color:#9A8D7F;font-size:13px;padding:.6rem">Belum dibagikan ke siapapun.</div>';
      return;
    }
    list.innerHTML = shares.map(function (u) {
      return '<div class="dm-share-item" id="fshare-item-' + u.user_id + '">'
        + '<div class="dm-share-avatar">' + (u.name || '?').charAt(0).toUpperCase() + '</div>'
        + '<div class="dm-share-info"><div class="dm-share-name">' + escH(u.name) + '</div><div class="dm-share-role">@' + escH(u.username) + '</div></div>'
        + '<select class="dm-share-perm-select" onchange="updateFolderPerm(' + u.user_id + ',this.value)">'
        + '<option value="view"' + (u.permission === 'view' ? ' selected' : '') + '>View Only</option>'
        + '<option value="edit"' + (u.permission === 'edit' ? ' selected' : '') + '>Edit</option>'
        + '</select>'
        + '<button class="dm-share-revoke" title="Cabut akses" onclick="revokeFolderShare(' + u.user_id + ')">'
        + '<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>'
        + '</button></div>';
    }).join('');
  }

  /* ── USER SEARCH AUTOCOMPLETE ────────────────────────────── */
  var searchDebounce = null;
  document.addEventListener('input', function (e) {
    if (e.target.id !== 'shareFolderUserSearch') return;
    clearTimeout(searchDebounce);
    var q = e.target.value.trim();
    var box = document.getElementById('shareFolderUserSuggestions');
    if (q.length < 1) { box.innerHTML = ''; return; }
    searchDebounce = setTimeout(function () {
      fetch(BASE + '/api/users?q=' + encodeURIComponent(q))
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var users = data.users || data || [];
          if (!users.length) { box.innerHTML = '<div class="dm-user-option" style="color:#9A8D7F;cursor:default">Tidak ada user ditemukan</div>'; return; }
          box.innerHTML = users.map(function (u) {
            return '<div class="dm-user-option" data-id="' + u.id + '" data-name="' + escH(u.name) + '">'
              + '<strong>' + escH(u.name) + '</strong> <small>@' + escH(u.username) + '</small></div>';
          }).join('');
        });
    }, 280);
  });

  document.addEventListener('click', function (e) {
    var item = e.target.closest('#shareFolderUserSuggestions .dm-user-option');
    if (!item) return;
    selectedUserId = item.dataset.id;
    selectedUserName = item.dataset.name;
    document.getElementById('shareFolderUserSearch').value = selectedUserName;
    document.getElementById('shareFolderUserSuggestions').innerHTML = '';
  });

  /* ── ADD SHARE ───────────────────────────────────────────── */
  document.addEventListener('click', function (e) {
    if (e.target.id !== 'btnAddFolderShare' && !e.target.closest('#btnAddFolderShare')) return;
    if (!selectedUserId) { setFolderMsg('Pilih pengguna terlebih dahulu.', false); return; }
    var permission = document.getElementById('shareFolderPermission').value;
    var btn = document.getElementById('btnAddFolderShare');
    btn.disabled = true;
    fetch(BASE + '/api/dokumen/folder/' + currentFolderId + '/shares', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: selectedUserId, permission: permission })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) {
          selectedUserId = null;
          document.getElementById('shareFolderUserSearch').value = '';
          renderFolderShares(data.shares || []);
          setFolderMsg('Berhasil dibagikan.', true);
        } else {
          setFolderMsg(data.message || 'Gagal membagikan.', false);
        }
        btn.disabled = false;
      })
      .catch(function () { setFolderMsg('Gagal koneksi.', false); btn.disabled = false; });
  });

  /* ── UPDATE PERMISSION ───────────────────────────────────── */
  window.updateFolderPerm = function (userId, permission) {
    fetch(BASE + '/api/dokumen/folder/' + currentFolderId + '/shares/' + userId + '/permission', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ permission: permission })
    })
      .then(function (r) { return r.json(); })
      .then(function (data) { if (data.shares) renderFolderShares(data.shares); });
  };

  /* ── REVOKE SHARE ────────────────────────────────────────── */
  window.revokeFolderShare = function (userId) {
    if (!confirm('Cabut akses user ini dari folder?')) return;
    fetch(BASE + '/api/dokumen/folder/' + currentFolderId + '/shares/' + userId + '/delete', { method: 'POST' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.success) renderFolderShares(data.shares || []);
        else alert(data.message || 'Gagal mencabut akses.');
      });
  };

  /* ── CONFIRM DELETE FOLDER ───────────────────────────────── */
  document.addEventListener('click', function (e) {
    if (e.target.id !== 'btnConfirmDeleteFolder' && !e.target.closest('#btnConfirmDeleteFolder')) return;
    var btn = document.getElementById('btnConfirmDeleteFolder');
    btn.disabled = true;
    btn.textContent = 'Menghapus...';
    fetch(BASE + '/api/dokumen/folder/' + currentFolderId + '/delete', { method: 'POST' })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        closeDmModal('modal-folder-delete');
        if (data.success) {
          var card = document.querySelector('.dm-folder-card[data-folder-id="' + currentFolderId + '"]');
          if (card) card.remove();
        } else {
          alert(data.message || 'Gagal menghapus folder.');
        }
        btn.disabled = false;
        btn.textContent = 'Ya, Hapus';
      })
      .catch(function () {
        alert('Gagal koneksi.');
        btn.disabled = false;
        btn.textContent = 'Ya, Hapus';
      });
  });

  /* ── CLOSE ON OVERLAY CLICK ──────────────────────────────── */
  ['modal-folder-share', 'modal-folder-delete'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('click', function (e) { if (e.target === el) closeDmModal(id); });
  });

  /* ── HELPERS ─────────────────────────────────────────────── */
  function escH(s) { var d = document.createElement('div'); d.textContent = String(s || ''); return d.innerHTML; }
  function setFolderMsg(msg, ok) {
    var el = document.getElementById('shareFolderMsg');
    if (!el) return;
    el.innerHTML = '<span class="dm-msg-' + (ok ? 'ok' : 'err') + '">' + msg + '</span>';
  }
})();
