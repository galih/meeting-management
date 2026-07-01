-- ============================================================
-- Fase 1: Fitur Dokumen (File Manager internal)
-- ============================================================

CREATE TABLE IF NOT EXISTS `dokumen_folders` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
  `name`        VARCHAR(255)  NOT NULL,
  `created_by`  INT UNSIGNED  NOT NULL,
  `created_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_parent`     (`parent_id`),
  KEY `idx_created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dokumen_files` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `folder_id`     INT UNSIGNED  NULL DEFAULT NULL,
  `original_name` VARCHAR(255)  NOT NULL,
  `stored_name`   VARCHAR(255)  NOT NULL,
  `file_path`     VARCHAR(500)  NOT NULL,
  `mime_type`     VARCHAR(120)  NOT NULL DEFAULT '',
  `file_size`     BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `uploaded_by`   INT UNSIGNED  NOT NULL,
  `is_public`     TINYINT(1)    NOT NULL DEFAULT 0,
  `share_token`   VARCHAR(64)   NULL DEFAULT NULL,
  `created_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_share_token` (`share_token`),
  KEY `idx_folder`      (`folder_id`),
  KEY `idx_uploaded_by` (`uploaded_by`),
  KEY `idx_is_public`   (`is_public`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `dokumen_shares` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `file_id`     INT UNSIGNED NOT NULL,
  `shared_to`   INT UNSIGNED NOT NULL,
  `permission`  ENUM('view','download') NOT NULL DEFAULT 'view',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_file_user` (`file_id`,`shared_to`),
  KEY `idx_shared_to` (`shared_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
