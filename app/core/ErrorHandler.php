<?php
declare(strict_types=1);

class ErrorHandler
{
    private static bool $debug = false;
    private static string $logFile = '';

    public static function register(): void
    {
        self::$debug   = defined('APP_DEBUG') && APP_DEBUG === true;
        self::$logFile = ROOT_PATH . '/logs/php_errors.log';

        // Pastikan folder logs ada
        $logDir = ROOT_PATH . '/logs';
        if (!is_dir($logDir)) @mkdir($logDir, 0755, true);

        ini_set('error_log', self::$logFile);
        error_reporting(E_ALL);

        if (self::$debug) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('display_startup_errors', '0');
        }

        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    // ── Uncaught Exception ────────────────────────────────────────────
    public static function handleException(\Throwable $e): void
    {
        $msg = sprintf(
            "[%s] EXCEPTION %s: %s in %s:%d\nStack trace:\n%s",
            date('Y-m-d H:i:s'),
            get_class($e),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        self::writeLog($msg);

        if (headers_sent()) {
            if (self::$debug) {
                echo '<pre style="background:#1e1e1e;color:#f44;padding:1rem;margin:1rem;border-radius:6px;font-size:13px;">';
                echo htmlspecialchars($msg);
                echo '</pre>';
            }
            return;
        }

        http_response_code(500);

        if (self::$debug) {
            self::renderDebugPage(get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
        } else {
            self::renderProductionError();
        }
    }

    // ── PHP Error (E_WARNING, E_NOTICE, dll) ─────────────────────────
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        if (!($errno & error_reporting())) return false;

        $type = self::errorTypeName($errno);
        $msg  = sprintf(
            "[%s] %s: %s in %s:%d",
            date('Y-m-d H:i:s'),
            $type,
            $errstr,
            $errfile,
            $errline
        );

        self::writeLog($msg);

        // Fatal-level error: lempar sebagai exception agar tertangkap
        if (in_array($errno, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR], true)) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }

        return true; // jangan lanjut ke default PHP error handler
    }

    // ── Fatal error via shutdown (parse error, out-of-memory, dll) ───
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if (!$error) return;

        $fatal = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_CORE_WARNING, E_COMPILE_WARNING];
        if (!in_array($error['type'], $fatal, true)) return;

        $type = self::errorTypeName($error['type']);
        $msg  = sprintf(
            "[%s] SHUTDOWN %s: %s in %s:%d",
            date('Y-m-d H:i:s'),
            $type,
            $error['message'],
            $error['file'],
            $error['line']
        );

        self::writeLog($msg);

        if (headers_sent()) return;

        http_response_code(500);

        if (self::$debug) {
            self::renderDebugPage(
                $type,
                $error['message'],
                $error['file'],
                $error['line'],
                '(no stack trace untuk fatal/shutdown error)'
            );
        } else {
            self::renderProductionError();
        }
    }

    // ── Tulis ke log file ─────────────────────────────────────────────
    private static function writeLog(string $msg): void
    {
        if (self::$logFile) {
            @file_put_contents(self::$logFile, $msg . "\n\n", FILE_APPEND | LOCK_EX);
        }
        error_log($msg);
    }

    // ── Render halaman debug (APP_DEBUG = true) ───────────────────────
    private static function renderDebugPage(
        string $type,
        string $message,
        string $file,
        int    $line,
        string $trace
    ): void {
        // Coba baca baris sekitar error
        $snippet = self::getCodeSnippet($file, $line, 7);

        // Relative path supaya lebih pendek
        $relFile = str_replace(ROOT_PATH, '', $file);

        ?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>500 — <?= htmlspecialchars($type) ?></title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;color:#e2e8f0;min-height:100vh;padding:2rem}
.container{max-width:960px;margin:0 auto}
.badge{display:inline-block;background:#ef4444;color:#fff;font-size:12px;font-weight:700;
       padding:2px 10px;border-radius:999px;letter-spacing:.5px;margin-bottom:1rem}
h1{font-size:1.6rem;font-weight:700;color:#f8fafc;margin-bottom:.5rem;word-break:break-word}
.file{font-size:.85rem;color:#94a3b8;margin-bottom:1.5rem;font-family:monospace}
.file span{color:#f59e0b}
.section{background:#1e293b;border:1px solid #334155;border-radius:8px;margin-bottom:1.5rem;overflow:hidden}
.section-header{padding:.6rem 1rem;background:#0f172a;border-bottom:1px solid #334155;
                font-size:.75rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#64748b}
.section-body{padding:1rem}
.snippet table{width:100%;border-collapse:collapse;font-family:monospace;font-size:13px;line-height:1.6}
.snippet td{padding:1px 8px;white-space:pre}
.snippet td.ln{color:#475569;text-align:right;user-select:none;padding-right:16px;min-width:40px}
.snippet tr.hl{background:#7c3aed22}
.snippet tr.hl td{color:#f8fafc}
.snippet tr.hl td.ln{color:#a78bfa}
pre.trace{font-size:12px;line-height:1.7;color:#94a3b8;white-space:pre-wrap;word-break:break-word}
pre.trace .frame{color:#e2e8f0}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;font-size:13px}
.info-row{display:flex;gap:.5rem}
.info-key{color:#64748b;min-width:130px;flex-shrink:0}
.info-val{color:#e2e8f0;font-family:monospace;word-break:break-all}
.tip{background:#1e3a5f;border:1px solid #1e40af;border-radius:6px;padding:.75rem 1rem;
     font-size:13px;color:#93c5fd;margin-bottom:1.5rem}
</style>
</head>
<body>
<div class="container">
  <div class="badge">500 ERROR</div>
  <h1><?= htmlspecialchars($message) ?></h1>
  <p class="file">
    <?= htmlspecialchars($relFile) ?> &nbsp;•&nbsp; line <span><?= $line ?></span>
    &nbsp;•&nbsp; <?= htmlspecialchars($type) ?>
  </p>

  <div class="tip">
    💡 Halaman ini hanya tampil karena <code>APP_DEBUG = true</code>.
       Matikan setelah bug ditemukan.
  </div>

  <?php if ($snippet): ?>
  <div class="section">
    <div class="section-header">📄 Source Code</div>
    <div class="section-body snippet">
      <table>
        <?php foreach ($snippet as $n => $src): ?>
        <tr class="<?= $n === $line ? 'hl' : '' ?>">
          <td class="ln"><?= $n ?></td>
          <td><?= htmlspecialchars($src) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php endif; ?>

  <div class="section">
    <div class="section-header">🔍 Stack Trace</div>
    <div class="section-body">
      <pre class="trace"><?= htmlspecialchars(str_replace(ROOT_PATH, '', $trace)) ?></pre>
    </div>
  </div>

  <div class="section">
    <div class="section-header">🌐 Request Info</div>
    <div class="section-body">
      <div class="info-grid">
        <div class="info-row"><span class="info-key">Method</span><span class="info-val"><?= htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? '-') ?></span></div>
        <div class="info-row"><span class="info-key">URI</span><span class="info-val"><?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? '-') ?></span></div>
        <div class="info-row"><span class="info-key">PHP Version</span><span class="info-val"><?= PHP_VERSION ?></span></div>
        <div class="info-row"><span class="info-key">Server</span><span class="info-val"><?= htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? '-') ?></span></div>
        <div class="info-row"><span class="info-key">User Agent</span><span class="info-val"><?= htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? '-', 0, 80)) ?></span></div>
        <div class="info-row"><span class="info-key">Log File</span><span class="info-val"><?= htmlspecialchars(str_replace(ROOT_PATH, '', self::$logFile)) ?></span></div>
      </div>
    </div>
  </div>

  <?php if (!empty($_GET)): ?>
  <div class="section">
    <div class="section-header">🔗 GET Params</div>
    <div class="section-body">
      <pre class="trace"><?= htmlspecialchars(print_r($_GET, true)) ?></pre>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION)): ?>
  <div class="section">
    <div class="section-header">🔐 Session</div>
    <div class="section-body">
      <pre class="trace"><?php
        $sess = $_SESSION;
        // Sensor password
        if (isset($sess['user']['password'])) $sess['user']['password'] = '***';
        echo htmlspecialchars(print_r($sess, true));
      ?></pre>
    </div>
  </div>
  <?php endif; ?>
</div>
</body>
</html>
<?php
    }

    // ── Render halaman production error (APP_DEBUG = false) ──────────
    private static function renderProductionError(): void
    {
        $errPage = APP_PATH . '/views/errors/500.php';
        if (file_exists($errPage)) {
            include $errPage;
        } else {
            echo '<h1>500 — Internal Server Error</h1>';
            echo '<p>Terjadi kesalahan pada server. Silakan coba lagi.</p>';
        }
    }

    // ── Ambil potongan kode sekitar baris error ───────────────────────
    private static function getCodeSnippet(string $file, int $line, int $context = 7): array
    {
        if (!file_exists($file) || !is_readable($file)) return [];

        $lines = file($file);
        if (!$lines) return [];

        $start  = max(0, $line - $context - 1);
        $end    = min(count($lines) - 1, $line + $context - 1);
        $result = [];

        for ($i = $start; $i <= $end; $i++) {
            $result[$i + 1] = rtrim($lines[$i]);
        }

        return $result;
    }

    // ── Nama tipe error ───────────────────────────────────────────────
    private static function errorTypeName(int $type): string
    {
        $map = [
            E_ERROR             => 'E_ERROR',
            E_WARNING           => 'E_WARNING',
            E_PARSE             => 'E_PARSE',
            E_NOTICE            => 'E_NOTICE',
            E_CORE_ERROR        => 'E_CORE_ERROR',
            E_CORE_WARNING      => 'E_CORE_WARNING',
            E_COMPILE_ERROR     => 'E_COMPILE_ERROR',
            E_COMPILE_WARNING   => 'E_COMPILE_WARNING',
            E_USER_ERROR        => 'E_USER_ERROR',
            E_USER_WARNING      => 'E_USER_WARNING',
            E_USER_NOTICE       => 'E_USER_NOTICE',
            E_STRICT            => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR',
            E_DEPRECATED        => 'E_DEPRECATED',
            E_USER_DEPRECATED   => 'E_USER_DEPRECATED',
        ];
        return $map[$type] ?? "E_UNKNOWN({$type})";
    }
}
