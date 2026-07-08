-- ============================================================
-- Migration: department_member_log
-- Mencatat riwayat assign / remove anggota ke unit kerja
-- ============================================================
CREATE TABLE IF NOT EXISTS department_member_log (
    id            INT PRIMARY KEY AUTO_INCREMENT,
    department_id INT          NOT NULL,
    user_id       INT          NOT NULL,
    action        ENUM('assign','remove') NOT NULL,
    from_dept_id  INT          DEFAULT NULL COMMENT 'Unit asal sebelum dipindah (NULL jika belum punya)',
    actor_id      INT          DEFAULT NULL COMMENT 'Admin yang melakukan aksi',
    actor_name    VARCHAR(100) DEFAULT NULL COMMENT 'Snapshot nama admin',
    note          VARCHAR(255) DEFAULT NULL,
    created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)       REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (from_dept_id)  REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (actor_id)      REFERENCES users(id)       ON DELETE SET NULL,
    INDEX idx_dml_dept    (department_id),
    INDEX idx_dml_user    (user_id),
    INDEX idx_dml_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
