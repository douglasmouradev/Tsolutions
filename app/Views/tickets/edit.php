<?php
$pageTitle = 'Editar chamado #' . $ticket['id'];
$val = fn($k) => e($ticket[$k] ?? '');
$valDate = fn($k) => !empty($ticket[$k]) ? date('Y-m-d', strtotime($ticket[$k])) : '';
$valTime = fn($k) => !empty($ticket[$k]) ? date('H:i', strtotime($ticket[$k])) : '';
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Editar chamado #<?= e((string) $ticket['id']) ?></h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <form method="post" action="/tickets/<?= $ticket['id'] ?>/update">
        <?= csrf_field() ?>

        <div class="card mb-3">
            <div class="card-header"><strong>Dados do chamado</strong></div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="title" class="form-label">Título *</label>
                    <input type="text" name="title" id="title" class="form-control" required minlength="3" value="<?= $val('title') ?>">
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Descrição</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= $val('description') ?></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="category_id" class="form-label">Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Selecione</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (string)($ticket['category_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="priority" class="form-label">Prioridade</label>
                        <select name="priority" id="priority" class="form-select">
                            <option value="baixa" <?= ($ticket['priority'] ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                            <option value="media" <?= ($ticket['priority'] ?? '') === 'media' ? 'selected' : '' ?>>Média</option>
                            <option value="alta" <?= ($ticket['priority'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="critica" <?= ($ticket['priority'] ?? '') === 'critica' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="sla_prazo" class="form-label">Prazo SLA</label>
                        <select name="sla_prazo" id="sla_prazo" class="form-select">
                            <?php $slaPrazoVal = $ticket['sla_prazo'] ?? ''; $slaPrazoVal = in_array($slaPrazoVal, ['prioridade','nbd','d1','d2','8','personalizado'], true) ? $slaPrazoVal : 'personalizado'; ?>
                            <option value="prioridade" <?= $slaPrazoVal === 'prioridade' ? 'selected' : '' ?>>Padrão da prioridade</option>
                            <option value="nbd" <?= $slaPrazoVal === 'nbd' ? 'selected' : '' ?>>NBD</option>
                            <option value="d1" <?= $slaPrazoVal === 'd1' ? 'selected' : '' ?>>D+1</option>
                            <option value="d2" <?= $slaPrazoVal === 'd2' ? 'selected' : '' ?>>D+2</option>
                            <option value="8" <?= $slaPrazoVal === '8' ? 'selected' : '' ?>>8 Hrs</option>
                            <option value="personalizado" <?= $slaPrazoVal === 'personalizado' ? 'selected' : '' ?>>Data personalizada</option>
                        </select>
                        <div class="mt-2">
                            <label for="due_at" class="form-label small">Data e hora do vencimento</label>
                            <input type="datetime-local" name="due_at" id="due_at" class="form-control" value="<?= !empty($ticket['due_at']) ? date('Y-m-d\TH:i', strtotime($ticket['due_at'])) : '' ?>">
                        </div>
                    </div>
                </div>
                <?php if ($currentUser && in_array($currentUser['role'], ['admin', 'agent', 'diretoria', 'suporte'], true)): ?>
                <div class="mb-3">
                    <label for="agent_id" class="form-label">Agente</label>
                    <select name="agent_id" id="agent_id" class="form-select">
                        <option value="">Nenhum</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (int)($ticket['agent_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-cliente" style="cursor:pointer"><strong>Cliente / Contrato</strong></div>
            <div id="sec-cliente" class="card-body collapse">
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Unidade</label>
                        <select id="unidade_select" class="form-select form-select-sm">
                            <option value="">Selecione a unidade...</option>
                            <?php foreach ($unidades ?? [] as $u): ?>
                            <option value="<?= e((string) $u['id']) ?>" data-endereco="<?= e($u['endereco'] ?? '') ?>" data-cidade="<?= e($u['cidade'] ?? '') ?>" data-uf="<?= e($u['uf'] ?? '') ?>" data-cep="<?= e($u['cep'] ?? '') ?>" data-nome="<?= e($u['name']) ?>"><?= e($u['name']) ?><?= !empty($u['sigla']) ? ' (' . e($u['sigla']) . ')' : '' ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Ao selecionar, preenche endereço, cidade e CEP</small>
                    </div>
                    <div class="col-md-4 mb-2"><label class="form-label">Cliente raiz</label><input type="text" name="cliente_raiz" class="form-control form-control-sm" value="<?= $val('cliente_raiz') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Cliente</label><input type="text" name="cliente" class="form-control form-control-sm" value="<?= $val('cliente') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Sigla/Unidade/Loja/AG</label><input type="text" name="sigla_unidade_loja_ag" id="sigla_unidade_loja_ag" class="form-control form-control-sm" value="<?= $val('sigla_unidade_loja_ag') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Tipo de contrato</label><input type="text" name="tipo_contrato" class="form-control form-control-sm" value="<?= $val('tipo_contrato') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Tipo de CH</label><input type="text" name="tipo_ch" class="form-control form-control-sm" value="<?= $val('tipo_ch') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Contrato baseline</label><input type="text" name="contrato_baseline" class="form-control form-control-sm" value="<?= $val('contrato_baseline') ?>"></div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">SLA</label>
                        <select name="sla" class="form-select form-select-sm">
                            <option value="">Selecione</option>
                            <option value="NBD (Úteis)" <?= ($ticket['sla'] ?? '') === 'NBD (Úteis)' ? 'selected' : '' ?>>NBD (Úteis)</option>
                            <option value="D+1 (Úteis)" <?= ($ticket['sla'] ?? '') === 'D+1 (Úteis)' ? 'selected' : '' ?>>D+1 (Úteis)</option>
                            <option value="D+2 (Úteis)" <?= ($ticket['sla'] ?? '') === 'D+2 (Úteis)' ? 'selected' : '' ?>>D+2 (Úteis)</option>
                            <?php $slaVal = $ticket['sla'] ?? ''; if ($slaVal && !in_array($slaVal, ['NBD (Úteis)', 'D+1 (Úteis)', 'D+2 (Úteis)'], true)): ?>
                            <option value="<?= e($slaVal) ?>" selected><?= e($slaVal) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-2"><label class="form-label">Reversa</label><input type="text" name="reversa" class="form-control form-control-sm" value="<?= $val('reversa') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-equipamento" style="cursor:pointer"><strong>Equipamento e endereço</strong></div>
            <div id="sec-equipamento" class="card-body collapse">
                <div class="row">
                    <div class="col-md-4 mb-2"><label class="form-label">Equipamento</label><input type="text" name="equipamento" class="form-control form-control-sm" value="<?= $val('equipamento') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Nº Série</label><input type="text" name="n_serie" class="form-control form-control-sm" value="<?= $val('n_serie') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Patrimônio</label><input type="text" name="patrimonio" class="form-control form-control-sm" value="<?= $val('patrimonio') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Hostname</label><input type="text" name="hostname" class="form-control form-control-sm" value="<?= $val('hostname') ?>"></div>
                    <div class="col-12 mb-2"><label class="form-label">Endereço</label><input type="text" name="endereco" id="endereco" class="form-control form-control-sm" value="<?= $val('endereco') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Bairro</label><input type="text" name="bairro" class="form-control form-control-sm" value="<?= $val('bairro') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Cidade</label><input type="text" name="cidade" id="cidade" class="form-control form-control-sm" value="<?= $val('cidade') ?>"></div>
                    <div class="col-md-2 mb-2"><label class="form-label">UF</label><input type="text" name="uf" id="uf" class="form-control form-control-sm" maxlength="5" value="<?= $val('uf') ?>"></div>
                    <div class="col-md-2 mb-2"><label class="form-label">CEP</label><input type="text" name="cep" id="cep" class="form-control form-control-sm" value="<?= $val('cep') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-solicitante" style="cursor:pointer"><strong>Solicitante / Usuário</strong></div>
            <div id="sec-solicitante" class="card-body collapse">
                <div class="row">
                    <div class="col-md-4 mb-2"><label class="form-label">Nome do solicitante do CH</label><input type="text" name="nome_solicitante_ch" class="form-control form-control-sm" value="<?= $val('nome_solicitante_ch') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Usuário</label><input type="text" name="usuario_contato" class="form-control form-control-sm" value="<?= $val('usuario_contato') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Telefone usuário</label><input type="text" name="telefone_usuario" class="form-control form-control-sm" value="<?= $val('telefone_usuario') ?>"></div>
                    <div class="col-md-6 mb-2"><label class="form-label">E-mail usuário</label><input type="email" name="email_usuario" class="form-control form-control-sm" value="<?= $val('email_usuario') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-tecnico" style="cursor:pointer"><strong>Dados do técnico / Atendimento</strong></div>
            <div id="sec-tecnico" class="card-body collapse">
                <div class="row">
                    <div class="col-md-4 mb-2"><label class="form-label">Nome do técnico</label><input type="text" name="nome_tecnico" class="form-control form-control-sm" value="<?= $val('nome_tecnico') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">CPF</label><input type="text" name="cpf_tecnico" class="form-control form-control-sm" placeholder="000.000.000-00" value="<?= $val('cpf_tecnico') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">RG</label><input type="text" name="rg_tecnico" class="form-control form-control-sm" value="<?= $val('rg_tecnico') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Data do atendimento</label><input type="date" name="data_atendimento" class="form-control form-control-sm" value="<?= $valDate('data_atendimento') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Hora do atendimento</label><input type="time" name="hora_atendimento" class="form-control form-control-sm" value="<?= $valTime('hora_atendimento') ?>"></div>
                    <?php if ($currentUser && in_array($currentUser['role'] ?? '', ['admin', 'agent', 'diretoria', 'suporte'], true)): ?>
                    <div class="col-md-4 mb-2"><label class="form-label">Valor do técnico</label><input type="text" name="valor_tecnico" class="form-control form-control-sm" placeholder="0,00" value="<?= e(isset($ticket['valor_tecnico']) && $ticket['valor_tecnico'] !== '' && $ticket['valor_tecnico'] !== null ? number_format((float) $ticket['valor_tecnico'], 2, ',', '') : '') ?>"></div>
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Modalidade</label>
                        <select name="modalidade_tecnico" class="form-select form-select-sm">
                            <option value="">Selecione</option>
                            <option value="Chamado" <?= ($ticket['modalidade_tecnico'] ?? '') === 'Chamado' ? 'selected' : '' ?>>Chamado</option>
                            <option value="Diária" <?= ($ticket['modalidade_tecnico'] ?? '') === 'Diária' ? 'selected' : '' ?>>Diária</option>
                        </select>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-numeros" style="cursor:pointer"><strong>Nº CH / Postagem / Disponibilidade</strong></div>
            <div id="sec-numeros" class="card-body collapse">
                <div class="row">
                    <div class="col-md-3 mb-2"><label class="form-label">N° de CH</label><input type="text" name="numero_ch" class="form-control form-control-sm" value="<?= $val('numero_ch') ?>"></div>
                    <div class="col-md-3 mb-2"><label class="form-label">Moebius</label><input type="text" name="moebius" class="form-control form-control-sm" value="<?= $val('moebius') ?>"></div>
                    <div class="col-md-3 mb-2"><label class="form-label">Nº de CL</label><input type="text" name="n_cl" class="form-control form-control-sm" value="<?= $val('n_cl') ?>"></div>
                    <div class="col-md-3 mb-2"><label class="form-label">Nº Tarefa/Remessa</label><input type="text" name="n_tarefa_remessa" class="form-control form-control-sm" value="<?= $val('n_tarefa_remessa') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Código de postagem</label><input type="text" name="codigo_postagem" class="form-control form-control-sm" value="<?= $val('codigo_postagem') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Data de postagem</label><input type="date" name="data_postagem" class="form-control form-control-sm" value="<?= $valDate('data_postagem') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Data vencimento CH</label><input type="date" name="data_vencimento_ch" class="form-control form-control-sm" value="<?= $valDate('data_vencimento_ch') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Data da disponibilidade</label><input type="date" name="data_disponibilidade" class="form-control form-control-sm" value="<?= $valDate('data_disponibilidade') ?>"></div>
                    <div class="col-md-4 mb-2"><label class="form-label">Hora da disponibilidade</label><input type="time" name="hora_disponibilidade" class="form-control form-control-sm" value="<?= $valTime('hora_disponibilidade') ?>"></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header collapsed" data-bs-toggle="collapse" data-bs-target="#sec-obs" style="cursor:pointer"><strong>Operação e observações</strong></div>
            <div id="sec-obs" class="card-body collapse">
                <div class="row">
                    <div class="col-md-6 mb-2"><label class="form-label">Operação</label><input type="text" name="operacao" class="form-control form-control-sm" value="<?= $val('operacao') ?>"></div>
                    <div class="col-12 mb-2"><label class="form-label">Intercorrências</label><textarea name="intercorrencias" class="form-control form-control-sm" rows="2"><?= $val('intercorrencias') ?></textarea></div>
                    <div class="col-12 mb-2"><label class="form-label">Observação sobre o técnico</label><textarea name="observacao_tecnico" class="form-control form-control-sm" rows="2"><?= $val('observacao_tecnico') ?></textarea></div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="/tickets/<?= $ticket['id'] ?>" class="btn btn-outline-secondary">Cancelar</a>
    </form>
</div>
<script>
(function(){
    var unidadeSelect=document.getElementById('unidade_select');
    if(!unidadeSelect)return;
    unidadeSelect.addEventListener('change',function(){
        var opt=this.options[this.selectedIndex];
        if(!opt||!opt.value)return;
        var endereco=document.getElementById('endereco'),cidade=document.getElementById('cidade'),uf=document.getElementById('uf'),cep=document.getElementById('cep'),sigla=document.getElementById('sigla_unidade_loja_ag');
        if(endereco)endereco.value=opt.dataset.endereco||'';
        if(cidade)cidade.value=opt.dataset.cidade||'';
        if(uf)uf.value=opt.dataset.uf||'';
        if(cep)cep.value=opt.dataset.cep||'';
        if(sigla)sigla.value=opt.dataset.nome||'';
    });
})();
</script>
<?php require __DIR__ . '/../partials/footer.php'; ?>
