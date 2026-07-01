<?php
declare(strict_types=1);

/**
 * Model khusus untuk operasi share dokumen (Fase 2).
 * DokumenModel tetap menangani CRUD file/folder.
 */
class DokumenShareModel
{
    /* ------------------------------------------------------------------ */
    /*  LIST SHARE                                                          */
    /* ------------------------------------------------------------------ */

    /** Semua user yang sudah di-share untuk 1 file */
    public static function getSharesByFile(int $fileId): array
    {
        return Database::query(
            "SELECT ds.*, u.name AS user_name, u.username, u.role
             FROM dokumen_shares ds
             JOIN users u ON u.id = ds.shared_to
             WHERE ds.file_id = ?
             ORDER BY u.name ASC",
            [$fileId]
        );
    }

    /** Semua file yang di-share ke user tertentu (dengan info file) */
    public static function getFilesSharedToUser(int $userId): array
    {
        return Database::query(
            "SELECT df.*, u.name AS uploader_name,
                    ds.permission AS share_permission, ds.created_at AS shared_at
             FROM dokumen_shares ds
             JOIN dokumen_files df ON df.id = ds.file_id
             LEFT JOIN users u ON u.id = df.uploaded_by
             WHERE ds.shared_to = ?
             ORDER BY ds.created_at DESC",
            [$userId]
        );
    }

    /* ------------------------------------------------------------------ */
    /*  CRUD SHARE                                                          */
    /* ------------------------------------------------------------------ */

    /** Tambah / update share (upsert) */
    public static function upsert(int $fileId, int $sharedTo, string $permission): void
    {
        Database::getInstance()->prepare(
            "INSERT INTO dokumen_shares (file_id, shared_to, permission)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE permission = VALUES(permission)"
        )->execute([$fileId, $sharedTo, $permission]);
    }

    /** Hapus 1 share record */
    public static function remove(int $fileId, int $sharedTo): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_shares WHERE file_id = ? AND shared_to = ?"
        )->execute([$fileId, $sharedTo]);
    }

    /** Hapus semua share untuk sebuah file */
    public static function removeAllByFile(int $fileId): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_shares WHERE file_id = ?"
        )->execute([$fileId]);
    }

    /* ------------------------------------------------------------------ */
    /*  CEK AKSES                                                           */
    /* ------------------------------------------------------------------ */

    /** Cek apakah user punya akses ke file (owner / share / admin) */
    public static function canAccess(int $fileId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) return true;
        $file = DokumenModel::getFileById($fileId);
        if (!$file) return false;
        if ((int)$file['uploaded_by'] === $userId) return true;
        $share = Database::queryOne(
            "SELECT id FROM dokumen_shares WHERE file_id = ? AND shared_to = ?",
            [$fileId, $userId]
        );
        return (bool)$share;
    }

    /** Cek apakah user boleh download (harus permission='download' atau owner/admin) */
    public static function canDownload(int $fileId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) return true;
        $file = DokumenModel::getFileById($fileId);
        if (!$file) return false;
        if ((int)$file['uploaded_by'] === $userId) return true;
        $share = Database::queryOne(
            "SELECT permission FROM dokumen_shares WHERE file_id = ? AND shared_to = ?",
            [$fileId, $userId]
        );
        return $share && $share['permission'] === 'download';
    }
}
