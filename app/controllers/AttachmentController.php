<?php
class AttachmentController
{
    private static int $MAX_SIZE   = 10485760; // 10 MB
    private static array $ALLOWED  = [
        'application/pdf', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'text/plain',
    ];

    private static function uploadDir(int $meetingId): string
    {
        // Coba assets/uploads dulu, fallback ke public/uploads
        $base = ROOT_PATH . '/assets/uploads/attachments/' . $meetingId . '/';
        if (is_dir($base)) return $base;
        return ROOT_PATH . '/public/uploads/attachments/' . $meetingId . '/';
    }

    /**
     * GET /api/meetings/{id}/attachments
     */
    public static function index(int $meetingId): void
    {
        // Cegah output apapun sebelum JSON
        while (ob_get_level()) ob_end_clean();

        Auth::requireAuth();

        $currentUserId = Auth::id();
        $isAdmin       = Auth::hasRole('admin', 'sekretaris');

        $list = Database::query(
            "SELECT a.*, u.name AS uploader_name
             FROM meeting_attachments a
             JOIN users u ON u.id = a.uploaded_by
             WHERE a.meeting_id = ?
             ORDER BY a.created_at DESC",
            [$meetingId]
        );

        foreach ($list as &$f) {
            $f['size_human']  = self::formatBytes((int)$f['file_size']);
            $f['icon']        = self::mimeIcon($f['mime_type']);
            $f['can_delete']  = $isAdmin || (int)$f['uploaded_by'] === (int)$currentUserId;
        }
        unset($f);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'attachments' => $list]);
        exit;
    }

    /**
     * POST /api/meetings/{id}/attachments
     */
    public static function upload(int $meetingId): void
    {
        while (ob_get_level()) ob_end_clean();
        Auth::requireRole('admin', 'sekretaris');

        if (empty($_FILES['file'])) {
            self::json(false, 'Tidak ada file yang dikirim'); return;
        }

        $file     = $_FILES['file'];
        $category = $_POST['category'] ?? 'lainnya';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            self::json(false, 'Upload gagal, kode error: ' . $file['error']); return;
        }
        if ($file['size'] > self::$MAX_SIZE) {
            self::json(false, 'Ukuran file maksimal 10 MB'); return;
        }

        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::$ALLOWED)) {
            self::json(false, 'Tipe file tidak diizinkan: ' . $mimeType); return;
        }

        // Buat folder jika belum ada
        $uploadDir = ROOT_PATH . '/assets/uploads/attachments/' . $meetingId . '/';
        if (!is_dir($uploadDir)) {
            if (!@mkdir($uploadDir, 0755, true)) {
                // Fallback ke public/uploads
                $uploadDir = ROOT_PATH . '/public/uploads/attachments/' . $meetingId . '/';
                if (!is_dir($uploadDir) && !@mkdir($uploadDir, 0755, true)) {
                    self::json(false, 'Gagal membuat folder upload. Periksa permission folder.'); return;
                }
            }
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin');
        $stored   = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $uploadDir . $stored;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            self::json(false, 'Gagal menyimpan file ke server'); return;
        }

        Database::getInstance()->prepare(
            "INSERT INTO meeting_attachments
             (meeting_id, uploaded_by, filename, stored_name, mime_type, file_size, category)
             VALUES (?,?,?,?,?,?,?)"
        )->execute([
            $meetingId, Auth::id(),
            $file['name'], $stored, $mimeType, $file['size'], $category
        ]);

        self::json(true, 'File berhasil diupload', [
            'filename'   => $file['name'],
            'size_human' => self::formatBytes($file['size']),
            'icon'       => self::mimeIcon($mimeType),
        ]);
    }

    /**
     * GET /attachments/{id}/download
     */
    public static function download(int $id): void
    {
        Auth::requireAuth();
        $att = Database::queryOne(
            "SELECT * FROM meeting_attachments WHERE id=?", [$id]
        );
        if (!$att) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }

        $path = self::uploadDir((int)$att['meeting_id']) . $att['stored_name'];
        if (!file_exists($path)) { http_response_code(404); echo 'File tidak ada di server.'; exit; }

        header('Content-Type: ' . $att['mime_type']);
        header('Content-Disposition: attachment; filename="' . addslashes($att['filename']) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path); exit;
    }

    /**
     * POST /api/attachments/{id}/delete
     */
    public static function delete(int $id): void
    {
        while (ob_get_level()) ob_end_clean();
        Auth::requireRole('admin', 'sekretaris');
        $att = Database::queryOne("SELECT * FROM meeting_attachments WHERE id=?", [$id]);
        if (!$att) { self::json(false, 'Tidak ditemukan'); return; }

        $path = self::uploadDir((int)$att['meeting_id']) . $att['stored_name'];
        if (file_exists($path)) @unlink($path);

        Database::getInstance()->prepare("DELETE FROM meeting_attachments WHERE id=?")->execute([$id]);
        self::json(true, 'File dihapus');
    }

    // ── Helpers ──────────────────────────────────────────────────────────
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    private static function mimeIcon(?string $mime): string
    {
        return match(true) {
            str_contains($mime ?? '', 'pdf')          => '📄',
            str_contains($mime ?? '', 'word')         => '📝',
            str_contains($mime ?? '', 'sheet')        => '📊',
            str_contains($mime ?? '', 'excel')        => '📊',
            str_contains($mime ?? '', 'presentation') => '📋',
            str_contains($mime ?? '', 'image')        => '🖼️',
            default                                    => '📎',
        };
    }

    private static function json(bool $success, string $message, array $extra = []): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array_merge(compact('success', 'message'), $extra));
        exit;
    }
}
