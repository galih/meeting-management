-- Migrasi: tambah kolom letterhead_html di tabel notulen_templates
-- Jalankan: mysql -u root -p nama_db < database/migrations/add_letterhead_to_templates.sql

ALTER TABLE notulen_templates
  ADD COLUMN letterhead_html MEDIUMTEXT NULL COMMENT 'HTML kop surat khusus template ini'
  AFTER description;
