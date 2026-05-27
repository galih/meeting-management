<?php
// Salin file ini menjadi mail.php dan sesuaikan konfigurasi
// JANGAN commit mail.php ke repository!
return [
    // 'mail' = gunakan fungsi mail() PHP (shared hosting biasa)
    // 'smtp' = gunakan SMTP (Gmail, Mailgun, Mailtrap, dll)
    'driver'      => 'smtp',

    // Email pengirim
    'from_email'  => 'noreply@domain.com',
    'from_name'   => 'Meeting Management App',

    // Konfigurasi SMTP (hanya jika driver = 'smtp')
    'smtp_host'   => 'smtp.gmail.com',
    'smtp_port'   => 587,
    'smtp_secure' => 'tls',       // 'tls' atau 'ssl'
    'smtp_user'   => 'email@gmail.com',
    'smtp_pass'   => 'app-password-gmail',

    // Contoh konfigurasi Mailtrap (untuk testing)
    // 'smtp_host'   => 'sandbox.smtp.mailtrap.io',
    // 'smtp_port'   => 2525,
    // 'smtp_user'   => 'xxx',
    // 'smtp_pass'   => 'xxx',
];
