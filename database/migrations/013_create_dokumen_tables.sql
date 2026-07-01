-- ============================================================
-- Migration 013: Fitur Dokumen (Fase 1)
-- ============================================================

-- Folder / kategori (mendukung nested folder)
CREATE TABLE IF NOT EXISTS `dokumen_folders` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `parent_id`   INT UNSIGNED NULL DEFAULT NULL,
  `name`        VARCHAR(255) NOT NULL,
  `created_by`  INT UNSIGNED NOT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_df_parent`  FOREIGN KEY (`parent_id`)  REFERENCES `dokumen_folders`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_df_creator` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File utama
CREATE TABLE IF NOT EXISTS `dokumen_files` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `folder_id`     INT UNSIGNED NULL DEFAULT NULL,
  `original_name` VARCHAR(255) NOT NULL,
  `stored_name`   VARCHAR(255) NOT NULL,
  `file_path`     VARCHAR(500) NOT NULL,
  `mime_type`     VARCHAR(120) NOT NULL DEFAULT '',
  `file_size`     BIGINT       NOT NULL DEFAULT 0,
  `uploaded_by`   INT UNSIGNED NOT NULL,
  `is_public`     TINYINT(1)   NOT NULL DEFAULT 0,
  `share_token`   VARCHAR(64)  NULL DEFAULT NULL UNIQUE,
  `deleted_at`    DATETIME     NULL DEFAULT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_dfi_folder`   FOREIGN KEY (`folder_id`)   REFERENCES `dokumen_folders`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_dfi_uploader` FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`)            ON DELETE CASCADE,
  INDEX `idx_dfi_folder`  (`folder_id`),
  INDEX `idx_dfi_uploader`(`uploaded_by`),
  INDEX `idx_dfi_deleted` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Berbagi file ke user internal
CREATE TABLE IF NOT EXISTS `dokumen_shares` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `file_id`     INT UNSIGNED NOT NULL,
  `shared_to`   INT UNSIGNED NOT NULL,
  `permission`  ENUM('view','download') NOT NULL DEFAULT 'view',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_share` (`file_id`, `shared_to`),
  CONSTRAINT `fk_ds_file` FOREIGN KEY (`file_id`)   REFERENCES `dokumen_files`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ds_user` FOREIGN KEY (`shared_to`) REFERENCES `users`(`id`)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
