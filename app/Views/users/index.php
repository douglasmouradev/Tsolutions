<?php
$pageTitle = 'Usuários';
$currentUser = $currentUser ?? null;
require __DIR__ . '/../partials/head.php';
require __DIR__ . '/../partials/nav.php';
?>
<div class="container py-4">
    <h1 class="mb-4">Usuários</h1>
    <?php require __DIR__ . '/../partials/flash.php'; ?>
    <div class="card mb-4">
        <div class="card-header">Novo usuário</div>
        <div class="card-body">
            <form method="post" action="/users" class="row g-3">
                <?= csrf_field() ?>
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Nome" required>
                </div>
                <div class="col-md-3">
                    <input type="email" name="email" class="form-control" placeholder="E-mail" required>
                </div>
                <div class="col-md-2">
                    <input type="password" name="password" class="form-control" placeholder="Senha" required minlength="6">
                </div>
                <div class="col-md-2">
                    <select name="role" class="form-select">
                        <option value="requester">Solicitante</option>
                        <option value="agent">Agente</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="col-md-2">
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
                        <th>E-mail</th>
                        <th>Papel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): ?>
                    <tr>
                        <td><?= e((string) $u['id']) ?></td>
                        <td><?= e($u['name']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><span class="badge bg-secondary"><?= e($u['role']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
