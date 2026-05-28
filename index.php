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

if (defined('APP_DEBUG') && APP_DEBUG === true) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    error_reporting(E_ALL);
    $logDir = ROOT_PATH . '/logs';
    if (!is_dir($logDir)) @mkdir($logDir, 0755, true);
    ini_set('error_log', $logDir . '/php_errors.log');
}

AuthController::checkRememberToken();

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

// === MEETINGS ===
$router->get('/meetings',               [MeetingController::class, 'index']);
$router->post('/meetings',              [MeetingController::class, 'store']);
$router->get('/meetings/{id}',          [MeetingController::class, 'show']);
$router->post('/meetings/{id}/status',  [MeetingController::class, 'updateStatus']);
$router->post('/meetings/{id}/delete',  [MeetingController::class, 'destroy']);

// === NOTULEN ===
$router->get('/notulen/{id}',             [NotulisController::class, 'editor']);
$router->get('/notulen/{id}/history',     [NotulisController::class, 'history']);
$router->get('/notulen/{id}/export-pdf',  [ExportController::class,  'exportPdf']);

// === API NOTULEN ===
$router->post('/api/notulen/save',        [NotulisController::class, 'save']);
$router->get('/api/notulen/sync',         [NotulisController::class, 'sync']);

// === API KOMENTAR ===
$router->get('/api/notulen/{id}/comments',  [CommentController::class, 'index']);
$router->post('/api/notulen/{id}/comments', [CommentController::class, 'store']);
$router->post('/api/comments/{id}/resolve', [CommentController::class, 'resolve']);
$router->post('/api/comments/{id}/delete',  [CommentController::class, 'delete']);

// === TINDAK LANJUT ===
$router->get('/tindak-lanjut',              [TindakLanjutController::class, 'index']);
$router->post('/tindak-lanjut',             [TindakLanjutController::class, 'store']);
$router->post('/tindak-lanjut/{id}/status', [TindakLanjutController::class, 'updateStatus']);
$router->post('/tindak-lanjut/{id}/delete', [TindakLanjutController::class, 'destroy']);

// === USERS ===
$router->get('/users',                [UserController::class, 'index']);
$router->post('/users',               [UserController::class, 'store']);
$router->post('/users/{id}/update',   [UserController::class, 'update']);
$router->post('/users/{id}/delete',   [UserController::class, 'delete']);
$router->post('/users/{id}/destroy',  [UserController::class, 'destroy']);

// === DEPARTMENTS ===
$router->get('/departments',               [DepartmentController::class, 'index']);
$router->post('/departments',              [DepartmentController::class, 'store']);
$router->post('/departments/{id}/update',  [DepartmentController::class, 'update']);
$router->post('/departments/{id}/delete',  [DepartmentController::class, 'delete']);
$router->get('/api/departments',           [DepartmentController::class, 'apiList']);

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

// ── Dispatch ──────────────────────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
    $uri = substr($uri, strlen($scriptDir));
}
$uri = $uri ?: '/';

$router->dispatch($method, $uri);
