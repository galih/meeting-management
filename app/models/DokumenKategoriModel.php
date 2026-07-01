<?php
declare(strict_types=1);

class DokumenKategoriModel
{
    public static function all(): array
    {
        $db = Database::getInstance();
        return $db->query("SELECT k.*, u.name AS creator_name
            FROM dokumen_kategoris k
            LEFT JOIN users u ON u.id = k.created_by
            ORDER BY k.name ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getById(int $id): ?array
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM dokumen_kategoris WHERE id=?");
        $st->execute([$id]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function create(string $name, string $color, int $userId): int
    {
        $db = Database::getInstance();
        $st = $db->prepare("INSERT INTO dokumen_kategoris (name, color, created_by) VALUES (?,?,?)");
        $st->execute([trim($name), $color, $userId]);
        return (int)$db->lastInsertId();
    }

    public static function update(int $id, string $name, string $color): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE dokumen_kategoris SET name=?, color=? WHERE id=?")
           ->execute([trim($name), $color, $id]);
    }

    public static function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM dokumen_kategoris WHERE id=?")->execute([$id]);
    }
}
