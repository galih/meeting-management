<?php
declare(strict_types=1);

class DokumenModel
{
    /* ================================================================
       FOLDERS
    ================================================================ */

    /** Ambil semua folder milik user (root = parent_id IS NULL) */
    public static function getFolders(?int $parentId = null, int $userId = 0, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $sql    = "SELECT f.*, u.name AS creator_name
                       FROM dokumen_folders f
                       LEFT JOIN users u ON u.id = f.created_by
                       WHERE f.parent_id " . ($parentId === null ? 'IS NULL' : '= ?') . "
                       ORDER BY f.name ASC";
            $params = $parentId === null ? [] : [$parentId];
        } else {
            $sql    = "SELECT f.*, u.name AS creator_name
                       FROM dokumen_folders f
                       LEFT JOIN users u ON u.id = f.created_by
                       WHERE f.created_by = ?
                         AND f.parent_id " . ($parentId === null ? 'IS NULL' : '= ?') . "
                       ORDER BY f.name ASC";
            $params = $parentId === null ? [$userId] : [$userId, $parentId];
        }
        return Database::query($sql, $params);
    }

    /** Buat folder baru */
    public static function createFolder(string $name, ?int $parentId, int $userId): int
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO dokumen_folders (name, parent_id, created_by) VALUES (?,?,?)"
        )->execute([$name, $parentId, $userId]);
        return (int)$db->lastInsertId();
    }

    /** Rename folder */
    public static function renameFolder(int $id, string $name): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_folders SET name=? WHERE id=?"
        )->execute([$name, $id]);
    }

    /** Hapus folder (hanya jika kosong) */
    public static function deleteFolder(int $id): bool
    {
        $hasFiles   = Database::queryOne("SELECT id FROM dokumen_files   WHERE folder_id=? AND deleted_at IS NULL LIMIT 1", [$id]);
        $hasFolders = Database::queryOne("SELECT id FROM dokumen_folders WHERE parent_id=? LIMIT 1", [$id]);
        if ($hasFiles || $hasFolders) return false;
        Database::getInstance()->prepare("DELETE FROM dokumen_folders WHERE id=?")->execute([$id]);
        return true;
    }

    /** Ambil satu folder */
    public static function getFolder(int $id): ?array
    {
        $row = Database::queryOne("SELECT * FROM dokumen_folders WHERE id=?", [$id]);
        return $row ?: null;
    }

    /* ================================================================
       FILES
    ================================================================ */

    /** Daftar file di folder (tidak terhapus) */
    public static function getFiles(?int $folderId = null, int $userId = 0, bool $isAdmin = false, string $search = '', string $typeFilter = ''): array
    {
        $conditions = ['f.deleted_at IS NULL'];
        $params     = [];

        if ($folderId !== null) {
            $conditions[] = 'f.folder_id = ?';
            $params[]     = $folderId;
        } else {
            $conditions[] = 'f.folder_id IS NULL';
        }

        if (!$isAdmin) {
            $conditions[] = '(f.uploaded_by = ? OR EXISTS (
                                SELECT 1 FROM dokumen_shares ds
                                WHERE ds.file_id = f.id AND ds.shared_to = ?))';
            $params[] = $userId;
            $params[] = $userId;
        }

        if ($search !== '') {
            $conditions[] = 'f.original_name LIKE ?';
            $params[]     = '%' . $search . '%';
        }

        if ($typeFilter !== '') {
            $conditions[] = 'f.mime_type LIKE ?';
            $params[]     = '%' . $typeFilter . '%';
        }

        $where = implode(' AND ', $conditions);
        $sql   = "SELECT f.*, u.name AS uploader_name
                  FROM dokumen_files f
                  LEFT JOIN users u ON u.id = f.uploaded_by
                  WHERE {$where}
                  ORDER BY f.created_at DESC";

        return Database::query($sql, $params);
    }

    /** Ambil satu file berdasarkan ID */
    public static function getFile(int $id): ?array
    {
        $row = Database::queryOne(
            "SELECT f.*, u.name AS uploader_name
             FROM dokumen_files f
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE f.id = ? AND f.deleted_at IS NULL",
            [$id]
        );
        return $row ?: null;
    }

    /** Simpan record file baru, kembalikan ID */
    public static function createFile(array $data): int
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO dokumen_files
               (folder_id, original_name, stored_name, file_path, mime_type, file_size, uploaded_by)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([
            $data['folder_id'] ?? null,
            $data['original_name'],
            $data['stored_name'],
            $data['file_path'],
            $data['mime_type'],
            $data['file_size'],
            $data['uploaded_by'],
        ]);
        return (int)$db->lastInsertId();
    }

    /** Soft-delete file */
    public static function softDelete(int $id): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_files SET deleted_at = NOW() WHERE id = ?"
        )->execute([$id]);
    }

    /** Restore dari trash */
    public static function restore(int $id): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_files SET deleted_at = NULL WHERE id = ?"
        )->execute([$id]);
    }

    /** Hard-delete file (dari trash) */
    public static function hardDelete(int $id): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_files WHERE id = ?"
        )->execute([$id]);
    }

    /** Daftar file di trash milik user */
    public static function getTrash(int $userId, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            return Database::query(
                "SELECT f.*, u.name AS uploader_name
                 FROM dokumen_files f
                 LEFT JOIN users u ON u.id = f.uploaded_by
                 WHERE f.deleted_at IS NOT NULL
                 ORDER BY f.deleted_at DESC",
                []
            );
        }
        return Database::query(
            "SELECT f.*, u.name AS uploader_name
             FROM dokumen_files f
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE f.uploaded_by = ? AND f.deleted_at IS NOT NULL
             ORDER BY f.deleted_at DESC",
            [$userId]
        );
    }

    /** Daftar file yang di-share ke user tertentu */
    public static function getSharedWithMe(int $userId): array
    {
        return Database::query(
            "SELECT f.*, u.name AS uploader_name, ds.permission
             FROM dokumen_shares ds
             JOIN dokumen_files f ON f.id = ds.file_id
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE ds.shared_to = ? AND f.deleted_at IS NULL
             ORDER BY f.created_at DESC",
            [$userId]
        );
    }

    /** File yang baru-baru ini diakses / diupload user */
    public static function getRecent(int $userId, bool $isAdmin = false, int $limit = 20): array
    {
        if ($isAdmin) {
            return Database::query(
                "SELECT f.*, u.name AS uploader_name
                 FROM dokumen_files f
                 LEFT JOIN users u ON u.id = f.uploaded_by
                 WHERE f.deleted_at IS NULL
                 ORDER BY f.updated_at DESC LIMIT ?",
                [$limit]
            );
        }
        return Database::query(
            "SELECT f.*, u.name AS uploader_name
             FROM dokumen_files f
             LEFT JOIN users u ON u.id = f.uploaded_by
             WHERE f.deleted_at IS NULL
               AND (f.uploaded_by = ? OR EXISTS (
                     SELECT 1 FROM dokumen_shares ds
                     WHERE ds.file_id = f.id AND ds.shared_to = ?))
             ORDER BY f.updated_at DESC LIMIT ?",
            [$userId, $userId, $limit]
        );
    }

    /* ================================================================
       HELPERS — ukuran storage per user
    ================================================================ */
    public static function storageSummary(int $userId, bool $isAdmin = false): array
    {
        if ($isAdmin) {
            $row = Database::queryOne(
                "SELECT COUNT(*) AS total_files, COALESCE(SUM(file_size),0) AS total_bytes
                 FROM dokumen_files WHERE deleted_at IS NULL",
                []
            );
        } else {
            $row = Database::queryOne(
                "SELECT COUNT(*) AS total_files, COALESCE(SUM(file_size),0) AS total_bytes
                 FROM dokumen_files WHERE uploaded_by=? AND deleted_at IS NULL",
                [$userId]
            );
        }
        return $row ?: ['total_files' => 0, 'total_bytes' => 0];
    }
}
