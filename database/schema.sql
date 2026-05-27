-- ============================================================
-- Meeting Management App — Database Schema v1.4.0
-- MySQL 8+, charset utf8mb4
--
-- CATATAN: File ini dijalankan oleh installer SETELAH
-- database dibuat. Jangan sertakan CREATE DATABASE / USE.
-- ============================================================

-- ── Users ──────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id                   INT PRIMARY KEY AUTO_INCREMENT,
    name                 VARCHAR(100)  NOT NULL,
    email                VARCHAR(100)  NOT NULL UNIQUE,
    password             VARCHAR(255)  NOT NULL,
    role                 ENUM('admin','sekretaris','peserta') NOT NULL DEFAULT 'peserta',
    department_id        INT           DEFAULT NULL,
    avatar               VARCHAR(255)  DEFAULT NULL,
    is_active            TINYINT(1)    NOT NULL DEFAULT 1,
    remember_token       VARCHAR(64)   DEFAULT NULL,
    reset_token          VARCHAR(64)   DEFAULT NULL,
    reset_token_expires  DATETIME      DEFAULT NULL,
    last_login           DATETIME      DEFAULT NULL,
    created_at           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed admin default (password: Admin@12345)
-- INSERT IGNORE: aman dijalankan ulang, tidak error jika sudah ada
INSERT IGNORE INTO users (name, email, password, role) VALUES
  ('Administrator', 'admin@meetingapp.id',
   '$2y$12$TKh8H1.PfunNGBz/znOlJuuBVZ7XMM/YpW5BWl8gBuIV9hWaX4Iye', 'admin');

-- ── Departments ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS departments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  DEFAULT NULL,
    description TEXT,
    head_id     INT          DEFAULT NULL COMMENT 'Kepala divisi (user_id)',
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tambah FK department ke users hanya jika belum ada
SET @db = DATABASE();
SET @fk_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @db
      AND TABLE_NAME = 'users'
      AND CONSTRAINT_NAME = 'fk_users_department'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql = IF(@fk_exists = 0,
    'ALTER TABLE users ADD CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ── Meetings ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS meetings (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    title          VARCHAR(200)  NOT NULL,
    description    TEXT,
    location       VARCHAR(200),
    start_datetime DATETIME      NOT NULL,
    end_datetime   DATETIME      NOT NULL,
    status         ENUM('scheduled','ongoing','done','cancelled') DEFAULT 'scheduled',
    color          VARCHAR(20)   DEFAULT '#f76707',
    department_id  INT           DEFAULT NULL,
    recurring_id   INT           DEFAULT NULL,
    created_by     INT,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS meeting_participants (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    user_id    INT NOT NULL,
    status     ENUM('invited','accepted','declined','attended') DEFAULT 'invited',
    UNIQUE KEY uq_meeting_user (meeting_id, user_id),
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS meeting_attendances (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    user_id    INT NOT NULL,
    status     ENUM('present','absent','late','excused') DEFAULT 'present',
    note       TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_attend (meeting_id, user_id),
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)
);

-- ── Notulen ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notulen (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL UNIQUE,
    content     LONGTEXT,
    version     INT       DEFAULT 1,
    created_by  INT       DEFAULT NULL,
    updated_by  INT       DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS notulen_history (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    content    LONGTEXT,
    version    INT       DEFAULT 1,
    edited_by  INT       DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by)  REFERENCES users(id)
);

-- ── Tindak Lanjut ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tindak_lanjut (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id   INT  NOT NULL,
    description  TEXT NOT NULL,
    assigned_to  INT  DEFAULT NULL,
    due_date     DATE DEFAULT NULL,
    priority     ENUM('low','medium','high') DEFAULT 'medium',
    status       ENUM('pending','in_progress','done','cancelled') DEFAULT 'pending',
    completed_at DATETIME DEFAULT NULL,
    created_by   INT  DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by)  REFERENCES users(id)
);

-- ── Notifications ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT          NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    message    TEXT,
    url        VARCHAR(255) DEFAULT NULL,
    is_read    TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- ── Email Queue & Export Log ─────────────────────────────────
CREATE TABLE IF NOT EXISTS email_queue (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    to_email   VARCHAR(150) NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    body       LONGTEXT,
    status     ENUM('pending','sent','failed') DEFAULT 'pending',
    attempts   TINYINT DEFAULT 0,
    meeting_id INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sent_at    DATETIME  DEFAULT NULL,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notulen_exports (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    exported_by INT DEFAULT NULL,
    format      ENUM('pdf','docx') DEFAULT 'pdf',
    filename    VARCHAR(255),
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (exported_by) REFERENCES users(id)
);

-- ── Komentar Notulen ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notulen_comments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT  NOT NULL,
    parent_id   INT  DEFAULT NULL COMMENT 'NULL = komentar utama, isi = reply',
    user_id     INT  NOT NULL,
    content     TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP  DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP  DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)  REFERENCES notulen_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    INDEX idx_nc_meeting (meeting_id)
);

CREATE TABLE IF NOT EXISTS comment_mentions (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id    INT NOT NULL,
    UNIQUE KEY uq_cm (comment_id, user_id),
    FOREIGN KEY (comment_id) REFERENCES notulen_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)
);

-- ── Lampiran File ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS meeting_attachments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    uploaded_by INT NOT NULL,
    filename    VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    mime_type   VARCHAR(100),
    file_size   INT,
    category    ENUM('agenda','notulen','referensi','lainnya') DEFAULT 'lainnya',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_ma_meeting (meeting_id)
);

-- ── Recurring Meeting ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS recurring_meetings (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    title          VARCHAR(200) NOT NULL,
    description    TEXT,
    location       VARCHAR(200),
    frequency      ENUM('daily','weekly','biweekly','monthly') NOT NULL DEFAULT 'weekly',
    day_of_week    TINYINT DEFAULT NULL,
    day_of_month   TINYINT DEFAULT NULL,
    start_time     TIME    NOT NULL,
    end_time       TIME    NOT NULL,
    start_date     DATE    NOT NULL,
    end_date       DATE    DEFAULT NULL,
    color          VARCHAR(20)  DEFAULT '#f76707',
    department_id  INT     DEFAULT NULL,
    created_by     INT     NOT NULL,
    is_active      TINYINT(1) DEFAULT 1,
    last_generated DATE    DEFAULT NULL,
    created_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)    REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS recurring_participants (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    recurring_id INT NOT NULL,
    user_id      INT NOT NULL,
    UNIQUE KEY uq_rec_user (recurring_id, user_id),
    FOREIGN KEY (recurring_id) REFERENCES recurring_meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)      REFERENCES users(id)
);

-- Tambah FK recurring ke meetings hanya jika belum ada
SET @db2 = DATABASE();
SET @fk2_exists = (
    SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = @db2
      AND TABLE_NAME = 'meetings'
      AND CONSTRAINT_NAME = 'fk_meetings_recurring'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
);
SET @sql2 = IF(@fk2_exists = 0,
    'ALTER TABLE meetings ADD CONSTRAINT fk_meetings_recurring FOREIGN KEY (recurring_id) REFERENCES recurring_meetings(id) ON DELETE SET NULL',
    'SELECT 2'
);
PREPARE stmt2 FROM @sql2;
EXECUTE stmt2;
DEALLOCATE PREPARE stmt2;
