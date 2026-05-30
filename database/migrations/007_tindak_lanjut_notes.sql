-- Migration: tabel progress note untuk tindak lanjut
CREATE TABLE IF NOT EXISTS tindak_lanjut_notes (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tindak_lanjut_id INT UNSIGNED NOT NULL,
    user_id     INT UNSIGNED NOT NULL,
    note        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_tln_tl   FOREIGN KEY (tindak_lanjut_id) REFERENCES tindak_lanjut(id) ON DELETE CASCADE,
    CONSTRAINT fk_tln_user FOREIGN KEY (user_id)          REFERENCES users(id)         ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
