-- Sistema CRM Chamados - Schema + Seed
-- MySQL 8.0+, utf8mb4

CREATE DATABASE IF NOT EXISTS crm_chamados CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE crm_chamados;

-- Tabela de usuários
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','agent','requester') NOT NULL DEFAULT 'requester',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de categorias
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de chamados
CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    requester_id BIGINT UNSIGNED NOT NULL,
    agent_id BIGINT UNSIGNED NULL,
    category_id INT UNSIGNED NULL,
    priority ENUM('baixa','media','alta','critica') NOT NULL DEFAULT 'baixa',
    status ENUM('aberto','em_andamento','fechado','cancelado') NOT NULL DEFAULT 'aberto',
    due_at DATETIME NULL,
    closed_at DATETIME NULL,
    cliente_raiz VARCHAR(120) NULL,
    cliente VARCHAR(120) NULL,
    sigla_unidade_loja_ag VARCHAR(100) NULL,
    tipo_contrato VARCHAR(80) NULL,
    tipo_ch VARCHAR(80) NULL,
    contrato_baseline VARCHAR(80) NULL,
    sla VARCHAR(80) NULL,
    reversa VARCHAR(80) NULL,
    codigo_postagem VARCHAR(80) NULL,
    data_postagem DATE NULL,
    equipamento VARCHAR(120) NULL,
    n_serie VARCHAR(80) NULL,
    patrimonio VARCHAR(80) NULL,
    hostname VARCHAR(120) NULL,
    usuario_contato VARCHAR(120) NULL,
    telefone_usuario VARCHAR(50) NULL,
    email_usuario VARCHAR(190) NULL,
    nome_solicitante_ch VARCHAR(120) NULL,
    numero_ch VARCHAR(80) NULL,
    moebius VARCHAR(80) NULL,
    n_cl VARCHAR(80) NULL,
    n_tarefa_remessa VARCHAR(80) NULL,
    endereco VARCHAR(255) NULL,
    bairro VARCHAR(100) NULL,
    cidade VARCHAR(100) NULL,
    uf VARCHAR(5) NULL,
    cep VARCHAR(20) NULL,
    data_vencimento_ch DATE NULL,
    data_disponibilidade DATE NULL,
    hora_disponibilidade TIME NULL,
    operacao VARCHAR(80) NULL,
    intercorrencias TEXT NULL,
    observacao_tecnico TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_agent_id (agent_id),
    INDEX idx_requester_id (requester_id),
    INDEX idx_created_at (created_at),
    INDEX idx_updated_at (updated_at),
    FULLTEXT idx_fulltext (title, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Histórico de mudanças de status
CREATE TABLE ticket_status_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    old_status ENUM('aberto','em_andamento','fechado','cancelado') NULL,
    new_status ENUM('aberto','em_andamento','fechado','cancelado') NOT NULL,
    changed_by BIGINT UNSIGNED NOT NULL,
    note VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comentários
CREATE TABLE ticket_comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anexos
CREATE TABLE attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_ticket_id (ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rate limit para login
CREATE TABLE auth_rate_limit (
    key_hash VARCHAR(64) PRIMARY KEY,
    attempts INT UNSIGNED NOT NULL DEFAULT 0,
    first_attempt DATETIME NOT NULL,
    INDEX idx_first_attempt (first_attempt)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tokens para redefinição de senha
CREATE TABLE password_reset_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========== SEED ==========

-- Admin (admin@local.test / Admin@123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Administrador', 'admin@local.test', '$2y$12$.f84WSh5is1OEH8Yn6OAKe63p1pb8cN2Ilz7zqyMwpXoYGFck9zqi', 'admin');

-- Agents (agent1@local.test, agent2@local.test / User@123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Maria Agente', 'agent1@local.test', '$2y$12$.iyGrhUimWpegsr0bRzjwuXAtufP5boYDjNmcMEx40Pu5ROObVXGa', 'agent'),
('João Agente', 'agent2@local.test', '$2y$12$.iyGrhUimWpegsr0bRzjwuXAtufP5boYDjNmcMEx40Pu5ROObVXGa', 'agent');

-- Requesters (requester1@local.test, requester2@local.test, requester3@local.test / User@123)
INSERT INTO users (name, email, password_hash, role) VALUES
('Carlos Solicitante', 'requester1@local.test', '$2y$12$.iyGrhUimWpegsr0bRzjwuXAtufP5boYDjNmcMEx40Pu5ROObVXGa', 'requester'),
('Ana Solicitante', 'requester2@local.test', '$2y$12$.iyGrhUimWpegsr0bRzjwuXAtufP5boYDjNmcMEx40Pu5ROObVXGa', 'requester'),
('Pedro Solicitante', 'requester3@local.test', '$2y$12$.iyGrhUimWpegsr0bRzjwuXAtufP5boYDjNmcMEx40Pu5ROObVXGa', 'requester');

-- Categorias
INSERT INTO categories (name) VALUES
('Suporte'),
('Infra'),
('Sistemas');

-- Tickets de exemplo
INSERT INTO tickets (title, description, requester_id, agent_id, category_id, priority, status, due_at) VALUES
('Erro ao acessar painel', 'Não consigo fazer login no painel administrativo. Mensagem de erro 500.', 4, 2, 1, 'alta', 'aberto', DATE_ADD(NOW(), INTERVAL 3 DAY)),
('Servidor lento', 'O servidor de produção está respondendo muito lento nas últimas horas.', 4, 2, 2, 'critica', 'em_andamento', DATE_ADD(NOW(), INTERVAL 1 DAY)),
('Solicitação de novo usuário', 'Preciso criar acesso para o novo funcionário do RH.', 5, 3, 1, 'baixa', 'aberto', DATE_ADD(NOW(), INTERVAL 7 DAY)),
('Integração API falhou', 'A integração com o gateway de pagamento não está retornando resposta.', 6, 3, 3, 'alta', 'em_andamento', DATE_ADD(NOW(), INTERVAL 2 DAY)),
('Backup não executou', 'O backup agendado das 02h não foi executado ontem.', 4, 2, 2, 'media', 'aberto', DATE_ADD(NOW(), INTERVAL 5 DAY)),
('Alterar layout do relatório', 'Precisamos alterar o cabeçalho do relatório mensal.', 5, NULL, 3, 'baixa', 'aberto', DATE_ADD(NOW(), INTERVAL 14 DAY)),
('Senha expirada', 'Minha senha expirou e não consigo redefinir.', 6, 3, 1, 'media', 'fechado', NULL),
('Instalar novo software', 'Solicito instalação do Microsoft Teams no meu computador.', 4, 2, 1, 'baixa', 'fechado', NULL),
('Erro na impressora', 'A impressora do 3º andar não está imprimindo.', 5, NULL, 2, 'media', 'aberto', DATE_ADD(NOW(), INTERVAL 3 DAY)),
('Timeout no sistema', 'O sistema está dando timeout ao gerar relatórios grandes.', 6, 3, 3, 'alta', 'em_andamento', DATE_ADD(NOW(), INTERVAL 2 DAY)),
('Acesso negado ao módulo', 'Recebo erro de permissão ao acessar o módulo de vendas.', 4, 2, 3, 'media', 'fechado', NULL),
('Troca de equipamento', 'Solicito troca do notebook por modelo mais recente.', 5, NULL, 1, 'baixa', 'cancelado', NULL),
('Atualização do antivírus', 'O antivírus está desatualizado em vários máquinas.', 6, 3, 2, 'media', 'aberto', DATE_ADD(NOW(), INTERVAL 4 DAY)),
('Dashboard não carrega', 'O dashboard principal não carrega os gráficos.', 4, 2, 3, 'alta', 'em_andamento', DATE_ADD(NOW(), INTERVAL 1 DAY)),
('Configuração de email', 'Preciso configurar o email corporativo no Outlook.', 5, 3, 1, 'baixa', 'fechado', NULL);

-- Histórico para tickets fechados/cancelados
UPDATE tickets SET closed_at = NOW(), updated_at = NOW() WHERE status IN ('fechado', 'cancelado');

INSERT INTO ticket_status_history (ticket_id, old_status, new_status, changed_by, note) VALUES
(7, 'em_andamento', 'fechado', 3, 'Problema resolvido - senha redefinida'),
(8, 'em_andamento', 'fechado', 2, 'Software instalado com sucesso'),
(11, 'aberto', 'fechado', 2, 'Permissões corrigidas'),
(12, 'aberto', 'cancelado', 5, 'Solicitação desnecessária');

-- Comentários de exemplo
INSERT INTO ticket_comments (ticket_id, author_id, body) VALUES
(1, 4, 'Já tentei limpar o cache do navegador mas o problema persiste.'),
(2, 2, 'Investigando os logs do servidor. Possível problema de memória.'),
(2, 4, 'Obrigado, avisem quando resolver.'),
(4, 3, 'Aguardando resposta do suporte do gateway.'),
(10, 3, 'Otimizei a query do relatório. Testando em homologação.');
