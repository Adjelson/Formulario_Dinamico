<?php
/**
 * DIAGNÓSTICO DE PRODUÇÃO — remover após resolver o problema!
 * Aceder via: https://ine.st/dynamic_forms/debug_info.php
 * APAGAR este ficheiro depois de diagnosticar.
 */

// Protecção básica — mudar esta chave
$key = $_GET['key'] ?? '';
if ($key !== 'df_debug_2025') {
    http_response_code(403);
    die('Forbidden');
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== PHP VERSION ===\n";
echo PHP_VERSION . "\n\n";

echo "=== EXTENSIONS ===\n";
$needed = ['pdo', 'pdo_mysql', 'gd', 'zip', 'mbstring', 'json', 'fileinfo'];
foreach ($needed as $ext) {
    echo $ext . ': ' . (extension_loaded($ext) ? 'OK' : 'MISSING') . "\n";
}

echo "\n=== CONFIG ===\n";
require_once dirname(__DIR__) . '/config/config.php';
echo 'APPROOT: ' . APPROOT . "\n";
echo 'URLROOT: ' . URLROOT . "\n";
echo 'UPLOAD_DIR: ' . UPLOAD_DIR . "\n";
echo 'UPLOAD_DIR writable: ' . (is_writable(UPLOAD_DIR) ? 'YES' : 'NO/MISSING') . "\n";

$coverDir = APPROOT . '/storage/covers';
echo 'COVER_DIR: ' . $coverDir . "\n";
echo 'COVER_DIR writable: ' . (is_writable($coverDir) ? 'YES' : 'NO/MISSING') . "\n";

echo "\n=== DATABASE ===\n";
try {
    require_once dirname(__DIR__) . '/core/Database.php';
    $db = new Database();
    echo "Connection: OK\n";

    // Testar tabelas
    $tables = ['forms', 'questions', 'responses', 'answers', 'users',
               'forms_trash', 'responses_trash', 'users_trash'];
    foreach ($tables as $t) {
        try {
            $db->query("SELECT COUNT(*) as c FROM $t");
            $row = $db->single();
            echo "$t: OK ({$row->c} rows)\n";
        } catch (Exception $e2) {
            echo "$t: MISSING - " . $e2->getMessage() . "\n";
        }
    }

    // Verificar colunas da tabela forms
    echo "\n=== FORMS TABLE COLUMNS ===\n";
    $db->query("SHOW COLUMNS FROM forms");
    $cols = $db->resultSet();
    foreach ($cols as $c) echo $c->Field . ' (' . $c->Type . ")\n";

} catch (Exception $e) {
    echo "Connection FAILED: " . $e->getMessage() . "\n";
}

echo "\n=== RECENT PHP ERRORS ===\n";
$logFile = APPROOT . '/storage/php_errors.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $last  = array_slice($lines, -20);
    echo implode('', $last);
} else {
    echo "No error log yet.\n";
}

echo "\n=== DONE ===\n";
