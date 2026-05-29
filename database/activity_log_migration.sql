-- ── Activity Log ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS activity_logs (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    user_id      INT          DEFAULT NULL,
    user_name    VARCHAR(100) DEFAULT NULL  COMMENT 'Snapshot nama user saat aksi',
    user_role    VARCHAR(50)  DEFAULT NULL  COMMENT 'Snapshot role saat aksi',
    action       VARCHAR(100) NOT NULL      COMMENT 'Kode aksi, contoh: meeting.create',
    description  TEXT         DEFAULT NULL  COMMENT 'Keterangan lengkap aksi',
    subject_type VARCHAR(50)  DEFAULT NULL  COMMENT 'Tipe objek: meeting / user / auth',
    subject_id   INT          DEFAULT NULL  COMMENT 'ID objek terkait jika ada',
    ip_address   VARCHAR(45)  DEFAULT NULL,
    user_agent   VARCHAR(300) DEFAULT NULL,
    created_at   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_al_user    (user_id),
    INDEX idx_al_action  (action),
    INDEX idx_al_created (created_at)
);
