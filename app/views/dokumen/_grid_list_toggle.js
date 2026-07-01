/**
 * Toggle Grid / List untuk halaman Dokumen
 * Dipanggil satu kali setelah DOM ready.
 * Menggunakan localStorage agar preferensi bertahan antar sesi.
 */
(function () {
  'use strict';

  const STORE_KEY = 'dm_view_mode';
  const GRID_COLS = 'repeat(auto-fill,minmax(300px,1fr))';
  const LIST_COLS = '1fr';

  /**
   * Render satu kartu dalam mode LIST (lebih ringkas, horizontal).
   * Hanya mengubah class & style tanpa menyentuh konten HTML yang sudah ada.
   */
  function applyMode(mode) {
    const grid = document.getElementById('file-tbody');
    if (!grid) return;

    if (mode === 'list') {
      grid.style.gridTemplateColumns = LIST_COLS;
      grid.classList.add('dm-view-list');
      grid.classList.remove('dm-view-grid');
    } else {
      grid.style.gridTemplateColumns = GRID_COLS;
      grid.classList.add('dm-view-grid');
      grid.classList.remove('dm-view-list');
    }

    // Simpan preferensi
    try { localStorage.setItem(STORE_KEY, mode); } catch (_) {}

    // Perbarui state tombol
    document.querySelectorAll('[data-view-btn]').forEach(btn => {
      btn.classList.toggle('active', btn.dataset.viewBtn === mode);
    });
  }

  function init() {
    let saved = 'grid';
    try { saved = localStorage.getItem(STORE_KEY) || 'grid'; } catch (_) {}

    // Pasang event listener ke tombol toggle
    document.querySelectorAll('[data-view-btn]').forEach(btn => {
      btn.addEventListener('click', () => applyMode(btn.dataset.viewBtn));
    });

    // Terapkan mode tersimpan
    applyMode(saved);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
