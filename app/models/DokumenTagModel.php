<?php
declare(strict_types=1);

class DokumenTagModel
{
    /* ---- Semua tag (dengan jumlah file) ---- */
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("
            SELECT t.*, k.name AS kategori_name, k.color AS kategori_color,
                   COUNT(ft.file_id) AS file_count
            FROM dokumen_tags t
            LEFT JOIN dokumen_kategoris k  ON k.id = t.kategori_id
            LEFT JOIN dokumen_file_tags ft ON ft.tag_id = t.id
            GROUP BY t.id
            ORDER BY t.name ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM dokumen_tags WHERE id=?");
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /* ---- Tag yang dimiliki sebuah file ---- */
    public static function forFile(int $fileId): array
    {
        $db = Database::getInstance();
        $st = $db->prepare("
            SELECT t.*, k.name AS kategori_name
            FROM dokumen_file_tags ft
            JOIN dokumen_tags t ON t.id = ft.tag_id
            LEFT JOIN dokumen_kategoris k ON k.id = t.kategori_id
            WHERE ft.file_id = ?
            ORDER BY t.name ASC
        ");
        $st->execute([$fileId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---- CRUD tag ---- */
    public static function create(string $name, string $color, ?int $kategoriId, int $userId): int
    {
        $db = Database::getInstance();
        $st = $db->prepare("INSERT INTO dokumen_tags (name, color, kategori_id, created_by) VALUES (?,?,?,?)");
        $st->execute([trim($name), $color, $kategoriId ?: null, $userId]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, string $name, string $color, ?int $kategoriId): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE dokumen_tags SET name=?, color=?, kategori_id=? WHERE id=?")
           ->execute([trim($name), $color, $kategoriId ?: null, $id]);
    }

    public static function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM dokumen_tags WHERE id=?")->execute([$id]);
    }

    /* ---- Relasi file ↔ tag ---- */
    public static function addToFile(int $fileId, int $tagId, int $userId): void
    {
        $db = Database::getInstance();
        $db->prepare("INSERT IGNORE INTO dokumen_file_tags (file_id, tag_id, added_by) VALUES (?,?,?)")
           ->execute([$fileId, $tagId, $userId]);
    }

    public static function removeFromFile(int $fileId, int $tagId): void
    {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM dokumen_file_tags WHERE file_id=? AND tag_id=?")
           ->execute([$fileId, $tagId]);
    }

    public static function syncFileTags(int $fileId, array $tagIds, int $userId): void
    {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM dokumen_file_tags WHERE file_id=?")->execute([$fileId]);
        if (!empty($tagIds)) {
            $st = $db->prepare("INSERT IGNORE INTO dokumen_file_tags (file_id, tag_id, added_by) VALUES (?,?,?)");
            foreach ($tagIds as $tid) $st->execute([$fileId, (int)$tid, $userId]);
        }
    }
}
