<?php
declare(strict_types=1);

class RecurringController
{
    /* ------------------------------------------------------------------ */
    /*  LIST                                                                */
    /* ------------------------------------------------------------------ */
    public static function index(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $recurring = Database::query(
            "SELECT r.*, u.name AS creator_name, d.name AS dept_name
             FROM recurring_meetings r
             LEFT JOIN users      u ON u.id = r.created_by
             LEFT JOIN departments d ON d.id = r.department_id
             ORDER BY r.created_at DESC"
        );
        View::layout('recurring/index', [
            'pageTitle' => 'Jadwal Berulang',
            'recurring' => $recurring,
        ]);
    }

    /* ------------------------------------------------------------------ */
    /*  STORE                                                               */
    /* ------------------------------------------------------------------ */
    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');
        Auth::csrfCheck();

        $data = self::validateInput($_POST);
        if (isset($data['error'])) { echo json_encode(['success'=>false,'message'=>$data['error']]); exit; }

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO recurring_meetings
             (title, frequency, day_of_week, day_of_month, start_time, end_time,
              location, description, department_id, participants, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->execute([
            $data['title'],
            $data['frequency'],
            $data['day_of_week']   ?: null,
            $data['day_of_month']  ?: null,
            $data['start_time'],
            $data['end_time'],
            $data['location']      ?: null,
            $data['description']   ?: null,
            $data['department_id'] ?: null,
            $data['participants'],
            Auth::id(),
        ]);

        ActivityLog::record('recurring.create', 'Buat jadwal berulang: ' . $data['title']);
        echo json_encode(['success'=>true,'message'=>'Jadwal berulang berhasil dibuat.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  UPDATE                                                              */
    /* ------------------------------------------------------------------ */
    public static function update(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');
        Auth::csrfCheck();

        $id = (int)($_POST['id'] ?? 0);
        $r  = Database::queryOne("SELECT * FROM recurring_meetings WHERE id=?", [$id]);
        if (!$r) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan']); exit; }

        $data = self::validateInput($_POST);
        if (isset($data['error'])) { echo json_encode(['success'=>false,'message'=>$data['error']]); exit; }

        Database::getInstance()->prepare(
            "UPDATE recurring_meetings
             SET title=?, frequency=?, day_of_week=?, day_of_month=?,
                 start_time=?, end_time=?, location=?, description=?,
                 department_id=?, participants=?
             WHERE id=?"
        )->execute([
            $data['title'], $data['frequency'],
            $data['day_of_week']  ?: null, $data['day_of_month'] ?: null,
            $data['start_time'],  $data['end_time'],
            $data['location']     ?: null, $data['description']  ?: null,
            $data['department_id'] ?: null, $data['participants'],
            $id,
        ]);

        ActivityLog::record('recurring.update', 'Ubah jadwal berulang: ' . $data['title'], 'recurring', $id);
        echo json_encode(['success'=>true,'message'=>'Jadwal berhasil diperbarui.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  DELETE                                                              */
    /* ------------------------------------------------------------------ */
    public static function delete(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');
        Auth::csrfCheck();

        $id = (int)($_POST['id'] ?? 0);
        $r  = Database::queryOne("SELECT * FROM recurring_meetings WHERE id=?", [$id]);
        if (!$r) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan']); exit; }

        Database::getInstance()->prepare("DELETE FROM recurring_meetings WHERE id=?")->execute([$id]);
        ActivityLog::record('recurring.delete', 'Hapus jadwal berulang: '.$r['title'], 'recurring', $id);
        echo json_encode(['success'=>true,'message'=>'Jadwal berhasil dihapus.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  GENERATE (buat meeting dari recurring)                             */
    /* ------------------------------------------------------------------ */
    public static function generate(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');
        Auth::csrfCheck();

        $id = (int)($_POST['id'] ?? 0);
        $r  = Database::queryOne("SELECT * FROM recurring_meetings WHERE id=?", [$id]);
        if (!$r) { echo json_encode(['success'=>false,'message'=>'Data tidak ditemukan']); exit; }

        $targetDate = $_POST['target_date'] ?? null;
        if (!$targetDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $targetDate)) {
            echo json_encode(['success'=>false,'message'=>'Format tanggal tidak valid']); exit;
        }

        // PHP 7.4 compat: ganti match() dengan array lookup
        $freqMap = [
            'weekly'    => 'Mingguan',
            'biweekly'  => 'Dua Mingguan',
            'monthly'   => 'Bulanan',
            'quarterly' => 'Triwulanan',
        ];
        $freqLabel = $freqMap[$r['frequency']] ?? ucfirst($r['frequency']);

        $title      = "[{$freqLabel}] {$r['title']} - " . date('d M Y', strtotime($targetDate));
        $startDt    = $targetDate . ' ' . $r['start_time'];
        $endDt      = $targetDate . ' ' . $r['end_time'];

        $db   = Database::getInstance();
        $stmt = $db->prepare(
            "INSERT INTO meetings (title, start_datetime, end_datetime, location, description, department_id, created_by, status, color)
             VALUES (?,?,?,?,?,?,?,'scheduled','#206bc4')"
        );
        $stmt->execute([
            $title, $startDt, $endDt,
            $r['location'], $r['description'],
            $r['department_id'], Auth::id(),
        ]);
        $meetingId = (int)$db->lastInsertId();

        if (!empty($r['participants'])) {
            $ids = json_decode($r['participants'], true) ?: [];
            foreach ($ids as $uid) {
                $db->prepare(
                    "INSERT IGNORE INTO meeting_participants (meeting_id,user_id,status) VALUES (?,?,'invited')"
                )->execute([$meetingId, (int)$uid]);
            }
        }

        ActivityLog::record('recurring.generate', "Generate meeting dari jadwal berulang: {$r['title']}", 'meeting', $meetingId);
        echo json_encode([
            'success'    => true,
            'message'    => 'Meeting berhasil dibuat dari jadwal berulang.',
            'meeting_id' => $meetingId,
        ]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  VALIDATE INPUT                                                      */
    /* ------------------------------------------------------------------ */
    private static function validateInput(array $post): array
    {
        $title     = trim($post['title'] ?? '');
        $frequency = trim($post['frequency'] ?? '');
        $startTime = trim($post['start_time'] ?? '');
        $endTime   = trim($post['end_time'] ?? '');

        if (!$title)     return ['error' => 'Judul wajib diisi'];
        if (!$frequency) return ['error' => 'Frekuensi wajib dipilih'];
        if (!$startTime) return ['error' => 'Jam mulai wajib diisi'];
        if (!$endTime)   return ['error' => 'Jam selesai wajib diisi'];

        return [
            'title'         => $title,
            'frequency'     => $frequency,
            'day_of_week'   => (int)($post['day_of_week']   ?? 0),
            'day_of_month'  => (int)($post['day_of_month']  ?? 0),
            'start_time'    => $startTime,
            'end_time'      => $endTime,
            'location'      => trim($post['location']    ?? ''),
            'description'   => trim($post['description'] ?? ''),
            'department_id' => (int)($post['department_id'] ?? 0) ?: null,
            'participants'  => json_encode(array_map('intval', (array)($post['participants'] ?? []))),
        ];
    }
}
