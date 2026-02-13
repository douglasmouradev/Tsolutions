-- Obrigar alteração de senha no primeiro login (perfis criados pelo admin)
-- Execute: mysql -u root -p crm_chamados < migration_must_change_password.sql

USE crm_chamados;

ALTER TABLE users
    ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER is_active;
