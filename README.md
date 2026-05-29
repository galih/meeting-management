<div align="center">

# 📅 Meeting Management App

**Aplikasi manajemen meeting profesional berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![Version](https://img.shields.io/badge/version-1.6.0-f76707?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

</div>

---

## 📖 Tentang Aplikasi

Meeting Management App adalah sistem manajemen meeting lengkap yang dibangun dengan **PHP 8.5 native** tanpa framework — cocok untuk di-deploy di **shared hosting** biasa. Aplikasi ini mendukung multi-role (Admin, Sekretaris, Peserta), kalender meeting interaktif, editor notulen real-time, lampiran file, recurring meeting, manajemen tindak lanjut terintegrasi, dan **log aktivitas lengkap untuk admin**.

---

## ✨ Fitur Utama

### 🔐 Autentikasi
- Login dengan **username** & password
- Remember Me (cookie 30 hari)
- Lupa password via email — cari akun dengan **username**, link reset dikirim ke email terdaftar
- Sistem role: **Admin**, **Sekretaris**, **Peserta**

### 📅 Meeting
- Buat, edit, dan hapus meeting
- Kalender interaktif (FullCalendar v6)
- Tampilan daftar dengan filter status
- Manajemen peserta per meeting
- Ubah status: `scheduled → ongoing → done → cancelled`

### 🔁 Recurring Meeting
- Buat jadwal meeting berulang: **harian, mingguan, dua mingguan, bulanan**
- Generate meeting otomatis sesuai jadwal
- Manajemen peserta recurring terpisah
- Link antara meeting instance dan template recurring

### 📎 Lampiran File
- Upload lampiran per meeting (agenda, notulen, referensi, lainnya)
- Validasi tipe & ukuran file
- Download & hapus lampiran
- Folder penyimpanan: `public/uploads/attachments/`

### 📝 Notulen
- Editor blok modern (Editor.js)
- **Real-time sync** antar pengguna via long polling
- Riwayat versi notulen lengkap
- Hak akses: Admin & Sekretaris bisa edit, Peserta hanya lihat
- Komentar & reply per notulen dengan mention (@user)

### ✅ Tindak Lanjut
- Buat tugas langsung dari halaman meeting / notulen
- Assign ke peserta meeting
- Set deadline & prioritas (High / Medium / Low)
- Update status via AJAX tanpa reload
- Highlight merah jika terlambat

### 🔔 Notifikasi
- Polling otomatis tiap 20 detik
- Badge jumlah notifikasi belum dibaca di navbar
- Tandai semua sudah dibaca dengan satu klik

### 👥 Manajemen User (Admin)
- Tambah, edit, nonaktifkan, dan hapus user
- Set username unik per user
- Pagination & search
- Reset password user

### 🏢 Departemen
- Manajemen departemen/divisi
- Assign user ke departemen
- Filter meeting per departemen

### 🗂️ Log Aktivitas *(v1.6.0 — Admin only)*
- Mencatat semua aktivitas penting secara otomatis: **login, logout, login gagal, buat/ubah/hapus meeting, buat/ubah/hapus user**
- Halaman khusus admin di menu **Administrasi → Log Aktivitas**
- Filter berdasarkan **user**, **modul** (auth / meeting / user), dan **rentang tanggal**
- Badge warna per jenis aksi (hijau = login, biru = dibuat, kuning = diubah, merah = dihapus)
- Pagination 30 entri per halaman
- Fitur **Bersihkan Log** — hapus log lebih dari N hari (default 90 hari)

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

# 3. Jika upgrade dari versi sebelumnya, jalankan juga:
#    database/activity_log_migration.sql

# 4. Buat file konfigurasi
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
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `json`, `fileinfo` |

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
notulen                → id, meeting_id, content (JSON Editor.js), version, created_by, updated_by
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
```

---

## 🏗️ Struktur Direktori

```
meeting-management/
├── install.php                      # Web Installer (hapus setelah install)
├── .htaccess                        # Redirect root → /public
├── database/
│   ├── schema.sql                   # Schema terpadu (semua tabel & relasi)
│   ├── activity_log_migration.sql   # Upgrade only: tabel activity_logs (v1.6.0)
│   ├── sprint1_migration.sql        # Upgrade only: Email Queue & Export Log
│   ├── sprint3_migration.sql        # Upgrade only: Departemen & Komentar
│   └── sprint4_migration.sql        # Upgrade only: Lampiran & Recurring
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
│   │   └── ActivityLog.php          # Helper log aktivitas (v1.6.0)
│   ├── controllers/
│   │   ├── AuthController.php       # Login, logout, reset password
│   │   ├── DashboardController.php
│   │   ├── MeetingController.php
│   │   ├── AttachmentController.php
│   │   ├── RecurringController.php
│   │   ├── NotulisController.php
│   │   ├── TindakLanjutController.php
│   │   ├── UserController.php
│   │   ├── DepartmentController.php
│   │   ├── NotifikasiController.php
│   │   └── ActivityLogController.php  # Log aktivitas (v1.6.0)
│   └── views/
│       ├── layouts/                 # base.php, sidebar.php
│       ├── auth/                    # login, forgot_password, reset_password
│       ├── dashboard/
│       ├── meetings/
│       ├── recurring/
│       ├── notulen/
│       ├── tindak-lanjut/
│       ├── users/
│       ├── departments/
│       ├── notifications/
│       ├── activity-log/            # index.php (v1.6.0)
│       └── errors/                  # 403, 404
└── public/
    ├── .htaccess                    # Front controller + security headers
    ├── index.php                    # Entry point + semua routes
    └── assets/
        ├── css/custom.css           # Override Tabler (tema oranye)
        ├── js/notifications.js      # Polling notifikasi
        └── js/notulen-realtime.js   # Editor.js + long polling
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
| GET | `/meetings` | Daftar & kalender meeting | Auth |
| POST | `/meetings` | Buat meeting baru | Admin/Sekretaris |
| GET | `/meetings/{id}` | Detail meeting + lampiran | Auth |
| POST | `/meetings/{id}/status` | Ubah status meeting | Admin/Sekretaris |
| POST | `/meetings/{id}/delete` | Hapus meeting | Admin |
| POST | `/meetings/{id}/attachments` | Upload lampiran | Admin/Sekretaris |
| GET | `/attachments/{id}/download` | Download lampiran | Auth |
| POST | `/attachments/{id}/delete` | Hapus lampiran | Admin/Sekretaris |
| GET | `/recurring` | Daftar recurring meeting | Auth |
| POST | `/recurring` | Buat recurring meeting | Admin/Sekretaris |
| GET | `/notulen/{id}` | Editor notulen | Auth |
| GET | `/notulen/{id}/history` | Riwayat notulen | Auth |
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
| GET | `/notifications` | Halaman notifikasi | Auth |
| GET | `/api/notifications` | API polling JSON | Auth |
| GET | `/api/meetings/calendar` | API events kalender | Auth |
| **GET** | **`/admin/activity-log`** | **Log aktivitas** | **Admin** |
| **POST** | **`/admin/activity-log/purge`** | **Bersihkan log lama** | **Admin** |

---

## 🎨 Tech Stack

| Layer | Teknologi |
|---|---|
| Backend | PHP 8.5 Native (no framework) |
| Database | MySQL 8.0 + PDO |
| Frontend UI | [Tabler](https://tabler.io) (Bootstrap 5) |
| Kalender | [FullCalendar v6](https://fullcalendar.io) |
| Rich Editor | [Editor.js](https://editorjs.io) |
| Real-time | Long Polling (PHP native) |
| Notifikasi | AJAX Polling (20 detik interval) |
| Hosting | Shared Hosting (Apache + mod_rewrite) |

---

## 🔒 Keamanan

- Password di-hash dengan `password_hash()` (bcrypt, cost 12)
- Login menggunakan **username** (bukan email publik)
- Semua output di-escape dengan `htmlspecialchars()`
- Prepared statements PDO untuk semua query database
- Token reset password expire dalam 1 jam
- File sensitif (`.env`, `.sql`, `.log`) diblokir via `.htaccess`
- `display_errors` dimatikan di production
- Security headers: X-Frame-Options, XSS-Protection, Content-Type-Options
- `install.php` harus dihapus setelah instalasi
- **Log aktivitas** mencatat IP address dan user agent setiap aksi

---

## 📦 Changelog

### v1.6.0 — Log Aktivitas Admin
- Tambah tabel `activity_logs` dengan index optimasi
- Helper `ActivityLog` statis (`record`, `badge`, `paginate`)
- Log otomatis untuk: login berhasil/gagal, logout, buat/ubah/hapus meeting & user
- Halaman `/admin/activity-log` dengan filter multi-dimensi & pagination
- Fitur purge log berdasarkan umur (default 90 hari)
- Submenu **Log Aktivitas** di menu Administrasi (admin only)

### v1.5.0
- Recurring Meeting (harian, mingguan, dua mingguan, bulanan)
- Lampiran file per meeting
- Template notulen

### v1.4.0
- Editor notulen real-time (Editor.js + long polling)
- Komentar & mention di notulen
- Export PDF & DOCX

### v1.3.0
- Manajemen departemen
- Filter meeting per departemen

### v1.2.0
- Tindak lanjut terintegrasi
- Sistem notifikasi polling

### v1.1.0
- Multi-role (Admin, Sekretaris, Peserta)
- Reset password via email

---

## 🤝 Kontribusi

1. Fork repository ini
2. Buat branch fitur: `git checkout -b feat/nama-fitur`
3. Commit perubahan: `git commit -m 'feat: tambah fitur X'`
4. Push ke branch: `git push origin feat/nama-fitur`
5. Buat Pull Request

---

## 📄 Lisensi

Distribusikan di bawah lisensi **MIT**. Lihat file `LICENSE` untuk detail.

---

<div align="center">
Dibuat dengan ❤️ menggunakan PHP 8.5 &amp; Tabler UI
</div>
