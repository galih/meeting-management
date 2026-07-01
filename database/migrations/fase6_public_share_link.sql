-- ============================================================
-- Fase 6: Public Share Link untuk Dokumen
-- ============================================================

CREATE TABLE IF NOT EXISTS `dokumen_public_links` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `file_id`        INT UNSIGNED NOT NULL,
  `token`          VARCHAR(64)  NOT NULL,
  `permission`     ENUM('view','download') NOT NULL DEFAULT 'view',
  `password_hash`  VARCHAR(255) NULL COMMENT 'NULL = tanpa password',
  `expires_at`     DATETIME     NULL COMMENT 'NULL = tidak kadaluarsa',
  `max_downloads`  INT UNSIGNED NULL COMMENT 'NULL = tidak dibatasi',
  `download_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_by`     INT UNSIGNED NOT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_token` (`token`),
  CONSTRAINT `fk_pl_file` FOREIGN KEY (`file_id`) REFERENCES `dokumen_files`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
