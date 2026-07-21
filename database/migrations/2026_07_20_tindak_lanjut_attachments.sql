-- Migration: Tabel lampiran untuk tindak lanjut (progress attachments)
-- Dibuat  : 2026-07-20
-- Fix     : 2026-07-21 — ubah INT UNSIGNED → INT agar cocok dengan
--           tindak_lanjut.id (INT) dan users.id (INT) di schema.sql
--           sehingga FK errno:150 tidak terjadi.
-- Jalankan sekali di database production/development.

CREATE TABLE IF NOT EXISTS `tindak_lanjut_attachments` (
    `id`               INT          NOT NULL AUTO_INCREMENT,
    `tindak_lanjut_id` INT          NOT NULL,
    `uploaded_by`      INT          NULL,
    `original_name`    VARCHAR(255) NOT NULL,
    `file_path`        VARCHAR(500) NOT NULL,
    `mime_type`        VARCHAR(100) NOT NULL DEFAULT '',
    `file_size`        BIGINT       NOT NULL DEFAULT 0,
    `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tla_tl`       (`tindak_lanjut_id`),
    INDEX `idx_tla_uploader` (`uploaded_by`),
    CONSTRAINT `fk_tla_tl`
        FOREIGN KEY (`tindak_lanjut_id`)
        REFERENCES `tindak_lanjut` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_tla_user`
        FOREIGN KEY (`uploaded_by`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
