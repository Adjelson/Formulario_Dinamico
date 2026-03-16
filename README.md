# Dynamic Forms — Guia de Instalação (XAMPP & IIS)

## Estrutura do Projeto

```
dynamic_forms/          ← raiz do projecto
├── app/
│   ├── controllers/
│   ├── models/
│   └── views/
├── config/
│   └── config.php      ← EDITAR: URLROOT, DB_PASS, etc.
├── core/
├── public/             ← único directório exposto ao browser
│   ├── index.php
│   ├── css/
│   ├── js/
│   ├── .htaccess       ← Apache / XAMPP
│   └── web.config      ← IIS
├── storage/
│   └── uploads/        ← ficheiros enviados (NUNCA acessível por URL directa)
├── .htaccess           ← redireciona tudo para public/ (Apache)
└── web.config          ← redireciona tudo para public/ (IIS)
```

---

## Instalação no XAMPP

### 1. Pré-requisitos
- XAMPP >= 8.0 (PHP 8.0+, MySQL 5.7+ ou MariaDB 10.4+)
- mod_rewrite activado (vem activo por padrão no XAMPP)

### 2. Copiar ficheiros
```
C:\xampp\htdocs\dynamic_forms\
```

### 3. Configurar config/config.php
```php
define('URLROOT', 'http://localhost/dynamic_forms/public');
define('DB_PASS', '');  // senha do MySQL, normalmente vazia no XAMPP
```

### 4. Criar a base de dados
1. Abrir http://localhost/phpmyadmin
2. Criar base de dados dynamic_forms
3. Importar database.sql

### 5. Permissões (Linux/Mac com XAMPP)
```bash
chmod 750 storage/uploads
chown www-data:www-data storage/uploads
```

### 6. Verificar mod_rewrite no httpd.conf
```apache
LoadModule rewrite_module modules/mod_rewrite.so
AllowOverride All    # dentro do bloco <Directory "C:/xampp/htdocs">
```

---

## Instalação no IIS (Windows Server / IIS 10)

### 1. Pré-requisitos
- IIS com URL Rewrite Module 2.x instalado
- PHP 8.0+ configurado como FastCGI
- MySQL / MariaDB instalado

### 2. Copiar ficheiros
```
C:\inetpub\wwwroot\dynamic_forms\
```

### 3. Configurar Site no IIS Manager
- Physical Path: C:\inetpub\wwwroot\dynamic_forms\public
- Application Pool: PHP 8.x, No Managed Code

### 4. Configurar config/config.php
```php
define('URLROOT', 'http://localhost/dynamic_forms/public');
```

### 5. Permissões NTFS (escrita apenas em storage\uploads)
```
icacls "C:\inetpub\wwwroot\dynamic_forms\storage\uploads" /grant "IIS AppPool\DefaultAppPool:(OI)(CI)W"
```

### 6. Criar a base de dados
Importar database.sql no MySQL/MariaDB.

---

## Segurança dos Uploads

Os ficheiros ficam em storage/uploads/ (FORA de public/) e são protegidos por:

| Servidor     | Protecção                              |
|--------------|----------------------------------------|
| Apache/XAMPP | storage/.htaccess com Require all denied |
| IIS          | storage/web.config com rule de bloqueio |

O acesso é feito APENAS via:  GET /download/{ficheiro}

Este endpoint verifica:
1. Utilizador autenticado
2. Admin ou dono do ficheiro
3. Nome sem path traversal
4. MIME type permitido (PDF, PNG, JPEG)

---

## Variáveis a Configurar em config/config.php

| Constante       | Descrição                          |
|-----------------|------------------------------------|
| URLROOT         | URL base da aplicação              |
| DB_HOST         | Host da base de dados              |
| DB_USER         | Utilizador da base de dados        |
| DB_PASS         | Senha da base de dados             |
| DB_NAME         | Nome da base de dados              |
| MAX_UPLOAD_SIZE | Tamanho máximo por ficheiro (bytes)|
