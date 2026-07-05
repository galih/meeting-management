<?php
declare(strict_types=1);

class DokumenPublicLinkController
{
    private static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function index(int $fileId): void
    {
        Auth::requireLogin();
        $file = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            self::json(['success'=>false,'message'=>'Hanya pemilik/admin yang dapat mengelola public link.'], 403);
        }
        $links = DokumenPublicLinkModel::forFile($fileId);
        foreach ($links as &$l) {
            $l['is_valid']   = DokumenPublicLinkModel::isValid($l);
            $l['has_password'] = !empty($l['password_hash']);
            $l['url']        = BASE_URL . '/d/' . $l['token'];
            unset($l['password_hash']);
        }
        unset($l);
        self::json(['success'=>true,'links'=>$links]);
    }

    public static function store(int $fileId): void
    {
        Auth::requireLogin();
        $file = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            self::json(['success'=>false,'message'=>'Hanya pemilik/admin.'], 403);
        }

        $permission   = in_array($_POST['permission'] ?? '', ['view','download']) ? $_POST['permission'] : 'view';
        $password     = trim($_POST['password'] ?? '') ?: null;
        $expiresAt    = trim($_POST['expires_at'] ?? '') ?: null;
        $maxDownloads = ($_POST['max_downloads'] ?? '') !== '' ? (int)$_POST['max_downloads'] : null;

        if ($expiresAt && !strtotime($expiresAt)) {
            self::json(['success'=>false,'message'=>'Format tanggal kadaluarsa tidak valid.']);
        }

        $link = DokumenPublicLinkModel::create($fileId, $permission, $password, $expiresAt, $maxDownloads, $userId);
        ActivityLog::record('dokumen.public_link.create', 'Public link dibuat untuk file '.$fileId, 'dokumen', $fileId);
        $link['is_valid']    = true;
        $link['has_password']= !empty($password);
        $link['url']         = BASE_URL . '/d/' . $link['token'];
        unset($link['password_hash']);
        self::json(['success'=>true,'message'=>'Link publik berhasil dibuat.','link'=>$link]);
    }

    public static function destroy(int $fileId, int $linkId): void
    {
        Auth::requireLogin();
        $file = DokumenModel::getFileById($fileId);
        if (!$file) self::json(['success'=>false,'message'=>'File tidak ditemukan.']);
        $userId  = Auth::id();
        $isAdmin = Auth::hasRole('admin');
        if (!$isAdmin && (int)$file['uploaded_by'] !== $userId) {
            self::json(['success'=>false,'message'=>'Tidak diizinkan.'], 403);
        }
        DokumenPublicLinkModel::delete($linkId);
        ActivityLog::record('dokumen.public_link.delete', 'Public link dihapus id '.$linkId, 'dokumen', $fileId);
        $links = DokumenPublicLinkModel::forFile($fileId);
        foreach ($links as &$l) {
            $l['is_valid']    = DokumenPublicLinkModel::isValid($l);
            $l['has_password']= !empty($l['password_hash']);
            $l['url']         = BASE_URL . '/d/' . $l['token'];
            unset($l['password_hash']);
        }
        unset($l);
        self::json(['success'=>true,'message'=>'Link dihapus.','links'=>$links]);
    }

    public static function publicPage(string $token): void
    {
        $link = DokumenPublicLinkModel::getByToken($token);
        if (!$link || !DokumenPublicLinkModel::isValid($link)) {
            http_response_code(404);
            self::renderError('Link tidak ditemukan atau sudah kadaluarsa.');
            return;
        }
        $file = DokumenModel::getFileById((int)$link['file_id']);
        if (!$file) {
            http_response_code(404);
            self::renderError('File tidak ditemukan.');
            return;
        }

        $needPassword   = !empty($link['password_hash']);
        $passVerified   = false;
        $passError      = '';
        if ($needPassword) {
            $session_key = 'pub_link_ok_' . $token;
            if (!empty($_SESSION[$session_key])) {
                $passVerified = true;
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $input = $_POST['password'] ?? '';
                if (DokumenPublicLinkModel::checkPassword($link, $input)) {
                    $_SESSION[$session_key] = true;
                    $passVerified = true;
                } else {
                    $passError = 'Password salah, coba lagi.';
                }
            }
        } else {
            $passVerified = true;
        }

        if (!$passVerified) {
            self::renderPasswordForm($file, $link, $passError);
            return;
        }

        self::renderPublicView($file, $link);
    }

    public static function publicDownload(string $token): void
    {
        $link = DokumenPublicLinkModel::getByToken($token);
        if (!$link || !DokumenPublicLinkModel::isValid($link)) {
            http_response_code(404); echo 'Link tidak valid atau kadaluarsa.'; exit;
        }
        if ($link['permission'] !== 'download') {
            http_response_code(403); echo 'Link ini hanya untuk melihat, bukan download.'; exit;
        }
        if (!empty($link['password_hash'])) {
            $session_key = 'pub_link_ok_' . $token;
            if (empty($_SESSION[$session_key])) {
                http_response_code(403); echo 'Masukkan password terlebih dahulu.'; exit;
            }
        }
        $file = DokumenModel::getFileById((int)$link['file_id']);
        if (!$file) { http_response_code(404); echo 'File tidak ditemukan.'; exit; }
        $path = ROOT_PATH . $file['file_path'];
        if (!file_exists($path)) { http_response_code(404); echo 'File fisik tidak ada.'; exit; }
        DokumenPublicLinkModel::incrementDownload((int)$link['id']);
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode(self::safeFileName($file['original_name'])));
        header('Content-Length: ' . filesize($path));
        header('X-Content-Type-Options: nosniff');
        readfile($path);
        exit;
    }

    private static function renderPublicView(array $file, array $link): void
    {
        $title      = htmlspecialchars(self::safeFileName($file['original_name']));
        $mimeLabel  = DokumenModel::mimeLabel($file['mime_type']);
        $mimeColor  = DokumenModel::mimeColor($file['mime_type']);
        $sizeLabel  = DokumenModel::formatSize((int)$file['file_size']);
        $canDownload= $link['permission'] === 'download';
        $dlUrl      = BASE_URL . '/d/' . $link['token'] . '/download';
        $previewUrl = BASE_URL . '/api/dokumen/' . $file['id'] . '/preview-public?token=' . $link['token'];
        $isImage    = str_starts_with($file['mime_type'], 'image/');
        $isVideo    = str_starts_with($file['mime_type'], 'video/');
        $isAudio    = str_starts_with($file['mime_type'], 'audio/');
        $isPdf      = $file['mime_type'] === 'application/pdf';
        $isText     = in_array($file['mime_type'], ['text/plain','text/csv']);
        $previewable= $isImage || $isVideo || $isAudio || $isPdf || $isText;
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $title ?> &mdash; Dokumen Shared</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#1a1614;min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:1.5rem}
.pub-card{background:#fff;border-radius:16px;width:100%;max-width:760px;overflow:hidden;box-shadow:0 32px 80px rgba(0,0,0,.4)}
.pub-header{background:#7B1C1C;padding:1.1rem 1.5rem;display:flex;align-items:center;gap:1rem}
.pub-icon{width:44px;height:44px;border-radius:10px;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;color:#fff;flex-shrink:0}
.pub-title{flex:1;min-width:0}
.pub-filename{font-size:15px;font-weight:800;color:#fff;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pub-meta{font-size:12px;color:rgba(255,255,255,.7);margin-top:.2rem}
.pub-dl-btn{height:38px;padding:0 1.2rem;border-radius:9px;background:#fff;color:#7B1C1C;font-weight:800;font-size:13px;border:none;cursor:pointer;text-decoration:none;display:flex;align-items:center;gap:.4rem;flex-shrink:0;transition:opacity 160ms}
.pub-dl-btn:hover{opacity:.88}
.pub-preview{width:100%;background:#f5f5f5;display:flex;align-items:center;justify-content:center;min-height:360px;max-height:70vh;overflow:auto}
.pub-preview img{max-width:100%;max-height:70vh;object-fit:contain}
.pub-preview video,.pub-preview audio{max-width:100%;outline:none}
.pub-preview iframe{width:100%;height:70vh;border:none}
.pub-preview pre{padding:1.5rem;font-size:13px;white-space:pre-wrap;word-break:break-all;color:#1a1a1a;max-height:70vh;overflow:auto;width:100%;background:#1e1e1e;color:#d4d4d4}
.pub-nopreview{display:flex;flex-direction:column;align-items:center;gap:.75rem;padding:3rem;text-align:center;color:#6B6055}
.pub-nopreview svg{opacity:.3}
.pub-footer{padding:.85rem 1.5rem;border-top:1px solid #F0EBE2;display:flex;align-items:center;justify-content:space-between;font-size:12px;color:#A89E90}
</style>
</head>
<body>
<div class="pub-card">
  <div class="pub-header">
    <div class="pub-icon" style="background:<?= htmlspecialchars($mimeColor) ?>"><?= htmlspecialchars($mimeLabel) ?></div>
    <div class="pub-title">
      <div class="pub-filename"><?= $title ?></div>
      <div class="pub-meta"><?= htmlspecialchars($sizeLabel) ?> &middot; Dokumen Dibagikan</div>
    </div>
    <?php if ($canDownload): ?>
    <a href="<?= $dlUrl ?>" class="pub-dl-btn">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Download
    </a>
    <?php endif; ?>
  </div>

  <div class="pub-preview">
    <?php if (!$previewable): ?>
    <div class="pub-nopreview">
      <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
      <p>Tipe file ini tidak dapat ditampilkan di browser.</p>
      <?php if ($canDownload): ?><a href="<?= $dlUrl ?>" class="pub-dl-btn" style="background:#7B1C1C;color:#fff;border-radius:8px;padding:.5rem 1.2rem;text-decoration:none;font-size:13px;font-weight:700">Download File</a><?php endif; ?>
    </div>
    <?php elseif ($isImage): ?>
      <img src="<?= $previewUrl ?>" alt="<?= $title ?>">
    <?php elseif ($isVideo): ?>
      <video controls><source src="<?= $previewUrl ?>" type="<?= htmlspecialchars($file['mime_type']) ?>"></video>
    <?php elseif ($isAudio): ?>
      <audio controls style="margin:2rem"><source src="<?= $previewUrl ?>" type="<?= htmlspecialchars($file['mime_type']) ?>"></audio>
    <?php elseif ($isPdf): ?>
      <iframe src="<?= $previewUrl ?>#toolbar=1" title="<?= $title ?>"></iframe>
    <?php elseif ($isText): ?>
      <pre id="text-content">Memuat...</pre>
      <script>
        fetch('<?= $previewUrl ?>').then(r=>r.text()).then(t=>{
          document.getElementById('text-content').textContent = t.slice(0,50000);
        });
      </script>
    <?php endif; ?>
  </div>

  <div class="pub-footer">
    <span>File ini dibagikan secara publik</span>
    <?php if ($link['expires_at']): ?>
    <span>Kadaluarsa: <?= date('d M Y H:i', strtotime($link['expires_at'])) ?></span>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
<?php
        exit;
    }

    private static function renderPasswordForm(array $file, array $link, string $error): void
    {
        $title = htmlspecialchars(self::safeFileName($file['original_name']));
        ?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>File Dilindungi Password</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#1a1614;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem}
.pw-card{background:#fff;border-radius:16px;width:100%;max-width:380px;padding:2rem;box-shadow:0 24px 64px rgba(0,0,0,.4);text-align:center}
.pw-icon{width:56px;height:56px;background:#F5F0E8;border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 1.1rem}
.pw-title{font-size:16px;font-weight:800;color:#1C1714;margin-bottom:.35rem}
.pw-sub{font-size:13px;color:#6B6055;margin-bottom:1.4rem}
.pw-input{width:100%;border:1.5px solid #DDD5C4;border-radius:10px;padding:.65rem .9rem;font-size:14px;color:#1C1714;outline:none;transition:border-color 150ms;margin-bottom:.85rem}
.pw-input:focus{border-color:#7B1C1C}
.pw-btn{width:100%;height:42px;background:#7B1C1C;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:800;cursor:pointer;transition:background 160ms}
.pw-btn:hover{background:#5A1212}
.pw-err{color:#C05621;font-size:12.5px;margin-bottom:.75rem}
.pw-fname{font-size:13px;font-weight:700;color:#1C1714;background:#F9F7F4;border-radius:8px;padding:.4rem .7rem;margin-bottom:1.2rem;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
</style>
</head>
<body>
<div class="pw-card">
  <div class="pw-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#7B1C1C" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
  </div>
  <div class="pw-title">File Dilindungi Password</div>
  <div class="pw-sub">Masukkan password untuk mengakses file ini.</div>
  <div class="pw-fname"><?= $title ?></div>
  <?php if ($error): ?><div class="pw-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST">
    <input type="password" name="password" class="pw-input" placeholder="Password" autofocus required>
    <button type="submit" class="pw-btn">Buka File</button>
  </form>
</div>
</body>
</html>
<?php
        exit;
    }

    private static function renderError(string $msg): void
    {
        ?>
<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Link Tidak Valid</title>
<style>body{font-family:system-ui,sans-serif;background:#1a1614;min-height:100vh;display:flex;align-items:center;justify-content:center;color:#fff;text-align:center;padding:2rem}
.box{background:rgba(255,255,255,.07);border-radius:16px;padding:2.5rem;max-width:420px}
.icon{font-size:48px;margin-bottom:1rem}
h2{font-size:18px;margin-bottom:.5rem}p{font-size:13px;opacity:.7}
</style></head>
<body><div class="box"><div class="icon">&#128274;</div><h2>Link Tidak Valid</h2><p><?= htmlspecialchars($msg) ?></p></div></body>
</html>
<?php
        exit;
    }

    private static function safeFileName(string $name): string
    {
        $name = trim(str_replace(["\r", "\n", "\0"], '', $name));
        return $name === '' ? 'file' : $name;
    }
}
