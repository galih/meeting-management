<?php
/**
 * ActivityLog — helper statis untuk mencatat aktivitas ke tabel activity_logs.
 *
 * Cara pakai:
 *   ActivityLog::record('meeting.create', 'Membuat kegiatan: Rapat Evaluasi Q2', 'meeting', $id);
 */
class ActivityLog
{
    /**
     * Catat satu baris log.
     *
     * @param string      $action       Kode aksi, format: <modul>.<verb>  (contoh: meeting.delete)
     * @param string      $description  Keterangan bebas yang ditampilkan di UI
     * @param string|null $subjectType  Tipe objek terkait (meeting / user / auth / dll)
     * @param int|null    $subjectId    ID objek terkait
     */
    public static function record(
        string  $action,
        string  $description,
        ?string $subjectType = null,
        ?int    $subjectId   = null
    ): void {
        try {
            $user     = Auth::user();
            $userId   = $user ? (int)$user['id']   : null;
            $userName = $user ? ($user['name'] ?? null) : null;
            $userRole = $user ? ($user['role'] ?? null) : null;

            $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['REMOTE_ADDR']
                ?? null;
            // Ambil IP pertama jika ada proxy chain
            if ($ip && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }

            $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
            if ($ua) $ua = substr($ua, 0, 300);

            Database::getInstance()->prepare(
                "INSERT INTO activity_logs
                 (user_id, user_name, user_role, action, description,
                  subject_type, subject_id, ip_address, user_agent)
                 VALUES (?,?,?,?,?,?,?,?,?)"
            )->execute([
                $userId, $userName, $userRole,
                $action, $description,
                $subjectType, $subjectId,
                $ip, $ua,
            ]);
        } catch (Throwable $e) {
            // Jangan sampai error log mengganggu flow utama aplikasi
            error_log('[ActivityLog] ' . $e->getMessage());
        }
    }

    /** Label & badge-color untuk kode aksi */
    public static function badge(string $action): array
    {
        return match(true) {
            str_ends_with($action, '.login')   => ['Login',   'bg-green-lt',  'text-green'],
            str_ends_with($action, '.logout')  => ['Logout',  'bg-secondary-lt', 'text-secondary'],
            str_ends_with($action, '.failed')  => ['Gagal Login', 'bg-red-lt','text-danger'],
            str_ends_with($action, '.create')  => ['Dibuat',  'bg-blue-lt',   'text-blue'],
            str_ends_with($action, '.update')  => ['Diubah',  'bg-yellow-lt', 'text-yellow'],
            str_ends_with($action, '.delete')  => ['Dihapus', 'bg-red-lt',    'text-danger'],
            str_ends_with($action, '.status')  => ['Status',  'bg-purple-lt', 'text-purple'],
            default                            => ['Lainnya', 'bg-secondary-lt','text-secondary'],
        };
    }

    /** Ambil daftar log dengan filter & paginasi */
    public static function paginate(
        int    $page     = 1,
        int    $perPage  = 30,
        array  $filters  = []
    ): array {
        $where  = ['1=1'];
        $params = [];

        if (!empty($filters['user_id'])) {
            $where[]  = 'al.user_id = ?';
            $params[] = (int)$filters['user_id'];
        }
        if (!empty($filters['action'])) {
            $where[]  = 'al.action LIKE ?';
            $params[] = '%' . $filters['action'] . '%';
        }
        if (!empty($filters['subject_type'])) {
            $where[]  = 'al.subject_type = ?';
            $params[] = $filters['subject_type'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(al.created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(al.created_at) <= ?';
            $params[] = $filters['date_to'];
        }

        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $total = (int)(Database::queryOne(
            "SELECT COUNT(*) AS cnt FROM activity_logs al WHERE {$whereStr}",
            $params
        )['cnt'] ?? 0);

        $rows = Database::query(
            "SELECT al.*, u.name AS current_name
             FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id
             WHERE {$whereStr}
             ORDER BY al.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return [
            'data'        => $rows,
            'total'       => $total,
            'page'        => $page,
            'perPage'     => $perPage,
            'totalPages'  => (int)ceil($total / $perPage),
        ];
    }
}
