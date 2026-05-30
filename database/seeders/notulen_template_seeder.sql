-- Seeder: Template Notula Resmi dengan kop surat per template (Opsi B)
-- Jalankan: mysql -u root -p nama_db < database/seeders/notulen_template_seeder.sql

INSERT INTO notulen_templates (name, description, letterhead_html, content, is_default, created_by)
VALUES (
  'Template Notula Resmi',
  'Template notula rapat resmi dengan kop surat, tabel info rapat, pembahasan, penutup, dan kolom tanda tangan.',

  -- Kop surat (edit bagian nama instansi & alamat sesuai kebutuhan)
  '<table style="width:100%;border-collapse:collapse;border-bottom:3px double #222;padding-bottom:10px;margin-bottom:8px;">
  <tr>
    <td style="width:90px;text-align:center;vertical-align:middle;">
      <!-- Ganti src dengan URL logo instansi Anda -->
      <!-- <img src="/uploads/logo.png" style="height:72px;max-width:100px;object-fit:contain;"> -->
    </td>
    <td style="text-align:center;vertical-align:middle;padding:0 8px;">
      <p style="font-size:15pt;font-weight:bold;text-transform:uppercase;margin:0;">NAMA INSTANSI / LEMBAGA</p>
      <p style="font-size:8.5pt;color:#333;margin:2px 0 0 0;">Jl. Alamat Instansi No. 1, Kota &nbsp;|&nbsp; Telp. (021) 000-0000</p>
    </td>
    <td style="width:90px;"></td>
  </tr>
</table>',

  -- Konten notula
  '<h2 style="text-align:center;"><strong>NOTULA</strong></h2>

<table style="width:100%;border-collapse:collapse;margin-bottom:16px;">
  <tr><td style="width:175px;">Nama Rapat/Kegiatan</td><td style="width:14px;">:</td><td>{{MEETING_TITLE}}</td></tr>
  <tr><td>Hari, Tanggal</td><td>:</td><td>{{MEETING_DAY}}, {{MEETING_DATE}}</td></tr>
  <tr><td>Pukul</td><td>:</td><td>Pukul {{MEETING_START}} WIB s.d. {{MEETING_END}} WIB</td></tr>
  <tr><td>Tempat</td><td>:</td><td>{{MEETING_LOCATION}}</td></tr>
  <tr><td>Pencatat/Notulis</td><td>:</td><td>{{NOTULIS_NAME}}</td></tr>
  <tr><td>Peserta Rapat</td><td>:</td><td>Terlampir</td></tr>
  <tr><td>Pembahasan</td><td>:</td><td>{{MEETING_TITLE}}</td></tr>
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
      <p><u>Notulis</u></p><br><br><br>
      <p>({{NOTULIS_NAME}})</p>
    </td>
    <td style="width:50%;text-align:center;">
      <p><u>Pimpinan Rapat</u></p><br><br><br>
      <p>(__________________________)</p>
    </td>
  </tr>
</table>

<h3>Dokumentasi</h3>
<p>(Lampirkan foto dokumentasi rapat di sini)</p>',

  1, 1
);
