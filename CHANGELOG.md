# Changelog

## 2.0.0 — 25 de julho de 2026

### Funcionamento

- Reunificada a aplicação numa única estrutura MVC.
- Corrigidos router, `deleteOwn`, formulários já respondidos e histórico.
- Adicionado suporte completo a perguntas de data.
- Implementadas transações em formulários, respostas e desativação de utilizadores.
- Perguntas passam a ser sincronizadas e mantêm IDs quando editadas.
- Corrigido o reaproveitamento das perguntas após erro de validação.
- Corrigida pesquisa/paginação de utilizadores.
- Adicionados slugs únicos automáticos.
- Adicionada verificação `/health`.

### Base de dados

- Novo esquema limpo em `database/schema.sql`.
- Dados de demonstração em `database/seed.sql`.
- Resposta única por utilizador/formulário.
- Chaves estrangeiras seguras para preservar histórico de utilizadores.
- Índices para os fluxos principais.
- Tabelas de arquivo, tentativas de login e auditoria.

### Segurança

- CSRF em todos os POST.
- Login com bloqueio temporário e limite por email/IP.
- Contas inativas impedidas de entrar.
- Passwords com política mínima coerente.
- Validação de MIME, tamanho e permissões dos uploads.
- Proteção contra CSV injection.
- Cabeçalhos CSP e cookies de sessão reforçados.
- Removido o diagnóstico público e dados pessoais do pacote.

### Desempenho

- Consultas agregadas e carregamento em lote nas exportações.
- PDO partilhado por pedido.
- Índices adicionais.
- OPcache no Docker.
- Cache/ETag das capas.

### Acessibilidade

- Labels e descrições associadas.
- Link para saltar conteúdo.
- `aria-live`, `aria-invalid`, foco em erros e nomes acessíveis.
- Paginação por botões e teclado.
- Suporte a movimento reduzido e foco visível.

### Instalação

- Dockerfile e Docker Compose.
- Scripts automáticos para Windows, Linux e macOS.
- `.env.example`, `.dockerignore` e README refeito.
- Testes e scripts de verificação.
