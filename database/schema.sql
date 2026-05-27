-- ============================================================
-- Meeting Management App — Database Schema
-- MySQL 8+, charset utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS meeting_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meeting_db;

-- ── Roles ───────────────────────────────────────────────────
CREATE TABLE roles (
    id   INT PRIMARY KEY AUTO_INCREMENT,
    name ENUM('admin','sekretaris','peserta') NOT NULL UNIQUE
);

INSERT INTO roles (name) VALUES ('admin'),('sekretaris'),('peserta');

-- ── Users ───────────────────────────────────────────────────
CREATE TABLE users (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(100)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role_id    INT           NOT NULL DEFAULT 3,
    avatar     VARCHAR(255)  DEFAULT NULL,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Default admin (password: Admin@12345)
INSERT INTO users (name, email, password, role_id) VALUES
  ('Administrator', 'admin@meetingapp.id',
   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- ── Meetings ────────────────────────────────────────────────
CREATE TABLE meetings (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    title          VARCHAR(200)  NOT NULL,
    description    TEXT,
    location       VARCHAR(200),
    start_datetime DATETIME      NOT NULL,
    end_datetime   DATETIME      NOT NULL,
    status         ENUM('scheduled','ongoing','completed','cancelled') DEFAULT 'scheduled',
    color          VARCHAR(20)   DEFAULT '#f76707',
    created_by     INT,
    created_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE meeting_participants (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id INT NOT NULL,
    user_id    INT NOT NULL,
    status     ENUM('invited','accepted','declined','attended') DEFAULT 'invited',
    UNIQUE KEY uq_meeting_user (meeting_id, user_id),
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)
);

-- ── Notulen ─────────────────────────────────────────────────
CREATE TABLE notulen (
    id             INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id     INT NOT NULL UNIQUE,
    content        JSON,
    last_edited_by INT,
    version        INT           DEFAULT 1,
    updated_at     TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)     REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (last_edited_by) REFERENCES users(id)
);

CREATE TABLE notulen_history (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    notulen_id INT NOT NULL,
    content    JSON,
    edited_by  INT,
    edited_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notulen_id) REFERENCES notulen(id) ON DELETE CASCADE,
    FOREIGN KEY (edited_by)  REFERENCES users(id)
);

-- ── Tindak Lanjut ───────────────────────────────────────────
CREATE TABLE tindak_lanjut (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    deskripsi   TEXT         NOT NULL,
    assigned_to INT          DEFAULT NULL,
    deadline    DATE         DEFAULT NULL,
    priority    ENUM('low','medium','high') DEFAULT 'medium',
    status      ENUM('pending','in_progress','done','cancelled') DEFAULT 'pending',
    created_by  INT,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id)  REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (created_by)  REFERENCES users(id)
);

-- ── Notifications ───────────────────────────────────────────
CREATE TABLE notifications (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT          NOT NULL,
    type       VARCHAR(50)  NOT NULL,
    title      VARCHAR(200) NOT NULL,
    message    TEXT,
    data       JSON,
    is_read    TINYINT(1)   DEFAULT 0,
    created_at TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_read (user_id, is_read)
);

-- ── Auth Tables ─────────────────────────────────────────────
CREATE TABLE login_logs (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    user_id      INT,
    ip_address   VARCHAR(45),
    user_agent   TEXT,
    logged_in_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE remember_tokens (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT UNIQUE NOT NULL,
    token      VARCHAR(64) NOT NULL,
    expires_at DATETIME    NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE password_resets (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    user_id    INT UNIQUE NOT NULL,
    token      VARCHAR(64) NOT NULL,
    expires_at DATETIME    NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
