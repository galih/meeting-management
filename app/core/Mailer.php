<?php
/**
 * Mailer — wrapper PHPMailer untuk shared hosting
 * Prioritas config: app_settings (DB) → app/config/mail.php → default
 */
class Mailer
{
    private static function config(): array
    {
        try {
            $host = SettingController::get('smtp_host');
            if (!empty($host)) {
                return [
                    'driver'      => 'smtp',
                    'smtp_host'   => $host,
                    'smtp_port'   => (int) SettingController::get('smtp_port', '587'),
                    'smtp_secure' => SettingController::get('smtp_encryption', 'tls'),
                    'smtp_user'   => SettingController::get('smtp_username'),
                    'smtp_pass'   => SettingController::get('smtp_password'),
                    'from_email'  => SettingController::get('smtp_from_email'),
                    'from_name'   => SettingController::get('smtp_from_name', APP_NAME),
                ];
            }
        } catch (\Throwable $e) {
            // DB belum siap, lanjut ke fallback
        }

        $cfgFile = APP_PATH . '/config/mail.php';
        if (file_exists($cfgFile)) return require $cfgFile;

        return [
            'driver'     => 'mail',
            'from_email' => 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'),
            'from_name'  => defined('APP_NAME') ? APP_NAME : 'Meeting App',
        ];
    }

    public static function send(string $to, string $toName, string $subject, string $htmlBody): bool
    {
        $cfg = self::config();

        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return self::sendViaPHPMailer($cfg, $to, $toName, $subject, $htmlBody);
        }

        return self::sendViaMail($cfg, $to, $toName, $subject, $htmlBody);
    }

    private static function sendViaPHPMailer(array $cfg, string $to, string $toName, string $subject, string $body): bool
    {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            if (($cfg['driver'] ?? 'mail') === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = $cfg['smtp_host']   ?? 'localhost';
                $mail->SMTPAuth   = true;
                $mail->Username   = $cfg['smtp_user']   ?? '';
                $mail->Password   = $cfg['smtp_pass']   ?? '';
                $mail->SMTPSecure = $cfg['smtp_secure'] ?? 'tls';
                $mail->Port       = (int)($cfg['smtp_port'] ?? 587);
            }
            $mail->setFrom($cfg['from_email'], $cfg['from_name']);
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->AltBody = strip_tags($body);
            return $mail->send();
        } catch (\Exception $e) {
            error_log('Mailer error: ' . $e->getMessage());
            return false;
        }
    }

    private static function sendViaMail(array $cfg, string $to, string $toName, string $subject, string $body): bool
    {
        $from     = $cfg['from_email'];
        $fromName = $cfg['from_name'];
        $headers  = implode("\r\n", [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            "From: {$fromName} <{$from}>",
            "Reply-To: {$from}",
            'X-Mailer: PHP/' . PHP_VERSION,
        ]);
        return mail($to, $subject, $body, $headers);
    }

    public static function queue(string $to, string $name, string $subject, string $body, string $type = 'invitation', ?string $scheduledAt = null): int
    {
        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO email_queue (`to`, `name`, subject, body, type, scheduled_at)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$to, $name, $subject, $body, $type, $scheduledAt ?? date('Y-m-d H:i:s')]);
        return (int) $db->lastInsertId();
    }

    public static function processQueue(int $limit = 20): array
    {
        $limit = max(1, min(500, $limit));
        $db    = Database::getInstance();
        $rows  = Database::query(
            "SELECT * FROM email_queue
             WHERE status='pending' AND attempts < 3 AND scheduled_at <= NOW()
             ORDER BY scheduled_at ASC
             LIMIT " . $limit
        );
        $result = ['sent' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $ok = self::send($row['to'], $row['name'] ?? '', $row['subject'], $row['body']);
            $db->prepare(
                "UPDATE email_queue
                 SET status=?, attempts=attempts+1, sent_at=IF(?=1,NOW(),NULL), error_msg=?
                 WHERE id=?"
            )->execute([$ok ? 'sent' : 'failed', $ok ? 1 : 0, $ok ? null : 'Gagal kirim', $row['id']]);
            $ok ? $result['sent']++ : $result['failed']++;
        }
        return $result;
    }
}
