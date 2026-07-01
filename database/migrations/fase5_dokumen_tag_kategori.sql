-- ============================================================
-- Fase 5: Tag & Kategori Dokumen
-- ============================================================

CREATE TABLE IF NOT EXISTS `dokumen_kategoris` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `color`      VARCHAR(7)   NOT NULL DEFAULT '#7B1C1C',
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_kat_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `dokumen_tags` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(80)  NOT NULL,
  `color`      VARCHAR(7)   NOT NULL DEFAULT '#2B6CB0',
  `kategori_id` INT UNSIGNED NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_tag_name` (`name`),
  CONSTRAINT `fk_tag_kat` FOREIGN KEY (`kategori_id`) REFERENCES `dokumen_kategoris`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `dokumen_file_tags` (
  `file_id`    INT UNSIGNED NOT NULL,
  `tag_id`     INT UNSIGNED NOT NULL,
  `added_by`   INT UNSIGNED NOT NULL,
  `added_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`file_id`, `tag_id`),
  CONSTRAINT `fk_ft_file` FOREIGN KEY (`file_id`) REFERENCES `dokumen_files`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ft_tag`  FOREIGN KEY (`tag_id`)  REFERENCES `dokumen_tags`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tambah kolom kategori_id ke dokumen_files (opsional, 1 kategori per file)
ALTER TABLE `dokumen_files`
  ADD COLUMN IF NOT EXISTS `kategori_id` INT UNSIGNED NULL AFTER `folder_id`,
  ADD CONSTRAINT `fk_file_kat` FOREIGN KEY (`kategori_id`) REFERENCES `dokumen_kategoris`(`id`) ON DELETE SET NULL;
