<?php
// Timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (set false di production)
define('APP_DEBUG', true);
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

define('APP_NAME',    'MeetingApp');
define('APP_VERSION', '1.0.0');
define('APP_LOCALE',  'id_ID');
