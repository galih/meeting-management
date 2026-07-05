<div align="center">

# 🗣️ Wicara

**Aplikasi Manajemen Kegiatan berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![Version](https://img.shields.io/badge/version-2.1.0-f76707?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

</div>

---

## 📖 Tentang Aplikasi

**Wicara** adalah aplikasi manajemen kegiatan lengkap yang dibangun dengan **PHP 8.5 native** tanpa framework — cocok untuk di-deploy di **shared hosting** biasa. Wicara mendukung multi-role (Admin, Sekretaris, Peserta), kalender kegiatan interaktif, editor notulen real-time, lampiran file, kegiatan berulang, manajemen tindak lanjut terintegrasi, **manajemen dokumen terpusat**, dan **log aktivitas lengkap untuk admin**.

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

### 🗂️ Manajemen Dokumen
- Struktur **folder & file** per kegiatan atau mandiri
- Upload file multi-format dengan validasi tipe & ukuran
- **Toggle tampilan Grid / List** untuk daftar file dan folder
- **Preview file** inline via modal (gambar, PDF, dll.)
- **Rename inline** nama file langsung dari tabel
- **Share file ke user** internal dengan permission **View** atau **Download**, disertai notifikasi
- **Share folder** ke user lain dengan hak akses terkontrol
- **Hapus folder** oleh Admin atau pembuat folder
- **Link Publik** berbasis token untuk akses anonim, dilengkapi:
  - Password opsional
  - Tanggal kedaluwarsa (expiry)
  - Batas jumlah download
- **Riwayat Versi** file: upload revisi baru tanpa menghapus versi lama, modal riwayat versi tersedia
- **Tag & Kategori** dokumen: model, controller, modal UI, routes, dan migration SQL tersedia
- UI halaman dokumen di-rebuild dengan UX modern berbasis Tabler

### 🎨 Detail Kegiatan
- Halaman detail kegiatan direbuild dengan **palet warna Kemenbud**: emas, cokelat, krem
- Hero header baru dengan badge status, PIC, waktu, dan lokasi kegiatan
- Kartu ringkasan statistik untuk peserta, tindak lanjut, progres selesai, dan keterlambatan
- Tab **Tindak Lanjut** dan **Peserta** dengan tampilan lebih modern, avatar inisial, status badge, dan mobile-friendly table/card behavior
- Aksi cepat untuk edit kegiatan, ubah status, hapus kegiatan, buka notulen, export DOCX, kirim undangan, dan kirim ringkasan
- Modal tambah tindak lanjut dan ubah status tetap dipertahankan dengan tampilan baru

---

## 🌐 Daftar Route

| Method | URL | Deskripsi | Role |
|---|---|---|---|
| GET | `/notulen/{id}` | Editor notulen | Auth |
| GET | `/notulen/{id}/history` | Riwayat notulen | Auth |
| GET | `/notulen/{id}/export-docx` | Export notula ke DOCX | Admin/Sekretaris |
| GET | `/dokumen` | Daftar folder & file | Auth |
| POST | `/dokumen/upload` | Upload file baru | Auth |
| POST | `/dokumen/folder/create` | Buat folder baru | Auth |
| POST | `/dokumen/folder/share` | Share folder ke user | Auth |
| POST | `/dokumen/folder/delete` | Hapus folder | Admin/Pembuat |
| POST | `/dokumen/share` | Share file ke user | Auth |
| GET | `/dokumen/preview/{id}` | Preview file (internal) | Auth |
| GET | `/dokumen/public/{token}` | Preview/download publik via token | Public |
| POST | `/dokumen/public-link` | Buat link publik (token, password, expiry) | Auth |
| GET | `/dokumen/{id}/versions` | Riwayat versi file | Auth |
| POST | `/dokumen/{id}/upload-revision` | Upload revisi file | Auth |
| POST | `/dokumen/{id}/rename` | Rename file inline | Auth |
| POST | `/dokumen/tag` | Tambah/edit tag & kategori | Auth |

---

## 🎨 Tech Stack

### Backend

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Export DOCX | Custom `DocxExporter.php` | Notula resmi, logo embed base64, Open XML native |
| Manajemen Dokumen | `DokumenController.php` | CRUD file/folder, share, versioning, public link |
| Public Share | Token-based (`share_tokens` table) | Password, expiry, download limit, anonymous access |
| Tag & Kategori | `TagController.php` + migration SQL | Kategorisasi dokumen dengan label bebas |

---

## 📦 Changelog

### v2.1.0 — Modul Dokumen Lengkap
- Rebuild UI dan UX halaman dokumen dengan Tabler modern
- Tambah toggle tampilan **Grid / List** fungsional
- Tambah **preview file** inline via modal (Fase 3)
- Tambah **share file** ke user internal dengan permission view/download + notifikasi (Fase 2)
- Tambah **share folder** ke user lain beserta modal dan JS module
- Tambah hak **hapus folder** oleh Admin atau pembuat
- Tambah **Link Publik** berbasis token: akses anonim, password, expiry, download limit (Fase 6)
- Tambah `previewPublic()` di `DokumenController` dan tombol Link Publik di preview modal
- Tambah **Riwayat Versi** file: upload revisi, routes, modal UI (Fase 4)
- Tambah **Tag & Kategori**: model, controller, modal UI, routes, migration SQL (Fase 5)
- README diperbarui untuk mendokumentasikan seluruh fitur modul Dokumen

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
