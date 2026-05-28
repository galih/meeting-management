-- ============================================================
-- Migration: Tabel app_settings untuk fitur upload logo & bg
-- Jalankan SEKALI di MySQL/phpMyAdmin sebelum deploy
-- ============================================================

CREATE TABLE IF NOT EXISTS `app_settings` (
  `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `key`        VARCHAR(100)    NOT NULL,
  `value`      TEXT,
  `updated_at` TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed data default (opsional)
INSERT IGNORE INTO `app_settings` (`key`, `value`) VALUES
  ('app_logo',   ''),
  ('login_bg',   ''),
  ('app_name_custom', '');

-- ============================================================
-- Pastikan folder assets/uploads/ ada dan writable:
--   chmod 755 assets/uploads/
--   chmod 755 assets/uploads/attachments/
-- ============================================================
