<?php
$pageTitle = 'Chamados';
$currentUser = $currentUser ?? null;
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Chamados</h1>
        <a href="/tickets/create" class="btn btn-primary">Novo chamado</a>
    </div>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" action="/tickets" class="row g-3">
                <?php foreach ($filters as $k => $v): if ($v !== '' && $k !== 'q'): ?>
                <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
                <?php endif; endforeach; ?>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="aberto" <?= ($filters['status'] ?? '') === 'aberto' ? 'selected' : '' ?>>Aberto</option>
                        <option value="em_andamento" <?= ($filters['status'] ?? '') === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="fechado" <?= ($filters['status'] ?? '') === 'fechado' ? 'selected' : '' ?>>Fechado</option>
                        <option value="cancelado" <?= ($filters['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioridade</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="baixa" <?= ($filters['priority'] ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                        <option value="media" <?= ($filters['priority'] ?? '') === 'media' ? 'selected' : '' ?>>Média</option>
                        <option value="alta" <?= ($filters['priority'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="critica" <?= ($filters['priority'] ?? '') === 'critica' ? 'selected' : '' ?>>Urgente</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoria</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (string)($filters['category_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($agents)): ?>
                <div class="col-md-2">
                    <label class="form-label">Agente</label>
                    <select name="agent_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (string)($filters['agent_id'] ?? '') === (string)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <?php if (!empty($requesters)): ?>
                <div class="col-md-2">
                    <label class="form-label">Solicitante</label>
                    <select name="requester_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($requesters as $r): ?>
                        <option value="<?= $r['id'] ?>" <?= (string)($filters['requester_id'] ?? '') === (string)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <label class="form-label">De</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($filters['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Até</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($filters['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">SLA</label>
                    <select name="sla_status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="vencidos" <?= ($filters['sla_status'] ?? '') === 'vencidos' ? 'selected' : '' ?>>Vencidos</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Busca</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Título ou descrição" value="<?= e($filters['q'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Prioridade</th>
                            <th>Categoria</th>
                            <th>Solicitante</th>
                            <th>Agente</th>
                            <th>Criado</th>
                            <th>Vencimento</th>
                            <th>SLA</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($result['items'] as $t): ?>
                        <?php $sla = slaStatus($t['due_at'] ?? null, $t['status']); ?>
                        <tr>
                            <td><?= e((string) $t['id']) ?></td>
                            <td><?= e($t['title']) ?></td>
                            <td><span class="badge bg-<?= $t['status'] === 'aberto' ? 'primary' : ($t['status'] === 'em_andamento' ? 'info' : ($t['status'] === 'fechado' ? 'success' : 'secondary')) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                            <td><span class="badge bg-<?= $t['priority'] === 'critica' ? 'danger' : ($t['priority'] === 'alta' ? 'warning' : 'light text-dark') ?>"><?= e(priorityLabel($t['priority'])) ?></span></td>
                            <td><?= e($t['category_name'] ?? '-') ?></td>
                            <td><?= e($t['requester_name'] ?? '-') ?></td>
                            <td><?= e($t['agent_name'] ?? '-') ?></td>
                            <td><?= formatDate($t['created_at']) ?></td>
                            <td><?= $t['due_at'] ? formatDate($t['due_at']) : '-' ?></td>
                            <td><?php if ($sla === 'vencido'): ?><span class="badge bg-danger">Vencido</span><?php elseif ($sla === 'proximo'): ?><span class="badge bg-warning">Próximo</span><?php elseif ($sla === 'ok'): ?><span class="badge bg-success">OK</span><?php else: ?>-<?php endif; ?></td>
                            <td>
                                <a href="/tickets/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
                                <?php if ($currentUser && $authService->canManageTicket($t)): ?>
                                <form method="post" action="/tickets/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Excluir este chamado?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($result['items'])): ?>
                        <tr><td colspan="12" class="text-center text-muted">Nenhum chamado encontrado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($result['total_pages'] > 1): ?>
        <div class="card-footer">
            <nav>
                <ul class="pagination pagination-sm mb-0 justify-content-center">
                    <?php
                    $queryParams = array_filter($filters, fn($v) => $v !== '');
                    $baseQuery = http_build_query($queryParams);
                    $baseUrl = '/tickets' . ($baseQuery ? '?' . $baseQuery : '');
                    $sep = $baseQuery ? '&' : '?';
                    ?>
                    <li class="page-item <?= $result['page'] <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $result['page'] <= 1 ? '#' : $baseUrl . $sep . 'page=' . ($result['page'] - 1) ?>">Anterior</a>
                    </li>
                    <?php for ($i = 1; $i <= min($result['total_pages'], 10); $i++): ?>
                    <li class="page-item <?= $i === $result['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $baseUrl . $sep . 'page=' . $i ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?= $result['page'] >= $result['total_pages'] ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $result['page'] >= $result['total_pages'] ? '#' : $baseUrl . $sep . 'page=' . ($result['page'] + 1) ?>">Próximo</a>
                    </li>
                </ul>
            </nav>
            <p class="text-center text-muted small mb-0 mt-1"><?= $result['total'] ?> registro(s)</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
