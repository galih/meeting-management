<?php
declare(strict_types=1);

class DokumenFolderShareModel
{
    public static function getSharesByFolder(int $folderId): array
    {
        return Database::query(
            "SELECT ds.*, u.name AS user_name, u.username, u.role
             FROM dokumen_folder_shares ds
             JOIN users u ON u.id = ds.shared_to
             WHERE ds.folder_id = ?
             ORDER BY u.name ASC",
            [$folderId]
        );
    }

    public static function getFoldersSharedToUser(int $userId): array
    {
        return Database::query(
            "SELECT f.*, u.name AS creator_name,
                    ds.permission AS share_permission, ds.created_at AS shared_at,
                    (SELECT COUNT(*) FROM dokumen_files df WHERE df.folder_id = f.id) AS file_count,
                    (SELECT COALESCE(SUM(df2.file_size),0) FROM dokumen_files df2 WHERE df2.folder_id = f.id) AS total_size
             FROM dokumen_folder_shares ds
             JOIN dokumen_folders f ON f.id = ds.folder_id
             LEFT JOIN users u ON u.id = f.created_by
             WHERE ds.shared_to = ?
             ORDER BY ds.created_at DESC",
            [$userId]
        );
    }

    public static function upsert(int $folderId, int $sharedTo, string $permission): void
    {
        Database::getInstance()->prepare(
            "INSERT INTO dokumen_folder_shares (folder_id, shared_to, permission)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE permission = VALUES(permission)"
        )->execute([$folderId, $sharedTo, $permission]);
    }

    public static function remove(int $folderId, int $sharedTo): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_folder_shares WHERE folder_id = ? AND shared_to = ?"
        )->execute([$folderId, $sharedTo]);
    }

    public static function removeAllByFolder(int $folderId): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_folder_shares WHERE folder_id = ?"
        )->execute([$folderId]);
    }

    public static function canAccess(int $folderId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) return true;
        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) return false;
        if ((int)$folder['created_by'] === $userId) return true;
        $share = Database::queryOne(
            "SELECT id FROM dokumen_folder_shares WHERE folder_id = ? AND shared_to = ?",
            [$folderId, $userId]
        );
        return (bool)$share;
    }

    public static function canDelete(int $folderId, int $userId, bool $isAdmin): bool
    {
        if ($isAdmin) return true;
        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) return false;
        return (int)$folder['created_by'] === $userId;
    }
}
