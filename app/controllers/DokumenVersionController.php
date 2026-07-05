<?php
declare(strict_types=1);

class DokumenVersionController
{
    public static function index(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }
        if (!DokumenShareModel::canAccess($id, $userId, $isAdmin)) {
            http_response_code(403);
            echo json_encode(['success'=>false,'message'=>'Akses ditolak.']); exit;
        }

        $versions = DokumenVersionModel::getVersions($id);
        foreach ($versions as &$v) {
            $v['size_fmt']  = DokumenModel::formatSize((int)$v['file_size']);
            $v['mime_label']= DokumenModel::mimeLabel($v['mime_type']);
            $v['mime_color']= DokumenModel::mimeColor($v['mime_type']);
        }
        unset($v);
        echo json_encode(['success'=>true,'versions'=>$versions]);
        exit;
    }

    public static function uploadNewVersion(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            echo json_encode(['success'=>false,'message'=>'Hanya pemilik atau admin yang dapat upload revisi.']); exit;
        }

        $up = $_FILES['file'] ?? null;
        if (!$up || $up['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success'=>false,'message'=>'Upload file revisi gagal.']); exit;
        }

        DokumenVersionModel::snapshotCurrentFile($file, $userId);

        $mime   = mime_content_type($up['tmp_name']);
        $ext    = self::extensionForMime($mime);
        $stored = 'dok_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
        $dir    = ROOT_PATH . '/assets/uploads/dokumen/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        if (!move_uploaded_file($up['tmp_name'], $dir . $stored)) {
            echo json_encode(['success'=>false,'message'=>'Gagal menyimpan file revisi ke server.']); exit;
        }

        $db = Database::getInstance();
        $st = $db->prepare("UPDATE dokumen_files
            SET original_name=?, stored_name=?, file_path=?, mime_type=?, file_size=?, updated_at=NOW()
            WHERE id=?");
        $st->execute([
            self::safeFileName($up['name']),
            $stored,
            '/assets/uploads/dokumen/' . $stored,
            $mime,
            (int)$up['size'],
            $id,
        ]);

        ActivityLog::record('dokumen.version.upload', 'Upload revisi: '.$up['name'], 'dokumen', $id);

        $versions = DokumenVersionModel::getVersions($id);
        foreach ($versions as &$v) {
            $v['size_fmt']   = DokumenModel::formatSize((int)$v['file_size']);
            $v['mime_label'] = DokumenModel::mimeLabel($v['mime_type']);
            $v['mime_color'] = DokumenModel::mimeColor($v['mime_type']);
        }
        unset($v);

        $updated = DokumenModel::getFileById($id);
        $updated['size_fmt']   = DokumenModel::formatSize((int)$updated['file_size']);
        $updated['mime_label'] = DokumenModel::mimeLabel($updated['mime_type']);
        $updated['mime_color'] = DokumenModel::mimeColor($updated['mime_type']);

        echo json_encode(['success'=>true,'message'=>'Revisi berhasil diupload.','versions'=>$versions,'file'=>$updated]);
        exit;
    }

    public static function downloadVersion(int $versionId): void
    {
        Auth::requireLogin();
        $version = DokumenVersionModel::getById($versionId);
        if (!$version) { http_response_code(404); echo 'Versi tidak ditemukan.'; exit; }

        $fileId  = (int)$version['file_id'];
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        if (!DokumenShareModel::canDownload($fileId, $userId, $isAdmin)) {
            http_response_code(403); echo '403 Forbidden'; exit;
        }

        $path = ROOT_PATH . $version['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File versi tidak ada.'; exit; }

        header('Content-Type: ' . $version['mime_type']);
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(self::safeFileName($version['original_name'])));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    public static function restore(int $id): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($id);
        if (!$file) { echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit; }
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $versionId = (int)($_POST['version_id'] ?? 0);
        $version   = DokumenVersionModel::getById($versionId);
        if (!$version || (int)$version['file_id'] !== $id) {
            echo json_encode(['success'=>false,'message'=>'Versi tidak ditemukan.']); exit;
        }

        DokumenVersionModel::snapshotCurrentFile($file, $userId);

        $db = Database::getInstance();
        $st = $db->prepare("UPDATE dokumen_files
            SET original_name=?, stored_name=?, file_path=?, mime_type=?, file_size=?, updated_at=NOW()
            WHERE id=?");
        $st->execute([
            self::safeFileName($version['original_name']),
            $version['stored_name'],
            $version['file_path'],
            $version['mime_type'],
            (int)$version['file_size'],
            $id,
        ]);

        ActivityLog::record('dokumen.version.restore', 'Restore versi '.$version['version_no'].': '.$version['original_name'], 'dokumen', $id);

        $versions = DokumenVersionModel::getVersions($id);
        foreach ($versions as &$v) {
            $v['size_fmt']   = DokumenModel::formatSize((int)$v['file_size']);
            $v['mime_label'] = DokumenModel::mimeLabel($v['mime_type']);
            $v['mime_color'] = DokumenModel::mimeColor($v['mime_type']);
        }
        unset($v);

        $updated = DokumenModel::getFileById($id);
        $updated['size_fmt']   = DokumenModel::formatSize((int)$updated['file_size']);
        $updated['mime_label'] = DokumenModel::mimeLabel($updated['mime_type']);
        $updated['mime_color'] = DokumenModel::mimeColor($updated['mime_type']);

        echo json_encode(['success'=>true,'message'=>'File berhasil di-restore ke versi tersebut.','versions'=>$versions,'file'=>$updated]);
        exit;
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
