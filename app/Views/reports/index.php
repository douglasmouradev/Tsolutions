<?php
$pageTitle = 'Relatórios';
$queryParams = array_filter($_GET ?? [], fn($v) => $v !== '');
$exportQuery = http_build_query($queryParams);
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Relatórios</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="card mb-4">
        <div class="card-header"><strong>Filtros</strong></div>
        <div class="card-body">
            <form method="get" action="/reports" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="aberto" <?= ($_GET['status'] ?? '') === 'aberto' ? 'selected' : '' ?>>Aberto</option>
                        <option value="em_andamento" <?= ($_GET['status'] ?? '') === 'em_andamento' ? 'selected' : '' ?>>Em andamento</option>
                        <option value="fechado" <?= ($_GET['status'] ?? '') === 'fechado' ? 'selected' : '' ?>>Fechado</option>
                        <option value="cancelado" <?= ($_GET['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prioridade</label>
                    <select name="priority" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="baixa" <?= ($_GET['priority'] ?? '') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                        <option value="media" <?= ($_GET['priority'] ?? '') === 'media' ? 'selected' : '' ?>>Média</option>
                        <option value="alta" <?= ($_GET['priority'] ?? '') === 'alta' ? 'selected' : '' ?>>Alta</option>
                        <option value="critica" <?= ($_GET['priority'] ?? '') === 'critica' ? 'selected' : '' ?>>Crítica</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Categoria</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= (string)($_GET['category_id'] ?? '') === (string)$c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if (!empty($agents)): ?>
                <div class="col-md-2">
                    <label class="form-label">Agente</label>
                    <select name="agent_id" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= (string)($_GET['agent_id'] ?? '') === (string)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
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
                        <option value="<?= $r['id'] ?>" <?= (string)($_GET['requester_id'] ?? '') === (string)$r['id'] ? 'selected' : '' ?>><?= e($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-2">
                    <label class="form-label">De</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="<?= e($_GET['date_from'] ?? '') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Até</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="<?= e($_GET['date_to'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Busca</label>
                    <input type="text" name="q" class="form-control form-control-sm" placeholder="Título ou descrição" value="<?= e($_GET['q'] ?? '') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm">Aplicar filtros</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Exportar</strong></div>
        <div class="card-body">
            <p class="text-muted">Exporte os chamados com os filtros aplicados para Excel ou PDF.</p>
            <div class="d-flex gap-2">
                <a href="/reports/export-excel<?= $exportQuery ? '?' . $exportQuery : '' ?>" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Exportar Excel (.xlsx)
                </a>
                <a href="/reports/export-pdf<?= $exportQuery ? '?' . $exportQuery : '' ?>" class="btn btn-danger">
                    <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
                </a>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
