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

        // Simpan file saat ini ke versi
        DokumenVersionModel::snapshotCurrentFile($file, $userId);

        // Simpan file baru
        $mime   = mime_content_type($up['tmp_name']);
        $ext    = strtolower(pathinfo($up['name'], PATHINFO_EXTENSION) ?: 'bin');
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
            $up['name'],
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
        header('Content-Disposition: attachment; filename="'.addslashes($version['original_name']).'"');
        header('Content-Length: ' . filesize($path));
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

        // Snapshot file aktif dulu
        DokumenVersionModel::snapshotCurrentFile($file, $userId);

        // Set file aktif = data dari versi yang dipilih
        $db = Database::getInstance();
        $st = $db->prepare("UPDATE dokumen_files
            SET original_name=?, stored_name=?, file_path=?, mime_type=?, file_size=?, updated_at=NOW()
            WHERE id=?");
        $st->execute([
            $version['original_name'],
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
}
