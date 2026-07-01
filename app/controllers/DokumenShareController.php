<?php
declare(strict_types=1);

class DokumenShareController
{
    /* ------------------------------------------------------------------ */
    /*  GET /api/dokumen/{id}/shares                                        */
    /*  List user yang sudah di-share                                       */
    /* ------------------------------------------------------------------ */

    public static function index(int $fileId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = DokumenModel::getFileById($fileId);
        if (!$file) {
            echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit;
        }

        // hanya owner atau admin yg boleh lihat daftar share
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $shares = DokumenShareModel::getSharesByFile($fileId);
        echo json_encode(['success'=>true,'shares'=>$shares]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/dokumen/{id}/shares                                       */
    /*  Tambah / update share                                               */
    /* ------------------------------------------------------------------ */

    public static function store(int $fileId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = DokumenModel::getFileById($fileId);
        if (!$file) {
            echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit;
        }

        // hanya owner atau admin
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $sharedTo   = (int)($_POST['user_id']    ?? 0);
        $permission = $_POST['permission'] ?? 'view';

        if (!$sharedTo) {
            echo json_encode(['success'=>false,'message'=>'User tidak dipilih.']); exit;
        }
        if (!in_array($permission, ['view','download'], true)) {
            echo json_encode(['success'=>false,'message'=>'Permission tidak valid.']); exit;
        }
        // tidak boleh share ke diri sendiri
        if ($sharedTo === Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak perlu share ke diri sendiri.']); exit;
        }

        $targetUser = Database::queryOne(
            "SELECT id, name FROM users WHERE id = ? AND is_active = 1", [$sharedTo]
        );
        if (!$targetUser) {
            echo json_encode(['success'=>false,'message'=>'User tidak ditemukan.']); exit;
        }

        DokumenShareModel::upsert($fileId, $sharedTo, $permission);

        // kirim notifikasi
        $senderName = Auth::user()['name'] ?? 'Seseorang';
        Notification::send(
            $sharedTo,
            'dokumen_share',
            "{$senderName} membagikan file \"" . $file['original_name'] . "\" kepada Anda.",
            BASE_URL . "/dokumen?section=shared"
        );

        ActivityLog::record(
            'dokumen.share',
            "Share file \"{$file['original_name']}\" ke {$targetUser['name']} ({$permission})",
            'dokumen', $fileId
        );

        $shares = DokumenShareModel::getSharesByFile($fileId);
        echo json_encode(['success'=>true,'message'=>"File berhasil dibagikan ke {$targetUser['name']}.", 'shares'=>$shares]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/dokumen/{id}/shares/{userId}/delete                       */
    /*  Cabut akses share                                                   */
    /* ------------------------------------------------------------------ */

    public static function destroy(int $fileId, int $userId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = DokumenModel::getFileById($fileId);
        if (!$file) {
            echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit;
        }
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        DokumenShareModel::remove($fileId, $userId);

        ActivityLog::record(
            'dokumen.unshare',
            "Cabut akses file \"{$file['original_name']}\" dari user ID {$userId}",
            'dokumen', $fileId
        );

        $shares = DokumenShareModel::getSharesByFile($fileId);
        echo json_encode(['success'=>true,'message'=>'Akses berhasil dicabut.','shares'=>$shares]);
        exit;
    }

    /* ------------------------------------------------------------------ */
    /*  POST /api/dokumen/{id}/shares/{userId}/permission                   */
    /*  Update permission (view ↔ download)                                 */
    /* ------------------------------------------------------------------ */

    public static function updatePermission(int $fileId, int $userId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $file = DokumenModel::getFileById($fileId);
        if (!$file) {
            echo json_encode(['success'=>false,'message'=>'File tidak ditemukan.']); exit;
        }
        if (!Auth::hasRole('admin') && $file['uploaded_by'] != Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $permission = $_POST['permission'] ?? '';
        if (!in_array($permission, ['view','download'], true)) {
            echo json_encode(['success'=>false,'message'=>'Permission tidak valid.']); exit;
        }

        DokumenShareModel::upsert($fileId, $userId, $permission);

        $shares = DokumenShareModel::getSharesByFile($fileId);
        echo json_encode(['success'=>true,'message'=>'Permission diperbarui.','shares'=>$shares]);
        exit;
    }
}
