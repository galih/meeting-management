<?php
declare(strict_types=1);

class DokumenTagController
{
    private static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /* ==== KATEGORI ==== */

    public static function kategoriList(): void
    {
        Auth::requireLogin();
        self::json(['success' => true, 'kategoris' => DokumenKategoriModel::all()]);
    }

    public static function kategoriStore(): void
    {
        Auth::requireLogin();
        if (!Auth::hasRole('admin')) self::json(['success'=>false,'message'=>'Hanya admin.'], 403);
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#7B1C1C');
        if (!$name) self::json(['success'=>false,'message'=>'Nama kategori wajib diisi.']);
        try {
            $id = DokumenKategoriModel::create($name, $color, Auth::id());
            ActivityLog::record('dokumen.kategori.create', 'Kategori dibuat: '.$name, 'dokumen_kategori', $id);
            self::json(['success'=>true,'message'=>'Kategori dibuat.','id'=>$id,'kategoris'=>DokumenKategoriModel::all()]);
        } catch (\Throwable $e) {
            self::json(['success'=>false,'message'=>'Nama sudah digunakan.']);
        }
    }

    public static function kategoriUpdate(int $id): void
    {
        Auth::requireLogin();
        if (!Auth::hasRole('admin')) self::json(['success'=>false,'message'=>'Hanya admin.'], 403);
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#7B1C1C');
        if (!$name) self::json(['success'=>false,'message'=>'Nama wajib diisi.']);
        if (!DokumenKategoriModel::getById($id)) self::json(['success'=>false,'message'=>'Tidak ditemukan.']);
        try {
            DokumenKategoriModel::update($id, $name, $color);
            ActivityLog::record('dokumen.kategori.update', 'Kategori diubah: '.$name, 'dokumen_kategori', $id);
            self::json(['success'=>true,'message'=>'Kategori diperbarui.','kategoris'=>DokumenKategoriModel::all()]);
        } catch (\Throwable $e) {
            self::json(['success'=>false,'message'=>'Nama sudah digunakan.']);
        }
    }

    public static function kategoriDelete(int $id): void
    {
        Auth::requireLogin();
        if (!Auth::hasRole('admin')) self::json(['success'=>false,'message'=>'Hanya admin.'], 403);
        if (!DokumenKategoriModel::getById($id)) self::json(['success'=>false,'message'=>'Tidak ditemukan.']);
        DokumenKategoriModel::delete($id);
        ActivityLog::record('dokumen.kategori.delete', 'Kategori dihapus id '.$id, 'dokumen_kategori', $id);
        self::json(['success'=>true,'message'=>'Kategori dihapus.','kategoris'=>DokumenKategoriModel::all()]);
    }

    /* ==== TAG ==== */

    public static function tagList(): void
    {
        Auth::requireLogin();
        self::json(['success'=>true,'tags'=>DokumenTagModel::all(),'kategoris'=>DokumenKategoriModel::all()]);
    }

    public static function tagStore(): void
    {
        Auth::requireLogin();
        $name     = trim($_POST['name'] ?? '');
        $color    = trim($_POST['color'] ?? '#2B6CB0');
        $katId    = ($_POST['kategori_id'] ?? '') !== '' ? (int)$_POST['kategori_id'] : null;
        if (!$name) self::json(['success'=>false,'message'=>'Nama tag wajib diisi.']);
        try {
            $id = DokumenTagModel::create($name, $color, $katId, Auth::id());
            ActivityLog::record('dokumen.tag.create', 'Tag dibuat: '.$name, 'dokumen_tag', $id);
            self::json(['success'=>true,'message'=>'Tag dibuat.','id'=>$id,'tags'=>DokumenTagModel::all(),'kategoris'=>DokumenKategoriModel::all()]);
        } catch (\Throwable $e) {
            self::json(['success'=>false,'message'=>'Nama tag sudah digunakan.']);
        }
    }

    public static function tagUpdate(int $id): void
    {
        Auth::requireLogin();
        $name  = trim($_POST['name'] ?? '');
        $color = trim($_POST['color'] ?? '#2B6CB0');
        $katId = ($_POST['kategori_id'] ?? '') !== '' ? (int)$_POST['kategori_id'] : null;
        if (!$name) self::json(['success'=>false,'message'=>'Nama wajib diisi.']);
        if (!DokumenTagModel::getById($id)) self::json(['success'=>false,'message'=>'Tidak ditemukan.']);
        try {
            DokumenTagModel::update($id, $name, $color, $katId);
            ActivityLog::record('dokumen.tag.update', 'Tag diubah: '.$name, 'dokumen_tag', $id);
            self::json(['success'=>true,'message'=>'Tag diperbarui.','tags'=>DokumenTagModel::all(),'kategoris'=>DokumenKategoriModel::all()]);
        } catch (\Throwable $e) {
            self::json(['success'=>false,'message'=>'Nama sudah digunakan.']);
        }
    }

    public static function tagDelete(int $id): void
    {
        Auth::requireLogin();
        if (!DokumenTagModel::getById($id)) self::json(['success'=>false,'message'=>'Tidak ditemukan.']);
        DokumenTagModel::delete($id);
        ActivityLog::record('dokumen.tag.delete', 'Tag dihapus id '.$id, 'dokumen_tag', $id);
        self::json(['success'=>true,'message'=>'Tag dihapus.','tags'=>DokumenTagModel::all(),'kategoris'=>DokumenKategoriModel::all()]);
    }

    /* ==== TAG pada FILE ==== */

    public static function fileTags(int $fileId): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        if (!DokumenShareModel::canAccess($fileId, $userId, $isAdmin)) self::json(['success'=>false,'message'=>'Akses ditolak.'], 403);
        self::json([
            'success'   => true,
            'tags'      => DokumenTagModel::forFile($fileId),
            'all_tags'  => DokumenTagModel::all(),
            'kategoris' => DokumenKategoriModel::all(),
        ]);
    }

    public static function syncFileTags(int $fileId): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId && !DokumenShareModel::hasSharePermission($fileId, $userId, 'edit')) {
            self::json(['success'=>false,'message'=>'Tidak diizinkan mengedit tag.'], 403);
        }
        $tagIds = array_filter(array_map('intval', (array)($_POST['tag_ids'] ?? [])));
        DokumenTagModel::syncFileTags($fileId, $tagIds, $userId);
        ActivityLog::record('dokumen.tag.sync', 'Tag file diperbarui', 'dokumen', $fileId);
        self::json(['success'=>true,'message'=>'Tag diperbarui.','tags'=>DokumenTagModel::forFile($fileId)]);
    }

    /* ==== KATEGORI pada FILE ==== */

    public static function setFileKategori(int $fileId): void
    {
        Auth::requireLogin();
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        $file    = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            self::json(['success'=>false,'message'=>'Hanya pemilik/admin yang dapat mengubah kategori.'], 403);
        }
        $katId = ($_POST['kategori_id'] ?? '') !== '' ? (int)$_POST['kategori_id'] : null;
        $db = Database::getInstance();
        $db->prepare("UPDATE dokumen_files SET kategori_id=? WHERE id=?")->execute([$katId, $fileId]);
        ActivityLog::record('dokumen.kategori.set', 'Kategori file diset', 'dokumen', $fileId);
        self::json(['success'=>true,'message'=>'Kategori file diperbarui.']);
    }
}
