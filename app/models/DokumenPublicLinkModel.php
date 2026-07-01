<?php
declare(strict_types=1);

class DokumenPublicLinkModel
{
    public static function generateToken(): string
    {
        return bin2hex(random_bytes(24)); // 48-char hex
    }

    public static function create(
        int $fileId,
        string $permission,
        ?string $password,
        ?string $expiresAt,
        ?int $maxDownloads,
        int $userId
    ): array {
        $token = self::generateToken();
        $hash  = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
        $db    = Database::getInstance();
        $db->prepare("
            INSERT INTO dokumen_public_links
              (file_id, token, permission, password_hash, expires_at, max_downloads, created_by)
            VALUES (?,?,?,?,?,?,?)
        ")->execute([$fileId, $token, $permission, $hash, $expiresAt ?: null, $maxDownloads ?: null, $userId]);
        return self::getByToken($token);
    }

    public static function getByToken(string $token): ?array
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM dokumen_public_links WHERE token=?");
        $st->execute([$token]);
        return $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public static function forFile(int $fileId): array
    {
        $db = Database::getInstance();
        $st = $db->prepare("SELECT * FROM dokumen_public_links WHERE file_id=? ORDER BY created_at DESC");
        $st->execute([$fileId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare("DELETE FROM dokumen_public_links WHERE id=?")->execute([$id]);
    }

    public static function isValid(array $link): bool
    {
        if ($link['expires_at'] && strtotime($link['expires_at']) < time()) return false;
        if ($link['max_downloads'] !== null && (int)$link['download_count'] >= (int)$link['max_downloads']) return false;
        return true;
    }

    public static function incrementDownload(int $id): void
    {
        $db = Database::getInstance();
        $db->prepare("UPDATE dokumen_public_links SET download_count=download_count+1 WHERE id=?")->execute([$id]);
    }

    public static function checkPassword(array $link, string $input): bool
    {
        if (!$link['password_hash']) return true;
        return password_verify($input, $link['password_hash']);
    }
}
