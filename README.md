<div align="center">

# 🗣️ Wicara

**Aplikasi Manajemen Kegiatan berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![Version](https://img.shields.io/badge/version-2.0.4-f76707?style=flat-square)
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

### v2.0.4 — DOCX Only
- Hapus fitur export PDF dari aplikasi
- Hapus route `/notulen/{id}/export-pdf`
- Hapus method `exportPdf()` dari `ExportController`
- Halaman detail kegiatan sekarang hanya menampilkan tombol **Export DOCX**
- README diperbarui agar dokumentasi sesuai perilaku aplikasi
