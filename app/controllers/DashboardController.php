<?php
declare(strict_types=1);

class DashboardController {

    public static function index(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $uid  = (int)$user['id'];

        if ($user['role'] === 'admin') {
            $stats = [
                'total_meetings'  => (int)(Database::queryOne("SELECT COUNT(*) c FROM meetings")['c'] ?? 0),
                'meeting_ongoing' => (int)(Database::queryOne("SELECT COUNT(*) c FROM meetings WHERE status='ongoing'")['c'] ?? 0),
                'meeting_today'   => (int)(Database::queryOne("SELECT COUNT(*) c FROM meetings WHERE DATE(start_datetime)=CURDATE()")['c'] ?? 0),
                'total_users'     => (int)(Database::queryOne("SELECT COUNT(*) c FROM users WHERE is_active=1")['c'] ?? 0),
                'tl_pending'      => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE status='pending'")['c'] ?? 0),
                'tl_overdue'      => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE due_date < CURDATE() AND status NOT IN ('done','cancelled')")['c'] ?? 0),
                'tl_done'         => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE status='done'")['c'] ?? 0),
                'notif_unread'    => Notification::countUnread($uid),
            ];
        } else {
            $stats = [
                'total_meetings'  => (int)(Database::queryOne("SELECT COUNT(*) c FROM meeting_participants WHERE user_id=?", [$uid])['c'] ?? 0),
                'meeting_today'   => (int)(Database::queryOne("SELECT COUNT(*) c FROM meetings m JOIN meeting_participants mp ON m.id=mp.meeting_id WHERE mp.user_id=? AND DATE(m.start_datetime)=CURDATE()", [$uid])['c'] ?? 0),
                'tl_pending'      => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND status='pending'", [$uid])['c'] ?? 0),
                'tl_overdue'      => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND due_date < CURDATE() AND status NOT IN ('done','cancelled')", [$uid])['c'] ?? 0),
                'tl_done'         => (int)(Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND status='done'", [$uid])['c'] ?? 0),
                'notif_unread'    => Notification::countUnread($uid),
            ];
        }

        if ($user['role'] === 'admin') {
            $upcoming = Database::query(
                "SELECT m.*, u.name AS creator_name,
                        COUNT(mp.id) AS total_peserta
                 FROM meetings m
                 JOIN users u ON m.created_by = u.id
                 LEFT JOIN meeting_participants mp ON m.id = mp.meeting_id
                 WHERE m.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                   AND m.status != 'cancelled'
                 GROUP BY m.id
                 ORDER BY m.start_datetime ASC LIMIT 5"
            );
        } else {
            $upcoming = Database::query(
                "SELECT m.*, u.name AS creator_name,
                        COUNT(mp2.id) AS total_peserta
                 FROM meetings m
                 JOIN users u ON m.created_by = u.id
                 JOIN meeting_participants mp ON m.id = mp.meeting_id
                 LEFT JOIN meeting_participants mp2 ON m.id = mp2.meeting_id
                 WHERE mp.user_id = ?
                   AND m.start_datetime BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                   AND m.status != 'cancelled'
                 GROUP BY m.id
                 ORDER BY m.start_datetime ASC LIMIT 5",
                [$uid]
            );
        }

        $tlDeadline = ($user['role'] === 'admin')
            ? Database::query(
                "SELECT tl.*, m.title AS meeting_title, u.name AS assigned_name
                 FROM tindak_lanjut tl
                 JOIN meetings m ON tl.meeting_id = m.id
                 LEFT JOIN users u ON tl.assigned_to = u.id
                 WHERE tl.status NOT IN ('done','cancelled')
                 ORDER BY tl.due_date ASC LIMIT 5"
              )
            : Database::query(
                "SELECT tl.*, m.title AS meeting_title, u.name AS assigned_name
                 FROM tindak_lanjut tl
                 JOIN meetings m ON tl.meeting_id = m.id
                 LEFT JOIN users u ON tl.assigned_to = u.id
                 WHERE tl.assigned_to = ? AND tl.status NOT IN ('done','cancelled')
                 ORDER BY tl.due_date ASC LIMIT 5",
                [$uid]
              );

        $recentActivity = Database::query(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC LIMIT 8",
            [$uid]
        );

        $notifications = Notification::getUnread($uid, 5);

        $availableYears = Database::query(
            "SELECT DISTINCT YEAR(start_datetime) AS yr FROM meetings ORDER BY yr DESC"
        );
        $availableYears = array_column($availableYears, 'yr');
        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        View::layout('dashboard/index', [
            'pageTitle'      => 'Dashboard',
            'user'           => $user,
            'stats'          => $stats,
            'upcoming'       => $upcoming,
            'tlDeadline'     => $tlDeadline,
            'recentActivity' => $recentActivity,
            'notifications'  => $notifications,
            'availableYears' => $availableYears,
        ]);
    }

    /**
     * GET /api/dashboard/chart-monthly?year=2026
     */
    public static function chartMonthly(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $uid  = (int)$user['id'];
        $year = (int)($_GET['year'] ?? date('Y'));

        if ($user['role'] === 'admin') {
            $rows = Database::query(
                "SELECT MONTH(start_datetime) AS bulan, COUNT(*) AS total
                 FROM meetings
                 WHERE YEAR(start_datetime) = ?
                 GROUP BY bulan ORDER BY bulan ASC",
                [$year]
            );
        } else {
            $rows = Database::query(
                "SELECT MONTH(m.start_datetime) AS bulan, COUNT(*) AS total
                 FROM meetings m
                 JOIN meeting_participants mp ON m.id = mp.meeting_id
                 WHERE mp.user_id = ? AND YEAR(m.start_datetime) = ?
                 GROUP BY bulan ORDER BY bulan ASC",
                [$uid, $year]
            );
        }

        $data = array_fill(1, 12, 0);
        foreach ($rows as $r) {
            $data[(int)$r['bulan']] = (int)$r['total'];
        }

        header('Content-Type: application/json');
        echo json_encode(['year' => $year, 'data' => array_values($data)]);
        exit;
    }

    /**
     * GET /api/dashboard/chart-tl-status
     * Distribusi status tindak lanjut (donut chart)
     */
    public static function chartTlStatus(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $uid  = (int)$user['id'];

        if ($user['role'] === 'admin') {
            $rows = Database::query(
                "SELECT status, COUNT(*) AS total FROM tindak_lanjut GROUP BY status"
            );
        } else {
            $rows = Database::query(
                "SELECT status, COUNT(*) AS total FROM tindak_lanjut WHERE assigned_to=? GROUP BY status",
                [$uid]
            );
        }

        $map = ['pending' => 0, 'in_progress' => 0, 'done' => 0, 'cancelled' => 0];
        foreach ($rows as $r) {
            if (isset($map[$r['status']])) {
                $map[$r['status']] = (int)$r['total'];
            }
        }

        // Overdue: pending/in_progress yang sudah lewat due_date
        if ($user['role'] === 'admin') {
            $map['overdue'] = (int)(Database::queryOne(
                "SELECT COUNT(*) c FROM tindak_lanjut
                 WHERE due_date < CURDATE() AND status NOT IN ('done','cancelled')"
            )['c'] ?? 0);
        } else {
            $map['overdue'] = (int)(Database::queryOne(
                "SELECT COUNT(*) c FROM tindak_lanjut
                 WHERE assigned_to=? AND due_date < CURDATE() AND status NOT IN ('done','cancelled')",
                [$uid]
            )['c'] ?? 0);
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $map]);
        exit;
    }

    /**
     * GET /api/dashboard/chart-top-dept
     * Top 5 departemen berdasarkan jumlah meeting (admin only)
     */
    public static function chartTopDept(): void {
        Auth::requireLogin();
        $user = Auth::user();

        if ($user['role'] !== 'admin') {
            header('Content-Type: application/json');
            echo json_encode(['labels' => [], 'data' => []]);
            exit;
        }

        $rows = Database::query(
            "SELECT d.name AS dept, COUNT(DISTINCT m.id) AS total
             FROM meetings m
             JOIN meeting_participants mp ON m.id = mp.meeting_id
             JOIN users u ON mp.user_id = u.id
             JOIN departments d ON u.department_id = d.id
             GROUP BY d.id
             ORDER BY total DESC
             LIMIT 5"
        );

        $labels = array_column($rows, 'dept');
        $data   = array_map('intval', array_column($rows, 'total'));

        header('Content-Type: application/json');
        echo json_encode(['labels' => $labels, 'data' => $data]);
        exit;
    }

    /**
     * GET /api/dashboard/chart-tl-trend?year=2026
     * Tren tindak lanjut: selesai vs terlambat per bulan
     */
    public static function chartTlTrend(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $uid  = (int)$user['id'];
        $year = (int)($_GET['year'] ?? date('Y'));

        $baseWhere = $user['role'] === 'admin' ? '' : 'AND tl.assigned_to = ' . $uid;

        $rowsDone = Database::query(
            "SELECT MONTH(updated_at) AS bulan, COUNT(*) AS total
             FROM tindak_lanjut tl
             WHERE status = 'done' AND YEAR(updated_at) = ? $baseWhere
             GROUP BY bulan ORDER BY bulan ASC",
            [$year]
        );

        $rowsOverdue = Database::query(
            "SELECT MONTH(due_date) AS bulan, COUNT(*) AS total
             FROM tindak_lanjut tl
             WHERE due_date < CURDATE()
               AND status NOT IN ('done','cancelled')
               AND YEAR(due_date) = ? $baseWhere
             GROUP BY bulan ORDER BY bulan ASC",
            [$year]
        );

        $done    = array_fill(1, 12, 0);
        $overdue = array_fill(1, 12, 0);
        foreach ($rowsDone    as $r) { $done[(int)$r['bulan']]    = (int)$r['total']; }
        foreach ($rowsOverdue as $r) { $overdue[(int)$r['bulan']] = (int)$r['total']; }

        header('Content-Type: application/json');
        echo json_encode([
            'year'    => $year,
            'done'    => array_values($done),
            'overdue' => array_values($overdue),
        ]);
        exit;
    }
}
