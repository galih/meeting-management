<?php
class RecurringController
{
    public static function index(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $list = Database::query(
            "SELECT r.*, u.name AS creator_name, d.name AS dept_name,
                    (SELECT COUNT(*) FROM meetings m WHERE m.recurring_id = r.id) AS total_generated
             FROM recurring_meetings r
             LEFT JOIN users u ON u.id = r.created_by
             LEFT JOIN departments d ON d.id = r.department_id
             WHERE r.is_active = 1
             ORDER BY r.created_at DESC"
        );
        $users       = Database::query("SELECT id, name FROM users WHERE is_active=1 ORDER BY name");
        $departments = Database::query("SELECT id, name FROM departments WHERE is_active=1 ORDER BY name");

        View::layout('recurring/index', [
            'pageTitle'   => 'Recurring Meeting',
            'list'        => $list,
            'users'       => $users,
            'departments' => $departments,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $d  = $_POST;
        $db = Database::getInstance();
        $db->prepare(
            "INSERT INTO recurring_meetings
             (title, description, location, frequency, day_of_week, day_of_month,
              start_time, end_time, start_date, end_date, color, department_id, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)"
        )->execute([
            trim($d['title']),
            trim($d['description'] ?? ''),
            trim($d['location']    ?? ''),
            $d['frequency'],
            !empty($d['day_of_week'])   ? (int)$d['day_of_week']   : null,
            !empty($d['day_of_month'])  ? (int)$d['day_of_month']  : null,
            $d['start_time'],
            $d['end_time'],
            $d['start_date'],
            !empty($d['end_date'])      ? $d['end_date'] : null,
            $d['color'] ?? '#f76707',
            !empty($d['department_id']) ? (int)$d['department_id'] : null,
            Auth::id(),
        ]);
        $recurringId = (int)$db->lastInsertId();

        $participants = $_POST['participants'] ?? [];
        foreach ($participants as $uid) {
            $db->prepare(
                "INSERT IGNORE INTO recurring_participants (recurring_id, user_id) VALUES (?,?)"
            )->execute([$recurringId, (int)$uid]);
        }

        self::generateNext($recurringId);

        $_SESSION['flash_success'] = 'Recurring meeting berhasil dibuat & meeting pertama sudah digenerate.';
        header('Location: ' . BASE_URL . '/recurring'); exit;
    }

    public static function generate(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $count = self::generateNext($id);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => $count > 0 ? "{$count} meeting berhasil digenerate." : 'Tidak ada meeting baru (sudah melewati end_date).',
            'count'   => $count,
        ]); exit;
    }

    public static function delete(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        Database::getInstance()->prepare(
            "UPDATE recurring_meetings SET is_active=0 WHERE id=?"
        )->execute([$id]);
        header('Content-Type: application/json');
        echo json_encode(['success' => true]); exit;
    }

    public static function generateAll(): void
    {
        $recurrings = Database::query(
            "SELECT * FROM recurring_meetings
             WHERE is_active=1
               AND (end_date IS NULL OR end_date >= CURDATE())"
        );
        $total = 0;
        foreach ($recurrings as $r) {
            $total += self::generateNext($r['id']);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'total_generated' => $total]); exit;
    }

    private static function generateNext(int $recurringId): int
    {
        $r = Database::queryOne(
            "SELECT * FROM recurring_meetings WHERE id=?", [$recurringId]
        );
        if (!$r || !$r['is_active']) return 0;

        $from  = $r['last_generated']
            ? date('Y-m-d', strtotime($r['last_generated'] . ' +1 day'))
            : $r['start_date'];
        $until = date('Y-m-d', strtotime('+4 weeks'));
        if ($r['end_date'] && $r['end_date'] < $until) $until = $r['end_date'];

        $dates        = self::getDates($r, $from, $until);
        $count        = 0;
        $lastDate     = null;
        $participants = Database::query(
            "SELECT user_id FROM recurring_participants WHERE recurring_id=?", [$recurringId]
        );

        $db = Database::getInstance();
        foreach ($dates as $date) {
            $startDt = $date . ' ' . $r['start_time'];
            $endDt   = $date . ' ' . $r['end_time'];
            $exists  = Database::queryOne(
                "SELECT id FROM meetings WHERE recurring_id=? AND DATE(start_datetime)=?",
                [$recurringId, $date]
            );
            if ($exists) continue;

            $db->prepare(
                "INSERT INTO meetings
                 (title, description, location, start_datetime, end_datetime,
                  color, department_id, recurring_id, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?)"
            )->execute([
                $r['title'], $r['description'], $r['location'],
                $startDt, $endDt,
                $r['color'], $r['department_id'], $recurringId, $r['created_by'],
            ]);
            $meetingId = (int)$db->lastInsertId();

            foreach ($participants as $p) {
                $db->prepare(
                    "INSERT IGNORE INTO meeting_participants (meeting_id, user_id) VALUES (?,?)"
                )->execute([$meetingId, $p['user_id']]);
            }
            $lastDate = $date;
            $count++;
        }

        if ($lastDate) {
            $db->prepare(
                "UPDATE recurring_meetings SET last_generated=? WHERE id=?"
            )->execute([$lastDate, $recurringId]);
        }
        return $count;
    }

    private static function getDates(array $r, string $from, string $until): array
    {
        $dates   = [];
        $current = strtotime($from);
        $end     = strtotime($until);

        while ($current <= $end) {
            $dateStr = date('Y-m-d', $current);
            $dow     = (int)date('N', $current);
            $dom     = (int)date('j', $current);

            $match = match($r['frequency']) {
                'daily'    => true,
                'weekly'   => $r['day_of_week'] !== null
                                  ? (int)$r['day_of_week'] === ($dow % 7)
                                  : true,
                'biweekly' => $r['day_of_week'] !== null
                                  ? ((int)$r['day_of_week'] === ($dow % 7))
                                    && (floor((strtotime($dateStr) - strtotime($r['start_date'])) / (86400 * 14)) % 2 == 0)
                                  : false,
                'monthly'  => $r['day_of_month'] !== null
                                  ? $dom === (int)$r['day_of_month']
                                  : false,
                default    => false,
            };

            if ($match) $dates[] = $dateStr;
            $current = strtotime('+1 day', $current);
        }
        return $dates;
    }
}
