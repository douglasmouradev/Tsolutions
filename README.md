# CRM Chamados

Sistema de chamados (tickets) estilo CRM em PHP nativo com arquitetura MVC.

## Requisitos

- PHP 8.3+
- MySQL 8.0+
- Composer

## Setup

1. Clone o repositório e entre na pasta do projeto.

2. Copie o arquivo de ambiente e preencha as credenciais:
   ```bash
   cp .env.example .env
   ```
   Edite `.env` e configure:
   - `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` - credenciais do MySQL
   - `APP_URL` - URL base da aplicação (ex: http://localhost:8000)
   - `TIMEZONE` - fuso horário para exibição (ex: America/Sao_Paulo)

3. Crie o banco de dados e execute o schema:
   ```bash
   mysql -u root -p < database.sql
   ```
   Ou importe manualmente o conteúdo de `database.sql` no MySQL.

4. Instale as dependências:
   ```bash
   composer install
   ```

5. Inicie o servidor PHP:
   ```bash
   composer serve
   ```
   Ou:
   ```bash
   php -S localhost:8000 -t public
   ```

   Para Apache/Nginx, configure o document root apontando para a pasta `public/`.

## Login

- **Admin:** admin@local.test / Admin@123
- **Agentes:** agent1@local.test, agent2@local.test / User@123
- **Solicitantes:** requester1@local.test, requester2@local.test, requester3@local.test / User@123

**Importante:** altere as senhas após o primeiro login em ambiente de produção.

## Funcionalidades

- **Autenticação:** login, logout, rate limit (5 tentativas/15 min), CSRF
- **Dashboard:** contagem por status (aberto, em andamento, fechado, cancelado) e últimos 10 chamados
- **Chamados:** CRUD, atribuição a agente, comentários, anexos, histórico de status
- **Filtros e busca:** status, prioridade, categoria, agente, solicitante, período, busca textual, paginação
- **Permissões:** admin (total), agent (gerencia chamados atribuídos), requester (cria e vê seus chamados)

## Segurança

- Senhas com `password_hash`/`password_verify`
- CSRF em todos os POSTs
- Escape de saída (XSS) com helper `e()`
- Prepared statements (anti-SQLi)
- Validação server-side
- Uploads: validação de MIME real, extensões whitelist, arquivos fora do webroot

## Configuração

- **MAX_UPLOAD_MB:** tamanho máximo de anexos (padrão 10)
- **ALLOW_REQUESTER_CLOSE:** permitir que solicitante feche/cancele seus chamados (1 ou 0)
- **TIMEZONE:** fuso para exibição de datas (ex: America/Sao_Paulo). Datas são armazenadas em UTC no banco.

## Estrutura

```
/
├── app/
│   ├── Controllers/
│   ├── Models/
│   ├── Views/
│   ├── Middlewares/
│   ├── Services/
│   └── helpers.php
├── config/
├── public/
│   ├── index.php
│   └── assets/
├── storage/
│   ├── logs/
│   └── uploads/
├── database.sql
└── composer.json
```

## Observações

- Anexos são armazenados em `storage/uploads/` (fora do webroot). O download é feito via controller.
- Logs são gravados em `storage/logs/app.log`.
- O sistema usa sessões com cookies HttpOnly e SameSite=Lax.
