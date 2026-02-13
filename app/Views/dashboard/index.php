<?php
$pageTitle = 'Dashboard';
$currentUser = $currentUser ?? null;
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Dashboard</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h6 class="text-muted">Aberto</h6>
                    <h2><?= $counts['aberto'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h6 class="text-muted">Em andamento</h6>
                    <h2><?= $counts['em_andamento'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h6 class="text-muted">Fechado</h6>
                    <h2><?= $counts['fechado'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body">
                    <h6 class="text-muted">Cancelado</h6>
                    <h2><?= $counts['cancelado'] ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Últimos chamados atualizados</span>
            <a href="/tickets" class="btn btn-sm btn-outline-primary">Ver todos</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Prioridade</th>
                            <th>Solicitante</th>
                            <th>Agente</th>
                            <th>Atualizado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lastTickets as $t): ?>
                        <tr>
                            <td><?= e((string) $t['id']) ?></td>
                            <td><?= e($t['title']) ?></td>
                            <td><span class="badge bg-<?= $t['status'] === 'aberto' ? 'primary' : ($t['status'] === 'em_andamento' ? 'info' : ($t['status'] === 'fechado' ? 'success' : 'secondary')) ?>"><?= e(statusLabel($t['status'])) ?></span></td>
                            <td><span class="badge bg-<?= $t['priority'] === 'critica' ? 'danger' : ($t['priority'] === 'alta' ? 'warning' : 'light text-dark') ?>"><?= e($t['priority']) ?></span></td>
                            <td><?= e($t['requester_name'] ?? '-') ?></td>
                            <td><?= e($t['agent_name'] ?? '-') ?></td>
                            <td><?= formatDate($t['updated_at'] ?? $t['created_at']) ?></td>
                            <td><a href="/tickets/<?= $t['id'] ?>" class="btn btn-sm btn-outline-secondary">Ver</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($lastTickets)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Nenhum chamado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
