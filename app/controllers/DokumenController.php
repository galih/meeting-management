<?php
declare(strict_types=1);

class DokumenController
{
    private static array $ALLOWED_MIMES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg','image/png','image/gif','image/webp',
        'video/mp4','video/quicktime','video/x-msvideo',
        'audio/mpeg','audio/wav','audio/ogg',
        'text/plain','text/csv',
        'application/zip','application/x-zip-compressed',
        'application/x-rar-compressed',
    ];
    private const MAX_SIZE = 52428800; // 50 MB

    /* ------------------------------------------------------------------ */
    /*  HALAMAN UTAMA                                                       */
    /* ------------------------------------------------------------------ */

    public static function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::user();
        $userId  = (int)$user['id'];
        $isAdmin = Auth::hasRole('admin');

        $folderId   = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
        $section    = $_GET['section'] ?? 'my-files'; // my-files | shared | recent
        $filterType = $_GET['type']    ?? '';
        $search     = trim($_GET['q']  ?? '');

        // breadcrumb folder
        $breadcrumb = [];
        if ($folderId) {
            $breadcrumb = self::buildBreadcrumb($folderId);
        }

        // data
        $folders = ($section === 'my-files')
            ? DokumenModel::getFolders($folderId)
            : [];

        $files = match($section) {
            'shared' => DokumenModel::getSharedWithMe($userId),
            'recent' => DokumenModel::getRecent($userId, $isAdmin),
            default  => DokumenModel::getFiles($folderId, $userId, $isAdmin, $filterType, $search),
        };

        // tambah meta ke setiap file
        foreach ($files as &$f) {
            $f['size_fmt']   = DokumenModel::formatSize((int)$f['file_size']);
            $f['mime_label'] = DokumenModel::mimeLabel($f['mime_type']);
            $f['mime_color'] = DokumenModel::mimeColor($f['mime_type']);
            $f['can_delete'] = $isAdmin || $f['uploaded_by'] == $userId;
        }
        unset($f);

        $stats = DokumenModel::getStats($userId, $isAdmin);
        $stats['total_size_fmt'] = DokumenModel::formatSize($stats['total_size']);

        $pageTitle = 'Dokumen';
        $view      = 'dokumen/index';
        require_once APP_PATH . '/views/layouts/main.php';
    }

    /* ------------------------------------------------------------------ */
    /*  API — UPLOAD FILE                                                   */
    /* ------------------------------------------------------------------ */

    public static function upload(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        if (!Auth::hasRole('admin', 'sekretaris')) {
            echo json_encode(['success'=>false,'message'=>'Hanya admin atau sekretaris yang dapat mengupload dokumen.']);
            exit;
        }

        $file = $_FILES['file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $codes = [1=>'Ukuran melebihi batas server',2=>'Ukuran melebihi MAX_FILE_SIZE',
                      3=>'Upload tidak lengkap',4=>'Tidak ada file dipilih',
                      6=>'Folder tmp tidak ada',7=>'Gagal tulis ke disk'];
            echo json_encode(['success'=>false,'message'=>$codes[$file['error'] ?? 0] ?? 'Upload gagal']);
            exit;
        }

        $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== ''
            ? (int)$_POST['folder_id'] : null;

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::$ALLOWED_MIMES, true)) {
            echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan: ' . $mime]);
            exit;
        }
        if ($file['size'] > self::MAX_SIZE) {
            echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 50 MB.']);
            exit;
        }

        $ext        = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin');
        $stored     = 'dok_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dir        = ROOT_PATH . '/assets/uploads/dokumen/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $stored)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file ke server.']);
            exit;
        }

        $fileId = DokumenModel::insertFile([
            'folder_id'     => $folderId,
            'original_name' => $file['name'],
            'stored_name'   => $stored,
            'file_path'     => '/assets/uploads/dokumen/' . $stored,
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'uploaded_by'   => Auth::id(),
        ]);

        ActivityLog::record('dokumen.upload', 'Upload dokumen: ' . $file['name'], 'dokumen', $fileId);

        $row = DokumenModel::getFileById($fileId);
        $row['size_fmt']   = DokumenModel::formatSize((int)$row['file_size']);
        $row['mime_label'] = DokumenModel::mimeLabel($row['mime_type']);
        $row['mime_color'] = DokumenModel::mimeColor($row['mime_type']);
        $row['can_delete'] = true;

        echo json_encode(['success'=>true,'message'=>'File berhasil diupload.','file'=>$row]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — BUAT FOLDER                                                   */
    /* ------------------------------------------------------------------ */

    public static function createFolder(): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        if (!Auth::hasRole('admin', 'sekretaris')) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success'=>false,'message'=>'Nama folder tidak boleh kosong.']); exit;
        }

        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== ''
            ? (int)$_POST['parent_id'] : null;

        $id = DokumenModel::createFolder($name, $parentId, Auth::id());
        ActivityLog::record('dokumen.folder.create', 'Buat folder: ' . $name, 'dokumen_folder', $id);

        echo json_encode(['success'=>true,'message'=>'Folder berhasil dibuat.','id'=>$id,'name'=>$name]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — RENAME FOLDER                                                 */
    /* ------------------------------------------------------------------ */

    public static function renameFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        if (!Auth::hasRole('admin', 'sekretaris')) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            echo json_encode(['success'=>false,'message'=>'Nama tidak boleh kosong.']); exit;
        }
        DokumenModel::renameFolder($id, $name);
        echo json_encode(['success'=>true,'message'=>'Folder berhasil diubah.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — HAPUS FOLDER                                                  */
    /* ------------------------------------------------------------------ */

    public static function deleteFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        if (!Auth::hasRole('admin')) {
            echo json_encode(['success'=>false,'message'=>'Hanya admin yang dapat menghapus folder.']); exit;
        }
        $folder = DokumenModel::getFolderById($id);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }
        // hapus semua file di dalam folder dari disk & DB
        $files = DokumenModel::getFiles($id, Auth::id(), true);
        foreach ($files as $f) {
            $path = ROOT_PATH . $f['file_path'];
            if (file_exists($path)) @unlink($path);
            DokumenModel::deleteFile((int)$f['id']);
        }
        DokumenModel::deleteFolder($id);
        ActivityLog::record('dokumen.folder.delete', 'Hapus folder: ' . $folder['name'], 'dokumen_folder', $id);
        echo json_encode(['success'=>true,'message'=>'Folder berhasil dihapus.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — RENAME FILE                                                   */
    /* ------------------------------------------------------------------ */

    public static function renameFile(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $file = DokumenModel::getFileById($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }
        $name = trim($_POST['name'] ?? '');
        if ($name === '') { echo json_encode(['success'=>false,'message'=>'Nama tidak boleh kosong.']); exit; }
        DokumenModel::renameFile($id, $name);
        echo json_encode(['success'=>true,'message'=>'File berhasil diubah.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — HAPUS FILE                                                    */
    /* ------------------------------------------------------------------ */

    public static function deleteFile(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $file = DokumenModel::getFileById($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }
        $path = ROOT_PATH . $file['file_path'];
        if (file_exists($path)) @unlink($path);
        DokumenModel::deleteFile($id);
        ActivityLog::record('dokumen.delete', 'Hapus dokumen: ' . $file['original_name'], 'dokumen', $id);
        echo json_encode(['success'=>true,'message'=>'File berhasil dihapus.']);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  API — DOWNLOAD FILE                                                 */
    /* ------------------------------------------------------------------ */

    public static function download(int $id): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');

        $file = DokumenModel::getFileById($id);
        if (!$file) { http_response_code(404); echo '404 Not Found'; exit; }

        // cek hak akses
        if (!$isAdmin && $file['uploaded_by'] != $userId) {
            $share = Database::queryOne(
                "SELECT id FROM dokumen_shares WHERE file_id=? AND shared_to=?",
                [$id, $userId]
            );
            if (!$share) { http_response_code(403); echo '403 Forbidden'; exit; }
        }

        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File tidak ditemukan di server.'; exit; }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . addslashes($file['original_name']) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        readfile($path);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  HELPER PRIVATE                                                      */
    /* ------------------------------------------------------------------ */

    private static function buildBreadcrumb(int $folderId): array
    {
        $crumbs = [];
        $current = $folderId;
        $safety  = 0;
        while ($current && $safety++ < 10) {
            $folder = DokumenModel::getFolderById($current);
            if (!$folder) break;
            array_unshift($crumbs, $folder);
            $current = $folder['parent_id'] ? (int)$folder['parent_id'] : 0;
        }
        return $crumbs;
    }
}
