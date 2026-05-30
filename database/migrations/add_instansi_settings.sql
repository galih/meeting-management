-- Migrasi: tambah setting instansi untuk kop surat
-- Jalankan: mysql -u root -p meeting_db < database/migrations/add_instansi_settings.sql

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
  ('instansi_nama',     'Nama Instansi / Lembaga'),
  ('instansi_alamat',   'Jl. Contoh No. 1, Kota'),
  ('instansi_telepon',  '(021) 000-0000');
