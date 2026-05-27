-- ============================================================
-- Sprint 1 Migration — Email Queue + Email Log
-- Jalankan sekali via phpMyAdmin atau installer
-- ============================================================

-- Email queue untuk pengiriman async
CREATE TABLE IF NOT EXISTS email_queue (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    `to`        VARCHAR(255) NOT NULL,
    `name`      VARCHAR(100),
    subject     VARCHAR(255) NOT NULL,
    body        LONGTEXT     NOT NULL,
    type        ENUM('invitation','reminder','summary','reset') DEFAULT 'invitation',
    status      ENUM('pending','sent','failed') DEFAULT 'pending',
    attempts    TINYINT      DEFAULT 0,
    error_msg   TEXT,
    scheduled_at DATETIME    DEFAULT CURRENT_TIMESTAMP,
    sent_at     DATETIME     DEFAULT NULL,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_status_scheduled (status, scheduled_at)
);

-- Log export PDF notulen
CREATE TABLE IF NOT EXISTS notulen_exports (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    exported_by INT NOT NULL,
    format      ENUM('pdf','docx') DEFAULT 'pdf',
    filename    VARCHAR(255),
    exported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (exported_by) REFERENCES users(id)
);
