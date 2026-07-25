<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';
require_once APPROOT . '/core/Security.php';

error_reporting(PRODUCTION ? 0 : E_ALL);
ini_set('display_errors', PRODUCTION ? '0' : '1');
ini_set('log_errors', '1');
ini_set('error_log', LOG_DIR . '/php_errors.log');

foreach ([STORAGE_DIR, UPLOAD_DIR, COVER_DIR, LOG_DIR] as $directory) {
    if (!is_dir($directory) && !mkdir($directory, 0750, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível preparar a pasta de armazenamento.');
    }
}

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (TRUST_PROXY && ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

session_name(SESSION_NAME);
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_start();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; font-src 'self' data:; script-src 'self' 'unsafe-inline'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'");

require_once APPROOT . '/core/Database.php';
require_once APPROOT . '/core/Controller.php';
require_once APPROOT . '/core/Model.php';
require_once APPROOT . '/core/Router.php';

// Revoga sessões de contas entretanto desativadas, sem consultar a BD em todos os pedidos.
if (!empty($_SESSION['user_id']) && (time() - (int) ($_SESSION['_last_account_check'] ?? 0)) > 60) {
    try {
        $db = new Database();
        $db->query('SELECT name, email, role, is_active FROM users WHERE id = :id LIMIT 1')
            ->bind(':id', (int) $_SESSION['user_id']);
        $account = $db->single();
        if (!$account || !(int) $account->is_active) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . URLROOT . '/login?inactive=1');
            exit;
        }
        $_SESSION['user_name'] = $account->name;
        $_SESSION['user_email'] = $account->email;
        $_SESSION['user_role'] = $account->role;
        $_SESSION['_last_account_check'] = time();
    } catch (Throwable $e) {
        error_log('[Session account check] ' . $e->getMessage());
    }
}

$router = new Router();
$router->add('', ['controller' => 'auth', 'action' => 'login']);
$router->add('health', ['controller' => 'health', 'action' => 'index']);
$router->add('login', ['controller' => 'auth', 'action' => 'login']);
$router->add('logout', ['controller' => 'auth', 'action' => 'logout']);
$router->add('register', ['controller' => 'auth', 'action' => 'register']);
$router->add('home', ['controller' => 'pages', 'action' => 'index']);

$router->add('admin/dashboard', ['controller' => 'form', 'action' => 'dashboard']);
$router->add('admin/forms', ['controller' => 'form', 'action' => 'index']);
$router->add('admin/forms/create', ['controller' => 'form', 'action' => 'create']);
$router->add('admin/forms/store', ['controller' => 'form', 'action' => 'store']);
$router->add('admin/forms/{id}/edit', ['controller' => 'form', 'action' => 'edit']);
$router->add('admin/forms/{id}/update', ['controller' => 'form', 'action' => 'update']);
$router->add('admin/forms/{id}/delete', ['controller' => 'form', 'action' => 'delete']);
$router->add('admin/forms/{id}/responses', ['controller' => 'response', 'action' => 'index']);
$router->add('admin/forms/{id}/export-csv', ['controller' => 'response', 'action' => 'exportCsv']);
$router->add('admin/forms/{id}/export-zip', ['controller' => 'response', 'action' => 'exportZip']);
$router->add('admin/responses/{id}/detail', ['controller' => 'response', 'action' => 'adminDetail']);
$router->add('admin/responses/{id}/delete', ['controller' => 'response', 'action' => 'delete']);

$router->add('admin/users', ['controller' => 'user', 'action' => 'index']);
$router->add('admin/users/store', ['controller' => 'user', 'action' => 'store']);
$router->add('admin/users/{id}/update', ['controller' => 'user', 'action' => 'update']);
$router->add('admin/users/{id}/delete', ['controller' => 'user', 'action' => 'delete']);

$router->add('forms/{slug}', ['controller' => 'form', 'action' => 'show']);
$router->add('forms/{slug}/submit', ['controller' => 'response', 'action' => 'store']);
$router->add('forms/{slug}/success', ['controller' => 'pages', 'action' => 'formSuccess']);
$router->add('forms/{slug}/retract/{id}', ['controller' => 'response', 'action' => 'deleteOwn']);

$router->add('download/{file}', ['controller' => 'download', 'action' => 'serve']);
$router->add('cover/{file}', ['controller' => 'cover', 'action' => 'serve']);
$router->add('my/history', ['controller' => 'response', 'action' => 'history']);
$router->add('my/history/{response_id}', ['controller' => 'response', 'action' => 'detail']);

$url = trim((string) ($_GET['url'] ?? ''), '/');

try {
    $router->dispatch($url);
} catch (Throwable $e) {
    $code = (int) $e->getCode();
    if (!in_array($code, [403, 404, 405, 419], true)) {
        $code = 500;
    }
    http_response_code($code);
    error_log(sprintf('[DynamicForms] %s | URL: %s | %s:%d', $e->getMessage(), $url, $e->getFile(), $e->getLine()));

    $titles = [403 => 'Acesso negado', 404 => 'Página não encontrada', 405 => 'Método não permitido', 419 => 'Sessão expirada', 500 => 'Erro interno'];
    $messages = [
        403 => 'Não tem permissão para aceder a este recurso.',
        404 => 'A página ou o registo solicitado não foi encontrado.',
        405 => 'Esta ação não aceita o método de pedido utilizado.',
        419 => 'Atualize a página e tente novamente.',
        500 => 'Ocorreu um erro inesperado. Consulte os logs da aplicação.',
    ];
    $detail = !PRODUCTION && $code === 500 ? '<pre>' . e($e->getMessage()) . '</pre>' : '';
    echo '<!doctype html><html lang="pt-PT"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>' . e($titles[$code]) . '</title><style>body{font-family:system-ui,sans-serif;background:#f1f5f9;color:#0f172a;display:grid;place-items:center;min-height:100vh;margin:0}.box{background:#fff;padding:2rem;border-radius:1rem;max-width:34rem;box-shadow:0 10px 30px #0002;text-align:center}a{color:#15803d;font-weight:700}pre{text-align:left;white-space:pre-wrap;background:#fee2e2;padding:1rem;border-radius:.5rem}</style></head><body><main class="box"><h1>' . $code . ' — ' . e($titles[$code]) . '</h1><p>' . e($messages[$code]) . '</p>' . $detail . '<a href="' . e(URLROOT) . '">Voltar ao início</a></main></body></html>';
}
