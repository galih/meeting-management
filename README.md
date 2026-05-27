<div align="center">

# 📅 Meeting Management App

**Aplikasi manajemen meeting profesional berbasis PHP 8.5 native**

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tabler](https://img.shields.io/badge/Tabler-UI-0054A6?style=flat-square)
![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)

</div>

---

## 📖 Tentang Aplikasi

Meeting Management App adalah sistem manajemen meeting lengkap yang dibangun dengan **PHP 8.5 native** tanpa framework — cocok untuk di-deploy di **shared hosting** biasa. Aplikasi ini mendukung multi-role (Admin, Sekretaris, Peserta), kalender meeting interaktif, editor notulen real-time, dan manajemen tindak lanjut terintegrasi.

---

## ✨ Fitur Utama

### 🔐 Autentikasi
- Login dengan email & password
- Remember Me (cookie 30 hari)
- Lupa password via email (reset token)
- Sistem role: **Admin**, **Sekretaris**, **Peserta**

### 📅 Meeting
- Buat, edit, dan hapus meeting
- Kalender interaktif (FullCalendar v6)
- Tampilan daftar dengan filter status
- Manajemen peserta per meeting
- Ubah status: `scheduled → ongoing → completed → cancelled`

### 📝 Notulen
- Editor blok modern (Editor.js)
- **Real-time sync** antar pengguna via long polling
- Riwayat versi notulen lengkap
- Hak akses: Admin & Sekretaris bisa edit, Peserta hanya bisa lihat

### ✅ Tindak Lanjut
- Buat tugas langsung dari halaman meeting / notulen
- Assign ke peserta meeting
- Set deadline & prioritas (High / Medium / Low)
- Update status via AJAX tanpa reload
- Notifikasi otomatis saat mendekati deadline
- Highlight merah jika terlambat

### 🔔 Notifikasi
- Polling otomatis tiap 20 detik
- Badge jumlah notifikasi belum dibaca di navbar
- Tandai semua sudah dibaca dengan satu klik
- Halaman daftar semua notifikasi

### 👥 Manajemen User (Admin)
- Tambah, edit, nonaktifkan user
- Pagination & search
- Reset password user

---

## 🏗️ Struktur Direktori

```
meeting-management/
├── .htaccess                    # Redirect root → /public
├── database/
│   └── schema.sql               # Skema DB + data default
├── app/
│   ├── config/
│   │   ├── app.php              # Konfigurasi aplikasi
│   │   └── database.php         # Konfigurasi PDO MySQL (buat manual)
│   ├── core/
│   │   ├── Database.php         # PDO Singleton
│   │   ├── Router.php           # Custom router dengan {param}
│   │   ├── Auth.php             # Session auth + role check
│   │   ├── View.php             # Template renderer
│   │   └── Notification.php     # Helper notifikasi
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── MeetingController.php
│   │   ├── NotulisController.php
│   │   ├── TindakLanjutController.php
│   │   ├── UserController.php
│   │   └── NotifikasiController.php
│   └── views/
│       ├── layouts/             # base.php, sidebar.php
│       ├── auth/                # login, forgot, reset password
│       ├── dashboard/
│       ├── meetings/            # index (kalender+list), show
│       ├── notulen/             # editor, history
│       ├── tindak-lanjut/
│       ├── users/
│       ├── notifications/
│       └── errors/              # 403, 404
└── public/
    ├── .htaccess                # Front controller + security
    ├── index.php                # Entry point + semua routes
    └── assets/
        ├── css/custom.css       # Override Tabler (tema oranye)
        ├── js/notifications.js  # Polling notifikasi
        └── js/notulen-realtime.js # Editor.js + long polling
```

---

## ⚙️ Persyaratan Server

| Kebutuhan | Versi Minimum |
|---|---|
| PHP | 8.1+ (disarankan 8.5) |
| MySQL / MariaDB | 8.0+ / 10.4+ |
| Apache | 2.4+ dengan `mod_rewrite` |
| Ekstensi PHP | `pdo_mysql`, `mbstring`, `openssl`, `json` |

---

## 🚀 Instalasi

### Opsi A — Shared Hosting (FTP / File Manager)

```bash
# 1. Upload semua file ke public_html/ atau subdomain folder
#    via FTP (FileZilla) atau File Manager cPanel

# 2. Import database
#    cPanel → phpMyAdmin → pilih database → Import → schema.sql

# 3. Buat file konfigurasi database
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

### Opsi B — Local Development

```bash
# 1. Clone repository
git clone https://github.com/galih/meeting-management.git
cd meeting-management

# 2. Import database
mysql -u root -p < database/schema.sql

# 3. Konfigurasi database
cp app/config/database.example.php app/config/database.php
# Edit sesuai konfigurasi lokal Anda

# 4. Jalankan dengan PHP built-in server
php -S localhost:8000 -t public

# 5. Buka browser
open http://localhost:8000
```

---

## 🔐 Akun Default

| Role | Email | Password |
|---|---|---|
| Admin | `admin@meetingapp.id` | `Admin@12345` |

> ⚠️ **Ganti password default segera setelah login pertama!**

---

## 🗄️ Skema Database

```
roles              → id, name (admin/sekretaris/peserta)
users              → id, name, email, password, role_id, is_active
password_resets    → email, token, expired_at
meetings           → id, title, description, location, start_datetime, end_datetime, status, created_by
meeting_participants → meeting_id, user_id, status (invited/accepted/attended/declined)
notulen            → id, meeting_id, content (JSON Editor.js), updated_by, updated_at
notulen_history    → id, meeting_id, content, edited_by, edited_at
tindak_lanjut      → id, meeting_id, deskripsi, assigned_to, deadline, priority, status, created_by
notifications      → id, user_id, type, title, message, data, is_read, created_at
```

---

## 🌐 Daftar Route

| Method | URL | Deskripsi | Role |
|---|---|---|---|
| GET/POST | `/login` | Halaman login | Public |
| GET/POST | `/forgot-password` | Lupa password | Public |
| GET/POST | `/reset-password` | Reset password | Public |
| GET | `/logout` | Logout | Auth |
| GET | `/` | Dashboard | Auth |
| GET | `/meetings` | Daftar & kalender meeting | Auth |
| POST | `/meetings` | Buat meeting baru | Admin/Sekretaris |
| GET | `/meetings/{id}` | Detail meeting | Auth |
| POST | `/meetings/{id}/status` | Ubah status meeting | Admin/Sekretaris |
| GET | `/notulen/{meetingId}` | Editor notulen | Auth |
| POST | `/notulen/{meetingId}/save` | Simpan notulen | Admin/Sekretaris |
| GET | `/notulen/{meetingId}/sync` | Long polling sync | Auth |
| GET | `/notulen/{meetingId}/history` | Riwayat notulen | Admin/Sekretaris |
| GET | `/tindak-lanjut` | Daftar tindak lanjut | Auth |
| POST | `/tindak-lanjut` | Buat tindak lanjut | Admin/Sekretaris |
| POST | `/tindak-lanjut/{id}/status` | Update status | Auth |
| POST | `/tindak-lanjut/{id}/delete` | Hapus tindak lanjut | Admin/Sekretaris |
| GET | `/notifications` | Halaman notifikasi | Auth |
| GET | `/api/notifications` | API polling JSON | Auth |
| POST | `/notifications/read-all` | Tandai semua dibaca | Auth |
| GET | `/api/meetings/calendar` | API events kalender | Auth |
| GET | `/users` | Daftar user | Admin |
| POST | `/users` | Tambah user | Admin |
| POST | `/users/{id}/update` | Update user | Admin |
| POST | `/users/{id}/delete` | Nonaktifkan user | Admin |

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

- Password di-hash dengan `password_hash()` (bcrypt)
- Semua output di-escape dengan `htmlspecialchars()`
- Prepared statements PDO untuk semua query database
- Token CSRF tersedia di session
- File sensitif (`.env`, `.sql`, `.log`) diblokir via `.htaccess`
- `display_errors` dimatikan di production
- Security headers: X-Frame-Options, XSS-Protection, Content-Type-Options

---

## 📱 Screenshot

> Dashboard adaptif berdasarkan role, kalender meeting interaktif, editor notulen real-time, dan manajemen tindak lanjut dengan highlight deadline.

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
