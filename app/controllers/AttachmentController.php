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

    /**
     * GET /api/meetings/{id}/attachments
     */
    public static function index(int $meetingId): void
    {
        Auth::requireAuth();
        $list = Database::query(
            "SELECT a.*, u.name AS uploader_name
             FROM meeting_attachments a
             JOIN users u ON u.id = a.uploaded_by
             WHERE a.meeting_id = ?
             ORDER BY a.created_at DESC",
            [$meetingId]
        );
        // Format ukuran file
        foreach ($list as &$f) {
            $f['size_human'] = self::formatBytes($f['file_size']);
            $f['icon']       = self::mimeIcon($f['mime_type']);
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'attachments' => $list]); exit;
    }

    /**
     * POST /api/meetings/{id}/attachments
     * Upload file lampiran
     */
    public static function upload(int $meetingId): void
    {
        Auth::requireRole('admin', 'sekretaris');

        if (empty($_FILES['file'])) {
            self::json(false, 'Tidak ada file yang dikirim'); return;
        }

        $file     = $_FILES['file'];
        $category = $_POST['category'] ?? 'lainnya';

        // Validasi ukuran
        if ($file['size'] > self::$MAX_SIZE) {
            self::json(false, 'Ukuran file maksimal 10 MB'); return;
        }

        // Validasi MIME type (baca dari file, bukan header)
        $finfo    = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, self::$ALLOWED)) {
            self::json(false, 'Tipe file tidak diizinkan'); return;
        }

        // Buat folder upload jika belum ada
        $uploadDir = ROOT_PATH . '/public/uploads/attachments/' . $meetingId . '/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        // Nama file unik
        $ext        = pathinfo($file['name'], PATHINFO_EXTENSION);
        $stored     = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
        $destPath   = $uploadDir . $stored;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            self::json(false, 'Gagal menyimpan file'); return;
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
     * Download file lampiran
     */
    public static function download(int $id): void
    {
        Auth::requireAuth();
        $att = Database::queryOne(
            "SELECT * FROM meeting_attachments WHERE id=?", [$id]
        );
        if (!$att) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }

        $path = ROOT_PATH . '/public/uploads/attachments/' . $att['meeting_id'] . '/' . $att['stored_name'];
        if (!file_exists($path)) { http_response_code(404); echo 'File tidak ditemukan di server.'; exit; }

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
        Auth::requireRole('admin', 'sekretaris');
        $att = Database::queryOne("SELECT * FROM meeting_attachments WHERE id=?", [$id]);
        if (!$att) { self::json(false, 'Tidak ditemukan'); return; }

        $path = ROOT_PATH . '/public/uploads/attachments/' . $att['meeting_id'] . '/' . $att['stored_name'];
        if (file_exists($path)) unlink($path);

        Database::getInstance()->prepare("DELETE FROM meeting_attachments WHERE id=?")->execute([$id]);
        self::json(true, 'File dihapus');
    }

    // ── Helpers ──────────────────────────────────────────────
    private static function formatBytes(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    private static function mimeIcon(?string $mime): string
    {
        return match(true) {
            str_contains($mime ?? '', 'pdf')         => '📄',
            str_contains($mime ?? '', 'word')        => '📝',
            str_contains($mime ?? '', 'sheet')       => '📊',
            str_contains($mime ?? '', 'excel')       => '📊',
            str_contains($mime ?? '', 'presentation')=> '📋',
            str_contains($mime ?? '', 'image')       => '🖼️',
            default                                   => '📎',
        };
    }

    private static function json(bool $success, string $message, array $extra = []): void
    {
        header('Content-Type: application/json');
        echo json_encode(array_merge(compact('success', 'message'), $extra)); exit;
    }
}
