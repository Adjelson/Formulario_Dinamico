<?php
// Require configuration primeiro (define constantes)
require_once '../config/config.php';

// Configurar sessão segura antes de session_start()
session_name(SESSION_NAME);
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
session_start();

// Require core classes
require_once '../core/Database.php';
require_once '../core/Controller.php';
require_once '../core/Model.php';
require_once '../core/Router.php';

// Garantir que a pasta de uploads existe (fora de public/)
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0750, true);
}

// Create router instance
$router = new Router();

// Define routes
// Auth routes
$router->add('', ['controller' => 'auth', 'action' => 'login']);
$router->add('login', ['controller' => 'auth', 'action' => 'login']);
$router->add('logout', ['controller' => 'auth', 'action' => 'logout']);
$router->add('register', ['controller' => 'auth', 'action' => 'register']);

// Página inicial do utilizador normal — lista formulários publicados
$router->add('home', ['controller' => 'pages', 'action' => 'index']);

// Admin routes
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

// Public form routes
$router->add('forms/{slug}', ['controller' => 'form', 'action' => 'show']);
$router->add('forms/{slug}/submit', ['controller' => 'response', 'action' => 'store']);
$router->add('forms/{slug}/success', ['controller' => 'pages', 'action' => 'formSuccess']);

// Rota segura de download de ficheiros (fora de public/)
$router->add('download/{file}', ['controller' => 'download', 'action' => 'serve']);

// Rota pública para imagens de capa dos formulários
$router->add('cover/{file}', ['controller' => 'cover', 'action' => 'serve']);

// User routes
$router->add('my/history', ['controller' => 'response', 'action' => 'history']);
$router->add('my/history/{response_id}', ['controller' => 'response', 'action' => 'detail']);

// Get URL
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Remove trailing slash
$url = rtrim($url, '/');

// Dispatch the router
try {
    $router->dispatch($url);
} catch (Exception $e) {
    http_response_code(404);
    echo "Error: " . $e->getMessage();
}
