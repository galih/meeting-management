-- ============================================================
-- Migration 004 — RBAC: Dynamic Roles & Permissions
-- Jalankan sekali: mysql -u root -p dbname < 004_rbac.sql
-- ============================================================

-- ── 1. Tabel roles ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS roles (
    id         INT UNSIGNED     PRIMARY KEY AUTO_INCREMENT,
    name       VARCHAR(50)      NOT NULL UNIQUE  COMMENT 'slug: admin, sekretaris, editor, peserta',
    label      VARCHAR(100)     NOT NULL         COMMENT 'Nama tampil: Administrator, Sekretaris, …',
    color      VARCHAR(20)      NOT NULL DEFAULT '#6c757d',
    is_system  TINYINT(1)       NOT NULL DEFAULT 0 COMMENT '1 = tidak bisa dihapus (admin)',
    created_at TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP        DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. Tabel permissions ────────────────────────────────────
CREATE TABLE IF NOT EXISTS permissions (
    id     INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT,
    name   VARCHAR(100)  NOT NULL UNIQUE COMMENT 'slug: meeting.create, notulen.edit …',
    label  VARCHAR(150)  NOT NULL,
    module VARCHAR(50)   NOT NULL        COMMENT 'Grup: meeting, notulen, tindaklanjut, dokumen, user, settings'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. Pivot role_permissions ───────────────────────────────
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT UNSIGNED NOT NULL,
    perm_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, perm_id),
    FOREIGN KEY (role_id) REFERENCES roles(id)       ON DELETE CASCADE,
    FOREIGN KEY (perm_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. Migrate users.role ENUM → VARCHAR ────────────────────
-- Tambah kolom baru dulu agar data tidak hilang
ALTER TABLE users
    MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'peserta';

-- ── 5. Seed: Default Roles ──────────────────────────────────
INSERT IGNORE INTO roles (name, label, color, is_system) VALUES
    ('admin',      'Administrator', '#e03131', 1),
    ('sekretaris', 'Sekretaris',    '#1971c2', 0),
    ('editor',     'Editor',        '#6741d9', 0),
    ('peserta',    'Peserta',       '#2f9e44', 0);

-- ── 6. Seed: Default Permissions ────────────────────────────
INSERT IGNORE INTO permissions (name, label, module) VALUES
    -- Meeting
    ('meeting.view',        'Lihat Rapat',          'meeting'),
    ('meeting.create',      'Buat Rapat',           'meeting'),
    ('meeting.edit',        'Edit Rapat',           'meeting'),
    ('meeting.delete',      'Hapus Rapat',          'meeting'),
    ('meeting.status',      'Ubah Status Rapat',    'meeting'),
    -- Notulen
    ('notulen.view',        'Lihat Notulen',        'notulen'),
    ('notulen.edit',        'Edit Notulen',         'notulen'),
    ('notulen.export',      'Export Notulen',       'notulen'),
    ('notulen.comment',     'Komentar Notulen',     'notulen'),
    ('notulen.template',    'Kelola Template',      'notulen'),
    -- Tindak Lanjut
    ('tindaklanjut.view',   'Lihat Tindak Lanjut',  'tindaklanjut'),
    ('tindaklanjut.create', 'Buat Tindak Lanjut',   'tindaklanjut'),
    ('tindaklanjut.manage', 'Kelola Tindak Lanjut', 'tindaklanjut'),
    -- Dokumen
    ('dokumen.view',        'Lihat Dokumen',        'dokumen'),
    ('dokumen.upload',      'Upload Dokumen',       'dokumen'),
    ('dokumen.delete',      'Hapus Dokumen',        'dokumen'),
    ('dokumen.share',       'Share Dokumen',        'dokumen'),
    -- User & Settings (admin only)
    ('user.view',           'Lihat Pengguna',       'user'),
    ('user.manage',         'Kelola Pengguna',      'user'),
    ('role.manage',         'Kelola Role',          'user'),
    ('settings.manage',     'Kelola Pengaturan',    'settings'),
    ('activitylog.view',    'Lihat Activity Log',   'settings');

-- ── 7. Assign permissions ke default roles ──────────────────
-- Semua permission → admin
INSERT IGNORE INTO role_permissions (role_id, perm_id)
    SELECT r.id, p.id FROM roles r, permissions p WHERE r.name = 'admin';

-- Sekretaris: semua kecuali user.manage, role.manage, settings.manage
INSERT IGNORE INTO role_permissions (role_id, perm_id)
    SELECT r.id, p.id FROM roles r
    JOIN permissions p ON p.name NOT IN ('user.manage','role.manage','settings.manage','activitylog.view')
    WHERE r.name = 'sekretaris';

-- Editor: meeting view/edit, notulen semua, tindak lanjut view/create, dokumen view/upload
INSERT IGNORE INTO role_permissions (role_id, perm_id)
    SELECT r.id, p.id FROM roles r
    JOIN permissions p ON p.name IN (
        'meeting.view','meeting.edit',
        'notulen.view','notulen.edit','notulen.export','notulen.comment','notulen.template',
        'tindaklanjut.view','tindaklanjut.create',
        'dokumen.view','dokumen.upload','dokumen.share'
    )
    WHERE r.name = 'editor';

-- Peserta: hanya view + komentar
INSERT IGNORE INTO role_permissions (role_id, perm_id)
    SELECT r.id, p.id FROM roles r
    JOIN permissions p ON p.name IN (
        'meeting.view',
        'notulen.view','notulen.comment',
        'tindaklanjut.view',
        'dokumen.view'
    )
    WHERE r.name = 'peserta';
