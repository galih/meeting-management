<div align="center">

# 🗣️ Wicara

**Aplikasi Manajemen Kegiatan berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![Version](https://img.shields.io/badge/version-2.0.3-f76707?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

</div>

---

## 📖 Tentang Aplikasi

**Wicara** adalah aplikasi manajemen kegiatan lengkap yang dibangun dengan **PHP 8.5 native** tanpa framework — cocok untuk di-deploy di **shared hosting** biasa. Wicara mendukung multi-role (Admin, Sekretaris, Peserta), kalender kegiatan interaktif, editor notulen real-time, lampiran file, kegiatan berulang, manajemen tindak lanjut terintegrasi, dan **log aktivitas lengkap untuk admin**.

---

## ✨ Fitur Utama

### 🔐 Autentikasi
- Login dengan **username** & password
- Remember Me (cookie 30 hari)
- Lupa password via email — cari akun dengan **username**, link reset dikirim ke email terdaftar
- Sistem role: **Admin**, **Sekretaris**, **Peserta**

### 📅 Kegiatan
- Buat, edit, dan hapus kegiatan
- Kalender interaktif (FullCalendar v6) dengan tampilan bulan, minggu, dan agenda
- Tampilan daftar dengan **search real-time**, **filter status**, dan **sort** (terbaru / terlama / A→Z)
- **Stat cards** ringkasan status — klik kartu untuk langsung filter daftar
- Manajemen peserta per kegiatan dengan checkbox pill
- Pilih warna kalender dengan **color preset** atau color picker
- Ubah status: `scheduled → ongoing → done → cancelled`

### 🔁 Kegiatan Berulang
- Buat jadwal kegiatan berulang: **harian, mingguan, dua mingguan, bulanan**
- Generate kegiatan otomatis sesuai jadwal
- Manajemen peserta recurring terpisah
- Link antara kegiatan instance dan template recurring

### 📎 Lampiran File
- Upload lampiran per kegiatan (agenda, notulen, referensi, lainnya)
- Validasi tipe & ukuran file
- Download & hapus lampiran
- Folder penyimpanan: `public/uploads/attachments/`

### 📝 Notulen
- Editor rich text modern (Quill)
- **Real-time sync** antar pengguna via long polling
- Riwayat versi notulen lengkap
- Hak akses: Admin & Sekretaris bisa edit, Peserta hanya lihat
- Komentar & reply per notulen dengan mention (@user)

### ✅ Tindak Lanjut
- Buat tugas langsung dari halaman kegiatan / notulen
- Assign ke peserta kegiatan
- Set deadline & prioritas (High / Medium / Low)
- Update status via AJAX tanpa reload
- Highlight merah jika terlambat

### 🔔 Notifikasi
- Polling otomatis tiap 20 detik
- Badge jumlah notifikasi belum dibaca di navbar
- Tandai semua sudah dibaca dengan satu klik

### 👥 Manajemen User *(Admin)*
- Tambah, edit, nonaktifkan, dan hapus user
- Set username unik per user
- Pagination & search
- Reset password user

### 🏢 Departemen
- Manajemen departemen/divisi dengan struktur hierarki
- Cascade dropdown Unit Kerja → Bidang → Sub Bidang
- Filter kegiatan per departemen

### 🗂️ Log Aktivitas *(Admin only)*
- Mencatat semua aktivitas penting: **login, logout, login gagal, buat/ubah/hapus kegiatan, buat/ubah/hapus user**
- Filter berdasarkan **user**, **modul**, dan **rentang tanggal**
- Badge warna per jenis aksi (hijau = login, biru = dibuat, kuning = diubah, merah = dihapus)
- Pagination 30 entri per halaman
- Fitur **Bersihkan Log** — hapus log lebih dari N hari (default 90 hari)

### 📄 Export Notula Resmi (DOCX)
- Template **Notula resmi** sesuai format surat dinas Kementerian Kebudayaan RI
- **Kop surat** otomatis: logo instansi + nama Kementerian & Inspektorat Jenderal
- Judul **N O T U L A** tebal bergaris bawah, rata tengah
- Font **Times New Roman 12pt**, kertas **A4**, margin standar surat dinas
- Logo di-embed langsung ke file DOCX (base64) — tidak butuh koneksi internet saat membuka

### ⚙️ Pengaturan Aplikasi *(Admin)*
- Upload logo instansi & background login
- Konfigurasi SMTP dengan **test email** langsung dari halaman pengaturan
- Antarmuka tab: **Branding** dan **Email / SMTP**

---

## 🚀 Instalasi via Web Installer

Cara termudah — tidak perlu setup manual sama sekali.

1. **Upload** semua file ke server (via FTP / File Manager cPanel)
2. **Buka browser** → akses `https://domain.com/install.php`
3. Ikuti 4 langkah wizard:
   - **Step 1** — Cek persyaratan sistem & ekstensi PHP
   - **Step 2** — Konfigurasi koneksi database
   - **Step 3** — Nama aplikasi, username/password admin, konfigurasi email
   - **Step 4** — Konfirmasi & eksekusi instalasi
4. **Hapus** `install.php` setelah selesai ✅

> ⚠️ Installer hanya mengimpor `database/schema.sql` (schema terpadu). File `*_migration.sql` hanya untuk upgrade instance lama — **tidak** dipanggil saat fresh install.

---

## 🛠️ Instalasi Manual

### Shared Hosting (FTP / File Manager)

```bash
# 1. Upload semua file ke public_html/ atau subdomain folder

# 2. Import database via phpMyAdmin
#    cPanel → phpMyAdmin → pilih database → Import → database/schema.sql

# 3. Buat file konfigurasi
cp app/config/database.example.php app/config/database.php
```

Edit `app/config/database.php`:

```php
return [
    'host'     => 'localhost',
    'dbname'   => 'nama_database_cpanel',
    'username' => 'user_database_cpanel',
    'password' => 'password_database',
    'charset'  => 'utf8mb4',
];
```

### Local Development

```bash
# 1. Clone repository
git clone https://github.com/galih/meeting-management.git
cd meeting-management

# 2. Import database
mysql -u root -p < database/schema.sql

# 3. Konfigurasi database
cp app/config/database.example.php app/config/database.php

# 4. Jalankan server
php -S localhost:8000 -t public

# 5. Buka browser
open http://localhost:8000
```

---

## ⚙️ Persyaratan Server

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | 8.1+ (disarankan 8.5) |
| MySQL / MariaDB | 8.0+ / 10.4+ |
| Apache | 2.4+ dengan `mod_rewrite` |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `json`, `fileinfo`, `zip` |

> 💡 Ekstensi `zip` diperlukan untuk fitur **Export DOCX**.

---

## 🔐 Akun Default

| Role | Username | Password |
|---|---|---|
| Admin | `admin` | `Admin@12345` |

> ⚠️ **Ganti username dan password default segera setelah login pertama!**
>
> Jika menggunakan Web Installer, username & password ditentukan sendiri di Step 3.

---

## 🗄️ Skema Database

```
users                  → id, username (UNIQUE), name, email, password, role, department_id, is_active
departments            → id, name, code, description, head_id, is_active
meetings               → id, title, description, location, start_datetime, end_datetime, status, color, department_id, recurring_id, created_by
meeting_participants   → id, meeting_id, user_id, status
meeting_attendances    → id, meeting_id, user_id, status, note
meeting_attachments    → id, meeting_id, uploaded_by, filename, stored_name, mime_type, file_size, category
notulen                → id, meeting_id, content (HTML Quill), version, created_by, updated_by
notulen_history        → id, meeting_id, content, version, edited_by
notulen_comments       → id, meeting_id, parent_id, user_id, content, is_resolved
comment_mentions       → id, comment_id, user_id
tindak_lanjut          → id, meeting_id, description, assigned_to, due_date, priority, status, created_by
recurring_meetings     → id, title, frequency, day_of_week, start_time, end_time, start_date, end_date, department_id, created_by
recurring_participants → id, recurring_id, user_id
email_queue            → id, to_email, subject, body, status, attempts, meeting_id
notulen_exports        → id, meeting_id, exported_by, format, filename
notifications          → id, user_id, type, message, url, is_read
activity_logs          → id, user_id, user_name, user_role, action, description, subject_type, subject_id, ip_address, user_agent, created_at
app_settings           → id, key, value, updated_at
```

---

## 🏗️ Struktur Direktori

```
meeting-management/
├── install.php                      # Web Installer (hapus setelah install)
├── .htaccess                        # Redirect root → /public
├── database/
│   ├── schema.sql                   # Schema terpadu (semua tabel & relasi)
│   └── *_migration.sql              # Upgrade only (tidak dipakai saat fresh install)
├── app/
│   ├── config/
│   │   ├── app.php                  # Konfigurasi aplikasi
│   │   ├── database.php             # Konfigurasi PDO MySQL
│   │   └── mail.php                 # Konfigurasi email (opsional)
│   ├── core/
│   │   ├── Database.php             # PDO Singleton
│   │   ├── Router.php               # Custom router dengan {param}
│   │   ├── Auth.php                 # Session auth + role check
│   │   ├── View.php                 # Template renderer
│   │   ├── Notification.php         # Helper notifikasi
│   │   ├── Mailer.php               # Wrapper PHPMailer + fallback mail()
│   │   ├── EmailTemplate.php        # Template HTML email
│   │   ├── PdfExporter.php          # Export PDF via mPDF + fallback HTML
│   │   ├── DocxExporter.php         # Export DOCX notula resmi (Open XML, logo embed)
│   │   ├── ErrorHandler.php         # Error handler (kompatibel PHP 8.4+)
│   │   └── ActivityLog.php          # Helper log aktivitas
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── MeetingController.php
│   │   ├── AttachmentController.php
│   │   ├── RecurringController.php
│   │   ├── NotulisController.php
│   │   ├── TindakLanjutController.php
│   │   ├── UserController.php
│   │   ├── DepartmentController.php
│   │   ├── NotifikasiController.php
│   │   ├── SettingController.php
│   │   └── ActivityLogController.php
│   └── views/
│       ├── layouts/
│       ├── auth/
│       ├── dashboard/
│       ├── meetings/
│       ├── recurring/
│       ├── notulen/
│       ├── tindak-lanjut/
│       ├── users/
│       ├── departments/
│       ├── settings/
│       ├── notifications/
│       ├── activity-log/
│       └── errors/
└── assets/
    ├── css/
    ├── js/
    └── uploads/                     # Logo & background login
```

---

## 🌐 Daftar Route

| Method | URL | Deskripsi | Role |
|---|---|---|---|
| GET/POST | `/login` | Halaman login | Public |
| GET/POST | `/forgot-password` | Lupa password | Public |
| GET/POST | `/reset-password` | Reset password via token | Public |
| GET | `/logout` | Logout | Auth |
| GET | `/` | Dashboard | Auth |
| GET | `/meetings` | Daftar & kalender kegiatan | Auth |
| POST | `/meetings` | Buat kegiatan baru | Admin/Sekretaris |
| GET | `/meetings/{id}` | Detail kegiatan + lampiran | Auth |
| POST | `/meetings/{id}/status` | Ubah status kegiatan | Admin/Sekretaris |
| POST | `/meetings/{id}/delete` | Hapus kegiatan | Admin |
| POST | `/meetings/{id}/attachments` | Upload lampiran | Admin/Sekretaris |
| GET | `/attachments/{id}/download` | Download lampiran | Auth |
| POST | `/attachments/{id}/delete` | Hapus lampiran | Admin/Sekretaris |
| GET | `/recurring` | Daftar kegiatan berulang | Auth |
| POST | `/recurring` | Buat kegiatan berulang | Admin/Sekretaris |
| GET | `/notulen/{id}` | Editor notulen | Auth |
| GET | `/notulen/{id}/history` | Riwayat notulen | Auth |
| GET | `/notulen/{id}/export/docx` | Export notula ke DOCX | Admin/Sekretaris |
| GET | `/notulen/{id}/export/pdf` | Export notulen ke PDF | Admin/Sekretaris |
| GET | `/tindak-lanjut` | Daftar tindak lanjut | Auth |
| POST | `/tindak-lanjut` | Buat tindak lanjut | Admin/Sekretaris |
| POST | `/tindak-lanjut/{id}/status` | Update status | Auth |
| GET | `/users` | Daftar user | Admin |
| POST | `/users` | Tambah user | Admin |
| POST | `/users/{id}/update` | Update user | Admin |
| POST | `/users/{id}/delete` | Nonaktifkan user | Admin |
| POST | `/users/{id}/destroy` | Hapus user permanen | Admin |
| GET | `/departments` | Daftar departemen | Admin |
| GET | `/settings` | Pengaturan aplikasi | Admin |
| POST | `/settings/logo` | Upload logo instansi | Admin |
| POST | `/settings/logo/remove` | Hapus logo | Admin |
| POST | `/settings/login-bg` | Upload background login | Admin |
| POST | `/settings/smtp` | Simpan konfigurasi SMTP | Admin |
| GET | `/notifications` | Halaman notifikasi | Auth |
| GET | `/api/notifications` | API polling JSON | Auth |
| GET | `/api/meetings/calendar` | API events kalender | Auth |
| GET | `/api/departments/children` | API cascade departemen | Auth |
| GET | `/admin/activity-log` | Log aktivitas | Admin |
| POST | `/admin/activity-log/purge` | Bersihkan log lama | Admin |

---

## 🎨 Tech Stack

### Backend

| Komponen | Teknologi | Keterangan |
|---|---|---|
| Language | PHP 8.5 | Native, tanpa framework |
| Database | MySQL 8.0 + PDO | Query via Prepared Statements |
| Router | Custom `Router.php` | Mendukung parameter `{id}` |
| Auth | Custom `Auth.php` | Session-based + Remember Me |
| Email | [PHPMailer](https://github.com/PHPMailer/PHPMailer) | SMTP / fallback ke `mail()` |
| Export PDF | [mPDF](https://mpdf.github.io) | Fallback ke HTML print |
| Export DOCX | Custom `DocxExporter.php` | Notula resmi, logo embed base64, Open XML native |
| Log Aktivitas | Custom `ActivityLog.php` | Statis helper, disimpan ke DB |
| Hosting | Apache 2.4+ | `mod_rewrite` + `.htaccess` |

### Frontend

| Komponen | Teknologi | Keterangan |
|---|---|---|
| UI Framework | [Tabler](https://tabler.io) | Berbasis Bootstrap 5 |
| Kalender | [FullCalendar v6](https://fullcalendar.io) | Tampilan bulan, minggu & agenda |
| Rich Text Editor | [Quill](https://quilljs.com) | Editor notulen WYSIWYG |
| Real-time Notulen | Long Polling (PHP native) | Sync antar pengguna |
| Notifikasi | AJAX Polling | Interval 20 detik |
| Ikon | SVG Inline (Tabler Icons) | Tanpa icon font eksternal |

---

## 🔒 Keamanan

- Password di-hash dengan `password_hash()` (bcrypt, cost 12)
- Login menggunakan **username** (bukan email publik)
- Semua output di-escape dengan `htmlspecialchars()`
- Prepared statements PDO untuk semua query database
- CSRF token pada setiap form POST
- Token reset password expire dalam 1 jam
- File sensitif (`.env`, `.sql`, `.log`) diblokir via `.htaccess`
- `display_errors` dimatikan di production
- Security headers: X-Frame-Options, XSS-Protection, Content-Type-Options
- `install.php` harus dihapus setelah instalasi
- **Log aktivitas** mencatat IP address dan user agent setiap aksi

---

## 📦 Changelog

### v2.0.3 — UI/UX Rebuild

**Perbaikan Kompatibilitas**
- `ErrorHandler.php`: hapus referensi `E_STRICT` yang telah dihapus di PHP 8.4+

**Halaman Kegiatan (`/meetings`)**
- Hero header dengan gradien merah marun, konsisten dengan halaman lain
- **Stat cards** ringkasan status (Total / Terjadwal / Berlangsung / Selesai / Dibatalkan) — klik kartu untuk filter daftar otomatis
- Flash toast fixed kanan atas dengan auto-dismiss 4 detik (menggantikan alert Bootstrap)
- Toolbar daftar: search real-time + tombol clear, filter status, dan sort (Terbaru / Terlama / A→Z)
- Judul kegiatan di tabel menjadi **link langsung** ke halaman detail
- Modal buat kegiatan: peserta via **checkbox pill**, **7 color preset swatch**, auto-fill waktu selesai +1 jam
- Modal hapus: tampilan lebih bersih dengan deskripsi dampak penghapusan
- Seluruh class menggunakan namespace `mi-*` — tanpa ketergantungan Bootstrap

**Halaman Pengaturan (`/settings`)**
- Layout tabbed: tab **Branding** (logo + background login) dan **Email / SMTP**
- Kartu branding sejajar dengan preview dashed box
- SMTP: field dikelompokkan per baris logis, toggle password (eye/eye-slash), guide cards grid
- Namespace `st-*` — tanpa ketergantungan Bootstrap

**Halaman Log Aktivitas (`/admin/activity-log`)**
- Hero banner + stat cards
- Timeline dengan avatar inisial, ikon aksi berwarna, pagination first/last
- Modal purge custom tanpa Bootstrap

**Halaman Departemen (`/departments`)**
- Hero banner, tree view collapse/expand, stats cards, modal preview level

**Halaman Error**
- `404.php` dan `403.php`: standalone HTML dengan card design, tidak bergantung layout utama

### v1.1.0 — Template Notula Resmi
- **Export DOCX**: template diperbarui sesuai format notula surat dinas Kementerian Kebudayaan RI
- Kop surat otomatis dengan logo instansi ter-embed langsung di file (base64)
- Judul *N O T U L A* tebal bergaris bawah, font Times New Roman 12pt, kertas A4
- Baris info rapat: Nama rapat, Hari/Tanggal, Pukul, Tempat, Pemimpin rapat
- Daftar peserta bernomor + unit kerja
- Seksi Simpulan dan tabel Tindak Lanjut
- Blok tanda tangan dua kolom: Mengetahui & Notulis
- Fallback `[ LOGO ]` jika logo belum diupload di Pengaturan

### v1.0.0 — Rilis Perdana
- Multi-role: Admin, Sekretaris, Peserta
- Login dengan username, Remember Me, reset password via email (PHPMailer)
- Manajemen kegiatan: buat, edit, hapus, ubah status
- Kalender interaktif (FullCalendar v6)
- Kegiatan berulang: harian, mingguan, dua mingguan, bulanan
- Lampiran file per kegiatan
- Editor notulen real-time (Quill + long polling) dengan riwayat versi
- Komentar & mention (@user) di notulen
- Export notulen ke PDF (mPDF) & DOCX (native Open XML)
- Tindak lanjut terintegrasi: assign, deadline, prioritas, update status
- Notifikasi polling otomatis (interval 20 detik)
- Manajemen user & departemen
- Pengaturan: logo, background login, konfigurasi SMTP
- Log aktivitas admin dengan filter & purge
- Web Installer 4 langkah

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur: `git checkout -b feat/nama-fitur`
3. Commit perubahan: `git commit -m 'feat: tambah fitur X'`
4. Push ke branch: `git push origin feat/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Didistribusikan di bawah lisensi **MIT**. Lihat file `LICENSE` untuk detail.

---

<div align="center">
Dibuat dengan ❤️ menggunakan PHP 8.5 &amp; Tabler UI
</div>
