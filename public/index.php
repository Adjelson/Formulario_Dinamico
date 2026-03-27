<?php
// ---- Tratamento de erros conforme ambiente ----
// Carregar config primeiro para saber se é produção
require_once '../config/config.php';

if (defined('PRODUCTION') && PRODUCTION) {
    // Produção: esconder erros, registar em ficheiro
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', APPROOT . '/storage/php_errors.log');
} else {
    // Desenvolvimento: mostrar todos os erros
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// ---- Sessão segura ----
session_name(SESSION_NAME);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');

// Forçar cookie seguro em HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', '1');
}

session_start();

// ---- Core classes ----
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/Model.php';
require_once '../core/Router.php';

// ---- Garantir pastas de storage ----
foreach (['uploads', 'covers'] as $dir) {
    $path = APPROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . $dir;
    if (!is_dir($path)) mkdir($path, 0750, true);
}

// ---- Router ----
$router = new Router();

// Auth
$router->add('', ['controller' => 'auth', 'action' => 'login']);
$router->add('login', ['controller' => 'auth', 'action' => 'login']);
$router->add('logout', ['controller' => 'auth', 'action' => 'logout']);
$router->add('register', ['controller' => 'auth', 'action' => 'register']);

// Página inicial do utilizador normal
$router->add('home', ['controller' => 'pages', 'action' => 'index']);

// Admin — Formulários
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

// Admin — Utilizadores
$router->add('admin/users', ['controller' => 'user', 'action' => 'index']);
$router->add('admin/users/store', ['controller' => 'user', 'action' => 'store']);
$router->add('admin/users/{id}/update', ['controller' => 'user', 'action' => 'update']);
$router->add('admin/users/{id}/delete', ['controller' => 'user', 'action' => 'delete']);

// Formulários públicos
$router->add('forms/{slug}', ['controller' => 'form', 'action' => 'show']);
$router->add('forms/{slug}/submit', ['controller' => 'response', 'action' => 'store']);
$router->add('forms/{slug}/success', ['controller' => 'pages', 'action' => 'formSuccess']);

// Ficheiros seguros
$router->add('download/{file}', ['controller' => 'download', 'action' => 'serve']);
$router->add('cover/{file}', ['controller' => 'cover', 'action' => 'serve']);

// Histórico do utilizador
$router->add('my/history', ['controller' => 'response', 'action' => 'history']);
$router->add('my/history/{response_id}', ['controller' => 'response', 'action' => 'detail']);

// Utilizador elimina a sua própria resposta para poder preencher novamente
// Rota para utilizador eliminar a sua própria resposta e preencher de novo
$router->add('forms/{slug}/retract/{id}', ['controller' => 'response', 'action' => 'deleteOwn']);

// ---- Obter URL da query string ----
$url = trim($_GET['url'] ?? '', '/');

// ---- Dispatch ----
try {
    $router->dispatch($url);
} catch (Exception $e) {
    // Registar o erro real sempre (vai para storage/php_errors.log em produção)
    error_log('[DynamicForms] ' . $e->getMessage() . ' | URL: ' . ($url ?? '') . ' | File: ' . $e->getFile() . ':' . $e->getLine());

    if (defined('PRODUCTION') && PRODUCTION) {
        $code = $e->getCode() === 404 ? 404 : 500;
        http_response_code($code);
        $msg = $code === 404 ? 'Página não encontrada' : 'Erro interno do servidor';
        echo '<!DOCTYPE html><html lang="pt"><head><meta charset="UTF-8"><title>' . $msg . ' — ' . SITENAME . '</title>
              <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f1f5f9;}
              .box{text-align:center;padding:3rem;background:#fff;border-radius:12px;box-shadow:0 4px 24px rgba(0,0,0,.1);max-width:480px;}
              h2{color:#1e293b;margin-bottom:.5rem;} p{color:#64748b;margin-bottom:1.5rem;} a{color:#16a34a;font-weight:600;text-decoration:none;}</style>
              </head><body><div class="box">
              <h2>' . ($code === 404 ? '&#128269; 404' : '&#9888; 500') . ' — ' . $msg . '</h2>
              <p>' . ($code === 404 ? 'A página que procura não foi encontrada.' : 'Ocorreu um erro. Por favor tente novamente mais tarde.') . '</p>
              <a href="' . URLROOT . '">&#8592; Voltar ao início</a>
              </div></body></html>';
    } else {
        http_response_code(500);
        echo '<pre style="background:#fee2e2;padding:1.5rem;border-radius:8px;color:#991b1b;font-size:.875rem;line-height:1.5;">';
        echo '<strong style="font-size:1rem;">&#10060; ' . get_class($e) . '</strong>' . "\n\n";
        echo '<strong>Mensagem:</strong> ' . htmlspecialchars($e->getMessage()) . "\n";
        echo '<strong>Ficheiro:</strong> ' . $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo '<strong>Stack Trace:</strong>' . "\n" . htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    }
}
