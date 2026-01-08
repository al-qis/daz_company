<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_NAME', 'daz_security');
define('DB_USER', 'root');
define('DB_PASS', ''); // Or your password

define('LOCKOUT_THRESHOLD', 3);
define('LOCKOUT_DURATION', 60);
define('OTP_EXPIRY', 300);

define('SITE_NAME', 'DAZ Security Control Terminal');
define('SITE_URL', 'http://localhost/daz_security/');

error_reporting(E_ALL);
ini_set('display_errors', 1);
?>