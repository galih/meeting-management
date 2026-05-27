-- ============================================================
-- Sprint 3 Migration — Departments + Notulen Comments
-- ============================================================

-- Tabel Departemen/Divisi
CREATE TABLE IF NOT EXISTS departments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    code        VARCHAR(20)  UNIQUE,
    description TEXT,
    head_id     INT DEFAULT NULL COMMENT 'User ID kepala divisi',
    is_active   TINYINT(1) DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Tambah kolom department_id ke tabel users
ALTER TABLE users ADD COLUMN IF NOT EXISTS department_id INT DEFAULT NULL AFTER role_id;
ALTER TABLE users ADD CONSTRAINT fk_users_dept
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- Tambah kolom department_id ke tabel meetings (opsional: meeting per departemen)
ALTER TABLE meetings ADD COLUMN IF NOT EXISTS department_id INT DEFAULT NULL AFTER created_by;
ALTER TABLE meetings ADD CONSTRAINT fk_meetings_dept
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL;

-- Tabel komentar notulen (per blok Editor.js)
CREATE TABLE IF NOT EXISTS notulen_comments (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    meeting_id  INT NOT NULL,
    block_id    VARCHAR(50) DEFAULT NULL COMMENT 'ID blok Editor.js, NULL = komentar umum',
    parent_id   INT DEFAULT NULL COMMENT 'Untuk thread reply',
    user_id     INT NOT NULL,
    content     TEXT NOT NULL,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (meeting_id) REFERENCES meetings(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id)  REFERENCES notulen_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id),
    INDEX idx_meeting_block (meeting_id, block_id)
);

-- Tabel mention di komentar
CREATE TABLE IF NOT EXISTS comment_mentions (
    id         INT PRIMARY KEY AUTO_INCREMENT,
    comment_id INT NOT NULL,
    user_id    INT NOT NULL,
    FOREIGN KEY (comment_id) REFERENCES notulen_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id) ON DELETE CASCADE
);

-- Data departemen contoh
INSERT IGNORE INTO departments (name, code, description) VALUES
  ('Direksi',             'DIR',  'Dewan Direksi'),
  ('Teknologi Informasi', 'IT',   'Divisi IT & Pengembangan'),
  ('Keuangan',            'FIN',  'Divisi Keuangan & Akuntansi'),
  ('Sumber Daya Manusia', 'HRD',  'Divisi HRD & Personalia'),
  ('Operasional',         'OPS',  'Divisi Operasional');
