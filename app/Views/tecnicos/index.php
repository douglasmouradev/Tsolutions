<?php
$pageTitle = 'Cadastro Técnicos';
$currentUser = $currentUser ?? null;
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$val = fn($k) => e($old[$k] ?? '');
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Cadastro Técnicos</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>

    <div class="card mb-4">
        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#form-novo" style="cursor:pointer">
            <strong>Novo técnico</strong> <small class="text-muted">(clique para expandir)</small>
        </div>
        <div id="form-novo" class="card-body collapse">
            <form method="post" action="/tecnicos">
                <?= csrf_field();
                $t = $old;
                require __DIR__ . '/_form_fields.php'; ?>
                <button type="submit" class="btn btn-primary">Criar</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span>Técnicos cadastrados</span>
            <form method="get" action="/tecnicos" class="d-flex gap-2">
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Filtrar por nome, e-mail, CPF, celular..." value="<?= e($filtro ?? '') ?>" style="min-width:220px">
                <button type="submit" class="btn btn-sm btn-outline-primary">Filtrar</button>
                <?php if (!empty($filtro ?? '')): ?>
                <a href="/tecnicos" class="btn btn-sm btn-outline-secondary">Limpar</a>
                <?php endif; ?>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>CPF</th>
                            <th>Celular</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tecnicos as $t): ?>
                        <tr>
                            <td><?= e((string) $t['id']) ?></td>
                            <td><?= e($t['name']) ?></td>
                            <td><?= e($t['email'] ?? '-') ?></td>
                            <td><?= e($t['cpf'] ?? '-') ?></td>
                            <td><?= e($t['celular_1'] ?? $t['telefone'] ?? '-') ?></td>
                            <td>
                                <a href="/tecnicos/<?= $t['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Editar</a>
                                <form method="post" action="/tecnicos/<?= $t['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Excluir técnico?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($tecnicos)): ?>
                        <tr><td colspan="6" class="text-center text-muted">Nenhum técnico cadastrado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
