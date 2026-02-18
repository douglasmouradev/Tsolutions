-- Valor do técnico e modalidade (Chamado/Diária)
-- Execute: mysql -u root -p crm_chamados < migrations/migration_valor_modalidade_tecnico.sql

USE crm_chamados;

ALTER TABLE tickets ADD COLUMN valor_tecnico DECIMAL(10,2) NULL AFTER hora_atendimento;
ALTER TABLE tickets ADD COLUMN modalidade_tecnico VARCHAR(20) NULL AFTER valor_tecnico;
