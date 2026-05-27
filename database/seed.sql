-- ============================================================
-- Meeting Management App — Seed Data (Dummy)
-- Jalankan SETELAH schema.sql
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE comment_mentions;
TRUNCATE TABLE notulen_comments;
TRUNCATE TABLE notulen_exports;
TRUNCATE TABLE email_queue;
TRUNCATE TABLE meeting_attachments;
TRUNCATE TABLE notifications;
TRUNCATE TABLE tindak_lanjut;
TRUNCATE TABLE notulen_history;
TRUNCATE TABLE notulen;
TRUNCATE TABLE meeting_attendances;
TRUNCATE TABLE meeting_participants;
TRUNCATE TABLE meetings;
TRUNCATE TABLE recurring_participants;
TRUNCATE TABLE recurring_meetings;
TRUNCATE TABLE departments;
TRUNCATE TABLE users;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- USERS
-- Password semua akun: Password@123
-- Hash bcrypt cost 12
-- ============================================================
INSERT INTO users (id, username, name, email, password, role, is_active) VALUES
(1,  'admin',       'Administrator',      'admin@meetingapp.id',      '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'admin',       1),
(2,  'budi.s',      'Budi Santoso',       'budi.s@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'sekretaris',  1),
(3,  'sari.d',      'Sari Dewi',          'sari.d@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'sekretaris',  1),
(4,  'andi.p',      'Andi Pratama',       'andi.p@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(5,  'rina.k',      'Rina Kusuma',        'rina.k@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(6,  'doni.w',      'Doni Wahyudi',       'doni.w@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(7,  'maya.l',      'Maya Lestari',       'maya.l@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(8,  'hendra.s',    'Hendra Saputra',     'hendra.s@meetingapp.id',   '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(9,  'fitri.n',     'Fitri Nuraini',      'fitri.n@meetingapp.id',    '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1),
(10, 'reza.f',      'Reza Firmansyah',    'reza.f@meetingapp.id',     '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'peserta',     1);

-- ============================================================
-- DEPARTMENTS
-- ============================================================
INSERT INTO departments (id, name, code, description, head_id, is_active) VALUES
(1, 'Teknologi Informasi', 'TI',  'Divisi pengembangan sistem dan infrastruktur IT', 2, 1),
(2, 'Sumber Daya Manusia', 'SDM', 'Divisi rekrutmen, pelatihan, dan kesejahteraan karyawan', 3, 1),
(3, 'Keuangan',            'FIN', 'Divisi pengelolaan keuangan dan akuntansi', 4, 1),
(4, 'Operasional',         'OPS', 'Divisi operasional dan logistik', 5, 1),
(5, 'Marketing',           'MKT', 'Divisi pemasaran dan pengembangan bisnis', 6, 1);

-- Update department_id pada users
UPDATE users SET department_id = 1 WHERE id IN (2, 8);
UPDATE users SET department_id = 2 WHERE id IN (3, 9);
UPDATE users SET department_id = 3 WHERE id IN (4);
UPDATE users SET department_id = 4 WHERE id IN (5, 10);
UPDATE users SET department_id = 5 WHERE id IN (6, 7);

-- ============================================================
-- RECURRING MEETINGS
-- ============================================================
INSERT INTO recurring_meetings (id, title, description, location, frequency, day_of_week, start_time, end_time, start_date, end_date, color, department_id, created_by, is_active) VALUES
(1, 'Standup Mingguan TI',   'Sync progress dan blocker tim TI',      'Ruang Meeting A',  'weekly',   1, '09:00:00', '09:30:00', '2025-01-06', '2025-12-31', '#206bc4', 1, 1, 1),
(2, 'Review Bulanan SDM',    'Review KPI dan rencana bulan depan',    'Ruang Meeting B',  'monthly',  NULL, '10:00:00', '11:30:00', '2025-01-15', '2025-12-31', '#d63939', 2, 1, 1),
(3, 'Rapat Koordinasi Ops',  'Koordinasi lintas divisi operasional',  'Aula Utama',       'biweekly', 3, '14:00:00', '15:00:00', '2025-01-08', '2025-12-31', '#f76707', NULL, 1, 1);

INSERT INTO recurring_participants (recurring_id, user_id) VALUES
(1, 2),(1, 4),(1, 8),
(2, 3),(2, 9),(2, 5),
(3, 1),(3, 2),(3, 3),(3, 4),(3, 5);

-- ============================================================
-- MEETINGS
-- ============================================================
INSERT INTO meetings (id, title, description, location, start_datetime, end_datetime, status, color, department_id, recurring_id, created_by) VALUES
-- Meetings lampau (done)
(1,  'Kick-off Proyek SIMA',
     'Pembahasan scope, timeline, dan pembagian tugas proyek SIMA.',
     'Ruang Meeting A',
     '2025-05-05 09:00:00', '2025-05-05 11:00:00', 'done',      '#206bc4', 1, NULL, 1),
(2,  'Review Desain UI/UX',
     'Review wireframe dan prototype aplikasi mobile.',
     'Ruang Meeting B',
     '2025-05-12 13:00:00', '2025-05-12 15:00:00', 'done',      '#206bc4', 1, NULL, 2),
(3,  'Evaluasi KPI Q1 2025',
     'Evaluasi pencapaian KPI seluruh divisi Q1 2025.',
     'Aula Utama',
     '2025-05-20 09:00:00', '2025-05-20 12:00:00', 'done',      '#d63939', NULL, NULL, 1),
(4,  'Rapat Anggaran Q2',
     'Pembahasan dan persetujuan anggaran operasional Q2 2025.',
     'Ruang Direksi',
     '2025-06-03 10:00:00', '2025-06-03 12:00:00', 'done',      '#d63939', 3,    NULL, 1),
(5,  'Workshop Keamanan Siber',
     'Workshop internal tentang best practice keamanan sistem.',
     'Lab Komputer Lt.2',
     '2025-06-10 08:00:00', '2025-06-10 17:00:00', 'done',      '#206bc4', 1,    NULL, 2),
(6,  'Standup Mingguan TI — W23',
     'Sync progress sprint dan blocker.',
     'Ruang Meeting A',
     '2025-06-02 09:00:00', '2025-06-02 09:30:00', 'done',      '#206bc4', 1,    1,    2),
-- Meetings sedang berlangsung / hari ini
(7,  'Sprint Planning Juni',
     'Planning sprint 2 bulan Juni tim TI.',
     'Ruang Meeting A',
     '2026-05-28 09:00:00', '2026-05-28 11:00:00', 'ongoing',   '#f76707', 1,    NULL, 2),
-- Meetings mendatang
(8,  'Demo Produk ke Manajemen',
     'Presentasi demo fitur terbaru ke jajaran manajemen.',
     'Ruang Direksi',
     '2026-05-30 13:00:00', '2026-05-30 15:00:00', 'scheduled', '#206bc4', 1,    NULL, 1),
(9,  'Rekrutmen Batch 3 2026',
     'Koordinasi proses seleksi dan jadwal interview batch 3.',
     'Ruang Meeting B',
     '2026-06-02 10:00:00', '2026-06-02 11:30:00', 'scheduled', '#d63939', 2,    NULL, 3),
(10, 'Rapat Evaluasi Marketing Q2',
     'Review kampanye digital dan target penjualan Q2.',
     'https://meet.google.com/abc-defg-hij',
     '2026-06-05 14:00:00', '2026-06-05 15:30:00', 'scheduled', '#ae3ec9', 5,    NULL, 1),
(11, 'Review Infrastruktur Server',
     'Audit kapasitas server dan rencana upgrade.',
     'Ruang Server',
     '2026-06-10 10:00:00', '2026-06-10 12:00:00', 'scheduled', '#206bc4', 1,    NULL, 2),
(12, 'Town Hall Q2 2026',
     'Town hall seluruh karyawan — update perusahaan dan Q&A.',
     'Aula Utama',
     '2026-06-15 09:00:00', '2026-06-15 11:00:00', 'scheduled', '#f76707', NULL, NULL, 1),
(13, 'Review Anggaran Semester 1',
     'Review realisasi anggaran semester 1 dan proyeksi semester 2.',
     'Ruang Direksi',
     '2026-07-01 10:00:00', '2026-07-01 12:00:00', 'scheduled', '#d63939', 3,    NULL, 1),
(14, 'Rapat Koordinasi Ops Juli',
     'Koordinasi operasional dan distribusi tugas Juli.',
     'Aula Utama',
     '2026-07-08 14:00:00', '2026-07-08 15:00:00', 'scheduled', '#f76707', 4,    3,    3),
-- Cancelled
(15, 'Workshop Agile (Dibatalkan)',
     'Workshop Agile & Scrum — dibatalkan karena pembicara berhalangan.',
     'Ruang Meeting A',
     '2026-05-25 09:00:00', '2026-05-25 17:00:00', 'cancelled', '#868e96', 1,    NULL, 2);

-- ============================================================
-- MEETING PARTICIPANTS
-- ============================================================
INSERT INTO meeting_participants (meeting_id, user_id, status) VALUES
-- Meeting 1
(1,2,'attended'),(1,4,'attended'),(1,5,'attended'),(1,8,'attended'),(1,9,'attended'),
-- Meeting 2
(2,2,'attended'),(2,4,'attended'),(2,8,'attended'),
-- Meeting 3
(3,1,'attended'),(3,2,'attended'),(3,3,'attended'),(3,4,'attended'),(3,5,'attended'),(3,6,'attended'),
-- Meeting 4
(4,1,'attended'),(4,3,'attended'),(4,4,'attended'),
-- Meeting 5
(5,2,'attended'),(5,4,'attended'),(5,8,'attended'),(5,9,'attended'),
-- Meeting 6
(6,2,'attended'),(6,4,'attended'),(6,8,'attended'),
-- Meeting 7 (ongoing)
(7,2,'invited'),(7,4,'invited'),(7,8,'invited'),
-- Meeting 8
(8,1,'invited'),(8,2,'invited'),(8,3,'invited'),(8,4,'invited'),(8,6,'invited'),
-- Meeting 9
(9,3,'invited'),(9,5,'invited'),(9,9,'invited'),
-- Meeting 10
(10,1,'invited'),(10,6,'invited'),(10,7,'invited'),
-- Meeting 11
(11,2,'invited'),(11,4,'invited'),(11,8,'invited'),
-- Meeting 12 (all hands)
(12,1,'invited'),(12,2,'invited'),(12,3,'invited'),(12,4,'invited'),(12,5,'invited'),
(12,6,'invited'),(12,7,'invited'),(12,8,'invited'),(12,9,'invited'),(12,10,'invited'),
-- Meeting 13
(13,1,'invited'),(13,3,'invited'),(13,4,'invited'),
-- Meeting 14
(14,1,'invited'),(14,5,'invited'),(14,10,'invited'),
-- Meeting 15 (cancelled)
(15,2,'declined'),(15,4,'declined'),(15,8,'declined');

-- ============================================================
-- MEETING ATTENDANCES (untuk meeting done)
-- ============================================================
INSERT INTO meeting_attendances (meeting_id, user_id, status) VALUES
(1,2,'present'),(1,4,'present'),(1,5,'late'),(1,8,'present'),(1,9,'absent'),
(2,2,'present'),(2,4,'present'),(2,8,'present'),
(3,1,'present'),(3,2,'present'),(3,3,'present'),(3,4,'late'),(3,5,'present'),(3,6,'excused'),
(4,1,'present'),(4,3,'present'),(4,4,'present'),
(5,2,'present'),(5,4,'present'),(5,8,'present'),(5,9,'present'),
(6,2,'present'),(6,4,'present'),(6,8,'absent');

-- ============================================================
-- NOTULEN
-- ============================================================
INSERT INTO notulen (meeting_id, content, version, created_by, updated_by) VALUES
(1, '<h2>Notulen Rapat: Kick-off Proyek SIMA</h2>
<p><strong>Tanggal:</strong> 5 Mei 2025 | <strong>Pimpinan:</strong> Administrator</p>
<h3>Agenda</h3>
<ol><li>Perkenalan tim proyek</li><li>Pembahasan scope dan deliverable</li><li>Penetapan timeline dan milestone</li></ol>
<h3>Hasil Rapat</h3>
<ul>
<li>Proyek SIMA dijadwalkan selesai pada akhir Q3 2025.</li>
<li>Tim TI (Budi, Andi, Hendra) bertanggung jawab atas pengembangan backend dan frontend.</li>
<li>Milestone 1: Desain database dan API — selesai 30 Mei 2025.</li>
<li>Milestone 2: Fitur core (CRUD meeting, notulen) — selesai 30 Juni 2025.</li>
<li>Milestone 3: Fitur lanjutan dan testing — selesai 31 Juli 2025.</li>
</ul>
<h3>Tindak Lanjut</h3>
<ul>
<li>Andi menyiapkan ERD database — deadline 15 Mei 2025.</li>
<li>Budi membuat project board di GitHub — deadline 10 Mei 2025.</li>
</ul>', 1, 2, 2),

(2, '<h2>Notulen Rapat: Review Desain UI/UX</h2>
<p><strong>Tanggal:</strong> 12 Mei 2025</p>
<h3>Hasil Review</h3>
<ul>
<li>Wireframe dashboard disetujui dengan catatan: tambahkan widget statistik tindak lanjut.</li>
<li>Prototype halaman meeting list perlu revisi — tambahkan filter by status dan departemen.</li>
<li>Color scheme: gunakan oranye (#f76707) sebagai primary brand color.</li>
</ul>
<h3>Tindak Lanjut</h3>
<ul><li>Hendra merevisi prototype sesuai feedback — deadline 20 Mei 2025.</li></ul>', 2, 2, 2),

(3, '<h2>Notulen Rapat: Evaluasi KPI Q1 2025</h2>
<p><strong>Tanggal:</strong> 20 Mei 2025</p>
<h3>Hasil Evaluasi</h3>
<table border="1" cellpadding="6" style="border-collapse:collapse;width:100%">
<tr><th>Divisi</th><th>Target</th><th>Realisasi</th><th>Status</th></tr>
<tr><td>TI</td><td>95%</td><td>92%</td><td>⚠ Hampir tercapai</td></tr>
<tr><td>SDM</td><td>90%</td><td>95%</td><td>✅ Tercapai</td></tr>
<tr><td>Keuangan</td><td>100%</td><td>98%</td><td>⚠ Hampir tercapai</td></tr>
<tr><td>Operasional</td><td>85%</td><td>88%</td><td>✅ Tercapai</td></tr>
<tr><td>Marketing</td><td>80%</td><td>72%</td><td>❌ Tidak tercapai</td></tr>
</table>
<h3>Catatan</h3>
<p>Divisi Marketing diminta membuat rencana perbaikan untuk Q2. Target Q2 tetap 85%.</p>', 1, 3, 3),

(4, '<h2>Notulen Rapat: Anggaran Q2 2025</h2>
<p><strong>Tanggal:</strong> 3 Juni 2025</p>
<h3>Keputusan</h3>
<ul>
<li>Total anggaran operasional Q2 disetujui: Rp 2.400.000.000</li>
<li>Alokasi TI: Rp 800 juta (upgrade server + lisensi software)</li>
<li>Alokasi SDM: Rp 400 juta (rekrutmen dan pelatihan)</li>
<li>Alokasi Marketing: Rp 600 juta (kampanye digital Q2)</li>
<li>Cadangan darurat: Rp 200 juta</li>
</ul>', 1, 3, 1);

-- Notulen history
INSERT INTO notulen_history (meeting_id, content, version, edited_by) VALUES
(2, '<h2>Notulen Rapat: Review Desain UI/UX (v1)</h2><p>Draft awal notulen.</p>', 1, 2);

-- ============================================================
-- KOMENTAR NOTULEN
-- ============================================================
INSERT INTO notulen_comments (id, meeting_id, parent_id, user_id, content, is_resolved) VALUES
(1, 1, NULL, 4, 'Apakah milestone 1 sudah include dokumentasi API juga?', 0),
(2, 1, 1,    2, 'Iya, dokumentasi Swagger/OpenAPI masuk milestone 1.', 1),
(3, 2, NULL, 8, 'Filter by departemen sudah masuk backlog sprint berapa?', 0),
(4, 3, NULL, 6, 'Data realisasi Marketing berdasarkan periode Januari–Maret saja?', 1),
(5, 3, 4,    3, 'Benar, data Q1 adalah Januari–Maret 2025.', 1);

-- ============================================================
-- TINDAK LANJUT
-- ============================================================
INSERT INTO tindak_lanjut (meeting_id, description, assigned_to, due_date, priority, status, completed_at, created_by) VALUES
-- Meeting 1 — done
(1, 'Buat ERD database SIMA lengkap',                   4,  '2025-05-15', 'high',   'done',        '2025-05-14 16:00:00', 1),
(1, 'Setup project board GitHub',                       2,  '2025-05-10', 'medium', 'done',        '2025-05-09 10:00:00', 1),
-- Meeting 2 — done
(2, 'Revisi prototype UI sesuai feedback',              8,  '2025-05-20', 'high',   'done',        '2025-05-19 17:00:00', 2),
-- Meeting 3 — tindak lanjut marketing
(3, 'Buat rencana perbaikan KPI Marketing Q2',          6,  '2025-06-05', 'high',   'done',        '2025-06-04 14:00:00', 1),
(3, 'Laporan realisasi anggaran Q1 ke direksi',         4,  '2025-06-01', 'medium', 'done',        '2025-05-30 11:00:00', 1),
-- Meeting 4 — anggaran
(4, 'Proses pengajuan anggaran upgrade server',         2,  '2025-06-15', 'high',   'done',        '2025-06-13 09:00:00', 1),
-- Meeting 7 — ongoing, tindak lanjut aktif
(7, 'Finalisasi fitur notifikasi real-time',            4,  '2026-06-10', 'high',   'in_progress', NULL,                  2),
(7, 'Setup CI/CD pipeline GitHub Actions',             8,  '2026-06-05', 'high',   'pending',     NULL,                  2),
(7, 'Code review modul tindak lanjut',                 2,  '2026-06-03', 'medium', 'pending',     NULL,                  2),
-- Meeting 8 — mendatang
(8, 'Siapkan slide deck demo produk',                  4,  '2026-05-29', 'high',   'in_progress', NULL,                  2),
(8, 'Rekam video demo backup',                         8,  '2026-05-29', 'medium', 'pending',     NULL,                  2),
-- Meeting 9
(9, 'Siapkan soal tes teknis batch 3',                 9,  '2026-05-30', 'medium', 'pending',     NULL,                  3),
-- Overdue (sengaja dibuat overdue untuk demo)
(7, 'Update dokumentasi API endpoint',                 4,  '2026-05-20', 'low',    'pending',     NULL,                  2),
(8, 'Kirim undangan demo ke manajemen',                3,  '2026-05-25', 'high',   'pending',     NULL,                  1);

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
INSERT INTO notifications (user_id, type, message, link, is_read) VALUES
-- Untuk admin (id=1)
(1, 'meeting_invite',    'Anda diundang ke meeting: Demo Produk ke Manajemen',           '/meetings/8',       1),
(1, 'meeting_invite',    'Anda diundang ke meeting: Rapat Evaluasi Marketing Q2',        '/meetings/10',      0),
(1, 'tindak_lanjut',     'Tindak lanjut baru ditugaskan: Kirim undangan demo ke manajemen', '/tindak-lanjut', 0),
-- Untuk Budi (id=2)
(2, 'meeting_invite',    'Anda diundang ke meeting: Sprint Planning Juni',                '/meetings/7',       1),
(2, 'meeting_invite',    'Anda diundang ke meeting: Demo Produk ke Manajemen',            '/meetings/8',       1),
(2, 'tindak_lanjut',     'Tindak lanjut baru: Code review modul tindak lanjut',           '/tindak-lanjut',    0),
-- Untuk Sari (id=3)
(3, 'meeting_invite',    'Anda diundang ke meeting: Rekrutmen Batch 3 2026',              '/meetings/9',       0),
(3, 'tindak_lanjut',     'Tindak lanjut overdue: Kirim undangan demo ke manajemen',       '/tindak-lanjut',    0),
-- Untuk Andi (id=4)
(4, 'meeting_invite',    'Anda diundang ke meeting: Sprint Planning Juni',                '/meetings/7',       1),
(4, 'meeting_invite',    'Anda diundang ke meeting: Demo Produk ke Manajemen',            '/meetings/8',       0),
(4, 'tindak_lanjut',     'Tindak lanjut baru: Finalisasi fitur notifikasi real-time',     '/tindak-lanjut',    0),
(4, 'tindak_lanjut',     'Tindak lanjut overdue: Update dokumentasi API endpoint',        '/tindak-lanjut',    0),
-- Untuk Rina (id=5)
(5, 'meeting_invite',    'Anda diundang ke meeting: Rekrutmen Batch 3 2026',              '/meetings/9',       0),
(5, 'meeting_invite',    'Anda diundang ke meeting: Town Hall Q2 2026',                   '/meetings/12',      0),
-- Untuk Hendra (id=8)
(8, 'meeting_invite',    'Anda diundang ke meeting: Sprint Planning Juni',                '/meetings/7',       1),
(8, 'tindak_lanjut',     'Tindak lanjut baru: Setup CI/CD pipeline GitHub Actions',       '/tindak-lanjut',    0),
(8, 'tindak_lanjut',     'Tindak lanjut baru: Rekam video demo backup',                  '/tindak-lanjut',    0),
-- Untuk Fitri (id=9)
(9, 'meeting_invite',    'Anda diundang ke meeting: Rekrutmen Batch 3 2026',              '/meetings/9',       0),
(9, 'tindak_lanjut',     'Tindak lanjut baru: Siapkan soal tes teknis batch 3',           '/tindak-lanjut',    0);

-- ============================================================
-- EMAIL QUEUE
-- ============================================================
INSERT INTO email_queue (to_email, subject, body, status, meeting_id, sent_at) VALUES
('andi.p@meetingapp.id',  'Undangan Meeting: Sprint Planning Juni',        '<p>Anda diundang ke meeting Sprint Planning Juni pada 28 Mei 2026.</p>', 'sent',    7,  '2026-05-27 08:00:00'),
('hendra.s@meetingapp.id','Undangan Meeting: Sprint Planning Juni',        '<p>Anda diundang ke meeting Sprint Planning Juni pada 28 Mei 2026.</p>', 'sent',    7,  '2026-05-27 08:00:00'),
('admin@meetingapp.id',   'Undangan Meeting: Demo Produk ke Manajemen',   '<p>Anda diundang ke meeting Demo Produk pada 30 Mei 2026.</p>',         'pending', 8,  NULL),
('budi.s@meetingapp.id',  'Undangan Meeting: Demo Produk ke Manajemen',   '<p>Anda diundang ke meeting Demo Produk pada 30 Mei 2026.</p>',         'failed',  8,  NULL);

-- ============================================================
-- NOTULEN EXPORTS LOG
-- ============================================================
INSERT INTO notulen_exports (meeting_id, exported_by, format, filename) VALUES
(1, 1, 'pdf',  'notulen-kickoff-sima-20250505.pdf'),
(3, 1, 'pdf',  'notulen-evaluasi-kpi-q1-20250520.pdf'),
(3, 2, 'docx', 'notulen-evaluasi-kpi-q1-20250520.docx');

-- ============================================================
-- Selesai. Summary:
-- Users        : 10 (1 admin, 2 sekretaris, 7 peserta)
-- Departments  : 5
-- Meetings     : 15 (6 done, 1 ongoing, 7 scheduled, 1 cancelled)
-- Recurring    : 3
-- Notulen      : 4 meeting
-- Tindak Lanjut: 14 (campuran done/in_progress/pending/overdue)
-- Notifications: 19
-- ============================================================
