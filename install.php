<?php
/**
 * Meeting Management App — Web Installer
 * Letakkan file ini di root folder, buka via browser sekali saja.
 * Hapus file ini setelah instalasi selesai!
 */

define('INSTALLER_VERSION', '1.0.0');
define('MIN_PHP', '8.1.0');

session_start();

// ─── Helpers ────────────────────────────────────────────────────────────────

function checkPhpExtensions(): array {
    $required = ['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'json', 'session'];
    $missing  = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) $missing[] = $ext;
    }
    return $missing;
}

function checkWritable(): array {
    $paths   = ['app/config', 'public/assets'];
    $results = [];
    foreach ($paths as $p) {
        $results[$p] = is_writable($p);
    }
    return $results;
}

function testDbConnection(array $cfg): bool|string {
    try {
        $dsn = "mysql:host={$cfg['host']};port={$cfg['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
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

function importSchema(PDO $pdo): bool|string {
    $schemaFile = __DIR__ . '/database/schema.sql';
    if (!file_exists($schemaFile)) return 'File schema.sql tidak ditemukan!';
    $sql = file_get_contents($schemaFile);
    // Hapus komentar dan split per statement
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

function writeDbConfig(array $cfg): bool {
    $content = <<<PHP
<?php
// Konfigurasi Database — di-generate oleh Installer
// Jangan commit file ini ke repository!
return [
    'host'     => '{$cfg['host']}',
    'port'     => '{$cfg['port']}',
    'dbname'   => '{$cfg['dbname']}',
    'username' => '{$cfg['username']}',
    'password' => '{$cfg['password']}',
    'charset'  => 'utf8mb4',
];
PHP;
    return (bool) file_put_contents(__DIR__ . '/app/config/database.php', $content);
}

function writeAppConfig(array $cfg): bool {
    $secret = bin2hex(random_bytes(32));
    $content = <<<PHP
<?php
// Konfigurasi Aplikasi — di-generate oleh Installer
define('APP_NAME',    '{$cfg['app_name']}');
define('APP_URL',     '{$cfg['app_url']}');
define('APP_ENV',     'production');
define('APP_DEBUG',   false);
define('APP_SECRET',  '{$secret}');
define('APP_LOCALE',  'id');
define('APP_TIMEZONE','Asia/Jakarta');
PHP;
    return (bool) file_put_contents(__DIR__ . '/app/config/app.php', $content);
}

function updateAdminUser(PDO $pdo, array $admin): void {
    $hash = password_hash($admin['password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("UPDATE users SET name=?, email=?, password=? WHERE role_id=(
        SELECT id FROM roles WHERE name='admin' LIMIT 1
    ) LIMIT 1")->execute([$admin['name'], $admin['email'], $hash]);
}

function isInstalled(): bool {
    return file_exists(__DIR__ . '/app/config/database.php')
        && file_exists(__DIR__ . '/app/config/app.php');
}

// ─── Step Logic ─────────────────────────────────────────────────────────────

$step    = (int) ($_GET['step'] ?? 1);
$errors  = [];
$success = [];

// Step 2 — Simpan config DB ke session
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbCfg = [
        'host'     => trim($_POST['db_host']     ?? 'localhost'),
        'port'     => trim($_POST['db_port']     ?? '3306'),
        'dbname'   => trim($_POST['db_name']     ?? ''),
        'username' => trim($_POST['db_user']     ?? ''),
        'password' => $_POST['db_pass']          ?? '',
    ];
    $test = testDbConnection($dbCfg);
    if ($test === true) {
        $_SESSION['installer_db']  = $dbCfg;
        header('Location: install.php?step=3');
        exit;
    } else {
        $errors[] = 'Koneksi gagal: ' . $test;
    }
}

// Step 3 — Simpan config App ke session
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['installer_app'] = [
        'app_name' => trim($_POST['app_name'] ?? 'Meeting Management App'),
        'app_url'  => rtrim(trim($_POST['app_url'] ?? ''), '/'),
    ];
    $_SESSION['installer_admin'] = [
        'name'     => trim($_POST['admin_name']  ?? 'Administrator'),
        'email'    => trim($_POST['admin_email'] ?? ''),
        'password' => $_POST['admin_password']   ?? '',
    ];
    $errs = [];
    if (empty($_SESSION['installer_admin']['email']))    $errs[] = 'Email admin wajib diisi.';
    if (strlen($_SESSION['installer_admin']['password']) < 8) $errs[] = 'Password minimal 8 karakter.';
    if ($_POST['admin_password'] !== $_POST['admin_password_confirm']) $errs[] = 'Konfirmasi password tidak cocok.';
    if ($errs) {
        $errors = $errs;
    } else {
        header('Location: install.php?step=4');
        exit;
    }
}

// Step 4 — Eksekusi instalasi
if ($step === 4 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbCfg    = $_SESSION['installer_db']    ?? [];
    $appCfg   = $_SESSION['installer_app']   ?? [];
    $adminCfg = $_SESSION['installer_admin'] ?? [];

    try {
        // 1. Koneksi tanpa DB name
        $dsn = "mysql:host={$dbCfg['host']};port={$dbCfg['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbCfg['username'], $dbCfg['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        // 2. Buat database
        createDatabase($pdo, $dbCfg['dbname']);
        $success[] = 'Database <strong>' . htmlspecialchars($dbCfg['dbname']) . '</strong> siap.';

        // 3. Import schema
        $import = importSchema($pdo);
        if ($import !== true) throw new Exception('Import schema gagal: ' . $import);
        $success[] = 'Tabel database berhasil diimport.';

        // 4. Update admin
        updateAdminUser($pdo, $adminCfg);
        $success[] = 'Akun admin berhasil dikonfigurasi.';

        // 5. Tulis config files
        if (!writeDbConfig($dbCfg))  throw new Exception('Gagal menulis app/config/database.php — cek permission folder.');
        $success[] = 'File <code>app/config/database.php</code> berhasil dibuat.';

        if (!writeAppConfig($appCfg)) throw new Exception('Gagal menulis app/config/app.php — cek permission folder.');
        $success[] = 'File <code>app/config/app.php</code> berhasil dibuat.';

        // 6. Bersihkan session
        unset($_SESSION['installer_db'], $_SESSION['installer_app'], $_SESSION['installer_admin']);

        $step = 5; // Selesai!

    } catch (Exception $e) {
        $errors[] = $e->getMessage();
        $step = 4;
    }
}

// ─── Requirement Check ──────────────────────────────────────────────────────
$phpOk        = version_compare(PHP_VERSION, MIN_PHP, '>=');
$missingExts  = checkPhpExtensions();
$writablePaths = checkWritable();
$allWritable  = !in_array(false, $writablePaths, true);
$canProceed   = $phpOk && empty($missingExts) && $allWritable;

$dbCfgSession    = $_SESSION['installer_db']    ?? [];
$appCfgSession   = $_SESSION['installer_app']   ?? [];
$adminCfgSession = $_SESSION['installer_admin'] ?? [];
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installer &mdash; Meeting Management App</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
  <style>
    body { background: #f4f6fb; }
    .installer-wrap { max-width: 680px; margin: 48px auto; padding: 0 16px; }
    .step-header { display:flex; gap:8px; margin-bottom:32px; }
    .step-item {
      flex:1; text-align:center; padding:10px 6px;
      border-radius:8px; font-size:13px; font-weight:600;
      background:#fff; border:2px solid #e5e7eb; color:#9ca3af;
    }
    .step-item.active  { border-color:#f76707; color:#f76707; background:#fff7ed; }
    .step-item.done    { border-color:#22c55e; color:#22c55e; background:#f0fdf4; }
    .step-num { display:block; font-size:20px; font-weight:800; }
    .brand-icon { color:#f76707; }
    .btn-primary { background:#f76707!important; border-color:#f76707!important; }
    .btn-primary:hover { background:#e8600a!important; }
    .form-label.required:after { content:' *'; color:#ef4444; }
  </style>
</head>
<body>
<div class="installer-wrap">

  <!-- Header -->
  <div class="text-center mb-4">
    <svg xmlns="http://www.w3.org/2000/svg" width="56" height="56" viewBox="0 0 24 24"
         fill="none" stroke="#f76707" stroke-width="2" class="brand-icon">
      <rect x="3" y="4" width="18" height="18" rx="2"/>
      <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
      <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    <h1 class="mt-2 mb-0 fw-bold" style="font-size:26px;color:#f76707;">Meeting Management App</h1>
    <p class="text-muted">Web Installer v<?= INSTALLER_VERSION ?></p>
  </div>

  <!-- Step Indicator -->
  <div class="step-header">
    <?php
    $steps = ['Cek Sistem','Database','Konfigurasi','Instalasi','Selesai'];
    foreach ($steps as $i => $label):
      $n = $i + 1;
      $cls = $n < $step ? 'done' : ($n === $step ? 'active' : '');
    ?>
    <div class="step-item <?= $cls ?>">
      <span class="step-num"><?= $n < $step ? '✓' : $n ?></span>
      <?= $label ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ── STEP 1: Cek Sistem ───────────────────────────────────────────── -->
  <?php if ($step === 1): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Pemeriksaan Sistem</h3></div>
    <div class="card-body">

      <table class="table table-sm mb-0">
        <tbody>
          <tr>
            <td>Versi PHP</td>
            <td><code><?= PHP_VERSION ?></code></td>
            <td><?= $phpOk
              ? '<span class="badge bg-green">OK</span>'
              : '<span class="badge bg-red">Butuh '.MIN_PHP.'+</span>' ?></td>
          </tr>
          <?php foreach (['pdo','pdo_mysql','mbstring','openssl','json'] as $ext): ?>
          <tr>
            <td>Ekstensi <code><?= $ext ?></code></td>
            <td></td>
            <td><?= extension_loaded($ext)
              ? '<span class="badge bg-green">Aktif</span>'
              : '<span class="badge bg-red">Tidak Ada</span>' ?></td>
          </tr>
          <?php endforeach; ?>
          <?php foreach ($writablePaths as $path => $ok): ?>
          <tr>
            <td>Folder <code><?= $path ?>/</code> writable</td>
            <td></td>
            <td><?= $ok
              ? '<span class="badge bg-green">OK</span>'
              : '<span class="badge bg-red">Tidak Writable</span>' ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <?php if (!$allWritable): ?>
      <div class="alert alert-warning mt-3">
        Jalankan perintah berikut via SSH atau File Manager:
        <pre class="mb-0 mt-1">chmod 755 app/config public/assets</pre>
      </div>
      <?php endif; ?>
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

  <!-- ── STEP 2: Konfigurasi Database ────────────────────────────────── -->
  <?php elseif ($step === 2): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfigurasi Database</h3></div>
    <form method="POST" action="install.php?step=2">
      <div class="card-body">
        <?php if ($errors): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label required">Host Database</label>
            <input type="text" name="db_host" class="form-control" required
                   value="<?= htmlspecialchars($dbCfgSession['host'] ?? 'localhost') ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label required">Port</label>
            <input type="number" name="db_port" class="form-control" required
                   value="<?= htmlspecialchars($dbCfgSession['port'] ?? '3306') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">Nama Database</label>
            <input type="text" name="db_name" class="form-control" required
                   placeholder="Contoh: meetingapp_db"
                   value="<?= htmlspecialchars($dbCfgSession['dbname'] ?? '') ?>">
            <small class="text-muted">Database akan dibuat otomatis jika belum ada.</small>
          </div>
          <div class="col-md-6">
            <label class="form-label required">Username Database</label>
            <input type="text" name="db_user" class="form-control" required
                   value="<?= htmlspecialchars($dbCfgSession['username'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Password Database</label>
            <input type="password" name="db_pass" class="form-control"
                   value="<?= htmlspecialchars($dbCfgSession['password'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="install.php?step=1" class="btn btn-outline-secondary">&larr; Kembali</a>
        <button type="submit" class="btn btn-primary">Test & Lanjut &rarr;</button>
      </div>
    </form>
  </div>

  <!-- ── STEP 3: Konfigurasi App + Admin ──────────────────────────────── -->
  <?php elseif ($step === 3): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfigurasi Aplikasi & Akun Admin</h3></div>
    <form method="POST" action="install.php?step=3">
      <div class="card-body">
        <?php if ($errors): ?>
        <div class="alert alert-danger"><?= implode('<br>', $errors) ?></div>
        <?php endif; ?>

        <h4 class="mb-3" style="font-size:15px;color:#f76707;">⚙️ Pengaturan Aplikasi</h4>
        <div class="row g-3 mb-4">
          <div class="col-12">
            <label class="form-label required">Nama Aplikasi</label>
            <input type="text" name="app_name" class="form-control" required
                   value="<?= htmlspecialchars($appCfgSession['app_name'] ?? 'Meeting Management App') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">URL Aplikasi</label>
            <input type="url" name="app_url" class="form-control" required
                   placeholder="https://domain.com"
                   value="<?= htmlspecialchars($appCfgSession['app_url'] ?? 'http://'.$_SERVER['HTTP_HOST']) ?>">
          </div>
        </div>

        <h4 class="mb-3" style="font-size:15px;color:#f76707;">👤 Akun Admin</h4>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label required">Nama Admin</label>
            <input type="text" name="admin_name" class="form-control" required
                   value="<?= htmlspecialchars($adminCfgSession['name'] ?? 'Administrator') ?>">
          </div>
          <div class="col-12">
            <label class="form-label required">Email Admin</label>
            <input type="email" name="admin_email" class="form-control" required
                   placeholder="admin@domain.com"
                   value="<?= htmlspecialchars($adminCfgSession['email'] ?? '') ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label required">Password Admin</label>
            <input type="password" name="admin_password" class="form-control"
                   required minlength="8" placeholder="Minimal 8 karakter">
          </div>
          <div class="col-md-6">
            <label class="form-label required">Konfirmasi Password</label>
            <input type="password" name="admin_password_confirm" class="form-control"
                   required minlength="8">
          </div>
        </div>
      </div>
      <div class="card-footer d-flex justify-content-between">
        <a href="install.php?step=2" class="btn btn-outline-secondary">&larr; Kembali</a>
        <button type="submit" class="btn btn-primary">Lanjut &rarr;</button>
      </div>
    </form>
  </div>

  <!-- ── STEP 4: Konfirmasi & Eksekusi ────────────────────────────────── -->
  <?php elseif ($step === 4): ?>
  <div class="card shadow-sm">
    <div class="card-header"><h3 class="card-title">Konfirmasi Instalasi</h3></div>
    <div class="card-body">

      <?php if ($errors): ?>
      <div class="alert alert-danger mb-3"><?= implode('<br>', $errors) ?></div>
      <?php endif; ?>

      <p class="text-muted">Periksa kembali konfigurasi sebelum instalasi dijalankan:</p>

      <table class="table table-sm">
        <tbody>
          <tr><th style="width:40%">Host DB</th><td><?= htmlspecialchars($dbCfgSession['host'] ?? '-') ?></td></tr>
          <tr><th>Nama Database</th><td><?= htmlspecialchars($dbCfgSession['dbname'] ?? '-') ?></td></tr>
          <tr><th>Username DB</th><td><?= htmlspecialchars($dbCfgSession['username'] ?? '-') ?></td></tr>
          <tr><th>Nama Aplikasi</th><td><?= htmlspecialchars($appCfgSession['app_name'] ?? '-') ?></td></tr>
          <tr><th>URL Aplikasi</th><td><?= htmlspecialchars($appCfgSession['app_url'] ?? '-') ?></td></tr>
          <tr><th>Email Admin</th><td><?= htmlspecialchars($adminCfgSession['email'] ?? '-') ?></td></tr>
        </tbody>
      </table>

      <div class="alert alert-warning">
        <strong>Perhatian:</strong> Proses ini akan membuat database, mengimpor tabel, dan membuat file konfigurasi.
        Jika database sudah ada, semua data akan di-reset!
      </div>
    </div>
    <div class="card-footer d-flex justify-content-between">
      <a href="install.php?step=3" class="btn btn-outline-secondary">&larr; Kembali</a>
      <form method="POST" action="install.php?step=4" class="d-inline">
        <button type="submit" class="btn btn-primary">🚀 Mulai Instalasi</button>
      </form>
    </div>
  </div>

  <!-- ── STEP 5: Selesai ──────────────────────────────────────────────── -->
  <?php elseif ($step === 5): ?>
  <div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
      <div style="font-size:64px;margin-bottom:16px;">🎉</div>
      <h2 class="fw-bold mb-2" style="color:#22c55e;">Instalasi Berhasil!</h2>
      <p class="text-muted mb-4">Aplikasi Meeting Management sudah siap digunakan.</p>

      <?php foreach ($success as $msg): ?>
      <div class="alert alert-success text-start py-2">✅ <?= $msg ?></div>
      <?php endforeach; ?>

      <div class="alert alert-danger mt-3 text-start">
        <strong>⚠️ Penting — Lakukan sekarang!</strong><br>
        Hapus file <code>install.php</code> dari server Anda sebelum membuka aplikasi.
        File ini bisa digunakan ulang oleh siapapun untuk me-reset instalasi!
      </div>

      <div class="d-flex gap-2 justify-content-center mt-4">
        <a href="<?= htmlspecialchars($appCfgSession['app_url'] ?? '/') ?>"
           class="btn btn-primary btn-lg">
          Buka Aplikasi &rarr;
        </a>
        <button onclick="deleteInstaller()" class="btn btn-outline-danger btn-lg">
          🗑️ Hapus install.php
        </button>
      </div>
    </div>
  </div>

  <script>
  function deleteInstaller() {
    if (!confirm('Hapus file install.php sekarang?')) return;
    fetch('install.php?action=self_delete')
      .then(r => r.json())
      .then(d => {
        if (d.success) {
          alert('install.php berhasil dihapus! ✅');
          window.location.href = '<?= htmlspecialchars($appCfgSession['app_url'] ?? '/') ?>';
        } else {
          alert('Gagal hapus otomatis. Hapus manual via FTP/File Manager.');
        }
      });
  }
  </script>
  <?php endif; ?>

</div>

<?php
// Self-delete endpoint
if (($_GET['action'] ?? '') === 'self_delete') {
    header('Content-Type: application/json');
    $deleted = @unlink(__FILE__);
    echo json_encode(['success' => $deleted]);
    exit;
}
?>

<script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
</body>
</html>
