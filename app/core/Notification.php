<?php
declare(strict_types=1);

/**
 * Skema tabel notifications:
 *   id, user_id, type, message, url, is_read, created_at
 */
class Notification {

    public static function send(int $userId, string $type, string $message, string $url = ''): void {
        Database::getInstance()->prepare(
            "INSERT INTO notifications (user_id, type, message, url) VALUES (?, ?, ?, ?)"
        )->execute([$userId, $type, $message, $url]);
    }

    public static function sendBulk(array $userIds, string $type, string $message, string $url = ''): void {
        foreach (array_unique($userIds) as $uid) {
            self::send((int)$uid, $type, $message, $url);
        }
    }

    /**
     * Ambil notifikasi unread + tambahkan field created_at_human untuk sidebar dropdown.
     */
    public static function getUnread(int $userId, int $limit = 20): array {
        $rows = Database::query(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
        foreach ($rows as &$row) {
            $row['is_read']          = (int)$row['is_read'];           // fix: pastikan int bukan string
            $row['created_at_human'] = self::timeAgo($row['created_at'] ?? '');
        }
        return $rows;
    }

    /**
     * Ambil semua notifikasi (read + unread) dengan human time — untuk halaman /notifications.
     */
    public static function getAll(int $userId, int $limit = 20, int $offset = 0): array {
        $rows = Database::query(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$userId, $limit, $offset]
        );
        foreach ($rows as &$row) {
            $row['is_read']          = (int)$row['is_read'];
            $row['created_at_human'] = self::timeAgo($row['created_at'] ?? '');
        }
        return $rows;
    }

    public static function countUnread(int $userId): int {
        $row = Database::queryOne(
            "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int)($row['total'] ?? 0);
    }

    public static function markRead(int $id, int $userId): void {
        Database::getInstance()
            ->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?")
            ->execute([$id, $userId]);
    }

    public static function markAllRead(int $userId): void {
        Database::getInstance()
            ->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ?")
            ->execute([$userId]);
    }

    public static function deleteAll(int $userId): void {
        Database::getInstance()
            ->prepare("DELETE FROM notifications WHERE user_id = ?")
            ->execute([$userId]);
    }

    // ── Helper: human-readable time ago ─────────────────────────────
    public static function timeAgo(string $datetime): string {
        if (!$datetime) return '';
        $diff = time() - strtotime($datetime);
        if ($diff < 0)      return 'Baru saja';
        if ($diff < 60)     return 'Baru saja';
        if ($diff < 3600)   return floor($diff / 60) . ' menit lalu';
        if ($diff < 86400)  return floor($diff / 3600) . ' jam lalu';
        if ($diff < 604800) return floor($diff / 86400) . ' hari lalu';
        return date('d M Y', strtotime($datetime));
    }
}
