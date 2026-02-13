-- Adiciona campo bairro à tabela unidades
-- Execute: mysql -u root -p crm_chamados < migration_unidades_bairro.sql

USE crm_chamados;

ALTER TABLE unidades ADD COLUMN bairro VARCHAR(100) NULL AFTER endereco;
