<?php
$pageTitle = 'Categorias';
$currentUser = $currentUser ?? null;
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Categorias</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="card mb-4">
        <div class="card-header">Nova categoria</div>
        <div class="card-body">
            <form method="post" action="/categories" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-auto">
                    <input type="text" name="name" class="form-control" placeholder="Nome" required minlength="2">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Criar</button>
                </div>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= e((string) $c['id']) ?></td>
                        <td><?= e($c['name']) ?></td>
                        <td>
                            <form method="post" action="/categories/<?= $c['id'] ?>/update" class="d-inline">
                                <?= csrf_field() ?>
                                <input type="text" name="name" class="form-control form-control-sm d-inline-block w-auto" value="<?= e($c['name']) ?>">
                                <button type="submit" class="btn btn-sm btn-outline-primary">Editar</button>
                            </form>
                            <form method="post" action="/categories/<?= $c['id'] ?>/delete" class="d-inline" class="d-inline" onsubmit="return confirm('Excluir categoria?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
