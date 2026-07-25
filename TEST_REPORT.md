# Relatório de validação

Data: 25 de julho de 2026

## Resultados

- PHP lint: **41 ficheiros aprovados**.
- JavaScript: ficheiros próprios e bibliotecas locais sem erro de sintaxe.
- Testes PHP: **16 aprovados, 0 falhas**.
- Rotas: todas as ações configuradas existem nos respetivos controladores.
- Docker Compose: YAML válido.
- Renderização simulada: criação, edição com objetos, edição após validação com arrays, preenchimento e gestão de utilizadores aprovados.
- Verificação de ficheiros: dados pessoais e uploads antigos removidos.

## Testes automatizados cobertos

- Criação e validação de token CSRF.
- Bloqueio de redirecionamento externo e path traversal.
- Neutralização de fórmulas CSV.
- Escape HTML.
- Correspondência e rejeição de rotas.
- Verificação de password correta e incorreta.

## Limitação do ambiente

Não foi possível executar um teste integral com MySQL, Apache/Docker e browser porque o ambiente de revisão não possui Docker, servidor MySQL nem `pdo_mysql`. A versão inclui Docker para que esse teste seja reproduzido localmente com `INICIAR_DOCKER.bat` ou `docker compose up --build -d`.

## Checklist manual recomendado

1. Entrar como administrador.
2. Criar formulário com todos os tipos de pergunta.
3. Editar e reordenar perguntas.
4. Registar utilizador e responder.
5. Confirmar bloqueio de segunda resposta.
6. Ver histórico, descarregar anexo e preencher novamente.
7. Exportar CSV e ZIP.
8. Desativar utilizador e confirmar bloqueio de login.
9. Arquivar resposta e formulário.
10. Confirmar navegação apenas com teclado e mensagens no leitor de ecrã.
