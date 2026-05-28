-- Migration: Tabel notulen_templates
CREATE TABLE IF NOT EXISTS `notulen_templates` (
  `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
  `name`        VARCHAR(150)     NOT NULL COMMENT 'Nama template',
  `description` VARCHAR(255)         NULL COMMENT 'Deskripsi singkat',
  `content`     LONGTEXT         NOT NULL COMMENT 'Konten HTML template',
  `is_default`  TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = default untuk semua meeting baru',
  `created_by`  INT UNSIGNED         NULL,
  `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME             NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_is_default` (`is_default`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed 3 template bawaan
INSERT INTO `notulen_templates` (`name`, `description`, `content`, `is_default`) VALUES
(
  'Template Rapat Umum',
  'Template standar untuk rapat umum internal',
  '<h2>Notulen Rapat</h2>
<p><strong>Hari/Tanggal&nbsp;:</strong> _______________</p>
<p><strong>Waktu&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<p><strong>Tempat&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<p><strong>Pimpinan&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<hr>
<h3>Peserta Hadir</h3>
<ol><li>_______________</li><li>_______________</li></ol>
<hr>
<h3>Agenda Rapat</h3>
<ol><li>_______________</li></ol>
<hr>
<h3>Jalannya Rapat</h3>
<h4>1. Pembukaan</h4>
<p>Rapat dibuka oleh _______________ pada pukul _______________.</p>
<h4>2. Pembahasan</h4>
<p>_______________</p>
<h4>3. Penutup</h4>
<p>Rapat ditutup pada pukul _______________.</p>
<hr>
<h3>Kesimpulan &amp; Tindak Lanjut</h3>
<ol><li>_______________</li></ol>
<hr>
<p style="text-align:right;"><strong>Notulis,</strong><br><br><br>_______________</p>',
  1
),
(
  'Template Evaluasi Proyek',
  'Template untuk rapat evaluasi kemajuan proyek',
  '<h2>Notulen Rapat Evaluasi Proyek</h2>
<p><strong>Proyek&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<p><strong>Tanggal&nbsp;&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<p><strong>Peserta&nbsp;&nbsp;&nbsp;&nbsp;:</strong> _______________</p>
<hr>
<h3>Status Proyek Saat Ini</h3>
<table style="width:100%;border-collapse:collapse;">
  <thead><tr style="background:#f1f3f5;">
    <th style="border:1px solid #dee2e6;padding:8px;">Item</th>
    <th style="border:1px solid #dee2e6;padding:8px;">Target</th>
    <th style="border:1px solid #dee2e6;padding:8px;">Realisasi</th>
    <th style="border:1px solid #dee2e6;padding:8px;">Keterangan</th>
  </tr></thead>
  <tbody><tr>
    <td style="border:1px solid #dee2e6;padding:8px;">___</td>
    <td style="border:1px solid #dee2e6;padding:8px;">___</td>
    <td style="border:1px solid #dee2e6;padding:8px;">___</td>
    <td style="border:1px solid #dee2e6;padding:8px;">___</td>
  </tr></tbody>
</table>
<h3>Kendala &amp; Risiko</h3>
<ul><li>_______________</li></ul>
<h3>Rencana Tindak Lanjut</h3>
<ol><li>_______________</li></ol>',
  0
),
(
  'Template Standup Harian',
  'Template singkat untuk daily standup / scrum meeting',
  '<h2>Daily Standup — <span style="color:#f76707;">_______________</span></h2>
<hr>
<h3>🟢 Kemarin (Selesai)</h3>
<ul><li>_______________</li></ul>
<h3>🔵 Hari Ini (Akan Dikerjakan)</h3>
<ul><li>_______________</li></ul>
<h3>🔴 Hambatan</h3>
<ul><li>Tidak ada hambatan.</li></ul>',
  0
);
