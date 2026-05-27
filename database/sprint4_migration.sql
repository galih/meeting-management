-- ============================================================
-- Sprint 4 Migration — File Attachments + Recurring Meetings
-- ============================================================

-- Lampiran file pada meeting / notulen
CREATE TABLE IF NOT EXISTS meeting_attachments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    uploaded_by INT NOT NULL,
    filename    VARCHAR(255) NOT NULL COMMENT 'Nama asli file',
    stored_name VARCHAR(255) NOT NULL COMMENT 'Nama file di server (UUID)',
    mime_type   VARCHAR(100),
    file_size   INT          COMMENT 'Ukuran dalam bytes',
    category    ENUM('agenda','notulen','referensi','lainnya') DEFAULT 'lainnya',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_meeting (meeting_id)
);

-- Recurring meeting template
CREATE TABLE IF NOT EXISTS recurring_meetings (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    title           VARCHAR(200) NOT NULL,
    description     TEXT,
    location        VARCHAR(200),
    frequency       ENUM('daily','weekly','biweekly','monthly') NOT NULL DEFAULT 'weekly',
    day_of_week     TINYINT DEFAULT NULL COMMENT '0=Minggu, 1=Senin, ..., 6=Sabtu (untuk weekly/biweekly)',
    day_of_month    TINYINT DEFAULT NULL COMMENT 'Tanggal 1-28 (untuk monthly)',
    start_time      TIME NOT NULL,
    end_time        TIME NOT NULL,
    start_date      DATE NOT NULL COMMENT 'Tanggal mulai recurring',
    end_date        DATE DEFAULT NULL COMMENT 'NULL = tidak ada batas',
    color           VARCHAR(20) DEFAULT '#f76707',
    department_id   INT DEFAULT NULL,
    created_by      INT NOT NULL,
    is_active       TINYINT(1) DEFAULT 1,
    last_generated  DATE DEFAULT NULL COMMENT 'Tanggal terakhir meeting di-generate',
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)
);

-- Peserta default untuk recurring meeting
CREATE TABLE IF NOT EXISTS recurring_participants (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    recurring_id INT NOT NULL,
    user_id      INT NOT NULL,
    UNIQUE KEY uq_rec_user (recurring_id, user_id),
    FOREIGN KEY (recurring_id) REFERENCES recurring_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id)
);

-- Relasi meeting ke recurring template
ALTER TABLE meetings ADD COLUMN IF NOT EXISTS recurring_id INT DEFAULT NULL AFTER department_id;
ALTER TABLE meetings ADD CONSTRAINT fk_meetings_recurring
    FOREIGN KEY (recurring_id) REFERENCES recurring_meetings(id) ON DELETE SET NULL;
