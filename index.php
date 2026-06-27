<?php
declare(strict_types=1);

session_start();
define('ROOT_PATH', __DIR__);
define('APP_PATH',  ROOT_PATH . '/app');
define('BASE_URL',
    (isset($_SERVER['HTTPS']) ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
);

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

// ── Global Error Handler ─────────────────────────────────────────────
// Harus di-register SETELAH config di-load (butuh APP_DEBUG & ROOT_PATH)
ErrorHandler::register();

// === AUTH ===
$router = new Router();

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
$router->post('/meetings',                 [MeetingController::class, 'store']);
$router->get('/meetings/{id}',             [MeetingController::class, 'show']);
$router->get('/meetings/{id}/edit',        [MeetingController::class, 'edit']);
$router->post('/meetings/{id}/update',     [MeetingController::class, 'update']);
$router->post('/meetings/{id}/status',     [MeetingController::class, 'updateStatus']);
$router->post('/meetings/{id}/delete',     [MeetingController::class, 'destroy']);

// === NOTULEN ===
$router->get('/notulen/{id}',              [NotulisController::class, 'editor']);
$router->get('/notulen/{id}/history',      [NotulisController::class, 'history']);
$router->get('/notulen/{id}/export-pdf',   [ExportController::class,  'exportPdf']);
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
$router->post('/tindak-lanjut/{id}/status',                  [TindakLanjutController::class, 'updateStatus']);
$router->get('/tindak-lanjut/{id}/notes',                    [TindakLanjutController::class, 'getNotes']);
$router->post('/tindak-lanjut/{id}/notes',                   [TindakLanjutController::class, 'addNote']);
$router->post('/tindak-lanjut/{tlId}/notes/{noteId}/delete', [TindakLanjutController::class, 'deleteNote']);
$router->post('/tindak-lanjut/{id}/delete',                  [TindakLanjutController::class, 'destroy']);

// === USERS ===
$router->get('/users',                [UserController::class, 'index']);
$router->post('/users',               [UserController::class, 'store']);
$router->post('/users/{id}/update',   [UserController::class, 'update']);
$router->post('/users/{id}/delete',   [UserController::class, 'delete']);
$router->post('/users/{id}/destroy',  [UserController::class, 'destroy']);

// === UNIT KERJA (departments) ===
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
$router->get('/api/notifications',        [NotifikasiController::class, 'index']);
$router->post('/api/notifications/read',  [NotifikasiController::class, 'markRead']);
$router->get('/notifications',            [NotifikasiController::class, 'page']);

// === SETTINGS ===
$router->get('/settings',                       [SettingController::class, 'index']);
$router->post('/api/settings/upload-logo',      [SettingController::class, 'uploadLogo']);
$router->post('/api/settings/upload-login-bg',  [SettingController::class, 'uploadLoginBg']);
$router->post('/api/settings/remove-logo',      [SettingController::class, 'removeLogo']);
$router->post('/api/settings/remove-login-bg',  [SettingController::class, 'removeLoginBg']);
$router->post('/api/settings/save-smtp',        [SettingController::class, 'saveSMTP']);
$router->post('/api/settings/test-smtp',        [SettingController::class, 'testSMTP']);

// === TEMPLATE NOTULEN ===
$router->get('/notulen-templates',                   [NotulenTemplateController::class, 'index']);
$router->post('/notulen-templates',                  [NotulenTemplateController::class, 'store']);
$router->post('/notulen-templates/{id}/update',      [NotulenTemplateController::class, 'update']);
$router->post('/notulen-templates/{id}/delete',      [NotulenTemplateController::class, 'destroy']);
$router->get('/api/notulen-templates',               [NotulenTemplateController::class, 'apiList']);
$router->get('/api/notulen-templates/{id}',          [NotulenTemplateController::class, 'apiGet']);

// === ACTIVITY LOG (admin only) ===
$router->get('/admin/activity-log',        [ActivityLogController::class, 'index']);
$router->post('/admin/activity-log/purge', [ActivityLogController::class, 'purge']);

AuthController::checkRememberToken();

// ── Dispatch ────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir !== '' && strncmp($uri, $scriptDir, strlen($scriptDir)) === 0) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = $uri ?: '/';

$router->dispatch($method, $uri);
