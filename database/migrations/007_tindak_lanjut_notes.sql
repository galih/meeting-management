-- ============================================================
-- Migration 007 — Tabel tindak_lanjut_notes
-- Jalankan sekali di database production / staging:
--   mysql -u USER -p DB_NAME < database/migrations/007_tindak_lanjut_notes.sql
-- ============================================================

CREATE TABLE IF NOT EXISTS tindak_lanjut_notes (
    id                INT          PRIMARY KEY AUTO_INCREMENT,
    tindak_lanjut_id  INT          NOT NULL,
    user_id           INT          NOT NULL,
    note              TEXT         NOT NULL,
    created_at        TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (tindak_lanjut_id) REFERENCES tindak_lanjut(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)          REFERENCES users(id),

    INDEX idx_tln_tl (tindak_lanjut_id),
    INDEX idx_tln_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
