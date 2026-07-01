<?php
declare(strict_types=1);

class DokumenModel
{
    /* ------------------------------------------------------------------ */
    /*  FOLDER                                                              */
    /* ------------------------------------------------------------------ */

    public static function getFolders(?int $parentId = null): array
    {
        $sql = "SELECT f.*, u.name AS creator_name,
                       (SELECT COUNT(*) FROM dokumen_files df WHERE df.folder_id = f.id) AS file_count,
                       (SELECT COALESCE(SUM(df2.file_size),0) FROM dokumen_files df2 WHERE df2.folder_id = f.id) AS total_size
                FROM dokumen_folders f
                LEFT JOIN users u ON u.id = f.created_by
                WHERE f.parent_id " . ($parentId === null ? 'IS NULL' : '= ?') . "
                ORDER BY f.name ASC";
        $params = $parentId === null ? [] : [$parentId];
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

    /* ------------------------------------------------------------------ */
    /*  FILE — LIST                                                         */
    /* ------------------------------------------------------------------ */

    /**
     * Ambil file berdasarkan folder + filter opsional.
     * Jika $userId diisi, hanya tampilkan file milik user atau yang di-share ke user.
     */
    public static function getFiles(
        ?int $folderId,
        int  $userId,
        bool $isAdmin,
        string $filterType = '',
        string $search = ''
    ): array {
        $params = [];

        $sql = "SELECT df.*, u.name AS uploader_name,
                       ds.permission AS share_permission
                FROM dokumen_files df
                LEFT JOIN users u  ON u.id  = df.uploaded_by
                LEFT JOIN dokumen_shares ds ON ds.file_id = df.id AND ds.shared_to = ?
                WHERE 1=1";
        $params[] = $userId;

        // folder filter
        if ($folderId === null) {
            $sql .= " AND df.folder_id IS NULL";
        } else {
            $sql .= " AND df.folder_id = ?";
            $params[] = $folderId;
        }

        // akses: admin lihat semua, user hanya milik sendiri + di-share
        if (!$isAdmin) {
            $sql .= " AND (df.uploaded_by = ? OR ds.id IS NOT NULL)";
            $params[] = $userId;
        }

        // filter tipe
        if ($filterType !== '') {
            $sql .= " AND df.mime_type LIKE ?";
            $params[] = '%' . $filterType . '%';
        }

        // search
        if ($search !== '') {
            $sql .= " AND df.original_name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        $sql .= " ORDER BY df.created_at DESC";
        return Database::query($sql, $params);
    }

    /** Semua file yang di-share ke user tertentu */
    public static function getSharedWithMe(int $userId): array
    {
        return Database::query(
            "SELECT df.*, u.name AS uploader_name, ds.permission AS share_permission
             FROM dokumen_shares ds
             JOIN dokumen_files df ON df.id = ds.file_id
             LEFT JOIN users u ON u.id = df.uploaded_by
             WHERE ds.shared_to = ?
             ORDER BY df.created_at DESC",
            [$userId]
        );
    }

    /** File yang baru-baru ini diakses/diupload oleh user */
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
            "SELECT df.*, u.name AS uploader_name
             FROM dokumen_files df
             LEFT JOIN users u ON u.id = df.uploaded_by
             LEFT JOIN dokumen_shares ds ON ds.file_id = df.id AND ds.shared_to = ?
             WHERE df.uploaded_by = ? OR ds.id IS NOT NULL
             ORDER BY df.updated_at DESC LIMIT ?",
            [$userId, $userId, $limit]
        );
    }

    /* ------------------------------------------------------------------ */
    /*  FILE — CRUD                                                         */
    /* ------------------------------------------------------------------ */

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
        // hapus share record juga
        Database::getInstance()->prepare(
            "DELETE FROM dokumen_shares WHERE file_id=?"
        )->execute([$id]);
    }

    /* ------------------------------------------------------------------ */
    /*  STATS                                                               */
    /* ------------------------------------------------------------------ */

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
                "SELECT COUNT(*) AS total_files,
                        COALESCE(SUM(file_size),0) AS total_size
                 FROM dokumen_files WHERE uploaded_by = ?",
                [$userId]
            );
        }
        $shared = Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM dokumen_shares WHERE shared_to = ?",
            [$userId]
        );
        return [
            'total_files'  => (int)($row['total_files']  ?? 0),
            'total_size'   => (int)($row['total_size']   ?? 0),
            'shared_count' => (int)($shared['cnt']       ?? 0),
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */

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
