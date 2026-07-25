<?php

require_once __DIR__ . '/env.php';

$projectRoot = dirname(__DIR__);
load_env_file($projectRoot . DIRECTORY_SEPARATOR . '.env');

define('APPROOT', $projectRoot);
define('APP_ENV', (string) env_value('APP_ENV', 'development'));
define('PRODUCTION', APP_ENV === 'production');
define('SITENAME', (string) env_value('APP_NAME', 'Dynamic Forms'));
define('APPVERSION', '2.0.0');

// Em produção, APP_URL deve ser preenchido no .env.
$appUrl = rtrim((string) env_value('APP_URL', 'http://localhost:8080'), '/');
define('URLROOT', $appUrl);

define('DB_HOST', (string) env_value('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) env_value('DB_PORT', 3306));
define('DB_NAME', (string) env_value('DB_NAME', 'dynamic_forms'));
define('DB_USER', (string) env_value('DB_USER', 'root'));
define('DB_PASS', (string) env_value('DB_PASS', ''));
define('DB_PERSISTENT', (bool) env_value('DB_PERSISTENT', false));

define('SESSION_NAME', (string) env_value('SESSION_NAME', 'dynamic_forms_session'));
define('CSRF_TOKEN_NAME', '_csrf');
define('SESSION_LIFETIME', (int) env_value('SESSION_LIFETIME', 7200));

define('STORAGE_DIR', APPROOT . DIRECTORY_SEPARATOR . 'storage');
define('UPLOAD_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'uploads');
define('COVER_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'covers');
define('LOG_DIR', STORAGE_DIR . DIRECTORY_SEPARATOR . 'logs');
define('MAX_UPLOAD_SIZE', (int) env_value('MAX_UPLOAD_SIZE', 5 * 1024 * 1024));
define('MAX_COVER_SIZE', (int) env_value('MAX_COVER_SIZE', 2 * 1024 * 1024));
define('MAX_QUESTIONS_PER_FORM', (int) env_value('MAX_QUESTIONS_PER_FORM', 100));

define('TRUST_PROXY', (bool) env_value('TRUST_PROXY', false));
