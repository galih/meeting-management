<?php
declare(strict_types=1);

class SettingController
{
    private static function getSetting(string $key, string $default = ''): string
    {
        try {
            $row = Database::queryOne("SELECT value FROM app_settings WHERE `key`=?", [$key]);
            return ($row && $row['value'] !== null) ? $row['value'] : $default;
        } catch (\Throwable $e) { return $default; }
    }

    private static function setSetting(string $key, string $value): void
    {
        try {
            $exists = Database::queryOne("SELECT id FROM app_settings WHERE `key`=?", [$key]);
            if ($exists) {
                Database::getInstance()->prepare("UPDATE app_settings SET value=?, updated_at=NOW() WHERE `key`=?")->execute([$value, $key]);
            } else {
                Database::getInstance()->prepare("INSERT INTO app_settings (`key`, value) VALUES (?,?)")->execute([$key, $value]);
            }
        } catch (\Throwable $e) { error_log('SettingController::setSetting error: ' . $e->getMessage()); }
    }

    public static function index(): void
    {
        Auth::requireRole('admin');
        $settings = [
            'app_logo'               => self::getSetting('app_logo'),
            'login_bg'               => self::getSetting('login_bg'),
            'app_name_custom'        => self::getSetting('app_name_custom', APP_NAME),
            'smtp_host'              => self::getSetting('smtp_host'),
            'smtp_port'              => self::getSetting('smtp_port', '587'),
            'smtp_encryption'        => self::getSetting('smtp_encryption', 'tls'),
            'smtp_username'          => self::getSetting('smtp_username'),
            'smtp_password'          => self::getSetting('smtp_password'),
            'smtp_from_email'        => self::getSetting('smtp_from_email'),
            'smtp_from_name'         => self::getSetting('smtp_from_name', APP_NAME),
            'notulen_history_limit'  => self::getSetting('notulen_history_limit', '10'),
            'docx_template'          => self::getSetting('docx_template'),
        ];
        View::layout('settings/index', [
            'pageTitle' => 'Pengaturan Aplikasi',
            'settings'  => $settings,
        ]);
    }

    /* ── SAVE NOTULEN ───────────────────────────────────────────────────────── */
    public static function saveNotulen(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $limit = (int)($_POST['notulen_history_limit'] ?? 10);
        if ($limit < 1)   $limit = 1;
        if ($limit > 100) $limit = 100;
        self::setSetting('notulen_history_limit', (string)$limit);
        echo json_encode(['success' => true, 'message' => 'Pengaturan notulen berhasil disimpan.']);
        exit;
    }

    /* ── UPLOAD TEMPLATE DOCX ───────────────────────────────────────────────── */
    public static function uploadDocxTemplate(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');

        $file = $_FILES['docx_template'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'File tidak valid atau upload gagal (kode: ' . ($file['error'] ?? 'N/A') . ')']);
            exit;
        }

        // Validasi ekstensi & MIME
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mime = mime_content_type($file['tmp_name']);
        $allowedMimes = [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',          // beberapa OS melaporkan .docx sebagai zip
            'application/octet-stream', // fallback
        ];
        if ($ext !== 'docx' || !in_array($mime, $allowedMimes)) {
            echo json_encode(['success' => false, 'message' => 'File harus berformat .docx']);
            exit;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
            echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 10 MB.']);
            exit;
        }

        $dir  = ROOT_PATH . '/assets/templates/';
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $name = 'notulen-template-' . time() . '.docx';
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan file. Periksa permission folder assets/templates/']);
            exit;
        }

        // Hapus template lama
        $old = self::getSetting('docx_template');
        self::deleteOldTemplate($old);

        $url = BASE_URL . '/assets/templates/' . $name;
        self::setSetting('docx_template', $url);
        echo json_encode(['success' => true, 'message' => 'Template berhasil diupload.', 'filename' => $file['name'], 'url' => $url]);
        exit;
    }

    /* ── REMOVE TEMPLATE DOCX ───────────────────────────────────────────────── */
    public static function removeDocxTemplate(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $old = self::getSetting('docx_template');
        self::deleteOldTemplate($old);
        self::setSetting('docx_template', '');
        echo json_encode(['success' => true, 'message' => 'Template berhasil dihapus.']);
        exit;
    }

    private static function deleteOldTemplate(string $url): void
    {
        if (empty($url)) return;
        $baseUrl  = rtrim(BASE_URL, '/');
        $relative = ltrim(str_replace($baseUrl, '', $url), '/');
        $path     = ROOT_PATH . '/' . $relative;
        $needle   = DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        if (file_exists($path) && strpos($path, $needle) !== false) @unlink($path);
    }

    /* ── SMTP ───────────────────────────────────────────────────────────────── */
    public static function saveSMTP(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $fields = ['smtp_host','smtp_port','smtp_encryption','smtp_username','smtp_from_email','smtp_from_name'];
        foreach ($fields as $f) { self::setSetting($f, trim($_POST[$f] ?? '')); }
        $pass = trim($_POST['smtp_password'] ?? '');
        if ($pass !== '') self::setSetting('smtp_password', $pass);
        echo json_encode(['success' => true, 'message' => 'Pengaturan SMTP berhasil disimpan.']);
        exit;
    }

    public static function testSMTP(): void
    {
        Auth::requireRole('admin');
        header('Content-Type: application/json');
        $to = trim($_POST['test_email'] ?? '');
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Alamat email tujuan tidak valid.']); exit;
        }
        $host=$this->getSetting('smtp_host');$port=(int)self::getSetting('smtp_port','587');
        $encryption=self::getSetting('smtp_encryption','tls');$username=self::getSetting('smtp_username');
        $password=self::getSetting('smtp_password');$fromEmail=self::getSetting('smtp_from_email');
        $fromName=self::getSetting('smtp_from_name',APP_NAME);
        if (empty($host)||empty($username)||empty($password)||empty($fromEmail)) {
            echo json_encode(['success'=>false,'message'=>'Lengkapi pengaturan SMTP terlebih dahulu.']); exit;
        }
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail=new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();$mail->Host=$host;$mail->SMTPAuth=true;
                $mail->Username=$username;$mail->Password=$password;
                $mail->SMTPSecure=$encryption;$mail->Port=$port;
                $mail->setFrom($fromEmail,$fromName);$mail->addAddress($to);
                $mail->Subject='['.APP_NAME.'] Test SMTP';
                $mail->Body='Email test dari '.APP_NAME.'. Konfigurasi SMTP berhasil!';
                $mail->send();
                echo json_encode(['success'=>true,'message'=>'Email test berhasil dikirim ke '.$to]);
            } catch (\Throwable $e) { echo json_encode(['success'=>false,'message'=>'Gagal: '.$e->getMessage()]); }
        } else {
            $conn=@fsockopen(($encryption==='ssl'?'ssl://':'').$host,$port,$errno,$errstr,5);
            if ($conn) { fclose($conn); echo json_encode(['success'=>true,'message'=>'Koneksi ke '.$host.':'.$port.' berhasil.']); }
            else { echo json_encode(['success'=>false,'message'=>'Gagal terhubung ke '.$host.':'.$port.' — '.$errstr]); }
        }
        exit;
    }

    /* ── UPLOAD IMAGE (logo / login-bg) ────────────────────────────────────── */
    public static function uploadLogo(): void
    {
        Auth::requireRole('admin'); header('Content-Type: application/json');
        $file=$_FILES['logo']??null;
        if(!$file||$file['error']!==UPLOAD_ERR_OK){echo json_encode(['success'=>false,'message'=>'File tidak valid (kode: '.($file['error']??'N/A').')']);exit;}
        $result=self::handleUpload($file,'logo');
        if(!$result['success']){echo json_encode($result);exit;}
        $old=self::getSetting('app_logo');self::deleteOldFile($old);
        self::setSetting('app_logo',$result['path']);
        echo json_encode(['success'=>true,'path'=>$result['path']]);exit;
    }
    public static function uploadLoginBg(): void
    {
        Auth::requireRole('admin'); header('Content-Type: application/json');
        $file=$_FILES['login_bg']??null;
        if(!$file||$file['error']!==UPLOAD_ERR_OK){echo json_encode(['success'=>false,'message'=>'File tidak valid (kode: '.($file['error']??'N/A').')']);exit;}
        $result=self::handleUpload($file,'login-bg');
        if(!$result['success']){echo json_encode($result);exit;}
        $old=self::getSetting('login_bg');self::deleteOldFile($old);
        self::setSetting('login_bg',$result['path']);
        echo json_encode(['success'=>true,'path'=>$result['path']]);exit;
    }
    public static function removeLogo(): void
    {
        Auth::requireRole('admin'); header('Content-Type: application/json');
        $old=self::getSetting('app_logo');self::deleteOldFile($old);
        self::setSetting('app_logo','');echo json_encode(['success'=>true]);exit;
    }
    public static function removeLoginBg(): void
    {
        Auth::requireRole('admin'); header('Content-Type: application/json');
        $old=self::getSetting('login_bg');self::deleteOldFile($old);
        self::setSetting('login_bg','');echo json_encode(['success'=>true]);exit;
    }
    private static function handleUpload(array $file, string $prefix): array
    {
        $allowed=['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
        $mime=mime_content_type($file['tmp_name']);
        if(!in_array($mime,$allowed))return['success'=>false,'message'=>'Tipe file tidak didukung.'];
        if($file['size']>2*1024*1024)return['success'=>false,'message'=>'Ukuran file maksimal 2 MB.'];
        $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION)?:'png');
        $name=$prefix.'-'.time().'.'.$ext;
        $dir=ROOT_PATH.'/assets/uploads/';
        if(!is_dir($dir))@mkdir($dir,0755,true);
        if(!move_uploaded_file($file['tmp_name'],$dir.$name))return['success'=>false,'message'=>'Gagal menyimpan file.'];
        return['success'=>true,'path'=>BASE_URL.'/assets/uploads/'.$name];
    }
    private static function deleteOldFile(string $url): void
    {
        if(empty($url))return;
        $baseUrl=rtrim(BASE_URL,'/');$relative=ltrim(str_replace($baseUrl,'',$url),'/');
        $path=ROOT_PATH.'/'.$relative;
        $needle=DIRECTORY_SEPARATOR.'assets'.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR;
        if(file_exists($path)&&strpos($path,$needle)!==false)@unlink($path);
    }
    public static function get(string $key, string $default = ''): string
    {
        return self::getSetting($key, $default);
    }
}
