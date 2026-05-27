<?php
declare(strict_types=1);

// Bootstrap
session_start();
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH',  ROOT_PATH . '/app');
define('BASE_URL',  (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']);

// Autoload core & controllers
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

// Auto-login via remember cookie
AuthController::checkRememberToken();

// Router
$router = new Router();

// === AUTH ROUTES ===
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
$router->get('/notulen/{id}',           [NotulisController::class, 'editor']);
$router->get('/notulen/{id}/history',   [NotulisController::class, 'history']);
$router->get('/notulen/{id}/export-pdf',[ExportController::class,  'exportPdf']);

// === API NOTULEN ===
$router->post('/api/notulen/save',      [NotulisController::class, 'save']);
$router->get('/api/notulen/sync',       [NotulisController::class, 'sync']);

// === TINDAK LANJUT ===
$router->get('/tindak-lanjut',              [TindakLanjutController::class, 'index']);
$router->post('/tindak-lanjut',             [TindakLanjutController::class, 'store']);
$router->post('/tindak-lanjut/{id}/status', [TindakLanjutController::class, 'updateStatus']);
$router->post('/tindak-lanjut/{id}/delete', [TindakLanjutController::class, 'destroy']);

// === USERS ===
$router->get('/users',               [UserController::class, 'index']);
$router->post('/users',              [UserController::class, 'store']);
$router->post('/users/{id}/update',  [UserController::class, 'update']);
$router->post('/users/{id}/delete',  [UserController::class, 'delete']);

// === API MEETINGS ===
$router->get('/api/meetings/calendar', [MeetingController::class, 'calendarApi']);

// === EMAIL ===
$router->post('/meetings/{id}/send-invitations', [EmailController::class, 'sendInvitations']);
$router->post('/meetings/{id}/send-summary',     [EmailController::class, 'sendSummary']);
$router->get('/api/email/send-reminders',        [EmailController::class, 'sendDeadlineReminders']);

// === API NOTIFICATIONS ===
$router->get('/api/notifications',        [NotifikasiController::class, 'index']);
$router->post('/api/notifications/read',  [NotifikasiController::class, 'markRead']);
$router->get('/notifications',            [NotifikasiController::class, 'page']);

// Dispatch
$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);
