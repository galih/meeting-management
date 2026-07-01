<?php
declare(strict_types=1);

class DokumenModel
{
    public static function getFolders(?int $parentId = null, ?int $userId = null, bool $isAdmin = false): array
    {
        $sql = "SELECT f.*, u.name AS creator_name,
                       (SELECT COUNT(*) FROM dokumen_files df WHERE df.folder_id = f.id) AS file_count,
                       (SELECT COALESCE(SUM(df2.file_size),0) FROM dokumen_files df2 WHERE df2.folder_id = f.id) AS total_size
                FROM dokumen_folders f
                LEFT JOIN users u ON u.id = f.created_by";

        if (!$isAdmin && $userId) {
            $sql .= " LEFT JOIN dokumen_folder_shares dfs ON dfs.folder_id = f.id AND dfs.shared_to = ?";
        }

        $sql .= " WHERE f.parent_id " . ($parentId === null ? 'IS NULL' : '= ?');
        $params = [];
        if (!$isAdmin && $userId) {
            $params[] = $userId;
        }
        if ($parentId !== null) {
            $params[] = $parentId;
        }

        if (!$isAdmin && $userId) {
            $sql .= " AND (f.created_by = ? OR dfs.id IS NOT NULL)";
            $params[] = $userId;
        }

        $sql .= " ORDER BY f.name ASC";
        return Database::query($sql, $params);
    }

    public static function getFolderById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT f.*, u.name AS creator_name FROM dokumen_folders f
             LEFT JOIN users u ON u.id = f.created_by WHERE f.id = ?",
            [$id]
        ) ?: null;
    }

    public static function createFolder(string $name, ?int $parentId, int $userId): int
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO dokumen_folders (name, parent_id, created_by) VALUES (?,?,?)"
        )->execute([$name, $parentId, $userId]);
        return (int)$db->lastInsertId();
    }

    public static function renameFolder(int $id, string $name): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_folders SET name=? WHERE id=?"
        )->execute([$name, $id]);
    }

    public static function deleteFolder(int $id): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_folders WHERE id=?"
        )->execute([$id]);
    }

    public static function getFiles(
        ?int $folderId,
        int  $userId,
        bool $isAdmin,
        string $filterType = '',
        string $search = ''
    ): array {
        $params = [];

        $sql = "SELECT df.*, u.name AS uploader_name,
                       ds.permission AS share_permission,
                       dfs.permission AS folder_share_permission
                FROM dokumen_files df
                LEFT JOIN users u  ON u.id  = df.uploaded_by
                LEFT JOIN dokumen_shares ds ON ds.file_id = df.id AND ds.shared_to = ?
                LEFT JOIN dokumen_folder_shares dfs ON dfs.folder_id = df.folder_id AND dfs.shared_to = ?
                WHERE 1=1";
        $params[] = $userId;
        $params[] = $userId;

        if ($folderId === null) {
            $sql .= " AND df.folder_id IS NULL";
        } else {
            $sql .= " AND df.folder_id = ?";
            $params[] = $folderId;
        }

        if (!$isAdmin) {
            $sql .= " AND (df.uploaded_by = ? OR ds.id IS NOT NULL OR dfs.id IS NOT NULL)";
            $params[] = $userId;
        }

        if ($filterType !== '') {
            $sql .= " AND df.mime_type LIKE ?";
            $params[] = '%' . $filterType . '%';
        }

        if ($search !== '') {
            $sql .= " AND df.original_name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY df.created_at DESC";
        return Database::query($sql, $params);
    }

    public static function getSharedWithMe(int $userId): array
    {
        $fileShares = Database::query(
            "SELECT df.*, u.name AS uploader_name, ds.permission AS share_permission
             FROM dokumen_shares ds
             JOIN dokumen_files df ON df.id = ds.file_id
             LEFT JOIN users u ON u.id = df.uploaded_by
             WHERE ds.shared_to = ?
             ORDER BY df.created_at DESC",
            [$userId]
        );

        $folderFiles = Database::query(
            "SELECT df.*, u.name AS uploader_name, dfs.permission AS share_permission
             FROM dokumen_folder_shares dfs
             JOIN dokumen_files df ON df.folder_id = dfs.folder_id
             LEFT JOIN users u ON u.id = df.uploaded_by
             WHERE dfs.shared_to = ?
             ORDER BY df.created_at DESC",
            [$userId]
        );

        $merged = [];
        foreach (array_merge($fileShares, $folderFiles) as $row) {
            $merged[(int)$row['id']] = $row;
        }
        return array_values($merged);
    }

    public static function getRecent(int $userId, bool $isAdmin, int $limit = 20): array
    {
        if ($isAdmin) {
            return Database::query(
                "SELECT df.*, u.name AS uploader_name
                 FROM dokumen_files df
                 LEFT JOIN users u ON u.id = df.uploaded_by
                 ORDER BY df.updated_at DESC LIMIT ?",
                [$limit]
            );
        }
        return Database::query(
            "SELECT df.*, u.name AS uploader_name,
                    COALESCE(ds.permission, dfs.permission) AS share_permission
             FROM dokumen_files df
             LEFT JOIN users u ON u.id = df.uploaded_by
             LEFT JOIN dokumen_shares ds ON ds.file_id = df.id AND ds.shared_to = ?
             LEFT JOIN dokumen_folder_shares dfs ON dfs.folder_id = df.folder_id AND dfs.shared_to = ?
             WHERE df.uploaded_by = ? OR ds.id IS NOT NULL OR dfs.id IS NOT NULL
             ORDER BY df.updated_at DESC LIMIT ?",
            [$userId, $userId, $userId, $limit]
        );
    }

    public static function getFileById(int $id): ?array
    {
        return Database::queryOne(
            "SELECT df.*, u.name AS uploader_name
             FROM dokumen_files df
             LEFT JOIN users u ON u.id = df.uploaded_by
             WHERE df.id = ?",
            [$id]
        ) ?: null;
    }

    public static function insertFile(array $data): int
    {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO dokumen_files
             (folder_id, original_name, stored_name, file_path, mime_type, file_size, uploaded_by)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([
            $data['folder_id']     ?? null,
            $data['original_name'],
            $data['stored_name'],
            $data['file_path'],
            $data['mime_type'],
            $data['file_size'],
            $data['uploaded_by'],
        ]);
        return (int)$db->lastInsertId();
    }

    public static function renameFile(int $id, string $name): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_files SET original_name=? WHERE id=?"
        )->execute([$name, $id]);
    }

    public static function moveFile(int $id, ?int $folderId): void
    {
        Database::getInstance()->prepare(
            "UPDATE dokumen_files SET folder_id=? WHERE id=?"
        )->execute([$folderId, $id]);
    }

    public static function deleteFile(int $id): void
    {
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_files WHERE id=?"
        )->execute([$id]);
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_shares WHERE file_id=?"
        )->execute([$id]);
    }

    public static function getStats(int $userId, bool $isAdmin): array
    {
        if ($isAdmin) {
            $row = Database::queryOne(
                "SELECT COUNT(*) AS total_files,
                        COALESCE(SUM(file_size),0) AS total_size
                 FROM dokumen_files"
            );
        } else {
            $row = Database::queryOne(
                "SELECT COUNT(DISTINCT df.id) AS total_files,
                        COALESCE(SUM(DISTINCT df.file_size),0) AS total_size
                 FROM dokumen_files df
                 LEFT JOIN dokumen_shares ds ON ds.file_id = df.id AND ds.shared_to = ?
                 LEFT JOIN dokumen_folder_shares dfs ON dfs.folder_id = df.folder_id AND dfs.shared_to = ?
                 WHERE df.uploaded_by = ? OR ds.id IS NOT NULL OR dfs.id IS NOT NULL",
                [$userId, $userId, $userId]
            );
        }
        $shared = Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM dokumen_shares WHERE shared_to = ?",
            [$userId]
        );
        $sharedFolders = Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM dokumen_folder_shares WHERE shared_to = ?",
            [$userId]
        );
        return [
            'total_files'  => (int)($row['total_files']  ?? 0),
            'total_size'   => (int)($row['total_size']   ?? 0),
            'shared_count' => (int)($shared['cnt']       ?? 0) + (int)($sharedFolders['cnt'] ?? 0),
        ];
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes < 1024)    return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public static function mimeLabel(string $mime): string
    {
        $map = [
            'pdf'          => 'PDF',
            'word'         => 'DOCX',
            'sheet'        => 'XLSX',
            'excel'        => 'XLS',
            'presentation' => 'PPTX',
            'powerpoint'   => 'PPT',
            'image/png'    => 'PNG',
            'image/jpeg'   => 'JPG',
            'image/gif'    => 'GIF',
            'image/webp'   => 'WEBP',
            'video/mp4'    => 'MP4',
            'video/'       => 'VIDEO',
            'audio/'       => 'AUDIO',
            'zip'          => 'ZIP',
            'text/plain'   => 'TXT',
            'text/csv'     => 'CSV',
        ];
        foreach ($map as $k => $v) {
            if (strpos($mime, $k) !== false) return $v;
        }
        return strtoupper(pathinfo($mime, PATHINFO_EXTENSION) ?: 'FILE');
    }

    public static function mimeColor(string $mime): string
    {
        if (strpos($mime, 'pdf')          !== false) return '#E53E3E';
        if (strpos($mime, 'word')         !== false) return '#2B6CB0';
        if (strpos($mime, 'sheet')        !== false) return '#276749';
        if (strpos($mime, 'excel')        !== false) return '#276749';
        if (strpos($mime, 'presentation') !== false) return '#C05621';
        if (strpos($mime, 'powerpoint')   !== false) return '#C05621';
        if (strpos($mime, 'image')        !== false) return '#6B46C1';
        if (strpos($mime, 'video')        !== false) return '#00718D';
        if (strpos($mime, 'audio')        !== false) return '#D53F8C';
        if (strpos($mime, 'zip')          !== false) return '#744210';
        return '#4A5568';
    }
}
