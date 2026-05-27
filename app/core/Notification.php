<?php
declare(strict_types=1);

class Notification {
    public static function send(int $userId, string $type, string $title, string $message, array $data = []): void {
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO notifications (user_id, type, title, message, data) VALUES (?, ?, ?, ?, ?)"
        )->execute([$userId, $type, $title, $message, json_encode($data)]);
    }

    public static function sendBulk(array $userIds, string $type, string $title, string $message, array $data = []): void {
        foreach (array_unique($userIds) as $uid) {
            self::send((int)$uid, $type, $title, $message, $data);
        }
    }

    public static function getUnread(int $userId, int $limit = 20): array {
        return Database::query(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function countUnread(int $userId): int {
        $row = Database::queryOne(
            "SELECT COUNT(*) as total FROM notifications WHERE user_id = ? AND is_read = 0",
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
}
