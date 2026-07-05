<?php
declare(strict_types=1);

// ── Session hardening (harus sebelum session_start) ──────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.gc_maxlifetime', '7200'); // 2 jam
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
           || ($_SERVER['SERVER_PORT'] ?? 80) == 443;
if ($isHttps) {
    ini_set('session.cookie_secure', '1');
}
session_start();

define('ROOT_PATH', __DIR__);
define('APP_PATH',  ROOT_PATH . '/app');
define('BASE_URL',
    ($isHttps ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
);

// ── Global Security Headers ──────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

spl_autoload_register(function(string $class): void {
    $paths = [
        APP_PATH . '/core/'        . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php',
        APP_PATH . '/models/'      . $class . '.php',
    ];
    foreach ($paths as $path) {
        if (file_exists($path)) { require_once $path; return; }
    }
});

require_once APP_PATH . '/config/app.php';

ErrorHandler::register();

$router = new Router();

// === AUTH ===
$router->get('/login',           [AuthController::class, 'loginForm']);
$router->post('/login',          [AuthController::class, 'login']);
$router->get('/logout',          [AuthController::class, 'logout']);
$router->get('/forgot-password', [AuthController::class, 'forgotPassword']);
$router->post('/forgot-password',[AuthController::class, 'forgotPassword']);
$router->get('/reset-password',  [AuthController::class, 'resetPassword']);
$router->post('/reset-password', [AuthController::class, 'resetPassword']);

// === DASHBOARD ===
$router->get('/', [DashboardController::class, 'index']);
$router->get('/api/dashboard/chart-monthly',  [DashboardController::class, 'chartMonthly']);
$router->get('/api/dashboard/chart-tl-status',[DashboardController::class, 'chartTlStatus']);
$router->get('/api/dashboard/chart-top-dept', [DashboardController::class, 'chartTopDept']);
$router->get('/api/dashboard/chart-tl-trend', [DashboardController::class, 'chartTlTrend']);

// === PROFILE ===
$router->get('/profile',                   [ProfileController::class, 'index']);
$router->post('/profile/update',           [ProfileController::class, 'update']);
$router->post('/profile/change-password',  [ProfileController::class, 'changePassword']);

// === MEETINGS ===
$router->get('/meetings',                  [MeetingController::class, 'index']);
$router->get('/meetings/create',           [MeetingController::class, 'create']);
$router->post('/meetings',                 [MeetingController::class, 'store']);
$router->get('/meetings/{id}',             [MeetingController::class, 'show']);
$router->get('/meetings/{id}/edit',        [MeetingController::class, 'edit']);
$router->post('/meetings/{id}/update',     [MeetingController::class, 'update']);
$router->post('/meetings/{id}/status',     [MeetingController::class, 'updateStatus']);
$router->post('/meetings/{id}/delete',     [MeetingController::class, 'destroy']);

// === NOTULEN ===
$router->get('/notulen/{id}',              [NotulisController::class, 'editor']);
$router->get('/notulen/{id}/history',      [NotulisController::class, 'history']);
$router->get('/notulen/{id}/export-docx',  [ExportController::class,  'exportDocx']);

// === API NOTULEN ===
$router->post('/api/notulen/save',        [NotulisController::class, 'save']);
$router->get('/api/notulen/sync',         [NotulisController::class, 'sync']);

// === API KOMENTAR ===
$router->get('/api/notulen/{id}/comments',  [CommentController::class, 'index']);
$router->post('/api/notulen/{id}/comments', [CommentController::class, 'store']);
$router->post('/api/comments/{id}/resolve', [CommentController::class, 'resolve']);
$router->post('/api/comments/{id}/delete',  [CommentController::class, 'delete']);

// === TINDAK LANJUT ===
$router->get('/tindak-lanjut',                               [TindakLanjutController::class, 'index']);
$router->post('/tindak-lanjut',                              [TindakLanjutController::class, 'store']);
$router->get('/tindak-lanjut/{id}',                          [TindakLanjutController::class, 'show']);
$router->post('/tindak-lanjut/{id}/status',                  [TindakLanjutController::class, 'updateStatus']);
$router->get('/tindak-lanjut/{id}/notes',                    [TindakLanjutController::class, 'getNotes']);
$router->post('/tindak-lanjut/{id}/notes',                   [TindakLanjutController::class, 'addNote']);
$router->post('/tindak-lanjut/{tlId}/notes/{noteId}/delete', [TindakLanjutController::class, 'deleteNote']);
$router->post('/tindak-lanjut/{id}/delete',                  [TindakLanjutController::class, 'destroy']);

// === USERS ===
$router->get('/users',                [UserController::class, 'index']);
$router->post('/users',               [UserController::class, 'store']);
$router->post('/users/{id}/update',   [UserController::class, 'update']);
$router->post('/users/{id}/delete',   [UserController::class, 'destroy']);  // fix: hapus duplikat, satukan ke destroy
$router->get('/api/users',            [UserController::class, 'apiList']);

// === ROLES & PERMISSIONS ===
$router->get('/roles',                          [RoleController::class, 'index']);
$router->post('/roles',                         [RoleController::class, 'store']);
$router->post('/roles/{id}/update',             [RoleController::class, 'update']);
$router->post('/roles/{id}/delete',             [RoleController::class, 'delete']);
$router->get('/api/roles',                      [RoleController::class, 'apiList']);
$router->post('/api/roles/{id}/permissions',    [RoleController::class, 'syncPermissions']);

// === UNIT KERJA ===
$router->get('/departments',                     [DepartmentController::class, 'index']);
$router->post('/departments',                    [DepartmentController::class, 'store']);
$router->post('/departments/{id}/update',        [DepartmentController::class, 'update']);
$router->post('/departments/{id}/delete',        [DepartmentController::class, 'delete']);
$router->get('/api/departments',                 [DepartmentController::class, 'apiList']);
$router->get('/api/departments/children',        [DepartmentController::class, 'apiChildren']);

// === ATTACHMENTS ===
$router->get('/api/meetings/{id}/attachments',  [AttachmentController::class, 'index']);
$router->post('/api/meetings/{id}/attachments', [AttachmentController::class, 'upload']);
$router->get('/attachments/{id}/download',      [AttachmentController::class, 'download']);
$router->post('/api/attachments/{id}/delete',   [AttachmentController::class, 'delete']);

// === RECURRING MEETINGS ===
$router->get('/recurring',                    [RecurringController::class, 'index']);
$router->post('/recurring',                   [RecurringController::class, 'store']);
$router->post('/recurring/{id}/generate',     [RecurringController::class, 'generate']);
$router->post('/recurring/{id}/delete',       [RecurringController::class, 'delete']);
$router->post('/api/recurring/generate-all',  [RecurringController::class, 'generateAll']);

// === API MEETINGS ===
$router->get('/api/meetings/calendar', [MeetingController::class, 'calendarApi']);

// === EMAIL ===
$router->post('/meetings/{id}/send-invitations', [EmailController::class, 'sendInvitations']);
$router->post('/meetings/{id}/send-summary',     [EmailController::class, 'sendSummary']);
$router->get('/api/email/send-reminders',        [EmailController::class, 'sendDeadlineReminders']);

// === NOTIFICATIONS ===
$router->get('/api/notifications',             [NotifikasiController::class, 'index']);
$router->post('/api/notifications/read',       [NotifikasiController::class, 'markRead']);
$router->post('/api/notifications/delete-all', [NotifikasiController::class, 'deleteAll']); // fix: tambah route baru
$router->get('/notifications',                 [NotifikasiController::class, 'page']);

// === SETTINGS ===
$router->get('/settings',                           [SettingController::class, 'index']);
$router->post('/api/settings/upload-logo',          [SettingController::class, 'uploadLogo']);
$router->post('/api/settings/upload-login-bg',      [SettingController::class, 'uploadLoginBg']);
$router->post('/api/settings/remove-logo',          [SettingController::class, 'removeLogo']);
$router->post('/api/settings/remove-login-bg',      [SettingController::class, 'removeLoginBg']);
$router->post('/api/settings/save-smtp',            [SettingController::class, 'saveSMTP']);
$router->post('/api/settings/test-smtp',            [SettingController::class, 'testSMTP']);
$router->post('/api/settings/save-notulen',         [SettingController::class, 'saveNotulen']);
$router->post('/api/settings/upload-docx-template', [SettingController::class, 'uploadDocxTemplate']);
$router->post('/api/settings/remove-docx-template', [SettingController::class, 'removeDocxTemplate']);

// === TEMPLATE NOTULEN ===
$router->get('/notulen-templates',                   [NotulenTemplateController::class, 'index']);
$router->post('/notulen-templates',                  [NotulenTemplateController::class, 'store']);
$router->post('/notulen-templates/{id}/update',      [NotulenTemplateController::class, 'update']);
$router->post('/notulen-templates/{id}/delete',      [NotulenTemplateController::class, 'destroy']);
$router->get('/api/notulen-templates',               [NotulenTemplateController::class, 'apiList']);
$router->get('/api/notulen-templates/{id}',          [NotulenTemplateController::class, 'apiGet']);

// === ACTIVITY LOG ===
$router->get('/admin/activity-log',        [ActivityLogController::class, 'index']);
$router->post('/admin/activity-log/purge', [ActivityLogController::class, 'purge']);

// === DOKUMEN Fase 1 ===
$router->get('/dokumen',                          [DokumenController::class, 'index']);
$router->post('/api/dokumen/upload',              [DokumenController::class, 'upload']);
$router->post('/api/dokumen/folder/create',       [DokumenController::class, 'createFolder']);
$router->post('/api/dokumen/folder/{id}/rename',  [DokumenController::class, 'renameFolder']);
$router->post('/api/dokumen/folder/{id}/delete',  [DokumenController::class, 'deleteFolder']);
$router->post('/api/dokumen/{id}/rename',         [DokumenController::class, 'renameFile']);
$router->post('/api/dokumen/{id}/delete',         [DokumenController::class, 'deleteFile']);
$router->get('/dokumen/{id}/download',            [DokumenController::class, 'download']);

// === DOKUMEN Fase 2 ===
$router->get('/api/dokumen/{id}/shares',                   [DokumenShareController::class, 'index']);
$router->post('/api/dokumen/{id}/shares',                  [DokumenShareController::class, 'store']);
$router->post('/api/dokumen/{id}/shares/{uid}/delete',     [DokumenShareController::class, 'destroy']);
$router->post('/api/dokumen/{id}/shares/{uid}/permission', [DokumenShareController::class, 'updatePermission']);

// === DOKUMEN Fase 3 ===
$router->get('/api/dokumen/{id}/preview',              [DokumenController::class, 'preview']);
$router->get('/api/dokumen/{id}/info',                 [DokumenController::class, 'info']);
$router->get('/api/dokumen/{id}/preview-public',       [DokumenController::class, 'previewPublic']);

// === DOKUMEN Fase 4 ===
$router->get('/api/dokumen/{id}/versions',             [DokumenVersionController::class, 'index']);
$router->post('/api/dokumen/{id}/versions/upload',     [DokumenVersionController::class, 'uploadNewVersion']);
$router->get('/api/dokumen/versions/{vid}/download',   [DokumenVersionController::class, 'downloadVersion']);
$router->post('/api/dokumen/{id}/versions/restore',    [DokumenVersionController::class, 'restore']);

// === DOKUMEN Fase 5 ===
$router->get('/api/dokumen/tags',                      [DokumenTagController::class, 'tagList']);
$router->post('/api/dokumen/tags',                     [DokumenTagController::class, 'tagStore']);
$router->post('/api/dokumen/tags/{id}/update',         [DokumenTagController::class, 'tagUpdate']);
$router->post('/api/dokumen/tags/{id}/delete',         [DokumenTagController::class, 'tagDelete']);
$router->get('/api/dokumen/kategoris',                 [DokumenTagController::class, 'kategoriList']);
$router->post('/api/dokumen/kategoris',                [DokumenTagController::class, 'kategoriStore']);
$router->post('/api/dokumen/kategoris/{id}/update',    [DokumenTagController::class, 'kategoriUpdate']);
$router->post('/api/dokumen/kategoris/{id}/delete',    [DokumenTagController::class, 'kategoriDelete']);
$router->get('/api/dokumen/{id}/tags',                 [DokumenTagController::class, 'fileTags']);
$router->post('/api/dokumen/{id}/tags/sync',           [DokumenTagController::class, 'syncFileTags']);
$router->post('/api/dokumen/{id}/kategori',            [DokumenTagController::class, 'setFileKategori']);

// === DOKUMEN Fase 6 — Public Share Link ===
$router->get('/api/dokumen/{id}/public-links',                       [DokumenPublicLinkController::class, 'index']);
$router->post('/api/dokumen/{id}/public-links',                      [DokumenPublicLinkController::class, 'store']);
$router->post('/api/dokumen/{id}/public-links/{lid}/delete',         [DokumenPublicLinkController::class, 'destroy']);
$router->get('/d/{token}',                                           [DokumenPublicLinkController::class, 'publicPage']);
$router->post('/d/{token}',                                          [DokumenPublicLinkController::class, 'publicPage']);
$router->get('/d/{token}/download',                                  [DokumenPublicLinkController::class, 'publicDownload']);

// ── Bootstrap: remember-me & CSRF global enforcement ──────────────────
Auth::checkRememberToken();

$method    = $_SERVER['REQUEST_METHOD'];
$uri       = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir !== '' && strncmp($uri, $scriptDir, strlen($scriptDir)) === 0) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = $uri ?: '/';

// ── CSRF: enforce on semua POST kecuali route publik & auth ─────────
if ($method === 'POST') {
    $exemptPrefixes = array_merge(
        Auth::csrfExemptPrefixes(),
        ['/d/']  // public share link (tanpa login)
    );
    $isExempt = false;
    foreach ($exemptPrefixes as $prefix) {
        if ($uri === $prefix || str_starts_with($uri, $prefix)) {
            $isExempt = true;
            break;
        }
    }
    if (!$isExempt) {
        Auth::requireCsrf();
    }
}

$router->dispatch($method, $uri);
