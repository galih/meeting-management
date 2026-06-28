<div align="center">

# 🗣️ Wicara

**Aplikasi Manajemen Kegiatan berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![Version](https://img.shields.io/badge/version-2.0.5-f76707?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

</div>

---

## 📖 Tentang Aplikasi

**Wicara** adalah aplikasi manajemen kegiatan lengkap yang dibangun dengan **PHP 8.5 native** tanpa framework — cocok untuk di-deploy di **shared hosting** biasa. Wicara mendukung multi-role (Admin, Sekretaris, Peserta), kalender kegiatan interaktif, editor notulen real-time, lampiran file, kegiatan berulang, manajemen tindak lanjut terintegrasi, dan **log aktivitas lengkap untuk admin**.

---

## ✨ Fitur Utama

### 📝 Notulen
- Editor rich text modern (Quill)
- **Real-time sync** antar pengguna via long polling
- Riwayat versi notulen lengkap
- Hak akses: Admin & Sekretaris bisa edit, Peserta hanya lihat
- Komentar & reply per notulen dengan mention (@user)
- Export notula resmi **DOCX saja**

### 📄 Export Notula Resmi (DOCX)
- Template **Notula resmi** sesuai format surat dinas Kementerian Kebudayaan RI
- **Kop surat** otomatis: logo instansi + nama Kementerian & Inspektorat Jenderal
- Judul **N O T U L A** tebal bergaris bawah, rata tengah
- Font **Times New Roman 12pt**, kertas **A4**, margin standar surat dinas
- Logo di-embed langsung ke file DOCX (base64) — tidak butuh koneksi internet saat membuka

### 🎨 Detail Kegiatan
- Halaman detail kegiatan direbuild dengan **palet warna Kemenbud**: emas, cokelat, krem
- Hero header baru dengan badge status, PIC, waktu, dan lokasi kegiatan
- Kartu ringkasan statistik untuk peserta, tindak lanjut, progres selesai, dan keterlambatan
- Tab **Tindak Lanjut** dan **Peserta** dengan tampilan lebih modern, avatar inisial, status badge, dan mobile-friendly table/card behavior
- Aksi cepat untuk edit kegiatan, ubah status, hapus kegiatan, buka notulen, export DOCX, kirim undangan, dan kirim ringkasan
- Modal tambah tindak lanjut dan ubah status tetap dipertahankan dengan tampilan baru

## 🌐 Daftar Route

| Method | URL | Deskripsi | Role |
|---|---|---|---|
| GET | `/notulen/{id}` | Editor notulen | Auth |
| GET | `/notulen/{id}/history` | Riwayat notulen | Auth |
| GET | `/notulen/{id}/export-docx` | Export notula ke DOCX | Admin/Sekretaris |

## 🎨 Tech Stack

### Backend

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Export DOCX | Custom `DocxExporter.php` | Notula resmi, logo embed base64, Open XML native |

## 📦 Changelog

### v2.0.5 — Meeting Detail Rebuild
- Rebuild halaman `app/views/meetings/show.php` dengan visual baru bernuansa Kementerian Kebudayaan
- Tambah hero section, stat cards, progress bar, empty states, tab peserta/tindak lanjut, dan tombol aksi cepat
- Pertahankan alur PHP existing untuk peserta, tindak lanjut, ubah status, hapus kegiatan, kirim undangan, dan kirim ringkasan
- README diperbarui untuk mendokumentasikan rebuild halaman detail kegiatan

### v2.0.4 — DOCX Only
- Hapus fitur export PDF dari aplikasi
- Hapus route `/notulen/{id}/export-pdf`
- Hapus method `exportPdf()` dari `ExportController`
- Halaman detail kegiatan sekarang hanya menampilkan tombol **Export DOCX**
- README diperbarui agar dokumentasi sesuai perilaku aplikasi
