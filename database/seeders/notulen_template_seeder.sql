-- Seeder: Template Notula berdasarkan template-notulen.docx
-- Jalankan sekali: mysql -u root -p meeting_db < database/seeders/notulen_template_seeder.sql

INSERT INTO notulen_templates (name, description, content, is_default, created_by)
VALUES (
  'Template Notula Resmi',
  'Template notula rapat resmi dengan kop surat, tabel info rapat, pembahasan, penutup, dan kolom tanda tangan notulis.',
  '<h2 style="text-align:center;"><strong>NOTULA</strong></h2>

<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr>
    <td style="width:180px;">Nama Rapat/Kegiatan</td>
    <td style="width:16px;">:</td>
    <td>{{MEETING_TITLE}}</td>
  </tr>
  <tr>
    <td>Hari, Tanggal</td>
    <td>:</td>
    <td>{{MEETING_DAY}}, {{MEETING_DATE}}</td>
  </tr>
  <tr>
    <td>Pukul</td>
    <td>:</td>
    <td>Pukul {{MEETING_START}} WIB s.d. {{MEETING_END}} WIB</td>
  </tr>
  <tr>
    <td>Tempat</td>
    <td>:</td>
    <td>{{MEETING_LOCATION}}</td>
  </tr>
  <tr>
    <td>Pencatat/Notulis</td>
    <td>:</td>
    <td>{{NOTULIS_NAME}}</td>
  </tr>
  <tr>
    <td>Peserta Rapat</td>
    <td>:</td>
    <td>Terlampir</td>
  </tr>
  <tr>
    <td>Pembahasan</td>
    <td>:</td>
    <td>{{MEETING_TITLE}}</td>
  </tr>
</table>

<p>Rapat dibuka pada pukul {{MEETING_START}} WIB.</p>

<p><strong>Pembukaan oleh ...</strong></p>
<p>(Isi pembukaan rapat)</p>

<p><strong>Penyampaian ...</strong></p>
<p>(Isi penyampaian / agenda utama)</p>

<h3><strong>Penutup Rapat</strong></h3>
<p>Rapat ditutup pada pukul {{MEETING_END}} WIB.</p>

<br><br>
<table style="width:100%;">
  <tr>
    <td style="width:50%;text-align:center;">
      <p><u>Notulis</u></p>
      <br><br><br>
      <p>({{NOTULIS_NAME}})</p>
    </td>
    <td style="width:50%;text-align:center;">
      <p><u>Pimpinan Rapat</u></p>
      <br><br><br>
      <p>(__________________________)</p>
    </td>
  </tr>
</table>

<h3>Dokumentasi</h3>
<p>(Lampirkan foto dokumentasi rapat di sini)</p>',
  1,
  1
);
