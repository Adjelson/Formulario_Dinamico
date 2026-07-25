# Dynamic Forms 2.0

Sistema web para criação, publicação e gestão de formulários dinâmicos, desenvolvido em **PHP 8 + MySQL 8**, com arquitetura MVC simples. Esta versão corrige a base original, elimina ficheiros duplicados, reorganiza a base de dados e reforça funcionamento, segurança, desempenho e acessibilidade.

## Funcionalidades

### Administração

- Dashboard com totais e formulários recentes.
- Criar, editar, publicar, fechar e arquivar formulários.
- Perguntas de texto curto, texto longo, número, data, escolha única, escolha múltipla e upload.
- Configuração de datas mínima/máxima e tipos de ficheiro permitidos.
- Gestão de utilizadores e perfis `admin`/`user`.
- Consulta individual de respostas.
- Exportação em CSV ou ZIP com anexos.
- Logs de auditoria para ações importantes.

### Utilizador

- Registo e autenticação.
- Lista de formulários publicados.
- Submissão única por formulário.
- Histórico e detalhe das respostas.
- Possibilidade de arquivar a resposta anterior e preencher novamente.
- Upload de PDF, PNG e JPEG, quando permitido pelo formulário.

## Correções importantes desta versão

- Base de dados reconstruída e válida, sem dados pessoais do projeto original.
- Tipo de pergunta `date` incluído no esquema.
- Remoção da aplicação duplicada que existia em `core/app`.
- Correção da rota `deleteOwn` e do fluxo “eliminar e preencher novamente”.
- Bloqueio de respostas duplicadas no código e por restrição única no MySQL.
- Operações críticas protegidas por transações; falhas não deixam dados pela metade.
- Perguntas existentes são atualizadas sem eliminar automaticamente todo o histórico.
- Slugs repetidos recebem sufixo automático.
- Contas inativas não conseguem iniciar sessão.
- Proteção contra CSRF, enumeração de contas e tentativas excessivas de login.
- Validação no servidor para números, datas, opções e uploads.
- Neutralização de fórmulas maliciosas na exportação CSV.
- Remoção do antigo ficheiro público de diagnóstico.
- Configuração por `.env`, sem credenciais fixas no código.
- Uploads guardados fora da pasta pública e servidos após autorização.
- Pesquisa e paginação de utilizadores corrigidas.
- Mensagens, foco, labels, navegação por teclado e preferência por movimento reduzido melhorados.

## Requisitos

### Opção recomendada: Docker

- Docker Desktop com Docker Compose.

### Instalação manual

- PHP **8.1 ou superior**.
- MySQL **8.0 ou superior** ou MariaDB recente.
- Extensões PHP: `pdo_mysql`, `fileinfo`, `json` e `session`.
- Recomendadas: `mbstring`, `zip`, `gd` e `opcache`.
- Apache com `mod_rewrite`, IIS com URL Rewrite, ou servidor embutido do PHP.

## Instalação automática com Docker

### Windows

1. Extraia o projeto.
2. Abra o Docker Desktop.
3. Execute:

```text
INICIAR_DOCKER.bat
```

O script cria o `.env`, constrói os contentores, prepara a base de dados, verifica `/health` e abre a aplicação.

### Linux ou macOS

```bash
chmod +x INICIAR_DOCKER.sh
./INICIAR_DOCKER.sh
```

### Comandos equivalentes

```bash
cp .env.example .env
docker compose up --build -d
```

A aplicação fica disponível em:

```text
http://localhost:8080
```

Verificação de saúde:

```text
http://localhost:8080/health
```

MySQL no computador anfitrião:

```text
localhost:3307
```

### Parar

No Windows, execute `PARAR_DOCKER.bat`. Pelo terminal:

```bash
docker compose down
```

Para apagar também os dados e recriar tudo do zero:

```bash
docker compose down -v
docker compose up --build -d
```

> `docker compose down -v` elimina definitivamente a base e os uploads guardados nos volumes Docker.

## Contas de demonstração

| Perfil | Email | Palavra-passe |
|---|---|---|
| Administrador | `admin@example.com` | `Admin@123` |
| Utilizador | `utilizador@example.com` | `Admin@123` |

Altere estas credenciais antes de publicar o sistema.

## Instalação manual com XAMPP/WAMP

1. Copie a pasta para `htdocs`, por exemplo:

```text
C:\xampp\htdocs\dynamic-forms
```

2. Copie o ficheiro de configuração:

```powershell
Copy-Item .env.example .env
```

3. Ajuste o `.env`:

```env
APP_ENV=development
APP_URL=http://localhost/dynamic-forms
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=dynamic_forms
DB_USER=root
DB_PASS=
```

4. No phpMyAdmin, importe por esta ordem:

```text
database/schema.sql
database/seed.sql
```

5. Confirme que o Apache permite `mod_rewrite` e que `AllowOverride All` está ativo.
6. Garanta permissão de escrita em:

```text
storage/uploads
storage/covers
storage/logs
```

7. Abra:

```text
http://localhost/dynamic-forms
```

### Servidor embutido do PHP

Depois de configurar o MySQL e o `.env`:

```bash
php -S localhost:8080 -t public public/router.php
```

## Configuração `.env`

| Variável | Descrição | Exemplo |
|---|---|---|
| `APP_ENV` | `development` ou `production` | `production` |
| `APP_URL` | URL pública sem barra final | `https://formularios.exemplo.pt` |
| `DB_HOST` | Servidor MySQL | `127.0.0.1` |
| `DB_PORT` | Porta interna do MySQL | `3306` |
| `DB_NAME` | Nome da base | `dynamic_forms` |
| `DB_USER` | Utilizador da base | `dynamic_forms` |
| `DB_PASS` | Palavra-passe da base | valor seguro |
| `SESSION_LIFETIME` | Inatividade máxima em segundos | `7200` |
| `MAX_UPLOAD_SIZE` | Limite de anexos em bytes | `5242880` |
| `MAX_COVER_SIZE` | Limite da capa em bytes | `2097152` |
| `TRUST_PROXY` | Confiar em `X-Forwarded-*` | `false` |

Em produção:

```env
APP_ENV=production
APP_URL=https://seu-dominio.pt
DB_PERSISTENT=false
TRUST_PROXY=false
```

Use palavras-passe fortes e não envie o `.env` para o Git.

## Estrutura

```text
app/
  controllers/       Controladores HTTP
  models/            Consultas e regras de persistência
  views/             Interfaces administrativas e públicas
config/               Configuração e leitura do .env
core/                 Router, Controller, Model, Database e segurança
database/
  schema.sql          Instalação limpa
  seed.sql            Dados de demonstração
  migrate_from_v1.sql Apoio à migração da versão antiga
docker/               Apache e PHP para Docker
public/               Front controller, CSS, JS e recursos públicos
storage/
  uploads/            Anexos privados
  covers/             Imagens de capa
  logs/               Logs PHP
tests/                 Testes sem dependência da base
scripts/               Validação de sintaxe e testes
```

## Segurança aplicada

- Passwords com `password_hash()` e `password_verify()`.
- Cookies de sessão `HttpOnly`, `SameSite=Lax` e `Secure` em HTTPS.
- Regeneração do ID após login.
- Encerramento por inatividade.
- CSRF em todas as ações de escrita.
- Prepared statements e emulação de prepares desativada.
- `utf8mb4` na ligação e nas tabelas.
- Bloqueio temporário e rate limiting de login.
- Cabeçalhos CSP, `X-Frame-Options`, `nosniff` e `Referrer-Policy`.
- Verificação do MIME real, tamanho e nome aleatório nos uploads.
- Download condicionado ao proprietário ou administrador.
- Validação duplicada no browser e no servidor.
- Arquivo de formulários, perguntas, respostas e utilizadores antes de remoções lógicas.

## Melhorias de desempenho

- Índices para login, formulários, perguntas, respostas, logs e ficheiros.
- Consultas agregadas para totais e dashboard.
- Uma única consulta para identificar formulários já respondidos.
- Carregamento em lote das respostas durante exportações, evitando N+1.
- OPcache ativado no contentor PHP.
- Imagens de capa com `ETag` e cache.
- Bloqueio de duplo envio no cliente e restrição única na base.
- PDO partilhado entre models durante o mesmo pedido, permitindo transações consistentes.

Para instalações com muitos dados, implemente paginação no servidor para listas administrativas e uma rotina de retenção para `audit_logs` e `login_attempts`.

## Acessibilidade

- Documento em `pt-PT` e link “Saltar para o conteúdo principal”.
- Labels e descrições associadas aos campos.
- Regiões `aria-live` para mensagens e erros.
- Foco deslocado para o primeiro erro.
- Estados `aria-invalid` durante validação.
- Botões com nomes acessíveis e navegação por teclado.
- Tabelas com legenda oculta para leitores de ecrã.
- Contraste e foco visível.
- Layout responsivo.
- Animações reduzidas quando `prefers-reduced-motion` está ativo.

## Validação do projeto

Execute:

### Linux/macOS

```bash
./scripts/check.sh
```

### Windows

```text
scripts\check.bat
```

Ou apenas os testes:

```bash
php tests/run.php
```

A validação incluída verifica helpers de segurança, CSRF, URLs internas, CSV, escaping, router e passwords.

## Migração da versão antiga

Faça primeiro um backup integral da base e dos diretórios `storage/uploads` e `storage/covers`.

Para um projeto ainda sem dados importantes, a instalação limpa com `schema.sql` e `seed.sql` é a opção mais segura.

Para tentar preservar uma base antiga:

1. Resolva respostas duplicadas do mesmo utilizador para o mesmo formulário.
2. Faça backup.
3. Execute `database/migrate_from_v1.sql` numa cópia da base.
4. Compare contagens de utilizadores, formulários, respostas e anexos.
5. Teste login, edição, submissão, histórico e exportação antes de substituir a produção.

O script de migração não substitui uma revisão dos dados antigos, pois dumps diferentes podem ter nomes de constraints diferentes.

## Resolução de problemas

### “Não foi possível ligar à base de dados”

- Confirme MySQL ativo.
- Reveja `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` e `DB_PASS`.
- No Docker, use `DB_HOST=mysql`, não `localhost`.
- Confirme a extensão `pdo_mysql` com `php -m`.

### Erro 404 em todas as rotas

- Ative `mod_rewrite` no Apache.
- Permita `.htaccess` com `AllowOverride All`.
- Em IIS, instale URL Rewrite.
- Como alternativa, use o servidor embutido com `public/router.php`.

### Upload falha

- Confirme permissões de escrita em `storage`.
- Verifique `upload_max_filesize` e `post_max_size` no PHP.
- O limite da aplicação deve ser igual ou inferior ao limite do PHP.
- Formatos aceites: PDF, PNG e JPEG; capas: JPG, PNG e WEBP.

### O Docker inicia, mas a base antiga continua

Os scripts de inicialização MySQL só executam quando o volume está vazio. Para uma reinstalação limpa:

```bash
docker compose down -v
docker compose up --build -d
```

### Ver logs

```bash
docker compose logs -f app
docker compose logs -f mysql
```

No modo manual, consulte:

```text
storage/logs/php_errors.log
```

## Produção

Antes de publicar:

- altere as contas e passwords de demonstração;
- use HTTPS;
- defina `APP_ENV=production`;
- configure backups automáticos da base e de `storage`;
- limite acesso ao endpoint `/health` na infraestrutura, se necessário;
- configure envio de logs para um serviço seguro;
- reveja a política de retenção de dados e consentimento;
- faça testes de carga e segurança no ambiente final.

## Estado da validação entregue

- 41 ficheiros PHP sem erros de sintaxe.
- JavaScript próprio sem erros de sintaxe.
- 16 testes automatizados aprovados.
- Rotas verificadas contra os métodos públicos dos controladores.
- `docker-compose.yml` validado como YAML.
- Views principais renderizadas com dados simulados, incluindo o retorno após erros de validação.

A execução integral com MySQL e navegador deve ser feita no seu computador através do Docker, porque o ambiente usado para esta revisão não dispõe de Docker, servidor MySQL nem extensão `pdo_mysql`.
