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
    private const MAX_SIZE = 52428800;

    public static function index(): void
    {
        Auth::requireLogin();
        $user    = Auth::user();
        $userId  = (int)$user['id'];
        $isAdmin = Auth::hasRole('admin');

        $folderId   = isset($_GET['folder']) ? (int)$_GET['folder'] : null;
        $section    = $_GET['section'] ?? 'my-files';
        $filterType = $_GET['type']    ?? '';
        $search     = trim($_GET['q']  ?? '');

        if ($folderId && !DokumenFolderShareModel::canAccess($folderId, $userId, $isAdmin)) {
            Flash::set('danger', 'Anda tidak memiliki akses ke folder tersebut.');
            redirect('/dokumen');
        }

        $breadcrumb = [];
        if ($folderId) {
            $breadcrumb = self::buildBreadcrumb($folderId);
        }

        $folders = ($section === 'my-files')
            ? DokumenModel::getFolders($folderId, $userId, $isAdmin)
            : [];

        $files = match($section) {
            'shared' => DokumenModel::getSharedWithMe($userId),
            'recent' => DokumenModel::getRecent($userId, $isAdmin),
            default  => DokumenModel::getFiles($folderId, $userId, $isAdmin, $filterType, $search),
        };

        foreach ($files as &$f) {
            $f['size_fmt']   = DokumenModel::formatSize((int)$f['file_size']);
            $f['mime_label'] = DokumenModel::mimeLabel($f['mime_type']);
            $f['mime_color'] = DokumenModel::mimeColor($f['mime_type']);
            $f['can_delete'] = $isAdmin || $f['uploaded_by'] == $userId;
            $f['previewable'] = self::isPreviewable($f['mime_type']);
        }
        unset($f);

        foreach ($folders as &$folder) {
            $folder['can_manage_share'] = $isAdmin || (int)$folder['created_by'] === $userId;
            $folder['can_delete'] = $isAdmin || (int)$folder['created_by'] === $userId;
        }
        unset($folder);

        $stats = DokumenModel::getStats($userId, $isAdmin);
        $stats['total_size_fmt'] = DokumenModel::formatSize($stats['total_size']);

        View::layout('dokumen/index', [
            'pageTitle'   => 'Dokumen',
            'folders'     => $folders,
            'files'       => $files,
            'stats'       => $stats,
            'breadcrumb'  => $breadcrumb,
            'section'     => $section,
            'folderId'    => $folderId,
            'filterType'  => $filterType,
            'search'      => $search,
        ]);
    }

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
            $codes = [1=>'Ukuran melebihi batas server',2=>'Ukuran melebihi MAX_FILE_SIZE',3=>'Upload tidak lengkap',4=>'Tidak ada file dipilih',6=>'Folder tmp tidak ada',7=>'Gagal tulis ke disk'];
            echo json_encode(['success'=>false,'message'=>$codes[$file['error'] ?? 0] ?? 'Upload gagal']);
            exit;
        }

        $folderId = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? (int)$_POST['folder_id'] : null;
        if ($folderId && !DokumenFolderShareModel::canAccess($folderId, Auth::id(), Auth::hasRole('admin'))) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses ke folder tujuan.']);
            exit;
        }

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, self::$ALLOWED_MIMES, true)) {
            echo json_encode(['success'=>false,'message'=>'Tipe file tidak diizinkan: ' . $mime]);
            exit;
        }
        if ($file['size'] > self::MAX_SIZE) {
            echo json_encode(['success'=>false,'message'=>'Ukuran file maksimal 50 MB.']);
            exit;
        }

        $ext    = self::extensionForMime($mime);
        $stored = 'dok_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dir    = ROOT_PATH . '/assets/uploads/dokumen/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($file['tmp_name'], $dir . $stored)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file ke server.']);
            exit;
        }

        $fileId = DokumenModel::insertFile([
            'folder_id'     => $folderId,
            'original_name' => self::safeFileName($file['name']),
            'stored_name'   => $stored,
            'file_path'     => '/assets/uploads/dokumen/' . $stored,
            'mime_type'     => $mime,
            'file_size'     => $file['size'],
            'uploaded_by'   => Auth::id(),
        ]);

        ActivityLog::record('dokumen.upload', 'Upload dokumen: ' . $file['name'], 'dokumen', $fileId);

        $row = DokumenModel::getFileById($fileId);
        $row['size_fmt']    = DokumenModel::formatSize((int)$row['file_size']);
        $row['mime_label']  = DokumenModel::mimeLabel($row['mime_type']);
        $row['mime_color']  = DokumenModel::mimeColor($row['mime_type']);
        $row['can_delete']  = true;
        $row['previewable'] = self::isPreviewable($row['mime_type']);

        echo json_encode(['success'=>true,'message'=>'File berhasil diupload.','file'=>$row]);
        exit;
    }

    public static function preview(int $id): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');

        $file = DokumenModel::getFileById($id);
        if (!$file) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }

        if (!DokumenShareModel::canAccess($id, $userId, $isAdmin)) {
            $folderId = isset($file['folder_id']) ? (int)$file['folder_id'] : 0;
            if (!$folderId || !DokumenFolderShareModel::canAccess($folderId, $userId, $isAdmin)) {
                http_response_code(403); echo 'Akses ditolak.'; exit;
            }
        }

        if (!self::isPreviewable($file['mime_type'])) {
            http_response_code(415); echo 'Tipe file tidak dapat dipratinjau.'; exit;
        }

        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File fisik tidak ditemukan.'; exit; }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: inline; filename*=UTF-8\'\'' . rawurlencode(self::safeFileName($file['original_name'])));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private, max-age=300');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public static function previewPublic(int $id): void
    {
        $token = trim($_GET['token'] ?? '');
        if (!$token) { http_response_code(403); echo 'Token diperlukan.'; exit; }

        $link = DokumenPublicLinkModel::getByToken($token);
        if (!$link || !DokumenPublicLinkModel::isValid($link)) {
            http_response_code(403); echo 'Link tidak valid atau kadaluarsa.'; exit;
        }
        if ((int)$link['file_id'] !== $id) {
            http_response_code(403); echo 'Token tidak sesuai file.'; exit;
        }

        if (!empty($link['password_hash'])) {
            $session_key = 'pub_link_ok_' . $token;
            if (empty($_SESSION[$session_key])) {
                http_response_code(403); echo 'Autentikasi password diperlukan.'; exit;
            }
        }

        $file = DokumenModel::getFileById($id);
        if (!$file) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }

        if (!self::isPreviewable($file['mime_type'])) {
            http_response_code(415); echo 'Tipe tidak dapat dipratinjau.'; exit;
        }

        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File fisik tidak ada.'; exit; }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: inline; filename*=UTF-8\'\'' . rawurlencode(self::safeFileName($file['original_name'])));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: public, max-age=300');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public static function info(int $id): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');

        $file = DokumenModel::getFileById($id);
        if (!$file) {
            header('Content-Type: application/json');
            echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit;
        }
        if (!DokumenShareModel::canAccess($id, $userId, $isAdmin)) {
            $folderId = isset($file['folder_id']) ? (int)$file['folder_id'] : 0;
            if (!$folderId || !DokumenFolderShareModel::canAccess($folderId, $userId, $isAdmin)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success'=>false,'message'=>'Akses ditolak.']); exit;
            }
        }

        $shares = DokumenShareModel::getSharesByFile($id);
        $file['size_fmt']    = DokumenModel::formatSize((int)$file['file_size']);
        $file['mime_label']  = DokumenModel::mimeLabel($file['mime_type']);
        $file['mime_color']  = DokumenModel::mimeColor($file['mime_type']);
        $file['previewable'] = self::isPreviewable($file['mime_type']);
        $file['can_delete']  = $isAdmin || (int)$file['uploaded_by'] === $userId;
        $file['can_share']   = $isAdmin || (int)$file['uploaded_by'] === $userId;
        $file['can_download']= DokumenShareModel::canDownload($id, $userId, $isAdmin)
            || ((int)($file['folder_id'] ?? 0) > 0 && DokumenFolderShareModel::canAccess((int)$file['folder_id'], $userId, $isAdmin));

        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'file'=>$file,'shares'=>$shares]);
        exit;
    }

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

        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        if ($parentId && !DokumenFolderShareModel::canAccess($parentId, Auth::id(), Auth::hasRole('admin'))) {
            echo json_encode(['success'=>false,'message'=>'Tidak punya akses ke folder induk.']); exit;
        }

        $id = DokumenModel::createFolder($name, $parentId, Auth::id());
        ActivityLog::record('dokumen.folder.create', 'Buat folder: ' . $name, 'dokumen_folder', $id);

        echo json_encode(['success'=>true,'message'=>'Folder berhasil dibuat.','id'=>$id,'name'=>$name]);
        exit;
    }

    public static function renameFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $folder = DokumenModel::getFolderById($id);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }
        if (!Auth::hasRole('admin') && (int)$folder['created_by'] !== Auth::id()) {
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

    public static function deleteFolder(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');
        $folder = DokumenModel::getFolderById($id);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }
        if (!DokumenFolderShareModel::canDelete($id, Auth::id(), Auth::hasRole('admin'))) {
            echo json_encode(['success'=>false,'message'=>'Hanya admin atau pembuat folder yang dapat menghapus folder.']); exit;
        }

        $files = DokumenModel::getFiles($id, Auth::id(), true);
        foreach ($files as $f) {
            $path = ROOT_PATH . $f['file_path'];
            if (file_exists($path)) @unlink($path);
            DokumenShareModel::removeAllByFile((int)$f['id']);
            DokumenModel::deleteFile((int)$f['id']);
        }
        DokumenFolderShareModel::removeAllByFolder($id);
        DokumenModel::deleteFolder($id);
        ActivityLog::record('dokumen.folder.delete', 'Hapus folder: ' . $folder['name'], 'dokumen_folder', $id);
        echo json_encode(['success'=>true,'message'=>'Folder berhasil dihapus.']);
        exit;
    }

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
        DokumenModel::renameFile($id, self::safeFileName($name));
        echo json_encode(['success'=>true,'message'=>'File berhasil diubah.','name'=>self::safeFileName($name)]);
        exit;
    }

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
        DokumenShareModel::removeAllByFile($id);
        DokumenModel::deleteFile($id);
        ActivityLog::record('dokumen.delete', 'Hapus dokumen: ' . $file['original_name'], 'dokumen', $id);
        echo json_encode(['success'=>true,'message'=>'File berhasil dihapus.']);
        exit;
    }

    public static function download(int $id): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');

        $file = DokumenModel::getFileById($id);
        if (!$file) { http_response_code(404); echo '404 Not Found'; exit; }

        $canDownload = DokumenShareModel::canDownload($id, $userId, $isAdmin);
        if (!$canDownload && (int)($file['folder_id'] ?? 0) > 0) {
            $canDownload = DokumenFolderShareModel::canAccess((int)$file['folder_id'], $userId, $isAdmin);
        }
        if (!$canDownload) {
            http_response_code(403); echo '403 Forbidden'; exit;
        }

        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File tidak ditemukan di server.'; exit; }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(self::safeFileName($file['original_name'])));
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: no-cache');
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public static function isPreviewable(string $mime): bool
    {
        return in_array(true, [
            str_starts_with($mime, 'image/'),
            str_starts_with($mime, 'video/'),
            str_starts_with($mime, 'audio/'),
            $mime === 'application/pdf',
            $mime === 'text/plain',
            $mime === 'text/csv',
        ], true);
    }

    private static function buildBreadcrumb(int $folderId): array
    {
        $crumbs  = [];
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

    private static function safeFileName(string $name): string
    {
        $name = trim(str_replace(["\r", "\n", "\0"], '', $name));
        return $name === '' ? 'file' : $name;
    }

    private static function extensionForMime(string $mime): string
    {
        return match ($mime) {
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/vnd.ms-powerpoint' => 'ppt',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'video/mp4' => 'mp4',
            'video/quicktime' => 'mov',
            'video/x-msvideo' => 'avi',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'audio/ogg' => 'ogg',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/zip', 'application/x-zip-compressed' => 'zip',
            'application/x-rar-compressed' => 'rar',
            default => 'bin',
        };
    }
}
