-- Campos do técnico e atendimento no chamado
-- Execute: mysql -u root -p crm_chamados < migration_tecnico_atendimento.sql

USE crm_chamados;

ALTER TABLE tickets ADD COLUMN nome_tecnico VARCHAR(120) NULL AFTER observacao_tecnico;
ALTER TABLE tickets ADD COLUMN cpf_tecnico VARCHAR(20) NULL AFTER nome_tecnico;
ALTER TABLE tickets ADD COLUMN rg_tecnico VARCHAR(20) NULL AFTER cpf_tecnico;
ALTER TABLE tickets ADD COLUMN data_atendimento DATE NULL AFTER rg_tecnico;
ALTER TABLE tickets ADD COLUMN hora_atendimento TIME NULL AFTER data_atendimento;
