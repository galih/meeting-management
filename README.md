# Meeting Management App

Aplikasi Manajemen Meeting berbasis **PHP 8.5 Native** (tanpa framework), **MySQL**, dan **Tabler Core** tema Orange Light.

## Fitur
- 📅 Kalender Kegiatan (FullCalendar v6)
- 📝 Notulen Real-time Collaboration (Editor.js + Long Polling)
- ✅ Tindak Lanjut Hasil Meeting
- 🔔 Notifikasi Pengguna
- 👥 Role Management (Admin / Sekretaris / Peserta)
- 🔐 Auth lengkap (Login, Remember Me, Forgot Password)

## Tech Stack
| Layer | Teknologi |
|---|---|
| Backend | PHP 8.5 Native |
| Database | MySQL 8+ |
| UI Framework | Tabler Core @latest (Orange Light) |
| Rich Editor | Editor.js @latest |
| Kalender | FullCalendar v6 |
| Real-time | Long Polling (shared hosting compatible) |

## Struktur Direktori
```
meeting-management/
├── app/
│   ├── config/
│   ├── core/
│   ├── controllers/
│   ├── models/
│   └── views/
├── public/        ← Document Root
│   ├── index.php
│   └── assets/
├── database/
│   └── schema.sql
├── .htaccess
└── composer.json
```

## Instalasi

```bash
# 1. Clone repo
git clone https://github.com/galih/meeting-management.git
cd meeting-management

# 2. Import database
mysql -u root -p meeting_db < database/schema.sql

# 3. Konfigurasi
cp app/config/database.example.php app/config/database.php
# Edit app/config/database.php sesuai kredensial DB Anda

# 4. Set document root ke folder /public
# 5. Pastikan mod_rewrite aktif
```

## Konfigurasi Web Server
Document root harus diarahkan ke folder `/public`. File `.htaccess` sudah tersedia untuk Apache.

## Default Admin
Setelah import schema.sql, login dengan:
- **Email:** admin@meetingapp.id
- **Password:** Admin@12345

> Segera ganti password setelah login pertama.

---
Dibuat dengan ❤️ menggunakan PHP 8.5 & Tabler
