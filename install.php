<?php
/**
 * Meeting Management App — Web Installer v1.6.0
 * Letakkan file ini di root folder, buka via browser sekali saja.
 * Hapus file ini setelah instalasi selesai!
 */

define('INSTALLER_VERSION', '1.6.0');
define('MIN_PHP', '8.1.0');

session_start();

// Self-delete handler
if (($_GET['action'] ?? '') === 'self_delete') {
    header('Content-Type: application/json');
    echo json_encode(['success' => (bool) @unlink(__FILE__)]);
    exit;
}

// ─── Helpers ────────────────────────────────────────────────────────────────

function checkPhpExtensions(): array {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'fileinfo'];
    $missing  = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) $missing[] = $ext;
    }
    return $missing;
}

function checkWritable(): array {
    $paths = ['app/config', 'public/assets', 'public/uploads', 'public/uploads/attachments'];
    $results = [];
    foreach ($paths as $p) {
        if (!is_dir($p)) @mkdir($p, 0755, true);
        $results[$p] = is_writable($p);
    }
    return $results;
}

function testDbConnection(array $cfg): bool|string {
    try {
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4";
        new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

function createDatabase(PDO $pdo, string $dbname): void {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `{$dbname}`");
}

function dropAllTables(PDO $pdo): void {
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `{$t}`");
    }
    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
}

function importSqlFile(PDO $pdo, string $filePath): bool|string {
    if (!file_exists($filePath)) return "File tidak ditemukan: {$filePath}";
    $sql = file_get_contents($filePath);
    $sql = preg_replace('/--[^\n]*/', '', $sql);
    $sql = preg_replace('#/\*.*?\*/#s', '', $sql);
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    try {
        foreach ($statements as $stmt) {
            if (!empty($stmt)) $pdo->exec($stmt);
        }
        return true;
    } catch (PDOException $e) {
        return $e->getMessage();
    }
}

/**
 * Tulis app/config/database.php
 * Key HARUS sesuai dengan yang dibaca Database.php: host, name, user, pass, charset
 */
function writeDbConfig(array $cfg): bool {
    $host    = addslashes($cfg['host']);
    $name    = addslashes($cfg['name']);
    $user    = addslashes($cfg['user']);
    $pass    = addslashes($cfg['pass']);
    $content = <<<PHP
<?php
// Konfigurasi Database — di-generate oleh Installer v<?= INSTALLER_VERSION ?>
// Jangan commit file ini ke repository!
return [
    'host'    => '{$host}',
    'name'    => '{$name}',
    'user'    => '{$user}',
    'pass'    => '{$pass}',
    'charset' => 'utf8mb4',
];
PHP;
    return (bool) file_put_contents(__DIR__ . '/app/config/database.php', $content);
}

function writeAppConfig(array $cfg): bool {
    $secret   = bin2hex(random_bytes(32));
    $appName  = addslashes($cfg['app_name']);
    $appUrl   = addslashes($cfg['app_url']);
    $baseUrl  = addslashes(rtrim($cfg['app_url'], '/'));
    $content  = <<<PHP
<?php
// Konfigurasi Aplikasi — di-generate oleh Installer
define('APP_NAME',    '{$appName}');
define('APP_URL',     '{$appUrl}');
define('BASE_URL',    '{$baseUrl}');
define('APP_ENV',     'production');
define('APP_DEBUG',   false);
define('APP_SECRET',  '{$secret}');
define('APP_LOCALE',  'id');
define('APP_TIMEZONE','Asia/Jakarta');
PHP;
    return (bool) file_put_contents(__DIR__ . '/app/config/app.php', $content);
}

function writeMailConfig(array $cfg): bool {
    if (empty($cfg['driver']) || $cfg['driver'] === 'skip') return true;
    $driver    = $cfg['driver'];
    $fromEmail = addslashes($cfg['from_email'] ?? '');
    $fromName  = addslashes($cfg['from_name']  ?? 'Meeting Management App');
    $smtpHost  = addslashes($cfg['smtp_host']  ?? '');
    $smtpPort  = (int)($cfg['smtp_port']        ?? 587);
    $smtpUser  = addslashes($cfg['smtp_user']  ?? '');
    $smtpPass  = addslashes($cfg['smtp_pass']  ?? '');
    $smtpSec   = $cfg['smtp_secure']            ?? 'tls';
    $content   = <<<PHP
<?php
// Konfigurasi Email — di-generate oleh Installer
return [
    'driver'      => '{$driver}',
    'from_email'  => '{$fromEmail}',
    'from_name'   => '{$fromName}',
    'smtp_host'   => '{$smtpHost}',
    'smtp_port'   => {$smtpPort},
    'smtp_secure' => '{$smtpSec}',
    'smtp_user'   => '{$smtpUser}',
    'smtp_pass'   => '{$smtpPass}',
];
PHP;
    return (bool) file_put_contents(__DIR__ . '/app/config/mail.php', $content);
}

function updateAdminUser(PDO $pdo, array $admin): void {
    $hash = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    // Coba update dulu, kalau tidak ada row baru insert
    $stmt = $pdo->prepare("UPDATE users SET username=?, name=?, email=?, password=? WHERE role='admin' LIMIT 1");
    $stmt->execute([$admin['username'], $admin['name'], $admin['email'], $hash]);
    if ($stmt->rowCount() === 0) {
        $pdo->prepare("INSERT INTO users (username, name, email, password, role) VALUES (?,?,?,?,'admin')")
            ->execute([$admin['username'], $admin['name'], $admin['email'], $hash]);
    }
}

// ─── Step Logic ─────────────────────────────────────────────────────────────

$step    = (int) ($_GET['step'] ?? 1);
$errors  = [];
$success = [];
$isReinstall = !empty($_SESSION['reinstall']);

if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!empty($_POST['reinstall'])) $_SESSION['reinstall'] = true;
    $dbCfg = [
        'host' => trim($_POST['db_host'] ?? 'localhost'),
        'port' => trim($_POST['db_port'] ?? '3306'),
        'name' => trim($_POST['db_name'] ?? ''),
        'user' => trim($_POST['db_user'] ?? ''),
        'pass' => $_POST['db_pass']      ?? '',
    ];
    $test = testDbConnection($dbCfg);
    if ($test === true) {
        $_SESSION['installer_db'] = $dbCfg;
        header('Location: install.php?step=3'); exit;
    } else {
        $errors[] = 'Koneksi gagal: ' . $test;
    }
}

if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['installer_app'] = [
        'app_name' => trim($_POST['app_name'] ?? 'Meeting Management App'),
        'app_url'  => rtrim(trim($_POST['app_url'] ?? ''), '/'),
    ];
    $_SESSION['installer_admin'] = [
        'username' => trim($_POST['admin_username'] ?? 'admin'),
        'name'     => trim($_POST['admin_name']     ?? 'Administrator'),
        'email'    => trim($_POST['admin_email']    ?? ''),
        'password' => $_POST['admin_password']      ?? '',
    ];
    $_SESSION['installer_mail'] = [
        'driver'      => $_POST['mail_driver']    ?? 'skip',
        'from_email'  => trim($_POST['mail_from'] ?? ''),
        'from_name'   => trim($_POST['mail_name'] ?? 'Meeting Management App'),
        'smtp_host'   => trim($_POST['smtp_host'] ?? ''),
        'smtp_port'   => trim($_POST['smtp_port'] ?? '587'),
        'smtp_secure' => $_POST['smtp_secure']    ?? 'tls',
        'smtp_user'   => trim($_POST['smtp_user'] ?? ''),
        'smtp_pass'   => $_POST['smtp_pass']      ?? '',
    ];
    $errs = [];
    if (empty($_SESSION['installer_admin']['username']))
        $errs[] = 'Username admin wajib diisi.';
    if (!preg_match('/^[a-z0-9_]{3,50}$/i', $_SESSION['installer_admin']['username']))
        $errs[] = 'Username hanya boleh huruf, angka, underscore (3–50 karakter).';
    if (empty($_SESSION['installer_admin']['email']))
        $errs[] = 'Email admin wajib diisi.';
    if (strlen($_SESSION['installer_admin']['password']) < 8)
        $errs[] = 'Password minimal 8 karakter.';
    if ($_POST['admin_password'] !== ($_POST['admin_password_confirm'] ?? ''))
        $errs[] = 'Konfirmasi password tidak cocok.';
    if ($errs) { $errors = $errs; }
    else { header('Location: install.php?step=4'); exit; }
}

if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbCfg    = $_SESSION['installer_db']    ?? [];
    $appCfg   = $_SESSION['installer_app']   ?? [];
    $adminCfg = $_SESSION['installer_admin'] ?? [];
    $mailCfg  = $_SESSION['installer_mail']  ?? [];
    $isReinstall = !empty($_SESSION['reinstall']);

    try {
        $dsn = "mysql:host={$dbCfg['host']};port={$dbCfg['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbCfg['user'], $dbCfg['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        createDatabase($pdo, $dbCfg['name']);
        $success[] = 'Database <strong>' . htmlspecialchars($dbCfg['name']) . '</strong> siap.';

        if ($isReinstall) {
            dropAllTables($pdo);
            $success[] = '🗑️ Semua tabel lama berhasil dihapus (reinstall).';
        }

        $res = importSqlFile($pdo, __DIR__ . '/database/schema.sql');
        if ($res !== true) throw new Exception('Import schema gagal: ' . $res);
        $success[] = '✅ Schema utama berhasil diimport.';

        updateAdminUser($pdo, $adminCfg);
        $success[] = '✅ Akun admin berhasil dikonfigurasi.';

        if (!writeDbConfig($dbCfg))   throw new Exception('Gagal menulis app/config/database.php.');
        $success[] = '✅ File <code>app/config/database.php</code> berhasil dibuat.';

        if (!writeAppConfig($appCfg)) throw new Exception('Gagal menulis app/config/app.php.');
        $success[] = '✅ File <code>app/config/app.php</code> berhasil dibuat.';

        if (!empty($mailCfg['driver']) && $mailCfg['driver'] !== 'skip') {
            if (!writeMailConfig($mailCfg)) throw new Exception('Gagal menulis app/config/mail.php.');
            $success[] = '✅ File <code>app/config/mail.php</code> berhasil dibuat.';
        } else {
            $success[] = '⏭️ Konfigurasi email dilewati.';
        }

        $uploadDir = __DIR__ . '/public/uploads/attachments';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $success[] = '✅ Folder <code>public/uploads/attachments</code> siap.';

        unset($_SESSION['installer_db'], $_SESSION['installer_app'],
              $_SESSION['installer_admin'], $_SESSION['installer_mail'],
              $_SESSION['reinstall']);
        $step = 5;

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        $step = 4;
    }
}

$phpOk         = version_compare(PHP_VERSION, MIN_PHP, '>=');
$missingExts   = checkPhpExtensions();
$writablePaths = checkWritable();
$allWritable   = !in_array(false, $writablePaths, true);
$canProceed    = $phpOk && empty($missingExts) && $allWritable;
$isInstalled   = file_exists(__DIR__ . '/app/config/database.php') && file_exists(__DIR__ . '/app/config/app.php');

$dbCfgSession    = $_SESSION['installer_db']    ?? [];
$appCfgSession   = $_SESSION['installer_app']   ?? [];
$adminCfgSession = $_SESSION['installer_admin'] ?? [];
$mailCfgSession  = $_SESSION['installer_mail']  ?? [];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installer &mdash; Meeting Management App</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>
    body { background:#f4f6fb; }
    .installer-wrap { max-width:700px; margin:48px auto; padding:0 16px; }
    .step-header { display:flex; gap:8px; margin-bottom:32px; }
    .step-item { flex:1; text-align:center; padding:10px 6px; border-radius:8px; font-size:13px;
                 font-weight:600; background:#fff; border:2px solid #e5e7eb; color:#9ca3af; }
    .step-item.active { border-color:#f76707; color:#f76707; background:#fff7ed; }
    .step-item.done   { border-color:#22c55e; color:#22c55e; background:#f0fdf4; }
    .step-num { display:block; font-size:20px; font-weight:800; }
    .btn-primary { background:#f76707!important; border-color:#f76707!important; }
    .btn-primary:hover { background:#e8600a!important; }
    .form-label.required:after { content:' *'; color:#ef4444; }
    .section-title { font-size:14px; font-weight:700; color:#f76707;
                     border-bottom:2px solid #fed7aa; padding-bottom:6px; margin:20px 0 12px; }
    .reinstall-banner { background:#fff3cd; border:1px solid #ffc107; border-radius:8px;
                        padding:12px 16px; margin-bottom:16px; font-size:13px; }
  </style>
</head>
<body>
<div class="installer-wrap">

  <div class="text-center mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24"
         fill="none" stroke="#f76707" stroke-width="2">
      <rect x="3" y="4" width="18" height="18" rx="2"/>
      <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
      <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    <h1 class="mt-2 mb-0 fw-bold" style="font-size:26px;color:#f76707;">Meeting Management App</h1>
    <p class="text-muted">Web Installer v<?= INSTALLER_VERSION ?></p>
  </div>

  <?php if ($isReinstall): ?>
  <div class="reinstall-banner">⚠️ <strong>Mode Reinstall:</strong> Semua tabel & data lama akan dihapus dan dibuat ulang.</div>
  <?php endif; ?>

  <div class="step-header">
    <?php
    $steps = ['Cek Sistem','Database','Konfigurasi','Instalasi','Selesai'];
    foreach ($steps as $i => $label):
      $n = $i + 1; $cls = $n < $step ? 'done' : ($n === $step ? 'active' : '');
    ?>
    <div class="step-item <?= $cls ?>">
      <span class="step-num"><?= $n < $step ? '✓' : $n ?></span><?= $label ?>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if ($step === 1): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Pemeriksaan Sistem</h3></div>
    <div class="card-body">
      <?php if ($isInstalled): ?>
      <div class="alert alert-warning">
        <strong>Aplikasi sudah terinstall.</strong> Jika ingin instalasi ulang (data akan direset),
        centang opsi <em>Reinstall</em> di Step 2.
      </div>
      <?php endif; ?>
      <table class="table table-sm mb-0">
        <tbody>
          <tr><td>Versi PHP</td><td><code><?= PHP_VERSION ?></code></td>
            <td><?= $phpOk ? '<span class="badge bg-green">OK</span>' : '<span class="badge bg-red">Butuh '.MIN_PHP.'+</span>' ?></td></tr>
          <?php foreach (['pdo','pdo_mysql','mbstring','openssl','json','fileinfo'] as $ext): ?>
          <tr><td>Ekstensi <code><?= $ext ?></code></td><td></td>
            <td><?= extension_loaded($ext) ? '<span class="badge bg-green">Aktif</span>' : '<span class="badge bg-red">Tidak Ada</span>' ?></td></tr>
          <?php endforeach; ?>
          <?php foreach ($writablePaths as $path => $ok): ?>
          <tr><td>Folder <code><?= $path ?>/</code></td><td><small class="text-muted">Writable</small></td>
            <td><?= $ok ? '<span class="badge bg-green">OK</span>' : '<span class="badge bg-red">Tidak Writable</span>' ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$allWritable): ?>
      <div class="alert alert-warning mt-3">
        <pre class="mb-0">chmod 755 app/config public/assets public/uploads public/uploads/attachments</pre>
      </div>
      <?php endif; ?>
      <div class="mt-3">
        <div class="section-title">📁 File Schema Database</div>
        <div class="d-flex justify-content-between small py-1">
          <span>Schema Utama <code class="text-muted">(database/schema.sql)</code></span>
          <?= file_exists('database/schema.sql')
            ? '<span class="badge bg-green-lt text-green">✓ Ada</span>'
            : '<span class="badge bg-red-lt text-red">✗ Tidak Ditemukan</span>' ?>
        </div>
      </div>
    </div>
    <div class="card-footer text-end">
      <?php if ($canProceed): ?>
      <a href="install.php?step=2" class="btn btn-primary">Lanjut &rarr;</a>
      <?php else: ?>
      <button class="btn btn-primary" disabled>Persyaratan Belum Terpenuhi</button>
      <a href="install.php?step=1" class="btn btn-outline-secondary ms-2">Cek Ulang</a>
      <?php endif; ?>
    </div>
  </div>

  <?php elseif ($step === 2): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfigurasi Database</h3></div>
    <form method="POST" action="install.php?step=2">
      <div class="card-body">
        <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', $errors) ?></div><?php endif; ?>
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label required">Host Database</label>
            <input type="text" name="db_host" class="form-control" required value="<?= htmlspecialchars($dbCfgSession['host'] ?? 'localhost') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label required">Port</label>
            <input type="number" name="db_port" class="form-control" required value="<?= htmlspecialchars($dbCfgSession['port'] ?? '3306') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">Nama Database</label>
            <input type="text" name="db_name" class="form-control" required placeholder="Contoh: meetingapp_db" value="<?= htmlspecialchars($dbCfgSession['name'] ?? '') ?>">
            <small class="text-muted">Database akan dibuat otomatis jika belum ada.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Username Database</label>
            <input type="text" name="db_user" class="form-control" required value="<?= htmlspecialchars($dbCfgSession['user'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Password Database</label>
            <input type="password" name="db_pass" class="form-control">
          </div>
          <?php if ($isInstalled): ?>
          <div class="col-12">
            <label class="form-check form-check-danger">
              <input type="checkbox" name="reinstall" value="1" class="form-check-input" id="chk-reinstall">
              <span class="form-check-label text-danger fw-semibold">
                ⚠️ Reinstall — hapus semua tabel & data lama, import ulang schema dari awal
              </span>
            </label>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="install.php?step=1" class="btn btn-outline-secondary">&larr; Kembali</a>
        <button type="submit" class="btn btn-primary">Test Koneksi & Lanjut &rarr;</button>
      </div>
    </form>
  </div>

  <?php elseif ($step === 3): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfigurasi Aplikasi, Admin & Email</h3></div>
    <form method="POST" action="install.php?step=3">
      <div class="card-body">
        <?php if ($errors): ?><div class="alert alert-danger"><?= implode('<br>', $errors) ?></div><?php endif; ?>

        <div class="section-title">⚙️ Pengaturan Aplikasi</div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label required">Nama Aplikasi</label>
            <input type="text" name="app_name" class="form-control" required value="<?= htmlspecialchars($appCfgSession['app_name'] ?? 'Meeting Management App') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">URL Aplikasi <small class="text-muted">(tanpa slash di akhir)</small></label>
            <input type="url" name="app_url" class="form-control" required
                   placeholder="https://domain.com/wicara"
                   value="<?= htmlspecialchars($appCfgSession['app_url'] ?? 'http://'.$_SERVER['HTTP_HOST']) ?>">
            <small class="text-muted">Contoh: <code>https://domain.com/wicara</code> atau <code>https://domain.com</code></small>
          </div>
        </div>

        <div class="section-title">👤 Akun Admin</div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label required">Username Admin</label>
            <input type="text" name="admin_username" class="form-control" required
                   placeholder="Contoh: admin" pattern="[a-zA-Z0-9_]{3,50}"
                   value="<?= htmlspecialchars($adminCfgSession['username'] ?? 'admin') ?>">
            <small class="text-muted">3–50 karakter, huruf/angka/underscore. Digunakan untuk login.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Nama Lengkap</label>
            <input type="text" name="admin_name" class="form-control" required value="<?= htmlspecialchars($adminCfgSession['name'] ?? 'Administrator') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">Email Admin</label>
            <input type="email" name="admin_email" class="form-control" required placeholder="admin@domain.com"
                   value="<?= htmlspecialchars($adminCfgSession['email'] ?? '') ?>">
            <small class="text-muted">Digunakan untuk menerima link reset password.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Password</label>
            <input type="password" name="admin_password" class="form-control" required minlength="8" placeholder="Minimal 8 karakter">
          </div>
          <div class="col-md-6">
            <label class="form-label required">Konfirmasi Password</label>
            <input type="password" name="admin_password_confirm" class="form-control" required minlength="8">
          </div>
        </div>

        <div class="section-title">✉️ Konfigurasi Email <small class="text-muted fw-normal">(opsional)</small></div>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Driver Email</label>
            <select name="mail_driver" class="form-select" id="mail-driver">
              <option value="skip" <?= ($mailCfgSession['driver']??'skip')==='skip'?'selected':''?>>⏭️ Lewati (atur manual nanti)</option>
              <option value="mail" <?= ($mailCfgSession['driver']??'')==='mail'?'selected':''?>>📧 PHP mail()</option>
              <option value="smtp" <?= ($mailCfgSession['driver']??'')==='smtp'?'selected':''?>>🔐 SMTP</option>
            </select>
          </div>
          <div class="col-md-7" id="field-from-email" style="display:none">
            <label class="form-label">Email Pengirim</label>
            <input type="email" name="mail_from" class="form-control" placeholder="noreply@domain.com" value="<?= htmlspecialchars($mailCfgSession['from_email'] ?? '') ?>">
          </div>
          <div class="col-md-5" id="field-from-name" style="display:none">
            <label class="form-label">Nama Pengirim</label>
            <input type="text" name="mail_name" class="form-control" value="<?= htmlspecialchars($mailCfgSession['from_name'] ?? 'Meeting Management App') ?>">
          </div>
          <div class="col-12" id="smtp-fields" style="display:none">
            <div class="row g-2">
              <div class="col-md-6"><label class="form-label">SMTP Host</label>
                <input type="text" name="smtp_host" class="form-control" placeholder="smtp.gmail.com" value="<?= htmlspecialchars($mailCfgSession['smtp_host'] ?? '') ?>"></div>
              <div class="col-md-3"><label class="form-label">Port</label>
                <input type="number" name="smtp_port" class="form-control" value="<?= htmlspecialchars($mailCfgSession['smtp_port'] ?? '587') ?>"></div>
              <div class="col-md-3"><label class="form-label">Enkripsi</label>
                <select name="smtp_secure" class="form-select">
                  <option value="tls" <?= ($mailCfgSession['smtp_secure']??'tls')==='tls'?'selected':''?>>TLS</option>
                  <option value="ssl" <?= ($mailCfgSession['smtp_secure']??'')==='ssl'?'selected':''?>>SSL</option>
                </select></div>
              <div class="col-md-6"><label class="form-label">SMTP Username</label>
                <input type="text" name="smtp_user" class="form-control" value="<?= htmlspecialchars($mailCfgSession['smtp_user'] ?? '') ?>"></div>
              <div class="col-md-6"><label class="form-label">SMTP Password</label>
                <input type="password" name="smtp_pass" class="form-control"></div>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="install.php?step=2" class="btn btn-outline-secondary">&larr; Kembali</a>
        <button type="submit" class="btn btn-primary">Lanjut &rarr;</button>
      </div>
    </form>
  </div>

  <?php elseif ($step === 4): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfirmasi Instalasi</h3></div>
    <div class="card-body">
      <?php if ($errors): ?><div class="alert alert-danger mb-3"><?= implode('<br>', $errors) ?></div><?php endif; ?>
      <?php if ($isReinstall): ?>
      <div class="alert alert-danger">⚠️ <strong>Mode Reinstall aktif.</strong> Semua data lama akan dihapus permanen!</div>
      <?php endif; ?>

      <div class="section-title">🗄️ Database</div>
      <table class="table table-sm mb-3">
        <tr><th style="width:40%">Host</th><td><?= htmlspecialchars($dbCfgSession['host']??'-') ?>:<?= htmlspecialchars($dbCfgSession['port']??'-') ?></td></tr>
        <tr><th>Nama Database</th><td><?= htmlspecialchars($dbCfgSession['name']??'-') ?></td></tr>
        <tr><th>Username DB</th><td><?= htmlspecialchars($dbCfgSession['user']??'-') ?></td></tr>
      </table>

      <div class="section-title">⚙️ Aplikasi & Admin</div>
      <table class="table table-sm mb-3">
        <tr><th style="width:40%">Nama Aplikasi</th><td><?= htmlspecialchars($appCfgSession['app_name']??'-') ?></td></tr>
        <tr><th>URL Aplikasi</th><td><?= htmlspecialchars($appCfgSession['app_url']??'-') ?></td></tr>
        <tr><th>Username Admin</th><td><strong><?= htmlspecialchars($adminCfgSession['username']??'-') ?></strong></td></tr>
        <tr><th>Email Admin</th><td><?= htmlspecialchars($adminCfgSession['email']??'-') ?></td></tr>
      </table>

      <div class="section-title">📁 Yang Akan Diimport</div>
      <div class="d-flex justify-content-between small py-1 border-bottom">
        <span>Schema Utama — semua tabel & relasi</span>
        <?= file_exists('database/schema.sql')
          ? '<span class="badge bg-green-lt text-green">✓ Siap diimport</span>'
          : '<span class="badge bg-red-lt text-red">✗ File tidak ditemukan!</span>' ?>
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <a href="install.php?step=3" class="btn btn-outline-secondary">&larr; Kembali</a>
      <form method="POST" action="install.php?step=4" class="d-inline">
        <button type="submit" class="btn btn-primary">🚀 Mulai Instalasi</button>
      </form>
    </div>
  </div>

  <?php elseif ($step === 5): ?>
  <div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
      <div style="font-size:64px;margin-bottom:16px;">🎉</div>
      <h2 class="fw-bold mb-2" style="color:#22c55e;">Instalasi Berhasil!</h2>
      <p class="text-muted mb-4">Aplikasi Meeting Management App siap digunakan.</p>
      <div class="text-start mb-4">
        <?php foreach ($success as $msg): ?>
        <div class="alert alert-success py-2 mb-1"><?= $msg ?></div>
        <?php endforeach; ?>
      </div>
      <div class="alert alert-info text-start">
        <strong>🔑 Kredensial Login:</strong><br>
        Username: <code><?= htmlspecialchars($adminCfgSession['username'] ?? 'admin') ?></code><br>
        Password: <em>sesuai yang Anda isi di Step 3</em>
      </div>
      <div class="alert alert-danger text-start">
        <strong>⚠️ Penting!</strong> Hapus <code>install.php</code> setelah ini!
      </div>
      <div class="d-flex gap-2 justify-content-center mt-4">
        <a href="<?= htmlspecialchars($appCfgSession['app_url'] ?? '/') ?>" class="btn btn-primary btn-lg">Buka Aplikasi &rarr;</a>
        <button onclick="deleteInstaller()" class="btn btn-outline-danger btn-lg">🗑️ Hapus install.php</button>
      </div>
    </div>
  </div>
  <script>
  function deleteInstaller() {
    if (!confirm('Hapus file install.php sekarang?')) return;
    fetch('install.php?action=self_delete').then(r=>r.json()).then(d=>{
      if(d.success){alert('install.php berhasil dihapus!');window.location.href='<?= htmlspecialchars($appCfgSession['app_url']??'/') ?>';}
      else{alert('Gagal hapus otomatis. Hapus manual via FTP/File Manager.');}
    });
  }
  </script>
  <?php endif; ?>

</div>
<script>
function toggleMailFields(){
  const d = document.getElementById('mail-driver')?.value;
  const s = document.getElementById('smtp-fields');
  const fe = document.getElementById('field-from-email');
  const fn = document.getElementById('field-from-name');
  if (!s) return;
  const showFrom = d !== 'skip';
  fe.style.display = showFrom ? '' : 'none';
  fn.style.display = showFrom ? '' : 'none';
  s.style.display  = d === 'smtp' ? '' : 'none';
}
document.getElementById('mail-driver')?.addEventListener('change', toggleMailFields);
toggleMailFields();
</script>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
