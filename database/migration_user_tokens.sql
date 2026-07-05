-- Migration: user_tokens table
-- Menggantikan kolom remember_token di tabel users dengan tabel dedicated.
-- Token disimpan sebagai SHA-256 HASH, bukan plaintext.
-- Jalankan sekali pada environment yang belum memiliki tabel ini.
--
-- FIX: user_id menggunakan INT (signed) agar cocok dengan users.id INT
-- (bukan INT UNSIGNED — schema.sql mendefinisikan users.id sebagai plain INT)

CREATE TABLE IF NOT EXISTS `user_tokens` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT             NOT NULL COMMENT 'Sama dengan users.id (INT, bukan UNSIGNED)',
  `token_hash`  CHAR(64)        NOT NULL COMMENT 'SHA-256 hash dari token plaintext di cookie',
  `expires_at`  DATETIME        NOT NULL,
  `created_at`  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_token` (`user_id`),
  KEY `idx_token_hash` (`token_hash`),
  CONSTRAINT `fk_user_tokens_user`
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Opsional: hapus kolom lama dari tabel users jika sudah ada
-- ALTER TABLE `users` DROP COLUMN IF EXISTS `remember_token`;
