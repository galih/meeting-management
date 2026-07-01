<?php
declare(strict_types=1);

class DokumenController
{
    /* ── tipe file yang diizinkan ─────────────────────────────────── */
    private const ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg','image/png','image/gif','image/webp',
        'video/mp4','video/quicktime','video/x-msvideo',
        'text/plain','text/csv',
        'application/zip','application/x-zip-compressed','application/x-rar-compressed',
        'application/json',
        'application/vnd.figma',   // Figma export
    ];
    private const MAX_SIZE = 50 * 1024 * 1024; // 50 MB

    /* ================================================================
       HALAMAN UTAMA
    ================================================================ */
    public static function index(): void
    {
        Auth::requireLogin();
        $user     = Auth::user();
        $isAdmin  = Auth::hasRole('admin');
        $userId   = (int)$user['id'];

        $section   = $_GET['section']   ?? 'my-files';
        $folderId  = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
        $search    = trim($_GET['q']    ?? '');
        $typeFilter= trim($_GET['type'] ?? '');

        // Breadcrumb untuk folder aktif
        $breadcrumb = [];
        if ($folderId) {
            $breadcrumb = self::buildBreadcrumb($folderId);
        }

        // Data sesuai section
        $folders = [];
        $files   = [];

        switch ($section) {
            case 'shared':
                $files = DokumenModel::getSharedWithMe($userId);
                break;
            case 'recent':
                $files = DokumenModel::getRecent($userId, $isAdmin);
                break;
            case 'trash':
                $files = DokumenModel::getTrash($userId, $isAdmin);
                break;
            default: // my-files
                $folders = DokumenModel::getFolders($folderId, $userId, $isAdmin);
                $files   = DokumenModel::getFiles($folderId, $userId, $isAdmin, $search, $typeFilter);
        }

        // Hitung ukuran & format
        foreach ($files as &$f) {
            $f['size_fmt'] = self::formatSize((int)$f['file_size']);
            $f['type_label'] = self::typeLabel($f['mime_type']);
            $f['icon_svg']   = self::iconSvg($f['mime_type']);
            $f['date_fmt']   = date('M j, Y', strtotime($f['updated_at']));
        }
        unset($f);

        $summary = DokumenModel::storageSummary($userId, $isAdmin);
        $summary['total_bytes_fmt'] = self::formatSize((int)$summary['total_bytes']);

        $currentFolder = $folderId ? DokumenModel::getFolder($folderId) : null;

        $viewData = compact(
            'section', 'folderId', 'folders', 'files',
            'breadcrumb', 'search', 'typeFilter', 'summary',
            'currentFolder', 'isAdmin', 'user'
        );

        View::render('dokumen/index', $viewData);
    }

    /* ================================================================
       API — UPLOAD FILE
    ================================================================ */
    public static function upload(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        if (!Auth::hasRole('admin', 'sekretaris')) {
            echo json_encode(['success'=>false,'message'=>'Hanya Admin / Sekretaris yang dapat mengupload dokumen.']);
            exit;
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success'=>false,'message'=>self::uploadErrMsg($file['error'] ?? 0)]);
            exit;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::ALLOWED_MIMES, true)) {
            echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan.']);
            exit;
        }
        if ($file['size'] > self::MAX_SIZE) {
            echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 50 MB.']);
            exit;
        }

        $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int)$_POST['folder_id'] : null;
        $userId   = (int)Auth::id();

        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin');
        $stored     = 'dok_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dir        = ROOT_PATH . '/assets/uploads/dokumen/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $stored)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file ke server.']);
            exit;
        }

        $fileId = DokumenModel::createFile([
            'folder_id'     => $folderId,
            'original_name' => $file['name'],
            'stored_name'   => $stored,
            'file_path'     => '/assets/uploads/dokumen/' . $stored,
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'uploaded_by'   => $userId,
        ]);

        ActivityLog::record('dokumen.upload', 'Upload dokumen: ' . $file['name']);

        echo json_encode([
            'success'       => true,
            'message'       => 'File berhasil diupload.',
            'file_id'       => $fileId,
            'original_name' => $file['name'],
            'size_fmt'      => self::formatSize($file['size']),
            'type_label'    => self::typeLabel($mime),
        ]);
        exit;
    }

    /* ================================================================
       API — BUAT FOLDER
    ================================================================ */
    public static function createFolder(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        if (!Auth::hasRole('admin', 'sekretaris')) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']);
            exit;
        }

        $name     = trim($_POST['name'] ?? '');
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;

        if ($name === '') {
            echo json_encode(['success'=>false,'message'=>'Nama folder tidak boleh kosong.']);
            exit;
        }

        $id = DokumenModel::createFolder($name, $parentId, (int)Auth::id());
        ActivityLog::record('dokumen.folder.create', 'Buat folder: ' . $name);

        echo json_encode(['success'=>true,'message'=>'Folder berhasil dibuat.','folder_id'=>$id,'name'=>$name]);
        exit;
    }

    /* ================================================================
       API — RENAME FOLDER
    ================================================================ */
    public static function renameFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolder($id);
        if (!$folder) { echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit; }
        if (!Auth::hasRole('admin') && $folder['created_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']); exit;
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') { echo json_encode(['success'=>false,'message'=>'Nama tidak boleh kosong.']); exit; }

        DokumenModel::renameFolder($id, $name);
        echo json_encode(['success'=>true,'message'=>'Folder berhasil direname.']);
        exit;
    }

    /* ================================================================
       API — HAPUS FOLDER
    ================================================================ */
    public static function deleteFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolder($id);
        if (!$folder) { echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit; }
        if (!Auth::hasRole('admin') && $folder['created_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']); exit;
        }

        $ok = DokumenModel::deleteFolder($id);
        if (!$ok) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak kosong, pindahkan atau hapus isinya terlebih dahulu.']);
            exit;
        }
        ActivityLog::record('dokumen.folder.delete', 'Hapus folder id:' . $id);
        echo json_encode(['success'=>true,'message'=>'Folder berhasil dihapus.']);
        exit;
    }

    /* ================================================================
       API — DOWNLOAD FILE
    ================================================================ */
    public static function download(int $id): void
    {
        Auth::requireLogin();

        $file   = DokumenModel::getFile($id);
        if (!$file) { http_response_code(404); exit('File tidak ditemukan.'); }

        $userId  = (int)Auth::id();
        $isAdmin = Auth::hasRole('admin');

        // Cek akses
        if (!$isAdmin && $file['uploaded_by'] != $userId) {
            $share = Database::queryOne(
                "SELECT id FROM dokumen_shares WHERE file_id=? AND shared_to=?",
                [$id, $userId]
            );
            if (!$share) { http_response_code(403); exit('Akses ditolak.'); }
        }

        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); exit('File tidak ada di server.'); }

        // Update updated_at (activity)
        Database::getInstance()->prepare("UPDATE dokumen_files SET updated_at=NOW() WHERE id=?")->execute([$id]);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . ($file['mime_type'] ?: 'application/octet-stream'));
        header('Content-Disposition: attachment; filename="' . addslashes($file['original_name']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }

    /* ================================================================
       API — SOFT DELETE (pindah ke Trash)
    ================================================================ */
    public static function deleteFile(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = DokumenModel::getFile($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }

        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']); exit;
        }

        DokumenModel::softDelete($id);
        ActivityLog::record('dokumen.delete', 'Hapus dokumen: ' . $file['original_name']);
        echo json_encode(['success'=>true,'message'=>'File dipindahkan ke Trash.']);
        exit;
    }

    /* ================================================================
       API — RESTORE DARI TRASH
    ================================================================ */
    public static function restoreFile(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = Database::queryOne("SELECT * FROM dokumen_files WHERE id=?", [$id]);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }

        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']); exit;
        }

        DokumenModel::restore($id);
        echo json_encode(['success'=>true,'message'=>'File berhasil dipulihkan.']);
        exit;
    }

    /* ================================================================
       API — HARD DELETE DARI TRASH
    ================================================================ */
    public static function forceDelete(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = Database::queryOne("SELECT * FROM dokumen_files WHERE id=?", [$id]);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }

        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses.']); exit;
        }

        // Hapus fisik
        $path = ROOT_PATH . $file['file_path'];
        if (file_exists($path)) @unlink($path);

        DokumenModel::hardDelete($id);
        ActivityLog::record('dokumen.force_delete', 'Hapus permanen: ' . $file['original_name']);
        echo json_encode(['success'=>true,'message'=>'File dihapus permanen.']);
        exit;
    }

    /* ================================================================
       HELPERS
    ================================================================ */
    private static function buildBreadcrumb(int $folderId): array
    {
        $crumbs = [];
        $current = $folderId;
        for ($i = 0; $i < 10; $i++) {
            $folder = DokumenModel::getFolder($current);
            if (!$folder) break;
            array_unshift($crumbs, ['id' => $folder['id'], 'name' => $folder['name']]);
            if (!$folder['parent_id']) break;
            $current = (int)$folder['parent_id'];
        }
        return $crumbs;
    }

    public static function formatSize(int $bytes): string
    {
        if ($bytes < 1024)       return $bytes . ' B';
        if ($bytes < 1048576)    return round($bytes / 1024, 1) . ' KB';
        if ($bytes < 1073741824) return round($bytes / 1048576, 2) . ' MB';
        return round($bytes / 1073741824, 2) . ' GB';
    }

    public static function typeLabel(string $mime): string
    {
        if (strpos($mime, 'pdf') !== false)          return 'PDF';
        if (strpos($mime, 'wordprocessingml') !== false ||strpos($mime, 'msword') !== false) return 'Word';
        if (strpos($mime, 'spreadsheetml') !== false || strpos($mime, 'ms-excel') !== false) return 'Excel';
        if (strpos($mime, 'presentationml') !== false|| strpos($mime, 'powerpoint') !== false) return 'PPT';
        if (strpos($mime, 'video') !== false)        return 'Video';
        if (strpos($mime, 'image') !== false)        return 'Gambar';
        if (strpos($mime, 'zip') !== false || strpos($mime, 'rar') !== false) return 'Arsip';
        if (strpos($mime, 'json') !== false)         return 'JSON';
        if (strpos($mime, 'text') !== false)         return 'Teks';
        return 'File';
    }

    public static function iconSvg(string $mime): string
    {
        if (strpos($mime, 'pdf') !== false)
            return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#E53935" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>';
        if (strpos($mime, 'video') !== false)
            return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#8E24AA" stroke-width="2"><polygon points="23 7 16 12 23 17 23 7"/><rect x="1" y="5" width="15" height="14" rx="2" ry="2"/></svg>';
        if (strpos($mime, 'image') !== false)
            return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#1E88E5" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
        if (strpos($mime, 'spreadsheetml') !== false || strpos($mime, 'ms-excel') !== false)
            return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2E7D32" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
        if (strpos($mime, 'zip') !== false || strpos($mime, 'rar') !== false)
            return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F57C00" stroke-width="2"><path d="M21 10H3"/><path d="M21 6H3"/><path d="M21 14H3"/><path d="M21 18H3"/></svg>';
        // default
        return '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#546E7A" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>';
    }

    private static function uploadErrMsg(int $code): string
    {
        $map = [
            1 => 'Ukuran melebihi batas php.ini',
            2 => 'Ukuran melebihi MAX_FILE_SIZE',
            3 => 'Upload tidak lengkap',
            4 => 'Tidak ada file yang dipilih',
            6 => 'Folder tmp tidak ditemukan',
            7 => 'Gagal menulis ke disk',
        ];
        return $map[$code] ?? 'Upload gagal (error ' . $code . ')';
    }
}
