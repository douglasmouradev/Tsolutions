<?php
$pageTitle = 'Chamado #' . $ticket['id'];
$statusLabels = ['aberto' => 'primary', 'em_andamento' => 'info', 'fechado' => 'success', 'cancelado' => 'secondary'];
$priorityLabels = ['baixa' => 'light text-dark', 'media' => 'warning', 'alta' => 'warning', 'critica' => 'danger'];
$slaSt = slaStatus($ticket['due_at'] ?? null, $ticket['status']);
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chamado #<?= e((string) $ticket['id']) ?></h1>
        <div>
            <?php if ($canManage): ?>
            <a href="/tickets/<?= $ticket['id'] ?>/edit" class="btn btn-outline-primary">Editar</a>
            <?php endif; ?>
            <a href="/tickets" class="btn btn-outline-secondary">Voltar</a>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body">
            <h4><?= e($ticket['title']) ?></h4>
            <?php if ($slaSt === 'vencido'): ?>
            <div class="alert alert-danger py-2 mb-2">
                <i class="bi bi-exclamation-triangle"></i> <strong>SLA vencido</strong> — O prazo de atendimento foi ultrapassado.
            </div>
            <?php elseif ($slaSt === 'proximo'): ?>
            <div class="alert alert-warning py-2 mb-2">
                <i class="bi bi-clock"></i> <strong>SLA próximo do vencimento</strong> — O prazo expira em breve.
            </div>
            <?php endif; ?>
            <div class="mb-2">
                <span class="badge bg-<?= $statusLabels[$ticket['status']] ?? 'secondary' ?> me-1"><?= e(statusLabel($ticket['status'])) ?></span>
                <span class="badge bg-<?= $priorityLabels[$ticket['priority']] ?? 'light text-dark' ?> me-1"><?= e(priorityLabel($ticket['priority'])) ?></span>
                <?php if ($ticket['category_name']): ?>
                <span class="badge bg-secondary"><?= e($ticket['category_name']) ?></span>
                <?php endif; ?>
            </div>
            <p class="text-muted small mb-0">
                Solicitante: <?= e($ticket['requester_name'] ?? '-') ?> (<?= e($ticket['requester_email'] ?? '') ?>) &bull;
                Agente: <?= e($ticket['agent_name'] ?? 'Não atribuído') ?> &bull;
                Criado: <?= formatDate($ticket['created_at']) ?> &bull;
                Atualizado: <?= formatDate($ticket['updated_at'] ?? $ticket['created_at']) ?>
                <?php if ($ticket['due_at']): ?> &bull; Vencimento: <?= formatDate($ticket['due_at']) ?><?php endif; ?>
            </p>
            <?php if ($ticket['description']): ?>
            <hr>
            <p class="mb-0"><?= nl2br(e($ticket['description'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $planilhaLabels = [
        'cliente_raiz' => 'Cliente raiz', 'cliente' => 'Cliente', 'sigla_unidade_loja_ag' => 'Sigla/Unidade/Loja/AG',
        'tipo_contrato' => 'Tipo de contrato', 'tipo_ch' => 'Tipo de CH', 'contrato_baseline' => 'Contrato baseline',
        'sla' => 'SLA', 'reversa' => 'Reversa', 'codigo_postagem' => 'Código de postagem', 'data_postagem' => 'Data de postagem',
        'equipamento' => 'Equipamento', 'n_serie' => 'Nº Série', 'patrimonio' => 'Patrimônio', 'hostname' => 'Hostname',
        'usuario_contato' => 'Usuário', 'telefone_usuario' => 'Telefone usuário', 'email_usuario' => 'E-mail usuário',
        'nome_solicitante_ch' => 'Nome do solicitante do CH', 'numero_ch' => 'N° de CH', 'moebius' => 'Moebius',
        'n_cl' => 'Nº de CL', 'n_tarefa_remessa' => 'Nº Tarefa/Remessa', 'endereco' => 'Endereço', 'bairro' => 'Bairro',
        'cidade' => 'Cidade', 'uf' => 'UF', 'cep' => 'CEP', 'data_vencimento_ch' => 'Data vencimento CH',
        'data_disponibilidade' => 'Data disponibilidade', 'hora_disponibilidade' => 'Hora disponibilidade',
        'operacao' => 'Operação', 'intercorrencias' => 'Intercorrências', 'observacao_tecnico' => 'Observação sobre o técnico',
        'nome_tecnico' => 'Nome do técnico', 'cpf_tecnico' => 'CPF', 'rg_tecnico' => 'RG',
        'data_atendimento' => 'Data do atendimento', 'hora_atendimento' => 'Hora do atendimento',
    ];
    $planilhaPreenchidos = [];
    foreach ($planilhaLabels as $key => $label) {
        $v = $ticket[$key] ?? null;
        if ($v !== null && $v !== '') {
            if (in_array($key, ['data_postagem', 'data_vencimento_ch', 'data_disponibilidade', 'data_atendimento'], true)) {
                $v = formatDate($v);
            } elseif (in_array($key, ['hora_disponibilidade', 'hora_atendimento'], true)) {
                $v = date('H:i', strtotime($v));
            }
            $planilhaPreenchidos[$label] = $v;
        }
    }
    if (!empty($planilhaPreenchidos)): ?>
    <div class="card mb-4">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#dados-planilha" style="cursor:pointer"><strong>Dados do controle (planilha)</strong></div>
        <div id="dados-planilha" class="card-body collapse show">
            <div class="row small">
                <?php foreach ($planilhaPreenchidos as $label => $valor): ?>
                <div class="col-md-6 col-lg-4 mb-2"><strong><?= e($label) ?>:</strong> <?= e(is_string($valor) ? $valor : (string)$valor) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($canManage && in_array($ticket['status'], ['aberto', 'em_andamento'], true)): ?>
    <div class="card mb-4">
        <div class="card-header">Alterar status</div>
        <div class="card-body">
            <form method="post" action="/tickets/<?= $ticket['id'] ?>/status" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm" required>
                        <option value="em_andamento" <?= $ticket['status'] === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <?php if ($canClose): ?>
                        <option value="fechado">Fechado</option>
                        <option value="cancelado">Cancelado</option>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <input type="text" name="note" class="form-control form-control-sm" placeholder="Observação (opcional)">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Alterar</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($currentUser && in_array($currentUser['role'], ['admin', 'agent'], true)): ?>
    <div class="card mb-4">
        <div class="card-header">Atribuir agente</div>
        <div class="card-body">
            <form method="post" action="/tickets/<?= $ticket['id'] ?>/assign" class="row g-2 align-items-end">
                <?= csrf_field() ?>
                <div class="col-auto">
                    <select name="agent_id" class="form-select form-select-sm">
                        <option value="">Nenhum</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (int)($ticket['agent_id'] ?? 0) === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">Atribuir</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#comments">Comentários</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#attachments">Anexos</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#history">Histórico</a></li>
    </ul>

    <div class="tab-content">
        <div id="comments" class="tab-pane fade show active">
            <?php if ($canManage): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <form method="post" action="/tickets/<?= $ticket['id'] ?>/comments">
                        <?= csrf_field() ?>
                        <div class="mb-2">
                            <textarea name="body" class="form-control" rows="3" placeholder="Novo comentário" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <div class="list-group">
                <?php foreach ($comments as $c): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <strong><?= e($c['author_name']) ?></strong>
                        <small><?= formatDate($c['created_at']) ?></small>
                    </div>
                    <p class="mb-0 mt-1"><?= nl2br(e($c['body'])) ?></p>
                </div>
                <?php endforeach; ?>
                <?php if (empty($comments)): ?>
                <div class="list-group-item text-muted">Nenhum comentário.</div>
                <?php endif; ?>
            </div>
        </div>
        <div id="attachments" class="tab-pane fade">
            <?php if ($canManage): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <form method="post" action="/tickets/<?= $ticket['id'] ?>/attachments" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" name="attachment" class="form-control form-control-sm mb-2" accept=".pdf,.png,.jpg,.jpeg,.txt,.docx" required>
                        <button type="submit" class="btn btn-sm btn-primary">Enviar</button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <div class="list-group">
                <?php foreach ($attachments as $a): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-file-earmark"></i> <?= e($a['original_name']) ?> (<?= number_format($a['size_bytes'] / 1024, 1) ?> KB)</span>
                    <a href="/tickets/<?= $ticket['id'] ?>/attachments/<?= $a['id'] ?>/download" class="btn btn-sm btn-outline-primary">Baixar</a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($attachments)): ?>
                <div class="list-group-item text-muted">Nenhum anexo.</div>
                <?php endif; ?>
            </div>
        </div>
        <div id="history" class="tab-pane fade">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>De</th>
                            <th>Para</th>
                            <th>Por</th>
                            <th>Observação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statusHistory as $h): ?>
                        <tr>
                            <td><?= formatDate($h['created_at']) ?></td>
                            <td><?= e(statusLabel($h['old_status'] ?? null) ?: '-') ?></td>
                            <td><?= e(statusLabel($h['new_status'])) ?></td>
                            <td><?= e($h['changed_by_name']) ?></td>
                            <td><?= e($h['note'] ?? '-') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($statusHistory)): ?>
                        <tr><td colspan="5" class="text-muted">Nenhum histórico.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
