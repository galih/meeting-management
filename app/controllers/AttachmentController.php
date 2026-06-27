<?php
declare(strict_types=1);

class AttachmentController
{
    /* ------------------------------------------------------------------ */
    /*  LIST                                                                */
    /* ------------------------------------------------------------------ */
    public static function index(): void
    {
        Auth::requireLogin();
        $meetingId = (int)($_GET['meeting_id'] ?? 0);
        if (!$meetingId) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'meeting_id diperlukan']); exit; }

        $rows = Database::query(
            "SELECT a.*, u.name AS uploader_name
             FROM attachments a
             LEFT JOIN users u ON u.id = a.uploaded_by
             WHERE a.meeting_id = ?
             ORDER BY a.created_at DESC",
            [$meetingId]
        );

        foreach ($rows as &$row) {
            $row['icon']     = self::iconForMime($row['mime_type'] ?? '');
            $row['size_fmt'] = self::formatSize((int)($row['file_size'] ?? 0));
        }
        unset($row);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  UPLOAD                                                              */
    /* ------------------------------------------------------------------ */
    public static function upload(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $meetingId = (int)($_POST['meeting_id'] ?? 0);
        if (!$meetingId) { echo json_encode(['success'=>false,'message'=>'meeting_id diperlukan']); exit; }

        $meeting = Database::queryOne("SELECT id FROM meetings WHERE id=?", [$meetingId]);
        if (!$meeting) { echo json_encode(['success'=>false,'message'=>'Meeting tidak ditemukan']); exit; }

        if (!Auth::hasRole('admin', 'sekretaris')) {
            $isMember = Database::queryOne(
                "SELECT id FROM meeting_participants WHERE meeting_id=? AND user_id=?",
                [$meetingId, Auth::id()]
            );
            if (!$isMember) { echo json_encode(['success'=>false,'message'=>'Anda tidak terdaftar di meeting ini']); exit; }
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $codes = [1=>'Ukuran melebihi php.ini',2=>'Ukuran melebihi MAX_FILE_SIZE',3=>'Upload tidak lengkap',4=>'Tidak ada file',6=>'Tidak ada folder tmp',7=>'Gagal tulis ke disk',8=>'Upload dihentikan ekstensi'];
            $msg   = $codes[$file['error'] ?? 0] ?? 'Upload gagal';
            echo json_encode(['success'=>false,'message'=>$msg]); exit;
        }

        $allowedMimes = [
            'application/pdf','application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'image/jpeg','image/png','image/gif','image/webp',
            'text/plain','text/csv',
            'application/zip','application/x-zip-compressed',
        ];
        $maxSize = 10 * 1024 * 1024; // 10 MB
        $mime    = mime_content_type($file['tmp_name']);

        if (!in_array($mime, $allowedMimes)) {
            echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan. Gunakan PDF, Word, Excel, PowerPoint, gambar, atau ZIP.']); exit;
        }
        if ($file['size'] > $maxSize) {
            echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 10 MB.']); exit;
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin');
        $filename = 'attachment_' . $meetingId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir      = ROOT_PATH . '/assets/uploads/attachments/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file']); exit;
        }

        $filePath = '/assets/uploads/attachments/' . $filename;
        $db       = Database::getInstance();
        $db->prepare(
            "INSERT INTO attachments (meeting_id, uploaded_by, original_name, file_path, mime_type, file_size)
             VALUES (?,?,?,?,?,?)"
        )->execute([$meetingId, Auth::id(), $file['name'], $filePath, $mime, $file['size']]);

        ActivityLog::record('attachment.create', 'Upload lampiran: ' . $file['name'], 'meeting', $meetingId);

        echo json_encode(['success'=>true,'message'=>'File berhasil diupload','file_path'=>BASE_URL.$filePath,'original_name'=>$file['name'],'icon'=>self::iconForMime($mime)]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  DELETE                                                              */
    /* ------------------------------------------------------------------ */
    public static function delete(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) { echo json_encode(['success'=>false,'message'=>'ID tidak valid']); exit; }

        $att = Database::queryOne("SELECT * FROM attachments WHERE id=?", [$id]);
        if (!$att) { echo json_encode(['success'=>false,'message'=>'Lampiran tidak ditemukan']); exit; }

        if (!Auth::hasRole('admin') && $att['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Anda tidak berhak menghapus file ini']); exit;
        }

        $path = ROOT_PATH . $att['file_path'];
        if (file_exists($path)) @unlink($path);

        Database::getInstance()->prepare("DELETE FROM attachments WHERE id=?")->execute([$id]);
        ActivityLog::record('attachment.delete', 'Hapus lampiran: '.$att['original_name'], 'meeting', $att['meeting_id']);

        echo json_encode(['success'=>true,'message'=>'Lampiran berhasil dihapus']); exit;
    }

    /* ------------------------------------------------------------------ */
    /*  HELPERS                                                             */
    /* ------------------------------------------------------------------ */

    /**
     * PHP 7.4 compat: ganti match(true) + str_contains dengan if-elseif chain
     */
    private static function iconForMime(string $mime): string
    {
        if (strpos($mime, 'pdf') !== false)          return '📄';
        if (strpos($mime, 'word') !== false)         return '📝';
        if (strpos($mime, 'sheet') !== false)        return '📊';
        if (strpos($mime, 'excel') !== false)        return '📊';
        if (strpos($mime, 'presentation') !== false) return '📋';
        if (strpos($mime, 'image') !== false)        return '🖼️';
        return '📎';
    }

    private static function formatSize(int $bytes): string
    {
        if ($bytes < 1024)        return $bytes . ' B';
        if ($bytes < 1048576)     return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 2) . ' MB';
    }
}
