<?php
declare(strict_types=1);

class NotulenTemplateController
{
    public static function index(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        $templates = Database::query(
            "SELECT t.*, u.name AS creator_name
             FROM notulen_templates t
             LEFT JOIN users u ON u.id = t.created_by
             ORDER BY t.is_default DESC, t.created_at DESC"
        );
        View::layout('notulen-templates/index', [
            'pageTitle' => 'Template Notulen',
            'templates' => $templates,
        ]);
    }

    public static function store(): void
    {
        Auth::requireRole('admin', 'sekretaris');
        Auth::verifyCsrf();

        $name          = trim($_POST['name']            ?? '');
        $description   = trim($_POST['description']     ?? '');
        $letterhead    = trim($_POST['letterhead_html'] ?? '');
        $content       = $_POST['content']              ?? '';
        $isDefault     = isset($_POST['is_default'])    ? 1 : 0;

        if ($name === '' || $content === '') {
            $_SESSION['flash_error'] = 'Nama dan konten template wajib diisi.';
            header('Location: ' . rtrim(BASE_URL, '/') . '/notulen-templates'); exit;
        }

        if ($isDefault) {
            Database::getInstance()->prepare("UPDATE notulen_templates SET is_default=0")->execute();
        }

        Database::getInstance()->prepare(
            "INSERT INTO notulen_templates (name, description, letterhead_html, content, is_default, created_by)
             VALUES (?, ?, ?, ?, ?, ?)"
        )->execute([$name, $description, $letterhead ?: null, $content, $isDefault, Auth::id()]);

        $_SESSION['flash_success'] = 'Template berhasil disimpan.';
        header('Location: ' . rtrim(BASE_URL, '/') . '/notulen-templates'); exit;
    }

    public static function update(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        Auth::verifyCsrf();

        $name        = trim($_POST['name']            ?? '');
        $description = trim($_POST['description']     ?? '');
        $letterhead  = trim($_POST['letterhead_html'] ?? '');
        $content     = $_POST['content']              ?? '';
        $isDefault   = isset($_POST['is_default'])    ? 1 : 0;

        if ($name === '' || $content === '') {
            $_SESSION['flash_error'] = 'Nama dan konten template wajib diisi.';
            header('Location: ' . rtrim(BASE_URL, '/') . '/notulen-templates'); exit;
        }

        if ($isDefault) {
            Database::getInstance()->prepare("UPDATE notulen_templates SET is_default=0")->execute();
        }

        Database::getInstance()->prepare(
            "UPDATE notulen_templates
             SET name=?, description=?, letterhead_html=?, content=?, is_default=?, updated_at=NOW()
             WHERE id=?"
        )->execute([$name, $description, $letterhead ?: null, $content, $isDefault, $id]);

        $_SESSION['flash_success'] = 'Template berhasil diupdate.';
        header('Location: ' . rtrim(BASE_URL, '/') . '/notulen-templates'); exit;
    }

    public static function destroy(int $id): void
    {
        Auth::requireRole('admin', 'sekretaris');
        header('Content-Type: application/json');
        $tpl = Database::queryOne("SELECT id FROM notulen_templates WHERE id=?", [$id]);
        if (!$tpl) {
            echo json_encode(['success' => false, 'message' => 'Template tidak ditemukan.']); exit;
        }
        Database::getInstance()->prepare("DELETE FROM notulen_templates WHERE id=?")->execute([$id]);
        echo json_encode(['success' => true]); exit;
    }

    public static function apiGet(int $id): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');
        $tpl = Database::queryOne(
            "SELECT id, name, letterhead_html, content FROM notulen_templates WHERE id=?", [$id]
        );
        if (!$tpl) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Template tidak ditemukan.']); exit;
        }
        echo json_encode(['success' => true, 'template' => $tpl]); exit;
    }

    public static function apiList(): void
    {
        Auth::requireAuth();
        header('Content-Type: application/json');
        $templates = Database::query(
            "SELECT id, name, description, is_default
             FROM notulen_templates ORDER BY is_default DESC, name ASC"
        );
        echo json_encode(['success' => true, 'templates' => $templates]); exit;
    }
}
