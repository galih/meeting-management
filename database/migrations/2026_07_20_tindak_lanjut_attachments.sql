-- Migration: Tabel lampiran untuk tindak lanjut (progress attachments)
-- Dibuat: 2026-07-20
-- Jalankan sekali di database production/development

CREATE TABLE IF NOT EXISTS `tindak_lanjut_attachments` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `tindak_lanjut_id` INT UNSIGNED     NOT NULL,
    `uploaded_by`      INT UNSIGNED     NULL,
    `original_name`    VARCHAR(255)     NOT NULL,
    `file_path`        VARCHAR(500)     NOT NULL,
    `mime_type`        VARCHAR(100)     NOT NULL DEFAULT '',
    `file_size`        BIGINT UNSIGNED  NOT NULL DEFAULT 0,
    `created_at`       DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_tl_id`  (`tindak_lanjut_id`),
    INDEX `idx_uploader` (`uploaded_by`),
    CONSTRAINT `fk_tla_tl`
        FOREIGN KEY (`tindak_lanjut_id`)
        REFERENCES `tindak_lanjut` (`id`)
        ON DELETE CASCADE,
    CONSTRAINT `fk_tla_user`
        FOREIGN KEY (`uploaded_by`)
        REFERENCES `users` (`id`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
