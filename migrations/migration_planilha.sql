-- Campos da planilha CONTROLE DE FECHAMENTO no ticket
-- Execute: mysql -u root -p crm_chamados < migration_planilha.sql

USE crm_chamados;

ALTER TABLE tickets
    ADD COLUMN cliente_raiz VARCHAR(120) NULL AFTER closed_at,
    ADD COLUMN cliente VARCHAR(120) NULL AFTER cliente_raiz,
    ADD COLUMN sigla_unidade_loja_ag VARCHAR(100) NULL AFTER cliente,
    ADD COLUMN tipo_contrato VARCHAR(80) NULL AFTER sigla_unidade_loja_ag,
    ADD COLUMN tipo_ch VARCHAR(80) NULL AFTER tipo_contrato,
    ADD COLUMN contrato_baseline VARCHAR(80) NULL AFTER tipo_ch,
    ADD COLUMN sla VARCHAR(80) NULL AFTER contrato_baseline,
    ADD COLUMN reversa VARCHAR(80) NULL AFTER sla,
    ADD COLUMN codigo_postagem VARCHAR(80) NULL AFTER reversa,
    ADD COLUMN data_postagem DATE NULL AFTER codigo_postagem,
    ADD COLUMN equipamento VARCHAR(120) NULL AFTER data_postagem,
    ADD COLUMN n_serie VARCHAR(80) NULL AFTER equipamento,
    ADD COLUMN patrimonio VARCHAR(80) NULL AFTER n_serie,
    ADD COLUMN hostname VARCHAR(120) NULL AFTER patrimonio,
    ADD COLUMN usuario_contato VARCHAR(120) NULL AFTER hostname,
    ADD COLUMN telefone_usuario VARCHAR(50) NULL AFTER usuario_contato,
    ADD COLUMN email_usuario VARCHAR(190) NULL AFTER telefone_usuario,
    ADD COLUMN nome_solicitante_ch VARCHAR(120) NULL AFTER email_usuario,
    ADD COLUMN numero_ch VARCHAR(80) NULL AFTER nome_solicitante_ch,
    ADD COLUMN moebius VARCHAR(80) NULL AFTER numero_ch,
    ADD COLUMN n_cl VARCHAR(80) NULL AFTER moebius,
    ADD COLUMN n_tarefa_remessa VARCHAR(80) NULL AFTER n_cl,
    ADD COLUMN endereco VARCHAR(255) NULL AFTER n_tarefa_remessa,
    ADD COLUMN bairro VARCHAR(100) NULL AFTER endereco,
    ADD COLUMN cidade VARCHAR(100) NULL AFTER bairro,
    ADD COLUMN uf VARCHAR(5) NULL AFTER cidade,
    ADD COLUMN cep VARCHAR(20) NULL AFTER uf,
    ADD COLUMN data_vencimento_ch DATE NULL AFTER cep,
    ADD COLUMN data_disponibilidade DATE NULL AFTER data_vencimento_ch,
    ADD COLUMN hora_disponibilidade TIME NULL AFTER data_disponibilidade,
    ADD COLUMN operacao VARCHAR(80) NULL AFTER hora_disponibilidade,
    ADD COLUMN intercorrencias TEXT NULL AFTER operacao,
    ADD COLUMN observacao_tecnico TEXT NULL AFTER intercorrencias;
