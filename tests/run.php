<?php

declare(strict_types=1);

session_start();
require dirname(__DIR__) . '/config/config.php';
require APPROOT . '/core/Security.php';
require APPROOT . '/core/Router.php';

$passed = 0;
$failed = 0;
function check(bool $condition, string $description): void {
    global $passed, $failed;
    if ($condition) { $passed++; echo "[OK] {$description}\n"; }
    else { $failed++; echo "[FALHA] {$description}\n"; }
}

$_SESSION = [];
$token = csrf_token();
check(strlen($token) === 64, 'CSRF gera 32 bytes em hexadecimal');
check(csrf_is_valid($token), 'CSRF aceita o token da sessão');
check(!csrf_is_valid($token . 'x'), 'CSRF rejeita token alterado');
check(safe_internal_path('forms/formulario-demo') === 'forms/formulario-demo', 'Redirecionamento interno válido');
check(safe_internal_path('https://example.com') === '', 'Redirecionamento externo bloqueado');
check(safe_internal_path('../admin') === '', 'Path traversal bloqueado');
check(csv_safe('=1+1') === "'=1+1", 'Fórmula CSV neutralizada');
check(csv_safe('Texto normal') === 'Texto normal', 'Texto CSV normal preservado');
check(e('<script>') === '&lt;script&gt;', 'Escape HTML funciona');
check(text_length('Formulário') >= 10, 'Comprimento de texto funciona com ou sem mbstring');
check(text_substr('Dynamic', 0, 3) === 'Dyn', 'Recorte de texto compatível');
check(user_initial('adjelson') === 'A', 'Inicial do utilizador normalizada');

$router = new Router();
$router->add('forms/{slug}', ['controller' => 'form', 'action' => 'show']);
check($router->match('forms/exemplo-1'), 'Router reconhece slug');
check(!$router->match('admin/forms/1'), 'Router rejeita rota não registada');

$password = password_hash('Admin@123', PASSWORD_DEFAULT);
check(password_verify('Admin@123', $password), 'Hash de palavra-passe válido');
check(!password_verify('errada', $password), 'Palavra-passe incorreta rejeitada');

printf("\nResultado: %d aprovados, %d falhas.\n", $passed, $failed);
exit($failed === 0 ? 0 : 1);
