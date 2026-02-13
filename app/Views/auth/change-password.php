<?php
$pageTitle = 'Alterar senha';
require __DIR__ . '/../partials/head.php';
?>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="card shadow-sm" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <img src="/assets/logo.png" alt="T Solutions" height="48" class="mb-2">
                <h2 class="card-title mb-0">Alteração obrigatória de senha</h2>
            </div>
            <p class="text-muted small text-center mb-4">É necessário alterar sua senha antes de continuar.</p>
            <?php require __DIR__ . '/../partials/flash.php'; ?>
            <form method="post" action="/change-password">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="current_password" class="form-label">Senha atual</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required autocomplete="current-password">
                </div>
                <div class="mb-3">
                    <label for="new_password" class="form-label">Nova senha</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirmar nova senha</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Alterar senha</button>
            </form>
            <div class="text-center mt-3">
                <form method="post" action="/logout" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-link text-muted btn-sm">Sair</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/../partials/footer.php'; ?>
