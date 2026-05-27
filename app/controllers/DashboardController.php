<?php
declare(strict_types=1);

class DashboardController {

    public function index(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $uid  = (int)$user['id'];

        // ── Statistik berdasarkan role ──────────────────────────────
        if ($user['role'] === 'admin') {
            $stats = [
                'total_meetings'  => Database::queryOne("SELECT COUNT(*) c FROM meetings")['c'],
                'meeting_ongoing' => Database::queryOne("SELECT COUNT(*) c FROM meetings WHERE status='ongoing'")['c'],
                'meeting_today'   => Database::queryOne("SELECT COUNT(*) c FROM meetings WHERE DATE(start_datetime)=CURDATE()")['c'],
                'total_users'     => Database::queryOne("SELECT COUNT(*) c FROM users WHERE is_active=1")['c'],
                'tl_pending'      => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE status='pending'")['c'],
                'tl_overdue'      => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE due_date < CURDATE() AND status NOT IN ('done','cancelled')")['c'],
                'tl_done'         => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE status='done'")['c'],
                'notif_unread'    => Notification::countUnread($uid),
            ];
        } else {
            $stats = [
                'total_meetings'  => Database::queryOne("SELECT COUNT(*) c FROM meeting_participants WHERE user_id=?", [$uid])['c'],
                'meeting_today'   => Database::queryOne("SELECT COUNT(*) c FROM meetings m JOIN meeting_participants mp ON m.id=mp.meeting_id WHERE mp.user_id=? AND DATE(m.start_datetime)=CURDATE()", [$uid])['c'],
                'tl_pending'      => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND status='pending'", [$uid])['c'],
                'tl_overdue'      => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND due_date < CURDATE() AND status NOT IN ('done','cancelled')", [$uid])['c'],
                'tl_done'         => Database::queryOne("SELECT COUNT(*) c FROM tindak_lanjut WHERE assigned_to=? AND status='done'", [$uid])['c'],
                'notif_unread'    => Notification::countUnread($uid),
            ];
        }

        // ── Meeting mendatang (7 hari ke depan) ─────────────────────
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

        // ── Tindak lanjut deadline terdekat ─────────────────────────
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

        // ── Aktivitas terbaru ────────────────────────────────────────
        $recentActivity = Database::query(
            "SELECT * FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC LIMIT 8",
            [$uid]
        );

        // ── Notifikasi unread untuk navbar ───────────────────────────
        $notifications = Notification::getUnread($uid, 5);

        View::layout('dashboard/index', [
            'pageTitle'      => 'Dashboard',
            'user'           => $user,
            'stats'          => $stats,
            'upcoming'       => $upcoming,
            'tlDeadline'     => $tlDeadline,
            'recentActivity' => $recentActivity,
            'notifications'  => $notifications,
        ]);
    }
}
