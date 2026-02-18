-- Novas modalidades: Diretoria, Externo, Suporte
-- Execute: mysql -u root -p crm_chamados < migrations/migration_roles_modalidades.sql

USE crm_chamados;

ALTER TABLE users MODIFY COLUMN role ENUM('admin','agent','requester','diretoria','externo','suporte') NOT NULL DEFAULT 'requester';
