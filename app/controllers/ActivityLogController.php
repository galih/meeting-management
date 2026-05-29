<?php
class ActivityLogController
{
    public static function index(): void
    {
        Auth::requireRole('admin');

        $page    = max(1, (int)($_GET['page']     ?? 1));
        $perPage = 30;

        $filters = [
            'user_id'      => $_GET['user_id']      ?? '',
            'action'       => $_GET['action']        ?? '',
            'subject_type' => $_GET['subject_type']  ?? '',
            'date_from'    => $_GET['date_from']     ?? '',
            'date_to'      => $_GET['date_to']       ?? '',
        ];

        $result = ActivityLog::paginate($page, $perPage, $filters);
        $users  = Database::query("SELECT id, name FROM users ORDER BY name");

        View::layout('activity-log/index', [
            'pageTitle'  => 'Log Aktivitas',
            'logs'       => $result['data'],
            'total'      => $result['total'],
            'page'       => $result['page'],
            'perPage'    => $result['perPage'],
            'totalPages' => $result['totalPages'],
            'filters'    => $filters,
            'users'      => $users,
        ]);
    }

    public static function purge(): void
    {
        Auth::requireRole('admin');

        $days = max(1, (int)($_POST['days'] ?? 90));
        $db   = Database::getInstance();
        $db->prepare(
            "DELETE FROM activity_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)"
        )->execute([$days]);

        $_SESSION['flash_success'] = "Log lebih dari {$days} hari berhasil dihapus.";
        header('Location: ' . BASE_URL . '/admin/activity-log'); exit;
    }
}
