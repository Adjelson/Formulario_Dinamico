<?php
// ============================================================
//  CONFIGURAÇÃO GERAL — compatível com XAMPP e IIS
// ============================================================

// ---- Base de dados (XAMPP usa root sem senha por padrão) ----
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');          // altere se definiu senha no XAMPP
define('DB_NAME', 'dynamic_forms');

// ---- Caminhos do sistema ----
// APPROOT aponta para a raiz do projeto (um nível acima de /config)
define('APPROOT', dirname(dirname(__FILE__)));

// ---- URL base ----
// Ajuste 'dynamic_forms' para o nome da pasta dentro de htdocs (XAMPP) ou wwwroot (IIS)
define('URLROOT', 'https://localhost/dynamic_forms');

// ---- Nome e versão ----
define('SITENAME', 'Dynamic Forms');
define('APPVERSION', '1.0.0');

// ---- Segurança de sessão ----
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_NAME', 'dynamic_forms_session');

// ---- Upload seguro (FORA de public/) ----
// Os ficheiros ficam em  <raiz>/storage/uploads/
// e são servidos apenas através de download.php (nunca por URL direta)
define('UPLOAD_DIR',  APPROOT . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
