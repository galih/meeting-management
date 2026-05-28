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

        // Ambil tahun yang tersedia untuk dropdown chart
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
     * Mengembalikan JSON jumlah kegiatan per bulan untuk tahun tertentu
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
                 GROUP BY bulan
                 ORDER BY bulan ASC",
                [$year]
            );
        } else {
            $rows = Database::query(
                "SELECT MONTH(m.start_datetime) AS bulan, COUNT(*) AS total
                 FROM meetings m
                 JOIN meeting_participants mp ON m.id = mp.meeting_id
                 WHERE mp.user_id = ? AND YEAR(m.start_datetime) = ?
                 GROUP BY bulan
                 ORDER BY bulan ASC",
                [$uid, $year]
            );
        }

        // Isi semua 12 bulan, default 0
        $data = array_fill(1, 12, 0);
        foreach ($rows as $r) {
            $data[(int)$r['bulan']] = (int)$r['total'];
        }

        header('Content-Type: application/json');
        echo json_encode(['year' => $year, 'data' => array_values($data)]);
        exit;
    }
}
