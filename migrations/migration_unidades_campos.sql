-- Adiciona campos de endereço e centro de lucro à tabela unidades
-- Execute: mysql -u root -p crm_chamados < migration_unidades_campos.sql
-- Execute apenas uma vez. Se as colunas já existirem, ignore os erros.
USE crm_chamados;

ALTER TABLE unidades ADD COLUMN endereco VARCHAR(255) NULL AFTER sigla;
ALTER TABLE unidades ADD COLUMN cidade VARCHAR(120) NULL AFTER endereco;
ALTER TABLE unidades ADD COLUMN uf VARCHAR(5) NULL AFTER cidade;
ALTER TABLE unidades ADD COLUMN cep VARCHAR(20) NULL AFTER uf;
ALTER TABLE unidades ADD COLUMN centro_de_lucro VARCHAR(120) NULL AFTER cep;
