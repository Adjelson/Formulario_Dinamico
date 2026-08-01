# 📝 Dynamic Forms 2.0

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.1+" />
  <img src="https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL 8.0+" />
  <img src="https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white" alt="Docker" />
  <img src="https://img.shields.io/badge/MVC_Architecture-000000?style=for-the-badge" alt="Arquitetura MVC" />
  <img src="https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white" alt="HTML5" />
  <img src="https://img.shields.io/badge/JavaScript-Vanilla-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black" alt="JavaScript Vanilla" />
</p>

> Sistema web para criação, publicação e gestão de formulários dinâmicos, desenvolvido em **PHP 8 + MySQL 8**, com arquitetura MVC simples. Esta versão corrige a base original, elimina ficheiros duplicados, reorganiza a base de dados e reforça funcionamento, segurança, desempenho e acessibilidade.

---

## ✨ Funcionalidades

### ⚙️ Administração

- Dashboard com totais e formulários recentes.
- Criar, editar, publicar, fechar e arquivar formulários.
- Perguntas de texto curto, texto longo, número, data, escolha única, escolha múltipla e upload.
- Configuração de datas mínima/máxima e tipos de ficheiro permitidos.
- Gestão de utilizadores e perfis `admin`/`user`.
- Consulta individual de respostas.
- Exportação em CSV ou ZIP com anexos.
- Logs de auditoria para ações importantes.

### 👤 Utilizador

- Registo e autenticação.
- Lista de formulários publicados.
- Submissão única por formulário.
- Histórico e detalhe das respostas.
- Possibilidade de arquivar a resposta anterior e preencher novamente.
- Upload de PDF, PNG e JPEG, quando permitido pelo formulário.

---

## 🛠️ Correções importantes desta versão

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

---

## 📦 Requisitos

### 🐳 Opção recomendada: Docker
- Docker Desktop com Docker Compose.

### 💻 Instalação manual
- PHP **8.1 ou superior**.
- MySQL **8.0 ou superior** ou MariaDB recente.
- Extensões PHP: `pdo_mysql`, `fileinfo`, `json` e `session`.
- Recomendadas: `mbstring`, `zip`, `gd` e `opcache`.
- Apache com `mod_rewrite`, IIS com URL Rewrite, ou servidor embutido do PHP.

---

## 🚀 Instalação automática com Docker

### 🪟 Windows

1. Extraia o projeto.
2. Abra o Docker Desktop.
3. Execute:
```text
INICIAR_DOCKER.bat
