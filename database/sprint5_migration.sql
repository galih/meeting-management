-- ============================================================
-- Sprint 5: Unit Kerja Hierarki (menggantikan Departemen)
-- Jalankan sekali pada database yang sudah ada
-- ============================================================

-- 1. Tambah kolom hierarki pada tabel departments (tetap pakai nama tabel lama agar FK tidak rusak)
ALTER TABLE departments
  ADD COLUMN IF NOT EXISTS parent_id INT DEFAULT NULL AFTER id,
  ADD COLUMN IF NOT EXISTS level     TINYINT UNSIGNED NOT NULL DEFAULT 1
    COMMENT '1=Unit Kerja, 2=Bidang/Bagian, 3=Sub Bidang/Sub Bagian' AFTER code;

-- 2. FK self-referencing
ALTER TABLE departments
  ADD CONSTRAINT IF NOT EXISTS fk_dept_parent
  FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL;

-- 3. Semua data lama otomatis jadi level 1 (Unit Kerja) — tidak perlu update
-- UPDATE departments SET level = 1 WHERE level IS NULL; -- sudah di-default
