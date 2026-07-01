<?php
declare(strict_types=1);

class DokumenVersionModel
{
    public static function createTableIfNotExists(): void
    {
        $db = Database::getInstance();
        $db->exec(
            "CREATE TABLE IF NOT EXISTS dokumen_versions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                file_id INT NOT NULL,
                version_no INT NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                stored_name VARCHAR(255) NOT NULL,
                file_path VARCHAR(255) NOT NULL,
                mime_type VARCHAR(150) DEFAULT NULL,
                file_size BIGINT DEFAULT 0,
                uploaded_by INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uniq_file_version (file_id, version_no),
                INDEX idx_file_id (file_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public static function getLatestVersionNo(int $fileId): int
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT COALESCE(MAX(version_no), 0) FROM dokumen_versions WHERE file_id = ?");
        $st->execute([$fileId]);
        return (int)$st->fetchColumn();
    }

    public static function snapshotCurrentFile(array $file, int $uploadedBy): int
    {
        self::createTableIfNotExists();
        $db = Database::getInstance();
        $versionNo = self::getLatestVersionNo((int)$file['id']) + 1;

        $st = $db->prepare("INSERT INTO dokumen_versions
            (file_id, version_no, original_name, stored_name, file_path, mime_type, file_size, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $st->execute([
            (int)$file['id'],
            $versionNo,
            $file['original_name'],
            $file['stored_name'],
            $file['file_path'],
            $file['mime_type'],
            (int)$file['file_size'],
            $uploadedBy,
        ]);
        return $versionNo;
    }

    public static function getVersions(int $fileId): array
    {
        self::createTableIfNotExists();
        $db = Database::getInstance();
        $st = $db->prepare("SELECT dv.*, u.name AS uploader_name
            FROM dokumen_versions dv
            LEFT JOIN users u ON u.id = dv.uploaded_by
            WHERE dv.file_id = ?
            ORDER BY dv.version_no DESC");
        $st->execute([$fileId]);
        return $st->fetchAll();
    }

    public static function getById(int $versionId): ?array
    {
        self::createTableIfNotExists();
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM dokumen_versions WHERE id = ? LIMIT 1");
        $st->execute([$versionId]);
        $row = $st->fetch();
        return $row ?: null;
    }
}
