<?php
declare(strict_types=1);

class DokumenFolderShareController
{
    public static function index(int $folderId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }

        if (!Auth::hasRole('admin') && (int)$folder['created_by'] !== Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $shares = DokumenFolderShareModel::getSharesByFolder($folderId);
        echo json_encode(['success'=>true,'shares'=>$shares]);
        exit;
    }

    public static function store(int $folderId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }

        if (!Auth::hasRole('admin') && (int)$folder['created_by'] !== Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $sharedTo   = (int)($_POST['user_id'] ?? 0);
        $permission = $_POST['permission'] ?? 'view';

        if (!$sharedTo) {
            echo json_encode(['success'=>false,'message'=>'User tidak dipilih.']); exit;
        }
        if (!in_array($permission, ['view','download'], true)) {
            echo json_encode(['success'=>false,'message'=>'Permission tidak valid.']); exit;
        }
        if ($sharedTo === Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak perlu share ke diri sendiri.']); exit;
        }

        $targetUser = Database::queryOne(
            "SELECT id, name FROM users WHERE id = ? AND is_active = 1", [$sharedTo]
        );
        if (!$targetUser) {
            echo json_encode(['success'=>false,'message'=>'User tidak ditemukan.']); exit;
        }

        DokumenFolderShareModel::upsert($folderId, $sharedTo, $permission);

        $senderName = Auth::user()['name'] ?? 'Seseorang';
        Notification::send(
            $sharedTo,
            'dokumen_folder_share',
            "{$senderName} membagikan folder \"" . $folder['name'] . "\" kepada Anda.",
            BASE_URL . "/dokumen?folder=" . $folderId
        );

        ActivityLog::record(
            'dokumen.folder.share',
            "Share folder \"{$folder['name']}\" ke {$targetUser['name']} ({$permission})",
            'dokumen_folder', $folderId
        );

        $shares = DokumenFolderShareModel::getSharesByFolder($folderId);
        echo json_encode(['success'=>true,'message'=>"Folder berhasil dibagikan ke {$targetUser['name']}.",'shares'=>$shares]);
        exit;
    }

    public static function destroy(int $folderId, int $userId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }
        if (!Auth::hasRole('admin') && (int)$folder['created_by'] !== Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        DokumenFolderShareModel::remove($folderId, $userId);
        ActivityLog::record(
            'dokumen.folder.unshare',
            "Cabut akses folder \"{$folder['name']}\" dari user ID {$userId}",
            'dokumen_folder', $folderId
        );

        $shares = DokumenFolderShareModel::getSharesByFolder($folderId);
        echo json_encode(['success'=>true,'message'=>'Akses folder berhasil dicabut.','shares'=>$shares]);
        exit;
    }

    public static function updatePermission(int $folderId, int $userId): void
    {
        Auth::requireLogin();
        header('Content-Type: application/json');

        $folder = DokumenModel::getFolderById($folderId);
        if (!$folder) {
            echo json_encode(['success'=>false,'message'=>'Folder tidak ditemukan.']); exit;
        }
        if (!Auth::hasRole('admin') && (int)$folder['created_by'] !== Auth::id()) {
            echo json_encode(['success'=>false,'message'=>'Tidak diizinkan.']); exit;
        }

        $permission = $_POST['permission'] ?? '';
        if (!in_array($permission, ['view','download'], true)) {
            echo json_encode(['success'=>false,'message'=>'Permission tidak valid.']); exit;
        }

        DokumenFolderShareModel::upsert($folderId, $userId, $permission);
        $shares = DokumenFolderShareModel::getSharesByFolder($folderId);
        echo json_encode(['success'=>true,'message'=>'Permission folder diperbarui.','shares'=>$shares]);
        exit;
    }
}
